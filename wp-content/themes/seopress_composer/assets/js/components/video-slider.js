/**
 * SEOPress Video Carousel (Vanilla JS)
 *
 * Dieses Skript findet alle Video-Karussells auf der Seite
 * und initialisiert die Slider-Logik sowie das Lazy Loading.
 */
export default function initVideoSlider() {
  console.log('SEOPress Video Carousel JS initialized.');
  // Finde ALLE Video-Sektionen auf der Seite
  const allCarousels = document.querySelectorAll('.seopress-video-section');

  if (allCarousels.length === 0) {
    return; // Nichts zu tun
  }

  // Wende die Logik auf JEDES gefundene Karussell an
  allCarousels.forEach(function (carousel) {

    // Verhindere doppelte Initialisierung
    if (carousel.dataset.jsAttached === 'true') {
      return;
    }
    carousel.dataset.jsAttached = 'true';

    // --- Selektoren relativ zum aktuellen Karussell ---
    const slides = carousel.querySelectorAll('.seopress-video-slide');
    const inner = carousel.querySelector('.seopress-video-carousel-inner');
    const nextBtn = carousel.querySelector('.seopress-carousel-control.seopress-next');
    const prevBtn = carousel.querySelector('.seopress-carousel-control.seopress-prev');
    const placeholders = carousel.querySelectorAll('.seopress-video-placeholder[data-loaded="false"]');

    if (!inner || !nextBtn || !prevBtn || slides.length === 0) {
      console.warn('SEOPress Carousel: Erforderliche Elemente (inner, next, prev) nicht gefunden.', carousel);
      return;
    }

    let currentIndex = 0;
    const totalSlides = slides.length;

    // --- 1. Carousel Logik ---

    function updateCarousel() {
      const offset = -currentIndex * 100;
      inner.style.transform = `translateX(${offset}%)`;

      slides.forEach((slide, index) => {
        const isActive = index === currentIndex;
        slide.setAttribute('aria-hidden', !isActive);
        if (isActive) {
          slide.classList.add('seopress-active');
        } else {
          slide.classList.remove('seopress-active');
        }
      });
    }

    nextBtn.addEventListener('click', () => {
      currentIndex = (currentIndex + 1) % totalSlides;
      updateCarousel();
    });

    prevBtn.addEventListener('click', () => {
      currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
      updateCarousel();
    });

    updateCarousel(); // Initialisiere erstes Slide

    // --- 2. Intersection Observer (Lazy Loading) ---

    const observerCallback = (entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el = entry.target;

          el.classList.add('seopress-is-ready');

          el.addEventListener('click', function loadVideo(e) {
            e.preventDefault();
            const src = el.getAttribute('data-src');

            el.innerHTML = `<iframe src="${src}" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen title="YouTube video"></iframe>`;
            el.setAttribute('data-loaded', 'true');

            el.removeEventListener('click', loadVideo);
          }, { once: true });

          observer.unobserve(el);
        }
      });
    };

    const observer = new IntersectionObserver(observerCallback, {
      rootMargin: '200px'
    });

    placeholders.forEach(el => observer.observe(el));
  });
}
