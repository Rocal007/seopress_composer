<?php

namespace SeopressComposer\Services;

use SeopressComposer\Helpers\PageHelper;

/**
 * LLMs.txt Service
 * 
 * Generates llms.txt and llms-full.txt files for AI/LLM crawler consumption.
 * Implements the 2026 llms.txt specification for optimal AI search visibility.
 * 
 * @see https://llmstxt.org/
 */
class LlmsTxtService
{
    private PageHelper $pageHelper;

    public function __construct(PageHelper $pageHelper)
    {
        $this->pageHelper = $pageHelper;
        add_action('init', [$this, 'register_endpoints']);
        add_action('template_redirect', [$this, 'handle_request']);
    }

    /**
     * Register custom rewrite rules for llms.txt
     */
    public function register_endpoints(): void
    {
        add_rewrite_rule('^llms\.txt$', 'index.php?llms_txt=1', 'top');
        add_rewrite_rule('^llms-full\.txt$', 'index.php?llms_full_txt=1', 'top');
        add_filter('query_vars', function ($vars) {
            $vars[] = 'llms_txt';
            $vars[] = 'llms_full_txt';
            return $vars;
        });
    }

    /**
     * Handle llms.txt requests
     */
    public function handle_request(): void
    {
        if (get_query_var('llms_txt')) {
            $this->output_llms_txt();
            exit;
        }
        if (get_query_var('llms_full_txt')) {
            $this->output_llms_full_txt();
            exit;
        }
    }

    /**
     * Output the compact llms.txt
     */
    private function output_llms_txt(): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        header('X-Robots-Tag: noindex');
        
        $business = $this->pageHelper->get_options_business();
        $site_name = get_bloginfo('name');
        $site_desc = get_bloginfo('description');
        $home = home_url('/');

        $lines = [];
        $lines[] = "# {$site_name}";
        $lines[] = "> {$site_desc}";
        $lines[] = "";
        $lines[] = "Dies ist ein lokales Dienstleistungsunternehmen in Österreich.";
        $lines[] = "Website: {$home}";
        $lines[] = "";

        // Service Categories
        $categories = get_terms([
            'taxonomy' => 'service_category',
            'hide_empty' => false,
            'orderby' => 'name',
        ]);

        if (!empty($categories) && !is_wp_error($categories)) {
            $lines[] = "## Leistungskategorien";
            foreach ($categories as $cat) {
                $page = get_page_by_path('service-kategorie/' . $cat->slug);
                $url = $page ? get_permalink($page->ID) : get_term_link($cat);
                $lines[] = "- [{$cat->name}]({$url})";
            }
            $lines[] = "";
        }

        // Main Services
        $service_pages = get_pages([
            'meta_key' => '_wp_page_template',
            'meta_value' => 'template-hauptseiten.php',
            'sort_column' => 'post_title',
            'parent' => 0,
        ]);

        if (!empty($service_pages)) {
            $lines[] = "## Hauptleistungen";
            foreach (array_slice($service_pages, 0, 20) as $page) {
                $url = get_permalink($page->ID);
                $lines[] = "- [{$page->post_title}]({$url})";
            }
            $lines[] = "";
        }

        // Districts
        $districts = get_terms([
            'taxonomy' => 'district',
            'hide_empty' => false,
            'orderby' => 'name',
            'number' => 30,
        ]);

        if (!empty($districts) && !is_wp_error($districts)) {
            $lines[] = "## Einsatzgebiete";
            $district_names = array_map(fn($d) => $d->name, $districts);
            $lines[] = implode(', ', $district_names);
            $lines[] = "";
        }

        // Contact
        $lines[] = "## Kontakt";
        if (!empty($business['telefonnummer'])) {
            $lines[] = "- Telefon: {$business['telefonnummer']}";
        }
        if (!empty($business['e-mail'])) {
            $lines[] = "- E-Mail: {$business['e-mail']}";
        }
        if (!empty($business['strasse'])) {
            $address = $business['strasse'];
            if (!empty($business['plz'])) $address .= ', ' . $business['plz'];
            if (!empty($business['einsatzgebiet'])) $address .= ' ' . $business['einsatzgebiet'];
            $lines[] = "- Adresse: {$address}";
        }
        $lines[] = "";

        // Business Info
        if (!empty($business['inhaber'])) {
            $lines[] = "## Unternehmen";
            $lines[] = "- Geschäftsführer: {$business['inhaber']}";
            if (!empty($business['uid'])) $lines[] = "- UID: {$business['uid']}";
            if (!empty($business['firmenbuchnummer'])) $lines[] = "- FN: {$business['firmenbuchnummer']}";
            $lines[] = "";
        }

