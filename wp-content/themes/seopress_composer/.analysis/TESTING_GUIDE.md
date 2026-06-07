# Quick Testing Guide

## 🧪 Pre-Deployment Testing

### 1. Local Testing
```bash
# Clear all caches
cd c:/laragon/www/antik-live/wp-content/themes/seopress_composer
npm run build

# Test in browser
# 1. Open DevTools (F12)
# 2. Go to Network tab
# 3. Disable cache
# 4. Reload page (Ctrl+Shift+R)
```

### 2. Check Critical CSS
**What to verify:**
- [ ] Page renders immediately (no white screen)
- [ ] Above-fold content visible within 100ms
- [ ] No layout shift when full CSS loads

**How to check:**
1. Open DevTools → Network tab
2. Throttle to "Slow 3G"
3. Reload page
4. Content should be visible immediately

### 3. Check Font Loading
**What to verify:**
- [ ] Text visible immediately (no FOIT)
- [ ] Fallback fonts display first
- [ ] Custom fonts swap in smoothly
- [ ] No layout shift during font swap

**How to check:**
1. Open DevTools → Network tab
2. Filter by "Font"
3. Should see only 6 font files (3 families × 2 formats)
4. Check for `font-display: swap` in CSS

### 4. Check Image Priority
**What to verify:**
- [ ] First image has `fetchpriority="high"`
- [ ] First image has `loading="eager"`
- [ ] Other images have `loading="lazy"`

**How to check:**
1. Right-click first image → Inspect
2. Check attributes in HTML

### 5. Check Resource Hints
**What to verify:**
- [ ] DNS prefetch links in `<head>`
- [ ] Preconnect links in `<head>`
- [ ] Preload links for CSS and fonts

**How to check:**
1. View page source (Ctrl+U)
2. Look in `<head>` section
3. Should see:
```html
<link rel="dns-prefetch" href="//fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" href="/dist/assets/main.css" as="style">
<link rel="preload" href="/dist/assets/inter-latin-400-normal.woff2" as="font" type="font/woff2" crossorigin>
```

## 📊 Performance Testing Tools

### 1. PageSpeed Insights
```
URL: https://pagespeed.web.dev/
Test: https://www.1a-antiquitaeten.at

Target Scores:
✅ Performance: > 90
✅ LCP: < 2.5s
✅ FCP: < 1.8s
✅ CLS: < 0.1
```

### 2. WebPageTest
```
URL: https://www.webpagetest.org/
Test: https://www.1a-antiquitaeten.at

Settings:
- Location: Frankfurt, Germany
- Browser: Chrome
- Connection: Cable

What to check:
✅ Start Render: < 1.5s
✅ LCP: < 2.5s
✅ Filmstrip shows content early
```

### 3. Chrome DevTools Lighthouse
```
1. Open DevTools (F12)
2. Go to Lighthouse tab
3. Select "Performance"
4. Click "Analyze page load"

Target Scores:
✅ Performance: > 90
✅ All Core Web Vitals: Green
```

### 4. Chrome DevTools Coverage
```
1. Open DevTools (F12)
2. Press Ctrl+Shift+P
3. Type "Coverage"
4. Click "Start instrumenting coverage"
5. Reload page

What to check:
✅ CSS coverage > 50% (critical CSS working)
✅ Unused CSS < 50KB
```

## 🔍 Visual Regression Testing

### Before/After Screenshots
1. Take screenshot BEFORE optimization
2. Take screenshot AFTER optimization
3. Compare side-by-side
4. Ensure no visual differences

### Layout Shift Check
1. Open DevTools → Performance tab
2. Record page load
3. Check for "Layout Shift" events
4. Should be minimal (< 0.1)

## ✅ Checklist

### Pre-Deployment
- [ ] Build completed successfully
- [ ] No console errors
- [ ] Fonts load correctly
- [ ] Images display properly
- [ ] No layout shifts
- [ ] Critical CSS renders
- [ ] Deferred CSS loads

### Post-Deployment
- [ ] Clear CDN cache
- [ ] Clear WordPress cache
- [ ] Test on mobile device
- [ ] Test on desktop
- [ ] PageSpeed score > 90
- [ ] LCP < 2.5s
- [ ] No visual regressions

## 🐛 Troubleshooting

### Issue: White screen on load
**Solution:**
- Check if Critical CSS is being output
- View page source, look for `<style id="critical-css">`
- If missing, check if `CriticalCssService` is initialized

### Issue: Fonts not loading
**Solution:**
- Check Network tab for font requests
- Verify font files exist in `/dist/assets/`
- Check for CORS errors
- Verify preload hints are present

### Issue: CSS not loading
**Solution:**
- Check if deferred CSS is working
- Look for `<link rel="preload" as="style">`
- Check for JavaScript errors
- Verify manifest.json is correct

### Issue: Images loading slowly
**Solution:**
- Check if `fetchpriority="high"` is on first image
- Verify `ImageOptimizationService` is initialized
- Check image file sizes
- Consider WebP format

## 📱 Mobile Testing

### Chrome DevTools Device Emulation
```
1. Open DevTools (F12)
2. Click device toolbar (Ctrl+Shift+M)
3. Select "iPhone 12 Pro"
4. Reload page
5. Check performance
```

### Real Device Testing
```
1. Open site on mobile device
2. Check loading speed
3. Verify fonts display correctly
4. Check for layout issues
5. Test touch interactions
```

## 🎯 Success Criteria

### Performance Metrics
```
✅ LCP < 2.5s
✅ FCP < 1.8s
✅ CLS < 0.1
✅ FID < 100ms
✅ PageSpeed Score > 90
```

### User Experience
```
✅ Content visible < 1s
✅ No white screen
✅ No invisible text (FOIT)
✅ Smooth font swap
✅ Fast image loading
```

### Technical
```
✅ CSS size reduced
✅ Font files reduced
✅ Critical CSS inline
✅ Resource hints present
✅ No console errors
```

## 📞 Support

If you encounter issues:
1. Check console for errors
2. Review Network tab
3. Verify all files built correctly
4. Check documentation in `.analysis/`
5. Rollback if necessary (see PERFORMANCE_SUMMARY.md)

---

**Last Updated:** 2026-01-15  
**Quick Test Time:** ~5 minutes  
**Full Test Time:** ~15 minutes
