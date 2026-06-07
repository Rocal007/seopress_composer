<?php
namespace SeopressComposer\Components;

/**
 * Backlinks Component
 * Enhanced with DaisyUI
 */
class Backlinks
{
  public function render(array $backlinks = []): string
  {
    if (empty($backlinks)) {
      return '';
    }

    ob_start();
    ?>
    <section id="backlinks-section" class="py-24 lg:py-32 bg-base-200">
      <div class="container mx-auto px-4">
        <div class="text-center mb-8">
          <h3 class="text-2xl font-bold text-secondary">Partner & Netzwerke</h3>
          <div class="divider w-16 mx-auto"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <?php foreach ($backlinks as $link): ?>
            <a href="<?= esc_url($link['url']) ?>" target="_blank" rel="noopener noreferrer"
              class="card bg-base-100 shadow-sm hover:shadow-md transition-all duration-300 border border-base-300 group hover:border-primary flex-row items-center p-4 h-full"
              aria-label="Visit <?= esc_attr($link['text']) ?>" title="Visit <?= esc_attr($link['text']) ?>">

              <!-- Color Indicator -->
              <div class="w-3 h-3 rounded-full mr-4 flex-shrink-0 shadow-sm"
                style="background: linear-gradient(to bottom, <?= esc_attr($link['color_1']) ?> 50%, <?= esc_attr($link['color_2']) ?> 50%);">
              </div>

              <span class="font-medium text-base-content group-hover:text-primary transition-colors">
                <?= esc_html($link['text']) ?>
              </span>

              <svg xmlns="http://www.w3.org/2000/svg"
                class="h-4 w-4 ml-auto opacity-0 group-hover:opacity-100 transition-opacity text-primary" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
              </svg>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php
    return ob_get_clean();
  }

  /**
   * Render backlinks as a dropdown/collapse component
   * Useful for footer or end-of-content areas
   */
  public function renderDropdown(array $backlinks = []): string
  {
    if (empty($backlinks)) {
      return '';
    }

    ob_start();
    ?>
    <section class="py-16 lg:py-24 bg-base-100">
      <div class="container mx-auto px-4 max-w-4xl">
        <div class="collapse collapse-arrow border border-base-300 bg-base-200">
          <input type="checkbox" />
          <div class="collapse-title text-xl font-medium text-secondary">
            Partner & Netzwerke ansehen
          </div>
          <div class="collapse-content bg-base-100">
            <div class="py-4 grid grid-cols-1 md:grid-cols-2 gap-4">
              <?php foreach ($backlinks as $link): ?>
                <a href="<?= esc_url($link['url']) ?>" target="_blank" rel="noopener noreferrer"
                  class="flex items-center p-3 rounded-lg hover:bg-base-200 transition-colors group"
                  title="Visit <?= esc_attr($link['text']) ?>" aria-label="Visit <?= esc_attr($link['text']) ?>">

                  <div class="w-2 h-2 rounded-full mr-3 flex-shrink-0"
                    style="background: linear-gradient(to bottom, <?= esc_attr($link['color_1']) ?> 50%, <?= esc_attr($link['color_2']) ?> 50%);">
                  </div>

                  <span class="text-sm font-medium text-base-content group-hover:text-primary transition-colors">
                    <?= esc_html($link['text']) ?>
                  </span>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </section>
    <?php
    return ob_get_clean();
  }
  /**
   * Render just the grid of backlinks (without section wrapper)
   * Useful for embedding in Tabs
   */
  public function renderGrid(array $backlinks = []): string
  {
    if (empty($backlinks)) {
      return '';
    }

    ob_start();
    ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php foreach ($backlinks as $link): ?>
        <a href="<?= esc_url($link['url']) ?>" target="_blank" rel="noopener noreferrer"
          class="card bg-base-100 shadow-sm hover:shadow-md transition-all duration-300 border border-base-300 group hover:border-primary flex-row items-center p-4 h-full"
          aria-label="Visit <?= esc_attr($link['text']) ?>" title="Visit <?= esc_attr($link['text']) ?>">

          <!-- Color Indicator -->
          <div class="w-3 h-3 rounded-full mr-4 flex-shrink-0 shadow-sm"
            style="background: linear-gradient(to bottom, <?= esc_attr($link['color_1']) ?> 50%, <?= esc_attr($link['color_2']) ?> 50%);">
          </div>

          <span class="font-medium text-base-content group-hover:text-primary transition-colors">
            <?= esc_html($link['text']) ?>
          </span>

          <svg xmlns="http://www.w3.org/2000/svg"
            class="h-4 w-4 ml-auto opacity-0 group-hover:opacity-100 transition-opacity text-primary" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
          </svg>
        </a>
      <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
  }
}
