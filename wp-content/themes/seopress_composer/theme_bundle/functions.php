<?php

/**
 * Theme Functions
 * 
 * Loads autoloader and provides helper functions for container access.
 */

require_once get_template_directory() . '/vendor/autoload.php';

// Include GraphQL Setup & Server Fixes (so this theme can also serve the API)
require_once get_template_directory() . '/inc/graphql-setup.php';

/**
 * ACF Compatibility Bridge
 * 
 * If ACF is not active, provide native replacements for get_field/update_field
 * that read from the same wp_options/post_meta/term_meta storage locations.
 * This makes the ACF plugin optional — the theme works with or without it.
 */
if (!function_exists('get_field')) {
    function get_field(string $selector, $post_id = null, $format_value = true) {
        return \SeopressComposer\Helpers\MetaHelper::getField($selector, $post_id);
    }
}

if (!function_exists('update_field')) {
    function update_field(string $selector, $value, $post_id = null) {
        \SeopressComposer\Helpers\MetaHelper::setField($selector, $value, $post_id);
    }
}

if (!function_exists('have_rows')) {
    function have_rows(string $selector, $post_id = null): bool {
        $value = \SeopressComposer\Helpers\MetaHelper::getField($selector, $post_id);
        return !empty($value) && is_array($value);
    }
}

if (!function_exists('get_sub_field')) {
    function get_sub_field(string $selector) {
        // Stub — repeater sub-fields are not used in a way that requires this
        return null;
    }
}

if (!function_exists('the_row')) {
    function the_row() {
        // Stub for repeater compatibility
        return true;
    }
}

if (!function_exists('acf_add_options_page')) {
    function acf_add_options_page($args = []) {
        // No-op: Options page is registered natively via SiteSettings
        return true;
    }
}

if (!function_exists('acf_add_local_field_group')) {
    function acf_add_local_field_group($args = []) {
        // No-op: Field groups are not needed without ACF UI
        return true;
    }
}

/**
 * Helper to get the service container
 */
function seopress_container()
{
    static $container = null;
    if ($container === null) {
        $container = new \SeopressComposer\Core\ServiceContainer();

        $container->set(\SeopressComposer\Core\ComponentContainer::class, function ($c) {
            return new \SeopressComposer\Core\ComponentContainer($c);
        });

        $container->set(\SeopressComposer\Core\ModelsDTO::class, function ($c) {
            return new \SeopressComposer\Core\ModelsDTO($c);
        });

        $container->set(\SeopressComposer\Services\SchemaService::class, function ($c) {
            return new \SeopressComposer\Services\SchemaService(
                $c->get(\SeopressComposer\Core\ModelsDTO::class),
                $c->get(\SeopressComposer\Helpers\PageHelper::class)
            );
        });
    }
    return $container;
}

/**
 * Helper to get components
 */
function seopress_components()
{
    return seopress_container()->get(\SeopressComposer\Core\ComponentContainer::class);
}

/**
 * Helper to get models
 */
function seopress_models()
{
    return seopress_container()->get(\SeopressComposer\Core\ModelsDTO::class);
}

/**
 * Helper to get layouts
 */
function seopress_layouts()
{
    return seopress_container()->get(\SeopressComposer\Core\LayoutsContainer::class);
}


/**
 * Get PageDataService instance
 * 
 * @return \SeopressComposer\Services\PageDataService
 */
function seopress_page_data()
{
    static $service = null;

    if ($service === null) {
        $service = new \SeopressComposer\Services\PageDataService(seopress_models());
    }

    return $service;
}

/**
 * Get and render contextual sidebar
 * 
 * @return string Rendered sidebar HTML
 */
function seopress_sidebar()
{
    return seopress_container()->get(\SeopressComposer\Controllers\MenuController::class)->renderSidebar();
}

/**
 * Enqueue theme assets (Vite)
 */
