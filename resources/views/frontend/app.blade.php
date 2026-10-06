<!DOCTYPE html>
<html lang="en">
@include('frontend.partials.head')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>

<body class="sticky-header" style="border: 1px solid darkgray; box-shadow: 0 0 12px rgb(0 0 0 / 42%);">

@php
    $gtm_id = $__gtmId ?? null;
    $information = \App\Models\Information::first();
    $sBrandGradient = $information->gradient_code ?? 'linear-gradient(135deg,#0d6efd,#00276C)';
    $sBrandText     = $information->primary_color ?? '#ffffff';
    $sScrollBg      = $information->floating_btn_bg_color ?? '#000000';
    $sScrollText    = $information->floating_btn_icon_color ?? '#ffffff';
    $sWaBg          = $information->whatsapp_btn_bg_color ?? '#25D366';
    $sWaText        = $information->whatsapp_btn_icon_color ?? '#ffffff';
@endphp

@if($gtm_id)
<noscript>
    <iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtm_id }}"
    height="0" width="0" style="display:none;visibility:hidden"></iframe>
</noscript>
@endif

@include('frontend.partials.header')

@yield('content')

<div class="modal fade" id="quick-view-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content" style="border-radius:16px; overflow:hidden;">

            <div class="modal-header" style="gap:10px;">
                <h5 class="modal-title"
                    style="font-family:'Hind Siliguri',sans-serif; font-weight:900; margin:0; flex:1; text-align:center;">
                    Quick View
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body" id="quickViewBody">
                <div class="text-center py-5">Loading...</div>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --scroller-gradient: {!! $sBrandGradient !!};
        --scroller-text: {{ $sBrandText }};
        --scroll-top-bg: {{ $sScrollBg }};
        --scroll-top-text: {{ $sScrollText }};
        --wa-fab-bg: {{ $sWaBg }};
        --wa-fab-text: {{ $sWaText }};
    }

    #quick-view-modal{ z-index:20000 !important; }
    .modal-backdrop{ z-index:19990 !important; }

    #quickViewBody{
        padding: 12px !important;
        max-height: calc(100vh - 160px);
        overflow: auto;
        -webkit-overflow-scrolling: touch;
        background: #f5f6fa;
    }

    #quickViewBody *{ max-width: 100%; box-sizing: border-box; }
    #quickViewBody img{ height:auto !important; max-width:100% !important; }
    #quickViewBody table{
        width:100% !important;
        display:block;
        overflow-x:auto;
    }

    .scroll-top, #scroll-top, .back-to-top, .custom-back-top, .custom-floating-wa { display: none !important; }

    .premium-float-stack {
        position: fixed;
        right: 22px;
        bottom: 28px;
        z-index: 999998;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 14px;
        pointer-events: none;
    }

    .premium-fab {
        position: relative;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none !important;
        overflow: visible;
        background: var(--scroller-gradient);
        color: var(--scroller-text) !important;
        box-shadow: 0 8px 22px rgba(0,0,0,.25);
        pointer-events: auto;
        transition: transform .5s cubic-bezier(.34,1.56,.64,1), box-shadow .35s ease, opacity .4s ease, visibility .4s ease;
    }

    .premium-fab::before {
        content: "";
        position: absolute;
        inset: -3px;
        border-radius: 50%;
        background: var(--scroller-gradient);
        z-index: -1;
        opacity: .5;
        animation: pfabPulse 2.2s ease-out infinite;
    }

    /* Back-to-top aar WhatsApp button — Primary Color theke alada, nijer own color */
    .premium-scroll-top,
    .premium-scroll-top::before { background: var(--scroll-top-bg); }
    .premium-scroll-top { color: var(--scroll-top-text) !important; }

    .premium-fab-wa,
    .premium-fab-wa::before { background: var(--wa-fab-bg); }
    .premium-fab-wa { color: var(--wa-fab-text) !important; }

    .premium-fab::after {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: 50%;
        background: radial-gradient(circle at 30% 25%, rgba(255,255,255,.35), transparent 55%);
        pointer-events: none;
        opacity: .85;
    }

    @keyframes pfabPulse {
        0%   { transform: scale(.95); opacity: .55; }
        70%  { transform: scale(1.4); opacity: 0; }
        100% { transform: scale(1.4); opacity: 0; }
    }

    .premium-fab .pfab-ring {
        position: absolute;
        inset: 4px;
        border-radius: 50%;
        border: 1.5px dashed rgba(255,255,255,.45);
        animation: pfabSpin 8s linear infinite;
        pointer-events: none;
        z-index: 1;
    }

    @keyframes pfabSpin {
        from { transform: rotate(0deg); }
        to   { transform: rotate(360deg); }
    }

    .premium-fab .pfab-icon {
        position: relative;
        z-index: 2;
        font-size: 17px;
        line-height: 1;
        transition: transform .35s cubic-bezier(.34,1.56,.64,1);
        animation: pfabFloat 2.4s ease-in-out infinite;
    }

    @keyframes pfabFloat {
        0%, 100% { transform: translateY(0); }
        50%      { transform: translateY(-3px); }
    }

    .premium-fab:hover {
        transform: translateY(-5px) scale(1.07);
        box-shadow: 0 14px 32px rgba(0,0,0,.32);
    }
    .premium-fab:hover::before { animation-duration: 1.6s; }
    .premium-fab:hover .pfab-icon { transform: translateY(-2px) scale(1.15); }
    .premium-fab:active {
        transform: translateY(-2px) scale(.95);
        transition-duration: .15s;
    }

    .premium-fab-wa {
        opacity: 0;
        visibility: hidden;
        transform: translateY(20px) scale(.85);
        animation: pfabIntro .7s cubic-bezier(.34,1.56,.64,1) .4s forwards;
    }
    .premium-fab-wa .pfab-icon { font-size: 22px; }
    .premium-fab-wa:hover .pfab-icon { transform: scale(1.15) rotate(-8deg); }

    @keyframes pfabIntro {
        to { opacity: 1; visibility: visible; transform: translateY(0) scale(1); }
    }

    .premium-scroll-top {
        opacity: 0;
        visibility: hidden;
        transform: translateY(20px) scale(.85);
    }

    .premium-scroll-top.is-visible {
        opacity: 1;
        visibility: visible;
        transform: translateY(0) scale(1);
    }

    .premium-scroll-top .pst-progress {
        position: absolute;
        inset: 0;
        z-index: 1;
        pointer-events: none;
        transform: rotate(-90deg);
    }
    .premium-scroll-top .pst-progress circle {
        fill: none;
        stroke-width: 3;
        stroke-linecap: round;
    }
    .premium-scroll-top .pst-progress .pst-track {
        stroke: rgba(255,255,255,.18);
    }
    .premium-scroll-top .pst-progress .pst-bar {
        stroke: rgba(255,255,255,.95);
        stroke-dasharray: 138;
        stroke-dashoffset: 138;
        transition: stroke-dashoffset .15s linear;
        filter: drop-shadow(0 0 4px rgba(255,255,255,.5));
    }

    .premium-scroll-top.is-clicked .pfab-icon {
        animation: pstLaunch .6s ease forwards;
    }

    @keyframes pstLaunch {
        0%   { transform: translateY(0); opacity: 1; }
        50%  { transform: translateY(-22px); opacity: 0; }
        51%  { transform: translateY(22px); opacity: 0; }
        100% { transform: translateY(0); opacity: 1; }
    }

    @media (max-width: 991px) {
        .premium-float-stack {
            right: 14px;
            bottom: 100px; /* 30px move up */
            gap: 10px;
        }
        .premium-fab {
            width: 50px; /* Icon size smaller */
            height: 50px; /* Icon size smaller */
        }
        .premium-fab .pfab-icon { font-size: 16px; }
        .premium-fab-wa .pfab-icon { font-size: 20px; }
    }

    body.hide-header .premium-float-stack { display: none !important; }
