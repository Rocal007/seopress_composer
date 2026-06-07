<?php

namespace SeopressComposer\Settings;

/**
 * Location Selection Page
 * 
 * Re-implementation of the custom location selection options page 
 * from the development theme. Handles fetching locations from API,
 * displaying selection application, and saving to 'selected_bezirke' option.
 */
class LocationSelectionPage
{
  public function __construct()
  {
    add_action('admin_menu', [$this, 'add_menu_page'], 100); // Priority 100 to ensure parent exists
  }

  /**
   * Adds the options page to the WordPress admin menu.
   */
  public function add_menu_page()
  {
    add_submenu_page(
      'site-settings',   // Parent slug (from SiteSettings.php)
      'Einsatzgebiete',  // Page title
      'Einsatzgebiete',  // Menu title
      'manage_options',  // Capability
      'location-selection', // Menu slug
      [$this, 'render_page'] // Callback
    );
  }

  /**
   * Helper function to separate locations by their main district name.
   */
  private function separateByDistrict($data)
  {
    $districts = array();
    if (!is_array($data)) {
      return $districts;
    }
    foreach ($data as $location) {
      $districtName = $location['bezirksname'] ?? 'Unbekannt';
      $gemeindename = $location['gemeindename'] ?? '';
      $plz = $location['plz'] ?? '';

      if (!isset($districts[$districtName])) {
        $districts[$districtName] = array();
      }
      $districts[$districtName][$gemeindename] = $plz;
    }
    return $districts;
  }

  /**
   * Helper function to fetch location data from local text file.
   */
  private function get_local_geo_data()
  {
    $locations = [];
    $file_path = get_template_directory() . '/inc/Geo-data/AT.txt';
    
    if (file_exists($file_path)) {
      $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      foreach ($lines as $line) {
        $parts = explode("\t", $line);
        if (count($parts) >= 8) {
          $locations[] = [
            'bezirksname' => trim($parts[5]),
            'gemeindename' => trim($parts[7]),
            'plz' => trim($parts[1])
          ];
        }
      }
    }
    return $locations;
  }

