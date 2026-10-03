@php
use App\Models\Information;
use App\Models\Category;
$information = Information::first();
$categories  = Category::whereNull('parent_id')->where('is_menu', 1)->with('subcats')->get();
$brandGradient = $information->gradient_code ?? 'linear-gradient(90deg,#0d6efd,#00276C)';
$brandText     = $information->primary_color ?? '#ffffff';
$topbarBg      = $information->topbar_bg_color ?? '#000000';
$topbarText    = $information->topbar_text_color ?? '#ffffff';
$floatingBtnBg   = $information->floating_btn_bg_color ?? '#000000';
$floatingBtnIcon = $information->floating_btn_icon_color ?? '#ffffff';
$waBtnBg         = $information->whatsapp_btn_bg_color ?? '#25D366';
$waBtnIcon       = $information->whatsapp_btn_icon_color ?? '#ffffff';
@endphp
<style>
:root {
--brand-gradient: {!! $brandGradient !!};
--brand-text: {{ $brandText }};
--topbar-bg: {{ $topbarBg }};
--topbar-text: {{ $topbarText }};
--floating-btn-bg: {{ $floatingBtnBg }};
--floating-btn-icon: {{ $floatingBtnIcon }};
--wa-btn-bg: {{ $waBtnBg }};
--wa-btn-icon: {{ $waBtnIcon }};
}
.main-bg {
background: var(--brand-gradient) !important;
border-color: rgba(32, 124, 202, 1);
color: var(--brand-text) !important;
}
.main-bg:not(.bg_alt), .main-bg:not(.bg_alt) a, .main-bg:not(.bg_alt) p, .main-bg:not(.bg_alt) span, .main-bg:not(.bg_alt) i {
color: var(--brand-text) !important;
}
.main-bg .bg_alt *, .main-bg .bg_alt, .main-bg .bg_alt a, .main-bg .bg_alt p, .main-bg .bg_alt span {
color: #000 !important;
}
body { font-family: 'Hind Siliguri', sans-serif; }
.topbar { overflow: hidden; height: 35px; background: var(--topbar-bg); border-bottom: 1px solid rgba(255,255,255,0.1); }
.topbar .container { height: 100%; display: flex; align-items: center; }
.topbar-notice { position: relative; width: 100%; height: 100%; display: flex; align-items: center; overflow: hidden; white-space: nowrap; }
.topbar-notice .notice-track { display: inline-flex; align-items: center; gap: 40px; will-change: transform; animation: noticeScroll 90s linear infinite; padding-left: 100%; }
.topbar-notice:hover .notice-track { animation-play-state: paused; }
.topbar-notice .notice-text { font-size: 13px; font-weight: 500; color: var(--topbar-text); letter-spacing: 0.5px; text-transform: uppercase; }
@keyframes noticeScroll {
0%   { transform: translateX(0); }
100% { transform: translateX(-100%); }
}
.axil-mainmenu-desktop {
transition: all 0.3s ease;
width: 100%;
border-bottom: none;
box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}
.header-navbar-clean {
display: flex;
align-items: center;
justify-content: space-between;
height: 75px;
width: 100%;
}
.desktop-logo-clean { flex: 0 0 15%; display: flex; align-items: center; }
.desktop-logo-clean img { max-height: 45px; object-fit: contain; }
/* Many menu items: the bar scrolls sideways instead of overflowing the header.
   margin:auto (not justify-content:center) keeps the first items reachable. */
.desktop-menu-clean {
flex: 1; min-width: 0; display: flex; margin: 0 15px;
overflow-x: auto; overflow-y: hidden;
scrollbar-width: thin; scrollbar-color: rgba(128,128,128,.45) transparent;
}
.desktop-menu-clean::-webkit-scrollbar { height: 4px; }
.desktop-menu-clean::-webkit-scrollbar-track { background: transparent; }
.desktop-menu-clean::-webkit-scrollbar-thumb { background: rgba(128,128,128,.45); border-radius: 4px; }
.nav-menu-clean { display: flex; gap: 25px; list-style: none; margin: 0 auto; padding: 0; align-items: center; }
.nav-item-clean { position: relative; flex-shrink: 0; }
.nav-link-clean {
font-size: 14px; font-weight: 600; text-transform: uppercase;
color: var(--brand-text) !important;
text-decoration: none !important; letter-spacing: 0.5px; padding: 27px 0;
display: inline-flex; align-items: center; transition: opacity 0.3s ease;
white-space: nowrap;
}
.nav-link-clean:hover { opacity: 0.7; }
.nav-item-clean.has-sub > .nav-link-clean::after {
content: "\f107"; font-family: "Font Awesome 5 Free", "Font Awesome 5 Pro", sans-serif; font-weight: 900;
font-size: 12px; margin-left: 6px; opacity: 0.8; color: var(--brand-text) !important;
}

