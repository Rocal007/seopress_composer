<?php

namespace SeopressComposer\Models;


class Faqs_Data extends BaseModel
{

  public function get_data($page_id = null): array
  {
    // Get current page ID if not provided
    $page_id = $this->resolve_page_id($page_id);

    // Get remote URL using the unified PageHelper resolution (handles taxonomies and fallbacks)
    $remote_url = $this->pageHelper->get_remote_url($page_id);

    if (empty($remote_url)) {
      return $this->get_fallback_data();
    }

    // Fetch data from remote API
    $data = $this->repository->fetch($remote_url);

    if (!$data || empty($data->acf->faq_eintrag)) {
      return $this->get_fallback_data();
    }

    // Transform API data to accordion format
    $faqs = [];
    $dienstleistungen = $data->acf->dienstleistungen ?? [];

    foreach ($data->acf->faq_eintrag as $faq_acf) {
      $faqs[] = [
        'title'   => $this->textReplacer->prozess_data($faq_acf->titel      ?? '', $dienstleistungen, true, '', false),
        'content' => $this->textReplacer->prozess_data($faq_acf->faq_inhalt ?? '', $dienstleistungen, true, '', false),
        'icon'    => 'faq',
      ];
    }

    return [
      'heading' => 'Häufig gestellte Fragen',
      'items'   => $faqs
    ];
  }

  private function get_fallback_data(): array
  {
    return [];
  }
}
