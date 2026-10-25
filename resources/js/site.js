import './bootstrap';
import { buildGallery } from './gallery';

const $ = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

function el(tag, className, text) {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
}

/* Mobile menu */
function initMobileMenu() {
    const btn = $('#mobile-menu-btn');
    const menu = $('#mobile-menu');
    if (!btn || !menu) return;

    const setOpen = (open) => {
        btn.setAttribute('aria-expanded', String(open));
        menu.setAttribute('aria-hidden', String(!open));
        menu.inert = !open;
        menu.classList.toggle('is-open', open);
    };
    setOpen(false);

    btn.addEventListener('click', () => setOpen(btn.getAttribute('aria-expanded') !== 'true'));
    menu.addEventListener('click', (e) => { if (e.target.closest('a')) setOpen(false); });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && btn.getAttribute('aria-expanded') === 'true') {
            setOpen(false);
            btn.focus();
        }
    });
}

/* Highlight the nav link of the section currently in view */
function initScrollSpy() {
    const links = $$('.nav-link[data-nav]');
    const sections = $$('.section-anchor');
    if (!('IntersectionObserver' in window) || !links.length) return;

    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            links.forEach((a) => {
                const active = a.getAttribute('href') === '#' + entry.target.id;
                a.classList.toggle('is-active', active);
                if (active) a.setAttribute('aria-current', 'true');
                else a.removeAttribute('aria-current');
            });
        });
    }, { rootMargin: '-45% 0px -50% 0px' });

    sections.forEach((s) => io.observe(s));
}

/* Reveal-on-scroll */
function initReveal() {
    const items = $$('.reveal');
    if (!('IntersectionObserver' in window)) {
        items.forEach((i) => i.classList.add('is-visible'));
        return;
    }
    const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
            if (e.isIntersecting) {
                e.target.classList.add('is-visible');
                io.unobserve(e.target);
            }
        });
    }, { threshold: 0.12 });
    items.forEach((i) => io.observe(i));
}

function initScrollToTop() {
    const btn = $('#scroll-to-top');
    if (!btn) return;
    const update = () => {
        const show = window.scrollY > 500;
        btn.classList.toggle('hidden', !show);
        btn.classList.toggle('flex', show);
    };
    window.addEventListener('scroll', update, { passive: true });
    requestAnimationFrame(update); // reading scroll position straight away forces an early layout
    btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}

/* Language links reload the page; keep the reader where they were */
function initLanguageScroll() {
    $$('[data-lang]').forEach((a) => {
        a.addEventListener('click', () => {
            try { sessionStorage.setItem('restoreScroll', String(window.scrollY)); } catch (e) { /* storage unavailable */ }
        });
    });
    try {
        const y = sessionStorage.getItem('restoreScroll');
        if (y !== null) {
            sessionStorage.removeItem('restoreScroll');
            const html = document.documentElement;
            html.style.scrollBehavior = 'auto';
            window.scrollTo(0, Number(y));
            html.style.scrollBehavior = '';
        }
    } catch (e) { /* storage unavailable */ }
}

/* Old links like /#projects/<slug> now open the project's own page */
function redirectOldProjectLinks() {
    const m = location.hash.match(/^#projects\/(.+)$/);
    if (!m) return;
    const slug = decodeURIComponent(m[1]);
    const card = $$('[data-project-slug]').find((c) => c.dataset.projectSlug === slug);
    if (card) location.replace(card.href);
}

/* Project page: upgrade the first screenshot to the swipeable gallery */
function initProjectPage() {
    const holder = $('#project-gallery');
    const dataEl = $('#project-gallery-data');
    if (!holder || !dataEl) return;

    const images = JSON.parse(dataEl.textContent);
    holder.replaceChildren(buildGallery(images, holder.dataset.title, { prev: holder.dataset.lPrev, next: holder.dataset.lNext }));
}

/* Blog share row: native share sheet where the browser has one, plus "copy link" */
function initShare() {
    const row = $('[data-share]');
    if (!row) return;

    const url = row.dataset.url;
    const title = row.dataset.title;
    const status = $('[data-copy-status]', row);
    const note = (text) => {
        status.textContent = text;
        setTimeout(() => { status.textContent = ''; }, 2500);
    };

    const native = $('[data-share-native]', row);
    if (navigator.share) {
        native.hidden = false;
        native.addEventListener('click', () => navigator.share({ title, url }).catch(() => { /* cancelled */ }));
    }

    $('[data-copy-link]', row).addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(url);
            note(row.dataset.copied || 'Link copied!');
        } catch (e) {
            window.prompt('Copy this link:', url);
        }
    });
}

/* After sending the contact form the page reloads: move focus to the result so screen readers announce it */
function initFormFeedback() {
    const box = $('[data-feedback]');
    if (!box) return;
    requestAnimationFrame(() => box.focus({ preventScroll: false }));
}

document.addEventListener('DOMContentLoaded', () => {
    initFormFeedback();
    initShare();
    initMobileMenu();
    initScrollSpy();
    initReveal();
    initScrollToTop();
    initLanguageScroll();
    redirectOldProjectLinks();
    initProjectPage();
});
