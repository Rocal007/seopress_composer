<?php

namespace SeopressComposer\Models;


class Claim_Data extends BaseModel
{
  public function get_data($page_id = null): array
  {
    $page_id = $this->resolve_page_id($page_id);

    $remote_url = get_field('remote_page', $page_id);

    if (empty($remote_url)) {
      return [];
    }

    $data = $this->repository->fetch($remote_url);

    if ($data !== null && isset($data->acf->claim)) {
      $claim_content = $data->acf->claim;
      if (!empty($claim_content)) {
        return [
          'claim' => ucfirst(wp_kses_post($this->textReplacer->process($claim_content)))
        ];
      }
    }

    return [];
  }
}
