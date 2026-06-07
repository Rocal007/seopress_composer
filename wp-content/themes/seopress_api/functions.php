<?php
// stop direct access
if (!defined('ABSPATH'))
    die("No Direct access");

// Disable Gutenberg editor.
add_filter('use_block_editor_for_post_type', '__return_false', 100);

// Theme Support
add_theme_support('menus');
add_theme_support('post-thumbnails'); // Enabled Featured Image Support

function make_href_root_relative($input)
{
    return preg_replace('!http(s)?://' . $_SERVER['SERVER_NAME'] . '/api/!', '/', $input);
}
function root_relative_permalinks($input)
{
    return make_href_root_relative($input);
}


if (!is_admin()) {
    add_filter('day_link', 'root_relative_permalinks');
    add_filter('year_link', 'root_relative_permalinks');
    add_filter('post_link', 'root_relative_permalinks');
    add_filter('page_link', 'root_relative_permalinks');
    add_filter('term_link', 'root_relative_permalinks');
    add_filter('month_link', 'root_relative_permalinks');
    add_filter('search_link', 'root_relative_permalinks');
    add_filter('the_content', 'root_relative_permalinks');
    add_filter('the_permalink', 'root_relative_permalinks');
    add_filter('get_shortlink', 'root_relative_permalinks');
    add_filter('post_type_link', 'root_relative_permalinks');
    add_filter('get_pagenum_link', 'root_relative_permalinks');
    add_filter('post_type_archive_link', 'root_relative_permalinks');
    add_filter('get_comments_pagenum_link', 'root_relative_permalinks');
}



// Return formatted about-nav menu
function about_nav_menu()
{
    $menu = wp_get_nav_menu_items('about');
    $result = [];
    foreach ($menu as $item) {
        $my_item = [
            'name' => $item->title,
            'href' => $item->url
        ];
        $result[] = $my_item;
    }
    return $result;
}
// add endpoint
add_action('rest_api_init', function () {
    // about-nav menu
    register_rest_route('wp/v2', 'about-nav', array(
        'methods' => 'GET',
        'callback' => 'about_nav_menu',
    ));
});
// Return formatted legal-nav menu
function legal_nav_menu()
{
    $menu = wp_get_nav_menu_items('legal');
    $result = [];
    foreach ($menu as $item) {
        $my_item = [
            'name' => $item->title,
            'href' => $item->url
        ];
        $result[] = $my_item;
    }
    return $result;
}
// add endpoint
add_action('rest_api_init', function () {
    // legal-nav menu
    register_rest_route('wp/v2', 'legal-nav', array(
        'methods' => 'GET',
        'callback' => 'legal_nav_menu',
    ));
});


// Return formatted services menu
function services_nav_menu()
{
    $menu = wp_get_nav_menu_items('services');
    $result = [];
    if ($menu) {
        foreach ($menu as $item) {
            $my_item = [
                'name' => $item->title,
                'href' => $item->url
            ];
            $result[] = $my_item;
        }
    }
    return $result;
}
// add endpoint
add_action('rest_api_init', function () {
    // services-nav menu
    register_rest_route('wp/v2', 'services-nav', array(
        'methods' => 'GET',
        'callback' => 'services_nav_menu',
    ));
});

// Custom Endpoint for Service Categories List with API Links
function get_service_cat_custom()
{
    $terms = get_terms([
        'taxonomy' => 'service_cat',
        'hide_empty' => false,
    ]);
    $data = [];

    if (!empty($terms) && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            $data[] = [
                'term_id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
                'link' => get_term_link($term),
                // Explicitly provide the REST API endpoint for this term
                'api_endpoint' => get_rest_url(null, 'wp/v2/service_cat/' . $term->term_id)
            ];
        }
    }
    return $data;
}

add_action('rest_api_init', function () {
    register_rest_route('wp/v2', 'service-cat-list', array(
        'methods' => 'GET',
        'callback' => 'get_service_cat_custom',
    ));
});



/*
 * Main function file of SEOPres theme
 */

// Include Server Fixes
require_once get_template_directory() . '/inc/init.php';

