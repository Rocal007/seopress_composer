<?php

namespace SeopressComposer\Components;

/**
 * Districts Component
 * Enhanced with DaisyUI
 */
class Districts
{
  public function render(array $districts = []): string
  {
    if (empty($districts)) {
      return '';
    }

    ob_start();
?>
    <section id="districts-section" class="py-24 lg:py-32 bg-base-100">
      <div class="container mx-auto px-4">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <?php foreach ($districts as $district): ?>
            <a href="<?= esc_url($district['link']) ?>"
              title="Entrümpelungen, Räumungen, Verlassenschaften,... in <?= esc_attr($district['name']) ?>"
              class="card bg-base-100 shadow-sm hover:shadow-md transition-all duration-300 border border-base-300 group hover:border-primary flex-row items-center p-4 h-full rounded-2xl">

              <div class="w-8 h-8 mr-4 flex-shrink-0">
                <?php if (!empty($district['karte'])): ?>
                  <img src="<?= esc_url($district['karte']) ?>" alt="Map icon for <?= esc_attr($district['name']) ?>"
                    class="w-full h-full object-contain" loading="lazy" />
                <?php else: ?>
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                <?php endif; ?>
              </div>

              <span class="font-medium text-base-content group-hover:text-primary transition-colors">
                <?= esc_html($district['name']) ?>
              </span>

              <svg xmlns="http://www.w3.org/2000/svg"
                class="h-4 w-4 ml-auto opacity-0 group-hover:opacity-100 transition-opacity text-primary" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </a>
          <?php endforeach; ?>
        </div>

      </div>
    </section>
  <?php
    return ob_get_clean();
  }

  public function renderGrid(array $districts = []): string
  {
    if (empty($districts)) {
      return '';
    }

    ob_start();
  ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php foreach ($districts as $district): ?>
        <a href="<?= esc_url($district['link']) ?>"
          title="Entrümpelungen, Räumungen, Verlassenschaften,... in <?= esc_attr($district['name']) ?>"
          class="card bg-base-100 shadow-sm hover:shadow-md transition-all duration-300 border border-base-300 group hover:border-primary flex-row items-center p-4 h-full rounded-2xl">

          <div class="w-8 h-8 mr-4 flex-shrink-0">
            <?php if (!empty($district['karte'])): ?>
              <img src="<?= esc_url($district['karte']) ?>" alt="Map icon for <?= esc_attr($district['name']) ?>"
                class="w-full h-full object-contain" loading="lazy" />
            <?php else: ?>
              <svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            <?php endif; ?>
          </div>

          <span class="font-medium text-base-content group-hover:text-primary transition-colors">
            <?= esc_html($district['name']) ?>
          </span>

          <svg xmlns="http://www.w3.org/2000/svg"
            class="h-4 w-4 ml-auto opacity-0 group-hover:opacity-100 transition-opacity text-primary" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </a>
      <?php endforeach; ?>
    </div>
<?php
    return ob_get_clean();
  }
}
