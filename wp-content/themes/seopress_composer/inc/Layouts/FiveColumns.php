<?php

namespace SeopressComposer\Layouts;

use SeopressComposer\Components\SectionTitle;

/**
 * FiveColumns Layout
 * 
 * A flexible 5-column grid layout.
 */
class FiveColumns
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
   * @param string $col4 Content for fourth column
   * @param string $col5 Content for fifth column
   * @param array $options Optional settings ['wrapper_class', 'container_class', 'grid_class', 'title', 'description']
   * @return string Rendered HTML
   */
  public function render(string $col1, string $col2, string $col3, string $col4, string $col5, array $options = []): string
  {
    $wrapperClass = $options['wrapper_class'] ?? 'py-24 lg:py-32';
    $containerClass = $options['container_class'] ?? 'container mx-auto px-4';
    // Adjusted grid class for 5 columns on large screens
    $gridClass = $options['grid_class'] ?? 'grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6';

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
          <div class="col-4"><?= $col4 ?></div>
          <div class="col-5"><?= $col5 ?></div>
        </div>
      </div>
    </section>
<?php
    return ob_get_clean();
  }
}
