<?php

namespace SeopressComposer\Models;


class Video_Data extends BaseModel
{

  public function get_data($page_id = null): array
  {
    $page_id = $this->resolve_page_id($page_id);

    $remote_url = get_field('remote_page', $page_id);

    if (empty($remote_url)) {
      return [];
    }

    $data = $this->repository->fetch($remote_url);

    if (isset($data->acf)) {
      $video_acfs = $data->acf;
      $icon = $video_acfs->hauptseiten_icon ?? [];

      return [
        "category_name" => get_the_title($page_id),
        "category_url" => get_permalink($page_id),
        "heading" => $this->textReplacer->process($video_acfs->video_uberschrift ?? ''),
        "content" => $this->textReplacer->process($video_acfs->video_text ?? ''),
        "video" => $video_acfs->video ?? '',
        "icon" => $icon[0] ?? '',
      ];
    }

    return [];
  }

  public function get_all_data(): array
  {
    $videos = [];
    $main_page_ids = $this->pageHelper->get_main_page_ids(false);

    foreach ($main_page_ids as $id) {
      $remote_url = get_field('remote_page', $id);

      if (!empty($remote_url)) {
        $all = $this->repository->fetch($remote_url);

        if ($all && isset($all->acf->video_uberschrift, $all->acf->video_text, $all->acf->video) && !empty($all->acf->video)) {

          $video_heading = $all->acf->video_uberschrift;
          $video_text = $all->acf->video_text;

          // Handle [DL] replacement specifically if dienstleistungen are available in remote data
          // Although TextReplacementService should handle general replacements, [DL] with random service from remote data is specific here.
          // However, TextReplacementService handles [DL] via PageHelper::get_random_dienstleistung() usually. 
          // But here we have remote data's dienstleistungen.

          // For consistency with old code, we might want to do it here, but let's try to rely on TextReplacementService first.
          // If TextReplacementService doesn't have access to this specific remote data's dienstleistungen, it might fail to replace [DL] correctly if it relies on local context.

          // Let's manually do the [DL] replacement using the remote data's services if available, similar to old code.
          if (isset($all->acf->dienstleistungen) && is_array($all->acf->dienstleistungen)) {
            $dl_list = $all->acf->dienstleistungen;
            $random_dl = $dl_list[array_rand($dl_list)]->dienstleistung ?? '';
            if ($random_dl) {
              $video_heading = str_replace('[DL]', $random_dl, $video_heading);
              $video_text = str_replace('[DL]', $random_dl, $video_text);
            }
          }

          $video_heading_short = (strlen($video_heading) > 130) ? substr($video_heading, 0, 130) . '...' : $video_heading;

          $videos[] = [
            "category_name" => get_the_title($id),
            "category_cleaned" => $this->textReplacer->clean_title(get_the_title($id)),
            "category_url" => get_permalink($id),
            "heading" => $this->textReplacer->process($video_heading),
            "content" => $this->textReplacer->process($video_heading_short),
            "extension" => $this->textReplacer->process($video_text),
            "video" => $all->acf->video
          ];
        }
      }
    }

    return $videos;
  }
}
