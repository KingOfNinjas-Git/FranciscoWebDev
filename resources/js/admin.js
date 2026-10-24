import './bootstrap';

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
