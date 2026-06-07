<?php

namespace SeopressComposer\Models;

use SeopressComposer\Services\TextReplacementService;
use SeopressComposer\Repositories\RemoteDataRepository;
use SeopressComposer\Helpers\PageHelper;
use SeopressComposer\Controllers\ContextController;
use SeopressComposer\Factories\DataFactory;

use SeopressComposer\Services\IconService;

class TopPicture_Data extends BaseModel
{

  public function get_data($page_id = null): array
  {
    $page_id = $this->resolve_page_id($page_id);

    $remote_url = $this->pageHelper->get_remote_url($page_id);

    // Fetch remote data
    $all = null;
    if ($remote_url) {
      $all = $this->repository->fetch($remote_url);
    }

    return $this->prepare_top_picture($page_id, $remote_url, $all);
  }

  private function prepare_top_picture($page_id, $remote_url, $all = null): array
  {
    // Get Data from PageHelper (Local ACF + Global Settings)
    $hero_data = $this->pageHelper->get_hero_data($page_id);
    $options_business = $this->pageHelper->get_options_business();

    // YOAST TITLE LOGIC via Repository
    // Always use the current Queried Object ID for SEO data to ensure it matches the URL/Page
    $repo_id = get_queried_object_id();
    $type = (is_category() || is_tag() || is_tax()) ? 'term' : 'post';

    $seo_title = $this->pageRepository->get_seo_title($repo_id, $type);

    // YOAST DESC LOGIC via Repository
    $seo_description = $this->pageRepository->get_seo_description($repo_id, $type);

    // Get category info
    $categories = get_the_category($page_id);
    $category_name = '';
    if ($categories) {
      $cat_id = $categories[0]->cat_ID;
      $category_title = get_cat_name($cat_id);
      $category_link = get_category_link($cat_id);
      $category_name = (has_category() || !is_category()) ?
        '<a href="' . esc_url($category_link) . '">' . esc_html($category_title) . '</a>' :
        $this->pageHelper->get_location();
    }

    // IMAGE LOGIC - PRIORITY ORDER:
    // 1. Category Featured Image (for category pages) - HIGHEST PRIORITY
    // 2. PageHelper (ACF/Global)
    // 3. Featured Image
    // 4. Remote Data

    $image = '';

    // 1. For category pages, try category featured image FIRST
    if (is_category() || is_tax('service_category')) {
      $cat_id = get_queried_object_id();
      $category = get_term($cat_id);

      if ($category && !is_wp_error($category)) {
        // Try category_featured_image term meta
        $image_id = get_term_meta($cat_id, 'category_featured_image', true);
        if ($image_id) {
          $image = wp_get_attachment_image_url($image_id, 'large');
        }

        // If still no image, try remote API with improved matching
        if (!$image) {
          // Use services_api_endpoint from settings
          $api_url = $this->pageHelper->get_option('services_api_endpoint');

          // Fallback to constructing from basicremote if not set
          if (!$api_url) {
            $remote_base = $this->pageHelper->get_option('basicremote');
            if ($remote_base) {
              $remote_base = rtrim($remote_base, '/');
              $remote_base = preg_replace('#/wp-json/wp/v2/?$#', '', $remote_base);
              $api_url = $remote_base . '/wp-json/custom/v1/pages';
            }
          }

          if ($api_url) {
            $response = wp_remote_get($api_url, ['timeout' => 10]);

            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
              $body = wp_remote_retrieve_body($response);
              $data = json_decode($body, true);

              if (is_array($data)) {
                // Normalize category name for comparison
                $cat_name = html_entity_decode($category->name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $cat_name_lower = mb_strtolower($cat_name);
                $cat_slug = $category->slug;

                foreach ($data as $item) {
                  if (empty($item['services-cat-image'])) {
                    continue;
                  }

                  // Normalize API title
                  $api_title = isset($item['title']) ? html_entity_decode($item['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';
                  $api_title_lower = mb_strtolower($api_title);

                  if ($api_title === $cat_name || $api_title_lower === $cat_name_lower || (isset($normalized_cat_slug) && $normalized_cat_slug === $normalized_api_slug)) {
                    $cat_img_data = $item['services-cat-image'];
                    if (is_array($cat_img_data) || is_object($cat_img_data)) {
                        $cat_img_data = (array)$cat_img_data;
                        $image = $cat_img_data['full_webp'] ?? $cat_img_data['full'] ?? reset($cat_img_data);
                        $image_mobile = $cat_img_data['mobile_webp'] ?? $cat_img_data['mobile'] ?? '';
                    } else {
                        $image = $cat_img_data;
                        $image_mobile = '';
                    }
                    break;
                  }
                }
              }
            }
          }
        }
      }
    }

    // 2. Local ACF (Explicit check to avoid early global fallback)
    if (!$image) {
      $image = get_field('top_picture_image', $page_id);
    }

    // 3. Fallback to Global Settings (Site Settings)
    if (empty($image)) {
      $image = $this->pageHelper->get_option('hero_image_global');
    }

    // 4. Featured Image (Local)
    if (!$image && has_post_thumbnail($page_id)) {
      $image = get_the_post_thumbnail_url($page_id, 'large');
    }

    // 5. Parent Image (Recursive for API support)
    $post = get_post($page_id);
    $parent_id = $post ? $post->post_parent : 0;
    
    if (!$image && $parent_id) {
      // Check simple local first
      $image = get_the_post_thumbnail_url($parent_id, 'large');

      // If no local parent image, check parent's full data (API)
      if (!$image && $parent_id !== $page_id) {
        $parent_data = $this->get_data($parent_id);

        // Verify if parent found a specific image (not just global fallback if possible, 
        // but since get_data returns global fallback, we accept it as "Parent's Look")
        if (!empty($parent_data['image'])) {
          $image = $parent_data['image'];
        }
      }
    }

    // 6. Remote Data Fallback
    if (!$image && $all) {
      // Priority: services-cat-image
      if (!empty($all->{'services-cat-image'})) {
        $cat_img_data = $all->{'services-cat-image'};
        if (is_array($cat_img_data) || is_object($cat_img_data)) {
            $cat_img_data = (array)$cat_img_data;
            $image = $cat_img_data['full_webp'] ?? $cat_img_data['full'] ?? reset($cat_img_data);
            if (empty($image_mobile)) {
                $image_mobile = $cat_img_data['mobile_webp'] ?? $cat_img_data['mobile'] ?? '';
            }
        } else {
            $image = $cat_img_data;
        }
      }

      // Check for better_featured_image
      if (!$image && isset($all->better_featured_image)) {
        if (isset($all->better_featured_image->media_details->sizes->top->source_url)) {
          $image = $all->better_featured_image->media_details->sizes->top->source_url;
        } elseif (isset($all->better_featured_image->source_url)) {
          $image = $all->better_featured_image->source_url;
        }
      }

      // Check for simple featured_image
      if (!$image && isset($all->featured_image_url)) {
        $image = $all->featured_image_url;
      }

      // ACF in remote
      if (!$image && !empty($all->acf->top_picture_image)) {
        $image = $all->acf->top_picture_image;
      }
    }

    // 7. Global Fallback (Last Resort)
    if (!$image) {
      $image = $hero_data['image']; // This contains the global settings fallback from PageHelper
    }

    // DEBUG: Check what type of page this is (Removed)

    // 3. For category pages, try category featured image
    if (!$image && (is_category() || is_tax('service_category'))) {
      $cat_id = get_queried_object_id();
      $category = get_term($cat_id);

      if ($category && !is_wp_error($category)) {
        // Try category_featured_image term meta
        $image_id = get_term_meta($cat_id, 'category_featured_image', true);
        if ($image_id) {
          $image = wp_get_attachment_image_url($image_id, 'large');
        }

        // If still no image, try remote API with improved matching
        if (!$image) {
          // Use services_api_endpoint from settings
          $api_url = $this->pageHelper->get_option('services_api_endpoint');

          // Fallback to constructing from basicremote if not set
          if (!$api_url) {
            $remote_base = $this->pageHelper->get_option('basicremote');
            if ($remote_base) {
              $remote_base = rtrim($remote_base, '/');
              $remote_base = preg_replace('#/wp-json/wp/v2/?$#', '', $remote_base);
              $api_url = $remote_base . '/wp-json/custom/v1/pages';
            }
          }

          if ($api_url) {
            $response = wp_remote_get($api_url, ['timeout' => 10]);

            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
              $body = wp_remote_retrieve_body($response);
              $data = json_decode($body, true);

              if (is_array($data)) {
                // Normalize category name for comparison
                $cat_name = html_entity_decode($category->name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $cat_name_lower = mb_strtolower($cat_name);
                $cat_slug = $category->slug;

                // TEMPORARY DEBUG (Removed)

                foreach ($data as $item) {
                  if (empty($item['services-cat-image'])) {
                    continue;
                  }

                  // Normalize API title
                  $api_title = isset($item['title']) ? html_entity_decode($item['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';
                  $api_title_lower = mb_strtolower($api_title);

                  // DEBUG (Removed)

                  if ($api_title === $cat_name) {
                    $image = $item['services-cat-image'];
                    break;
                  }

                  // Try case-insensitive match
                  if ($api_title_lower === $cat_name_lower) {
                    $image = $item['services-cat-image'];
                    break;
                  }

                  // Try slug-based match (handle ae vs a variations)
                  if (isset($item['url'])) {
                    $api_slug = basename(parse_url($item['url'], PHP_URL_PATH));
                    $normalized_cat_slug = str_replace('ae', 'a', $cat_slug);
                    $normalized_api_slug = str_replace('ae', 'a', $api_slug);

                    if ($normalized_cat_slug === $normalized_api_slug) {
                      $image = $item['services-cat-image'];
                      break;
                    }
                  }
                }

                if (!$image) {
                }
              }
            }
          }
        }
      }
    }

    // 8. HOMEPAGE IMAGE FALLBACK — Always have an image, never show an empty hero
    // Fetch the homepage's remote data and use its hero image as the ultimate fallback
    if (!$image) {
      $home_id = (int) get_option('page_on_front');
      if ($home_id && $home_id !== (int) $page_id) {
        // Try homepage featured image first (fastest)
        $image = get_the_post_thumbnail_url($home_id, 'large');

        // If no local homepage image, fetch from remote API
        if (!$image) {
          $home_remote_url = $this->pageHelper->get_remote_url($home_id, false);
          if ($home_remote_url) {
            $home_data = $this->repository->fetch($home_remote_url);
            // Handle array response
            if (is_array($home_data) && !empty($home_data)) {
              $home_data = is_object($home_data[0]) ? $home_data[0] : (object) $home_data[0];
            }
            if ($home_data) {
              // Try services-cat-image
              if (!empty($home_data->{'services-cat-image'})) {
                $home_img = $home_data->{'services-cat-image'};
                $image = is_string($home_img) ? $home_img : (is_array($home_img) || is_object($home_img) ? ((array)$home_img)['full_webp'] ?? ((array)$home_img)['full'] ?? reset((array)$home_img) : '');
              }
              // Try better_featured_image
              if (!$image && isset($home_data->better_featured_image)) {
                if (isset($home_data->better_featured_image->media_details->sizes->top->source_url)) {
                  $image = $home_data->better_featured_image->media_details->sizes->top->source_url;
                } elseif (isset($home_data->better_featured_image->source_url)) {
                  $image = $home_data->better_featured_image->source_url;
                }
              }
              // Try featured_image_url
              if (!$image && isset($home_data->featured_image_url)) {
                $image = $home_data->featured_image_url;
              }
              // Try ACF
              if (!$image && !empty($home_data->acf->top_picture_image)) {
                $image = $home_data->acf->top_picture_image;
              }
            }
          }
        }
      }
    }

    // CLAIM LOGIC: Page Specific > Yoast Description > Global Setting

    // 1. Check strict page specific ACF claim (bypassing PageHelper's global merge for a moment)
    // Need to handle category ID formatting for this check since PageHelper won't help
    $claim_id = is_category() ? 'category_' . $page_id : $page_id;
    $specific_claim = get_field('top_picture_claim', $claim_id);

    $claim = $specific_claim;

    // 2. If no specific claim, use Yoast Description
    if (empty($claim)) {
      $claim = $seo_description;
    }

    // 3. If no Yoast Description, use Global Fallback (from PageHelper)
    if (empty($claim)) {
      $claim = $hero_data['claim']; // This might contain global setting
    }

    // 4. Remote Data Fallback
    if (empty($claim) && $all && !empty($all->acf->claim)) {
      $claim = ucfirst(trim(strip_tags($all->acf->claim)));
    }

    // VARIANT LOGIC: Local > Global > Default
    $variant = get_field('hero_design_variant', $page_id) ?: 'default';
    if (!$variant || $variant === 'default') {
      $variant = $this->pageHelper->get_option('hero_design_variant_global') ?: 'default';
    }

    // TITLE LOGIC: SEO > Local > Page/Term Title > Global
    $local_hero_title = get_field('top_picture_title', $page_id);
    $global_hero_title = $this->pageHelper->get_option('hero_title_global');

    $is_term = is_category(); // re-declare for clarity
    $fallback_title = $is_term ? single_term_title('', false) : get_the_title($page_id);

    // Prioritize SEO Title (from Repository) as requested by user, then Local, then Fallback, then Global
    $final_title = $seo_title ?: ($local_hero_title ?: ($fallback_title ?: $global_hero_title));

    // Process replacements through TextReplacementService and safe-guard output
    $final_title = wp_kses_post($this->textReplacer->process($final_title ?? ''));
    $claim = esc_html($this->textReplacer->process($claim ?? ''));

    // Title Split Logic (moved from View)
    $title_prefix = $final_title;
    $title_suffix = '';

    // Check if title already has a suffix pattern
    // Matches " für ...", " in 1234..." (PLZ), or " - ..." (Separator)
    $pattern = '/(\s+für\s+.*$|\s+(?:in\s+)?\d{4}\s+.*$|\s+-\s+.*$)/iu';

    if (preg_match($pattern, $final_title, $matches)) {
      $title_suffix = trim($matches[0]);
      // Remove leading dash if present
      $title_suffix = ltrim($title_suffix, '- ');
      $title_prefix = trim(str_replace($matches[0], '', $final_title));
    }

    // If no suffix found, generate one dynamically
    if (empty($title_suffix)) {
      // Get company name from site settings
      $company_name = $this->pageHelper->get_option('company_name') ?: get_bloginfo('name');
      $location = $this->pageHelper->get_location();

      // Check for redundancy
      if (stripos($company_name, $location) !== false) {
        $title_suffix = $company_name;
      } else {
        $title_suffix = $company_name . ' in ' . $location;
      }
    }

    // Formatting: Keep "in Wien" as regular inline text (no sup wrapping)
    // The Hero component handles visual hierarchy via its own layout

    // Cache remote image locally for better SEO
    if (!empty($image)) {
        $clean_title_for_img = wp_strip_all_tags($final_title);
        $image = $this->getImageDownloadService()->get_local_image_url($image, $clean_title_for_img, $clean_title_for_img);
    }
    if (!empty($image_mobile)) {
        $clean_title_for_img_mobile = wp_strip_all_tags($final_title) . ' mobile';
        $image_mobile = $this->getImageDownloadService()->get_local_image_url($image_mobile, $clean_title_for_img_mobile, $clean_title_for_img_mobile);
    }

    // Create the top picture data array
    $top_picture = [
      "all" => $options_business,
      "image" => $image,
      "image_mobile" => $image_mobile ?? '',
      "title" => $final_title,
      "title_prefix" => $title_prefix,
      "title_suffix" => $title_suffix,
      "location" => $this->pageHelper->get_location(),
      "content" => '', // Descriptions often hidden in new design
      "claim" => $claim,
      "mobile_number" => $hero_data['mobile_number'] ?: ($options_business['telefonnummer'] ?? ''),
      "view_number" => $options_business["phone_number_view"] ?? '',
      "remote_page" => $remote_url,
      "category_name" => $category_name,
      // Button Data from PageHelper
      "btn2_text" => $hero_data['btn2_text'],
      "btn2_url" => $hero_data['btn2_url'],
      "phone_icon" => 'telefon',
      "btn2_icon" => 'formular',
      "variant" => $variant,
      "button_style" => $this->pageHelper->get_option('hero_button_style_global') ?: 'standard',
    ];

    return $top_picture;
  }
}
