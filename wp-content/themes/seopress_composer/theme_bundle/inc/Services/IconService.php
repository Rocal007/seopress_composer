<?php

namespace SeopressComposer\Services;

/**
 * IconService - Central service for theme SVG icons.
 * Refactored to use file-based lazy loading from assets/icons/.
 */
class IconService
{
  private static ?IconService $instance = null;
  private array $icons = [];
  private string $iconDirectory;

  private function __construct()
  {
    $this->iconDirectory = get_template_directory() . '/assets/icons/';
  }

  public static function getInstance(): IconService
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  /**
   * Main method to retrieve an icon.
   * Checks memory cache, then file system.
   * 
   * @param string $iconName Name of the icon
   * @param array $options ['invert' => bool, 'border' => bool, 'size' => 'sm'|'md'|'lg'|'xl']
   */
  public function getIcon(string $iconName, array $options = []): string
  {
    // Normalize first to ensure we hit the correct file/key
    $iconName = $this->normalizeIconName($iconName);

    // 1. Try Memory Cache
    if (!isset($this->icons[$iconName])) {
      // 2. Try File System
      $filePath = $this->iconDirectory . $iconName . '.svg';
      if (file_exists($filePath)) {
        $this->icons[$iconName] = file_get_contents($filePath);
      } else {
        // Fallback to dummy if not found
        $filePath = $this->iconDirectory . 'dummy.svg';
        $this->icons[$iconName] = file_exists($filePath) ? file_get_contents($filePath) : '';
      }
    }

    $icon = $this->icons[$iconName];

    // 3. Styling Logic (Same as before)
    // Illustration Class
    $innerClass = 'illu';
    if (!empty($options['invert'])) {
      $innerClass = 'illu-invert';
    }
    if (!empty($options['inner_class'])) {
      $innerClass = $options['inner_class'];
    }

    // Frame logic moved to wrapper container in getIconHtml

    // Size Class
    $sizeClass = '';
    if (!empty($options['size'])) {
      $sizeClass = match ($options['size']) {
        'sm' => ' w-6 h-6',
        'md' => ' w-12 h-12',
        'mdl' => ' w-20 h-20',
        'lg' => ' w-24 h-24',
        'xl' => ' w-32 h-32',
        default => ''
      };
    }

    // Replace Placeholders
    // Replace Placeholders - $rahmen_class is now handled via wrapper classes
    $icon = str_replace('$illu_class', $innerClass, $icon);

    // Size Style
    $svgInlineStyle = '';
    if (!empty($options['size'])) {
      $svgInlineStyle = match ($options['size']) {
        'sm' => 'width: 24px; height: 24px;',
        'md' => 'width: 48px; height: 48px;',
        'mdl' => 'width: 80px; height: 80px;',
        'lg' => 'width: 96px; height: 96px;',
        'xl' => 'width: 128px; height: 128px;',
        default => 'width: 100%; height: 100%;'
      };
    }

    // Inject Size Class and Inline Style
    if ($sizeClass && strpos($icon, '<svg') !== false) {
      // Inject inline style
      if ($svgInlineStyle) {
          if (preg_match('/<svg[^>]*style="[^"]*"[^>]*>/i', $icon)) {
              $icon = preg_replace('/(<svg[^>]*style=")([^"]*)(")/i', '${1}${2}; ' . $svgInlineStyle . '${3}', $icon, 1);
          } else {
              $icon = str_replace('<svg ', '<svg style="' . $svgInlineStyle . '" ', $icon);
          }
      }

      // Check if class attribute exists
      if (preg_match('/<svg[^>]*class="[^"]*"[^>]*>/i', $icon)) {
        // Append to existing class
        $icon = preg_replace('/(<svg[^>]*class=")([^"]*)(")/i', '${1}${2} ' . $sizeClass . '${3}', $icon, 1);
      } else {
        // Add new class attribute
        $icon = str_replace('<svg ', '<svg class="' . trim($sizeClass) . '" ', $icon);
      }
    }

    // Inject Title for SEO
    $iconTitle = $options['title'] ?? ucfirst(str_replace('_', ' ', $iconName));
    if (!empty($iconTitle) && strpos($icon, '<title>') === false && strpos($icon, '<svg') !== false) {
      $titleTag = '<title>' . esc_html($iconTitle) . '</title>';
      $icon = preg_replace('/<svg([^>]*)>/i', '<svg$1 role="img" aria-label="' . esc_attr($iconTitle) . '">' . $titleTag, $icon, 1);
    }

    return $icon;
  }

