const ftp = require('basic-ftp');
const dotenv = require('dotenv');
const chalk = require('chalk');
const fs = require('fs');
const os = require('os');
const path = require('path');

dotenv.config();

// --- Configuration Helper ---
function getPrefix(name) {
  return name.replace(/\./g, '_').replace(/-/g, '_').replace(/ /g, '_').replace(/ä/g, 'ae').replace(/ö/g, 'oe').replace(/ü/g, 'ue').toUpperCase();
}

// --- Recursive File Walker with Excludes ---
function getAllFiles(dirPath, arrayOfFiles) {
  const files = fs.readdirSync(dirPath);
  arrayOfFiles = arrayOfFiles || [];

  const excludes = [
    '.git',
    '.github',
    'node_modules',
    'tests',
    'Tests',
    'test',
    'Test',
    'docs',
    'doc',
    'examples',
    'EXAMPLE',
    'README.md',
    'CONTRIBUTING.md',
    'LICENSE',
    'LICENSE.txt',
    'CHANGELOG.md',
    '.gitignore',
    '.DS_Store',
    'phpunit.xml',
    'phpunit.xml.dist',
    'phpcs.xml',
    'phpcs.xml.dist',
    'installers' // Added as requested to reduce bloat
  ];

  files.forEach(function (file) {
    if (excludes.includes(file)) return;

    const fullPath = path.join(dirPath, file);
    if (fs.statSync(fullPath).isDirectory()) {
      arrayOfFiles = getAllFiles(fullPath, arrayOfFiles);
    } else {
      arrayOfFiles.push(fullPath);
    }
  });
  return arrayOfFiles;
}

// --- Core Deployment Function ---
async function deployToServer(targetName, config) {
  console.log(chalk.bold.blue(`\n--- Deployment START: ${targetName} ---`));
  const client = new ftp.Client();
  client.ftp.timeout = 30000;
  const localRoot = 'theme_bundle';

  try {
    console.log(chalk.blue(`Verbinde zu ${config.host}...`));
    await client.access(config);
    console.log(chalk.green('Verbunden.'));

    const list = await client.list('/');
    let webRoot = '';
    if (list.find(f => f.name === 'httpdocs')) webRoot = '/httpdocs';
    else if (list.find(f => f.name === 'public_html')) webRoot = '/public_html';

    const deployPath = webRoot + '/wp-content/themes/seopress_composer';

    console.log(chalk.blue(`Zielordner: ${deployPath}`));
    await client.ensureDir(deployPath);

    const allFiles = getAllFiles(localRoot);
    const knownRemoteDirs = new Set([deployPath]);
    console.log(chalk.cyan(`Upload: ${allFiles.length} Dateien.`));

    for (const filePath of allFiles) {
      const relativePath = path.relative(localRoot, filePath).replace(/\\/g, '/');
      const remoteFilePath = path.posix.join(deployPath, relativePath);
      const remoteDir = path.posix.dirname(remoteFilePath);

      if (!knownRemoteDirs.has(remoteDir)) {
        try {
          await client.ensureDir(remoteDir);
          knownRemoteDirs.add(remoteDir);
        } catch (e) { }
      }

      // Retry Logic
      let uploaded = false;
      let attempts = 0;
      const maxAttempts = 3;
      process.stdout.write(`${relativePath} ... `);

      while (!uploaded && attempts < maxAttempts) {
        attempts++;
        try {
          await client.uploadFrom(filePath, remoteFilePath);
          console.log(chalk.green('OK'));
          uploaded = true;
        } catch (err) {
          process.stdout.write(chalk.yellow(`RETRY(${attempts}) `));
          if (attempts >= maxAttempts) {
            console.log(chalk.red('FAIL'));
            throw new Error(`Upload failed for ${relativePath}: ${err.message}`);
          }
          await new Promise(r => setTimeout(r, 2000));
          try { await client.access(config); } catch (e) { }
        }
      }
    }

    // --- Upload SMTP mu-plugin (per-domain credentials) ---
    await uploadSmtpMuPlugin(client, webRoot, targetName, config.password);

    console.log(chalk.green(`✓ Deployment für ${targetName} OK.`));
  } catch (err) {
    console.error(chalk.red(`X FEHLER bei ${targetName}:`), err.message);
    throw err;
  } finally {
    client.close();
  }
}

// --- Derive domain name from prefix/target ---
function prefixToDomain(prefix) {
  // DOMAINS list in .env has the original domain names
  const domainsList = (process.env.DOMAINS || '').split(',').map(d => d.trim()).filter(Boolean);
  // Find the domain whose prefix matches
  for (const domain of domainsList) {
    if (getPrefix(domain) === prefix.toUpperCase()) {
      return domain;
    }
  }
  // Fallback: reverse the prefix conversion (underscores → hyphens, last part is TLD)
  const parts = prefix.toLowerCase().split('_');
  const tld = parts.pop();
  return parts.join('-') + '.' + tld;
}

