import { wrapPayload } from './safe-encode';
import { runJob, errorMessage } from './ai-job';

// Project form: "Translate to Portuguese & Spanish with AI" (title, short description and details).

const button = document.getElementById('project-translate');

if (button) {
    const status = document.getElementById('project-translate-status');
    const $ = (id) => document.getElementById(id);
    const val = (id) => ($(id) ? $(id).value : '');

    const say = (text, kind = 'info') => {
        status.textContent = text;
        status.className = 'text-sm ' + (kind === 'error' ? 'text-rose-300' : kind === 'ok' ? 'text-emerald-300' : 'text-slate-400');
    };

    button.addEventListener('click', async () => {
        if (!val('p-en-title').trim() || !(val('p-en-description').trim() || val('p-en-notes').trim())) {
            say('Fill in the English title and at least the short description or the details first.', 'error');
            return;
        }

        const hasTranslations = ['pt', 'es'].some((c) => val('p-' + c + '-description').trim() || val('p-' + c + '-notes').trim());
        if (hasTranslations && !window.confirm('This overwrites the Portuguese and Spanish text. Continue?')) return;

        button.disabled = true;
        try {
            const data = await runJob({
                axios: window.axios,
                startUrl: button.dataset.translateUrl,
                statusUrl: button.dataset.statusUrl,
                // The project's short description is sent as "excerpt" and its details as "body"
                payload: wrapPayload({
                    kind: 'project',
                    title: val('p-en-title'),
                    excerpt: val('p-en-description'),
                    body: val('p-en-notes'),
                    locales: ['pt', 'es'],
                }, ['title', 'excerpt', 'body']),
                onTick: (seconds) => say('Translating (' + seconds + 's)'),
            });

            ['pt', 'es'].forEach((code) => {
                const t = data.result[code];
                if (!t) return;
                $('p-' + code + '-title').value = t.title;
                $('p-' + code + '-description').value = t.excerpt;
                $('p-' + code + '-notes').value = t.body;
            });
            say('Translations added to the PT and ES tabs. Review them before saving.'
                + (data.input_tokens ? ' Used ' + data.input_tokens.toLocaleString() + ' input and ' + data.output_tokens.toLocaleString() + ' output tokens.' : ''), 'ok');
        } catch (e) {
            say(errorMessage(e), 'error');
        } finally {
            button.disabled = false;
        }
    });
}
