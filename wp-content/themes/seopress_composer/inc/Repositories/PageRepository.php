<?php

namespace SeopressComposer\Repositories;


/**
 * PageRepository
 * 
 * Handles data retrieval for Pages, SEO titles, and Meta data.
 */
class PageRepository
{
  // CRITICAL: Static cache to persist across all instances during a request
  // This prevents 700+ WP_Term::get_instance queries when PageHelper creates multiple instances
  private static array $cache = [];

  /**
   * Get the SEO Title using various strategies (Yoast API, WP Core, Raw Meta)
   */
  public function get_seo_title(int $page_id, string $type = 'post'): string
  {
    $cache_key = "seo_title_{$type}_{$page_id}";
    if (isset(self::$cache[$cache_key])) {
      return self::$cache[$cache_key];
    }

    $is_current = $this->is_current_page($page_id, $type);

    // 1. Try Yoast Surface API first
    if (function_exists('YoastSEO')) {
      $context = null;
      if ($type === 'term') {
        $context = \YoastSEO()->meta->for_term($page_id);
      } else {
        $context = $is_current ? \YoastSEO()->meta->for_current_page() : \YoastSEO()->meta->for_post($page_id);
      }

      if ($context) {
        $title = $context->title ?? ($context->presentation->title ?? '');
        if (!empty($title)) {
          return self::$cache[$cache_key] = $this->clean_title($title);
        }
      }
    }

    // 2. Priority: Check Raw Meta (Custom Override Logic)
    $raw_title = $this->get_yoast_meta($page_id, '_yoast_wpseo_title', $type);
    if (!empty($raw_title)) {
      return self::$cache[$cache_key] = $this->clean_title($raw_title);
    }

    // 3. Fallback to WP Document Title (only for current page)
    if ($is_current && $type === 'post') {
      $title = wp_get_document_title();
      if (!empty($title)) {
        return self::$cache[$cache_key] = $this->clean_title($title);
      }
    }

    // 4. Final Fallback: Post Title / Term Title (Raw)
    $title = ($type === 'term') ? get_term($page_id)->name ?? '' : get_post_field('post_title', $page_id);

    return self::$cache[$cache_key] = $this->clean_title((string) $title);
  }

  /**
   * Get the SEO Description
   */
  public function get_seo_description(int $page_id, string $type = 'post'): string
  {
    $cache_key = "seo_description_{$type}_{$page_id}";
    if (isset(self::$cache[$cache_key])) {
      return self::$cache[$cache_key];
    }

    $is_current = $this->is_current_page($page_id, $type);

    // 1. Try Yoast Surface API
    if (function_exists('YoastSEO')) {
      $context = null;
      if ($type === 'term') {
        $context = \YoastSEO()->meta->for_term($page_id);
      } else {
        $context = $is_current ? \YoastSEO()->meta->for_current_page() : \YoastSEO()->meta->for_post($page_id);
      }

      if ($context) {
        $desc = $context->description ?? ($context->presentation->description ?? '');
        if (!empty($desc)) {
          return self::$cache[$cache_key] = (string) $desc;
        }
      }
    }

    // 2. Fallback to parsed raw meta
    return self::$cache[$cache_key] = $this->get_yoast_meta($page_id, '_yoast_wpseo_metadesc', $type);
  }

  /**
   * Get Featured Image URL
   */
  public function get_featured_image(int $page_id, string $size = 'large'): string
  {
    $cache_key = "featured_image_{$page_id}_{$size}";
    if (isset(self::$cache[$cache_key])) {
      return self::$cache[$cache_key];
    }

    return self::$cache[$cache_key] = get_the_post_thumbnail_url($page_id, $size) ?: '';
  }

  /**
   * Get Main Page Content (Cleaned)
   */
  public function get_content(int $page_id): string
  {
    $cache_key = "content_{$page_id}";
    if (isset(self::$cache[$cache_key])) {
      return self::$cache[$cache_key];
    }

    $post = get_post($page_id);
    if (!$post) {
      return self::$cache[$cache_key] = '';
    }
    // Apply filters to mimic standard WP behavior
    return self::$cache[$cache_key] = apply_filters('the_content', $post->post_content);
  }