.axil-submenu-clean {
position: absolute; top: 100%; left: 0; min-width: 220px;
background: var(--brand-gradient) !important;
border: 1px solid rgba(255, 255, 255, 0.1); 
box-shadow: 0 10px 30px rgba(0,0,0,0.15);
list-style: none; padding: 10px 0; margin: 0;
opacity: 0; visibility: hidden; transform: translateY(10px);
transition: all 0.3s ease; z-index: 99999;
}
.nav-item-clean:hover .axil-submenu-clean { opacity: 1; visibility: visible; transform: translateY(0); }
.axil-submenu-clean li a { 
display: block; padding: 10px 20px; 
color: var(--brand-text) !important;
font-size: 14px; font-weight: 500; text-decoration: none; transition: 0.3s; 
border-bottom: 1px solid rgba(255, 255, 255, 0.1); 
text-transform: capitalize; 
}
.axil-submenu-clean li:last-child a { border-bottom: none; }
.axil-submenu-clean li a:hover { 
color: var(--brand-text) !important; 
background: rgba(0, 0, 0, 0.1); 
padding-left: 25px; font-weight: 600; 
}

.desktop-icons-clean { flex: 0 0 20%; display: flex; justify-content: flex-end; align-items: center; }
.action-list-clean { display: flex; gap: 18px; list-style: none; margin: 0; padding: 0; align-items: center; }
.action-list-clean a {
background: transparent !important; border: none !important; box-shadow: none !important;
color: var(--brand-text) !important; font-size: 20px !important; text-decoration: none !important;
position: relative !important; display: flex !important; align-items: center !important; justify-content: center !important;
transition: transform 0.3s ease !important;
}
.action-list-clean a:hover { opacity: 0.7; transform: translateY(-2px) !important; }
.custom-cart-badge {
position: absolute !important; top: -6px !important; right: -8px !important;
background: #000 !important; color: #ffffff !important;
font-size: 11px !important; font-weight: 800 !important; width: 18px !important; height: 18px !important;
display: flex !important; align-items: center !important; justify-content: center !important;
border-radius: 50% !important; box-shadow: 0 2px 5px rgba(0,0,0,0.2) !important; line-height: 1 !important; z-index: 10 !important;
}
.desktop-search-wrapper { position: absolute; top: 100%; right: 15px; width: 320px; background: #fff; padding: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border-radius: 0 0 8px 8px; z-index: 99999; display: none; border-top: 3px solid var(--brand-gradient); }
.desktop-search-wrapper.active { display: block; animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }

@media (max-width: 991px) {
.desktop { display: none !important; }
.axil-mainmenu-mobile { padding: 10px 0; border-bottom: none; box-shadow: 0 2px 10px rgba(0,0,0,0.05); width: 100%; transition: all 0.3s ease; position: relative;}
.mobile-header-navbar { display: flex; align-items: center; justify-content: space-between; padding: 0 !important; } 
.mobile-logo img { max-height: 35px; }
.mobile-nav-toggler { background: transparent !important; border: none !important; font-size: 24px !important; padding: 5px !important; color: var(--brand-text) !important; box-shadow: none !important; margin: 0 !important; outline: none;}
.mobile-icons a { background: transparent !important; border: none !important; box-shadow: none !important; color: var(--brand-text) !important; font-size: 20px; padding: 5px !important; margin: 0 !important;}
.mobile-top-search-outside { display: none; position: absolute; top: 100%; left: 0; width: 100%; padding: 12px 15px; background: #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.1); z-index: 99999; border-top: 2px solid var(--brand-gradient); }
.mobile-top-search-outside.active { display: block; animation: fadeIn 0.3s ease; }
.custom-back-top { display: none !important; }
.custom-floating-wa { bottom: 150px !important; right: 10px !important; } 
}
@media (min-width: 992px) { .mobile { display: none !important; } .mobile-top-search-outside { display: none !important; } }

.search-box { position: relative; width: 100%; display: block; }
.search-box input { 
    width: 100%; 
    height: 42px; 
    padding: 0 45px 0 15px;
    border-radius: 30px; 
    border: 1px solid #ddd; 
    font-size: 13px; 
    outline: none; 
    transition: 0.3s; 
    background: #fff;
    color: #000;
}
.search-box input:focus { border-color: #000; }
.search-box button { 
    position: absolute; 
    right: 4px; 
    top: 50%; 
    transform: translateY(-50%); 
    background: var(--brand-gradient); 
    border: none; 
    color: var(--brand-text); 
    font-size: 13px; 
    height: 34px; 
    width: 34px; 
    border-radius: 50%; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    cursor: pointer;
    z-index: 5;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* ===========================================================
   🎨 PREMIUM MOBILE MENU — Brand New Design
   =========================================================== */
.premium-mobile-menu {
    width: 310px !important;
    z-index: 999999;
    border: none !important;
    background: #ffffff !important;
    box-shadow: 8px 0 30px rgba(0,0,0,.15) !important;
}

/* ── HEADER (Brand gradient hero) ─────────────────────────── */
.pmm-header {
    position: relative;
    background: var(--brand-gradient);
    padding: 22px 18px 50px;
    overflow: hidden;
}
.pmm-header::before {
    content:"";
    position: absolute;
    top: -60px; right: -60px;
    width: 180px; height: 180px;
    background: rgba(255,255,255,.08);
    border-radius: 50%;
}
.pmm-header::after {
    content:"";
    position: absolute;
    bottom: -40px; left: -40px;
    width: 120px; height: 120px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}
.pmm-close {
    position: absolute;
    top: 12px; right: 12px;
    width: 32px; height: 32px;
    border-radius: 50%;
    background: rgba(255,255,255,.18) !important;
    border: 1px solid rgba(255,255,255,.25) !important;
    color: var(--brand-text) !important;
    display: flex !important;
    align-items: center; justify-content: center;
    cursor: pointer;
    transition: all .3s ease;
    z-index: 5;
    backdrop-filter: blur(6px);
}
.pmm-close:hover { background: rgba(255,255,255,.30) !important; transform: rotate(90deg); }
.pmm-close i { font-size: 13px; }

.pmm-logo {
    display: block;
    margin-bottom: 16px;
    position: relative;
    z-index: 2;
}
.pmm-logo img { max-height: 38px; filter: drop-shadow(0 2px 6px rgba(0,0,0,.15)); }

.pmm-user-card {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(255,255,255,.15);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,.20);
    padding: 10px 12px;
    border-radius: 14px;
    text-decoration: none;
    transition: all .3s ease;
}
.pmm-user-card:hover {
    background: rgba(255,255,255,.25);
    transform: translateY(-2px);
}
.pmm-avatar {
    width: 42px; height: 42px;
    border-radius: 50%;
    background: rgba(255,255,255,.95);
    color: #0f172a;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    font-weight: 800;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(0,0,0,.12);
}
.pmm-user-info { flex: 1; min-width: 0; }
.pmm-welcome {
    display: block;
    font-size: 11px;
    color: var(--brand-text);
    opacity: .85;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.pmm-username {
    display: block;
    font-size: 15px;
    color: var(--brand-text) !important;
    font-weight: 700;
    margin-top: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-decoration: none !important;
}
.pmm-user-card i.fa-chevron-right {
    color: var(--brand-text);
    opacity: .7;
    font-size: 12px;
}

/* ── QUICK ACTION GRID ──────────────────────────────────────── */
.pmm-quick-grid {
    margin-top: -32px;
    margin-left: 14px;
    margin-right: 14px;
    background: #ffffff;
    border-radius: 16px;
    padding: 12px 8px;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 4px;
    box-shadow: 0 10px 30px rgba(0,0,0,.10);
    border: 1px solid rgba(15,23,42,.04);
    position: relative;
    z-index: 5;
}
.pmm-quick-item {
    display: flex !important;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 8px 4px;
    border-radius: 10px;
    text-decoration: none !important;
    color: #0f172a !important;
    transition: all .3s cubic-bezier(.34,1.56,.64,1);
    text-align: center;
}
.pmm-quick-item:hover, .pmm-quick-item:active {
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    transform: translateY(-2px);
}
.pmm-quick-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    background: var(--brand-gradient);
    color: var(--brand-text);
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    box-shadow: 0 4px 10px rgba(15,23,42,.10);
    transition: transform .3s cubic-bezier(.34,1.56,.64,1);
}
.pmm-quick-item:hover .pmm-quick-icon {
    transform: scale(1.12) rotate(-5deg);
}
.pmm-quick-label {
    font-size: 10.5px;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: .2px;
    line-height: 1.2;
}

/* ── SECTION TITLE ──────────────────────────────────────────── */
.pmm-section-title {
    padding: 18px 18px 8px;
    font-size: 11px;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.pmm-section-title::before {
    content:"";
    width: 4px;
    height: 14px;
    background: var(--brand-gradient);
    border-radius: 999px;
}

/* ── CATEGORY LIST ──────────────────────────────────────────── */
.pmm-body {
    flex: 1;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 100px;
}
.pmm-body::-webkit-scrollbar { width: 4px; }
.pmm-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }

.pmm-cat-list {
    list-style: none;
    margin: 0;
    padding: 0 14px;
}
.pmm-cat-item {
    margin-bottom: 6px;
    border-radius: 12px;
    overflow: hidden;
    background: #f8fafc;
    border: 1px solid rgba(15,23,42,.04);
    transition: all .3s ease;
}
.pmm-cat-item:hover {
    background: #ffffff;
    border-color: rgba(13,110,253,.15);
    box-shadow: 0 4px 12px rgba(15,23,42,.05);
}
.pmm-cat-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 11px 12px;
}
.pmm-cat-icon {
    width: 32px; height: 32px;
    border-radius: 9px;
    background: #ffffff;
    color: #0f172a;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
    border: 1px solid rgba(15,23,42,.06);
    transition: all .3s ease;
}
.pmm-cat-item:hover .pmm-cat-icon {
    background: var(--brand-gradient);
    color: var(--brand-text);
    border-color: transparent;
    transform: scale(1.08);
}
.pmm-cat-link {
    flex: 1;
    color: #0f172a !important;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none !important;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.3;
}
.pmm-cat-arrow {
    width: 28px; height: 28px;
    border-radius: 8px;
    background: transparent;
    border: 0;
    color: #64748b;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px;
    cursor: pointer;
    transition: all .3s ease;
    flex-shrink: 0;
}
.pmm-cat-arrow:not(.collapsed) {
    background: var(--brand-gradient);
    color: var(--brand-text);
    transform: rotate(180deg);
}
.pmm-cat-arrow.collapsed { transform: rotate(0); }

.pmm-subcat-list {
    list-style: none;
    margin: 0;
    padding: 0 12px 8px 50px;
}
.pmm-subcat-list li { margin: 2px 0; }
.pmm-subcat-list a {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 7px 10px;
    color: #475569 !important;
    text-decoration: none !important;
    font-size: 13px;
    font-weight: 500;
    border-radius: 8px;
    transition: all .25s ease;
}
.pmm-subcat-list a::before {
    content:"";
    width: 5px; height: 5px;
    background: #cbd5e1;
    border-radius: 50%;
    flex-shrink: 0;
    transition: all .25s ease;
}
.pmm-subcat-list a:hover {
    background: #ffffff;
    color: #0d6efd !important;
    padding-left: 14px;
}
.pmm-subcat-list a:hover::before {
    background: #0d6efd;
    transform: scale(1.4);
}

/* ── FOOTER (sticky contact card) ────────────────────────── */
.pmm-footer {
    position: absolute;
    left: 0; right: 0; bottom: 0;
    padding: 12px 14px;
    background: #ffffff;
    border-top: 1px solid rgba(15,23,42,.06);
    box-shadow: 0 -4px 20px rgba(0,0,0,.06);
    z-index: 5;
}
.pmm-contact {
    display: flex !important;
    align-items: center;
    gap: 12px;
    background: var(--brand-gradient);
    padding: 10px 14px;
    border-radius: 12px;
    text-decoration: none !important;
    color: var(--brand-text) !important;
    transition: all .3s ease;
    box-shadow: 0 6px 18px rgba(0,0,0,.08);
    position: relative;
    overflow: hidden;
}
.pmm-contact::before {
    content:"";
    position: absolute; top: 0; left: -100%;
    width: 60%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.25), transparent);
    transform: skewX(-20deg);
    transition: left .8s ease;
}
.pmm-contact:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(0,0,0,.12); }
.pmm-contact:hover::before { left: 200%; }
.pmm-contact-icon {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: rgba(255,255,255,.22);
    display: flex; align-items: center; justify-content: center;
    color: var(--brand-text);
    flex-shrink: 0;
    font-size: 14px;
    animation: pmmRing 1.8s ease-in-out infinite;
}
@keyframes pmmRing {
    0%, 100% { transform: rotate(0); }
    10%, 30% { transform: rotate(-12deg); }
    20%, 40% { transform: rotate(12deg); }
    50% { transform: rotate(0); }
}
.pmm-contact-text { flex: 1; min-width: 0; line-height: 1.2; }
.pmm-contact-text small {
    display: block;
    font-size: 10px;
    opacity: .85;
    text-transform: uppercase;
    letter-spacing: .5px;
    font-weight: 500;
}
.pmm-contact-text strong {
    display: block;
    font-size: 14px;
    font-weight: 800;
    margin-top: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ── Backdrop animation ───────────────────────────────────── */
.offcanvas-backdrop.show { opacity: .55; backdrop-filter: blur(2px); }

/* ── Stagger entrance for cat items ───────────────────────── */
.premium-mobile-menu.show .pmm-cat-item {
    animation: pmmSlideIn .4s ease both;
}
.premium-mobile-menu.show .pmm-cat-item:nth-child(1) { animation-delay: .08s; }
.premium-mobile-menu.show .pmm-cat-item:nth-child(2) { animation-delay: .12s; }
.premium-mobile-menu.show .pmm-cat-item:nth-child(3) { animation-delay: .16s; }
.premium-mobile-menu.show .pmm-cat-item:nth-child(4) { animation-delay: .20s; }
.premium-mobile-menu.show .pmm-cat-item:nth-child(5) { animation-delay: .24s; }
.premium-mobile-menu.show .pmm-cat-item:nth-child(6) { animation-delay: .28s; }
.premium-mobile-menu.show .pmm-cat-item:nth-child(n+7) { animation-delay: .32s; }
@keyframes pmmSlideIn {
    from { opacity: 0; transform: translateX(-15px); }
    to { opacity: 1; transform: translateX(0); }
}
.premium-mobile-menu.show .pmm-quick-item {
    animation: pmmFadeUp .4s ease both;
}
.premium-mobile-menu.show .pmm-quick-item:nth-child(1) { animation-delay: .15s; }
.premium-mobile-menu.show .pmm-quick-item:nth-child(2) { animation-delay: .20s; }
.premium-mobile-menu.show .pmm-quick-item:nth-child(3) { animation-delay: .25s; }
.premium-mobile-menu.show .pmm-quick-item:nth-child(4) { animation-delay: .30s; }
@keyframes pmmFadeUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 360px) {
    .premium-mobile-menu { width: 285px !important; }
    .pmm-quick-label { font-size: 9.5px; }
}

