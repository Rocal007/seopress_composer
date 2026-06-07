<?php
/**
 * Generic Page Template — Best of Composer + Kopie
 */

$components  = seopress_components();
$models      = seopress_models();
$layouts     = seopress_layouts();
$subtextData = $models->getSubtextData();

?>

<!-- 1. HERO -->
<?= $components->getHero()->render($models->getTopPictureData()); ?>

<!-- 2. BREADCRUMB -->
<?= $components->getBreadcrumb()->render(); ?>

<!-- 3. PAGE CONTENT -->
<section class="py-12 lg:py-24 bg-base-100">
  <div class="container mx-auto px-4 max-w-4xl">
    <div class="prose prose-lg mx-auto w-full max-w-none bg-white p-8 md:p-12 rounded-2xl shadow-sm border border-base-200">
      <?php
      if (have_posts()) :
        while (have_posts()) :
          the_post();
          the_content();
        endwhile;
      endif;
      ?>
    </div>
  </div>
</section>

<!-- 4. CONTACT INFO (Always helpful at the bottom of standard pages) -->
<div class="bg-base-200 pt-16 pb-16 border-t border-base-300">
  <div class="container mx-auto px-4">
    <?php
    $contactDataModel = \seopress_container()->get(\SeopressComposer\Models\Contact_Data::class);
    $contactData = $contactDataModel->get_data();
    
    if (!empty($contactData)) {
      echo $components->getContactFooter()->render($contactData);
    }
    ?>
  </div>
</div>
