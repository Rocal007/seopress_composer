<?php

namespace SeopressComposer\Models;

class Backlinks_Data extends BaseModel
{
  public function get_data($page_id = null): array
  {
    $page_id = $this->resolve_page_id($page_id);
    return $this->fetch_backlinks($page_id);
  }

  private function fetch_backlinks($page_id): array
  {
    $remote_url = $this->get_remote_url($page_id);

    if (empty($remote_url)) {
      return [];
    }

    $data = $this->repository->fetch($remote_url);
    $backlinks = [];

    if ($data !== null && isset($data->acf->backlink_seiten) && is_array($data->acf->backlink_seiten)) {
      foreach ($data->acf->backlink_seiten as $backlink_acf) {
        $backlinks[] = [
          "text" => isset($backlink_acf->link_name) ? $this->textReplacer->prozess_data($backlink_acf->link_name) : '',
          "url" => isset($backlink_acf->webadresse_url) ? esc_url($backlink_acf->webadresse_url) : '#',
          "color_1" => !empty($backlink_acf->farbe_1) ? $backlink_acf->farbe_1 : 'var(--color-primary)',
          "color_2" => !empty($backlink_acf->farbe_2) ? $backlink_acf->farbe_2 : 'var(--color-accent)',
        ];
      }
    }

    return $backlinks;
  }

  private function get_remote_url($page_id): string
  {
    $remote_url = get_field('remote_page', $page_id);

    if (empty($remote_url)) {
      // Fallback to options page
      $options_page_id = function_exists('get_option') ? get_option('page_on_front') : 0;
      $remote_url = get_field('remote_page', $options_page_id);
    }

    return $remote_url;
  }
}
