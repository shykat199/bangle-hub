@extends('frontend.app')

@section('content')

@php
    use App\Models\AdminText;
    use App\Models\Information;
    $adminText = AdminText::first();
    $viewAllText  = $adminText->view_all_text ?? 'View All';
    $info = Information::first();
    $popularDotColor  = $info->popular_dot_color ?? '#0d6efd';
    $viewAllBtnBg     = $info->view_all_btn_bg_color ?? '#0d6efd';
    $viewAllBtnColor  = $info->view_all_btn_text_color ?? '#ffffff';
    $categoryHeadingColor = $info->category_heading_color ?? '#0d6efd';

    // Dot-er pulse glow ar heading-er pashe divider line — dot color-erই halka
    // (rgba) version, jate alada color diye ek jaygay 2 rokom accent na dekhay.
    $hexToRgba = function (string $hex, float $alpha): string {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) $hex = '0d6efd';
        [$r, $g, $b] = sscanf($hex, "%02x%02x%02x");
        return "rgba($r, $g, $b, $alpha)";
    };
    $dotGlowStrong = $hexToRgba($popularDotColor, 0.45);
    $dotGlowFade   = $hexToRgba($popularDotColor, 0);
    $dotLineColor  = $hexToRgba($popularDotColor, 0.35);
@endphp

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
@endpush