</style>

<div class="premium-float-stack">
    @if(isset($information) && $information->whats_active == '1' && !empty($information->whats_num))
        <a href="https://wa.me/+88{{ $information->whats_num }}" target="_blank" rel="noopener" class="premium-fab premium-fab-wa" aria-label="Chat on WhatsApp" title="Chat on WhatsApp">
            <span class="pfab-ring"></span>
            <i class="fab fa-whatsapp pfab-icon"></i>
        </a>
    @endif

    <button type="button" class="premium-fab premium-scroll-top" id="premiumScrollTop" aria-label="Scroll to top">
        <svg class="pst-progress" viewBox="0 0 50 50" aria-hidden="true">
            <circle class="pst-track" cx="25" cy="25" r="22"></circle>
            <circle class="pst-bar"   cx="25" cy="25" r="22"></circle>
        </svg>
        <span class="pfab-ring"></span>
        <i class="fas fa-arrow-up pfab-icon"></i>
    </button>
</div>

@include('frontend.partials.footer')

@include('frontend.partials.exit_popup')

{{-- MOBILE PERFORMANCE MODE: decorative animations & blur effects are the main
     cause of scroll jank on low-end phones, so they are switched off <= 991px.
     One-shot "forwards" animations (dockSlide, pfabIntro) still land on their
     final state because iteration-count 1 + near-zero duration completes them
     instantly — do not change that to animation:none. --}}
