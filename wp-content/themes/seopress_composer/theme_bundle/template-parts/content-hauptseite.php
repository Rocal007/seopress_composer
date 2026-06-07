<?php
/**
 * Hauptseite (Service+Location) — Best of Composer + Kopie
 *
 * Fallback-Strategie: eigene Seite → Parent-Seite
 */

$components = seopress_components();
$models     = seopress_models();
$layouts    = seopress_layouts();

$page_id   = get_the_ID();
$parent_id = wp_get_post_parent_id($page_id) ?: 0;

// Kosten: eigene Seite → Parent
$kostenData = $models->getKostenData();
if (empty($kostenData) && $parent_id) {
  $kostenData = $models->getKostenModel()->get_data($parent_id);
}

// Vorteile: eigene Seite → Parent
$vorteileData = $models->getVorteileData();
if (empty($vorteileData['items'] ?? []) && $parent_id) {
  $vorteileData = $models->getVorteileModel()->get_data($parent_id);
}

$subtextData = $models->getSubtextData();

?>

<!-- 1. HERO -->
<?= $components->getHero()->render($models->getTopPictureData()); ?>

<!-- 2. BREADCRUMB -->
<?= $components->getBreadcrumb()->render(); ?>

<!-- 3. TEASER — SEO Excerpt -->
<?php
$post    = get_post($page_id);
$excerpt = '';
if (!empty($post->post_excerpt)) {
  $excerpt = wp_strip_all_tags($post->post_excerpt);
} else {
  $excerpt = (string) get_post_meta($page_id, '_seopress_titles_desc', true);
}
if (empty($excerpt) && $parent_id) {
  $parent_post = get_post($parent_id);
  if (!empty($parent_post->post_excerpt)) {
    $excerpt = wp_strip_all_tags($parent_post->post_excerpt);
  }
}
if (!empty($excerpt)): ?>
<div class="bg-base-100 border-b border-base-200">
  <div class="container mx-auto px-4 py-8 max-w-4xl">
    <p class="text-lg text-base-content/70 font-sans leading-relaxed text-center">
      <?= esc_html($excerpt) ?>
    </p>
  </div>
</div>
<?php endif; ?>

<!-- 4. VORTEILE — DESIRE -->
<?php if (!empty($vorteileData['items'] ?? [])): ?>
  <?= $components->getListTooltip()->render($vorteileData); ?>
<?php endif; ?>

<!-- 5. FORM — ACTION (Primary Conversion Point) -->
<?php
$multiStepForm = \seopress_container()->get(\SeopressComposer\Components\MultiStepForm::class);

// Pre-selection: derive service from page title (parent page = service name)
// and location from page title suffix ("Service PLZ Ort")
$page_title   = get_the_title($page_id);
$parent_title = $parent_id ? get_the_title($parent_id) : '';

// Service = parent page title (e.g. "Umzug") or current if no parent
$preselected_service = $parent_id
  ? sanitize_title($parent_title)   // child page: parent is the service
  : sanitize_title($page_title);    // top-level service page

// Location: extract from title suffix "Service PLZ Ort" → "Ort"
$location_name = null;
if ($parent_id && preg_match('/\d{4}\s+(.+)$/', $page_title, $m)) {
  $location_name = trim($m[1]);
}

// Fallback: still check taxonomy terms for backwards compatibility
if (!$preselected_service) {
  $current_terms = wp_get_post_terms($page_id, 'service_category');
  if (!empty($current_terms) && !is_wp_error($current_terms)) {
    $preselected_service = $current_terms[0]->slug;
  }
}
if (!$location_name) {
  $district_terms = wp_get_post_terms($page_id, 'district');
  if (!empty($district_terms) && !is_wp_error($district_terms)) {
    $location_name = $district_terms[0]->name;
  }
}

echo $layouts->render(
  $multiStepForm->render($preselected_service, 'service', $location_name),
  1,
  [
    'title'           => 'Kostenlos anfragen',
    'description'     => 'Nutzen Sie unser Formular für eine kostenlose Beratung und schnelle Rückmeldung.',
    'wrapper_class'   => 'pt-16 lg:pt-24 pb-24 lg:pb-32 bg-silver-shine shadow-inner',
    'container_class' => 'container mx-auto px-4 max-w-3xl'
  ]
);
?>

