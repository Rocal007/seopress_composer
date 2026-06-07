<?php
namespace SeopressComposer\Components;

/**
 * Logo Component - Displays site logo with title and description
 * Enhanced with DaisyUI components
 */
class Logo
{
  /**
   * Render clean logo variant with DaisyUI hero section
   */
  public function renderClean(array $logo = []): string
  {
    if (empty($logo)) {
      return '';
    }

    ob_start();
    ?>
    <header class="hero bg-gradient-to-r from-primary to-secondary py-12"
      aria-label="<?= esc_attr($logo['title'] ?? '') ?>">
      <div class="hero-content container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center gap-8 w-full">

          <!-- Logo Icon with Avatar -->
          <div class="flex-shrink-0">
            <a href="<?= esc_url(home_url('/')) ?>" rel="home noopener" title="<?= esc_attr(get_bloginfo('name')) ?>"
              class="avatar">
              <div role="img" aria-label="<?= esc_attr($logo['title'] . ' icon') ?>"
                class="w-24 h-24 rounded-full bg-base-100 ring ring-base-100 ring-offset-base-100 ring-offset-2 hover:ring-accent transition-all duration-300 p-3 flex items-center justify-center">
                <?= $logo['icon_svg'] ?? '' ?>
              </div>
            </a>
          </div>

          <!-- Logo Title with Badge -->
          <div class="flex-1 text-center md:text-left">
            <h1 class="text-base-100">
              <strong class="text-4xl md:text-5xl font-bold block mb-2" style="font-family: var(--font-logo);">
                <?= esc_html($logo['title'] ?? '') ?>
              </strong>
              <?php if (!empty($logo['location'])): ?>
                <span class="badge badge-lg badge-accent gap-2 mt-2"
                  title="<?= esc_attr(sprintf(__('Ihr Profi für Entrümpelung in %s', 'seopress'), $logo['location'])) ?>"
                  aria-label="<?= esc_attr(sprintf(__('Einsatzgebiet: %s', 'seopress'), $logo['location'])) ?>">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    class="inline-block w-4 h-4 stroke-current" role="img" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                  <?= $logo['location'] ?>
                </span>
              <?php endif; ?>
            </h1>
            <?php if (!empty($logo['content'])): ?>
              <p class="text-base-100 mt-4 text-lg md:text-xl opacity-90 max-w-2xl">
                <?= esc_html($logo['content']) ?>
              </p>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </header>
    <?php
    return ob_get_clean();
  }

  /**
   * Render logo inside variant with DaisyUI card
   */
  public function renderInside(array $logo = []): string
  {
    if (empty($logo)) {
      return '';
    }

    ob_start();
    ?>
    <header class="text-center py-12" aria-label="<?= esc_attr($logo['title'] ?? '') ?>">
      <!-- Icon with Avatar -->
      <div class="avatar mb-6">
        <div
          class="w-28 h-28 rounded-full bg-primary ring ring-primary ring-offset-base-100 ring-offset-4 shadow-2xl p-4 flex items-center justify-center">
          <?= $logo['icon_svg'] ?? '' ?>
        </div>
      </div>

      <!-- Title -->
      <h1 class="text-5xl md:text-6xl font-bold text-primary mb-4" style="font-family: var(--font-logo);">
        <?= esc_html($logo['content'] ?? $logo['title'] ?? '') ?>
      </h1>
    </header>
    <?php
    return ob_get_clean();
  }

