# MenuController Usage

The `MenuController` provides contextual sidebar navigation that automatically adapts based on the current page context.

## Quick Start

### In any template file:

```php
<?php
// Simply echo the sidebar
echo seopress_sidebar();
?>
```

### In a two-column layout:

```php
<div class="container mx-auto px-4 py-8">
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Main Content -->
    <div class="lg:col-span-2">
      <?php the_content(); ?>
    </div>
    
    <!-- Contextual Sidebar -->
    <aside class="lg:col-span-1">
      <?php echo seopress_sidebar(); ?>
    </aside>
  </div>
</div>
```

## How It Works

The sidebar automatically detects the current page context and displays relevant navigation:

### Scenario 1: Deep Page (Service + District)
**Example:** "Antiquitäten Ankauf in Wien 1. Bezirk"

Shows:
- **"Weitere Dienstleistungen in Wien 1. Bezirk"** - Other services in the same district
- **"Diesen Service woanders"** - Same service in other districts

### Scenario 2: District Archive Page
**Example:** Viewing all pages tagged with "Wien 1. Bezirk"

Shows:
- **"Leistungen in Wien 1. Bezirk"** - All services available in this district
- **"Andere Standorte"** - Links to other districts

### Scenario 3: Service Category Archive
**Example:** Viewing all pages in "Antiquitäten Ankauf" category

Shows:
- **"Bereich Antiquitäten Ankauf"** - All pages in this service category
- **"Finden Sie uns in Ihrem Bezirk"** - List of all districts

### Scenario 4: Fallback
**Example:** Homepage or other pages

Shows:
- **"Unsere Fachbereiche"** - Overview of all services

## Advanced Usage

### Get the controller instance directly:

```php
<?php
$container = seopress_container();
$sidebar = $container->get(\SeopressComposer\Controllers\MenuController::class);

// Render the sidebar
echo $sidebar->renderSidebar();
?>
```

### Conditional rendering:

```php
<?php
// Only show sidebar on single pages
if (is_single() || is_page()) {
    echo seopress_sidebar();
}
?>
```

## Customization

The sidebar sections are styled using Tailwind CSS classes. You can customize the appearance by:

1. **Modifying the Menu component** (`inc/Components/Menu.php` - `renderSidebarGroups` method)
2. **Adjusting the section data** in `MenuController::getContextualSections()`
3. **Adding custom CSS** to override the default styles

## Architecture

```
MenuController
├── Uses Menu_Data model to get menu data
├── Uses PageHelper to determine current context
└── Uses Menu component to render the HTML

Flow:
1. Controller calls getContextualSections()
2. Determines current page context (district, service, etc.)
3. Builds array of sections with titles and menu items
4. Passes sections to Menu::renderSidebarGroups()
5. Returns rendered HTML
```

## Dependencies

- `Menu_Data` - Provides menu data based on taxonomies
- `PageHelper` - Determines current location/context
- `Menu` - Renders the sidebar HTML

All dependencies are automatically injected via the ServiceContainer.
