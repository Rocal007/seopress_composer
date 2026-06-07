<?php

/**
 * Template part for displaying the main header with logo
 */

// Get Services
$components = \seopress_components();
$models = \seopress_models();

// Get Data
$logoData = $models->getLogoData();
$menuModel = $models->getMenuModel();
// $menuData = $menuModel->get_menu_data(); // REMOVED monolithic call
$menuItems = $menuModel->get_primary_menu_items();

// Direct fetch
$districtsMenu = $menuModel->get_districts_list();
$serviceCategoriesMenu = $menuModel->get_service_categories();

// Render Header & Menu (Logo is injected)
$logoHtml = $components->getLogo()->renderNavbarBrand($logoData);

// Create Icons
$searchHtml = $components->getHeaderSearch()->render();
$iconService = \seopress_container()->get(\SeopressComposer\Services\IconService::class);
$menuIconHtml = $iconService->getIcon('menu_burger', ['inner_class' => 'illu text-neutral-800']);

// Individual components for the end section
$kontaktBtnHtml = '<a class="btn btn-primary btn-sm px-5 shadow-sm hover:shadow-md text-white font-bold hidden lg:flex" href="/kontakt" title="Jetzt kostenlosen Beratungstermin vereinbaren">Kontakt</a>';

/**
 * Helper to render menu items recursively
 * Copied from HeaderMenu component
 */
$render_menu_items = function ($items, $is_mobile = false) use (&$render_menu_items) {
  if (empty($items)) return;

  foreach ($items as $item):
    $has_children = !empty($item->children);
    $url = $item->url ?? '#';
    $title = $item->title ?? '';

    // Skip the generic 'Unsere Leistungen' from WP Menu in the hamburger since we already injected the custom loop at the top
    if ($is_mobile && $title === 'Unsere Leistungen') {
        continue;
    }

    // Data from Menu_Data model
    $image_url = $item->image_url ?? '';
    $content = $item->content ?? ''; // Overview text

    // Icon handling using Service
    $icon_html = '';
    if (!empty($item->icon)) {
      $icon_service = \seopress_container()->get(\SeopressComposer\Services\IconService::class);
      $icon_html = $icon_service->getIconHtml($item->icon, $title);
    }

    if ($has_children):
?>
      <li tabindex="0" class="group h-full">
        <?php if ($is_mobile): ?>
          <?php if ($title === 'Unsere Leistungen'): ?>
            <div class="w-full py-4">
              <div class="flex items-center gap-2 font-bold text-xl text-primary mb-6 pb-4 border-b-2 border-base-200">
                <?php if ($icon_html) echo $icon_html; ?>
                <?php echo esc_html($title); ?>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <?php foreach ($item->children as $child): 
                  $c_icon = '';
                  if (!empty($child->icon)) {
                    $c_icon = \seopress_container()->get(\SeopressComposer\Services\IconService::class)->getIconHtml($child->icon, $child->title, ['size' => 'md']);
                  }
                ?>
                  <a href="<?= esc_url($child->url ?? '#') ?>" class="group/card flex flex-col items-center justify-center p-4 bg-base-100 border border-base-200 rounded-xl hover:border-primary hover:shadow-lg transition-all duration-300 text-center gap-3">
                    <?php if ($c_icon): ?>
                      <div class="text-primary group-hover/card:scale-110 transition-transform">
                        <?= $c_icon ?>
                      </div>
                    <?php endif; ?>
                    <span class="font-bold text-sm md:text-base group-hover/card:text-primary transition-colors"><?= esc_html($child->title) ?></span>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          <?php else: ?>
          <?php if ($title === 'Rechtliches'): ?>
            <div class="w-full mt-2 mb-2">
              <div class="text-xs font-bold uppercase tracking-widest text-base-content/40 px-1 mb-3">
                <?php echo esc_html($title); ?>
              </div>
              <ul class="flex flex-col gap-1">
                <?php $render_menu_items($item->children, true); ?>
              </ul>
            </div>
          <?php else: ?>
            <details class="w-full">
              <summary class="justify-between flex items-center gap-2" aria-haspopup="true">
                <span class="flex items-center gap-2">
                  <?php if ($icon_html): ?>
                    <?php echo $icon_html; ?>
                  <?php endif; ?>
                  <?php echo esc_html($title); ?>
                </span>
              </summary>
              <ul class="p-2 bg-base-100">
                <?php $render_menu_items($item->children, true); ?>
              </ul>
            </details>
          <?php endif; ?>
          <?php endif; ?>
        <?php else: ?>
          <?php if ($title === 'Unsere Leistungen'): ?>
            <!-- Desktop Mega Menu for Services -->
            <?php
            echo \seopress_container()->get(\SeopressComposer\Components\MegaMenu::class)->render($item);
            ?>
          <?php else: ?>
            <!-- Standard Desktop Dropdown -->
            <details class="group-hover:open h-full flex items-center relative">
              <summary class="group-hover:text-primary transition-colors py-4 flex items-center gap-2 h-full cursor-pointer select-none"
                aria-haspopup="true">
                <?php if ($icon_html): ?>
                  <?php echo $icon_html; ?>
                <?php endif; ?>
                <?php echo esc_html($title); ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none"
                  class="stroke-current opacity-75 group-open:rotate-180 transition-transform" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </summary>
              <ul class="p-4 bg-base-100 text-base-content shadow-2xl rounded-xl absolute top-full left-0 min-w-[250px] border border-base-200 z-[100] grid gap-2">
                <?php foreach ($item->children as $child): 
                  $c_icon = '';
                  if (!empty($child->icon)) {
                    $c_icon = \seopress_container()->get(\SeopressComposer\Services\IconService::class)->getIconHtml($child->icon, $child->title, ['size' => 'sm']);
                  }
                ?>
                  <li>
                    <a href="<?= esc_url($child->url ?? '#') ?>" class="flex items-center gap-3 p-3 hover:bg-base-200 rounded-lg hover:text-primary transition-colors">
                      <?php if ($c_icon) echo $c_icon; ?>
                      <?= esc_html($child->title) ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </details>
          <?php endif; ?>
        <?php endif; ?>
      </li>
    <?php else: ?>
      <li class="h-full" role="none">
        <a href="<?php echo esc_url($url); ?>" title="<?php echo esc_attr($title); ?> - Hier informieren"
          class="<?php echo $is_mobile ? 'w-full' : 'hover:text-primary hover:bg-transparent focus:text-primary focus:bg-transparent transition-colors py-4 flex items-center gap-2 h-full'; ?>"
          role="menuitem">
          <?php if ($icon_html): ?>
            <?php echo $icon_html; ?>
          <?php endif; ?>
          <?php echo esc_html($title); ?>
        </a>
      </li>
    <?php endif; ?>
<?php endforeach;
};
?>

