<?php
$components = seopress_components();
$models = seopress_models();
?>

<!-- Hero -->
<?= $components->getHero()->render($models->getTopPictureData(['buttons' => true, 'logo' => false])); ?>

<!-- Main Text -->
<?= $components->getMainText()->render($models->getMainTextData()); ?>

<!-- Tipps Tabs (Categories) with Nested Accordion -->
<?php
// Combine Components: Structure Data for Tabs > Accordion
$tipsData = $models->getAllTipsData();
foreach ($tipsData as &$tipCat) {
  if (!empty($tipCat['items'])) {
    $tipCat['content'] = $components->getAccordion()->render($tipCat['items'] ?? []);
  }
}
unset($tipCat);
?>
<?= $components->getTabs()->render($tipsData); ?>