function seopress_composer_enqueue_assets()
{
    $is_dev = false; // Set to true for local development (npm run dev)

    if (defined('WP_ENV') && constant('WP_ENV') === 'development') {
        $is_dev = true;
    }

    if ($is_dev) {
        // Vite Dev Server - CSS is loaded via JS import
        wp_enqueue_script('theme-dev-js', 'http://localhost:5173/assets/js/main.js', [], null, true);
        wp_enqueue_script('vite-client', 'http://localhost:5173/@vite/client', [], null, true);
        wp_script_add_data('vite-client', 'type', 'module');
        wp_script_add_data('theme-dev-js', 'type', 'module');
    } else {
        // Production: Load from manifest
        $manifest_path = get_template_directory() . '/dist/.vite/manifest.json';

        // Fallback for older Vite versions
        if (!file_exists($manifest_path)) {
            $manifest_path = get_template_directory() . '/dist/manifest.json';
        }

        if (file_exists($manifest_path)) {
            $manifest = json_decode(file_get_contents($manifest_path), true);

            // Check for main JS entry
            if (isset($manifest['assets/js/main.js'])) {
                $js_file = $manifest['assets/js/main.js']['file'];
                wp_enqueue_script('theme', get_template_directory_uri() . '/dist/' . $js_file, [], null, true);

                // CSS associated with JS - Add preload hint
                if (isset($manifest['assets/js/main.js']['css'])) {
                    foreach ($manifest['assets/js/main.js']['css'] as $css_file) {
                        $css_url = get_template_directory_uri() . '/dist/' . $css_file;

                        // Preload CSS for faster loading
                        echo '<link rel="preload" href="' . esc_url($css_url) . '" as="style">';

                        // Enqueue with async loading strategy
                        wp_enqueue_style('theme', $css_url, [], null);
                    }
                }

                // Enqueue standalone icons CSS (not processed by Tailwind)
                wp_enqueue_style('theme-icons', get_template_directory_uri() . '/assets/css/icons.css', [], null);

                // Preload critical fonts
                $critical_fonts = [
                    'assets/inter-latin-400-normal.woff2',
                ];

                foreach ($critical_fonts as $font) {
                    if (file_exists(get_template_directory() . '/dist/' . $font)) {
                        $font_url = get_template_directory_uri() . '/dist/' . $font;
                        echo '<link rel="preload" href="' . esc_url($font_url) . '" as="font" type="font/woff2" crossorigin>';
                    }
                }
            }
        } else {
            // Fallback if manifest not found (legacy explicit paths)
            wp_enqueue_style('theme', get_template_directory_uri() . '/dist/assets/main.css', [], null);
            wp_enqueue_script('theme', get_template_directory_uri() . '/dist/assets/main.js', [], null, true);
        }
    }
}
add_action('wp_enqueue_scripts', 'seopress_composer_enqueue_assets');



/**
 * Add resource hints for performance optimization
 * Note: Google Fonts removed — fonts are self-hosted via @fontsource (no external requests)
 */
function seopress_composer_resource_hints()
{
    // No external font requests — all fonts self-hosted via @fontsource
    // Add dns-prefetch here only for external domains actually used (e.g. CDN, API)
}
add_action('wp_head', 'seopress_composer_resource_hints', 1);

/**
 * Initialize WordPress Setup Service early to catch after_setup_theme
 */
$setup_service = new \SeopressComposer\Services\WordPressSetupService();

/**
 * Enable ACF Caching for Performance
 * Reduces database queries by caching field lookups
 */
add_filter('acf/settings/cache', '__return_true');

/**
 * Initialize Settings and Services on 'init' hook
 */
/**
 * Initialize Settings and Services
 * 
 * Split into after_setup_theme (for hooks registration) and init (for logic)
 * to avoid ACF "doing it wrong" notices regarding translation loading.
 */
add_action('after_setup_theme', function () {
    // Initialize Settings (Register hooks for ACF)
    seopress_container()->get(\SeopressComposer\Settings\SiteSettings::class);
    new \SeopressComposer\Settings\CategoryFields();
    new \SeopressComposer\Core\Admin\RemotePageFields();

    // Initialize SiteSettings_Data (Cached Model)
    seopress_container()->set(\SeopressComposer\Models\SiteSettings_Data::class, function ($c) {
        return new \SeopressComposer\Models\SiteSettings_Data();
    });

    // Initialize Theme Customizer (CSS Variables - hooks to wp_head)
    new \SeopressComposer\Services\ThemeCustomizerService();

    // Initialize Critical CSS Service for performance optimization
    new \SeopressComposer\Services\CriticalCssService();

    // Initialize Image Optimization Service for LCP improvement
    new \SeopressComposer\Services\ImageOptimizationService();
    
    // Initialize SEO Meta Service (replaces Yoast Meta)
    seopress_container()->get(\SeopressComposer\Services\SeoMetaService::class);
    
    // Initialize robots.txt Service (AI-SEO compliant)
    new \SeopressComposer\Services\RobotsTxtService();
    
    // Initialize XML Sitemap Service (WordPress Core Sitemaps)
    new \SeopressComposer\Services\SitemapService();
    
    // Initialize Google Image Sitemap (/image-sitemap.xml)
    seopress_container()->get(\SeopressComposer\Services\ImageSitemapService::class);
    
    // Initialize llms.txt Service (AI/LLM Crawler Context)
    seopress_container()->get(\SeopressComposer\Services\LlmsTxtService::class);
    
    // Initialize Internal Link Service (PageRank Flow Optimization)
    seopress_container()->get(\SeopressComposer\Services\InternalLinkService::class);
    
    // Add modern title-tag support
    add_theme_support('title-tag');
});

