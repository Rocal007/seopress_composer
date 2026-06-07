<?php

namespace SeopressComposer\Controllers;

use SeopressComposer\Core\ContextState;

/**
 * ContextController - Central logic to determine "where we are".
 * Handles ID resolution and context checks.
 */
class ContextController
{
  /**
   * Determine the correct page ID based on context
   * 
   * @param int|null $id Optional explicit ID
   * @param bool $with_home Include home fallback logic
   * @param bool $with_cat Include category fallback logic
   * @return int
   */
  public function get_current_page_id($id = null, $with_home = true, $with_cat = true): int
  {
    $page_id = $id ? intval($id) : get_queried_object_id();

    // Check for ancestors to use parent data (important for district subpages)
    // We use get_post_type to ensure it works even if we're not on the actual page context
    $post_type = get_post_type($page_id);
    if ($post_type) {
      $ancestors = get_ancestors($page_id, $post_type);
      if (!empty($ancestors)) {
        // Return the first ancestor (direct parent)
        return intval($ancestors[0]);
      }
    }

    // For categories (if no ID passed and we are on a category archive)
    if (!$id && is_category() && $with_cat) {
      $front_page_id = get_option('page_on_front');
      if ($front_page_id) {
        return intval($front_page_id);
      }
    }

    return intval($page_id);
  }

  /**
   * Determine the current context state (stage)
   * 
   * @param int|null $id Optional explicit ID
   * @return string One of ContextState constants
   */
  public function get_context_state(?int $id = null): string
  {
    if (is_front_page()) {
      return ContextState::HOME;
    }

    if (is_tax('district')) {
      return ContextState::DISTRICT;
    }

    if (is_page_template('template-hauptseite.php')) {
      return ContextState::SERVICE;
    }

    $page_id = $id ? $id : get_queried_object_id();

    $has_district = has_term('', 'district', $page_id);
    $has_service = has_term('', 'service_category', $page_id);

    if ($has_district && $has_service) {
      return ContextState::DISTRICT_SERVICE;
    }

    if ($has_service) {
      return ContextState::SERVICE;
    }

    return ContextState::OTHER;
  }
}
