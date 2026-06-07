<?php
/**
 * Service Category Archive — FACTORIUM Hub Page
 *
 * Nexus-equivalent CollectionPage: pure navigation hub + trust signals.
 * No remote text content — services are linked, not duplicated.
 *
 * Nexus Mapping:
 *  SiloLayout          → WP header/footer
 *  Hero + Breadcrumb   → Hero + Breadcrumb components
 *  Services Grid       → Child pages of matching WP parent page
 *  BenefitsGrid        → Leistungen from remote ACF (cached)
 *  TrustSection        → SiteSettings GISA/ATU trust signals
 *  ServiceForm         → MultiStepForm
 *  Sibling Categories  → Footer pill navigation
 *  Schema.org          → CollectionPage + ItemList + BreadcrumbList
 */

get_header();

$term = get_queried_object();
if (!$term || !isset($term->term_id)) {
  get_template_part('404');
  get_footer();
  return;
}

$components = \seopress_components();
$models     = \seopress_models();
$layouts    = \seopress_layouts();
$pageHelper = \seopress_container()->get(\SeopressComposer\Helpers\PageHelper::class);

// ══════════════════════════════════════════════════════════════════════════
// DATA LAYER
// ══════════════════════════════════════════════════════════════════════════

// ── 1. Service Pages: all pages tagged with this service_category ─────
// Categories are pure taxonomy hubs — no own WP page, no parent-child hierarchy.
// Service pages have the category term assigned via service_category taxonomy.
$service_pages = get_posts([
  'post_type'      => 'page',
  'posts_per_page' => -1,
  'post_status'    => 'publish',
  'orderby'        => 'menu_order title',
  'order'          => 'ASC',
  'tax_query'      => [[
    'taxonomy' => $term->taxonomy,
    'field'    => 'term_id',
    'terms'    => $term->term_id,
  ]],
]);

// ── 1b. Featured Hauptseite: page with same slug as term ──────────────
// e.g. term "raeumungen" → /raeumungen/ (Hauptseite with full remote content)
$hauptseite = get_page_by_path($term->slug);
$hauptseite_id = $hauptseite ? $hauptseite->ID : 0;

// Remove hauptseite from grid (it gets its own featured card)
if ($hauptseite_id) {
  $service_pages = array_filter($service_pages, fn($p) => $p->ID !== $hauptseite_id);
}

// ── 2. Remote data (for benefits/leistungen & icons) ──────────────────
$remote_data = null;
$remote_url  = $pageHelper->get_remote_url(null, false);
if ($remote_url) {
  $repo = \seopress_container()->get(\SeopressComposer\Repositories\RemoteDataRepository::class);
  $remote_data = $repo->fetch($remote_url);
  if (is_array($remote_data) && !empty($remote_data)) {
    $remote_data = $remote_data[0];
  }
}

// ── 3. Trust Signals from SiteSettings ────────────────────────────────
$siteSettings = $pageHelper->get_options_business();
$trust_signals = [];
$fields_trust = [
  ['key' => 'gisa_nummern',          'label' => 'Geprüfter Fachbetrieb', 'short' => 'GISA-Verifiziert'],
  ['key' => 'atu',                   'label' => 'Steuerlich registriert', 'short' => 'ATU-Zertifiziert'],
  ['key' => 'gewerbeberechtigung',   'label' => 'Gewerbeberechtigt',      'short' => 'Lizenziert'],
];
foreach ($fields_trust as $f) {
  $val = $siteSettings[$f['key']] ?? '';
  if (!empty($val)) {
    $trust_signals[] = ['label' => $f['label'], 'short' => $f['short'], 'detail' => $val];
  }
}

// ── 4. Leistungen / Benefits from Remote ACF ─────────────────────────
$benefits = [];
if ($remote_data && !empty($remote_data->acf->leistungen_listen)) {
  foreach ($remote_data->acf->leistungen_listen as $item) {
    $title = $item->leistungen_listen_title ?? ($item->title ?? '');
    $desc  = $item->leistungen_listen_text  ?? ($item->text ?? $item->description ?? '');
    if (!empty($title)) {
      $benefits[] = ['title' => $title, 'description' => strip_tags($desc)];
    }
  }
}

