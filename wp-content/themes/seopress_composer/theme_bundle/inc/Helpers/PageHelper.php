<?php

namespace SeopressComposer\Helpers;

/**
 * PageHelper - Utility class for page-related operations
 */
class PageHelper
{
  private \SeopressComposer\Repositories\RemoteDataRepository $remoteRepository;
  private \SeopressComposer\Repositories\PageRepository $pageRepository;
  private \SeopressComposer\Controllers\ContextController $contextController;
  private \SeopressComposer\Models\SiteSettings_Data $siteSettingsData;
  private \SeopressComposer\Services\LocationService $locationService;
  private \SeopressComposer\Services\RemoteRouteService $remoteRouteService;
  private static $cache_icons = [];
  private static $cache_attachment_urls = [];

  public function __construct(
    \SeopressComposer\Repositories\RemoteDataRepository $remoteRepository,
    \SeopressComposer\Repositories\PageRepository $pageRepository,
    \SeopressComposer\Controllers\ContextController $contextController,
    \SeopressComposer\Models\SiteSettings_Data $siteSettingsData,
    \SeopressComposer\Services\LocationService $locationService,
    \SeopressComposer\Services\RemoteRouteService $remoteRouteService
  ) {
    $this->remoteRepository = $remoteRepository;
    $this->pageRepository = $pageRepository;
    $this->contextController = $contextController;
    $this->siteSettingsData = $siteSettingsData;
    $this->locationService = $locationService;
    $this->remoteRouteService = $remoteRouteService;
  }

  /**
   * Cached wrapper for SiteSettings::get_option()
   */
  public function get_option(string $selector, $default = null)
  {
    return $this->siteSettingsData->get_option($selector, $default);
  }
  /**
   * Determine the correct page ID based on context
   */
  public function check_page_id($id = null, $with_home = true, $with_cat = true): int
  {
    return $this->contextController->get_current_page_id($id, $with_home, $with_cat);
  }

  /**
   * Get the location based on page context
   */
  private array $location_cache = [];

  /**
   * Get the location based on page context
   */
  /**
   * Get the location based on page context
   */
  public function get_location($return_full_data = false)
  {
    // Delegate to LocationService, but we need to handle the fact that get_location currently returns an array OR string.
    // Our new service does the same.
    // However, the original code had some Icon Logic intermixed which we moved to the service as a placeholder.
    // Ideally we should pass icon resolution strategy or let the service handle it fully.
    // For now, simple delegation.

    // Original logic also fetched icon here using $this->get_page_icon.
    // The extracted service returns 'icon' => '' currently.
    // We should re-hydrate the icon here to maintain backward compatibility if the service doesn't have it.

    $data = $this->locationService->get_location($return_full_data);

    if ($return_full_data && is_array($data) && empty($data['icon'])) {
      $page_id = get_queried_object_id();
      $data['icon'] = $this->get_page_icon($page_id, null, false);
    }

    return $data;
  }

  /**
   * Resolve strictly the location name string from context
   */
  private function resolve_location_name($page_id)
  {
    return $this->locationService->resolve_location_name($page_id);
  }

  /**
   * Get business options
   */
  public function get_options_business(): array
  {
    return $this->siteSettingsData->get_data();
  }

