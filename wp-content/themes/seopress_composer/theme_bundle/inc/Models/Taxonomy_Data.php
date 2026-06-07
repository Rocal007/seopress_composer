<?php

namespace SeopressComposer\Models;

/**
 * Taxonomy_Data Model
 * 
 * Handles fetching and formatting taxonomy term data for archive pages.
 */

use SeopressComposer\Controllers\ContextController;
use SeopressComposer\Factories\DataFactory;
use SeopressComposer\Repositories\PageRepository;

/**
 * Taxonomy_Data Model
 * 
 * Handles fetching and formatting taxonomy term data for archive pages.
 */


/**
 * Taxonomy_Data Model
 * 
 * Handles fetching and formatting taxonomy term data for archive pages.
 */
class Taxonomy_Data extends BaseModel
{
  // Cache Configuration
  private const CACHE_GROUP = 'taxonomy_data';
  private const CACHE_DURATION = 3600; // 1 hour

  public function get_data($page_id = null): array
  {
    // Cache the entire term data
    $cache_key = $this->generate_cache_key($page_id);
    $cached = wp_cache_get($cache_key, self::CACHE_GROUP);

    if ($cached !== false) {
      return $cached;
    }

    $data = $this->get_current_term_data($page_id);

    wp_cache_set($cache_key, $data, self::CACHE_GROUP, self::CACHE_DURATION);

    return $data;
  }

  /**
   * Generate cache key based on context
   */
  private function generate_cache_key($page_id = null): string
  {
    $term = get_queried_object();
    $term_id = $page_id ?? ($term->term_id ?? 0);
    $taxonomy = $term->taxonomy ?? 'category';

    return "taxonomy_data_{$term_id}_{$taxonomy}_" . get_locale();
  }

  /**
   * Clear all taxonomy data cache
   */
  public static function clear_cache(): void
  {
    wp_cache_flush_group(self::CACHE_GROUP);
  }
  /**
   * Get current taxonomy term data
   * 
   * @param int|null $term_id Optional term ID. If null, uses current queried object
   * @param string|null $taxonomy Optional taxonomy name. If null, auto-detects
   * @return array Term data including name, description, slug, etc.
   */
  public function get_current_term_data($term_id = null, $taxonomy = null): array
  {
    if (!$term_id) {
      $term = get_queried_object();

      if (!$term || !isset($term->term_id)) {
        return $this->get_empty_term_data();
      }

      $term_id = $term->term_id;
      $taxonomy = $term->taxonomy ?? null;
    }

    if (!$taxonomy) {
      $term = get_queried_object();
      $taxonomy = $term->taxonomy ?? 'category';
    }

    $term = get_term($term_id, $taxonomy);

    if (is_wp_error($term) || !$term) {
      return $this->get_empty_term_data();
    }

    return $this->format_term_data($term);
  }

  /**
   * Get term data for service_category taxonomy
   * 
   * @param int|null $term_id Optional term ID
   * @return array Formatted term data
   */
  public function get_service_category_data($term_id = null): array
  {
    return $this->get_current_term_data($term_id, 'service_category');
  }

  /**
   * Get section headings for service_category archive pages
   * 
   * @param int|null $term_id Optional term ID
   * @return array Section headings
   */
  public function get_service_category_sections($term_id = null): array
  {
    $term_data = $this->get_service_category_data($term_id);

    return [
      'service_locations' => $this->pageHelper->get_section_titles('inner_section_title', $term_data['name'], ''),
      'other_services'    => $this->pageHelper->get_section_titles('other_services', 'Weitere Leistungen', ''),
    ];
  }

  /**
   * Get term data for district taxonomy
   * 
   * @param int|null $term_id Optional term ID
   * @return array Formatted term data
   */
  public function get_district_data($term_id = null): array
  {
    return $this->get_current_term_data($term_id, 'category');
  }

