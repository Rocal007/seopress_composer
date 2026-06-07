# SEOPress Composer - Quick Reference Guide

## 🚀 Quick Start Commands

```bash
# Development
npm run dev              # Start Vite dev server (port 5173)
npm run build            # Build production assets

# Deployment
npm run bundle           # Create theme bundle
npm run deploy           # Deploy to production (FTP)

# Utilities
npm run clean            # Remove theme_bundle directory
composer install         # Install PHP dependencies
```

---

## 📁 Directory Structure

```
seopress_composer/
├── 📄 functions.php              # Main theme setup (641 lines)
├── 📄 header.php                 # HTML head
├── 📄 footer.php                 # Footer & closing tags
├── 📄 style.css                  # Theme metadata
├── 📄 tailwind.config.js         # Tailwind configuration
├── 📄 vite.config.js             # Vite build config
├── 📄 package.json               # NPM dependencies
├── 📄 composer.json              # PHP dependencies
├── 📄 .env                       # FTP deployment credentials
│
├── 📂 inc/                       # PHP Classes (PSR-4)
│   ├── 📂 Components/            # 41 UI components
│   ├── 📂 Models/                # 26 data models
│   ├── 📂 Services/              # 14 services
│   ├── 📂 Layouts/               # 9 layout containers
│   ├── 📂 Core/                  # DI Container, ACF Manager
│   ├── 📂 Settings/              # 4 ACF settings pages
│   ├── 📂 Helpers/               # Utility classes
│   ├── 📂 Repositories/          # Data access layer
│   ├── 📂 Controllers/           # Business logic
│   └── 📂 Factories/             # Object creation
│
├── 📂 template-parts/            # 22 template parts
│   ├── content-*.php             # Content templates
│   ├── header-*.php              # Header parts
│   └── footer-*.php              # Footer parts
│
├── 📂 assets/                    # Source assets
│   ├── 📂 js/                    # JavaScript modules
│   │   ├── main.js               # Entry point
│   │   └── components/           # 8 JS components
│   ├── 📂 scss/                  # SASS stylesheets
│   │   ├── main.scss             # Main stylesheet
│   │   └── components/           # Component styles
│   ├── 📂 icons/                 # 124 SVG icons
│   ├── 📂 img/                   # Images
│   └── 📂 css/                   # Compiled CSS
│
├── 📂 dist/                      # Compiled assets (production)
├── 📂 vendor/                    # Composer dependencies
├── 📂 node_modules/              # NPM dependencies
└── 📂 theme_bundle/              # Deployment bundle
```

---

## 🎨 Component Quick Reference

### Content Components
```php
$components->getAccordion()          // FAQ/collapsible sections
$components->getCard()               // Content cards
$components->getGroupedCards()       // Categorized cards
$components->getImageTextReadMore()  // Image + text expandable
$components->getMainText()           // SEO main content
$components->getSectionTitle()       // Section headers
```

### Navigation Components
```php
$components->getMenu()               // Main navigation
$components->getMegaMenu()           // Dropdown mega menu
$components->getBreadcrumb()         // Breadcrumbs
$components->getIconLinks()          // Icon-based links
$components->getLinkList()           // Styled link lists
$components->getBacklinks()          // Related links
```

### Interactive Components
```php
$components->getTabs()               // Tabbed content
$components->getLeftRightToggle()    // Two-panel toggle
$components->getSlider()             // Image slider
$components->getVideoTextSlider()    // Video carousel
$components->getMultiStepForm()      // Form wizard
```

### Marketing Components
```php
$components->getHero()               // Hero section
$components->getPunchline()          // CTA messages
$components->getContactCTA()         // Contact CTA
$components->getCtaBoxes()           // CTA grid
$components->getPricingCard()        // Pricing tables
```

### Visual Components
```php
$components->getLogo()               // SVG logo
$components->getProcessCircle()      // Process visualization
$components->getNumberedFeatures()   // Numbered features
$components->getListTooltip()        // List with tooltips
$components->getFooterWave()         // Decorative wave
```

---

## 📊 Model Quick Reference

```php
$models->getTopPictureData()         // Hero images
$models->getMainTextData()           // Main content
$models->getPunchlineData()          // CTA punchlines
$models->getVorteileData()           // Benefits/features
$models->getSubtextData()            // Subtext sections
$models->getKostenData()             // Pricing data
$models->getCtaBoxesData()           // CTA boxes
$models->getVideoModel()             // Video content
$models->getFaqsData()               // FAQ content
$models->getTipsData()               // Tips content
$models->getBacklinksData()          // Related links
$models->getDistrictsData()          // Location data
$models->getOtherServices()          // Other services
$models->get_all_services_grouped()  // Grouped services
$models->getContactData()            // Contact info
$models->getLogoData()               // Logo variants
$models->getMenuModel()              // Navigation menus
$models->getFooterLinksData()        // Footer links
```

---

## 🔧 Service Quick Reference

```php
// Get service from container
$container = seopress_container();
$service = $container->get(ServiceClass::class);

// Available Services
IconService                          // SVG icon management
TextReplacementService               // Dynamic text replacement
PageDataService                      // Page data aggregation
SchemaService                        // Schema.org markup
ThemeCustomizerService               // WordPress Customizer
FaviconService                       // Favicon handling
SmtpService                          // Email configuration
LocationService                      // Location management
RemoteRouteService                   // API routing
CriticalCssService                   // Critical CSS
ImageOptimizationService             // Image optimization
WordPressOptimizationService         // DB optimization
WordPressSetupService                // Theme initialization
```

---

## 🎯 Layout Quick Reference

