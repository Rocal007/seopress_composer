<?php

namespace SeopressComposer\Components;

/**
 * Punchline Component
 * 
 * Renders short, punchy text lines between components
 * Used for visual breaks and emphasis
 */
class Punchline
{
  /**
   * Render a single punchline by index
   * 
   * @param array $data Punchline data from Punchline_Data model
   * @param int $index Zero-based index of punchline to render
   * @return string Rendered HTML or empty string
   */
  public function render(array $data, int $index = 0): string
  {
    if (empty($data[$index]['content'])) {
      return '';
    }

    $content = $data[$index]['content'];
    $cta_text = $data[$index]['cta_text'] ?? '';
    $cta_url = $data[$index]['cta_url'] ?? '';

    ob_start();
?>
    <div class="punchline-section py-24 lg:py-32 bg-gradient-to-r from-primary/5 via-primary/10 to-primary/5">
      <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
          <div class="punchline-content prose prose-lg lg:prose-xl max-w-none">
            <p class="text-2xl lg:text-3xl font-bold text-primary leading-relaxed mb-0">
              <?= wp_kses_post($content) ?>
              <?php if (!empty($cta_text) && !empty($cta_url)): ?>
                — <a href="<?= esc_url($cta_url) ?>" class="link link-primary hover:link-accent font-extrabold whitespace-nowrap"><?= esc_html($cta_text) ?> →</a>
              <?php endif; ?>
            </p>
          </div>
        </div>
      </div>
    </div>
  <?php
    return ob_get_clean();
  }

  /**
   * Render punchline with custom styling variant
   * 
   * @param array $data Punchline data
   * @param int $index Index of punchline
   * @param string $variant Style variant: 'default', 'accent', 'minimal'
   * @return string Rendered HTML
   */
  public function renderVariant(array $data, int $index = 0, string $variant = 'default'): string
  {
    if (empty($data[$index]['content'])) {
      return '';
    }

    $content = $data[$index]['content'];

    // Variant-specific classes
    $variants = [
      'default' => [
        'section' => 'py-24 lg:py-32 bg-gradient-to-r from-primary/5 via-primary/10 to-primary/5',
        'text' => 'text-2xl lg:text-3xl font-bold text-primary'
      ],
      'accent' => [
        'section' => 'py-24 lg:py-32 bg-accent',
        'text' => 'text-3xl lg:text-4xl font-extrabold text-white'
      ],
      'minimal' => [
        'section' => 'py-24 lg:py-32',
        'text' => 'text-xl lg:text-2xl font-semibold text-secondary italic'
      ],
      'highlight' => [
        'section' => 'py-24 lg:py-32 bg-primary',
        'text' => 'text-2xl lg:text-3xl font-bold text-white'
      ]
    ];

    $classes = $variants[$variant] ?? $variants['default'];

    ob_start();
  ?>
    <div class="punchline-section <?= esc_attr($classes['section']) ?>">
      <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
          <div class="punchline-content prose prose-lg lg:prose-xl max-w-none">
            <p class="<?= esc_attr($classes['text']) ?> leading-relaxed mb-0">
              <?= wp_kses_post($content) ?>
            </p>
          </div>
        </div>
      </div>
    </div>
<?php
    return ob_get_clean();
  }
}