  /**
   * Get IDs of main pages
   */
  public function get_main_page_ids($with_home = true): array
  {
    $args = array(
      'orderby' => 'menu_order',
      'order' => 'ASC',
      'post_type' => 'page',
      'post_status' => 'publish',
      'post_parent' => 0,
      'meta_query' => array(
        array(
          'key' => '_wp_page_template',
          'value' => 'template-hauptseiten.php',
        )
      ),
      'posts_per_page' => -1,
    );

    $main_page_query = new \WP_Query($args);
    $mainpage_ids = [];

    if ($with_home) {
      $home_page_id = (int) (get_option('page_on_front') ?: 0);
      if ($home_page_id) {
        $mainpage_ids[] = $home_page_id;
      }
    }

    while ($main_page_query->have_posts()) {
      $main_page_query->the_post();
      $mainpage_ids[] = get_the_id();
    }

    wp_reset_postdata();
    return $mainpage_ids;
  }
  /**
   * Get Hero/Header Data from attributes or Global Settings
   */
  public function get_hero_data($page_id = null): array
  {
    if (!$page_id) {
      $page_id = get_queried_object_id();
    }

    // Handle Category Context for ACF
    $acf_id = $page_id;
    if (is_category()) {
      $acf_id = 'category_' . $page_id;
    }

    // 1. Try Page Specific Fields
    $image = get_field('top_picture_image', $acf_id);
    $title = get_field('top_picture_title', $acf_id); // Often empty, use page title
    $claim = get_field('top_picture_claim', $acf_id);

    // 2. Fetch Remote Data if image is missing AND we have a remote URL
    if (empty($image)) {
      $remote_url = $this->get_remote_url($page_id);
      if ($remote_url) {
        $remote_data = $this->remoteRepository->fetch($remote_url);

        // Handle array response (search by slug returns array)
        if (is_array($remote_data) && !empty($remote_data)) {
          $remote_data = $remote_data[0];
        }

        if ($remote_data) {
          // Try 'top' size first from better_featured_image
          if (!empty($remote_data->better_featured_image->media_details->sizes->top->source_url)) {
            $image = $remote_data->better_featured_image->media_details->sizes->top->source_url;
          }
          // Fallback to full source_url
          elseif (!empty($remote_data->better_featured_image->source_url)) {
            $image = $remote_data->better_featured_image->source_url;
          }
          // Fallback to ACF top_picture_image inside remote data if it exists (structure depends on API)
          elseif (!empty($remote_data->acf->top_picture_image)) {
            $image = $remote_data->acf->top_picture_image;
          }
        }
      }
    }

    // 3. Fallback to Global Settings (Site Settings)
    if (empty($image)) {
      $image = $this->get_option('hero_image_global');
      // Extra safety fallback if SiteSettings cache failed
      if (empty($image)) {
        $image = get_field('hero_image_global', 'option');
      }

      // Handle raw ID from database (if field definition suggests ID or if raw option is ID)
      if (is_numeric($image) && $image > 0) {
        $img_url = wp_get_attachment_image_url($image, 'full');
        if ($img_url) {
          $image = $img_url;
        }
      }
    }
    if (empty($title)) {
      $title = $this->get_option('hero_title_global');
    }
    if (empty($claim)) {
      $claim = $this->get_option('hero_claim_global');
    }

    // 3. Fallback for Title if still empty
    if (empty($title)) {
      if (is_category()) {
        $title = single_term_title('', false);
      } else {
        $title = get_the_title($page_id);
      }
      $title = seopress_container()->get(\SeopressComposer\Services\TextReplacementService::class)->clean_title($title);
    }

    // 4. Get Button 2 Configuration
    $btn2_text = $this->get_option('hero_button_2_text_global') ?: 'Termin vereinbaren';
    $btn2_url = $this->get_option('hero_button_2_url_global') ?: '/kontakt';

    // 5. Get Mobile Number
    $mobile_number = $this->get_option('telefonnummer');

    return [
      'image' => $image,
      'title' => $title,
      'claim' => $claim,
      'btn2_text' => $btn2_text,
      'btn2_url' => $btn2_url,
      'mobile_number' => $mobile_number,
    ];
  }

  /**
   * Get Remote URL for the page
   * Priority: Local Field > Home Page Field > Global 'basicremote' Option
   */
  public function get_remote_url($page_id = null, $allow_fallback_to_home = true): string
  {
    return $this->remoteRouteService->get_remote_url($page_id, $allow_fallback_to_home);
  }

  /**
   * REUSABLE: Resolve localized child link for Silo-SEO
   */
  public function resolve_localized_link(int $page_id, string $location_name = ''): string
  {
    if (empty($location_name)) {
      $location_name = $this->get_location();
    }
    if (empty($location_name)) {
      return get_permalink($page_id);
    }

    $children = get_posts([
      'post_parent' => $page_id,
      'post_type' => 'page',
      'numberposts' => -1,
      'fields' => 'ids',
    ]);

    if ($children) {
      foreach ($children as $child_id) {
        if (stripos(get_the_title($child_id), $location_name) !== false) {
          return get_permalink($child_id);
        }
      }
    }
    return get_permalink($page_id);
  }

