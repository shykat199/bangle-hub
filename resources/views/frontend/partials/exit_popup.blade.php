{{-- ============================================================
     EXIT-INTENT COUPON POPUP
     Renders nothing unless the admin switched it on AND picked a
     valid (in-date) coupon AND the visitor's cart is empty.
     Every behaviour is admin-driven: which coupon, texts, repeat
     interval (hours), desktop trigger, mobile back-button trigger.
     ============================================================ --}}
@php
    $epInfo    = getInfo();
    $epCoupon  = null;
    $epLanding = (bool) ($epLandingMode ?? false);

    if ($epInfo && ($epInfo->exit_popup_active ?? 0) == 1 && !empty($epInfo->exit_popup_coupon_id) && empty(session('cart'))) {
        try {
            $epCoupon = \App\Models\CouponCode::find($epInfo->exit_popup_coupon_id);
            if ($epCoupon) {
                $epToday = date('Y-m-d');
                if (($epCoupon->start && $epCoupon->start > $epToday) || ($epCoupon->end && $epCoupon->end < $epToday)) {
                    $epCoupon = null;
                }
            }

            // Admin-chosen scope: everywhere / main site only / landing pages
            // only / a hand-picked set of products
            if ($epCoupon) {
                $epScope = $epInfo->exit_popup_scope ?? 'all';
                if ($epScope === 'site' && $epLanding) {
                    $epCoupon = null;
                } elseif ($epScope === 'landing' && !$epLanding) {
                    $epCoupon = null;
                } elseif ($epScope === 'products') {
                    $epIds = array_map('intval', json_decode($epInfo->exit_popup_product_ids ?? '[]', true) ?: []);
                    // landing views expose $product, the product page $singleProduct
                    $epPid = $epLanding
                        ? (int) ($product->id ?? 0)
                        : (int) ($singleProduct->id ?? 0);
                    if (!$epPid || !in_array($epPid, $epIds, true)) {
                        $epCoupon = null;
                    }
                }
            }
        } catch (\Throwable $e) {
            $epCoupon = null;
        }
    }
@endphp

@if($epCoupon)
@php
    $epDiscountLabel = ($epCoupon->discount_type === 'fixed')
        ? '৳' . rtrim(rtrim(number_format((float) $epCoupon->amount, 2, '.', ''), '0'), '.') . ' ছাড়'
        : rtrim(rtrim(number_format((float) $epCoupon->amount, 2, '.', ''), '0'), '.') . '% ছাড়';
    $epMin = (float) ($epCoupon->minimum_amount ?? 0);

    // Dynamic brand colors — the same admin-set values the header/buttons use,
    // so the popup always matches the shop's theme automatically
    $epGrad  = $epInfo->gradient_code    ?: 'linear-gradient(135deg,#0d6efd,#00276C)';
    $epSolid = $epInfo->common_btn_color ?: '#0d6efd';
    $epText  = $epInfo->primary_color    ?: '#ffffff';