<!-- 6. SERVICE LIST -->
<?= $components->getServiceList()->render($models->getServiceListData(), 'bg-base-100'); ?>

<!-- 7. ABLAUF -->
<?= $components->getCtaBoxes()->render($models->getCtaBoxesData()); ?>

<!-- 8. PREISE & KOSTEN -->
<?php if (!empty($kostenData)): ?>
  <?= $layouts->render(
    $components->getPricingCard()->renderManyFromModel($kostenData),
    4,
    [
      'title'           => 'Preise &amp; Kosten',
      'tooltip'         => $kostenData[0]['heading'] ?? '',
      'wrapper_class'   => 'py-24 lg:py-32 bg-base-200',
      'container_class' => 'container mx-auto px-4',
      'heading_class'   => 'text-3xl font-bold text-primary mb-6 flex items-center justify-center gap-2'
    ]
  ); ?>
<?php endif; ?>

<!-- 9. SUBTEXT 1 -->
<?= $components->getImageTextReadMore()->renderFromModel($subtextData, 0, 'right', 'bg-white'); ?>

<!-- 10. FAQ -->
<?= $components->getAccordion()->render($models->getFaqsData(), null, ['title' => 'Häufig gestellte Fragen', 'bg_class' => 'bg-base-100']); ?>

<!-- 11. SILO NAVIGATION — Links DOWN to child location pages (PageRank flow) -->
<?php
// Fetch all pages that have the service_category term matching this page's slug
$child_pages = get_posts([
  'post_type'      => 'page',
  'post_status'    => 'publish',
  'posts_per_page' => -1,
  'orderby'        => 'title',
  'order'          => 'ASC',
  'tax_query'      => [[
    'taxonomy' => 'service_category',
    'field'    => 'slug',
    'terms'    => $preselected_service, // We derived this above (e.g. "raeumungen")
  ]],
  'post__not_in'   => [$page_id], // Exclude the Hauptseite itself
]);

if (!empty($child_pages)) {
  $child_links = array_map(function($child) {
    return [
      'title' => get_the_title($child->ID),
      'url'   => get_permalink($child->ID),
      'icon'  => 'location',
    ];
  }, $child_pages);

  $silo_title = 'Einsatzgebiete';
  $parent_name = $parent_id ? get_the_title($parent_id) : get_the_title($page_id);
  if ($parent_name) {
    $silo_title = esc_html($parent_name) . ' in Ihrer Nähe';
  }

  echo $layouts->render(
    $components->getLinkList()->render($child_links, true, 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4'),
    1,
    [
      'title'         => $silo_title,
      'description'   => 'Wählen Sie Ihren Bezirk für lokale Preise und schnelle Verfügbarkeit.',
      'wrapper_class' => 'py-16 bg-base-200',
    ]
  );
}
?>

<!-- 12. SEO CONTENT AREA (Texts placed lower to reduce repetition for users but keep SEO relevance) -->
<div class="bg-base-200 pt-12 pb-12 border-t border-base-300">
  <!-- MAIN TEXT — SEO Content -->
  <?php
  $mainTextData = $models->getMainTextData();
  if (!empty($mainTextData['haupttext'])) {
    echo $components->getSimpleContent()->render($mainTextData['haupttext']);
  } else {
    echo $components->getNumberedFeatures()->render($mainTextData, 2);
  }
  ?>

  <!-- SUBTEXT 2 -->
  <?= $components->getImageTextReadMore()->renderFromModel($subtextData, 1, 'left', 'bg-transparent'); ?>

  <!-- CTA — ACTION (Secondary Conversion Point) -->
  <div class="py-16">
    <div class="container mx-auto px-4">
      <?= $components->getContactCTA()->render(); ?>
    </div>
  </div>
</div>

<!-- 13. OTHER SERVICES — Cross-Silo Navigation (internal linking) -->
<div class="bg-base-100 py-12">
  <div class="container mx-auto px-4">
    <?php
    $serviceLinks = $models->getOtherServices(0, 999);
    if (!empty($serviceLinks)) {
      echo $components->getLinkList()->render($serviceLinks, true, 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4', 'Unsere Services', 'text-2xl font-bold mb-6 text-center');
    }
    ?>
  </div>
</div>
