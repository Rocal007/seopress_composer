<?php

namespace SeopressComposer\Services;

/**
 * Image Download Service
 * 
 * Downloads remote images to the local server and assigns SEO-optimized filenames.
 * This ensures images rank better in Google Image Search for the target site's location.
 */
class ImageDownloadService
{
    /**
     * Cache remote image locally with SEO optimized filename
     *
     * @param string $remote_url The URL of the image on the API server
     * @param string $seo_title The desired title (e.g., "Entrümpelung Wien 1010 Innere Stadt")
     * @param string $alt_text The ALT text to assign to the media library
     * @return string The local URL of the image (or the remote URL on failure)
     */
    public function get_local_image_url(string $remote_url, string $seo_title, string $alt_text = ''): string
    {
        if (empty($remote_url)) {
            return '';
        }

        // Only process remote URLs (e.g. if the API returns a relative URL or it's already local, skip)
        $home_url = home_url();
        if (strpos($remote_url, $home_url) !== false || substr($remote_url, 0, 1) === '/') {
            return $remote_url;
        }

        // 1. Check if we already downloaded this image (Query by meta_key _source_url)
        // We use a transient cache for the query to avoid DB hits on every page load
        $cache_key = 'local_img_' . md5($remote_url);
        $local_url = get_transient($cache_key);

        if ($local_url !== false && str_ends_with(strtolower($local_url), '.webp')) {
            return $local_url;
        }

        global $wpdb;
        $attachment_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_source_url' AND meta_value = %s LIMIT 1",
            $remote_url
        ));

        if ($attachment_id) {
            $local_url = wp_get_attachment_url($attachment_id);
            if ($local_url) {
                if (str_ends_with(strtolower($local_url), '.webp')) {
                    set_transient($cache_key, $local_url, WEEK_IN_SECONDS);
                    return $local_url;
                } else {
                    // Force upgrade: delete old non-webp attachment so we can re-download
                    wp_delete_attachment($attachment_id, true);
                    delete_transient($cache_key);
                }
            }
        }

        // 2. We need to download it
        // Ensure WordPress image functions are available
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        // Download file to temp dir
        $temp_file = download_url($remote_url);

        if (is_wp_error($temp_file)) {
            // Fallback: If WebP was requested but failed (e.g. older images on API), try original JPG
            if (str_ends_with(strtolower($remote_url), '.webp')) {
                $fallback_url = preg_replace('/\.webp$/i', '.jpg', $remote_url);
                $temp_file = download_url($fallback_url);
                
                // Try PNG if JPG fails
                if (is_wp_error($temp_file)) {
                    $fallback_url = preg_replace('/\.webp$/i', '.png', $remote_url);
                    $temp_file = download_url($fallback_url);
                }
            }

            if (is_wp_error($temp_file)) {
                error_log('ImageDownloadService: Failed to download ' . $remote_url . ' - ' . $temp_file->get_error_message());
                return $remote_url; // Fallback to remote on error
            }
        }

        // Apply Visual Enhancements & Ensure WebP Format
        $mime = mime_content_type($temp_file);
        $extension = 'jpg'; // default
        
        $converted_to_webp = false;

        if (in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) {
            $new_temp_file = $temp_file . '_converted.webp';

            // 1. Try Imagick first (most robust, supports transparency and palettes natively)
            if (extension_loaded('imagick') && class_exists('Imagick')) {
                try {
                    $imagick = new \Imagick($temp_file);
                    
                    // Simple Optical Enhancements in Imagick
                    $imagick->contrastImage(true); // increase contrast slightly
                    $imagick->modulateImage(105, 100, 100); // 105% brightness

                    $imagick->setImageFormat('webp');
                    $imagick->setImageCompressionQuality(85);
                    $imagick->writeImage($new_temp_file);
                    $imagick->clear();
                    $imagick->destroy();
                    $converted_to_webp = true;
                } catch (\Exception $e) {
                    error_log('ImageDownloadService: Imagick failed - ' . $e->getMessage());
                }
            }

            // 2. Fallback to GD
            if (!$converted_to_webp && extension_loaded('gd') && function_exists('imagewebp')) {
                $gd_image = null;
                if ($mime === 'image/jpeg') $gd_image = @imagecreatefromjpeg($temp_file);
                elseif ($mime === 'image/png') $gd_image = @imagecreatefrompng($temp_file);
                elseif ($mime === 'image/webp') $gd_image = @imagecreatefromwebp($temp_file);

                if ($gd_image) {
                    if (!imageistruecolor($gd_image)) {
                        $tc = imagecreatetruecolor(imagesx($gd_image), imagesy($gd_image));
                        imagealphablending($tc, false);
                        imagesavealpha($tc, true);
                        $transparent = imagecolorallocatealpha($tc, 0, 0, 0, 127);
                        imagefill($tc, 0, 0, $transparent);
                        imagecopy($tc, $gd_image, 0, 0, 0, 0, imagesx($gd_image), imagesy($gd_image));
                        imagedestroy($gd_image);
                        $gd_image = $tc;
                    }
                    imagealphablending($gd_image, false);
                    imagesavealpha($gd_image, true);

                    imagefilter($gd_image, IMG_FILTER_CONTRAST, -8);
                    imagefilter($gd_image, IMG_FILTER_BRIGHTNESS, 5);

                    if (@imagewebp($gd_image, $new_temp_file, 85)) {
                        $converted_to_webp = true;
                    }
                    imagedestroy($gd_image);
                }
            }

            // If conversion succeeded, replace original temp file
            if ($converted_to_webp && file_exists($new_temp_file)) {
                @unlink($temp_file);
                $temp_file = $new_temp_file;
                $extension = 'webp';
            }
        }

        // 3. Prepare SEO Filename
        // e.g. "Entrümpelung Wien 1010 Innere Stadt" -> "entruempelung-wien-1010-innere-stadt"
        $safe_filename = sanitize_title($seo_title);
        if (empty($safe_filename)) {
            $safe_filename = 'service-image-' . wp_generate_password(6, false);
        }

        // If not converted to webp above, extract extension from URL
        if ($extension === 'jpg' && $mime !== 'image/jpeg') {
            $mime_map = [
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif'
            ];
            $extension = $mime_map[$mime] ?? 'jpg';
        }

        $final_filename = $safe_filename . '.' . $extension;

        // 4. Move temp file into uploads directory
        $file_array = [
            'name' => $final_filename,
            'tmp_name' => $temp_file
        ];

        // Sideload the image
        $id = media_handle_sideload($file_array, 0, $seo_title);

        if (is_wp_error($id)) {
            @unlink($temp_file);
            error_log('ImageDownloadService: Failed to sideload ' . $final_filename . ' - ' . $id->get_error_message());
            return $remote_url; // Fallback to remote
        }

        // 5. Update Metadata
        update_post_meta($id, '_source_url', $remote_url);
        
        if (!empty($alt_text)) {
            update_post_meta($id, '_wp_attachment_image_alt', sanitize_text_field($alt_text));
        }

        // 6. Return new local URL
        $local_url = wp_get_attachment_url($id);
        
        if ($local_url) {
            set_transient($cache_key, $local_url, WEEK_IN_SECONDS);
            return $local_url;
        }

        return $remote_url;
    }
}
