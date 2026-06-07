/**
 * Accessibility Enhancements
 * WCAG 2.1 AA Compliance
 */

export default function initAccessibility() {
  // Detect keyboard navigation
  detectKeyboardNavigation();

  // Enhance menu accessibility
  enhanceMenuAccessibility();

  // Add ARIA live regions
  addLiveRegions();

  // Improve focus management
  improveFocusManagement();
}

/**
 * Detect keyboard navigation and add visual indicators
 * WCAG 2.4.7 Focus Visible
 */
function detectKeyboardNavigation() {
  let isUsingKeyboard = false;

  // Detect Tab key usage
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Tab') {
      isUsingKeyboard = true;
      document.body.classList.add('keyboard-nav');
    }
  });

  // Detect mouse usage
  document.addEventListener('mousedown', () => {
    isUsingKeyboard = false;
    document.body.classList.remove('keyboard-nav');
  });
}

/**
 * Enhance menu accessibility with proper ARIA attributes
 * WCAG 4.1.2 Name, Role, Value
 */
function enhanceMenuAccessibility() {
  const menuButton = document.querySelector('[aria-controls="main-navigation-menu"]');
  const menu = document.getElementById('main-navigation-menu');

  if (!menuButton || !menu) return;

  // Toggle aria-expanded on click
  menuButton.addEventListener('click', () => {
    const isExpanded = menuButton.getAttribute('aria-expanded') === 'true';
    menuButton.setAttribute('aria-expanded', !isExpanded);
  });

  // Close menu on Escape key
  menu.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      menuButton.setAttribute('aria-expanded', 'false');
      menuButton.focus();
    }
  });

  // Handle submenu keyboard navigation
  const submenus = menu.querySelectorAll('details');
  submenus.forEach(details => {
    const summary = details.querySelector('summary');

    if (summary) {
      // Add ARIA attributes
      summary.setAttribute('aria-expanded', details.open);

      // Update aria-expanded on toggle
      details.addEventListener('toggle', () => {
        summary.setAttribute('aria-expanded', details.open);
      });

      // Keyboard navigation for submenus
      summary.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          details.open = !details.open;
          summary.setAttribute('aria-expanded', details.open);
        }
      });
    }
  });

  // Trap focus within menu when open
  menu.addEventListener('keydown', (e) => {
    if (e.key === 'Tab') {
      const focusableElements = menu.querySelectorAll(
        'a[href], button:not([disabled]), summary, [tabindex]:not([tabindex="-1"])'
      );
      const firstElement = focusableElements[0];
      const lastElement = focusableElements[focusableElements.length - 1];

      if (e.shiftKey && document.activeElement === firstElement) {
        e.preventDefault();
        lastElement.focus();
      } else if (!e.shiftKey && document.activeElement === lastElement) {
        e.preventDefault();
        firstElement.focus();
      }
    }
  });
}

/**
 * Add ARIA live regions for dynamic content updates
 * WCAG 4.1.3 Status Messages
 */
function addLiveRegions() {
  // Create polite live region for non-critical updates
  const politeLiveRegion = document.createElement('div');
  politeLiveRegion.setAttribute('aria-live', 'polite');
  politeLiveRegion.setAttribute('aria-atomic', 'true');
  politeLiveRegion.className = 'sr-only';
  politeLiveRegion.id = 'polite-announcements';
  document.body.appendChild(politeLiveRegion);

  // Create assertive live region for critical updates
  const assertiveLiveRegion = document.createElement('div');
  assertiveLiveRegion.setAttribute('aria-live', 'assertive');
  assertiveLiveRegion.setAttribute('aria-atomic', 'true');
  assertiveLiveRegion.className = 'sr-only';
  assertiveLiveRegion.id = 'assertive-announcements';
  document.body.appendChild(assertiveLiveRegion);

  // Expose announce function globally
  window.announceToScreenReader = function (message, priority = 'polite') {
    const region = priority === 'assertive'
      ? assertiveLiveRegion
      : politeLiveRegion;

    region.textContent = message;

    // Clear after announcement
    setTimeout(() => {
      region.textContent = '';
    }, 1000);
  };
}