<!-- Navbar with Light Theme -->
<nav id="seopress-composer-header" class="navbar bg-white shadow-md text-base-content lg:sticky lg:top-0 z-[1000] py-4 transition-transform duration-300" aria-label="Hauptnavigation">
  <div class="w-full flex items-center justify-between px-4 lg:px-8">

    <!-- Start: Logo -->
    <div class="navbar-start flex-1 flex items-center">
      <!-- Logo Component Injection -->
      <?php echo $logoHtml; ?>
    </div>

    <!-- Center: Desktop Menu (Hidden) -->
    <div class="navbar-center hidden">
    </div>

    <!-- End: CTA & Mobile Menu -->
    <div class="navbar-end flex-none flex justify-end items-center gap-3">

      <!-- Kontakt Button -->
      <?php echo $kontaktBtnHtml; ?>

      <div class="hidden lg:block ml-2 mr-0">
        <?php echo $searchHtml; ?>
      </div>

      <!-- Hamburger (Visible Always on Desktop, Hidden on Mobile where Topbar has Hamburger) -->
      <div class="dropdown static hidden lg:block">
        <label tabindex="0" class="btn btn-ghost btn-circle btn-sm" role="button"
               aria-label="Hauptmenü öffnen" aria-expanded="false" aria-controls="main-navigation-menu"
               id="hamburger-btn"
               onclick="this.setAttribute('aria-expanded', this.getAttribute('aria-expanded')==='true' ? 'false' : 'true')">
          <div class="h-6 w-6 flex items-center justify-center">
            <?php echo $menuIconHtml; ?>
          </div>
          <span class="sr-only">Menü</span>
        </label>
      <div id="main-navigation-menu" tabindex="0" role="menu" aria-label="Hauptnavigation"
          class="dropdown-content z-[1] p-6 lg:p-12 shadow-2xl bg-base-100 text-base-content w-full left-0 top-full mt-0 border-t border-base-200 rounded-none overflow-y-auto max-h-[80vh]">
          
          <div class="w-full px-4 lg:px-8 grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Spalte 1 & 2: Leistungen -->
            <div class="w-full lg:col-span-2">
              <div class="text-xs font-bold uppercase tracking-widest text-base-content/40 px-1 mb-4 border-b border-base-200 pb-2">
                Unsere Leistungen
              </div>
              <ul class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1">
                <?php
                $service_pages = get_pages([
                  'meta_key' => '_wp_page_template',
                  'meta_value' => 'template-hauptseiten.php',
                  'sort_column' => 'menu_order, post_title',
                ]);
                
                if (!empty($service_pages)):
                  foreach ($service_pages as $page):
                    $districts = get_the_terms($page->ID, 'district');
                    if (!empty($districts) && !is_wp_error($districts)) {
                        continue;
                    }
                    $icon_url = '';
                    $remote_data = get_post_meta($page->ID, 'remote_page_data', true);
                    if (is_string($remote_data)) $remote_data = json_decode($remote_data);
                    
                    if ($remote_data && !empty($remote_data->acf->hauptseiten_icon)) {
                        $raw = $remote_data->acf->hauptseiten_icon;
                        if (is_string($raw)) $icon_url = $raw;
                        elseif (is_object($raw) && !empty($raw->url)) $icon_url = $raw->url;
                        elseif (is_array($raw) && !empty($raw['url'])) $icon_url = $raw['url'];
                    }
                    if (empty($icon_url)) {
                        $icon_raw = function_exists('get_field') ? get_field('hauptseiten_icon', $page->ID) : '';
                        if (is_array($icon_raw) && !empty($icon_raw['url'])) $icon_url = $icon_raw['url'];
                        elseif (is_numeric($icon_raw) && $icon_raw > 0) $icon_url = wp_get_attachment_image_url($icon_raw, 'thumbnail') ?: '';
                        elseif (is_string($icon_raw) && !empty($icon_raw)) $icon_url = $icon_raw;
                    }

                    // Convert and cache icon locally as WebP
                    if (!empty($icon_url)) {
                        $clean_title = wp_strip_all_tags(\seopress_container()->get(\SeopressComposer\Services\TextReplacementService::class)->clean_title($page->post_title));
                        $icon_url = \seopress_container()->get(\SeopressComposer\Services\ImageDownloadService::class)->get_local_image_url($icon_url, $clean_title . ' icon', $clean_title . ' icon');
                    }
                  ?>
                    <li>
                      <a href="<?= get_permalink($page->ID) ?>"
                         title="<?= esc_attr($page->post_title) ?>"
                         class="group flex items-center gap-3 p-2 hover:bg-primary/5 rounded-lg transition-colors">
                        <?php if ($icon_url): ?>
                          <div class="w-6 h-6 flex-shrink-0 flex items-center justify-center bg-base-200 rounded group-hover:bg-primary/10 transition-colors">
                            <img src="<?= esc_url($icon_url) ?>" alt="" class="w-4 h-4 object-contain filter group-hover:brightness-90 transition-all" aria-hidden="true">
                          </div>
                        <?php endif; ?>
                        <span class="font-bold text-sm text-base-content group-hover:text-primary transition-colors">
                          <?= esc_html(\seopress_container()->get(\SeopressComposer\Services\TextReplacementService::class)->clean_title($page->post_title)) ?>
                        </span>
                      </a>
                    </li>
                  <?php endforeach; ?>
                <?php else:
                  $iconService = \seopress_container()->get(\SeopressComposer\Services\IconService::class);
                  foreach ($serviceCategoriesMenu as $cat):
                    $catName  = is_array($cat) ? ($cat['name'] ?? '') : ($cat->name ?? '');
                    $catLink  = is_array($cat) ? ($cat['link'] ?? ($cat['url'] ?? '#')) : ($cat->url ?? '#');
                    $catIcon  = is_array($cat) ? ($cat['icon'] ?? '') : ($cat->icon ?? '');
                    $catSlug  = is_array($cat) ? ($cat['slug'] ?? '') : ($cat->slug ?? '');

                    if (empty($catLink) || $catLink === '#') {
                      $term = get_term_by('slug', $catSlug, 'service_category');
                      $catLink = ($term && !is_wp_error($term)) ? get_term_link($term) : '#';
                    }
                    $iconHtml = $catIcon ? $iconService->getIconHtml($catIcon, $catName, ['size' => 'sm', 'primary_background' => true, 'invert' => true]) : '';
                  ?>
                    <li>
                      <a href="<?= esc_url($catLink) ?>"
                         title="<?= esc_attr($catName) ?>"
                         class="group flex items-center gap-3 p-2 hover:bg-primary/5 rounded-lg transition-colors">
                        <?php if ($iconHtml): ?>
                          <div class="w-6 h-6 flex-shrink-0 flex items-center justify-center bg-base-200 rounded group-hover:bg-primary/10 transition-colors">
                            <?= $iconHtml ?>
                          </div>
                        <?php endif; ?>
                        <span class="font-bold text-sm text-base-content group-hover:text-primary transition-colors">
                          <?= esc_html(\seopress_container()->get(\SeopressComposer\Services\TextReplacementService::class)->clean_title($catName)) ?>
                        </span>
                      </a>
                    </li>
                  <?php endforeach; ?>
                <?php endif; ?>
              </ul>
            </div>

            <!-- Spalte 3: Kontakt -->
            <div class="w-full">
              <div class="text-xs font-bold uppercase tracking-widest text-base-content/40 px-1 mb-4 border-b border-base-200 pb-2">
                Kontakt
              </div>
              <div class="flex flex-col gap-3 px-1 mt-2">
                <?php
                  $contactDataObj = \seopress_container()->get(\SeopressComposer\Models\Contact_Data::class)->get_data();
                  $cLinks = $contactDataObj['contact_links'] ?? [];
                  
                  foreach ($cLinks as $index => $link) {
                     $btnClass = 'btn w-full justify-start gap-3 border-none shadow-sm';
                     if ($link['type'] === 'phone') $btnClass .= ' bg-primary text-white hover:bg-primary-focus';
                     else if ($link['type'] === 'whatsapp') $btnClass .= ' bg-[#25D366] text-white hover:bg-[#20bd5a]';
                     else $btnClass .= ' bg-base-200 text-base-content hover:bg-base-300';
                     
                     $iHtml = '';
                     if ($link['type'] === 'phone') $iHtml = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>';
                     else if ($link['type'] === 'email') $iHtml = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>';
                     else if ($link['type'] === 'form') $iHtml = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>';
                     else if ($link['type'] === 'whatsapp') $iHtml = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>';
                     
                     // Keep placeholders replaced if they exist
                     $title = $link['title']; // Simple representation
                     ?>
                     <a href="<?= esc_url($link['href']) ?>" class="<?= $btnClass ?>" title="<?= esc_attr($title) ?>">
                       <?= $iHtml ?>
                       <?= esc_html($link['text']) ?>
                     </a>
                 <?php } ?>
              </div>
            </div>

            <!-- Spalte 4: Rechtliches -->
            <div class="w-full">
              <div class="text-xs font-bold uppercase tracking-widest text-base-content/40 px-1 mb-4 border-b border-base-200 pb-2">
                Rechtliches
              </div>
              <ul class="menu p-0">
                <li><a href="<?= esc_url(home_url('/impressum')) ?>" class="hover:text-primary">Impressum</a></li>
                <li><a href="<?= esc_url(home_url('/datenschutzerklaerung')) ?>" class="hover:text-primary">Datenschutz</a></li>
                <li><a href="<?= esc_url(home_url('/agb')) ?>" class="hover:text-primary">AGB</a></li>
              </ul>
            </div>
          </div>
      </div>
      </div>

      <!-- Share Buttons (Header Version) -->
      <div class="hidden lg:block">
        <?php echo $components->getShareButtons()->render(); ?>
      </div>



    </div>

  </div>
</nav>