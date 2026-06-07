<?php

namespace SeopressComposer\Components;

use SeopressComposer\Services\IconService;

/**
 * ImageTextReadMore Component
 * 
 * Renders a flexible section with text and an optional floating image.
 * Supports "read more" expand/collapse functionality.
 */
class ImageTextReadMore
{
  use \SeopressComposer\Components\Traits\ModelRenderable;

  private IconService $iconService;

  public function __construct(IconService $iconService)
  {
    $this->iconService = $iconService;
  }

  /**
   * Render the ImageText component
   * 
   * @param array $args [
   *   'title' => string,
   *   'content' => string,
   *   'excerpt' => string (optional),
   *   'label' => string (optional, e.g. "01"),
   *   'image_url' => string,
   *   'image_alt' => string,
   *   'image_caption' => string,
   *   'image_position' => 'left'|'right',
   *   'bg_class' => string,
   *   'id' => string (optional unique id)
   * ]
   * @return string Rendered HTML
   */

  protected function getModelIndices(array $data): array
  {
    if (!empty($data['subtexte_hs']) && is_array($data['subtexte_hs'])) {
      return array_keys($data['subtexte_hs']);
    }
    return [];
  }

  /**
   * Helper to render from Standard Subtext Data Model
   * 
   * @param array $subtextData Full data array from Subtext_Data model
   * @param int $index Index of the subtext to render
   * @param string $imagePosition 'left' or 'right' or 'top'
   * @param string $bgClass Optional background class
   * @return string Rendered HTML or empty string
   */
  public function renderFromModel(array $subtextData, int $index, string $imagePosition = 'right', string $bgClass = ''): string
  {
    if (empty($subtextData['subtexte_hs'][$index])) {
      return '';
    }

    $item = $subtextData['subtexte_hs'][$index];
    $image = $subtextData['images'][$index] ?? null;

    return $this->render([
      'id' => 'subtext-' . $index,
      'title' => $item['title'] ?? '',
      'content' => $item['content'] ?? '',
      'excerpt' => $item['excerpt'] ?? '',
      'label' => $item['index'] ?? '',
      'image_url' => (!empty($image) && !empty($image['image_url'])) ? $image['image_url'] : '',
      'image_mobile_url' => (!empty($image) && !empty($image['image_mobile_url'])) ? $image['image_mobile_url'] : '',
      'image_alt' => (!empty($image)) ? ($image['image_alt'] ?? $item['title']) : '',
      'image_caption' => (!empty($image)) ? ($image['image_caption'] ?? '') : '',
      'image_position' => $imagePosition,
      'bg_class' => $bgClass
    ]);
  }



