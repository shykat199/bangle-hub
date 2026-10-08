@php
use App\Models\Information;
use App\Models\Category;
// Same row the admin Settings page edits (newest), so toggles like the announcement bar take effect.
$information = Information::orderBy('id', 'desc')->first();
// Every top-level category, for the "All Categories" menu (desktop) and the mobile menu,
// in the order set on the admin "Sort Categories" page.
$allCategories = Category::whereNull('parent_id')->ordered()->with('subcats')->get();
// Nav bar categories: the ones switched on in admin "Home Category Manage", in its serial order.
$navCategories = \App\Models\HomeCategory::with('category')->where('status', 1)->orderBy('serial')->get()
    ->pluck('category')->filter()->unique('id')->values();
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
/* Announcement bar: tall, bold, high-contrast strip above the header. A short line sits
   centred; a line too long for the screen scrolls (the script below turns that on). */
.topbar { background: var(--topbar-bg); color: var(--topbar-text); position: relative; z-index: 1021; }
.topbar .container { display: flex; align-items: center; justify-content: center; gap: 14px; min-height: 48px; padding-top: 7px; padding-bottom: 7px; }
.topbar-icon { flex: 0 0 auto; font-size: 17px; color: var(--topbar-text); }
.topbar-notice { position: relative; flex: 0 1 auto; min-width: 0; overflow: hidden; white-space: nowrap; }
.topbar-notice .notice-track { display: inline-flex; align-items: center; }
.topbar-notice .notice-text {
    font-size: 15.5px; font-weight: 700; line-height: 1.35; letter-spacing: .01em;
    color: var(--topbar-text); -webkit-font-smoothing: antialiased; text-rendering: optimizeLegibility;
}
.topbar-notice .notice-text + .notice-text { display: none; }
.topbar.is-scrolling .topbar-notice { flex: 1 1 auto;
    -webkit-mask-image: linear-gradient(90deg, transparent 0, #000 28px, #000 calc(100% - 28px), transparent 100%);
            mask-image: linear-gradient(90deg, transparent 0, #000 28px, #000 calc(100% - 28px), transparent 100%); }
.topbar.is-scrolling .notice-track { gap: 64px; padding-right: 64px; will-change: transform; animation: noticeScroll var(--notice-time, 30s) linear infinite; }
.topbar.is-scrolling .notice-text + .notice-text { display: inline; }
/* pause under a mouse only: on touch screens :hover sticks after a tap and would freeze the text */
@media (hover: hover) { .topbar.is-scrolling .topbar-notice:hover .notice-track { animation-play-state: paused; } }
.topbar-cta {
    flex: 0 0 auto; display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 16px; border-radius: 999px; white-space: nowrap;
    background: var(--topbar-text); color: var(--topbar-bg) !important;
    font-size: 13.5px; font-weight: 800; line-height: 1; text-decoration: none !important;
    transition: transform .18s ease, opacity .18s ease;
}
.topbar-cta:hover { transform: translateY(-1px); opacity: .9; }
.topbar-cta i { font-size: 11px; }
@media (max-width: 575.98px) {
    .topbar .container { min-height: 42px; gap: 10px; padding-top: 6px; padding-bottom: 6px; }
    .topbar-icon { font-size: 15px; }
    .topbar-notice .notice-text { font-size: 13.5px; }
    .topbar-cta { padding: 6px 12px; font-size: 12.5px; }
}
@media (prefers-reduced-motion: reduce) {
    .topbar.is-scrolling .notice-track { animation: none; }
    .topbar.is-scrolling .topbar-notice { overflow-x: auto; -webkit-mask-image: none; mask-image: none; }
}
@keyframes noticeScroll { from { transform: translateX(0); } to { transform: translateX(-50%); } }
.logo-lockup { display: inline-flex; flex-direction: column; align-items: center; text-decoration: none !important; }

/* ===========================================================
   DESKTOP HEADER (992px+): a top row with logo, search, help, account,
   wishlist and cart, then a nav row led by the "All Categories" menu.
   =========================================================== */
.hx { --hx-accent: {{ themeAccent('#be1e30') }}; --hx-accent-dark: color-mix(in srgb, var(--hx-accent) 80%, #000);
      --hx-soft: color-mix(in srgb, var(--hx-accent) 8%, #fff); --hx-ink: #111827; --hx-muted: #6b7280; --hx-line: #e5e7eb;
      background: #fff; box-shadow: 0 2px 14px rgba(15,23,42,.06); }
.hx a { text-decoration: none !important; }
.hx-top { display: flex; align-items: center; gap: 28px; height: 86px; }
.hx-logo { flex: 0 0 auto; }
.hx-logo img { max-height: 54px; max-width: 230px; object-fit: contain; }

.hx-search { flex: 1 1 auto; max-width: 600px; position: relative; }
.hx-search .search-box { display: flex; }
.hx-search .search-box input {
    height: 48px; padding: 0 64px 0 18px; border-radius: 8px; background: #fff;
    border: 1.5px solid var(--hx-line); font-size: 14.5px;
}
.hx-search .search-box input:focus { border-color: var(--hx-accent); box-shadow: 0 0 0 4px color-mix(in srgb, var(--hx-accent) 12%, transparent); }
.hx-search .search-box button[type="submit"] {
    right: 0; top: 0; transform: none; height: 48px; width: 58px; border-radius: 0 8px 8px 0;
    background: var(--hx-accent); color: #fff; font-size: 18px; box-shadow: none;
}
.hx-search .search-box button[type="submit"]:hover { transform: none; background: var(--hx-accent-dark); }
.hx-search .search-box .ls-spinner { right: 70px; }
.hx-search .ls-results {
    position: absolute; left: 0; right: 0; top: calc(100% + 8px); margin: 0; padding: 8px;
    background: #fff; border-radius: 12px; border: 1px solid var(--hx-line);
    box-shadow: 0 22px 44px -12px rgba(15,23,42,.25); z-index: 99999;
}

.hx-actions { flex: 0 0 auto; margin-left: auto; display: flex; align-items: center; gap: 22px; }
.hx-info { display: flex; align-items: center; gap: 10px; color: var(--hx-ink); }
.hx-info > .nav-ico { width: 32px; height: 32px; stroke: var(--hx-accent); stroke-width: 1.8; flex: 0 0 auto; }
.hx-info span { display: flex; flex-direction: column; line-height: 1.25; }
.hx-info small { font-size: 13px; color: var(--hx-muted); }
.hx-info strong { font-size: 15px; font-weight: 700; color: var(--hx-accent); white-space: nowrap; }
.hx-info.is-account strong { color: var(--hx-ink); font-weight: 600; font-size: 13.5px; }
.hx-info.is-account small { color: var(--hx-ink); font-size: 14px; font-weight: 600; }
.hx-info:hover strong { text-decoration: underline; }
.hx-divider { width: 1px; height: 38px; background: var(--hx-line); }
.hx-icon { position: relative; display: inline-flex; line-height: 1; transition: transform .2s ease; }
.hx-icon .nav-ico { width: 27px; height: 27px; stroke: var(--hx-accent); stroke-width: 2; }
.hx-icon:hover { transform: translateY(-2px); }
.hx .hx-icon .custom-cart-badge { background: var(--hx-accent) !important; border: 2px solid #fff; width: 20px !important; height: 20px !important; top: -9px !important; right: -11px !important; }

.hx-nav { border-top: 1px solid #f1f2f4; }
.hx-nav .container { display: flex; align-items: center; gap: 26px; height: 58px; }
.hx-cats { position: relative; flex: 0 0 auto; align-self: stretch; display: flex; align-items: center; }
.hx-cats-btn {
    display: inline-flex; align-items: center; gap: 12px; height: 50px; min-width: 240px; padding: 0 20px;
    border: 0; border-radius: 6px; background: var(--hx-accent); color: #fff;
    font-size: 17px; font-weight: 700; cursor: pointer; transition: background .2s ease;
}
.hx-cats-btn:hover, .hx-cats.is-open .hx-cats-btn { background: var(--hx-accent-dark); }
.hx-cats-btn .fa-chevron-down { margin-left: auto; font-size: 12px; transition: transform .25s ease; }
.hx-cats.is-open .hx-cats-btn .fa-chevron-down { transform: rotate(180deg); }
/* Panel > scrolling list. Items are position:static, so each flyout is placed
   against the panel (outside the scroller) and is not clipped by it. */
.hx-cats-menu {
    position: absolute; left: 0; top: calc(100% + 4px); width: 280px; z-index: 99999;
    display: flex; flex-direction: column; padding: 8px 0; background: #fff; border-radius: 10px;
    border: 1px solid var(--hx-line); box-shadow: 0 24px 48px -14px rgba(15,23,42,.28);
    opacity: 0; visibility: hidden; transform: translateY(8px); transition: opacity .2s ease, transform .2s ease, visibility .2s;
}
.hx-cats.is-open .hx-cats-menu { opacity: 1; visibility: visible; transform: translateY(0); }
.hx-cats-list { list-style: none; margin: 0; padding: 0; max-height: min(64vh, 480px); overflow-y: auto; scrollbar-width: thin; }
.hx-cats-list > li > a {
    display: flex; align-items: center; gap: 12px; padding: 8px 18px;
    color: var(--hx-ink); font-size: 14.5px; font-weight: 500; transition: background .15s ease, color .15s ease;
}
.hx-cats-list img { width: 30px; height: 30px; border-radius: 6px; object-fit: cover; background: #f3f4f6; flex: 0 0 auto; }
.hx-cat-dot { width: 30px; height: 30px; border-radius: 6px; background: var(--hx-soft); color: var(--hx-accent); display: flex; align-items: center; justify-content: center; font-size: 12px; flex: 0 0 auto; }
.hx-cat-name { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.hx-cats-list .fa-chevron-right { margin-left: auto; font-size: 11px; color: #9ca3af; }
.hx-cats-list > li:hover > a { background: var(--hx-soft); color: var(--hx-accent); }
.hx-sub {
    position: absolute; left: calc(100% - 1px); top: -1px; bottom: -1px; width: 250px; padding: 8px 0; overflow-y: auto;
    background: #fff; border-radius: 0 10px 10px 0; border: 1px solid var(--hx-line); box-shadow: 18px 24px 48px -18px rgba(15,23,42,.25);
    opacity: 0; visibility: hidden; transition: opacity .15s ease, visibility .15s;
}
.hx-cats-list > li:hover > .hx-sub { opacity: 1; visibility: visible; }
.hx-sub a { display: block; padding: 8px 20px; color: var(--hx-ink); font-size: 14px; }
.hx-sub a:hover { background: var(--hx-soft); color: var(--hx-accent); }
.hx-sub .hx-sub-head { font-weight: 700; color: var(--hx-accent); border-bottom: 1px solid #f1f2f4; margin-bottom: 4px; padding-bottom: 10px; }
.hx-sub .hx-sub-head i { font-size: 11px; margin-left: 4px; }
.hx-cats-all { display: block; margin: 8px 12px 2px; padding: 9px; border-radius: 8px; text-align: center; font-weight: 700; font-size: 13.5px; background: var(--hx-soft); color: var(--hx-accent) !important; }
.hx-cats-all i { font-size: 11px; margin-left: 4px; }

/* one row that scrolls sideways when the links do not fit; the scrollbar itself is hidden */
.hx-links {
    display: flex; align-items: center; gap: 30px; list-style: none; margin: 0; padding: 0; flex: 1 1 auto; min-width: 0;
    overflow-x: auto; overflow-y: hidden; scrollbar-width: none; -ms-overflow-style: none; overscroll-behavior-x: contain;
}
.hx-links::-webkit-scrollbar { display: none; }
/* the row can also be dragged sideways with the mouse */
.hx-links.can-left, .hx-links.can-right { cursor: grab; }
.hx-links.is-dragging, .hx-links.is-dragging a { cursor: grabbing; -webkit-user-select: none; user-select: none; }
.hx-links li { flex: 0 0 auto; }
/* a soft fade on the side that still has links to scroll to */
.hx-links.can-right { -webkit-mask-image: linear-gradient(to right, #000 calc(100% - 44px), transparent); mask-image: linear-gradient(to right, #000 calc(100% - 44px), transparent); }
.hx-links.can-left { -webkit-mask-image: linear-gradient(to right, transparent, #000 44px); mask-image: linear-gradient(to right, transparent, #000 44px); }
.hx-links.can-left.can-right { -webkit-mask-image: linear-gradient(to right, transparent, #000 44px, #000 calc(100% - 44px), transparent); mask-image: linear-gradient(to right, transparent, #000 44px, #000 calc(100% - 44px), transparent); }
.hx-links a {
    position: relative; display: inline-flex; align-items: center; height: 58px;
    font-size: 15.5px; font-weight: 600; color: var(--hx-ink); white-space: nowrap; transition: color .2s ease;
}
.hx-links a::after {
    content: ""; position: absolute; left: 0; right: 0; bottom: 10px; height: 2.5px; border-radius: 2px;
    background: var(--hx-accent); transform: scaleX(0); transition: transform .25s ease;
}
.hx-links a:hover, .hx-links a.is-active { color: var(--hx-accent); }
.hx-links a:hover::after, .hx-links a.is-active::after { transform: scaleX(1); }

.hx-badge {
    margin-left: auto; flex: 0 0 auto; display: flex; align-items: center; gap: 12px;
    padding: 8px 18px; border-radius: 6px; background: var(--hx-soft); border: 1px solid color-mix(in srgb, var(--hx-accent) 14%, transparent);
}
.hx-badge .nav-ico { width: 28px; height: 28px; stroke: var(--hx-accent); stroke-width: 1.8; flex: 0 0 auto; }
.hx-badge span { display: flex; flex-direction: column; line-height: 1.2; }
.hx-badge strong { font-size: 15px; color: var(--hx-accent); }
.hx-badge small { font-size: 12px; color: var(--hx-accent); }

@media (max-width: 1399.98px) {
    .hx-top { gap: 20px; }
    .hx-actions { gap: 16px; }
    .hx-links { gap: 22px; }
    .hx-cats-btn { min-width: 220px; }
}
@media (max-width: 1199.98px) {
    .hx-help, .hx-divider, .hx-badge { display: none !important; }
    .hx-links { gap: 18px; }
    .hx-links a { font-size: 14.5px; }
    .hx-cats-btn { min-width: 0; font-size: 15.5px; }
}
@media (prefers-reduced-motion: reduce) {
    .hx-cats-menu, .hx-sub, .hx-links a::after, .hx-icon { transition: none; }
}

/* Header (desktop + mobile) stays pinned to the top while scrolling, below
   Bootstrap's offcanvas/modal layers. The theme puts overflow-x:hidden on body,
   which turns body into a scroll container and silently disables sticky —
   clip hides the sideways overflow the same way without doing that. */
body { overflow-x: clip !important; overflow-y: visible !important; }
header.axil-header { position: -webkit-sticky; position: sticky; top: 0; z-index: 1020; }
/* Wishlist: heart buttons (product cards, product page) + the count badge in the nav.
   Clicks are handled once, in the script at the bottom of this file. */
.custom-cart-badge[hidden]{ display: none !important; }
.wl-heart{
    position: absolute; right: 8px; bottom: 8px; z-index: 6;
    width: 34px; height: 34px; padding: 0; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background: rgba(255,255,255,.94); border: 1px solid rgba(15,23,42,.08);
    box-shadow: 0 4px 12px rgba(15,23,42,.14); cursor: pointer;
    transition: transform .18s ease, background .18s ease;
}
.wl-heart svg, .wl-btn svg{
    width: 18px; height: 18px; fill: none; stroke: {{ themeAccent('#e11d2e') }};
    stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; transition: fill .18s ease;
}
.wl-heart:hover{ transform: scale(1.1); }
.wl-toggle.is-saved svg{ fill: {{ themeAccent('#e11d2e') }}; }
.wl-toggle.is-busy{ opacity: .6; pointer-events: none; }
@keyframes wlPop{ 0%{ transform: scale(1); } 40%{ transform: scale(1.3); } 100%{ transform: scale(1); } }
.wl-toggle.wl-pop svg{ animation: wlPop .35s ease; }
/* labelled variant used on the product page */
.wl-btn{
    display: inline-flex; align-items: center; gap: 8px; padding: 0; border: 0; background: none; cursor: pointer;
    font-size: 14px; font-weight: 600; color: #0f172a;
}
.wl-btn:hover{ color: {{ themeAccent('#e11d2e') }}; }
@media (max-width: 575.98px){ .wl-heart{ width: 30px; height: 30px; right: 6px; bottom: 6px; } .wl-heart svg{ width: 16px; height: 16px; } }

/* Reading progress: a thin bar hanging under the sticky header, filled as the page scrolls. */
.scroll-progress { position: absolute; left: 0; right: 0; top: 100%; height: 3px; pointer-events: none; overflow: hidden; }
.scroll-progress span {
    display: block; width: 100%; height: 100%;
    background: {{ themeAccent('#e11d2e') }};
    transform: scaleX(0); transform-origin: left center; will-change: transform;
}
.nav-ico { width: 22px; height: 22px; display: block; fill: none; stroke: #e11d2e; stroke-width: 2.3; stroke-linecap: round; stroke-linejoin: round; }
.custom-cart-badge {
position: absolute !important; top: -6px !important; right: -8px !important;
background: #000 !important; color: #ffffff !important;
font-size: 11px !important; font-weight: 800 !important; width: 18px !important; height: 18px !important;
display: flex !important; align-items: center !important; justify-content: center !important;
border-radius: 50% !important; box-shadow: 0 2px 5px rgba(0,0,0,0.2) !important; line-height: 1 !important; z-index: 10 !important;
}
@keyframes fadeIn { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }

@media (max-width: 991px) {
.desktop { display: none !important; }
.axil-mainmenu-mobile { padding: 10px 0; border-bottom: none; box-shadow: 0 2px 10px rgba(0,0,0,0.05); width: 100%; transition: all 0.3s ease; position: relative;}
.mobile-header-navbar { display: flex; align-items: center; justify-content: space-between; padding: 0 !important; } 
.mobile-logo img { max-height: 30px; }
.mobile-nav-toggler { background: transparent !important; border: none !important; font-size: 24px !important; padding: 5px !important; color: var(--brand-text) !important; box-shadow: none !important; margin: 0 !important; outline: none;}
.mobile-icons a { background: transparent !important; border: none !important; box-shadow: none !important; color: var(--brand-text) !important; font-size: 20px; padding: 5px !important; margin: 0 !important;}
/* search, track, dashboard (logged in) and cart must still fit beside the logo on small phones */
@media (max-width: 400px) { .mobile-icons { gap: 4px !important; } .mobile-icons a { padding: 3px !important; } .mobile-icons .nav-ico { width: 20px; height: 20px; } }
.mobile-top-search-outside { display: none; position: absolute; top: 100%; left: 0; width: 100%; padding: 12px 15px; background: #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.1); z-index: 99999; border-top: 2px solid var(--brand-gradient); }
.mobile-top-search-outside.active { display: block; animation: fadeIn 0.3s ease; }
.custom-back-top { display: none !important; }
.custom-floating-wa { bottom: 150px !important; right: 10px !important; } 
}
@media (min-width: 992px) { .mobile { display: none !important; } .mobile-top-search-outside { display: none !important; } }

.search-box { position: relative; width: 100%; display: block; }
.search-box .ls-lead {
    position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
    width: 18px; height: 18px; fill: none; stroke: #9ca3af; stroke-width: 2;
    stroke-linecap: round; stroke-linejoin: round; pointer-events: none; transition: stroke .2s ease;
}
.search-box:focus-within .ls-lead { stroke: #e11d2e; }
.search-box input {
    width: 100%; height: 46px; padding: 0 78px 0 44px;
    border-radius: 30px; border: 1.5px solid #e5e7eb;
    font-size: 14px; outline: none; background: #f9fafb; color: #111827;
    transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
}
.search-box input::placeholder { color: #9ca3af; }
.search-box input:focus { border-color: #e11d2e; background: #fff; box-shadow: 0 0 0 4px rgba(225,29,46,.10); }
.search-box input::-webkit-search-cancel-button { display: none; }
.search-box button[type="submit"] {
    position: absolute; right: 5px; top: 50%; transform: translateY(-50%);
    background: var(--brand-gradient); border: none; color: var(--brand-text);
    font-size: 13px; height: 36px; width: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; z-index: 5; box-shadow: 0 2px 6px rgba(0,0,0,.15);
    transition: transform .2s ease;
}
.search-box button[type="submit"]:hover { transform: translateY(-50%) scale(1.06); }
/* Preloader: small ring next to the submit button while a request is in flight */
.search-box .ls-spinner {
    position: absolute; right: 50px; top: 50%; width: 18px; height: 18px; margin-top: -9px;
    border: 2px solid rgba(225,29,46,.2); border-top-color: #e11d2e; border-radius: 50%;
    opacity: 0; transition: opacity .15s ease; pointer-events: none;
}
.search-form.ls-loading .ls-spinner { opacity: 1; animation: lsSpin .6s linear infinite; }
@keyframes lsSpin { to { transform: rotate(360deg); } }

/* Live search preview */
.ls-results { display: none; margin-top: 10px; max-height: min(62vh, 440px); overflow-y: auto; scrollbar-width: thin; }
.ls-results.open { display: block; }
.ls-item {
    display: flex; align-items: center; gap: 12px; padding: 8px; border-radius: 10px;
    text-decoration: none !important; color: #111827 !important;
    transition: background .15s ease; animation: lsIn .25s ease both;
}
.ls-item:hover, .ls-item.ls-active { background: #fef2f2; }
@keyframes lsIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
.ls-thumb { flex: 0 0 52px; width: 52px; height: 52px; border-radius: 8px; object-fit: cover; background: #f3f4f6; border: 1px solid #f1f5f9; }
.ls-info { flex: 1; min-width: 0; }
.ls-name { font-size: 13.5px; font-weight: 600; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.ls-name mark, .ls-cat mark { background: none; color: #e11d2e !important; padding: 0; font-weight: 800; }
.ls-cat { font-size: 11.5px; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ls-price { flex: 0 0 auto; text-align: right; font-size: 13.5px; font-weight: 800; color: #e11d2e; white-space: nowrap; }
.ls-price del { display: block; font-size: 11px; font-weight: 500; color: #9ca3af; }
.ls-all {
    display: block; margin-top: 6px; padding: 10px; border-top: 1px solid #f1f5f9;
    position: sticky; bottom: 0; background: #fff;
    text-align: center; font-size: 13px; font-weight: 700;
    color: #e11d2e !important; text-decoration: none !important;
}
.ls-all:hover { text-decoration: underline !important; }
.ls-empty { padding: 22px 10px; text-align: center; font-size: 13px; color: #6b7280; }
.ls-empty strong { display: block; color: #111827; font-size: 14px; margin-bottom: 2px; word-break: break-word; }
/* The header's .main-bg rule forces brand text colour on every a/span/i with
   !important, so the preview's own colours need a more specific selector. */
.main-bg:not(.bg_alt) .ls-results .ls-name { color: #111827 !important; }
.main-bg:not(.bg_alt) .ls-results .ls-cat { color: #6b7280 !important; }
.main-bg:not(.bg_alt) .ls-results .ls-price,
.main-bg:not(.bg_alt) .ls-results .ls-all,
.main-bg:not(.bg_alt) .ls-results .ls-all i { color: #e11d2e !important; }
/* Skeleton rows shown while the first results load */
.ls-skel { display: flex; align-items: center; gap: 12px; padding: 8px; }
.ls-skel i, .ls-skel b, .ls-skel u {
    display: block; border-radius: 6px;
    background: linear-gradient(90deg, #f1f5f9 25%, #e5e7eb 37%, #f1f5f9 63%); background-size: 400% 100%;
    animation: lsShimmer 1.2s ease infinite;
}
.ls-skel i { flex: 0 0 52px; height: 52px; border-radius: 8px; }
.ls-skel span { flex: 1; }
.ls-skel b { height: 11px; width: 80%; margin-bottom: 8px; }
.ls-skel u { height: 9px; width: 45%; }
@keyframes lsShimmer { 0% { background-position: 100% 50%; } 100% { background-position: 0 50%; } }
@media (prefers-reduced-motion: reduce) { .ls-item, .ls-skel i, .ls-skel b, .ls-skel u { animation: none; } }

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

@if(isset($information) && $information->topbar_active == 1 && !empty(trim((string) $information->topbar_notice)))
@php
    // Admin types either a full URL or a site path ("/products"); anything else is treated as a path.
    $topbarLink = trim((string) ($information->topbar_link ?? ''));
    if ($topbarLink !== '' && !preg_match('#^(https?:)?//#i', $topbarLink)) {
        $topbarLink = url(ltrim($topbarLink, '/'));
    }
@endphp
<div class="topbar" id="announcementBar" role="region" aria-label="Announcement">
<div class="container">
<i class="fas fa-bullhorn topbar-icon" aria-hidden="true"></i>
<div class="topbar-notice">
<div class="notice-track">
<span class="notice-text">{{ $information->topbar_notice }}</span>
<span class="notice-text" aria-hidden="true">{{ $information->topbar_notice }}</span>
</div>
</div>
@if($topbarLink !== '')
<a href="{{ $topbarLink }}" class="topbar-cta">{{ trim((string) $information->topbar_link_text) ?: 'Shop Now' }} <i class="fas fa-arrow-right"></i></a>
@endif
</div>
</div>
@endif

<header class="desktop header axil-header hx">
<div class="container">
<div class="hx-top">
    <div class="hx-logo">
        <a href="{{ route('front.home') }}" class="logo-lockup">
            <img src="{{ asset('uploads/img/'.$information->site_logo) }}" alt="{{ $information->site_name ?: 'Site Logo' }}">
        </a>
    </div>

    <div class="hx-search">
        <form action="{{ route('front.products.index') }}" class="search-form m-0" role="search">
            <div class="search-box">
                <input type="search" id="desktop-search-input" name="q" value="{{ request('q') ?? '' }}" placeholder="Search for products, categories, brands..." autocomplete="off" aria-label="Search products">
                <span class="ls-spinner"></span>
                <button type="submit" aria-label="Search"><i class="fas fa-search"></i></button>
            </div>
            <div class="ls-results" aria-live="polite"></div>
        </form>
    </div>

    <div class="hx-actions">
        @if(!empty($information->owner_phone))
        <a href="tel:{{ $information->owner_phone }}" class="hx-info hx-help">
            <svg class="nav-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M3.5 14v-2a8.5 8.5 0 0 1 17 0v2"/><path d="M3.5 14.5a2 2 0 0 1 2-2h1v6h-1a2 2 0 0 1-2-2z"/><path d="M20.5 14.5a2 2 0 0 0-2-2h-1v6h1a2 2 0 0 0 2-2z"/><path d="M18.5 18.5c0 1.7-2 2.5-5 2.5"/></svg>
            <span><small>Need Help?</small><strong>{{ $information->owner_phone }}</strong></span>
        </a>
        <span class="hx-divider" aria-hidden="true"></span>
        @endif

        @guest
        <a href="{{ route('login') }}" class="hx-info is-account">
            <svg class="nav-ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9.5"/><circle cx="12" cy="10" r="3.2"/><path d="M6.2 18.6a6.5 6.5 0 0 1 11.6 0"/></svg>
            <span><small>My Account</small><strong>Login / Register</strong></span>
        </a>
        <span class="hx-divider" aria-hidden="true"></span>
        @endguest

        <a href="{{ route('front.order.track') }}" class="hx-icon" title="Track Order" aria-label="Track Order">
            <svg class="nav-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M1.5 5.5h12v10.5h-12z"/><path d="M13.5 9h4.2l3.8 3.8V16h-8z"/><circle cx="6" cy="18" r="2"/><circle cx="17.5" cy="18" r="2"/></svg>
        </a>
        @auth
        <a href="{{ route('front.dashboard.index') }}" class="hx-icon" title="My Dashboard" aria-label="My Dashboard">
            <svg class="nav-ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="9.5" cy="8" r="4"/><path d="M2 21v-1a6 6 0 0 1 6-6h3a6 6 0 0 1 4.5 2"/><path d="M16 18.5l2.2 2.2 4.3-4.6"/></svg>
        </a>
        @endauth
        <a href="{{ route('front.wishlist.index') }}" class="hx-icon" title="Wishlist" aria-label="Wishlist">
            <svg class="nav-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20.5s-7.5-4.4-9.6-9.1C1 8.1 2.9 4.5 6.5 4.5c2 0 3.6 1 4.6 2.5.3.4.6.4.9 0 1-1.5 2.6-2.5 4.6-2.5 3.6 0 5.5 3.6 4.1 6.9-2.1 4.7-9.6 9.1-9.6 9.1z"/></svg>
            <span class="custom-cart-badge wl-count" @if(wishlistCount() === 0) hidden @endif>{{ wishlistCount() }}</span>
        </a>
        <a href="{{ route('front.carts.index') }}?segment={{ request()->segment(1) }}" class="hx-icon cart-dropdown-btn" title="Cart" aria-label="Cart">
            <svg class="nav-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 7.5h14l1.2 13.5H3.8z"/><path d="M8.5 10.5V6.5a3.5 3.5 0 0 1 7 0v4"/></svg>
            <span class="custom-cart-badge cart-count">{{ getTotalCart() }}</span>
        </a>
    </div>
</div>
</div>

<nav class="hx-nav" aria-label="Main">
<div class="container">
    <div class="hx-cats" id="hxCats">
        <button type="button" class="hx-cats-btn" aria-expanded="false" aria-controls="hxCatsMenu">
            <i class="fas fa-bars" aria-hidden="true"></i> All Categories <i class="fas fa-chevron-down" aria-hidden="true"></i>
        </button>
        <div class="hx-cats-menu" id="hxCatsMenu">
            <ul class="hx-cats-list">
            @foreach($allCategories as $cat)
                <li>
                    <a href="{{ route('front.category', [$cat->url]) }}">
                        @if(!empty($cat->image) && file_exists(public_path('categories/'.$cat->image)))
                            <img src="{{ asset('categories/'.$cat->image) }}" alt="" loading="lazy">
                        @else
                            <span class="hx-cat-dot"><i class="fas fa-tag"></i></span>
                        @endif
                        <span class="hx-cat-name">{{ $cat->name }}</span>
                        @if($cat->subcats->count())<i class="fas fa-chevron-right" aria-hidden="true"></i>@endif
                    </a>
                    @if($cat->subcats->count())
                    <div class="hx-sub">
                        <a href="{{ route('front.category', [$cat->url]) }}" class="hx-sub-head">{{ $cat->name }} <i class="fas fa-arrow-right"></i></a>
                        @foreach($cat->subcats as $sub)
                            <a href="{{ route('front.category', [$sub->url]) }}">{{ $sub->name }}</a>
                        @endforeach
                    </div>
                    @endif
                </li>
            @endforeach
            </ul>
            <a href="{{ route('front.categories') }}" class="hx-cats-all">View All Categories <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>

    <ul class="hx-links">
        <li><a href="{{ route('front.products.index') }}" class="{{ request()->routeIs('front.products.index') ? 'is-active' : '' }}">Shop</a></li>
        @foreach($navCategories as $navCat)
            <li><a href="{{ route('front.category', [$navCat->url]) }}" class="{{ request()->routeIs('front.category') && request()->route('slug') === $navCat->url ? 'is-active' : '' }}">{{ $navCat->name }}</a></li>
        @endforeach
    </ul>

    <div class="hx-badge">
        <svg class="nav-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M3.5 7.5 12 3l8.5 4.5v9L12 21l-8.5-4.5z"/><path d="M3.5 7.5 12 12l8.5-4.5"/><path d="M12 12v9"/><path d="M7.8 5.3l8.4 4.5"/></svg>
        <span><strong>Wholesale Only</strong><small>Minimum Order Applicable</small></span>
    </div>
</div>
</nav>
<div class="scroll-progress" aria-hidden="true"><span></span></div>
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
<a href="{{ route('front.home')}}" class="logo-lockup"><img src="{{ asset('uploads/img/'.$information->site_logo)}}" alt="Site Logo"></a>
</div>
<div class="mobile-icons" style="flex: 1; display: flex; justify-content: flex-end; gap: 10px; align-items:center;">
<a href="javascript:void(0)" class="mobile-search-toggle-btn">
<svg class="nav-ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.6-3.6"/></svg>
</a>
<a href="{{ route('front.order.track') }}" title="Track Order" aria-label="Track Order">
<svg class="nav-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M1.5 5.5h12v10.5h-12z"/><path d="M13.5 9h4.2l3.8 3.8V16h-8z"/><circle cx="6" cy="18" r="2"/><circle cx="17.5" cy="18" r="2"/></svg>
</a>
@auth
<a href="{{ route('front.dashboard.index') }}" title="My Dashboard" aria-label="My Dashboard">
<svg class="nav-ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="9.5" cy="8" r="4"/><path d="M2 21v-1a6 6 0 0 1 6-6h3a6 6 0 0 1 4.5 2"/><path d="M16 18.5l2.2 2.2 4.3-4.6"/></svg>
</a>
@endauth
<a href="{{ route('front.carts.index')}}?segment={{request()->segment(1)}}" class="cart-dropdown-btn" style="position: relative;">
<svg class="nav-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 7.5h14l1.2 13.5H3.8z"/><path d="M8.5 10.5V6.5a3.5 3.5 0 0 1 7 0v4"/></svg>
<span class="custom-cart-badge cart-count">{{ getTotalCart()}}</span>
</a>
</div>
</div>
<div id="mobile-search-dropdown" class="mobile-top-search-outside">
<form action="{{ route('front.products.index') }}" class="search-form m-0">
<div class="search-box">
<svg class="ls-lead" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.6-3.6"/></svg>
<input type="search" id="mobile-search-input" name="q" value="{{ request('q') ?? '' }}" placeholder="Search by product name or SKU..." autocomplete="off" aria-label="Search products">
<span class="ls-spinner"></span>
<button type="submit" aria-label="Search"><i class="fas fa-search"></i></button>
</div>
<div class="ls-results" aria-live="polite"></div>
</form>
</div>
</div>
</div>
<div class="scroll-progress" aria-hidden="true"><span></span></div>
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
        <a href="{{ route('front.wishlist.index') }}" class="pmm-quick-item">
            <div class="pmm-quick-icon"><i class="far fa-heart"></i></div>
            <span class="pmm-quick-label">Wishlist</span>
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
            @foreach($allCategories as $key => $cat)
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
// Nav bar links: the mouse wheel scrolls the row sideways (its scrollbar is hidden),
// the edge fades show where more links are, and the current page's link starts in view.
(function(){
    var row = document.querySelector('.hx-links');
    if(!row) return;
    function edges(){
        var max = row.scrollWidth - row.clientWidth;
        row.classList.toggle('can-left', row.scrollLeft > 2);
        row.classList.toggle('can-right', row.scrollLeft < max - 2);
    }
    row.addEventListener('wheel', function(e){
        if(row.scrollWidth <= row.clientWidth || Math.abs(e.deltaX) > Math.abs(e.deltaY)) return;
        e.preventDefault();
        row.scrollLeft += e.deltaY;
    }, { passive: false });
    // Click and drag with the mouse slides the row too (touch screens already scroll it natively).
    var dragging = false, moved = false, startX = 0, startLeft = 0;
    row.addEventListener('mousedown', function(e){
        moved = false;
        if(e.button !== 0 || row.scrollWidth <= row.clientWidth) return;
        dragging = true; startX = e.pageX; startLeft = row.scrollLeft;
    });
    document.addEventListener('mousemove', function(e){
        if(!dragging) return;
        var dx = e.pageX - startX;
        if(!moved && Math.abs(dx) < 5) return;
        moved = true;
        row.classList.add('is-dragging');
        e.preventDefault();
        row.scrollLeft = startLeft - dx;
    });
    document.addEventListener('mouseup', function(){
        dragging = false;
        row.classList.remove('is-dragging');
    });
    // a drag must not open the link the mouse was released on, nor start the browser's own link drag
    row.addEventListener('click', function(e){
        if(!moved) return;
        moved = false;
        e.preventDefault(); e.stopPropagation();
    }, true);
    row.addEventListener('dragstart', function(e){ e.preventDefault(); });
    row.addEventListener('scroll', edges, { passive: true });
    window.addEventListener('resize', edges);
    var active = row.querySelector('a.is-active');
    if(active) row.scrollLeft += active.getBoundingClientRect().left - row.getBoundingClientRect().left - (row.clientWidth - active.offsetWidth) / 2;
    edges();
})();
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
    if (!$(e.target).closest('.mobile-search-toggle-btn, #mobile-search-dropdown').length) {
        $('#mobile-search-dropdown').removeClass('active');
    }
});

// Announcement bar: scroll the text only when it does not fit, at a steady reading speed.
(function(){
    var bar = document.getElementById('announcementBar');
    if(!bar) return;
    var box = bar.querySelector('.topbar-notice'), text = bar.querySelector('.notice-text');
    function fit(){
        bar.classList.remove('is-scrolling');
        // room the text may use = the row minus icon, button and gaps
        var row = bar.querySelector('.container'), used = 0;
        Array.prototype.forEach.call(row.children, function(el){ if(el !== box) used += el.getBoundingClientRect().width + 14; });
        var room = row.clientWidth - parseFloat(getComputedStyle(row).paddingLeft) - parseFloat(getComputedStyle(row).paddingRight) - used;
        if(text.getBoundingClientRect().width > room){
            bar.classList.add('is-scrolling');
            bar.style.setProperty('--notice-time', Math.max(12, (text.getBoundingClientRect().width + 64) / 70) + 's');
        }
    }
    fit();
    window.addEventListener('resize', fit);
    if(document.fonts && document.fonts.ready) document.fonts.ready.then(fit);
})();

// Wishlist hearts. Any element with .wl-toggle[data-product] saves / removes that
// product; every heart for the same product and the nav count follow the answer.
(function(){
    var URL = @json(route('front.wishlist.toggle'));
    var PAGE = @json(route('front.wishlist.index'));

    document.addEventListener('click', function(e){
        var btn = e.target.closest ? e.target.closest('.wl-toggle[data-product]') : null;
        if(!btn) return;
        e.preventDefault();
        e.stopPropagation();
        if(btn.classList.contains('is-busy')) return;

        var id = btn.getAttribute('data-product');
        var token = document.querySelector('meta[name="csrf-token"]');
        btn.classList.add('is-busy');

        $.ajax({ type: 'POST', url: URL, data: { product_id: id, _token: token ? token.getAttribute('content') : '' } })
            .done(function(res){
                if(!res || !res.status){ if(window.toastr) toastr.error((res && res.msg) || 'Could not update your wishlist'); return; }

                document.querySelectorAll('.wl-toggle[data-product="' + id + '"]').forEach(function(el){
                    el.classList.toggle('is-saved', !!res.saved);
                    el.setAttribute('aria-pressed', res.saved ? 'true' : 'false');
                    el.setAttribute('title', res.saved ? 'Remove from Wishlist' : 'Add to Wishlist');
                    var label = el.querySelector('[data-wl-label]');
                    if(label) label.textContent = res.saved ? 'Saved to Wishlist' : 'Add to Wishlist';
                });
                if(res.saved){ btn.classList.add('wl-pop'); setTimeout(function(){ btn.classList.remove('wl-pop'); }, 400); }

                document.querySelectorAll('.wl-count').forEach(function(el){ el.textContent = res.count; el.hidden = !res.count; });

                if(window.toastr){
                    if(res.saved) toastr.success('<a href="' + PAGE + '" style="color:inherit;text-decoration:underline;">View wishlist</a>', res.msg, { escapeHtml: false });
                    else toastr.info(res.msg);
                }
                document.dispatchEvent(new CustomEvent('wishlist:changed', { detail: { id: id, saved: !!res.saved, count: res.count } }));
            })
            .fail(function(x){
                var msg = (x.responseJSON && x.responseJSON.msg) || 'Could not update your wishlist. Please try again.';
                if(window.toastr) toastr.error(msg);
            })
            .always(function(){ btn.classList.remove('is-busy'); });
    }, true);
})();

// Scroll progress bars (one per header, only one header is visible at a time).
(function(){
    var bars = document.querySelectorAll('.scroll-progress span');
    if(!bars.length) return;
    var ticking = false;
    function draw(){
        ticking = false;
        var doc = document.documentElement;
        var max = doc.scrollHeight - window.innerHeight;
        var ratio = max > 0 ? Math.min(1, Math.max(0, window.scrollY / max)) : 0;
        for(var i = 0; i < bars.length; i++) bars[i].style.transform = 'scaleX(' + ratio + ')';
    }
    function queue(){ if(!ticking){ ticking = true; window.requestAnimationFrame(draw); } }
    window.addEventListener('scroll', queue, { passive: true });
    window.addEventListener('resize', queue);
    window.addEventListener('load', queue);
    draw();
})();

// Live search preview for the header search boxes (desktop + mobile).
(function(){
    var ENDPOINT = @json(route('front.products.liveSearch'));
    var LIST_URL = @json(route('front.products.index'));
    var CURRENCY = @json(['BDT' => '৳ ', 'Dollar' => '$ '][$information->currency ?? 'BDT'] ?? '');
    var MIN = 2;

    function esc(t){ return $('<div>').text(t == null ? '' : t).html(); }
    function money(n){ return CURRENCY + Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function highlight(name, q){
        var i = name.toLowerCase().indexOf(q.toLowerCase());
        if (i < 0) return esc(name);
        return esc(name.slice(0, i)) + '<mark>' + esc(name.slice(i, i + q.length)) + '</mark>' + esc(name.slice(i + q.length));
    }
    function skeleton(){
        var row = '<div class="ls-skel"><i></i><span><b></b><u></u></span></div>';
        return row + row + row;
    }

    $('.search-form').each(function(){
        var $form = $(this), $input = $form.find('input[name="q"]'), $box = $form.find('.ls-results');
        var timer = null, xhr = null, lastQ = null;

        function close(){ $box.removeClass('open').empty(); $form.removeClass('ls-loading'); lastQ = null; }

        function render(q, res){
            var html = '';
            if (!res.items.length) {
                html = '<div class="ls-empty"><strong>No results for "' + esc(q) + '"</strong>Try a different or shorter keyword.</div>';
            } else {
                res.items.forEach(function(p){
                    html += '<a class="ls-item" href="' + esc(p.url) + '">'
                         +  '<img class="ls-thumb" src="' + esc(p.image) + '" alt="" loading="lazy">'
                         +  '<span class="ls-info"><span class="ls-name">' + highlight(p.name, q) + '</span>'
                         +  '<span class="ls-cat">' + [p.category ? esc(p.category) : '', p.sku ? 'SKU: ' + highlight(p.sku, q) : ''].filter(Boolean).join(' &middot; ') + '</span></span>'
                         +  '<span class="ls-price">' + money(p.price) + (p.old_price ? '<del>' + money(p.old_price) + '</del>' : '') + '</span>'
                         +  '</a>';
                });
                if (res.total > res.items.length) {
                    html += '<a class="ls-all" href="' + LIST_URL + '?q=' + encodeURIComponent(q) + '">View all ' + res.total + ' results <i class="fas fa-arrow-right"></i></a>';
                }
            }
            $box.html(html).addClass('open');
        }

        function search(){
            var q = $.trim($input.val());
            if (q.length < MIN) { if (xhr) xhr.abort(); close(); return; }
            if (q === lastQ) return;
            lastQ = q;
            if (xhr) xhr.abort();
            $form.addClass('ls-loading');
            if (!$box.find('.ls-item').length) $box.html(skeleton()).addClass('open');
            xhr = $.getJSON(ENDPOINT, { q: q })
                .done(function(res){ render(q, res); })
                .fail(function(x, status){
                    if (status === 'abort') return;
                    $box.html('<div class="ls-empty">Search is unavailable right now. Press Enter to see all results.</div>').addClass('open');
                })
                .always(function(x, status){ if (status !== 'abort') $form.removeClass('ls-loading'); });
        }

        $input.on('input', function(){ clearTimeout(timer); timer = setTimeout(search, 280); });
        $input.on('focus', function(){ if (!$box.hasClass('open')) search(); });
        $input.on('keydown', function(e){
            var $items = $box.find('.ls-item'), $cur = $items.filter('.ls-active'), idx = $items.index($cur);
            if (e.key === 'Escape') { close(); $input.blur(); $form.closest('.desktop-search-wrapper, .mobile-top-search-outside').removeClass('active'); return; }
            if (!$items.length) return;
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                idx = e.key === 'ArrowDown' ? (idx + 1) % $items.length : (idx <= 0 ? $items.length - 1 : idx - 1);
                $items.removeClass('ls-active').eq(idx).addClass('ls-active')[0].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'Enter' && $cur.length) {
                e.preventDefault();
                window.location.href = $cur.attr('href');
            }
        });
    });
})();

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

// "All Categories" menu: opens on hover (mouse) and on click (touch / keyboard), closes on outside click or Escape.
(function(){
    var box = document.getElementById('hxCats');
    if (!box) return;
    var btn = box.querySelector('.hx-cats-btn'), timer = null;
    function set(open){ clearTimeout(timer); box.classList.toggle('is-open', open); btn.setAttribute('aria-expanded', open ? 'true' : 'false'); }
    btn.addEventListener('click', function(){ set(!box.classList.contains('is-open')); });
    box.addEventListener('mouseenter', function(){ if (window.matchMedia('(hover: hover)').matches) set(true); });
    box.addEventListener('mouseleave', function(){ if (window.matchMedia('(hover: hover)').matches) timer = setTimeout(function(){ set(false); }, 180); });
    document.addEventListener('click', function(e){ if (!box.contains(e.target)) set(false); });
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape' && box.classList.contains('is-open')) { set(false); btn.focus(); } });
})();
</script>