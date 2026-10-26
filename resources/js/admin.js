import './bootstrap';
import './safe-submit';
import './admin-posts';
import './admin-project-translate';
import './admin-image-alt';
import './admin-notes-preview';
import './admin-autosave';
import './admin-lead-reply';
import qrcode from 'qrcode-generator';

// Language tabs in the project form
document.querySelectorAll('[role="tablist"]').forEach((list) => {
    const tabs = Array.from(list.querySelectorAll('[data-tab]'));
    const form = list.closest('form');

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            tabs.forEach((t) => {
                const active = t === tab;
                t.setAttribute('aria-selected', String(active));
                t.classList.toggle('is-active', active);
                t.classList.toggle('text-slate-400', !active);
            });
            form.querySelectorAll('[data-panel]').forEach((panel) => {
                panel.classList.toggle('hidden', panel.dataset.panel !== tab.dataset.tab);
            });
        });
    });
});

// Confirm before destructive actions
document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (e) => {
        if (!window.confirm(form.dataset.confirm)) e.preventDefault();
    });
});

// Image reordering: drag and drop, or the arrow buttons. Saves the order as soon as it changes.
const list = document.getElementById('image-list');
if (list) {
    const cards = () => Array.from(list.querySelectorAll('.image-card'));
    let dragging = null;

    const refreshCover = () => {
        cards().forEach((card, i) => {
            card.querySelector('.cover-badge')?.classList.toggle('hidden', i !== 0);
            card.querySelector('.make-cover')?.classList.toggle('hidden', i === 0);
        });
    };

    const save = () => {
        window.axios
            .post(list.dataset.reorderUrl, { order: cards().map((c) => c.dataset.id) })
            .catch(() => window.alert('Could not save the new order. Reload the page and try again.'));
    };

    // Only the picture itself starts a drag, so text inside the "Edit text" forms stays selectable
    cards().forEach((card) => {
        const handle = card.querySelector('.relative');
        handle.addEventListener('mousedown', () => { card.draggable = true; });
        card.addEventListener('dragstart', (e) => {
            dragging = card;
            e.dataTransfer.effectAllowed = 'move';
            card.classList.add('opacity-50');
        });
        card.addEventListener('dragend', () => {
            card.draggable = false;
            card.classList.remove('opacity-50');
            if (dragging) { dragging = null; refreshCover(); save(); }
        });
    });

    list.addEventListener('dragover', (e) => {
        if (!dragging) return;
        e.preventDefault();
        const target = e.target.closest('.image-card');
        if (!target || target === dragging) return;
        const rect = target.getBoundingClientRect();
        const before = (e.clientX - rect.left) < rect.width / 2;
        list.insertBefore(dragging, before ? target : target.nextSibling);
    });

    list.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-move]');
        if (!btn) return;
        const card = btn.closest('.image-card');
        const dir = Number(btn.dataset.move);
        const sibling = dir < 0 ? card.previousElementSibling : card.nextElementSibling;
        if (!sibling) return;
        list.insertBefore(card, dir < 0 ? sibling : sibling.nextSibling);
        refreshCover();
        save();
        btn.focus();
    });
}

// Two-factor setup: draw the QR code in the browser (the secret never leaves this page)
const qrBox = document.getElementById('totp-qr');
if (qrBox) {
    const qr = qrcode(0, 'M');
    qr.addData(qrBox.dataset.uri);
    qr.make();
    qrBox.innerHTML = qr.createSvgTag({ cellSize: 5, margin: 0, scalable: true });
    const svg = qrBox.querySelector('svg');
    if (svg) { svg.setAttribute('width', '208'); svg.setAttribute('height', '208'); svg.setAttribute('aria-hidden', 'true'); }
}
