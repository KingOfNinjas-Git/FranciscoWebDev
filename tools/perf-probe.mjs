// Where does the home page spend its time? Loads it with a 4x slower CPU and reports style, layout and script time,
// with one visual effect switched off at a time (edit the "variants" list to try others).
//
//   npm install --no-save puppeteer-core
//   CHROME_PATH="path/to/chrome" node tools/perf-probe.mjs https://franciscowebdev.com/ 5
import puppeteer from 'puppeteer-core';
// node perf_probe.mjs <url> [runs]  — loads the page with 4x slower CPU, with one visual effect switched off at a time,
// and reports style-calculation, layout and total main-thread time (median of the runs).
const url = process.argv[2];
const runs = Number(process.argv[3] || 5);

const variants = {
    'as it is now': '',
    'system font instead of the web fonts': '*,*::before,*::after{font-family:system-ui,sans-serif!important}',
    'no wide letter-spacing': '*,*::before,*::after{letter-spacing:normal!important}',
    'faster text rendering': '*{text-rendering:optimizeSpeed!important;font-kerning:none!important}',
    'no font features / variations': '*{font-variation-settings:normal!important;font-feature-settings:normal!important}',
};

const browser = await puppeteer.launch({ executablePath: process.env.CHROME_PATH, headless: true, args: ['--no-sandbox', '--disable-gpu'] });
const median = (a) => a.slice().sort((x, y) => x - y)[Math.floor(a.length / 2)];

async function once(css) {
    const page = await browser.newPage();
    const client = await page.createCDPSession();
    await page.setViewport({ width: 412, height: 823, deviceScaleFactor: 2, isMobile: true, hasTouch: true });
    await client.send('Emulation.setCPUThrottlingRate', { rate: 4 });
    await client.send('Performance.enable');
    if (css) await page.evaluateOnNewDocument((c) => {
        const add = () => { const s = document.createElement('style'); s.textContent = c; (document.head || document.documentElement).appendChild(s); };
        document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', add) : add();
        new MutationObserver((m, o) => { if (document.head) { add(); o.disconnect(); } }).observe(document.documentElement, { childList: true });
    }, css);
    await page.goto(url, { waitUntil: 'networkidle0', timeout: 90000 });
    await new Promise((r) => setTimeout(r, 1500));
    const m = Object.fromEntries((await client.send('Performance.getMetrics')).metrics.map((x) => [x.name, x.value]));
    await page.close();
    return { style: m.RecalcStyleDuration * 1000, layout: m.LayoutDuration * 1000, task: m.TaskDuration * 1000, script: m.ScriptDuration * 1000 };
}

console.log(`${url}  (4x slower CPU, median of ${runs})\n`);
let base = null;
for (const [name, css] of Object.entries(variants)) {
    const rs = [];
    for (let i = 0; i < runs; i++) rs.push(await once(css));
    const r = { style: median(rs.map((x) => x.style)), layout: median(rs.map((x) => x.layout)), task: median(rs.map((x) => x.task)), script: median(rs.map((x) => x.script)) };
    base ??= r;
    const diff = ((r.task - base.task) / base.task * 100).toFixed(0);
    console.log(name.padEnd(48), `style ${r.style.toFixed(0).padStart(4)} ms | layout ${r.layout.toFixed(0).padStart(4)} ms | script ${r.script.toFixed(0).padStart(4)} ms | all main-thread work ${r.task.toFixed(0).padStart(5)} ms (${diff >= 0 ? '+' : ''}${diff}%)`);
}
await browser.close();
