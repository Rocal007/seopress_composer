# SEOPress Composer - Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         SEOPRESS COMPOSER THEME                              │
│                            Version 2.0.0                                     │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                          BUILD & ASSET PIPELINE                              │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐                 │
│  │   Vite 4.4   │───▶│  Tailwind 3  │───▶│   PostCSS    │                 │
│  │  Dev Server  │    │  + DaisyUI   │    │   + SASS     │                 │
│  └──────────────┘    └──────────────┘    └──────────────┘                 │
│         │                    │                    │                         │
│         ▼                    ▼                    ▼                         │
│  ┌──────────────────────────────────────────────────────┐                 │
│  │              dist/assets/                             │                 │
│  │  ├── main.js  (Bundled JavaScript)                   │                 │
│  │  └── main.css (Compiled CSS)                         │                 │
│  └──────────────────────────────────────────────────────┘                 │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                        PHP ARCHITECTURE (PSR-4)                              │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                    ServiceContainer (DI)                             │   │
│  │                  Dependency Injection + Autowiring                   │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                    │                                        │
│         ┌──────────────────────────┼──────────────────────────┐            │
│         │                          │                           │            │
│         ▼                          ▼                           ▼            │
│  ┌─────────────┐          ┌─────────────┐           ┌─────────────┐       │
│  │ Components  │          │   Models    │           │  Services   │       │
│  │  (41 UI)    │          │ (26 Data)   │           │    (14)     │       │
│  └─────────────┘          └─────────────┘           └─────────────┘       │
│         │                          │                           │            │
│         │                          │                           │            │
│         ▼                          ▼                           ▼            │
│  ┌─────────────┐          ┌─────────────┐           ┌─────────────┐       │
│  │  Layouts    │          │  Settings   │           │   Helpers   │       │
│  │  (9 Grid)   │          │  (4 ACF)    │           │ (Utilities) │       │
│  └─────────────┘          └─────────────┘           └─────────────┘       │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                          DATA FLOW DIAGRAM                                   │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  WordPress Request                                                           │
│         │                                                                    │
│         ▼                                                                    │
│  ┌─────────────┐                                                            │
│  │ functions.php│  ◀─── Autoloader (Composer)                               │
│  └─────────────┘                                                            │
│         │                                                                    │
│         ├──▶ Initialize ServiceContainer                                    │
│         ├──▶ Register Services                                              │
│         ├──▶ Setup WordPress Hooks                                          │
│         └──▶ Initialize Settings (ACF)                                      │
│                                                                              │
│  Template Loading                                                            │
│         │                                                                    │
│         ▼                                                                    │
│  ┌─────────────┐                                                            │
│  │  header.php │───▶ template-parts/header-topbar.php                       │
│  └─────────────┘    template-parts/header-main.php                          │
│         │                                                                    │
│         ▼                                                                    │
│  ┌─────────────┐                                                            │
│  │ Template    │───▶ template-parts/content-*.php                           │
│  │ (page.php)  │                                                            │
│  └─────────────┘                                                            │
│         │                                                                    │
│         │  1. Get Components Container                                      │
│         │  2. Get Models Container                                          │
│         │  3. Fetch Data from Models                                        │
│         │  4. Render Components with Data                                   │
│         │  5. Wrap in Layouts                                               │
│         │                                                                    │
│         ▼                                                                    │
│  ┌─────────────┐                                                            │
│  │  footer.php │───▶ template-parts/footer-widgets.php                      │
│  └─────────────┘    template-parts/footer-copyright.php                     │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                        COMPONENT ARCHITECTURE                                │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  Template                                                                    │
│      │                                                                       │
│      ▼                                                                       │
│  ┌──────────────────────────────────────────────────────┐                  │
│  │  $components = seopress_components()                 │                  │
│  │  $models = seopress_models()                         │                  │
│  │  $layouts = seopress_layouts()                       │                  │
│  └──────────────────────────────────────────────────────┘                  │
│      │                                                                       │
│      ▼                                                                       │
│  ┌──────────────────────────────────────────────────────┐                  │
│  │  $data = $models->getSomeData()                      │                  │
│  │  // Returns structured array from Model              │                  │
│  └──────────────────────────────────────────────────────┘                  │
│      │                                                                       │
│      ▼                                                                       │
│  ┌──────────────────────────────────────────────────────┐                  │
│  │  $html = $components->getSomeComponent()             │                  │
│  │                     ->render($data)                  │                  │
│  │  // Component generates HTML from data               │                  │
│  └──────────────────────────────────────────────────────┘                  │
│      │                                                                       │
│      ▼                                                                       │
│  ┌──────────────────────────────────────────────────────┐                  │
│  │  echo $layouts->render($html, 3, $options)           │                  │
│  │  // Wraps component in responsive grid               │                  │
│  └──────────────────────────────────────────────────────┘                  │
│      │                                                                       │
│      ▼                                                                       │
│  Final HTML Output                                                           │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                         CACHING STRATEGY                                     │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                    WordPress Object Cache                            │   │
│  │                                                                       │   │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐              │   │
│  │  │  Menu Cache  │  │  Term Cache  │  │ Options Cache│              │   │
│  │  │   (1 hour)   │  │   (1 hour)   │  │   (Primed)   │              │   │
│  │  └──────────────┘  └──────────────┘  └──────────────┘              │   │
│  │                                                                       │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                    │                                        │
│                          Cache Invalidation                                 │
│                                    │                                        │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  Triggers:                                                           │   │
│  │  • save_post          → Clear menu cache                            │   │
│  │  • delete_post        → Clear menu cache                            │   │
│  │  • edited_term        → Clear taxonomy cache                        │   │
│  │  • create_term        → Clear taxonomy cache                        │   │
│  │  • delete_term        → Clear taxonomy cache                        │   │
│  │  • set_object_terms   → Clear taxonomy cache                        │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                      JAVASCRIPT ARCHITECTURE                                 │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  assets/js/main.js (Entry Point)                                             │
│         │                                                                    │
│         ├──▶ Import SCSS                                                    │
│         ├──▶ Import Fonts (Latin-400 only)                                  │
│         └──▶ Import Components                                              │
│                                                                              │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                      JS Components (8)                               │   │
│  │                                                                       │   │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐              │   │
│  │  │   header.js  │  │   menu.js    │  │   modal.js   │              │   │
│  │  │ (Scroll Hide)│  │ (Navigation) │  │  (Dialogs)   │              │   │
│  │  └──────────────┘  └──────────────┘  └──────────────┘              │   │
│  │                                                                       │   │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐              │   │
│  │  │multi-step-   │  │  slider.js   │  │   tabs.js    │              │   │
│  │  │  form.js     │  │ (Carousels)  │  │  (Tabbed)    │              │   │
│  │  └──────────────┘  └──────────────┘  └──────────────┘              │   │
│  │                                                                       │   │
│  │  ┌──────────────┐  ┌──────────────┐                                 │   │
│  │  │  toggle.js   │  │video-slider  │                                 │   │
│  │  │  (Switches)  │  │    .js       │                                 │   │
│  │  └──────────────┘  └──────────────┘                                 │   │
│  │                                                                       │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│         │                                                                    │
│         ▼                                                                    │
│  DOMContentLoaded Event                                                      │
│         │                                                                    │
│         └──▶ Initialize all components                                      │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                       DEPLOYMENT ARCHITECTURE                                │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  Development                                                                 │
│      │                                                                       │
│      ├──▶ npm run dev    (Vite Dev Server)                                  │
│      │                                                                       │
│      └──▶ npm run build  (Production Build)                                 │
│            │                                                                 │
│            ▼                                                                 │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                      npm run bundle                                  │   │
│  │                                                                       │   │
│  │  1. npm run clean     → Remove old bundle                           │   │
│  │  2. npm run build     → Build production assets                     │   │
│  │  3. npm run copy:php  → Copy PHP files                              │   │
│  │  4. npm run copy:assets → Copy dist folder                          │   │
│  │  5. npm run copy:dirs → Copy inc, template-parts, vendor, assets    │   │
│  │                                                                       │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│            │                                                                 │
│            ▼                                                                 │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                      theme_bundle/                                   │   │
│  │  Complete deployable theme package                                   │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│            │                                                                 │
│            ▼                                                                 │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                    npm run deploy                                    │   │
│  │                                                                       │   │
│  │  FTP Upload to:                                                      │   │
│  │  • 1AAntiquitaeten (ftp.world4you.com)                              │   │
│  │  • Antik-ankauf-wien (ftp.world4you.com)                            │   │
│  │  • Antik-noe (ftp.world4you.com)                                    │   │
│  │                                                                       │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│            │                                                                 │
│            ▼                                                                 │
│  Production Sites (Live)                                                     │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                        WORDPRESS INTEGRATION                                 │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                    WordPress Core Hooks                              │   │
│  │                                                                       │   │
│  │  • after_setup_theme  → Register theme features                     │   │
│  │  • init               → Register post types & taxonomies             │   │
│  │  • wp_enqueue_scripts → Enqueue assets (Vite)                       │   │
│  │  • wp_head            → Resource hints, meta tags                   │   │
│  │  • save_post          → Cache invalidation                          │   │
│  │  • edited_term        → Cache invalidation                          │   │
│  │                                                                       │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                              │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                   Custom Taxonomies (4)                              │   │
│  │                                                                       │   │
│  │  • district           → Locations (root level URLs)                 │   │
│  │  • service_category   → Service categories                          │   │
│  │  • ankauf_kategorie   → Purchase categories                         │   │
│  │  • menu_category      → Menu organization                           │   │
│  │                                                                       │   │
│  │  All with GraphQL support + REST API                                │   │
│  │                                                                       │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                              │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                  Advanced Custom Fields (ACF)                        │   │
│  │                                                                       │   │
│  │  • SiteSettings           → Global settings (34KB)                  │   │
│  │  • CategoryFields         → Category metadata                       │   │
│  │  • LocationSelectionPage  → Location config (11KB)                  │   │
│  │  • ServiceSelectionPage   → Service config (38KB)                   │   │
│  │                                                                       │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                              │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                       GraphQL API                                    │   │
│  │                                                                       │   │
│  │  • Custom post types exposed                                        │   │
│  │  • Taxonomies exposed                                               │   │
│  │  • ACF fields exposed                                               │   │
│  │  • API endpoint: /graphql                                           │   │
│  │                                                                       │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                      PERFORMANCE OPTIMIZATIONS                               │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  Frontend Performance                                                        │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  • Font subsetting (Latin-400 only)                                 │   │
│  │  • font-display: swap                                               │   │
│  │  • Resource hints (dns-prefetch, preconnect)                        │   │
│  │  • Critical CSS extraction                                          │   │
│  │  • Image optimization service                                       │   │
│  │  • SVG icon system (no icon fonts)                                  │   │
│  │  • Tailwind CSS purging                                             │   │
│  │  • Vite code splitting                                              │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                              │
│  Backend Performance                                                         │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  • WordPress object cache (menus, terms, options)                   │   │
│  │  • Options cache priming                                            │   │
│  │  • Term cache priming                                               │   │
│  │  • Transient caching                                                │   │
│  │  • Query optimization                                               │   │
│  │  • Lazy loading components                                          │   │
│  │  • Optimized autoloader (Composer)                                  │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                              │
│  Database Performance                                                        │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  • Prevent redundant queries                                        │   │
│  │  • Batch query operations                                           │   │
│  │  • Index optimization                                               │   │
│  │  • Query result caching                                             │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                            KEY METRICS                                       │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  Code Statistics                                                             │
│  • Total Components:      41 PHP components                                 │
│  • Total Models:          26 data models                                    │
│  • Total Services:        14 services                                       │
│  • Total Layouts:         9 layout containers                               │
│  • Total Templates:       15 page templates                                 │
│  • Total Template Parts:  22 template parts                                 │
│  • Total JS Modules:      8 components                                      │
│  • Total Icons:           124 SVG files                                     │
│  • Total Lines of Code:   ~10,000+ lines                                    │
│                                                                              │
│  Largest Files                                                               │
│  • ServiceSelectionPage:  38.6 KB                                           │
│  • SiteSettings:          34.5 KB                                           │
│  • ServiceContainer:      24.8 KB                                           │
│  • functions.php:         21.4 KB                                           │
│  • Menu_Data:             21.3 KB                                           │
│  • Taxonomy_Data:         19.6 KB                                           │
│                                                                              │
│  Dependencies                                                                │
│  • PHP:                   >= 7.4                                            │
│  • Composer Packages:     3                                                 │
│  • NPM Packages:          20 (dev) + 7 (runtime)                            │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

**Architecture Diagram Version:** 1.0  
**Generated:** 2026-02-16  
**Theme Version:** 2.0.0
