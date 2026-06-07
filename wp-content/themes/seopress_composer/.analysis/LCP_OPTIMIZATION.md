# LCP & Performance Optimization Guide

## Problem
The CSS file (38.3 KiB) was blocking the initial page render, causing a 310ms delay in LCP (Largest Contentful Paint).

## Solutions Implemented

### 1. **Critical CSS Inlining**
- Created `CriticalCssService.php` to inline critical above-the-fold CSS
- Moves essential styles directly into the `<head>` for instant rendering
- Reduces render-blocking time for initial paint

**File:** `inc/Services/CriticalCssService.php`

### 2. **Deferred CSS Loading**
- Non-critical CSS is loaded asynchronously using `media="print"` trick
- Prevents blocking the initial render
- Includes `<noscript>` fallback for accessibility

### 3. **Resource Hints**
Added performance hints in `functions.php`:
- **DNS Prefetch:** Pre-resolves domain names for external resources
- **Preconnect:** Establishes early connections to critical origins
- **Preload:** Prioritizes critical CSS and font files

```php
// DNS Prefetch
<link rel="dns-prefetch" href="//fonts.googleapis.com">

// Preconnect
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

// Preload
<link rel="preload" href="/dist/assets/main.css" as="style">
<link rel="preload" href="/dist/assets/inter-latin-400-normal.woff2" as="font" type="font/woff2" crossorigin>
```

### 4. **Font Optimization**
**Before:**
- 7 font families (Inter, Roboto, Open Sans, Lato, Montserrat, Playfair Display, Merriweather)
- All character subsets (Latin, Cyrillic, Greek, Vietnamese, etc.)
- ~80 font files
- 183.5 KB CSS

**After:**
- 3 font families (Inter, Roboto, Open Sans)
- Latin subset only
- 6 font files
- 148.05 KB CSS (19% reduction)
- Gzipped: 22.40 KB

**Changes in `assets/js/main.js`:**
```javascript
// Before
import "@fontsource/inter";
import "@fontsource/roboto";
// ... 5 more fonts

// After
import "@fontsource/inter/latin-400.css";
import "@fontsource/roboto/latin-400.css";
import "@fontsource/open-sans/latin-400.css";
```

### 5. **Font Display Swap**
- Added `font-display: swap` to all font-face declarations
- Prevents FOIT (Flash of Invisible Text)
- Shows fallback fonts immediately while custom fonts load
- Improves perceived performance

**File:** `assets/css/fonts-optimized.css`

## Performance Metrics

### CSS Size
- **Original:** 183.5 KB
- **Optimized:** 148.05 KB
- **Reduction:** 35.45 KB (19%)
- **Gzipped:** 22.40 KB (85% compression)

### Font Files
- **Before:** ~80 files across 7 families
- **After:** 6 files (3 families × 2 formats)
- **Total font size:** ~131 KB (vs. ~500+ KB before)

### Expected LCP Improvements
- **Critical CSS inlined:** ~50-100ms faster initial render
- **Deferred CSS:** Removes render-blocking delay
- **Font optimization:** ~200ms faster font loading
- **Resource hints:** ~20-50ms faster resource discovery

**Estimated total improvement:** 270-350ms reduction in LCP

## How It Works

### Loading Sequence
1. **HTML loads** → Browser starts parsing
2. **Critical CSS renders** → Above-the-fold content displays immediately
3. **Resource hints** → Browser pre-fetches critical resources
4. **Deferred CSS loads** → Full styles apply without blocking
5. **Fonts load with swap** → Text visible immediately with fallback fonts

### Browser Compatibility
- ✅ All modern browsers (Chrome, Firefox, Safari, Edge)
- ✅ Graceful degradation for older browsers
- ✅ `<noscript>` fallback for CSS

## Testing

### Before Deployment
1. Clear all caches (browser, WordPress, CDN)
2. Test on PageSpeed Insights
3. Check WebPageTest for filmstrip view
4. Verify fonts load correctly

### After Deployment
1. Monitor Core Web Vitals in Google Search Console
2. Check LCP scores in PageSpeed Insights
3. Verify no FOIT (Flash of Invisible Text)
4. Ensure all fonts render correctly

## Maintenance

### Adding New Fonts
If you need to add fonts in the future:
1. Only import Latin subset: `@fontsource/font-name/latin-400.css`
2. Add preload hint in `functions.php`
3. Rebuild: `npm run build`

### Updating Critical CSS
Edit `inc/Services/CriticalCssService.php` → `getCriticalCss()` method

### Monitoring Performance
- Use Google Search Console → Core Web Vitals report
- Run monthly PageSpeed Insights tests
- Monitor real user metrics (RUM) if available

## Additional Recommendations

### 1. **Image Optimization**
- Use WebP format with fallbacks
- Add `loading="lazy"` to below-fold images
- Add `fetchpriority="high"` to LCP image

### 2. **JavaScript Optimization**
- Defer non-critical JavaScript
- Use code splitting for large bundles
- Consider dynamic imports for heavy components

### 3. **Caching Strategy**
- Set long cache headers for static assets (1 year)
- Use versioning/hashing for cache busting
- Implement service worker for offline support

### 4. **CDN Integration**
- Serve static assets from CDN
- Use HTTP/2 or HTTP/3 for multiplexing
- Enable Brotli compression

## Files Modified

1. ✅ `inc/Services/CriticalCssService.php` (new)
2. ✅ `functions.php` (updated)
3. ✅ `assets/js/main.js` (optimized)
4. ✅ `assets/css/fonts-optimized.css` (new)

## Next Steps

1. Deploy to production
2. Clear all caches
3. Test with PageSpeed Insights
4. Monitor Core Web Vitals
5. Consider implementing additional optimizations above

---

**Last Updated:** 2026-01-15
**Performance Target:** LCP < 2.5s, FCP < 1.8s
