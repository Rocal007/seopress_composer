// Menu toggle and scroll behavior
export default function initMenu() {
  // Mobile Menu Toggle (Side Drawer)
  const toggle = document.getElementById('mobile-menu-toggle');
  const closeBtn = document.getElementById('mobile-menu-close');
  const menu = document.getElementById('mobile-menu');
  const backdrop = document.getElementById('mobile-menu-backdrop');
  const drawer = document.getElementById('mobile-menu-drawer');

  function openMenu() {
    if (!menu || !drawer || !backdrop) return;
    menu.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    // Trigger reflow for transition
    void drawer.offsetWidth;
    // Small timeout to allow display:block to apply before opacity transition
    setTimeout(() => {
      backdrop.classList.remove('opacity-0');
      backdrop.classList.add('opacity-100');
      drawer.classList.remove('-translate-x-full');
      drawer.classList.add('translate-x-0');
    }, 10);
  }

  function closeMenu() {
    if (!menu || !drawer || !backdrop) return;
    backdrop.classList.remove('opacity-100');
    backdrop.classList.add('opacity-0');
    drawer.classList.remove('translate-x-0');
    drawer.classList.add('-translate-x-full');

    setTimeout(() => {
      menu.classList.add('hidden');
      document.body.style.overflow = '';
    }, 300);
  }

  if (toggle) toggle.addEventListener('click', openMenu);
  if (closeBtn) closeBtn.addEventListener('click', closeMenu);
  if (backdrop) backdrop.addEventListener('click', closeMenu);

  // Scroll Behavior
  const navbar = document.querySelector('.navbar');
  if (navbar) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 20) {
        navbar.classList.add('is-scrolled', 'shadow-md', 'py-1');
        navbar.classList.remove('shadow-sm', 'py-2');
      } else {
        navbar.classList.remove('is-scrolled', 'shadow-md', 'py-1');
        navbar.classList.add('shadow-sm', 'py-2');
      }
    });

    // Initial check
    if (window.scrollY > 20) {
      navbar.classList.add('is-scrolled', 'shadow-md', 'py-1');
      navbar.classList.remove('shadow-sm', 'py-2');
    }
  }
}