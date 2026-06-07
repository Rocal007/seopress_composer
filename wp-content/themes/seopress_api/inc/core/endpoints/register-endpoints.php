<?php
// custom-endpoints.php
// All custom REST API endpoints with Object Cache

if (!defined('ABSPATH')) exit;

// Register all custom endpoints
add_action('rest_api_init', function () {
    
    // Synonyms endpoint
    register_rest_route('custom/v1', '/synonyms', array(
        'methods' => 'GET',
        'callback' => 'aso_api_get_synonyms',
        'permission_callback' => '__return_true',
    ));
    
    // Pages by template endpoint
    register_rest_route('custom/v1', '/pages/(?P<template>[\w-]+)', array(
        'methods' => 'GET',
        'callback' => 'custom_template_pages_endpoint_callback',
        'permission_callback' => '__return_true',
    ));
    
    // Backlinks endpoint
    register_rest_route('custom/v1', '/backlinks', array(
        'methods'  => 'GET',
        'callback' => 'get_backlinks_data',
        'permission_callback' => '__return_true',
    ));
    
    // Service Categories endpoint
    register_rest_route('custom/v1', '/service-categories', array(
        'methods' => 'GET',
        'callback' => 'custom_service_categories_endpoint_callback',
        'permission_callback' => '__return_true',
    ));
    
    // Site Structure endpoint (Categories, Pages, Static Pages)
    register_rest_route('custom/v1', '/site-structure', array(
        'methods'  => 'GET',
        'callback' => 'seopress_get_site_structure_endpoint_callback',
        'permission_callback' => '__return_true'
    ));
});

// ==================== SYNONYMS ENDPOINT ====================

function aso_api_get_synonyms($request) {
    $cache_key = 'aso_synonyms_data';
    $cached_data = wp_cache_get($cache_key, 'custom_api');
    
    if ($cached_data !== false) {
        return rest_ensure_response($cached_data);
    }
    
    // Daten aus der Datenbank holen
    $data = get_option('aso_api_words_lists', array('words' => array(), 'lists' => array()));

    // Normalize to arrays
    $words = isset($data['words']) ? (array)$data['words'] : array();
    $lists = isset($data['lists']) ? (array)$data['lists'] : array();
    
    $response_data = array('words' => $words, 'lists' => $lists);
    
    // Daten für 1 Stunde im Object Cache speichern
    wp_cache_set($cache_key, $response_data, 'custom_api', HOUR_IN_SECONDS);
    
    return rest_ensure_response($response_data);
}

// ==================== PAGES BY TEMPLATE ENDPOINT ====================

function custom_template_pages_endpoint_callback($data) {
    $template = $data['template'];
    $cache_key = 'custom_pages_' . sanitize_key($template);
    $cached_data = wp_cache_get($cache_key, 'custom_api');
    
    if ($cached_data !== false) {
        return $cached_data;
    }

    // Query pages with the specified template
    $pages_query = new WP_Query(array(
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'post_type' => 'page',
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => '_wp_page_template',
                'value' => $template . '.php',
            )
        ),
        'posts_per_page' => -1,
    ));

    $pages = array();

    if ($pages_query->have_posts()) {
        while ($pages_query->have_posts()) {
            $pages_query->the_post();
            $pages[] = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'content' => get_the_content(),
                'url' => get_permalink(),
                'api_endpoint' => rest_url('wp/v2/pages/' . get_the_ID()),
            );
        }
        wp_reset_postdata();
    }

    // Im Object Cache speichern
    wp_cache_set($cache_key, $pages, 'custom_api', HOUR_IN_SECONDS);

    return $pages;
}

// ==================== BACKLINKS ENDPOINT ====================

function get_backlinks_data() {
    $cache_key = 'custom_backlinks_data';
    $cached_data = wp_cache_get($cache_key, 'custom_api');
    
    if ($cached_data !== false) {
        return $cached_data;
    }

    $page_id = 653;
    $backlinks = get_field('backlink_seiten', $page_id);

    if (!$backlinks) {
        return new WP_Error('no_backlinks', 'No backlink data found', array('status' => 404));
    }

    $formatted_backlinks = array_map(function ($item) {
        return array(
            'link_name'  => $item['link_name'] ?? '',
            'webaddress' => $item['webadresse_url'] ?? '',
            'color1'     => $item['farbe_1'] ?? '',
            'color2'     => $item['farbe_2'] ?? '',
        );
    }, $backlinks);

    // Im Object Cache speichern
    wp_cache_set($cache_key, $formatted_backlinks, 'custom_api', HOUR_IN_SECONDS);

    return $formatted_backlinks;
}

// ==================== SERVICE CATEGORIES ENDPOINT ====================

function custom_service_categories_endpoint_callback($request) {
    $cache_key = 'custom_service_categories';
    $cached_data = wp_cache_get($cache_key, 'custom_api');
    
    if ($cached_data !== false) {
        return $cached_data;
    }

    $terms = get_terms(array(
        'taxonomy' => 'service_cat',
        'hide_empty' => false,
    ));

    $categories = array();

    if (!is_wp_error($terms) && !empty($terms)) {
        foreach ($terms as $term) {
            $categories[] = array(
                'id'           => $term->term_id,
                'name'         => $term->name,
                'slug'         => $term->slug,
                'link'         => get_term_link($term),
                'api_endpoint' => rest_url('wp/v2/service_cat/' . $term->term_id),
                'remote_url'   => rest_url('wp/v2/service_cat/' . $term->term_id), // Die zuständige remote url
            );
        }
    }

    // Im Object Cache speichern
    wp_cache_set($cache_key, $categories, 'custom_api', HOUR_IN_SECONDS);

    return $categories;
}