.custom-floating-wa {
position: fixed !important;
right: 20px !important;
bottom: 175px !important; 
width: 45px !important;
height: 45px !important;
border-radius: 50% !important;
display: flex !important;
align-items: center !important;
justify-content: center !important;
background: var(--wa-btn-bg) !important;
color: var(--wa-btn-icon) !important;
box-shadow: 0 4px 15px rgba(0,0,0,0.2) !important;
text-decoration: none !important;
z-index: 999999 !important;
transition: all 0.3s ease !important;
border: 1px solid rgba(255,255,255,0.2) !important;
}
.custom-floating-wa::before {
content: '';
position: absolute;
top: 0; left: 0; right: 0; bottom: 0;
border-radius: 50%;
background: var(--wa-btn-bg);
z-index: -1;
opacity: 0.6;
animation: pulse-ring 2s infinite cubic-bezier(0.215, 0.61, 0.355, 1);
}
@keyframes pulse-ring {
0% { transform: scale(0.95); opacity: 0.7; }
50% { opacity: 0.4; }
100% { transform: scale(1.6); opacity: 0; }
}
.custom-floating-wa:hover {
transform: scale(1.1) !important;
filter: brightness(1.08);
}
.custom-floating-wa i { font-size: 24px !important; z-index: 2; }
.scroll-top, #scroll-top, .back-to-top:not(.custom-back-top) { display: none !important; }
.custom-back-top {
position: fixed !important;
bottom: 25px !important;
right: 20px !important;
width: 42px !important;
height: 42px !important;
border-radius: 8px !important;
display: flex !important;
align-items: center !important;
justify-content: center !important;
background: var(--floating-btn-bg) !important;
color: var(--floating-btn-icon) !important;
box-shadow: 0 4px 15px rgba(0,0,0,0.2) !important;
text-decoration: none !important;
z-index: 999998 !important;
opacity: 0;
visibility: hidden;
transition: all 0.3s ease !important;
border: 1px solid rgba(255,255,255,0.1) !important;
}
.custom-back-top.show-btn {
opacity: 1;
visibility: visible;
transform: translateY(0);
}
.custom-back-top:hover {
transform: translateY(-5px) !important;
background: var(--brand-gradient) !important;
color: var(--brand-text) !important;
}
.custom-back-top i { font-size: 16px !important; }

