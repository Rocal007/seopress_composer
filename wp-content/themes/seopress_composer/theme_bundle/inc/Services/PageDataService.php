<?php
namespace SeopressComposer\Services;

use SeopressComposer\Core\ModelsDTO;

/**
 * PageDataService - Provides all data for a specific page template
 */
class PageDataService
{
  private ModelsDTO $models;

  public function __construct(ModelsDTO $models)
  {
    $this->models = $models;
  }

  /**
   * Get all data for Startseite template
   */
  public function getStartseiteData(): object
  {
    return (object) [
      'hero' => $this->models->getTopPictureData(),
      'faqs' => $this->models->getFaqsData(),
      'mainText' => $this->models->getMainTextData(),
      'vorteile' => $this->models->getVorteileData(),
    ];
  }

  /**
   * Magic method to get data for any page
   * Example: getStartseiteData() -> returns startseite page data
   */
  public function __call(string $name, array $arguments)
  {
    if (strpos($name, 'get') === 0 && substr($name, -4) === 'Data') {
      $page = lcfirst(substr($name, 3, -4));
      $methodName = 'get' . ucfirst($page) . 'Data';

      if (method_exists($this, $methodName)) {
        return $this->$methodName();
      }
    }

    throw new \BadMethodCallException("Method {$name} does not exist");
  }
}
