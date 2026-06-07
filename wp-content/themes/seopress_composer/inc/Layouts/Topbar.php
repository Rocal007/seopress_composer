<?php

namespace SeopressComposer\Layouts;

use SeopressComposer\Services\IconService;

class Topbar
{
  private IconService $iconService;

  public function __construct(IconService $iconService)
  {
    $this->iconService = $iconService;
  }

  public function render(array $data): string
  {
    $links = $data['links'] ?? [];
    $site_name = $data['site_name'] ?? get_bloginfo('name');
    $home_url = $data['home_url'] ?? home_url('/');
    $location = $data['location'] ?? '';
    $branche = $data['branche'] ?? 'Experten für Entrümpelung & Räumung';

    $is_front_page = $data['is_front_page'] ?? false;
    $heading_tag = $is_front_page ? 'h1' : 'div';

    ob_start();
?>
    <!-- Mobile Topbar -->
    <div
      class="lg:hidden bg-primary bg-gradient-to-r from-primary to-primary-focus text-white sticky top-0 z-[1001] shadow-md transition duration-300">
      <div class="flex items-center justify-between px-4 py-2.5">
        <!-- Left: Menu Toggle -->
        <div class="flex items-center space-x-3">
          <button id="mobile-menu-toggle" class="text-white hover:text-white focus:outline-none" aria-label="Toggle menu">
            <svg class="w-7 h-7 filter drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          <a href="<?= esc_url($home_url) ?>" class="flex flex-col group">
            <<?= $heading_tag ?> class="text-lg font-bold leading-tight tracking-tight group-hover:text-secondary
              transition-colors duration-300">
              <?= esc_html($site_name) ?>
            </<?= $heading_tag ?>>
          </a>
        </div>


      </div>

      <!-- Mobile Side Drawer Content -->
      <div id="mobile-menu" class="fixed inset-0 z-[2000] hidden">
        <!-- Backdrop -->
        <div id="mobile-menu-backdrop"
          class="absolute inset-0 bg-black/50 backdrop-blur-sm opacity-0 transition-opacity duration-300"></div>

        <!-- Drawer -->
        <div id="mobile-menu-drawer"
          class="absolute top-0 left-0 w-80 h-full bg-white shadow-2xl transform -translate-x-full transition-transform duration-300 flex flex-col">
          <!-- Drawer Header -->
          <div class="p-5 border-b flex items-center justify-between bg-primary text-white shrink-0">
            <div class="flex flex-col">
              <span class="font-bold text-lg tracking-tight"><?= esc_html($site_name) ?></span>
              <span class="text-xs opacity-80 mt-1"><?= esc_html($branche) ?> | <?= esc_html($location) ?></span>
            </div>
            <button id="mobile-menu-close" class="p-2 hover:bg-white/10 rounded-full transition-colors text-white"
              aria-label="Close menu">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>


          <!-- Main Services Links -->
          <?php if (!empty($data['services'])): ?>
            <nav aria-label="Mobilnavigation" class="mb-8">
              <h3 class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-400 mb-4 px-2">Dienstleistungen</h3>
              <ul class="space-y-1">
                <?php foreach ($data['services'] as $service): ?>
                  <li>
                    <a href="<?= esc_url($service->url) ?>"
                      class="flex items-center gap-4 p-2.5 rounded-xl hover:bg-slate-50 transition-all group">
                      <div
                        class="w-10 h-10 bg-slate-100 text-primary rounded-xl flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-all shrink-0">
                        <div class="w-5 h-5 flex items-center justify-center">
                          <?php
                          $iconVal = !empty($service->icon) ? $service->icon : 'arrow_icon';
                          echo $this->iconService->getIconHtml($iconVal, '', [
                            'inner_class' => 'fill-current',
                            'primary_background' => true,
                            'size' => false, // We control size via wrapper
                            'wrapper_class' => 'w-5 h-5'
                          ]);
                          ?>
                        </div>
                      </div>
                      <span
                        class="font-bold text-slate-700 group-hover:text-primary transition-colors"><?= esc_html($service->title) ?></span>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </nav>
          <?php endif; ?>

          <!-- Service Categories (from Footer Logic) -->
          <?php if (!empty($data['service_categories'])): ?>
            <nav aria-label="Service Kategorien" class="mb-8">
              <h3 class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-400 mb-4 px-2">Alle Kategorien</h3>
              <ul class="space-y-1">
                <?php foreach ($data['service_categories'] as $catItem):
                  $service = (object)$catItem;
                ?>
                  <li>
                    <a href="<?= esc_url($service->url) ?>"
                      class="flex items-center gap-4 p-2.5 rounded-xl hover:bg-slate-50 transition-all group">
                      <div
                        class="w-10 h-10 bg-slate-100 text-primary rounded-xl flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-all shrink-0">
                        <div class="w-5 h-5 flex items-center justify-center">
                          <?php
                          $iconVal = !empty($service->icon) ? $service->icon : 'arrow_icon';
                          echo $this->iconService->getIconHtml($iconVal, '', [
                            'inner_class' => 'fill-current',
                            'primary_background' => true,
                            'size' => false,
                            'wrapper_class' => 'w-5 h-5'
                          ]);
                          ?>
                        </div>
                      </div>
                      <span
                        class="font-bold text-slate-700 group-hover:text-primary transition-colors"><?= esc_html($service->title) ?></span>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </nav>
          <?php endif; ?>

          <!-- Contact Quick Links -->
          <div class="mb-8">
            <h3 class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-400 mb-4 px-2">Direkt-Kontakt</h3>
            <div class="space-y-3 px-1">
              <?php if (!empty($links['phone'])): ?>
                <a href="<?= esc_url($links['phone']['href']) ?>"
                  class="flex items-center gap-4 p-4 rounded-2xl bg-primary text-white shadow-md shadow-primary/20 font-bold hover:scale-[1.02] active:scale-95 transition-all">
                  <div class="w-6 h-6 shrink-0">
                    <?= $this->iconService->getIcon('telefon', ['inner_class' => 'fill-current']) ?>
                  </div>
                  <div class="flex flex-col">
                    <span class="text-[10px] uppercase tracking-wider opacity-80">Rufen Sie uns an</span>
                    <span class="text-base"><?= esc_html($links['phone']['text']) ?></span>
                  </div>
                </a>
              <?php endif; ?>
              <?php if (!empty($links['whatsapp'])): ?>
                <a href="<?= esc_url($links['whatsapp']['href']) ?>"
                  class="flex items-center gap-4 p-4 rounded-2xl bg-success text-white shadow-md shadow-success/20 font-bold hover:scale-[1.02] active:scale-95 transition-all">
                  <div class="w-6 h-6 shrink-0">
                    <?= $this->iconService->getIcon('whatsapp', ['inner_class' => 'fill-current']) ?>
                  </div>
                  <div class="flex flex-col">
                    <span class="text-[10px] uppercase tracking-wider opacity-80">Schreiben Sie uns</span>
                    <span class="text-base italic">WhatsApp Chat</span>
                  </div>
                </a>
              <?php endif; ?>
              <?php if (!empty($links['contact'])): ?>
                <a href="<?= esc_url($links['contact']['href']) ?>"
                  class="flex items-center gap-4 p-4 rounded-2xl bg-secondary text-white shadow-md shadow-secondary/20 font-bold hover:scale-[1.02] active:scale-95 transition-all">
                  <div class="w-6 h-6 shrink-0">
                    <?= $this->iconService->getIcon('email', ['inner_class' => 'fill-current']) ?>
                  </div>
                  <div class="flex flex-col">
                    <span class="text-[10px] uppercase tracking-wider opacity-80">Schriftlich Anfragen</span>
                    <span class="text-base">Kontaktformular</span>
                  </div>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Drawer Footer (Legal) -->
        <div class="p-6 border-t bg-slate-50 shrink-0">
          <nav aria-label="Rechtliche Links"
            class="flex flex-wrap gap-x-6 gap-y-3 justify-center text-xs font-bold text-slate-400 uppercase tracking-wider">
            <?php foreach ($data['legal_links'] ?? [] as $legal): ?>
              <a href="<?= esc_url($legal['url']) ?>" class="hover:text-primary transition-colors">
                <?= esc_html($legal['title']) ?>
              </a>
            <?php endforeach; ?>
          </nav>
        </div>
      </div>
    </div>
    </div>

    <!-- Desktop Topbar -->
    <div class="hidden lg:block bg-primary text-white shadow-md text-sm font-semibold">
      <div class="w-full px-4 lg:px-8">
        <div class="flex items-center justify-between py-2">

          <!-- Left: Slogan -->
          <div class="flex items-center gap-4 opacity-90">
            <span class="tracking-wide"><?= esc_html($branche) ?></span>
            <span class="hidden lg:inline px-2">|</span>
            <span class="hidden lg:inline"><?= esc_html($location) ?> und Umgebung</span>
          </div>

          <!-- Right: Contact Info -->
          <div class="flex items-center gap-6">

            <!-- Email -->
            <?php if (!empty($links['email'])):
              $em = $links['email']; ?>
              <a href="<?= esc_url($em['href']) ?>" class="flex items-center gap-2 hover:opacity-80 transition group"
                title="<?= esc_attr($em['title']) ?>" aria-label="<?= esc_attr($em['aria_label']) ?>"
                rel="<?= esc_attr($em['rel']) ?>">
                <div
                  class="bg-white/10 p-1.5 rounded-full group-hover:bg-white/20 transition w-7 h-7 flex items-center justify-center text-white group-hover:text-white">
                  <div class="w-3.5 h-3.5">
                    <?= $this->iconService->getIcon('email', ['inner_class' => 'fill-current']) ?>
                  </div>
                </div>
                <span><?= esc_html($em['text']) ?></span>
              </a>
            <?php endif; ?>

            <!-- WhatsApp -->
            <?php if (!empty($links['whatsapp'])):
              $wa = $links['whatsapp']; ?>
              <a href="<?= esc_url($wa['href']) ?>" class="flex items-center gap-2 hover:opacity-80 transition group"
                title="<?= esc_attr($wa['title']) ?>" aria-label="<?= esc_attr($wa['aria_label']) ?>"
                target="<?= esc_attr($wa['target']) ?>" rel="<?= esc_attr($wa['rel']) ?>">
                <div
                  class="bg-white/10 p-1.5 rounded-full group-hover:bg-white/20 transition w-7 h-7 flex items-center justify-center text-white group-hover:text-white">
                  <div class="w-3.5 h-3.5">
                    <?= $this->iconService->getIcon('whatsapp', ['inner_class' => 'fill-current']) ?>
                  </div>
                </div>
                <span>WhatsApp</span>
              </a>
            <?php endif; ?>

            <!-- Phone -->
            <?php if (!empty($links['phone'])):
              $ph = $links['phone']; ?>
              <a href="<?= esc_url($ph['href']) ?>" class="flex items-center gap-2 hover:opacity-80 transition group"
                title="<?= esc_attr($ph['title']) ?>" aria-label="<?= esc_attr($ph['aria_label']) ?>"
                rel="<?= esc_attr($ph['rel']) ?>">
                <div
                  class="bg-white/10 p-1.5 rounded-full group-hover:bg-white/20 transition w-7 h-7 flex items-center justify-center text-white group-hover:text-white">
                  <div class="w-3.5 h-3.5">
                    <?= $this->iconService->getIcon('telefon', ['inner_class' => 'fill-current']) ?>
                  </div>
                </div>
                <span><?= esc_html($ph['text']) ?></span>
              </a>
            <?php endif; ?>


          </div>
        </div>
      </div>
    </div>

<?php
    return ob_get_clean();
  }
}