  /**
   * Get random overview text from Startseite API for the category page
   */
  public function get_category_overview_text(): string
  {
    $remote_url = $this->pageHelper->get_remote_url(get_option('page_on_front'));
    if (!$remote_url) return '';
    $data = $this->repository->fetch($remote_url);
    if (!$data || !isset($data->acf)) return '';

    $dienstleistungen = $data->acf->dienstleistungen ?? [];
    
    // Check multiple possible ACF fields for "texte für unterseiten"
    $content = $data->acf->texte_fuer_unterseiten ?? $data->acf->texte_unterseiten ?? $data->acf->ubersichtstexte_radom ?? [];
    
    if (is_array($content) && !empty($content)) {
      $shuffledText = $content[array_rand($content)];
      
      // Look for the text field inside the repeater
      $text = $shuffledText->ubersichtstext ?? $shuffledText->ubersichtstext_radom ?? $shuffledText->text ?? '';
      
      if (!empty($text)) {
        return $this->textReplacer->prozess_data($text, $dienstleistungen);
      }
    }
    
    return '';
  }

  /**
   * Get section headings for district archive pages
   * 
   * @param int|null $term_id Optional term ID
   * @return array Section headings
   */
  public function get_district_sections($term_id = null): array
  {
    $term_data = $this->get_district_data($term_id);

    return [
      'district_services' => 'Unsere Services in ' . $term_data['name'],
      'other_districts' => 'Weitere Einsatzgebiete',
    ];
  }

  /**
   * Get main text data (title and description) for taxonomy pages
   * Includes remote Yoast description support.
   */
  public function get_main_text_data($term_id = null): array
  {
    $term_data = $this->get_current_term_data($term_id);
    $data = [];

    if (!empty($term_data['description'])) {
      $data['haupttext'] = [
        'title' => $term_data['main_text_title'] ?? $term_data['name'],
        'content' => $term_data['description']
      ];
    }

    return $data;
  }

  /**
   * Get pages for a district grouped by service category
   * 
   * @param int|null $term_id
   * @return array Grouped pages data
   */
  public function get_district_grouped_pages($term_id = null): array
  {
    if (!$term_id) {
      $term = get_queried_object();
      $term_id = $term->term_id ?? 0;
    }

    if (!$term_id) return [];

    // Replaced Taxonomy_Repository::getPagesByDistrict
    $posts = $this->pageRepository->getPagesByDistrict((int)$term_id);
    $grouped = [];

    if (!empty($posts)) {
      foreach ($posts as $post) {
        // ── Fix: fetch service_category terms for this post ──
        $services = wp_get_post_terms($post->ID, 'service_category');

        // Skip non-service pages (legal, FAQ, Über uns, etc.)
        if (empty($services) || is_wp_error($services)) {
          continue;
        }

        $card_data = $this->dataFactory->create($post, 'service_category');

        if (!empty($services) && !is_wp_error($services)) {
          foreach ($services as $s) {
            if (!isset($grouped[$s->term_id])) {
              // Get category icon from remote data
              $cat_icon = '';
              $term_remote_url = get_field('remote_page', $s->taxonomy . '_' . $s->term_id);

              if ($term_remote_url) {
                $term_remote_data = $this->repository->fetch((string)$term_remote_url);
                $term_obj = is_array($term_remote_data) ? ($term_remote_data[0] ?? null) : $term_remote_data;

                if ($term_obj && !empty($term_obj->acf->hauptseiten_icon)) {
                  $raw_icon = $term_obj->acf->hauptseiten_icon;
                  if (is_string($raw_icon)) {
                    $cat_icon = $raw_icon;
                  } elseif (is_object($raw_icon) && !empty($raw_icon->url)) {
                    $cat_icon = $raw_icon->url;
                  } elseif (is_array($raw_icon)) {
                    $first = $raw_icon[0] ?? null;
                    if (is_string($first)) {
                      $cat_icon = $first;
                    } elseif (is_object($first) && !empty($first->url)) {
                      $cat_icon = $first->url;
                    }
                  }
                }
              }

              $grouped[$s->term_id] = [
                'term' => $s,
                'name' => $s->name,
                'icon' => (string)$cat_icon,
                'items' => []
              ];
            }
            $grouped[$s->term_id]['items'][] = $card_data;
          }
        }
      }

      uasort($grouped, function ($a, $b) {
        return strnatcmp($a['name'], $b['name']);
      });
    }

    return $grouped;
  }


