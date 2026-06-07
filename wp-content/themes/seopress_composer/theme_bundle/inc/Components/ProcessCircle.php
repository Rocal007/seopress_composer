<?php
namespace SeopressComposer\Components;

use SeopressComposer\Services\IconService;

/**
 * ProcessCircle Component
 * Displays process steps (Ablauf) in a circular/step visual layout using daisyUI & Tailwind
 */
class ProcessCircle
{
  private IconService $iconService;

  public function __construct(IconService $iconService)
  {
    $this->iconService = $iconService;
  }

  public function render(array $data): string
  {
    if (empty($data['items'])) {
      return '';
    }

    $heading = $data['heading'] ?? 'Unser Ablauf';
    $subheading = $data['subheading'] ?? '';
    $items = $data['items'];

    ob_start();
    ?>
    <section class="py-24 lg:py-32 bg-base-100 overflow-hidden">
      <div class="container mx-auto px-4">

        <!-- Header -->
        <div class="text-center mb-16 max-w-3xl mx-auto">
          <h2 class="text-4xl lg:text-5xl font-black mb-6 text-primary tracking-tight">
            <?= esc_html($heading) ?>
          </h2>
          <?php if (!empty($subheading)): ?>
            <p class="text-xl text-base-content/70">
              <?= esc_html($subheading) ?>
            </p>
          <?php endif; ?>
        </div>

        <!-- Desktop Process (Horizontal) -->
        <div class="hidden lg:flex justify-between items-start relative gap-8">

          <!-- Connection Line -->
          <div
            class="absolute top-[3.5rem] left-0 right-0 h-1 bg-gradient-to-r from-primary/20 via-primary to-primary/20 -z-0">
          </div>

          <?php foreach ($items as $index => $item): ?>
            <div class="relative z-10 flex-1 flex flex-col items-center text-center group">

              <!-- Circle Icon (Logo Style) -->
              <div
                class="w-28 h-28 rounded-full bg-primary text-white ring-4 ring-primary/10 ring-offset-4 shadow-xl flex items-center justify-center mb-8 group-hover:scale-110 group-hover:ring-primary/30 transition-all duration-300 relative">
                <div class="w-14 h-14 flex items-center justify-center">
                  <?= $this->iconService->getIcon($item['icon'] ?? 'check', ['class' => 'w-full h-full fill-current']) ?>
                </div>

                <!-- Step Number Badge -->
                <div
                  class="absolute -top-2 -right-2 w-10 h-10 rounded-full bg-secondary text-white font-bold text-xl flex items-center justify-center border-4 border-white shadow-lg">
                  <?= $index + 1 ?>
                </div>
              </div>

              <!-- Content -->
              <h3 class="text-2xl font-bold mb-3 text-base-content group-hover:text-primary transition-colors">
                <?= esc_html($item['heading'] ?? '') ?>
              </h3>
              <div class="text-base text-base-content/70 px-4 leading-relaxed">
                <?= $item['content'] ?? '' ?>
              </div>

            </div>
          <?php endforeach; ?>

        </div>

        <!-- Mobile Process (Vertical Timeline) -->
        <div class="lg:hidden space-y-8 pl-4">
          <?php foreach ($items as $index => $item): ?>
            <div class="relative pl-8 border-l-4 border-primary/20 last:border-0 pb-8 last:pb-0">
              <!-- Dot -->
              <div
                class="absolute -left-[1.2rem] top-0 w-8 h-8 rounded-full bg-primary border-4 border-white shadow text-white font-bold flex items-center justify-center text-sm">
                <?= $index + 1 ?>
              </div>

              <div class="bg-base-200 rounded-2xl p-6 shadow-sm border border-base-300 active:scale-[0.98] transition-transform">
                <div class="flex items-center gap-4 mb-4">
                  <div class="w-12 h-12 text-primary flex-shrink-0">
                    <?= $this->iconService->getIcon($item['icon'] ?? 'check', ['inner_class' => 'fill-current']) ?>
                  </div>
                  <h3 class="text-xl font-bold text-base-content">
                    <?= esc_html($item['heading'] ?? '') ?>
                  </h3>
                </div>
                <div class="text-base-content/80">
                  <?= $item['content'] ?? '' ?>
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
