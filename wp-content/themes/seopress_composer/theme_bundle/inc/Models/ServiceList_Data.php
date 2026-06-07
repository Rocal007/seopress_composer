<?php

namespace SeopressComposer\Models;


class ServiceList_Data extends BaseModel
{

  public function get_data($page_id = null): array
  {
    $page_id = $this->resolve_page_id($page_id);

    $remote_url = get_field('remote_page', $page_id);

    if (empty($remote_url)) {
      return [];
    }

    $leistungen_listen = [];
    $data = $this->repository->fetch($remote_url);
    $leistungen_listen_acfs = $data->acf->leistungen_listen ?? [];

    if (!empty($leistungen_listen_acfs)) {
      $location = $this->pageHelper->get_options_business()['bundesland'] ?? '';
      $processed_location = $this->textReplacer->prozess_data($location);

      foreach ($leistungen_listen_acfs as $liste_acf) {
        $processed_block = [];

        if (!empty($liste_acf->leistungsblock)) {
          foreach ($liste_acf->leistungsblock as $block) {
            $processed_leistungen = [];

            if (!empty($block->leistung)) {
              foreach ($block->leistung as $leistungen_single) {
                $processed_leistungen[] = (object) [
                  'leistung_single' => $this->textReplacer->prozess_data($leistungen_single->leistung_single ?? '', [], true, '', false),
                  'info_single' => !empty($leistungen_single->info_single)
                    ? $this->textReplacer->prozess_data($leistungen_single->info_single, [], true, '', false)
                    : '',
                ];
              }
            }

            $processed_block[] = (object) [
              'uberschrift_block' => $this->textReplacer->process($block->uberschrift_block ?? ''),
              'leistung' => $processed_leistungen,
            ];
          }
        }

        $leistungen_listen[] = [
          "title" => $this->textReplacer->process($liste_acf->uberschrift ?? ''),
          "block" => $processed_block,
          "location" => $processed_location,
        ];
      }
    }

    return $leistungen_listen;
  }
}