```php
// Render content in layout
$layouts->render($content, $columns, $options);

// Available Layouts
1 column  - OneColumn      // Full width
2 columns - TwoColumns     // 50/50 split
3 columns - ThreeColumns   // 33/33/33
4 columns - FourColumns    // 25/25/25/25
5 columns - FiveColumns    // 20/20/20/20/20
6 columns - SixColumns     // Grid layout

// Special Layouts
FooterLinks                // Footer navigation
Navbar                     // Header navigation
Topbar                     // Top bar layout
```

---

## 🎨 CSS Variables

```css
/* Colors */
--color-primary
--color-secondary
--color-accent
--color-neutral
--color-background-primary
--color-info
--color-success
--color-warning
--color-danger

/* Typography */
--font-body
--font-heading
--font-heading-weight

/* Custom Classes */
.bg-silver-shine           /* Silver gradient */
.bg-gold-shine             /* Gold gradient */
.text-shimmer              /* Gray shimmer text */
.text-gold-shimmer         /* Gold shimmer text */

/* Icon Styles */
.illu                      /* Illustration stroke */
.rahmen                    /* Frame stroke */
.illu-invert               /* Inverted illustration */
.rahmen-invert             /* Inverted frame */

/* Buttons */
.btn-primary               /* Primary button */
.btn-secondary             /* Secondary button */
.btn-accent                /* Accent button */
```

---

## 📝 Template Usage

### Page Templates
```php
// Assign in WordPress admin
template-startseite.php        // Homepage
template-hauptseiten.php       // Main pages
template-kontakt.php           // Contact
template-ueber-uns.php         // About
template-kosten.php            // Pricing
template-locations.php         // Locations
template-faq.php               // FAQ
template-blog.php              // Blog
```

### Template Parts
```php
// Include in templates
get_template_part('template-parts/header', 'topbar');
get_template_part('template-parts/header', 'main');
get_template_part('template-parts/content', 'startseite');
get_template_part('template-parts/footer', 'widgets');
```

---

## 🔌 Helper Functions

```php
// Container Access
seopress_container()           // Get DI container
seopress_components()          // Get components container
seopress_models()              // Get models container
seopress_layouts()             // Get layouts container
seopress_page_data()           // Get page data service
seopress_sidebar()             // Get contextual sidebar

// Usage Example
$components = seopress_components();
$hero = $components->getHero();
echo $hero->render($data);
```

---

## 🎯 Common Patterns

### Rendering a Component
```php
// 1. Get dependencies
$components = seopress_components();
$models = seopress_models();

// 2. Fetch data
$data = $models->getSomeData();

// 3. Render component
echo $components->getSomeComponent()->render($data);
```

### Using Layouts
```php
// Render component in layout
echo $layouts->render(
    $components->getCard()->render($data),
    3,  // 3 columns
    [
        'wrapper_class' => 'py-16 bg-base-200',
        'container_class' => 'container mx-auto px-4',
        'title' => 'Section Title',
        'description' => 'Section description'
    ]
);
```

### Multiple Components
```php
// Render multiple items
echo $components->getCard()->renderMany($items);
echo $components->getImageTextReadMore()->renderManyFromModel($data, [0, 1, 2]);
```

---

## 🚀 Performance Tips

### Caching
```php
// Menu caching (automatic)
wp_cache_get('formatted_menu_' . $menu_id, 'menus');

// Category caching (automatic)
wp_cache_get('formatted_cat_' . $cat_id, 'categories');

// Clear cache manually
Taxonomy_Data::clear_cache();
```

### Asset Loading
```php
// Fonts (only load what you need)
import "@fontsource/inter/latin-400.css";

// Critical CSS
CriticalCssService::extract_critical_css();

// Image optimization
ImageOptimizationService::optimize_image($image_id);
```

---

## 🐛 Debugging

### Enable Vite Dev Mode
```bash
npm run dev
# Access site at: http://antik-live.test
# Vite server: http://localhost:5173
```

### Check Container
```php
// List all registered services
$container = seopress_container();
var_dump($container->has('ServiceName'));
```

### Clear Caches
```php
// WordPress object cache
wp_cache_flush();

// Rewrite rules
flush_rewrite_rules();

// Transients
delete_transient('transient_name');
```

---

## 📚 File Locations

### Key Configuration Files
```
tailwind.config.js         # Tailwind configuration
vite.config.js             # Vite build settings
postcss.config.js          # PostCSS plugins
package.json               # NPM scripts & dependencies
composer.json              # PHP autoloading & dependencies
.env                       # FTP deployment credentials
```

### Entry Points
```
assets/js/main.js          # JavaScript entry
assets/scss/main.scss      # CSS entry
functions.php              # PHP entry
```

### Compiled Output
```
dist/assets/main.js        # Compiled JavaScript
dist/assets/main.css       # Compiled CSS
dist/.vite/manifest.json   # Asset manifest
```

---

## 🎓 Best Practices

### Component Development
1. Extend base component class if needed
2. Use dependency injection for services
3. Keep components focused and reusable
4. Document parameters and return types

### Model Development
1. Extend `BaseModel`
2. Implement caching where appropriate
3. Return structured data arrays
4. Handle missing data gracefully

### Service Development
1. Single responsibility principle
2. Use constructor injection for dependencies
3. Register in `ServiceContainer::register_defaults()`
4. Add proper error handling

### Template Development
1. Separate logic from presentation
2. Use template parts for reusability
3. Escape all output
4. Follow WordPress coding standards

---

**Quick Reference Version:** 1.0  
**Last Updated:** 2026-02-16  
**Theme Version:** 2.0.0
