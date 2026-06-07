<?php

namespace SeopressComposer\Components;

/**
 * SimpleContent Component
 * Enhanced with DaisyUI Prose (Typography)
 */
class SimpleContent
{
  use \SeopressComposer\Components\Traits\ModelRenderable;

  public function renderFromModel(array $data, int $index): string
  {
    if (isset($data[$index])) {
      return $this->render($data[$index]);
    }
    return '';
  }

  public function render(array $data): string
  {
    $title = $data['title'] ?? '';
    $content = $data['content'] ?? '';

    if (empty($title) && empty($content)) {
      return '';
    }

    ob_start();
?>
    <section id="simple-content" aria-labelledby="content-title" class="py-24 lg:py-32 bg-base-100">
      <div class="container mx-auto px-4">
        <?php if ($title): ?>
          <h2 id="content-title" class="text-3xl md:text-4xl font-bold text-center mb-12 text-primary">
            <?= $title ?>
            <div class="divider divider-accent w-24 mx-auto mt-4"></div>
          </h2>
        <?php endif; ?>

        <div class="prose prose-2xl max-w-5xl mx-auto prose-headings:text-secondary prose-a:text-primary leading-[2.5] text-xl md:text-2xl [&_p]:text-xl md:[&_p]:text-2xl [&_p]:leading-[2.5] [&_p]:mb-8">
          <?php
          if (is_array($content)) {
            echo implode('', array_map(fn($p) => "<p>" . $p . "</p>", $content));
          } else {
            echo $content;
          }
          ?>
        </div>
      </div>
    </section>
  <?php
    return ob_get_clean();
  }
  /**
   * Render just the content prose without wrappers/title
   */
  public function renderInner($content): string
  {
    ob_start();
  ?>
    <div class="prose prose-3xl max-w-5xl mx-auto prose-headings:text-secondary prose-a:text-primary text-lg leading-relaxed text-center">
      <?php
      if (is_array($content)) {
        echo implode('', array_map(fn($p) => "<p>" . $p . "</p>", $content));
      } else {
        echo $content;
      }
      ?>
    </div>
<?php
    return ob_get_clean();
  }
}
