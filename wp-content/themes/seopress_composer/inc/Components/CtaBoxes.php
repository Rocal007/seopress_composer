<?php

namespace SeopressComposer\Components;

use SeopressComposer\Services\IconService;

/**
 * CtaBoxes Component
 * Enhanced with DaisyUI cards and timeline/step visualization elements
 */
class CtaBoxes
{
  private IconService $iconService;
  private SectionTitle $sectionTitle;

  public function __construct(IconService $iconService, SectionTitle $sectionTitle)
  {
    $this->iconService = $iconService;
    $this->sectionTitle = $sectionTitle;
  }
  use \SeopressComposer\Components\Traits\ModelRenderable;

  public function render(array $data, string $bgClass = 'bg-base-200'): string
  {
    if (empty($data) || empty($data['items'])) {
      return '';
    }

    $heading = $data['heading'] ?? '';
    $subheading = $data['subheading'] ?? '';
    $items = $data['items'];

    ob_start();
?>
    <section id="process-steps" class="<?= esc_attr($bgClass) ?> py-24 lg:py-32" aria-label="<?= esc_attr(wp_strip_all_tags($heading)) ?>">
      <div class="container mx-auto px-4">

        <?= $this->sectionTitle->render($heading, $subheading) ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <?php foreach ($items as $box): ?>
            <?= $this->renderOne($box); ?>
          <?php endforeach; ?>
        </div>

      </div>
    </section>
  <?php
    return ob_get_clean();
  }

  /**
   * Render a single CTA box
   */
  public function renderOne(array $box): string
  {
    ob_start();
  ?>
    <div
      class="card bg-base-100 shadow-xl hover:shadow-2xl transition-all duration-300 border-t-8 border-primary relative overflow-visible mt-8 lg:mt-0 h-full">

      <!-- Step Badge -->
      <div class="absolute -top-6 left-1/2 transform -translate-x-1/2">
        <div
          class="w-12 h-12 rounded-full bg-primary text-white flex items-center justify-center text-xl font-bold ring ring-base-100 ring-offset-2 shadow-lg">
          <?= $box['index'] ?>
        </div>
      </div>

      <div class="card-body items-center text-center pt-10">
        <?php if (!empty($box['icon'])): ?>
          <div class="w-20 h-20 rounded-full bg-secondary text-secondary-content flex items-center justify-center mx-auto mb-4 p-3 shadow-lg">
            <?= $this->iconService->getIcon(strtolower($box['icon']), ['invert' => true]) ?>
          </div>
        <?php endif; ?>
        <h3 class="card-title text-xl text-primary mb-2">
          <?= wp_kses_post($box['heading']) ?>
        </h3>

        <div class="text-base-content/70 mb-6 leading-relaxed">
          <?= wp_kses_post($box['content']) ?>
        </div>

        <?php if (!empty($box['link']) && !empty($box['button_text'])): ?>
          <div class="card-actions mt-auto">
            <a href="<?= esc_url($box['link']) ?>"
              class="btn btn-secondary rounded-full px-8 hover:btn-accent transition-colors"
              title="<?= esc_attr($box['button_link_text'] ?? $box['button_text']) ?>"
              aria-label="<?= esc_attr($box['button_text']) ?>">
              <?= esc_html($box['button_text']) ?>
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
<?php
    return ob_get_clean();
  }

  /**
   * Render from model using index
   */
  public function renderFromModel(array $data, int $index): string
  {
    if (!isset($data['items'][$index])) {
      return '';
    }
    return $this->renderOne($data['items'][$index]);
  }

  /**
   * Override indices getter for complex data structure
   */
  protected function getModelIndices(array $data): array
  {
    return isset($data['items']) ? array_keys($data['items']) : [];
  }
}