add_action('init', function () {
    // RENAMED: ankauf_kategorie -> service_cat
    register_taxonomy('service_cat', ['page'], [
        'labels' => [
            'name' => 'Service Kategorien',
            'singular_name' => 'Service Kategorie',
        ],
        'hierarchical' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'service-kategorie'],
    ]);

    register_taxonomy('menu_category', ['page'], [
        'labels' => [
            'name' => 'Menü Kategorien',
            'singular_name' => 'Menü Kategorie',
        ],
        'hierarchical' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'menus'],
    ]);
});

/**
 * One-time script:
 * Assign existing Pages to main Service Categories
 * DELETE AFTER RUNNING
 */

add_action('init', function () {

    // Renamed option to allow script to run again for the new taxonomy
    if (get_option('service_pages_assigned')) {
        return;
    }

    $mapping = [

        'Schmuck & Edelmetalle' => [
            'schmuck-ankauf',
            'gold-ankauf',
            'silber-ankauf',
            'uhren-ankauf',
            'muenzen-ankauf',
        ],

        'Antiquitäten & Kunst' => [
            'kunst-ankauf',
            'antike-moebel-ankauf',
            'antiquariat-ankauf',
            'porzellan-ankauf',
            'keramik',
            'glas',
            'skulpturen-plastiken',
            'volkskunst-ankauf',
            'sakrale-kunst-ankauf',
        ],

        'Historische Objekte' => [
            'militaria-ankauf',
            'kaiserhaus-ankauf',
        ],

        'Einrichtung & Design' => [
            'design',
            'teppiche',
            'pelze',
        ],

        'Asiatika & Afrikana' => [
            'asiatika',
            'afrika',
        ],

        'Sammlerstücke & Technik' => [
            'spielzeug',
            'musik-instrumente',
            'oldtimer',
        ],

        'Varia Ankauf' => [
            'baustoffe',
            'varia-ankauf',
        ],
    ];

    foreach ($mapping as $category_name => $page_slugs) {

        // Ensure category exists in new taxonomy
        $term = term_exists($category_name, 'service_cat');

        if (!$term) {
            $term = wp_insert_term($category_name, 'service_cat');
        }

        if (is_wp_error($term)) {
            continue;
        }

        $term_id = $term['term_id'];

        foreach ($page_slugs as $slug) {

            $page = get_page_by_path($slug, OBJECT, 'page');

            if (!$page) {
                continue;
            }

            wp_set_object_terms(
                $page->ID,
                [$term_id],
                'service_cat',
                false // overwrite existing terms
            );
        }
    }

    update_option('service_pages_assigned', true);
});

/**
 * Show Service Categories column in Pages overview
 */

// 1. Add columns
add_filter('manage_pages_columns', function ($columns) {
    $new_columns = [];
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'cb') {
            $new_columns['featured_image'] = 'Image';
        }
    }
    // Fallback if 'cb' is missing
    if (!isset($new_columns['featured_image'])) {
        $new_columns = array_merge(['featured_image' => 'Image'], $columns);
    }

    $new_columns['service_cat'] = 'Service Kategorien';
    $new_columns['page_template'] = 'Template';
    return $new_columns;
});

// Style the column
add_action('admin_head', function () {
    echo '<style>
        .column-featured_image { width: 80px; text-align: center; vertical-align: middle !important; }
        .column-featured_image img { vertical-align: middle; }
    </style>';
});

