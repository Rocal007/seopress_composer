<?php

namespace SeopressComposer\Settings;

/**
 * Service Selection & Page Generator Page
 * 
 * Handles fetching services from API, selecting them, and generating pages
 * based on the selection and the location settings.
 */
class ServiceSelectionPage
{
  public function __construct()
  {
    add_action('admin_menu', [$this, 'add_menu_page'], 101); // Priority 101 to ensure it appears after LocationSelectionPage or at least exists
  }

  /**
   * Adds the options page to the WordPress admin menu.
   */
  public function add_menu_page()
  {
    add_submenu_page(
      'site-settings',         // Parent slug
      'Dienstleistungen',      // Page title
      'Dienstleistungen',      // Menu title
      'manage_options',        // Capability
      'service-selection',     // Menu slug
      [$this, 'render_page']   // Callback
    );
  }

  /**
   * Renders the content of the options page.
   */
  public function render_page()
  {
    if (!current_user_can('manage_options')) {
      return;
    }

    // --- Fetch Remote Pages (Services) ---
    // --- Fetch Remote Pages (Services) ---
    // --- Fetch Remote Pages (Services) ---
    $fetch_result = $this->fetch_remote_pages();
    $remote_parent_pages = $fetch_result['data'];
    $error_message = $fetch_result['error'];

    // --- Fetch Locations from Local File ---
    $fetched_locations = $this->get_local_geo_data();
    if (empty($fetched_locations)) {
      $error_message .= ' <br>Fehler beim Laden der Standorte aus Geo-data/AT.txt.';
    }

    // Prepare Maps/Lookups
    $districts_grouped = $this->separateByDistrict($fetched_locations);
    $fetched_districts_lookup = [];
    $fetched_plz_lookup = [];

    foreach ($fetched_locations as $location) {
      if (isset($location['gemeindename'])) {
        $fetched_districts_lookup[$location['gemeindename']] = $location['plz'] ?? '';
        if (!empty($location['plz'])) {
          $fetched_plz_lookup[$location['plz']] = $location['gemeindename'];
        }
      }
    }

    if ($error_message) {
      echo '<div class="notice notice-error"><p>' . $error_message . '</p></div>'; // esc_html removed to allow <br>
    }

    // --- Handle Settings Save (Unified) ---
    $selected_dienstleistungen = get_option('selected_dienstleistungen', array());
    $selected_bezirke_stored = get_option('selected_bezirke', array());

    if (isset($_POST['submit_services_settings'])) {
      // 1. Save Services
      $neue_auswahl = isset($_POST['selected_dienstleistungen_options']) ? $_POST['selected_dienstleistungen_options'] : array();
      $sanitized_auswahl = [];
      foreach ($neue_auswahl as $key => $val) {
        $sanitized_auswahl[sanitize_text_field($key)] = sanitize_text_field($val);
      }
      update_option('selected_dienstleistungen', $sanitized_auswahl);
      $selected_dienstleistungen = $sanitized_auswahl;

      // 2. Save Locations
      $selected_bezirke_from_checkboxes = $_POST['selected_bezirke'] ?? [];
      $manual_bezirke_input = isset($_POST['manual_bezirke']) ? sanitize_textarea_field($_POST['manual_bezirke']) : '';

      $all_selected_bezirke_to_store = [];

      // Process checkboxes
      if (is_array($selected_bezirke_from_checkboxes)) {
        foreach ($selected_bezirke_from_checkboxes as $plz_or_placeholder_key => $bezirk_name) {
          $safe_name = sanitize_text_field($bezirk_name);
          $safe_plz = sanitize_text_field($plz_or_placeholder_key);
          $all_selected_bezirke_to_store[$safe_name] = $safe_plz;
        }
      }

      // Process manual
      $manual_lines = array_map('trim', explode("\n", $manual_bezirke_input));
      $manual_merged = 0;
      $manual_added = 0;

      foreach ($manual_lines as $zeile) {
        if (strpos($zeile, '|') !== false) {
          list($b_name, $b_plz) = array_map('trim', explode('|', $zeile, 2));
          if (!empty($b_name) && !empty($b_plz)) {

            $match_found = false;

            // Check Name Match
            if (isset($fetched_districts_lookup[$b_name])) {
              $all_selected_bezirke_to_store[$b_name] = $b_plz;
              $manual_merged++;
              $match_found = true;
            }
            // Check PLZ Match (Smart Merge)
            elseif (isset($fetched_plz_lookup[$b_plz])) {
              $canonical_name = $fetched_plz_lookup[$b_plz];
              $all_selected_bezirke_to_store[$canonical_name] = $b_plz;
              $manual_merged++;
              $match_found = true;
            }

            if (!$match_found) {
              $all_selected_bezirke_to_store[$b_name] = $b_plz;
              $manual_added++;
            }
          }
        }
      }

      update_option('selected_bezirke', $all_selected_bezirke_to_store);
      $selected_bezirke_stored = $all_selected_bezirke_to_store;

      $msg = 'Einstellungen gespeichert.';
      if ($manual_merged > 0 || $manual_added > 0) {
        $msg .= " Manuelle Einträge: $manual_added neu angelegt, $manual_merged existierenden Orten in der Liste zugeordnet.";
      }

      echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($msg) . '</p></div>';
    }

    // Add manual entries to view list
    foreach ($selected_bezirke_stored as $bezirk_name => $plz) {
      if (!isset($fetched_districts_lookup[$bezirk_name])) {
        $districts_grouped['Manuell'][$bezirk_name] = $plz;
      }
    }


    // --- Handle Cleanup (Delete Unselected Services) ---
    if (isset($_POST['delete_unselected'])) {
      check_admin_referer('generate_pages_action', 'generate_pages_nonce');
      $this->delete_unselected_pages();
    }

    // --- Handle Cleanup (Delete Unselected Locations) ---
    if (isset($_POST['cleanup_locations'])) {
      check_admin_referer('generate_pages_action', 'generate_pages_nonce');
      $this->cleanup_unselected_locations_pages();
    }

    // --- Handle Page Generation ---
    if (isset($_POST['generate_pages'])) {
      check_admin_referer('generate_pages_action', 'generate_pages_nonce');
      $this->run_page_generation();
    }

    // --- Render View ---
?>
    <div class="wrap">
      <h1>Dienstleistungen & Page Generator</h1>

      <form method="post" action="">
        <h2>1. Dienstleistungen auswählen</h2>
        <p>Wähle die Hauptseiten aus, für die Inhalte generiert werden sollen.</p>

        <!-- UI Styles -->
        <style>
          .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
          }

