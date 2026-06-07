<?php

namespace SeopressComposer\Models;

class SiteSettings_Data
{
  private const CACHE_GROUP = 'seopress_site_settings';

  /**
   * Get all business options
   * 
   * @param mixed $page_id Unused in this context but required by abstract
   * @return array
   */
  public function get_data($page_id = null): array
  {
    $fields = [
      'inhaber',
      'branche',
      'strasse',
      'plz',
      'ort',
      'firmenname',
      'firmenbuchnummer',
      'gerichtsstand',
      'homepage',
      'bundesland',
      'bundesland_icon',
      'einsatzgebiet',
      'country',
      'e-mail',
      'angezeigte_telefonnummer',
      'telefonnummer',
      'angezeigte_festnetznummer',
      'festnetznummer',
      'uid',
      'gln',
      'urheberrecht',
      'berechtigungen',
      'contact_form_id',
      'call_back_form_id',
      'google_site_verification'
    ];

    return $this->get_options($fields);
  }

  /**
   * Get multiple options
   * 
   * @param array $selectors
   * @return array
   */
  public function get_options(array $selectors): array
  {
    $results = [];
    foreach ($selectors as $selector) {
      $results[$selector] = $this->get_option($selector);
    }
    return $results;
  }

  /**
   * Get single option with wp_cache
   * 
   * @param string $key
   * @param mixed $default
   * @return mixed
   */
  public function get_option(string $key, $default = null)
  {
    $cache_key = 'option_' . $key;
    $found = false;
    $value = wp_cache_get($cache_key, self::CACHE_GROUP, false, $found);

    if ($found) {
      return $value;
    }

    // Retrieval Logic
    $value = get_field($key, 'option');

    // Fallback to raw option
    if ($value === null) {
      $value = \get_option('options_' . $key);
    }

    if ($value === null || $value === false || $value === '') {
      $value = $default;
    }

    // Cache the result
    wp_cache_set($cache_key, $value, self::CACHE_GROUP);

    return $value;
  }
}
