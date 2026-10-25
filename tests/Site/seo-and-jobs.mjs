// JavaScript logic tests: job polling (ai-job.js) and the SEO checklist (seo-check.js).
import { runJob, errorMessage } from '../../resources/js/ai-job.js';
import { analyse } from '../../resources/js/seo-check.js';

let pass = 0, fail = 0;
const check = (name, ok, extra = '') => { console.log((ok ? '  ok   ' : '  FAIL ') + name + (!ok && extra ? '  -> ' + extra : '')); ok ? pass++ : fail++; };
const fast = { pollMs: 1, retryMs: 1 };
const fakeAxios = (statuses, startData = { id: 7 }) => {
    const calls = { post: [], get: [] };
    let i = 0;
    return {
        calls,
        post: async (url, payload) => { calls.post.push([url, payload]); return { data: startData }; },
        get: async (url) => { calls.get.push(url); const s = statuses[Math.min(i++, statuses.length - 1)]; if (s instanceof Error) throw s; return { data: s }; },
    };
};

console.log('== runJob');
let ax = fakeAxios([{ status: 'running' }, { status: 'running' }, { status: 'done', result: { title: 'T' }, input_tokens: 5 }]);
let ticks = [];
let r = await runJob({ axios: ax, startUrl: '/start', statusUrl: '/status', payload: { a: 1 }, onTick: (s) => ticks.push(s), ...fast });
check('returns the finished job after polling', r.status === 'done' && r.result.title === 'T' && r.input_tokens === 5);
check('posts the payload to the start url, polls {statusUrl}/{id}', ax.calls.post[0][0] === '/start' && ax.calls.post[0][1].a === 1 && ax.calls.get.length === 3 && ax.calls.get[0] === '/status/7');
check('onTick is called before every check', ticks.length === 3 && ticks.every((t) => typeof t === 'number'));

ax = fakeAxios([{ status: 'failed', error: 'The AI declined to write this.' }]);
try { await runJob({ axios: ax, startUrl: '/s', statusUrl: '/t', payload: {}, ...fast }); check('failed job throws', false); }
catch (e) { check('failed job throws the server message', e.message === 'The AI declined to write this.', e.message); }

ax = fakeAxios([{ status: 'failed' }]);
try { await runJob({ axios: ax, startUrl: '/s', statusUrl: '/t', payload: {}, ...fast }); } catch (e) { check('failed job without a message gets a default one', e.message === 'The AI request failed.'); }

const err429 = Object.assign(new Error('x'), { response: { status: 429 } });
ax = fakeAxios([err429, err429, { status: 'done', result: {} }]);
r = await runJob({ axios: ax, startUrl: '/s', statusUrl: '/t', payload: {}, ...fast });
check('a 429 rate-limit while polling is waited out, not fatal', r.status === 'done' && ax.calls.get.length === 3);

const netErr = new Error('Network Error');
ax = fakeAxios([netErr, { status: 'done', result: {} }]);
r = await runJob({ axios: ax, startUrl: '/s', statusUrl: '/t', payload: {}, ...fast });
check('a brief network error (no response) is retried', r.status === 'done');

const err500 = Object.assign(new Error('Server error'), { response: { status: 500, data: { message: 'Server exploded' } } });
ax = fakeAxios([err500]);
try { await runJob({ axios: ax, startUrl: '/s', statusUrl: '/t', payload: {}, ...fast }); check('server error throws', false); }
catch (e) { check('a real server error (500) is not retried and is thrown', e === err500 && ax.calls.get.length === 1); }

ax = fakeAxios([{ status: 'running' }]);
try { await runJob({ axios: ax, startUrl: '/s', statusUrl: '/t', payload: {}, maxMs: 30, ...fast }); check('timeout throws', false); }
catch (e) { check('gives up with a clear message when it takes too long', /taking too long/.test(e.message)); }

ax = fakeAxios([err429]);
try { await runJob({ axios: ax, startUrl: '/s', statusUrl: '/t', payload: {}, maxMs: 30, ...fast }); check('endless 429 throws', false); }
catch (e) { check('endless rate limiting also ends with the same message', /taking too long/.test(e.message)); }

const bad = { post: async () => { throw Object.assign(new Error('boom'), { response: { status: 429, data: { message: 'Daily AI limit reached (30).' } } }); }, get: async () => ({}) };
try { await runJob({ axios: bad, startUrl: '/s', statusUrl: '/t', payload: {}, ...fast }); check('start failure throws', false); }
catch (e) { check('a failed start request surfaces the server message via errorMessage()', errorMessage(e) === 'Daily AI limit reached (30).'); }