body.hide-header .axil-header,
body.hide-header .topbar {
    display: none !important;
}

#toast-container > div {
    max-width: 250px !important;
    padding: 10px 15px 10px 45px !important;
    border-radius: 6px !important;
    min-height: 40px !important;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important;
}
.toast-message {
    font-size: 13px !important;
    font-weight: 500 !important;
    line-height: 1.4 !important;
}
.toast-title {
    font-size: 14px !important;
    font-weight: bold !important;
}

.cart-dropdown .cart-content-wrap {
    width: 320px !important;
}

/* এই ৩২০px প্যানেলে থিমের কার্ট-আইটেম মার্কআপ আঁটে না: .item-quantity (৮৮px)
   absolute হয়ে টাইটেলের উপরেই বসে, ফলে নামের জন্য ~৩২px পড়ে থাকত আর ফ্লেক্স
   .item-img কে চিপে ৪৮px করে ফেলত (থাম্বনেইল সরু দাগ)। ছবির মাপ পাকা করে,
   কোয়ান্টিটিটা টাইটেলের নিচে নামিয়ে দিলে দুটোই ঠিকমতো দেখা যায়। */
.cart-dropdown .cart-item .item-img {
    flex: 0 0 70px !important;
    width: 70px !important;
    margin-right: 14px !important;
}
.cart-dropdown .cart-item .item-img img {
    width: 100% !important;
    height: 80px !important;
    object-fit: contain !important;   /* বর্গাকার ছবি cover-এ কেটে যেত */
    background: #f6f6f6;
    border-radius: 6px;
}
.cart-dropdown .cart-item .item-content { padding-right: 0 !important; }
.cart-dropdown .cart-item .item-quantity {
    position: static !important;
    transform: none !important;
    margin-top: 8px;
}
.cart-dropdown .cart-item .close-btn { right: 0 !important; top: 0 !important; }
.cart-dropdown .cart-item .item-title {
    padding-right: 34px;
    font-size: 13px !important;
    line-height: 1.35 !important;
    display: -webkit-box !important;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden !important;
    max-height: 2.7em !important;
}
@media (max-width: 767px) {
    .cart-dropdown .cart-content-wrap {
        width: 280px !important;
        height: calc(100vh - 30px) !important;
        bottom: 30px !important; 
        top: 0 !important;
    }
    .cart-dropdown {
        height: calc(100vh - 30px) !important;
        bottom: 30px !important;
    }
}
</style>

