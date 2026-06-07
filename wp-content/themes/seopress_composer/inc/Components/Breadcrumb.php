<?php

namespace SeopressComposer\Components;

/**
 * Breadcrumb Component
 * Generiert einen nativen Breadcrumb-Pfad und das dazugehörige JSON-LD Schema.
 * Keine Abhängigkeit mehr zu Yoast SEO.
 */
class Breadcrumb
{
  public function render(): string
  {
    if (is_front_page()) {
      return '';
    }

    $breadcrumbs = $this->build_breadcrumb_data();
    if (empty($breadcrumbs)) {
      return '';
    }

    $html = $this->render_html($breadcrumbs);
    $html .= $this->render_schema($breadcrumbs);

    return $html;
  }

  private function build_breadcrumb_data(): array
  {
    $breadcrumbs = [];
    
    // 1. Home
    $breadcrumbs[] = [
      'name' => 'Startseite',
      'url'  => home_url('/')
    ];

    // 2. Aktuelle Seite/Archiv
    if (is_singular()) {
      $post = get_post();
      
      // Wenn es Parent-Seiten gibt
      if ($post->post_parent) {
        $ancestors = get_post_ancestors($post->ID);
        $ancestors = array_reverse($ancestors);
        foreach ($ancestors as $ancestor) {
          $breadcrumbs[] = [
            'name' => get_the_title($ancestor),
            'url'  => get_permalink($ancestor)
          ];
        }
      }

      // Aktuelle Seite
      $breadcrumbs[] = [
        'name' => get_the_title(),
        'url'  => get_permalink()
      ];

    } elseif (is_category() || is_tax()) {
      $term = get_queried_object();
      
      // Add parent term chain for hierarchical taxonomies
      if ($term && $term->parent) {
        $ancestors = get_ancestors($term->term_id, $term->taxonomy, 'taxonomy');
        foreach (array_reverse($ancestors) as $ancestor_id) {
          $ancestor = get_term($ancestor_id, $term->taxonomy);
          if ($ancestor && !is_wp_error($ancestor)) {
            $ancestor_link = get_term_link($ancestor);
            if (!is_wp_error($ancestor_link)) {
              $breadcrumbs[] = [
                'name' => $ancestor->name,
                'url'  => $ancestor_link
              ];
            }
          }
        }
      }
      
      $breadcrumbs[] = [
        'name' => $term->name,
        'url'  => get_term_link($term)
      ];
    } elseif (is_archive()) {
      $breadcrumbs[] = [
        'name' => get_the_archive_title(),
        'url'  => get_post_type_archive_link(get_post_type())
      ];
    } elseif (is_search()) {
      $breadcrumbs[] = [
        'name' => 'Suche: ' . get_search_query(),
        'url'  => home_url(add_query_arg(['s' => get_search_query()], '/'))
      ];
    } elseif (is_404()) {
      $breadcrumbs[] = [
        'name' => 'Seite nicht gefunden',
        'url'  => ''
      ];
    }

    return $breadcrumbs;
  }

  private function render_html(array $breadcrumbs): string
  {
    ob_start();
?>
    <nav aria-label="Breadcrumb" class="w-full bg-base-200 py-3 hidden md:block border-b border-base-300">
      <div class="w-full px-4 lg:px-8 text-sm text-base-content/70">
        <div class="breadcrumbs-content flex flex-wrap items-center gap-2">
          <?php foreach ($breadcrumbs as $index => $crumb): ?>
            <span class="flex items-center">
              <?php if ($index === count($breadcrumbs) - 1 || empty($crumb['url'])): ?>
                <span class="text-base-content" aria-current="page"><?= esc_html($crumb['name']) ?></span>
              <?php else: ?>
                <a href="<?= esc_url($crumb['url']) ?>" class="hover:text-primary hover:underline transition-colors">
                  <?= esc_html($crumb['name']) ?>
                </a>
                <span class="mx-2 opacity-50 select-none" aria-hidden="true">/</span>
              <?php endif; ?>
            </span>
          <?php endforeach; ?>
        </div>
      </div>
    </nav>
<?php
    return ob_get_clean();
  }

  /**
   * Get breadcrumb data for @graph integration in SchemaService
   */
  public function get_schema_data(): array
  {
    if (is_front_page()) return [];
    
    $breadcrumbs = $this->build_breadcrumb_data();
    if (empty($breadcrumbs)) return [];
    
    return $this->build_schema_array($breadcrumbs);
  }

  private function render_schema(array $breadcrumbs): string
  {
    // Schema is now rendered centrally via SchemaService @graph
    // No separate script tag needed
    return '';
  }

  private function build_schema_array(array $breadcrumbs): array
  {
    $itemListElement = [];
    foreach ($breadcrumbs as $index => $crumb) {
      $item = [
        '@type'    => 'ListItem',
        'position' => $index + 1,
        'name'     => $crumb['name']
      ];
      if (!empty($crumb['url'])) {
        $item['item'] = $crumb['url'];
      }
      $itemListElement[] = $item;
    }

    return [
      '@type'           => 'BreadcrumbList',
      'itemListElement' => $itemListElement
    ];
  }
}
