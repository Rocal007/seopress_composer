<?php

namespace SeopressComposer\Models;


class Banner_Data extends BaseModel
{
  public function get_data($page_id = null): array
  {
    if (!$page_id) {
      $page_id = $this->pageHelper->check_page_id();
    }

    $remote_url = get_field('remote_page', $page_id);

    if (empty($remote_url)) {
      return [];
    }

    $response = $this->repository->fetch($remote_url);

    if ($response !== null && isset($response->acf)) {
      $banner_all = $response->acf;
      return [
        "link" => $banner_all->banner_link ?? '',
        "img_url" => $banner_all->banner_picture->sizes->medium_large ?? '',
        "img_title" => $banner_all->add_text ?? '',
        "img_alt" => $banner_all->add_text ?? '',
      ];
    }

    return [];
  }
}
