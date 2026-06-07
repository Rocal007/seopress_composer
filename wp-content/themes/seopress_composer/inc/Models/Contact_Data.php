<?php

namespace SeopressComposer\Models;

use SeopressComposer\Helpers\PageHelper;

class Contact_Data
{
  private PageHelper $pageHelper;

  public function __construct(PageHelper $pageHelper)
  {
    $this->pageHelper = $pageHelper;
  }

  public function get_data($page_id = null): array
  {
    $businessOptions = $this->pageHelper->get_options_business() ?? [];

    $phone_number = $businessOptions['telefonnummer'] ?? '';
    $phone_number_view = $businessOptions['angezeigte_telefonnummer'] ?? $phone_number;
    $email = $businessOptions['e-mail'] ?? '';

    $address = [
      'street' => $businessOptions['strasse'] ?? '',
      'zip' => $businessOptions['plz'] ?? '',
      'city' => $businessOptions['ort'] ?? '',
    ];
    $uid = $businessOptions['uid'] ?? '';
    $firmenname = $businessOptions['firmenname'] ?? '';
    $firmenbuchnummer = $businessOptions['firmenbuchnummer'] ?? '';
    $gerichtsstand = $businessOptions['gerichtsstand'] ?? '';
    $inhaber = $businessOptions['inhaber'] ?? '';
    $gln = $businessOptions['gln'] ?? '';

    return [
      'site_name' => get_bloginfo(),
      'location' => $this->get_location(),
      'dl' => strip_tags(apply_filters('the_content', '[DL]')),
      'page_title' => get_the_title(),
      'address' => $address,
      'phone_number' => $phone_number,
      'phone_number_view' => $phone_number_view,
      'email' => $email,
      'uid' => $uid,
      'firmenname' => $firmenname,
      'firmenbuchnummer' => $firmenbuchnummer,
      'gerichtsstand' => $gerichtsstand,
      'inhaber' => $inhaber,
      'gln' => $gln,
      'contact_links' => [
        [
          'type' => 'phone',
          'title' => 'Jetzt anrufen und Termin mit %site_name% für %dl% in %location% vereinbaren',
          'href' => 'tel:' . esc_attr($phone_number),
          'icon' => 'Telefon',
          'text' => 'Anruf'
        ],
        [
          'type' => 'email',
          'title' => "Schreiben Sie uns eine E-Mail und vereinbaren Sie einen unverbindlichen Besichtigungstermin mit %site_name% für %dl% in %location%",
          'href' => "mailto:$email",
          'icon' => 'E-Mail',
          'text' => 'E-Mail'
        ],
        [
          'type' => 'form',
          'title' => "Verwenden Sie unser Kontaktformular, schicken Sie uns Fotos, vereinbaren Sie einen Rückruf,... mit %site_name% für eine %dl% in %location%",
          'href' => "/kontakt",
          'icon' => 'Formular',
          'text' => 'Kontakt'
        ],
        [
          'type' => 'whatsapp',
          'title' => "Kontaktieren Sie %site_name% auch per WhatsApp, schreiben Sie uns und schicken Sie Fotos für %dl% in %location%",
          'href' => "https://api.whatsapp.com/send?phone=$phone_number&text=Anfrage%20" . rawurlencode(get_the_title()),
          'icon' => 'Whatsapp',
          'text' => 'WhatsApp'
        ],
      ],
      'cta' => [
        'title' => 'Sie möchten Antiquitäten verkaufen oder kaufen?',
        'description' => 'Wir beraten Sie gerne unverbindlich vor Ort oder telefonisch.'
      ],
    ];
  }

  private function get_location(): string
  {
    return $this->pageHelper->get_location();
  }

  public function get_data_fixed_bar(): array
  {
    $data = $this->get_data();
    $site_name_safe = esc_attr($data['site_name']);
    $data['aria_nav_label'] = esc_attr('Wichtige Kontaktmöglichkeiten für ' . $data['site_name']);

    $replace_keys = ['%site_name%', '%dl%', '%location%'];
    $replace_values = [$site_name_safe, esc_html($data['dl']), esc_html($data['location'])];

    $config_map = [
      'phone' => ['aria_label' => esc_attr('Telefonnummer anrufen'), 'target_blank' => false, 'rel_attrs' => esc_attr('nofollow')],
      'whatsapp' => ['aria_label' => esc_attr('WhatsApp Nachricht senden'), 'target_blank' => true, 'rel_attrs' => esc_attr('noopener noreferrer')],
      'email' => ['aria_label' => esc_attr('E-Mail schreiben'), 'target_blank' => false, 'rel_attrs' => esc_attr('nofollow')],
      'form' => ['aria_label' => esc_attr('Zum Kontaktformular wechseln'), 'target_blank' => false, 'rel_attrs' => esc_attr('nofollow')],
      'location' => ['aria_label' => esc_attr('Standort auf Google Maps anzeigen'), 'target_blank' => true, 'rel_attrs' => esc_attr('noopener noreferrer')],
    ];

    $data['contact_links'] = array_map(function ($link) use ($replace_keys, $replace_values, $config_map) {
      $type = $link['type'];
      $link = array_merge($link, $config_map[$type] ?? $config_map['form']);
      $link['title'] = str_replace($replace_keys, $replace_values, $link['title'] ?? '');
      $link['title'] = esc_attr($link['title']);
      $link['href'] = esc_url($link['href']);
      return $link;
    }, $data['contact_links']);

    return $data;
  }
}