@if(isset($information) && $information->topbar_active == 1 && !empty($information->topbar_notice))
<div class="topbar">
<div class="container position-relative">
<div class="topbar-notice">
<div class="notice-track">
<span class="notice-text">{{ $information->topbar_notice }}</span>
<span class="notice-text">{{ $information->topbar_notice }}</span>
<span class="notice-text">{{ $information->topbar_notice }}</span>
</div>
</div>
</div>
</div>
@endif

<header class="desktop header axil-header">
<div class="axil-mainmenu-desktop position-relative main-bg">
<div class="container">
<div class="header-navbar-clean">
<div class="desktop-logo-clean">
<a href="{{ route('front.home')}}">
<img src="{{ asset('uploads/img/'.$information->site_logo)}}" alt="Site Logo">
</a>
</div>
<div class="desktop-menu-clean">
<ul class="nav-menu-clean">
<li class="nav-item-clean"><a href="{{ route('front.home') }}" class="nav-link-clean">Home</a></li>
@foreach($categories as $cat)
<li class="nav-item-clean {{ $cat->subcats->count() ? 'has-sub' : '' }}">
<a href="{{ route('front.category',[$cat->url])}}" class="nav-link-clean">{{ $cat->name }}</a>
@if($cat->subcats->count())
<ul class="axil-submenu-clean">
@foreach($cat->subcats as $sub)
<li><a href="{{ route('front.category',[$sub->url])}}">{{ $sub->name }}</a></li>
@endforeach
</ul>
@endif
</li>
@endforeach
<li class="nav-item-clean"><a href="{{ route('front.products.index') }}" class="nav-link-clean">Shop</a></li>
</ul>
</div>
<div class="desktop-icons-clean">
<ul class="action-list-clean">
<li><a href="javascript:void(0)" onclick="document.getElementById('desktop-search-dropdown').classList.toggle('active'); $('#desktop-search-input').focus();"><i class="fas fa-search"></i></a></li>
<li><a href="{{ route('front.order.track') }}"><i class="fas fa-truck"></i></a></li>
<li><a href="tel:{{ $information->owner_phone }}"><i class="fas fa-phone-alt"></i></a></li>
@guest
<li><a href="{{ route('login') }}"><i class="far fa-user"></i></a></li>
@else
<li><a href="{{ route('front.dashboard.index') }}"><i class="fas fa-user-check"></i></a></li>
@endguest
<li>
<a href="{{ route('front.carts.index')}}?segment={{request()->segment(1)}}" class="cart-dropdown-btn">
<i class="fas fa-shopping-bag"></i>
<span class="custom-cart-badge cart-count">{{ getTotalCart()}}</span>
</a>
</li>
</ul>
</div>
</div>
<div id="desktop-search-dropdown" class="desktop-search-wrapper">
<form action="{{ route('front.products.index') }}" class="search-form m-0">
<div class="search-box">
<input type="search" id="desktop-search-input" name="q" value="{{ request('q') ?? '' }}" placeholder="Search here..." autocomplete="off">
<button type="submit"><i class="fas fa-search"></i></button>
</div>
</form>
</div>
</div>
</div>
</header>

