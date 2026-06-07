<?php
namespace SeopressComposer\Components;

/**
 * LeftRightToggle Component
 * Replicates the behavior of the original toggle component using accessible details/summary or radio logic.
 * 
 * Logic: Two headers side-by-side. 
 * Clicking Left Header toggles Left Content.
 * Clicking Right Header toggles Right Content.
 * They are independent (or can be mutually exclusive if using radio).
 * Original bootstrap 'collapse' allows independent or grouped toggling. 
 * The visual design suggests a split header.
 */
class LeftRightToggle
{
  public function render(string $leftTitle, string $rightTitle, string $leftContent, string $rightContent): string
  {
    $id = uniqid('toggle-');

    ob_start();
    ?>
    <section class="py-0 bg-secondary w-full">
      <div class="w-full">

        <!-- Toggle Headers (Tab Style) -->
        <div class="container mx-auto px-4">
          <div class="flex flex-col md:flex-row gap-0">

            <!-- Left Header (Active by Default) -->
            <button onclick="document.getElementById('<?= $id ?>-left').classList.toggle('hidden');this.classList.toggle('bg-white/10');"
              class="flex-1 bg-white/10 hover:bg-white/15 py-4 text-left text-sm font-semibold uppercase tracking-wider text-white cursor-pointer transition-colors block group border-b-2 border-white/50">
              <span>▼ <?= esc_html($leftTitle) ?></span>
            </button>

            <!-- Right Header -->
            <button onclick="document.getElementById('<?= $id ?>-right').classList.toggle('hidden');this.classList.toggle('bg-white/10');"
              class="flex-1 bg-secondary hover:bg-white/10 py-4 text-right text-sm font-semibold uppercase tracking-wider text-white cursor-pointer transition-colors block group border-b-2 border-transparent hover:border-white/30">
              <span>▼ <?= esc_html($rightTitle) ?></span>
            </button>

          </div>
        </div>

        <!-- Content Area -->
        <div class="bg-secondary p-0 w-full">

          <!-- Left Content (VISIBLE by default) -->
          <div id="<?= $id ?>-left"
            class="transition-all duration-300 ease-in-out p-8 text-white/90 text-sm container mx-auto">
            <?= $leftContent ?>
          </div>

          <!-- Right Content (Hidden) -->
          <div id="<?= $id ?>-right"
            class="hidden transition-all duration-300 ease-in-out p-8 text-white/90 text-sm container mx-auto">
            <?= $rightContent ?>
          </div>

        </div>

      </div>
    </section>
    <?php
    return ob_get_clean();
  }
}
