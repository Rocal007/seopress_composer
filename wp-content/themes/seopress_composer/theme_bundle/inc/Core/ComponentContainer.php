<?php

namespace SeopressComposer\Core;

use SeopressComposer\Core\ServiceContainer;

/**
 * Stellt alle verfügbaren UI-Komponenten bereit.
 * 
 * Nutzt Magic Methods (__call), um Komponenten dynamisch aufzulösen.
 * 
 * @method \SeopressComposer\Components\Hero getHero()
 * @method \SeopressComposer\Components\Accordion getAccordion()
 * @method \SeopressComposer\Components\Card getCard()
 * @method \SeopressComposer\Components\NumberedFeatures getNumberedFeatures()
 * @method \SeopressComposer\Components\GridContent getGridContent()
 * @method \SeopressComposer\Components\SimpleContent getSimpleContent()
 * @method \SeopressComposer\Layouts\Topbar getTopbar()
 * @method \SeopressComposer\Components\Slider getSlider()
 * @method \SeopressComposer\Components\Tabs getTabs()
 * @method \SeopressComposer\Components\Logo getLogo()
 * @method \SeopressComposer\Components\VideoTextSlider getVideoTextSlider()
 * @method \SeopressComposer\Components\ListTooltip getListTooltip()
 * @method \SeopressComposer\Components\PricingCard getPricingCard()
 * @method \SeopressComposer\Components\CtaBoxes getCtaBoxes()
 * @method \SeopressComposer\Components\ServiceList getServiceList()
 * @method \SeopressComposer\Components\ServiceDetails getServiceDetails()
 * @method \SeopressComposer\Components\ServiceSection getServiceSection()
 * @method \SeopressComposer\Components\ContactBar getContactBar()
 * @method \SeopressComposer\Layouts\FooterLinks getFooterLinks()
 * @method \SeopressComposer\Components\SectionTitle getSectionTitle()
 * @method \SeopressComposer\Components\Punchline getPunchline()
 * @method \SeopressComposer\Components\Breadcrumb getBreadcrumb()
 * @method \SeopressComposer\Components\Backlinks getBacklinks()
 * @method \SeopressComposer\Components\Menu getMenu()
 * @method \SeopressComposer\Components\ImageMenu getImageMenu()
 * @method \SeopressComposer\Layouts\OneColumn getOneColumn()
 * @method \SeopressComposer\Layouts\TwoColumns getTwoColumns()
 * @method \SeopressComposer\Layouts\ThreeColumns getThreeColumns()
 * @method \SeopressComposer\Layouts\FourColumns getFourColumns()
 * @method \SeopressComposer\Layouts\FiveColumns getFiveColumns()
 * @method \SeopressComposer\Layouts\SixColumns getSixColumns()
 * @method \SeopressComposer\Components\ImageTextReadMore getImageTextReadMore()
 * @method \SeopressComposer\Components\Districts getDistricts()
 * @method \SeopressComposer\Components\LeftRightToggle getLeftRightToggle()
 * @method \SeopressComposer\Components\ProcessCircle getProcessCircle()
 * @method \SeopressComposer\Components\ProcessPie getProcessPie()
 * @method \SeopressComposer\Components\GroupedCards getGroupedCards()
 * @method \SeopressComposer\Components\IconLinks getIconLinks()
 * @method \SeopressComposer\Components\SocialIcons getSocialIcons()
 * @method \SeopressComposer\Components\ContactFooter getContactFooter()
 * @method \SeopressComposer\Components\LinkList getLinkList()
 * @method \SeopressComposer\Components\MultiStepForm getMultiStepForm()
 */
class ComponentContainer
{
  private $serviceContainer;

  // Kapselt den Service Container
  public function __construct(ServiceContainer $serviceContainer)
  {
    $this->serviceContainer = $serviceContainer;
  }

  /**
   * Magische Methode, um Komponenten dynamisch zu laden.
   * Aufruf: $container->getHero() -> lädt SeopressComposer\Components\Hero
   */
  public function __call($name, $arguments)
  {
    if (strpos($name, 'get') === 0) {
      $componentName = substr($name, 3);

      // Try Components namespace
      $classComponent = "SeopressComposer\\Components\\$componentName";
      if (class_exists($classComponent)) {
        return $this->serviceContainer->get($classComponent);
      }

      // Try Layouts namespace
      $classLayout = "SeopressComposer\\Layouts\\$componentName";
      if (class_exists($classLayout)) {
        return $this->serviceContainer->get($classLayout);
      }

      throw new \Exception("Component or Layout class for '$componentName' not found.");
    }

    throw new \Exception("Method '$name' not found in ComponentContainer.");
  }

  /**
   * Helper to render Main Text Content with fallback.
   * Logic: If 'haupttext' (Content) exists, render SimpleContent.
   * Else render NumberedFeatures.
   */
  public function renderMainContent(array $data)
  {
    if (!empty($data['haupttext'])) {
      return $this->getSimpleContent()->render($data['haupttext']);
    }
    return $this->getNumberedFeatures()->render($data, 2);
  }
}
