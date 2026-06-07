<?php

namespace SeopressComposer\Services;

/**
 * Critical CSS Service
 * 
 * Handles critical CSS extraction and inline rendering to improve LCP
 */
class CriticalCssService
{
  /**
   * Initialize the service
   */
  public function __construct()
  {
    add_action('wp_head', [$this, 'inlineCriticalCss'], 1);
    add_filter('style_loader_tag', [$this, 'deferNonCriticalCss'], 10, 4);
  }

  /**
   * Inline critical CSS in the head
   */
  public function inlineCriticalCss()
  {
    // Critical CSS for above-the-fold content
    $critical_css = $this->getCriticalCss();

    if (!empty($critical_css)) {
      echo '<style id="critical-css">' . $critical_css . '</style>';
    }
  }

  /**
   * Get critical CSS content
   * 
   * @return string
   */
  private function getCriticalCss()
  {
    // Critical CSS - optimized for above-the-fold content
    return '
            /* Reset & Base */
            *,::before,::after{box-sizing:border-box;border-width:0;border-style:solid;border-color:#e5e7eb}
            html{line-height:1.5;-webkit-text-size-adjust:100%;tab-size:4;font-family:var(--font-body, ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif)}
            body{margin:0;line-height:inherit}
            
            /* Critical Layout */
            .container{width:100%;margin-right:auto;margin-left:auto;padding-right:1rem;padding-left:1rem}
            @media (min-width:640px){.container{max-width:640px}}
            @media (min-width:768px){.container{max-width:768px}}
            @media (min-width:1024px){.container{max-width:1024px}}
            @media (min-width:1280px){.container{max-width:1280px}}
            
            /* Critical Typography */
            h1,h2,h3,h4,h5,h6{font-size:inherit;font-weight:inherit;font-family:var(--font-heading, inherit)}
            a{color:inherit;text-decoration:inherit}
            
            /* Critical Utilities */
            .flex{display:flex}
            .grid{display:grid}
            .hidden{display:none}
            .block{display:block}
            .inline-block{display:inline-block}
            
            /* Screen Reader Only (for hidden crawler nav) */
            .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border-width:0}
            
            /* Prevent FOUC */
            body{opacity:1;transition:opacity 0.3s ease-in}
            
            /* Critical Header Styles */
            header{position:relative;z-index:50}
            
            /* Loading State */
            .loading-css{opacity:0}
        ';
  }

  /**
   * Defer non-critical CSS loading
   * 
   * @param string $html
   * @param string $handle
   * @param string $href
   * @param string $media
   * @return string
   */
  public function deferNonCriticalCss($html, $handle, $href, $media)
  {
    // Only defer theme CSS
    if ($handle === 'theme') {
      // Use media="print" trick to load CSS asynchronously
      // Handle both single and double quote styles from WordPress
      $html = str_replace(
        ["media='all'", 'media="all"'],
        ["media='print' onload=\"this.media='all'; this.onload=null;\"", 'media="print" onload="this.media=\'all\'; this.onload=null;"'],
        $html
      );

      // Add noscript fallback
      $html .= '<noscript><link rel="stylesheet" href="' . esc_url($href) . '"></noscript>';
    }

    return $html;
  }
}