<style>
    .swiper:not(.swiper-initialized) .swiper-slide { display: none; }
    .swiper:not(.swiper-initialized) .swiper-slide:first-child { display: block; }

    .row>[class*=col] { padding-left: 5px !important; padding-right: 5px !important; }
    .desktop-slide { display: block; }
    .mobile-slide  { display: none; }
    @media (max-width: 991px){
        .desktop-slide { display: none !important; }
        .mobile-slide  { display: block !important; }
    }

    /* ============================================================
       HERO SLIDER
       ============================================================ */
    .hero-slider-wrap {
        margin-top: 0; margin-bottom: 0;
        border-radius: 16px; overflow: hidden; /* Changed border-radius to 16px for container layout */
        background: #0a0f1e; position: relative; z-index: 1; isolation: isolate;
    }
    .hero-slider-wrap::before{
        content:""; position:absolute; left:0; right:0; top:0; height:1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.3), transparent);
        z-index: 5; pointer-events:none;
    }
    .hero-slider-wrap::after{
        content:""; position:absolute; top:0; bottom:0; width:40%; left:-50%;
        background: linear-gradient(120deg, transparent, rgba(255,255,255,0.08) 50%, transparent);
        transform: skewX(-15deg);
        animation: lightSweep 12s ease-in-out infinite;
        animation-delay: 3s;
        pointer-events:none; z-index: 4;
    }
    @keyframes lightSweep { 0%{left:-50%;} 70%{left:150%;} 100%{left:150%;} }

    .swiper-slide { overflow: hidden; width: 100%; height: auto; position: relative;
        backface-visibility: hidden; -webkit-backface-visibility: hidden; }
    .hero-slider-wrap .swiper-slide img {
        width:100%; height:auto; display:block; object-fit:cover;
        will-change: transform; transform: scale(1.08);
        transition: transform 9s cubic-bezier(.25,.46,.45,.94);
    }
    .hero-slider-wrap .swiper-slide-active img { transform: scale(1); }
    .hero-slider-wrap .swiper-slide::after{
        content:""; position:absolute; inset:0;
        background: radial-gradient(120% 70% at 50% 50%, transparent 70%, rgba(0,0,0,.20) 100%);
        pointer-events:none; z-index: 2;
    }

    .swiper-button-next, .swiper-button-prev {
        color: #fff !important; background: rgba(255,255,255,0.10);
        width: 46px; height: 46px; border-radius: 50%;
        backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.20);
        transition: all .4s cubic-bezier(.25,.46,.45,.94);
        opacity: 0; z-index: 10;
    }
    .hero-slider-wrap:hover .swiper-button-next,
    .hero-slider-wrap:hover .swiper-button-prev { opacity: 1; }
    .swiper-button-next:after, .swiper-button-prev:after { font-size: 15px; font-weight: 700; }
    .swiper-button-next:hover, .swiper-button-prev:hover {
        background: rgba(255,255,255,0.95);
        color: #00276C !important; transform: scale(1.06);
    }

    .desktop-swiper .swiper-pagination,
    .mobile-swiper .swiper-pagination{ z-index: 10; bottom: 18px !important; }
    .desktop-swiper .swiper-pagination-bullet,
    .mobile-swiper .swiper-pagination-bullet {
        background: rgba(255,255,255,0.28) !important;
        opacity: 1; width: 26px; height: 3px; border-radius: 3px;
        transition: all .5s cubic-bezier(.25,.46,.45,.94);
        margin: 0 4px !important; position: relative; overflow: hidden;
    }
    .desktop-swiper .swiper-pagination-bullet-active,
    .mobile-swiper .swiper-pagination-bullet-active{
        background: rgba(255,255,255,0.30) !important; width: 56px;
    }
    .desktop-swiper .swiper-pagination-bullet-active::after,
    .mobile-swiper .swiper-pagination-bullet-active::after{
        content:""; position:absolute; left:0; top:0; bottom:0; width:0;
        background: rgba(255,255,255,0.95); border-radius: 3px;
        animation: pgFill var(--pg-dur, 5s) linear forwards;
    }
    @keyframes pgFill { to { width: 100%; } }

    /* ============================================================
       ✅ FEATURED COLLECTIONS — NO ANIMATION (static)
       ============================================================ */
    .featured-section {
        background: #ffffff !important;
        padding: 30px 0 10px;
        position: relative;
    }
    .featured-grid-container { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .left-4-grid { display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; gap: 15px; }
    .right-1-grid { display: block; width: 100%; height: 100%; }

    .grid-img-wrapper {
        display: block; width: 100%; height: 100%;
        overflow: hidden; position: relative;
        border-radius: 16px; background: #fff;
        box-shadow: 0 10px 24px -10px rgba(15,23,42,.14), 0 4px 10px -4px rgba(15,23,42,.06);
        cursor: pointer;
        isolation: isolate;
    }
    .grid-img-wrapper img {
        width: 100%; height: 100%; object-fit: cover; object-position: center;
        display: block;
    }
    .grid-img-wrapper .shine,
    .grid-img-wrapper .explore-btn,
    .grid-img-wrapper .corner-tag,
    .grid-img-wrapper::before,
    .grid-img-wrapper::after {
        display: none !important;
    }

    @media (max-width: 991px) {
        .featured-grid-container { grid-template-columns: 1fr; }
        .right-1-grid { min-height: 400px; }
    }
    @media (max-width: 575px) {
        .featured-grid-container { gap: 10px; }
        .left-4-grid { gap: 10px; }
        .right-1-grid { min-height: 300px; }
        .grid-img-wrapper{ border-radius: 12px; }
    }

    /* ============================================================
       POPULAR CATEGORY
       ============================================================ */
    .popular_section{
        margin-top: 0; padding: 26px 0 24px;
        background: #ffffff !important;
        position: relative; overflow: hidden;
    }
    .popular_section::before{
        content:""; position:absolute; inset:0;
        background-image: radial-gradient(circle at 1px 1px, rgba(15,23,42,.05) 1px, transparent 0);
        background-size: 24px 24px; opacity:.5;
        mask-image: radial-gradient(ellipse at center, #000 30%, transparent 75%);
        -webkit-mask-image: radial-gradient(ellipse at center, #000 30%, transparent 75%);
        pointer-events:none;
    }

    .popular_product {
        align-items: center; display: flex; flex-flow: row wrap;
        justify-content: center; position: relative; width: 100%;
        margin: 0 0 22px; padding: 0 15px; z-index:2;
    }
    .popular_product span {
        font-size: 26px; font-family: 'Hind Siliguri', sans-serif;
        color: #0f172a; font-weight: 800;
        padding: 0 36px;
        text-transform: uppercase; letter-spacing: .5px;
        position: relative;
        background: linear-gradient(135deg, #0f172a 0%, {{ $categoryHeadingColor }} 50%, #0f172a 100%);
        background-size: 200% 100%;
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: titleShimmer 6s ease-in-out infinite;
    }
    @keyframes titleShimmer {
        0%, 100% { background-position: 0% 50%; }
        50%      { background-position: 100% 50%; }
    }
    .popular_product span::before,
    .popular_product span::after{
        content:""; position:absolute; top:50%; width:8px; height:8px;
        border-radius:50%; transform: translateY(-50%);
        background: {{ $popularDotColor }};
        animation: dotPulse 2.4s ease-in-out infinite;
    }
    .popular_product span::before{ left: 6px; }
    .popular_product span::after { right: 6px; animation-delay: 1.2s; }
    @keyframes dotPulse {
        0%, 100% { transform: translateY(-50%) scale(1);  box-shadow: 0 0 0 0 {{ $dotGlowStrong }}; }
        50%      { transform: translateY(-50%) scale(1.3); box-shadow: 0 0 0 8px {{ $dotGlowFade }}; }
    }
    .popular_product b {
        display: block; flex: 1; height: 1px;
        background: linear-gradient(90deg, transparent, {{ $dotLineColor }}, transparent);
        position: relative;
        transform: scaleX(0); transform-origin: center;
        animation: lineGrow .9s cubic-bezier(.22,.61,.36,1) .15s forwards;
    }
    .popular_product b:first-child{ transform-origin: right center; }
    .popular_product b:last-child { transform-origin: left center; }
    @keyframes lineGrow { to { transform: scaleX(1); } }

    @media (max-width: 991px){ .popular_product span{ padding: 0 30px; } }
    @media (max-width: 767px){
        .popular_product { margin-bottom: 14px; }
        .popular_product span { font-size: 20px; padding: 0 26px; }
        .popular_product span::before{ left: 5px; }
        .popular_product span::after { right: 5px; }
    }
    @media (max-width: 420px){
        .popular_product span { font-size: 17px; padding: 0 22px; }
        .popular_product span::before,
        .popular_product span::after{ width: 6px; height: 6px; }
    }

    .popular_swiper_wrap{
        position: relative; width: 100%;
        padding: 20px 0 36px; perspective: 1600px; z-index: 2;
    }
    .popular-swiper{
        width: 100%; overflow: hidden !important;
        padding: 20px 30px !important;
        -webkit-mask-image: linear-gradient(90deg, transparent 0, #000 8%, #000 92%, transparent 100%);
                mask-image: linear-gradient(90deg, transparent 0, #000 8%, #000 92%, transparent 100%);
    }
    .popular-swiper .swiper-wrapper{ align-items: center; }

    .popular-swiper .swiper-slide{
        width: 220px; height: auto;
        transition: filter .5s ease, opacity .5s ease;
        filter: blur(.4px) brightness(.88); opacity: .72;
        background: transparent !important;
        box-shadow: none !important;
    }
    .popular-swiper .swiper-slide-active{ filter: blur(0) brightness(1); opacity: 1; }
    .popular-swiper .swiper-slide-next,
    .popular-swiper .swiper-slide-prev{ filter: blur(.2px) brightness(.94); opacity: .9; }

    /* ✅ AGGRESSIVE SHADOW REMOVAL */
    .popular-swiper .pop-card,
    .popular-swiper .swiper-slide-active .pop-card,
    .popular-swiper .swiper-slide:hover .pop-card {
        box-shadow: none !important;
        border: 1px solid rgba(15,23,42,.06) !important;
    }
    .popular-swiper .pop-card::before,
    .popular-swiper .pop-card::after,
    .popular-swiper .swiper-slide-active .pop-card::before,
    .popular-swiper .swiper-slide-active .pop-card::after {
        display: none !important;
        opacity: 0 !important;
        background: transparent !important;
        content: none !important;
    }

    /* ✅ Kill ALL swiper coverflow / 3D shadows */
    .popular-swiper .swiper-cube-shadow,
    .popular-swiper .swiper-3d .swiper-slide-shadow,
    .popular-swiper .swiper-3d .swiper-slide-shadow-left,
    .popular-swiper .swiper-3d .swiper-slide-shadow-right,
    .popular-swiper .swiper-3d .swiper-slide-shadow-top,
    .popular-swiper .swiper-3d .swiper-slide-shadow-bottom,
    .popular-swiper .swiper-slide-shadow,
    .popular-swiper .swiper-slide-shadow-left,
    .popular-swiper .swiper-slide-shadow-right,
    .popular-swiper .swiper-slide-shadow-top,
    .popular-swiper .swiper-slide-shadow-bottom {
        display: none !important;
        background: transparent !important;
        background-image: none !important;
        opacity: 0 !important;
        visibility: hidden !important;
    }

    .pop-card {
        display: flex; flex-direction: column; align-items: center;
        text-decoration: none !important;
        background: #fff;
        border-radius: 22px;
        position: relative; padding: 16px 14px 14px; width: 100%;
        transition: transform .35s cubic-bezier(.22,.61,.36,1);
        isolation: isolate; overflow: hidden;
    }

    .pop-img {
        width: 100%; display: flex; align-items: center; justify-content: center;
        min-height: 140px;
        background: linear-gradient(180deg,#f5f8fd 0%, #e9eff8 100%);
        border-radius: 16px;
        margin-bottom: 12px; padding: 14px;
        position: relative; overflow: hidden;
        transition: background .5s ease;
    }
    .swiper-slide-active .pop-img{ background: linear-gradient(180deg, #eef4ff 0%, #dde8ff 100%); }

    .pop-card img {
        width: 100%; max-height: 110px; object-fit: contain;
        transition: transform .5s cubic-bezier(.22,.61,.36,1), filter .4s ease;
        filter: drop-shadow(0 8px 12px rgba(0,0,0,.08));
        position: relative; z-index: 1;
    }
    .swiper-slide-active .pop-card img{
        transform: scale(1.08);
        filter: drop-shadow(0 14px 22px rgba(0,39,108,.22));
    }

    .pop-title {
        font-family: 'Hind Siliguri', sans-serif; font-weight: 700;
        color: #1f2937; font-size: 16px; margin: 4px 0 0;
        text-align: center; width: 100%;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        transition: color .4s ease, transform .4s ease;
    }
    .swiper-slide-active .pop-title{ color: #0d6efd; transform: translateY(-2px); }
    .pop-card:hover{ transform: translateY(-4px); }

    @media (min-width: 1400px){ .popular-swiper .swiper-slide{ width: 240px; } }
    @media (min-width: 1200px) and (max-width: 1399.98px){ .popular-swiper .swiper-slide{ width: 220px; } }
    @media (min-width: 992px) and (max-width: 1199.98px){ .popular-swiper .swiper-slide{ width: 200px; } }
    @media (max-width: 991px){
        .popular-swiper .swiper-slide{ width: 180px; }
        .pop-img { min-height: 115px; }
        .pop-card img { max-height: 90px; }
    }
    @media (max-width: 767px){
        .popular_section{ padding: 18px 0 18px; }
        .popular_swiper_wrap{ padding: 14px 0 24px; perspective: 1000px; }
        .popular-swiper{
            padding: 12px 10px !important;
            -webkit-mask-image: linear-gradient(90deg, transparent 0, #000 5%, #000 95%, transparent 100%);
                    mask-image: linear-gradient(90deg, transparent 0, #000 5%, #000 95%, transparent 100%);
        }
        .popular-swiper .swiper-slide{ width: 150px; }
        .pop-card { border-radius: 18px; padding: 10px; }
        .pop-img { min-height: 92px; padding: 10px; border-radius: 12px; margin-bottom: 8px; }
        .pop-card img { max-height: 75px; }
        .pop-title { font-size: 13px; font-weight: 700; }
    }

    /* ============================================================
       PRODUCT SECTION
       ============================================================ */
    .product-section-wrap{
        background: #ffffff;
        padding: 24px 0 18px;
        position: relative;
        overflow: hidden;
    }
    .product-section-wrap::before,
    .product-section-wrap::after{
        content:""; position:absolute;
        width: 280px; height: 280px;
        border-radius: 50%;
        pointer-events:none; z-index: 0;
        opacity: .35; filter: blur(40px);
    }
    .product-section-wrap::before{
        top: -120px; left: -80px;
        background: radial-gradient(circle, rgba(13,110,253,.18), transparent 70%);
        animation: orbFloatA 14s ease-in-out infinite;
    }
    .product-section-wrap::after{
        bottom: -120px; right: -80px;
        background: radial-gradient(circle, rgba(0,39,108,.18), transparent 70%);
        animation: orbFloatB 18s ease-in-out infinite;
    }
    @keyframes orbFloatA{ 0%,100%{transform:translate(0,0);} 50%{transform:translate(40px,30px);} }
    @keyframes orbFloatB{ 0%,100%{transform:translate(0,0);} 50%{transform:translate(-30px,-40px);} }
    .product-section-wrap > *{ position: relative; z-index: 1; }

    .product-section-wrap .popular_product{ margin: 0 0 22px; }

    .cat-product-swiper{ padding: 4px 2px 10px; }
    .cat-product-swiper .swiper-slide{ height: auto; }

    .product-section-wrap .row > [class*="col"]{
        opacity: 0;
        transform: translateY(24px);
        transition: opacity .7s cubic-bezier(.22,.61,.36,1), transform .7s cubic-bezier(.22,.61,.36,1);
    }
    .product-section-wrap.in-view .row > [class*="col"]{ opacity: 1; transform: translateY(0); }
    .product-section-wrap.in-view .row > [class*="col"]:nth-child(1){ transition-delay: .05s; }
    .product-section-wrap.in-view .row > [class*="col"]:nth-child(2){ transition-delay: .10s; }
    .product-section-wrap.in-view .row > [class*="col"]:nth-child(3){ transition-delay: .15s; }
    .product-section-wrap.in-view .row > [class*="col"]:nth-child(4){ transition-delay: .20s; }
    .product-section-wrap.in-view .row > [class*="col"]:nth-child(5){ transition-delay: .25s; }
    .product-section-wrap.in-view .row > [class*="col"]:nth-child(6){ transition-delay: .30s; }

    /* CATEGORY VIEW ALL */
    .category-view-all-wrap{
        text-align: center;
        padding: 14px 0 4px;
        opacity: 0;
        transform: translateY(16px);
        transition: opacity .7s ease, transform .7s cubic-bezier(.22,.61,.36,1);
        transition-delay: .45s;
    }
    .product-section-wrap.in-view .category-view-all-wrap{
        opacity: 1; transform: translateY(0);
    }
    .category-view-all-btn{
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 9px 22px;
        border-radius: 999px;
        font-family: 'Hind Siliguri', sans-serif;
        font-size: 12.5px;
        font-weight: 800;
        letter-spacing: .4px;
        text-transform: uppercase;
        text-decoration: none !important;
        color: {{ $viewAllBtnColor }} !important;
        background: {{ $viewAllBtnBg }};
        border: 1px solid rgba(255,255,255,.18);
        box-shadow: 0 8px 18px rgba(0,0,0,.22);
        overflow: hidden;
        isolation: isolate;
        transition:
            transform .35s cubic-bezier(.22,.61,.36,1),
            box-shadow .35s ease,
            filter .3s ease;
    }
    .category-view-all-btn::before{
        content:""; position: absolute; top:0; left:-130%;
        width: 60%; height: 100%;
        background: linear-gradient(120deg, transparent, rgba(255,255,255,.55), transparent);
        transform: skewX(-18deg);
        transition: left .8s cubic-bezier(.22,.61,.36,1);
        z-index: -1;
    }
    .category-view-all-btn:hover::before{ left: 140%; }
    .category-view-all-btn:hover{
        transform: translateY(-2px) scale(1.04);
        box-shadow: 0 14px 26px rgba(0,0,0,.32);
        filter: brightness(1.06);
    }
    .category-view-all-btn i{ font-size: 10px; transition: transform .35s ease; }
    .category-view-all-btn:hover i{ transform: translateX(4px); }

    @media (max-width: 575px){
        .category-view-all-wrap{ padding: 10px 0 2px; }
        .category-view-all-btn{ padding: 8px 18px; font-size: 11.5px; gap: 6px; }
        .category-view-all-btn i{ font-size: 9px; }
    }

    /* ✅ Popular section reveal kept, Featured section NO REVEAL */
    .popular_section{
        opacity: 0; transform: translateY(20px);
        transition: opacity .8s ease, transform .8s cubic-bezier(.22,.61,.36,1);
    }
    .popular_section.in-view{
        opacity: 1; transform: translateY(0);
    }

    /* BOTTOM CTA */
    .bottom-cta-container { text-align: center; margin-top: 22px; margin-bottom: 36px; }
    .bottom-view-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        padding: 10px 24px;
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #fff !important;
        border-radius: 999px;
        text-decoration: none;
        background: linear-gradient(135deg, #0d6efd, #00276C);
        box-shadow: 0 8px 20px rgba(13,110,253,.30);
        transition: all .35s cubic-bezier(.22,.61,.36,1);
        border: 1px solid rgba(255,255,255,.16);
        position: relative; overflow: hidden;
        isolation: isolate;
    }
    .bottom-view-btn::before{
        content:""; position:absolute; top:0; left:-120%;
        width: 60%; height:100%;
        background: linear-gradient(120deg, transparent, rgba(255,255,255,.55), transparent);
        transform: skewX(-18deg);
        transition: left .8s cubic-bezier(.22,.61,.36,1);
        z-index: -1;
    }
    .bottom-view-btn:hover::before{ left: 140%; }
    .bottom-view-btn:hover {
        transform: translateY(-3px) scale(1.03);
        box-shadow: 0 14px 28px rgba(13,110,253,.42);
        filter: brightness(1.06);
    }
    .bottom-view-btn i{ font-size: 11px; transition: transform .35s ease; }
    .bottom-view-btn:hover i{ transform: translateX(5px); }
    @media (max-width: 575px){
        .bottom-cta-container{ margin-top: 16px; margin-bottom: 26px; }
        .bottom-view-btn{ padding: 9px 20px; font-size: 12px; gap: 6px; }
        .bottom-view-btn i{ font-size: 10px; }
    }

    @media (prefers-reduced-motion: reduce){
        *, *::before, *::after {
            animation-duration: .001ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .001ms !important;
        }
    }
</style>

<main class="main-wrapper">

    {{-- ✅ DESKTOP SLIDER --}}
    <div class="desktop-slide container mt-3">
        <div class="hero-slider-wrap">
            <div class="swiper main-swiper desktop-swiper">
                <div class="swiper-wrapper">
                    @foreach($sliders as $s)
                        <div class="swiper-slide">
                            <a href="{{$s->link}}">
                                <img src="{{ getImage('sliders', $s->image) }}" alt="Slider Image">
                            </a>
                        </div>
                    @endforeach
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>

    {{-- ✅ MOBILE SLIDER --}}
    <div class="mobile-slide container mt-3">
        <div class="hero-slider-wrap" style="margin-top:0;">
            <div class="swiper main-swiper mobile-swiper">
                <div class="swiper-wrapper">
                    @foreach($sliders as $s)
                        <div class="swiper-slide">
                            <a href="{{$s->link}}">
                                <img src="{{ getImage('mobile_sliders', $s->mobile_image) }}" alt="Mobile Slider">
                            </a>
                        </div>
                    @endforeach
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>

    {{-- ✅ FEATURED COLLECTIONS — Static, no animation --}}
    @if(!empty($featured_images))
    <div class="featured-section">
        <div class="container mt-3 mb-2">
            <div class="popular_product" style="margin-bottom: 30px;">
                <b></b><span>FEATURED COLLECTIONS</span><b></b>
            </div>

            <div class="featured-grid-container px-2 px-md-3">
                <div class="left-4-grid">
                    <a href="{{ $featured_images->left_link_1 ?? '#' }}" class="grid-img-wrapper">
                        <img src="{{ $featured_images->left_image_1 ? asset('homeimages/'.$featured_images->left_image_1) : 'https://via.placeholder.com/400?text=No+Image' }}" alt="Featured 1">
                    </a>
                    <a href="{{ $featured_images->left_link_2 ?? '#' }}" class="grid-img-wrapper">
                        <img src="{{ $featured_images->left_image_2 ? asset('homeimages/'.$featured_images->left_image_2) : 'https://via.placeholder.com/400?text=No+Image' }}" alt="Featured 2">
                    </a>
                    <a href="{{ $featured_images->left_link_3 ?? '#' }}" class="grid-img-wrapper">
                        <img src="{{ $featured_images->left_image_3 ? asset('homeimages/'.$featured_images->left_image_3) : 'https://via.placeholder.com/400?text=No+Image' }}" alt="Featured 3">
                    </a>
                    <a href="{{ $featured_images->left_link_4 ?? '#' }}" class="grid-img-wrapper">
                        <img src="{{ $featured_images->left_image_4 ? asset('homeimages/'.$featured_images->left_image_4) : 'https://via.placeholder.com/400?text=No+Image' }}" alt="Featured 4">
                    </a>
                </div>

                <div class="right-1-grid">
                    <a href="{{ $featured_images->right_link ?? '#' }}" class="grid-img-wrapper">
                        <img src="{{ $featured_images->right_image ? asset('homeimages/'.$featured_images->right_image) : 'https://via.placeholder.com/800?text=No+Image' }}" alt="Featured Large">
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ✅ PRODUCTS LOOP --}}
    @foreach ($homeProducts as $categoryId => $products)
        @php
            $catUrl  = $products->first()->category->url ?? null;
            $catName = $products->first()->category->name ?? '';
        @endphp

        <div class="product-section-wrap reveal-section">
            <div class="container">

                @if(!empty($products->first()->category->id))
                    <div class="popular_product">
                        <b></b><span>{{ $catName }}</span><b></b>
                    </div>
                @endif

                <div class="swiper cat-product-swiper">
                    <div class="swiper-wrapper">
                        @foreach($products as $product)
                            <div class="swiper-slide">
                                @include('frontend.products.partials.product_section', ['adminText' => $adminText])
                            </div>
                        @endforeach
                    </div>
                </div>

                @if($catUrl)
                    <div class="category-view-all-wrap">
                        <a href="{{ route('front.category', [$catUrl]) }}" class="category-view-all-btn">
                            {{ $viewAllText }} <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                @endif

            </div>
        </div>
    @endforeach

    <div class="bottom-cta-container">
        <a href="{{ route('front.products.index') }}" class="bottom-view-btn">
            View All Products <i class="fas fa-arrow-right"></i>
        </a>
    </div>

</main>

@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Category product sliders — one per home category, auto-sliding.
    document.querySelectorAll('.cat-product-swiper').forEach(function (el) {
        new Swiper(el, {
            slidesPerView: 2, spaceBetween: 10,
            rewind: true, speed: 600, grabCursor: true,
            autoplay: { delay: 3000, disableOnInteraction: false, pauseOnMouseEnter: true },
            breakpoints: {
                576:  { slidesPerView: 3, spaceBetween: 12 },
                768:  { slidesPerView: 4, spaceBetween: 14 },
                992:  { slidesPerView: 5, spaceBetween: 16 },
                1200: { slidesPerView: 6, spaceBetween: 16 }
            }
        });
    });

    const DESKTOP_DELAY = 5500;
    const desktopSwiper = new Swiper('.desktop-swiper', {
        loop: true, effect: 'creative', speed: 1600, grabCursor: true,
        creativeEffect: {
            prev: { translate: ['-8%', 0, 0], opacity: 0 },
            next: { translate: ['8%', 0, 0], opacity: 0 }
        },
        autoplay: { delay: DESKTOP_DELAY, disableOnInteraction: false, pauseOnMouseEnter: true },
        observer: true, observeParents: true,
        pagination: { el: '.desktop-swiper .swiper-pagination', clickable: true },
        navigation: {
            nextEl: '.desktop-swiper .swiper-button-next',
            prevEl: '.desktop-swiper .swiper-button-prev'
        },
        on: {
            init(sw){ sw.el.style.setProperty('--pg-dur', (DESKTOP_DELAY/1000) + 's'); restartPagination(sw); },
            slideChangeTransitionStart(sw){ restartPagination(sw); }
        }
    });

    const MOBILE_DELAY = 4500;
    const mobileSwiper = new Swiper('.mobile-swiper', {
        loop: true, effect: 'creative', speed: 1300, grabCursor: true,
        creativeEffect: {
            prev: { translate: [0, '-6%', 0], opacity: 0 },
            next: { translate: [0, '6%', 0], opacity: 0 }
        },
        autoplay: { delay: MOBILE_DELAY, disableOnInteraction: false },
        observer: true, observeParents: true,
        pagination: { el: '.mobile-swiper .swiper-pagination', clickable: true },
        on: {
            init(sw){ sw.el.style.setProperty('--pg-dur', (MOBILE_DELAY/1000) + 's'); restartPagination(sw); },
            slideChangeTransitionStart(sw){ restartPagination(sw); }
        }
    });

    function restartPagination(sw){
        const active = sw.pagination.el && sw.pagination.el.querySelector('.swiper-pagination-bullet-active');
        if(!active) return;
        const fresh = active.cloneNode(true);
        active.parentNode.replaceChild(fresh, active);
    }

    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });
        document.querySelectorAll('.reveal-section').forEach(el => io.observe(el));
    } else {
        document.querySelectorAll('.reveal-section').forEach(el => el.classList.add('in-view'));
    }
});
</script>

<script>
(function(){
    function getCsrf(){
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    document.addEventListener('click', function(e){
        const btn = e.target.closest('button[type="submit"]');
        if(!btn) return;
        const form = btn.closest('form');
        if(!form) return;

        if(form.getAttribute('action') && form.getAttribute('action').includes('carts')){
            form.classList.add('cart_form');
            let at = form.querySelector('input[name="action_type"]');
            if(!at){
                at = document.createElement('input');
                at.type = 'hidden';
                at.name = 'action_type';
                form.appendChild(at);
            }
            at.value = btn.getAttribute('data-action') || 'order';
        }
    });

    document.addEventListener('submit', function(e){
        const form = e.target;
        if(!form || !form.classList.contains('cart_form')) return;
        e.preventDefault();

        const actionUrl = form.getAttribute('action');
        const fd = new FormData(form);

        fetch(actionUrl, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': getCsrf() },
            body: fd
        })
        .then(r => r.json())
        .then(res => {
            if(res && res.url){ window.location.href = res.url; return; }
            window.location.reload();
        })
        .catch(() => window.location.reload());
    }, true);
})();
</script>
@endpush