<?php
namespace SeopressComposer\Services;

use SeopressComposer\Helpers\PageHelper;
use SeopressComposer\Repositories\RemoteDataRepository;
use SeopressComposer\Services\TextReplacementService;

class SeoMetaService
{
    private PageHelper $pageHelper;
    private RemoteDataRepository $repository;
    private TextReplacementService $textService;
    
    private ?string $resolved_title = null;
    private ?string $resolved_description = null;
    private ?string $og_image_url = null;
    private int $og_image_width = 1200;
    private int $og_image_height = 630;

    public function __construct(
        PageHelper $pageHelper, 
        RemoteDataRepository $repository, 
        TextReplacementService $textService
    ) {
        $this->pageHelper = $pageHelper;
        $this->repository = $repository;
        $this->textService = $textService;

        // Hooks
        add_action('wp_head', [$this, 'output_seo_meta'], 1);
        add_filter('pre_get_document_title', [$this, 'get_document_title'], 1);
        
        // Remove canonical output by WP core since we output it
        remove_action('wp_head', 'rel_canonical');
    }

    public function get_document_title($default = ''): string
    {
        if ($this->resolved_title !== null) {
            return $this->resolved_title;
        }

        $title = $this->resolve_from_remote('title')
            ?? $this->resolve_from_local_meta('_seopress_titles_title')
            ?? null;

        if ($title) {
            $this->resolved_title = $this->textService->process($title);
        } else {
            $this->resolved_title = $this->build_default_title();
        }

        return $this->resolved_title;
    }

    public function get_meta_description(): string
    {
        if ($this->resolved_description !== null) {
            return $this->resolved_description;
        }

        $desc = $this->resolve_from_remote('description')
            ?? $this->resolve_from_local_meta('_seopress_titles_desc')
            ?? null;

        if ($desc) {
            $this->resolved_description = $this->textService->process($desc);
        } else {
            $this->resolved_description = $this->build_default_description();
        }

        return $this->resolved_description;
    }

    private function get_og_image(): string
    {
        if ($this->og_image_url !== null) {
            return $this->og_image_url;
        }

        $remote = $this->get_remote_data();
        if ($remote && !empty($remote->better_featured_image->source_url)) {
            $this->og_image_url = esc_url($remote->better_featured_image->source_url);
            return $this->og_image_url;
        }

        if (has_post_thumbnail()) {
            $img = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large');
            if ($img) {
                $this->og_image_url = esc_url($img[0]);
                // Use actual image dimensions instead of hardcoded values
                if (!empty($img[1]) && !empty($img[2])) {
                    $this->og_image_width = (int) $img[1];
                    $this->og_image_height = (int) $img[2];
                }
                return $this->og_image_url;
            }
        }

        // Use raster fallback — SVG not supported by Facebook/LinkedIn
        $raster_fallback = get_template_directory() . '/assets/img/og-default.png';
        if (file_exists($raster_fallback)) {
            $this->og_image_url = esc_url(get_template_directory_uri() . '/assets/img/og-default.png');
        } else {
            $this->og_image_url = esc_url(get_template_directory_uri() . '/assets/img/logo.svg');
        }
        return $this->og_image_url;
    }

    public function output_seo_meta(): void
    {
        if (is_admin() || is_feed()) {
            return;
        }

        $title       = esc_html($this->get_document_title());
        $description = esc_html($this->get_meta_description());
        $canonical   = esc_url($this->get_canonical_url());
        $og_image    = $this->get_og_image();
        $site_name   = esc_html(get_bloginfo('name'));
        // Hardcode og:locale to de_AT — target market is Austria, not Germany.
        // WordPress get_locale() returns de_DE by default which conflicts with hreflang de-AT.
        $og_locale   = 'de_AT';

        $robots = $this->get_robots_directive();

        echo "\n<!-- SEO Meta (Composer Native) -->\n";
        echo '<meta name="description" content="' . $description . '">' . "\n";
        
        if ($robots) {
            echo '<meta name="robots" content="' . esc_attr($robots) . '">' . "\n";
        }

        echo '<link rel="canonical" href="' . $canonical . '">' . "\n";

        // hreflang for geo-linguistic targeting (de-AT)
        echo '<link rel="alternate" hreflang="de-AT" href="' . $canonical . '">' . "\n";
        echo '<link rel="alternate" hreflang="x-default" href="' . $canonical . '">' . "\n";

        // AI Content Declaration
        echo '<meta name="ai-content-declaration" content="human-authored, factual">' . "\n";

        $og_type = (is_single() && get_post_type() === 'post') ? 'article' : 'website';
        echo '<meta property="og:type"        content="' . esc_attr($og_type) . '">' . "\n";
        echo '<meta property="og:title"       content="' . $title . '">' . "\n";
        echo '<meta property="og:description" content="' . $description . '">' . "\n";
        echo '<meta property="og:url"         content="' . $canonical . '">' . "\n";
        echo '<meta property="og:site_name"   content="' . $site_name . '">' . "\n";
        echo '<meta property="og:locale"      content="' . esc_attr($og_locale) . '">' . "\n";

        if ($og_type === 'article') {
            echo '<meta property="article:published_time" content="' . esc_attr(get_the_date('c')) . '">' . "\n";
            echo '<meta property="article:modified_time"  content="' . esc_attr(get_the_modified_date('c')) . '">' . "\n";
            $author = get_the_author_meta('display_name');
            if ($author) {
                echo '<meta property="article:author" content="' . esc_attr($author) . '">' . "\n";
            }
        }

        if ($og_image) {
            echo '<meta property="og:image"       content="' . $og_image . '">' . "\n";
            echo '<meta property="og:image:width" content="' . esc_attr($this->og_image_width) . '">' . "\n";
            echo '<meta property="og:image:height" content="' . esc_attr($this->og_image_height) . '">' . "\n";
            // Descriptive alt for the image — not just the page title
            $og_img_alt = esc_attr($title . ' — ' . get_bloginfo('name'));
            echo '<meta property="og:image:alt"   content="' . $og_img_alt . '">' . "\n";
        }

        echo '<meta name="twitter:card"        content="summary_large_image">' . "\n";
        echo '<meta name="twitter:title"       content="' . $title . '">' . "\n";
        echo '<meta name="twitter:description" content="' . $description . '">' . "\n";
        if ($og_image) {
            echo '<meta name="twitter:image"   content="' . $og_image . '">' . "\n";
        }

        // Pagination: rel=prev/next for archive pages
        if (is_archive() || is_home()) {
            global $wp_query;
            $paged = max(1, get_query_var('paged', 1));
            $max_pages = $wp_query->max_num_pages ?? 1;

            if ($paged > 1) {
                $prev_url = get_pagenum_link($paged - 1);
                echo '<link rel="prev" href="' . esc_url($prev_url) . '">' . "\n";
            }
            if ($paged < $max_pages) {
                $next_url = get_pagenum_link($paged + 1);
                echo '<link rel="next" href="' . esc_url($next_url) . '">' . "\n";
            }
        }

        echo "<!-- /SEO Meta -->\n\n";
    }