  /**
   * Get flat list of pages for a service category
   * 
   * @param int|null $term_id
   * @return array List of card data
   */
  public function get_service_category_pages($term_id = null): array
  {
    if (!$term_id) {
      $term = get_queried_object();
      $term_id = $term->term_id ?? 0;
    }

    if (!$term_id) return [];

    $posts = $this->pageRepository->getMainPagesByServiceCategory((int)$term_id);
    $data = [];

    if (!empty($posts)) {
      foreach ($posts as $post) {
        $card_data = $this->dataFactory->create($post, 'district');
        $data[] = $card_data;
      }
    }

    return $data;
  }
  /**
   * Get pages for a service category grouped by district
   * 
   * @param int|null $term_id
   * @return array Grouped pages data
   */
  public function get_service_category_grouped_pages($term_id = null): array
  {
    if (!$term_id) {
      $term = get_queried_object();
      $term_id = $term->term_id ?? 0;
    }

    if (!$term_id) return [];

    // Replaced with PageRepository::getMainPagesByServiceCategory
    $posts = $this->pageRepository->getMainPagesByServiceCategory((int)$term_id);
    $grouped = [];

    if (!empty($posts)) {
      foreach ($posts as $post) {
        $card_data = $this->dataFactory->create($post, 'district');
        $districts = wp_get_post_terms($post->ID, 'category');

        if (!empty($districts) && !is_wp_error($districts)) {
          foreach ($districts as $d) {
            if (!isset($grouped[$d->term_id])) {
              $grouped[$d->term_id] = [
                'term' => $d,
                'name' => $d->name,
                'items' => []
              ];
            }
            $grouped[$d->term_id]['items'][] = $card_data;
          }
        } else {
          if (!isset($grouped['uncategorized'])) {
            $grouped['uncategorized'] = [
              'term' => null,
              'name' => 'Weitere Standorte',
              'items' => []
            ];
          }
          $grouped['uncategorized']['items'][] = $card_data;
        }
      }
      wp_reset_postdata();

      // Sort by district name (using numeric part if possible)
      uasort($grouped, function ($a, $b) {
        return strnatcmp($a['name'], $b['name']);
      });
    }

    return $grouped;
  }

  /**
   * Get all service categories with their icons
   * 
   * @return array List of all service categories with icons
   */
  public function get_all_service_categories(): array
  {
    $terms = get_terms([
      'taxonomy' => 'service_category',
      'hide_empty' => false,
      'orderby' => 'name',
      'order' => 'ASC'
    ]);

    if (empty($terms) || is_wp_error($terms)) return [];

    return array_map(function ($t) {
      $icon = '';
      $remote_url = get_field('remote_page', $t->taxonomy . '_' . $t->term_id);

      if ($remote_url) {
        $remote_data = $this->repository->fetch((string)$remote_url);
        if ($remote_data) {
          // Try to find icon in various places
          $raw_icon = $remote_data->acf->hauptseiten_icon ?? '';

          if (!empty($raw_icon)) {
            if (is_string($raw_icon)) {
              $icon = $raw_icon;
            } elseif (is_object($raw_icon) && !empty($raw_icon->url)) {
              $icon = $raw_icon->url;
            } elseif (is_array($raw_icon)) {
              $first = $raw_icon[0] ?? null;
              if (is_string($first)) {
                $icon = $first;
              } elseif (is_object($first) && !empty($first->url)) {
                $icon = $first->url;
              }
            }
          }

          // Fallback to services-cat-icon (used in Menu_Data) if still empty
          if (empty($icon) && isset($remote_data->{'services-cat-icon'})) {
            $icon = $remote_data->{'services-cat-icon'};
          }
        }
      }

      // Fallback to slug if no specific icon found in remote data
      if (empty($icon)) {
        $icon = $t->slug;
      }

      return [
        'id'    => $t->slug,
        'name'  => $t->name,
        'slug'  => $t->slug,
        'icon'  => $icon,
      ];
    }, $terms);
  }

