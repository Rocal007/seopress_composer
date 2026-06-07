<?php

namespace SeopressComposer\Components;

/**
 * GroupedCards Component
 * Renders pages grouped by category/service in a grid of cards
 */
class GroupedCards
{
  use \SeopressComposer\Components\Traits\ModelRenderable;

  private \SeopressComposer\Services\IconService $iconService;

  public function __construct(\SeopressComposer\Services\IconService $iconService)
  {
    $this->iconService = $iconService;
  }

  /**
   * Render the grouped cards section
   * 
   * @param array $grouped_pages Array of groups, each with 'name' and 'items'
   * @param array|string|null $section_title Data for SectionTitle component
   * @param bool $collapsible Whether group sections should be collapsible (default: false)
   * @return string
   */
  public function render(array $grouped_pages, $section_title = null, bool $collapsible = false): string
  {
    if (empty($grouped_pages)) {
      return '';
    }

    $components = seopress_components();
    $group_name = 'grouped-cards-' . uniqid();

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

        <?php foreach ($grouped_pages as $index => $group): ?>

          <?php if ($collapsible): ?>
            <details class="bg-base-200 group rounded-box mb-4 border border-base-300" <?= $index === 0 ? 'open' : '' ?> name="<?= esc_attr($group_name) ?>">
              <summary class="text-2xl font-bold text-secondary cursor-pointer p-4 flex justify-between items-center outline-none list-none">
                <div class="flex items-center gap-4">
                  <?php
                  $iconIdentifier = !empty($group['icon']) ? $group['icon'] : 'varia';
                  echo $this->iconService->getIconHtml($iconIdentifier, $group['name'], ['size' => 'md', 'secondary_background' => true, 'invert' => true]);
                  ?>
                  <span class="text-primary"><?= esc_html($group['name']) ?></span>
                </div>
                <div class="w-6 h-6 flex-shrink-0 text-secondary transition-transform duration-200 group-open:rotate-180">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-full h-full"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                </div>
              </summary>
              <div class="p-4 pt-0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 pt-4">
                  <?php foreach ($group['items'] as $item): ?>
                    <?= $components->getCard()->renderOne($item); ?>
                  <?php endforeach; ?>
                </div>
              </div>
            </details>
          <?php else: ?>
            <div class="mb-20">
              <div class="flex items-center gap-4 mb-10">
                <h3 class="text-2xl font-bold text-secondary">
                  <?= esc_html($group['name']) ?>
                </h3>
                <div class="flex-grow h-px bg-base-300"></div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($group['items'] as $item): ?>
                  <?= $components->getCard()->renderOne($item); ?>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </section>
<?php
    return ob_get_clean();
  }
}