<style>
@media (max-width: 991.98px) {
    *, *::before, *::after {
        animation-duration: .01ms !important;
        animation-iteration-count: 1 !important;
        animation-delay: 0ms !important;
    }
    /* functional loading spinners must keep spinning */
    .fa-spin          { animation: fa-spin 2s linear infinite !important; }
    .spinner-border   { animation: spinner-border .75s linear infinite !important; }
    .spinner-grow     { animation: spinner-grow .75s linear infinite !important; }
    /* the announcement bar carries a message: its text must keep scrolling (a cheap transform-only animation) */
    .topbar.is-scrolling .notice-track {
        animation-duration: var(--notice-time, 30s) !important;
        animation-iteration-count: infinite !important;
    }

    /* backdrop blur re-renders on every scroll frame on fixed/sticky elements */
    *, *::before, *::after {
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }
    .footer-copy-row { background: rgba(0,0,0,.55) !important; }

    /* hero slider ken-burns zoom repaints a full-width image continuously */
    .main-swiper .swiper-slide-active img { animation: none !important; }

    /* scroll-top progress ring: drop-shadow made every scroll frame repaint */
    .premium-scroll-top .pst-progress .pst-bar { filter: none !important; }
}
</style>

<script>
(function(){
    if(window.__VIDEO_VIEW_PAUSE__) return;
    window.__VIDEO_VIEW_PAUSE__ = true;
    // autoplay videos keep decoding while off-screen; pause them until visible
    document.addEventListener('DOMContentLoaded', function(){
        var vids = document.querySelectorAll('video[autoplay]');
        if(!vids.length || !('IntersectionObserver' in window)) return;
        var io = new IntersectionObserver(function(entries){
            entries.forEach(function(en){
                var v = en.target;
                if(en.isIntersecting){ if(v.paused && v.play) v.play().catch(function(){}); }
                else if(!v.paused && v.pause){ v.pause(); }
            });
        }, { threshold: 0.15 });
        vids.forEach(function(v){ io.observe(v); });
    });
})();
</script>

