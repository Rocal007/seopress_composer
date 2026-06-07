<?php

namespace SeopressComposer\Models;

use SeopressComposer\Repositories\RemoteDataRepository;
use SeopressComposer\Helpers\PageHelper;
use SeopressComposer\Services\TextReplacementService;
use SeopressComposer\Factories\DataFactory;
use SeopressComposer\Controllers\ContextController;
use stdClass;
use WP_Query;

/**
 * Class Menu_Data
 * * Zentrale Klasse für die Menü-Steuerung.
 * Verwaltet zwei Custom Taxonomies:
 * 1. 'services' (Dienstleistungen)
 * 2. 'district' (Bezirke/Standorte)
 */

use SeopressComposer\Services\IconService;

class Menu_Data extends BaseModel
{
  // Konfiguration der Taxonomy-Slugs
  private const TAX_SERVICES = 'service_category';
  private const TAX_DISTRICTS = 'district';

  private const CACHE_GROUP = 'menu_data';
  private array $item_cache = [];
  private array $menu_tree_cache = [];

  public function get_data($page_id = null): array
  {
    return [];
  }

  // =========================================================================
  // 1. PUBLIC API METHODS (Direct Access)
  // =========================================================================

  /**
   * List of all service categories (Parent=0)
   */
  public function get_service_categories(): array
  {
    return $this->get_taxonomy_menu_items(self::TAX_SERVICES);
  }

  /**
   * List of all service categories (Duplicate for API consistency maybe, but essentially same as above)
   */
  public function get_services_overview(): array
  {
    return $this->get_taxonomy_menu_items(self::TAX_SERVICES);
  }

  /**
   * Hierarchical tree of services
   */
  public function get_services_tree(): array
  {
    return $this->get_taxonomy_tree(self::TAX_SERVICES);
  }

  /**
   * List of all districts
   */
  public function get_districts_list(): array
  {
    return $this->get_taxonomy_menu_items(self::TAX_DISTRICTS);
  }

  /**
   * Clear all menu data cache
   * @return void
   */
  public static function clear_cache(): void
  {
    wp_cache_flush_group(self::CACHE_GROUP);
  }

  // =========================================================================
  // 1. TAXONOMY BUILDER (Listen & Bäume)
  // =========================================================================

  private function get_taxonomy_menu_items(string $taxonomy): array
  {
    $terms = [];
    if ($taxonomy === self::TAX_SERVICES) {
      $all_terms = $this->categoryRepository->get_cached_service_categories();
      // Filter for parent = 0
      $terms = array_filter($all_terms, function ($t) {
        return $t->parent == 0;
      });
    } else {
      $args = ['taxonomy' => $taxonomy, 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => 0, 'parent' => 0];
      $terms = get_terms($args);

      if (empty($terms) || is_wp_error($terms)) return [];

      update_term_cache($terms, $taxonomy);
    }

    if (empty($terms) || is_wp_error($terms)) return [];

    // Preload Remote Data to avoid N+1 queries
    $urls = [];
    foreach ($terms as $term) {
      $url = get_field('remote_page', $taxonomy . '_' . $term->term_id);
      if ($url) {
        $urls[] = $url;
      }
    }
    $this->repository->preload($urls);

    return $this->format_terms($terms, $taxonomy);
  }

  private function get_taxonomy_tree(string $taxonomy): array
  {
    $terms = [];
    if ($taxonomy === self::TAX_SERVICES) {
      $terms = $this->categoryRepository->get_cached_service_categories();
    } else {
      $args = ['taxonomy' => $taxonomy, 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => 0]; // Kein Parent-Filter
      $terms = get_terms($args);

      if (!empty($terms) && !is_wp_error($terms)) {
        update_term_cache($terms, $taxonomy);
      }
    }

    if (empty($terms) || is_wp_error($terms)) return [];

    $formatted = $this->format_terms($terms, $taxonomy);
    return $this->build_term_tree($formatted);
  }

