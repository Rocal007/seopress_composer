<?php
namespace SeopressComposer\Core;

class AcfManager
{
  public function __construct()
  {
    add_action('acf/init', [$this, 'register_options_page']);
    add_action('acf/init', [$this, 'register_field_groups']);
  }

  public function register_options_page()
  {
    if (function_exists('acf_add_options_page')) {
      acf_add_options_page([
        'page_title' => 'Theme Settings',
        'menu_title' => 'Theme Settings',
        'menu_slug' => 'theme-settings',
        'capability' => 'edit_posts',
        'redirect' => false
      ]);
    }
  }

  public function register_field_groups()
  {
    if (function_exists('acf_add_local_field_group')) {
      acf_add_local_field_group([
        'key' => 'group_theme_api_settings',
        'title' => 'API Settings',
        'fields' => [
          [
            'key' => 'field_districts_api_url',
            'label' => 'Districts API URL',
            'name' => 'districts_api_url',
            'type' => 'url',
            'instructions' => 'Enter the URL for the Districts API (e.g., https://.../ortsinfos/)',
            'required' => 0,
            'default_value' => 'https://www.xn--entrmpelung-whb.at/locations_api/wp-json/seopressortsinfos/v1/ortsinfos/',
          ]
        ],
        'location' => [
          [
            [
              'param' => 'options_page',
              'operator' => '==',
              'value' => 'theme-settings',
            ],
          ],
        ],
      ]);
    }
  }
}
