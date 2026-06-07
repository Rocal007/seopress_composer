<?php

namespace SeopressComposer\Core;

use ReflectionClass;
use ReflectionException;

/**
 * ServiceContainer - Simple Dependency Injection Container
 */
class ServiceContainer
{
  private array $services = [];
  private array $instances = [];

  // Container services storage

  public function __construct()
  {
    $this->register_defaults();
  }

  /**
   * Register default services and repositories
   */
  private function register_defaults(): void
  {
    // Repositories
    $this->set(\SeopressComposer\Repositories\RemoteDataRepository::class, function ($c) {
      return new \SeopressComposer\Repositories\RemoteDataRepository();
    });

    $this->set(\SeopressComposer\Repositories\PageRepository::class, function ($c) {
      return new \SeopressComposer\Repositories\PageRepository();
    });

    $this->set(\SeopressComposer\Repositories\CategoryRepository::class, function ($c) {
      return new \SeopressComposer\Repositories\CategoryRepository(
        $c->get(\SeopressComposer\Helpers\PageHelper::class)
      );
    });

    $this->set(\SeopressComposer\Services\LocationService::class, function ($c) {
      return new \SeopressComposer\Services\LocationService(
        $c->get(\SeopressComposer\Repositories\PageRepository::class)
      );
    });

    $this->set(\SeopressComposer\Services\RemoteRouteService::class, function ($c) {
      return new \SeopressComposer\Services\RemoteRouteService(
        $c->get(\SeopressComposer\Models\SiteSettings_Data::class)
      );
    });

    // Helpers
    $this->set(\SeopressComposer\Helpers\PageHelper::class, function ($c) {
      return new \SeopressComposer\Helpers\PageHelper(
        $c->get(\SeopressComposer\Repositories\RemoteDataRepository::class),
        $c->get(\SeopressComposer\Repositories\PageRepository::class),
        $c->get(\SeopressComposer\Controllers\ContextController::class),
        $c->get(\SeopressComposer\Models\SiteSettings_Data::class),
        $c->get(\SeopressComposer\Services\LocationService::class),
        $c->get(\SeopressComposer\Services\RemoteRouteService::class)
      );
    });

    $this->set(\SeopressComposer\Settings\SiteSettings::class, function ($c) {
      return new \SeopressComposer\Settings\SiteSettings();
    });

    // Services
    $this->set(\SeopressComposer\Services\TextReplacementService::class, function ($c) {
      return new \SeopressComposer\Services\TextReplacementService(
        $c->get(\SeopressComposer\Helpers\PageHelper::class),
        $c->get(\SeopressComposer\Repositories\RemoteDataRepository::class)
      );
    });

    $this->set(\SeopressComposer\Services\IconService::class, function ($c) {
      return \SeopressComposer\Services\IconService::getInstance();
    });

    $this->set(\SeopressComposer\Services\SeoMetaService::class, function ($c) {
      return new \SeopressComposer\Services\SeoMetaService(
        $c->get(\SeopressComposer\Helpers\PageHelper::class),
        $c->get(\SeopressComposer\Repositories\RemoteDataRepository::class),
        $c->get(\SeopressComposer\Services\TextReplacementService::class)
      );
    });

    $this->set(\SeopressComposer\Services\LlmsTxtService::class, function ($c) {
      return new \SeopressComposer\Services\LlmsTxtService(
        $c->get(\SeopressComposer\Helpers\PageHelper::class)
      );
    });

    $this->set(\SeopressComposer\Services\InternalLinkService::class, function ($c) {
      return new \SeopressComposer\Services\InternalLinkService(
        $c->get(\SeopressComposer\Helpers\PageHelper::class)
      );
    });

    $this->set(\SeopressComposer\Services\ImageSitemapService::class, function ($c) {
      return new \SeopressComposer\Services\ImageSitemapService(
        $c->get(\SeopressComposer\Helpers\PageHelper::class),
        $c->get(\SeopressComposer\Repositories\RemoteDataRepository::class)
      );
    });

    $this->set(\SeopressComposer\Settings\LocationSelectionPage::class, function ($c) {
      return new \SeopressComposer\Settings\LocationSelectionPage();
    });

    $this->set(\SeopressComposer\Settings\ServiceSelectionPage::class, function ($c) {
      return new \SeopressComposer\Settings\ServiceSelectionPage();
    });

    // Models - All extending BaseModel get CategoryRepository injected last
    $this->set(\SeopressComposer\Models\Claim_Data::class, function ($c) {
      return new \SeopressComposer\Models\Claim_Data(
        $c->get(\SeopressComposer\Repositories\RemoteDataRepository::class),
        $c->get(\SeopressComposer\Services\TextReplacementService::class),
        $c->get(\SeopressComposer\Helpers\PageHelper::class),
        $c->get(\SeopressComposer\Controllers\ContextController::class),
        $c->get(\SeopressComposer\Factories\DataFactory::class),
        $c->get(\SeopressComposer\Services\IconService::class),
        $c->get(\SeopressComposer\Repositories\PageRepository::class),
        $c->get(\SeopressComposer\Repositories\CategoryRepository::class)
      );
    });

    $this->set(\SeopressComposer\Services\FaviconService::class, function ($c) {
      return new \SeopressComposer\Services\FaviconService(
        $c->get(\SeopressComposer\Services\IconService::class)
      );
    });

    // Factories
    $this->set(\SeopressComposer\Factories\DataFactory::class, function ($c) {
      return new \SeopressComposer\Factories\DataFactory(
        $c->get(\SeopressComposer\Repositories\RemoteDataRepository::class),
        $c->get(\SeopressComposer\Helpers\PageHelper::class),
        $c->get(\SeopressComposer\Services\TextReplacementService::class)
      );
    });

    // Models
    $this->set(\SeopressComposer\Models\TopPicture_Data::class, function ($c) {
      return new \SeopressComposer\Models\TopPicture_Data(
        $c->get(\SeopressComposer\Repositories\RemoteDataRepository::class),
        $c->get(\SeopressComposer\Services\TextReplacementService::class),
        $c->get(\SeopressComposer\Helpers\PageHelper::class),
        $c->get(\SeopressComposer\Controllers\ContextController::class),
        $c->get(\SeopressComposer\Factories\DataFactory::class),
        $c->get(\SeopressComposer\Services\IconService::class),
        $c->get(\SeopressComposer\Repositories\PageRepository::class),
        $c->get(\SeopressComposer\Repositories\CategoryRepository::class)
      );
    });

    $this->set(\SeopressComposer\Models\Banner_Data::class, function ($c) {
      return new \SeopressComposer\Models\Banner_Data(
        $c->get(\SeopressComposer\Repositories\RemoteDataRepository::class),
        $c->get(\SeopressComposer\Services\TextReplacementService::class),
        $c->get(\SeopressComposer\Helpers\PageHelper::class),
        $c->get(\SeopressComposer\Controllers\ContextController::class),
        $c->get(\SeopressComposer\Factories\DataFactory::class),
        $c->get(\SeopressComposer\Services\IconService::class),
        $c->get(\SeopressComposer\Repositories\PageRepository::class),
        $c->get(\SeopressComposer\Repositories\CategoryRepository::class)
      );
    });

    $this->set(\SeopressComposer\Models\Vorteile_Data::class, function ($c) {
      return new \SeopressComposer\Models\Vorteile_Data(
        $c->get(\SeopressComposer\Repositories\RemoteDataRepository::class),
        $c->get(\SeopressComposer\Services\TextReplacementService::class),
        $c->get(\SeopressComposer\Helpers\PageHelper::class),
        $c->get(\SeopressComposer\Controllers\ContextController::class),
        $c->get(\SeopressComposer\Factories\DataFactory::class),
        $c->get(\SeopressComposer\Services\IconService::class),
        $c->get(\SeopressComposer\Repositories\PageRepository::class),
        $c->get(\SeopressComposer\Repositories\CategoryRepository::class)
      );
    });

    $this->set(\SeopressComposer\Models\MainText_Data::class, function ($c) {
      return new \SeopressComposer\Models\MainText_Data(
        $c->get(\SeopressComposer\Repositories\RemoteDataRepository::class),
        $c->get(\SeopressComposer\Services\TextReplacementService::class),
        $c->get(\SeopressComposer\Helpers\PageHelper::class),
        $c->get(\SeopressComposer\Controllers\ContextController::class),
        $c->get(\SeopressComposer\Factories\DataFactory::class),
        $c->get(\SeopressComposer\Services\IconService::class),
        $c->get(\SeopressComposer\Repositories\PageRepository::class),
        $c->get(\SeopressComposer\Repositories\CategoryRepository::class)
      );
    });

    $this->set(\SeopressComposer\Models\Video_Data::class, function ($c) {
      return new \SeopressComposer\Models\Video_Data(
        $c->get(\SeopressComposer\Repositories\RemoteDataRepository::class),
        $c->get(\SeopressComposer\Services\TextReplacementService::class),
        $c->get(\SeopressComposer\Helpers\PageHelper::class),
        $c->get(\SeopressComposer\Controllers\ContextController::class),
        $c->get(\SeopressComposer\Factories\DataFactory::class),
        $c->get(\SeopressComposer\Services\IconService::class),
        $c->get(\SeopressComposer\Repositories\PageRepository::class),
        $c->get(\SeopressComposer\Repositories\CategoryRepository::class)
      );
    });

    $this->set(\SeopressComposer\Models\Image_Data::class, function ($c) {
      return new \SeopressComposer\Models\Image_Data(
        $c->get(\SeopressComposer\Repositories\RemoteDataRepository::class),
        $c->get(\SeopressComposer\Services\TextReplacementService::class),
        $c->get(\SeopressComposer\Helpers\PageHelper::class),
        $c->get(\SeopressComposer\Controllers\ContextController::class),
        $c->get(\SeopressComposer\Factories\DataFactory::class),
        $c->get(\SeopressComposer\Services\IconService::class),
        $c->get(\SeopressComposer\Repositories\PageRepository::class),
        $c->get(\SeopressComposer\Repositories\CategoryRepository::class)
      );
    });

    $this->set(\SeopressComposer\Models\Subtext_Data::class, function ($c) {
      return new \SeopressComposer\Models\Subtext_Data(
        $c->get(\SeopressComposer\Repositories\RemoteDataRepository::class),
        $c->get(\SeopressComposer\Services\TextReplacementService::class),
        $c->get(\SeopressComposer\Helpers\PageHelper::class),
        $c->get(\SeopressComposer\Controllers\ContextController::class),
        $c->get(\SeopressComposer\Factories\DataFactory::class),
        $c->get(\SeopressComposer\Services\IconService::class),
        $c->get(\SeopressComposer\Repositories\PageRepository::class),
        $c->get(\SeopressComposer\Repositories\CategoryRepository::class),
        $c->get(\SeopressComposer\Models\Image_Data::class)
      );
    });

    $this->set(\SeopressComposer\Models\Menu_Data::class, function ($c) {
      return new \SeopressComposer\Models\Menu_Data(
        $c->get(\SeopressComposer\Repositories\RemoteDataRepository::class),
        $c->get(\SeopressComposer\Services\TextReplacementService::class),
        $c->get(\SeopressComposer\Helpers\PageHelper::class),
        $c->get(\SeopressComposer\Controllers\ContextController::class),
        $c->get(\SeopressComposer\Factories\DataFactory::class),
        $c->get(\SeopressComposer\Services\IconService::class),
        $c->get(\SeopressComposer\Repositories\PageRepository::class),
        $c->get(\SeopressComposer\Repositories\CategoryRepository::class)
      );
    });

    $this->set(\SeopressComposer\Models\Kosten_Data::class, function ($c) {
      return new \SeopressComposer\Models\Kosten_Data(
        $c->get(\SeopressComposer\Repositories\RemoteDataRepository::class),
        $c->get(\SeopressComposer\Services\TextReplacementService::class),
        $c->get(\SeopressComposer\Helpers\PageHelper::class),
        $c->get(\SeopressComposer\Controllers\ContextController::class),
        $c->get(\SeopressComposer\Factories\DataFactory::class),
        $c->get(\SeopressComposer\Services\IconService::class),
        $c->get(\SeopressComposer\Repositories\PageRepository::class),
        $c->get(\SeopressComposer\Repositories\CategoryRepository::class)
      );
    });

    $this->set(\SeopressComposer\Models\CtaBoxes_Data::class, function ($c) {
      return new \SeopressComposer\Models\CtaBoxes_Data(
        $c->get(\SeopressComposer\Repositories\RemoteDataRepository::class),
        $c->get(\SeopressComposer\Services\TextReplacementService::class),
        $c->get(\SeopressComposer\Helpers\PageHelper::class),
        $c->get(\SeopressComposer\Controllers\ContextController::class),
        $c->get(\SeopressComposer\Factories\DataFactory::class),
        $c->get(\SeopressComposer\Services\IconService::class),
        $c->get(\SeopressComposer\Repositories\PageRepository::class),
        $c->get(\SeopressComposer\Repositories\CategoryRepository::class)
      );
    });

    $this->set(\SeopressComposer\Components\ServiceSection::class, function ($c) {
      return new \SeopressComposer\Components\ServiceSection(
        $c->get(\SeopressComposer\Services\IconService::class)
      );
    });

    $this->set(\SeopressComposer\Models\ServiceList_Data::class, function ($c) {
      return new \SeopressComposer\Models\ServiceList_Data(
        $c->get(\SeopressComposer\Repositories\RemoteDataRepository::class),
        $c->get(\SeopressComposer\Services\TextReplacementService::class),
        $c->get(\SeopressComposer\Helpers\PageHelper::class),
        $c->get(\SeopressComposer\Controllers\ContextController::class),
        $c->get(\SeopressComposer\Factories\DataFactory::class),
        $c->get(\SeopressComposer\Services\IconService::class),
        $c->get(\SeopressComposer\Repositories\PageRepository::class),
        $c->get(\SeopressComposer\Repositories\CategoryRepository::class)
      );
    });

    $this->set(\SeopressComposer\Models\Contact_Data::class, function ($c) {
      return new \SeopressComposer\Models\Contact_Data(
        $c->get(\SeopressComposer\Helpers\PageHelper::class)
      );
    });

    $this->set(\SeopressComposer\Models\FooterLinks_Data::class, function ($c) {
      return new \SeopressComposer\Models\FooterLinks_Data(
        $c->get(\SeopressComposer\Models\Taxonomy_Data::class)
      );
    });

    $this->set(\SeopressComposer\Models\Impressum_Data::class, function ($c) {
      return new \SeopressComposer\Models\Impressum_Data(
        $c->get(\SeopressComposer\Models\SiteSettings_Data::class)
      );
    });

    $this->set(\SeopressComposer\Models\Backlinks_Data::class, function ($c) {
      return new \SeopressComposer\Models\Backlinks_Data(
        $c->get(\SeopressComposer\Repositories\RemoteDataRepository::class),
        $c->get(\SeopressComposer\Services\TextReplacementService::class),
        $c->get(\SeopressComposer\Helpers\PageHelper::class),
        $c->get(\SeopressComposer\Controllers\ContextController::class),
        $c->get(\SeopressComposer\Factories\DataFactory::class),
        $c->get(\SeopressComposer\Services\IconService::class),
        $c->get(\SeopressComposer\Repositories\PageRepository::class),
        $c->get(\SeopressComposer\Repositories\CategoryRepository::class)
      );
    });

    $this->set(\SeopressComposer\Models\Faqs_Data::class, function ($c) {
      return new \SeopressComposer\Models\Faqs_Data(
        $c->get(\SeopressComposer\Repositories\RemoteDataRepository::class),
        $c->get(\SeopressComposer\Services\TextReplacementService::class),
        $c->get(\SeopressComposer\Helpers\PageHelper::class),
        $c->get(\SeopressComposer\Controllers\ContextController::class),
        $c->get(\SeopressComposer\Factories\DataFactory::class),
        $c->get(\SeopressComposer\Services\IconService::class),
        $c->get(\SeopressComposer\Repositories\PageRepository::class),
        $c->get(\SeopressComposer\Repositories\CategoryRepository::class)
      );
    });

    $this->set(\SeopressComposer\Models\Tips_Data::class, function ($c) {
      return new \SeopressComposer\Models\Tips_Data(
        $c->get(\SeopressComposer\Repositories\RemoteDataRepository::class),
        $c->get(\SeopressComposer\Services\TextReplacementService::class),
        $c->get(\SeopressComposer\Helpers\PageHelper::class),
        $c->get(\SeopressComposer\Controllers\ContextController::class),
        $c->get(\SeopressComposer\Factories\DataFactory::class),
        $c->get(\SeopressComposer\Services\IconService::class),
        $c->get(\SeopressComposer\Repositories\PageRepository::class),
        $c->get(\SeopressComposer\Repositories\CategoryRepository::class)
      );
    });

    $this->set(\SeopressComposer\Models\Punchline_Data::class, function ($c) {
      return new \SeopressComposer\Models\Punchline_Data(
        $c->get(\SeopressComposer\Repositories\RemoteDataRepository::class),
        $c->get(\SeopressComposer\Services\TextReplacementService::class),
        $c->get(\SeopressComposer\Helpers\PageHelper::class),
        $c->get(\SeopressComposer\Controllers\ContextController::class),
        $c->get(\SeopressComposer\Factories\DataFactory::class),
        $c->get(\SeopressComposer\Services\IconService::class),
        $c->get(\SeopressComposer\Repositories\PageRepository::class),
        $c->get(\SeopressComposer\Repositories\CategoryRepository::class)
      );
    });

    $this->set(\SeopressComposer\Models\Topbar_Data::class, function ($c) {
      return new \SeopressComposer\Models\Topbar_Data(
        $c->get(\SeopressComposer\Repositories\RemoteDataRepository::class),
        $c->get(\SeopressComposer\Services\TextReplacementService::class),
        $c->get(\SeopressComposer\Helpers\PageHelper::class),
        $c->get(\SeopressComposer\Controllers\ContextController::class),
        $c->get(\SeopressComposer\Factories\DataFactory::class),
        $c->get(\SeopressComposer\Services\IconService::class),
        $c->get(\SeopressComposer\Repositories\PageRepository::class),
        $c->get(\SeopressComposer\Repositories\CategoryRepository::class),
        $c->get(\SeopressComposer\Models\Menu_Data::class),
        $c->get(\SeopressComposer\Models\FooterLinks_Data::class)
      );
    });

    $this->set(\SeopressComposer\Models\Taxonomy_Data::class, function ($c) {
      return new \SeopressComposer\Models\Taxonomy_Data(
        $c->get(\SeopressComposer\Repositories\RemoteDataRepository::class),
        $c->get(\SeopressComposer\Services\TextReplacementService::class),
        $c->get(\SeopressComposer\Helpers\PageHelper::class),
        $c->get(\SeopressComposer\Controllers\ContextController::class),
        $c->get(\SeopressComposer\Factories\DataFactory::class),
        $c->get(\SeopressComposer\Services\IconService::class),
        $c->get(\SeopressComposer\Repositories\PageRepository::class),
        $c->get(\SeopressComposer\Repositories\CategoryRepository::class)
      );
    });

    // Layouts
    $this->set(\SeopressComposer\Layouts\OneColumn::class, function ($c) {
      return new \SeopressComposer\Layouts\OneColumn(
        $c->get(\SeopressComposer\Components\SectionTitle::class)
      );
    });
    $this->set(\SeopressComposer\Layouts\TwoColumns::class, function ($c) {
      return new \SeopressComposer\Layouts\TwoColumns(
        $c->get(\SeopressComposer\Components\SectionTitle::class)
      );
    });
    $this->set(\SeopressComposer\Layouts\ThreeColumns::class, function ($c) {
      return new \SeopressComposer\Layouts\ThreeColumns(
        $c->get(\SeopressComposer\Components\SectionTitle::class)
      );
    });
    $this->set(\SeopressComposer\Layouts\FourColumns::class, function ($c) {
      return new \SeopressComposer\Layouts\FourColumns(
        $c->get(\SeopressComposer\Components\SectionTitle::class)
      );
    });
    $this->set(\SeopressComposer\Layouts\FiveColumns::class, function ($c) {
      return new \SeopressComposer\Layouts\FiveColumns(
        $c->get(\SeopressComposer\Components\SectionTitle::class)
      );
    });
    $this->set(\SeopressComposer\Layouts\SixColumns::class, function ($c) {
      return new \SeopressComposer\Layouts\SixColumns(
        $c->get(\SeopressComposer\Components\SectionTitle::class)
      );
    });

    $this->set(\SeopressComposer\Core\LayoutsContainer::class, function ($c) {
      return new \SeopressComposer\Core\LayoutsContainer(
        $c->get(\SeopressComposer\Layouts\OneColumn::class),
        $c->get(\SeopressComposer\Layouts\TwoColumns::class),
        $c->get(\SeopressComposer\Layouts\ThreeColumns::class),
        $c->get(\SeopressComposer\Layouts\FourColumns::class),
        $c->get(\SeopressComposer\Layouts\FiveColumns::class),
        $c->get(\SeopressComposer\Layouts\SixColumns::class)
      );
    });

    $this->set(\SeopressComposer\Components\ServiceList::class, function ($c) {
      return new \SeopressComposer\Components\ServiceList(
        $c->get(\SeopressComposer\Components\ServiceSection::class)
      );
    });

    // Components with dependencies
    $this->set(\SeopressComposer\Components\VideoTextSlider::class, function ($c) {
      return new \SeopressComposer\Components\VideoTextSlider(
        $c->get(\SeopressComposer\Helpers\PageHelper::class),
        $c->get(\SeopressComposer\Controllers\ContextController::class)
      );
    });

    $this->set(\SeopressComposer\Components\Hero::class, function ($c) {
      return new \SeopressComposer\Components\Hero(
        $c->get(\SeopressComposer\Services\IconService::class)
      );
    });

    $this->set(\SeopressComposer\Components\ImageTextReadMore::class, function ($c) {
      return new \SeopressComposer\Components\ImageTextReadMore(
        $c->get(\SeopressComposer\Services\IconService::class)
      );
    });

    $this->set(\SeopressComposer\Components\LinkList::class, function ($c) {
      return new \SeopressComposer\Components\LinkList(
        $c->get(\SeopressComposer\Services\IconService::class)
      );
    });

    $this->set(\SeopressComposer\Components\MultiStepForm::class, function ($c) {
      return new \SeopressComposer\Components\MultiStepForm(
        $c->get(\SeopressComposer\Models\Taxonomy_Data::class),
        $c->get(\SeopressComposer\Services\IconService::class)
      );
    });

    $this->set(\SeopressComposer\Components\FooterWave::class, function ($c) {
      return new \SeopressComposer\Components\FooterWave();
    });

    // Controllers
    $this->set(\SeopressComposer\Controllers\MenuController::class, function ($c) {
      return new \SeopressComposer\Controllers\MenuController(
        $c->get(\SeopressComposer\Models\Menu_Data::class),
        $c->get(\SeopressComposer\Helpers\PageHelper::class),
        $c->get(\SeopressComposer\Components\Menu::class)
      );
    });

    $this->set(\SeopressComposer\Controllers\ContextController::class, function ($c) {
      return new \SeopressComposer\Controllers\ContextController();
    });
  }

