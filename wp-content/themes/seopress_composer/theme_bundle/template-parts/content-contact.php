<?php

/**
 * Content part for displaying the contact page
 */
$components = seopress_components();
?>

<!-- Breadcrumbs -->
<?= $components->getBreadcrumb()->render(); ?>

<main id="main-content" role="main">
<section class="py-24 lg:py-32 bg-base-100">
  <div class="container mx-auto px-4">
    <div class="text-center mb-12">
      <h1 class="text-4xl font-bold mb-4">Kontaktieren Sie uns</h1>
      <p class="text-lg text-gray-600">Füllen Sie das Formular aus oder rufen Sie uns an.</p>
    </div>

    <!-- Multi Step Form Component -->
    <?= $components->getMultiStepForm()->render(); ?>

    <div class="mt-16">
      <?php
      // Get contact data and render ContactFooter component
      $container = seopress_container();
      $contactDataModel = $container->get(\SeopressComposer\Models\Contact_Data::class);
      $contactData = $contactDataModel->get_data();

      if (!empty($contactData)) {
        echo $components->getContactFooter()->render($contactData);
      }
      ?>
    </div>
  </div>
</section>