<?php
/**
 * Content part for Locations Template
 */

$components = seopress_components();
$models = seopress_models();
$layouts = seopress_layouts();

// Get Data
$topPictureData = $models->getTopPictureData();
$districtsData = $models->getDistrictsData();
?>

<main>
  <!-- Hero Section -->
  <?= $components->getHero()->render($topPictureData); ?>

  <!-- Breadcrumbs -->
  <?= $components->getBreadcrumb()->render(); ?>

  <!-- Locations Grid (Districts) -->
  <section class="py-24 lg:py-32 bg-base-100" id="standorte">
    <div class="container mx-auto px-4">
      <div class="text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-bold text-primary mb-4">Unsere Einsatzgebiete</h2>
      </div>

      <?= $components->getDistricts()->renderGrid($districtsData); ?>
    </div>
  </section>

</main>