// 2. Render column content
add_action('manage_pages_custom_column', function ($column_name, $post_id) {

    if ($column_name === 'featured_image') {
        $nonce = wp_create_nonce('set_featured_image_column_nonce');
        $img = has_post_thumbnail($post_id)
            ? get_the_post_thumbnail($post_id, [50, 50], ['style' => 'width:50px;height:50px;object-fit:cover;border-radius:4px;pointer-events:none;'])
            : '<span style="color:#ccc;font-size:20px;line-height:1;pointer-events:none;display:inline-block;padding:8px 12px;border:1px dashed #ccc;border-radius:4px;">+</span>';

        echo sprintf(
            '<div class="featured-image-col-wrapper" data-post_id="%d" data-nonce="%s" style="cursor:pointer;" title="Click to edit image">%s</div>',
            $post_id,
            $nonce,
            $img
        );
    }

    if ($column_name === 'service_cat') {
        $terms = get_the_terms($post_id, 'service_cat');

        if (empty($terms) || is_wp_error($terms)) {
            echo '—';
            return;
        }

        $out = [];

        foreach ($terms as $term) {
            $out[] = sprintf(
                '<a href="%s">%s</a>',
                esc_url(add_query_arg([
                    'post_type' => 'page',
                    'service_cat' => $term->slug,
                ], admin_url('edit.php'))),
                esc_html($term->name)
            );
        }

        echo implode(', ', $out);
    }

    if ($column_name === 'page_template') {
        $template = get_post_meta($post_id, '_wp_page_template', true);

        if (empty($template) || $template === 'default') {
            echo '<span style="color: #999;">Default</span>';
            return;
        }

        // Clean up template name for display
        $template_name = basename($template, '.php');
        $template_name = str_replace(['template_', '-', '_'], ['', ' ', ' '], $template_name);
        $template_name = ucwords($template_name);

        echo '<strong>' . esc_html($template_name) . '</strong>';
    }
}, 10, 2);

/**
 * Add Featured Image support for service_cat taxonomy
 */

// Add featured image field to category add form
add_action('service_cat_add_form_fields', 'add_category_featured_image_field');
function add_category_featured_image_field()
{
?>
    <div class="form-field term-featured-image-wrap">
        <label for="category-featured-image"><?php _e('Featured Image', 'seopress'); ?></label>
        <div id="category-featured-image-preview" style="margin-bottom: 10px;"></div>
        <input type="hidden" id="category-featured-image" name="category_featured_image" value="">
        <button type="button" class="button category-featured-image-upload"><?php _e('Upload Image', 'seopress'); ?></button>
        <button type="button" class="button category-featured-image-remove" style="display:none;"><?php _e('Remove Image', 'seopress'); ?></button>
        <p class="description"><?php _e('Upload a featured image for this category.', 'seopress'); ?></p>
    </div>
<?php
}

// Add featured image field to category edit form
add_action('service_cat_edit_form_fields', 'edit_category_featured_image_field');
function edit_category_featured_image_field($term)
{
    $image_id = get_term_meta($term->term_id, 'category_featured_image', true);
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : '';
?>
    <tr class="form-field term-featured-image-wrap">
        <th scope="row">
            <label for="category-featured-image"><?php _e('Featured Image', 'seopress'); ?></label>
        </th>
        <td>
            <div id="category-featured-image-preview" style="margin-bottom: 10px;">
                <?php if ($image_url): ?>
                    <img src="<?php echo esc_url($image_url); ?>" style="max-width: 300px; height: auto; display: block;">
                <?php endif; ?>
            </div>
            <input type="hidden" id="category-featured-image" name="category_featured_image" value="<?php echo esc_attr($image_id); ?>">
            <button type="button" class="button category-featured-image-upload"><?php _e('Upload Image', 'seopress'); ?></button>
            <button type="button" class="button category-featured-image-remove" style="<?php echo $image_id ? '' : 'display:none;'; ?>"><?php _e('Remove Image', 'seopress'); ?></button>
            <p class="description"><?php _e('Upload a featured image for this category.', 'seopress'); ?></p>
        </td>
    </tr>
<?php
}

// Save featured image when category is created or updated
add_action('created_service_cat', 'save_category_featured_image');
add_action('edited_service_cat', 'save_category_featured_image');
function save_category_featured_image($term_id)
{
    if (isset($_POST['category_featured_image'])) {
        $image_id = absint($_POST['category_featured_image']);
        if ($image_id) {
            update_term_meta($term_id, 'category_featured_image', $image_id);
        } else {
            delete_term_meta($term_id, 'category_featured_image');
        }
    }
}