/**
 * PRE-LOAD OPTIMIZATION (High Priority)
 * Must run before other logic to prime caches
 */
add_action('after_setup_theme', function () {
    // Initialize WordPress Optimization Service early
    // This primes the options cache to prevent hundreds of DB queries
    new \SeopressComposer\Services\WordPressOptimizationService();
}, 1);

add_action('init', function () {
    // Get the TextReplacementService from the container to ensure filters are registered
    seopress_container()->get(\SeopressComposer\Services\TextReplacementService::class);

    // Initialize Location Selection Page
    seopress_container()->get(\SeopressComposer\Settings\LocationSelectionPage::class);

    // Initialize Service Selection Page
    seopress_container()->get(\SeopressComposer\Settings\ServiceSelectionPage::class);

    // Initialize SMTP Service
    seopress_container()->get(\SeopressComposer\Services\SmtpService::class);

    // Initialize Contact Form Controller
    $contactController = new \SeopressComposer\Controllers\ContactController(
        seopress_container()->get(\SeopressComposer\Settings\SiteSettings::class)
    );
    $contactController->register_routes();

    // Support categories for pages
    register_taxonomy_for_object_type('category', 'page');

    // Register Ankauf Kategorie (from API theme)
    register_taxonomy('ankauf_kategorie', ['page'], [
        'labels' => [
            'name' => 'Ankauf Kategorien',
            'singular_name' => 'Ankauf Kategorie',
        ],
        'hierarchical' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'show_in_graphql' => true,
        'graphql_single_name' => 'ankaufKategorie',
        'graphql_plural_name' => 'ankaufKategorien',
        'rewrite' => ['slug' => 'ankauf'],
    ]);

    // Note: menu_category taxonomy is registered in WordPressSetupService
}, 10); // Standard priority

/**
 * Fallback for WPForms Shortcode
 * If the user has [wpforms] anywhere in their content, render the new MultiStepForm instead.
 */
add_shortcode('wpforms', function ($atts) {
    return seopress_components()->getMultiStepForm()->render();
});

/**
 * Cache Invalidation for Menu_Data
 * Clear menu cache when content is updated
 */
add_action('save_post', function ($post_id) {
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }
    \SeopressComposer\Models\Menu_Data::clear_cache();
});

add_action('edited_term', function ($term_id, $tt_id, $taxonomy) {
    \SeopressComposer\Models\Menu_Data::clear_cache();
}, 10, 3);

add_action('create_term', function ($term_id, $tt_id, $taxonomy) {
    \SeopressComposer\Models\Menu_Data::clear_cache();
}, 10, 3);

add_action('delete_term', function ($term_id, $tt_id, $taxonomy) {
    \SeopressComposer\Models\Menu_Data::clear_cache();
}, 10, 3);

add_action('wp_update_nav_menu', function () {
    \SeopressComposer\Models\Menu_Data::clear_cache();
});

/**
 * Cache Invalidation for CategoryRepository
 * Clear category cache when terms are updated
 */
add_action('edited_category', function ($term_id) {
    wp_cache_delete('formatted_cat_' . $term_id, 'categories');
});

add_action('create_category', function ($term_id) {
    wp_cache_delete('formatted_cat_' . $term_id, 'categories');
});

add_action('delete_category', function ($term_id) {
    wp_cache_delete('formatted_cat_' . $term_id, 'categories');
});

/**
 * Cache Invalidation for Taxonomy_Data
 * Clear taxonomy cache when terms are updated
 */
add_action('edited_term', function ($term_id, $tt_id, $taxonomy) {
    \SeopressComposer\Models\Taxonomy_Data::clear_cache();
}, 10, 3);

add_action('create_term', function ($term_id, $tt_id, $taxonomy) {
    \SeopressComposer\Models\Taxonomy_Data::clear_cache();
}, 10, 3);

add_action('delete_term', function ($term_id, $tt_id, $taxonomy) {
    \SeopressComposer\Models\Taxonomy_Data::clear_cache();
}, 10, 3);

