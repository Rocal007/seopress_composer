<?php

namespace SeopressComposer\Models;


class MainText_Data extends BaseModel
{

  public function get_data($page_id = null): array
  {
    // Get current page ID if not provided
    $page_id = $this->pageHelper->check_page_id($page_id);

    // Get remote URL 
    $remote_url = $this->pageHelper->get_remote_url($page_id);

    if (empty($remote_url)) {
      return $this->get_taxonomy_fallback_data();
    }

    // Fetch data from remote API
    $data = $this->repository->fetch($remote_url);

    if (!$data || !isset($data->acf)) {
      return $this->get_taxonomy_fallback_data();
    }

    $result = [];
    $overview_headlines = $data->acf->uberschriften ?? [];
    $overview_text_blocks = $data->acf->ubersichtstexte_bezirke ?? [];
    if (!is_array($overview_text_blocks)) {
      $overview_text_blocks = [];
    }
    $dienstleistungen = $data->acf->dienstleistungen ?? [];

    // Process headlines
    if (!empty($overview_headlines)) {
      $overview_headlines_clean = array_map(
        fn($item) => $this->textReplacer->prozess_data($item->uberschrift ?? '', $dienstleistungen, true, '', false),
        $overview_headlines
      );
      shuffle($overview_headlines_clean);
      $result['title'] = $overview_headlines_clean[0] ?? '';
    }

    // Process keyword blocks
    $keywords = [
      'keywords_1' => [],
      'keywords_2' => [],
      'keywords_3' => [],
      'keywords_4' => []
    ];

    foreach ($overview_text_blocks as $block) {
      for ($i = 1; $i <= 4; $i++) {
        $key = "keywords_$i";
        if (!empty($block->{"keywords_$i"})) {
          $keywords[$key][] = $this->textReplacer->prozess_data($block->ubersichtstext_bezirke ?? '', $dienstleistungen, false);
        }
      }
    }

    foreach ($keywords as &$keyword_array) {
      shuffle($keyword_array);
    }

    $result['items'] = [];
    foreach ($keywords as $key => $array) {
      if (isset($array[0])) {
        $result['items'][] = $array[0];
      }
    }

    // Process haupttext for specific templates (e.g. template-hauptseiten.php)
    // Prioritize yoast_desc_raw as the SEO title/heading
    $remote_title = $data->yoast_desc_raw ?? $data->acf->hauptinhalt_uberschrift ?? $data->name ?? '';
    $remote_content = $data->acf->hauptinhalt_text ?? $data->description ?? '';

    if (!empty($remote_content)) {
      $result['haupttext'] = [
        'title' => !empty($remote_title) ? $this->textReplacer->prozess_data($remote_title, $dienstleistungen, false, '', false) : '',
        'content' => $this->textReplacer->prozess_data($remote_content, $dienstleistungen, false, '', false)
      ];
    }

    return $result;
  }



  private function get_taxonomy_fallback_data(): array
  {
    $fallback = $this->get_fallback_data();

    $queried_object = get_queried_object();
    if ($queried_object instanceof \WP_Term && !empty($queried_object->description)) {
      $fallback['haupttext'] = [
        'title' => $this->textReplacer->prozess_data($queried_object->name, [], false, '', false),
        'content' => $this->textReplacer->prozess_data($queried_object->description, [], false, '', true)
      ];
    }

    return $fallback;
  }

  private function get_fallback_data(): array
  {
    return [
      'title' => '',
      'items' => []
    ];
  }
}