// Add media uploader script
add_action('admin_enqueue_scripts', 'category_featured_image_scripts');
function category_featured_image_scripts($hook)
{
    if ($hook !== 'edit-tags.php' && $hook !== 'term.php') {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_script('category-featured-image', get_template_directory_uri() . '/js/category-featured-image.js', array('jquery'), '1.0', true);
}

/**
 * Handle Inline Featured Image Editing
 */
add_action('admin_enqueue_scripts', function ($hook) {
    // Also allow on edit-tags.php for service_cat
    if ($hook !== 'edit.php' && ($hook !== 'edit-tags.php' || get_current_screen()->taxonomy !== 'service_cat')) {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_script('admin-featured-image', get_template_directory_uri() . '/js/admin-featured-image.js', ['jquery'], '1.0', true);
});

/**
 * Add Columns to Service Categories Overview
 */
add_filter('manage_edit-service_cat_columns', function ($columns) {
    $new_columns = [];
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'cb') {
            $new_columns['term_featured_image'] = 'Image';
        }
    }
    // Fallback if 'cb' is missing
    if (!isset($new_columns['term_featured_image'])) {
        $new_columns = array_merge(['term_featured_image' => 'Image'], $columns);
    }
    return $new_columns;
});

add_action('manage_service_cat_custom_column', function ($content, $column_name, $term_id) {
    if ($column_name === 'term_featured_image') {
        $image_id = get_term_meta($term_id, 'category_featured_image', true);
        $nonce = wp_create_nonce('set_term_featured_image_column_nonce'); // Distinct nonce for terms

        $img = $image_id
            ? wp_get_attachment_image($image_id, [50, 50], false, ['style' => 'width:50px;height:50px;object-fit:cover;border-radius:4px;pointer-events:none;'])
            : '<span style="color:#ccc;font-size:20px;line-height:1;pointer-events:none;display:inline-block;padding:8px 12px;border:1px dashed #ccc;border-radius:4px;">+</span>';

        return sprintf(
            '<div class="term-featured-image-col-wrapper" data-term_id="%d" data-nonce="%s" style="cursor:pointer;" title="Click to edit image">%s</div>',
            $term_id,
            $nonce,
            $img
        );
    }
    return $content;
}, 10, 3);


/**
 * Ajax Handler for Setting Term Featured Image
 */
add_action('wp_ajax_set_term_featured_image_column', function () {
    check_ajax_referer('set_term_featured_image_column_nonce', '_ajax_nonce');

    $term_id = intval($_POST['term_id']);
    $image_id = intval($_POST['image_id']);

    if (!current_user_can('edit_term', $term_id)) {
        wp_send_json_error('Permission denied');
    }

    if ($image_id > 0) {
        update_term_meta($term_id, 'category_featured_image', $image_id);
    } else {
        delete_term_meta($term_id, 'category_featured_image');
    }

    wp_send_json_success();
});


add_action('wp_ajax_set_featured_image_column', function () {
    check_ajax_referer('set_featured_image_column_nonce', '_ajax_nonce');

    $post_id = intval($_POST['post_id']);
    $image_id = intval($_POST['image_id']);

    if (!current_user_can('edit_post', $post_id)) {
        wp_send_json_error('Permission denied');
    }

    if ($image_id > 0) {
        set_post_thumbnail($post_id, $image_id);
    } else {
        delete_post_thumbnail($post_id);
    }

    wp_send_json_success();
});


// Register Term Meta for REST API
add_action('rest_api_init', function () {
    // Renamed to service_cat
    register_rest_field('service_cat', 'services-cat-image', [
        'get_callback' => function ($term_arr) {
            $term_id = $term_arr['id'];
            $image_id = get_term_meta($term_id, 'category_featured_image', true);
            if ($image_id) {
                $sizes = get_intermediate_image_sizes();
                $sizes[] = 'full';
                $data = [];
                foreach ($sizes as $size) {
                    $src = wp_get_attachment_image_src($image_id, $size);
                    if ($src) {
                        // FIX: Use normal URL instead of missing helper
                        $data[$size] = $src[0];
                        
                        // Add WebP variant URL
                        $webp_url = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $src[0]);
                        $data[$size . '_webp'] = $webp_url;
                    }
                }
                return $data;
            }
            return null;
        },
        'update_callback' => null,
        'schema' => null,
    ]);
});