<script>
(function(){
    if(window.__PREMIUM_SCROLLER__) return;
    window.__PREMIUM_SCROLLER__ = true;

    var btn = document.getElementById('premiumScrollTop');
    if(!btn) return;

    var bar = btn.querySelector('.pst-bar');
    var CIRC = 138;
    var ticking = false;

    function update(){
        var st = window.pageYOffset || document.documentElement.scrollTop || 0;
        var dh = (document.documentElement.scrollHeight || document.body.scrollHeight) - window.innerHeight;
        var ratio = dh > 0 ? Math.min(1, Math.max(0, st / dh)) : 0;

        if(st > 280) btn.classList.add('is-visible');
        else btn.classList.remove('is-visible');

        if(bar) bar.style.strokeDashoffset = String(CIRC - (CIRC * ratio));

        ticking = false;
    }

    function onScroll(){
        if(!ticking){
            window.requestAnimationFrame(update);
            ticking = true;
        }
    }

    function smoothScrollToTop(){
        var supportsSmooth = 'scrollBehavior' in document.documentElement.style;
        if(supportsSmooth){
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }
        var start = window.pageYOffset || document.documentElement.scrollTop;
        var startTime = null;
        var duration = 600;
        function easeOutCubic(t){ return 1 - Math.pow(1 - t, 3); }
        function step(ts){
            if(!startTime) startTime = ts;
            var p = Math.min((ts - startTime) / duration, 1);
            window.scrollTo(0, start * (1 - easeOutCubic(p)));
            if(p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    btn.addEventListener('click', function(e){
        e.preventDefault();
        btn.classList.add('is-clicked');
        smoothScrollToTop();
        setTimeout(function(){ btn.classList.remove('is-clicked'); }, 650);
    });

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);
    update();
})();
</script>

<script>
(function () {
    if(window.__QV_LOADER__) return;
    window.__QV_LOADER__ = true;

    let popupXhr = null;

    function setTitle(txt){
        const t = document.querySelector('#quick-view-modal .modal-title');
        if(t) t.textContent = txt || 'Quick View';
    }

    function setLoading(){
        setTitle('Quick View');
        var body = document.getElementById('quickViewBody');
        if(body) body.innerHTML = '<div class="text-center py-5">Loading...</div>';
    }

    function setHtml(html){
        var body = document.getElementById('quickViewBody');
        if(body) body.innerHTML = html;

        setTitle('Quick View');

        setTimeout(() => window.dispatchEvent(new Event('resize')), 60);

        var mb = document.getElementById('quickViewBody');
        if(mb) mb.scrollTop = 0;

        setTimeout(() => {
            if(window.__QV_APPLY_STOCK__) window.__QV_APPLY_STOCK__();
        }, 20);

        setTimeout(() => {
            if(window.__QV_INIT_POPUP__) window.__QV_INIT_POPUP__();
        }, 10);
    }

    function setError(){
        setTitle('Quick View');
        var body = document.getElementById('quickViewBody');
        if(body) body.innerHTML = '<div class="alert alert-danger m-0">Something went wrong!</div>';
    }

    function openModal(){
        var el = document.getElementById('quick-view-modal');
        if(!el) return;

        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(el).show();
            return;
        }

        if (window.jQuery && $('#quick-view-modal').modal) {
            $('#quick-view-modal').modal('show');
            return;
        }

        el.classList.add('show');
        el.style.display = 'block';
    }

    function loadPopupUrl(url){
        if(!url) return;

        setLoading();
        openModal();

        if (popupXhr && popupXhr.readyState !== 4) {
            try { popupXhr.abort(); } catch(e){}
        }

        if (window.jQuery && window.$ && $.ajax) {
            popupXhr = $.ajax({
                url: url,
                type: "GET",
                success: function (html) { setHtml(html); },
                error: function () { setError(); }
            });
            return;
        }

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => setHtml(html))
            .catch(() => setError());
    }

    function loadPopupById(id){
        if(!id) return;
        const url = "{{ url('/product-popup') }}/" + id;
        loadPopupUrl(url);
    }

    document.addEventListener('click', function(e){
        const el = e.target.closest('.openProductPopup');
        if(!el) return;
        e.preventDefault();
        loadPopupById(el.getAttribute('data-id'));
    }, true);

    document.addEventListener('click', function(e){
        const el = e.target.closest('.js-quickview-open');
        if(!el) return;

        e.preventDefault();
        e.stopPropagation();

        const url = el.getAttribute('data-popup-url');
        if(url) { loadPopupUrl(url); return; }

        loadPopupById(el.getAttribute('data-id'));
    }, true);

})();
</script>

