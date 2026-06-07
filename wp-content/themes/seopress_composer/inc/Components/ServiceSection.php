<?php

namespace SeopressComposer\Components;

use SeopressComposer\Services\IconService;

/**
 * ServiceSection Component
 * Renders a single section of services (Title + Grid of Cards).
 * Extracted from ServiceList for reusability.
 */
class ServiceSection
{
  private IconService $iconService;

  public function __construct(IconService $iconService)
  {
    $this->iconService = $iconService;
  }

  public function render(array $section): string
  {
    if (empty($section)) {
      return '';
    }

    $ariaLabel = !empty($section['title']) ? 'aria-label="' . esc_attr(wp_strip_all_tags($section['title'])) . '"' : '';

    ob_start();
?>
    <section class="mb-16 last:mb-0" <?= $ariaLabel ?>>

      <?php if (!empty($section['title'])): ?>
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-heading font-medium text-primary mb-6">
            <?= wp_kses_post($section['title']) ?>
          </h2>
          <div class="h-px w-48 mx-auto bg-gradient-to-r from-transparent via-secondary to-transparent opacity-60"></div>
        </div>
      <?php endif; ?>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if (!empty($section['block'])): ?>
          <?php foreach ($section['block'] as $block): ?>
            <div
              class="card bg-base-100 shadow-lg transition-all duration-300 border-t-4 border-secondary h-full overflow-visible rounded-xl group/card">

              <?php if (!empty($block->uberschrift_block)): ?>
                <div class="card-body pb-0 pt-8 text-center relative flex-none">
                  <h3 class="card-title justify-center text-primary text-3xl font-heading font-medium mb-3">
                    <?= wp_kses_post($block->uberschrift_block) ?>
                  </h3>
                  <!-- Elegant decorative separation -->
                  <div class="h-px w-24 mx-auto bg-gradient-to-r from-transparent via-secondary to-transparent opacity-50"></div>
                </div>
              <?php endif; ?>

              <div class="card-body pt-6 px-0 justify-start">
                <ul class="flex flex-col w-full">
                  <?php foreach ($block->leistung as $item): ?>
                    <li
                      class="flex items-start px-8 py-3 hover:bg-base-200/50 transition-all duration-300 hover:scale-105 hover:z-10 relative rounded-lg group/item">
                      <div class="mt-1 mr-4 flex-shrink-0 w-6 h-6">
                        <?= $this->iconService->getIcon('kunst', ['size' => 'sm', 'secondary_background' => true, 'invert' => true]) ?>
                      </div>

                      <div class="flex-1">
                        <?php if (!empty($item->info_single)): ?>
                          <!-- Pure CSS Tooltip Pattern -->
                          <div class="group/tip relative w-full cursor-help">
                            <div class="flex items-center justify-between gap-2 group/text">
                              <span class="text-base-content font-sans font-normal group-hover/item:text-primary transition-colors block">
                                <?= esc_html(wp_strip_all_tags($item->leistung_single)) ?>
                              </span>
                              <div class="text-secondary/40 group-hover/item:text-primary transition-colors shrink-0">
                                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                              </div>
                            </div>
                            
                            <!-- Tooltip Dropdown -->
                            <div class="absolute bottom-full right-0 mb-3 w-72 bg-base-100 text-base-content border border-base-200 rounded-xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] p-4 opacity-0 invisible group-hover/tip:opacity-100 group-hover/tip:visible focus-within:opacity-100 focus-within:visible transition-all duration-200 z-[9999] pointer-events-none group-hover/tip:pointer-events-auto">
                              <p class="text-sm font-normal normal-case text-left leading-relaxed">
                                <?= wp_kses_post($item->info_single) ?>
                              </p>
                              <!-- Arrow pointing down -->
                              <div class="absolute top-full right-4 border-8 border-transparent border-t-base-100 drop-shadow-sm"></div>
                            </div>
                          </div>
                        <?php else: ?>
                          <span class="text-base-content font-sans font-normal group-hover/item:text-primary transition-colors block">
                            <?= esc_html(wp_strip_all_tags($item->leistung_single)) ?>
                          </span>
                        <?php endif; ?>
                      </div>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      </div>
  <?php
    return ob_get_clean();
  }
}