// Add mobile image size
add_action('after_setup_theme', function() {
    add_image_size('mobile', 600, 400, true);
});

// Generate WebP images on upload
add_filter('wp_generate_attachment_metadata', function($metadata, $attachment_id) {
    $file = get_attached_file($attachment_id);
    if (!file_exists($file)) return $metadata;
    
    $mime = get_post_mime_type($attachment_id);
    if (!in_array($mime, ['image/jpeg', 'image/png'])) {
        return $metadata;
    }

    // Original WebP
    $image_editor = wp_get_image_editor($file);
    if (!is_wp_error($image_editor)) {
        $webp_file = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $file);
        $image_editor->save($webp_file, 'image/webp');
    }

    // Sizes WebP
    if (isset($metadata['sizes'])) {
        $upload_dir = wp_upload_dir();
        $basepath = $upload_dir['basedir'] . '/' . dirname($metadata['file']) . '/';
        
        foreach ($metadata['sizes'] as $size => $size_data) {
            $size_file = $basepath . $size_data['file'];
            if (file_exists($size_file)) {
                $size_editor = wp_get_image_editor($size_file);
                if (!is_wp_error($size_editor)) {
                    $webp_size_file = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $size_file);
                    $size_editor->save($webp_size_file, 'image/webp');
                }
            }
        }
    }
    
    return $metadata;
}, 10, 2);

/**
 * Fügt der REST API Antwort Roh-Yoast-Templates (mit %% Variablen) in eigenen Datenfeldern hinzu.
 */
function add_raw_yoast_to_rest($response, $item)
{
    // Bestimme die ID
    $id = isset($item->term_id) ? $item->term_id : (isset($item->ID) ? $item->ID : 0);
    if ($id <= 0) return $response;

    // Roh-Templates aus der Datenbank abrufen
    $title_template = '';
    $desc_template = '';

    if (isset($item->term_id)) {
        // 1. Versuche Standard Term Meta (verschiedene Varianten)
        $title_template = get_term_meta($id, '_yoast_wpseo_title', true);
        if (!$title_template) $title_template = get_term_meta($id, 'wpseo_title', true);

        $desc_template = get_term_meta($id, '_yoast_wpseo_metadesc', true);
        if (!$desc_template) $desc_template = get_term_meta($id, 'wpseo_metadesc', true);
        if (!$desc_template) $desc_template = get_term_meta($id, '_yoast_wpseo_desc', true);
        if (!$desc_template) $desc_template = get_term_meta($id, 'wpseo_desc', true);

        // 2. Fallback: Alte Yoast-Option für Taxonomy Meta
        if (!$title_template || !$desc_template) {
            $tax_meta = get_option('wpseo_taxonomy_meta');
            // Check correct taxonomy name
            $tax = isset($item->taxonomy) ? $item->taxonomy : 'service_cat';
            if ($tax && isset($tax_meta[$tax][$id])) {
                if (!$title_template && isset($tax_meta[$tax][$id]['wpseo_title'])) {
                    $title_template = $tax_meta[$tax][$id]['wpseo_title'];
                }
                if (!$desc_template && isset($tax_meta[$tax][$id]['wpseo_desc'])) {
                    $desc_template = $tax_meta[$tax][$id]['wpseo_desc'];
                }
            }
        }

        // 3. Fallback: Globale Yoast-Defaults für die Taxonomy
        if (!$title_template || !$desc_template) {
            $wpseo_titles = get_option('wpseo_titles');
            $tax = isset($item->taxonomy) ? $item->taxonomy : 'service_cat';
            if ($wpseo_titles) {
                if (!$title_template && isset($wpseo_titles['title-tax-' . $tax])) {
                    $title_template = $wpseo_titles['title-tax-' . $tax];
                }
                if (!$desc_template && isset($wpseo_titles['metadesc-tax-' . $tax])) {
                    $desc_template = $wpseo_titles['metadesc-tax-' . $tax];
                }
            }
        }
    } else {
        $id = $item->ID;
        $title_template = get_post_meta($id, '_yoast_wpseo_title', true);
        if (!$title_template) $title_template = get_post_meta($id, 'wpseo_title', true);

        $desc_template = get_post_meta($id, '_yoast_wpseo_metadesc', true);
        if (!$desc_template) $desc_template = get_post_meta($id, '_yoast_wpseo_desc', true);
        if (!$desc_template) $desc_template = get_post_meta($id, 'wpseo_metadesc', true);
        if (!$desc_template) $desc_template = get_post_meta($id, 'wpseo_desc', true);

        // Fallback für Seiten-Defaults
        if (!$title_template || !$desc_template) {
            $wpseo_titles = get_option('wpseo_titles');
            if ($wpseo_titles) {
                if (!$title_template && isset($wpseo_titles['title-page'])) {
                    $title_template = $wpseo_titles['title-page'];
                }
                if (!$desc_template && isset($wpseo_titles['metadesc-page'])) {
                    $desc_template = $wpseo_titles['metadesc-page'];
                }
            }
        }
    }

    // Neue Variablen zur API-Antwort hinzufügen
    $response->data['yoast_title_raw'] = !empty($title_template) ? $title_template : '';
    $response->data['yoast_desc_raw'] = !empty($desc_template) ? $desc_template : '';

    return $response;
}

