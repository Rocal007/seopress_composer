<?php

namespace SeopressComposer\Models;


class Vorteile_Data extends BaseModel
{

  public function get_data($page_id = null): array
  {
    $page_id = $this->resolve_page_id($page_id);
    $remote_url = $this->pageHelper->get_remote_url($page_id);

    if (empty($remote_url)) {
      return [];
    }

    $data = $this->repository->fetch($remote_url);

    if ($data !== null && isset($data->acf->schlagworte)) {
      $vorteile_acfs = $data->acf->schlagworte;

      if (!empty($vorteile_acfs)) {
        $vorteile = array_map(function ($vorteil_acf) {

          return [
            "title" => $this->textReplacer->process($vorteil_acf->schlagwort ?? ''),
            "content" => $this->textReplacer->prozess_data($vorteil_acf->beschreibung ?? ''),
            "icon" => $this->iconService->normalizeIconName($vorteil_acf->icon ?: ($vorteil_acf->schlagwort ?? '')),
            "icon_options" => [
              'primary_background' => true
            ]
          ];
        }, $vorteile_acfs);

        $titles = $this->pageHelper->get_section_titles('vorteile', 'Ihre Vorteile', '', $data);

        return [
          'heading' => $titles['title'],
          'items' => $vorteile
        ];
      }
    }

    return [];
  }
}
