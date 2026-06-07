<?php

namespace SeopressComposer\Components;



/**
 * ServiceList Component
 * Enhanced with DaisyUI Cards and List components
 */
class ServiceList
{
  use \SeopressComposer\Components\Traits\ModelRenderable;

  private ServiceSection $serviceSection;

  public function __construct(ServiceSection $serviceSection)
  {
    $this->serviceSection = $serviceSection;
  }

  public function render(array $data, string $bgClass = 'bg-base-200'): string
  {
    if (empty($data)) {
      return '';
    }

    ob_start();
?>
    <div id="service-list" class="<?= esc_attr($bgClass) ?> py-24 lg:py-32">
      <div class="container mx-auto px-4">

        <?php foreach ($data as $section): ?>
          <?= $this->serviceSection->render($section); ?>
        <?php endforeach; ?>

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
    if (!isset($data[$index])) {
      return '';
    }
    return $this->serviceSection->render($data[$index]);
  }

  /**
   * Render many from model
   */
  public function renderManyFromModel(array $data): string
  {
    if (empty($data)) {
      return '';
    }
    return $this->render($data);
  }
}
