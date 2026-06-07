<?php

/**
 * The main template file
 */

get_header();

$components = seopress_components();
$models = seopress_models();

?>

<main>

  <!-- Hero Section -->
  <?= $components->getHero()->render($models->getTopPictureData()); ?>

  <!-- Breadcrumbs -->
  <?= $components->getBreadcrumb()->render(); ?>

  <!-- Main Text Section -->
  <?= $components->getMainText()->render($models->getMainTextData()); ?>

  <!-- Features Grid -->
  <?php
  $vorteileData = $models->getVorteileData();
  if (!empty($vorteileData)) {
  ?>
    <div class="container mx-auto px-4 py-8">
      <div class="mb-12">
        <h2 class="text-3xl font-bold mb-6 text-center text-primary"><?= $vorteileData['heading'] ?? 'Unsere Vorteile' ?></h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <?= $components->getTooltipCard()->renderManyFromModel($vorteileData); ?>
        </div>
      </div>
    </div>
  <?php
  <!-- FAQ Section -->
  <div class="container mx-auto px-4 py-8">
    <?= $components->getAccordion()->render($models->getFaqsData()); ?>
  </div>

</main>

<?php
get_footer();
