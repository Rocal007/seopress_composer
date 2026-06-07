<?php

namespace SeopressComposer\Settings;

class SiteSettings
{
  /**
   * Centralized Option Caching
   */
  private array $options_cache = [];

  /**
   * Handle cache flush action from Site Settings
   */
  public function handle_cache_flush()
  {
    if (!is_admin()) {
      return;
    }
    if (isset($_GET['page']) && $_GET['page'] === 'site-settings' && isset($_GET['flush_site_cache']) && $_GET['flush_site_cache'] == 1) {
      if (!current_user_can('edit_theme_options')) {
        return;
      }

      // Clear Helper/Model caches stored in transients if any
      // In this theme, we use transients for remote menus and some data.
      // We also use wp_cache_flush() to be sure.

      global $wpdb;

      // Clear all transients
      $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE ('_transient_%')");
      $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE ('_site_transient_%')");

      // Flush Object Cache
      wp_cache_flush();

      add_action('admin_notices', function () {
        echo '<div class="notice notice-success is-dismissible"><p><strong>Success:</strong> Object Cache and Transients have been flushed.</p></div>';
      });
    }
  }

  /**
   * Handle 'Delete All Pages' action
   */
  /**
   * AJAX Handler: Delete All Pages
   */
  public function ajax_delete_all_pages()
  {
    check_ajax_referer('seopress_site_settings_action', 'nonce');

    if (!current_user_can('edit_theme_options')) {
      wp_send_json_error('Permission denied');
    }

    global $wpdb;

    // Fast deletion using direct SQL to avoid timeouts on large sites
    $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'page'");

    if ($count > 0) {
      // 1. Delete Post Meta
      $wpdb->query("DELETE pm FROM $wpdb->postmeta pm INNER JOIN $wpdb->posts p ON pm.post_id = p.ID WHERE p.post_type = 'page'");
      
      // 2. Delete Term Relationships
      $wpdb->query("DELETE tr FROM $wpdb->term_relationships tr INNER JOIN $wpdb->posts p ON tr.object_id = p.ID WHERE p.post_type = 'page'");
      
      // 3. Delete Posts
      $wpdb->query("DELETE FROM $wpdb->posts WHERE post_type = 'page'");
    }

    wp_send_json_success(['message' => "$count Seiten wurden gelöscht."]);
  }

  /**
   * AJAX Handler: Delete All Categories
   */
  public function ajax_delete_all_categories()
  {
    check_ajax_referer('seopress_site_settings_action', 'nonce');

    if (!current_user_can('edit_theme_options')) {
      wp_send_json_error('Permission denied');
    }

    // 1. Delete Service Categories & Districts & Menu Categories
    $taxonomies = ['service_category', 'district', 'menu_category'];
    $count = 0;

    foreach ($taxonomies as $taxonomy) {
      if (!taxonomy_exists($taxonomy)) continue;
      $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);
      if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
          wp_delete_term($term->term_id, $taxonomy);
          $count++;
        }
      }
    }

    // 2. Delete Standard Categories (except Uncategorized)
    $cats = get_terms(['taxonomy' => 'category', 'hide_empty' => false]);
    if (!is_wp_error($cats)) {
      foreach ($cats as $cat) {
        if ($cat->term_id == 1 || $cat->slug == 'uncategorized') continue;
        wp_delete_term($cat->term_id, 'category');
        $count++;
      }
    }

    wp_send_json_success(['message' => "$count Kategorien & Terme wurden gelöscht (inkl. Services/Bezirke)."]);
  }

  public function __construct()
  {
    // AJAX Hooks
    add_action('wp_ajax_seopress_delete_all_pages', [$this, 'ajax_delete_all_pages']);
    add_action('wp_ajax_seopress_delete_all_categories', [$this, 'ajax_delete_all_categories']);

    // Admin UI
    add_action('admin_footer', [$this, 'render_admin_scripts']);

    // Register Settings Page — ACF or native fallback
    if (class_exists('ACF')) {
      add_action('acf/init', [$this, 'register_options_page']);
      add_action('acf/init', [$this, 'register_field_group']);
    } else {
      // Native WordPress admin page when ACF is not installed
      add_action('admin_menu', [$this, 'register_native_options_page']);
      add_action('admin_init', [$this, 'register_native_settings']);
    }

    add_action('admin_bar_menu', [$this, 'add_site_settings_to_admin_bar'], 999);
    add_action('admin_init', [$this, 'handle_cache_flush']);
  }

  /**
   * Render Admin Scripts for Button Handling
   */
  public function render_admin_scripts()
  {
    $screen = get_current_screen();
    if (!$screen || strpos($screen->id, 'site-settings') === false) return;
?>
    <script type="text/javascript">
      jQuery(document).ready(function($) {
        function runDeleteAction(action, button) {
          if (!confirm('Sind Sie sicher? Dies kann nicht rückgängig gemacht werden.')) return;

          var $btn = $(button);
          var originalText = $btn.text();
          $btn.text('Verarbeite...').prop('disabled', true);

          // Create Progress Indicator
          var $messageContainer = $btn.closest('.acf-field').find('.acf-input'); // Try to find closest container
          if ($messageContainer.length === 0) $messageContainer = $btn.parent();

          var $msg = $('<div class="notice notice-info inline" style="margin-top:10px;"><p>Verarbeitung läuft...</p></div>');
          $messageContainer.append($msg);

          console.log('Starting delete action:', action);
          $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
              action: action,
              nonce: '<?php echo wp_create_nonce('seopress_site_settings_action'); ?>'
            },
            success: function(response) {
              console.log('Response received:', response);
              if (response.success) {
                $msg.removeClass('notice-info').addClass('notice-success').html('<p>' + response.data.message + '</p>');
              } else {
                $msg.removeClass('notice-info').addClass('notice-error').html('<p>Fehler: ' + (response.data || 'Unbekannter Fehler') + '</p>');
              }
            },
            error: function(xhr, status, error) {
              console.error('AJAX Error:', status, error, xhr);
              $msg.removeClass('notice-info').addClass('notice-error').html('<p>Server-Fehler.</p>');
            },
            complete: function() {
              $btn.text(originalText).prop('disabled', false);
              setTimeout(function() {
                $msg.fadeOut();
              }, 5000);
            }
          });
        }

        $(document).on('click', '.seopress-delete-pages-btn', function(e) {
          e.preventDefault();
          runDeleteAction('seopress_delete_all_pages', this);
        });

        $(document).on('click', '.seopress-delete-cats-btn', function(e) {
          e.preventDefault();
          runDeleteAction('seopress_delete_all_categories', this);
        });
      });
    </script>