<header class="mobile header axil-header">
<div class="axil-mainmenu-mobile main-bg position-relative">
<div class="container">
<div class="mobile-header-navbar">
<div style="flex: 1; display: flex; justify-content: flex-start;">
<button class="mobile-nav-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu">
<i class="fas fa-bars"></i>
</button>
</div>
<div class="mobile-logo text-center" style="flex: 2;">
<a href="{{ route('front.home')}}"><img src="{{ asset('uploads/img/'.$information->site_logo)}}" alt="Site Logo"></a>
</div>
<div class="mobile-icons" style="flex: 1; display: flex; justify-content: flex-end; gap: 10px; align-items:center;">
<a href="javascript:void(0)" class="mobile-search-toggle-btn">
<i class="fas fa-search"></i>
</a>
<a href="{{ route('front.carts.index')}}?segment={{request()->segment(1)}}" class="cart-dropdown-btn" style="position: relative;">
<i class="fas fa-shopping-bag"></i>
<span class="custom-cart-badge cart-count">{{ getTotalCart()}}</span>
</a>
</div>
</div>
<div id="mobile-search-dropdown" class="mobile-top-search-outside">
<form action="{{ route('front.products.index') }}" class="search-form m-0">
<div class="search-box">
<input type="search" id="mobile-search-input" name="q" value="{{ request('q') ?? '' }}" placeholder="Search for products..." autocomplete="off">
<button type="submit"><i class="fas fa-search"></i></button>
</div>
</form>
</div>
</div>
</div>
</header>

