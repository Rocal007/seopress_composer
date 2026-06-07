<?php

namespace SeopressComposer\Models;

use SeopressComposer\Repositories\RemoteDataRepository;
use SeopressComposer\Services\TextReplacementService;
use SeopressComposer\Helpers\PageHelper;
use SeopressComposer\Controllers\ContextController;
use SeopressComposer\Factories\DataFactory;

use SeopressComposer\Services\IconService;

class Subtext_Data extends BaseModel
{
  private Image_Data $imageData;

  public function __construct(
    RemoteDataRepository $repository,
    TextReplacementService $textReplacer,
    PageHelper $pageHelper,
    ContextController $contextController,
    DataFactory $dataFactory,
    IconService $iconService,
    \SeopressComposer\Repositories\PageRepository $pageRepository,
    \SeopressComposer\Repositories\CategoryRepository $categoryRepository,
    Image_Data $imageData
  ) {
    parent::__construct($repository, $textReplacer, $pageHelper, $contextController, $dataFactory, $iconService, $pageRepository, $categoryRepository);
    $this->imageData = $imageData;
  }

  public function get_data($page_id = null): array
  {
    $result = [
      'subtexte_hs' => [],
      'subtexte_sub' => [],
      'images' => []
    ];

    $page_id = $this->resolve_page_id($page_id);

    $remote_url = $this->get_remote_url($page_id);
    if (!$remote_url) {
      return $result;
    }

    $data = $this->repository->fetch($remote_url);
    if (!$data || !isset($data->acf)) {
      return $result;
    }

    // Process Standard Subtexts (subtexte)
    if (!empty($data->acf->subtexte)) {
      $index = 1;
      foreach ($data->acf->subtexte as $subtext_acf) {
        $result['subtexte_hs'][] = [
          "index" => $this->textReplacer->prozess_data($subtext_acf->subtext_index ?? ''),
          "title" => $this->textReplacer->prozess_data($subtext_acf->uberschrift ?? '', [], true, '', false),
          "content" => $this->textReplacer->prozess_data($subtext_acf->subtext ?? '', [], false, '', false),
          "excerpt" => $this->get_excerpt($this->textReplacer->prozess_data($subtext_acf->subtext ?? '', [], false, '', false)),
        ];
        $index++;
      }
    }

    // Process Random Subtexts (subtexte_radom)
    if (!empty($data->acf->subtexte_radom)) {
      $subtexte_sub_all = $data->acf->subtexte_radom;
      if (count($subtexte_sub_all) > 1) {
        shuffle($subtexte_sub_all);
      }

      if (!empty($subtexte_sub_all[0])) {
        $subtext_random_heading = $this->textReplacer->prozess_data($subtexte_sub_all[0]->subtext_uberschrift_radom ?? '');
        $subtext_random_paragraphs = $subtexte_sub_all[0]->subtext_radom ?? [];
        shuffle($subtext_random_paragraphs);

        $subtext_random_content = [];
        foreach ($subtext_random_paragraphs as $p) {
          $subtext_random_content[] = $this->textReplacer->prozess_data($p->subtext_radom_absatz ?? '');
        }

        $result['subtexte_sub'] = [
          "title" => strip_tags($subtext_random_heading),
          "content" => $subtext_random_content,
        ];
      }
    }

    $result['images'] = $this->imageData->get_data($page_id);

    return $result;
  }

  private function get_remote_url($page_id): string
  {
    // For categories, grab remote_url from Homepage
    if (is_category()) {
      $home_id = get_option('page_on_front');
      $val = get_field('remote_page', $home_id);
      return (string) ($val ?: '');
    }

    $val = get_field('remote_page', $page_id);
    return (string) ($val ?: '');
  }



  private function get_excerpt($content, $word_limit = 50)
  {
    $content = strip_tags($content);
    $words = explode(' ', $content);
    return count($words) > $word_limit ? implode(' ', array_slice($words, 0, $word_limit)) . '...' : $content;
  }
}
