<?php

namespace SeopressComposer\Services;

use SeopressComposer\Helpers\PageHelper;
use SeopressComposer\Repositories\RemoteDataRepository;

class TextReplacementService
{
  private PageHelper $pageHelper;
  private RemoteDataRepository $repository;
  private array $shortcodes = [];
  private array $shortcodes_listen = [];

  public function __construct(PageHelper $pageHelper, RemoteDataRepository $repository)
  {
    $this->pageHelper = $pageHelper;
    $this->repository = $repository;
    $this->initShortcodes();

    // Register variables with Yoast SEO if available
    if (function_exists('add_filter')) {
      add_filter('wpseo_replacements', [$this, 'add_custom_yoast_replacements'], 1);
      // Filter the final title output to ensure custom variables are replaced
      add_filter('wpseo_title', [$this, 'filter_yoast_title'], 999, 1);
      add_filter('wpseo_opengraph_title', [$this, 'filter_yoast_title'], 999, 1);
      add_filter('wpseo_twitter_title', [$this, 'filter_yoast_title'], 999, 1);

      add_filter('wpseo_metadesc', [$this, 'filter_yoast_desc'], 999, 1);
      add_filter('wpseo_opengraph_desc', [$this, 'filter_yoast_desc'], 999, 1);
      add_filter('wpseo_twitter_description', [$this, 'filter_yoast_desc'], 999, 1);
    }
  }

  /**
   * Filter Yoast SEO title output to replace custom variables
   */
  public function filter_yoast_title($title)
  {
    $override = $this->get_remote_override('yoast_title_raw');
    if ($override) {
      $title = $override;
    }
    return $this->process($title);
  }

  /**
   * Filter Yoast SEO description output with remote override
   */
  public function filter_yoast_desc($desc)
  {
    $override = $this->get_remote_override('yoast_desc_raw');
    if ($override) {
      $desc = $override;
    }
    // Handle null/false descriptions gracefully
    if (empty($desc)) {
      return '';
    }
    return $this->process($desc);
  }

  /**
   * Helper to retrieve remote override field based on context
   */
  private function get_remote_override(string $field): ?string
  {
    $remote_url = '';

    // 1. Check current queried object
    $obj = get_queried_object();

    // Explicit check for service_category if object is missing or not a term (safety net)
    if ((!$obj || !($obj instanceof \WP_Term)) && is_tax('service_category')) {
      $term_id = get_queried_object_id();
      if ($term_id) {
        $obj = get_term($term_id, 'service_category');
      }
    }

    if ($obj instanceof \WP_Post) {
      $data_id = $this->pageHelper->check_page_id($obj->ID);
      $remote_url = get_field('remote_page', $data_id);
    } elseif ($obj instanceof \WP_Term) {
      $remote_url = get_field('remote_page', $obj->taxonomy . '_' . $obj->term_id);
    }

    if (empty($remote_url)) {
      return null;
    }

    // 2. Fetch Remote Data
    $data = $this->repository->fetch((string)$remote_url);
    if (!$data) {
      return null;
    }

    // 3. Extract Field
    // Handle array vs object (RemoteDataRepository usually returns object from json_decode, but let's be safe)
    $data_obj = is_array($data) ? ($data[0] ?? null) : $data;

    if ($data_obj && isset($data_obj->$field) && !empty($data_obj->$field)) {
      return $data_obj->$field;
    }

    return null;
  }

  /**
   * Register custom variables for Yoast SEO
   */
  public function add_custom_yoast_replacements($replacements)
  {
    // Add custom variables (ORT) — with einsatzgebiet fallback from site settings
    $location = $this->pageHelper->get_location();
    if (empty($location)) {
      $location = $this->pageHelper->get_option('einsatzgebiet') ?: '';
    }
    $replacements['%%ORT%%'] = $location;

    // Single Words
    if (!empty($this->shortcodes)) {
      foreach ($this->shortcodes as $key => $element) {
        $replacements['%%' . $key . '%%'] = $this->getRandomElement($element);
      }
    }

    // Lists
    if (!empty($this->shortcodes_listen)) {
      foreach ($this->shortcodes_listen as $key => $element) {
        $replacements['%%' . $key . '%%'] = $this->getRandomList($element, $key);
      }
    }

    return $replacements;
  }

