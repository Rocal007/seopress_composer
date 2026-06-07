<?php

namespace SeopressComposer\Services;

use SeopressComposer\Models\SiteSettings_Data;

/**
 * RemoteRouteService - Handles Remote API URL resolution
 */
class RemoteRouteService
{
  private SiteSettings_Data $siteSettingsData;

  public function __construct(SiteSettings_Data $siteSettingsData)
  {
    $this->siteSettingsData = $siteSettingsData;
  }

  /**
   * Get Remote URL for the page
   *
   * Priority:
   *   1.   Term ACF field (correct 'taxonomy_id' format for custom terms)
   *   1.5. Parent page ACF field (for Location pages inheriting from Service Category)
   *   1.7. First child service page in category (for category archives without own remote URL)
   *   2.   Homepage ACF field
   *   3.   Global 'basicremote' option + slug
   */
  public function get_remote_url($page_id = null, $allow_fallback_to_home = true): string
  {
    $is_explicit = ($page_id !== null);

    // Always resolve the queried object so we can detect taxonomy context
    // even when an explicit page_id (= term_id) was passed by a model.
    $current_obj = get_queried_object();

    if (!$page_id) {
      $page_id     = get_queried_object_id();
      $queried_obj = $current_obj;
    } else {
      // Use term context if the explicit id matches the queried term_id.
      // This handles the case where ContextController passes term_id as page_id.
      if ($current_obj instanceof \WP_Term && (int)$current_obj->term_id === (int)$page_id) {
        $queried_obj = $current_obj;
      } else {
        $queried_obj = null;
      }
    }

    // 1. Try Local ACF field — correct identifier per object type
    if ($queried_obj instanceof \WP_Term) {
      // Custom taxonomy terms need format: 'taxonomy_termid'
      $acf_id = $queried_obj->taxonomy . '_' . $queried_obj->term_id;
    } elseif (!$is_explicit && is_category()) {
      $acf_id = 'category_' . $page_id;
    } else {
      $acf_id = $page_id;
    }

    $remote_url = (string) get_field('remote_page', $acf_id);

    // 1.5 Try Parent Page (location child pages inherit from service page)
    if (empty($remote_url) && !($queried_obj instanceof \WP_Term) && !is_category()) {
      $post = get_post($page_id);
      if ($post && $post->post_parent) {
        $remote_url = (string) get_field('remote_page', $post->post_parent);

        // If still empty, try grandparent (e.g. location page 2 levels deep)
        if (empty($remote_url)) {
          $parent_post = get_post($post->post_parent);
          if ($parent_post && $parent_post->post_parent) {
            $remote_url = (string) get_field('remote_page', $parent_post->post_parent);
          }
        }
      }
    }

    // 1.7 Taxonomy term fallback: resolve remote URL via multiple strategies
    if (empty($remote_url) && ($queried_obj instanceof \WP_Term)) {
      // Strategy A: pages still assigned to this taxonomy (legacy compatibility)
      $child_pages = get_posts([
        'post_type'      => 'page',
        'posts_per_page' => 1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
        'tax_query'      => [[
          'taxonomy' => $queried_obj->taxonomy,
          'field'    => 'term_id',
          'terms'    => $queried_obj->term_id,
        ]],
        'fields' => 'ids',
      ]);

      if (!empty($child_pages)) {
        $child_id   = $child_pages[0];
        $remote_url = (string) get_field('remote_page', $child_id);

        // Try child's parent if child also has no direct remote URL
        if (empty($remote_url)) {
          $child_post = get_post($child_id);
          if ($child_post && $child_post->post_parent) {
            $remote_url = (string) get_field('remote_page', $child_post->post_parent);
          }
        }
      }

      // Strategy B (new parent-child): find WP page with same slug as this term
      // e.g. term slug 'umzug' → WP page /umzug/ → or its first child /umzug/umzuege/
      if (empty($remote_url)) {
        $term_slug  = $queried_obj->slug;
        $slug_page  = get_page_by_path($term_slug);

        if ($slug_page) {
          // Check the parent page itself
          $remote_url = (string) get_field('remote_page', $slug_page->ID);

          // If no remote on parent, try first child (the actual service page)
          if (empty($remote_url)) {
            $first_child = get_posts([
              'post_type'      => 'page',
              'post_parent'    => $slug_page->ID,
              'posts_per_page' => 1,
              'orderby'        => 'menu_order',
              'order'          => 'ASC',
              'meta_key'       => 'remote_page',
              'fields'         => 'ids',
            ]);
            if (!empty($first_child)) {
              $remote_url = (string) get_field('remote_page', $first_child[0]);
            }
          }
        }
      }
    }

    // 2. Fallback to Home Page
    if ($allow_fallback_to_home && empty($remote_url)) {
      $home_id = get_option('page_on_front');
      if ($home_id) {
        $remote_url = (string) get_field('remote_page', $home_id);
      }
    }

    // 3. Final Fallback to Site Settings 'basicremote' + slug
    if (empty($remote_url)) {
      $base_remote = $this->siteSettingsData->get_option('basicremote');
      if ($base_remote) {
        $slug = get_post_field('post_name', $page_id);

        if (empty($slug) && $page_id == get_option('page_on_front')) {
          $slug = 'startseite';
        }

        if (!empty($slug)) {
          $base_remote = trailingslashit($base_remote);
          $remote_url  = $base_remote . 'pages?slug=' . $slug;
        }
      }
    }

    return (string) $remote_url;
  }
}
