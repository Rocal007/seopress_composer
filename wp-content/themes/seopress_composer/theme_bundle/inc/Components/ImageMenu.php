<?php
namespace SeopressComposer\Components;

class ImageMenu
{
  use \SeopressComposer\Components\Traits\ModelRenderable;

  private SectionTitle $sectionTitle;

  public function __construct(SectionTitle $sectionTitle)
  {
    $this->sectionTitle = $sectionTitle;
  }

  public function render(array $data, int $columns = 4, ?int $limit = null): string
  {
    $title = $data['title'] ?? '';
    $titleHtml = $this->sectionTitle->render($title);

    $items = $data['items'] ?? $data;
    if ($limit !== null && $limit > 0) {
      $items = array_slice($items, 0, $limit);
    }

    $gridHtml = "";
    if (!empty($items)) {
      // Map columns to classes
      $colClass = match ($columns) {
        2 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-2',
        3 => 'grid-cols-1 md:grid-cols-3',
        4 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
        default => 'grid-cols-1 md:grid-cols-3'
      };

      $gridHtml = '<div class="grid ' . $colClass . ' gap-6">';
      foreach ($items as $item) {
        $gridHtml .= $this->renderOne($item);
      }
      $gridHtml .= '</div>';
    }

    ob_start();
    ?>
    <section class="py-24 lg:py-32 bg-base-100">
      <div class="container mx-auto px-4">
        <?= $titleHtml ?>
        <?= $gridHtml ?>
      </div>
    </section>
    <?php
    return ob_get_clean();
  }

  public function renderOne(array $item): string
  {
    $url = $item['url'] ?? $item['link'] ?? '#';
    $image = $item['image'] ?? $item['image_url'] ?? '';
    $title = $item['title'] ?? '';

    if (!empty($image) && strpos($image, 'http') === 0) {
        $clean_title = wp_strip_all_tags(\seopress_container()->get(\SeopressComposer\Services\TextReplacementService::class)->clean_title($title));
        $image = \seopress_container()->get(\SeopressComposer\Services\ImageDownloadService::class)->get_local_image_url($image, $clean_title . ' menu', $clean_title . ' menu');
    }

    ob_start();
    ?>
    <a href="<?= esc_url($url) ?>" title="<?= esc_attr($title) ?> - Zur Seite"
      class="card bg-base-100 shadow-xl hover:shadow-2xl transition-all duration-300 border border-base-200 group h-full">
      <?php if ($image): ?>
        <figure class="px-4 pt-4">
          <img src="<?= esc_url($image) ?>" alt="<?= esc_attr($title) ?> Vorschaubild" title="<?= esc_attr($title) ?>"
            class="rounded-xl object-cover h-48 w-full group-hover:scale-105 transition-transform duration-300" />
        </figure>
      <?php endif; ?>
      <div class="card-body items-center text-center">
        <h3 class="card-title group-hover:text-primary transition-colors"><?= esc_html($title) ?></h3>
      </div>
    </a>
    <?php
    return ob_get_clean();
  }

  // ModelRenderable support
  public function renderFromModel(array $data, int $index): string
  {
    return '';
  }
  protected function getModelIndices(array $data): array
  {
    return [];
  }
}