  /**
   * Helper to get single ACF field safely
   */
  public function get_field(string $key, $page_id)
  {
    $cache_key = "field_{$page_id}_{$key}";
    if (isset(self::$cache[$cache_key])) {
      return self::$cache[$cache_key];
    }

    return self::$cache[$cache_key] = get_field($key, $page_id);
  }

  /**
   * Check if the given ID is the current queried object
   */
  private function is_current_page(int $page_id, string $type = 'post'): bool
  {
    $queried_id = (int) get_queried_object_id();
    if ($page_id !== $queried_id) {
      return false;
    }

    if ($type === 'term') {
      return is_category() || is_tag() || is_tax();
    }

    return is_singular();
  }

  /**
   * Get Yoast meta with variable replacement
   */
  private function get_yoast_meta(int $page_id, string $meta_key, string $type = 'post'): string
  {
    $is_cat = ($type === 'term');
    $raw_value = $is_cat ? get_term_meta($page_id, $meta_key, true) : get_post_meta($page_id, $meta_key, true);

    if ($raw_value && function_exists('wpseo_replace_vars')) {
      $object = $is_cat ? get_term($page_id) : get_post($page_id);
      return wpseo_replace_vars($raw_value, $object);
    }

    return (string) $raw_value;
  }

  /**
   * Get Cached Term
   */
  public function get_term(int $term_id, string $taxonomy = 'category')
  {
    $cache_key = "term_{$term_id}_{$taxonomy}";
    if (isset(self::$cache[$cache_key])) {
      return self::$cache[$cache_key];
    }

    $term = get_term($term_id, $taxonomy);

    // Store even errors/false to prevent re-querying invalid IDs
    return self::$cache[$cache_key] = $term;
  }

  /**
   * Get Cached Categories for a Post
   */
  public function get_the_category(int $page_id): array
  {
    $cache_key = "categories_{$page_id}";
    if (isset(self::$cache[$cache_key])) {
      return self::$cache[$cache_key];
    }

    $cats = get_the_category($page_id);
    return self::$cache[$cache_key] = $cats;
  }

  /**
   * Clean title (optional, e.g. decode entities)
   */
  private function clean_title(string $title): string
  {
    return html_entity_decode(trim($title), ENT_QUOTES | ENT_HTML5, 'UTF-8');
  }
  /**
   * Get Main Pages for a specific Service Category
   * 
   * @param int $term_id
   * @return array List of WP_Post objects
   */
  public function getMainPagesByServiceCategory(int $term_id): array
  {
    $cache_key = "main_pages_service_cat_{$term_id}";
    if (isset(self::$cache[$cache_key])) {
      return self::$cache[$cache_key];
    }

    $posts = get_posts([
      'post_type'      => 'page',
      'posts_per_page' => -1,
      'post_parent'    => 0,
      'tax_query'      => [
        [
          'taxonomy' => 'service_category',
          'field'    => 'term_id',
          'terms'    => $term_id,
        ],
      ],
      'orderby'        => 'title',
      'order'          => 'ASC',
    ]);

    return self::$cache[$cache_key] = $posts;
  }

  /**
   * Get pages for a district
   * 
   * @param int $term_id District Term ID
   * @return array List of WP_Post objects
   */
  public function getPagesByDistrict(int $term_id): array
  {
    $cache_key = "pages_district_{$term_id}";
    if (isset(self::$cache[$cache_key])) {
      return self::$cache[$cache_key];
    }

    $posts = get_posts([
      'post_type'      => 'page',
      'posts_per_page' => -1,
      'post_parent'    => 0,
      'tax_query'      => [
        [
          'taxonomy' => 'category',
          'field'    => 'term_id',
          'terms'    => $term_id,
        ],
      ],
      'orderby'        => 'title',
      'order'          => 'ASC',
    ]);

    return self::$cache[$cache_key] = $posts;
  }

  /**
   * Get Cached Terms for a Post
   * Avoids N+1 queries when looping through posts
   */
  public function get_terms_for_post(int $post_id, string $taxonomy): array
  {
    $cache_key = "terms_{$post_id}_{$taxonomy}";
    if (isset(self::$cache[$cache_key])) {
      return self::$cache[$cache_key];
    }

    $terms = get_the_terms($post_id, $taxonomy);
    if (empty($terms) || is_wp_error($terms)) {
      $terms = [];
    }

    return self::$cache[$cache_key] = $terms;
  }
}
