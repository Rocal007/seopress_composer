<?php
namespace SeopressComposer\Services;

use SeopressComposer\Core\ModelsDTO;
use SeopressComposer\Helpers\PageHelper;

class SchemaService
{
  private ModelsDTO $models;
  private PageHelper $pageHelper;

  public function __construct(ModelsDTO $models, PageHelper $pageHelper)
  {
    $this->models = $models;
    $this->pageHelper = $pageHelper;
  }

  /**
   * Output JSON-LD Schema to wp_head using @graph
   */
  public function output()
  {
    $graph = [];

    // 1. Service & Pricing Schema
    $kostenData = $this->models->getKostenData();
    if (!empty($kostenData)) {
      $serviceSchema = $this->generateServiceSchema($kostenData);
      if ($serviceSchema) {
        $graph[] = $serviceSchema;
      }
    }

    // 2. FAQ Schema
    $faqData = $this->models->getFaqsData();
    $items = $faqData['items'] ?? $faqData;
    if (!empty($items)) {
      $faqSchema = $this->generateFAQSchema($items);
      if ($faqSchema) {
        $graph[] = $faqSchema;
      }
    }

    // 3. Benefits (Vorteile) Schema
    $vorteileData = $this->models->getVorteileData();
    $vItems = $vorteileData['items'] ?? $vorteileData;
    if (!empty($vItems)) {
      $vSchema = $this->generateBenefitsSchema($vItems, $vorteileData['heading'] ?? 'Ihre Vorteile');
      if ($vSchema) {
        $graph[] = $vSchema;
      }
    }

    // 4. LocalBusiness Schema (with AggregateRating)
    $localBusiness = $this->generateLocalBusinessSchema();
    if ($localBusiness) {
        // Add AggregateRating if available
        $rating = $this->getAggregateRating();
        if ($rating) {
            $localBusiness['aggregateRating'] = $rating;
        }
        array_unshift($graph, $localBusiness); // Prioritize LocalBusiness
    }

    // 5. WebSite & WebPage Schema
    $webSiteSchema = $this->generateWebSiteSchema();
    $graph = array_merge($graph, $webSiteSchema);

    // 6. BreadcrumbList Schema (integrated into @graph)
    $breadcrumb = new \SeopressComposer\Components\Breadcrumb();
    $breadcrumbData = $breadcrumb->get_schema_data();
    if (!empty($breadcrumbData)) {
        $graph[] = $breadcrumbData;
    }

    // Output Graph
    if (!empty($graph)) {
      // Sanitize: strip HTML tags from all string values in the graph.
      // TextReplacementService::highlighter() may inject <strong> into text fields
      // which is invalid in JSON-LD and causes Google Rich Results warnings.
      $graph = $this->sanitizeSchemaGraph($graph);

      $output = [
        '@context' => 'https://schema.org',
        '@graph' => $graph
      ];
      echo PHP_EOL . '<!-- Dynamic Schema (Theme Native) -->' . PHP_EOL;
      echo '<script type="application/ld+json">' . json_encode($output, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . PHP_EOL;
    }
  }

  /**
   * Recursively strip HTML tags from all string values in a schema array.
   */
  private function sanitizeSchemaGraph(array $data): array
  {
    foreach ($data as $key => &$value) {
      if (is_array($value)) {
        $value = $this->sanitizeSchemaGraph($value);
      } elseif (is_string($value) && $value !== strip_tags($value)) {
        $value = strip_tags($value);
      }
    }
    return $data;
  }

  /**
   * Get AggregateRating from ACF options or defaults
   */
  private function getAggregateRating(): ?array
  {
    $rating_value = get_field('aggregate_rating_value', 'option');
    $review_count = get_field('aggregate_review_count', 'option');

    // Use stored values or sensible defaults if reviews exist
    if (!empty($rating_value) && !empty($review_count)) {
        return [
            '@type' => 'AggregateRating',
            'ratingValue' => (string) $rating_value,
            'reviewCount' => (string) $review_count,
            'bestRating' => '5',
            'worstRating' => '1',
        ];
    }

    return null;
  }

  private function generateLocalBusinessSchema()
  {
    $options = $this->models->getSiteSettingsData()->get_data();
    if (empty($options)) return null;

    $name = get_bloginfo('name');
    $homepage = !empty($options['homepage']) ? $options['homepage'] : home_url('/');
    
    $telephone = !empty($options['festnetznummer']) ? preg_replace('/\s+/', '', $options['festnetznummer']) : null;
    $logo_id = get_theme_mod('custom_logo');
    $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : null;

    $schema = [
        '@type' => ['LocalBusiness', 'HomeAndConstructionBusiness'],
        '@id' => home_url('/#localbusiness'),
        'name' => $name,
        'url' => $homepage,
        'description' => get_bloginfo('description') ?: 'Professionelle Entrümpelungs- und Räumungsdienstleistungen',
        'priceRange' => '€€',
        'currenciesAccepted' => 'EUR',
        'paymentAccepted' => 'Barzahlung, Überweisung',
        'areaServed' => $this->resolveAreaServed($options),
        'openingHoursSpecification' => [
            [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => ['Monday','Tuesday','Wednesday','Thursday','Friday'],
                'opens' => '08:00',
                'closes' => '20:00',
            ],
            [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => ['Saturday','Sunday'],
                'opens' => '10:00',
                'closes' => '18:00',
            ],
        ],
    ];

    if (!empty($options['strasse']) || !empty($options['plz']) || !empty($options['einsatzgebiet'])) {
        $schema['address'] = array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $options['strasse'] ?? null,
            'postalCode' => $options['plz'] ?? null,
            'addressLocality' => $options['einsatzgebiet'] ?? null,
            'addressCountry' => 'AT',
        ]);
    }