<?php
  }

    // Preload all theme options to avoid N+1 queries
    // Moved to get_option() to perform lazy loading and avoid early init translation errors
    // $this->preload_options();

  /**
   * Preload all ACF options starting with 'options_' into local cache.
   */
  private function preload_options()
  {
    global $wpdb;

    // Direct DB query to fetch all options starting with 'options_'
    // avoiding the overhead of get_field('option') which triggers one query per field.
    $results = $wpdb->get_results("SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE 'options_%'");

    if ($results) {
      foreach ($results as $row) {
        // Remove 'options_' prefix (length 8)
        $key = substr($row->option_name, 8);
        $this->options_cache[$key] = maybe_unserialize($row->option_value);
      }
    }
  }

  /**
   * Get a single option with caching
   *
   * @param string $selector
   * @param mixed $default
   * @return mixed
   */
  private bool $is_preloaded = false;

  public function get_option(string $selector, $default = null)
  {
    if (!$this->is_preloaded) {
      $this->preload_options();
      $this->is_preloaded = true;
    }

    if (array_key_exists($selector, $this->options_cache)) {
      return $this->options_cache[$selector];
    }

    // Fallback to standard ACF get_field which handles formatting logic we might miss
    // But ideally we rely on the preloaded raw value if simple. 
    // ACF get_field does extra processing (formatting). 
    // If we want FULL optimization, we might skip get_field for simple text/numbers.
    // For now, let's try to trust the cache IF present, else fallback.
    // However, ACF values might need formatting? 
    // Most Site Settings are simple text/urls.

    // If not in cache (meaning not in DB or different key format), try standard get_field
    $value = get_field($selector, 'option');

    // Fallback: Try raw WP option (ACF stores them as 'options_name')
    if ($value === null) {
      $value = \get_option('options_' . $selector);
    }

    if ($value === null || $value === false || $value === '') {
      $value = $default;
    }

    $this->options_cache[$selector] = $value;
    return $value;
  }

  public function register_options_page()
  {
    if (function_exists('acf_add_options_page')) {
      acf_add_options_page([
        'page_title' => 'Site Settings',
        'menu_title' => 'Site Settings',
        'menu_slug' => 'site-settings',
        'capability' => 'edit_theme_options',
        'redirect' => false
      ]);
    }
  }

  /**
   * Add Site Settings link to the Admin Bar
   */
  public function add_site_settings_to_admin_bar(\WP_Admin_Bar $admin_bar)
  {
    // Only for users with manage_options capability
    if (!current_user_can('edit_theme_options')) {
      return;
    }

    $admin_bar->add_node([
      'id' => 'site-settings',
      'title' => '<span class="ab-icon dashicons dashicons-admin-generic"></span> ' . __('Site Settings', 'seopress'),
      'href' => admin_url('admin.php?page=site-settings'),
      'meta' => [
        'title' => __('Go to Site Settings', 'seopress'),
      ],
    ]);

    $admin_bar->add_node([
      'id' => 'seopress-clear-cache',
      'title' => '<span class="ab-icon dashicons dashicons-update"></span> ' . __('Clear Cache', 'seopress'),
      'href' => admin_url('admin.php?page=site-settings&flush_site_cache=1'),
      'meta' => [
        'title' => __('Clear Object Cache and Transients', 'seopress'),
      ],
    ]);
  }

  public function register_field_group()
  {
    if (function_exists('acf_add_local_field_group')) {
      acf_add_local_field_group(array(
        'key' => 'group_610164b88a2d9',
        'title' => 'Das Unternehmen',
        'fields' => array(
          // Appearance Tab
          array(
            'key' => 'field_tab_appearance',
            'label' => 'Appearance',
            'type' => 'tab',
          ),
          array(
            'key' => 'field_color_scheme',
            'label' => 'Color Scheme',
            'name' => 'color_scheme',
            'type' => 'select',
            'choices' => array(
              'default' => 'Default (Blue)',
              'modern' => 'Modern (Indigo)',
              'ocean' => 'Ocean (Cyan/Blue)',
              'sunset' => 'Sunset (Orange/Pink)',
              'forest' => 'Forest (Dark Green)',
              'professional' => 'Professional (Navy)',
              'elegant' => 'Elegant (Black/Gold)',
              'vibrant' => 'Vibrant (Colorful)',
              'minimal' => 'Minimal (Grayscale)',
              'nature' => 'Nature (Green)',
              'warm' => 'Warm (Orange)',
              'antique' => 'Antique (Gold/Brown/Black)',
              'custom' => 'Custom',
            ),
            'default_value' => 'default',
            'wrapper' => array('width' => '100'),
          ),
          array(
            'key' => 'field_615debd354fae',
            'label' => 'Primary',
            'name' => 'primary',
            'type' => 'color_picker',
            'wrapper' => array('width' => '25'),
            'conditional_logic' => array(
              array(
                array(
                  'field' => 'field_color_scheme',
                  'operator' => '==',
                  'value' => 'custom',
                ),
              ),
            ),
          ),
          array(
            'key' => 'field_615debfd54faf',
            'label' => 'Secondary',
            'name' => 'secondary',
            'type' => 'color_picker',
            'wrapper' => array('width' => '25'),
            'conditional_logic' => array(
              array(
                array(
                  'field' => 'field_color_scheme',
                  'operator' => '==',
                  'value' => 'custom',
                ),
              ),
            ),
          ),
          array(
            'key' => 'field_615ef1b8fd1a4',
            'label' => 'Accent',
            'name' => 'accent',
            'type' => 'color_picker',
            'wrapper' => array('width' => '25'),
            'conditional_logic' => array(
              array(
                array(
                  'field' => 'field_color_scheme',
                  'operator' => '==',
                  'value' => 'custom',
                ),
              ),
            ),
          ),
          array(
            'key' => 'field_6165618b74dd2',
            'label' => 'Neutral / Light',
            'name' => 'neutral',
            'type' => 'color_picker',
            'wrapper' => array('width' => '25'),
            'conditional_logic' => array(
              array(
                array(
                  'field' => 'field_color_scheme',
                  'operator' => '==',
                  'value' => 'custom',
                ),
              ),
            ),
          ),
          array(
            'key' => 'field_footer_link_color',
            'label' => 'Footer Link Color',
            'name' => 'footer_link_color',
            'type' => 'color_picker',
            'instructions' => 'Color for footer menu links (default: white/90% opacity)',
            'default_value' => '#e5e5e5',
            'wrapper' => array('width' => '25'),
          ),

          // Hero Settings
          array(
            'key' => 'field_hero_design_variant_global',
            'label' => 'Global Hero Variant',
            'name' => 'hero_design_variant_global',
            'type' => 'select',
            'instructions' => 'Select the default layout for the Hero/Top Picture section.',
            'choices' => array(
              'default' => 'Default (Split Screen)',
              'boxed' => 'Boxed (Overlap / Golden Plaque)',
            ),
            'default_value' => 'default',
            'wrapper' => array('width' => '50'),
          ),
          array(
            'key' => 'field_font_body',
            'label' => 'Body Font',
            'name' => 'font_body',
            'type' => 'select',
            'choices' => array(
              'system-ui' => 'System Sans-Serif',
              'Inter' => 'Inter',
              'Roboto' => 'Roboto',
              'Open Sans' => 'Open Sans',
              'Lato' => 'Lato',
              'Montserrat' => 'Montserrat',
            ),
            'default_value' => 'system-ui',
            'wrapper' => array('width' => '20'),
          ),
          array(
            'key' => 'field_font_body_weight',
            'label' => 'Body Weight',
            'name' => 'font_body_weight',
            'type' => 'select',
            'choices' => array(
              '400' => 'Normal (400)',
              '500' => 'Medium (500)',
              '600' => 'Semi-Bold (600)',
              '700' => 'Bold (700)',
            ),
            'default_value' => '400',
            'wrapper' => array('width' => '13'),
          ),
          array(
            'key' => 'field_font_heading',
            'label' => 'Heading Font',
            'name' => 'font_heading',
            'type' => 'select',
            'choices' => array(
              'system-ui' => 'System Sans-Serif',
              'Inter' => 'Inter',
              'Roboto' => 'Roboto',
              'Open Sans' => 'Open Sans',
              'Lato' => 'Lato',
              'Montserrat' => 'Montserrat',
              'Playfair Display' => 'Playfair Display (Serif)',
              'Merriweather' => 'Merriweather (Serif)',
            ),
            'default_value' => 'system-ui',
            'wrapper' => array('width' => '20'),
          ),
          array(
            'key' => 'field_font_heading_weight',
            'label' => 'Heading Weight',
            'name' => 'font_heading_weight',
            'type' => 'select',
            'choices' => array(
              '400' => 'Normal (400)',
              '600' => 'Semi-Bold (600)',
              '700' => 'Bold (700)',
              '800' => 'Extra-Bold (800)',
              '900' => 'Black (900)',
            ),
            'default_value' => '700',
            'wrapper' => array('width' => '13'),
          ),
          array(
            'key' => 'field_font_logo',
            'label' => 'Logo Font',
            'name' => 'font_logo',
            'type' => 'select',
            'choices' => array(
              'system-ui' => 'System Sans-Serif',
              'Inter' => 'Inter',
              'Roboto' => 'Roboto',
              'Open Sans' => 'Open Sans',
              'Lato' => 'Lato',
              'Montserrat' => 'Montserrat',
              'Playfair Display' => 'Playfair Display (Serif)',
              'Merriweather' => 'Merriweather (Serif)',
              'Outfit' => 'Outfit',
              'Plus Jakarta Sans' => 'Plus Jakarta Sans',
              'Sora' => 'Sora',
            ),
            'default_value' => 'Montserrat',
            'wrapper' => array('width' => '20'),
          ),
          array(
            'key' => 'field_font_logo_weight',
            'label' => 'Logo Font Weight',
            'name' => 'font_logo_weight',
            'type' => 'select',
            'choices' => array(
              '400' => 'Normal (400)',
              '500' => 'Medium (500)',
              '600' => 'Semi-Bold (600)',
              '700' => 'Bold (700)',
              '800' => 'Extra-Bold (800)',
              '900' => 'Black (900)',
            ),
            'default_value' => '900',
            'wrapper' => array('width' => '13'),
          ),

          // Location Tab
          array(
            'key' => 'field_tab_location',
            'label' => 'Location',
            'type' => 'tab',
          ),
          array(
            'key' => 'field_6101674d718d0',
            'label' => 'Einsatzgebiet (Bundesland)',
            'name' => 'einsatzgebiet',
            'type' => 'text',
            'wrapper' => array('width' => '25'),
          ),
          array(
            'key' => 'field_6101674d718d0h23',
            'label' => 'Bundesland',
            'name' => 'bundesland',
            'type' => 'text',
            'wrapper' => array('width' => '25'),
          ),

          array(
            'key' => 'field_bundesland_icon',
            'label' => 'Bundesland Icon (SVG)',
            'name' => 'bundesland_icon',
            'type' => 'image',
            'return_format' => 'url',
            'preview_size' => 'medium',
            'library' => 'all',
            'mime_types' => 'svg,png',
            'wrapper' => array('width' => '25'),
          ),
          array(
            'key' => 'field_615fe015f85_country',
            'label' => 'Land',
            'name' => 'country',
            'type' => 'text',
            'wrapper' => array('width' => '25'),
          ),




          // Legals & Business Tab
          array(
            'key' => 'field_tab_legal',
            'label' => 'Legals & Business',
            'type' => 'tab',
          ),
          array(
            'key' => 'field_6101673575312',
            'label' => 'Inhaber',
            'name' => 'inhaber',
            'type' => 'text',
            'wrapper' => array('width' => '33'),
          ),
          array(
            'key' => 'field_branche',
            'label' => 'Branche',
            'name' => 'branche',
            'type' => 'text',
            'wrapper' => array('width' => '33'),
          ),
          array(
            'key' => 'field_610164c3c5d55',
            'label' => 'Straße',
            'name' => 'strasse',
            'type' => 'text',
            'wrapper' => array('width' => '33'),
          ),
          array(
            'key' => 'field_6101664d4603a',
            'label' => 'PLZ Ort',
            'name' => 'plz',
            'type' => 'text',
            'wrapper' => array('width' => '33'),
          ),
          array(
            'key' => 'field_61016661aa52a',
            'label' => 'Homepage',
            'name' => 'homepage',
            'type' => 'text',
            'wrapper' => array('width' => '33'),
          ),
          array(
            'key' => 'field_610166769834a',
            'label' => 'E-Mail',
            'name' => 'e-mail',
            'type' => 'text',
            'wrapper' => array('width' => '33'),
          ),
          array(
            'key' => 'field_610164f7c5d56',
            'label' => 'Mobilnummer',
            'name' => 'telefonnummer',
            'type' => 'text',
            'wrapper' => array('width' => '33'),
          ),
          array(
            'key' => 'field_610165a7c5d57',
            'label' => 'Anzeige Mobil',
            'name' => 'angezeigte_telefonnummer',
            'type' => 'text',
            'wrapper' => array('width' => '33'),
          ),
          array(
            'key' => 'field_610164f7c5dfest',
            'label' => 'Festnetznummer',
            'name' => 'festnetznummer',
            'type' => 'text',
            'wrapper' => array('width' => '33'),
          ),
          array(
            'key' => 'field_610165a7c5d57_fest_view',
            'label' => 'Anzeige Festnetz',
            'name' => 'angezeigte_festnetznummer',
            'type' => 'text',
            'wrapper' => array('width' => '33'),
          ),
          array(
            'key' => 'field_610166a43e466',
            'label' => 'Gerichtsstand',
            'name' => 'gerichtsstand',
            'type' => 'text',
            'wrapper' => array('width' => '33'),
          ),
          array(
            'key' => 'field_610166b23e467',
            'label' => 'GLN',
            'name' => 'gln',
            'type' => 'text',
            'wrapper' => array('width' => '33'),
          ),
          array(
            'key' => 'field_610166d16add5',
            'label' => 'UID',
            'name' => 'uid',
            'type' => 'text',
            'wrapper' => array('width' => '33'),
          ),
          array(
            'key' => 'field_610166d16add6',
            'label' => 'Kontakt Form ID',
            'name' => 'contact_form_id',
            'type' => 'number',
            'wrapper' => array('width' => '50'),
          ),
          array(
            'key' => 'field_610166d16adq9',
            'label' => 'Rückruf Form ID',
            'name' => 'call_back_form_id',
            'type' => 'number',
            'wrapper' => array('width' => '50'),
          ),
          array(
            'key' => 'field_610166dccc1c5',
            'label' => 'Berechtigungen (Impressum)',
            'name' => 'berechtigungen',
            'type' => 'wysiwyg',
            'toolbar' => 'full',
            'media_upload' => 1,
            'wrapper' => array('width' => '100'),
          ),
          array(
            'key' => 'field_615ef015f5703',
            'label' => 'Urheberrecht (Footer)',
            'name' => 'urheberrecht',
            'type' => 'wysiwyg',
            'toolbar' => 'basic',
            'media_upload' => 0,
            'wrapper' => array('width' => '100'),
          ),
          array(
            'key' => 'field_615fe015f5703',
            'label' => 'Entsorgungspartner',
            'name' => 'entsorgungspartner',
            'type' => 'wysiwyg',
            'toolbar' => 'basic',
            'media_upload' => 1,
            'wrapper' => array('width' => '100'),
          ),


          // Opening Hours (Moved to Legals)
          array(
            'key' => 'field_opening_hours_section',
            'label' => 'Öffnungszeiten',
            'type' => 'message',
            'message' => 'Bitte geben Sie hier die Öffnungszeiten an, die im Footer angezeigt werden.',
            'new_lines' => 'wpautop',
            'esc_html' => 0,
          ),
          array(
            'key' => 'field_opening_hours_mo_fr',
            'label' => 'Mo-Fr',
            'name' => 'opening_hours_mo_fr',
            'type' => 'text',
            'default_value' => '08:00-20:00',
            'wrapper' => array('width' => '50'),
          ),
          array(
            'key' => 'field_opening_hours_sa_so',
            'label' => 'Sa-So',
            'name' => 'opening_hours_sa_so',
            'type' => 'text',
            'default_value' => '10:00-18:00',
            'wrapper' => array('width' => '50'),
          ),

          // Content Generator Tab
          array(
            'key' => 'field_tab_content_generator',
            'label' => 'Content Generator',
            'type' => 'tab',
          ),
          array(
            'key' => 'field_content_generator_link',
            'label' => 'Dienstleistungen & Seiten Generator',
            'name' => 'content_generator_message',
            'type' => 'message',
            'message' => '<div style="text-align: center; padding: 20px; background: #fff; border: 1px solid #ccd0d4; border-left: 4px solid #72aee6; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
              <h3 style="margin-top:0;">Seiten & Einsatzgebiete verwalten</h3>
              <p>Nutzen Sie den <strong>Page Generator</strong>, um Dienstleistungen auszuwählen, Einsatzgebiete zu definieren und automatisch Landingpages zu generieren.</p>
              <p>
                  <a href="' . admin_url('admin.php?page=service-selection') . '" class="button button-primary button-hero">Generator öffnen</a>
              </p>
            </div>',
            'new_lines' => '', // No auto p
            'esc_html' => 0,
          ),

          // Remote Settings Tab
          array(
            'key' => 'field_tab_remote',
            'label' => 'Remote Settings',
            'type' => 'tab',
          ),
          array(
            'key' => 'field_615fe015f85',
            'label' => 'Basic Remote URL',
            'name' => 'basicremote',
            'type' => 'url',
            'instructions' => 'Zentrale Basis-URL für die Daten-API (z.B. https://www.xn--entrmpelung-whb.at/api/). Alle Struktur-Daten werden automatisch vom /site-structure Endpoint geladen.',
          ),
          array(
            'key' => 'field_locations_api_endpoint',
            'label' => 'Locations API Endpoint',
            'name' => 'locations_api_endpoint',
            'type' => 'url',
            'default_value' => 'https://www.xn--entrmpelung-whb.at/locations_api/wp-json/seopressortsinfos/v1/ortsinfos/',
            'instructions' => 'URL für die Abfrage der Bezirks- und Ortsdaten.',
            'wrapper' => array('width' => '50'),
          ),
          array(
            'key' => 'field_synonyms_api_endpoint',
            'label' => 'Synonyms API Endpoint',
            'name' => 'synonyms_api_endpoint',
            'type' => 'url',
            'default_value' => 'https://www.xn--entrmpelung-whb.at/api/wp-json/custom/v1/synonyms',
            'instructions' => 'URL für die Abfrage der Synonyme und Wortstämme.',
            'wrapper' => array('width' => '50'),
          ),

          // SMTP Settings Tab

          array(
            'key' => 'field_tab_smtp',
            'label' => 'SMTP Settings',
            'type' => 'tab',
          ),
          array(
            'key' => 'field_smtp_host',
            'label' => 'SMTP Host',
            'name' => 'smtp_host',
            'type' => 'text',
            'instructions' => 'z.B. smtp.example.com',
            'wrapper' => array('width' => '50'),
          ),
          array(
            'key' => 'field_smtp_port',
            'label' => 'SMTP Port',
            'name' => 'smtp_port',
            'type' => 'number',
            'default_value' => 587,
            'wrapper' => array('width' => '25'),
          ),
          array(
            'key' => 'field_smtp_secure',
            'label' => 'Verschlüsselung',
            'name' => 'smtp_secure',
            'type' => 'select',
            'choices' => array(
              'tls' => 'TLS (Empfohlen)',
              'ssl' => 'SSL',
              'none' => 'Keine',
            ),
            'default_value' => 'tls',
            'wrapper' => array('width' => '25'),
          ),
          array(
            'key' => 'field_smtp_user',
            'label' => 'SMTP Benutzername',
            'name' => 'smtp_user',
            'type' => 'text',
            'wrapper' => array('width' => '50'),
          ),
          array(
            'key' => 'field_smtp_pass',
            'label' => 'SMTP Passwort',
            'name' => 'smtp_pass',
            'type' => 'password',
            'wrapper' => array('width' => '50'),
          ),
          array(
            'key' => 'field_smtp_test_ui',
            'label' => 'Test-E-Mail senden',
            'name' => 'smtp_test_message',
            'type' => 'message',
            'message' => '
              <div style="display:flex; gap:10px; align-items:center;">
                  <input type="email" id="smtp_test_email_input" placeholder="test@example.com" style="padding: 5px; border: 1px solid #ccc; border-radius: 4px; width: 250px;">
                  <button type="button" class="button button-secondary" onclick="var email = document.getElementById(\'smtp_test_email_input\').value; if(email) { window.location.href=\'admin.php?page=site-settings&smtp_test=1&recipient=\'+email; } else { alert(\'Bitte geben Sie eine E-Mail-Adresse ein.\'); }">
                      Test-E-Mail jetzt senden
                  </button>
              </div>
              <p class="description">Speichern Sie zuerst die Einstellungen, bevor Sie den Test durchführen.</p>',
            'new_lines' => '',
            'esc_html' => 0,
          ),

          // SEO Settings Tab
          array(
            'key' => 'field_tab_seo',
            'label' => 'SEO Settings',
            'type' => 'tab',
          ),
          array(
            'key' => 'field_google_site_verification',
            'label' => 'Google Site Verification Code',
            'name' => 'google_site_verification',
            'type' => 'text',
            'instructions' => 'Geben Sie den Verifizierungscode von der Google Search Console ein (nur den Code, nicht das ganze Tag, oder das ganze Tag, das System extrahiert den Code wenn nötig).',
            'wrapper' => array('width' => '100'),
          ),

          // Tools Tab
          array(
            'key' => 'field_tab_tools',
            'label' => 'Tools',
            'type' => 'tab',
          ),

          array(
            'key' => 'field_clear_cache_button',
            'label' => 'Cache Management',
            'name' => 'clear_cache_message',
            'type' => 'message',
            'message' => '<a href="' . admin_url('admin.php?page=site-settings&flush_site_cache=1') . '" class="button button-primary">Alle Caches leeren</a><br><small>Leert den Objekt-Cache, Transients und API-Caches.</small>',
            'new_lines' => 'wpautop',
            'esc_html' => 0,
          ),
          array(
            'key' => 'field_delete_pages_button',
            'label' => 'Alle Seiten löschen',
            'name' => 'delete_pages_message',
            'type' => 'message',
            'message' => '<button type="button" class="button button-secondary seopress-delete-pages-btn" style="color: #b32d2e; border-color: #b32d2e;">Alle Seiten endgültig löschen</button><br><small style="color: #b32d2e;">Vorsicht: Löscht alle Inhalte vom Typ "Page" dauerhaft.</small>',
            'new_lines' => 'wpautop',
            'esc_html' => 0,
          ),
          array(
            'key' => 'field_delete_categories_button',
            'label' => 'Alle Kategorien löschen',
            'name' => 'delete_categories_message',
            'type' => 'message',
            'message' => '<button type="button" class="button button-secondary seopress-delete-cats-btn" style="color: #b32d2e; border-color: #b32d2e;">Alle Kategorien löschen</button><br><small style="color: #b32d2e;">Vorsicht: Löscht alle Taxonomie-Terme "Category" sowie Services & Bezirke.</small>',
            'new_lines' => 'wpautop',
            'esc_html' => 0,
          ),
        ),
        'location' => array(
          array(
            array(
              'param' => 'options_page',
              'operator' => '==',
              'value' => 'site-settings',
            ),
          ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
      ));
    }
  }

  /**
   * Get business data/options
   * Returns array of all global business settings.
   */
  public function get_business_options(): array
  {
    $fields = [
      'inhaber',
      'branche',
      'street',
      'zip',
      'homepage',
      'bundesland',
      'bundesland_icon',
      'einsatzgebiet',
      'country',
      'e-mail',
      'angezeigte_telefonnummer',
      'telefonnummer',
      'phone_number_view',
      'uid',
      'gln',
      'urheberrecht',
      'berechtigungen',
      'form_id',
      'call_back_form_id'
    ];

    return $this->get_options($fields);
  }

  /**
   * Get multiple options in a batch
   *
   * @param array $selectors
   * @return array
   */
  public function get_options(array $selectors): array
  {
    $results = [];
    foreach ($selectors as $selector) {
      $results[$selector] = $this->get_option($selector);
    }
    return $results;
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * NATIVE SETTINGS PAGE (when ACF is not installed)
   * ═══════════════════════════════════════════════════════════════
   */

  /** All option keys managed by this settings page */
  private function get_settings_fields(): array
  {
    return [
      'appearance' => [
        'color_scheme', 'primary', 'secondary', 'accent', 'neutral', 'footer_link_color',
        'hero_design_variant_global',
        'font_body', 'font_body_weight', 'font_heading', 'font_heading_weight',
        'font_logo', 'font_logo_weight',
      ],
      'location' => [
        'einsatzgebiet', 'bundesland', 'bundesland_icon', 'country',
      ],
      'business' => [
        'inhaber', 'branche', 'strasse', 'plz', 'homepage', 'e-mail',
        'telefonnummer', 'angezeigte_telefonnummer', 'festnetznummer', 'angezeigte_festnetznummer',
        'gerichtsstand', 'gln', 'uid', 'contact_form_id', 'call_back_form_id',
        'berechtigungen', 'urheberrecht', 'entsorgungspartner',
        'opening_hours_mo_fr', 'opening_hours_sa_so',
      ],
      'remote' => [
        'basicremote', 'locations_api_endpoint', 'synonyms_api_endpoint',
      ],
      'smtp' => [
        'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password',
        'smtp_from_email', 'smtp_from_name', 'smtp_encryption',
      ],
      'seo' => [
        'aggregate_rating_value', 'aggregate_review_count',
        'social_facebook', 'social_instagram', 'social_linkedin',
        'social_youtube', 'social_tiktok', 'social_xing',
      ],
    ];
  }

  public function register_native_options_page()
  {
    add_menu_page(
      'Site Settings',
      'Site Settings',
      'edit_theme_options',
      'site-settings',
      [$this, 'render_native_settings_page'],
      'dashicons-admin-generic',
      80
    );
  }

  public function register_native_settings()
  {
    foreach ($this->get_settings_fields() as $group => $fields) {
      foreach ($fields as $field) {
        register_setting('seopress_settings', 'options_' . $field);
      }
    }
  }

  public function render_native_settings_page()
  {
    if (!current_user_can('edit_theme_options')) {
      wp_die('Unauthorized');
    }

    // Handle form save
    if (isset($_POST['seopress_settings_nonce']) && wp_verify_nonce($_POST['seopress_settings_nonce'], 'seopress_save_settings')) {
      $all_fields = $this->get_settings_fields();
      foreach ($all_fields as $group => $fields) {
        foreach ($fields as $field) {
          if (isset($_POST['options_' . $field])) {
            update_option('options_' . $field, sanitize_text_field($_POST['options_' . $field]));
          }
        }
      }
      echo '<div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>';
    }

    $active_tab = $_GET['tab'] ?? 'business';
    $tabs = [
      'business'   => 'Legals & Business',
      'location'   => 'Location',
      'appearance' => 'Appearance',
      'remote'     => 'Remote Settings',
      'seo'        => 'SEO & Social',
      'smtp'       => 'SMTP',
    ];

    ?>
    <div class="wrap">
      <h1>Site Settings</h1>

      <?php if (!class_exists('ACF')): ?>
        <div class="notice notice-info"><p><strong>ACF not installed</strong> — Using native settings. All existing data is preserved.</p></div>
      <?php endif; ?>

      <nav class="nav-tab-wrapper">
        <?php foreach ($tabs as $slug => $label): ?>
          <a href="?page=site-settings&tab=<?= $slug ?>"
             class="nav-tab <?= $active_tab === $slug ? 'nav-tab-active' : '' ?>">
            <?= esc_html($label) ?>
          </a>
        <?php endforeach; ?>
        <a href="<?= admin_url('admin.php?page=site-settings&flush_site_cache=1') ?>"
           class="nav-tab" style="color: #d63638;">🗑 Clear Cache</a>
      </nav>

      <form method="post" style="max-width: 800px; margin-top: 20px;">
        <?php wp_nonce_field('seopress_save_settings', 'seopress_settings_nonce'); ?>

        <table class="form-table">
          <?php
          $fields = $this->get_settings_fields()[$active_tab] ?? [];
          foreach ($fields as $field):
            $value = get_option('options_' . $field, '');
            $label = ucfirst(str_replace(['_', '-'], ' ', $field));
          ?>
          <tr>
            <th scope="row"><label for="options_<?= $field ?>"><?= esc_html($label) ?></label></th>
            <td>
              <?php if (in_array($field, ['berechtigungen', 'urheberrecht', 'entsorgungspartner'])): ?>
                <textarea name="options_<?= $field ?>" id="options_<?= $field ?>"
                  rows="4" class="large-text"><?= esc_textarea($value) ?></textarea>
              <?php elseif ($field === 'color_scheme'): ?>
                <select name="options_<?= $field ?>" id="options_<?= $field ?>">
                  <?php foreach (['default'=>'Default','modern'=>'Modern','ocean'=>'Ocean','sunset'=>'Sunset','forest'=>'Forest','professional'=>'Professional','elegant'=>'Elegant','vibrant'=>'Vibrant','minimal'=>'Minimal','nature'=>'Nature','warm'=>'Warm','antique'=>'Antique','custom'=>'Custom'] as $k => $v): ?>
                    <option value="<?= $k ?>" <?= selected($value, $k, false) ?>><?= $v ?></option>
                  <?php endforeach; ?>
                </select>
              <?php elseif ($field === 'hero_design_variant_global'): ?>
                <select name="options_<?= $field ?>" id="options_<?= $field ?>">
                  <option value="default" <?= selected($value, 'default', false) ?>>Default (Split Screen)</option>
                  <option value="boxed" <?= selected($value, 'boxed', false) ?>>Boxed (Golden Plaque)</option>
                </select>
              <?php elseif (str_contains($field, 'font_') && !str_contains($field, 'weight')): ?>
                <select name="options_<?= $field ?>" id="options_<?= $field ?>">
                  <?php foreach (['system-ui'=>'System','Inter'=>'Inter','Roboto'=>'Roboto','Open Sans'=>'Open Sans','Lato'=>'Lato','Montserrat'=>'Montserrat','Playfair Display'=>'Playfair Display','Merriweather'=>'Merriweather'] as $k => $v): ?>
                    <option value="<?= $k ?>" <?= selected($value, $k, false) ?>><?= $v ?></option>
                  <?php endforeach; ?>
                </select>
              <?php elseif (str_contains($field, '_weight')): ?>
                <select name="options_<?= $field ?>" id="options_<?= $field ?>">
                  <?php foreach (['400'=>'Normal','500'=>'Medium','600'=>'Semi-Bold','700'=>'Bold','800'=>'Extra-Bold','900'=>'Black'] as $k => $v): ?>
                    <option value="<?= $k ?>" <?= selected($value, $k, false) ?>><?= $v ?></option>
                  <?php endforeach; ?>
                </select>
              <?php elseif (in_array($field, ['primary','secondary','accent','neutral','footer_link_color'])): ?>
                <input type="color" name="options_<?= $field ?>" id="options_<?= $field ?>"
                  value="<?= esc_attr($value) ?>" style="width: 80px; height: 40px;">
                <input type="text" value="<?= esc_attr($value) ?>" style="width: 100px;" readonly>
              <?php elseif ($field === 'smtp_encryption'): ?>
                <select name="options_<?= $field ?>" id="options_<?= $field ?>">
                  <option value="" <?= selected($value, '', false) ?>>None</option>
                  <option value="tls" <?= selected($value, 'tls', false) ?>>TLS</option>
                  <option value="ssl" <?= selected($value, 'ssl', false) ?>>SSL</option>
                </select>
              <?php elseif ($field === 'smtp_password'): ?>
                <input type="password" name="options_<?= $field ?>" id="options_<?= $field ?>"
                  value="<?= esc_attr($value) ?>" class="regular-text">
              <?php else: ?>
                <input type="text" name="options_<?= $field ?>" id="options_<?= $field ?>"
                  value="<?= esc_attr($value) ?>" class="regular-text">
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </table>

        <?php submit_button('Settings speichern'); ?>
      </form>
    </div>
    <?php
  }
}