  /**
   * Get sibling service categories
   * 
   * @param int|null $current_term_id
   * @param int $limit
   * @return array List of other services
   */
  public function get_other_services($current_term_id = null, $limit = 12): array
  {
    if (!$current_term_id) {
      $term = get_queried_object();
      $current_term_id = $term->term_id ?? 0;
    }

    // Replaced Taxonomy_Repository::getSiblingTerms
    $terms = $this->categoryRepository->getSiblingTerms('service_category', (int)$current_term_id, $limit);

    if (empty($terms)) return [];

    return array_map(function ($t) {
      $icon = '';
      $remote_url = get_field('remote_page', $t->taxonomy . '_' . $t->term_id);

      if ($remote_url) {
        $remote_data = $this->repository->fetch((string)$remote_url);
        if ($remote_data) {
          // Try to find icon in various places
          $raw_icon = $remote_data->acf->hauptseiten_icon ?? '';

          if (!empty($raw_icon)) {
            if (is_string($raw_icon)) {
              $icon = $raw_icon;
            } elseif (is_object($raw_icon) && !empty($raw_icon->url)) {
              $icon = $raw_icon->url;
            } elseif (is_array($raw_icon)) {
              $first = $raw_icon[0] ?? null;
              if (is_string($first)) {
                $icon = $first;
              } elseif (is_object($first) && !empty($first->url)) {
                $icon = $first->url;
              }
            }
          }
        }
      }

      // Fallback to slug if no specific icon found in remote data
      if (empty($icon)) {
        $icon = $t->slug;
      }
      
      // Find local category page if it exists
      $local_page_url = esc_url(get_term_link($t));
      $page = get_page_by_path('service-kategorie/' . $t->slug) ?: get_page_by_title($t->name, OBJECT, 'page');
      if ($page) {
          $local_page_url = get_permalink($page->ID);
      }

      return [
        'name'  => $t->name,
        'link'  => $local_page_url,
        'icon'  => $icon,
        'title' => 'Mehr über ' . $t->name . ' erfahren'
      ];
    }, $terms);
  }

  /**
   * Get pages by menu_category slug
   * 
   * @param string $slug
   * @return array
   */
  /**
   * Get pages by menu_category slug from Remote API
   * 
   * @param string $slug
   * @return array
   */
  public function get_pages_by_menu_category(string $slug): array
  {
    // 1. Determine API Endpoint URL
    $api_url = $this->pageHelper->get_option('services_api_endpoint');

    if (!$api_url) {
      $remote_url = $this->pageHelper->get_option('basicremote');
      if ($remote_url) {
        $remote_url = rtrim($remote_url, '/');
        $remote_url = preg_replace('#/wp-json/wp/v2/?$#', '', $remote_url);
        $api_url = $remote_url . '/wp-json/custom/v1/pages';
      }
    }

    if (!$api_url) {
      return [];
    }

    // 2. Fetch Data
    // Use repository which handles caching (default 1 hour)
    $response = $this->repository->fetch($api_url);

    if (empty($response) || !is_array($response)) {
      return [];
    }

    // 3. Filter by Menu Category Slug
    $filtered_pages = [];
    foreach ($response as $page) {
      // Check if menu_categories matches any term with the slug
      if (!empty($page->menu_categories) && is_array($page->menu_categories)) {
        foreach ($page->menu_categories as $term) {
          if (isset($term->slug) && $term->slug === $slug) {
            // Match found
            $filtered_pages[] = $this->dataFactory->create_from_api_object($page);
            break; // Stop checking terms for this page
          }
        }
      }
    }

    // Sort by menu_order if available in API? 
    // The API Endpoint endpoint_hauptseiten.php sorts by 'menu_order' ASC.
    // So the array should already be sorted.

    return $filtered_pages;
  }

  /**
   * Get sibling districts
   * 
   * @param int|null $current_term_id
   * @param int $limit
   * @return array List of other districts
   */
  public function get_other_districts($current_term_id = null, $limit = 12): array
  {
    if (!$current_term_id) {
      $term = get_queried_object();
      $current_term_id = $term->term_id ?? 0;
    }

    // Replaced Taxonomy_Repository::getSiblingTerms
    $terms = $this->categoryRepository->getSiblingTerms('category', (int)$current_term_id, $limit);

    if (empty($terms)) return [];

    return array_map(function ($t) {
      return [
        'name'  => $t->name,
        'link'  => get_term_link($t),
        'icon'  => 'besichtigung',
        'title' => 'Services in ' . $t->name . ' ansehen'
      ];
    }, $terms);
  }


