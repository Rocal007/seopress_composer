<?php

namespace SeopressComposer\Core\Admin;

class RemotePageFields
{
  public function __construct()
  {
    add_action('acf/init', [$this, 'register_field_group']);
  }

  public function register_field_group()
  {
    if (function_exists('acf_add_local_field_group')) {
      $templates = [
        'template-startseite.php',
        'template-hauptseiten.php',
        'template-kontakt.php',
        'template-impressum.php',
        'template-entsorgung.php',
        'template-videos.php',
        'template-faq.php',
        'template-tipps.php',
        'template-search.php',
        'template-kosten.php',
        'template-blog.php',
        'template-ratgeber.php',
      ];


      $locations = array_map(fn($template) => [[
        'param' => 'page_template',
        'operator' => '==',
        'value' => $template,
      ]], $templates);

      // Add Service Category taxonomy
      $locations[] = [[
        'param' => 'taxonomy',
        'operator' => '==',
        'value' => 'service_category',
      ]];

      acf_add_local_field_group([
        'key' => 'group_remote_page_fields',
        'title' => 'Remote Page eintragen',
        'fields' => [
          [
            'key' => 'field_remote_page_url',
            'label' => 'Remote Page',
            'name' => 'remote_page',
            'type' => 'url',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [],
            'default_value' => '',
            'placeholder' => '',
          ],
        ],
        'location' => $locations,
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'left',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
      ]);
    }
  }
}
