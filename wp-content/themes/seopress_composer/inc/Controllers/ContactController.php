<?php

namespace SeopressComposer\Controllers;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use SeopressComposer\Settings\SiteSettings;

class ContactController
{
  private SiteSettings $siteSettings;

  public function __construct(SiteSettings $siteSettings)
  {
    $this->siteSettings = $siteSettings;
  }

  public function register_routes(): void
  {
    add_action('rest_api_init', function () {
      register_rest_route('seopress/v1', '/contact', [
        'methods' => 'POST',
        'callback' => [$this, 'handle_submission'],
        'permission_callback' => '__return_true', // Public form
      ]);
    });
  }

  public function handle_submission(WP_REST_Request $request): WP_REST_Response
  {
    $params = $request->get_params();
    $files = $request->get_file_params();

    // 1. Validation
    if (empty($params['firstname']) || empty($params['lastname']) || empty($params['email'])) {
      return new WP_REST_Response(['success' => false, 'message' => 'Bitte füllen Sie alle Pflichtfelder aus.'], 400);
    }

    if (!is_email($params['email'])) {
      return new WP_REST_Response(['success' => false, 'message' => 'Ungültige E-Mail-Adresse.'], 400);
    }

    // 2. Prepare Data
    $firstname = sanitize_text_field($params['firstname']);
    $lastname = sanitize_text_field($params['lastname']);
    $email = sanitize_email($params['email']);
    $phone = sanitize_text_field($params['phone'] ?? '');
    $description = sanitize_textarea_field($params['description'] ?? '');
    $area = sanitize_text_field($params['area'] ?? '');
    $floor = sanitize_text_field($params['floor'] ?? '');
    $viewing = !empty($params['request_viewing']) ? 'JA (Kostenlose Besichtigung/Termin gewünscht)' : 'Nein';
    $preferred_date = sanitize_text_field($params['preferred_date'] ?? '');

    $services = isset($params['services']) ? (array)$params['services'] : [];
    $services_text = implode(', ', array_map('sanitize_text_field', $services));

    // 3. Handle File Uploads (Optional)
    $attachments = [];
    if (!empty($files['images'])) {
      require_once(ABSPATH . 'wp-admin/includes/image.php');
      require_once(ABSPATH . 'wp-admin/includes/file.php');
      require_once(ABSPATH . 'wp-admin/includes/media.php');

      // Handle multiple files
      foreach ($files['images'] as $file) {
        if ($file['error'] !== UPLOAD_ERR_OK) continue;
        $upload_overrides = ['test_form' => false];
        $movefile = wp_handle_upload($file, $upload_overrides);
        if ($movefile && !isset($movefile['error'])) {
          $attachments[] = $movefile['file'];
        }
      }
    }

    // 4. Send Email
    // Fetch recipient email from Site Settings, fallback to admin email
    $to = $this->siteSettings->get_option('e-mail', get_option('admin_email'));
    
    // Fallback if empty
    if (empty($to)) {
       $to = get_option('admin_email');
    }

    $subject = 'Entrümpelungs-Anfrage: ' . $firstname . ' ' . $lastname;
    $headers = [
      'Content-Type: text/html; charset=UTF-8',
      'Reply-To: ' . $firstname . ' ' . $lastname . ' <' . $email . '>'
    ];


    ob_start();
?>
    <!DOCTYPE html>
    <html>

    <head>
      <style>
        body {
          font-family: sans-serif;
          line-height: 1.6;
          color: #333;
        }

        .container {
          max-width: 600px;
          margin: 0 auto;
          padding: 20px;
          border: 1px solid #ddd;
        }

        h2 {
          color: #1a120b;
          border-bottom: 2px solid #A67C00;
          padding-bottom: 10px;
        }

        .field {
          margin-bottom: 15px;
        }

        .label {
          font-weight: bold;
          display: block;
          margin-bottom: 5px;
          color: #71717a;
          font-size: 12px;
          text-transform: uppercase;
        }

        .value {
          background: #fdfbf7;
          padding: 10px;
          border: 1px solid #e5e7eb;
          border-radius: 4px;
          font-size: 16px;
        }

        .highlight {
          border-left: 4px solid #A67C00;
          background: #fff;
        }
      </style>
    </head>

    <body>
      <div class="container">
        <h2>Neue Entrümpelungs-Anfrage</h2>

        <div class="field">
          <span class="label">Besichtigungstermin:</span>
          <div class="value highlight"><strong><?= $viewing ?></strong> <?= $preferred_date ? ' | Wunschtermin: ' . $preferred_date : '' ?></div>
        </div>

        <div class="field">
          <span class="label">Zu entrümpelnde Bereiche:</span>
          <div class="value"><?= $services_text ?: 'Keine Auswahl' ?></div>
        </div>

        <div class="field">
          <span class="label">Fläche & Etage:</span>
          <div class="value"><?= $area ? $area . ' m²' : 'Keine Angabe' ?> | Stockwerk: <?= $floor ?: 'Keine Angabe' ?></div>
        </div>

        <div class="field">
          <span class="label">Kunde:</span>
          <div class="value"><?= $firstname ?> <?= $lastname ?></div>
        </div>

        <div class="field">
          <span class="label">Kontakt:</span>
          <div class="value">
            E-Mail: <a href="mailto:<?= $email ?>"><?= $email ?></a><br>
            Tel: <a href="tel:<?= $phone ?>"><?= $phone ?></a>
          </div>
        </div>

        <div class="field">
          <span class="label">Zusätzliche Infos:</span>
          <div class="value"><?= nl2br($description) ?: 'Keine Beschreibung' ?></div>
        </div>
      </div>
    </body>

    </html>
<?php
    $message = ob_get_clean();

    try {
      $sent = wp_mail($to, $subject, $message, $headers, $attachments);
    } catch (\Throwable $e) {
      error_log('Contact form wp_mail error: ' . $e->getMessage());
      $sent = false;
    }

    if (!$sent) {
       error_log('Contact form email failed to send to ' . $to . '. Check SMTP settings.');
       return new WP_REST_Response(['success' => false, 'message' => 'Fehler beim Senden der E-Mail. Bitte prüfen Sie die SMTP-Einstellungen oder versuchen Sie es später erneut.'], 500);
    }

    return new WP_REST_Response(['success' => true, 'message' => 'Vielen Dank! Ihre Anfrage wurde versendet.'], 200);
  }
}
