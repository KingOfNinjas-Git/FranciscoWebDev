// A plain-language SEO and readiness checklist for a blog post (English text), shown beside the editor.
// Pure functions, so they can be tested without a browser.

const TITLE_SUFFIX = ' | Francisco Cavaco'; // added to every page title by the site

const words = (text) => (text.replace(/```[\s\S]*?```/g, ' ').match(/[\p{L}\p{N}'’]+/gu) || []).length;

/**
 * @param {{title: string, excerpt: string, body: string, tags: string, origin?: string}} post
 * @returns {{id: string, level: 'ok'|'warn'|'bad', label: string, detail: string}[]}
 */
export function analyse({ title = '', excerpt = '', body = '', tags = '', origin = '' }) {
    const items = [];
    const add = (id, level, label, detail) => items.push({ id, level, label, detail });

    // Browser tab / search result title
    const titleLen = title.trim().length;
    const shown = titleLen + TITLE_SUFFIX.length;
    if (titleLen === 0) add('title', 'bad', 'Title', 'Add a title.');
    else if (shown <= 60) add('title', 'ok', 'Title length', shown + ' characters in the search result (up to about 60 fit).');
    else if (shown <= 70) add('title', 'warn', 'Title length', shown + ' characters in the search result: Google may cut the end off. Aim for ' + (60 - TITLE_SUFFIX.length) + ' or fewer.');
    else add('title', 'bad', 'Title length', shown + ' characters in the search result: too long and it will be cut off. Aim for ' + (60 - TITLE_SUFFIX.length) + ' or fewer.');

    // Meta description / snippet
    const exLen = excerpt.trim().length;
    if (exLen === 0) add('excerpt', 'bad', 'Excerpt (search snippet)', 'Add an excerpt: it becomes the description under your title in search results.');
    else if (exLen >= 100 && exLen <= 160) add('excerpt', 'ok', 'Excerpt (search snippet)', exLen + ' characters: a good length.');
    else if (exLen < 100) add('excerpt', 'warn', 'Excerpt (search snippet)', exLen + ' characters: a little short. 100 to 160 works best.');
    else add('excerpt', 'warn', 'Excerpt (search snippet)', exLen + ' characters: Google may shorten it. Aim for 160 or fewer.');

    // Substance
    const count = words(body);
    if (count >= 300) add('length', 'ok', 'Length', count + ' words.');
    else add('length', 'warn', 'Length', count + ' words. Posts under about 300 words rarely rank; add depth if you can.');

    // Structure
    const h2 = (body.match(/^##\s+\S/gm) || []).length;
    if (h2 >= 2) add('headings', 'ok', 'Headings', h2 + ' sections with a "##" heading.');
    else add('headings', 'warn', 'Headings', 'Split the post into sections with "## Heading" lines: it helps readers and search engines.');

    // Internal links
    const urls = [...body.matchAll(/\]\(([^)\s]+)/g)].map((m) => m[1]);
    const links = urls.filter((u) => (u.startsWith('/') && !u.startsWith('//')) || (origin !== '' && u.startsWith(origin))).length;
    if (links >= 1) add('links', 'ok', 'Links to your own pages', links + ' internal ' + (links === 1 ? 'link' : 'links') + '.');
    else add('links', 'warn', 'Links to your own pages', 'Link to a related post or a project, e.g. [my gallery](/#projects/gamehaven).');

    // Tags
    const tagCount = tags.split(',').map((t) => t.trim()).filter(Boolean).length;
    if (tagCount >= 2 && tagCount <= 5) add('tags', 'ok', 'Tags', tagCount + ' tags.');
    else add('tags', 'warn', 'Tags', tagCount + ' tags. 2 to 5 topical tags works best.');

    // Not an SEO rule, but the most common reason to hold a post back: leftover placeholders from the AI
    const placeholders = (body + ' ' + title + ' ' + excerpt).match(/\[(?:TODO|Add |Paste |Explain |Insert |Fill )[^\]]*\]/gi) || [];
    if (placeholders.length === 0) add('placeholders', 'ok', 'Placeholders', 'None left in the text.');
    else add('placeholders', 'bad', 'Placeholders', placeholders.length + ' still to fill in, e.g. ' + placeholders[0].slice(0, 50) + (placeholders[0].length > 50 ? '...' : ''));

    return items;
}