    private function get_remote_data(): ?object
    {
        $obj = get_queried_object();
        $selector = ($obj instanceof \WP_Term) ? $obj->taxonomy . '_' . $obj->term_id : $this->pageHelper->check_page_id($obj->ID ?? 0);
        $remote_url = get_field('remote_page', $selector);

        if ($remote_url && ($data = $this->repository->fetch((string)$remote_url))) {
            return is_array($data) ? ($data[0] ?? null) : $data;
        }

        return null;
    }

    private function resolve_from_remote(string $type): ?string
    {
        $data = $this->get_remote_data();
        if (!$data) {
            return null;
        }

        return match($type) {
            'title'       => !empty($data->yoast_title_raw) ? $data->yoast_title_raw : null,
            'description' => !empty($data->yoast_desc_raw)  ? $data->yoast_desc_raw  : null,
            default       => null,
        };
    }

    private function resolve_from_local_meta(string $meta_key): ?string
    {
        if (!is_singular()) {
            return null;
        }
        $val = get_post_meta(get_the_ID(), $meta_key, true);
        return !empty($val) ? $val : null;
    }

    private function build_default_title(): string
    {
        $site_name = get_bloginfo('name');
        $separator = '|';

        if (is_front_page() || is_home()) {
            return $site_name . ' ' . $separator . ' ' . get_bloginfo('description');
        }
        if (is_singular()) {
            return get_the_title() . ' | ' . get_bloginfo('name');
        }
        if (is_archive()) {
            return get_the_archive_title() . ' ' . $separator . ' ' . $site_name;
        }
        if (is_search()) {
            return 'Suchergebnisse: ' . get_search_query() . ' ' . $separator . ' ' . $site_name;
        }
        if (is_404()) {
            return 'Seite nicht gefunden ' . $separator . ' ' . $site_name;
        }

        return $site_name;
    }

    private function build_default_description(): string
    {
        if (is_singular() && has_excerpt()) {
            return wp_strip_all_tags(get_the_excerpt());
        }
        return get_bloginfo('description') ?: '';
    }

    private function get_canonical_url(): string
    {
        if (is_singular()) {
            return get_permalink();
        }
        if (is_front_page()) {
            return home_url('/');
        }
        if (is_category()) {
            return get_category_link(get_queried_object_id());
        }
        if (is_tax()) {
            $term = get_queried_object();
            if ($term && !is_wp_error(get_term_link($term))) {
                return get_term_link($term);
            }
        }
        if (is_tag()) {
            return get_tag_link(get_queried_object_id());
        }
        if (is_post_type_archive()) {
            $link = get_post_type_archive_link(get_post_type());
            return $link ?: home_url('/');
        }
        if (is_search()) {
            return home_url('/');
        }
        global $wp;
        return home_url(add_query_arg([], $wp->request));
    }

    private function get_robots_directive(): string
    {
        // 404 and search pages should never be indexed
        if (is_404()) {
            return 'noindex, nofollow';
        }
        if (is_search()) {
            return 'noindex, follow';
        }

        if (is_singular()) {
            $noindex_sp = get_post_meta(get_the_ID(), '_seopress_robots_index', true);
            if ($noindex_sp === 'yes') {
                return 'noindex, nofollow';
            }
        }

        // Paginated archive pages beyond page 1
        if (is_paged()) {
            return 'noindex, follow';
        }

        return 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1';
    }
}
