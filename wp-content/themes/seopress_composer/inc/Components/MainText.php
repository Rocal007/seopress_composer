<?php

namespace SeopressComposer\Components;

/**
 * MainText Component
 * Renders the main content text block (H1 + Content)
 */
class MainText
{
  public function render(array $data): string
  {
    if (empty($data)) {
      return '';
    }

    $title = $data['title'] ?? '';
    // If title is missing, fallback to standard WP title if in loop, but usually passed data
    $content = $data['content'] ?? $data['items'][0] ?? ''; // Handle different structures

    // If 'haupttext' specific structure exists (from Data Model)
    if (isset($data['haupttext'])) {
      $title = $data['haupttext']['title'] ?? $title;
      $content = $data['haupttext']['content'] ?? $content;
    }

    if (empty($title) && empty($content)) {
      return '';
    }

    ob_start();
?>
    <section class="py-24 lg:py-32 bg-base-100">
      <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
          <?php if (!empty($title)): ?>
            <h1 class="text-3xl md:text-5xl font-black mb-8 text-primary leading-tight">
              <?= wp_kses_post($title) ?>
            </h1>
          <?php endif; ?>

          <?php if (!empty($content)): ?>
            <div class="prose prose-lg mx-auto text-base-content/80 text-left leading-relaxed">
              <?= $content ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>
<?php
    return ob_get_clean();
  }
}
