<?php

namespace SeopressComposer\Core;

use SeopressComposer\Models\Backlinks_Data;
use SeopressComposer\Models\Banner_Data;
use SeopressComposer\Models\Claim_Data;
use SeopressComposer\Models\Contact_Data;
use SeopressComposer\Models\CtaBoxes_Data;

use SeopressComposer\Models\Faqs_Data;
use SeopressComposer\Models\FooterLinks_Data;
use SeopressComposer\Models\GridContent_Data;
use SeopressComposer\Models\Image_Data;
use SeopressComposer\Models\Kosten_Data;
use SeopressComposer\Models\Logo_Data;
use SeopressComposer\Models\MainText_Data;
use SeopressComposer\Models\Menu_Data;
use SeopressComposer\Models\Punchline_Data;
use SeopressComposer\Models\ServiceList_Data;
use SeopressComposer\Models\SimpleContent_Data;
use SeopressComposer\Models\Subtext_Data;
use SeopressComposer\Models\Taxonomy_Data;
use SeopressComposer\Models\Tips_Data;
use SeopressComposer\Models\Topbar_Data;
use SeopressComposer\Models\TopPicture_Data;
use SeopressComposer\Models\Video_Data;
use SeopressComposer\Models\Vorteile_Data;

/**
 * ModelsDTO - Provides explicit, typed access to all data models.
 */
class ModelsDTO
{
  private ServiceContainer $container;

  public function __construct(ServiceContainer $container)
  {
    $this->container = $container;
  }

  public function getBacklinksModel(): Backlinks_Data
  {
    return $this->container->get(Backlinks_Data::class);
  }

  public function getBacklinksData($args = null)
  {
    return $this->getBacklinksModel()->get_data($args);
  }

  public function getBannerModel(): Banner_Data
  {
    return $this->container->get(Banner_Data::class);
  }

  public function getBannerData($args = null)
  {
    return $this->getBannerModel()->get_data($args);
  }

  public function getClaimModel(): Claim_Data
  {
    return $this->container->get(Claim_Data::class);
  }

  public function getClaimData($args = null)
  {
    return $this->getClaimModel()->get_data($args);
  }

  public function getContactModel(): Contact_Data
  {
    return $this->container->get(Contact_Data::class);
  }

  public function getContactData($args = null)
  {
    return $this->getContactModel()->get_data($args);
  }

  public function getCtaBoxesModel(): CtaBoxes_Data
  {
    return $this->container->get(CtaBoxes_Data::class);
  }

  public function getCtaBoxesData($args = null)
  {
    return $this->getCtaBoxesModel()->get_data($args);
  }

  public function getDistrictsModel()
  {
    // Returning the repository instance as the "Model" for simplicity, 
    // or we could keep Districts_Data as a wrapper. 
    // For now, let's assume Districts_Data is deprecated/removed.
    return $this->container->get(\SeopressComposer\Repositories\CategoryRepository::class);
  }

  public function getDistrictsData($args = [])
  {
    // Get districts from Menu_Data (uses 'district' custom taxonomy)
    $districts_menu = $this->getMenuModel()->get_districts_list();

    // Transform to the format expected by Districts component
    $data = [];
    foreach ($districts_menu as $district) {
      $data[] = [
        'name' => $district['title'],
        'link' => $district['link'],
        'karte' => $district['image_url'] ?? '',
      ];
    }

    return $data;
  }

  public function getFaqsModel(): Faqs_Data
  {
    return $this->container->get(Faqs_Data::class);
  }

  public function getFaqsData($args = null)
  {
    return $this->getFaqsModel()->get_data($args);
  }


  public function getFooterLinksModel(): FooterLinks_Data
  {
    return $this->container->get(FooterLinks_Data::class);
  }

  public function getFooterLinksData()
  {
    return $this->getFooterLinksModel()->get_data();
  }

  public function getGridContentModel(): GridContent_Data
  {
    return $this->container->get(GridContent_Data::class);
  }

  public function getGridContentData()
  {
    return $this->getGridContentModel()->get_data();
  }

  public function getImageModel(): Image_Data
  {
    return $this->container->get(Image_Data::class);
  }

  public function getImageData($args = null)
  {
    return $this->getImageModel()->get_data($args);
  }

  public function getKostenModel(): Kosten_Data
  {
    return $this->container->get(Kosten_Data::class);
  }

  public function getKostenData($args = null)
  {
    return $this->getKostenModel()->get_data($args);
  }

  public function getImpressumModel(): \SeopressComposer\Models\Impressum_Data
  {
    return $this->container->get(\SeopressComposer\Models\Impressum_Data::class);
  }

  public function getImpressumData()
  {
    return $this->getImpressumModel()->get_data();
  }

