<?php

namespace SeopressComposer\Layouts;

use SeopressComposer\Components\SectionTitle;

/**
 * OneColumn Layout
 * 
 * A simple 1-column layout wrapper.
 */
class OneColumn
{
  private SectionTitle $sectionTitle;

  public function __construct(SectionTitle $sectionTitle)
  {
    $this->sectionTitle = $sectionTitle;
  }

  /**
   * Render the layout
   * 
   * @param string $content Content to render inside the column
   * @param array $options Optional settings ['wrapper_class', 'container_class', 'title', 'description']
   * @return string Rendered HTML
   */
  public function render(string $content, array $options = []): string
  {
    $wrapperClass = $options['wrapper_class'] ?? 'py-24 lg:py-32';
    $containerClass = $options['container_class'] ?? 'container mx-auto px-4';

    ob_start();
?>
    <section class="<?= esc_attr($wrapperClass) ?>">
      <div class="<?= esc_attr($containerClass) ?>">
        <?php if (!empty($options['title'])): ?>
          <?= $this->sectionTitle->render($options['title'], $options['description'] ?? ''); ?>
        <?php endif; ?>

        <?= $content ?>
      </div>
    </section>
<?php
    return ob_get_clean();
  }
}
