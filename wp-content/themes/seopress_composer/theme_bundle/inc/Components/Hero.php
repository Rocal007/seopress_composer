<?php

namespace SeopressComposer\Components;

use SeopressComposer\Services\IconService;

/**
 * TopPictureSplitScreen Component
 * Ported from seopress_dev content/top_picture/views/top_picture_split_screen_view.php
 * Enhanced with DaisyUI
 */
class Hero
{
  private IconService $iconService;

  public function __construct(IconService $iconService)
  {
    $this->iconService = $iconService;
  }

  public function render(array $data): string
  {
    $image = $data['image'] ?? '';
    $title = $data['title'] ?? '';
    if (empty($title)) {
      $title = get_the_title();
    }
    $claim = $data['claim'] ?? '';
    $mobile_number = $data['mobile_number'] ?? '';
    // buttons logic: default true unless set to false
    $show_buttons = !isset($data['buttons']) || $data['buttons'];

    // Variant Switch
    $variant = $data['variant'] ?? 'default';

    if ($variant === 'boxed') {
      ob_start();
?>
      <section class="relative w-full min-h-[700px] bg-base-100 overflow-hidden font-sans"
        style="border-top: 4px solid hsl(var(--p)); box-shadow: inset 0 4px 0 0 hsl(var(--s));">
        <!-- Right Background Image -->
        <div class="absolute inset-y-0 right-0 w-full lg:w-[65%] h-full bg-base-200">
          <?php if (!empty($image)): ?>
            <?php $image_mobile = $data['image_mobile'] ?? ''; ?>
            <?php if (!empty($image_mobile)): ?>
                <picture class="absolute inset-0 w-full h-full">
                    <source media="(max-width: 768px)" srcset="<?= esc_url($image_mobile) ?>">
                    <img src="<?= esc_url($image) ?>" alt="<?= esc_attr($title) ?>" title="<?= esc_attr($title) ?>" class="w-full h-full object-cover" loading="eager" fetchpriority="high">
                </picture>
            <?php else: ?>
                <img src="<?= esc_url($image) ?>" alt="<?= esc_attr($title) ?>" title="<?= esc_attr($title) ?>"
                  class="w-full h-full object-cover" loading="eager" fetchpriority="high">
            <?php endif; ?>
            <div class="absolute inset-0 bg-black/10"></div>
          <?php endif; ?>
        </div>

        <!-- Content Area -->
        <div class="container mx-auto px-4 relative z-10 py-16 lg:py-24 h-full flex flex-col justify-center">


          <!-- Golden Plaque Title Box (Boxed Variant) — CSS in main.scss -->

          <div class="gold-plaque-container-v2 relative max-w-4xl shadow-2xl">
            <div class="gold-plaque-frame-v2">
              <div class="gold-plaque-inner-v2">
                <h1 class="gold-text-main-v2">
                  <?php
                  $prefix = $data['title_prefix'] ?? $title;
                  $suffix = $data['title_suffix'] ?? '';
                  echo wp_kses($prefix, ['sup' => ['class' => []]]);
                  ?>
                </h1>
                <?php if (!empty($suffix)): ?>
                  <div class="gold-separator-v2">
                    <span class="gold-text-sub-v2"><?= wp_kses_post($suffix) ?></span>
                  </div>
                <?php else: ?>
                  <div class="gold-separator-v2">
                    <span class="gold-text-sub-v2"><?= 'ANTIK & MODERN' ?></span>
                  </div>
                <?php endif; ?>
                <div class="gold-text-bottom-v2">
                  <?= 'FAIR & DISKRET' ?>
                </div>
              </div>
            </div>
          </div>

          <!-- White Content Box with Border -->
          <div class="bg-white/95 text-gray-800 p-6 sm:p-10 lg:p-12 max-w-3xl shadow-xl border-l-[8px] lg:border-l-[12px] border-secondary relative -mt-1 lg:-mt-4 ml-0 lg:ml-12 backdrop-blur-md rounded-b-2xl lg:rounded-none">
            <?php if (!empty($claim)): ?>
              <p class="text-lg md:text-xl font-medium mb-10 leading-relaxed opacity-90">
                <?= $claim ?>
              </p>
            <?php endif; ?>

            <!-- Buttons -->
            <?php if ($show_buttons): ?>
              <?php
              $btn_style = $data['button_style'] ?? 'standard';
              ?>

              <div class="flex flex-col sm:flex-row gap-4 w-full">
                <?php if (!empty($mobile_number)): ?>
                  <a href="tel:<?= esc_attr($mobile_number) ?>"
                    class="btn btn-primary btn-lg rounded-xl shadow-lg hover:shadow-xl transition-transform active:scale-95 w-full sm:w-auto order-1"
                    title="Jetzt anrufen" aria-label="Jetzt anrufen: <?= esc_attr($mobile_number) ?>">
                    <div class="w-5 h-5 mr-3">
                      <?= $this->iconService->getIcon($data['phone_icon'] ?? 'telefon', ['inner_class' => 'fill-current']) ?>
                    </div>
                    <?= esc_html($mobile_number) ?>
                  </a>
                <?php endif; ?>

                <?php
                $btn2_text = $data['btn2_text'] ?? 'Termin vereinbaren';
                $btn2_url = $data['btn2_url'] ?? '/kontakt';
                $btn2_href = (strpos($btn2_url, 'http') === 0) ? $btn2_url : get_bloginfo('url') . '/' . ltrim($btn2_url, '/');
                $btn2_icon = $data['btn2_icon'] ?? 'formular';
                ?>
                <a href="<?= esc_url($btn2_href) ?>"
                  class="btn btn-secondary btn-lg rounded-xl shadow-lg hover:shadow-xl transition-transform active:scale-95 w-full sm:w-auto order-2"
                  aria-label="<?= esc_attr($btn2_text) ?>">
                  <div class="w-5 h-5 mr-3">
                    <?= $this->iconService->getIcon($btn2_icon, ['inner_class' => 'fill-current']) ?>
                  </div>
                  <?= esc_html($btn2_text) ?>
                </a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </section>
    <?php
      return ob_get_clean();
    }

    ob_start();

    // Format phone number for display: +436649577732 → +43 664 957 77 32
    $phone_display = $mobile_number;
    if (preg_match('/^\+43(\d{3})(\d{3})(\d{2})(\d{2})$/', $mobile_number, $pm)) {
      $phone_display = '+43 ' . $pm[1] . ' ' . $pm[2] . ' ' . $pm[3] . ' ' . $pm[4];
    } elseif (preg_match('/^\+43(\d{3,4})(\d+)$/', $mobile_number, $pm)) {
      $phone_display = '+43 ' . $pm[1] . ' ' . chunk_split($pm[2], 3, ' ');
      $phone_display = rtrim($phone_display);
    }
    ?>
    <section class="split-screen-component w-full relative overflow-hidden"
      style="border-top: 4px solid hsl(var(--p)); box-shadow: 0 4px 20px -4px rgba(0,0,0,0.08);">

      <div class="flex flex-col lg:flex-row min-h-[420px] lg:min-h-[480px]">

        <!-- LEFT: Content on solid background -->
        <div class="w-full lg:w-1/2 bg-base-100 flex flex-col justify-center px-6 sm:px-10 lg:px-14 xl:px-20 py-10 lg:py-14 text-center lg:text-left order-2 lg:order-1">

            <?php
            $prefix = $data['title_prefix'] ?? $title;
            $suffix = $data['title_suffix'] ?? '';
            $prefix_clean = preg_replace('/<sup[^>]*>(.*?)<\/sup>/i', '$1', $prefix);
            ?>

            <!-- H1 -->
            <h1 class="text-2xl sm:text-3xl lg:text-[2.4rem] font-extrabold tracking-tight leading-[1.18] mb-4 text-base-content">
              <?= wp_kses($prefix_clean, ['strong' => ['class' => []], 'br' => []]) ?>
            </h1>

            <?php if (!empty($suffix)): ?>
              <p class="text-base lg:text-lg font-bold text-primary mb-5 tracking-wide uppercase" style="font-size: clamp(0.85rem, 1.4vw, 1.1rem); letter-spacing: 0.04em;">
                <?= wp_kses_post($suffix) ?>
              </p>
            <?php endif; ?>

            <!-- Claim -->
            <?php if (!empty($claim)): ?>
              <p class="text-sm lg:text-[0.95rem] mb-7 text-base-content/60 leading-relaxed max-w-lg">
                <?= $claim ?>
              </p>
            <?php endif; ?>

            <!-- Buttons -->
            <?php if ($show_buttons): ?>
              <div class="flex flex-col sm:flex-row gap-3 justify-center lg:justify-start">
                <?php if (!empty($mobile_number)): ?>
                  <a href="tel:<?= esc_attr($mobile_number) ?>"
                    class="btn btn-primary btn-md lg:btn-lg rounded-xl shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5 active:scale-95 text-sm lg:text-base"
                    aria-label="Jetzt anrufen: <?= esc_attr($mobile_number) ?>">
                    <div class="w-4 h-4 lg:w-5 lg:h-5 mr-2">
                      <?= $this->iconService->getIcon($data['phone_icon'] ?? 'telefon', ['inner_class' => 'fill-current']) ?>
                    </div>
                    <?= esc_html($phone_display) ?>
                  </a>
                <?php endif; ?>

                <?php
                $btn2_text = $data['btn2_text'] ?? 'Termin vereinbaren';
                $btn2_url = $data['btn2_url'] ?? '/kontakt';
                $btn2_href = (strpos($btn2_url, 'http') === 0) ? $btn2_url : get_bloginfo('url') . '/' . ltrim($btn2_url, '/');
                $btn2_icon = $data['btn2_icon'] ?? 'formular';
                ?>
                <a href="<?= esc_url($btn2_href) ?>"
                  class="btn btn-outline btn-primary btn-md lg:btn-lg rounded-xl shadow-md hover:shadow-xl transition-all hover:-translate-y-0.5 active:scale-95 bg-base-100 text-sm lg:text-base"
                  aria-label="<?= esc_attr($btn2_text) ?>">
                  <div class="w-4 h-4 lg:w-5 lg:h-5 mr-2">
                    <?= $this->iconService->getIcon($btn2_icon, ['inner_class' => 'fill-current']) ?>
                  </div>
                  <?= esc_html($btn2_text) ?>
                </a>
              </div>

              <!-- Trust Badges -->
              <div class="flex flex-wrap items-center gap-2.5 mt-6 justify-center lg:justify-start">
                <span class="inline-flex items-center gap-1.5 bg-success/15 text-success px-3 py-1 rounded-full font-semibold text-xs">
                  <svg aria-hidden="true" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                  Geprüfter Fachbetrieb
                </span>
                <span class="inline-flex items-center gap-1.5 bg-primary/10 text-primary px-3 py-1 rounded-full font-semibold text-xs">
                  <svg aria-hidden="true" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                  Festpreisgarantie
                </span>
                <span class="inline-flex items-center gap-1.5 bg-warning/15 text-warning px-3 py-1 rounded-full font-semibold text-xs">
                  <svg aria-hidden="true" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                  Kostenlose Besichtigung
                </span>
              </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT: Hero Image -->
        <div class="w-full lg:w-1/2 relative min-h-[240px] lg:min-h-0 order-1 lg:order-2">
          <?php if (!empty($image)): ?>
            <?php $image_mobile = $data['image_mobile'] ?? ''; ?>
            <?php if (!empty($image_mobile)): ?>
              <picture class="absolute inset-0 w-full h-full">
                <source media="(max-width: 768px)" srcset="<?= esc_url($image_mobile) ?>">
                <img src="<?= esc_url($image) ?>" alt="<?= esc_attr($title) ?>" class="w-full h-full object-cover" loading="eager" fetchpriority="high">
              </picture>
            <?php else: ?>
              <img src="<?= esc_url($image) ?>" alt="<?= esc_attr($title) ?>"
                class="absolute inset-0 w-full h-full object-cover" loading="eager" fetchpriority="high">
            <?php endif; ?>
            <!-- Subtle left-edge blend on desktop only -->
            <div class="hidden lg:block absolute inset-y-0 left-0 w-16 z-10" style="background: linear-gradient(to right, hsl(var(--b1)), transparent);"></div>
          <?php else: ?>
            <div class="absolute inset-0 bg-base-200"></div>
          <?php endif; ?>
        </div>

      </div>

    </section>
<?php
    return ob_get_clean();
  }
}