  public function getLogoModel(): Logo_Data
  {
    return $this->container->get(Logo_Data::class);
  }

  public function getLogoData($args = null)
  {
    return $this->getLogoModel()->get_data($args);
  }

  public function getMainTextModel(): MainText_Data
  {
    return $this->container->get(MainText_Data::class);
  }

  public function getMainTextData($args = null)
  {
    if (is_tax() || is_category() || is_tag()) {
      return $this->getTaxonomyModel()->get_main_text_data($args);
    }
    return $this->getMainTextModel()->get_data($args);
  }

  public function getMenuModel(): Menu_Data
  {
    return $this->container->get(Menu_Data::class);
  }

  public function getMenuData()
  {
    return []; // Deprecated: Monolithic get_menu_data removed
  }

  public function getServiceListModel(): ServiceList_Data
  {
    return $this->container->get(ServiceList_Data::class);
  }

  public function getServiceListData($args = null)
  {
    return $this->getServiceListModel()->get_data($args);
  }

  public function getPunchlineModel(): Punchline_Data
  {
    return $this->container->get(Punchline_Data::class);
  }

  public function getPunchlineData($args = null)
  {
    return $this->getPunchlineModel()->get_data($args);
  }

  public function getSimpleContentModel(): SimpleContent_Data
  {
    return $this->container->get(SimpleContent_Data::class);
  }

  public function getSimpleContentData()
  {
    return $this->getSimpleContentModel()->get_data();
  }

  public function getSubtextModel(): Subtext_Data
  {
    return $this->container->get(Subtext_Data::class);
  }

  public function getSubtextData($args = null)
  {
    return $this->getSubtextModel()->get_data($args);
  }

  public function getTipsModel(): Tips_Data
  {
    return $this->container->get(Tips_Data::class);
  }

  public function getTipsData($args = null)
  {
    return $this->getTipsModel()->get_data($args);
  }

  public function getAllTipsData()
  {
    return $this->getTipsModel()->get_all_data();
  }

  public function getTopbarModel(): Topbar_Data
  {
    return $this->container->get(Topbar_Data::class);
  }

  public function getTopbarData()
  {
    return $this->getTopbarModel()->get_data();
  }

  public function getTopPictureModel(): TopPicture_Data
  {
    return $this->container->get(TopPicture_Data::class);
  }

  public function getTopPictureData($args = null)
  {
    return $this->getTopPictureModel()->get_data($args);
  }

  public function getVideoModel(): Video_Data
  {
    return $this->container->get(Video_Data::class);
  }

  public function getVideoData($args = null)
  {
    return $this->getVideoModel()->get_data($args);
  }

  public function getVorteileModel(): Vorteile_Data
  {
    return $this->container->get(Vorteile_Data::class);
  }

  public function getVorteileData($args = null)
  {
    return $this->getVorteileModel()->get_data($args);
  }

  public function getTaxonomyModel(): Taxonomy_Data
  {
    return $this->container->get(Taxonomy_Data::class);
  }

  public function getServiceCategoryData($term_id = null)
  {
    return $this->getTaxonomyModel()->get_service_category_data($term_id);
  }

  public function getServiceCategorySections($term_id = null)
  {
    return $this->getTaxonomyModel()->get_service_category_sections($term_id);
  }

  public function getServiceCategoryPages($term_id = null): array
  {
    return $this->getTaxonomyModel()->get_service_category_pages($term_id);
  }

  public function getServiceCategoryGroupedPages($term_id = null): array
  {
    return $this->getTaxonomyModel()->get_service_category_grouped_pages($term_id);
  }

  public function getOtherServices($term_id = null, $limit = 12): array
  {
    return $this->getTaxonomyModel()->get_other_services($term_id, $limit);
  }

  public function getDistrictSections($term_id = null): array
  {
    return $this->getTaxonomyModel()->get_district_sections($term_id);
  }

  public function getDistrictData($term_id = null): array
  {
    return $this->getTaxonomyModel()->get_district_data($term_id);
  }

  public function getDistrictTermData($term_id = null): array
  {
    return $this->getTaxonomyModel()->get_district_data($term_id);
  }

  public function getDistrictGroupedPages($term_id = null): array
  {
    return $this->getTaxonomyModel()->get_district_grouped_pages($term_id);
  }

  public function getOtherDistricts($term_id = null, $limit = 12): array
  {
    return $this->getTaxonomyModel()->get_other_districts($term_id, $limit);
  }

  public function get_all_services_grouped(): array
  {
    return $this->getTaxonomyModel()->get_all_services_grouped();
  }

  public function getSiteSettingsData(): \SeopressComposer\Models\SiteSettings_Data
  {
    return $this->container->get(\SeopressComposer\Models\SiteSettings_Data::class);
  }
}