  /**
   * Render the ImageText component
   * 
   * @param array $args
   * @return string Rendered HTML
   */
  public function render(array $args): string
  {
    $title = $args['title'] ?? '';
    $content = $args['content'] ?? '';
    // Remove break tags to allow natural wrapping around images, but preserve paragraphs
    $content = preg_replace('/<br\s*\/?>/i', ' ', $content);

    $excerpt = $args['excerpt'] ?? $content;
    $excerpt = preg_replace('/<br\s*\/?>/i', ' ', $excerpt);

    // Safety check: if content is same as excerpt or short, maybe don't need toggle logic? 
    // Logic below checks strlen > 300.

    $label = $args['label'] ?? '';

    $imageUrl = $args['image_url'] ?? '';
    $imageAlt = $args['image_alt'] ?? $title;
    $imageCaption = $args['image_caption'] ?? '';
    $imagePosition = $args['image_position'] ?? 'right';

    $bgClass = $args['bg_class'] ?? '';
    $blockId = $args['id'] ?? uniqid('img-text-');

    $hasImage = !empty($imageUrl);
    $contentId = $blockId . '-content';
    $toggleId = $blockId . '-toggle';

    // Float direction based on image position
    // If 'top', we don't float, just stack.
    if ($imagePosition === 'top') {
      $floatClass = 'w-full mb-6';
    } else {
      $floatClass = ($imagePosition === 'left') ? 'float-left mr-12 mb-6 w-full lg:w-5/12 max-w-md' : 'float-right ml-12 mb-6 w-full lg:w-5/12 max-w-md';
    }

    ob_start();
?>
    <div class="subtext-block <?= esc_attr($bgClass) ?> py-16 lg:py-24" id="<?= esc_attr($blockId) ?>">
      <div class="container mx-auto px-4 max-w-5xl">
        <div class="text-content bg-base-200 border border-base-300 rounded-box p-6 lg:p-10 shadow-sm">

          <?php if (!empty($label)): ?>
            <span class="text-sm font-bold text-primary uppercase tracking-wider mb-2 block">
              <?= wp_kses_post($label) ?>
            </span>
          <?php endif; ?>

          <?php if (!empty($title)): ?>
            <h3 class="text-xl md:text-2xl font-bold mb-4 text-primary leading-snug">
              <?= wp_kses_post($title) ?>
            </h3>
          <?php endif; ?>

          <div class="relative">
            <!-- Floating Image -->
            <?php if ($hasImage): ?>
              <figure class="<?= $floatClass ?>">
                <div
                  class="rounded-xl overflow-hidden shadow-xl transform hover:scale-[1.02] transition-transform duration-300">
                  <?php $imageMobileUrl = $args['image_mobile_url'] ?? ''; ?>
                  <?php if (!empty($imageMobileUrl)): ?>
                      <picture>
                          <source media="(max-width: 768px)" srcset="<?= esc_url($imageMobileUrl) ?>">
                          <img src="<?= esc_url($imageUrl) ?>" alt="<?= esc_attr($imageAlt) ?>" title="<?= esc_attr($imageAlt) ?>"
                            class="w-full h-auto object-cover aspect-[4/3]" loading="lazy">
                      </picture>
                  <?php else: ?>
                      <img src="<?= esc_url($imageUrl) ?>" alt="<?= esc_attr($imageAlt) ?>" title="<?= esc_attr($imageAlt) ?>"
                        class="w-full h-auto object-cover aspect-[4/3]" loading="lazy">
                  <?php endif; ?>
                </div>
                <?php if (!empty($imageCaption)): ?>
                  <figcaption class="text-sm text-secondary mt-2 text-center italic">
                    <?= esc_html($imageCaption) ?>
                  </figcaption>
                <?php endif; ?>
              </figure>
            <?php endif; ?>

            <!-- Text Content with Read More -->
            <div class="prose max-w-none text-secondary text-lg leading-relaxed">
              <div id="<?= esc_attr($contentId) ?>" class="subtext-content" data-collapsed="true">
                <div class="content-preview">
                  <?= wp_kses_post($excerpt) ?>
                </div>
                <div class="content-full hidden">
                  <?= wp_kses_post($content) ?>
                </div>
              </div>

              <?php if (!empty($content) && strlen(strip_tags($content)) > 200 && $content !== $excerpt): ?>
                <button id="<?= esc_attr($toggleId) ?>"
                  class="read-more-btn mt-4 inline-flex items-center gap-2 text-primary font-semibold hover:text-primary-focus transition-colors"
                  onclick="toggleSubtext('<?= esc_js($contentId) ?>', '<?= esc_js($toggleId) ?>')">
                  <span class="btn-text">Weiterlesen</span>
                  <div class="w-4 h-4 transform transition-transform btn-icon">
                    <?= $this->iconService->getIcon('chevron_down') ?>
                  </div>
                </button>
              <?php endif; ?>
            </div>

            <!-- Clear float -->
            <div class="clear-both"></div>
          </div>
        </div>
      </div>
    </div>

    <script>
      // Iterate over simple script ensuring only one definition if loaded multiple times? 
      // Better: Check if function exists.
      if (typeof toggleSubtext !== 'function') {
        function toggleSubtext(contentId, toggleId) {
          const content = document.getElementById(contentId);
          const toggle = document.getElementById(toggleId);
          const preview = content.querySelector('.content-preview');
          const full = content.querySelector('.content-full');
          const btnText = toggle.querySelector('.btn-text');
          const btnIcon = toggle.querySelector('.btn-icon');

          const isCollapsed = content.dataset.collapsed === 'true';

          if (isCollapsed) {
            // Expand
            preview.classList.add('hidden');
            full.classList.remove('hidden');
            btnText.textContent = 'Weniger anzeigen';
            btnIcon.classList.add('rotate-180');
            content.dataset.collapsed = 'false';
          } else {
            // Collapse
            preview.classList.remove('hidden');
            full.classList.add('hidden');
            btnText.textContent = 'Weiterlesen';
            btnIcon.classList.remove('rotate-180');
            content.dataset.collapsed = 'true';

            // Scroll to section top
            // content.closest('section').scrollIntoView({ // safer selection
            const section = document.getElementById(content.id.replace('-content', ''));
            if (section) {
              section.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
              });
            }
          }
        }
      }
    </script>
<?php
    return ob_get_clean();
  }
}