  private function format_terms(array $terms, string $taxonomy): array
  {
    $menu = [];
    $location_name = $this->pageHelper->get_location();

    foreach ($terms as $term) {
      // Fetch thumbnail (Local fallback)
      $thumbnail = '';
      $image_field = get_field('category_thumbnail', $taxonomy . '_' . $term->term_id);
      if ($image_field) {
        if (is_array($image_field)) {
          $thumbnail = $image_field['url'];
        } elseif (is_numeric($image_field)) {
          $thumbnail = wp_get_attachment_image_url((int)$image_field, 'full');
        } else {
          $thumbnail = $image_field;
        }
      }

      // Fetch icon from remote data
      $icon = '';
      $remote_url = get_field('remote_page', $taxonomy . '_' . $term->term_id);

      if ($remote_url) {
        $remote_data = $this->repository->fetch($remote_url);
        if ($remote_data) {
          if (isset($remote_data->{'services-cat-icon'})) {
            $icon = $remote_data->{'services-cat-icon'};
          } elseif (isset($remote_data->{'services-cat-image'})) {
            $icon = $remote_data->{'services-cat-image'};
          }
        }
      }

      // Fetch remote data for extraction (random texts etc) if needed
      // Note: CategoryRepository handles some remote logic but we might need specific fields here
      $overview_text = $term->description;

      if ($remote_url) {
        $remote_data = $this->repository->fetch($remote_url);
        if ($remote_data) {
          $extracted_text = $this->extract_random_overview_text($remote_data);
          if ($extracted_text) $overview_text = $extracted_text;
        }
      }

      // Find local category page if it exists
      $local_page_url = esc_url(get_term_link($term));
      $page = get_page_by_path('service-kategorie/' . $term->slug) ?: get_page_by_title($term->name, OBJECT, 'page');
      if ($page) {
          $local_page_url = get_permalink($page->ID);
      }

      $menu[] = [
        'id'            => $term->term_id,
        'term_id'       => $term->term_id,
        'parent'        => $term->parent,
        'type'          => 'taxonomy',
        'object'        => $taxonomy,
        'title'         => $term->name,
        'link'          => $local_page_url,
        'url'           => $local_page_url,
        'link_title'    => $term->name,
        'location'      => $location_name,
        'icon'          => $icon, // From Remote Data
        'image_url'     => $thumbnail ?: '', // From Repository
        'overview_text' => $overview_text,
        'children'      => []
      ];
    }
    return $menu;
  }

  // =========================================================================
  // 2. RELATIONEN & SILO LOGIK
  // =========================================================================


  /**
   * Case: Standort-Umschalter
   * Zeigt: Liste von Bezirken
   * Link: Führt zur Service-Seite innerhalb jenes Bezirks
   */
  public function get_districts_for_current_service(): array
  {
    $service_id = $this->get_context_id(self::TAX_SERVICES);
    if (!$service_id) return [];

    // Alle Seiten dieses Services holen
    $args = [
      "post_type" => "page",
      "posts_per_page" => -1,
      "tax_query" => [["taxonomy" => self::TAX_SERVICES, "field" => "term_id", "terms" => $service_id]],
    ];

    $pages = get_posts($args);
    $menu = [];

    foreach ($pages as $post) {
      if ($post->ID === get_the_ID()) continue; // Aktuelle Seite ausschließen

      $district_terms = get_the_terms($post->ID, self::TAX_DISTRICTS);
      if ($district_terms && !is_wp_error($district_terms)) {
        $district = $district_terms[0];

        // Wir bauen ein Hybrid-Item: Titel vom Bezirk, Link zur Page
        $menu[] = [
          'id'         => $post->ID,
          'title'      => $district->name,
          'link'       => get_permalink($post->ID),
          'link_title' => $this->textReplacer->process('Wechsle zu ' . $district->name), // Process in case of weird chars, though ORT not relevant here
          'icon'       => 'location_icon',
          'location'   => $district->name,
          'image_url'  => '', // Optional: District Bild hier laden
        ];
      }
    }

    // Sortieren nach Bezirksname (A-Z)
    usort($menu, function ($a, $b) {
      return strcmp($a['title'], $b['title']);
    });
    return $menu;
  }

