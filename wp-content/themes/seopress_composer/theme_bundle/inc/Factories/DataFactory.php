<?php

namespace SeopressComposer\Factories;

use SeopressComposer\Repositories\RemoteDataRepository;
use SeopressComposer\Helpers\PageHelper;
use SeopressComposer\Services\TextReplacementService;

class DataFactory
{
  private RemoteDataRepository $repository;
  private PageHelper $pageHelper;
  private TextReplacementService $textReplacement;

  public function __construct(
    RemoteDataRepository $repository,
    PageHelper $pageHelper,
    TextReplacementService $textReplacement
  ) {
    $this->repository = $repository;
    $this->pageHelper = $pageHelper;
    $this->textReplacement = $textReplacement;
  }

  /**
   * Create card data from a WP_Post object
   * 
   * @param \WP_Post $post
   * @param string $badgeTaxonomy Taxonomy to use for badges (e.g. 'district' or 'service_category')
   * @return array
   */
  public function create(\WP_Post $post, string $badgeTaxonomy = ''): array
  {
    $current_id = $post->ID;
    $data_id = $this->pageHelper->check_page_id($current_id);

    // 1. Fetch Remote Data
    $remote_url = get_field('remote_page', $data_id);
    $remote_data = null;
    if ($remote_url) {
      $remote_data = $this->repository->fetch((string)$remote_url);
    }

    // 2. Fetch Badges (Taxonomy Terms)
    $badges = [];
    if ($badgeTaxonomy) {
      $terms = wp_get_post_terms($current_id, $badgeTaxonomy);
      if (!empty($terms) && !is_wp_error($terms)) {
        $badges = array_map(fn($t) => $t->name, $terms);
      }
    }

    // 3. Initialize Variables
    $image = '';
    $claim = '';
    $icon = '';
    $remote_excerpt = '';

    // 4. Process Remote Data
    if ($remote_data) {
      $post_obj = is_array($remote_data) ? ($remote_data[0] ?? null) : $remote_data;

      if ($post_obj) {
        // Image resolution
        if (!empty($post_obj->better_featured_image->media_details->sizes->medium->source_url)) {
          $image = $post_obj->better_featured_image->media_details->sizes->medium->source_url;
        } elseif (!empty($post_obj->better_featured_image->source_url)) {
          $image = $post_obj->better_featured_image->source_url;
        } elseif (!empty($post_obj->acf->top_picture_image)) {
          $img_data = $post_obj->acf->top_picture_image;
          $image = is_array($img_data) ? ($img_data['url'] ?? '') : $img_data;
        }

        // Claim resolution
        if (!empty($post_obj->acf->claim)) {
          $claim = $post_obj->acf->claim;
        }

        // Excerpt/Content from Yoast
        if (!empty($post_obj->yoast_desc_raw)) {
          $remote_excerpt = $post_obj->yoast_desc_raw;
        }

        // Icon resolution
        if (!empty($post_obj->acf->hauptseiten_icon)) {
          $icon_data = $post_obj->acf->hauptseiten_icon;
          $icon_data = is_array($icon_data) ? ($icon_data[0] ?? '') : $icon_data;
          if (is_string($icon_data)) {
            $icon = $icon_data;
          }
        }
      }
    }

    // 5. Local Fallbacks
    if (empty($image)) {
      $image = get_the_post_thumbnail_url($current_id, 'medium');
    }

    if (empty($claim)) {
      $claim = get_field('top_picture_claim', $data_id);
    }

    if (empty($icon)) {
      $icon_raw = get_field('hauptseiten_icon', $data_id);
      $icon = is_array($icon_raw) ? ($icon_raw[0] ?? '') : $icon_raw;
    }

    // 6. Title Processing (Supertitle separation)
    $processed_title = $this->textReplacement->process((string)$post->post_title);
    $title = $processed_title;
    $supertitle = '';

    if (preg_match('/^(.*?)\s+(\d{4}\s+[A-Za-zäöüÄÖÜß\-\/].*)$/u', $processed_title, $matches)) {
      $title = trim($matches[1]);
      $supertitle = trim($matches[2]);
    }

    // 7. Content Processing
    $content_raw = (!empty($remote_excerpt)) ? $remote_excerpt : (string)get_the_excerpt($current_id);

    // 8. Construct Data Array
    return [
      'title' => $title,
      'supertitle' => $supertitle,
      'link' => get_permalink($current_id),
      'content' => $this->textReplacement->process($content_raw),
      'excerpt' => $this->textReplacement->process((string)$claim),
      'extension' => $this->textReplacement->process((string)$claim),
      'claim' => $this->textReplacement->process((string)$claim),
      'image' => (string)$image,
      'icon' => (string)$icon,
      'badges' => $badges,
    ];
  }

  /**
   * Create data array from Remote API object
   * 
   * @param object $obj
   * @return array
   */
  public function create_from_api_object($obj): array
  {
    $url_slug = basename(rtrim($obj->url, '/'));

    return [
      'title' => $obj->title,
      'name' => $obj->title,
      'link' => '/' . $url_slug . '/',
    ];
  }
}
