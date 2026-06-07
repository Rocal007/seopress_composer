<?php
$components = seopress_components();
$models = seopress_models();
?>

<!-- Hero -->
<?= $components->getHero()->render($models->getTopPictureData(['buttons' => true, 'logo' => false])); ?>

<!-- Breadcrumbs -->
<?= $components->getBreadcrumb()->render(); ?>

<!-- Main Text -->
<?= $components->getMainText()->render($models->getMainTextData()); ?>

<!-- FAQ Accordion -->
<?= $components->getAccordion()->render($models->getFaqsData()); ?>