  /**
   * Case: Related Posts
   * Zeigt: Seiten im GLEICHEN Bezirk UND GLEICHEN Service
   */
  public function get_related_district_services(): array
  {
    $district_id = $this->get_context_id(self::TAX_DISTRICTS);
    $service_id  = $this->get_context_id(self::TAX_SERVICES);

    if (!$district_id || !$service_id) return [];

    $args = [
      'post_type'      => 'page',
      'posts_per_page' => -1,
      'orderby' => 'menu_order',
      'order' => 'ASC',
      'post__not_in'   => [get_the_ID()],
      'tax_query'      => [
        'relation' => 'AND',
        ['taxonomy' => self::TAX_DISTRICTS, 'field' => 'term_id', 'terms' => $district_id],
        ['taxonomy' => self::TAX_SERVICES, 'field' => 'term_id', 'terms' => $service_id],
      ],
    ];

    $pages = get_posts($args);
    $menu = [];
    foreach ($pages as $post) {
      $menu[] = $this->build_standardized_item($post->ID);
    }
    return $menu;
  }



  public function get_current_service_pages(): array
  {
    $service_id = $this->get_context_id(self::TAX_SERVICES);
    if ($service_id) return $this->get_pages_by_taxonomy(self::TAX_SERVICES, $service_id);
    return [];
  }

  public function get_services_in_district(): array
  {
    $location_name = $this->pageHelper->get_location();
    if (empty($location_name)) return [];

    // Alle Seiten, die ein Service sind (via Repository)
    $pages = $this->categoryRepository->get_all_service_pages();

    if (empty($pages)) return [];

    // OPTIMIZATION: Prime the term cache for all these pages at once
    $page_ids = array_map(function ($p) {
      return $p->ID;
    }, $pages);
    update_object_term_cache($page_ids, 'page');

    // Preload Remote Data
    $urls = [];
    foreach ($pages as $p) {
      $url = get_field('remote_page', $p->ID);
      if (empty($url) && $p->post_parent) {
        $url = get_field('remote_page', $p->post_parent);
      }
      if ($url) $urls[] = $url;
    }
    $this->repository->preload($urls);

    $menu = [];

    foreach ($pages as $post) {
      $item = $this->build_standardized_item($post->ID);
      // Link Lokalisierung erzwingen
      if ($item['location'] !== $location_name) {
        $item['link'] = $this->pageHelper->resolve_localized_link($post->ID, $location_name);
        $item['location'] = $location_name;
        // Use text replacement logic
        $raw_title = $item['title'] . ' - [ORT]';
        $item['link_title'] = $this->textReplacer->process($raw_title);
      }
      $menu[] = $item;
    }
    return $menu;
  }

  // =========================================================================
  // 3. HAUPTMENÜ & PRIMARY SWITCH
  // =========================================================================



  public function get_primary_menu_items(): array
  {
    if (!empty($this->menu_tree_cache)) return $this->menu_tree_cache;

    // Try WP Menu first
    $menu = $this->get_wp_fallback_menu();
    
    // If empty (no menu assigned), auto-generate a robust default structure
    if (empty($menu)) {
      $menu = $this->build_synthetic_primary_menu();
    }
    
    $this->menu_tree_cache = $menu;
    return $this->menu_tree_cache;
  }

