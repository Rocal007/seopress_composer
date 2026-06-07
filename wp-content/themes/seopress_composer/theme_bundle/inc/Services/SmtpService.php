<?php

namespace SeopressComposer\Services;

use SeopressComposer\Settings\SiteSettings;

class SmtpService
{
  private SiteSettings $siteSettings;

  public function __construct(SiteSettings $siteSettings)
  {
    $this->siteSettings = $siteSettings;

    // Configure PHPMailer
    add_action('phpmailer_init', [$this, 'configure_phpmailer']);

    // Handle Test Email Logic (triggered via GET param in admin)
    add_action('admin_init', [$this, 'handle_test_email']);
  }

  /**
   * Configure PHPMailer with SMTP settings.
   * Prefers SEOPRESS_SMTP_* constants (set via mu-plugin on deploy)
   * and falls back to ACF site settings.
   */
  public function configure_phpmailer($phpmailer)
  {
    // Constants take priority (set by deploy mu-plugin), then ACF fields
    $host   = defined('SEOPRESS_SMTP_HOST')   ? \SEOPRESS_SMTP_HOST   : $this->siteSettings->get_option('smtp_host');
    $port   = defined('SEOPRESS_SMTP_PORT')   ? \SEOPRESS_SMTP_PORT   : $this->siteSettings->get_option('smtp_port');
    $user   = defined('SEOPRESS_SMTP_USER')   ? \SEOPRESS_SMTP_USER   : $this->siteSettings->get_option('smtp_user');
    $pass   = defined('SEOPRESS_SMTP_PASS')   ? \SEOPRESS_SMTP_PASS   : $this->siteSettings->get_option('smtp_pass');
    $secure = defined('SEOPRESS_SMTP_SECURE') ? \SEOPRESS_SMTP_SECURE : $this->siteSettings->get_option('smtp_secure');

    $host = is_string($host) ? trim($host) : $host;
    $user = is_string($user) ? trim($user) : $user;
    $port = is_string($port) ? trim($port) : $port;

    // If no host is configured anywhere, do not override - fallback to PHP mail()
    if (empty($host)) {
      return;
    }

    $phpmailer->isSMTP();
    $phpmailer->Host       = $host;
    $phpmailer->Port       = $port ?: 587;
    
    if (!empty($user) || !empty($pass)) {
      $phpmailer->SMTPAuth   = true;
      $phpmailer->Username   = $user;
      $phpmailer->Password   = $pass;
    } else {
      $phpmailer->SMTPAuth   = false;
    }

    // Authorization Mode
    if ($secure !== 'none') {
      $phpmailer->SMTPSecure = $secure ?: 'tls';
    } else {
      $phpmailer->SMTPSecure = '';
      $phpmailer->SMTPAutoTLS = false;
    }

    $phpmailer->From       = $user ?: get_option('admin_email');
    $phpmailer->FromName   = get_bloginfo('name');

    // Bypass SSL verification issues (especially common in local dev like Laragon)
    $phpmailer->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
  }

  /**
   * Handle sending a test email
   */
  public function handle_test_email()
  {
    // Check permissions and params
    if (!is_admin() || !current_user_can('manage_options')) {
      return;
    }

    if (isset($_GET['page']) && $_GET['page'] === 'site-settings' && isset($_GET['smtp_test']) && $_GET['smtp_test'] == '1') {

      $to = isset($_GET['recipient']) ? sanitize_email($_GET['recipient']) : get_option('admin_email');
      if (!is_email($to)) {
        add_action('admin_notices', function () {
          echo '<div class="notice notice-error is-dismissible"><p>Ungültige E-Mail-Adresse für den Empfänger.</p></div>';
        });
        return;
      }

      $subject = 'SMTP Verbindungstest - ' . get_bloginfo('name');
      $message = "Erfolg! \n\nDie SMTP-Konfiguration auf Ihrer Website (" . home_url() . ") funktioniert korrekt.\n\nGesendet an: " . $to;

      // Capture errors
      global $phpmailer;
      
      // We will capture SMTP output to show detailed error
      $smtp_debug_log = '';

      // Set up phpmailer_init hook just for this test
      add_action('phpmailer_init', function($phpmailer) use (&$smtp_debug_log) {
          $phpmailer->SMTPDebug = 3; // Connection + Client + Server messages
          $phpmailer->Debugoutput = function($str, $level) use (&$smtp_debug_log) {
              $smtp_debug_log .= htmlspecialchars($str) . "<br>";
          };
      }, 999);

      // Force error capturing
      add_action('wp_mail_failed', function ($error) {
        global $ts_mail_error;
        $ts_mail_error = $error;
      });

      $start_time = microtime(true);
      $sent = wp_mail($to, $subject, $message);
      $end_time = microtime(true);
      $duration = round($end_time - $start_time, 2);

      if ($sent) {
        add_action('admin_notices', function () use ($to, $duration) {
          echo '<div class="notice notice-success is-dismissible"><p><strong>Test-E-Mail erfolgreich versendet!</strong><br>Empfänger: ' . esc_html($to) . '<br>Dauer: ' . $duration . 's</p></div>';
        });
      } else {
        global $ts_mail_error;
        $error_msg = 'Unbekannter Fehler';

        if (isset($ts_mail_error) && is_wp_error($ts_mail_error)) {
          $error_msg = $ts_mail_error->get_error_message();
          // Add extra data if available
          if ($error_data = $ts_mail_error->get_error_data()) {
            $error_msg .= ' <br>Details: ' . print_r($error_data, true);
          }
        }

        add_action('admin_notices', function () use ($error_msg, &$smtp_debug_log) {
          echo '<div class="notice notice-error is-dismissible" style="padding-bottom:10px;">';
          echo '<p><strong>Fehler beim Senden:</strong><br>' . wp_kses_post($error_msg) . '</p>';
          if (!empty($smtp_debug_log)) {
             echo '<hr><strong>SMTP Debug Log (Server-Antwort):</strong>';
             echo '<pre style="background:#f1f1f1; padding:10px; max-height:300px; overflow-y:auto; font-size:11px;">' . $smtp_debug_log . '</pre>';
          }
          echo '</div>';
        });
      }
    }
  }
}