console.log('== errorMessage');
check('uses the server message first', errorMessage({ response: { data: { message: 'M' } } }) === 'M');
check('falls back to the first validation error', errorMessage({ response: { data: { errors: { a: ['First', 'Second'] } } } }) === 'First');
check('explains an expired session (401)', /session expired/.test(errorMessage({ response: { status: 401, data: {} } })));
check('then to the error text, then to a default', errorMessage(new Error('E')) === 'E' && errorMessage({}) === 'Something went wrong.');

console.log('== SEO checklist');
const by = (items, id) => items.find((i) => i.id === id);
const good = analyse({
    title: 'Accessible project galleries', // 28 + 19 = 47
    excerpt: 'Learn how to make a project gallery work with a keyboard and a screen reader, step by step.', // ~92... pad below
    body: '## Intro\n\n' + 'word '.repeat(320) + '\n\n## Next\n\nSee [my gallery](/#projects/gamehaven).',
    tags: 'accessibility, wcag',
    origin: 'https://franciscowebdev.com',
});
check('title within 60 once the site name is added -> ok', by(good, 'title').level === 'ok' && /47 characters/.test(by(good, 'title').detail), by(good, 'title').detail);
check('a long good post passes length, headings, links, tags, placeholders', ['length', 'headings', 'links', 'tags', 'placeholders'].every((id) => by(good, id).level === 'ok'));

const goodEx = analyse({ title: 'x', excerpt: 'e'.repeat(130), body: '', tags: '' });
check('excerpt of 130 characters is ok', by(goodEx, 'excerpt').level === 'ok');
check('excerpt of 90 is a little short (warn), 200 may be cut (warn), empty is bad',
    by(analyse({ excerpt: 'e'.repeat(90) }), 'excerpt').level === 'warn' && by(analyse({ excerpt: 'e'.repeat(200) }), 'excerpt').level === 'warn' && by(analyse({ excerpt: '' }), 'excerpt').level === 'bad');
check('title: 41 + site name = 60 ok; 42 -> warn; 52+ -> bad; empty -> bad',
    by(analyse({ title: 'a'.repeat(41) }), 'title').level === 'ok' && by(analyse({ title: 'a'.repeat(42) }), 'title').level === 'warn' && by(analyse({ title: 'a'.repeat(52) }), 'title').level === 'bad' && by(analyse({ title: '' }), 'title').level === 'bad');
check('code blocks are not counted as words', by(analyse({ body: 'one two three\n```js\n' + 'x '.repeat(500) + '\n```\nfour' }), 'length').detail.startsWith('4 words'), by(analyse({ body: 'one two three\n```js\n' + 'x '.repeat(500) + '\n```\nfour' }), 'length').detail);
check('under 300 words warns', by(analyse({ body: 'short post' }), 'length').level === 'warn');
check('needs two "##" headings', by(analyse({ body: '## One\ntext' }), 'headings').level === 'warn' && by(analyse({ body: '## One\n\n## Two' }), 'headings').level === 'ok' && by(analyse({ body: '### Only h3\n### Another' }), 'headings').level === 'warn');
const L = (body, origin = 'https://franciscowebdev.com') => by(analyse({ body, origin }), 'links');
check('relative and own-site absolute links count as internal', L('[a](/blog/x)').level === 'ok' && L('[a](https://franciscowebdev.com/blog/x)').level === 'ok');
check('external, protocol-relative and mailto links do not count', L('[a](https://example.com/x)').level === 'warn' && L('[a](//evil.com/x)').level === 'warn' && L('[a](mailto:me@x.com)').level === 'warn');
check('tags: 2 to 5 ok, otherwise warn', by(analyse({ tags: 'a,b' }), 'tags').level === 'ok' && by(analyse({ tags: 'a' }), 'tags').level === 'warn' && by(analyse({ tags: 'a,b,c,d,e,f' }), 'tags').level === 'warn' && by(analyse({ tags: ' , ,' }), 'tags').detail.startsWith('0 tags'));
const ph = by(analyse({ body: 'Ratio: [Add the contrast ratio you measured.] and [Paste your short toggle function here.] and [TODO: link]' }), 'placeholders');
check('leftover [TODO]/[Add ...]/[Paste ...] placeholders are flagged as "bad"', ph.level === 'bad' && ph.detail.startsWith('3 still to fill in'), ph.detail);
check('ordinary square brackets are not mistaken for placeholders', by(analyse({ body: 'An array like [1, 2, 3] or [link text](/x)' }), 'placeholders').level === 'ok');
check('every item has an id, level, label and detail', good.length === 7 && good.every((i) => i.id && ['ok', 'warn', 'bad'].includes(i.level) && i.label && i.detail));
check('empty input never throws', analyse({}).length === 7);

console.log(`\n${pass} passed, ${fail} failed`);
process.exit(fail ? 1 : 0);