    if ($telephone) {
        $schema['telephone'] = $telephone;
        $schema['contactPoint'] = [
            '@type'             => 'ContactPoint',
            'telephone'         => $telephone,
            'contactType'       => 'customer service',
            'availableLanguage' => ['German'],
            'areaServed'        => 'AT',
        ];
    }

    if (!empty($options['e-mail'])) {
        $schema['email'] = sanitize_email($options['e-mail']);
    }

    if ($logo_url) {
        $schema['logo'] = [
            '@type' => 'ImageObject',
            'url' => $logo_url,
        ];
        $schema['image'] = $logo_url;
    }

    if (!empty($options['inhaber'])) {
        $schema['founder'] = [
            '@type' => 'Person',
            'name' => esc_html($options['inhaber']),
        ];
    }

    if (!empty($options['uid'])) {
        $schema['vatID'] = esc_html($options['uid']);
        $schema['legalName'] = $name;
    }

    // sameAs — Entity disambiguation for Knowledge Panel
    $sameAs = [];
    $social_fields = ['facebook_url', 'instagram_url', 'google_maps_url', 'youtube_url', 'linkedin_url'];
    foreach ($social_fields as $field) {
        $url = get_field($field, 'option');
        if (!empty($url)) {
            $sameAs[] = esc_url($url);
        }
    }
    if (!empty($options['homepage']) && $options['homepage'] !== home_url('/')) {
        $sameAs[] = esc_url($options['homepage']);
    }
    if (!empty($sameAs)) {
        $schema['sameAs'] = $sameAs;
    }

