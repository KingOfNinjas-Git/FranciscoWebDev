// Accessibility scan of the public pages: axe-core (WCAG 2.2 A/AA + best practice) at desktop and 320px phone width,
// a keyboard Tab-through of the home page (every stop must show a focus style) and a "reduce motion" check.
//
//   npm install --no-save axe-core puppeteer-core
//   CHROME_PATH="path/to/chrome" node tools/a11y-scan.mjs http://127.0.0.1:8000
//
// Run it against a local copy of the site that has some sample posts and projects.
import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import { createRequire } from 'node:module';
const require = createRequire(import.meta.url);
const axeSource = fs.readFileSync(require.resolve('axe-core/axe.min.js'), 'utf8');
const base = process.argv[2];
const pages = ['/', '/pt', '/es', '/blog', '/pt/blog', '/es/blog', '/blog/gallery', '/pt/blog/gallery', '/projects/gamehaven', '/es/projects/gamehaven', '/privacy', '/accessibility', '/pt/accessibility', '/es/accessibility'];

const browser = await puppeteer.launch({ executablePath: process.env.CHROME_PATH, headless: true, args: ['--no-sandbox', '--disable-gpu'] });
const page = await browser.newPage();
let problems = 0;

// 1) axe on every page, desktop and a 320px phone (WCAG "reflow" width)
for (const [w, h] of [[1280, 900], [320, 640]]) {
    await page.setViewport({ width: w, height: h });
    for (const path of pages) {
        await page.goto(base + path, { waitUntil: 'networkidle0' });
        await page.evaluate(async () => { for (let y = 0; y < document.documentElement.scrollHeight; y += 500) { scrollTo(0, y); await new Promise((r) => setTimeout(r, 60)); } scrollTo(0, 0); });
        await new Promise((r) => setTimeout(r, 400));
        await page.evaluate(axeSource);
        const result = await page.evaluate(() => axe.run(document, { runOnly: { type: 'tag', values: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa', 'best-practice'] } }));
        const overflow = await page.evaluate(() => document.documentElement.scrollWidth - innerWidth);
        const issues = result.violations.map((v) => `${v.id} (${v.impact}) x${v.nodes.length}: ${v.nodes[0].target.join(' ').slice(0, 70)}`);
        if (overflow > 1 && w === 320) issues.push(`sideways scroll at 320px: ${overflow}px wider than the screen`);
        problems += issues.length;
        console.log(`${String(w).padStart(4)}px ${path.padEnd(26)} ${issues.length ? issues.length + ' problem(s)' : 'clean'}`);
        issues.forEach((i) => console.log('        - ' + i));
    }
}

// 2) keyboard: Tab through the home page, every stop must show where the focus is
await page.setViewport({ width: 1280, height: 900 });
await page.goto(base + '/', { waitUntil: 'networkidle0' });
const stops = [];
for (let i = 0; i < 70; i++) {
    await page.keyboard.press('Tab');
    const info = await page.evaluate(() => {
        const el = document.activeElement;
        if (!el || el === document.body) return null;
        const cs = getComputedStyle(el); const r = el.getBoundingClientRect();
        const visibleRing = (cs.outlineStyle !== 'none' && parseFloat(cs.outlineWidth) > 0) || cs.boxShadow !== 'none';
        return { tag: el.tagName.toLowerCase(), text: (el.getAttribute('aria-label') || el.textContent || el.getAttribute('href') || '').trim().replace(/\s+/g, ' ').slice(0, 30), visibleRing, hidden: r.width === 0 || r.height === 0 };
    });
    if (info) stops.push(info);
}
const noRing = stops.filter((s) => !s.visibleRing);
console.log(`\nkeyboard: ${stops.length} stops; first stop = "${stops[0]?.text}" (should be the skip link); without a visible focus style: ${noRing.length}`);
noRing.slice(0, 8).forEach((s) => console.log(`        - <${s.tag}> ${s.text}`));
problems += noRing.length;

// 3) reduced motion: nothing should be animating
const ctx = await browser.createBrowserContext();
const rm = await ctx.newPage();
await rm.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
await rm.goto(base + '/', { waitUntil: 'networkidle0' });
await new Promise((r) => setTimeout(r, 800));
const running = await rm.evaluate(() => document.getAnimations().filter((a) => a.playState === 'running' && a.effect?.getTiming().iterations === Infinity).map((a) => (a.effect?.target?.className || a.effect?.target?.tagName || '').toString().slice(0, 40)));
console.log(`reduced motion: ${running.length} endless animation(s) still running ${running.slice(0, 5).join(', ')}`);
problems += running.length;

console.log(`\ntotal problems: ${problems}`);
await browser.close();
