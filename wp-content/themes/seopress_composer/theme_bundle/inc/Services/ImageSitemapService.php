<?php

namespace SeopressComposer\Services;

use SeopressComposer\Helpers\PageHelper;
use SeopressComposer\Repositories\RemoteDataRepository;

/**
 * Google Image Sitemap Service
 * 
 * Generates a dedicated image sitemap (/image-sitemap.xml) conforming to
 * Google's Image Sitemap extension specification.
 * 
 * @see https://developers.google.com/search/docs/crawling-indexing/sitemaps/image-sitemaps
 */
class ImageSitemapService
{
    private PageHelper $pageHelper;
    private RemoteDataRepository $repository;

    public function __construct(PageHelper $pageHelper, RemoteDataRepository $repository = null)
    {
        $this->pageHelper = $pageHelper;
        $this->repository = $repository ?? new RemoteDataRepository();
        add_action('init', [$this, 'register_endpoint']);
        add_action('template_redirect', [$this, 'handle_request']);
    }

    /**
     * Register the image-sitemap.xml rewrite rule
     */
    public function register_endpoint(): void
    {
        add_rewrite_rule('^image-sitemap\.xml$', 'index.php?image_sitemap=1', 'top');
        add_filter('query_vars', function ($vars) {
            $vars[] = 'image_sitemap';
            return $vars;
        });
    }

    /**
     * Handle image sitemap requests
     */
    public function handle_request(): void
    {
        if (!get_query_var('image_sitemap')) {
            return;
        }

        header('Content-Type: application/xml; charset=utf-8');
        header('X-Robots-Tag: noindex');

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        echo '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        $this->generate_page_entries();

        echo '</urlset>' . "\n";
        exit;
    }

    /**
     * Generate sitemap entries for all published pages with images
     */
    private function generate_page_entries(): void
    {
        $pages = get_posts([
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ]);

        foreach ($pages as $page) {
            $images = $this->collect_images($page);

            if (empty($images)) {
                continue;
            }

            echo '  <url>' . "\n";
            echo '    <loc>' . esc_url(get_permalink($page->ID)) . '</loc>' . "\n";

            foreach ($images as $image) {
                echo '    <image:image>' . "\n";
                echo '      <image:loc>' . esc_url($image['url']) . '</image:loc>' . "\n";

                if (!empty($image['title'])) {
                    echo '      <image:title>' . $this->xml_escape($image['title']) . '</image:title>' . "\n";
                }

                if (!empty($image['caption'])) {
                    echo '      <image:caption>' . $this->xml_escape($image['caption']) . '</image:caption>' . "\n";
                }

                echo '    </image:image>' . "\n";
            }

            echo '  </url>' . "\n";
        }
    }

    /**
     * Collect all relevant images for a page
     */
    private function collect_images(\WP_Post $page): array
    {
        $images = [];

        // 1. Featured Image (highest priority)
        if (has_post_thumbnail($page->ID)) {
            $thumb_id = get_post_thumbnail_id($page->ID);
            $thumb_url = wp_get_attachment_image_url($thumb_id, 'large');
            $thumb_alt = get_post_meta($thumb_id, '_wp_attachment_image_alt', true);
            $thumb_post = get_post($thumb_id);

            if ($thumb_url) {
                $images[] = [
                    'url'     => $thumb_url,
                    'title'   => $thumb_alt ?: $page->post_title,
                    'caption' => $thumb_post->post_excerpt ?? '',
                ];
            }
        }

        // 2. Remote API featured image (from remote_page field)
        $data_id = $this->pageHelper->check_page_id($page->ID);
        $remote_url = get_field('remote_page', $data_id);

        if ($remote_url) {
            $data = $this->repository->fetch((string) $remote_url);
            $data_obj = is_array($data) ? ($data[0] ?? null) : $data;

            if ($data_obj && !empty($data_obj->better_featured_image->source_url)) {
                $remote_img_url = $data_obj->better_featured_image->source_url;

                // Avoid duplicates
                $already_added = array_filter($images, fn($i) => $i['url'] === $remote_img_url);
                if (empty($already_added)) {
                    $images[] = [
                        'url'     => $remote_img_url,
                        'title'   => $data_obj->better_featured_image->alt_text ?? $page->post_title,
                        'caption' => $data_obj->better_featured_image->caption ?? '',
                    ];
                }
            }
        }

        // 3. Images embedded in post content
        $content = $page->post_content;
        if (!empty($content)) {
            preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
            if (!empty($matches[1])) {
                foreach (array_slice($matches[1], 0, 5) as $img_url) {
                    // Skip data URIs and external tracking pixels
                    if (str_starts_with($img_url, 'data:')) continue;

                    $already_added = array_filter($images, fn($i) => $i['url'] === $img_url);
                    if (empty($already_added)) {
                        // Extract alt text
                        $alt = '';
                        if (preg_match('/alt=["\']([^"\']*)["\']/', $matches[0][array_search($img_url, $matches[1])], $alt_match)) {
                            $alt = $alt_match[1];
                        }

                        $images[] = [
                            'url'     => $img_url,
                            'title'   => $alt ?: $page->post_title,
                            'caption' => '',
                        ];
                    }
                }
            }
        }

        // Cap at 10 images per page (Google recommendation)
        return array_slice($images, 0, 10);
    }

    /**
     * XML-safe escape for text content
     */
    private function xml_escape(string $text): string
    {
        return htmlspecialchars(strip_tags($text), ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
