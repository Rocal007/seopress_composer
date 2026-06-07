<?php

namespace SeopressComposer\Models;


class Kosten_Data extends BaseModel
{

  public function get_data($page_id = null): array
  {
    $page_id = $this->resolve_page_id($page_id);
    $remote_url = $this->pageHelper->get_remote_url($page_id);

    if (empty($remote_url)) {
      return [];
    }

    $data = $this->repository->fetch($remote_url);
    $kosten = [];

    if ($data !== null) {
      $posten_kosten_acf = $data->acf->posten_kosten ?? null;
      $heading_kosten = $data->acf->uberschrift_kosten ?? '';

      if (!empty($posten_kosten_acf)) {
        $kosten_count = count($posten_kosten_acf);
        $adjusted_kosten_count = ($kosten_count === 3) ? 4 : (($kosten_count === 4) ? 3 : $kosten_count);

        foreach ($posten_kosten_acf as $kosten_acf) {
          // Pass $apply_filters=false to prevent the_content filter from
          // injecting service/district lists into short price strings.
          $wenig  = $this->textReplacer->prozess_data($kosten_acf->wenig_hausrat_kosten  ?? '', [], true, '', false);
          $normal = $this->textReplacer->prozess_data($kosten_acf->normaler_hausrat_kosten ?? '', [], true, '', false);
          $viel   = $this->textReplacer->prozess_data($kosten_acf->viel_hausrat_kosten   ?? '', [], true, '', false);
          $messie = $this->textReplacer->prozess_data($kosten_acf->messie_kosten         ?? '', [], true, '', false);

          // Skip row if no prices are set
          if (empty($wenig) && empty($normal) && empty($viel) && empty($messie)) {
            continue;
          }

          $kosten[] = [
            'heading'        => $this->textReplacer->prozess_data($heading_kosten, [], true, '', false),
            'location'       => $this->pageHelper->get_options_business()['bundesland'] ?? '',
            'adjusted_count' => $adjusted_kosten_count,
            'art_kosten'     => $this->textReplacer->prozess_data($kosten_acf->art_kosten ?? '', [], true, '', false),
            'wenig_kosten'   => $wenig,
            'normal_kosten'  => $normal,
            'viel_kosten'    => $viel,
            'messie_kosten'  => $messie,
          ];
        }
      }
    }

    return $kosten;
  }
}
