# Accessibility Quick Reference Card

## 🎯 New CSS Classes Available

### Screen Reader Utilities
```html
<!-- Hide visually, available to screen readers -->
<span class="sr-only">Additional context for screen readers</span>

<!-- Show on focus (e.g., skip links) -->
<a href="#main" class="sr-only-focusable">Skip to content</a>
```

### Focus Indicators
```html
<!-- Highlight container when child has focus -->
<div class="focus-within-highlight">
  <input type="text">
</div>
```

### High Contrast Text
```html
<!-- Maximum contrast (black on white) -->
<div class="text-contrast-high">High contrast text</div>

<!-- Inverse (white on black) -->
<div class="text-contrast-high-inverse">Inverse contrast</div>
```

### Form Validation
```html
<!-- Error message with icon -->
<span class="error-message">Please enter a valid email</span>

<!-- Success message with icon -->
<span class="success-message">Form submitted successfully</span>
```

---

## 🎹 Keyboard Shortcuts

| Key | Action |
|-----|--------|
| `Tab` | Navigate forward through interactive elements |
| `Shift + Tab` | Navigate backward |
| `Enter` | Activate links and buttons |
| `Space` | Activate buttons, toggle checkboxes |
| `Escape` | Close menus and modals |
| `Arrow Keys` | Navigate within menus (if implemented) |

---

## 🔊 JavaScript API

### Announce to Screen Readers
```javascript
// Polite announcement (non-interrupting)
window.announceToScreenReader('Page loaded successfully');

// Assertive announcement (interrupts current speech)
window.announceToScreenReader('Error: Form submission failed', 'assertive');
```

### Enhance Form Accessibility
```javascript
import { enhanceFormAccessibility } from './components/accessibility.js';

const form = document.querySelector('#my-form');
enhanceFormAccessibility(form);
```

---

## 🏷️ ARIA Attributes to Use

### Landmarks
```html
<main id="main-content" role="main" aria-label="Hauptinhalt">
<nav role="navigation" aria-label="Hauptnavigation">
<aside role="complementary" aria-label="Sidebar">
<footer role="contentinfo" aria-label="Footer">
```

### Interactive Elements
```html
<!-- Button with expanded state -->
<button aria-expanded="false" aria-controls="menu-id">Menu</button>

<!-- Current page in navigation -->
<a href="/current" aria-current="page">Current Page</a>

<!-- Toggle button -->
<button aria-pressed="false">Toggle</button>

<!-- Required form field -->
<input type="text" aria-required="true" aria-invalid="false">
```

### Decorative Elements
```html
<!-- Hide from screen readers -->
<img src="decorative.jpg" alt="" aria-hidden="true">
<svg aria-hidden="true">...</svg>
```

### Live Regions
```html
<!-- Polite updates -->
<div aria-live="polite" aria-atomic="true">
  Status: Processing...
</div>

<!-- Urgent updates -->
<div aria-live="assertive" aria-atomic="true">
  Error: Connection lost
</div>
```

---

## ✅ Accessibility Checklist for New Components

### Before Adding a New Component:

- [ ] All images have alt text (or alt="" if decorative)
- [ ] All form inputs have associated labels
- [ ] Interactive elements are keyboard accessible
- [ ] Focus indicators are visible
- [ ] Color is not the only means of conveying information
- [ ] Text has sufficient contrast (4.5:1 minimum)
- [ ] ARIA attributes are used correctly
- [ ] Component works with screen readers
- [ ] Component respects prefers-reduced-motion
- [ ] Component works at 200% zoom

---

## 🧪 Quick Testing Commands

### Test Keyboard Navigation
1. Press `Tab` to navigate through the page
2. Verify all interactive elements are reachable
3. Check that focus indicators are visible
4. Test `Escape` key in menus/modals

### Test with Screen Reader (NVDA)
```
1. Download NVDA: https://www.nvaccess.org/
2. Install and start NVDA
3. Navigate with Tab key
4. Listen to announcements
5. Test forms and dynamic content
```

