import axios from 'axios';
import { wrapPayload } from './safe-encode';

// Project form: shows the case study text as it will look on the public page (same safe Markdown renderer as blog posts).
document.querySelectorAll('[data-notes-preview]').forEach((wrap) => {
    const field = document.getElementById(wrap.dataset.for);
    const button = wrap.querySelector('[data-toggle]');
    const box = wrap.querySelector('[data-box]');
    if (!field || !button || !box) return;

    button.addEventListener('click', async () => {
        if (!box.classList.contains('hidden')) {
            box.classList.add('hidden');
            button.textContent = 'Preview';
            return;
        }

        button.disabled = true;
        try {
            const res = await axios.post(wrap.dataset.url, wrapPayload({ body: field.value }, ['body']));
            box.innerHTML = res.data.html || '<p class="text-slate-400">Nothing to preview yet.</p>';
            box.classList.remove('hidden');
            button.textContent = 'Hide preview';
        } catch (error) {
            window.alert('Could not render the preview.');
        } finally {
            button.disabled = false;
        }
    });
});
