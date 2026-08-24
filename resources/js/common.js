// Common functionality shared across pages

function initScrollToTop() {
    const scrollToTopBtn = document.getElementById('scroll-to-top');
    if (!scrollToTopBtn) return;

    const updateVisibility = () => {
        if (window.pageYOffset > 300) {
            scrollToTopBtn.classList.remove('hidden');
            scrollToTopBtn.classList.add('flex');
            scrollToTopBtn.classList.remove('opacity-0', 'translate-y-4');
            scrollToTopBtn.classList.add('opacity-100', 'translate-y-0');
        } else {
            scrollToTopBtn.classList.add('hidden');
            scrollToTopBtn.classList.remove('flex');
            scrollToTopBtn.classList.add('opacity-0', 'translate-y-4');
            scrollToTopBtn.classList.remove('opacity-100', 'translate-y-0');
        }
    };

    window.addEventListener('scroll', updateVisibility, { passive: true });
    updateVisibility();

    scrollToTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

function initFadeInAnimations() {
    document.querySelectorAll('.animate-fade-in').forEach((el) => {
        requestAnimationFrame(() => {
            el.style.opacity = 1;
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    initScrollToTop();
    initFadeInAnimations();
});