{{-- ✨ PREMIUM MOBILE MENU --}}
<div class="offcanvas offcanvas-start premium-mobile-menu" tabindex="-1" id="mobileMenu">

    {{-- Hero Header --}}
    <div class="pmm-header">
        <button type="button" class="pmm-close" data-bs-dismiss="offcanvas" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>

        <a href="{{ route('front.home') }}" class="pmm-logo">
            <img src="{{ asset('uploads/img/'.$information->site_logo) }}" alt="Logo">
        </a>

        @guest
            <a href="{{ route('login') }}" class="pmm-user-card">
                <div class="pmm-avatar"><i class="fas fa-user"></i></div>
                <div class="pmm-user-info">
                    <span class="pmm-welcome">Welcome</span>
                    <span class="pmm-username">Login / Register</span>
                </div>
                <i class="fas fa-chevron-right"></i>
            </a>
        @else
            <a href="{{ route('front.dashboard.index') }}" class="pmm-user-card">
                <div class="pmm-avatar">
                    {{ strtoupper(substr(auth()->user()->first_name ?: auth()->user()->mobile, 0, 1)) }}
                </div>
                <div class="pmm-user-info">
                    <span class="pmm-welcome">Hello,</span>
                    <span class="pmm-username">{{ auth()->user()->first_name ? auth()->user()->first_name : auth()->user()->mobile }}</span>
                </div>
                <i class="fas fa-chevron-right"></i>
            </a>
        @endguest
    </div>

    {{-- Quick Actions --}}
    <div class="pmm-quick-grid">
        <a href="{{ route('front.home') }}" class="pmm-quick-item">
            <div class="pmm-quick-icon"><i class="fas fa-home"></i></div>
            <span class="pmm-quick-label">Home</span>
        </a>
        <a href="{{ route('front.products.index') }}" class="pmm-quick-item">
            <div class="pmm-quick-icon"><i class="fas fa-store"></i></div>
            <span class="pmm-quick-label">Shop</span>
        </a>
        <a href="{{ route('front.order.track') }}" class="pmm-quick-item">
            <div class="pmm-quick-icon"><i class="fas fa-truck"></i></div>
            <span class="pmm-quick-label">Track</span>
        </a>
        @auth
            <a href="{{ route('front.dashboard.index') }}" class="pmm-quick-item">
                <div class="pmm-quick-icon"><i class="fas fa-user-check"></i></div>
                <span class="pmm-quick-label">Account</span>
            </a>
        @else
            <a href="tel:{{ $information->owner_phone }}" class="pmm-quick-item">
                <div class="pmm-quick-icon"><i class="fas fa-phone-alt"></i></div>
                <span class="pmm-quick-label">Call</span>
            </a>
        @endauth
    </div>

    {{-- Section Title --}}
    <div class="pmm-section-title">
        <span>Shop by Category</span>
    </div>

    {{-- Scrollable Body --}}
    <div class="pmm-body">
        <ul class="pmm-cat-list">
            @foreach($categories as $key => $cat)
                <li class="pmm-cat-item">
                    <div class="pmm-cat-row">
                        <div class="pmm-cat-icon">
                            <i class="fas fa-{{ $cat->subcats->count() ? 'layer-group' : 'tag' }}"></i>
                        </div>
                        <a href="{{ route('front.category', [$cat->url]) }}" class="pmm-cat-link">
                            {{ $cat->name }}
                        </a>
                        @if($cat->subcats->count() > 0)
                            <button class="pmm-cat-arrow collapsed" type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#pmmCat_{{ $key }}"
                                    aria-expanded="false">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        @endif
                    </div>

                    @if($cat->subcats->count() > 0)
                        <div class="collapse" id="pmmCat_{{ $key }}">
                            <ul class="pmm-subcat-list">
                                @foreach($cat->subcats as $sub)
                                    <li>
                                        <a href="{{ route('front.category', [$sub->url]) }}">
                                            {{ $sub->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Sticky Footer Contact Card --}}
    @if(!empty($information->owner_phone))
        <div class="pmm-footer">
            <a href="tel:{{ $information->owner_phone }}" class="pmm-contact">
                <div class="pmm-contact-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <div class="pmm-contact-text">
                    <small>Need help? Call us</small>
                    <strong>{{ $information->owner_phone }}</strong>
                </div>
            </a>
        </div>
    @endif

