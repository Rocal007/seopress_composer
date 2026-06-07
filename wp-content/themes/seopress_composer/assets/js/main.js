// Import styles
import '../scss/main.scss';

// Import Fonts (Local) - Optimized for performance
// Only import Latin subsets with font-display: swap for better LCP
import "@fontsource/inter/latin-400.css";
import "@fontsource/roboto/latin-400.css";
import "@fontsource/open-sans/latin-400.css";

// Import components
import initMenu from './components/menu.js';
import initToggle from './components/toggle.js';
import initTabs from './components/tabs.js';
import initModal from './components/modal.js';
import { initMediaSliders } from './components/slider.js';

import initVideoSlider from './components/video-slider.js';
import initHeader from './components/header.js';
import initMultiStepForm from './components/multi-step-form.js';
import initAccessibility from './components/accessibility.js';

document.addEventListener('DOMContentLoaded', () => {
    // Initialize accessibility features first
    initAccessibility();

    // Initialize other components
    initMenu();
    initToggle();
    initTabs();
    initModal();
    initMediaSliders();
    initVideoSlider();
    initHeader();
    initMultiStepForm();
    console.log('seopress_composer — Vite JS initialized with accessibility enhancements');
});
