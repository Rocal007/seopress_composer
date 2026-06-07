<?php

namespace SeopressComposer\Components;

class SectionTitle
{
  /**
   * Render a standardized section title with divider and optional description.
   *
   * @param string $title The main heading text.
   * @param string $description Optional description text.
   * @param string $tag The HTML tag for the heading (default h2).
   * @param string $align Alignment class (default text-center).
   * @param string $className Custom CSS classes for the title.
   * @param string $tooltip Optional tooltip text to display in an info icon.
   * @return string HTML output.
   */
  public function render(string $title, string $description = '', string $tag = 'h2', string $align = 'text-center', string $className = '', string $tooltip = ''): string
  {
    if (empty($title)) {
      return '';
    }

    // Default premium styling if no specific class provided
    $finalClass = !empty($className) ? $className : 'text-3xl md:text-5xl font-bold text-primary mb-6 leading-tight flex items-center justify-center gap-2';

    ob_start();
?>
    <div class="<?= esc_attr($align) ?> mb-12 md:mb-20 px-4">
      <<?= esc_attr($tag) ?> class="<?= esc_attr($finalClass) ?>">
        <?= wp_kses_post($title) ?>
        <?php if (!empty($tooltip)): ?>
          <span class="group relative inline-flex items-center ml-2 cursor-help" tabindex="0" role="button" aria-label="Mehr Informationen">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-6 h-6 stroke-info shrink-0"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 w-72 md:w-96 bg-base-100 text-base-content border border-base-200 rounded-xl shadow-xl p-4 opacity-0 invisible group-hover:opacity-100 group-hover:visible focus-within:opacity-100 focus-within:visible transition-all duration-200 z-50 pointer-events-none group-hover:pointer-events-auto">
              <p class="text-sm font-normal normal-case text-left leading-relaxed"><?= wp_kses_post($tooltip) ?></p>
              <div class="absolute top-full left-1/2 -translate-x-1/2 border-8 border-transparent border-t-base-100 drop-shadow-sm"></div>
            </div>
          </span>
        <?php endif; ?>
      </<?= esc_attr($tag) ?>>
      <div class="divider divider-accent w-24 mx-auto" aria-hidden="true" role="presentation"></div>
      <?php if (!empty($description)): ?>
        <p class="mt-4 text-base-content/70 max-w-2xl mx-auto text-xl md:text-2xl">
          <?= esc_html($description) ?>
        </p>
      <?php endif; ?>
    </div>
<?php
    return ob_get_clean();
  }
}
