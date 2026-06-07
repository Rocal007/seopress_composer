<?php

namespace SeopressComposer\Components;

class Menu
{
  use \SeopressComposer\Components\Traits\ModelRenderable;

  /**
   * Standard Menu Layout (Dropdowns)
   */
  public function render(array $items, bool $is_mobile = false): string
  {
    if (empty($items)) {
      return '';
    }

    ob_start();
?>
    <ul
      class="<?= $is_mobile ? 'menu menu-lg dropdown-content mt-3 z-[1] p-2 shadow-2xl bg-white text-base-content rounded-box w-64 border border-base-200' : 'menu menu-horizontal px-1 gap-1 text-[15px] font-medium text-secondary/90' ?>">
      <?php $this->render_items_recursive($items, $is_mobile); ?>
    </ul>
  <?php
    return ob_get_clean();
  }

  /**
   * Mega Dropdown Menu (Full Width Grid)
   * Designed to be placed inside a hamburger dropdown container.
   */
  public function renderMegaDropdown(array $items): string
  {
    if (empty($items)) {
      return '';
    }

    ob_start();
  ?>
    <div id="mega-menu-overlay"
      class="menu p-8 shadow-2xl bg-white fixed top-[80px] left-0 right-0 w-screen max-w-none h-[calc(100vh-80px)] overflow-y-auto border-t-4 border-primary z-[2000] hidden">

      <!-- Close Button -->
      <button onclick="document.getElementById('mega-menu-overlay').classList.add('hidden')"
        class="absolute top-4 right-4 btn btn-circle btn-sm btn-ghost hover:bg-base-200"
        aria-label="Menü schließen">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>

      <div class="container mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <?php foreach ($items as $item):
            $url = $item->url ?? '#';
            $title = $item->title ?? '';
            $has_children = !empty($item->children);
            $image_url = $item->image_url ?? '';
            $overview_text = $item->overview_text ?? '';
          ?>
            <div class="flex flex-col h-full hover:bg-base-200/50 p-4 rounded-xl transition-colors">
              <!-- Hero Image Preview -->
              <?php if (!empty($image_url)): ?>
                <a href="<?= esc_url($url) ?>" title="<?= esc_attr($title) ?> - Zur Übersicht"
                  class="block mb-4 overflow-hidden rounded-lg shadow-sm hover:shadow-md transition-shadow group aspect-video">
                  <img src="<?= esc_url($image_url) ?>" alt="<?= esc_attr($title) ?> Übersicht Bild"
                    title="<?= esc_attr($title) ?> Übersicht"
                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                </a>
              <?php endif; ?>

              <!-- Column Header / Top Level Item -->
              <a href="<?= esc_url($url) ?>" title="<?= esc_attr($title) ?> - Mehr erfahren"
                class="text-xl font-bold text-primary mb-3 hover:underline block pb-2 border-b-2 border-primary/20">
                <?= esc_html($title) ?>
              </a>

              <!-- Overview Text -->
              <?php if (!empty($overview_text)): ?>
                <div class="text-sm text-base-content/70 mb-4 line-clamp-3">
                  <?= $overview_text ?>
                </div>
              <?php endif; ?>

              <?php if ($has_children): ?>
                <ul class="space-y-2 mt-auto">
                  <?php foreach ($item->children as $child): ?>
                    <li>
                      <a href="<?= esc_url($child->url) ?>" title="<?= esc_attr($child->title) ?> - Details"
                        class="block py-1 text-base-content font-medium hover:text-primary hover:translate-x-1 transition-all">
                        <?= esc_html($child->title) ?>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Service Categories Section -->
        <?php
        // Get service categories from menu data
        // Get service categories from menu data
        $serviceCategories = \seopress_models()->getMenuModel()->get_service_categories();

        if (!empty($serviceCategories)):
          $megaMenu = \seopress_container()->get(\SeopressComposer\Components\MegaMenu::class);
        ?>
          <div class="mt-12 pt-8 border-t-2 border-base-200">
            <?= $megaMenu->renderServiceCategories($serviceCategories) ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php
    return ob_get_clean();
  }

