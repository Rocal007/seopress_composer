<?php

namespace SeopressComposer\Layouts;

class FooterLinks
{
  public function render(array $data): string
  {
    $copyright = $data['copyright'] ?? '';
    $links = $data['links'] ?? [];

    ob_start();
?>
    <div class="bg-neutral text-neutral-content py-4 border-t border-neutral-content/10 font-sans text-sm">
      <div class="container mx-auto px-4 flex flex-wrap justify-center items-center gap-4">

        <div class="opacity-80">
          <?= wp_kses_post($copyright) ?>
        </div>

        <?php if (!empty($links)): ?>
          <nav>
            <ul class="flex flex-wrap justify-center gap-4 md:gap-8">
              <?php foreach ($links as $link): ?>
                <li>
                  <a href="<?= esc_url($link['url']) ?>"
                    class="hover:text-accent transition-colors opacity-80 hover:opacity-100">
                    <?= esc_html($link['title']) ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </nav>
        <?php endif; ?>

      </div>
    </div>
<?php
    return ob_get_clean();
  }
}