  public function process(string $content, array $dienstleistungen = []): string
  {
    // 1. Build all replacements first
    $replacements = [];

    // Basics
    $location = $this->pageHelper->get_location();
    // Fallback: if no location resolved (no district, no local ACF), use global einsatzgebiet from site settings
    if (empty($location)) {
      $location = $this->pageHelper->get_option('einsatzgebiet') ?: '';
    }
    $replacements['[ORT]'] = $location;
    $replacements['%%ORT%%'] = $location;
    $replacements['%%LOCATION%%'] = $location;
    $replacements['[SITE_TITLE]'] = get_bloginfo('name');
    $replacements['%%sitename%%'] = get_bloginfo('name');

    // Yoast compatibility patterns — Remote API may deliver unresolved Yoast variables
    // %%sep%% is the Yoast title separator (typically "–" or "|"), NOT a category name!
    $replacements['%%sep%%'] = '–';
    $replacements['%%sitedesc%%'] = get_bloginfo('description');
    $replacements['%%page%%'] = '';
    $replacements['%%primary_category%%'] = '';
    $replacements['%%currentdate%%'] = date_i18n('Y');
    $replacements['%%currentyear%%'] = date('Y');

    // Contextual Fallbacks
    $obj = get_queried_object();
    if (!$obj && is_tax('service_category')) {
      $term_id = get_queried_object_id();
      $obj = get_term($term_id, 'service_category');
    }

    if ($obj instanceof \WP_Term) {
      $replacements['%%term_title%%'] = $obj->name;
      // Also support standard title for terms if used erroneously
      $replacements['%%title%%'] = $obj->name;
    } elseif ($obj instanceof \WP_Post) {
      $replacements['%%title%%'] = $obj->post_title;
    }

    $bl = $this->pageHelper->get_option('bundesland');
    $replacements['[BL]'] = $bl ?: '';

    // Service Categories List
    $service_cats = $this->get_service_cat_list();
    $replacements['[SERVICE_CAT]'] = $service_cats;
    $replacements['%%SERVICE_CAT%%'] = $service_cats;
    $replacements['%%service_cat%%'] = $service_cats;

    // Dictionary Shortcodes
    if (!empty($this->shortcodes)) {
      foreach ($this->shortcodes as $placeholder => $options) {
        $val = $this->getRandomElement($options);
        $replacements["[$placeholder]"] = $val;
        $replacements["%%$placeholder%%"] = $val;
      }
    }
    if (!empty($this->shortcodes_listen)) {
      foreach ($this->shortcodes_listen as $placeholder => $options) {
        $val = $this->getRandomList($options, $placeholder);
        $replacements["[$placeholder]"] = $val;
        $replacements["%%$placeholder%%"] = $val;
      }
    }

    // [DL] Logic
    if (str_contains($content, '[DL]')) {
      $replacements['[DL]'] = $this->resolve_dl($dienstleistungen);
    }

    // 2. Apply all custom replacements tag-safely
    return $this->apply_replacements_tag_safe($content, $replacements);
  }

  private function resolve_dl(array $dienstleistungen): string
  {
    $queried_object = get_queried_object();
    if ($queried_object instanceof \WP_Term && $queried_object->taxonomy === 'service_category') {
      return $queried_object->name;
    }
    if (!empty($dienstleistungen)) {
      return $this->get_random_dienstleistung($dienstleistungen);
    }
    $remote_url = $this->pageHelper->get_remote_url();
    if ($remote_url) {
      $data = $this->repository->fetch($remote_url);
      if ($data && !empty($data->acf->dienstleistungen)) {
        return $this->get_random_dienstleistung($data->acf->dienstleistungen);
      }
    }
    return 'Entrümpelung';
  }

  private function apply_replacements_tag_safe(string $content, array $replacements): string
  {
    if (empty($replacements)) return $content;

    // Protection for HTML tags: split the content by tags
    $parts = preg_split('/(<[^>]+>)/u', $content, -1, PREG_SPLIT_DELIM_CAPTURE);

    foreach ($parts as &$part) {
      // Only process text nodes, skip HTML tags (starting with <)
      if (isset($part[0]) && $part[0] === '<') {
        continue;
      }

      foreach ($replacements as $search => $replace) {
        $part = str_ireplace($search, (string)$replace, $part);
      }

      // Clean up any remaining unresolved %%...%% variables natively since Yoast is removed.
      // E.g., %%profis%% becomes Profis, ensuring the sentence remains grammatically intact.
      if (strpos($part, '%%') !== false) {
        $part = preg_replace_callback('/%%([a-zA-Z0-9_\-]+)%%/u', function($matches) {
          return ucfirst(strtolower($matches[1]));
        }, $part);
      }
    }

    return implode('', $parts);
  }


