// /src/components/slider.js
export class Slider {
    constructor(element) {
        this.element = element;
        this.config = JSON.parse(element.dataset.sliderConfig || '{}');
        this.track = element.querySelector('.slider-track');
        this.wrapper = element.querySelector('.slider-wrapper');
        this.slides = Array.from(element.querySelectorAll('.slider-slide'));
        this.prevBtn = element.querySelector('.slider-btn-prev');
        this.nextBtn = element.querySelector('.slider-btn-next');
        this.dots = Array.from(element.querySelectorAll('.slider-dot'));
        
        this.currentSlide = 0;
        this.totalSlides = 0;
        this.slidesPerView = this.config.perView.mobile;
        this.autoplayInterval = null;
        this.isTouchDevice = 'ontouchstart' in window;
        
        this.init();
    }
    
    init() {
        this.setupSlides();
        this.setupNavigation();
        this.setupPagination();
        this.setupResponsive();
        this.setupAccessibility();
        
        if (this.config.autoplay) {
            this.startAutoplay();
        }
        
        this.updateUI();
        this.bindEvents();
    }
    
    setupSlides() {
        // Initiale Slide-Breite setzen
        this.updateSlideWidth();
        
        // Slides zählen basierend auf aktueller Ansicht
        this.updateTotalSlides();
    }
    
