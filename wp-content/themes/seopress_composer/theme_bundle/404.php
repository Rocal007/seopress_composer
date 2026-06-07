<?php

/**
 * The template for displaying 404 pages (not found)
 */

get_header();

// Get Services
$components = seopress_components();
$models = seopress_models();
$layouts = seopress_layouts();
?>

<main id="main-content" role="main">
  <!-- Hero Section for 404 -->
  <div class="bg-gradient-to-br from-base-200 to-base-300 py-20">
    <div class="container mx-auto px-4 text-center">
      <div class="max-w-3xl mx-auto">
        <!-- Large 404 Number -->
        <div class="text-9xl font-bold text-primary/20 mb-4">404</div>

        <!-- Error Message -->
        <h1 class="text-4xl md:text-5xl font-bold text-base-content mb-6">
          Seite nicht gefunden
        </h1>

        <p class="text-xl text-base-content/70 mb-8">
          Die von Ihnen gesuchte Seite existiert leider nicht oder wurde verschoben.
        </p>

        <!-- Search Form -->
        <div class="max-w-2xl mx-auto mb-8">
          <form role="search" method="get" class="search-form" action="<?= esc_url(home_url('/')) ?>">
            <div class="relative">
              <input
                type="search"
                class="input input-bordered w-full pr-24 text-lg py-6"
                placeholder="Wonach suchen Sie?"
                value="<?= get_search_query() ?>"
                name="s"
                aria-label="Suche" />
              <button
                type="submit"
                class="btn btn-primary absolute right-2 top-1/2 -translate-y-1/2">
                <?= seopress_container()->get(\SeopressComposer\Services\IconService::class)->getIcon('suche', ['inner_class' => 'fill-current']) ?>
                <span class="ml-2">Suchen</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Quick Links -->
        <div class="flex flex-wrap justify-center gap-4">
          <a href="<?= esc_url(home_url('/')) ?>" class="btn btn-primary btn-lg">
            <?= seopress_container()->get(\SeopressComposer\Services\IconService::class)->getIcon('home', ['inner_class' => 'fill-current']) ?>
            <span class="ml-2">Zur Startseite</span>
          </a>
          <a href="/kontakt" class="btn btn-outline btn-lg">
            <?= seopress_container()->get(\SeopressComposer\Services\IconService::class)->getIcon('telefon', ['inner_class' => 'fill-current']) ?>
            <span class="ml-2">Kontakt</span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Popular Services Section -->
  <?php
  $menuModel = $models->getMenuModel();
  $services = $menuModel->get_service_categories();

  if (!empty($services)):
  ?>
    <div class="py-16 bg-base-100">
      <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Unsere Dienstleistungen</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php foreach (array_slice($services, 0, 6) as $service): ?>
            <a
              href="<?= esc_url($service['link']) ?>"
              class="card bg-base-200 hover:bg-base-300 transition-all hover:shadow-xl group">
              <div class="card-body">
                <div class="flex items-center gap-4">
                  <?php if (!empty($service['image_url'])): ?>
                    <img
                      src="<?= esc_url($service['image_url']) ?>"
                      alt="<?= esc_attr($service['title']) ?>"
                      class="w-12 h-12 object-contain" />
                  <?php else: ?>
                    <div class="w-12 h-12 flex items-center justify-center text-primary">
                      <?= seopress_container()->get(\SeopressComposer\Services\IconService::class)->getIcon('default', ['inner_class' => 'fill-current']) ?>
                    </div>
                  <?php endif; ?>
                  <h3 class="card-title text-lg group-hover:text-primary transition-colors">
                    <?= esc_html($service['title']) ?>
                  </h3>
                </div>
                <?php if (!empty($service['overview_text'])): ?>
                  <p class="text-sm text-base-content/70 line-clamp-2">
                    <?= esc_html($service['overview_text']) ?>
                  </p>
                <?php endif; ?>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Districts Section -->
  <?php
  $districts = $menuModel->get_districts_list();

  if (!empty($districts)):
  ?>
    <div class="py-16 bg-base-200">
      <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Unsere Einsatzgebiete</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          <?php foreach (array_slice($districts, 0, 12) as $district): ?>
            <a
              href="<?= esc_url($district['link']) ?>"
              class="btn btn-outline hover:btn-primary transition-all">
              <?= esc_html($district['title']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Help Section -->
  <div class="py-16 bg-base-100">
    <div class="container mx-auto px-4">
      <div class="max-w-3xl mx-auto text-center">
        <h2 class="text-3xl font-bold mb-6">Brauchen Sie Hilfe?</h2>
        <p class="text-lg text-base-content/70 mb-8">
          Unser Team steht Ihnen gerne zur Verfügung. Kontaktieren Sie uns telefonisch oder per E-Mail.
        </p>

        <?php
        $pageHelper = seopress_container()->get(\SeopressComposer\Helpers\PageHelper::class);
        $business = $pageHelper->get_options_business();
        $phone = $business['telefonnummer'] ?? '+43 676 681 20 90';
        $email = $business['e-mail'] ?? $business['email'] ?? 'office@example.com';
        ?>

        <div class="flex flex-wrap justify-center gap-4">
          <a href="tel:<?= esc_attr($phone) ?>" class="btn btn-primary btn-lg gap-2">
            <?= seopress_container()->get(\SeopressComposer\Services\IconService::class)->getIcon('telefon', ['inner_class' => 'fill-current']) ?>
            <?= esc_html($phone) ?>
          </a>
          <a href="mailto:<?= esc_attr($email) ?>" class="btn btn-outline btn-lg gap-2">
            <?= seopress_container()->get(\SeopressComposer\Services\IconService::class)->getIcon('email', ['inner_class' => 'fill-current']) ?>
            <?= esc_html($email) ?>
          </a>
        </div>
      </div>
    </div>
  </div>

</main>

<?php
get_footer();
