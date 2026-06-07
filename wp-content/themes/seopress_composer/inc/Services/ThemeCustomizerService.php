<?php

namespace SeopressComposer\Services;

class ThemeCustomizerService
{
  public function __construct()
  {
    add_action('wp_head', [$this, 'output_css_variables'], 5);
    add_filter('upload_mimes', [$this, 'add_svg_mime_type']);
  }

  /**
   * Allow SVG uploads.
   */
  public function add_svg_mime_type($mimes)
  {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
  }

  public function output_css_variables()
  {

    // Colors
    $scheme = get_field('color_scheme', 'option') ?: 'default';

    // Fonts
    $font_body = get_field('font_body', 'option') ?: 'system-ui';
    $font_body_weight = get_field('font_body_weight', 'option') ?: '400';
    $font_heading = get_field('font_heading', 'option') ?: 'system-ui';
    $font_heading_weight = get_field('font_heading_weight', 'option') ?: '700';
    $font_logo = get_field('font_logo', 'option') ?: 'Montserrat';
    $font_logo_weight = get_field('font_logo_weight', 'option') ?: '900';

    $font_stack_map = [
      'system-ui' => 'ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif',
      'Inter' => '"Inter", sans-serif',
      'Roboto' => '"Roboto", sans-serif',
      'Open Sans' => '"Open Sans", sans-serif',
      'Lato' => '"Lato", sans-serif',
      'Montserrat' => '"Montserrat", sans-serif',
      'Playfair Display' => '"Playfair Display", serif',
      'Merriweather' => '"Merriweather", serif',
      'Outfit' => '"Outfit", sans-serif',
      'Plus Jakarta Sans' => '"Plus Jakarta Sans", sans-serif',
      'Sora' => '"Sora", sans-serif',
    ];

    $css_font_body = $font_stack_map[$font_body] ?? $font_stack_map['system-ui'];
    $css_font_heading = $font_stack_map[$font_heading] ?? $font_stack_map['system-ui'];
    $css_font_logo = $font_stack_map[$font_logo] ?? $font_stack_map['Montserrat'];

    // Google Fonts are now loaded locally via Vite/assets (see assets/js/main.js)
    // No external requests to fonts.googleapis.com

    $schemes = [
      'default' => [
        'primary' => '#0d6efd',
        'secondary' => '#6c757d',
        'accent' => '#6610f2',
        'neutral' => '#F2F3F4',
        'background_primary' => '#F2F3F4',
        'info' => '#0dcaf0',
        'success' => '#198754',
        'warning' => '#ffc107',
        'danger' => '#dc3545',
      ],
      'modern' => [
        'primary' => '#6366f1', // Indigo 500
        'secondary' => '#64748b', // Slate 500
        'accent' => '#8b5cf6', // Violet 500
        'neutral' => '#f1f5f9', // Slate 100
        'background_primary' => '#ffffff',
        'info' => '#06b6d4', // Cyan 500
        'success' => '#22c55e', // Green 500
        'warning' => '#eab308', // Yellow 500
        'danger' => '#ef4444', // Red 500
      ],
      'ocean' => [
        'primary' => '#0891b2', // Cyan 600
        'secondary' => '#475569', // Slate 600
        'accent' => '#06b6d4', // Cyan 500
        'neutral' => '#e0f2fe', // Sky 100
        'background_primary' => '#ffffff',
        'info' => '#0284c7', // Sky 600
        'success' => '#14b8a6', // Teal 500
        'warning' => '#f59e0b', // Amber 500
        'danger' => '#ef4444', // Red 500
      ],
      'sunset' => [
        'primary' => '#f97316', // Orange 500
        'secondary' => '#be123c', // Rose 700
        'accent' => '#ec4899', // Pink 500
        'neutral' => '#fff7ed', // Orange 50
        'background_primary' => '#ffffff',
        'info' => '#06b6d4', // Cyan 500
        'success' => '#22c55e', // Green 500
        'warning' => '#fbbf24', // Amber 400
        'danger' => '#dc2626', // Red 600
      ],
      'forest' => [
        'primary' => '#15803d', // Green 700
        'secondary' => '#44403c', // Stone 700
        'accent' => '#84cc16', // Lime 500
        'neutral' => '#f0fdf4', // Green 50
        'background_primary' => '#ffffff',
        'info' => '#0ea5e9', // Sky 500
        'success' => '#16a34a', // Green 600
        'warning' => '#eab308', // Yellow 500
        'danger' => '#dc2626', // Red 600
      ],
      'professional' => [
        'primary' => '#1e40af', // Blue 800
        'secondary' => '#374151', // Gray 700
        'accent' => '#3b82f6', // Blue 500
        'neutral' => '#f3f4f6', // Gray 100
        'background_primary' => '#ffffff',
        'info' => '#0ea5e9', // Sky 500
        'success' => '#10b981', // Emerald 500
        'warning' => '#f59e0b', // Amber 500
        'danger' => '#ef4444', // Red 500
      ],
      'elegant' => [
        'primary' => '#1f2937', // Gray 800
        'secondary' => '#6b7280', // Gray 500
        'accent' => '#d97706', // Amber 600 (Gold)
        'neutral' => '#f9fafb', // Gray 50
        'background_primary' => '#ffffff',
        'info' => '#3b82f6', // Blue 500
        'success' => '#10b981', // Emerald 500
        'warning' => '#f59e0b', // Amber 500
        'danger' => '#ef4444', // Red 500
      ],
      'vibrant' => [
        'primary' => '#dc2626', // Red 600
        'secondary' => '#7c3aed', // Violet 600
        'accent' => '#f59e0b', // Amber 500
        'neutral' => '#fef2f2', // Red 50
        'background_primary' => '#ffffff',
        'info' => '#3b82f6', // Blue 500
        'success' => '#10b981', // Emerald 500
        'warning' => '#f59e0b', // Amber 500
        'danger' => '#dc2626', // Red 600
      ],
      'minimal' => [
        'primary' => '#18181b', // Zinc 900
        'secondary' => '#71717a', // Zinc 500
        'accent' => '#3f3f46', // Zinc 700
        'neutral' => '#fafafa', // Zinc 50
        'background_primary' => '#ffffff',
        'info' => '#3b82f6', // Blue 500
        'success' => '#10b981', // Emerald 500
        'warning' => '#f59e0b', // Amber 500
        'danger' => '#ef4444', // Red 500
      ],
      'nature' => [
        'primary' => '#16a34a', // Green 600
        'secondary' => '#57534e', // Stone 500
        'accent' => '#ca8a04', // Yellow 600
        'neutral' => '#f5f5f4', // Stone 100
        'background_primary' => '#ffffff',
        'info' => '#0ea5e9', // Sky 500
        'success' => '#22c55e', // Green 500
        'warning' => '#eab308', // Yellow 500
        'danger' => '#ef4444', // Red 500
      ],
      'warm' => [
        'primary' => '#ea580c', // Orange 600
        'secondary' => '#78716c', // Stone 500
        'accent' => '#d97706', // Amber 600
        'neutral' => '#fafaf9', // Stone 50
        'background_primary' => '#ffffff',
        'info' => '#0ea5e9',
        'success' => '#22c55e',
        'warning' => '#f59e0b',
        'danger' => '#ef4444',
      ],
      'antique' => [
        'primary' => '#1a120b', // Deep Black/Brown
        'secondary' => '#996515', // Golden Brown
        'accent' => '#A67C00', // Dark Antique Gold
        'neutral' => '#C0C0C0', // Silver
        'background_primary' => '#fdfbf7', // Off-white/Cream
        'info' => '#3b82f6',
        'success' => '#10b981',
        'warning' => '#FFD700',
        'danger' => '#ef4444',
      ],
    ];

    if ($scheme === 'custom') {
      $colors = [
        'primary' => get_field('primary', 'option') ?: '#0d6efd',
        'secondary' => get_field('secondary', 'option') ?: '#6c757d',
        'accent' => get_field('accent', 'option') ?: '#6610f2',
        'neutral' => get_field('neutral', 'option') ?: '#F2F3F4',
        'background_primary' => get_field('background_primary', 'option') ?: '#F2F3F4',
        'info' => get_field('info', 'option') ?: '#0dcaf0',
        'success' => get_field('success', 'option') ?: '#198754',
        'warning' => get_field('warning', 'option') ?: '#ffc107',
        'danger' => get_field('danger', 'option') ?: '#dc3545',
      ];
    } else {
      $colors = $schemes[$scheme] ?? $schemes['default'];
    }

    echo "<style>:root {\n";
    foreach ($colors as $name => $value) {
      $css_var_name = str_replace('_', '-', $name);
      echo "  --color-{$css_var_name}: {$value};\n";

      // Add RGB version for transparent effects
      if (strpos($value, '#') === 0) {
        $hex = ltrim($value, '#');
        if (strlen($hex) == 3) {
          $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
          $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
          $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
          $r = hexdec(substr($hex, 0, 2));
          $g = hexdec(substr($hex, 2, 2));
          $b = hexdec(substr($hex, 4, 2));
        }
        echo "  --color-{$css_var_name}-rgb: {$r}, {$g}, {$b};\n";
      }
    }

    echo "  --font-body: {$css_font_body};\n";
    echo "  --font-body-weight: {$font_body_weight};\n";
    echo "  --font-heading: {$css_font_heading};\n";
    echo "  --font-heading-weight: {$font_heading_weight};\n";
    echo "  --font-logo: {$css_font_logo};\n";
    echo "  --font-logo-weight: {$font_logo_weight};\n";

    echo "}</style>\n";
  }
}
