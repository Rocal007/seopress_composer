<?php

namespace SeopressComposer\Services;

class FaviconService
{
  private IconService $iconService;

  public function __construct(
    IconService $iconService
  ) {
    $this->iconService = $iconService;
  }

  public function output(): void
  {
    $icon_svg = $this->get_favicon_svg();

    if (!$icon_svg) {
      return;
    }

    $base64_svg = base64_encode($icon_svg);
    $data_uri = 'data:image/svg+xml;base64,' . $base64_svg;

    echo '<link rel="icon" type="image/svg+xml" href="' . esc_attr($data_uri) . '">' . "\n";
  }

  private function get_favicon_svg(): string
  {
    // 1. Get Icon Name from Start Page (Local only)
    $page_id = get_option('page_on_front');
    $icon_name = $this->get_icon_name($page_id);

    // 2. Get Primary Color
    $primary_color = $this->get_primary_color();

    // 3. Get SVG Content
    // We use unique temporary classes to target the elements we want to colorize.
    $svg = $this->iconService->getIcon($this->normalizeIconName($icon_name), [
      'inner_class' => 'favicon-inner',
      'no_border' => false
    ]);

    // 4. Inject Color directly into SVG
    // Replace class attributes with fill attribute
    $svg = str_replace(
      ['class="rahmen"', 'class="favicon-inner"'],
      ['fill="' . $primary_color . '"', 'fill="' . $primary_color . '"'],
      $svg
    );

    // Also remove any other potential class attributes or styling that might interfere?
    // Ensure the SVG namespace is correct for data URI? Usually mostly fine.

    return $svg;
  }

  private function get_primary_color(): string
  {
    $color = get_field('primary', 'option');
    if ($color) {
      return $color;
    }
    return '#0d6efd'; // Fallback
  }

  private function get_icon_name($page_id): string
  {
    $icon_val = 'stecker'; // Default

    // Try Local Field
    $local_icon = get_field("hauptseiten_icon", $page_id);
    if ($local_icon) {
      if (is_array($local_icon)) {
        $icon_val = $local_icon['value'] ?? $local_icon[0] ?? $icon_val;
      } else {
        $icon_val = (string) $local_icon;
      }
      return $icon_val;
    }

    return $icon_val ?: 'stecker';
  }

  private function normalizeIconName(string $icon_name): string
  {
    // Copied from Logo_Data for consistency
    $icon_map = [
      'entrümpelungen' => 'entruempelung',
      'Entrümpelungen' => 'entruempelung',
      'wohnungsauflösungen' => 'wohnungsaufloesungen',
      'Wohnungsauflösungen' => 'wohnungsaufloesungen',
      'wohnungsauflösung' => 'wohnungsaufloesungen',
      'Wohnungsauflösung' => 'wohnungsaufloesungen',
      'kellerräumung' => 'kellerraeumung',
      'Kellerräumung' => 'kellerraeumung',
      'räumung' => 'raeumung_1',
      'Räumung' => 'raeumung_1',
      'Räumung (Variante 2)' => 'raeumung_2',
      'räumung (variante 2)' => 'raeumung_2',
      'büroräumung' => 'bueroraeumung',
      'Büroräumung' => 'bueroraeumung',
      'hotelräumung' => 'hotelraeumung',
      'Hotelräumung' => 'hotelraeumung',
      'verlassenschaft (variante 2)' => 'verlassenschaft_2',
      'Verlassenschaft (Variante 2)' => 'verlassenschaft_2',
      'übersiedelung' => 'uebersiedelung',
      'Übersiedelung' => 'uebersiedelung',
      'antiquitäten' => 'antiquitaeten_ankauf',
      'Antiquitäten' => 'antiquitaeten_ankauf',
      'antiquitäten ankauf' => 'antiquitaeten_ankauf',
      'Antiquitäten Ankauf' => 'antiquitaeten_ankauf',
      'dachböden' => 'dachboden',
      'Dachböden' => 'dachboden',
      'Generation' => 'generation',
      'Generation' => 'generation',
      'stecker' => 'stecker',
      'Stecker' => 'stecker',
      'suche' => 'suche',
      'Suche' => 'suche',
    ];

    if (isset($icon_map[$icon_name]))
      return $icon_map[$icon_name];
    $normalized = strtolower($icon_name);
    if (isset($icon_map[$normalized]))
      return $icon_map[$normalized];

    $normalized = str_replace(['ü', 'ä', 'ö', 'ß'], ['ue', 'ae', 'oe', 'ss'], $normalized);
    return $normalized;
  }
}
