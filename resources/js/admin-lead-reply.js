// Lead page: picking a reply template fills in the subject and message (still editable before sending).
document.querySelectorAll('[data-reply-form]').forEach((form) => {
    const templates = JSON.parse(form.dataset.templates || '{}');
    const select = form.querySelector('[data-template-select]');
    const subject = form.querySelector('[name="subject"]');
    const body = form.querySelector('[name="body"]');
    if (!select || !subject || !body) return;

    select.addEventListener('change', () => {
        const template = templates[select.value];
        if (!template) return;
        if (body.value.trim() !== '' && !window.confirm('Replace what you have written with this template?')) {
            select.value = '';
            return;
        }
        subject.value = template.subject;
        body.value = template.body;
        body.dispatchEvent(new Event('input', { bubbles: true }));
        body.focus();
    });
});
