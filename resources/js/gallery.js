// Swipeable screenshot gallery for project pages: snap-scroll carousel with a blurred backdrop,
// counter, thumbnails and keyboard arrows. Pure DOM, so it can be tested without a browser.

function el(tag, className, text) {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
}

const chevron = (path) =>
    '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="' + path + '"/></svg>';

/**
 * @param {{url: string, thumb?: string, alt?: string, caption?: string|null}[]} images
 * @param {string} title   used as the carousel's accessible name and as fallback alt text
 * @param {{prev?: string, next?: string}} labels   accessible names of the arrow buttons
 * @returns {HTMLElement}
 */
export function buildGallery(images, title, labels = {}) {
    const n = images.length;
    const wrap = el('div', 'pm-gallery-wrap');
    const gallery = el('div', 'pm-gallery');
    const track = el('div', 'pm-track');
    track.tabIndex = 0;
    track.setAttribute('role', 'region');
    track.setAttribute('aria-roledescription', 'carousel');
    track.setAttribute('aria-label', title);

    images.forEach((image, i) => {
        const slide = el('div', 'pm-slide');
        slide.setAttribute('role', 'group');
        slide.setAttribute('aria-label', (i + 1) + ' / ' + n);

        const bg = el('img', 'pm-slide-bg');
        bg.src = image.thumb || image.url; // the backdrop is blurred, so the small copy is enough
        bg.alt = '';
        bg.setAttribute('aria-hidden', 'true');

        const img = el('img', 'pm-slide-img');
        img.src = image.url;
        img.alt = image.alt || title;
        img.draggable = false;
        if (i > 0) img.loading = 'lazy';

        slide.append(bg, img);
        track.append(slide);
    });
    gallery.append(track);

    const caption = el('p', 'pm-caption');
    const setCaption = (i) => {
        caption.textContent = images[i].caption || '';
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
    let idx = 0;

    const thumbBtns = images.map((image, i) => {
        const b = el('button', 'pm-thumb');
        b.type = 'button';
        b.setAttribute('aria-label', (i + 1) + ' / ' + n);
        const t = el('img');
        t.src = image.thumb || image.url;
        t.alt = '';
        b.append(t);
        b.addEventListener('click', () => goTo(i));
        thumbs.append(b);
        return b;
    });

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

    // Swiping or scrolling the track updates the counter and thumbnails
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
        b.setAttribute('aria-label', label || '');
        b.innerHTML = chevron(path);
        b.addEventListener('click', () => goTo(idx + delta));
        return b;
    };

    gallery.append(arrow('pm-prev', labels.prev, -1, 'M15 6l-6 6 6 6'), arrow('pm-next', labels.next, 1, 'M9 6l6 6-6 6'), counter);
    wrap.append(gallery, caption, thumbs);

    // Left / right arrow keys while the gallery has focus
    wrap.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') { e.preventDefault(); goTo(idx - 1); }
        if (e.key === 'ArrowRight') { e.preventDefault(); goTo(idx + 1); }
    });

    sync();

    return wrap;
}