@endphp
<style>
    .exit-popup-overlay {
        /* admin-set brand colors — popup follows the shop theme automatically */
        --ep-grad: {!! $epGrad !!};
        --ep-solid: {{ $epSolid }};
        --ep-text: {{ $epText }};
        position: fixed; inset: 0; z-index: 999999;
        background: rgba(10, 15, 30, .72);
        display: flex; align-items: center; justify-content: center;
        padding: 16px;
        opacity: 0; visibility: hidden;
        transition: opacity .3s ease, visibility .3s ease;
    }
    .exit-popup-overlay.is-open { opacity: 1; visibility: visible; }

    .exit-popup-card {
        width: min(92vw, 380px);
        max-height: 92vh; overflow-y: auto;
        background: #ffffff; border-radius: 24px;
        position: relative;
        box-shadow: 0 30px 80px rgba(0,0,0,.45);
        transform: translateY(28px) scale(.94);
        transition: transform .35s cubic-bezier(.34,1.4,.64,1);
        font-family: 'Hind Siliguri', 'Inter', sans-serif;
        -webkit-overflow-scrolling: touch;
    }
    .exit-popup-overlay.is-open .exit-popup-card { transform: translateY(0) scale(1); }

    /* ---- top: gradient hero ---- */
    .ep-top {
        position: relative; overflow: hidden;
        background: var(--ep-grad);
        border-radius: 24px 24px 0 0;
        text-align: center;
        padding: 30px 22px 46px;
    }
    .ep-top::before, .ep-top::after {
        content: ""; position: absolute; border-radius: 50%;
        background: rgba(255,255,255,.09); pointer-events: none;
    }
    .ep-top::before { width: 150px; height: 150px; top: -55px; left: -45px; }
    .ep-top::after  { width: 110px; height: 110px; bottom: -45px; right: -35px; }
    .ep-gift {
        width: 68px; height: 68px; margin: 0 auto 12px;
        background: rgba(255,255,255,.16);
        border: 2px solid rgba(255,255,255,.45);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 32px; line-height: 1;
        box-shadow: 0 8px 24px rgba(0,0,0,.18);
        position: relative; z-index: 1;
    }
    .ep-top h3 {
        margin: 0 0 6px; position: relative; z-index: 1;
        font-size: clamp(18px, 5.5vw, 21px); font-weight: 800; color: var(--ep-text); line-height: 1.35;
    }
    .ep-top p {
        margin: 0; position: relative; z-index: 1;
        font-size: clamp(12.5px, 3.8vw, 14px); color: var(--ep-text); opacity: .88; line-height: 1.6;
    }

    /* ---- middle: coupon ticket ---- */
    .ep-ticket {
        position: relative;
        margin: -26px 18px 0;
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 12px 30px rgba(15,23,42,.10);
        padding: 18px 16px 16px;
        text-align: center;
        z-index: 2;
    }
    .ep-off-badge {
        display: inline-block;
        background: var(--ep-grad);
        color: var(--ep-text); font-size: clamp(20px, 6.5vw, 26px); font-weight: 800;
        padding: 8px 22px; border-radius: 999px;
        box-shadow: 0 8px 20px rgba(15,23,42,.25);
        margin-bottom: 4px; white-space: nowrap;
    }
    .ep-min { font-size: 12px; color: #94a3b8; font-weight: 600; margin-bottom: 12px; }
    .ep-cut {
        display: flex; align-items: center; gap: 8px; margin: 0 4px 12px;
        color: #cbd5e1; font-size: 13px;
    }
    .ep-cut::before, .ep-cut::after { content: ""; flex: 1; border-top: 2px dashed #e2e8f0; }
    /* code + COPY: one joined row (input-group style) */
    .ep-code-box {
        display: flex; align-items: stretch;
        border: 2px dashed var(--ep-solid);
        border-radius: 14px;
        background: #f8fafc;
        overflow: hidden;
    }
    .ep-code {
        flex: 1; display: flex; align-items: center; justify-content: center;
        font-size: clamp(16px, 5vw, 20px); font-weight: 800;
        letter-spacing: 2px; color: var(--ep-solid);
        padding: 13px 10px; min-width: 0; word-break: break-all;
    }
    .ep-copy-btn {
        /* width/height must be pinned — the theme's global button CSS
           (width:100%) otherwise swallows the whole row */
        width: auto !important; min-width: 86px; max-width: 40%;
        height: auto !important;
        flex: 0 0 auto;
        border: none; cursor: pointer;
        background: var(--ep-grad);
        color: var(--ep-text); font-weight: 800; font-size: 13px; letter-spacing: .8px;
        padding: 0 18px; margin: 4px; border-radius: 10px;
        display: flex; flex-direction: row; align-items: center; justify-content: center; gap: 7px;
        transition: transform .15s ease, background .2s ease;
        white-space: nowrap;
    }
    .ep-copy-btn:active { transform: scale(.94); }
    .ep-copy-btn i { font-size: 14px; }
    .ep-copy-btn.copied { background: linear-gradient(135deg, #059669, #10b981); color: #fff; }

    /* ---- bottom ---- */
    .ep-bottom { padding: 16px 18px 18px; text-align: center; }
    .ep-shop-btn {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        width: 100%; text-decoration: none !important;
        border: none; cursor: pointer;
        background: var(--ep-grad);
        color: var(--ep-text) !important; font-size: clamp(14.5px, 4.5vw, 16px); font-weight: 800;
        padding: 14px; border-radius: 14px;
        box-shadow: 0 10px 24px rgba(15,23,42,.28);
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .ep-shop-btn:hover { transform: translateY(-2px); box-shadow: 0 14px 30px rgba(15,23,42,.36); }
    .ep-later {
        display: inline-block; margin-top: 10px;
        border: none; background: none; cursor: pointer;
        font-size: 12.5px; color: #94a3b8; font-weight: 600;
        text-decoration: underline; text-underline-offset: 3px;
    }
    .ep-close {
        position: absolute; top: 12px; right: 12px; z-index: 3;
        width: 34px; height: 34px; border: none; cursor: pointer;
        background: rgba(255,255,255,.2); color: #fff;
        backdrop-filter: none;
        border-radius: 50%; font-size: 17px; line-height: 1;
        display: flex; align-items: center; justify-content: center;
        transition: background .2s ease, transform .15s ease;
    }
    .ep-close:hover { background: rgba(255,255,255,.35); }
    .ep-close:active { transform: scale(.9); }

    @media (max-width: 359.98px) {
        .ep-top { padding: 24px 16px 42px; }
        .ep-ticket { margin: -24px 12px 0; padding: 14px 12px 12px; }
        .ep-bottom { padding: 14px 14px 16px; }
        .ep-gift { width: 58px; height: 58px; font-size: 26px; }
    }
</style>

<div class="exit-popup-overlay" id="exitPopupOverlay" role="dialog" aria-modal="true">
    <div class="exit-popup-card">
        <button type="button" class="ep-close" id="exitPopupClose" aria-label="Close">&times;</button>

        <div class="ep-top">
            <div class="ep-gift">🎁</div>
            <h3>{{ $epInfo->exit_popup_title ?: 'যাওয়ার আগে একটু দাঁড়ান!' }}</h3>
            <p>{{ $epInfo->exit_popup_text ?: 'আপনার জন্য বিশেষ ছাড়! নিচের কুপন কোডটি checkout-এ ব্যবহার করলেই ছাড় পেয়ে যাবেন।' }}</p>
        </div>

        <div class="ep-ticket">
            <div class="ep-off-badge">{{ $epDiscountLabel }}</div>
            @if($epMin > 0)
                <div class="ep-min">সর্বনিম্ন অর্ডার ৳{{ number_format($epMin) }}</div>
            @else
                <div class="ep-min">যেকোনো অর্ডারে প্রযোজ্য</div>
            @endif
            <div class="ep-cut">✂</div>
            <div class="ep-code-box">
                <span class="ep-code" id="exitPopupCode">{{ $epCoupon->code }}</span>
                <button type="button" class="ep-copy-btn" id="exitPopupCopy"><i class="far fa-copy"></i> Copy</button>
            </div>
        </div>

        <div class="ep-bottom">
            @if($epLanding)
                <button type="button" class="ep-shop-btn" id="exitPopupShop">অর্ডার করতে নিচে যান <i class="fas fa-arrow-down"></i></button>
            @else
                <a href="{{ route('front.products.index') }}" class="ep-shop-btn" id="exitPopupShop">কেনাকাটা শুরু করুন <i class="fas fa-arrow-right"></i></a>
            @endif
            <button type="button" class="ep-later" id="exitPopupLater">না ধন্যবাদ, পরে দেখব</button>
        </div>
    </div>
</div>

<script>
(function(){
    if (window.__EXIT_POPUP__) return;
    window.__EXIT_POPUP__ = true;

    var cfg = {
        hours:   {{ (int) ($epInfo->exit_popup_hours ?? 24) }},
        desktop: {{ ($epInfo->exit_popup_desktop ?? 1) == 1 ? 'true' : 'false' }},
        mobile:  {{ ($epInfo->exit_popup_mobile ?? 1) == 1 ? 'true' : 'false' }},
        delayMs: {{ (int) ($epInfo->exit_popup_delay_seconds ?? 5) * 1000 }},
        maxShows: {{ max(1, (int) ($epInfo->exit_popup_max_shows ?? 1)) }},
        landing: {{ $epLanding ? 'true' : 'false' }},
        code:    @json($epCoupon->code),
        trackUrl: @json(route('front.exit_popup.track')),
        csrf:    @json(csrf_token())
    };

    var overlay = document.getElementById('exitPopupOverlay');
    if (!overlay) return;
    var shown = false, armed = false;
    var KEY = 'exitPopupTs';

    // The lock stores {c: dismiss-count, t: last-dismiss-time}. The admin
    // decides how many dismissals (maxShows) a visitor gets per period —
    // after that the popup rests until the period (hours) expires, which
    // resets the counter. Old installs stored a bare timestamp; treated as
    // one dismissal.
    function readLock(){
        try {
            var raw = (cfg.hours <= 0 ? sessionStorage : localStorage).getItem(KEY);
            if (!raw) return {c: 0, t: 0};
            if (raw.charAt(0) === '{') { var o = JSON.parse(raw); return {c: o.c || 0, t: o.t || 0}; }
            var n = parseInt(raw, 10);
            return {c: 1, t: (n > 1000000000000 ? n : Date.now())};
        } catch(e) { return {c: 0, t: 0}; }
    }
    function periodExpired(lock){
        return cfg.hours > 0 && lock.t > 0 && (Date.now() - lock.t) > cfg.hours * 3600 * 1000;
    }
    function freqOk(){
        var lock = readLock();
        if (periodExpired(lock)) return true; // period over: counter resets
        return lock.c < cfg.maxShows;
    }
    function markShown(){
        try {
            var lock = readLock();
            if (periodExpired(lock)) lock = {c: 0, t: 0};
            var v = JSON.stringify({c: lock.c + 1, t: Date.now()});
            localStorage.setItem(KEY, v);
            sessionStorage.setItem(KEY, v);
        } catch(e) {}
    }
    function track(t){
        try {
            fetch(cfg.trackUrl, {
                method: 'POST', keepalive: true,
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': cfg.csrf, 'X-Requested-With': 'XMLHttpRequest'},
                body: JSON.stringify({type: t})
            });
        } catch(e) {}
    }
    // The repeat-lock starts only when the visitor ENGAGES (closes, copies,
    // or clicks shop) — not on mere display. An accidental reload while the
    // popup is open must not burn their one look at the coupon.
    function show(){
        if (shown) return;
        shown = true;
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        track('show');
    }
    function hide(){
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
        markShown();
    }

    document.getElementById('exitPopupClose').addEventListener('click', hide);
    var laterBtn = document.getElementById('exitPopupLater');
    if (laterBtn) laterBtn.addEventListener('click', hide);
    overlay.addEventListener('click', function(e){ if (e.target === overlay) hide(); });
    var shopBtn = overlay.querySelector('.ep-shop-btn');
    if (shopBtn) shopBtn.addEventListener('click', function(){
        markShown();
        if (cfg.landing) {
            hide();
            // scroll to this landing page's own order form
            var f = document.querySelector('form input[name="mobile"], form input[name="phone"], form input[name="name"]');
            var target = f ? f.closest('form') : document.querySelector('form');
            if (target) target.scrollIntoView({behavior: 'smooth', block: 'start'});
        }
    });

    document.getElementById('exitPopupCopy').addEventListener('click', function(){
        var btn = this;
        function done(){ btn.textContent = 'Copied ✓'; btn.classList.add('copied'); track('copy'); markShown(); }
        function fallback(){
            var ta = document.createElement('textarea');
            ta.value = cfg.code; ta.style.position = 'fixed'; ta.style.opacity = '0';
            document.body.appendChild(ta); ta.focus(); ta.select();
            try { document.execCommand('copy'); } catch(e) {}
            document.body.removeChild(ta); done();
        }
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(cfg.code).then(done, fallback);
        } else { fallback(); }
    });

    if (!freqOk()) return;

    // primary input coarse = real phone/tablet; touch-screen laptops stay "desktop"
    var isMobile = window.matchMedia && window.matchMedia('(pointer: coarse)').matches;

    // Desktop: mouse races to the top of the window (back / close button).
    // Attached whenever enabled — a phone simply never fires mouseout.
    // Arms only after the admin-set delay on the page, so a visitor who just
    // landed and immediately moves the mouse up is not ambushed.
    if (cfg.desktop) {
        var deskArmed = cfg.delayMs <= 0;
        if (!deskArmed) setTimeout(function(){ deskArmed = true; }, cfg.delayMs);
        document.addEventListener('mouseout', function(e){
            if (deskArmed && !e.relatedTarget && e.clientY <= 8) show();
        });
    }

    // Mobile: catch the back button once — the first back opens the popup
    // (the pushed state pops, so the NEXT back leaves the site normally).
    // The history entry is pushed on the visitor's FIRST touch, not on a
    // timer: Chrome skips back-button entries that were added without a user
    // gesture, which silently disabled the popup for look-only visitors.
    if (isMobile && cfg.mobile) {
        var armOnce = function(){
            if (armed || shown) return;
            try { history.pushState({exitPopup: 1}, ''); armed = true; } catch(e) {}
        };
        ['touchend', 'mousedown', 'keydown', 'click'].forEach(function(ev){
            window.addEventListener(ev, armOnce, {passive: true, capture: true});
        });
        window.addEventListener('popstate', function(){
            if (armed && !shown) show();
        });
    }
})();
</script>
@endif
