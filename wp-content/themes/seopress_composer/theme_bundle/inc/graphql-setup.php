<?php

/**
 * GraphQL Setup & Server Compatibility
 * 
 * Handles:
 * 1. CORS & Auth Headers (World4You/Headless logic)
 * 2. Schema Extensions for Open Graph (replacing REST API filters)
 */

if (!defined('ABSPATH')) {
  exit;
}

/**
 * ==========================================
 * 1. SERVER COMPATIBILITY (CORS & AUTH)
 * ==========================================
 */

function seopress_gql_cors_headers()
{
  $origin = get_http_origin();
  if (!$origin) {
    $origin = '*';
  }

  header("Access-Control-Allow-Origin: $origin");
  header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Credentials: true");
  header("Access-Control-Allow-Headers: Origin, X-Requested-With, X-WP-Nonce, Content-Type, Accept, Authorization");

  if ('OPTIONS' == $_SERVER['REQUEST_METHOD']) {
    status_header(200);
    exit();
  }
}
add_action('init', 'seopress_gql_cors_headers');

function seopress_gql_fix_auth_header()
{
  if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    return;
  }
  if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $_SERVER['HTTP_AUTHORIZATION'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
  }
}
add_action('init', 'seopress_gql_fix_auth_header', 1);

function seopress_gql_security_headers()
{
  header("X-Content-Type-Options: nosniff");
  header("X-Frame-Options: SAMEORIGIN");
}
add_action('send_headers', 'seopress_gql_security_headers');


/**
 * ==========================================
 * 2. GRAPHQL SCHEMA EXTENSIONS (OG DATA)
 * ==========================================
 */

add_action('graphql_register_types', function () {

  // Define the generic 'OGData' type
  register_graphql_object_type('OGData', [
    'description' => 'Open Graph Meta Data',
    'fields' => [
      'ogTitle' => ['type' => 'String'],
      'ogDescription' => ['type' => 'String'],
      'ogImage' => ['type' => 'String'],
      'ogUrl' => ['type' => 'String'],
      'ogType' => ['type' => 'String'],
    ]
  ]);

  // Register field 'ogData' on types
  $types_to_extend = ['Page', 'Post'];

  // Extend custom taxonomies only if they exist
  if (taxonomy_exists('ankauf_kategorie')) {
    $types_to_extend[] = 'AnkaufKategorie';
  }
  if (taxonomy_exists('menu_category')) {
    // Assuming Graphql name matches
    // $types_to_extend[] = 'MenuCategory'; 
  }

  foreach ($types_to_extend as $type) {
    register_graphql_field($type, 'ogData', [
      'type' => 'OGData',
      'description' => 'Open Graph data for this object',
      'resolve' => function ($root, $args, $context, $info) {
        // Determine ID based on object type
        $id = 0;
        $object_type = 'post'; // default

        if (isset($root->databaseId)) {
          $id = $root->databaseId;
        } elseif (isset($root->term_id)) { // Generic term object
          $id = $root->term_id;
          $object_type = 'term';
        } elseif (isset($root->termId)) { // GraphQL term object often has termId
          $id = $root->termId;
          $object_type = 'term';
        }

        // Fallback for missing ID
        if (!$id) return null;

        // --- Resolution Logic (similar to previous REST logic) ---
        $og_data = [];

        // 1. OG Title
        $og_title = '';
        if ($object_type === 'term') {
          $og_title = get_term_meta($id, '_yoast_wpseo_opengraph-title', true);
          if (!$og_title) $og_title = get_term_meta($id, '_yoast_wpseo_title', true);
          if (!$og_title) $og_title = isset($root->name) ? $root->name : '';
        } else {
          $og_title = get_post_meta($id, '_yoast_wpseo_opengraph-title', true);
          if (!$og_title) $og_title = get_post_meta($id, '_yoast_wpseo_title', true);
          if (!$og_title) $og_title = get_the_title($id);
        }
        $og_data['ogTitle'] = $og_title;

        // 2. OG Description
        $og_desc = '';
        if ($object_type === 'term') {
          $og_desc = get_term_meta($id, '_yoast_wpseo_opengraph-description', true);
          if (!$og_desc) $og_desc = get_term_meta($id, '_yoast_wpseo_metadesc', true);
          if (!$og_desc) $og_desc = term_description($id);
        } else {
          $og_desc = get_post_meta($id, '_yoast_wpseo_opengraph-description', true);
          if (!$og_desc) $og_desc = get_post_meta($id, '_yoast_wpseo_metadesc', true);
          if (empty($og_desc) && isset($root->content)) {
            // Rough excerpt generation from raw content if possible
            // Note: $root->content might be rendered HTML or raw depending on context
            $raw_content = isset($root->rawContent) ? $root->rawContent : wp_strip_all_tags($root->content);
            $og_desc = wp_trim_words($raw_content, 20);
          }
        }
        $og_data['ogDescription'] = $og_desc;

        // 3. OG Image
        $og_image = '';
        if ($object_type === 'term') {
          $og_image = get_term_meta($id, '_yoast_wpseo_opengraph-image', true);
          if (!$og_image) {
            $image_id = get_term_meta($id, 'category_featured_image', true);
            if ($image_id) $og_image = wp_get_attachment_image_url($image_id, 'full');
          }
        } else {
          $og_image = get_post_meta($id, '_yoast_wpseo_opengraph-image', true);
          if (!$og_image && has_post_thumbnail($id)) {
            $og_image = get_the_post_thumbnail_url($id, 'full');
          }
        }
        // WebP Conversion check (Removed in Frontend Theme to avoid dependency issues)
        // if (!empty($og_image) && class_exists('\\SeopressApi\\Helpers\\WebPHelper')) {
        //   $og_image = \SeopressApi\Helpers\WebPHelper::get_webp_url($og_image);
        // }
        $og_data['ogImage'] = $og_image;

        // 4. OG URL
        $og_data['ogUrl'] = ($object_type === 'term') ? get_term_link($id) : get_permalink($id);

        // 5. OG Type
        $og_data['ogType'] = 'website';
        if ($object_type === 'post') {
          // Check post type if needed, assume 'article' for generic posts
          $og_data['ogType'] = 'article';
        }

        return $og_data;
      }
    ]);
  }
});