  private function getRandomElement($elements)
  {
    if (is_array($elements) && !empty($elements)) {
      return $elements[array_rand($elements)];
    }
    return is_string($elements) ? $elements : '';
  }

  private function getRandomList($elements, string $placeholder = ''): string
  {
    if (is_array($elements) && !empty($elements)) {
      // Determine how many items to return
      $count = 6; // Default
      if ($placeholder && preg_match('/(\d+)$/', $placeholder, $matches)) {
        $count = (int)$matches[1];
      }

      // If count is 0 (like in LI0), maybe pick a random small number or just 1
      if ($count <= 0) $count = 6;

      $elements = array_unique($elements);
      shuffle($elements);

      $selection = array_slice($elements, 0, $count);
      return implode(", ", $selection);
    }
    return '';
  }

  private function initShortcodes()
  {
    $api_url = $this->pageHelper->get_option('synonyms_api_endpoint', 'https://www.xn--entrmpelung-whb.at/api/wp-json/custom/v1/synonyms');

    // Fetch via RemoteDataRepository — which has 2-layer caching (wp_cache + transient, 1h TTL).
    // Previously this appended ?v=time() which defeated the repository cache on every request.
    // Now the clean URL flows through the repo's native cache: zero external HTTP requests on warm cache.
    $data = $this->repository->fetch($api_url);

    if ($data) {
      $dataArray = json_decode(json_encode($data), true);

      // Normalize keys to lowercase for easier matching
      $normalizedData = [];
      $this->flattenAndNormalize($dataArray, $normalizedData);

      // 1. Dynamic generation based on patterns
      foreach ($normalizedData as $key => $value) {
        // Pattern: *wo[number] -> *WORTE[number]
        if (preg_match('/^(.*)wo(\d*)$/i', $key, $matches)) {
          $stem = strtoupper($matches[1]);
          $num = $matches[2];

          // 1. Standard: STEM + WORTE + NUM
          $this->shortcodes[$stem . 'WORTE' . $num] = $value;

          // 2. Legacy/Direct: STEM + WO + NUM
          $this->shortcodes[$stem . 'WO' . $num] = $value;

          // 3. Smart 'S' handling (verlassenschaftswo -> VERLASSENSCHAFTWO)
          if (substr($stem, -1) === 'S') {
            $stemNoS = substr($stem, 0, -1);
            $this->shortcodes[$stemNoS . 'WORTE' . $num] = $value;
            $this->shortcodes[$stemNoS . 'WO' . $num] = $value;
          }
        }
        // Pattern: *li[number] -> *LISTE[number]
        elseif (preg_match('/^(.*)li(\d*)$/i', $key, $matches)) {
          $stem = strtoupper($matches[1]);
          $num = $matches[2];

          // 1. Standard: STEM + LISTE + NUM
          $this->shortcodes_listen[$stem . 'LISTE' . $num] = $value;

          // 2. Short: STEM + LI + NUM
          $this->shortcodes_listen[$stem . 'LI' . $num] = $value;
        }
        // Pattern: Already *WORTE[number]
        elseif (preg_match('/worte(\d*)$/i', $key)) {
          $this->shortcodes[strtoupper($key)] = $value;
        }
        // Pattern: Already *LISTE[number]
        elseif (preg_match('/liste(\d*)$/i', $key)) {
          $this->shortcodes_listen[strtoupper($key)] = $value;
        }
        // NEW: Pattern: simple stems (e.g. 'gem')
        else {
          $this->shortcodes[strtoupper($key)] = $value;
        }
      }

      // 2. Manual overrides/additions from mapping
      $shortcodeMapping = $this->getShortcodeMapping();
      foreach ($shortcodeMapping as $apiKey => $placeholders) {
        if (isset($normalizedData[strtolower($apiKey)])) {
          foreach ($placeholders as $placeholder) {
            $this->shortcodes[$placeholder] = $normalizedData[strtolower($apiKey)];
          }
        }
      }

      $listMapping = $this->getListMapping();
      foreach ($listMapping as $apiKey => $placeholders) {
        if (isset($normalizedData[strtolower($apiKey)])) {
          foreach ($placeholders as $placeholder) {
            $this->shortcodes_listen[$placeholder] = $normalizedData[strtolower($apiKey)];
          }
        }
      }
    }
  }