// Filter für die service_cat Taxonomy
add_filter('rest_prepare_service_cat', 'add_raw_yoast_to_rest', 99, 2);
// Filter für Seiten
add_filter('rest_prepare_page', 'add_raw_yoast_to_rest', 99, 2);

/**
 * Revisions Manager - Button to delete all revisions
 */
add_action('admin_menu', function () {
    add_management_page(
        'Revisions Manager',
        'Revisions Manager',
        'manage_options',
        'revisions-manager',
        'seopress_revisions_manager_page'
    );
});

function seopress_revisions_manager_page()
{
    global $wpdb;

    if (isset($_POST['delete_revisions']) && check_admin_referer('delete_revisions_action', 'revisions_nonce')) {
        $count = $wpdb->query("DELETE FROM $wpdb->posts WHERE post_type = 'revision'");
        echo '<div class="updated"><p>' . sprintf(__('Erfolgreich %d Revisionen gelöscht.', 'seopress'), $count) . '</p></div>';
    }

    $revision_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'revision'");
?>
    <div class="wrap">
        <h1>Revisionsmanager</h1>
        <p>Hier können Sie alle gespeicherten Beitrags-Revisionen löschen, um die Datenbank zu bereinigen.</p>
        <div class="card" style="max-width: 400px; padding: 20px;">
            <h2>Aktuelle Revisionen: <strong><?php echo (int)$revision_count; ?></strong></h2>
            <form method="post">
                <?php wp_nonce_field('delete_revisions_action', 'revisions_nonce'); ?>
                <input type="submit" name="delete_revisions" class="button button-primary" value="Alle Revisionen jetzt löschen" <?php echo $revision_count == 0 ? 'disabled' : ''; ?> onclick="return confirm('Möchten Sie wirklich ALLE Revisionen unwiderruflich löschen?');">
            </form>
        </div>
    </div>
<?php
}

/**
 * Allow .jfif image uploads in WordPress Media Library
 * JFIF (JPEG File Interchange Format) is a valid JPEG variant
 * that WordPress/Apache doesn't recognize by default.
 */

// 1. Register .jfif as allowed upload MIME type
add_filter('upload_mimes', function ($mimes) {
    $mimes['jfif'] = 'image/jpeg';
    return $mimes;
});

// 2. Fix WordPress strict file-type check that rejects .jfif
//    Without this, WordPress returns "Sorry, this file type is not permitted"
add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename, $mimes) {
    if (substr($filename, -5) === '.jfif') {
        $data['ext']  = 'jfif';
        $data['type'] = 'image/jpeg';
        $data['proper_filename'] = $filename;
    }
    return $data;
}, 10, 4);

// 3. Serve correct Content-Type header when .jfif files are accessed directly
add_action('send_headers', function () {
    if (isset($_SERVER['REQUEST_URI']) && preg_match('/\.jfif$/i', $_SERVER['REQUEST_URI'])) {
        header('Content-Type: image/jpeg');
    }
});