  /**
   * Render compact logo variant with DaisyUI navbar style
   */
  public function renderCompact(array $logo = []): string
  {
    if (empty($logo)) {
      return '';
    }

    ob_start();
    ?>
    <div class="navbar-start flex items-center gap-4">
      <!-- Icon with Avatar -->
      <div class="avatar">
        <a href="<?= esc_url(home_url('/')) ?>" rel="home" title="<?= esc_attr(get_bloginfo('name')) ?>"
          aria-label="<?= esc_attr(get_bloginfo('name')) ?>"
          class="w-12 h-12 rounded-full bg-primary hover:bg-primary-focus transition-colors">
          <div class="w-full h-full flex items-center justify-center p-2">
            <?= $logo['icon_svg'] ?? '' ?>
          </div>
        </a>
      </div>

      <!-- Title -->
      <div>
        <h2 class="text-xl font-bold text-primary" style="font-family: var(--font-logo);">
          <?= esc_html($logo['title'] ?? '') ?>
          <?php if (!empty($logo['location'])): ?>
            <span class="badge badge-sm badge-secondary ml-2"
              title="<?= esc_attr(sprintf(__('Räumungsfirma in %s', 'seopress'), $logo['location'])) ?>"
              aria-label="<?= esc_attr(sprintf(__('Region: %s', 'seopress'), $logo['location'])) ?>">
              <?= esc_html($logo['location']) ?>
            </span>
          <?php endif; ?>
        </h2>
      </div>
    </div>
    <?php
    return ob_get_clean();
  }

  /**
   * Render as card variant (NEW!)
   */
  public function renderCard(array $logo = []): string
  {
    if (empty($logo)) {
      return '';
    }

    ob_start();
    ?>
    <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow duration-300">
      <div class="card-body items-center text-center">
        <!-- Icon -->
        <div class="avatar mb-4">
          <div
            class="w-20 h-20 rounded-full bg-primary ring ring-primary ring-offset-base-100 ring-offset-2 p-3 flex items-center justify-center">
            <?= $logo['icon_svg'] ?? '' ?>
          </div>
        </div>

        <!-- Title -->
        <h2 class="card-title text-2xl" style="font-family: var(--font-logo);">
          <?= esc_html($logo['title'] ?? '') ?>
        </h2>

        <!-- Location Badge -->
        <?php if (!empty($logo['location'])): ?>
          <div class="badge badge-accent badge-outline"
            title="<?= esc_attr(sprintf(__('Beste Entrümpelung in %s', 'seopress'), $logo['location'])) ?>"
            aria-label="<?= esc_attr(sprintf(__('Einsatzort: %s', 'seopress'), $logo['location'])) ?>">
            <?= $logo['location'] ?>
          </div>
        <?php endif; ?>

        <!-- Content -->
        <?php if (!empty($logo['content'])): ?>
          <p class="text-base-content opacity-70">
            <?= esc_html($logo['content']) ?>
          </p>
        <?php endif; ?>
      </div>
    </div>
    <?php
    return ob_get_clean();
  }

