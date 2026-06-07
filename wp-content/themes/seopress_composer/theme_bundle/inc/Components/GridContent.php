<?php
namespace SeopressComposer\Components;

/**
 * GridContent Component
 * Enhanced with DaisyUI cards
 */
class GridContent
{
  public function render(array $data): string
  {
    $title = $data['title'] ?? '';
    $keywords = array_filter($data['keywords'] ?? []); // Filtert leere Keywords heraus

    if (empty($keywords)) {
      return '';
    }

    ob_start();
    ?>
    <section id="grid-content" aria-labelledby="grid-content-title" class="py-24 lg:py-32 bg-base-200">
      <div class="container mx-auto px-4">
        <?php if ($title): ?>
          <h2 id="grid-content-title" class="text-3xl md:text-4xl font-bold text-center mb-12 text-primary">
            <?= esc_html($title) ?>
            <div class="divider divider-primary w-24 mx-auto mt-4"></div>
          </h2>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-6xl mx-auto">
          <?php foreach ($keywords as $index => $keyword_content): ?>
            <div class="card bg-base-100 shadow-lg hover:shadow-xl transition-all duration-300 border-l-4 border-accent">
              <div class="card-body flex-row gap-4 items-center">
                <span class="text-3xl font-black text-base-content/20 flex-shrink-0 w-12 text-center">
                  <?= $index + 1 ?>.
                </span>
                <div class="prose prose-sm max-w-none">
                  <?= wp_kses_post($keyword_content) ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php
    return ob_get_clean();
  }
}