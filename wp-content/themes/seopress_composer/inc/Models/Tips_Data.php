<?php

namespace SeopressComposer\Models;


class Tips_Data extends BaseModel
{

  /**
   * Fetches tips for a single page.
   *
   * @param int|null $page_id The ID of the page to fetch tips for. If null, it will try to determine the current page ID.
   * @return array An array of tip items for the specified page.
   */
  public function get_data($page_id = null): array
  {
    $page_id = $this->resolve_page_id($page_id);
    return $this->fetch_tips_for_page($page_id);
  }

  /**
   * Fetches tips for all main pages, structured into categories (tabs).
   *
   * @return array An array where each element represents a category (main page) with its title and a list of tips.
   */
  public function get_all_data(): array
  {
    $valid_ids = $this->pageHelper->get_main_page_ids(false);
    $all_tipps = [];
    foreach ($valid_ids as $id) {
      $tips = $this->fetch_tips_for_page($id);
      if (!empty($tips)) {
        $all_tipps[] = [
          'title' => $this->textReplacer->clean_title(get_the_title($id)),
          'items' => $tips
        ];
      }
    }
    return $all_tipps;
  }

  private function fetch_tips_for_page($page_id): array
  {
    $remote_url = $this->pageHelper->get_remote_url($page_id);

    if (empty($remote_url)) {
      return [];
    }

    $data = $this->repository->fetch($remote_url);
    if (!$data || empty($data->acf->tipps))
      return [];

    $tips_for_page = [];
    $dienstleistungen = $data->acf->dienstleistungen ?? [];

    foreach ($data->acf->tipps as $tip_acf) {
      $tip_title = $tip_acf->tipp_headline ?? '';
      $tip_content = $tip_acf->tip ?? '';
      $tip_extension = $tip_acf->tip_extension ?? '';

      $processed_title     = $this->textReplacer->prozess_data($tip_title,      $dienstleistungen, true, '', false);
      $processed_content   = $this->textReplacer->prozess_data($tip_content,    $dienstleistungen, true, '', false);
      $processed_extension = $this->textReplacer->prozess_data($tip_extension,  $dienstleistungen, true, '', false);

      // Structure extension as nested collapse
      $full_content = $processed_content;
      if (!empty($processed_extension)) {
        $full_content .= '
          <div class="collapse collapse-plus bg-base-200/50 mt-4 rounded-box">
            <input type="checkbox" /> 
            <div class="collapse-title text-sm font-semibold">
              Mehr erfahren
            </div>
            <div class="collapse-content text-sm"> 
              ' . $processed_extension . '
            </div>
          </div>';
      }

      $tips_for_page[] = [
        'title' => $processed_title,
        'content' => $full_content,
        'icon' => 'faq'
      ];
    }

    return $tips_for_page;
  }
}
