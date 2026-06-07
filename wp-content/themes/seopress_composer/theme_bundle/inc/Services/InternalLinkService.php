<?php

namespace SeopressComposer\Services;

use SeopressComposer\Helpers\PageHelper;

/**
 * Internal Link Service
 * 
 * Maximizes PageRank flow through automated contextual internal linking.
 * Implements silo-aware cross-linking between services, categories, and districts.
 * Adds hidden crawler nav for maximum discoverability.
 */
class InternalLinkService
{
    private PageHelper $pageHelper;
    private static ?array $cached_links = null;

    public function __construct(PageHelper $pageHelper)
    {
        $this->pageHelper = $pageHelper;
        add_action('wp_footer', [$this, 'render_crawler_nav'], 5);
        add_filter('the_content', [$this, 'inject_contextual_links'], 15);
    }

    /**
     * Render hidden crawler navigation in footer
     * Ensures all important pages are discoverable by crawlers
     * Uses aria-hidden + visually-hidden for accessibility compliance
     */
    public function render_crawler_nav(): void
    {
        if (is_admin() || is_feed()) return;

        $links = $this->get_all_internal_links();
        if (empty($links)) return;

        echo "\n<!-- Internal Link Hub (Crawler Nav) -->\n";
        echo '<nav aria-hidden="true" class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap">';
        echo '<ul>';

        foreach ($links as $link) {
            echo '<li><a href="' . esc_url($link['url']) . '" tabindex="-1">' . esc_html($link['title']) . '</a></li>';
        }

        echo '</ul>';
        echo '</nav>';
        echo "\n<!-- /Internal Link Hub -->\n";
    }

    /**
     * Inject contextual internal links into content
     * Adds "Related Services" and "Nearby Districts" after main content
     */
    public function inject_contextual_links(string $content): string
    {
        if (is_admin() || !is_singular('page') || is_front_page()) {
            return $content;
        }

        $post_id = get_the_ID();
        $template = get_post_meta($post_id, '_wp_page_template', true);

        // Only inject on service pages (Hauptseiten)
        if ($template !== 'template-hauptseiten.php') {
            return $content;
        }

        $contextual_html = $this->build_contextual_links($post_id);
        if (!empty($contextual_html)) {
            $content .= $contextual_html;
        }

        return $content;
    }

    /**
     * Build contextual internal links for a service page
     */
    private function build_contextual_links(int $post_id): string
    {
        $html = '';

        // 1. Sibling services in same category
        $categories = wp_get_post_terms($post_id, 'service_category');
        if (!empty($categories) && !is_wp_error($categories)) {
            foreach ($categories as $cat) {
                $siblings = get_posts([
                    'post_type' => 'page',
                    'posts_per_page' => 6,
                    'post__not_in' => [$post_id],
                    'post_parent' => 0,
                    'meta_key' => '_wp_page_template',
                    'meta_value' => 'template-hauptseiten.php',
                    'tax_query' => [[
                        'taxonomy' => 'service_category',
                        'terms' => $cat->term_id,
                    ]],
                ]);

                if (!empty($siblings)) {
                    $html .= '<aside class="contextual-links py-8 mt-8 border-t border-base-200" aria-label="Verwandte Services">';
                    $html .= '<h3 class="text-lg font-semibold mb-4">Weitere ' . esc_html($cat->name) . ' Services</h3>';
                    $html .= '<ul class="grid grid-cols-2 md:grid-cols-3 gap-3">';
                    foreach ($siblings as $sibling) {
                        $html .= '<li><a href="' . get_permalink($sibling->ID) . '" class="text-primary hover:underline">';
                        $html .= esc_html($sibling->post_title) . '</a></li>';
                    }
                    $html .= '</ul>';

                    // Link to category page
                    // Correctly resolve taxonomy term link (get_page_by_path does NOT work for taxonomy archives)
                    $cat_term = get_term_by('slug', $cat->slug, 'service_category');
                    if ($cat_term && !is_wp_error(get_term_link($cat_term))) {
                        $html .= '<p class="mt-4"><a href="' . esc_url(get_term_link($cat_term)) . '" class="font-medium text-primary hover:underline">';
                        $html .= '→ Alle ' . esc_html($cat->name) . ' Services ansehen</a></p>';
                    }
                    $html .= '</aside>';
                }
            }
        }

        // 2. District cross-links for this service
        $districts = wp_get_post_terms($post_id, 'district');
        $parent = get_post_parent($post_id);
        if (empty($districts) && $parent) {
            // This is a child page — get parent's districts for cross-link
            $parent_title = $parent->post_title;
            $sibling_districts = get_posts([
                'post_type' => 'page',
                'posts_per_page' => 8,
                'post__not_in' => [$post_id],
                'post_parent' => $parent->ID,
            ]);

            if (!empty($sibling_districts)) {
                $html .= '<aside class="contextual-links py-8 mt-4 border-t border-base-200" aria-label="Weitere Standorte">';
                $html .= '<h3 class="text-lg font-semibold mb-4">' . esc_html($parent_title) . ' in weiteren Bezirken</h3>';
                $html .= '<ul class="grid grid-cols-2 md:grid-cols-4 gap-3">';
                foreach ($sibling_districts as $sd) {
                    $html .= '<li><a href="' . get_permalink($sd->ID) . '" class="text-primary hover:underline">';
                    $html .= esc_html($sd->post_title) . '</a></li>';
                }
                $html .= '</ul></aside>';
            }
        }

        return $html;
    }

