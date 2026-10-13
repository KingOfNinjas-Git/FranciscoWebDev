// Navbar functionality

// Mobile menu toggle
function initMobileMenu() {
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');

    if (!mobileMenuBtn || !mobileMenu) return;

    const setMenuOpen = (isOpen) => {
        mobileMenuBtn.setAttribute('aria-expanded', String(isOpen));
        mobileMenuBtn.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');
        mobileMenu.setAttribute('aria-hidden', String(!isOpen));
        mobileMenu.inert = !isOpen;

        if (isOpen) {
            mobileMenu.classList.remove('max-h-0', 'opacity-0', 'border-transparent');
            mobileMenu.classList.add('max-h-96', 'opacity-100', 'mt-4', 'border-slate-800');
        } else {
            mobileMenu.classList.add('max-h-0', 'opacity-0', 'border-transparent');
            mobileMenu.classList.remove('max-h-96', 'opacity-100', 'mt-4', 'border-slate-800');
        }
    };

    mobileMenuBtn.addEventListener('click', function() {
        setMenuOpen(mobileMenuBtn.getAttribute('aria-expanded') !== 'true');
    });

    mobileMenu.addEventListener('click', function(event) {
        if (event.target.closest('a')) setMenuOpen(false);
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && mobileMenuBtn.getAttribute('aria-expanded') === 'true') {
            setMenuOpen(false);
            mobileMenuBtn.focus();
        }
    });
}

// Initialize navbar functionality
document.addEventListener('DOMContentLoaded', function() {
    initMobileMenu();
});