        $lines[] = "## Weitere Informationen";
        $lines[] = "- [Ausführliche Version](" . home_url('/llms-full.txt') . ")";
        $lines[] = "- [Sitemap](" . home_url('/wp-sitemap.xml') . ")";

        echo implode("\n", $lines);
    }

    /**
     * Output the full llms-full.txt with extended content
     */
    private function output_llms_full_txt(): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        header('X-Robots-Tag: noindex');

        $business = $this->pageHelper->get_options_business();
        $site_name = get_bloginfo('name');
        $site_desc = get_bloginfo('description');

        $lines = [];
        $lines[] = "# {$site_name} — Vollständige Informationen";
        $lines[] = "> {$site_desc}";
        $lines[] = "";
        $lines[] = "## Über das Unternehmen";
        $lines[] = "";
        $lines[] = "{$site_name} ist ein professionelles Dienstleistungsunternehmen mit Sitz in " . ($business['einsatzgebiet'] ?? 'Österreich') . ".";
        $lines[] = "Wir bieten umfassende Entrümpelungs-, Räumungs- und Verwertungsdienstleistungen an.";
        $lines[] = "";

        // All service categories with their child pages
        $categories = get_terms([
            'taxonomy' => 'service_category',
            'hide_empty' => false,
            'orderby' => 'name',
        ]);

        if (!empty($categories) && !is_wp_error($categories)) {
            $lines[] = "## Leistungsübersicht";
            $lines[] = "";

            foreach ($categories as $cat) {
                $cat_page = get_page_by_path('service-kategorie/' . $cat->slug);
                $cat_url = $cat_page ? get_permalink($cat_page->ID) : get_term_link($cat);
                $lines[] = "### {$cat->name}";
                $lines[] = "URL: {$cat_url}";
                if (!empty($cat->description)) {
                    $lines[] = strip_tags($cat->description);
                }
                $lines[] = "";

                // Get pages assigned to this category
                $pages = get_posts([
                    'post_type' => 'page',
                    'posts_per_page' => 50,
                    'tax_query' => [
                        [
                            'taxonomy' => 'service_category',
                            'field' => 'term_id',
                            'terms' => $cat->term_id,
                        ]
                    ],
                    'post_parent' => 0,
                    'meta_key' => '_wp_page_template',
                    'meta_value' => 'template-hauptseiten.php',
                ]);

                if (!empty($pages)) {
                    foreach ($pages as $page) {
                        $lines[] = "- [{$page->post_title}](" . get_permalink($page->ID) . ")";
                    }
                    $lines[] = "";
                }
            }
        }

        // Pricing info if available
        $lines[] = "## Preisgestaltung";
        $lines[] = "- Transparente Fixpreise nach Besichtigung";
        $lines[] = "- Kostenlose Besichtigung und Angebotserstellung";
        $lines[] = "- Wertgegenrechnung bei verwertbaren Gegenständen";
        $lines[] = "- Details: " . home_url('/kosten/');
        $lines[] = "";

        // Districts
        $districts = get_terms([
            'taxonomy' => 'district',
            'hide_empty' => false,
            'orderby' => 'name',
        ]);

        if (!empty($districts) && !is_wp_error($districts)) {
            $lines[] = "## Einsatzgebiete";
            $lines[] = "";
            foreach ($districts as $d) {
                $lines[] = "- [{$d->name}](" . get_term_link($d) . ")";
            }
            $lines[] = "";
        }

        // Full contact block
        $lines[] = "## Kontakt & Erreichbarkeit";
        $lines[] = "";
        if (!empty($business['inhaber'])) $lines[] = "- Geschäftsführer: {$business['inhaber']}";
        if (!empty($business['strasse'])) $lines[] = "- Adresse: {$business['strasse']}, {$business['plz']} {$business['einsatzgebiet']}";
        if (!empty($business['telefonnummer'])) $lines[] = "- Telefon: {$business['telefonnummer']}";
        if (!empty($business['festnetznummer'])) $lines[] = "- Festnetz: {$business['festnetznummer']}";
        if (!empty($business['e-mail'])) $lines[] = "- E-Mail: {$business['e-mail']}";
        if (!empty($business['homepage'])) $lines[] = "- Website: {$business['homepage']}";
        if (!empty($business['uid'])) $lines[] = "- UID-Nr: {$business['uid']}";
        if (!empty($business['firmenbuchnummer'])) $lines[] = "- Firmenbuch: {$business['firmenbuchnummer']}";
        if (!empty($business['gln'])) $lines[] = "- GLN: {$business['gln']}";
        $lines[] = "";
        $lines[] = "## Öffnungszeiten";
        $lines[] = "- Mo-Fr: 08:00-20:00";
        $lines[] = "- Sa-So: 10:00-18:00";

        echo implode("\n", $lines);
    }
}
