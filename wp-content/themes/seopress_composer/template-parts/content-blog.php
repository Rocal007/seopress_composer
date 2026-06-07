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

<!-- Blogs All (Legacy Function) -->
<?php
if (function_exists('blogs_all')) {
    blogs_all();
} else {
    // If the legacy function is missing, we might want to implement a temporary fallback or logs
    // This loop should ideally be replaced by a GridContent or similar component if possible in the future
    // For now, we respect the existing function call.
}
?>