  /**
   * REUSABLE: Unified Page Icon Resolution
   */
  /**
   * REUSABLE: Unified Page Icon Resolution
   */
  public function get_page_icon($id, $remote_data = null, bool $check_service_icons = true, string $context = 'auto'): string
  {
    // Handle term objects
    $original_id = $id;
    if ($id instanceof \WP_Term) {
      $id = $id->term_id;
      $context = 'term';
    }
    $id = (int) $id;

    // 1. Try Local ACF 'hauptseiten_icon' (Specific service illustration)
    // Removed per user request to ignore internal DB settings
    $icon_raw = null;

    // 2. Try Category 'karte' (Location based map)
    if (empty($icon_raw)) {
      $term_id = 0;

      // OPTIMIZATION: Only check if ID is a term if context allows it
      if ($context !== 'post') {
        // Check if $id is a category term directly
        // Cached Term Lookup via Repository
        $term = $this->pageRepository->get_term($id, 'category');

        if ($term && !is_wp_error($term)) {
          $term_id = $id;
        }
      }

      if (!$term_id && $context !== 'term') {
        // Check if $id is a post that belongs to a category
        $cats = $this->pageRepository->get_the_category($id);

        if (!empty($cats)) {
          $term_id = $cats[0]->term_id;
        }
      }

      if ($term_id) {
        $current_term_id = $term_id;
        while ($current_term_id) {
          // Check static optional cache for field if possible? 
          // ACF get_field might trigger DB too.

          $karte_raw = get_field('karte', 'category_' . $current_term_id, false);
          if ($karte_raw) {
            $icon_raw = $karte_raw;
            break;
          }

          // Cached Parent Lookup via Repository
          $term_next = $this->pageRepository->get_term($current_term_id, 'category'); // Use get_term, it's cached

          $current_term_id = ($term_next && !is_wp_error($term_next)) ? $term_next->parent : 0;
        }
      }
    }

    // 3. Try Parent ACF 'hauptseiten_icon'
    // Removed per user request to ignore internal DB settings

    // 4. Try Remote Data
    if (empty($icon_raw) && $remote_data && !empty($remote_data->acf->hauptseiten_icon)) {
      $icon_raw = $remote_data->acf->hauptseiten_icon;
    }

    // 5. Fallback to Location based Map Icon from options if still empty
    if (empty($icon_raw)) {
      $icon_raw = get_field('bundesland_icon', 'option');
    }

    // Resolve to URL
    $cache_key = 'icon_' . (is_array($icon_raw) ? md5(serialize($icon_raw)) : (string) $icon_raw);
    if (isset(self::$cache_attachment_urls[$cache_key])) {
      return self::$cache_attachment_urls[$cache_key];
    }

    $url = '';
    if (is_numeric($icon_raw)) {
      $url = wp_get_attachment_image_url($icon_raw, 'karte') ?: wp_get_attachment_url($icon_raw);
    } elseif (is_array($icon_raw)) {
      $url = $icon_raw['sizes']['karte'] ?? ($icon_raw['url'] ?? '');
    } elseif (is_string($icon_raw) && !empty($icon_raw)) {
      $url = $icon_raw;
    }

    if (empty($url)) {
      $url = get_template_directory_uri() . '/assets/img/wien.svg';
    }

    self::$cache_attachment_urls[$cache_key] = (string) $url;
    return (string) $url;
  }

  public function is_district($string): bool
  {
    return $this->locationService->is_district($string);
  }

  public function get_district_names(): array
  {
    return $this->locationService->get_district_names();
  }

  /**
   * Helper to get section titles and descriptions
   * 
   * @param string $section_key The key prefix (e.g., 'vorteile', 'cta')
   * @param string $default_title Default title if none found
   * @param string $default_desc Default description if none found
   * @param object|null $remote_data Optional remote data object to check first
   * @return array ['title' => string, 'description' => string]
   */
  public function get_section_titles(string $section_key, string $default_title = '', string $default_desc = '', $remote_data = null): array
  {
    $title = $default_title;
    $desc = $default_desc;
    $page_id = $this->check_page_id();

    // 1. Try Remote Data (if available) - Assuming structure like $data->acf->{$section_key}_title
    if ($remote_data && isset($remote_data->acf)) {
      // Check for variations: {$section_key}_title, {$section_key}_heading
      if (!empty($remote_data->acf->{$section_key . '_title'})) {
        $title = $remote_data->acf->{$section_key . '_title'};
      } elseif (!empty($remote_data->acf->{$section_key . '_heading'})) {
        $title = $remote_data->acf->{$section_key . '_heading'};
      }

      // Description / Subheading
      if (!empty($remote_data->acf->{$section_key . '_description'})) {
        $desc = $remote_data->acf->{$section_key . '_description'};
      } elseif (!empty($remote_data->acf->{$section_key . '_subheading'})) {
        $desc = $remote_data->acf->{$section_key . '_subheading'};
      }
    }

    // 2. Try Local ACF Fields (Overrides remote/default if present on the actual page)
    // 2. Try Local Fields (Overrides remote/default if present on the actual page)
    // Check local variations
    $local_title = get_field($section_key . '_title', $page_id);
    if (!$local_title) $local_title = get_field($section_key . '_heading', $page_id);

    if ($local_title) {
      $title = $local_title;
    }

    $local_desc = get_field($section_key . '_description', $page_id);
    if (!$local_desc) $local_desc = get_field($section_key . '_subheading', $page_id);

    if ($local_desc) {
      $desc = $local_desc;
    }

    return ['title' => $title, 'description' => $desc];
  }
}