  private function flattenAndNormalize($array, &$result)
  {
    foreach ($array as $key => $value) {
      if (is_array($value) && !isset($value[0])) {
        // Associative array, recurse
        $this->flattenAndNormalize($value, $result);
      } else {
        // Indexed array (list of words) or string
        $result[strtolower($key)] = $value;
      }
    }
  }

  private function getShortcodeMapping()
  {
    return [
      'unternehmenwo' => ['DASDERUNTERNEHMENWORTE', 'DERDASUNTERNEHMENWORTE', 'UNTERNEHMENWORTE'],
      'firmawo' => ['DIEFIRMAWORTE', 'FIRMAWORTE'],
      // dynamic handling covers others
      'installierwo' => ['INSTALLIERTWORTE'],
      'kompetentwo' => ['KOMPETENZWORTE', 'KOMPETENTWORTE'],
      'ueberwachungswo' => ['ÜBERWACHUNGSWORTE'],
      'ueberwachungswo2' => ['ÜBERWACHUNGSWORTE2'],
      'zuverlaessigwo' => ['ZUVERLÄSSIGWORTE'],
    ];
  }

  private function getListMapping()
  {
    return [];
  }
  /**
   * Get a random service from the list
   */
  public function get_random_dienstleistung($dienstleistungen)
  {
    if (!empty($dienstleistungen) && is_array($dienstleistungen)) {
      shuffle($dienstleistungen);
      $item = $dienstleistungen[0];
      return is_object($item) ? ($item->dienstleistung ?? '') : ($item['dienstleistung'] ?? '');
    }
    return '';
  }

  /**
   * Find a service from the list that exists in the content
   */
  public function find_matching_service(string $content, array $dienstleistungen): string
  {
    foreach ($dienstleistungen as $item) {
      $term = is_object($item) ? ($item->dienstleistung ?? '') : ($item['dienstleistung'] ?? '');
      // Check if term exists (independent of case)
      if ($term && stripos($content, $term) !== false) {
        return $term;
      }
    }
    return '';
  }

  /**
   * Process content with highlighter and service replacement
   */
  public function prozess_data($content, $dienstleistungen = [], $strip_tags = true, $manualHighlightTerm = '', $apply_filters = false)
  {
    // 1. Core replacement process
    $content = $this->process((string)$content, (array)$dienstleistungen);

    // 4. Apply WordPress filters to the content (like wpautop)
    if ($apply_filters) {
      $content = apply_filters('the_content', $content);
    }

    // 5. Strip tags
    if ($strip_tags === true) {
      $content = strip_tags($content, '<strong><b>');
    } elseif (is_string($strip_tags)) {
      $content = strip_tags($content, $strip_tags);
    }

    // 6. Highlighter
    $highlightTerm = $manualHighlightTerm;
    if (empty($highlightTerm)) {
      // Try to find a service from the list that is actually in the text
      if (!empty($dienstleistungen)) {
        $highlightTerm = $this->find_matching_service($content, $dienstleistungen);
      }
      // Fallback to random service from list or default
      if (empty($highlightTerm)) {
        $highlightTerm = !empty($dienstleistungen) ? $this->get_random_dienstleistung($dienstleistungen) : 'Entrümpelung';
      }
    }

    if (!empty($highlightTerm)) {
      $content = $this->highlighter($content, $highlightTerm);
    }

    return $content;
  }