    return $schema;
  }

  private function generateWebSiteSchema()
  {
    $site_name = get_bloginfo('name');
    $home = home_url('/');
    $graph = [];

    $graph[] = [
        '@type' => 'WebSite',
        '@id' => $home . '#website',
        'url' => $home,
        'name' => $site_name,
        'description' => get_bloginfo('description'),
        'inLanguage' => 'de-AT',
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => $home . '?s={search_term_string}',
            ],
            'query-input' => 'required name=search_term_string',
        ],
    ];

    if (!is_admin()) {
        $page_url = is_singular() ? get_permalink() : (is_front_page() ? $home : home_url(add_query_arg([], $GLOBALS['wp']->request)));
        $page_title = is_singular() ? get_the_title() : get_the_archive_title();

        $webpage = [
            '@type' => 'WebPage',
            '@id' => esc_url($page_url) . '#webpage',
            'url' => esc_url($page_url),
            'name' => esc_html($page_title ?: $site_name),
            'isPartOf' => ['@id' => $home . '#website'],
            'about' => ['@id' => home_url('/#localbusiness')],
            'inLanguage' => 'de-AT',
            'datePublished' => is_singular() ? get_the_date('c') : null,
            'dateModified' => is_singular() ? get_the_modified_date('c') : null,
            'speakable' => [
                '@type' => 'SpeakableSpecification',
                'cssSelector' => ['h1', '.haupttext', '.faq-section', '[role="main"]'],
            ],
        ];

        // Add author reference from business owner
        $options = $this->models->getSiteSettingsData()->get_data();
        if (!empty($options['inhaber'])) {
            $webpage['author'] = [
                '@type' => 'Person',
                'name' => esc_html($options['inhaber']),
                'worksFor' => ['@id' => home_url('/#localbusiness')],
            ];
        }

        $graph[] = array_filter($webpage, fn($v) => $v !== null);
    }

    return $graph;
  }

  private function generateServiceSchema(array $kostenData)
  {
    // Get Location
    $location = $this->pageHelper->get_location();
    if (is_array($location)) {
      $location = $location['name'] ?? '';
    }

    $offers = [];

    foreach ($kostenData as $row) {
      $serviceName = $row['art_kosten'] ?? '';
      if (empty($serviceName))
        continue;

      $prices = [
        'Wenig Hausrat' => $row['wenig_kosten'] ?? '',
        'Normaler Hausrat' => $row['normal_kosten'] ?? '',
        'Viel Hausrat' => $row['viel_kosten'] ?? '',
        'Messie / Extrem' => $row['messie_kosten'] ?? ''
      ];

      foreach ($prices as $type => $priceStr) {
        if (empty($priceStr) || $priceStr === '-')
          continue;

        $offers[] = [
          '@type' => 'Offer',
          'itemOffered' => [
            '@type' => 'Service',
            'name' => $serviceName . ' (' . $type . ')'
          ],
          'priceCurrency' => 'EUR',
          'price' => $this->parsePrice($priceStr),
          'description' => "Preis für $serviceName ($type) in $location: $priceStr"
        ];
      }
    }

    if (empty($offers))
      return null;

    return [
      '@type' => 'Service',
      'name' => 'Entrümpelung & Räumung',
      'areaServed' => [
        '@type' => 'City',
        'name' => $location
      ],
      'hasOfferCatalog' => [
        '@type' => 'OfferCatalog',
        'name' => 'Dienstleistungen und Preise',
        'itemListElement' => $offers
      ]
    ];
  }

  private function generateFAQSchema(array $items)
  {
    $faqEntities = [];
    foreach ($items as $item) {
      $q = $item['faq_heading'] ?? $item['title'] ?? '';
      $a = $item['faq_content'] ?? $item['content'] ?? '';

      if ($q && $a) {
        $faqEntities[] = [
          '@type' => 'Question',
          'name' => strip_tags($q),
          'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => wp_kses_post($a)
          ]
        ];
      }
    }

    if (empty($faqEntities))
      return null;

    return [
      '@type' => 'FAQPage',
      'mainEntity' => $faqEntities
    ];
  }

  private function generateBenefitsSchema(array $items, string $heading)
  {
    $listElements = [];
    foreach ($items as $key => $item) {
      $name = $item['title'] ?? '';
      $desc = $item['content'] ?? '';
      if ($name) {
        $listElements[] = [
          '@type' => 'ListItem',
          'position' => $key + 1,
          'name' => esc_html($name),
          'description' => wp_strip_all_tags($desc)
        ];
      }
    }

    if (empty($listElements))
      return null;

    return [
      '@type' => 'ItemList',
      'name' => esc_html($heading),
      'itemListElement' => $listElements
    ];
  }

  private function parsePrice(string $price): string
  {
    $price = str_replace('.', '', $price);
    $price = str_replace(',', '.', $price);

    if (preg_match('/(\d+(?:\.\d+)?)/', $price, $matches)) {
      return $matches[1];
    }
    return '0';
  }
  /**
   * Resolve areaServed dynamically based on page context.
   * - District pages → specific City with sameAs Wikipedia link
   * - Main/service pages → City "Wien" (primary market)
   * - Fallback → einsatzgebiet from site settings or "Österreich"
   */
  private function resolveAreaServed(array $options): array
  {
    $obj = get_queried_object();

    // District taxonomy page → specific district as City
    if ($obj instanceof \WP_Term && $obj->taxonomy === 'district') {
      return [
        '@type' => 'City',
        'name' => $obj->name,
        'containedInPlace' => [
          '@type' => 'City',
          'name' => 'Wien',
          'sameAs' => 'https://de.wikipedia.org/wiki/Wien',
        ],
      ];
    }

    // Service page with district assignment → specific district
    if ($obj instanceof \WP_Post) {
      $districts = get_the_terms($obj->ID, 'district');
      if ($districts && !is_wp_error($districts)) {
        return [
          '@type' => 'City',
          'name' => $districts[0]->name,
          'containedInPlace' => [
            '@type' => 'City',
            'name' => 'Wien',
            'sameAs' => 'https://de.wikipedia.org/wiki/Wien',
          ],
        ];
      }
    }

    // Default: Wien as primary service area (or einsatzgebiet from settings)
    $area = !empty($options['einsatzgebiet']) ? $options['einsatzgebiet'] : 'Wien';
    return [
      '@type' => 'City',
      'name' => $area,
      'sameAs' => 'https://de.wikipedia.org/wiki/Wien',
    ];
  }
}