</div>

@if($information->whats_active == '1')
<a href="https://wa.me/+88{{ $information->whats_num }}" target="_blank" class="custom-floating-wa">
<i class="fab fa-whatsapp"></i>
</a>
@endif

<a href="#top" class="custom-back-top" id="backto-top">
<i class="fas fa-arrow-up"></i>
</a>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(function(){
function onScroll(){
var st = $(window).scrollTop();
var btn = $('#backto-top');
if (st > 300) btn.addClass('show-btn'); else btn.removeClass('show-btn');
}
onScroll();
$(window).on('scroll', onScroll);

$('#backto-top').on('click', function(e) {
e.preventDefault(); $('html, body').animate({scrollTop:0}, '300');
});

$('.mobile-search-toggle-btn').on('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    $('#mobile-search-dropdown').toggleClass('active');
    if ($('#mobile-search-dropdown').hasClass('active')) {
        $('#mobile-search-input').focus();
    }
});

$(document).on('click', function(e) {
    if (!$(e.target).closest('.desktop-icons-clean li, .desktop-search-wrapper').length) {
        $('#desktop-search-dropdown').removeClass('active');
    }
    if (!$(e.target).closest('.mobile-search-toggle-btn, #mobile-search-dropdown').length) {
        $('#mobile-search-dropdown').removeClass('active');
    }
});

$(document).on('click', '.cart-dropdown-btn', function() {
$('body').addClass('hide-header');
});
$(document).on('click', '.close-cart, .cart-close, .close, .cart-cancel, .cart-overlay, .closeModal', function() {
$('body').removeClass('hide-header');
});
$(document).on('click', function(e) {
if (!$(e.target).closest('.cart-dropdown, .cart-dropdown-btn, #cart-dropdown, #cart_section, .cart-content-wrap').length) {
$('body').removeClass('hide-header');
}
});

// ✨ PMM: Close menu after clicking any category/quick-action link
$('#mobileMenu').on('click', '.pmm-cat-link, .pmm-quick-item, .pmm-subcat-list a, .pmm-user-card', function(){
    var off = bootstrap.Offcanvas.getInstance(document.getElementById('mobileMenu'));
    if(off) setTimeout(function(){ off.hide(); }, 150);
});
});

// Desktop nav scrolls horizontally. A scroll container clips its children,
// so dropdowns are switched to fixed positioning under their menu item.
(function(){
    var menu = document.querySelector('.desktop-menu-clean');
    if (!menu) return;

    function placeSub(item){
        var sub = item.querySelector('.axil-submenu-clean');
        if (!sub) return;
        var r = item.getBoundingClientRect();
        var left = Math.max(8, Math.min(r.left, window.innerWidth - sub.offsetWidth - 8));
        sub.style.position = 'fixed';
        sub.style.top = r.bottom + 'px';
        sub.style.left = left + 'px';
    }
    function placeHovered(){
        menu.querySelectorAll('.nav-item-clean.has-sub:hover').forEach(placeSub);
    }

    menu.querySelectorAll('.nav-item-clean.has-sub').forEach(function(item){
        item.addEventListener('mouseenter', function(){ placeSub(item); });
    });
    menu.addEventListener('scroll', placeHovered, { passive: true });
    window.addEventListener('scroll', placeHovered, { passive: true });
    window.addEventListener('resize', placeHovered);

    // Mouse wheel scrolls the bar sideways when it overflows.
    menu.addEventListener('wheel', function(e){
        if (menu.scrollWidth <= menu.clientWidth || Math.abs(e.deltaY) <= Math.abs(e.deltaX)) return;
        e.preventDefault();
        menu.scrollLeft += e.deltaY;
    }, { passive: false });
})();
</script>