  /**
   * Highlight specific words in content
   */
  public function highlighter($content, $word): string
  {
    if (empty($word) || empty($content)) return (string)$content;

    // Static page-level counter: persists across all component calls in a single request.
    // This prevents keyword spam where every component's first occurrence gets bolded.
    // Max 2 <strong> tags per unique keyword per page.
    static $page_bold_count = [];
    $word_key = md5(mb_strtolower($word));
    if (!isset($page_bold_count[$word_key])) {
      $page_bold_count[$word_key] = 0;
    }

    $parts   = preg_split('/(<[^>]+>)/u', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
    $pattern = '/' . preg_quote($word, '/') . '/iu';

    foreach ($parts as &$part) {
      if (isset($part[0]) && $part[0] === '<') continue;

      $part = preg_replace_callback(
        $pattern,
        function ($matches) use (&$page_bold_count, $word_key) {
          $page_bold_count[$word_key]++;
          // Bold only the 1st and 2nd occurrence across the entire page (all components)
          return ($page_bold_count[$word_key] <= 2)
            ? '<strong>' . $matches[0] . '</strong>'
            : $matches[0];
        },
        $part
      );
    }

    return implode('', $parts);
  }

  public function clean_title($title): string
  {
    if (!is_string($title)) {
      return '';
    }

    // Decode entities first to handle things like &#038; (ampersand)
    $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    $location = $this->pageHelper->get_location();

    $bl = $this->pageHelper->get_option('einsatzgebiet');

    $locationname = is_array($location) ? ($location['name'] ?? '') : $location;
    $bl = (is_string($bl) || is_array($bl)) ? $bl : ''; // ACF can return array if select? usually text.

    if ($locationname) {
      $title = str_replace($locationname, '', $title);
    }

    if ($bl && is_string($bl)) {
      $title = str_replace($bl, '', $title);
    }

    $replacements = [
      'für' => '',
      'Haushaltsauflösungen und Wohnungsauflösungen' => 'Wohnungsauflösungen',
      '& Geschäftsauflösungen' => '',
      'Hotelräumungen' => 'Hotelauflösungen',
      'Büroräumungen' => 'Büroauflösungen',
      'Umzüge' => 'Umzüge & Übersiedlungen',
      'Betriebs- &' => '',
      'Geschäfts- &' => '',
      'Altwaren und ' => '',
      '& Altwaren' => '',
      '-' => '',
    ];

    foreach ($replacements as $search => $replace) {
      $title = str_replace($search, $replace, $title);
    }

    // Use clean_location logic to remove PLZ and generic region names from the remaining title
    $title = $this->clean_location($title);

    // Final cleanup of dangling separators
    $title = trim($title, " \t\n\r\0\x0B&-,|");
    $title = preg_replace('/\s+/', ' ', $title);

    return $title;
  }

  /**
   * Clean a location string (e.g. "1060 Wien Mariahilf" -> "Mariahilf")
   */
  public function clean_location(string $location): string
  {
    // Remove PLZ (4 digits)
    $location = preg_replace('/\b\d{4,5}\b/u', '', $location);

    // Get generic region from options (e.g. "Wien", "Niederösterreich")
    $bl = get_field("einsatzgebiet", 'option');
    if ($bl && is_string($bl)) {
      $location = str_ireplace($bl, '', $location);
    }

    // Common generic parts to remove
    $generics = [
      'Wien',
      'Österreich',
      'Bezirk',
      'Stadt'
    ];

    foreach ($generics as $generic) {
      $location = str_ireplace($generic, '', $location);
    }

    // Clean up separators and extra spaces
    $location = str_replace(['-', ',', '|'], ' ', $location);
    $location = preg_replace('/\s+/', ' ', $location);

    return trim($location);
  }

  /**
   * Get service category name — context-aware to prevent concatenated list in price fields
   */
  private function get_service_cat_list(): string
  {
    // On taxonomy archive pages: return just the current term name
    $obj = get_queried_object();
    if ($obj instanceof \WP_Term) {
      return $obj->name;
    }

    // On singular pages: return their assigned service_category name
    if (is_singular()) {
      $terms = get_the_terms(get_the_ID(), 'service_category');
      if (!empty($terms) && !is_wp_error($terms)) {
        return $terms[0]->name;
      }
    }

    // Fallback: comma-separated list (for general/uncategorized contexts)
    $terms = get_terms([
      'taxonomy'   => 'service_category',
      'hide_empty' => false,
      'fields'     => 'names',
    ]);

    if (!empty($terms) && !is_wp_error($terms)) {
      return implode(', ', $terms);
    }
    return '';
  }
}