  private function build_synthetic_primary_menu(): array
  {
    $menu = [];
    $home_url = home_url('/');
    $location_name = $this->pageHelper->get_location();
    
    // 1. Home
    $menu[] = (object) [
      'title' => 'Startseite',
      'url' => $home_url,
      'link' => $home_url,
      'children' => [],
      'object' => 'page',
      'object_id' => get_option('page_on_front'),
      'icon' => 'Home'
    ];

    // 2. Services
    $service_cats = $this->get_service_categories();
    $service_children = [];
    foreach ($service_cats as $cat) {
      $cat_obj = (object) $cat;
      // Make sure the object property is set to taxonomy for enrichment
      $cat_obj->object = self::TAX_SERVICES; 
      $service_children[] = $cat_obj;
    }
    
    $menu[] = (object) [
      'title' => 'Unsere Leistungen',
      'url' => '#',
      'link' => '#',
      'children' => $service_children,
      'object' => 'custom',
      'icon' => 'Services'
    ];

    // Get Business Data for contact
    $business = $this->pageHelper->get_options_business();
    $phone = $business['telefonnummer'] ?? '+43 676 681 20 90';
    $email = $business['e-mail'] ?? $business['email'] ?? 'office@example.com';

    // 3. Kontakt
    $menu[] = (object) [
      'title' => 'Kontakt',
      'url' => home_url('/kontakt/'),
      'link' => home_url('/kontakt/'),
      'children' => [
        (object) [
          'title' => $phone,
          'url' => 'tel:' . str_replace([' ', '/', '-'], '', $phone),
          'link' => 'tel:' . str_replace([' ', '/', '-'], '', $phone),
          'icon' => 'telefon',
          'object' => 'custom',
        ],
        (object) [
          'title' => $email,
          'url' => 'mailto:' . $email,
          'link' => 'mailto:' . $email,
          'icon' => 'email',
          'object' => 'custom',
        ]
      ],
      'object' => 'custom',
      'icon' => 'Formular'
    ];

    // 4. Rechtliches
    $menu[] = (object) [
      'title' => 'Rechtliches',
      'url' => '#',
      'link' => '#',
      'children' => [
        (object) [
          'title' => 'Über uns',
          'url' => home_url('/ueber-uns/'),
          'link' => home_url('/ueber-uns/'),
          'icon' => 'Info',
          'object' => 'page',
        ],
        (object) [
          'title' => 'Impressum',
          'url' => home_url('/impressum/'),
          'link' => home_url('/impressum/'),
          'icon' => 'Rechtliches',
          'object' => 'page',
        ],
        (object) [
          'title' => 'Datenschutz',
          'url' => home_url('/datenschutz/'),
          'link' => home_url('/datenschutz/'),
          'icon' => 'Sicherheit',
          'object' => 'page',
        ]
      ],
      'object' => 'custom',
      'icon' => 'Info'
    ];

    // Pass through enrich logic to populate pages under service_categories
    $menu = $this->enrich_menu_tree($menu);
    return $menu;
  }

  // =========================================================================
  // 4. CORE HELPER & BUILDER
  // =========================================================================

  private function get_context_id(string $taxonomy): int
  {
    if (is_tax($taxonomy)) return get_queried_object_id();

    if (is_single() || is_page()) {
      $current_page_id = get_queried_object_id();
      $terms = get_the_terms($current_page_id, $taxonomy);
      if ($terms && !is_wp_error($terms)) return $terms[0]->term_id;
    }
    return 0;
  }

  private function get_pages_by_taxonomy(string $taxonomy, int $term_id): array
  {
    $args = [
      "post_type" => "page",
      "orderby" => "menu_order",
      "order" => "ASC",
      "posts_per_page" => -1,
      "tax_query" => [["taxonomy" => $taxonomy, "field" => "term_id", "terms" => $term_id]],
    ];
    $pages = get_posts($args);

    // Preload Remote Data
    $urls = [];
    foreach ($pages as $p) {
      $url = get_field('remote_page', $p->ID);
      if (empty($url) && $p->post_parent) {
        $url = get_field('remote_page', $p->post_parent);
      }
      if ($url) $urls[] = $url;
    }
    $this->repository->preload($urls);

    $menu = [];
    foreach ($pages as $post) {
      $menu[] = $this->build_standardized_item($post->ID);
    }
    return $menu;
  }