  /**
   * Render specifically for the Navbar (Brand Link)
   * Clean, no location (avoids redundancy with topbar), high aesthetics
   */
  public function renderNavbarBrand(array $logo = []): string
  {
    if (empty($logo)) {
      return '';
    }

    ob_start();
    ?>
    <div class="flex items-center gap-3 px-2 py-1 relative">
      <a href="<?= esc_url(home_url('/')) ?>" rel="home"
        title="<?= esc_attr($logo['title_hover'] ?? $logo['title'] ?? get_bloginfo('name')) ?>"
        class="flex items-center gap-3 group shrink-0"
        aria-label="<?= esc_attr($logo['title_hover'] ?? $logo['title'] ?? get_bloginfo('name')) ?>">

        <?php if (!empty($logo['icon_svg'])): ?>
          <div
            class="w-12 h-12 md:w-16 md:h-16 bg-primary text-primary-content rounded-full flex items-center justify-center ring-4 ring-primary/10 ring-offset-2 ring-offset-base-100 shadow-xl transition-all duration-500 group-hover:ring-primary/20">
            <div class="w-8 h-8 md:w-10 md:h-10 group-hover:scale-110 transition-transform duration-500">
              <?= $logo['icon_svg'] ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Title Container -->
        <div class="flex flex-col justify-center leading-tight z-10 relative">
          <span
            class="text-xl sm:text-2xl md:text-3xl lg:text-4xl tracking-tighter text-secondary group-hover:text-primary transition-all duration-300 whitespace-nowrap lg:pr-12"
            style="font-family: var(--font-logo); font-weight: var(--font-logo-weight, 900);">
            <?= esc_html(trim($logo['title'] ?? get_bloginfo('name'))) ?>
          </span>
        </div>
      </a>

      <!-- Location Overlay Area (Separated to avoid nested <a> tags) -->
      <div class="absolute inset-0 pointer-events-none flex items-center justify-end z-20">
        <div class="relative h-full w-full max-w-[280px]">
          <?php if (!empty($logo['location_image'])): ?>
            <sup
              class="flex absolute -top-1 -right-2 md:-right-6 w-9 h-9 md:w-12 md:h-12 items-center justify-center pointer-events-auto opacity-95 hover:opacity-100 transition-all duration-300">
              <?php if (!empty($logo['location_link'])): ?>
                <a href="<?= esc_url($logo['location_link']) ?>"
                  title="<?= esc_attr($logo['location_seo_title'] ?: $logo['location']) ?>"
                  aria-label="<?= esc_attr(sprintf(__('Alle Informationen zu %s', 'seopress'), $logo['location'])) ?>"
                  class="w-full h-full block hover:scale-110 transition-transform duration-300">
                <?php endif; ?>

                <?php
                $ext = pathinfo($logo['location_image'], PATHINFO_EXTENSION);
                if ($ext === 'svg'):
                  // Convert URL to local path more robustly
                  $site_url = untrailingslashit(get_site_url());
                  $abs_path = untrailingslashit(ABSPATH);
                  $image_path = str_replace($site_url, $abs_path, $logo['location_image']);
                  $image_path = wp_normalize_path($image_path);

                  if (file_exists($image_path)) {
                    // Output inline SVG with accessibility attributes
                    $svg_content = file_get_contents($image_path);
                    echo str_replace('<svg', '<svg class="w-full h-full object-contain" role="img" aria-label="' . esc_attr($logo['location_seo_title'] ?: $logo['location']) . '"', $svg_content);
                  } else {
                    echo '<img src="' . esc_url($logo['location_image']) . '" alt="' . esc_attr($logo['location_seo_title'] ?: $logo['location']) . '" title="' . esc_attr($logo['location_seo_title'] ?: $logo['location']) . '" class="w-full h-full object-contain" />';
                  }
                else: ?>
                  <img src="<?= esc_url($logo['location_image']) ?>"
                    alt="<?= esc_attr($logo['location_seo_title'] ?: $logo['location']) ?>"
                    title="<?= esc_attr($logo['location_seo_title'] ?: $logo['location']) ?>"
                    class="w-full h-full object-contain" />
                <?php endif; ?>

                <?php if (!empty($logo['location'])): ?>
                  <span
                    class="absolute inset-0 flex items-center justify-center text-[0.7rem] md:text-[0.85rem] font-light text-base-100 z-10 pointer-events-none tracking-wide text-center px-1 leading-none"
                    style="text-shadow: 0 1px 3px rgba(0,0,0,0.9);">
                    <?= esc_html($logo['location']) ?>
                  </span>
                <?php endif; ?>

                <?php if (!empty($logo['location_link'])): ?>
                </a>
              <?php endif; ?>
            </sup>
          <?php elseif (!empty($logo['location'])): ?>
            <sup
              class="hidden xl:block absolute top-1 -right-2 md:-right-4 text-sm md:text-base font-bold text-primary opacity-90 pointer-events-auto">
              <?php if (!empty($logo['location_link'])): ?>
                <a href="<?= esc_url($logo['location_link']) ?>" class="hover:underline"
                  title="<?= esc_attr($logo['location_seo_title'] ?: $logo['location']) ?>"
                  aria-label="<?= esc_attr(sprintf(__('Mehr über %s erfahren', 'seopress'), $logo['location'])) ?>">
                  <?= esc_html($logo['location']) ?>
                </a>
              <?php else: ?>
                <?= esc_html($logo['location']) ?>
              <?php endif; ?>
            </sup>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php
    return ob_get_clean();
  }

  /**
   * Default render method (uses clean variant)
   */
  public function render(array $logo = []): string
  {
    return $this->renderClean($logo);
  }
}
