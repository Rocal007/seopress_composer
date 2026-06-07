<?php

/**
 * The template for displaying District (Bezirk) taxonomy archives
 * 
 * @global \SeopressComposer\Core\ComponentContainer seopress_components()
 * @global \SeopressComposer\Core\ModelsDTO seopress_models()
 */

get_header();

$components = \seopress_components();
$models = \seopress_models();
$district = $models->getDistrictData();
$sections = $models->getDistrictSections();
$grouped_pages = $models->getDistrictGroupedPages();
$other_districts = $models->getOtherDistricts();
?>

<main id="main-content" role="main">
  <?= $components->getHero()->render($models->getTopPictureData()); ?>
  <?= $components->getBreadcrumb()->render(); ?>

  <?php
  // Use Haupttext from Startseite
  $pageHelper = \seopress_container()->get(\SeopressComposer\Helpers\PageHelper::class);
  $startPageId = get_option('page_on_front');
  $validStartId = $pageHelper->check_page_id($startPageId);

  $mainTextData = $models->getMainTextModel()->get_data($validStartId);

  if (!empty($mainTextData['haupttext'])) {
    echo $components->getSimpleContent()->render($mainTextData['haupttext']);
  }
  ?>

  <?= $components->getGroupedCards()->render($grouped_pages, $sections['district_services'], true); ?>

  <!-- FAQ Section -->
  <?php
  $faqsData = $models->getFaqsData();
  echo $components->getAccordion()->render($faqsData);
  ?>

  <!-- ── FORM ── -->
  <?php
  $multiStepForm = \seopress_container()->get(\SeopressComposer\Components\MultiStepForm::class);
  $districtName  = $district['name'] ?? '';

  echo \seopress_layouts()->render(
    $multiStepForm->render(null, 'location', $districtName),
    1,
    [
      'title'           => 'Kostenlos anfragen in ' . esc_html($districtName),
      'description'     => 'Schildern Sie uns Ihr Anliegen — wir melden uns noch am selben Tag.',
      'wrapper_class'   => 'pt-20 pb-20 bg-silver-shine shadow-inner',
      'container_class' => 'container mx-auto px-4 max-w-4xl'
    ]
  );
  ?>

  <?php
  if (!empty($other_districts)) {
    // 1. Render Title
    $odTitleData = $sections['other_districts'] ?? '';
    $odTitle = is_array($odTitleData) ? ($odTitleData['title'] ?? $odTitleData[0] ?? '') : $odTitleData;
    $odDesc = is_array($odTitleData) ? ($odTitleData['description'] ?? $odTitleData[1] ?? '') : '';

    if (!empty($odTitle)) {
  ?>

      <section class="pt-16 bg-base-200">
        <?= $components->getSectionTitle()->render($odTitle, $odDesc) ?>
      </section>
  <?php
    }

    echo \seopress_layouts()->render(
      $components->getIconLinks()->renderMany($other_districts),
      4,
      [
        'wrapper_class' => 'pb-12 bg-base-200',
        'grid_class'    => 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6'
      ]
    );
  }
  ?>


</main>

<?php
get_footer();