  /**
   * Sidebar Groups Renderer
   * Renders multiple sections of menu items for sidebar navigation
   * 
   * @param array $sections Array of sections, each with 'title', 'items', and 'class'
   * @return string Rendered sidebar HTML
   */
  public function renderSidebarGroups(array $sections): string
  {
    if (empty($sections)) {
      return '';
    }

    ob_start();
  ?>
    <aside class="sidebar-navigation bg-base-100 rounded-lg shadow-md p-6 space-y-8">
      <?php foreach ($sections as $section):
        $title = $section['title'] ?? '';
        $items = $section['items'] ?? [];
        $class = $section['class'] ?? '';

        if (empty($items)) continue;
      ?>
        <div class="sidebar-section <?= esc_attr($class) ?>">
          <?php if (!empty($title)): ?>
            <h3 class="text-lg font-bold text-primary mb-4 pb-2 border-b-2 border-primary/20">
              <?= esc_html($title) ?>
            </h3>
          <?php endif; ?>

          <ul class="space-y-2">
            <?php foreach ($items as $item):
              $url = is_array($item) ? ($item['link'] ?? '#') : ($item->url ?? '#');
              $item_title = is_array($item) ? ($item['title'] ?? '') : ($item->title ?? '');
              $icon = is_array($item) ? ($item['icon'] ?? '') : ($item->icon ?? '');
            ?>
              <li>
                <a href="<?= esc_url($url) ?>"
                  title="<?= esc_attr($item_title) ?>"
                  class="flex items-center gap-3 py-2 px-3 rounded-lg hover:bg-base-200 hover:text-primary transition-all group">
                  <?php if (!empty($icon)): ?>
                    <span class="text-primary/60 group-hover:text-primary transition-colors">
                      <?= esc_html($icon) ?>
                    </span>
                  <?php endif; ?>
                  <span class="font-medium"><?= esc_html($item_title) ?></span>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>
    </aside>
    <?php
    return ob_get_clean();
  }

  // ModelRenderable stub
  public function renderFromModel(array $data, int $index): string
  {
    return ''; // Not used
  }
  protected function getModelIndices(array $data): array
  {
    return [];
  }

  private function render_items_recursive(array $items, bool $is_mobile, int $depth = 0)
  {
    foreach ($items as $item):
      $has_children = !empty($item->children);
      $url = $item->url ?? '#';
      $title = $item->title ?? '';

      if ($has_children): ?>
        <li tabindex="0" class="group">
          <?php if ($is_mobile): ?>
            <details>
              <summary><?= esc_html($title) ?></summary>
              <ul>
                <?php $this->render_items_recursive($item->children, true, $depth + 1); ?>
              </ul>
            </details>
          <?php else: ?>
            <?php if ($depth === 0): ?>
              <?php echo \seopress_container()->get(\SeopressComposer\Components\MegaMenu::class)->render($item); ?>
            <?php else: ?>
              <details class="group-hover:open">
                <summary class="group-hover:text-primary transition-colors py-2"><?= esc_html($title) ?></summary>
                <ul class="p-2 bg-white text-base-content z-[100] min-w-[200px] shadow-lg rounded-box mt-2 border border-base-200">
                  <?php $this->render_items_recursive($item->children, false, $depth + 1); ?>
                </ul>
              </details>
            <?php endif; ?>
          <?php endif; ?>
        </li>
      <?php else: ?>
        <li role="none">
          <a href="<?= esc_url($url) ?>" title="<?= esc_attr($title) ?> - Jetzt informieren"
            class="<?= $is_mobile ? '' : 'hover:text-primary hover:bg-transparent focus:text-primary focus:bg-transparent transition-colors py-2' ?>"
            role="menuitem">
            <?= esc_html($title) ?>
          </a>
        </li>
<?php endif;
    endforeach;
  }
}
