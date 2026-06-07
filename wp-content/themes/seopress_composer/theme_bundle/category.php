<?php
/**
 * Category Archive Template — Fokussiert
 * 
 * 2026 SEO: Max. 6 Blöcke
 */

get_header();

$components = seopress_components();
$models = seopress_models();
$layouts = seopress_layouts();
?>

<main>

  <!-- 1. HERO -->
  <?= $components->getHero()->render($models->getTopPictureData()); ?>

  <!-- 2. BREADCRUMB -->
  <?= $components->getBreadcrumb()->render(); ?>

  <!-- 1.5. OVERVIEW TEXT FROM API -->
  <?php
  $overviewText = $models->getTaxonomyModel()->get_category_overview_text();
  if (!empty($overviewText)):
  ?>
    <section class="py-12 bg-base-100">
      <div class="container mx-auto px-4">
        <div class="prose prose-xl md:prose-2xl font-medium max-w-4xl mx-auto text-center text-base-content/80 leading-relaxed">
          <?= wp_kses_post($overviewText) ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <!-- 3. VORTEILE -->
  <?php
  $vorteileData = $models->getVorteileData();
  echo $components->getListTooltip()->render($vorteileData);
  ?>

  <!-- 4. MAIN TEXT -->
  <?= $components->getNumberedFeatures()->render($models->getMainTextData(), 2); ?>

  <!-- 4.5. ABLAUF / CTA-Boxes -->
  <?php 
  $ctaData = $models->getCtaBoxesData(); 
  echo $components->getProcessCircle()->render($ctaData); 
  ?>

  <!-- 5. LEISTUNGEN DER KATEGORIE (GROUPED SERVICES) -->
  <?php
  $term_id = get_queried_object_id();
  $grouped_pages = $models->getDistrictGroupedPages($term_id);
  
  // Fallback to all services if none are explicitly assigned to this district category
  if (empty($grouped_pages)) {
    $grouped_pages = $models->get_all_services_grouped();
  }
  
  $sections = $models->getDistrictSections($term_id);
  $slTitle = $sections['district_services'] ?? 'Unsere Leistungen';
  
  echo $components->getGroupedCards()->render(
    $grouped_pages,
    ['title' => $slTitle, 'description' => ''],
    true
  );
  ?>

  <!-- 6. MULTI-STEP FORM (replaces ContactCTA) -->
  <?php
  $multiStepForm = \seopress_container()->get(\SeopressComposer\Components\MultiStepForm::class);
  $cat_name = single_cat_title('', false);
  echo $layouts->render(
    $multiStepForm->render(null, 'default'),
    1,
    [
      'title'           => 'Kostenlos anfragen für ' . esc_html($cat_name),
      'description'     => 'Unverbindlich — wir melden uns noch am selben Tag.',
      'wrapper_class'   => 'pt-16 lg:pt-24 pb-24 lg:pb-32 bg-silver-shine shadow-inner',
      'container_class' => 'container mx-auto px-4 max-w-4xl'
    ]
  );
  ?>

  <!-- 6. PRICING -->
  <?= $layouts->render(
    $components->getPricingCard()->renderManyFromModel($models->getKostenData()),
    4,
    [
      'wrapper_class' => 'py-16 bg-base-200',
      'container_class' => 'container mx-auto px-4'
    ]
  ); ?>

</main>
<?php
get_footer();