  /**
   * Resolves an icon identifier (URL or Name) and returns the standard HTML.
   * Handles normalization of keys and URL detection.
   */
  public function getIconHtml($identifier, string $title = '', array $options = []): string
  {
    if (empty($identifier)) {
      return '';
    }

    $identifier = is_array($identifier) ? ($identifier['url'] ?? '') : $identifier;

    // 1. Check if it's a URL (Image)
    if (filter_var($identifier, FILTER_VALIDATE_URL) || strpos($identifier, '/') !== false) {
      return '<img src="' . esc_url($identifier) . '" class="w-full h-full object-contain" alt="' . esc_attr($title) . ' Icon" title="' . esc_attr($title) . '" />';
    }

    // 2. Normalize Key (if it's a name)
    $icon_key = $this->normalizeIconName($identifier);

    // 3. Get SVG
    // Merge default options
    $defaultOptions = ['primary_background' => true];
    if (!empty($title)) {
      $defaultOptions['title'] = $title;
    }
    $finalOptions = array_merge($defaultOptions, $options);

    $svg = $this->getIcon($icon_key, $finalOptions);

    // 4. Return wrapped SVG if valid
    if ($svg && strpos($svg, '<svg') !== false) {
      $wrapperSize = 'w-5 h-5';
      $wrapperStyle = 'width: 20px; height: 20px;';
      if (!empty($options['size'])) {
        $wrapperSize = match ($options['size']) {
          'sm' => 'w-6 h-6',
          'md' => 'w-10 h-10',
          'lg' => 'w-24 h-24',
          'xl' => 'w-32 h-32',
          default => 'w-5 h-5'
        };
        $wrapperStyle = match ($options['size']) {
          'sm' => 'width: 24px; height: 24px;',
          'md' => 'width: 40px; height: 40px;',
          'lg' => 'width: 96px; height: 96px;',
          'xl' => 'width: 128px; height: 128px;',
          default => 'width: 20px; height: 20px;'
        };
      }

      // Override wrapper size if passed in options specifically for wrapper
      if (!empty($options['wrapper_class'])) {
        $wrapperSize = $options['wrapper_class']; // Be careful with this, handled by merging usually
        // Note: we can't easily deduce inline style from arbitrary classes, so we rely on the class here
      }

      // Wrapper styling for borders/bg
      $styleClasses = '';
      if (!empty($options['border'])) {
        $styleClasses .= ' border border-2 border-primary rounded-full p-2';
      }
      if (!empty($options['primary_background'])) {
        $styleClasses .= ' bg-primary text-white rounded-full p-2';
      }
      if (!empty($options['secondary_background'])) {
        $styleClasses .= ' bg-secondary text-white rounded-full p-2';
      }

      $classes = $wrapperSize . ' shrink-0 flex items-center justify-center transition-all duration-300' . $styleClasses;

      return '<div class="' . $classes . '" style="' . esc_attr($wrapperStyle) . '">' . $svg . '</div>';
    }

    return '';
  }

  /**
   * Normalizes an icon name or term to a valid icon key.
   */
  public function normalizeIconName(string $name): string
  {
    $icon_map = [
      'Entrümpelungen' => 'entruempelung',
      'Wohnungsauflösungen' => 'wohnungsaufloesung',
      'Kellerräumung' => 'kellerraeumung',
      'Räumung' => 'entruempelung',
      'Büroräumung' => 'bueroraeumung',
      'Hotelräumung' => 'hotelraeumung',
      'Übersiedelung' => 'uebersiedelung',
      'Antiquitäten' => 'antiquitaeten_ankauf',
      'Festpreisangebot' => 'fixpreis',
      'Festpreisangebot (Variante 1)' => 'fixpreis',
      'unverbindliches Angebot' => 'fixpreis',
      'Angebot' => 'fixpreis',
      'Besichtigung' => 'besichtigung',
      'besichtigung' => 'besichtigung',
      'Kostenlose Besichtigung' => 'besichtigung',
      'Besenreine Übergabe' => 'reinigung',
      'Besen' => 'reinigung',
      'Reinigung' => 'reinigung',
      'gründliche Reinigung' => 'reinigung',
      'Wertausgleich' => 'antiquitaeten_ankauf',
      'fachliche Beratung' => 'antiquitaeten_ankauf',
      'Termine' => 'uhr',
      'Durchführung' => 'transport',
      'goldankauf' => 'gold',
      'tipp' => 'faq',
      'antik_muenzen' => 'muenzen',
      'antiquitaeten_kunst' => 'antiquitaeten_gemaelde',
      'asiatika_afrikana' => 'asiatika',
      'einrichtung_design' => 'design',
      'historische_objekte' => 'antiquitaeten_ankauf',
      'sammlerstuecke_technik' => 'varia',
      'schmuck_edelmetalle' => 'schmuck',
      'varia_ankauf' => 'varia',
      'kostenlose_besichtigung' => 'besichtigung',
      'jahrelange_erfahrung' => 'generation',
      'tradition' => 'generation',
      '3_generation' => 'generation',
      'kompetenz_in_3_generation' => 'generation',
      'festpreisangebot_variante_3' => 'fixpreis',
      'immobilienmakler' => 'makler'
    ];

    if (isset($icon_map[$name])) {
      return $icon_map[$name];
    }

    // Manual Normalization
    $normalized = $name;
    $normalized = str_replace(['Ä', 'Ö', 'Ü', 'ß'], ['Ae', 'Oe', 'Ue', 'ss'], $normalized);
    $normalized = mb_strtolower($normalized);
    $normalized = str_replace(['ä', 'ö', 'ü'], ['ae', 'oe', 'ue'], $normalized);
    $normalized = str_replace(' ', '_', $normalized);
    $normalized = preg_replace('/[^a-z0-9_]/', '', $normalized);

    if (isset($icon_map[$normalized])) {
      return $icon_map[$normalized];
    }

    return $normalized;
  }

  public function getAvailableIcons(): array
  {
    // Scan directory
    $files = glob($this->iconDirectory . '*.svg');
    $icons = [];
    foreach ($files as $file) {
      $icons[] = basename($file, '.svg');
    }
    return $icons;
  }
}