  /**
   * Format term object into standardized array
   * 
   * @param \WP_Term $term WordPress term object
   * @return array Formatted term data
   */
  private function format_term_data($term): array
  {
    if (!$term || is_wp_error($term)) {
      return $this->get_empty_term_data();
    }

    $description = $term->description ?? '';
    $title = ($term->taxonomy === 'service_category') ? 'Services' : $term->name;

    // Attempt to fetch remote data for titles and descriptions
    $remote_url = get_field('remote_page', $term->taxonomy . '_' . $term->term_id);
    if ($remote_url) {
      $remote_data = $this->repository->fetch((string)$remote_url);
      if ($remote_data) {
        // User wants yoast_desc_raw as the heading/title
        if (!empty($remote_data->yoast_desc_raw)) {
          $title = $remote_data->yoast_desc_raw;
        }
        // Keep the description (was ok in previous version)
        if (!empty($remote_data->description)) {
          $description = $remote_data->description;
        }
      }
    }

    return [
      'term_id' => $term->term_id,
      'name' => $term->name,
      'slug' => $term->slug,
      'description' => $this->textReplacer->process($description),
      'main_text_title' => $this->textReplacer->process($title),
      'taxonomy' => $term->taxonomy,
      'parent' => $term->parent ?? 0,
      'count' => $term->count ?? 0,
      'link' => get_term_link($term),
      'term_object' => $term,
    ];
  }

  /**
   * Get all main pages grouped by their service categories
   * Useful for the startpage to show a comprehensive overview.
   * 
   * @return array Grouped pages data
   */
  public function get_all_services_grouped(): array
  {
    $main_ids = $this->pageHelper->get_main_page_ids(false);
    if (empty($main_ids)) return [];

    $grouped = [];
    foreach ($main_ids as $current_id) {
      $post = get_post($current_id);
      if (!$post) continue;

      $services = wp_get_post_terms($current_id, 'service_category');

      // Skip pages that have no service_category assigned
      if (empty($services) || is_wp_error($services)) {
        continue;
      }

      $card_data = $this->dataFactory->create($post, 'district');

      foreach ($services as $s) {
        if (!isset($grouped[$s->term_id])) {
          // Resolve category icon from remote API data
          $cat_icon = '';
          $term_remote_url = get_field('remote_page', $s->taxonomy . '_' . $s->term_id);

          if ($term_remote_url) {
            $term_remote_data = $this->repository->fetch((string)$term_remote_url);
            $term_obj = is_array($term_remote_data) ? ($term_remote_data[0] ?? null) : $term_remote_data;

            if ($term_obj) {
              // Try ACF hauptseiten_icon first
              if (!empty($term_obj->acf->hauptseiten_icon)) {
                $raw_icon = $term_obj->acf->hauptseiten_icon;
                if (is_string($raw_icon)) {
                  $cat_icon = $raw_icon;
                } elseif (is_object($raw_icon) && !empty($raw_icon->url)) {
                  $cat_icon = $raw_icon->url;
                } elseif (is_array($raw_icon)) {
                  $first = $raw_icon[0] ?? null;
                  if (is_string($first)) {
                    $cat_icon = $first;
                  } elseif (is_object($first) && !empty($first->url)) {
                    $cat_icon = $first->url;
                  }
                }
              }
              // Fallback to services-cat-icon
              if (empty($cat_icon) && isset($term_obj->{'services-cat-icon'})) {
                $cat_icon = $term_obj->{'services-cat-icon'};
              }
            }
          }

          // Final fallback: use term slug as icon identifier
          if (empty($cat_icon)) {
            $cat_icon = $s->slug;
          }

          $grouped[$s->term_id] = [
            'term'  => $s,
            'name'  => $s->name,
            'icon'  => (string)$cat_icon,
            'items' => []
          ];
        }
        $grouped[$s->term_id]['items'][] = $card_data;
      }
    }

    return array_values($grouped);
  }

  /**
   * Get empty term data structure
   * 
   * @return array Empty term data
   */
  private function get_empty_term_data(): array
  {
    return [
      'term_id' => 0,
      'name' => '',
      'slug' => '',
      'description' => '',
      'taxonomy' => '',
      'parent' => 0,
      'count' => 0,
      'link' => '',
      'term_object' => null,
    ];
  }
}
