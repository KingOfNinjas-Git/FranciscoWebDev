import './bootstrap';

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
    update();
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

/* Project details modal */
function initProjects() {
    const dataEl = $('#projects-data');
    const modal = $('#project-modal');
    if (!dataEl || !modal) return;

    const projects = JSON.parse(dataEl.textContent);
    const body = $('#pm-body');
    const L = modal.dataset;
    let opener = null;

    const render = (p) => {
        $('#pm-title').textContent = p.title;
        $('#pm-year').textContent = p.year || '';
        body.replaceChildren();

        if (p.images.length) {
            let idx = 0;
            const gallery = el('div', 'pm-gallery');
            const img = el('img');
            const show = () => { img.src = p.images[idx].url; img.alt = p.images[idx].alt || p.title; };
            show();
            gallery.append(img);

            if (p.images.length > 1) {
                const mk = (cls, label, delta, path) => {
                    const b = el('button', 'pm-nav ' + cls);
                    b.type = 'button';
                    b.setAttribute('aria-label', label);
                    b.innerHTML = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="' + path + '"/></svg>';
                    b.addEventListener('click', () => {
                        idx = (idx + delta + p.images.length) % p.images.length;
                        show();
                    });
                    return b;
                };
                gallery.append(mk('pm-prev', L.lPrev, -1, 'M15 6l-6 6 6 6'), mk('pm-next', L.lNext, 1, 'M9 6l6 6-6 6'));
            }
            body.append(gallery);
        }

        if (p.description) body.append(el('p', 'text-base leading-7 text-slate-300', p.description));

        if (p.tags.length) {
            const wrap = el('div');
            wrap.append(el('h4', 'mb-2 font-mono text-xs uppercase tracking-[0.2rem] text-sky-300', L.lStack));
            const row = el('div', 'flex flex-wrap gap-2');
            p.tags.forEach((t) => row.append(el('span', 'chip', t)));
            wrap.append(row);
            body.append(wrap);
        }

        if (p.notes) {
            const box = el('div', 'rounded-2xl border border-white/10 bg-white/5 p-4');
            box.append(el('h4', 'mb-2 font-semibold text-white', L.lDetails));
            box.append(el('div', 'whitespace-pre-wrap text-sm leading-6 text-slate-300', p.notes));
            body.append(box);
        }

        if (p.live_url || p.repo_url) {
            const row = el('div', 'flex flex-wrap gap-3 pt-1');
            const link = (url, label, cls) => {
                const a = el('a', 'btn px-5 py-2.5 text-sm font-semibold ' + cls, label);
                a.href = url;
                a.target = '_blank';
                a.rel = 'noopener noreferrer';
                return a;
            };
            if (p.live_url) row.append(link(p.live_url, L.lLive, 'btn-primary'));
            if (p.repo_url) row.append(link(p.repo_url, L.lRepo, 'btn-ghost'));
            body.append(row);
        }
    };

    $$('[data-project]').forEach((tile) => {
        tile.addEventListener('click', () => {
            opener = tile;
            render(projects[Number(tile.dataset.project)]);
            modal.showModal();
            document.body.classList.add('overflow-hidden');
        });
    });

    $('#pm-close').addEventListener('click', () => modal.close());
    // Click on the backdrop (outside the dialog box) closes it
    modal.addEventListener('click', (e) => { if (e.target === modal) modal.close(); });
    modal.addEventListener('close', () => {
        document.body.classList.remove('overflow-hidden');
        if (opener) opener.focus();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initMobileMenu();
    initScrollSpy();
    initReveal();
    initScrollToTop();
    initLanguageScroll();
    initProjects();
});
