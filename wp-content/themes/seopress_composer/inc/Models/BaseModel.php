<?php

namespace SeopressComposer\Models;

use SeopressComposer\Repositories\RemoteDataRepository;
use SeopressComposer\Services\TextReplacementService;
use SeopressComposer\Helpers\PageHelper;
use SeopressComposer\Controllers\ContextController;
use SeopressComposer\Factories\DataFactory;

use SeopressComposer\Services\IconService;
use SeopressComposer\Repositories\PageRepository;

/**
 * BaseModel
 * 
 * Base abstract class for all data models.
 * Standardizes common dependencies and helper methods.
 */
abstract class BaseModel
{
  protected RemoteDataRepository $repository;
  protected TextReplacementService $textReplacer;
  protected PageHelper $pageHelper;
  protected ContextController $contextController;
  protected DataFactory $dataFactory;
  protected IconService $iconService;
  protected \SeopressComposer\Repositories\PageRepository $pageRepository;
  protected \SeopressComposer\Repositories\CategoryRepository $categoryRepository;
  protected ?\SeopressComposer\Services\ImageDownloadService $imageDownloadService = null;

  public function __construct(
    RemoteDataRepository $repository,
    TextReplacementService $textReplacer,
    PageHelper $pageHelper,
    ContextController $contextController,
    DataFactory $dataFactory,
    IconService $iconService,
    \SeopressComposer\Repositories\PageRepository $pageRepository,
    \SeopressComposer\Repositories\CategoryRepository $categoryRepository
  ) {
    $this->repository = $repository;
    $this->textReplacer = $textReplacer;
    $this->pageHelper = $pageHelper;
    $this->contextController = $contextController;
    $this->dataFactory = $dataFactory;
    $this->iconService = $iconService;
    $this->pageRepository = $pageRepository;
    $this->categoryRepository = $categoryRepository;
  }

  protected function getImageDownloadService(): \SeopressComposer\Services\ImageDownloadService
  {
      if ($this->imageDownloadService === null) {
          $this->imageDownloadService = new \SeopressComposer\Services\ImageDownloadService();
      }
      return $this->imageDownloadService;
  }

  /**
   * Resolve page ID using ContextController if null provided.
   * 
   * @param int|string|null $page_id
   * @return int|string
   */
  protected function resolve_page_id($page_id = null)
  {
    if (!$page_id) {
      return $this->contextController->get_current_page_id();
    }
    return $page_id;
  }

  /**
   * Abstract method that all models must implement to return their data.
   */
  abstract public function get_data($page_id = null): array;
}
