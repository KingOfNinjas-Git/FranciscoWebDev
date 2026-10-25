// Keeps a copy of what is typed in long admin forms (posts, projects, lead notes) in this browser, so a closed tab,
// a crash or an expired login does not lose it. On the next visit to the same page the copy is offered back.
// Nothing leaves the browser; the copy is removed when the form is saved or the offer is dismissed.

const PREFIX = 'autosave:' + location.pathname + ':';

const read = (key) => { try { return JSON.parse(localStorage.getItem(key)); } catch (e) { return null; } };
const write = (key, value) => { try { localStorage.setItem(key, JSON.stringify(value)); } catch (e) { /* storage full or blocked */ } };
const drop = (key) => { try { localStorage.removeItem(key); } catch (e) { /* nothing to do */ } };

document.querySelectorAll('form[data-autosave]').forEach((form) => {
    const fields = Array.from(form.querySelectorAll('textarea[name], input[type="text"][name]'))
        .filter((field) => !field.readOnly && !field.disabled);
    const keys = new Map(fields.map((field) => [field, PREFIX + field.getAttribute('name')]));
    const keyOf = (field) => keys.get(field);

    // Offer back what was typed earlier but never saved
    const found = fields.map((field) => ({ field, saved: read(keyOf(field)) }))
        .filter(({ field, saved }) => saved && typeof saved.v === 'string' && saved.v !== field.value && saved.v.trim() !== '');

    if (found.length) {
        const when = new Date(Math.max(...found.map(({ saved }) => saved.t))).toLocaleString();
        const banner = document.createElement('div');
        banner.setAttribute('role', 'status');
        banner.className = 'col-span-full mb-6 flex flex-wrap items-center gap-3 rounded-2xl border border-amber-400/40 bg-amber-400/10 px-4 py-3 text-sm text-amber-200';
        banner.innerHTML = '<span class="flex-1"></span>';
        banner.firstChild.textContent = `Unsaved text from ${when} was found in ${found.length} field${found.length > 1 ? 's' : ''} of this page.`;

        const button = (label, onClick) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'btn btn-ghost px-4 py-1.5 text-xs font-semibold';
            b.textContent = label;
            b.addEventListener('click', onClick);
            banner.append(b);
        };
        button('Restore it', () => {
            found.forEach(({ field, saved }) => {
                field.value = saved.v;
                field.dispatchEvent(new Event('input', { bubbles: true }));
            });
            banner.remove();
        });
        button('Discard', () => {
            found.forEach(({ field }) => drop(keyOf(field)));
            banner.remove();
        });
        form.prepend(banner);
    }

    // Save while typing (a short pause after the last key)
    fields.forEach((field) => {
        let timer;
        field.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                if (field.value === field.defaultValue || field.value.trim() === '') drop(keyOf(field));
                else write(keyOf(field), { v: field.value, t: Date.now() });
            }, 600);
        });
    });

    // A successful save makes the copies pointless
    form.addEventListener('submit', () => fields.forEach((field) => drop(keyOf(field))));
});
