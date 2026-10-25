import { wrapPayload } from './safe-encode';
import { runJob as runAiJob, errorMessage } from './ai-job';
import { analyse } from './seo-check';

// Blog post form: Markdown preview, SEO checklist and the "Draft with AI" panel.
// The AI calls run on the server (the API key never reaches the browser); this file only starts a job and polls for its result.

const form = document.getElementById('post-form');
const panel = document.getElementById('ai-panel');

const $ = (id) => document.getElementById(id);
const val = (id) => ($(id) ? $(id).value : '');

if (form) {
    // ---- Markdown preview (rendered by the server with the same safe renderer the public page uses)
    form.querySelectorAll('[data-preview]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const code = btn.dataset.preview;
            const box = $('preview-' + code);

            if (!box.classList.contains('hidden')) {
                box.classList.add('hidden');
                btn.textContent = 'Preview';
                return;
            }

            btn.textContent = 'Loading...';
            try {
                const res = await window.axios.post(form.dataset.previewUrl, wrapPayload({ body: val('f-' + code + '-body') }, ['body']));
                box.innerHTML = res.data.html || '<p class="text-slate-400">Nothing to preview yet.</p>';
                box.classList.remove('hidden');
                btn.textContent = 'Hide preview';
            } catch (e) {
                btn.textContent = 'Preview';
                window.alert('Could not render the preview.');
            }
        });
    });

    // ---- SEO checklist (updates while you type)
    const seoList = $('seo-list');
    if (seoList) {
        const labels = { ok: 'OK', warn: 'Check', bad: 'Fix' };
        const marks = { ok: '✓', warn: '!', bad: '✗' };
        const colours = { ok: 'text-emerald-300', warn: 'text-amber-300', bad: 'text-rose-300' };

        const render = () => {
            const items = analyse({
                title: val('f-en-title'),
                excerpt: val('f-en-excerpt'),
                body: val('f-en-body'),
                tags: val('f-tags'),
                origin: window.location.origin,
            });

            seoList.replaceChildren(...items.map((item) => {
                const li = document.createElement('li');
                li.className = 'flex gap-3';

                const mark = document.createElement('span');
                mark.className = 'mt-0.5 w-5 shrink-0 text-center font-bold ' + colours[item.level];
                mark.setAttribute('aria-hidden', 'true');
                mark.textContent = marks[item.level];

                const text = document.createElement('span');
                const strong = document.createElement('strong');
                strong.className = 'text-white';
                strong.textContent = item.label + ' (' + labels[item.level] + '): ';
                text.append(strong, document.createTextNode(item.detail));

                li.append(mark, text);
                return li;
            }));
        };

        let timer;
        ['f-en-title', 'f-en-excerpt', 'f-en-body', 'f-tags'].forEach((id) => {
            $(id)?.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(render, 250); });
        });
        // The AI fills these fields in code (no input event), so it asks for a refresh too
        form.addEventListener('seo:refresh', render);
        render();
    }
}