  private function get_wp_fallback_menu(): array
  {
    $locations = get_nav_menu_locations();
    $menu_id = $locations['primary'] ?? null;
    
    file_put_contents(ABSPATH . 'debug_menu.txt', "Menu ID: " . ($menu_id ? $menu_id : 'none') . "\n", FILE_APPEND);

    if (!$menu_id) return [];

    $wp_items = wp_get_nav_menu_items($menu_id);
    if (!$wp_items) return [];

    file_put_contents(ABSPATH . 'debug_menu.txt', "WP Items Before: " . count($wp_items) . "\n", FILE_APPEND);

    // Filter out child pages (district pages) from the menu
    $filtered_items = [];
    foreach ($wp_items as $item) {
      $is_child_page = false;
      
      // Check 0: Zip code fallback
      if (preg_match('/\d{4}/', $item->title)) {
        $is_child_page = true;
      }
      
      if (!$is_child_page && $item->object === 'page') {
        $p_post = get_post($item->object_id);
        if ($p_post) {
          if ($p_post->post_parent != 0) {
            $is_child_page = true;
          }
          
          // Fallback 1: Generated district pages now get the 'district' taxonomy!
          $districts = get_the_terms($p_post->ID, 'district');
          if (!empty($districts) && !is_wp_error($districts)) {
             $is_child_page = true;
          }

          // Fallback 2: Main service pages ALWAYS have a 'service_category'. 
          $service_cats = get_the_terms($p_post->ID, 'service_category');
          if (empty($service_cats) || is_wp_error($service_cats)) {
            $is_child_page = true;
          }
        }
      } elseif (!$is_child_page && $item->object === 'custom') {
        $p_post = get_post(url_to_postid($item->url));
        if ($p_post && $p_post->post_type === 'page') {
          if ($p_post->post_parent != 0) {
             $is_child_page = true;
          }
          
          $districts = get_the_terms($p_post->ID, 'district');
          if (!empty($districts) && !is_wp_error($districts)) {
             $is_child_page = true;
          }

          $service_cats = get_the_terms($p_post->ID, 'service_category');
          if (empty($service_cats) || is_wp_error($service_cats)) {
            $is_child_page = true;
          }
        }
      }

      if ($is_child_page) {
        continue; // Skip district pages!
      }
      $filtered_items[] = $item;
    }
    $wp_items = $filtered_items;

    file_put_contents(ABSPATH . 'debug_menu.txt', "WP Items After: " . count($wp_items) . "\n", FILE_APPEND);

    $location_name = $this->pageHelper->get_location();

    // Preload Remote Data
    $urls = [];
    foreach ($wp_items as $item) {
      if ($item->object === 'page') {
        $pid = (int)$item->object_id;
        $url = get_field('remote_page', $pid);
        if (empty($url)) {
          $p_post = get_post($pid);
          if ($p_post && $p_post->post_parent) {
            $url = get_field('remote_page', $p_post->post_parent);
          }
        }
        if ($url) $urls[] = $url;
      }
    }
    $this->repository->preload($urls);

    foreach ($wp_items as &$item) {
      // Standardize Page Items
      if ($item->object === 'page') {
        $standardized = $this->build_standardized_item((int)$item->object_id);
        $item->image_url = $standardized['image_url'];
        $item->icon = $standardized['icon'];
        $item->overview_text = $standardized['overview_text'];
        $item->content = $standardized['content'] ?? '';

        if ($item->menu_item_parent == 0 && $location_name && !is_front_page()) {
          $item->url = $standardized['link'];
        }
      }
      // Standardize Service Category Items
      elseif ($item->object === 'services') {
        $term_id = (int)$item->object_id;
        // Fetch ACF data similar to format_terms
        $thumbnail = get_field('services_thumbnail', 'services_' . $term_id);
        $icon = get_field('services_icon', 'services_' . $term_id) ?: 'default_icon';
        // Fallback thumbnail
        if (!$thumbnail) {
          $thumbnail = get_field('category_thumbnail', 'services_' . $term_id);
        }

        $item->image_url = $thumbnail ?: '';
        $item->icon = $icon;
        $item->content = term_description($term_id, 'services'); // Overview text
      }
    }
    $this->menu_tree_cache = $this->build_page_tree($wp_items);

    // Inject related services into categories
    $this->menu_tree_cache = $this->enrich_menu_tree($this->menu_tree_cache);

    return $this->menu_tree_cache;
  }

