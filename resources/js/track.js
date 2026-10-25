// Anonymous click counts. Elements with data-track="<name>" send a tiny beacon when clicked; the server only adds one to
// that day's total for the name (see App\Http\Controllers\EventController). Nothing about the visitor is sent or stored.

export function initTracking() {
    document.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target.closest('[data-track]') : null;
        if (!target || !navigator.sendBeacon) return;

        // Respect "Do Not Track" and Global Privacy Control: send nothing
        if (navigator.doNotTrack === '1' || navigator.globalPrivacyControl) return;

        try {
            navigator.sendBeacon('/e', new URLSearchParams({ e: target.dataset.track }));
        } catch (e) { /* counting must never get in the way of a click */ }
    });
}