          .service-card {
            background: #fff;
            border: 1px solid #ccd0d4;
            box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
            border-radius: 4px;
            overflow: hidden;
          }

          .service-card-header {
            background: #f3f4f5;
            padding: 10px 15px;
            border-bottom: 1px solid #ccd0d4;
            display: flex;
            justify-content: space-between;
            align-items: center;
          }

          .service-card-header h3 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            color: #50575e;
          }

          .service-list {
            padding: 0;
            margin: 0;
            max-height: 400px;
            overflow-y: auto;
          }

          .service-item {
            padding: 8px 15px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background 0.2s;
          }

          .service-item:last-child {
            border-bottom: none;
          }

          .service-item:hover {
            background: #fbfbfb;
          }

          .service-label {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            cursor: pointer;
            font-size: 13px;
          }

          .badge {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 500;
            text-transform: uppercase;
          }

          .badge-service {
            background: #e5f6fd;
            color: #0073aa;
            border: 1px solid #bce1f5;
          }

          .badge-static {
            background: #f6f7f7;
            color: #50575e;
            border: 1px solid #dcdcde;
          }

          .template-info {
            font-size: 10px;
            color: #888;
            font-style: italic;
            margin-left: auto;
          }
        </style>

        <script>
          function toggleCategory(checkbox) {
            const card = checkbox.closest('.service-card');
            const inputs = card.querySelectorAll('.service-list input[type="checkbox"]');
            inputs.forEach(input => {
                input.checked = checkbox.checked;
                syncCheckboxes(input);
            });
          }

          function syncCheckboxes(changedInput) {
            const name = changedInput.getAttribute('name');
            if (name && name.includes('selected_dienstleistungen_options')) {
                const duplicates = document.querySelectorAll('input[name="' + name + '"]');
                duplicates.forEach(dup => dup.checked = changedInput.checked);
            }
          }

          document.addEventListener('change', function(e) {
              if (e.target && e.target.type === 'checkbox' && !e.target.closest('.service-card-header')) {
                  syncCheckboxes(e.target);
              }
          });
        </script>

        <div style="margin-bottom: 20px;">
          <?php if (!empty($remote_parent_pages)): ?>
            <?php
            // Group services by parent_name (replaces old categories array)
            $grouped_pages = [];

            if (is_object($remote_parent_pages)) {
                // 1. Services — grouped by parent_name
                if (!empty($remote_parent_pages->services)) {
                    foreach ($remote_parent_pages->services as $svc) {
                        $group = !empty($svc->parent_name) ? $svc->parent_name : 'Ohne Gruppe';
                        $grouped_pages[$group][] = $svc;
                    }
                }

                // 2. Statische Seiten
                if (!empty($remote_parent_pages->static_pages)) {
                    foreach ((array)$remote_parent_pages->static_pages as $static_page) {
                        $static_page->is_static = true;
                        $grouped_pages['Statische Seiten'][] = $static_page;
                    }
                }
            }

            ksort($grouped_pages);

            // Statische Seiten ans Ende
            if (isset($grouped_pages['Statische Seiten'])) {
                $s = $grouped_pages['Statische Seiten'];
                unset($grouped_pages['Statische Seiten']);
                $grouped_pages['Statische Seiten'] = $s;
            }
            if (isset($grouped_pages['Ohne Gruppe'])) {
                $o = $grouped_pages['Ohne Gruppe'];
                unset($grouped_pages['Ohne Gruppe']);
                $grouped_pages['Ohne Gruppe'] = $o;
            }
            ?>

            <div class="services-grid">
              <?php foreach ($grouped_pages as $category => $pages): ?>
                <details class="service-card" open>
                  <summary class="service-card-header" style="cursor: pointer;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                      <span class="dashicons dashicons-arrow-right-alt2" style="font-size: 16px; width: 16px; height: 16px; transition: transform 0.2s;"></span>
                      <h3><?php echo esc_html($category); ?></h3>
                    </div>
                    <label title="Alle auswählen" onclick="event.stopPropagation();">
                      <input type="checkbox" onchange="toggleCategory(this)">
                    </label>
                  </summary>
                  <div class="service-list">
                    <?php foreach ($pages as $page):
                      $title = $page->title ?? '';
                      if (is_object($title)) {
                        $title = $title->rendered ?? '';
                      } elseif (is_array($title)) {
                        $title = $title['rendered'] ?? '';
                      }
                      if (!is_string($title) && !is_numeric($title)) {
                        $title = '';
                      }

                      $endpoint = $page->api_endpoint ?? '';
                      if (empty($endpoint) && !empty($page->_links->self[0]->href)) {
                          $endpoint = $page->_links->self[0]->href;
                      }
                      
                      $template = $page->template ?? '';
                      $is_main_service = ($template === 'template-hauptseiten' || $template === 'template-hauptseiten.php');
                      $is_category = !empty($page->is_category);
                      $is_static = !empty($page->is_static);

                      $checked = isset($selected_dienstleistungen[$endpoint]) ? 'checked' : '';
                      
                      // Visuelle Hierarchie
                      $item_style = '';
                      if ($is_category) {
                          $item_style = 'font-weight: bold; background-color: #f6f7f7; border-bottom: 2px solid #e2e4e7;';
                      } elseif (!$is_static) {
                          // Service pages indented slightly
                          $item_style = 'padding-left: 30px; border-left: 2px solid #0073aa;';
                      }
                    ?>
                      <div class="service-item" style="<?php echo $item_style; ?>">
                        <label class="service-label">
                          <input type="checkbox"
                            name="selected_dienstleistungen_options[<?php echo esc_attr($endpoint); ?>]"
                            value="<?php echo esc_attr($title); ?>"
                            <?php echo $checked; ?> />
                          <span><?php echo esc_html($title); ?></span>
                        </label>

                        <!-- Badge Info -->
                        <?php if ($is_category): ?>
                          <span class="badge badge-static" title="Kategorie">Kategorie-Seite</span>
                        <?php elseif ($is_static): ?>
                          <span class="badge badge-static" title="Statische Seite">Statisch</span>
                        <?php elseif ($is_main_service): ?>
                          <span class="badge badge-service" title="Bereichsseite mit Unterseiten">Service</span>
                        <?php else: ?>
                          <span class="badge badge-static" title="Einzelseite">Seite</span>
                        <?php endif; ?>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </details>
              <?php endforeach; ?>
            </div>

          <?php else: ?>
            <div class="notice notice-warning inline">
              <p>Keine Seiten gefunden. Bitte überprüfen Sie die API Verbindung.</p>
            </div>
          <?php endif; ?>
        </div>

        <hr style="margin: 30px 0;">

        <h2>2. Einsatzgebiete auswählen</h2>
        <p>Wählen Sie die Bezirke/Orte aus, für die Unterseiten generiert werden sollen.</p>

        <div class="services-grid" style="margin-bottom: 20px;">
          <?php if (empty($districts_grouped)): ?>
            <p>Keine Standorte gefunden.</p>
          <?php else: ?>
            <?php foreach ($districts_grouped as $stadt => $bezirke): ?>
              <details class="service-card">
                <summary class="service-card-header" style="cursor: pointer;">
                  <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="dashicons dashicons-arrow-right-alt2" style="font-size: 16px; width: 16px; height: 16px; transition: transform 0.2s;"></span>
                    <h3><?php echo esc_html($stadt); ?></h3>
                  </div>
                  <label title="Alle auswählen" onclick="event.stopPropagation();">
                    <input type="checkbox" onchange="toggleCategory(this)">
                  </label>
                </summary>
                <div class="service-list">
                  <?php
                  ksort($bezirke);
                  foreach ($bezirke as $bezirk_name => $plz):
                    $input_key = $plz;
                    $checked_loc = array_key_exists($bezirk_name, $selected_bezirke_stored) ? 'checked' : '';
                  ?>
                    <div class="service-item">
                      <label class="service-label">
                        <input type="checkbox"
                          name="selected_bezirke[<?php echo esc_attr($input_key); ?>]"
                          value="<?php echo esc_attr($bezirk_name); ?>"
                          <?php echo $checked_loc; ?> />
                        <span><?php echo esc_html($plz . ' ' . $bezirk_name); ?></span>
                      </label>
                    </div>
                  <?php endforeach; ?>
                </div>
              </details>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <h3>Manuelle Einträge</h3>
        <p>Geben Sie zusätzliche Bezirke im Format <code>Bezirk|PLZ</code> ein. Pro Zeile ein Eintrag (z.B. <code>Altstadt|1010</code>).</p>
        <p class="description">Hinweis: Einträge, die bereits in der Liste oben existieren, werden dort automatisch angehakt und erscheinen nicht doppelt hier.</p>
        <textarea name="manual_bezirke" rows="3" class="large-text code" style="margin-bottom: 20px;"></textarea>

        <?php submit_button('Gesamte Auswahl speichern', 'primary', 'submit_services_settings'); ?>
      </form>

      <hr>



      <form method="post" action="">
        <?php wp_nonce_field('generate_pages_action', 'generate_pages_nonce'); ?>
        <h2>3. Seiten generieren</h2>
        <p>Generiert Hauptseiten (Dienstleistungen) und Unterseiten (Orte) basierend auf der Auswahl oben.</p>
        <p class="description">ACHTUNG: Dies kann viele Seiten erstellen. Existierende Seiten werden übersprungen.</p>

        <?php submit_button('Seiten generieren starten', 'secondary', 'generate_pages'); ?>
      </form>

      <hr>

      <form method="post" action="">
        <?php wp_nonce_field('generate_pages_action', 'generate_pages_nonce'); ?>
        <h2>4. Seiten & Orte bereinigen</h2>
        <p>Löscht Seiten, die nicht mehr den aktuellen Einstellungen entsprechen.</p>

        <p><strong>A. Dienstleistungen bereinigen:</strong> Löscht Hauptseiten, die oben nicht angehakt sind.</p>
        <?php submit_button('Nicht ausgewählte Dienstleistungen löschen', 'delete', 'delete_unselected', false, array('onclick' => "return confirm('Warnung: Löscht nicht-gewählte Hauptseiten inkl. Unterseiten.');")); ?>

        <br><br>

        <p><strong>B. Orte bereinigen:</strong> Löscht Unterseiten von Bezirken, die oben nicht mehr ausgewählt sind.</p>
        <?php submit_button('Nicht ausgewählte Orts-Seiten löschen', 'delete', 'cleanup_locations', false, array('onclick' => "return confirm('Warnung: Löscht Unterseiten für nicht-gewählte Bezirke.');")); ?>

      </form>
    </div>
