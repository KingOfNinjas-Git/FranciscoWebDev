// Projects page specific functionality

// Initialize AOS and other libraries
function initProjectsPage() {
    // Initialize AOS
    if (typeof AOS !== 'undefined') {
        AOS.init({ once: true, duration: 700, easing: 'ease-out-cubic' });
    }

    // VanillaTilt on .tilt elements
    if (typeof VanillaTilt !== 'undefined') {
        VanillaTilt.init(document.querySelectorAll('.tilt'));
    }
}

// Lightbox functionality
function openLightbox(src, title) {
    const lb = document.getElementById('lightbox');
    const img = document.getElementById('lb-img');
    const t = document.getElementById('lb-title');
    img.src = src;
    img.alt = title || '';
    t.textContent = title || '';
    lb.classList.remove('hidden');
    lb.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    const lb = document.getElementById('lightbox');
    lb.classList.add('hidden');
    lb.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

// Intersection Observer for project cards (fallback for AOS)
function initProjectCardsObserver() {
    const cards = document.querySelectorAll('.project-card');
    if (!('IntersectionObserver' in window) || !cards.length) return;

    const io = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) e.target.classList.add('animate-fade-in-up');
        });
    }, { threshold: 0.12 });

    cards.forEach(c => io.observe(c));
}

// Initialize projects page functionality
document.addEventListener('DOMContentLoaded', function() {
    initProjectsPage();
    initProjectCardsObserver();

    // Lightbox keyboard events
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeLightbox();
    });
});

// Make functions globally available
window.openLightbox = openLightbox;
window.closeLightbox = closeLightbox;