// ── 5. Sibling Categories ─────────────────────────────────────────────
$sibling_terms = get_terms([
  'taxonomy'   => 'service_category',
  'hide_empty' => false,
  'exclude'    => [$term->term_id],
  'parent'     => 0,
  'number'     => 10,
]);
if (is_wp_error($sibling_terms)) $sibling_terms = [];

// ══════════════════════════════════════════════════════════════════════════
// SCHEMA.ORG — CollectionPage + ItemList + BreadcrumbList
// ══════════════════════════════════════════════════════════════════════════
$schema_items = [];
foreach ($service_pages as $i => $sp) {
  $schema_items[] = [
    '@type'    => 'ListItem',
    'position' => $i + 1 + ($hauptseite_id ? 1 : 0),
    'name'     => get_the_title($sp->ID),
    'url'      => get_permalink($sp->ID),
  ];
}
// Prepend Hauptseite to schema
if ($hauptseite_id) {
  array_unshift($schema_items, [
    '@type'    => 'ListItem',
    'position' => 1,
    'name'     => get_the_title($hauptseite_id),
    'url'      => get_permalink($hauptseite_id),
  ]);
}
$schema = [
  '@context' => 'https://schema.org',
  '@graph' => [
    [
      '@type'       => 'CollectionPage',
      'name'        => esc_html($term->name) . ' – ' . get_bloginfo('name'),
      'description' => 'Alle professionellen Leistungen im Bereich ' . esc_html($term->name),
      'url'         => get_term_link($term),
      'mainEntity'  => ['@type' => 'ItemList', 'itemListElement' => $schema_items],
      'isPartOf'    => ['@type' => 'WebSite', 'name' => get_bloginfo('name'), 'url' => home_url('/')],
    ],
    [
      '@type' => 'BreadcrumbList',
      'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => get_bloginfo('name'), 'item' => home_url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => $term->name, 'item' => get_term_link($term)],
      ],
    ],
  ],
];
?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>

