<!-- Language splash modal (shows once if no site_locale cookie) -->
<style>
    .lang-splash-backdrop{position:fixed;inset:0;display:flex;align-items:center;justify-content:center;background:linear-gradient(180deg,rgba(2,6,23,0.35),rgba(2,6,23,0.25));z-index:60}
    .lang-splash-card{width:min(720px,95%);background:linear-gradient(180deg,rgba(255,255,255,0.98),rgba(245,247,255,0.95));border-radius:18px;padding:2rem;box-shadow:0 30px 80px rgba(10,10,30,0.45);text-align:center}
    .lang-flag{width:88px;height:88px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:28px;box-shadow:0 12px 30px rgba(99,102,241,0.16)}
    .lang-btn{display:inline-flex;align-items:center;gap:.6rem;padding:.6rem 1.25rem;border-radius:999px;border:0;font-weight:700;cursor:pointer}
    .lang-pt{background:linear-gradient(90deg,#06b6d4,#8b5cf6);color:white}
    .lang-en{background:linear-gradient(90deg,#6366f1,#06b6d4);color:white}
    .lang-close{position:absolute;top:14px;right:18px;background:transparent;border:0;font-size:20px;cursor:pointer}
</style>

<div id="langSplash" class="lang-splash-backdrop" aria-hidden="true" style="display:none">
    <div class="lang-splash-card relative">
        <button class="lang-close" aria-label="Close" onclick="closeLangSplash()">✕</button>
        <h2 class="text-3xl font-extrabold text-indigo-900 mb-2">Bem-vindo / Welcome</h2>
        <p class="text-gray-700 mb-6">Choose your language — escolha a sua língua</p>

        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
            <button class="lang-btn lang-pt" onclick="chooseLang('pt')">
                <span class="lang-flag">PT</span>
                Português (Portugal)
            </button>

            <button class="lang-btn lang-en" onclick="chooseLang('en')">
                <span class="lang-flag">EN</span>
                English
            </button>
        </div>

        <p class="text-xs text-gray-500 mt-6">Your choice will be remembered for future visits.</p>
    </div>
</div>

<script>
(function(){
    function hasLocale() {
        // Check cookie first
        if (document.cookie.split('; ').find(row => row.startsWith('site_locale='))) return true;
        // Fallback to localStorage
        return !!localStorage.getItem('site_locale');
    }

    // Show splash if no locale
    if (!hasLocale()) {
        // delay to allow page to render nicely
        setTimeout(()=>{
            const el = document.getElementById('langSplash');
            if (el) { el.style.display = 'flex'; el.setAttribute('aria-hidden', 'false'); }
        }, 400);
    }

    window.chooseLang = function(code) {
        // Save to cookie (365 days)
        const days = 365;
        const date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        document.cookie = `site_locale=${code}; expires=${date.toUTCString()}; path=/`;
        localStorage.setItem('site_locale', code);
        // Optionally set html lang attribute
        try { document.documentElement.lang = code; } catch(e){}
        // Smooth close animation then reload to apply server-side locale
        closeLangSplash();
        setTimeout(()=> location.reload(), 300);
    }

    window.closeLangSplash = function(){
        const el = document.getElementById('langSplash');
        if (!el) return;
        el.style.transition = 'opacity .28s ease, transform .28s ease';
        el.style.opacity = 0; el.style.transform = 'translateY(8px)';
        setTimeout(()=>{ el.style.display = 'none'; el.setAttribute('aria-hidden','true') }, 320);
    }
})();
</script>
