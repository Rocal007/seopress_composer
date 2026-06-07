<?php
namespace SeopressComposer\Models;

class SimpleContent_Data extends BaseModel
{
  public function get_data($page_id = null): array
  {
    $page_id = $this->resolve_page_id($page_id);
    
    $title = get_field('simple_title', $page_id);
    $content = get_field('simple_content', $page_id);

    return [
      'title' => $title ? $this->textReplacer->prozess_data($title) : '',
      'content' => $content ? $this->textReplacer->prozess_data($content) : '',
    ];
  }
}
