import axios from 'axios';

// "Suggest alt text with AI" on a project picture: asks the AI to look at the image and fills the three alt-text fields.
document.querySelectorAll('[data-suggest-alt]').forEach((button) => {
    const box = button.closest('[data-alt-box]');
    const status = box.querySelector('[data-suggest-status]');
    const fields = {
        en: box.querySelector('input[name="alt"]'),
        pt: box.querySelector('input[name="translations[pt][alt]"]'),
        es: box.querySelector('input[name="translations[es][alt]"]'),
    };

    button.addEventListener('click', async () => {
        const hasText = Object.values(fields).some((field) => field && field.value.trim() !== '');
        if (hasText && !window.confirm('Replace the alt text that is already there?')) return;

        button.disabled = true;
        status.textContent = 'Looking at the picture...';
        status.className = 'text-xs text-slate-400';

        try {
            const { data } = await axios.post(button.dataset.url, { image_id: Number(button.dataset.suggestAlt) });
            Object.entries(fields).forEach(([code, field]) => {
                if (field && data.alt[code]) field.value = data.alt[code];
            });
            status.textContent = 'Suggested. Check it, then press "Save text".';
            status.className = 'text-xs text-emerald-300';
        } catch (error) {
            status.textContent = error.response?.data?.message || 'The AI could not suggest text right now.';
            status.className = 'text-xs text-rose-300';
        } finally {
            button.disabled = false;
        }
    });
});
