<?php

namespace SeopressComposer\Models;

class FooterLinks_Data
{
  private Taxonomy_Data $taxonomyData;

  public function __construct(Taxonomy_Data $taxonomyData)
  {
    $this->taxonomyData = $taxonomyData;
  }

  public function get_data(): array
  {
    return [
      'copyright' => sprintf(
        '&copy; %s %s. Alle Rechte vorbehalten.',
        date('Y'),
        get_bloginfo('name')
      ),
      'links' => $this->get_legal_links()
    ];
  }

  private function get_legal_links(): array
  {
    $links = [];

    // Fetch pages using legal templates
    $query = new \WP_Query([
      'post_type' => 'page',
      'posts_per_page' => -1,
      'meta_query' => [
        [
          'key' => '_wp_page_template',
          'value' => ['template-impressum.php', 'template-datenschutz-agb.php'],
          'compare' => 'IN'
        ]
      ],
      'orderby' => 'menu_order title',
      'order' => 'ASC'
    ]);

    if ($query->have_posts()) {
      foreach ($query->posts as $page) {
        $links[] = [
          'title' => $page->post_title,
          'url' => get_permalink($page->ID),
        ];
      }
      return $links;
    }

    // Fetch legal menu items using Taxonomy_Data
    // Try 'rechtliches' first, then 'legal'
    $legalPages = $this->taxonomyData->get_pages_by_menu_category('rechtliches');
    if (empty($legalPages)) {
      $legalPages = $this->taxonomyData->get_pages_by_menu_category('legal');
    }

    if (!empty($legalPages)) {
      foreach ($legalPages as $page) {
        $links[] = [
          'title' => $page['title'],
          'url' => $page['link'], // DataFactory returns 'link'
        ];
      }
    }

    // Fallback if no taxonomy menu found (keep basic fallback or rely on setup)
    if (empty($links)) {
      $slugs = ['impressum', 'datenschutz', 'agb', 'cookie-richtlinie'];
      $found_pages = get_posts([
        'post_type' => 'page',
        'post_name__in' => $slugs,
        'posts_per_page' => -1,
        'suppress_filters' => false
      ]);

      foreach ($slugs as $slug) {
        foreach ($found_pages as $p) {
          if ($p->post_name === $slug) {
            $links[] = [
              'title' => $p->post_title,
              'url' => get_permalink($p->ID),
            ];
            break;
          }
        }
      }
    }

    return $links;
  }
}
