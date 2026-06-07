<?php
$components = seopress_components();
$models = seopress_models();
?>

<!-- Hero -->
<?= $components->getHero()->render($models->getTopPictureData(['buttons' => true, 'logo' => false])); ?>

<!-- Main Text -->
<?= $components->getMainText()->render($models->getMainTextData()); ?>