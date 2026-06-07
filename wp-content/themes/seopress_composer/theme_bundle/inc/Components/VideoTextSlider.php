<?php

namespace SeopressComposer\Components;

use SeopressComposer\Helpers\PageHelper;
use SeopressComposer\Controllers\ContextController;

class VideoTextSlider
{
  private PageHelper $pageHelper;
  private ContextController $contextController;

  public function __construct(
    PageHelper $pageHelper,
    ContextController $contextController
  ) {
    $this->pageHelper = $pageHelper;
    $this->contextController = $contextController;
  }

  public function render(array $data, string $bgClass = 'bg-base-100'): string
  {
    if (empty($data)) {
      return '';
    }

    $items_per_slide = 3;
    $menu_type = 'seopress-video-carousel';
    $carousel_id = esc_attr($menu_type . '-carousel');

    ob_start();
?>

    <div id="<?php echo $carousel_id; ?>" class="<?= esc_attr($bgClass) ?>" aria-label="Video Showcase">
      <div class="container mx-auto px-4">

        <div class="text-center mb-12">
          <h2 class="text-4xl font-bold text-primary mb-2">Videos</h2>
          <h3 class="text-2xl font-bold text-secondary mb-4">
            <?php echo get_the_title($this->pageHelper->check_page_id()); ?>
          </h3>
          <div class="divider divider-accent w-24 mx-auto"></div>
        </div>

        <!-- DaisyUI Carousel (Snap) -->
        <div class="carousel carousel-center max-w-full p-4 space-x-4 bg-base-200 rounded-box">
          <?php foreach ($data as $index => $video):
            $video_id = esc_attr($video['video']);
            $video_title = esc_html($video['content']);
          ?>
            <!-- Carousel Item -->
            <div class="carousel-item w-full md:w-1/2 lg:w-1/3">
              <article class="card w-full bg-base-100 shadow-xl overflow-hidden hover:shadow-2xl transition-all duration-300">
                <figure class="relative aspect-video group cursor-pointer">
                  <img src="https://img.youtube.com/vi/<?php echo $video_id; ?>/hqdefault.jpg"
                    alt="<?php echo $video_title; ?>"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">

                  <!-- Play Button Overlay -->
                  <div
                    class="absolute inset-0 bg-black/40 flex items-center justify-center group-hover:bg-black/30 transition-colors">
                    <div
                      class="w-16 h-16 bg-primary/90 rounded-full flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                      <span class="text-3xl text-primary-content ml-1">▶</span>
                    </div>
                  </div>

                  <!-- Iframe inject target (handled by JS usually, or link to YT) -->
                  <iframe class="hidden absolute inset-0 w-full h-full" src="about:blank"
                    data-src="https://www.youtube.com/embed/<?php echo $video_id; ?>?autoplay=1" frameborder="0"
                    allow="autoplay; encrypted-media" allowfullscreen></iframe>
                </figure>

                <div class="card-body p-5">
                  <h2 class="card-title text-lg leading-tight min-h-[3rem]">
                    <?php echo $video_title; ?>
                  </h2>

                  <div class="card-actions justify-between items-center mt-4 pt-4 border-t border-base-200">
                    <span class="badge badge-outline"><?php echo esc_html($video['category_cleaned']); ?></span>
                    <a href="<?php echo esc_url($video['category_url']); ?>" class="btn btn-primary btn-xs btn-outline">
                      Mehr &raquo;
                    </a>
                  </div>
                </div>
              </article>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="flex justify-center w-full py-4 gap-2">
          <span class="text-sm text-base-content/50">← Swipen für mehr Videos →</span>
        </div>

      </div>
    </div>
<?php
    return ob_get_clean();
  }
}
