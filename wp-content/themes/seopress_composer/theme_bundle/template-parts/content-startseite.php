<?php

/**
 * Startseite Template — Best of Composer + Kopie
 *
 * Note: get_header() lives HERE (not in the wrapper) — this is how the
 * original seopress_composer theme was architected.
 */

get_header();

/**
 * Startseite Template — Best of Composer + Kopie
 *
 * AIDA-Flow: Attention → Interest → Desire → Action
 * + MultiStepForm als primärer Conversion-Block
 */

$components    = seopress_components();
$models        = seopress_models();
$layouts       = seopress_layouts();
$sections      = $models->getServiceCategorySections();
$other_services = $models->getOtherServices();
$grouped_pages = $models->get_all_services_grouped();

?>

<main id="main-content" role="main">

  <!-- 1. HERO — ATTENTION -->
  <?= $components->getHero()->render($models->getTopPictureData()); ?>

  <!-- 2. BREADCRUMB -->
  <?= $components->getBreadcrumb()->render(); ?>

  <!-- 2.5. HAUPTTEXT (TEASER) -->
  <?php
  $mainTextData = $models->getMainTextData();
  if (!empty($mainTextData['haupttext'])) {
    echo $components->getSimpleContent()->render($mainTextData['haupttext']);
  }
  ?>

  <!-- 3. VORTEILE — DESIRE (Icon Cards) -->
  <?php
  $vorteileData = $models->getVorteileData();
  if (!empty($vorteileData['items'] ?? [])) {
    echo $components->getListTooltip()->render($vorteileData);
  }
  ?>

  <!-- 4. MULTI-STEP FORM — ACTION (Primary Conversion Point) -->
  <?php
  $multiStepForm = \seopress_container()->get(\SeopressComposer\Components\MultiStepForm::class);
  echo $layouts->render(
    $multiStepForm->render(null, 'default'),
    1,
    [
      'title'           => 'Kostenlos anfragen',
      'description'     => 'Unverbindlich — wir melden uns noch am selben Tag.',
      'wrapper_class'   => 'pt-16 lg:pt-24 pb-24 lg:pb-32 bg-silver-shine shadow-inner',
      'container_class' => 'container mx-auto px-4 max-w-4xl'
    ]
  );
  ?>

  <!-- 5. ABLAUF / CTA-Boxes -->
  <?php $ctaData = $models->getCtaBoxesData(); ?>
  <?= $components->getProcessCircle()->render($ctaData); ?>

  <!-- 6. KOSTEN — DESIRE -->
  <?php
  $kostenData = $models->getKostenData();
  if (!empty($kostenData)) {
    echo '<div class="bg-base-200 pb-24 lg:pb-32">';
    echo $layouts->render(
      $components->getPricingCard()->renderManyFromModel($kostenData),
      4,
      [
        'title'           => 'Preise &amp; Kosten',
        'wrapper_class'   => 'pt-24 lg:pt-32 pb-12',
        'container_class' => 'container mx-auto px-4',
        'heading_class'   => 'text-3xl font-bold text-primary mb-6 flex items-center justify-center gap-2'
      ]
    );
    echo '<div class="container mx-auto px-4 mt-8 flex justify-center">';
    echo '<a href="' . esc_url(home_url('/kontakt')) . '" class="btn btn-primary btn-lg rounded-xl shadow-lg hover:shadow-xl hover:scale-105 transition-all w-full max-w-md text-lg">';
    echo 'Jetzt kostenloses Angebot anfordern →';
    echo '</a>';
    echo '</div>';
    echo '</div>';
  }
  ?>

  <!-- 7. SUBTEXT 1 -->
  <?php
  $subtextData = $models->getSubtextData();
  echo $layouts->render(
    $components->getImageTextReadMore()->renderFromModel($subtextData, 0, 'right'),
    1
  );
  ?>



  <!-- 8. FAQ -->
  <?php echo $components->getAccordion()->render($models->getFaqsData()); ?>

  <!-- 9. GROUPED SERVICES -->
  <?php
  $slTitle = $sections['service_locations']['title'] ?? 'Unsere Services';
  echo $components->getGroupedCards()->render(
    $grouped_pages,
    ['title' => $slTitle, 'description' => $sections['service_locations']['description'] ?? ''],
    true
  );
  ?>

  <!-- 10. SEO CONTENT AREA (Texts placed lower to reduce repetition for users but keep SEO relevance) -->
  <div class="bg-base-200 pt-12 pb-12 border-t border-base-300">
    <!-- NUMBERED FEATURES — INTEREST -->
    <!-- MAIN TEXT moved to top as teaser -->

    <!-- PUNCHLINE (Accent) -->
    <?= $components->getPunchline()->renderVariant($models->getPunchlineData(), 1, 'accent'); ?>

    <!-- SUBTEXT 2 & 3 — 2-Column -->
    <?= $layouts->render(
      $components->getImageTextReadMore()->renderManyFromModel($subtextData, [1, 2], 'top'),
      2
    ); ?>
  </div>

  <!-- 11. ANDERE SERVICES (Icon Links) -->
  <?php
  if (!empty($other_services)) {
    $osTitleData = $sections['other_services'] ?? '';
    $osTitle     = is_array($osTitleData) ? ($osTitleData['title'] ?? $osTitleData[0] ?? '') : $osTitleData;
    $osDesc      = is_array($osTitleData) ? ($osTitleData['description'] ?? $osTitleData[1] ?? '') : '';

    echo $layouts->render(
      $components->getIconLinks()->renderMany($other_services),
      6,
      [
        'wrapper_class' => 'pt-20 pb-20 bg-base-100',
        'title'         => $osTitle,
        'description'   => $osDesc
      ]
    );
  }
  ?>

  <!-- 12. DISTRICT SILOS (Collapsible at bottom) -->
  <?php
  $districts = get_terms([
      'taxonomy'   => 'district',
      'hide_empty' => false,
  ]);
  
  if (!empty($districts) && !is_wp_error($districts)) {
      usort($districts, function($a, $b) {
          return strcmp($a->name, $b->name);
      });
  ?>
  <div class="bg-base-200 py-12 lg:py-16 border-t border-base-300">
    <div class="container mx-auto px-4">
      <details class="w-full group">
        <summary class="list-none outline-none cursor-pointer text-center font-bold text-sm uppercase tracking-widest text-base-content/50 select-none [&::-webkit-details-marker]:hidden flex flex-col items-center gap-2">
          Einsatzgebiete & Bezirke anzeigen
          <svg class="w-5 h-5 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </summary>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 mt-8">
          <?php 
          $wien_plz = [
            'Innere Stadt' => '1010', 'Leopoldstadt' => '1020', 'Landstrasse' => '1030', 
            'Wieden' => '1040', 'Margareten' => '1050', 'Mariahilf' => '1060', 
            'Neubau' => '1070', 'Josefstadt' => '1080', 'Alsergrund' => '1090', 
            'Favoriten' => '1100', 'Simmering' => '1110', 'Meidling' => '1120', 
            'Hietzing' => '1130', 'Penzing' => '1140', 'Rudolfsheim-Fünfhaus' => '1150', 
            'Ottakring' => '1160', 'Hernals' => '1170', 'Währing' => '1180', 
            'Döbling' => '1190', 'Brigittenau' => '1200', 'Floridsdorf' => '1210', 
            'Donaustadt' => '1220', 'Liesing' => '1230'
          ];
          foreach ($districts as $district): 
            $name = $district->name;
            if (strpos($name, 'Gemeindebezirk') !== false) {
                $bezirk_name = trim(str_replace('Gemeindebezirk', '', $name));
                if (isset($wien_plz[$bezirk_name])) {
                    $name = $wien_plz[$bezirk_name] . ' ' . $bezirk_name;
                } else {
                    // Fallback if exact match fails (e.g. umlauts)
                    foreach ($wien_plz as $key => $plz) {
                        if (strpos($name, $key) !== false) {
                            $name = $plz . ' ' . $key;
                            break;
                        }
                    }
                }
            }
          ?>
            <a href="<?= esc_url(get_term_link($district)) ?>" 
               class="btn btn-outline btn-primary btn-sm h-auto py-3 hover:scale-105 transition-transform text-center font-bold">
              <?= esc_html($name) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </details>
    </div>
  </div>
  <?php
  }
  ?>

</main>
<?php get_footer();