if (panel) {
    const status = $('ai-status');
    const generate = $('ai-generate');
    const translateBtn = $('ai-translate');
    const notes = $('ai-notes');
    const box = $('ai-context-box');

    const setStatus = (text, kind = 'info') => {
        status.textContent = text;
        status.className = 'text-sm ' + (kind === 'error' ? 'text-rose-300' : kind === 'ok' ? 'text-emerald-300' : 'text-slate-400');
    };

    // Start a server job and wait for it. The server answers at once, then talks to the AI in the background.
    const runJob = (url, payload, waitingText) => runAiJob({
        axios: window.axios,
        startUrl: url,
        statusUrl: panel.dataset.statusUrl,
        payload,
        onTick: (seconds) => setStatus(waitingText + ' (' + seconds + 's)'),
    });

    const usage = (d) => (d.input_tokens ? ' Used ' + d.input_tokens.toLocaleString() + ' input and ' + d.output_tokens.toLocaleString() + ' output tokens.' : '');

    const busy = (on) => {
        generate.disabled = on;
        if (translateBtn) translateBtn.disabled = on;
    };

    // ---- "See what the AI will read"
    const showContext = async (fresh) => {
        setStatus('Reading the live site, projects and posts...');
        try {
            const res = await window.axios.post(panel.dataset.contextUrl, { fresh });
            const s = res.data.stats;
            $('ai-context-stats').textContent =
                s.pages_read + ' live page(s) read from ' + s.site_url
                + (s.pages_failed.length ? ' (could not read: ' + s.pages_failed.join(', ') + ')' : '')
                + ' | ' + s.projects + ' project(s), ' + s.new_projects + ' new since the last post'
                + ' | ' + s.posts + ' existing post(s) | ' + s.characters.toLocaleString() + ' characters';
            $('ai-context-text').textContent = res.data.text;
            box.classList.remove('hidden');
            box.open = true;
            setStatus('');
        } catch (e) {
            setStatus(errorMessage(e), 'error');
        }
    };
    $('ai-context-btn').addEventListener('click', () => showContext(false));
    $('ai-context-refresh').addEventListener('click', () => showContext(true));

    // ---- Generate a draft
    generate.addEventListener('click', async () => {
        const topic = val('ai-topic').trim();
        if (topic.length < 10) {
            setStatus('Describe the topic in at least a sentence.', 'error');
            $('ai-topic').focus();
            return;
        }

        const hasText = val('f-en-title').trim() || val('f-en-body').trim();
        if (hasText && !window.confirm('This replaces the English title, excerpt, body and tags in the form below. Continue?')) return;

        busy(true);
        notes.classList.add('hidden');
        try {
            const data = await runJob(panel.dataset.draftUrl, wrapPayload({
                topic,
                type: val('ai-type'),
                tone: val('ai-tone'),
                length: val('ai-length'),
                project: val('ai-project') || null,
                fresh_only: $('ai-fresh').checked,
            }, ['topic']), 'Reading your site, then writing the draft');

            const r = data.result;
            $('f-en-title').value = r.title;
            $('f-en-excerpt').value = r.excerpt;
            $('f-en-body').value = r.body;
            $('f-tags').value = r.tags.join(', ');
            $('f-ai').checked = true;
            form.dispatchEvent(new Event('seo:refresh'));

            const lines = [];
            if (r.focus_keyword) lines.push('Focus keyword the draft was written for: "' + r.focus_keyword + '".');
            if (r.notes) lines.push(r.notes);
            if (lines.length) {
                notes.innerHTML = '<strong>Check before publishing:</strong><br>';
                notes.append(document.createTextNode(lines.join(' ')));
                notes.classList.remove('hidden');
            }

            // Show the context the model actually used
            if (data.context_stats) {
                const s = data.context_stats;
                $('ai-context-stats').textContent = s.pages_read + ' live page(s) read | ' + s.projects + ' project(s), ' + s.new_projects + ' new | ' + s.posts + ' existing post(s)';
            }

            setStatus('Draft ready. Review and edit it below, then save.' + usage(data), 'ok');
            $('f-en-title').scrollIntoView({ behavior: 'smooth', block: 'center' });
        } catch (e) {
            setStatus(errorMessage(e), 'error');
        } finally {
            busy(false);
        }
    });

    // ---- Translate EN -> PT + ES
    if (translateBtn) {
        translateBtn.addEventListener('click', async () => {
            if (!val('f-en-title').trim() || !val('f-en-body').trim()) {
                setStatus('Write or generate the English title and body first.', 'error');
                return;
            }
            if ((val('f-pt-body').trim() || val('f-es-body').trim()) && !window.confirm('This overwrites the existing Portuguese and Spanish text. Continue?')) return;

            busy(true);
            try {
                const data = await runJob(panel.dataset.translateUrl, wrapPayload({
                    title: val('f-en-title'),
                    excerpt: val('f-en-excerpt'),
                    body: val('f-en-body'),
                    locales: ['pt', 'es'],
                }, ['title', 'excerpt', 'body']), 'Translating');

                ['pt', 'es'].forEach((code) => {
                    const t = data.result[code];
                    if (!t) return;
                    $('f-' + code + '-title').value = t.title;
                    $('f-' + code + '-excerpt').value = t.excerpt;
                    $('f-' + code + '-body').value = t.body;
                });
                setStatus('Translations added to the PT and ES tabs. Review them before saving.' + usage(data), 'ok');
            } catch (e) {
                setStatus(errorMessage(e), 'error');
            } finally {
                busy(false);
            }
        });
    }
}
