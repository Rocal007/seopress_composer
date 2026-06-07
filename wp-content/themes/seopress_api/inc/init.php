<?php
/**
 * Theme Backend Initialization
 * Loads all admin functionality and components
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

class ThemeBackendLoader {
    
    private $core_path;
    private $components_path;
    
    public function __construct() {
        $this->core_path = get_template_directory() . '/inc/core/';
        $this->components_path = $this->core_path . 'components/';
        
        $this->load_core_functionality();
        $this->load_components();
    }
    
    private function load_core_functionality() {
        $core_files = [
            'admin/dienstleistungen-backend-fields.php',
            'admin/text-editor-backend-fields.php',
            'api-options.php',
            'admin/blogs-backend-fields.php',
            'admin/add_admin_colums-class.php',
            'endpoints/register-endpoints.php'
        ];
        
        foreach ($core_files as $file) {
            if (file_exists($this->core_path . $file)) {
                require_once $this->core_path . $file;
            }
        }
        
        // Icons (located in assets)
        require_once get_template_directory() . '/assets/icons_backend-fields.php';
    }
    
    private function load_components() {
        $components = [
            'banner/banner-backend-fields.php',
            'faq/faq-backend-fields.php',
            'tipps/tipps-backend-fields.php',
            'punsh_lines/punsh_lines-backend-fields.php',
            'subtexte/subtexte-backend-fields.php',
            'favorite_blogs/favorite_blogs-backend-fields.php',
            'video/video-backend-fields.php',
            'vorteile/vorteile-backend-fields.php',
            'listen/listen-backend-fields.php',
            'backlinks/backlinks-backend-fields.php',
            'cta_boxes/cta_boxes-backend-fields.php',
            'timeline/timeline-backend-fields.php',
            'main_text/main_text-backend-fields.php',
            'kosten/kosten-backend-fields.php',
            'images/images-backend-fields.php',
            'overview_texte/overview_texte-backend-fields.php',
            'claims/claims-backend-fields.php'
        ];
        
        foreach ($components as $component) {
            if (file_exists($this->components_path . $component)) {
                require_once $this->components_path . $component;
            }
        }
    }
}

// Initialize the backend loader
new ThemeBackendLoader();