/**
 * Improve focus management for better keyboard navigation
 * WCAG 2.4.3 Focus Order
 */
function improveFocusManagement() {
  // Skip to content link functionality
  const skipLink = document.querySelector('.skip-to-content');
  if (skipLink) {
    skipLink.addEventListener('click', (e) => {
      e.preventDefault();
      const mainContent = document.getElementById('main-content');
      if (mainContent) {
        mainContent.setAttribute('tabindex', '-1');
        mainContent.focus();
        mainContent.addEventListener('blur', () => {
          mainContent.removeAttribute('tabindex');
        }, { once: true });
      }
    });
  }

  // Ensure modals trap focus
  const modals = document.querySelectorAll('[role="dialog"]');
  modals.forEach(modal => {
    modal.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        const closeButton = modal.querySelector('[aria-label*="close"], [aria-label*="schließen"]');
        if (closeButton) {
          closeButton.click();
        }
      }

      // Trap focus within modal
      if (e.key === 'Tab') {
        const focusableElements = modal.querySelectorAll(
          'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])'
        );
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        if (e.shiftKey && document.activeElement === firstElement) {
          e.preventDefault();
          lastElement.focus();
        } else if (!e.shiftKey && document.activeElement === lastElement) {
          e.preventDefault();
          firstElement.focus();
        }
      }
    });
  });

  // Add focus indicators to custom interactive elements
  const interactiveElements = document.querySelectorAll('[onclick], [data-toggle]');
  interactiveElements.forEach(element => {
    if (!element.hasAttribute('tabindex') && element.tagName !== 'A' && element.tagName !== 'BUTTON') {
      element.setAttribute('tabindex', '0');
      element.setAttribute('role', 'button');

      // Add keyboard support
      element.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          element.click();
        }
      });
    }
  });

  // Announce page changes for SPAs (if applicable)
  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
        const newHeading = Array.from(mutation.addedNodes).find(
          node => node.tagName && node.tagName.match(/^H[1-6]$/)
        );
        if (newHeading && window.announceToScreenReader) {
          window.announceToScreenReader(`Neue Sektion: ${newHeading.textContent}`);
        }
      }
    });
  });

  // Observe main content for changes
  const mainContent = document.getElementById('main-content');
  if (mainContent) {
    observer.observe(mainContent, { childList: true, subtree: true });
  }
}

/**
 * Enhance form accessibility
 * WCAG 3.3.1 Error Identification, 3.3.2 Labels or Instructions
 */
export function enhanceFormAccessibility(form) {
  if (!form) return;

  const inputs = form.querySelectorAll('input, textarea, select');

  inputs.forEach(input => {
    // Ensure all inputs have labels
    const label = form.querySelector(`label[for="${input.id}"]`);
    if (!label && !input.getAttribute('aria-label')) {
      console.warn('Input without label:', input);
    }

    // Add aria-invalid for validation
    input.addEventListener('invalid', () => {
      input.setAttribute('aria-invalid', 'true');

      // Announce error to screen reader
      if (window.announceToScreenReader) {
        const errorMessage = input.validationMessage || 'Eingabe ungültig';
        window.announceToScreenReader(`Fehler: ${errorMessage}`, 'assertive');
      }
    });

    input.addEventListener('input', () => {
      if (input.validity.valid) {
        input.setAttribute('aria-invalid', 'false');
      }
    });

    // Add aria-required for required fields
    if (input.hasAttribute('required')) {
      input.setAttribute('aria-required', 'true');
    }
  });

  // Announce form submission
  form.addEventListener('submit', () => {
    if (window.announceToScreenReader) {
      window.announceToScreenReader('Formular wird gesendet...', 'polite');
    }
  });
}
