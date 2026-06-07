<?php

namespace SeopressComposer\Models;


class Image_Data extends BaseModel
{

  public function get_data($page_id = null): array
  {
    $page_id = $this->resolve_page_id($page_id);

    $remote_url = get_field('remote_page', $page_id);

    if (empty($remote_url)) {
      return [];
    }

    $data = $this->repository->fetch($remote_url);

    if ($data !== null && !empty($data->acf->bilder_fur_hauptkategorie)) {
      $images_acfs = $data->acf->bilder_fur_hauptkategorie;
      $result = [];

      foreach ($images_acfs as $image_acf) {
        $image_sizes = $image_acf->bild->sizes ?? [];
        $image_url_desktop = '';
        $image_url_mobile = '';

        if (is_array($image_sizes) || is_object($image_sizes)) {
            $image_sizes = (array)$image_sizes;
            $image_url_desktop = $image_sizes['large_webp'] ?? $image_sizes['large'] ?? $image_sizes['full_webp'] ?? $image_sizes['full'] ?? reset($image_sizes);
            $image_url_mobile = $image_sizes['mobile_webp'] ?? $image_sizes['mobile'] ?? '';
        } else if (is_string($image_sizes)) {
            $image_url_desktop = $image_sizes;
        }

        $image_caption_raw = $image_acf->bild_beschreibung ?? '';

        // Use TextReplacementService for full processing
        $image_caption_processed = $this->textReplacer->prozess_data($image_caption_raw);
        $image_caption_cleaned = wp_strip_all_tags($image_caption_processed);

        $image_title = $image_caption_cleaned . ' in ' . $this->get_location();
        
        $seo_image_url_desktop = '';
        if ($image_url_desktop) {
            $seo_image_url_desktop = $this->getImageDownloadService()->get_local_image_url($image_url_desktop, $image_title, $image_caption_cleaned);
        }

        $seo_image_url_mobile = '';
        if ($image_url_mobile) {
            $seo_image_url_mobile = $this->getImageDownloadService()->get_local_image_url($image_url_mobile, $image_title . ' mobile', $image_caption_cleaned);
        }

        $result[] = [
          "image_url" => $seo_image_url_desktop,
          "image_mobile_url" => $seo_image_url_mobile,
          "image_caption" => $this->textReplacer->clean_title($image_caption_cleaned),
          "image_alt" => $image_caption_cleaned,
          "image_title" => $image_title,
        ];
      }
      return $result;
    }

    return [];
  }

  private function get_location(): string
  {
    $options = $this->pageHelper->get_options_business();
    return $options['bundesland'] ?? '';
  }
}
