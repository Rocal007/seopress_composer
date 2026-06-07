<?php
namespace SeopressComposer\Components\Traits;

/**
 * Trait ModelRenderable
 * 
 * Provides functionality to render multiple items from a model array.
 * Requires the consuming class to implement renderFromModel().
 */
trait ModelRenderable
{
  /**
   * Render multiple items from a data model by indices.
   * 
   * @param array $data The full data array from the model.
   * @param array|null $indices Array of indices/keys to render. If null, render all available items.
   * @param mixed ...$args Additional arguments to pass to renderFromModel.
   * @return array Array of rendered HTML strings.
   */
  public function renderManyFromModel(array $data, ?array $indices = null, ...$args): array
  {
    if ($indices === null) {
      $indices = $this->getModelIndices($data);
    }

    $results = [];
    foreach ($indices as $index) {
      if (method_exists($this, 'renderFromModel')) {
        $results[] = $this->renderFromModel($data, $index, ...$args);
      }
    }
    return $results;
  }

  /**
   * Determine available indices from the data model.
   * Defaults to array_keys, assuming a list.
   * Override this in components where $data structure is complex.
   * 
   * @param array $data
   * @return array
   */
  protected function getModelIndices(array $data): array
  {
    return array_keys($data);
  }
}
