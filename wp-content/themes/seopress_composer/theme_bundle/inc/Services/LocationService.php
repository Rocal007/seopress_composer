<?php

namespace SeopressComposer\Services;

use SeopressComposer\Repositories\PageRepository;

/**
 * LocationService - Handles location and district logic
 */
class LocationService
{
  private PageRepository $pageRepository;
  private array $location_cache = [];

  public function __construct(PageRepository $pageRepository)
  {
    $this->pageRepository = $pageRepository;
  }

  /**
   * Get the location based on page context (Facade)
   */
  public function get_location($return_full_data = false)
  {
    $page_id = get_queried_object_id();

    // 1. Resolve Name (Cached independently)
    $ort = $this->resolve_location_name($page_id);

    if (!$return_full_data) {
      return $ort;
    }

    // 2. Resolve Full Data (Cached independently)
    $cache_key = $page_id . '_full';
    if (isset($this->location_cache[$cache_key])) {
      return $this->location_cache[$cache_key];
    }

    // --- ICON LOGIC ---
    // Note: Icon resolution is currently handled by PageHelper's get_page_icon facade or IconService
    // Ideally, IconService should be injected here if we want to move get_page_icon logic too.
    // For now, we will assume the icon logic remains in PageHelper or moves to IconResolutionService
    // But PageHelper::get_location() called get_page_icon().

    // Circular dependency risk if we inject PageHelper here. 
    // We will defer icon fetching to the caller or inject IconService/IconResolutionService later.
    // For this refactor, we'll keep the logic pure to location data and return placeholders/raw data.

    $link = '';
    $term_id = 0;

    $categories = $this->pageRepository->get_the_category($page_id);

    if (is_category()) {
      $term_id = get_queried_object_id();
      $link = get_category_link($term_id);
    } elseif (!empty($categories)) {
      $term_id = $categories[0]->term_id;
      $link = get_category_link($term_id);
    }

    $result = [
      'id' => $term_id,
      'name' => (string) $ort,
      'seo_title' => (string) $ort,
      'icon' => '', // Icon resolution to be handled by consumer or separate service to avoid circular dep
      'link' => $link
    ];

    $this->location_cache[$cache_key] = $result;
    return $result;
  }

  /**
   * Resolve strictly the location name string from context
   */
  public function resolve_location_name($page_id)
  {
    $cache_key = $page_id . '_name';
    if (isset($this->location_cache[$cache_key])) {
      return $this->location_cache[$cache_key];
    }

    $queried_object = get_queried_object();
    $ort = '';

    // 1. Try Local ACF Field 'einsatzgebiet'
    $local_ort = get_field('einsatzgebiet', $page_id);

    if ($local_ort) {
      $ort = $local_ort;
    } elseif ($queried_object instanceof \WP_Term) {
      // 2. Handle Taxonomy Archives
      if ($queried_object->taxonomy === 'district') {
        $ort = $queried_object->name;
      } elseif ($queried_object->taxonomy === 'service_category' || is_category()) {
        $ort = get_field("einsatzgebiet", 'option');
      } else {
        $ort = $queried_object->name;
      }
    } else {
      // 3. Default to Global Option
      $ort = get_field("einsatzgebiet", 'option');

      // 4. Override if Singular Page belongs to a District taxonomy term
      $districts = get_the_terms($page_id, 'district');
      if ($districts && !is_wp_error($districts)) {
        $ort = $districts[0]->name;
      }
      // 5. For blog POSTS only: override with WP category name (e.g. location category)
      //    Do NOT apply to pages — page categories are editorial, not geographic.
      elseif (get_post_type($page_id) === 'post') {
        $categories = $this->pageRepository->get_the_category($page_id);
        if (!empty($categories)) {
          $ort = $categories[0]->name ?? $ort;
        }
      }
      // 6. If $ort is still empty, ensure einsatzgebiet is the final fallback
      if (empty($ort)) {
        $ort = get_field('einsatzgebiet', 'option') ?: '';
      }
    }

    $this->location_cache[$cache_key] = (string)$ort;
    return (string)$ort;
  }

  public function is_district($string): bool
  {
    if (empty($string)) return false;
    $string_lower = mb_strtolower($string);
    foreach ($this->get_district_names() as $district) {
      if (strpos($string_lower, $district) !== false) {
        return true;
      }
    }
    return false;
  }

  /**
   * Get list of district names (Hardcoded for now as in original)
   */
  public function get_district_names(): array
  {
    return [
      'alsergrund',
      'brigittenau',
      'döbling',
      'donaustadt',
      'favoriten',
      'floridsdorf',
      'hernals',
      'hietzing',
      'innere stadt',
      'josefstadt',
      'landstraße',
      'leopoldstadt',
      'liesing',
      'margareten',
      'mariahilf',
      'meidling',
      'neubau',
      'ottakring',
      'penzing',
      'rudolfsheim-fünfhaus',
      'simmering',
      'währing',
      'wieden'
    ];
  }
}