<main id="main-content" role="main">

  <!-- ═══════════════════════════════════════════════════════════════════ -->
  <!-- 1. HERO                                                           -->
  <!-- ═══════════════════════════════════════════════════════════════════ -->
  <?= $components->getHero()->render($models->getTopPictureData()); ?>

  <!-- ═══════════════════════════════════════════════════════════════════ -->
  <!-- 2. BREADCRUMB                                                     -->
  <!-- ═══════════════════════════════════════════════════════════════════ -->
  <?= $components->getBreadcrumb()->render(); ?>

  <!-- ═══════════════════════════════════════════════════════════════════ -->
  <!-- 3. TRUST SIGNALS — Nexus TrustSection equivalent                  -->
  <!-- ═══════════════════════════════════════════════════════════════════ -->
  <?php if (!empty($trust_signals)): ?>
  <section class="bg-base-100 py-8 border-b border-base-200" aria-label="Vertrauenssignale">
    <div class="container mx-auto px-4">
      <div class="grid grid-cols-1 md:grid-cols-<?= count($trust_signals) ?> gap-4">
        <?php foreach ($trust_signals as $sig): ?>
        <div class="group relative text-center p-5 rounded-2xl border border-base-200 hover:border-primary/20 hover:shadow-sm transition-all cursor-help">
          <p class="text-xl font-bold text-primary mb-1">✓</p>
          <p class="font-bold text-base-content text-sm"><?= esc_html($sig['label']) ?></p>
          <p class="text-[10px] text-base-content/40 uppercase tracking-wider font-semibold mt-1"><?= esc_html($sig['short']) ?></p>
          <!-- Tooltip -->
          <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-56 bg-neutral text-neutral-content text-xs rounded-lg px-3 py-2 opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity shadow-lg z-50">
            <p class="font-bold border-b border-white/10 pb-1 mb-1"><?= esc_html($sig['label']) ?></p>
            <p class="whitespace-pre-line text-white/90"><?= esc_html($sig['detail']) ?></p>
            <div class="absolute top-full left-1/2 -ml-1 border-4 border-transparent border-t-neutral"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ═══════════════════════════════════════════════════════════════════ -->
  <!-- 4. SERVICES GRID — Nexus CollectionPage pattern                   -->
  <!-- ═══════════════════════════════════════════════════════════════════ -->
  <?php if (!empty($service_pages) || $hauptseite_id): ?>
  <section id="leistungen" class="py-20 lg:py-28 bg-base-200/30" aria-labelledby="section-services">
    <div class="container mx-auto px-4">

      <!-- Section label -->
      <div class="flex items-center gap-4 mb-10">
        <span class="text-xs font-bold uppercase tracking-[0.2em] px-4 py-1.5 rounded-full bg-primary/10 text-primary">
          Alle <?= esc_html($term->name) ?> Leistungen
        </span>
        <div class="flex-1 h-px bg-base-300"></div>
      </div>

      <h2 id="section-services" class="sr-only">Unsere <?= esc_html($term->name) ?> Leistungen</h2>

      <?php // ── Featured Hauptseite ─────────────────────────────────── ?>
      <?php if ($hauptseite_id): ?>
      <a
        href="<?= esc_url(get_permalink($hauptseite_id)) ?>"
        class="group flex flex-col md:flex-row items-center gap-8 bg-primary/5 border-2 border-primary/20 rounded-3xl p-8 md:p-10 mb-10 hover:border-primary/40 hover:shadow-lg transition-all duration-300"
        title="<?= esc_attr(get_the_title($hauptseite_id)) ?> — Übersichtsseite"
      >
        <div class="w-16 h-16 rounded-2xl bg-primary/15 flex items-center justify-center shrink-0">
          <svg class="w-8 h-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
        </div>
        <div class="flex-1 text-center md:text-left">
          <h3 class="text-2xl font-bold text-base-content mb-2 group-hover:text-primary transition-colors">
            <?= esc_html(get_the_title($hauptseite_id)) ?>
          </h3>
          <p class="text-base-content/60 text-sm leading-relaxed">
            Alle Informationen, Ablauf und Preise auf der Hauptseite.
          </p>
        </div>
        <div class="inline-flex items-center gap-2 text-sm font-bold text-primary group-hover:underline underline-offset-4 decoration-2 shrink-0">
          Zur Hauptseite
          <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </div>
      </a>
      <?php endif; ?>

      <?php // ── Service Cards Grid ──────────────────────────────────── ?>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
        <?php foreach ($service_pages as $sp):
          $title     = get_the_title($sp->ID);
          $permalink = get_permalink($sp->ID);
          $excerpt   = get_the_excerpt($sp->ID);

          // Icon resolution: remote ACF → local ACF → fallback SVG
          $icon_url = '';
          // Try remote icon
          if ($remote_data && !empty($remote_data->acf->hauptseiten_icon)) {
            $raw = $remote_data->acf->hauptseiten_icon;
            if (is_string($raw)) $icon_url = $raw;
            elseif (is_object($raw) && !empty($raw->url)) $icon_url = $raw->url;
          }
          // Try local ACF
          if (empty($icon_url)) {
            $icon_raw = function_exists('get_field') ? get_field('hauptseiten_icon', $sp->ID) : '';
            if (is_array($icon_raw) && !empty($icon_raw['url'])) $icon_url = $icon_raw['url'];
            elseif (is_numeric($icon_raw) && $icon_raw > 0)       $icon_url = wp_get_attachment_image_url($icon_raw, 'thumbnail') ?: '';
            elseif (is_string($icon_raw) && !empty($icon_raw))    $icon_url = $icon_raw;
          }

          if (!empty($icon_url)) {
              $clean_title = wp_strip_all_tags(\seopress_container()->get(\SeopressComposer\Services\TextReplacementService::class)->clean_title($title));
              $icon_url = \seopress_container()->get(\SeopressComposer\Services\ImageDownloadService::class)->get_local_image_url($icon_url, $clean_title . ' icon', $clean_title . ' icon');
          }
        ?>
        <a
          href="<?= esc_url($permalink) ?>"
          class="group flex flex-col bg-base-100 rounded-3xl border border-base-200 shadow-[0_2px_12px_rgba(0,0,0,0.04)] hover:shadow-[0_8px_24px_rgba(0,0,0,0.08)] hover:border-primary/20 transition-all duration-300 p-8 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2"
          title="<?= esc_attr($title) ?>"
        >
          <!-- Icon Frame -->
          <div class="w-14 h-14 mb-5 rounded-2xl bg-primary/10 flex items-center justify-center shrink-0">
            <?php if ($icon_url): ?>
            <img src="<?= esc_url($icon_url) ?>" alt="" class="w-8 h-8 object-contain" loading="lazy" width="32" height="32" aria-hidden="true">
            <?php else: ?>
            <svg class="w-7 h-7 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <?php endif; ?>
          </div>

          <!-- Content -->
          <div class="flex-1 flex flex-col">
            <h3 class="text-xl font-bold text-base-content mb-3 group-hover:text-primary transition-colors leading-tight">
              <?= esc_html($title) ?>
            </h3>
            <?php if ($excerpt): ?>
            <p class="text-base-content/60 text-sm leading-relaxed mb-6 line-clamp-3 flex-grow">
              <?= esc_html($excerpt) ?>
            </p>
            <?php endif; ?>
          </div>

          <!-- CTA -->
          <div class="mt-auto inline-flex items-center text-sm font-semibold text-primary group-hover:underline decoration-2 underline-offset-4">
            Details ansehen
            <svg class="ml-1.5 w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </a>
        <?php endforeach; ?>
      </div>

    </div>
  </section>
  <?php endif; ?>

  <!-- ═══════════════════════════════════════════════════════════════════ -->
  <!-- 5. BENEFITS GRID — Nexus BenefitsGrid equivalent                  -->
  <!-- ═══════════════════════════════════════════════════════════════════ -->
  <?php if (!empty($benefits)): ?>
  <section class="bg-base-100 py-20 lg:py-28" aria-labelledby="section-benefits">
    <div class="container mx-auto px-4 max-w-5xl">
      <h2 id="section-benefits" class="sr-only">Unsere Unternehmensvorteile</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <?php foreach (array_slice($benefits, 0, 6) as $benefit): ?>
        <div class="flex flex-col sm:flex-row gap-5 p-7 bg-base-100 rounded-3xl border border-base-200 shadow-[0_2px_12px_rgba(0,0,0,0.04)] hover:shadow-[0_8px_24px_rgba(0,0,0,0.08)] transition-all duration-300 items-start">
          <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-success/10 flex items-center justify-center">
            <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <div>
            <h3 class="font-bold text-lg mb-2 text-base-content"><?= esc_html($benefit['title']) ?></h3>
            <?php if (!empty($benefit['description'])): ?>
            <p class="text-base-content/60 leading-relaxed text-sm"><?= esc_html($benefit['description']) ?></p>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ═══════════════════════════════════════════════════════════════════ -->
  <!-- 6. FORM — Nexus ServiceForm equivalent                            -->
  <!-- ═══════════════════════════════════════════════════════════════════ -->
  <?php
  $multiStepForm = \seopress_container()->get(\SeopressComposer\Components\MultiStepForm::class);
  echo $layouts->render(
    $multiStepForm->render(null, 'category'),
    1,
    [
      'title'           => 'Kostenlose Beratung & Angebot',
      'description'     => 'Wählen Sie einen Bereich – wir rufen Sie noch am selben Tag zurück.',
      'wrapper_class'   => 'pt-24 lg:pt-32 pb-24 lg:pb-32 bg-silver-shine shadow-inner',
      'container_class' => 'container mx-auto px-4',
    ]
  );
  ?>

  <!-- ═══════════════════════════════════════════════════════════════════ -->
  <!-- 7. SIBLING CATEGORIES — cross-silo navigation                     -->
  <!-- ═══════════════════════════════════════════════════════════════════ -->
  <?php if (!empty($sibling_terms)): ?>
  <nav class="py-14 bg-base-100 border-t border-base-200" aria-label="Weitere Service-Kategorien">
    <div class="container mx-auto px-4">
      <p class="text-xs font-bold uppercase tracking-[0.2em] text-base-content/40 mb-6">Weitere Leistungsbereiche</p>
      <ul class="flex flex-wrap gap-3">
        <?php foreach ($sibling_terms as $st): ?>
        <li>
          <a
            href="<?= esc_url(get_term_link($st)) ?>"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full border border-base-200 text-sm font-medium text-base-content/70 hover:border-primary hover:text-primary hover:bg-primary/5 transition-all duration-200"
            title="<?= esc_attr('Mehr über ' . $st->name . ' erfahren') ?>"
          >
            <?= esc_html($st->name) ?>
            <svg class="w-3.5 h-3.5 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </nav>
  <?php endif; ?>

</main>

<?php get_footer();