<?php
  }

  private function fetch_remote_pages()
  {
    $settings = new SiteSettings();
    $base_url = $settings->get_option('basicremote', 'https://www.1a-antiquitaeten.at/api');
    $base_url = untrailingslashit($base_url);

    $parsed_url = parse_url($base_url);
    $api_domain = ($parsed_url['scheme'] ?? 'https') . '://' . ($parsed_url['host'] ?? '');
    $api_path = $parsed_url['path'] ?? '';
    $api_base_path = explode('/wp-json', $api_path)[0] ?? '';

    $site_structure_url = $api_domain . $api_base_path . '/wp-json/custom/v1/site-structure';

    $response = wp_remote_get($site_structure_url);
    $error_message = '';

    if (is_wp_error($response)) {
      $error_message = 'Fehler beim Abrufen der Site-Structure: ' . $response->get_error_message();
    } else {
      $body = wp_remote_retrieve_body($response);
      $data = json_decode($body);

      // Check for API errors (e.g. 404)
      if (isset($data->code) && ($data->code === 'rest_no_route' || ($data->data->status ?? 0) === 404)) {
        $error_message = 'API Fehler: Endpoint nicht gefunden (404). Bitte das Remote-Theme aktualisieren.';
      } elseif (!is_object($data) && empty($data)) {
        $error_message = 'Keine Struktur-Daten gefunden.';
      }

      if (empty($error_message) && is_object($data)) {
         return ['data' => $data, 'error' => ''];
      }
    }
    return ['data' => null, 'error' => $error_message];
  }

  /**
   * Executes the page generation logic.
   */
  private function run_page_generation()
  {
    echo '<div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; margin-top: 20px; max-height: 400px; overflow-y: auto;">';
    echo '<h3>Generierungsprotokoll</h3>';

    $dienstleistungen = get_option('selected_dienstleistungen', []);
    $locations        = get_option('selected_bezirke', []);

    if (empty($dienstleistungen)) { echo '<p>Keine Dienstleistungen ausgewählt.</p></div>'; return; }
    
    // Fallback: If no locations are selected, use 'einsatzgebiet' from Site Settings
    if (empty($locations)) {
      $einsatzgebiet = get_field('einsatzgebiet', 'option') ?: '';
      if (!empty($einsatzgebiet)) {
        $locations = [ $einsatzgebiet => 'manual' ];
        echo '<p>Hinweis: Keine Standorte gewählt. Verwende primäres Einsatzgebiet: ' . esc_html($einsatzgebiet) . '</p>';
      } else {
        echo '<p>Keine Einsatzgebiete ausgewählt und kein primäres Einsatzgebiet gefunden.</p></div>';
        return;
      }
    }

    // --- Fetch Site Structure ---
    $fetch_result   = $this->fetch_remote_pages();
    $site_structure = $fetch_result['data'];

    // Build lookup maps from site-structure
    $template_map         = []; // endpoint => template
    $parent_slug_map      = []; // endpoint => parent_slug
    $page_name_to_url_map = []; // title    => endpoint
    $page_slug_to_url_map = []; // slug     => endpoint

    if (!empty($site_structure) && is_object($site_structure)) {
      if (!empty($site_structure->services)) {
        foreach ($site_structure->services as $svc) {
          $page_name_to_url_map[$svc->title] = $svc->api_endpoint;
          $page_slug_to_url_map[$svc->slug]  = $svc->api_endpoint;
          if (!empty($svc->template))      $template_map[$svc->api_endpoint]    = $svc->template;
          if (!empty($svc->parent_slug))   $parent_slug_map[$svc->api_endpoint] = $svc->parent_slug;
        }
      }
      if (!empty($site_structure->static_pages)) {
        foreach ((array) $site_structure->static_pages as $static_slug => $page_data) {
          $page_name_to_url_map[$page_data->title] = $page_data->api_endpoint;
          $page_slug_to_url_map[$static_slug]       = $page_data->api_endpoint;
        }
      }
    }

    $locations_with_plz = array_filter($locations, fn($plz) => $plz !== 'manual');

    // Pre-compute: first endpoint per parent_slug (used as remote_page for group pages)
    $parent_slug_endpoint = []; // parent_slug => first service endpoint
    foreach ($dienstleistungen as $endpoint => $title) {
      $p_slug = $parent_slug_map[$endpoint] ?? '';
      if (!empty($p_slug) && !isset($parent_slug_endpoint[$p_slug])) {
        $parent_slug_endpoint[$p_slug] = $endpoint; // first endpoint for this group
      }
    }

    // --- PHASE 1: Ensure parent group pages exist ---
    $created_parent_ids = []; // parent_slug => WP page ID

    foreach ($dienstleistungen as $endpoint => $title) {
      $p_slug = $parent_slug_map[$endpoint] ?? '';
      if (empty($p_slug) || isset($created_parent_ids[$p_slug])) continue;

      // Remote URL for the group page = first service in this group
      $group_remote_url = $parent_slug_endpoint[$p_slug] ?? '';

      $existing = get_page_by_path($p_slug);
      if ($existing) {
        $created_parent_ids[$p_slug] = $existing->ID;
        echo 'Gruppe "' . esc_html($p_slug) . '" existiert bereits. ID: ' . $existing->ID . '<br>';

        // Ensure remote_page is set on existing parent pages
        if ($group_remote_url) {
          $existing_remote = (string) get_field('remote_page', $existing->ID);
          if (empty($existing_remote)) {
            update_field('remote_page', $group_remote_url, $existing->ID);
            echo '&nbsp;&nbsp;&nbsp;&nbsp;&#8594; remote_page gesetzt: ' . esc_html($group_remote_url) . '<br>';
          }
        }
      } else {
        // Derive readable title from term name or first matching service title
        $p_title_raw = '';
        foreach ($dienstleistungen as $ep => $t) {
          if (($parent_slug_map[$ep] ?? '') === $p_slug) { $p_title_raw = $t; break; }
        }
        $p_title = !empty($p_title_raw) ? $p_title_raw : ucfirst(str_replace('-', ' ', $p_slug));

        $p_id = $this->create_new_page($p_title, '', 0, 'template-hauptseiten.php', $group_remote_url);
        if (!is_wp_error($p_id)) {
          $created_parent_ids[$p_slug] = $p_id;
          echo 'Gruppenseite <strong>"' . esc_html($p_title) . '"</strong> erstellt (remote: ' . esc_html(basename($group_remote_url)) . '). ID: ' . $p_id . '<br>';
        }
      }
    }

    // Create a lookup for static endpoints so we can skip them in Phase 2
    $static_endpoints = [];
    if (!empty($site_structure->static_pages)) {
      foreach ((array) $site_structure->static_pages as $page_data) {
        $static_endpoints[] = $page_data->api_endpoint;
      }
    }

    // --- PHASE 2: Create service hauptseiten as direct children of their parent ---
    foreach ($dienstleistungen as $endpoint => $title) {
      // Skip static pages explicitly, they are handled in generate_static_pages
      if (in_array($endpoint, $static_endpoints)) {
        continue;
      }

      $current_template = $template_map[$endpoint] ?? 'template-hauptseiten.php';
      if ($current_template === 'default' || empty($current_template)) {
        $current_template = 'template-hauptseiten.php';
      }

      $is_service_template = str_contains($current_template, 'template-hauptseiten');
      if (!$is_service_template) continue; // Static pages handled separately

      $p_slug       = $parent_slug_map[$endpoint] ?? '';
      $wp_parent_id = $p_slug ? ($created_parent_ids[$p_slug] ?? 0) : 0;
      $service_slug = sanitize_title($title);
      $full_path    = $wp_parent_id ? (get_post_field('post_name', $wp_parent_id) . '/' . $service_slug) : $service_slug;

      $existing_service = get_page_by_path($full_path);
      if ($existing_service) {
        $service_page_id = $existing_service->ID;
        echo 'Seite "' . esc_html($title) . '" existiert. ID: ' . $service_page_id . '<br>';
      } else {
        $service_page_id = $this->create_new_page($title, '', $wp_parent_id, $current_template, $endpoint);
        if (is_wp_error($service_page_id)) {
          echo 'Fehler "' . esc_html($title) . '": ' . $service_page_id->get_error_message() . '<br>';
          continue;
        }
        echo 'Seite <strong>"' . esc_html($title) . '"</strong> erstellt unter /' . esc_html($full_path) . '. ID: ' . $service_page_id . '<br>';
      }

      // --- PHASE 3: Create location child pages under each service ---
      foreach ($locations_with_plz as $bezirk => $plz) {
        $child_title = $title . ' ' . $plz . ' ' . $bezirk;
        $child_slug  = sanitize_title($child_title);
        $child_path  = $full_path . '/' . $child_slug;

        $existing_child = get_page_by_path($child_path);
        if ($existing_child) {
          echo '&nbsp;&nbsp;&nbsp;&nbsp;&#10003; "' . esc_html($bezirk) . '" existiert.<br>';
          
          // Ensure it has the district term assigned even if it exists
          $term_id = $this->create_or_get_term($bezirk, 'district');
          if ($term_id) {
            wp_set_post_terms($existing_child->ID, [(int)$term_id], 'district', false);
          }
          
          continue;
        }

        // Verwende 'default' anstelle von 'template-hauptseiten.php' für die Kindseiten
        $child_id = $this->create_new_page($child_title, '', $service_page_id, 'default', '');
        if (is_wp_error($child_id)) {
          echo 'Fehler Kindseite "' . esc_html($bezirk) . '": ' . $child_id->get_error_message() . '<br>';
        } else {
          echo '&nbsp;&nbsp;&nbsp;&nbsp;+ Kindseite "' . esc_html($child_title) . '" erstellt. ID: ' . $child_id . '<br>';
          
          // Assign to district taxonomy
          $term_id = $this->create_or_get_term($bezirk, 'district');
          if ($term_id) {
            wp_set_post_terms($child_id, [(int)$term_id], 'district', false);
          }
        }
      }
    }

    // Static Pages
    $this->generate_static_pages($page_name_to_url_map, $page_slug_to_url_map);

    echo '<br><strong>Vorgang abgeschlossen.</strong></div>';
  }

  private function generate_static_pages($page_name_to_url_map = [], $page_slug_to_url_map = [])
  {
    $static_pages = array(
      'FAQ' => 'template-faq.php',
      'Tipps' => 'template-tipps.php',
      'Ratgeber' => 'template-ratgeber.php',
      'Kosten' => 'template-kosten.php',
      'Startseite' => 'template-startseite.php',
      'Kontakt' => 'template-kontakt.php',
      'Impressum' => 'template-impressum.php',
      'Datenschutz' => 'template-datenschutz-agb.php',
      'Über uns' => 'template-ueber-uns.php',
      'Videos' => 'template-videos.php',
    );

    echo '<h4>Statische Seiten prüfen...</h4>';

    foreach ($static_pages as $title => $tpl) {
      $slug = sanitize_title($title);
      $existing = get_page_by_path($slug);

      $remote_url = '';
      if (isset($page_name_to_url_map[$title])) {
        $remote_url = $page_name_to_url_map[$title];
      } elseif (isset($page_slug_to_url_map[$slug])) {
        $remote_url = $page_slug_to_url_map[$slug];
      }

      if ($existing) {
        if ($remote_url) {
          update_field('remote_page', $remote_url, $existing->ID);
        }
        continue;
      }

      $page_id = $this->create_new_page($title, '', 0, $tpl, $remote_url);

      if (!is_wp_error($page_id)) {
        echo 'Statische Seite "' . esc_html($title) . '" erstellt. ID: ' . $page_id . '<br>';

        if ($title === 'Startseite') {
          update_option('show_on_front', 'page');
          update_option('page_on_front', $page_id);
        }
      } else {
        echo 'Fehler bei statischer Seite "' . esc_html($title) . '": ' . $page_id->get_error_message() . '<br>';
      }
    }
  }

  private function create_new_page($page_title, $content = '', $parent_id = 0, $template = '', $remote_page = '', $seo_data = [])
  {
    $page_data = array(
      'post_title'   => $page_title,
      'post_content' => $content,
      'post_status'  => 'publish',
      'post_type'    => 'page',
      'post_parent'  => $parent_id,
    );

    $page_id = wp_insert_post($page_data);

    if (!is_wp_error($page_id)) {
      if ($template) {
        update_post_meta($page_id, '_wp_page_template', $template);
      }

      if ($remote_page) {
        update_field('remote_page', $remote_page, $page_id);
      }

      // Yoast SEO Fields
      if (!empty($seo_data['title'])) {
        update_post_meta($page_id, '_yoast_wpseo_title', $seo_data['title']);
      }
      if (!empty($seo_data['desc'])) {
        update_post_meta($page_id, '_yoast_wpseo_metadesc', $seo_data['desc']);
      }

      // Mark as generated by this tool
      update_post_meta($page_id, '_seopress_generated', '1');
    }

    return $page_id;
  }

  private function create_district_category($name)
  {
    $existing = get_term_by('name', $name, 'category');

    if ($existing) {
      return $existing->term_id;
    }

    $new_term = wp_insert_term(
      $name,
      'category',
      array('slug' => sanitize_title($name))
    );

    if (is_wp_error($new_term)) {
      echo 'Kategoriefehler: ' . $new_term->get_error_message() . '<br>';
      return 0;
    }

    return $new_term['term_id'];
  }

  /**
   * Create or get a term in a custom taxonomy
   */
  private function create_or_get_term($name, $taxonomy)
  {
    $existing = get_term_by('name', $name, $taxonomy);

    if ($existing) {
      return $existing->term_id;
    }

    $new_term = wp_insert_term(
      $name,
      $taxonomy,
      array('slug' => sanitize_title($name))
    );

    if (is_wp_error($new_term)) {
      echo 'Taxonomy-Fehler (' . $taxonomy . '): ' . $new_term->get_error_message() . '<br>';
      return 0;
    }

    return $new_term['term_id'];
  }

  /**
   * Deletes (trashes) pages that are no longer selected.
   */
  private function delete_unselected_pages()
  {
    $selected_services_titles = array_values(get_option('selected_dienstleistungen', []));
    $selected_locations = get_option('selected_bezirke', []);

    // Prepare expected suffixes "PLZ Bezirk"
    $valid_location_suffixes = [];
    foreach ($selected_locations as $bezirk => $plz) {
      if ($plz !== 'manual') {
        $valid_location_suffixes[] = $plz . ' ' . $bezirk;
      }
    }

    // Find Service Parent Pages (identified by template)
    $parents = get_posts([
      'post_type' => 'page',
      'posts_per_page' => -1,
      'meta_key' => '_wp_page_template',
      'meta_value' => 'template-hauptseiten.php'
    ]);

    $deleted_parents = 0;
    $deleted_children = 0;

    foreach ($parents as $parent) {
      // 1. Check Parent
      if (!in_array($parent->post_title, $selected_services_titles)) {
        // Parent not selected -> Trash it (and its children)
        wp_trash_post($parent->ID);
        $deleted_parents++;
        continue; // Next parent
      }

      // 2. Check Children (if Parent is kept)
      $children = get_children([
        'post_parent' => $parent->ID,
        'post_type' => 'page',
        'numberposts' => -1
      ]);

      foreach ($children as $child) {
        // Improvements:
        // 1. Only target child pages that look like generated location pages (Title ends with "PLZ Name")
        //    Regex: / \d{4} .+$/ (Space, 4 digits, space, any text)
        // 2. If it looks like a location page, check if it's in our valid list.

        // Check if child matches the generated pattern (ParentTitle + " " + Digits...)
        // Actually, safer to check if it Ends with any valid suffix? 
        // Or if it matches the general pattern "ParentTitle \d{4} .+"

        $is_generated_location_page = preg_match('/^' . preg_quote($parent->post_title, '/') . ' \d{4} .+$/', $child->post_title);

        if ($is_generated_location_page) {
          // It looks like a location page. Is it a VALID one?
          $is_valid_child = false;
          foreach ($valid_location_suffixes as $suffix) {
            // Strict title match check: "ParentTitle PLZ Bezirk"
            $expected_title = $parent->post_title . ' ' . $suffix;
            if ($child->post_title === $expected_title) {
              $is_valid_child = true;
              break;
            }
          }

          if (!$is_valid_child) {
            // It's a location page (by pattern) but NOT in our valid list -> Trash it
            wp_trash_post($child->ID);
            $deleted_children++;
          }
        }
        // Else: It's a child page but doesn't look like a generated location page. Keep it (e.g. manual subpages).
      }
    } // Closes foreach ($parents as $parent)

    echo '<div class="notice notice-info is-dismissible"><p>Bereinigung: ' . $deleted_parents . ' Hauptseiten und ' . $deleted_children . ' Unterseiten gelöscht.</p></div>';
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
   * Cleans up pages for unselected locations.
   */
  private function cleanup_unselected_locations_pages()
  {
    $selected_locations = get_option('selected_bezirke', []);

    // Build valid suffixes: "PLZ Bezirk"
    $valid_location_suffixes = [];
    foreach ($selected_locations as $bezirk => $plz) {
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

    // --- Cleanup Categories (Terms) ---
    // Fetch known locations from local file to identify which categories are "Districts"
    $loc_data = $this->get_local_geo_data();
    
    if (!empty($loc_data)) {
      $known_district_names = [];
      foreach ($loc_data as $loc) {
        if (!empty($loc['gemeindename'])) $known_district_names[] = $loc['gemeindename'];
      }

      $valid_district_names = array_keys($selected_locations); // The names user wants to KEEP.

      $all_terms = get_terms(['taxonomy' => 'category', 'hide_empty' => false]);
      $deleted_terms = 0;

      if (!is_wp_error($all_terms)) {
        foreach ($all_terms as $term) {
          // Check if this term is a "District Category" (i.e., it is in the API list)
          if (in_array($term->name, $known_district_names)) {
            // It IS a district category. Should we keep it?
            if (!in_array($term->name, $valid_district_names)) {
              // Not selected -> Delete
              wp_delete_term($term->term_id, 'category');
              $deleted_terms++;
            }
          }
        }
      }
      
      if ($deleted_terms > 0) {
        echo '<div class="notice notice-info is-dismissible"><p>Kategorien bereinigt: ' . $deleted_terms . ' nicht verwendete Bezirks-Kategorien gelöscht.</p></div>';
      }
    }

    echo '<div class="notice notice-info is-dismissible"><p>Bereinigung abgeschlossen: ' . $deleted_count . ' Seiten gelöscht.</p></div>';
  }
} // End Class
