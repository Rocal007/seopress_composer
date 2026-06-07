<?php

namespace SeopressComposer\Components;

class MegaMenu
{
  public function render($item): string
  {
    $title = $item->title ?? '';
    $content = $item->content ?? ''; // Overview text
    $image_url = $item->image_url ?? '';

    if (!empty($image_url) && strpos($image_url, 'http') === 0) {
        $clean_title = wp_strip_all_tags(\seopress_container()->get(\SeopressComposer\Services\TextReplacementService::class)->clean_title($title));
        $image_url = \seopress_container()->get(\SeopressComposer\Services\ImageDownloadService::class)->get_local_image_url($image_url, $clean_title . ' mega', $clean_title . ' mega');
    }

    // Parent Icon via Service
    $icon_html = '';
    if (!empty($item->icon)) {
      $icon_html = \seopress_container()->get(\SeopressComposer\Services\IconService::class)->getIconHtml($item->icon, $title);
    }

    ob_start();
?>
    <details class="group-hover:open h-full flex items-center">
      <summary class="group-hover:text-primary transition-colors py-4 flex items-center gap-2 h-full cursor-pointer select-none"
        aria-haspopup="true" aria-label="<?php echo esc_attr($title); ?> Untermenü öffnen"
        title="<?php echo esc_attr($title); ?> Details anzeigen">
        <?php if ($icon_html): ?>
          <?php echo $icon_html; ?>
        <?php endif; ?>
        <?php echo esc_html($title); ?>
        <!-- Chevron -->
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none"
          class="stroke-current opacity-75 group-open:rotate-180 transition-transform" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </summary>

      <!-- Desktop Mega Menu Dropdown -->
      <div class="p-6 bg-base-100 text-base-content z-[100] w-[1000px] shadow-2xl rounded-2xl mt-0 border border-base-200 absolute left-1/2 -translate-x-1/2 top-full overflow-hidden">

        <?php if (!empty($content) || !empty($image_url)): ?>
          <!-- Optional Header/Intro Section if the parent itself has content -->
          <div class="mb-6 pb-6 border-b border-base-200 flex gap-6 items-center">
            <?php if ($image_url): ?>
              <div class="w-1/4 aspect-video rounded-lg overflow-hidden shrink-0">
                <img src="<?= esc_url($image_url) ?>" class="w-full h-full object-cover">
              </div>
            <?php endif; ?>
            <div class="flex-1">
              <h3 class="font-bold text-xl text-primary mb-2"><?= esc_html($title) ?></h3>
              <?php if ($content): ?>
                <div class="prose prose-sm text-base-content/70">
                  <?= wp_kses_post($content) ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Children Grid -->
        <div class="grid grid-cols-4 gap-6">
          <?php foreach ($item->children as $child):
            $c_img = $child->image_url ?? '';
            $c_title = $child->title ?? '';
            $c_url = $child->url ?? '#';

            if (!empty($c_img) && strpos($c_img, 'http') === 0) {
                $clean_title = wp_strip_all_tags(\seopress_container()->get(\SeopressComposer\Services\TextReplacementService::class)->clean_title($c_title));
                $c_img = \seopress_container()->get(\SeopressComposer\Services\ImageDownloadService::class)->get_local_image_url($c_img, $clean_title . ' child', $clean_title . ' child');
            }

            $c_icon_html = '';
            if (!empty($child->icon)) {
              $c_icon_html = \seopress_container()->get(\SeopressComposer\Services\IconService::class)->getIconHtml($child->icon, $child->title, ['size' => 'sm']);
            }
          ?>
            <a href="<?= esc_url($c_url) ?>" class="group/card block h-full">
              <div class="relative overflow-hidden rounded-xl bg-base-100 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 h-full border border-base-200">
                <!-- Image -->
                <div class="aspect-[4/3] overflow-hidden bg-base-200 relative">
                  <?php if ($c_img): ?>
                    <img src="<?= esc_url($c_img) ?>" alt="<?= esc_attr($c_title) ?>"
                      class="w-full h-full object-cover group-hover/card:scale-110 transition-transform duration-500">
                  <?php else: ?>
                    <div class="flex items-center justify-center w-full h-full text-base-content/20">
                      <!-- Fallback Placeholder -->
                      <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                    </div>
                  <?php endif; ?>

                  <!-- Icon Overlay -->
                  <?php if ($c_icon_html): ?>
                    <div class="absolute top-2 right-2 bg-white/90 backdrop-blur rounded-full p-1.5 shadow-sm text-primary">
                      <?= $c_icon_html ?>
                    </div>
                  <?php endif; ?>
                </div>

                <!-- Content -->
                <div class="p-4">
                  <h4 class="font-bold text-base text-primary mb-1 group-hover/card:text-secondary transition-colors line-clamp-2">
                    <?= esc_html($c_title) ?>
                  </h4>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </details>
  <?php
    return ob_get_clean();
  }

