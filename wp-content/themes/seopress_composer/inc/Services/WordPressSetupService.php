<?php

namespace SeopressComposer\Services;

/**
 * WordPress Setup Service
 * 
 * Handles WordPress theme setup, features, and configurations
 */
class WordPressSetupService
{
  public function __construct()
  {
    // Theme Setup
    add_action('after_setup_theme', [$this, 'theme_setup']);

    // Add taxonomies to pages
    add_action('init', [$this, 'add_taxonomies_to_pages']);

    // Include pages in category and tag archives
    add_action('pre_get_posts', [$this, 'category_and_tag_archives']);

    // Exclude specific page templates from search
    add_action('pre_get_posts', [$this, 'exclude_pages_by_template_from_search']);

    // Custom admin styles
    add_action('admin_head', [$this, 'custom_admin_styles']);

    // Load text domain
    add_action('init', [$this, 'load_text_domain']);
  }

  /**
   * Theme Setup - Register theme features
   */
  public function theme_setup(): void
  {
    // Add theme support for menus
    add_theme_support('menus');

    // Add theme support for post thumbnails
    add_theme_support('post-thumbnails');

    // Add theme support for title tag
    add_theme_support('title-tag');

    // Add theme support for HTML5
    add_theme_support('html5', [
      'search-form',
      'comment-form',
      'comment-list',
      'gallery',
      'caption',
    ]);

    // Add custom image sizes
    add_image_size('karte', 80, 80, true);
    add_image_size('top', 1300, 700, true);
    add_image_size('category-thumb', 300, 225, true);
    add_image_size('map-thumb', 40, 40, false);
    add_image_size('hauptseiten-3col', 226, 170, true);
    add_image_size('homepage-thumb', 300, 200, true);
    add_image_size('category-thumb-big', 500, 300, true);
    add_image_size('blog-thumb', 360, 205, true);
    add_image_size('mobile-top', 640, 320, false);
    add_image_size('menue_image', 500, 500, true);

    // Register navigation menus - moved to init for 6.7 compatibility
    add_action('init', function () {
      register_nav_menu('primary', __('Primary Menu', 'seopress'));
    }, 10);
  }

  /**
   * Add categories and tags to pages
   */
  public function add_taxonomies_to_pages(): void
  {
    register_taxonomy_for_object_type('post_tag', 'page');
    register_taxonomy_for_object_type('category', 'page');

    // Register Menu Category Taxonomy
    $labels = [
      'name'              => 'Menü Kategorien',
      'singular_name'     => 'Menü Kategorie',
      'search_items'      => 'Menü Kategorien suchen',
      'all_items'         => 'Alle Menü Kategorien',
      'parent_item'       => 'Übergeordnete Menü Kategorie',
      'parent_item_colon' => 'Übergeordnete Menü Kategorie:',
      'edit_item'         => 'Menü Kategorie bearbeiten',
      'update_item'       => 'Menü Kategorie aktualisieren',
      'add_new_item'      => 'Neue Menü Kategorie hinzufügen',
      'new_item_name'     => 'Neuer Menü Kategorie Name',
      'menu_name'         => 'Menü Kategorien',
    ];

    $args = [
      'hierarchical'      => true,
      'labels'            => $labels,
      'show_ui'           => true,
      'show_admin_column' => true,
      'query_var'         => true,
      'rewrite'           => ['slug' => 'menus', 'with_front' => false],
      'show_in_rest'      => true,
    ];

    register_taxonomy('menu_category', ['page'], $args);
  }

  /**
   * Include pages in category and tag archives
   */
  public function category_and_tag_archives($wp_query): void
  {
    if (!is_admin() && ($wp_query->get('category_name') || $wp_query->get('cat') || $wp_query->get('tag'))) {
      $wp_query->set('post_type', ['post', 'page']);
    }
  }

  /**
   * Exclude specific page templates from search results
   */
  public function exclude_pages_by_template_from_search($query): void
  {
    if ($query->is_search && !is_admin()) {
      $excluded_pages = get_pages([
        'meta_key' => '_wp_page_template',
        'meta_value' => 'template-datenschutz-agb.php',
        'fields' => 'ids'
      ]);

      if (!empty($excluded_pages)) {
        $query->set('post__not_in', $excluded_pages);
      }
    }
  }

  /**
   * Custom Admin Styles
   */
  public function custom_admin_styles(): void
  {
    echo '<style>
      .acf-field-checkbox .acf-checkbox-list { 
        display: flex; 
        flex-wrap: wrap; 
      }
      .acf-field-checkbox .acf-checkbox-list input { 
        margin-right: 5px; 
      }
      .acf-field-checkbox .acf-checkbox-list li { 
        width: 30%; 
      }
    </style>';
  }

  /**
   * Load plugin text domain
   */
  public function load_text_domain(): void
  {
    load_plugin_textdomain('rocket', false, dirname(plugin_basename(__FILE__)) . '/languages/');
  }
}
