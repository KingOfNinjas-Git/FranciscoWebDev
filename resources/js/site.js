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
    let galleryGo = null; // moves the open gallery by +1 / -1 (used by the arrow keys)

    const chevron = (path) => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="' + path + '"/></svg>';

    // Swipeable snap-scroll carousel with a blurred backdrop, counter and thumbnails
    const buildGallery = (p) => {
        const n = p.images.length;
        const wrap = el('div', 'pm-gallery-wrap');
        const gallery = el('div', 'pm-gallery');
        const track = el('div', 'pm-track');
        track.tabIndex = 0;
        track.setAttribute('role', 'region');
        track.setAttribute('aria-roledescription', 'carousel');
        track.setAttribute('aria-label', p.title);

        p.images.forEach((image, i) => {
            const slide = el('div', 'pm-slide');
            slide.setAttribute('role', 'group');
            slide.setAttribute('aria-label', (i + 1) + ' / ' + n);

            const bg = el('img', 'pm-slide-bg');
            bg.src = image.url;
            bg.alt = '';
            bg.setAttribute('aria-hidden', 'true');

            const img = el('img', 'pm-slide-img');
            img.src = image.url;
            img.alt = image.alt || p.title;
            img.draggable = false;
            if (i > 0) img.loading = 'lazy';

            slide.append(bg, img);
            track.append(slide);
        });
        gallery.append(track);

        const caption = el('p', 'pm-caption');
        const setCaption = (i) => {
            caption.textContent = p.images[i].caption || '';
            caption.hidden = !caption.textContent;
        };
        setCaption(0);

        if (n === 1) {
            wrap.append(gallery, caption);
            return wrap;
        }

        const counter = el('div', 'pm-counter');
        counter.setAttribute('aria-live', 'polite');
        const thumbs = el('div', 'pm-thumbs');
        const thumbBtns = p.images.map((image, i) => {
            const b = el('button', 'pm-thumb');
            b.type = 'button';
            b.setAttribute('aria-label', (i + 1) + ' / ' + n);
            const t = el('img');
            t.src = image.url;
            t.alt = '';
            b.append(t);
            b.addEventListener('click', () => goTo(i));
            thumbs.append(b);
            return b;
        });

        let idx = 0;
        const sync = () => {
            counter.textContent = (idx + 1) + ' / ' + n;
            setCaption(idx);
            thumbBtns.forEach((b, i) => {
                b.classList.toggle('is-active', i === idx);
                if (i === idx) b.setAttribute('aria-current', 'true');
                else b.removeAttribute('aria-current');
            });
            const active = thumbBtns[idx];
            thumbs.scrollTo({ left: active.offsetLeft - (thumbs.clientWidth - active.clientWidth) / 2, behavior: 'smooth' });
        };
        const goTo = (i) => {
            idx = (i + n) % n;
            track.scrollTo({ left: idx * track.clientWidth, behavior: 'smooth' });
            sync();
        };

        let ticking = false;
        track.addEventListener('scroll', () => {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(() => {
                ticking = false;
                const i = Math.round(track.scrollLeft / track.clientWidth);
                if (i !== idx && i >= 0 && i < n) { idx = i; sync(); }
            });
        }, { passive: true });

        const arrow = (cls, label, delta, path) => {
            const b = el('button', 'pm-nav ' + cls);
            b.type = 'button';
            b.setAttribute('aria-label', label);
            b.innerHTML = chevron(path);
            b.addEventListener('click', () => goTo(idx + delta));
            return b;
        };

        galleryGo = (delta) => goTo(idx + delta);
        gallery.append(arrow('pm-prev', L.lPrev, -1, 'M15 6l-6 6 6 6'), arrow('pm-next', L.lNext, 1, 'M9 6l6 6-6 6'), counter);
        wrap.append(gallery, caption, thumbs);
        sync();
        return wrap;
    };

    const render = (p) => {
        $('#pm-title').textContent = p.title;
        $('#pm-year').textContent = p.year || '';
        body.replaceChildren();

        galleryGo = null;
        if (p.images.length) body.append(buildGallery(p));

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

    const hashFor = (slug) => location.pathname + location.search + '#projects/' + encodeURIComponent(slug);

    const openProject = (i, trigger) => {
        const p = projects[i];
        if (!p) return;
        opener = trigger || null;
        render(p);
        if (!modal.open) modal.showModal();
        document.body.classList.add('overflow-hidden');
        history.replaceState(null, '', hashFor(p.slug)); // makes the project linkable: /#projects/<slug>
    };

    const openFromHash = () => {
        const m = location.hash.match(/^#projects\/(.+)$/);
        if (!m) return;
        const slug = decodeURIComponent(m[1]);
        const i = projects.findIndex((p) => p.slug === slug);
        if (i < 0) return;
        $('#projects').scrollIntoView();
        openProject(i, null);
    };

    $$('[data-project]').forEach((tile) => {
        tile.addEventListener('click', () => openProject(Number(tile.dataset.project), tile));
    });
    window.addEventListener('hashchange', openFromHash);
    openFromHash();

    $('#pm-close').addEventListener('click', () => modal.close());
    modal.addEventListener('keydown', (e) => {
        if (!galleryGo || e.target.closest('input, textarea, select')) return;
        if (e.key === 'ArrowLeft') { e.preventDefault(); galleryGo(-1); }
        if (e.key === 'ArrowRight') { e.preventDefault(); galleryGo(1); }
    });
    // Click on the backdrop (outside the dialog box) closes it
    modal.addEventListener('click', (e) => { if (e.target === modal) modal.close(); });
    modal.addEventListener('close', () => {
        document.body.classList.remove('overflow-hidden');
        if (location.hash.startsWith('#projects/')) {
            history.replaceState(null, '', location.pathname + location.search + '#projects');
        }
        if (opener) opener.focus();
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
    initMobileMenu();
    initScrollSpy();
    initReveal();
    initScrollToTop();
    initLanguageScroll();
    initProjects();
});
