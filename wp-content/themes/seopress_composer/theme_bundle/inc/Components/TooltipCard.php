<?php

namespace SeopressComposer\Components;

use SeopressComposer\Services\IconService;

/**
 * TooltipCard Component
 * Renders a single tooltip card item
 */
class TooltipCard
{
  private IconService $iconService;

  public function __construct(IconService $iconService)
  {
    $this->iconService = $iconService;
  }

  use \SeopressComposer\Components\Traits\ModelRenderable;

  /**
   * Render a single tooltip item
   */
  public function render(array $item): string
  {
    $iconName = !empty($item['icon']) ? strtolower($item['icon']) : 'hook';
    $description = wp_strip_all_tags($item['content'] ?? '');

    ob_start();
?>
    <div
      class="group relative z-0 hover:z-50 bg-base-100 rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-500 border border-base-300/50 hover:border-primary/40 flex items-center gap-4 min-h-[100px]"
      role="article">

      <!-- Absolute Info Icon Top-Right -->
      <div class="absolute top-2 right-2 z-20">
        <div
          class="tooltip tooltip-left tooltip-primary cursor-help before:p-4 before:rounded-2xl before:shadow-2xl before:max-w-[20rem] before:whitespace-normal before:text-sm before:leading-relaxed before:bg-white before:text-black"
          data-tip="<?= esc_attr($description) ?>"
          aria-label="<?= esc_attr($description) ?>">
          <div class="text-primary/30 group-hover:text-primary/70 transition-colors p-1">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Icon Wrapper (Logo Style) - Responsive size -->
      <div class="flex-shrink-0">
        <div
          class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-primary text-primary-content flex items-center justify-center p-1.5 shadow-md ring ring-primary ring-offset-base-100 ring-offset-2 group-hover:scale-110 transition-transform duration-500">
          <?= $this->iconService->getIcon($iconName, ['invert' => true]) ?>
        </div>
      </div>

      <!-- Content -->
      <div class="flex-1 pr-4">
        <h4
          class="text-sm md:text-base font-bold text-base-content group-hover:text-primary transition-colors leading-snug">
          <?= esc_html($item['title'] ?? '') ?>
        </h4>
      </div>

    </div>
<?php
    return ob_get_clean();
  }
}
