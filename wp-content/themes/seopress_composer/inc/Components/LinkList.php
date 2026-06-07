<?php

namespace SeopressComposer\Components;

use SeopressComposer\Services\IconService;

/**
 * LinkList Component
 * Renders a list of links with optional icons
 */
class LinkList
{
  private IconService $iconService;

  public function __construct(IconService $iconService)
  {
    $this->iconService = $iconService;
  }

  /**
   * Render a list of links
   * 
   * @param array $items Array of items with 'name', 'link', optional 'icon', 'title', and 'content' (for tooltip)
   * @param bool $showIcons Whether to display icons (default: true)
   * @param string $listClass Additional CSS classes for the list container
   * @param string|null $title Optional section title to display above the list
   * @param string $titleClass CSS classes for the title (default: 'text-lg font-bold mb-4')
   * @param string|null $linkColor Optional hex color for the links
   * @return string
   */
  public function render(array $items, bool $showIcons = true, string $listClass = '', ?string $title = null, string $titleClass = 'text-lg font-bold mb-4', ?string $linkColor = null): string
  {
    if (empty($items)) {
      return '';
    }

    $listClasses = 'space-y-2 ' . esc_attr($listClass);

    // Prepare link style and class
    $linkStyle = $linkColor ? 'style="color: ' . esc_attr($linkColor) . ';"' : '';
    // If we have a custom color, don't add the default text color class (text-base-content) 
    // but keep base styling or add a neutral one if needed, relying on style attribute for color.
    // However, text-base-content might override style if not careful, but style attribute usually wins.
    // Let's remove text-base-content if custom color is set to be safe.
    $linkClassBase = $linkColor ? '' : 'text-base-content';

    ob_start();
?>
    <?php if ($title): ?>
      <h3 class="<?= esc_attr($titleClass) ?>"><?= esc_html($title) ?></h3>
    <?php endif; ?>
    <ul class="<?= $listClasses ?>">
      <?php foreach ($items as $item): ?>
        <?php if (!empty($item['link']) && !empty($item['name'])): ?>
          <li class="flex items-center group">
            <?php if ($showIcons && !empty($item['icon'])): ?>
              <div class="mr-3 flex-shrink-0">
                <div class="bg-white/10 p-2 rounded-full w-10 h-10 flex items-center justify-center">
                  <div class="w-5 h-5 flex items-center justify-center">
                    <?= $this->iconService->getIconHtml($item['icon'], '', ['size' => 'xs', 'inner_class' => 'illu-invert']) ?>
                  </div>
                </div>
              </div>
            <?php endif; ?>

            <?php if (!empty($item['content'])): ?>
              <!-- Link with tooltip -->
              <div class="tooltip tooltip-right flex-1 text-left z-[100] [--tooltip-color:theme(colors.neutral)] [--tooltip-text-color:theme(colors.primary)]"
                data-tip="<?= esc_attr(wp_strip_all_tags($item['content'])) ?>"
                aria-label="<?= esc_attr(wp_strip_all_tags($item['content'])) ?>">
                <a href="<?= esc_url($item['link']) ?>"
                  title="<?= esc_attr($item['title'] ?? $item['name']) ?>"
                  class="<?= $linkClassBase ?> hover:text-primary transition-colors duration-200 cursor-help border-b border-dotted border-secondary/50"
                  <?= $linkStyle ?>>
                  <?= esc_html($item['name']) ?>
                </a>
              </div>
            <?php else: ?>
              <!-- Link without tooltip -->
              <a href="<?= esc_url($item['link']) ?>"
                title="<?= esc_attr($item['title'] ?? $item['name']) ?>"
                class="<?= $linkClassBase ?> hover:text-primary transition-colors duration-200 flex-1"
                <?= $linkStyle ?>>
                <?= esc_html($item['name']) ?>
              </a>
            <?php endif; ?>
          </li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ul>
  <?php
    return ob_get_clean();
  }

  /**
   * Render multiple link lists
   * 
   * @param array $lists Array of link list configurations
   * @param bool $showIcons Whether to display icons (default: true)
   * @return array Array of rendered HTML strings
   */
  public function renderMany(array $lists, bool $showIcons = true): array
  {
    return array_map(function ($list) use ($showIcons) {
      return $this->render($list, $showIcons);
    }, $lists);
  }

  /**
   * Render a compact inline link list (horizontal)
   * 
   * @param array $items Array of items with 'name', 'link', optional 'icon', 'title', and 'content' (for tooltip)
   * @param bool $showIcons Whether to display icons (default: true)
   * @param string $separator Separator between links (default: '•')
   * @return string
   */
  public function renderInline(array $items, bool $showIcons = false, string $separator = '•'): string
  {
    if (empty($items)) {
      return '';
    }

    ob_start();
  ?>
    <div class="flex flex-wrap items-center gap-2">
      <?php foreach ($items as $index => $item): ?>
        <?php if (!empty($item['link']) && !empty($item['name'])): ?>
          <?php if ($index > 0): ?>
            <span class="text-base-content/50"><?= esc_html($separator) ?></span>
          <?php endif; ?>
          <div class="flex items-center gap-2 group">
            <?php if ($showIcons && !empty($item['icon'])): ?>
              <?= $this->iconService->getIconHtml($item['icon'], '', ['size' => 'xs', 'inner_class' => 'illu-invert group-hover:text-primary transition-colors']) ?>
            <?php endif; ?>

            <?php if (!empty($item['content'])): ?>
              <!-- Link with tooltip -->
              <div class="tooltip tooltip-bottom z-[100] [--tooltip-color:theme(colors.neutral)] [--tooltip-text-color:theme(colors.primary)]"
                data-tip="<?= esc_attr(wp_strip_all_tags($item['content'])) ?>"
                aria-label="<?= esc_attr(wp_strip_all_tags($item['content'])) ?>">
                <a href="<?= esc_url($item['link']) ?>"
                  title="<?= esc_attr($item['title'] ?? $item['name']) ?>"
                  class="text-base-content hover:text-primary transition-colors duration-200 cursor-help border-b border-dotted border-secondary/50">
                  <?= esc_html($item['name']) ?>
                </a>
              </div>
            <?php else: ?>
              <!-- Link without tooltip -->
              <a href="<?= esc_url($item['link']) ?>"
                title="<?= esc_attr($item['title'] ?? $item['name']) ?>"
                class="text-base-content hover:text-primary transition-colors duration-200">
                <?= esc_html($item['name']) ?>
              </a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
<?php
    return ob_get_clean();
  }
}
