<?php

namespace SeopressComposer\Components;

/**
 * Cards Component
 * Renders a simple grid of cards
 */
class Cards
{
  use \SeopressComposer\Components\Traits\ModelRenderable;

  /**
   * Render the cards section
   * 
   * @param array $cards Array of card data
   * @param array|string|null $section_title Data for SectionTitle component
   * @return string
   */
  public function render(array $cards, $section_title = null): string
  {
    if (empty($cards)) {
      return '';
    }

    $components = seopress_components();

    ob_start();
?>
    <section class="py-24 lg:py-32 bg-base-100">
      <div class="container mx-auto px-4">
        <?php if ($section_title): ?>
          <?php
          if (is_array($section_title)) {
            echo $components->getSectionTitle()->render(
              $section_title['title'] ?? ($section_title[0] ?? ''),
              $section_title['description'] ?? ($section_title[1] ?? '')
            );
          } else {
            echo $components->getSectionTitle()->render($section_title);
          }
          ?>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <?php foreach ($cards as $item): ?>
            <?= $components->getCard()->renderOne($item); ?>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
<?php
    return ob_get_clean();
  }
}
