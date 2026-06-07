/**
 * Smart Header (Sticky Scroll) Behavior
 */
export default function initHeader() {
  const header = document.getElementById("seopress-composer-header");
  if (!header) return;

  let lastScroll = window.scrollY;
  let headerVisible = true;
  let ticking = false;
  const triggerPoint = 200; // Start behavior after 200px scroll

  const onScroll = () => {
    const current = window.scrollY;

    // Only start hiding/showing header after scrolling past triggerPoint
    if (current > triggerPoint) {
      if (current > lastScroll && headerVisible) {
        header.classList.add("seopress-composer-hidden");
        headerVisible = false;
      } else if (current < lastScroll && !headerVisible) {
        header.classList.remove("seopress-composer-hidden");
        headerVisible = true;
      }
    } else {
      // Ensure header is visible again when user scrolls back to top
      if (!headerVisible) {
        header.classList.remove("seopress-composer-hidden");
        headerVisible = true;
      }
    }

    lastScroll = current;
    ticking = false;
  };

  window.addEventListener("scroll", () => {
    if (!ticking) {
      requestAnimationFrame(onScroll);
      ticking = true;
    }
  }, { passive: true });
}
