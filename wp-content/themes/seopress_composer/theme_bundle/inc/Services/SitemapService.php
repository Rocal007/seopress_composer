<?php

namespace SeopressComposer\Services;

/**
 * Sitemap Service
 * 
 * Configures WordPress Core Sitemaps for optimal crawlability.
 * Removes unnecessary providers and adds custom taxonomy support.
 */
class SitemapService
{
    public function __construct()
    {
        // Enable WordPress Core Sitemaps (enabled by default since WP 5.5)
        add_filter('wp_sitemaps_enabled', '__return_true');

        // Remove users from sitemap (privacy/security)
        add_filter('wp_sitemaps_add_provider', [$this, 'remove_users_provider'], 10, 2);

        // Add lastmod to sitemap entries
        add_filter('wp_sitemaps_posts_entry', [$this, 'add_lastmod_to_posts'], 10, 2);
        add_filter('wp_sitemaps_taxonomies_entry', [$this, 'add_lastmod_to_taxonomies'], 10, 3);

        // Exclude specific templates from sitemap
        add_filter('wp_sitemaps_posts_query_args', [$this, 'filter_sitemap_posts'], 10, 2);

        // Ensure custom taxonomies are included
        add_filter('wp_sitemaps_taxonomies', [$this, 'include_custom_taxonomies']);

        // Set maximum URLs per sitemap page
        add_filter('wp_sitemaps_max_urls', [$this, 'set_max_urls']);
    }

    /**
     * Remove users provider from sitemap
     */
    public function remove_users_provider($provider, string $name)
    {
        if ($name === 'users') {
            return false;
        }
        return $provider;
    }

    /**
     * Add lastmod to post sitemap entries
     */
    public function add_lastmod_to_posts(array $entry, $post): array
    {
        $entry['lastmod'] = get_the_modified_date('c', $post);
        return $entry;
    }

    /**
     * Add lastmod to taxonomy sitemap entries
     */
    public function add_lastmod_to_taxonomies(array $entry, $term_id, string $taxonomy): array
    {
        // Use the most recent post modification date in this term
        $posts = get_posts([
            'post_type' => 'page',
            'posts_per_page' => 1,
            'orderby' => 'modified',
            'order' => 'DESC',
            'tax_query' => [
                [
                    'taxonomy' => $taxonomy,
                    'terms' => $term_id,
                ]
            ],
        ]);

        if (!empty($posts)) {
            $entry['lastmod'] = get_the_modified_date('c', $posts[0]);
        }

        return $entry;
    }

    /**
     * Filter posts included in sitemap
     */
    public function filter_sitemap_posts(array $args, string $post_type): array
    {
        if ($post_type === 'page') {
            // Exclude datenschutz/agb pages
            $excluded = get_pages([
                'meta_key' => '_wp_page_template',
                'meta_value' => 'template-datenschutz-agb.php',
                'fields' => 'ids',
            ]);

            if (!empty($excluded)) {
                $args['post__not_in'] = array_merge(
                    $args['post__not_in'] ?? [],
                    wp_list_pluck($excluded, 'ID')
                );
            }
        }

        return $args;
    }

    /**
     * Ensure custom taxonomies are included in sitemap
     */
    public function include_custom_taxonomies(array $taxonomies): array
    {
        // Ensure service_category and district are included
        if (!isset($taxonomies['service_category'])) {
            $tax = get_taxonomy('service_category');
            if ($tax) {
                $taxonomies['service_category'] = $tax;
            }
        }
        if (!isset($taxonomies['district'])) {
            $tax = get_taxonomy('district');
            if ($tax) {
                $taxonomies['district'] = $tax;
            }
        }

        // Remove unwanted taxonomies
        unset($taxonomies['post_tag']);
        unset($taxonomies['menu_category']);
        unset($taxonomies['ankauf_kategorie']);

        return $taxonomies;
    }

    /**
     * Set maximum URLs per sitemap page
     */
    public function set_max_urls(): int
    {
        return 1000;
    }
}
