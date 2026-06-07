<?php

namespace SeopressComposer\Layouts;

use SeopressComposer\Components\SectionTitle;

/**
 * ThreeColumns Layout
 * 
 * A flexible 3-column grid layout.
 */
class ThreeColumns
{
  private SectionTitle $sectionTitle;

  public function __construct(SectionTitle $sectionTitle)
  {
    $this->sectionTitle = $sectionTitle;
  }

  /**
   * Render the layout
   * 
   * @param string $col1 Content for first column
   * @param string $col2 Content for second column
   * @param string $col3 Content for third column
   * @param array $options Optional settings ['wrapper_class', 'container_class', 'grid_class', 'title', 'description']
   * @return string Rendered HTML
   */
  public function render(string $col1, string $col2, string $col3, array $options = []): string
  {
    $wrapperClass = $options['wrapper_class'] ?? 'py-24 lg:py-32';
    $containerClass = $options['container_class'] ?? 'container mx-auto px-4';
    $gridClass = $options['grid_class'] ?? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 justify-center';

    ob_start();
?>
    <section class="<?= esc_attr($wrapperClass) ?>">
      <div class="<?= esc_attr($containerClass) ?>">
        <?php if (!empty($options['title'])): ?>
          <?= $this->sectionTitle->render($options['title'], $options['description'] ?? ''); ?>
        <?php endif; ?>

        <div class="<?= esc_attr($gridClass) ?>">
          <div class="col-1"><?= $col1 ?></div>
          <div class="col-2"><?= $col2 ?></div>
          <div class="col-3"><?= $col3 ?></div>
        </div>
      </div>
    </section>
<?php
    return ob_get_clean();
  }
}
