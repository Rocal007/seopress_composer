<?php

namespace SeopressComposer\Components;

/**
 * NumberedFeatures Component
 * Renders a grid of cards with number badges, handling layout internally.
 */
class NumberedFeatures
{
  use \SeopressComposer\Components\Traits\ModelRenderable;

  private SectionTitle $sectionTitle;

  public function __construct(SectionTitle $sectionTitle)
  {
    $this->sectionTitle = $sectionTitle;
  }

  public function render(array $data, int $columns = 2, ?int $limit = null): string
  {
    $title = $data['title'] ?? '';
    $titleHtml = $this->sectionTitle->render($title, '', 'h2', 'text-center');

    $items = [];
    if (isset($data['items']) && is_array($data['items'])) {
      $items = $data['items'];
    } else {
      // Fallback for flat structure? 
      // Assuming normalized data handles this component
      $items = $data;
    }

    if ($limit !== null && $limit > 0) {
      $items = array_slice($items, 0, $limit);
    }

    $cardsHtml = "";
    if (!empty($items)) {
      // Map columns to classes
      $colClass = match ($columns) {
        1 => 'grid-cols-1',
        3 => 'grid-cols-1 md:grid-cols-3',
        4 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
        default => 'grid-cols-1 md:grid-cols-2'
      };

      $cardsHtml = '<div class="grid ' . $colClass . ' gap-8 max-w-6xl mx-auto">';
      $counter = 1;
      foreach ($items as $index => $item) {
        if (empty($item) || is_array($item))
          continue;
        $cardsHtml .= $this->renderOne((string)$item, $counter);
        $counter++;
      }
      $cardsHtml .= '</div>';
    }

    ob_start();
?>
    <!-- Section Heading with Elegant Layout -->
    <section class="pt-24 lg:pt-32 pb-16 bg-white overflow-hidden">
      <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center relative">
          <!-- Elegant Top Label -->
          <div class="flex justify-center mb-6">
            <span class="px-4 py-1.5 rounded-full border border-primary/20 bg-primary/5 text-primary text-xs font-bold tracking-[0.2em] uppercase">
              Seit über 25 Jahren Ihr Partner
            </span>
          </div>

          <h3 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-base-content leading-tight mb-8 text-balance">
            <?= wp_kses_post($title) ?>
          </h3>

          <div class="flex justify-center items-center gap-4 mb-12">
            <div class="h-[1px] w-12 bg-gradient-to-r from-transparent to-primary/40"></div>
            <div class="w-2 h-2 rounded-full border-2 border-primary rotate-45"></div>
            <div class="h-[1px] w-12 bg-gradient-to-l from-transparent to-primary/40"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Section Content -->
    <section class="pb-24 lg:pb-32 bg-gradient-to-b from-white to-base-200/30">
      <div class="container mx-auto px-4">
        <?= $cardsHtml ?>
      </div>
    </section>
  <?php
    return ob_get_clean();
  }

  public function renderOne(string $content, int $number): string
  {
    ob_start();
  ?>
    <div class="card bg-base-100 shadow-md hover:shadow-xl transition-all duration-300 border border-base-300 h-full">
      <div class="card-body flex-row items-start gap-4">
        <!-- Number Badge -->
        <div class="avatar placeholder">
          <div class="bg-primary/20 text-primary rounded-full w-12 h-12">
            <span class="text-xl font-bold"><?= $number ?></span>
          </div>
        </div>

        <div class="prose prose-lg max-w-none text-base-content/80">
          <?= wp_kses_post($content) ?>
        </div>
      </div>
    </div>
<?php
    return ob_get_clean();
  }

  // Helper required by ModelRenderable but unused here as we iterate directly
  protected function getModelIndices(array $data): array
  {
    return [];
  }

  // Implementation for renderMany wrapper if needed
  public function renderFromModel(array $data, int $index): string
  {
    // Not typically used directly in this optimized render flow
    return '';
  }
  /**
   * Render multiple features
   * 
   * @param array $items Array of content strings
   * @return array Array of rendered HTML strings
   */
  public function renderMany(array $items): array
  {
    $rendered = [];
    foreach ($items as $index => $item) {
      if (empty($item)) continue;
      $rendered[] = $this->renderOne($item, $index + 1);
    }
    return $rendered;
  }
}