  /**
   * Registriert einen Service oder eine Klasse.
   */
  public function set(string $id, $concrete = null): void
  {
    if ($concrete === null) {
      $concrete = $id;
    }
    $this->services[$id] = $concrete;
  }

  /**
   * Holt eine Instanz des angeforderten Services.
   */
  public function get(string $id)
  {
    // 1. Singleton caching
    if (isset($this->instances[$id])) {
      return $this->instances[$id];
    }

    // 2. If not registered, try autowiring
    if (!isset($this->services[$id])) {
      if (class_exists($id)) {
        return $this->resolve($id);
      }
      throw new \Exception("Service '$id' not found.");
    }

    $concrete = $this->services[$id];

    // 3. Handle Closure
    if ($concrete instanceof \Closure) {
      $this->instances[$id] = $concrete($this);
      return $this->instances[$id];
    }

    // 4. Handle Objects
    if (is_object($concrete)) {
      $this->instances[$id] = $concrete;
      return $this->instances[$id];
    }

    // 5. Resolve class name
    return $this->resolve($concrete);
  }

  /**
   * Löst eine Klasse und ihre Abhängigkeiten auf.
   */
  private function resolve(string $class)
  {
    try {
      $reflector = new ReflectionClass($class);
    } catch (ReflectionException $e) {
      throw new \Exception("Target class [$class] does not exist.", 0, $e);
    }

    if (!$reflector->isInstantiable()) {
      throw new \Exception("Class [$class] is not instantiable.");
    }

    $constructor = $reflector->getConstructor();

    if ($constructor === null) {
      $instance = new $class();
      $this->instances[$class] = $instance;
      return $instance;
    }

    $parameters = $constructor->getParameters();
    $dependencies = $this->getDependencies($parameters);

    $instance = $reflector->newInstanceArgs($dependencies);
    $this->instances[$class] = $instance;

    return $instance;
  }

  /**
   * Löst die Abhängigkeiten für den Konstruktor auf.
   */
  private function getDependencies(array $parameters): array
  {
    $dependencies = [];

    foreach ($parameters as $parameter) {
      $type = $parameter->getType();

      if ($type && !$type->isBuiltin()) {
        $dependencies[] = $this->get($type->getName());
      } else {
        if ($parameter->isDefaultValueAvailable()) {
          $dependencies[] = $parameter->getDefaultValue();
        } else {
          throw new \Exception("Cannot resolve class dependency {$parameter->name}");
        }
      }
    }

    return $dependencies;
  }

  public function has(string $id): bool
  {
    return isset($this->services[$id]) || class_exists($id);
  }
}