/**
 * CRITICAL PERFORMANCE OPTIMIZATION
 * Prime term cache early to prevent 700+ individual WP_Term::get_instance queries
 * This batch-loads all terms into WordPress's internal cache before any lookups occur
 */
add_action('wp', function () {
    // Only prime cache on frontend
    if (is_admin()) {
        return;
    }

    // Batch load all terms for taxonomies we use
    $taxonomies = ['category', 'district', 'service_category'];

    foreach ($taxonomies as $taxonomy) {
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'fields' => 'all', // Get all term data
        ]);

        if (!empty($terms) && !is_wp_error($terms)) {
            // Prime the term cache - this loads all term data into memory
            update_term_cache($terms, $taxonomy);
        }
    }
}, 1); // Priority 1 - run very early

/**
 * Register Custom Taxonomies for Districts and Services
 */
function register_district_taxonomy()
{
    $labels = [
        'name'              => 'Bezirke',
        'singular_name'     => 'Bezirk',
        'search_items'      => 'Bezirke suchen',
        'all_items'         => 'Alle Bezirke',
        'parent_item'       => 'Übergeordneter Bezirk',
        'parent_item_colon' => 'Übergeordneter Bezirk:',
        'edit_item'         => 'Bezirk bearbeiten',
        'update_item'       => 'Bezirk aktualisieren',
        'add_new_item'      => 'Neuen Bezirk hinzufügen',
        'new_item_name'     => 'Neuer Bezirksname',
        'menu_name'         => 'Bezirke',
    ];

    $args = [
        'hierarchical'      => true, // Wie Kategorien (mit Eltern/Kindern)
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => ['slug' => 'district', 'with_front' => false], // Prefix 'district' which we strip later
        'show_in_rest'      => true, // Wichtig für Gutenberg Editor
        'show_in_graphql'   => true, // Expose to GraphQL
        'graphql_single_name' => 'district',
        'graphql_plural_name' => 'districts',
    ];

    // Wir verknüpfen 'district' mit dem Post Type 'page'
    register_taxonomy('district', ['page'], $args);
}
add_action('init', 'register_district_taxonomy');

/**
 * Remove 'district' slug from district terms
 */
function seopress_district_link_filter($url, $term, $taxonomy)
{
    if ($taxonomy !== 'district') {
        return $url;
    }
    return str_replace('/district/', '/', $url);
}
add_filter('term_link', 'seopress_district_link_filter', 10, 3);

/**
 * Add rewrite rules for district terms at root level
 */
function seopress_district_rewrite_rules($rules)
{
    $new_rules = [];
    $terms = get_terms(['taxonomy' => 'district', 'hide_empty' => false]);

    if ($terms && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            $new_rules[$term->slug . '/?$'] = 'index.php?district=' . $term->slug;
            $new_rules[$term->slug . '/page/?([0-9]{1,})/?$'] = 'index.php?district=' . $term->slug . '&paged=$matches[1]';
        }
    }

    return $new_rules + $rules;
}
add_filter('rewrite_rules_array', 'seopress_district_rewrite_rules');

// Flush rewrite rules if our specific option isn't set (simple on-the-fly flush)
add_action('init', function () {
    if (!get_option('seopress_districts_flushed')) {
        flush_rewrite_rules();
        update_option('seopress_districts_flushed', 1);
    }
}, 99);

function register_service_taxonomy()
{
    $labels = [
        'name'              => 'Services',
        'singular_name'     => 'Service',
        'search_items'      => 'Services suchen',
        'all_items'         => 'Alle Services',
        'parent_item'       => 'Übergeordneter Service',
        'parent_item_colon' => 'Übergeordneter Service:',
        'edit_item'         => 'Service bearbeiten',
        'update_item'       => 'Service aktualisieren',
        'add_new_item'      => 'Neuen Service hinzufügen',
        'new_item_name'     => 'Neuer Service-Name',
        'menu_name'         => 'Services',
    ];

    $args = [
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => ['slug' => 'service-kategorie', 'with_front' => false],
        'show_in_rest'      => true,
        'show_in_graphql'   => true,
        'graphql_single_name' => 'serviceCategory',
        'graphql_plural_name' => 'serviceCategories',
    ];

    register_taxonomy('service_category', ['page'], $args);
}
add_action('init', 'register_service_taxonomy');

// Service categories live at /service-kategorie/{slug}/ — no root-level rewrite needed.
// The taxonomy rewrite slug 'service-kategorie' handles this natively.

// Force rewrite flush when slug changes
add_action('init', function () {
    if (!get_option('seopress_service_categories_flushed_v2')) {
        flush_rewrite_rules();
        update_option('seopress_service_categories_flushed_v2', 1);
    }
}, 99);

