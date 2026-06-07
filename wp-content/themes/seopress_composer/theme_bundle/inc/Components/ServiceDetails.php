<?php

namespace SeopressComposer\Components;



/**
 * ServiceDetails Component
 * Enhanced with DaisyUI Cards and List components
 */
class ServiceDetails
{
  use \SeopressComposer\Components\Traits\ModelRenderable;

  private ServiceSection $serviceSection;

  public function __construct(ServiceSection $serviceSection)
  {
    $this->serviceSection = $serviceSection;
  }

  public function render(array $data, string $bgClass = 'bg-base-200'): string
  {
    $inner_content = '';
    foreach ($data as $section) {
      $inner_content .= $this->serviceSection->render($section);
    }

    if (trim($inner_content) === '') {
      return '';
    }

    ob_start();
?>
    <div id="service-list" class="<?= esc_attr($bgClass) ?> py-24 lg:py-32">
      <div class="container mx-auto px-4">
        <?= $inner_content; ?>
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
