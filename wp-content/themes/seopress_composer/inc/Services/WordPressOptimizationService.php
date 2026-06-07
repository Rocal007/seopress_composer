<?php

namespace SeopressComposer\Services;

/**
 * WordPress Optimization Service
 * 
 * Handles WordPress performance optimizations and cleanup
 */
class WordPressOptimizationService
{
  public function __construct()
  {
    // Disable Gutenberg Editor
    add_filter('use_block_editor_for_post_type', '__return_false');

    // Remove unnecessary wp_head actions
    add_action('init', [$this, 'remove_wp_head_actions']);

    // Deregister unnecessary assets
    add_action('wp_footer', [$this, 'deregister_unnecessary_assets']);
    add_action('wp_print_styles', [$this, 'deregister_unnecessary_assets'], 100);

    // Disable RSS feeds
    $this->disable_feeds();

    // Remove admin notices
    add_action('admin_init', [$this, 'remove_admin_notices']);

    // YouTube no-cookie embed
    add_filter('embed_oembed_html', [$this, 'youtube_nocookie_embed'], 10, 4);

    // Disable post comments feed
    add_filter('post_comments_feed_link', '__return_false');

    // Defer parsing of JavaScript files
    add_filter('script_loader_tag', [$this, 'defer_scripts'], 10, 2);

    // Preload ACF Options
    $this->preload_acf_options();
  }

  /**
   * Preload ACF Options to reduce DB queries
   * 
   * Fetches all 'options_%' and '_options_%' from wp_options in a single query
   * and seeds the object cache.
   */
  private function preload_acf_options(): void
  {
    global $wpdb;

    // Check if we are in admin to avoid unnecessary memory usage if not needed
    // But ACF options are often needed in admin too. 
    // Optimization is critical for frontend.

    $options = $wpdb->get_results("
        SELECT option_name, option_value 
        FROM $wpdb->options 
        WHERE option_name LIKE 'options_%' 
           OR option_name LIKE '_options_%'
           OR option_name IN (
               'CookieLawInfo-0.9', 
               'cky_first_time_activated_plugin', 
               'as_has_wp_comment_logs', 
               'fresh_site', 
               'site_logo'
           )
    ");

    if (!$options) {
      return;
    }

    foreach ($options as $option) {
      // key, value, group, expiration
      wp_cache_add($option->option_name, maybe_unserialize($option->option_value), 'options');
    }
  }

  /**
   * Defer JavaScript files
   */
  public function defer_scripts($tag, $handle)
  {
    // Don't defer admin scripts
    if (is_admin()) {
      return $tag;
    }

    // Exclude jQuery, underscore, and WordPress core scripts to avoid dependency errors
    if (strpos($handle, 'jquery') !== false || strpos($handle, 'wp-') !== false || strpos($handle, 'underscore') !== false) {
      return $tag;
    }

    // Don't defer if already deferred/async
    if (strpos($tag, 'defer') !== false || strpos($tag, 'async') !== false) {
      return $tag;
    }

    return str_replace(' src', ' defer src', $tag);
  }

  /**
   * Remove unnecessary wp_head actions
   */
  public function remove_wp_head_actions(): void
  {
    $actions = [
      'rsd_link',
      'wlwmanifest_link',
      'wp_generator',
      'start_post_rel_link',
      'index_rel_link',
      'adjacent_posts_rel_link_wp_head',
      'wp_shortlink_wp_head',
      'print_emoji_detection_script',
      'wp_print_styles',
      'set_comment_cookies'
    ];

    foreach ($actions as $action) {
      remove_action('wp_head', $action);
    }

    remove_action('wp_print_styles', 'print_emoji_styles');
  }

  /**
   * Deregister unnecessary scripts and styles
   */
  public function deregister_unnecessary_assets(): void
  {
    wp_deregister_script('wp-embed');
    wp_dequeue_style('wp-block-library');
  }

  /**
   * Disable RSS feeds
   */
  private function disable_feeds(): void
  {
    $feeds = [
      'do_feed',
      'do_feed_rdf',
      'do_feed_rss',
      'do_feed_rss2',
      'do_feed_atom',
      'do_feed_rss2_comments',
      'do_feed_atom_comments'
    ];

    foreach ($feeds as $feed) {
      add_action($feed, [$this, 'disable_feeds_callback'], 1);
    }
  }

  /**
   * Callback to disable feeds
   */
  public function disable_feeds_callback(): void
  {
    wp_die(__('No feeds available!'));
  }

  /**
   * Remove admin notices
   */
  public function remove_admin_notices(): void
  {
    global $wp_filter;
    unset($wp_filter['admin_notices'], $wp_filter['all_admin_notices']);
  }

  /**
   * Replace YouTube embeds with no-cookie versions
   */
  public function youtube_nocookie_embed($original, $url, $attr, $post_ID): string
  {
    return str_replace(
      ["youtube.com", "feature=oembed"],
      ["youtube-nocookie.com", "feature=oembed&showinfo=0"],
      $original
    );
  }


}
