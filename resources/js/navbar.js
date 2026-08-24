// Navbar functionality

// Mobile menu toggle
function initMobileMenu() {
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');

    if (!mobileMenuBtn || !mobileMenu) return;

    mobileMenuBtn.addEventListener('click', function() {
        const isClosed = mobileMenu.classList.contains('max-h-0');

        if (isClosed) {
            mobileMenu.classList.remove('max-h-0', 'opacity-0', 'border-transparent');
            mobileMenu.classList.add('max-h-96', 'opacity-100', 'mt-4', 'border-slate-800');
            mobileMenuBtn.setAttribute('aria-expanded', 'true');
        } else {
            mobileMenu.classList.add('max-h-0', 'opacity-0', 'border-transparent');
            mobileMenu.classList.remove('max-h-96', 'opacity-100', 'mt-4', 'border-slate-800');
            mobileMenuBtn.setAttribute('aria-expanded', 'false');
        }
    });
}

// Initialize navbar functionality
document.addEventListener('DOMContentLoaded', function() {
    initMobileMenu();
});