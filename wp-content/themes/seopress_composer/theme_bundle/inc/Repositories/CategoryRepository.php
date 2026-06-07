<?php

namespace SeopressComposer\Repositories;

use WP_Term;

use SeopressComposer\Helpers\PageHelper;

class CategoryRepository
{
  private PageHelper $pageHelper;

  private array $attachment_url_cache = [];


  // In-memory cache to prevent re-processing within the same request
  private array $district_check_cache = [];


  public function __construct(PageHelper $pageHelper)
  {
    $this->pageHelper = $pageHelper;
    if (function_exists('add_action')) {
      add_action('save_post', [$this, 'on_save_post'], 10, 3);
      add_action('edited_service_category', [$this, 'clear_district_cache']);
      add_action('edited_district', [$this, 'clear_district_cache']);
    }
  }

  /**
   * Event Listener: Clear cache on post save
   */
  public function on_save_post($post_id, $post, $update): void
  {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!$post || $post->post_type !== 'page') return;

    // Clear cache for the parent of this page (links to this page as a sibling)
    if ($post->post_parent) {
      delete_transient('district_service_links_parent_' . $post->post_parent);
    }

    // Clear cache for this page itself (if it is a parent to others)
    delete_transient('district_service_links_parent_' . $post_id);
  }

  /**
   * Event Listener: Clear district caches on term update
   */
  public function clear_district_cache(): void
  {
    // Clear object cache group
    if (function_exists('wp_cache_flush_group')) {
      wp_cache_flush_group('districts');
    } else {
      wp_cache_flush(); // Fallback if group flushing isn't supported/configured
    }
  }

  /**
   * Public getter that returns cached data.
   * Calculates on-demand if cache is missing.
   */
  public function get_sibling_links(int $current_page_id): array
  {
    $parent_page_id = wp_get_post_parent_id($current_page_id);
    if (!$parent_page_id) {
      return [];
    }

    $cache_key = 'district_service_links_parent_' . $parent_page_id;
    $siblings_data = get_transient($cache_key);

    if ($siblings_data === false) {
      // Cache Miss: Calculate immediately and store
      return $this->calculate_sibling_links($current_page_id);
    }

    return $siblings_data ?: [];
  }


  /**
   * Internal logic to calculate and cache siblings.
   * Can accept pre-indexed districts to optimize loops.
   */
  private function calculate_sibling_links(int $current_page_id, ?array $districts_by_link = null): array
  {
    $parent_page_id = wp_get_post_parent_id($current_page_id);
    if (!$parent_page_id) {
      return [];
    }

    $cache_key = 'district_service_links_parent_' . $parent_page_id;

    // Fetch siblings
    $siblings = get_pages([
      'child_of'    => $parent_page_id,
      'parent'      => $parent_page_id,
      'sort_column' => 'post_title',
      'post_status' => 'publish'
    ]);

    $siblings_data = [];

    if (!empty($siblings)) {
      // If districts weren't passed (not called from Cron), fetch them now
      if ($districts_by_link === null) {
        $districts = $this->get_districts();
        $districts_by_link = [];
        foreach ($districts as $d) {
          if (!empty($d['link'])) {
            $districts_by_link[$d['link']] = $d;
          }
        }
      }

      foreach ($siblings as $sibling) {
        $perm = get_permalink($sibling->ID);

        // O(1) Lookup instead of nested loop
        if (isset($districts_by_link[$perm]) && !empty($districts_by_link[$perm]['karte'])) {
          $district = $districts_by_link[$perm];
          $siblings_data[] = [
            'id'        => $sibling->ID,
            'name'      => $sibling->post_title,
            'link'      => $perm,
            'thumbnail' => $district['thumbnail'] ?? '',
            'karte'     => $district['karte'],
          ];
        }
      }
    }

    // Cache the result for 24 hours (transient)
    set_transient($cache_key, $siblings_data, 24 * HOUR_IN_SECONDS);

    return $siblings_data;
  }



  public function is_district_category(int $cat_id, string $cat_name): bool
  {
    if (isset($this->district_check_cache[$cat_id])) {
      return $this->district_check_cache[$cat_id];
    }

    // Check term meta field first (Most accurate)
    if (get_field('karte', 'category_' . $cat_id)) {
      $this->district_check_cache[$cat_id] = true;
      return true;
    }

    // Check for 4-digit zip code
    if (preg_match('/\d{4}/', $cat_name)) {
      $this->district_check_cache[$cat_id] = true;
      return true;
    }

    $this->district_check_cache[$cat_id] = false;
    return false;
  }



  public function get_remote_url(): string
  {
    $api_url = $this->pageHelper->get_option('services_api_endpoint');

    if (!$api_url) {
      $remote_url = $this->pageHelper->get_option('basicremote');
      if ($remote_url) {
        $remote_url = rtrim($remote_url, '/');
        $remote_url = preg_replace('#/wp-json/wp/v2/?$#', '', $remote_url);
        $api_url = $remote_url . '/wp-json/custom/v1/pages';
      }
    }

    return $api_url ?: '';
  }

  public function get_districts(array $args = []): array
  {
    // Serialize args to create unique key
    $cache_key = 'districts_with_details_' . md5(serialize($args));

    $districts = wp_cache_get($cache_key, 'districts');
    if (false === $districts) {
      $args = wp_parse_args($args, [
        'orderby'    => 'name',
        'order'      => 'DESC',
        'parent'     => 0,
        'hide_empty' => 0,
        'exclude'    => 1,
      ]);

      $categories = $this->get_categories($args);
      $districts = [];

      foreach ($categories as $category) {
        if ($this->is_district_category($category->term_id, $category->name)) {
          $orte_detail = $this->get_orte_detail($category);
          $districts[] = [
            'id'          => $category->term_id,
            'name'        => $category->name,
            'link'        => $this->get_category_link($category->term_id),
            'thumbnail'   => '',
            'wappen'      => $this->get_wappen($category),
            'orte_detail' => $orte_detail['details'],
            'orte_string' => $orte_detail['orte_string'],
            'karte'       => $this->get_karte($category),
            'location'    => $category->name,
          ];
        }
      }

      wp_cache_set($cache_key, $districts, 'districts', 3600 * 24 * 365);
    }
    return $districts ?: [];
  }

  public function get_categories(array $args = []): array
  {
    $categories = get_categories($args);
    if (!empty($categories) && !is_wp_error($categories)) {
      update_term_cache($categories, 'category');
    }
    return is_wp_error($categories) ? [] : $categories;
  }

  public function get_category_link(int $term_id): string
  {
    return get_category_link($term_id);
  }

  private function resolve_attachment_url($image, $size = 'full'): string
  {
    if (empty($image)) {
      return '';
    }

    // 1. Array (ACF object)
    if (is_array($image)) {
      if ($size !== 'full' && isset($image['sizes'][$size])) {
        return $image['sizes'][$size];
      }
      return $image['url'] ?? '';
    }

    // 2. String URL
    if (is_string($image) && !is_numeric($image)) {
      return $image;
    }

    // 3. Numeric ID
    if (is_numeric($image)) {
      $cache_key = $image . '_' . $size;
      if (isset($this->attachment_url_cache[$cache_key])) {
        return $this->attachment_url_cache[$cache_key];
      }

      $url = '';
      if ($size !== 'full') {
        $url = wp_get_attachment_image_url((int)$image, $size);
      }

      if (!$url) {
        $url = wp_get_attachment_url((int)$image);
      }

      $this->attachment_url_cache[$cache_key] = $url ?: '';
      return $this->attachment_url_cache[$cache_key];
    }

    return '';
  }



  public function get_wappen(WP_Term $category): string
  {
    $image = get_field('wappen', 'category_' . $category->term_id, false);
    return $this->resolve_attachment_url($image);
  }

  public function get_karte(WP_Term $category): string
  {
    $image = get_field('karte', 'category_' . $category->term_id, false);
    return $this->resolve_attachment_url($image, 'karte');
  }

  public function get_orte_detail(WP_Term $category): array
  {
    $details = [];
    $ort_names = [];

    if (have_rows('orte_repeater', 'category_' . $category->term_id)) {
      while (have_rows('orte_repeater', 'category_' . $category->term_id)) {
        the_row();
        $name = get_sub_field('ort_name');
        if ($name) {
          $details[] = [
            'name' => $name,
            'zip'  => get_sub_field('plz'),
            'link' => get_sub_field('link')
          ];
          $ort_names[] = $name;
        }
      }
    }

    return [
      'details'     => $details,
      'orte_string' => implode(', ', $ort_names)
    ];
  }

  /**
   * Get all pages that have service_category taxonomy terms assigned.
   * Used for building service category menus.
   * 
   * @return array Array of WP_Post objects
   */
  public function get_all_service_pages(): array
  {
    $cache_key = 'all_service_pages_v2';
    $cached = wp_cache_get($cache_key, 'service_pages');

    if ($cached !== false) {
      return $cached;
    }

    $args = [
      'post_type'      => 'page',
      'post_status'    => 'publish',
      'posts_per_page' => -1,
      'post_parent'    => 0,
      'orderby'        => 'menu_order',
      'order'          => 'ASC',
      'tax_query'      => [
        [
          'taxonomy' => 'service_category',
          'operator' => 'EXISTS'
        ]
      ]
    ];

    $pages = get_posts($args);

    // Cache for 1 hour
    wp_cache_set($cache_key, $pages, 'service_pages', 3600);

    return $pages;
  }


  /**
   * Get cached service categories to avoid redundant queries
   * refactored to cache the result of get_terms('service_category')
   */
  public function get_cached_service_categories(): array
  {
    $cache_key = 'all_service_categories_terms';
    $terms = wp_cache_get($cache_key, 'categories');

    if ($terms === false) {
      $terms = get_terms([
        'taxonomy'   => 'service_category',
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC'
      ]);

      if (is_wp_error($terms)) {
        return [];
      }

      // 1 Hour Cache
      wp_cache_set($cache_key, $terms, 'categories', 3600);
    }

    return $terms;
  }

  /**
   * Get sibling terms for a specific taxonomy term
   * using PHP filtering for service_category to reuse cache
   * 
   * @param string $taxonomy Taxonomy name
   * @param int $current_term_id ID of term to exclude
   * @param int $limit Maximum number of terms to return
   * @return array List of WP_Term objects
   */
  public function getSiblingTerms(string $taxonomy, int $current_term_id, int $limit = 12): array
  {
    // Optimization for service_category: Use centralized cache
    if ($taxonomy === 'service_category') {
      $all_terms = $this->get_cached_service_categories();
      $siblings = [];
      $count = 0;

      foreach ($all_terms as $term) {
        if ($term->term_id !== $current_term_id) {
          $siblings[] = $term;
          $count++;
          if ($count >= $limit) break;
        }
      }
      return $siblings;
    }

    // Default behavior for other taxonomies (like district)
    $cache_key = 'sibling_terms_' . $taxonomy . '_' . $current_term_id . '_' . $limit;
    $cached = wp_cache_get($cache_key, 'categories');

    if ($cached !== false) {
      return $cached;
    }

    $args = [
      'taxonomy' => $taxonomy,
      'hide_empty' => false,
      'exclude' => [$current_term_id],
      'number' => $limit,
      'orderby' => 'name',
      'order' => 'ASC'
    ];

    $terms = get_terms($args);

    if (is_wp_error($terms)) {
      return [];
    }

    // Cache for 1 hour
    wp_cache_set($cache_key, $terms, 'categories', 3600);

    return $terms;
  }
}