  /**
   * Render Service Categories in a Grid Layout
   * Displays service categories similar to "Weitere Leistungen" design
   * 
   * @param array $categories Array of service category items
   * @param string $title Optional title for the section (default: "Weitere Leistungen")
   * @return string Rendered HTML
   */
  public function renderServiceCategories(array $categories, string $title = 'Weitere Leistungen'): string
  {
    if (empty($categories)) {
      return '';
    }

    $iconService = \seopress_container()->get(\SeopressComposer\Services\IconService::class);

    ob_start();
  ?>
    <div class="bg-white py-12">
      <div class="container mx-auto px-4">
        <!-- Section Title -->
        <h2 class="text-3xl md:text-4xl font-bold text-center text-base-content mb-10">
          <?= esc_html($title) ?>
        </h2>

        <!-- Categories Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 md:gap-6">
          <?php foreach ($categories as $category):
            $cat = is_array($category) ? (object)$category : $category;
            $cat_title = $cat->title ?? '';
            $cat_url = $cat->url ?? $cat->link ?? '#';
            $cat_icon = $cat->icon ?? '';

            // Get icon HTML
            $icon_html = '';
            if (!empty($cat_icon)) {
              if (is_string($cat_icon) && (strpos($cat_icon, 'http') === 0 || strpos($cat_icon, '/') === 0)) {
                // It's a URL/path to an image
                if (strpos($cat_icon, 'http') === 0) {
                    $clean_title = wp_strip_all_tags(\seopress_container()->get(\SeopressComposer\Services\TextReplacementService::class)->clean_title($cat_title));
                    $cat_icon = \seopress_container()->get(\SeopressComposer\Services\ImageDownloadService::class)->get_local_image_url($cat_icon, $clean_title . ' icon', $clean_title . ' icon');
                }
                $icon_html = '<img src="' . esc_url($cat_icon) . '" class="w-full h-full object-contain" alt="' . esc_attr($cat_title) . '" />';
              } else {
                // It's an icon name
                $icon_html = $iconService->getIconHtml($cat_icon, $cat_title, ['size' => 'md']);
              }
            }
          ?>
            <a href="<?= esc_url($cat_url) ?>"
              class="group flex flex-col items-center justify-center p-6 bg-white border-2 border-base-300 rounded-xl hover:border-primary hover:shadow-lg transition-all duration-300"
              title="<?= esc_attr($cat_title) ?>">

              <!-- Icon Container -->
              <div class="w-16 h-16 md:w-20 md:h-20 mb-4 flex items-center justify-center text-primary group-hover:scale-110 transition-transform duration-300">
                <?php if ($icon_html): ?>
                  <?= $icon_html ?>
                <?php else: ?>
                  <!-- Fallback Icon -->
                  <svg class="w-full h-full" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                  </svg>
                <?php endif; ?>
              </div>

              <!-- Category Title -->
              <h3 class="text-sm md:text-base font-bold text-center text-base-content group-hover:text-primary transition-colors line-clamp-2">
                <?= esc_html($cat_title) ?>
              </h3>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
<?php
    return ob_get_clean();
  }
}