    setupNavigation() {
        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', () => this.prev());
        }
        
        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', () => this.next());
        }
    }
    
    setupPagination() {
        this.dots.forEach(dot => {
            dot.addEventListener('click', (e) => {
                const slideIndex = parseInt(e.target.dataset.slide);
                this.goToSlide(slideIndex);
            });
        });
    }
    
    setupResponsive() {
        this.updateOnResize();
        window.addEventListener('resize', () => this.updateOnResize());
    }
    
    setupAccessibility() {
        this.element.setAttribute('role', 'region');
        this.element.setAttribute('aria-label', 'Media carousel');
        
        // Keyboard navigation
        this.element.addEventListener('keydown', (e) => {
            switch(e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                    this.prev();
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    this.next();
                    break;
                case 'Home':
                    e.preventDefault();
                    this.goToSlide(0);
                    break;
                case 'End':
                    e.preventDefault();
                    this.goToSlide(this.totalSlides - 1);
                    break;
            }
        });
        
        // Tabindex für bessere Navigation
        this.slides.forEach((slide, index) => {
            slide.setAttribute('tabindex', '0');
            slide.setAttribute('aria-label', `Media item ${index + 1} of ${this.slides.length}`);
        });
    }
    
    updateSlideWidth() {
        const width = this.getCurrentSlideWidth();
        this.slides.forEach(slide => {
            slide.style.width = `${width}%`;
        });
    }
    
    getCurrentSlideWidth() {
        const width = window.innerWidth;
        
        if (width < 768) {
            this.slidesPerView = this.config.perView.mobile;
            return 100 / this.config.perView.mobile;
        } else if (width < 1024) {
            this.slidesPerView = this.config.perView.tablet;
            return 100 / this.config.perView.tablet;
        } else {
            this.slidesPerView = this.config.perView.desktop;
            return 100 / this.config.perView.desktop;
        }
    }
    
    updateTotalSlides() {
        this.totalSlides = Math.ceil(this.slides.length / this.slidesPerView);
    }
    
    updateOnResize() {
        const oldSlidesPerView = this.slidesPerView;
        this.updateSlideWidth();
        this.updateTotalSlides();
        
        // Wenn sich slidesPerView ändert, aktualisiere currentSlide
        if (oldSlidesPerView !== this.slidesPerView) {
            this.currentSlide = Math.floor(this.currentSlide * oldSlidesPerView / this.slidesPerView);
            this.goToSlide(this.currentSlide);
        }
    }
    
    goToSlide(index) {
        if (index < 0 || index >= this.totalSlides) return;
        
        this.currentSlide = index;
        const translateX = -(index * 100);
        this.wrapper.style.transform = `translateX(${translateX}%)`;
        
        this.updateUI();
        this.dispatchEvent('slideChange', { index, total: this.totalSlides });
    }
    
    next() {
        if (this.currentSlide < this.totalSlides - 1) {
            this.goToSlide(this.currentSlide + 1);
        } else if (this.config.loop) {
            this.goToSlide(0);
        }
    }
    
    prev() {
        if (this.currentSlide > 0) {
            this.goToSlide(this.currentSlide - 1);
        } else if (this.config.loop) {
            this.goToSlide(this.totalSlides - 1);
        }
    }
    
    startAutoplay() {
        this.stopAutoplay();
        this.autoplayInterval = setInterval(() => {
            this.next();
        }, this.config.delay);
        
        // Pause bei Hover/Focus
        this.element.addEventListener('mouseenter', () => this.stopAutoplay());
        this.element.addEventListener('mouseleave', () => this.startAutoplay());
        this.element.addEventListener('focusin', () => this.stopAutoplay());
        this.element.addEventListener('focusout', () => this.startAutoplay());
    }
    
    stopAutoplay() {
        if (this.autoplayInterval) {
            clearInterval(this.autoplayInterval);
            this.autoplayInterval = null;
        }
    }
    
    updateUI() {
        // Update navigation buttons
        if (this.prevBtn) {
            this.prevBtn.disabled = !this.config.loop && this.currentSlide === 0;
        }
        
        if (this.nextBtn) {
            this.nextBtn.disabled = !this.config.loop && this.currentSlide === this.totalSlides - 1;
        }
        
        // Update pagination dots
        const currentDotIndex = Math.floor(this.currentSlide);
        this.dots.forEach((dot, index) => {
            dot.classList.toggle('!bg-blue-600', index === currentDotIndex);
            dot.classList.toggle('bg-gray-300', index !== currentDotIndex);
            dot.setAttribute('aria-current', index === currentDotIndex ? 'true' : 'false');
        });
        
        // Update aria-live für Screen Reader
        const liveRegion = this.element.querySelector('[aria-live]') || this.createLiveRegion();
        liveRegion.textContent = `Slide ${this.currentSlide + 1} of ${this.totalSlides}`;
    }
    
    createLiveRegion() {
        const region = document.createElement('div');
        region.setAttribute('aria-live', 'polite');
        region.setAttribute('aria-atomic', 'true');
        region.className = 'sr-only';
        this.element.appendChild(region);
        return region;
    }
    
    dispatchEvent(eventName, detail) {
        const event = new CustomEvent(`mediaSlider:${eventName}`, {
            detail: { ...detail, slider: this }
        });
        this.element.dispatchEvent(event);
    }
    
    bindEvents() {
        // Touch Events für Mobile
        if (this.isTouchDevice) {
            this.setupTouchEvents();
        }
        
        // Intersection Observer für Lazy Loading
        this.setupIntersectionObserver();
    }
    
    setupTouchEvents() {
        let startX = 0;
        let endX = 0;
        const threshold = 50;
        
        this.track.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
        });
        
        this.track.addEventListener('touchend', (e) => {
            endX = e.changedTouches[0].clientX;
            const diff = startX - endX;
            
            if (Math.abs(diff) > threshold) {
                if (diff > 0) {
                    this.next();
                } else {
                    this.prev();
                }
            }
        });
    }
    
    setupIntersectionObserver() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target.querySelector('img');
                    if (img && !img.loading) {
                        img.loading = 'eager';
                    }
                }
            });
        }, { threshold: 0.1 });
        
        this.slides.forEach(slide => observer.observe(slide));
    }
    
    destroy() {
        this.stopAutoplay();
        
        // Event Listeners entfernen
        if (this.prevBtn) this.prevBtn.removeEventListener('click', this.prev);
        if (this.nextBtn) this.nextBtn.removeEventListener('click', this.next);
        
        window.removeEventListener('resize', this.updateOnResize);
        
        this.dispatchEvent('destroy', { slider: this });
    }
}

// Auto-Initialisierung für alle Slider auf der Seite
export function initMediaSliders() {
    document.querySelectorAll('[data-media-slider]').forEach(element => {
        new MediaSlider(element);
    });
}

// Optional: Vite Hot Module Replacement
if (import.meta.hot) {
    import.meta.hot.accept(() => {
        document.querySelectorAll('[data-media-slider]').forEach(element => {
            if (element.mediaSlider) {
                element.mediaSlider.destroy();
            }
            element.mediaSlider = new MediaSlider(element);
        });
    });
}