  /**
   * Renders the content of the options page.
   */
  public function render_page()
  {
    if (!current_user_can('manage_options')) {
      return;
    }

    // Fetch locations from Local File
    $fetched_locations = $this->get_local_geo_data();
    $error_message = '';

    if (empty($fetched_locations)) {
      $error_message = 'Fehler beim Laden der Standorte aus Geo-data/AT.txt.';
    }

    if ($error_message) {
      echo '<div class="notice notice-error"><p>' . esc_html($error_message) . '</p></div>';
    }

    $districts = $this->separateByDistrict($fetched_locations);

    $fetched_districts_lookup = [];
    foreach ($fetched_locations as $location) {
      if (isset($location['gemeindename'])) {
        $fetched_districts_lookup[$location['gemeindename']] = $location['plz'] ?? '';
      }
    }

    $selected_bezirke_stored = get_option('selected_bezirke', []);
    if (!is_array($selected_bezirke_stored)) {
      $selected_bezirke_stored = [];
    }

    // Handle Form Submission
    if (isset($_POST['submit_locations_settings'])) {
      // Verify nonce
      if (!isset($_POST['bezirke_nonce']) || !wp_verify_nonce($_POST['bezirke_nonce'], 'save_bezirke_settings')) {
        wp_die('Sicherheitsüberprüfung fehlgeschlagen.');
      }

      $selected_bezirke_from_checkboxes = $_POST['selected_bezirke'] ?? [];

      $manual_bezirke_input = isset($_POST['manual_bezirke']) ? sanitize_textarea_field($_POST['manual_bezirke']) : '';
      $manual_bezirke_array = array_map('trim', explode("\n", $manual_bezirke_input));
      $manual_bezirke_array = array_filter($manual_bezirke_array);

      $all_selected_bezirke_to_store = [];

      // Process checkboxes: value is Bezirk Name, key is PLZ (or ID)
      // HTML: name="selected_bezirke[PLZ]" value="Name"
      if (is_array($selected_bezirke_from_checkboxes)) {
        foreach ($selected_bezirke_from_checkboxes as $plz_or_placeholder_key => $bezirk_name) {
          // Sanitize
          $safe_name = sanitize_text_field($bezirk_name);
          $safe_plz = sanitize_text_field($plz_or_placeholder_key);
          $all_selected_bezirke_to_store[$safe_name] = $safe_plz;
        }
      }

      // Process manual entries
      foreach ($manual_bezirke_array as $zeile) {
        if (strpos($zeile, '|') !== false) {
          list($bezirk_name, $plz) = array_map('trim', explode('|', $zeile, 2));
          if (!empty($bezirk_name) && !empty($plz)) {
            $all_selected_bezirke_to_store[$bezirk_name] = $plz;
          }
        }
      }

      update_option('selected_bezirke', $all_selected_bezirke_to_store);
      echo '<div class="notice notice-success is-dismissible"><p>Einstellungen gespeichert.</p></div>';
      $selected_bezirke_stored = $all_selected_bezirke_to_store;
    }

    // Handle Cleanup
    if (isset($_POST['cleanup_locations'])) {
      check_admin_referer('cleanup_locations_action', 'cleanup_locations_nonce');
      $this->cleanup_unselected_locations_pages();
    }

    // Add manually saved districts to the list (grouped under 'Manuell')
    foreach ($selected_bezirke_stored as $bezirk_name => $plz) {
      if (!isset($fetched_districts_lookup[$bezirk_name])) {
        $districts['Manuell'][$bezirk_name] = $plz;
      }
    }

    // Render Form
?>
    <div class="wrap">
      <h1>Einsatzgebiete auswählen</h1>
      <p>Wählen Sie die Gebiete aus, die unter "Einsatzgebiete" auf der Website angezeigt werden sollen.</p>

      <form method="post" action="">
        <?php wp_nonce_field('save_bezirke_settings', 'bezirke_nonce'); ?>

        <style>
          .district-column {
            width: 23%;
            margin-right: 2%;
            margin-bottom: 20px;
            background: #fff;
            padding: 15px;
            box-sizing: border-box;
            border: 1px solid #ccd0d4;
            box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
          }

          .district-container {
            display: flex;
            flex-wrap: wrap;
          }
        </style>

        <div class="district-container">
          <?php if (empty($districts)): ?>
            <p>Keine Standorte gefunden oder API nicht erreichbar.</p>
          <?php else: ?>
            <?php foreach ($districts as $stadt => $bezirke): ?>
              <div class="district-column">
                <h2 style="margin-top:0; border-bottom:1px solid #eee; padding-bottom:10px;"><?php echo esc_html($stadt); ?></h2>
                <label style="display:block; margin-bottom:10px;">
                  <input style="font-weight: bold" type="checkbox" class="select-all-checkbox"
                    data-target="<?php echo sanitize_title($stadt); ?>" /> <strong>Alle auswählen</strong>
                </label>

                <div style="max-height: 400px; overflow-y: auto;">
                  <?php
                  ksort($bezirke);
                  foreach ($bezirke as $bezirk_name => $plz):
                    $input_name_key = $plz;
                    $checked = array_key_exists($bezirk_name, $selected_bezirke_stored);
                  ?>
                    <label style="display:block; margin-bottom: 5px;">
                      <input type="checkbox" name="selected_bezirke[<?php echo esc_attr($input_name_key); ?>]"
                        value="<?php echo esc_attr($bezirk_name); ?>"
                        <?php checked($checked); ?> data-group="<?php echo sanitize_title($stadt); ?>" />
                      <?php echo esc_html($plz . ' - ' . $bezirk_name); ?>
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <hr>

        <h2>Manuelle Einträge hinzufügen</h2>
        <p>Geben Sie zusätzliche Bezirke im Format <code>Bezirk|PLZ</code> ein. Pro Zeile ein Eintrag, z.B.: <code>Altstadt|1010</code></p>
        <textarea name="manual_bezirke" rows="5" cols="50" style="width: 100%; max-width: 600px; font-family: monospace;"></textarea>
        <p class="description">Hinweis: Bereits gespeicherte manuelle Einträge erscheinen in der Liste oben unter "Manuell".</p>

        <?php submit_button('Einstellungen speichern', 'primary', 'submit_locations_settings'); ?>
      </form>

      <hr>

      <form method="post" action="">
        <?php wp_nonce_field('cleanup_locations_action', 'cleanup_locations_nonce'); ?>
        <h2>Einsatzgebiete bereinigen</h2>
        <p>Löscht alle Seiten (Unterseiten von Dienstleistungen), die zu Bezirken gehören, die oben <strong>nicht</strong> ausgewählt sind.</p>
        <p class="description" style="color: #b32d2e;">WARNUNG: Dies löscht Seiten unwiderruflich (ab in den Papierkorb).</p>
        <?php submit_button('Nicht ausgewählte Einsatzgebiete löschen', 'delete', 'cleanup_locations', false, array('onclick' => "return confirm('Sind Sie sicher? Dies löscht Seiten für nicht mehr gewählte Bezirke.');")); ?>
      </form>
    </div>

    <script>
      jQuery(document).ready(function($) {
        $('.select-all-checkbox').on('click', function() {
          var target = $(this).data('target');
          $('input[data-group="' + target + '"]').prop('checked', this.checked);
        });
      });
    </script>
<?php
  }

  /**
   * Cleans up pages for unselected locations.
   */
  private function cleanup_unselected_locations_pages()
  {
    $selected_locations = get_option('selected_bezirke', []);

    // Build valid suffixes: "PLZ Bezirk"
    $valid_location_suffixes = [];
    foreach ($selected_locations as $bezirk => $plz) {
      // Skip manual if format differs, but usually manual is handled same way
      if ($plz !== 'manual') {
        $valid_location_suffixes[] = $plz . ' ' . $bezirk;
      }
    }

    // Find all Service Parent Pages
    $parents = get_posts([
      'post_type' => 'page',
      'posts_per_page' => -1,
      'meta_key' => '_wp_page_template',
      'meta_value' => 'template-hauptseiten.php'
    ]);

    $deleted_count = 0;

    foreach ($parents as $parent) {
      $children = get_children([
        'post_parent' => $parent->ID,
        'post_type' => 'page',
        'numberposts' => -1
      ]);

      foreach ($children as $child) {
        // Check if child looks like a location page: "ParentTitle 1234 Name"
        // Regex matches "ParentTitle " + 4 digits + " " + anything
        $pattern = '/^' . preg_quote($parent->post_title, '/') . ' \d{4} .+$/';

        if (preg_match($pattern, $child->post_title)) {
          // It matches the pattern of a location page.
          // Check if it matches any VALID suffix.
          $is_valid = false;
          foreach ($valid_location_suffixes as $suffix) {
            if ($child->post_title === $parent->post_title . ' ' . $suffix) {
              $is_valid = true;
              break;
            }
          }

          if (!$is_valid) {
            // Not in the valid list -> Delete
            wp_trash_post($child->ID);
            $deleted_count++;
          }
        }
      }
    }

    echo '<div class="notice notice-info is-dismissible"><p>Bereinigung abgeschlossen: ' . $deleted_count . ' Seiten gelöscht.</p></div>';
  }
}
