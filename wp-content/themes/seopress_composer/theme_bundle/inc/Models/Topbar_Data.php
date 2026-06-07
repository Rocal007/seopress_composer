<?php

namespace SeopressComposer\Models;

use SeopressComposer\Repositories\RemoteDataRepository;
use SeopressComposer\Services\TextReplacementService;
use SeopressComposer\Helpers\PageHelper;
use SeopressComposer\Controllers\ContextController;
use SeopressComposer\Factories\DataFactory;
use SeopressComposer\Services\IconService;
use SeopressComposer\Repositories\PageRepository;
use SeopressComposer\Repositories\CategoryRepository;
use SeopressComposer\Models\Menu_Data;
use SeopressComposer\Models\FooterLinks_Data;

class Topbar_Data extends BaseModel
{
  private Menu_Data $menuData;
  private FooterLinks_Data $footerLinksData;

  public function __construct(
    RemoteDataRepository $repository,
    TextReplacementService $textReplacer,
    PageHelper $pageHelper,
    ContextController $contextController,
    DataFactory $dataFactory,
    IconService $iconService,
    PageRepository $pageRepository,
    CategoryRepository $categoryRepository,
    Menu_Data $menuData,
    FooterLinks_Data $footerLinksData
  ) {
    parent::__construct($repository, $textReplacer, $pageHelper, $contextController, $dataFactory, $iconService, $pageRepository, $categoryRepository);
    $this->menuData = $menuData;
    $this->footerLinksData = $footerLinksData;
  }

  public function get_data($page_id = null): array
  {
    $business = $this->pageHelper->get_options_business();
    $phone = $business['telefonnummer'] ?? '+43 1 234 5678';
    $email = $business['e-mail'] ?? $business['email'] ?? 'office@example.com';
    $whatsapp_number = $business['mobilnummer'] ?? $phone; // Fallback
    $branche = $business['branche'] ?? '';
    $location = $this->pageHelper->get_location();

    return [
      'site_name' => get_bloginfo('name'),
      'site_description' => get_bloginfo('description'),
      'home_url' => home_url('/'),
      'phone_number' => $phone,
      'email' => $email,
      'whatsapp_url' => "https://api.whatsapp.com/send?phone=" . preg_replace('/[^0-9]/', '', $whatsapp_number),
      'location' => $location,
      'branche' => $branche,
      'search_query' => get_search_query(),
      'links' => $this->get_seo_links($phone, $email, $whatsapp_number, $location),
      'services' => $this->menuData->get_primary_menu_items(),
      'service_categories' => $this->menuData->get_service_categories(),
      'districts' => $this->menuData->get_districts_list(),
      'legal_links' => $this->footerLinksData->get_data()['links'] ?? [],
      'is_front_page' => is_front_page(),
    ];
  }

  private function get_seo_links($phone, $email, $whatsapp, $location): array
  {
    $links = [
      'email' => [
        'href' => "mailto:$email",
        'text' => $email,
        'title' => "Schreiben Sie uns eine E-Mail und vereinbaren Sie einen unverbindlichen Besichtigungstermin mit [SITE_TITLE] für [DL] in [ORT]",
        'aria_label' => 'E-Mail schreiben',
        'target' => '',
        'rel' => 'nofollow',
        'icon' => 'email'
      ],
      'phone' => [
        'href' => 'tel:' . esc_attr($phone),
        'text' => $phone,
        'title' => 'Jetzt anrufen und Termin mit [SITE_TITLE] für [DL] in [ORT] vereinbaren',
        'aria_label' => 'Telefonnummer anrufen',
        'target' => '',
        'rel' => 'nofollow',
        'icon' => 'telefon'
      ],
      'whatsapp' => [
        'href' => "https://api.whatsapp.com/send?phone=" . preg_replace('/[^0-9]/', '', $whatsapp),
        'text' => 'WhatsApp',
        'title' => "Kontaktieren Sie [SITE_TITLE] auch per WhatsApp, schreiben Sie uns und schicken Sie Fotos für [DL] in [ORT]",
        'aria_label' => 'WhatsApp Nachricht senden',
        'target' => '_blank',
        'rel' => 'noopener noreferrer',
        'icon' => 'whatsapp'
      ],
      'contact' => [
        'href' => '/kontakt/',
        'text' => 'Kontakt',
        'title' => "Verwenden Sie unser Kontaktformular, schicken Sie uns Fotos, vereinbaren Sie einen Rückruf,... mit [SITE_TITLE] für eine [DL] in [ORT]",
        'aria_label' => 'Zum Kontaktformular wechseln',
        'target' => '',
        'rel' => 'nofollow',
        'icon' => 'formular'
      ]
    ];

    // Process replacements using TextReplacementService
    foreach ($links as $key => &$link) {
      // Use prozess_data to handle [DL], [ORT], [SITE_TITLE] + Synonyms
      // The third argument "true" strips tags, which is good for attributes
      $link['title'] = $this->textReplacer->prozess_data($link['title'], [], true);
      $link['title'] = esc_attr($link['title']);
    }

    return $links;
  }
}