// --- SMTP mu-plugin Upload (per-domain) ---
async function uploadSmtpMuPlugin(client, webRoot, targetName, ftpPassword) {
  // Derive domain from target name
  const domain = prefixToDomain(targetName);

  // Try to get per-domain SMTP settings from .env
  const prefix = getPrefix(domain);
  const explicitSmtpHost = process.env[`${prefix}_SMTP_HOST`];
  const explicitSmtpUser = process.env[`${prefix}_SMTP_USER`];
  const explicitSmtpPass = process.env[`${prefix}_SMTP_PASSWORD`];
  const explicitSmtpPort = process.env[`${prefix}_SMTP_PORT`];
  const explicitSmtpSecure = process.env[`${prefix}_SMTP_SECURE`];

  // Fallback to global SMTP settings or defaults
  const smtpHost = explicitSmtpHost || process.env.SMTP_HOST || 'smtp.world4you.com';
  const smtpPort = explicitSmtpPort || process.env.SMTP_PORT || 587;
  const smtpSecure = explicitSmtpSecure || process.env.SMTP_SECURE || 'tls';
  const smtpUser = explicitSmtpUser || process.env.SMTP_USER || `info@${domain}`;
  const smtpPass = explicitSmtpPass || process.env.SMTP_PASS || ftpPassword;

  const phpContent = `<?php
/**
 * Plugin Name: SMTP Constants (Auto-Deployed)
 * Description: Defines SEOPRESS_SMTP_* constants for the theme SmtpService.
 * Auto-generated by deploy.js – do not edit manually.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'SEOPRESS_SMTP_HOST',   '${smtpHost}' );
define( 'SEOPRESS_SMTP_PORT',    ${smtpPort} );
define( 'SEOPRESS_SMTP_SECURE', '${smtpSecure}' );
define( 'SEOPRESS_SMTP_USER',   '${smtpUser}' );
define( 'SEOPRESS_SMTP_PASS',   '${smtpPass.replace(/'/g, "\\'")}' );
`;

  const muPluginPath = webRoot + '/wp-content/mu-plugins/smtp-constants.php';
  const tmpFile = path.join(os.tmpdir(), 'smtp-constants.php');

  try {
    fs.writeFileSync(tmpFile, phpContent, 'utf8');

    // Ensure mu-plugins directory exists
    const muDir = path.posix.dirname(muPluginPath);
    try { await client.ensureDir(muDir); } catch (e) { }

    process.stdout.write(`mu-plugins/smtp-constants.php (${smtpUser}) ... `);
    await client.uploadFrom(tmpFile, muPluginPath);
    console.log(chalk.green('OK'));
  } catch (err) {
    console.log(chalk.yellow(`⚠ SMTP mu-plugin Upload fehlgeschlagen: ${err.message}`));
  } finally {
    try { fs.unlinkSync(tmpFile); } catch (e) { }
  }
}

// --- Main execution ---
async function main() {
  const input = process.argv[2];
  if (!input) {
    console.log(chalk.red('Fehler: Ziel oder "all" angeben.'));
    process.exit(1);
  }

  if (input === 'all') {
    const targets = [];
    for (const key in process.env) {
      if (key.endsWith('_HOST')) {
        const prefix = key.replace('_HOST', '');
        if (process.env[`${prefix}_USER`] && process.env[`${prefix}_PASSWORD`]) {
          targets.push({
            name: prefix,
            config: {
              host: process.env[`${prefix}_HOST`],
              user: process.env[`${prefix}_USER`],
              password: process.env[`${prefix}_PASSWORD`],
              secure: true
            }
          });
        }
      }
    }

    console.log(chalk.magenta(`Starte Batch-Deployment für ${targets.length} Server.`));
    const results = { success: 0, fail: 0 };

    for (const t of targets) {
      try {
        await deployToServer(t.name, t.config);
        results.success++;
      } catch (e) {
        results.fail++;
      }
    }
    console.log(chalk.bold(`\nBATCH FERTIG: ${results.success} Erfolgreich, ${results.fail} Fehler.`));

  } else {
    const prefix = getPrefix(input);
    const config = {
      host: process.env[`${prefix}_HOST`],
      user: process.env[`${prefix}_USER`],
      password: process.env[`${prefix}_PASSWORD`],
      secure: true
    };
    if (!config.host || !config.user) {
      console.log(chalk.red(`Keine Config für ${input} (${prefix}).`));
      process.exit(1);
    }
    await deployToServer(input, config);
  }
}

main();
