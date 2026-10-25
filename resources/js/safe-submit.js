import { toB64, encodedFieldName } from './safe-encode';

// Admin forms: fields marked data-safe are sent encoded (see safe-encode.js for why).
document.querySelectorAll('form').forEach((form) => {
    if (!form.querySelector('[data-safe]')) return;

    form.addEventListener('submit', () => {
        const swapped = [];

        form.querySelectorAll('[data-safe][name]').forEach((field) => {
            if (field.disabled || field.value === '') return;

            const name = field.getAttribute('name');
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = encodedFieldName(name);
            hidden.value = toB64(field.value);
            form.appendChild(hidden);

            // The browser builds the form data right after this handler: send the encoded copy instead of the plain field
            field.removeAttribute('name');
            swapped.push({ field, name, hidden });
        });

        // Put everything back afterwards, so the Back button never shows a half-converted form
        setTimeout(() => swapped.forEach(({ field, name, hidden }) => {
            field.setAttribute('name', name);
            hidden.remove();
        }), 0);
    });
});
