<?php

namespace SeopressComposer\Components;

use SeopressComposer\Services\IconService;

/**
 * IconLinks Component
 * Renders a grid of links/buttons, useful for "Other Districts" or "Related Tags" sections.
 */
class IconLinks
{
  private IconService $iconService;

  public function __construct(IconService $iconService)
  {
    $this->iconService = $iconService;
  }

  /**
   * Render multiple icon link buttons
   *
   * @param array $items Array of items
   * @return array Array of rendered HTML strings
   */
  public function renderMany(array $items): array
  {
    return array_map([$this, 'render'], $items);
  }
  /**
   * Render a single icon link button
   * 
   * @param array $item Item with 'name', 'link', and optional 'icon'
   * @return string
   */
  public function render(array $item): string
  {
    if (empty($item['link']) || empty($item['name'])) {
      return '';
    }

    ob_start();
?>
    <a href="<?= esc_url($item['link']) ?>"
      title="<?= esc_attr($item['title'] ?? $item['name']) ?>"
      class="btn bg-base-100 hover:btn-primary text-base-content border-base-200 shadow-sm hover:shadow-md transition-all duration-300 w-full h-auto min-h-[3rem] py-4 whitespace-normal gap-3 flex-col group h-full">
      <?php if (!empty($item['icon'])): ?>
        <?= $this->iconService->getIconHtml($item['icon'], '', ['size' => 'md', 'inner_class' => 'illu-invert group-hover:text-primary-content']) ?>
      <?php endif; ?>
      <span class="break-words leading-tight"><?= esc_html($item['name']) ?></span>
    </a>
<?php
    return ob_get_clean();
  }
}
