<?php

namespace SeopressComposer\Helpers;

/**
 * MetaHelper — Native WordPress replacement for ACF's get_field() / update_field()
 *
 * ACF stores data in standard WordPress tables:
 * - Options:   wp_options with key 'options_{field_name}'
 * - Post Meta: wp_postmeta with key '{field_name}'
 * - Term Meta: wp_termmeta with key '{field_name}'
 *
 * This helper reads/writes from the same locations, making ACF optional.
 */
class MetaHelper
{
    /**
     * Get a post/page meta value (replaces get_field($key, $post_id))
     */
    public static function get(string $key, int $post_id = 0)
    {
        if ($post_id === 0) {
            $post_id = get_the_ID();
        }
        if (!$post_id) {
            return null;
        }
        $value = get_post_meta($post_id, $key, true);
        return ($value !== '' && $value !== false) ? $value : null;
    }

    /**
     * Set a post/page meta value (replaces update_field($key, $value, $post_id))
     */
    public static function set(string $key, $value, int $post_id): void
    {
        update_post_meta($post_id, $key, $value);
    }

    /**
     * Get a term meta value (replaces get_field($key, 'taxonomy_123'))
     */
    public static function getTerm(string $key, int $term_id)
    {
        $value = get_term_meta($term_id, $key, true);
        return ($value !== '' && $value !== false) ? $value : null;
    }

    /**
     * Set a term meta value
     */
    public static function setTerm(string $key, $value, int $term_id): void
    {
        update_term_meta($term_id, $key, $value);
    }

    /**
     * Get a global option value (replaces get_field($key, 'option'))
     * ACF stores options as 'options_{key}' in wp_options table.
     */
    public static function getOption(string $key, $default = null)
    {
        $value = get_option('options_' . $key);
        if ($value === false || $value === '') {
            return $default;
        }
        return maybe_unserialize($value);
    }

    /**
     * Set a global option value (replaces update_field($key, $value, 'option'))
     */
    public static function setOption(string $key, $value): void
    {
        update_option('options_' . $key, $value);
    }

    /**
     * Parse an ACF-style selector like 'taxonomy_123' or 'category_45'
     * Returns ['type' => 'term', 'id' => 123] or ['type' => 'post', 'id' => 45]
     */
    public static function parseSelector(string $selector): array
    {
        // ACF term meta format: '{taxonomy}_{term_id}'
        if (preg_match('/^([a-z_]+)_(\d+)$/i', $selector, $m)) {
            $taxonomy = $m[1];
            $term_id = (int) $m[2];
            // Verify it's actually a taxonomy (not a post meta key that ends with digits)
            if (taxonomy_exists($taxonomy) || in_array($taxonomy, ['category', 'post_tag', 'district', 'service_category', 'menu_category'])) {
                return ['type' => 'term', 'taxonomy' => $taxonomy, 'id' => $term_id];
            }
        }
        // Numeric = post ID
        if (is_numeric($selector)) {
            return ['type' => 'post', 'id' => (int) $selector];
        }
        // 'option' = global
        if ($selector === 'option') {
            return ['type' => 'option', 'id' => 0];
        }
        return ['type' => 'unknown', 'id' => 0];
    }

    /**
     * Universal getter — drop-in replacement for get_field($key, $selector)
     * Handles all ACF selector formats: post_id, 'option', 'taxonomy_123'
     */
    public static function getField(string $key, $selector = null)
    {
        if ($selector === null || $selector === '' || $selector === 0) {
            return self::get($key);
        }
        if ($selector === 'option') {
            return self::getOption($key);
        }
        if (is_int($selector) || is_numeric($selector)) {
            return self::get($key, (int) $selector);
        }
        if (is_string($selector)) {
            $parsed = self::parseSelector($selector);
            if ($parsed['type'] === 'term') {
                return self::getTerm($key, $parsed['id']);
            }
        }
        return null;
    }

    /**
     * Universal setter — drop-in replacement for update_field($key, $value, $selector)
     */
    public static function setField(string $key, $value, $selector): void
    {
        if ($selector === 'option') {
            self::setOption($key, $value);
            return;
        }
        if (is_int($selector) || is_numeric($selector)) {
            self::set($key, $value, (int) $selector);
            return;
        }
        if (is_string($selector)) {
            $parsed = self::parseSelector($selector);
            if ($parsed['type'] === 'term') {
                self::setTerm($key, $value, $parsed['id']);
                return;
            }
        }
    }
}
