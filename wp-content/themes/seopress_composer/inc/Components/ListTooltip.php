<?php

namespace SeopressComposer\Components;

use SeopressComposer\Services\IconService;

/**
 * ListTooltip Component - Interactive list with tooltips
 * Enhanced with DaisyUI cards and tooltips
 */
class ListTooltip
{
  private TooltipCard $tooltipCard;

  public function __construct(TooltipCard $tooltipCard)
  {
    $this->tooltipCard = $tooltipCard;
  }

  use \SeopressComposer\Components\Traits\ModelRenderable;

  public function render(array $data = []): string
  {
    if (empty($data)) {
      return '';
    }

    $items = [];
    if (isset($data['items']) && is_array($data['items'])) {
      $items = $data['items'];
    } else {
      $items = $data;
    }

    if (empty($items)) {
      return '';
    }

    ob_start();
?>

    <section id="list-tooltip" role="region" class="relative py-24 lg:py-32 bg-base-200/50">
      <div class="container mx-auto px-4">
        <?php 
        $heading = $data['heading'] ?? '';
        if (!empty($heading)): ?>
          <h2 class="text-3xl lg:text-4xl font-bold text-center mb-12 text-base-content"><?= esc_html($heading) ?></h2>
        <?php endif; ?>
        <!-- Items Grid with Compact Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto">
          <?php foreach ($items as $index => $item): ?>
            <?= $this->renderOne($item, $index); ?>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

<?php
    return ob_get_clean();
  }

  /**
   * Render a single tooltip item
   */
  public function renderOne(array $item, int $index = 0): string
  {
    return $this->tooltipCard->render($item);
  }

  /**
   * Render from model using index
   */
  public function renderFromModel(array $data, int $index): string
  {
    $items = isset($data['items']) ? $data['items'] : $data;
    if (!isset($items[$index])) {
      return '';
    }
    return $this->tooltipCard->render($items[$index]);
  }

  /**
   * Override indices getter for complex data structure
   */
  protected function getModelIndices(array $data): array
  {
    $items = isset($data['items']) ? $data['items'] : $data;
    return array_keys($items);
  }
}
