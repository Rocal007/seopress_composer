<?php

namespace SeopressComposer\Models;


class CtaBoxes_Data extends BaseModel
{

  public function get_data($page_id = null): array
  {
    $page_id = $this->resolve_page_id($page_id);

    $remote_url = $this->pageHelper->get_remote_url($page_id);

    if (empty($remote_url)) {
      return [];
    }

    $data = $this->repository->fetch($remote_url);
    $ablauf_boxes = [];

    if (isset($data->acf->ablauf_blocke)) {
      $ablaufe = $data->acf->ablauf_blocke;
      $index = 1;

      foreach ($ablaufe as $ablauf_single) {
        $ablauf_boxes[] = [
          "index" => $index,
          "heading" => $this->textReplacer->prozess_data($ablauf_single->uberschrift ?? ''),
          "content" => $this->textReplacer->prozess_data($ablauf_single->text ?? ''),
          "icon" => $this->iconService->normalizeIconName($ablauf_single->icon ?? ''),
          "link" => esc_url($ablauf_single->link ?? ''),
          "button_text" => esc_html($ablauf_single->button_text ?? ''),
          "button_link_text" => $this->textReplacer->prozess_data($ablauf_single->button_link_text ?? '')
        ];
        $index++;
      }
    }

    $titles = $this->pageHelper->get_section_titles('ablauf', 'Unser Ablauf', 'Einfach, transparent und professionell. So arbeiten wir für Sie.', $data);

    return [
      'heading' => $titles['title'],
      'subheading' => $titles['description'],
      'items' => $ablauf_boxes
    ];
  }
}
