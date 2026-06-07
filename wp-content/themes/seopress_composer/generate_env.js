import { writeFileSync } from 'fs';

const servers = [
  { name: '1a-entruempelung-kaernten.at', host: 'ftp.world4you.com', user: 'ftp31818021', pass: 'RTZwYUhHMDA3IzExOTA=' },
  { name: 'bregenz-entruempelung.at', host: 'ftp.world4you.com', user: 'ftp31749465', pass: 'RTZwYUhHMDA3IzExOTA=' },
  { name: 'burgenland-entruempelung.at', host: 'ftp.world4you.com', user: 'ftp31749276', pass: 'RTZwYUhHMDA3IzExOTA=' },
  { name: 'entruempelung-experte.at', host: 'ftp.world4you.com', user: 'ftp31667936', pass: 'RTZwYUhHMDA3IzExOTA=' },
  { name: 'entruempelung-experte.de', host: 'ftp.world4you.com', user: 'ftp31777853', pass: 'RTZwYUhHMDA3IzExOTA=' },
  { name: 'entruempelung-experte.com', host: 'ftp.world4you.com', user: 'ftp31722733', pass: 'RTZwYUhHMDA3IzExOTA=' },
  { name: 'entruempelung-raeumung.bayern', host: 'ftp.world4you.com', user: 'ftp31818593', pass: 'RTZwYUhHMDA3IzExOTA=' },
  { name: 'entruempelung-st-poelten.at', host: 'ftp.world4you.com', user: 'ftp31766363', pass: 'RTZwYUhHMDA3IzExOTA=' },
  { name: 'entruempelungen-wien.at', host: 'ftp.world4you.com', user: 'ftp31808808', pass: 'RTZwYUhHMDA3IzExOTA=' },
  { name: 'graz-entruempelung.at', host: 'ftp.world4you.com', user: 'ftp31667063', pass: 'RTZwYUhHMDA3IzExOTA=' },
  { name: 'innsbruck-entruempelung.at', host: 'ftp.world4you.com', user: 'ftp31667071', pass: 'RTZwYUhHMDA3IzExOTA=' },
  { name: 'klagenfurt-entruempelung.at', host: 'ftp.world4you.com', user: 'ftp31667083', pass: 'RTZwYUhHMDA3IzExOTA=' },
  { name: 'linz-entruempelungen.at', host: 'ftp.world4you.com', user: 'ftp31757404', pass: 'RTZwYUhHMDA3IzExOTA=' },
  { name: 'messie-entruempelung-experte.at', host: 'ftp.world4you.com', user: 'ftp31684382', pass: 'RTZwYUhHMDA3IzExOTA=' },
  { name: 'niederoesterreich-entruempelung.at', host: 'ftp.world4you.com', user: 'ftp31637754', pass: 'RTZwYUhHMDA3IzExOTA=' },
  { name: 'oberoesterreich-entruempelung.at', host: 'ftp.world4you.com', user: 'ftp31749187', pass: 'RTZwYUhHMDA3IzExOTA=' },
  { name: 'salzburg-entruempelung.at', host: 'ftp.world4you.com', user: 'ftp31641749', pass: 'RTZwYUhHMDA3IzExOTA=' },
  { name: 'st-poelten-entruempelung.at', host: 'ftp.world4you.com', user: 'ftp31749067', pass: 'RTZwYUhHMDA3IzExOTA=' },
  { name: 'steiermark-entruempelung.at', host: 'ftp.world4you.com', user: 'ftp31749064', pass: 'RTZwYUhHMDA3IzExOTA=' },
  { name: 'tirol-entruempelung.at', host: 'ftp.world4you.com', user: 'ftp31778595', pass: 'RTZwYUhHMDA3IzExOTA=' },
  { name: 'messie.wien', host: 'ftp.world4you.com', user: 'ftp4138182', pass: 'RTZwYUhHMDA3IzExMzA=' }
];


let envContent = `DOMAINS="${servers.map(s => s.name).join(',')}"\n\n`;

servers.forEach(s => {
  // Determine a clean key/slug
  // Remove dots, spaces, special chars, uppercase
  let slug = s.name.replace(/\./g, '_').replace(/-/g, '_').replace(/ /g, '_').replace(/ä/g, 'ae').replace(/ö/g, 'oe').replace(/ü/g, 'ue').toUpperCase();

  // Decode password
  let decodedPass = Buffer.from(s.pass, 'base64').toString('utf-8');

  envContent += `# ${s.name}\n`;
  envContent += `${slug}_HOST=${s.host}\n`;
  envContent += `${slug}_USER=${s.user}\n`;
  envContent += `${slug}_PASSWORD="${decodedPass}"\n`;

  // Per-domain SMTP configuration
  envContent += `${slug}_SMTP_HOST=smtp.world4you.com\n`;
  envContent += `${slug}_SMTP_PORT=587\n`;
  envContent += `${slug}_SMTP_SECURE=tls\n`;
  envContent += `${slug}_SMTP_USER=info@${s.name}\n`;
  envContent += `${slug}_SMTP_PASSWORD="${decodedPass}"\n\n`;
});

// Append the global SMTP configuration used by deploy.js (e.g. for fallback or other scripts)
envContent += `# SMTP Configuration (used by deploy.js for mu-plugin generation)\n`;
envContent += `SMTP_HOST=smtp.gmail.com\n`;
envContent += `SMTP_PORT=587\n`;
envContent += `SMTP_SECURE=tls\n`;
envContent += `SMTP_USER=r.sauer007@gmail.com\n`;
envContent += `SMTP_PASS="uoei nsqp rwch aptd"\n`;

writeFileSync('.env', envContent);
console.log('.env file generated with ' + servers.length + ' servers.');
