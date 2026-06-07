<?php

namespace SeopressComposer\Controllers;

use SeopressComposer\Components\Menu;
use SeopressComposer\Models\Menu_Data;
use SeopressComposer\Helpers\PageHelper;

/**
 * MenuController
 * 
 * Handles contextual menu/sidebar rendering based on current page context.
 * Determines which menu sections to display based on:
 * - Current page taxonomies (district, service_category)
 * - Page hierarchy and relationships
 * - Available menu data from Menu_Data model
 * 
 * Usage in templates:
 * ```php
 * // Get the controller instance for more control
 * $menuController = seopress_container()->get(\SeopressComposer\Controllers\MenuController::class);
 * echo $menuController->renderSidebar();
 * ```
 */
class MenuController
{
  private Menu_Data $menuData;
  private PageHelper $pageHelper;
  private Menu $menuComponent;

  public function __construct(
    Menu_Data $menuData,
    PageHelper $pageHelper,
    Menu $menuComponent
  ) {
    $this->menuData = $menuData;
    $this->pageHelper = $pageHelper;
    $this->menuComponent = $menuComponent;
  }

  /**
   * Main method: Returns the complete sidebar HTML
   * 
   * @return string Rendered sidebar HTML
   */
  public function renderSidebar(): string
  {
    // 1. Get the raw section data (your logic)
    $sections = $this->getContextualSections();

    // 2. Pass to Menu component for rendering
    return $this->menuComponent->renderSidebarGroups($sections);
  }

  /**
   * Determines which sidebar sections to display based on current context
   * 
   * @return array Array of sidebar sections with title, items, and class
   */
  private function getContextualSections(): array
  {
    $sections = [];
    $location = $this->pageHelper->get_location();

    // Fetch required data on demand
    $related_services = $this->menuData->get_related_district_services();
    $districts_switch = $this->menuData->get_districts_for_current_service();
    $services_in_district = $this->menuData->get_services_in_district();
    $districts_list = $this->menuData->get_districts_list();
    $current_service_pages = $this->menuData->get_current_service_pages();
    $services_overview = $this->menuData->get_services_overview();

    // SCENARIO 1: Deep in a page (both district and service context)
    if (!empty($related_services) && !empty($districts_switch)) {
      $sections[] = [
        'title' => 'Weitere Dienstleistungen in ' . $location,
        'items' => $related_services,
        'class' => 'sidebar-related-services'
      ];
      $sections[] = [
        'title' => 'Diesen Service woanders',
        'items' => $districts_switch,
        'class' => 'sidebar-district-switch'
      ];
      return $sections;
    }

    // SCENARIO 2: District taxonomy archive
    if (function_exists('is_tax') && is_tax('district')) {
      $sections[] = [
        'title' => 'Leistungen in ' . single_term_title('', false),
        'items' => $services_in_district,
        'class' => 'sidebar-district-services'
      ];
      $sections[] = [
        'title' => 'Andere Standorte',
        'items' => $districts_list,
        'class' => 'sidebar-all-districts'
      ];
      return $sections;
    }

    // SCENARIO 3: Service category taxonomy archive
    if ((function_exists('is_tax') && is_tax('services')) || (!empty($current_service_pages) && empty($location))) {
      $sections[] = [
        'title' => 'Bereich ' . single_term_title('', false),
        'items' => $current_service_pages,
        'class' => 'sidebar-service-pages'
      ];
      $sections[] = [
        'title' => 'Finden Sie uns in Ihrem Bezirk',
        'items' => $districts_list,
        'class' => 'sidebar-districts'
      ];
      return $sections;
    }

    // SCENARIO 4: Fallback - show main services
    $sections[] = [
      'title' => 'Unsere Fachbereiche',
      'items' => $services_overview,
      'class' => 'sidebar-main-services'
    ];

    return $sections;
  }
}
