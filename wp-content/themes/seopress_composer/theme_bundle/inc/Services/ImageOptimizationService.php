<?php

namespace SeopressComposer\Services;

/**
 * Image Optimization Service
 * 
 * Automatically optimizes images for better LCP and performance
 */
class ImageOptimizationService
{
  private $first_image_processed = false;

  /**
   * Initialize the service
   */
  public function __construct()
  {
    add_filter('wp_get_attachment_image_attributes', [$this, 'addFetchPriorityToFirstImage'], 10, 3);
    add_filter('the_content', [$this, 'optimizeContentImages'], 20);

    // Hooks for native WebP conversion of local media library images
    add_filter('wp_get_attachment_url', [$this, 'serveWebpForAttachmentUrl'], 10, 2);
    add_filter('wp_get_attachment_image_src', [$this, 'serveWebpForImageSrc'], 10, 4);
    add_filter('wp_calculate_image_srcset', [$this, 'serveWebpForSrcset'], 10, 5);
  }

  /**
   * Filter srcset to serve WebP
   */
  public function serveWebpForSrcset($sources, $size_array, $image_src, $image_meta, $attachment_id)
  {
    if (is_array($sources)) {
      foreach ($sources as &$source) {
        if (isset($source['url'])) {
          $source['url'] = $this->convertToWebp($source['url']);
        }
      }
    }
    return $sources;
  }

  /**
   * Add fetchpriority="high" to the first image in content
   * 
   * @param array $attr Image attributes
   * @param WP_Post $attachment Image attachment post
   * @param string|array $size Image size
   * @return array Modified attributes
   */
  public function addFetchPriorityToFirstImage($attr, $attachment, $size)
  {
    // Only add to the first image
    if (!$this->first_image_processed && is_singular()) {
      $attr['fetchpriority'] = 'high';
      $attr['loading'] = 'eager';
      $this->first_image_processed = true;
    }

    return $attr;
  }

  /**
   * Optimize images in post content
   * 
   * @param string $content Post content
   * @return string Modified content
   */
  public function optimizeContentImages($content)
  {
    if (!is_singular() || empty($content)) {
      return $content;
    }

    // Add fetchpriority="high" to the first image in content
    $content = preg_replace_callback(
      '/<img([^>]+)>/i',
      function ($matches) {
        static $first_image = true;

        if ($first_image) {
          $first_image = false;
          $img_tag = $matches[0];

          // Add fetchpriority="high" if not already present
          if (strpos($img_tag, 'fetchpriority') === false) {
            $img_tag = str_replace('<img', '<img fetchpriority="high"', $img_tag);
          }

          // Change loading="lazy" to loading="eager" for first image
          $img_tag = str_replace('loading="lazy"', 'loading="eager"', $img_tag);

          return $img_tag;
        }

        return $matches[0];
      },
      $content,
      1 // Only replace the first occurrence
    );

    return $content;
  }

  /**
   * Reset the first image flag (useful for testing)
   */
  public function resetFirstImageFlag()
  {
    $this->first_image_processed = false;
  }

  /**
   * Filter attachment URL to serve WebP
   */
  public function serveWebpForAttachmentUrl($url, $post_id)
  {
    return $this->convertToWebp($url);
  }

  /**
   * Filter image src to serve WebP
   */
  public function serveWebpForImageSrc($image, $attachment_id, $size, $icon)
  {
    if ($image && is_array($image) && isset($image[0])) {
      $image[0] = $this->convertToWebp($image[0]);
    }
    return $image;
  }

  /**
   * Convert local image URL to WebP dynamically
   */
  private function convertToWebp($url)
  {
    if (empty($url) || !is_string($url)) return $url;
    if (strpos(strtolower($url), '.webp') !== false) return $url;

    // Only process jpg, jpeg, png
    if (!preg_match('/\.(jpe?g|png)$/i', $url)) return $url;

    $upload_dir = wp_upload_dir();
    $base_url = $upload_dir['baseurl'];
    $base_dir = $upload_dir['basedir'];

    if (strpos($url, $base_url) === 0) {
      $rel_path = str_replace($base_url, '', $url);
      $file_path = $base_dir . $rel_path;

      if (file_exists($file_path)) {
        $webp_path = preg_replace('/\.(jpe?g|png)$/i', '.webp', $file_path);
        $webp_url = preg_replace('/\.(jpe?g|png)$/i', '.webp', $url);

        if (file_exists($webp_path)) {
          return $webp_url;
        }

        // Need to convert
        $converted = $this->generateWebp($file_path, $webp_path);
        if ($converted) {
          return $webp_url;
        }
      }
    }
    return $url;
  }

  /**
   * Generate WebP file from source image using Imagick with GD fallback
   */
  private function generateWebp($source, $destination)
  {
    // 1. Try Imagick for best quality and preservation
    if (extension_loaded('imagick') && class_exists('Imagick')) {
      try {
        $imagick = new \Imagick($source);
        $imagick->setImageFormat('webp');
        $imagick->setImageCompressionQuality(82);
        $imagick->setOption('webp:lossless', 'false');
        $imagick->writeImage($destination);
        $imagick->clear();
        $imagick->destroy();
        return true;
      } catch (\Exception $e) {
        // Silently fallback to GD
      }
    }

    // 2. Try GD Fallback
    $info = @getimagesize($source);
    if ($info === false) return false;

    $image = null;
    if ($info[2] === IMAGETYPE_JPEG) {
      $image = @imagecreatefromjpeg($source);
    } elseif ($info[2] === IMAGETYPE_PNG) {
      $image = @imagecreatefrompng($source);
      if ($image) {
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);
      }
    }

    if ($image) {
      $result = @imagewebp($image, $destination, 82);
      imagedestroy($image);
      return $result;
    }

    return false;
  }
}
