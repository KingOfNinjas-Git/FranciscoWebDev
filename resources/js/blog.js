/* Blog post page: reading progress bar, table of contents that follows you, copy buttons on code */
const $ = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

function initReadingProgress() {
    const bar = $('#read-progress-bar');
    const article = $('[data-article] .prose-blog');
    if (!bar || !article) return;

    let ticking = false;
    const update = () => {
        ticking = false;
        const rect = article.getBoundingClientRect();
        const total = rect.height - window.innerHeight * 0.6;
        const done = total > 0 ? Math.min(1, Math.max(0, -rect.top / total)) : 0;
        bar.style.transform = `scaleX(${done.toFixed(3)})`;
    };
    const onScroll = () => {
        if (!ticking) { ticking = true; requestAnimationFrame(update); }
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);
    update();
}

function initToc() {
    const toc = $('[data-toc]');
    if (!toc) return;

    // On a wide screen the contents list is always open (it sits beside the article)
    const wide = window.matchMedia('(min-width: 1024px)');
    const sync = () => { if (wide.matches) toc.open = true; };
    sync();
    wide.addEventListener('change', sync);

    const links = $$('[data-toc-link]', toc);
    const byId = new Map(links.map((a) => [decodeURIComponent(a.getAttribute('href').slice(1)), a]));
    const headings = $$('[data-article] .prose-blog h2[id], [data-article] .prose-blog h3[id]');
    if (!headings.length || !('IntersectionObserver' in window)) return;

    const setActive = (id) => {
        links.forEach((a) => {
            const on = a === byId.get(id);
            a.classList.toggle('is-active', on);
            if (on) a.setAttribute('aria-current', 'location'); else a.removeAttribute('aria-current');
        });
    };

    // The active section is the first heading inside the band near the top of the window,
    // or else the last heading that has already scrolled past
    const seen = new Set();
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((e) => { if (e.isIntersecting) seen.add(e.target.id); else seen.delete(e.target.id); });
        const current = headings.find((h) => seen.has(h.id));
        if (current) { setActive(current.id); return; }
        const above = headings.filter((h) => h.getBoundingClientRect().top < 0);
        if (above.length) setActive(above[above.length - 1].id);
    }, { rootMargin: '-90px 0px -70% 0px' });
    headings.forEach((h) => observer.observe(h));

    // Tapping a link on a phone closes the list again
    toc.addEventListener('click', (e) => {
        if (e.target.closest('a') && !wide.matches) toc.open = false;
    });
}

function initCodeCopy() {
    const article = $('[data-article]');
    if (!article) return;

    // Code and tables that scroll sideways on a small screen must be reachable with the keyboard
    $$('.prose-blog pre, .prose-blog table', article).forEach((node) => { node.tabIndex = 0; });

    if (!navigator.clipboard) return;
    const { lCopy = 'Copy', lCopied = 'Copied!', lCopyLabel = 'Copy this code' } = article.dataset;

    $$('.prose-blog pre', article).forEach((pre) => {
        const wrap = document.createElement('div');
        wrap.className = 'code-block';
        pre.parentNode.insertBefore(wrap, pre);
        wrap.appendChild(pre);

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'code-copy';
        btn.textContent = lCopy;
        btn.setAttribute('aria-label', lCopyLabel);
        wrap.appendChild(btn);

        btn.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(pre.innerText.replace(/\n$/, ''));
                btn.textContent = lCopied;
                btn.classList.add('is-done');
                setTimeout(() => { btn.textContent = lCopy; btn.classList.remove('is-done'); }, 2000);
            } catch (e) { /* clipboard blocked: the code stays selectable */ }
        });
    });
}

export function initBlogPost() {
    initReadingProgress();
    initToc();
    initCodeCopy();
}