<script>
(function(){
  if(window.__POPUP_CART_FIX__) return;
  window.__POPUP_CART_FIX__ = true;

  function hasJQ(){ return (window.jQuery && window.$ && typeof $.ajax === 'function'); }

  function toast(type,msg){
    if(window.toastr){ toastr.clear(); toastr[type](msg); }
    else alert(msg);
  }

  function getBox(){
    return document.querySelector('#quickViewBody .pmodal[data-popup="1"]');
  }

  function parseVarMap(box){
    try{
      const el = box.querySelector('#pmodalVarJson');
      return el ? JSON.parse(el.textContent || '{}') : {};
    }catch(e){ return {}; }
  }

  function curr(box){
    return box.getAttribute('data-currency') || 'BDT';
  }

  function moneyText(c, val){
    val = parseFloat(val || 0);
    if(isNaN(val)) val = 0;
    if(c === 'BDT') return '৳ ' + Math.round(val);
    if(c === 'Dollar') return '$ ' + (Math.round(val*100)/100).toFixed(2);
    if(c === 'Euro') return '€ ' + (Math.round(val*100)/100).toFixed(2);
    return String(val);
  }

  function resolve(varMap, sizeId, colorId){
    const key = String(sizeId) + '|' + String(colorId);
    return varMap && varMap[key] ? varMap[key] : null;
  }

  function setWaiting(box){
    box.__ACTIVE_VAR__ = null;

    const label = box.querySelector('#pmodal_variant_label');
    if(label) label.textContent = 'Size এবং Color সিলেক্ট করুন';

    box.querySelectorAll('.js-popup-cart, .js-popup-order').forEach(b => b.disabled = true);

    const badge = box.querySelector('#pmodal_stock_badge');
    if(badge){
      badge.classList.remove('green','red');
      badge.classList.add('red');
      badge.textContent = '0 Items left';
    }
  }

  function setInvalid(box){
    box.__ACTIVE_VAR__ = null;

    const sizeTxt  = box.querySelector('#pmodalSizeOptions .pmodal-size-opt.active')?.textContent.trim() || '';
    const colorTxt = box.querySelector('#pmodalColorOptions .pmodal-color-opt.active')?.textContent.trim() || '';

    const label = box.querySelector('#pmodal_variant_label');
    if(label) label.textContent = (sizeTxt + ' - ' + colorTxt + ' (Not available)').trim();

    const badge = box.querySelector('#pmodal_stock_badge');
    if(badge){
      badge.classList.remove('green','red');
      badge.classList.add('red');
      badge.textContent = '0 Items left';
    }

    box.querySelectorAll('.js-popup-cart, .js-popup-order').forEach(b => b.disabled = true);

    toast('warning','❌ এই Size-Color কম্বিনেশনটি নেই, অন্যটি সিলেক্ট করুন');
  }

  function setDiscountUI(box, c, raw, price){
    const oldEl  = box.querySelector('#pmodal_old_price');
    const discEl = box.querySelector('#pmodal_disc_badge');

    raw = parseFloat(raw || 0);
    price = parseFloat(price || 0);

    if(raw > price && raw > 0 && price > 0){
      if(oldEl){ oldEl.style.display=''; oldEl.textContent = moneyText(c, raw); }
      if(discEl){
        const percent = Math.round(((raw - price)/raw)*100);
        discEl.style.display='';
        discEl.textContent = percent + '% Off';
      }
    }else{
      if(oldEl){ oldEl.style.display='none'; oldEl.textContent=''; }
      if(discEl){ discEl.style.display='none'; discEl.textContent=''; }
    }
  }

  function applyVar(box, v){
    box.__ACTIVE_VAR__ = v;

    const c = curr(box);

    const labelTxt = (v.size || '') + (v.color && v.color !== 'Default' ? (' - ' + v.color) : '');
    const label = box.querySelector('#pmodal_variant_label');
    if(label) label.textContent = labelTxt;

    const finalEl = box.querySelector('#pmodal_final_price');
    if(finalEl) finalEl.textContent = moneyText(c, v.price);

    setDiscountUI(box, c, v.raw, v.price);

    const vid = box.querySelector('#pmodal_variation_id'); if(vid) vid.value = v.id;

    const vn  = box.querySelector('#pmodal_variant_name'); if(vn) vn.value = labelTxt;

    const sv  = box.querySelector('#pmodal_size_value'); if(sv) sv.value = labelTxt;
    const sv1 = box.querySelector('#pmodal_size_value1'); if(sv1) sv1.value = labelTxt;

    const pv  = box.querySelector('#pmodal_price_val'); if(pv) pv.value = v.price;
    const pv1 = box.querySelector('#pmodal_price_val1'); if(pv1) pv1.value = v.price;

    const stock = parseInt(v.stock || 0);

    const badge = box.querySelector('#pmodal_stock_badge');
    if(badge){
      badge.classList.remove('green','red');
      badge.classList.add(stock > 0 ? 'green' : 'red');
      badge.textContent = (stock > 0 ? stock : 0) + ' Items left';
    }

    box.querySelectorAll('.js-popup-cart, .js-popup-order').forEach(b => b.disabled = (stock <= 0));
  }

  window.__QV_INIT_POPUP__ = function(){
    const box = getBox();
    if(!box) return;

    const varMap = parseVarMap(box);
    const keys = Object.keys(varMap || {});

    if(!keys.length){
      return;
    }

    let defSize  = parseInt(box.getAttribute('data-def-size') || 0);
    let defColor = parseInt(box.getAttribute('data-def-color') || 0);

    let v = null;

    if(defSize || defColor){
      v = resolve(varMap, defSize, defColor);
    }

    if(!v){
      v = varMap[keys[0]];
      defSize  = v.size_id;
      defColor = v.color_id;
    }

    box.querySelectorAll('#pmodalSizeOptions .pmodal-size-opt').forEach(x=>x.classList.remove('active'));
    box.querySelectorAll('#pmodalColorOptions .pmodal-color-opt').forEach(x=>x.classList.remove('active'));

    const sBtn = box.querySelector('#pmodalSizeOptions .pmodal-size-opt[data-size-id="'+defSize+'"]');
    const cBtn = box.querySelector('#pmodalColorOptions .pmodal-color-opt[data-color-id="'+defColor+'"]');

    if(sBtn) sBtn.classList.add('active');
    if(cBtn) cBtn.classList.add('active');

    applyVar(box, v);
  };

  document.addEventListener('click', function(e){
    const th = e.target.closest('#pmodalThumbs .pmodal-thumb');
    if(!th) return;

    const box = getBox();
    if(!box) return;

    const wrap = th.closest('#pmodalThumbs');
    const main = box.querySelector('#pmodalMainImg');
    if(!wrap || !main) return;

    wrap.querySelectorAll('.pmodal-thumb').forEach(x=>x.classList.remove('active'));
    th.classList.add('active');

    const src = th.getAttribute('data-src');
    if(src) main.src = src;
  }, true);

  document.addEventListener('click', function(e){
    const box = getBox();
    if(!box) return;

    const btn = e.target.closest('#pmodalSizeOptions .pmodal-size-opt');
    if(!btn) return;

    box.querySelectorAll('#pmodalSizeOptions .pmodal-size-opt').forEach(x=>x.classList.remove('active'));
    btn.classList.add('active');

    const sizeId = parseInt(btn.getAttribute('data-size-id') || 0);
    const colorEl = box.querySelector('#pmodalColorOptions .pmodal-color-opt.active');
    if(!colorEl){ setWaiting(box); return; }

    const colorId = parseInt(colorEl.getAttribute('data-color-id') || 0);
    const v = resolve(parseVarMap(box), sizeId, colorId);
    if(v) applyVar(box, v); else setInvalid(box);
  }, true);

  document.addEventListener('click', function(e){
    const box = getBox();
    if(!box) return;

    const btn = e.target.closest('#pmodalColorOptions .pmodal-color-opt');
    if(!btn) return;

    box.querySelectorAll('#pmodalColorOptions .pmodal-color-opt').forEach(x=>x.classList.remove('active'));
    btn.classList.add('active');

    const colorId = parseInt(btn.getAttribute('data-color-id') || 0);
    const sizeEl  = box.querySelector('#pmodalSizeOptions .pmodal-size-opt.active');
    if(!sizeEl){ setWaiting(box); return; }

    const sizeId  = parseInt(sizeEl.getAttribute('data-size-id') || 0);
    const v = resolve(parseVarMap(box), sizeId, colorId);
    if(v) applyVar(box, v); else setInvalid(box);
  }, true);

  document.addEventListener('click', function(e){
    const box = getBox();
    if(!box) return;

    const plus = e.target.closest('.pmodal .quantity .plus');
    const minus = e.target.closest('.pmodal .quantity .minus');
    if(!plus && !minus) return;

    const input = box.querySelector('.quantity input[name="quantity"]');
    if(!input) return;

    const minQty = parseInt(input.dataset.min || '1') || 1;
    let v = Math.max(minQty, parseInt(input.value || '1') || 1);
    if(plus) v++;
    if(minus) v = Math.max(minQty, v-1);
    input.value = v;
  }, true);

  document.addEventListener('click', function(e){
    const box = getBox();
    if(!box) return;

    const cartBtn  = e.target.closest('.js-popup-cart');
    const orderBtn = e.target.closest('.js-popup-order');
    if(!cartBtn && !orderBtn) return;

    if(!box.__ACTIVE_VAR__){
      toast('warning','দয়া করে Size এবং Color সিলেক্ট করুন!');
      return;
    }

    if(parseInt(box.__ACTIVE_VAR__.stock || 0) <= 0){
      toast('warning','❌ এই ভ্যারিয়েশনটি স্টকে নেই');
      return;
    }

    const at = box.querySelector('#popup_action_type');
    if(at) at.value = cartBtn ? 'cart' : 'order';

    const form = box.querySelector('#cart_submit_popup');
    if(form) form.dispatchEvent(new Event('submit', {cancelable:true, bubbles:true}));
  }, true);

  document.addEventListener('submit', function(e){
    const form = e.target;
    if(!form || form.id !== 'cart_submit_popup') return;

    const box = getBox();
    if(!box) return;

    if(!box.__ACTIVE_VAR__){
      e.preventDefault();
      toast('warning','দয়া করে Size এবং Color সিলেক্ট করুন!');
      return;
    }

    const actionType = box.querySelector('#popup_action_type')?.value || 'cart';

    const qtyInput = box.querySelector('.quantity input[name="quantity"]');
    const q = Math.max(parseInt(qtyInput?.dataset.min || '1') || 1, parseInt(qtyInput?.value || '1') || 1);
    if(qtyInput) qtyInput.value = q;

    if(!hasJQ()){
      return;
    }

    e.preventDefault();

    $.ajax({
      url: form.action,
      method: 'POST',
      data: $(form).serialize(),
      success: function(res){
        if(res && res.success){
          toast('success', res.msg || 'Added');

          window.dataLayer = window.dataLayer || [];
          dataLayer.push({
            event: "add_to_cart",
            ecommerce: {
                currency: "BDT",
                value: parseFloat(box.__ACTIVE_VAR__.price),
                items: [{
                    item_id: box.__ACTIVE_VAR__.id,
                    item_name: box.__ACTIVE_VAR__.name || box.querySelector('#pmodal_variant_name')?.value || 'Product',
                    price: parseFloat(box.__ACTIVE_VAR__.price),
                    quantity: q
                }]
            }
          });

          if(res.view) $('#cart_section').html(res.view);
          if(res.item !== undefined) $('.cart-count').text(res.item);
          if(res.amount !== undefined && res.amount !== null) $('.cart-amount').text('৳ '+res.amount);

          if(actionType === 'order'){
            window.location.href = (res.url ? res.url : "{{ url('/checkouts') }}");
            return;
          }

          const btn = document.querySelector('.cart-dropdown-btn');
          if(btn) btn.click();
        }else{
          toast('error', (res && res.msg) ? res.msg : 'Something went wrong!');
        }
      },
      error: function(){ toast('error','Something went wrong!'); }
    });
  }, true);

})();
</script>

{{-- @stack('js') lives in frontend/partials/js.blade.php (pulled in by the footer),
     after jQuery loads. A second @stack here rendered every pushed block twice. --}}

</body>
</html>