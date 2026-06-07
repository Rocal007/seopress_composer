<?php

namespace SeopressComposer\Repositories;

class RemoteDataRepository
{
    private int $cacheDuration;

    public function __construct(int $cacheDuration = 3600)
    {
        $this->cacheDuration = $cacheDuration;
    }

    public function fetch(string $url)
    {
        if (empty($url)) return null;

        $cacheKey = 'remote_repo_' . md5($url);

        // Layer 1: Try wp_cache (fast, in-memory, per-request)
        $cached = wp_cache_get($cacheKey, 'remote_data');
        if ($cached !== false) {
            return $cached;
        }

        // Layer 2: Try transient (persistent across requests, but slower)
        $cached = get_transient($cacheKey);
        if ($cached !== false) {
            wp_cache_set($cacheKey, $cached, 'remote_data', $this->cacheDuration);
            return $cached;
        }

        // Layer 3: Fetch from remote API
        $body = $this->fetchRemote($url);

        // Layer 3b: Fallback for broken custom/v1/pages?slug=X — try wp/v2/pages?slug=X instead
        // This fixes stored remote_page URLs that were generated with the wrong endpoint format.
        if ($body === null && preg_match('/custom\/v1\/pages\?slug=([a-z0-9_-]+)/i', $url, $m)) {
            $slug        = $m[1];
            $base        = preg_replace('#/wp-json/.*#', '', $url);
            $fallback    = $base . '/wp-json/wp/v2/pages?slug=' . $slug . '&_fields=id,title,template,acf,yoast_title_raw,yoast_desc_raw';
            $body        = $this->fetchRemote($fallback);

            if ($body !== null) {
                // Also cache under the original URL so future calls are instant
                wp_cache_set($cacheKey, $body, 'remote_data', $this->cacheDuration);
                set_transient($cacheKey, $body, $this->cacheDuration);
                return $body;
            }
        }

        if ($body !== null) {
            wp_cache_set($cacheKey, $body, 'remote_data', $this->cacheDuration);
            set_transient($cacheKey, $body, $this->cacheDuration);
        }

        return $body;
    }

    /**
     * Internal HTTP fetch with response parsing.
     * Returns decoded object/array, or null on error/404.
     */
    private function fetchRemote(string $url)
    {
        $response = wp_remote_get($url, [
            'sslverify' => false,
            'timeout'   => 15,
        ]);

        if (is_wp_error($response)) {
            error_log('RemoteDataRepository Error [' . $url . ']: ' . $response->get_error_message());
            return null;
        }

        $status = (int) wp_remote_retrieve_response_code($response);

        // Don't process error responses (404, 500, etc.)
        if ($status >= 400) {
            error_log('RemoteDataRepository HTTP ' . $status . ': ' . $url);
            return null;
        }

        $body = json_decode(wp_remote_retrieve_body($response));

        if ($body === null) return null;

        // Handle array response (e.g. from wp/v2/pages?slug= query)
        if (is_array($body) && !empty($body)) {
            if (isset($body[0]->id) || isset($body[0]->acf)) {
                $body = $body[0];
            }
        }

        return $body;
    }

    public function clearCache(string $url): void
    {
        $cacheKey = 'remote_repo_' . md5($url);
        wp_cache_delete($cacheKey, 'remote_data');
        delete_transient($cacheKey);
    }

    /**
     * Preload multiple URLs into cache to avoid N+1 queries
     */
    public function preload(array $urls): void
    {
        if (empty($urls)) return;

        $cacheKeys = [];
        $keyMap = []; // map hash -> md5

        foreach ($urls as $url) {
            if (empty($url)) continue;
            $hash = md5($url);
            $key = 'remote_repo_' . $hash;
            $cacheKeys[] = $key;
            $keyMap[$key] = $url;

            // Should potential timeouts also be preloaded?
            // Transients have two records: _transient_KEY and _transient_timeout_KEY
            // We usually only need the value. wp_options query will fetch value.
            // But get_transient also checks timeout.
        }

        if (empty($cacheKeys)) return;

        // Check which keys are already in object cache to avoid DB query
        $keysToQuery = [];
        foreach ($cacheKeys as $key) {
            if (wp_cache_get($key, 'remote_data') === false) {
                // Not in memory, so we need to check DB
                $keysToQuery[] = '_transient_' . $key;
                $keysToQuery[] = '_transient_timeout_' . $key;
            }
        }

        if (empty($keysToQuery)) return;

        global $wpdb;

        // Escape all keys for safety, though they are just md5 prefixed
        $placeholders = implode("','", array_map('esc_sql', $keysToQuery));

        $results = $wpdb->get_results("
            SELECT option_name, option_value 
            FROM $wpdb->options 
            WHERE option_name IN ('$placeholders')
        ");

        if (!$results) return;

        // Process results
        $values = [];
        $timeouts = [];

        foreach ($results as $row) {
            if (strpos($row->option_name, '_transient_timeout_') === 0) {
                $baseKey = substr($row->option_name, strlen('_transient_timeout_'));
                $timeouts[$baseKey] = $row->option_value;
            } else {
                $baseKey = substr($row->option_name, strlen('_transient_'));
                $values[$baseKey] = $row->option_value;
            }
        }

        $now = time();

        foreach ($values as $key => $value) {
            // Check expiration
            if (isset($timeouts[$key]) && $timeouts[$key] < $now) {
                // Expired
                continue;
            }

            // Valid, set to object cache
            // Note: get_transient unserializes. We must do same if manual.
            $data = maybe_unserialize($value);

            // Set for this request
            wp_cache_set($key, $data, 'remote_data', $this->cacheDuration);
        }
    }
}
