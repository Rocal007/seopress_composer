<?php
$components = seopress_components();
$models = seopress_models();
?>

<!-- Hero -->
<?= $components->getHero()->render($models->getTopPictureData(['buttons' => false, 'logo' => false])); ?>

<!-- Breadcrumbs -->
<?= $components->getBreadcrumb()->render(); ?>

<main id="main-content" role="main">
<!-- Main Text -->
<?= $components->getMainText()->render($models->getMainTextData()); ?>

<!-- Kosten Component -->
<?= $components->getKosten()->render($models->getKostenData()); ?>