// ==================== CACHE MANAGEMENT ====================

// Clear cache when data changes
function aso_clear_synonyms_cache() {
    wp_cache_delete('aso_synonyms_data', 'custom_api');
}

// Clear all custom endpoints cache
function clear_custom_endpoints_cache() {
    // Clear synonyms cache
    wp_cache_delete('aso_synonyms_data', 'custom_api');
    
    // Clear pages cache for all templates (pattern deletion)
    global $wp_object_cache;
    if (isset($wp_object_cache->cache['custom_api'])) {
        foreach (array_keys($wp_object_cache->cache['custom_api']) as $key) {
            if (strpos($key, 'custom_pages_') === 0) {
                wp_cache_delete($key, 'custom_api');
            }
        }
    }
    
    // Clear backlinks cache
    wp_cache_delete('custom_backlinks_data', 'custom_api');
    
    // Clear service categories cache
    wp_cache_delete('custom_service_categories', 'custom_api');
}

// Hook into various update events
add_action('update_option_aso_api_words_lists', 'aso_clear_synonyms_cache');
add_action('add_option_aso_api_words_lists', 'aso_clear_synonyms_cache');
add_action('save_post_page', 'clear_custom_endpoints_cache');
add_action('acf/save_post', 'clear_custom_endpoints_cache');
add_action('created_service_cat', 'clear_custom_endpoints_cache');
add_action('edited_service_cat', 'clear_custom_endpoints_cache');
add_action('delete_service_cat', 'clear_custom_endpoints_cache');

// ==================== OPTIONAL: CACHE GROUPS CLEAR ====================

// Nuclear option: Clear entire cache group
function clear_custom_api_cache_group() {
    wp_cache_flush_group('custom_api');
}

// ==================== SITE STRUCTURE ENDPOINT ====================

function seopress_get_site_structure_endpoint_callback() {
    $cache_key = 'custom_site_structure';
    $cached_data = wp_cache_get($cache_key, 'custom_api');
    
    if ($cached_data !== false) {
        return rest_ensure_response($cached_data);
    }

    $response = [
        'services'     => [],
        'static_pages' => []
    ];

    // 1. Leistungsseiten (Hauptseiten) — parent_slug direkt am Service
    $services_query = new WP_Query([
        'post_type'      => 'page',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
        'meta_key'       => '_wp_page_template',
        'meta_value'     => 'template-hauptseiten.php'
    ]);

    if ($services_query->have_posts()) {
        while ($services_query->have_posts()) {
            $services_query->the_post();
            $post_id = get_the_ID();

            // Determine parent_slug from service_cat taxonomy (remote source of truth)
            $terms = wp_get_post_terms($post_id, 'service_cat');
            $parent_slug = '';
            $parent_name = '';
            if (!empty($terms) && !is_wp_error($terms)) {
                $parent_slug = $terms[0]->slug;
                $parent_name = $terms[0]->name;
            }

            $response['services'][] = [
                'id'           => $post_id,
                'title'        => get_the_title(),
                'slug'         => get_post_field('post_name', $post_id),
                'parent_slug'  => $parent_slug,   // <-- used for WP post_parent hierarchy
                'parent_name'  => $parent_name,   // <-- used for grouping in admin UI
                'template'     => get_page_template_slug($post_id),
                'api_endpoint' => rest_url('wp/v2/pages/' . $post_id)  // native WP REST — returns full ACF data
            ];
        }
        wp_reset_postdata();
    }

    // 3. Statische Seiten
    $static_slugs = ['startseite', 'kontakt', 'impressum', 'datenschutz', 'datenschutzerklaerung', 'agb', 'faq', 'preise-kosten', 'ueber-uns', 'videos', 'locations'];
    
    $front_page_id = get_option('page_on_front');
    if ($front_page_id) {
        $response['static_pages']['startseite'] = [
            'id' => $front_page_id,
            'title' => get_the_title($front_page_id),
            'api_endpoint' => rest_url("wp/v2/pages/{$front_page_id}")
        ];
    }

    $static_query = new WP_Query([
        'post_type' => 'page',
        'posts_per_page' => -1,
        'post_name__in' => $static_slugs
    ]);

    if ($static_query->have_posts()) {
        while ($static_query->have_posts()) {
            $static_query->the_post();
            $post_id = get_the_ID();
            $slug = get_post_field('post_name', $post_id);
            
            if ($slug === 'datenschutzerklaerung') $slug = 'datenschutz';
            if ($slug === 'agb') $slug = 'datenschutz';
            if ($slug === 'preise-kosten') $slug = 'kosten';
            
            if ($post_id != $front_page_id) {
                $response['static_pages'][$slug] = [
                    'id' => $post_id,
                    'title' => get_the_title(),
                    'api_endpoint' => rest_url("wp/v2/pages/{$post_id}")
                ];
            }
        }
        wp_reset_postdata();
    }

    wp_cache_set($cache_key, $response, 'custom_api', HOUR_IN_SECONDS);
    return rest_ensure_response($response);
}