### Test Color Contrast
```
1. Open: https://webaim.org/resources/contrastchecker/
2. Enter foreground and background colors
3. Verify ratio is at least 4.5:1 (normal text)
4. Verify ratio is at least 3:1 (large text, 18pt+)
```

### Run Automated Audit
```bash
# Using Lighthouse in Chrome DevTools
1. Open Chrome DevTools (F12)
2. Go to "Lighthouse" tab
3. Select "Accessibility"
4. Click "Generate report"

# Using axe DevTools
1. Install axe DevTools extension
2. Open DevTools
3. Go to "axe DevTools" tab
4. Click "Scan ALL of my page"
```

---

## 🎨 Color Contrast Requirements

### WCAG 2.1 AA Standards

| Text Size | Contrast Ratio |
|-----------|----------------|
| Normal text (< 18pt) | 4.5:1 |
| Large text (≥ 18pt or 14pt bold) | 3:1 |
| UI components & graphics | 3:1 |

### Quick Check
```scss
// Good contrast examples
color: #000000; background: #ffffff; // 21:1 ✅
color: #ffffff; background: #0066cc; // 7.7:1 ✅
color: #333333; background: #ffffff; // 12.6:1 ✅

// Poor contrast examples
color: #777777; background: #ffffff; // 4.5:1 ⚠️ (borderline)
color: #cccccc; background: #ffffff; // 1.6:1 ❌ (fails)
```

---

## 📱 Responsive Accessibility

### Touch Target Size
```scss
// Minimum touch target: 44x44 pixels (WCAG 2.5.5)
button, a {
  min-height: 44px;
  min-width: 44px;
  padding: 12px 16px;
}
```

### Zoom and Reflow
```html
<!-- Viewport meta tag (already in header.php) -->
<meta name="viewport" content="width=device-width, initial-scale=1.0">
```

Test at 200% zoom:
- No horizontal scrolling
- All content readable
- No overlapping elements

---

## 🚨 Common Mistakes to Avoid

### ❌ Don't Do This:
```html
<!-- Missing alt text -->
<img src="photo.jpg">

<!-- Div as button without keyboard support -->
<div onclick="doSomething()">Click me</div>

<!-- Color-only indication -->
<span style="color: red;">Error</span>

<!-- Placeholder as label -->
<input type="text" placeholder="Name">

<!-- Low contrast text -->
<p style="color: #999; background: #fff;">Text</p>
```

### ✅ Do This Instead:
```html
<!-- Descriptive alt text -->
<img src="photo.jpg" alt="Team meeting in conference room">

<!-- Proper button with keyboard support -->
<button onclick="doSomething()">Click me</button>

<!-- Icon + text for error -->
<span class="error-message">Error: Invalid input</span>

<!-- Proper label -->
<label for="name">Name</label>
<input type="text" id="name" placeholder="John Doe">

<!-- Sufficient contrast -->
<p style="color: #333; background: #fff;">Text</p>
```

---

## 🔧 Browser Support

### Focus-Visible
- ✅ Chrome 86+
- ✅ Firefox 85+
- ✅ Safari 15.4+
- ✅ Edge 86+

### Prefers-Reduced-Motion
- ✅ Chrome 74+
- ✅ Firefox 63+
- ✅ Safari 10.1+
- ✅ Edge 79+

### Prefers-Contrast
- ✅ Chrome 96+
- ✅ Firefox 101+
- ✅ Safari 14.1+
- ✅ Edge 96+

---

## 📞 Need Help?

### Resources
- **WCAG Guidelines:** https://www.w3.org/WAI/WCAG21/quickref/
- **WebAIM:** https://webaim.org/
- **A11y Project:** https://www.a11yproject.com/
- **MDN Accessibility:** https://developer.mozilla.org/en-US/docs/Web/Accessibility

### Testing Tools
- **axe DevTools:** https://www.deque.com/axe/devtools/
- **WAVE:** https://wave.webaim.org/
- **Lighthouse:** Built into Chrome DevTools
- **NVDA Screen Reader:** https://www.nvaccess.org/

---

**Quick Reference Version:** 1.0  
**Last Updated:** 2026-02-16  
**WCAG Level:** 2.1 AA
