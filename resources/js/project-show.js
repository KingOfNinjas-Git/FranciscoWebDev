// Project show page specific functionality

// Lightbox functionality for project images
let currentLightboxIndex = 0;
let lightboxImages = [];

function openLightbox(index) {
    currentLightboxIndex = index;
    updateLightboxContent();

    const lightbox = document.getElementById('lightbox');
    const img = document.getElementById('lightbox-img');
    const caption = document.getElementById('lightbox-caption');

    lightbox.classList.remove('hidden');
    // Force reflow to enable transition
    void lightbox.offsetWidth;

    lightbox.classList.remove('opacity-0');

    img.classList.remove('scale-95', 'opacity-0');
    img.classList.add('scale-100', 'opacity-100');

    if (caption) {
        caption.classList.remove('opacity-0', 'translate-y-4');
        caption.classList.add('opacity-100', 'translate-y-0');
    }

    document.body.classList.add('overflow-hidden');
}

function updateLightboxContent() {
    const img = document.getElementById('lightbox-img');
    const caption = document.getElementById('lightbox-caption');
    const data = lightboxImages[currentLightboxIndex];

    if (!data) return;

    img.src = data.src;
    img.alt = data.alt;
    if (caption) caption.textContent = data.alt;
}

function changeLightboxImage(direction) {
    currentLightboxIndex = (currentLightboxIndex + direction + lightboxImages.length) % lightboxImages.length;
    const img = document.getElementById('lightbox-img');
    img.classList.add('opacity-0', 'scale-95');
    setTimeout(() => {
        updateLightboxContent();
        img.classList.remove('opacity-0', 'scale-95');
    }, 200);
}

function closeLightbox() {
    const lightbox = document.getElementById('lightbox');
    const img = document.getElementById('lightbox-img');
    const caption = document.getElementById('lightbox-caption');

    lightbox.classList.add('opacity-0');

    img.classList.remove('scale-100', 'opacity-100');
    img.classList.add('scale-95', 'opacity-0');

    if (caption) {
        caption.classList.remove('opacity-100', 'translate-y-0');
        caption.classList.add('opacity-0', 'translate-y-4');
    }

    document.body.classList.remove('overflow-hidden');

    setTimeout(() => {
        lightbox.classList.add('hidden');
        img.src = '';
    }, 300);
}

// Initialize project show page functionality
document.addEventListener('DOMContentLoaded', function() {
    // Get images data from global variable
    if (typeof window.lightboxImages !== 'undefined') {
        lightboxImages = window.lightboxImages;
    }

    // Initialize AOS
    if (typeof AOS !== 'undefined') {
        AOS.init({ once: true, duration: 700, easing: 'ease-out-cubic' });
    }

    // Initialize Swiper
    if (typeof Swiper !== 'undefined') {
        const swiper = new Swiper('.mySwiper', {
            loop: true,
            lazy: true,
            effect: 'slide',
            grabCursor: true,
            spaceBetween: 30,
            slidesPerView: 1,
            autoHeight: true,
            keyboard: { enabled: true },
            autoplay: {
                delay: 3500,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            navigation: { nextEl: '.custom-next', prevEl: '.custom-prev' },
            pagination: { el: '.swiper-pagination', clickable: true },
        });
    }

    // Lightbox keyboard events
    document.addEventListener('keydown', function(e) {
        const lightbox = document.getElementById('lightbox');
        if (lightbox && lightbox.classList.contains('hidden')) return;

        if (e.key === 'ArrowLeft') changeLightboxImage(-1);
        if (e.key === 'ArrowRight') changeLightboxImage(1);
        if (e.key === 'Escape') closeLightbox();
    });
});

// Make functions globally available
window.openLightbox = openLightbox;
window.closeLightbox = closeLightbox;
window.changeLightboxImage = changeLightboxImage;