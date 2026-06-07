<?php

namespace SeopressComposer\Components;

use SeopressComposer\Services\IconService;

/**
 * ShareButtons Component
 * Renders a collapsible floating share button group
 */
class ShareButtons
{
  private IconService $iconService;

  public function __construct(IconService $iconService)
  {
    $this->iconService = $iconService;
  }

  /**
   * Render the share buttons
   * 
   * @return string
   */
  public function render(): string
  {
    $url = urlencode(get_permalink());
    $title = urlencode(get_the_title());

    $shares = [
      [
        'name' => 'whatsapp',
        'url' => "https://wa.me/?text={$title}%20{$url}",
        'color' => '#25D366',
        'icon' => 'whatsapp'
      ],
      [
        'name' => 'facebook',
        'url' => "https://www.facebook.com/sharer/sharer.php?u={$url}",
        'color' => '#1877F2',
        'icon' => 'facebook'
      ],
      [
        'name' => 'email',
        'url' => "mailto:?subject={$title}&body={$url}",
        'color' => '#EA4335',
        'icon' => 'email'
      ]
    ];

    ob_start();
?>
    <div id="header-share-wrapper" class="relative flex items-center group">
      <!-- Main Toggle Button -->
      <button id="share-toggle-btn"
        class="btn btn-ghost btn-circle btn-sm hover:bg-primary/10 transition-all duration-300 relative z-20"
        aria-label="Teilen-Menü umschalten">
        <div id="share-toggle-icon" class="w-6 h-6 flex items-center justify-center transition-transform duration-500">
          <?= $this->iconService->getIcon('share', ['inner_class' => 'illu text-primary']) ?>
        </div>
      </button>

      <!-- Expanded Share Icons (Absolute to the Right) -->
      <div id="share-items-container"
        class="absolute right-12 top-1/2 flex flex-row gap-2 opacity-0 invisible transition-all duration-300 z-10 origin-right">
        <?php foreach ($shares as $index => $share): ?>
          <a href="<?= $share['url'] ?>"
            target="_blank"
            rel="noopener noreferrer"
            class="btn btn-circle btn-xs shadow-sm hover:scale-110 active:scale-95 transition-all duration-300"
            style="background-color: <?= $share['color'] ?>; border-color: <?= $share['color'] ?>; color: white;"
            aria-label="Share on <?= ucfirst($share['name']) ?>">
            <div class="w-2.5 h-2.5 flex items-center justify-center">
              <?= $this->iconService->getIcon($share['icon'], ['inner_class' => 'fill-current']) ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>

      <style>
        #header-share-wrapper #share-items-container {
          transform: translateY(-50%) scale(0.9);
        }

        #header-share-wrapper.active #share-items-container {
          opacity: 1;
          visibility: visible;
          transform: translateY(-50%) scale(1);
        }

        #header-share-wrapper.active #share-toggle-icon {
          transform: rotate(90deg);
        }
      </style>

      <script>
        document.addEventListener('DOMContentLoaded', () => {
          const wrapper = document.getElementById('header-share-wrapper');
          const btn = document.getElementById('share-toggle-btn');

          if (btn && wrapper) {
            btn.addEventListener('click', (e) => {
              e.preventDefault();
              e.stopPropagation();
              wrapper.classList.toggle('active');
            });

            document.addEventListener('click', (e) => {
              if (!wrapper.contains(e.target)) {
                wrapper.classList.remove('active');
              }
            });
          }
        });
      </script>
    </div>
<?php
    return ob_get_clean();
  }
}