    /**
     * Get all important internal links for crawler nav
     */
    private function get_all_internal_links(): array
    {
        if (self::$cached_links !== null) {
            return self::$cached_links;
        }

        $links = [];

        // 1. Category hub pages
        $cat_pages = get_pages(['parent' => 0, 'sort_column' => 'post_title']);
        $cat_parent = get_page_by_path('service-kategorie');
        if ($cat_parent) {
            $cat_children = get_pages(['parent' => $cat_parent->ID]);
            foreach ($cat_children as $cp) {
                $links[] = ['url' => get_permalink($cp->ID), 'title' => $cp->post_title];
            }
        }

        // 2. Top-level service pages (Hauptseiten)
        $services = get_pages([
            'meta_key' => '_wp_page_template',
            'meta_value' => 'template-hauptseiten.php',
            'parent' => 0,
            'sort_column' => 'post_title',
            'number' => 50,
        ]);
        foreach ($services as $s) {
            $links[] = ['url' => get_permalink($s->ID), 'title' => $s->post_title];
        }

        // 3. District taxonomy archive pages (only if they have indexed pages — prevents thin content)
        $districts = get_terms(['taxonomy' => 'district', 'hide_empty' => true, 'number' => 30]);
        if (!empty($districts) && !is_wp_error($districts)) {
            foreach ($districts as $d) {
                // Skip districts with fewer than 2 pages to avoid thin content signals
                if ($d->count < 2) continue;
                $term_link = get_term_link($d);
                if (!is_wp_error($term_link)) {
                    $links[] = ['url' => $term_link, 'title' => $d->name];
                }
            }
        }

        // 4. Service category taxonomy archive pages
        $cats = get_terms(['taxonomy' => 'service_category', 'hide_empty' => false]);
        if (!empty($cats) && !is_wp_error($cats)) {
            foreach ($cats as $c) {
                $term_link = get_term_link($c);
                if (!is_wp_error($term_link)) {
                    $links[] = ['url' => $term_link, 'title' => $c->name];
                }
            }
        }

        // 5. Static important pages
        $important_slugs = ['kontakt', 'kosten', 'ratgeber', 'ueber-uns', 'faq'];
        foreach ($important_slugs as $slug) {
            $page = get_page_by_path($slug);
            if ($page) {
                $links[] = ['url' => get_permalink($page->ID), 'title' => $page->post_title];
            }
        }

        // De-duplicate by URL
        $seen = [];
        $unique = [];
        foreach ($links as $link) {
            if (!isset($seen[$link['url']])) {
                $seen[$link['url']] = true;
                $unique[] = $link;
            }
        }

        self::$cached_links = $unique;
        return $unique;
    }

    /**
     * Get related service links for a given page
     * Used by templates to render contextual link blocks
     */
    public function get_related_services(int $post_id, int $limit = 6): array
    {
        $categories = wp_get_post_terms($post_id, 'service_category');
        if (empty($categories) || is_wp_error($categories)) return [];

        $related = [];
        foreach ($categories as $cat) {
            $posts = get_posts([
                'post_type' => 'page',
                'posts_per_page' => $limit,
                'post__not_in' => [$post_id],
                'post_parent' => 0,
                'meta_key' => '_wp_page_template',
                'meta_value' => 'template-hauptseiten.php',
                'tax_query' => [[
                    'taxonomy' => 'service_category',
                    'terms' => $cat->term_id,
                ]],
            ]);

            foreach ($posts as $p) {
                $related[] = [
                    'name' => $p->post_title,
                    'link' => get_permalink($p->ID),
                    'category' => $cat->name,
                ];
            }
        }

        return array_slice($related, 0, $limit);
    }

    /**
     * Get sibling district pages for a child page
     */
    public function get_sibling_locations(int $post_id, int $limit = 8): array
    {
        $parent = wp_get_post_parent_id($post_id);
        if (!$parent) return [];

        $siblings = get_posts([
            'post_type' => 'page',
            'posts_per_page' => $limit,
            'post__not_in' => [$post_id],
            'post_parent' => $parent,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        return array_map(fn($p) => [
            'name' => $p->post_title,
            'link' => get_permalink($p->ID),
        ], $siblings);
    }
}
