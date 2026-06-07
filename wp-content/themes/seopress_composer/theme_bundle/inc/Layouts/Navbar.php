<?php

namespace SeopressComposer\Layouts;

class Navbar
{
  /**
   * Render Navbar Layout
   * 
   * @param array $content Array of content blocks:
   *  0 => Start (Logo)
   *  1 => Center (Desktop Menu)
   *  2 => End Extra (Search, CTA Buttons)
   *  3 => Mobile Menu Content (The <ul> list)
   */
  public function render(array $content, string $class = ''): string
  {
    $start = $content[0] ?? '';
    $center = $content[1] ?? '';
    $endExtra = $content[2] ?? '';
    $mobileMenu = $content[3] ?? '';

    ob_start();
?>
    <!-- Navbar with Gray Theme (matches body) -->
    <div
      class="navbar bg-gray-50 text-base-content lg:sticky lg:top-0 z-[1000] py-4 border-b-4 border-double border-base-200/60 <?= esc_attr($class) ?>">
      <div class="container mx-auto px-4">

        <!-- Start: Logo -->
        <div class="navbar-start flex-1 2xl:flex-none 2xl:w-1/4 flex items-center">
          <?= $start ?>
        </div>

        <!-- Center: Desktop Menu -->
        <div class="navbar-center hidden lg:flex 2xl:w-1/2 justify-center">
          <?= $center ?>
        </div>

        <!-- End: CTA & Mobile Menu -->
        <div class="navbar-end flex-none 2xl:w-1/4 flex justify-end items-center gap-2">

          <!-- Hamburger (Visible everywhere) -->
          <!-- Hamburger (Visible everywhere) -->
          <button class="btn btn-ghost flex items-center gap-2" onclick="document.getElementById('mega-menu-overlay').classList.toggle('hidden')" aria-label="Menu">
            <span class="hidden sm:inline font-bold uppercase tracking-wider text-sm">Menü</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" />
            </svg>
          </button>

          <!-- Search / Extra HTML -->
          <?= $endExtra ?>

        </div>

      </div>

      <!-- Injected Mega Menu (Fixed Position overlay) -->
      <?= $mobileMenu ?>
    </div>
<?php
    return ob_get_clean();
  }
}
