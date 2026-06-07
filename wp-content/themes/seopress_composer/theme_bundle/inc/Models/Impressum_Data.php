<?php

namespace SeopressComposer\Models;

class Impressum_Data
{
  private SiteSettings_Data $siteSettings;

  public function __construct(SiteSettings_Data $siteSettings)
  {
    $this->siteSettings = $siteSettings;
  }

  public function get_data(): array
  {
    $settings = $this->siteSettings->get_data();

    return [
      'company_name' => $settings['inhaber'] ?? '',
      'industry' => $settings['branche'] ?? '',
      'street' => $settings['strasse'] ?? '',
      'zip' => $settings['plz'] ?? '',
      'city' => $settings['bundesland'] ?? '', // Often used as city context or region
      'country' => $settings['country'] ?? '',
      'email' => $settings['e-mail'] ?? '',
      'phone' => $settings['telefonnummer'] ?? '',
      'uid' => $settings['uid'] ?? '',
      'gln' => $settings['gln'] ?? '',
      'authority' => $settings['behoerde_gem_ecg'] ?? '', // Assuming this exists or falls back
      'copyright' => $settings['urheberrecht'] ?? '',
      'permissions' => $settings['berechtigungen'] ?? '',
      'platform_eu' => 'https://ec.europa.eu/consumers/odr/', // Standard value usually
    ];
  }
}
