<?php

namespace SeopressComposer\Models;


/**
 * Punchline Data Model
 * 
 * Handles short, punchy text lines used between components for visual breaks
 */
class Punchline_Data extends BaseModel
{

  /**
   * Get all punchlines for a page
   * 
   * @param int|null $page_id WordPress page ID
   * @return array Array of punchlines
   */
  public function get_data($page_id = null): array
  {
    $page_id = $this->resolve_page_id($page_id);

    $remote_url = $this->get_remote_url($page_id);
    if (!$remote_url) {
      return [];
    }

    $data = $this->repository->fetch($remote_url);
    if (!$data || !isset($data->acf->punsh_lines)) {
      return [];
    }

    $raw_punchlines = $data->acf->punsh_lines ?? [];
    if (!is_array($raw_punchlines)) {
      return [];
    }

    $punchlines = [];
    foreach ($raw_punchlines as $punchline_acf) {
      $punchlines[] = [
        'content' => $this->textReplacer->prozess_data($punchline_acf->punsh_line ?? '')
      ];
    }

    return $punchlines;
  }

  /**
   * Get a single punchline by index
   * 
   * @param int $index Zero-based index
   * @param int|null $page_id WordPress page ID
   * @return string|null Punchline content or null if not found
   */
  public function get_by_index(int $index, $page_id = null): ?string
  {
    $punchlines = $this->get_data($page_id);

    if (isset($punchlines[$index]['content']) && !empty($punchlines[$index]['content'])) {
      return $punchlines[$index]['content'];
    }

    return null;
  }

  private function get_remote_url($page_id): string
  {
    if (is_category()) {
      $home_id = get_option('page_on_front');
      $val = get_field('remote_page', $home_id);
      return (string) ($val ?: '');
    }

    $val = get_field('remote_page', $page_id);
    return (string) ($val ?: '');
  }
}
