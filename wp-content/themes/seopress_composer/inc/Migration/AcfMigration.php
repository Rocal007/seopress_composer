<?php

namespace SeopressComposer\Migration;

/**
 * ACF Data Migration
 * 
 * Migrates ACF field data to native WordPress meta.
 * ACF stores both:
 *   - The actual value: post_meta 'remote_page' = 'https://...'
 *   - A reference key: post_meta '_remote_page' = 'field_xyz123'
 * 
 * Native WordPress only needs the value. The reference keys can be removed
 * to save DB space and avoid confusion.
 * 
 * Run via WP Admin: Site Settings > Migration tab, or via WP-CLI:
 *   wp eval "\SeopressComposer\Migration\AcfMigration::run();"
 */
class AcfMigration
{
    /** ACF field names used in this theme */
    private const FIELD_NAMES = [
        'remote_page',
        'hauptseiten_icon',
        'einsatzgebiet',
        'top_picture_image',
        // Term meta fields
        'karte',
        'wappen',
        'orte_repeater',
    ];

    /**
     * Check if migration is needed
     */
    public static function needs_migration(): bool
    {
        global $wpdb;
        
        // Check if any ACF reference keys exist (prefixed with _)
        $count = $wpdb->get_var(
            "SELECT COUNT(*) FROM $wpdb->postmeta 
             WHERE meta_key IN ('_remote_page', '_hauptseiten_icon', '_einsatzgebiet', '_top_picture_image')"
        );
        
        return $count > 0;
    }

    /**
     * Run the migration
     * 
     * @return array Migration report
     */
    public static function run(): array
    {
        global $wpdb;
        $report = [
            'post_meta_cleaned' => 0,
            'term_meta_cleaned' => 0,
            'options_verified'  => 0,
            'errors'            => [],
        ];

        // ═══════════════════════════════════════
        // Phase 1: Clean up ACF reference keys from post_meta
        // ═══════════════════════════════════════
        // ACF stores '_field_name' => 'field_key_xyz' for each field.
        // These reference keys are only needed by ACF's internal resolver.
        // The actual values stay untouched in 'field_name' => 'value'.
        
        foreach (self::FIELD_NAMES as $field) {
            $ref_key = '_' . $field;
            $deleted = $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM $wpdb->postmeta WHERE meta_key = %s",
                    $ref_key
                )
            );
            if ($deleted > 0) {
                $report['post_meta_cleaned'] += $deleted;
            }
        }

        // ═══════════════════════════════════════
        // Phase 2: Clean up ACF reference keys from term_meta
        // ═══════════════════════════════════════
        foreach (['_karte', '_wappen', '_orte_repeater', '_remote_page'] as $ref_key) {
            $deleted = $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM $wpdb->termmeta WHERE meta_key = %s",
                    $ref_key
                )
            );
            if ($deleted > 0) {
                $report['term_meta_cleaned'] += $deleted;
            }
        }

        // ═══════════════════════════════════════
        // Phase 3: Verify options are accessible
        // ═══════════════════════════════════════
        $critical_options = [
            'einsatzgebiet', 'bundesland', 'inhaber', 'telefonnummer',
            'basicremote', 'primary', 'color_scheme',
        ];

        foreach ($critical_options as $key) {
            $value = get_option('options_' . $key);
            if ($value !== false && $value !== '') {
                $report['options_verified']++;
            }
        }

        // ═══════════════════════════════════════
        // Phase 4: Clean up ACF option references
        // ═══════════════════════════════════════
        // ACF also stores '_options_fieldname' => 'field_key' in wp_options
        $deleted_opts = $wpdb->query(
            "DELETE FROM $wpdb->options WHERE option_name LIKE '_options_%' AND option_value LIKE 'field_%'"
        );
        if ($deleted_opts > 0) {
            $report['options_cleaned'] = $deleted_opts;
        }

        // Flush caches after cleanup
        wp_cache_flush();

        return $report;
    }

    /**
     * Verify post_meta integrity after migration
     * 
     * @return array Pages with remote_page meta
     */
    public static function verify(): array
    {
        global $wpdb;

        $results = $wpdb->get_results(
            "SELECT p.ID, p.post_title, pm.meta_value as remote_page
             FROM $wpdb->posts p
             INNER JOIN $wpdb->postmeta pm ON p.ID = pm.post_id
             WHERE pm.meta_key = 'remote_page'
             AND pm.meta_value != ''
             AND p.post_status = 'publish'
             ORDER BY p.post_title
             LIMIT 20"
        );

        return $results ?: [];
    }
}