/**
 * View Helper: Backlinks
 * Bridges the new component/model architecture to the legacy view function pattern.
 */
function backlinks()
{
    $data = seopress_models()->getBacklinksData();
    $component = new \SeopressComposer\Components\Backlinks();
    echo $component->render($data);
}

/**
 * View Helper: Backlinks Dropdown
 * Renders backlinks in a collapsible dropdown
 */
function backlinks_dropdown()
{
    $data = seopress_models()->getBacklinksData();
    $component = new \SeopressComposer\Components\Backlinks();
    echo $component->renderDropdown($data);
}

/**
 * Initialize Schema Service
 */
function seopress_init_schema()
{
    seopress_container()->get(\SeopressComposer\Services\SchemaService::class)->output();
}
add_action('wp_head', 'seopress_init_schema');

/**
 * Initialize Favicon Service
 */
function seopress_init_favicon()
{
    seopress_container()->get(\SeopressComposer\Services\FaviconService::class)->output();
}
add_action('wp_head', 'seopress_init_favicon');

/**
 * Ensure page with "template-startseite.php" is set as homepage
 */
function seopress_set_startseite_as_homepage()
{
    // Find the page that uses template-startseite.php
    $pages = get_pages([
        'meta_key' => '_wp_page_template',
        'meta_value' => 'template-startseite.php'
    ]);

    if (!empty($pages)) {
        $startseite = $pages[0]; // Get first page with this template
        $current_homepage = get_option('page_on_front');

        // Only update if it's not already set
        if ($current_homepage != $startseite->ID) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $startseite->ID);
        }
    }
}
// Run on theme activation
add_action('after_switch_theme', 'seopress_set_startseite_as_homepage');
// Also check on admin init to ensure it stays set
add_action('admin_init', 'seopress_set_startseite_as_homepage');

/**
 * Remove '/category/' from category URLs
 */
function seopress_remove_category_base()
{
    global $wp_rewrite;
    $wp_rewrite->extra_permastructs['category']['struct'] = '%category%';
}
add_action('init', 'seopress_remove_category_base');

// Prevent category base removal from breaking other pages
add_filter('category_rewrite_rules', 'seopress_category_rewrite_rules');
function seopress_category_rewrite_rules($category_rewrite)
{
    $category_rewrite = [];
    $categories = get_categories(['hide_empty' => false]);

    foreach ($categories as $category) {
        $category_nicename = $category->slug;
        if ($category->parent == $category->cat_ID) {
            $category->parent = 0;
        } elseif ($category->parent != 0) {
            $category_nicename = get_category_parents($category->parent, false, '/', true) . $category_nicename;
        }
        $category_rewrite['(' . $category_nicename . ')/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?category_name=$matches[1]&feed=$matches[2]';
        $category_rewrite['(' . $category_nicename . ')/page/?([0-9]{1,})/?$'] = 'index.php?category_name=$matches[1]&paged=$matches[2]';
        $category_rewrite['(' . $category_nicename . ')/?$'] = 'index.php?category_name=$matches[1]';
    }

    return $category_rewrite;
}

/**
 * Add Template column to Pages overview in admin
 */
add_filter('manage_pages_columns', function ($columns) {
    $columns['page_template'] = 'Template';
    return $columns;
});

add_action('manage_pages_custom_column', function ($column_name, $post_id) {
    if ($column_name === 'page_template') {
        $template = get_post_meta($post_id, '_wp_page_template', true);

        if (empty($template) || $template === 'default') {
            echo '<span style="color: #999;">Default</span>';
            return;
        }

        // Clean up template name for display
        $template_name = basename($template, '.php');
        $template_name = str_replace(['template-', 'template_', '-', '_'], ['', '', ' ', ' '], $template_name);
        $template_name = ucwords($template_name);

        echo '<strong>' . esc_html($template_name) . '</strong>';
    }
}, 10, 2);

/**
 * Enforce html lang="de-AT" for geo-targeting Austria.
 * WordPress outputs "de-DE" by default — this overrides it to match
 * hreflang de-AT and og:locale de_AT for consistent geo-signals.
 */
add_filter('language_attributes', function ($output) {
    return preg_replace('/lang="de[^"]*"/', 'lang="de-AT"', $output);
});

/**
 * Allow .jfif image uploads
 */
add_filter('upload_mimes', function ($mimes) {
    $mimes['jfif'] = 'image/jpeg';
    return $mimes;
});

