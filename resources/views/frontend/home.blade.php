@extends('frontend.app')

@section('content')

@php
    use App\Models\AdminText;
    use App\Models\Information;
    $adminText   = AdminText::first();
    $viewAllText = $adminText->view_all_text ?? 'View All';
    $info        = Information::orderBy('id', 'desc')->first();

    // Featured Collections: up to 4 products from each home category, interleaved so
    // "All" shows a mix. Each card remembers its category for the tab filter.
    $featuredTabs  = [];
    $featuredItems = [];
    $perCat = 4;
    foreach ($homeProducts as $catId => $products) {
        $cat = $products->first()->category ?? null;
        if ($cat) $featuredTabs[$catId] = $cat->name;
    }
    for ($i = 0; $i < $perCat; $i++) {
        foreach ($homeProducts as $catId => $products) {
            if (isset($products[$i])) $featuredItems[] = ['cat' => $catId, 'product' => $products[$i]];
        }
    }

    // A marquee row needs enough tiles to run past the screen edge; short rows are repeated.
    $marqueeRow = function ($tiles) {
        $tiles = collect($tiles);
        if ($tiles->isEmpty()) return $tiles;
        $times = (int) max(1, ceil(8 / $tiles->count()));
        $out = collect();
        for ($i = 0; $i < $times; $i++) $out = $out->concat($tiles);
        return $out;
    };
@endphp

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
@endpush

