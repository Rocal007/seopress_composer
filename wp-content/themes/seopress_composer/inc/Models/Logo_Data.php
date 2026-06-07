<?php

namespace SeopressComposer\Models;

class Logo_Data extends BaseModel
{
  public function get_data($page_id = null): array
  {
    if (!$page_id) {
      if (is_category() || is_tag() || is_tax()) {
        $page_id = get_queried_object();
      } else {
        $page_id = get_the_ID();
      }
    }

    return $this->fetch_logo_data($page_id);
  }

  private function fetch_logo_data($page_id): array
  {
    $remote_url = $this->pageHelper->get_remote_url(is_numeric($page_id) ? $page_id : null);
    $icon_name = $this->get_icon_name($remote_url, $page_id);

    // Use PageHelper for title cleaning
    $raw_title = '';
    if ($page_id instanceof \WP_Term) {
      $raw_title = get_bloginfo('name');
    } elseif (is_numeric($page_id) && $page_id > 0) {
      $raw_title = get_the_title($page_id);
    } else {
      // Fallback or current global post
      $raw_title = get_the_title();
    }
    $logo_title = $this->textReplacer->clean_title($raw_title);
    $sub_title = $this->textReplacer->clean_title($logo_title);

    $site_description = get_bloginfo('description');

    // Get Yoast title if available
    $sub_description = '';
    if (function_exists('YoastSEO')) {
      $sub_description = YoastSEO()->meta->for_current_page()->title ?? '';
    }

    // Use PageHelper for location and icon
    $locationData = $this->pageHelper->get_location(true);
    $business = $this->pageHelper->get_options_business();

    // Get SEO Title for the location if it's a category/term or has an ID
    $location_seo_title = $locationData['seo_title'];
    if (!empty($locationData['id'])) {
      $location_seo_title = $this->pageRepository->get_seo_title((int) $locationData['id'], 'term');
    }

    // Default Logo Structure
    $logoData = [
      "icon_svg" => $this->get_icon_svg($icon_name),
      "title" => $logo_title,
      "title_hover" => $logo_title, // Default hover title matches visible title
      "location" => $this->textReplacer->clean_location($locationData['name']),
      "location_seo_title" => $location_seo_title,
      "location_image" => $locationData['icon'],
      "location_link" => $locationData['link'],
      "content" => $sub_description,
      "font_family" => $this->pageHelper->get_option('font_logo') ?: 'Montserrat',
    ];

    // Specific Overrides
    if (is_single() && has_category()) {
      $logoData['title'] = get_bloginfo();
      $logoData['location'] = 'Blog';
      $logoData['content'] = $site_description;
    } elseif (is_search()) {
      $logoData['icon_svg'] = $this->get_icon_svg('Suche');
      $logoData['location'] = '';
      $logoData['content'] = $site_description;
    } elseif (has_category() && !is_category() && !is_single()) {
      $logoData['content'] = $sub_title;
    } elseif (is_front_page() || get_page_template_slug($page_id) === 'template-hauptseiten.php' || get_page_template_slug($page_id) === 'template-locations.php') {
      // Front Page & Main Pages Logic:
      // 1. Force location on map (use full name "Wien" instead of empty)
      $einsatzgebiet_option = $this->pageHelper->get_option('einsatzgebiet');
      if ($einsatzgebiet_option) {
        $logoData['location'] = $einsatzgebiet_option;

        // Force global map icon for consistency
        $global_icon = $this->pageHelper->get_option('bundesland_icon');
        if ($global_icon) {
          $icon_url = is_numeric($global_icon) ? wp_get_attachment_url($global_icon) : ($global_icon['url'] ?? $global_icon);
          if ($icon_url) {
            $logoData['location_image'] = $icon_url;
          }
        }

        // Force Title to Site Name (Start page style)
        $logoData['title'] = get_bloginfo('name');

        // Link map to Locations Page
        $locations_pages = get_pages([
          'meta_key' => '_wp_page_template',
          'meta_value' => 'template-locations.php',
          'number' => 1
        ]);
        if (!empty($locations_pages)) {
          $logoData['location_link'] = get_permalink($locations_pages[0]->ID);
        }
      }

      // 2. Add location to HOVER title, but keep visible title clean
      if ($einsatzgebiet_option && !str_contains($logoData['title_hover'], $einsatzgebiet_option)) {
        $logoData['title_hover'] .= ' ' . $einsatzgebiet_option;
      }
    }

    return $logoData;
  }


  /**
   * Get icon name from API or fallback
   */
  private function get_icon_name($remote_url, $page_id): string
  {
    $icon_val = '';

    // 1. Try Remote Data first
    if (!empty($remote_url)) {
      $all = $this->repository->fetch($remote_url);

      if (!empty($all->acf->hauptseiten_icon)) {
        $icon_data = $all->acf->hauptseiten_icon;

        if (is_object($icon_data)) {
          if (isset($icon_data->value)) {
            $icon_val = $icon_data->value;
          } else {
            $arr = (array) $icon_data;
            $icon_val = array_shift($arr);
          }
        } elseif (is_array($icon_data)) {
          $icon_val = $icon_data['value'] ?? $icon_data[0] ?? '';
        } else {
          $icon_val = (string) $icon_data;
        }
      }
    }

    // 2. Fallback to Page Title (if no remote icon found) - Ignoring Local DB settings
    if (empty($icon_val)) {
      $icon_val = get_the_title($page_id);
    }

    return $icon_val ?: 'stecker';
  }

  /**
   * Convert icon name to SVG markup using IconService
   */
  private function get_icon_svg(string $icon_name): string
  {
    // Normalize icon name (handle umlauts and variations)
    // Using default normalization now available in IconService
    $icon_key = $this->iconService->normalizeIconName($icon_name);

    // Get SVG with inverted colors for logo display
    return $this->iconService->getIcon($icon_key, ['invert' => true]);
  }
}