  private function enrich_menu_tree(array $tree): array
  {
    foreach ($tree as $item) {
      // 1. Special Case: Startseite (Home) -> Removed as per simple menu request
      // We no longer inject Service Categories into the Home link.

      // 2. Regular Case: Service Category
      // We no longer append individual Service pages here as per requirements.
      // "das menu soll immer nur die service categorien anzeigen der rest ist location silo sache"

      // Recurse if there are children (newly added or existing)
      if (!empty($item->children)) {
        $item->children = $this->enrich_menu_tree($item->children);
      }
    }
    return $tree;
  }

  private function build_standardized_item(int $page_id): array
  {
    if (isset($this->item_cache[$page_id])) return $this->item_cache[$page_id];

    $post = get_post($page_id);
    if (!$post) return [];

    $location_name = $this->pageHelper->get_location();
    $clean_title = $this->textReplacer->clean_title(get_the_title($page_id));
    $permalink = get_the_permalink($page_id);

    if (!empty($location_name) && $post->post_parent == 0) {
      $permalink = $this->pageHelper->resolve_localized_link($page_id, $location_name);
    }

    $image_url = get_the_post_thumbnail_url($page_id, 'large');
    $overview_text = '';
    $remote_url = get_field('remote_page', $page_id);
    if (empty($remote_url) && $post->post_parent) $remote_url = get_field('remote_page', $post->post_parent);

    $remote_data = null;
    if ($remote_url) {
      $remote_data = $this->repository->fetch($remote_url);
      if ($remote_data) {
        $overview_text = $this->extract_random_overview_text($remote_data);
        if (empty($image_url)) $image_url = $remote_data->better_featured_image->media_details->sizes->medium_large->source_url ?? '';
      }
    }

    $icon = $this->pageHelper->get_page_icon($page_id, $remote_data, true, 'post');

    $item_data = [
      'id' => $page_id,
      'object_id' => $page_id,
      'title' => $clean_title,
      'link' => $permalink,
      'url' => $permalink,
      'link_title' => $this->textReplacer->process($clean_title . ($location_name ? ' - [ORT]' : '')),
      'location' => $location_name,
      'icon' => $icon,
      'image_url' => $image_url,
      'featured_image_url' => get_the_post_thumbnail_url($page_id, 'full'),
      'tooltip_image_url' => get_the_post_thumbnail_url($page_id, 'thumbnail'),
      'overview_text' => $overview_text,
      'content' => $overview_text,
      'remote_url' => $remote_url,
      'menu_item_parent' => 0,
      'children' => []
    ];

    $this->item_cache[$page_id] = $item_data;
    return $item_data;
  }

  private function extract_random_overview_text($all): string
  {
    $content = $all->acf->ubersichtstexte_radom ?? [];
    $dienstleistungen = $all->acf->dienstleistungen ?? [];
    if (is_array($content) && !empty($content)) {
      $shuffledText = $content[array_rand($content)];
      $text = $shuffledText->ubersichtstext_radom ?? '';
      if (!empty($text)) return $this->textReplacer->prozess_data($text, $dienstleistungen);
    }
    return '';
  }

  private function build_term_tree(array $elements, int $parentId = 0): array
  {
    $branch = [];
    foreach ($elements as $element) {
      if ($element['parent'] == $parentId) {
        $children = $this->build_term_tree($elements, $element['id']);
        if ($children) $element['children'] = $children;
        $branch[] = $element;
      }
    }
    return $branch;
  }

  private function build_page_tree(array &$elements, $parentId = 0): array
  {
    $branch = [];
    foreach ($elements as $element) {
      $elObj = (object) $element;

      // Handle both WP_Post (ID) and our standardized items (id or object_id)
      $itemParent = isset($elObj->menu_item_parent) ? $elObj->menu_item_parent : 0;
      $itemId = isset($elObj->ID) ? $elObj->ID : (isset($elObj->id) ? $elObj->id : (isset($elObj->object_id) ? $elObj->object_id : 0));

      if ($itemParent == $parentId) {
        // Critical: Avoid infinite recursion if itemId is same as parentId (or both 0)
        if ($itemId != 0 && $itemId != $parentId) {
          $children = $this->build_page_tree($elements, $itemId);
          if ($children) $elObj->children = $children;
        }
        $branch[] = $elObj;
      }
    }
    return $branch;
  }
}