<style>
    /* Accent = the site's brand colour (themeAccent()), with darker and soft shades mixed from it. */
    :root{
        --hp-accent: {{ themeAccent('#be1e30') }};
        --hp-accent-dark: color-mix(in srgb, var(--hp-accent) 80%, #000);
        --hp-soft: color-mix(in srgb, var(--hp-accent) 8%, #fff);
        --hp-ink: #111827; --hp-muted: #6b7280; --hp-line: #e8eaee;
        --hp-space: 44px; --hp-gap: 16px; --hp-radius: 12px;
    }
    @media (max-width: 767.98px){ :root{ --hp-space: 28px; --hp-gap: 10px; } }
    @media (min-width: 1400px){ .hp .container{ max-width: 1360px; } }

    .hp{ background: #fff; }
    .hp .swiper:not(.swiper-initialized) .swiper-slide{ display: none; }
    .hp .swiper:not(.swiper-initialized) .swiper-slide:first-child{ display: block; }
    .hp-section{ padding-top: var(--hp-space); }

    /* ---------- section heading: red bar + title, link and arrows on the right ---------- */
    .hp-head{ display: flex; align-items: center; gap: 14px; margin-bottom: 18px; flex-wrap: wrap; }
    .hp-title{
        position: relative; margin: 0; padding-left: 16px;
        font-size: 28px; font-weight: 800; color: var(--hp-ink); line-height: 1.15; letter-spacing: -.01em;
    }
    .hp-title::before{ content: ""; position: absolute; left: 0; top: 3px; bottom: 3px; width: 5px; border-radius: 3px; background: var(--hp-accent); }
    .hp-head-end{ margin-left: auto; display: flex; align-items: center; gap: 10px; }
    .hp-link{ font-size: 15px; font-weight: 700; color: var(--hp-accent) !important; text-decoration: none !important; white-space: nowrap; }
    .hp-link i{ font-size: 12px; margin-left: 4px; transition: transform .2s ease; }
    .hp-link:hover i{ transform: translateX(4px); }
    .hp-arrows{ display: flex; gap: 8px; }
    .hp-arrow{
        width: 36px; height: 36px; border-radius: 50%; border: 1px solid var(--hp-line); background: #fff;
        display: flex; align-items: center; justify-content: center; color: var(--hp-ink); font-size: 13px;
        cursor: pointer; transition: background .2s ease, color .2s ease, border-color .2s ease;
    }
    .hp-arrow:hover{ background: var(--hp-accent); border-color: var(--hp-accent); color: #fff; }
    .hp-arrow.swiper-button-disabled{ opacity: .4; pointer-events: none; }
    @media (max-width: 767.98px){
        .hp-head{ margin-bottom: 12px; gap: 10px; }
        .hp-title{ font-size: 20px; padding-left: 12px; }
        .hp-title::before{ width: 4px; }
        .hp-link{ font-size: 13px; }
        .hp-arrows{ display: none; }
    }

    /* ---------- hero slider ---------- */
    .hp-hero{ padding-top: 16px; }
    .hp-hero-box{ position: relative; border-radius: 16px; overflow: hidden; background: #f3f4f6; isolation: isolate; }
    .hp-hero .swiper-slide{ width: 100%; flex-shrink: 0; }
    .hp-hero .swiper-slide img{ display: block; width: 100%; height: auto; }
    .hp-hero-nav{
        position: absolute; top: 50%; z-index: 5; transform: translateY(-50%);
        width: 46px; height: 46px; border-radius: 50%; border: 0; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        background: rgba(17,24,39,.55); color: #fff; font-size: 16px;
        backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);
        transition: background .2s ease, transform .2s ease;
    }
    .hp-hero-nav:hover{ background: var(--hp-accent); transform: translateY(-50%) scale(1.06); }
    .hp-hero-nav.is-prev{ left: 18px; } .hp-hero-nav.is-next{ right: 18px; }
    .hp-hero .swiper-pagination{ bottom: 14px !important; }
    .hp-hero .swiper-pagination-bullet{ width: 9px; height: 9px; background: rgba(255,255,255,.75); opacity: 1; transition: width .3s ease, background .3s ease; border-radius: 9px; }
    .hp-hero .swiper-pagination-bullet-active{ width: 26px; background: var(--hp-accent); }
    .hp-hero .is-mobile{ display: none; }
    @media (max-width: 991.98px){
        .hp-hero .is-desktop{ display: none; }
        .hp-hero .is-mobile{ display: block; }
        .hp-hero-box{ border-radius: 12px; }
        .hp-hero-nav{ display: none; }
    }

    /* ---------- features strip ---------- */
    .hp-features{
        display: grid; grid-template-columns: repeat(4, minmax(0, 1fr));
        margin-top: 18px; background: #fff; border: 1px solid var(--hp-line); border-radius: var(--hp-radius);
        box-shadow: 0 6px 20px -12px rgba(15,23,42,.15);
    }
    .hp-feature{ display: flex; align-items: center; gap: 14px; padding: 18px 20px; position: relative; min-width: 0; }
    .hp-feature + .hp-feature::before{ content: ""; position: absolute; left: 0; top: 22%; bottom: 22%; width: 1px; background: var(--hp-line); }
    .hp-feature i{ flex: 0 0 auto; font-size: 30px; color: var(--hp-accent); width: 36px; text-align: center; }
    .hp-feature strong{ display: block; font-size: 16px; font-weight: 700; color: var(--hp-ink); line-height: 1.25; }
    .hp-feature small{ display: block; font-size: 13px; color: var(--hp-muted); line-height: 1.35; margin-top: 2px; }
    @media (max-width: 1199.98px){
        .hp-feature{ padding: 16px 14px; gap: 10px; }
        .hp-feature i{ font-size: 24px; width: 28px; }
        .hp-feature strong{ font-size: 14.5px; }
        .hp-feature small{ font-size: 12px; }
    }
    @media (max-width: 991.98px){
        /* phones and tablets: a compact 2 x 2 grid */
        .hp-features{ grid-template-columns: repeat(2, minmax(0, 1fr)); margin-top: 12px; }
        .hp-feature{ padding: 11px 12px; gap: 9px; align-items: flex-start; }
        .hp-feature + .hp-feature::before{ display: none; }
        .hp-feature:nth-child(even){ border-left: 1px solid var(--hp-line); }
        .hp-feature:nth-child(n+3){ border-top: 1px solid var(--hp-line); }
        .hp-feature i{ font-size: 19px; width: 22px; margin-top: 2px; }
        .hp-feature strong{ font-size: 13px; }
        .hp-feature small{ font-size: 11px; line-height: 1.3; }
    }

    /* ---------- popular category cards ---------- */
    .hp-cat{
        display: flex; flex-direction: column; height: 100%; overflow: hidden; text-decoration: none !important;
        background: #fff; border: 1px solid var(--hp-line); border-radius: var(--hp-radius);
        transition: transform .3s ease, box-shadow .3s ease, border-color .3s ease;
    }
    .hp-cat-img{ aspect-ratio: 16 / 10; background: linear-gradient(180deg, #f8f9fb, #eef0f4); display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .hp-cat-img{ position: relative; }
    .hp-cat-img img{ width: 100%; height: 100%; object-fit: cover; transition: transform .6s cubic-bezier(.22,.61,.36,1); }
    /* same high-quality photo look as the product cards */
    .hp-cat-img img, .hp-tile img{ filter: contrast(1.06) saturate(1.12) brightness(1.02); image-rendering: high-quality; }
    .hp-cat-body{ padding: 12px 10px 14px; text-align: center; }
    .hp-cat-name{ display: block; font-size: 17px; font-weight: 700; color: var(--hp-ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .hp-cat-cta{ display: inline-block; margin-top: 2px; font-size: 14px; font-weight: 600; color: var(--hp-accent); }
    .hp-cat-cta i{ font-size: 11px; margin-left: 3px; transition: transform .2s ease; }
    .hp-cat:hover{ transform: translateY(-4px); border-color: color-mix(in srgb, var(--hp-accent) 30%, var(--hp-line)); box-shadow: 0 18px 32px -18px rgba(15,23,42,.35); }
    .hp-cat:hover .hp-cat-img img{ transform: scale(1.06); }
    .hp-cat:hover .hp-cat-cta i{ transform: translateX(4px); }
    @media (max-width: 575.98px){
        .hp-cat-name{ font-size: 14px; }
        .hp-cat-cta{ font-size: 12.5px; }
        .hp-cat-body{ padding: 9px 6px 10px; }
    }

    /* ---------- shop by collection: two rows scrolling at the same time ---------- */
    .hp-marquee{ display: flex; flex-direction: column; gap: var(--hp-gap); overflow: hidden;
        -webkit-mask-image: linear-gradient(90deg, transparent 0, #000 4%, #000 96%, transparent 100%);
                mask-image: linear-gradient(90deg, transparent 0, #000 4%, #000 96%, transparent 100%); }
    /* Moved by the script at the bottom: scrolls by itself, and can be dragged / swiped. */
    .hp-marquee-row{ display: flex; width: max-content; will-change: transform; cursor: grab; touch-action: pan-y; user-select: none; -webkit-user-select: none; }
    .hp-marquee-row.is-dragging{ cursor: grabbing; }
    .hp-marquee-row.is-dragging a{ pointer-events: none; }
    .hp-marquee-row img{ -webkit-user-drag: none; user-select: none; }
    .hp-marquee-set{ display: flex; gap: var(--hp-gap); padding-right: var(--hp-gap); }

    .hp-tile{ flex: 0 0 auto; display: block; text-decoration: none !important; border-radius: var(--hp-radius); overflow: hidden; position: relative; }
    .hp-tile.is-banner{ width: 360px; height: 200px; background: #f3f4f6; }
    .hp-tile.is-banner img{ width: 100%; height: 100%; object-fit: cover; transition: transform .6s ease; }
    .hp-tile.is-banner:hover img{ transform: scale(1.05); }
    .hp-tile.is-category{
        width: 300px; display: flex; align-items: center; gap: 14px; padding: 12px;
        background: #fff; border: 1px solid var(--hp-line); transition: border-color .25s ease, box-shadow .25s ease;
    }
    .hp-tile.is-category img{ flex: 0 0 auto; width: 92px; height: 92px; border-radius: 10px; object-fit: cover; background: #f3f4f6; }
    .hp-tile-text{ min-width: 0; display: flex; flex-direction: column; gap: 3px; }
    .hp-tile-name{ font-size: 16.5px; font-weight: 700; color: var(--hp-ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .hp-tile-count{ font-size: 13px; color: var(--hp-muted); }
    .hp-tile-cta{ font-size: 13.5px; font-weight: 700; color: var(--hp-accent); margin-top: 4px; }
    .hp-tile-cta i{ font-size: 10px; margin-left: 3px; }
    .hp-tile.is-category:hover{ border-color: color-mix(in srgb, var(--hp-accent) 35%, var(--hp-line)); box-shadow: 0 14px 28px -18px rgba(15,23,42,.35); }
    @media (max-width: 767.98px){
        .hp-tile.is-banner{ width: 250px; height: 140px; }
        .hp-tile.is-category{ width: 230px; padding: 9px; gap: 10px; }
        .hp-tile.is-category img{ width: 64px; height: 64px; }
        .hp-tile-name{ font-size: 14px; }
        .hp-tile-count, .hp-tile-cta{ font-size: 12px; }
    }

    /* ---------- featured collections tabs ---------- */
    .hp-tabs{ display: flex; gap: 8px; overflow-x: auto; scrollbar-width: none; margin-left: auto; max-width: 100%; }
    .hp-tabs::-webkit-scrollbar{ display: none; }
    .hp-tab{
        flex: 0 0 auto; width: auto; height: auto; margin: 0; padding: 7px 18px; border-radius: 999px; border: 1px solid var(--hp-line); background: #fff;
        font-size: 14px; font-weight: 500; color: #374151; cursor: pointer; transition: all .2s ease; white-space: nowrap;
    }
    .hp-tab:hover{ border-color: var(--hp-accent); color: var(--hp-accent); }
    .hp-tab.is-active{ background: var(--hp-accent); border-color: var(--hp-accent); color: #fff; font-weight: 700; }
    @media (max-width: 991.98px){ .hp-head.has-tabs .hp-tabs{ margin-left: 0; width: 100%; order: 3; } }
    @media (max-width: 575.98px){ .hp-tab{ padding: 6px 14px; font-size: 13px; } }

    /* ---------- product sliders ---------- */
    .hp-products .swiper-slide{ height: auto; }
    .hp-products .swiper-slide > *{ height: 100%; }
    .hp-products{ padding: 2px 2px 8px; }

    .hp-more{ text-align: center; padding-top: var(--hp-space); }
    .hp-more a{
        display: inline-flex; align-items: center; gap: 10px; padding: 13px 30px; border-radius: 8px;
        background: var(--hp-accent); color: #fff !important; text-decoration: none !important;
        font-size: 15px; font-weight: 700; transition: background .2s ease, transform .2s ease;
    }
    .hp-more a:hover{ background: var(--hp-accent-dark); transform: translateY(-2px); }

    /* ---------- bottom strip ---------- */
    .hp-strip{ margin-top: var(--hp-space); background: #f6f7f9; border-top: 1px solid var(--hp-line); }
    .hp-strip-grid{ display: grid; grid-template-columns: 1.35fr 1fr 1.25fr 1fr auto; align-items: center; gap: 0; padding: 24px 0; }
    .hp-strip-item{ display: flex; align-items: center; gap: 14px; padding: 6px 22px; position: relative; min-width: 0; }
    .hp-strip-item + .hp-strip-item::before{ content: ""; position: absolute; left: 0; top: 10%; bottom: 10%; width: 1px; background: #dcdfe4; }
    .hp-strip-item:first-child{ padding-left: 0; }
    .hp-strip-item > i{ flex: 0 0 auto; font-size: 32px; color: var(--hp-accent); }
    .hp-strip-item strong{ display: block; font-size: 16px; color: var(--hp-ink); line-height: 1.25; }
    .hp-strip-item small{ display: block; font-size: 13px; color: var(--hp-muted); line-height: 1.4; }
    .hp-strip-shop small{ font-size: 13.5px; color: #374151; }
    .hp-strip-shop .hp-strip-name{ font-size: 24px; font-weight: 800; color: var(--hp-accent); line-height: 1.15; }
    .hp-strip-shop .hp-strip-addr{ font-size: 13px; color: var(--hp-muted); }
    .hp-contact{
        display: flex; align-items: center; gap: 12px; margin-left: 22px; padding: 14px 24px; border-radius: 10px;
        background: var(--hp-accent); color: #fff !important; text-decoration: none !important; transition: background .2s ease, transform .2s ease;
    }
    .hp-contact:hover{ background: var(--hp-accent-dark); transform: translateY(-2px); }
    .hp-contact i{ font-size: 26px; }
    .hp-contact span{ display: flex; flex-direction: column; line-height: 1.25; }
    .hp-contact small{ font-size: 15px; opacity: .95; }
    .hp-contact strong{ font-size: 18px; white-space: nowrap; }
    @media (max-width: 1199.98px){
        .hp-strip-grid{ grid-template-columns: repeat(2, minmax(0, 1fr)); row-gap: 18px; }
        .hp-strip-item{ padding: 4px 0; }
        .hp-strip-item + .hp-strip-item::before{ display: none; }
        .hp-contact{ margin-left: 0; justify-self: start; }
    }
    @media (max-width: 575.98px){
        .hp-strip-grid{ grid-template-columns: 1fr; padding: 20px 0; row-gap: 14px; }
        .hp-strip-shop .hp-strip-name{ font-size: 20px; }
        .hp-contact{ justify-self: stretch; justify-content: center; }
    }

    @media (prefers-reduced-motion: reduce){
        .hp-cat, .hp-cat-img img, .hp-tile img, .hp-more a, .hp-contact{ transition: none; }
    }
</style>

<main class="main-wrapper hp">

    {{-- HERO --}}
    @if($sliders->isNotEmpty())
    <section class="hp-hero">
        <div class="container">
            <div class="hp-hero-box is-desktop">
                <div class="swiper hp-hero-swiper" data-variant="desktop">
                    <div class="swiper-wrapper">
                        @foreach($sliders as $s)
                            <div class="swiper-slide">
                                <a href="{{ $s->link ?: route('front.products.index') }}">
                                    <img src="{{ getImage('sliders', $s->image) }}" alt="Banner {{ $loop->iteration }}" @if(!$loop->first) loading="lazy" @endif>
                                </a>
                            </div>
                        @endforeach
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
                <button type="button" class="hp-hero-nav is-prev" aria-label="Previous slide"><i class="fas fa-chevron-left"></i></button>
                <button type="button" class="hp-hero-nav is-next" aria-label="Next slide"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="hp-hero-box is-mobile">
                <div class="swiper hp-hero-swiper" data-variant="mobile">
                    <div class="swiper-wrapper">
                        @foreach($sliders as $s)
                            <div class="swiper-slide">
                                <a href="{{ $s->link ?: route('front.products.index') }}">
                                    <img src="{{ getImage('mobile_sliders', $s->mobile_image) }}" alt="Banner {{ $loop->iteration }}" @if(!$loop->first) loading="lazy" @endif>
                                </a>
                            </div>
                        @endforeach
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- FEATURES --}}
    <div class="container">
        <div class="hp-features">
            <div class="hp-feature"><i class="fas fa-shipping-fast"></i><div><strong>Direct Import</strong><small>China | India | Dubai | Malaysia | USA | UK</small></div></div>
            <div class="hp-feature"><i class="fas fa-shield-alt"></i><div><strong>Quality Assured</strong><small>Premium &amp; Tested Products</small></div></div>
            <div class="hp-feature"><i class="fas fa-box-open"></i><div><strong>Wide Range</strong><small>{{ $productCount }}+ Wholesale Products</small></div></div>
            <div class="hp-feature"><i class="fas fa-users"></i><div><strong>Best Wholesale Price</strong><small>Ideal for Resellers</small></div></div>
        </div>
    </div>

    {{-- POPULAR CATEGORY --}}
    @if($popularCategories->isNotEmpty())
    <section class="hp-section">
        <div class="container">
            <div class="hp-head">
                <h2 class="hp-title">Popular Category</h2>
                <div class="hp-head-end">
                    <a href="{{ route('front.categories') }}" class="hp-link">View All Categories <i class="fas fa-arrow-right"></i></a>
                    <div class="hp-arrows">
                        <button type="button" class="hp-arrow js-prev" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
                        <button type="button" class="hp-arrow js-next" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
            </div>
            <div class="swiper hp-products js-auto-swiper" data-kind="category">
                <div class="swiper-wrapper">
                    @foreach($popularCategories as $pc)
                        <div class="swiper-slide">
                            <a href="{{ route('front.category', [$pc->url]) }}" class="hp-cat">
                                <span class="hp-cat-img"><img src="{{ asset('categories/' . $pc->image) }}" alt="{{ $pc->name }}" loading="lazy" decoding="async"></span>
                                <span class="hp-cat-body">
                                    <span class="hp-cat-name">{{ $pc->name }}</span>
                                    <span class="hp-cat-cta">View Products <i class="fas fa-arrow-right"></i></span>
                                </span>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- SHOP BY COLLECTION: two rows moving at the same time --}}
    @if(!empty($collectionRows))
    <section class="hp-section">
        <div class="container">
            <div class="hp-head">
                <h2 class="hp-title">Shop by Collection</h2>
                <div class="hp-head-end">
                    <a href="{{ route('front.products.index') }}" class="hp-link">{{ $viewAllText }} <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="hp-marquee">
                @foreach($collectionRows as $r => $row)
                    @php $tiles = $marqueeRow($row); @endphp
                    <div class="hp-marquee-row {{ $r % 2 ? 'is-reverse' : '' }}" data-duration="{{ max(30, $tiles->count() * 5) }}">
                        @foreach([0, 1] as $copy)
                            <div class="hp-marquee-set" @if($copy) aria-hidden="true" @endif>
                                @foreach($tiles as $t)
                                    @if($t['type'] === 'banner')
                                        <a href="{{ $t['link'] }}" class="hp-tile is-banner" @if($copy) tabindex="-1" @endif>
                                            <img src="{{ $t['image'] }}" alt="Collection" loading="lazy" decoding="async" draggable="false">
                                        </a>
                                    @else
                                        <a href="{{ $t['link'] }}" class="hp-tile is-category" @if($copy) tabindex="-1" @endif>
                                            <img src="{{ $t['image'] }}" alt="{{ $t['name'] }}" loading="lazy" decoding="async" draggable="false">
                                            <span class="hp-tile-text">
                                                <span class="hp-tile-name">{{ $t['name'] }}</span>
                                                <span class="hp-tile-count">{{ $t['count'] }} {{ $t['count'] === 1 ? 'product' : 'products' }}</span>
                                                <span class="hp-tile-cta">Shop Now <i class="fas fa-arrow-right"></i></span>
                                            </span>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- FEATURED COLLECTIONS: mixed products, filtered by category tabs --}}
    @if(!empty($featuredItems))
    <section class="hp-section">
        <div class="container">
            <div class="hp-head has-tabs">
                <h2 class="hp-title">Featured Collections</h2>
                <div class="hp-tabs" role="tablist" aria-label="Filter featured products">
                    <button type="button" class="hp-tab is-active" data-cat="all" role="tab" aria-selected="true">All</button>
                    @foreach($featuredTabs as $catId => $catName)
                        <button type="button" class="hp-tab" data-cat="{{ $catId }}" role="tab" aria-selected="false">{{ $catName }}</button>
                    @endforeach
                </div>
            </div>
            <div class="swiper hp-products js-auto-swiper" id="hpFeatured" data-kind="product">
                <div class="swiper-wrapper">
                    @foreach($featuredItems as $item)
                        @php $product = $item['product']; @endphp
                        <div class="swiper-slide" data-cat="{{ $item['cat'] }}">
                            @include('frontend.products.partials.product_section', ['adminText' => $adminText])
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- ONE AUTO-SCROLLING SLIDER PER HOME CATEGORY --}}
    @foreach ($homeProducts as $categoryId => $products)
        @php
            $catUrl  = $products->first()->category->url ?? null;
            $catName = $products->first()->category->name ?? '';
        @endphp
        <section class="hp-section">
            <div class="container">
                <div class="hp-head">
                    <h2 class="hp-title">{{ $catName }}</h2>
                    <div class="hp-head-end">
                        @if($catUrl)
                            <a href="{{ route('front.category', [$catUrl]) }}" class="hp-link">{{ $viewAllText }} <i class="fas fa-arrow-right"></i></a>
                        @endif
                        <div class="hp-arrows">
                            <button type="button" class="hp-arrow js-prev" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
                            <button type="button" class="hp-arrow js-next" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
                <div class="swiper hp-products js-auto-swiper" data-kind="product">
                    <div class="swiper-wrapper">
                        @foreach($products as $product)
                            <div class="swiper-slide">
                                @include('frontend.products.partials.product_section', ['adminText' => $adminText])
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endforeach

    <div class="hp-more">
        <a href="{{ route('front.products.index') }}">View All Products <i class="fas fa-arrow-right"></i></a>
    </div>

    {{-- BOTTOM STRIP --}}
    <section class="hp-strip">
        <div class="container">
            <div class="hp-strip-grid">
                <div class="hp-strip-item hp-strip-shop">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <small>Visit Our Wholesale Shop</small>
                        <span class="hp-strip-name d-block">{{ $info->site_name ?: config('app.name') }}</span>
                        @if(!empty($info->address))<span class="hp-strip-addr d-block">{{ $info->address }}</span>@endif
                    </div>
                </div>
                <div class="hp-strip-item"><i class="fas fa-handshake"></i><div><strong>Wholesale Only</strong><small>Retail Not Available</small></div></div>
                <div class="hp-strip-item"><i class="fas fa-globe-asia"></i><div><strong>Imported Products</strong><small>China | India | Dubai | Malaysia | USA | UK</small></div></div>
                <div class="hp-strip-item"><i class="fas fa-shield-alt"></i><div><strong>Genuine Quality</strong><small>Trusted by Resellers</small></div></div>
                @if(!empty($info->owner_phone))
                    <a href="tel:{{ $info->owner_phone }}" class="hp-contact">
                        <i class="fas fa-phone-alt"></i>
                        <span><small>Contact Us</small><strong>{{ $info->owner_phone }}</strong></span>
                    </a>
                @endif
            </div>
        </div>
    </section>

</main>

@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    function autoplay(delay){ return reduceMotion ? false : { delay: delay, disableOnInteraction: false, pauseOnMouseEnter: true }; }

    // Hero: desktop and mobile banners (only one of the two is visible).
    document.querySelectorAll('.hp-hero-swiper').forEach(function (el) {
        var box = el.closest('.hp-hero-box');
        new Swiper(el, {
            loop: el.querySelectorAll('.swiper-slide').length > 1,
            speed: 800, grabCursor: true,
            autoplay: autoplay(el.dataset.variant === 'mobile' ? 4500 : 5500),
            pagination: { el: el.querySelector('.swiper-pagination'), clickable: true },
            navigation: { prevEl: box.querySelector('.is-prev'), nextEl: box.querySelector('.is-next') }
        });
    });

    // Category and product sliders: every one scrolls by itself.
    var layouts = {
        category: { base: 2.2, gap: 10, bp: { 576: [3, 12], 768: [4, 14], 992: [5, 16], 1200: [6, 16] }, delay: 2800 },
        product:  { base: 2,   gap: 10, bp: { 576: [3, 12], 768: [4, 14], 992: [5, 16], 1200: [6, 16] }, delay: 3200 }
    };
    function makeSwiper(el, opts) {
        var cfg = layouts[el.dataset.kind] || layouts.product;
        var head = el.parentNode.querySelector('.hp-head');
        var breakpoints = {};
        Object.keys(cfg.bp).forEach(function (w) { breakpoints[w] = { slidesPerView: cfg.bp[w][0], spaceBetween: cfg.bp[w][1] }; });
        var count = el.querySelectorAll('.swiper-slide').length;
        // loop needs at least two screens of slides; with fewer it rewinds to the start instead
        var canLoop = !(opts && opts.noLoop) && count >= 12;
        return new Swiper(el, Object.assign({
            slidesPerView: cfg.base, spaceBetween: cfg.gap, breakpoints: breakpoints,
            loop: canLoop, rewind: !canLoop, speed: 650, grabCursor: true,
            autoplay: autoplay(cfg.delay),
            navigation: head ? { prevEl: head.querySelector('.js-prev'), nextEl: head.querySelector('.js-next') } : false
        }, opts || {}));
    }

    document.querySelectorAll('.js-auto-swiper').forEach(function (el) {
        if (el.id === 'hpFeatured') return;
        makeSwiper(el);
    });

    // Shop by Collection: each row scrolls by itself (opposite directions) and can be
    // dragged with the mouse or swiped. Hovering pauses it; a flick keeps it gliding.
    // The row holds two identical sets, so the offset wraps every set-width.
    document.querySelectorAll('.hp-marquee-row').forEach(function (row) {
        var dir = row.classList.contains('is-reverse') ? 1 : -1;
        var duration = parseFloat(row.dataset.duration) || 40;   // seconds per set
        var x = 0, setWidth = 0, speed = 0, hovering = false;
        var drag = null, glide = 0, suppressClick = false, last = performance.now();

        function measure() {
            setWidth = row.firstElementChild ? row.firstElementChild.getBoundingClientRect().width : 0;
            speed = setWidth / duration;
        }
        function wrap() { if (setWidth > 0) x = ((x % setWidth) - setWidth) % setWidth; }

        function frame(now) {
            var dt = Math.min(0.05, (now - last) / 1000);
            last = now;
            if (!drag) {
                if (Math.abs(glide) > 5) { x += glide * dt; glide *= Math.pow(0.04, dt); }
                else if (!hovering && !reduceMotion) x += dir * speed * dt;
            }
            wrap();
            row.style.transform = 'translate3d(' + x + 'px,0,0)';
            requestAnimationFrame(frame);
        }

        row.addEventListener('pointerdown', function (e) {
            if (e.button !== 0) return;
            drag = { id: e.pointerId, startX: e.clientX, startOffset: x, lastX: e.clientX, lastT: e.timeStamp, v: 0, moved: false };
            glide = 0;
        });
        row.addEventListener('pointermove', function (e) {
            if (!drag || e.pointerId !== drag.id) return;
            var dx = e.clientX - drag.startX;
            if (!drag.moved && Math.abs(dx) > 6) {
                drag.moved = true;
                row.classList.add('is-dragging');
                try { row.setPointerCapture(e.pointerId); } catch (err) {}
            }
            if (!drag.moved) return;
            var dt = (e.timeStamp - drag.lastT) / 1000;
            if (dt > 0) drag.v = (e.clientX - drag.lastX) / dt;
            drag.lastX = e.clientX; drag.lastT = e.timeStamp;
            x = drag.startOffset + dx;
        });
        function endDrag(e) {
            if (!drag || (e && e.pointerId !== drag.id)) return;
            if (drag.moved) {
                suppressClick = true;
                glide = Math.max(-2500, Math.min(2500, drag.v));
                setTimeout(function () { suppressClick = false; }, 0);
            }
            row.classList.remove('is-dragging');
            drag = null;
        }
        row.addEventListener('pointerup', endDrag);
        row.addEventListener('pointercancel', endDrag);
        // a drag must not also open the tile it started on
        row.addEventListener('click', function (e) { if (suppressClick) { e.preventDefault(); e.stopPropagation(); } }, true);
        row.addEventListener('mouseenter', function () { hovering = true; });
        row.addEventListener('mouseleave', function () { hovering = false; endDrag(drag ? { pointerId: drag.id } : null); });

        measure();
        window.addEventListener('resize', measure);
        window.addEventListener('load', measure);
        requestAnimationFrame(frame);
    });

    // Featured Collections: the tabs swap which product slides the slider holds.
    var featuredEl = document.getElementById('hpFeatured');
    if (featuredEl) {
        var allSlides = Array.prototype.slice.call(featuredEl.querySelectorAll('.swiper-slide'));
        var featured = makeSwiper(featuredEl, { noLoop: true });
        document.querySelectorAll('.hp-tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                document.querySelectorAll('.hp-tab').forEach(function (t) {
                    t.classList.toggle('is-active', t === tab);
                    t.setAttribute('aria-selected', t === tab ? 'true' : 'false');
                });
                var cat = tab.dataset.cat;
                featured.removeAllSlides();
                featured.appendSlide(allSlides.filter(function (s) { return cat === 'all' || s.dataset.cat === cat; }));
                featured.slideTo(0, 0);
                if (featured.autoplay && featured.params.autoplay) featured.autoplay.start();
            });
        });
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
