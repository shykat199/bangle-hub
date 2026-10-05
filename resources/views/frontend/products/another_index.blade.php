{{-- resources/views/frontend/products/another_index.blade.php --}}
@extends('frontend.app')
@section('content')

@php
    use App\Models\Information;

    $info = Information::first();

    $minDb = (float)($minDb ?? 0);
    $maxDb = (float)($maxDb ?? 0);
    if($maxDb <= $minDb){ $minDb = 0; $maxDb = 5000; }

    $sort  = $sort ?? request('sort', request('shorting', 'latest'));

    $brandGradient = $info->gradient_code ?? 'linear-gradient(90deg,#0d6efd,#00276C)';
    $brandText     = $info->primary_color ?? '#ffffff';

    $hasSizes = isset($sizes) && $sizes->count() > 0;

    // Request parameters setup
    $qBrand = (array) request('brand_id', []);
    $qSize  = (array) request('size_id', []);
    $qMin   = (int) request('min_price', (int)$minDb);
    $qMax   = (int) request('max_price', (int)$maxDb);
    
    // Current Category setup for initial active state
    $currentCatId = $cat->id ?? null;
@endphp

<style>
:root{
    --brand-gradient: {!! $brandGradient !!};
    --brand-text: {{ $brandText }};
    --bg:#f6f9ff;
    --text:#0f172a;
    --muted:#64748b;
    --line:rgba(2,6,23,.10);
    --shadow:0 16px 44px rgba(2,6,23,.10);
    --radius:18px;
}
.axil-shop-area{ background: var(--bg) !important; }
.container-fluid{ max-width: 1600px; }

.axil-breadcrumb-area{
    background:
        radial-gradient(1200px 220px at 10% 10%, rgba(14,165,233,.14), transparent 55%),
        radial-gradient(900px 220px at 90% 0%, rgba(11,62,168,.14), transparent 55%),
        linear-gradient(135deg, rgba(255,255,255,.60), rgba(255,255,255,.88));
    border-bottom:1px solid var(--line);
}
.axil-breadcrumb{ padding:12px 0; }
.axil-breadcrumb li a{ color: var(--muted); }
.axil-breadcrumb-item.active{ color: var(--text); font-weight:900; }

.mob-top{
    display:none;
    padding:10px 12px;
    align-items:center;
    justify-content:space-between;
    gap:10px;
}
.mob-top .crumb{
    display:flex; gap:10px; align-items:center;
    font-weight:900; color: var(--text);
}
.mob-top .crumb a{ color: var(--muted); font-weight:900; text-decoration:none; }
.mob-top .dots-btn{
    border:0;
    width:44px;
    height:38px;
    border-radius:12px;
    background: transparent;
    color: var(--text);
    display:flex;
    align-items:center;
    justify-content:center;
    -webkit-tap-highlight-color: transparent;
    touch-action: manipulation;
}
.mob-top .dots-btn i{ font-size:22px; line-height:1; }

.shop-topbar{
    background: rgba(255,255,255,.88);
    backdrop-filter: blur(12px);
    border:1px solid var(--line);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding:14px;
    display:flex; align-items:center; justify-content:space-between; gap:12px;
}
.shop-topbar .result-text{ color: var(--text); font-weight:900; font-size:15px; }
.shop-topbar .result-text span{ color: var(--muted); font-weight:800; }
.shop-topbar .sort-wrap{ display:flex; align-items:center; gap:10px; }
.shop-topbar label{ margin:0; font-weight:900; color: var(--muted); font-size:13px; }
.shop-topbar select{
    border:1px solid var(--line);
    border-radius: 14px;
    padding:12px 14px;
    min-width: 240px;
    background:#fff;
    box-shadow: 0 12px 26px rgba(2,6,23,.06);
    font-weight:900;
    color: var(--text);
    outline:none !important;
}

/* Filters */
.filters-col{ position:sticky; top:10px; align-self:flex-start; }
.filter-card{
    background: rgba(255,255,255,.90);
    backdrop-filter: blur(12px);
    border:1px solid var(--line);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow:hidden;
}
.filter-head{
    padding:14px 14px;
    background:
        radial-gradient(700px 180px at 0% 0%, rgba(14,165,233,.16), transparent 60%),
        radial-gradient(700px 180px at 100% 0%, rgba(11,62,168,.16), transparent 60%),
        linear-gradient(135deg, rgba(255,255,255,.55), rgba(255,255,255,.92));
    border-bottom:1px solid var(--line);
    display:flex; align-items:center; justify-content:space-between;
}
.filter-head .title{
    font-size:14px;
    font-weight:1000;
    color: var(--text);
    letter-spacing:.08em;
    text-transform:uppercase;
}
.filter-section{ padding:14px; }

.filter-acc{
    border:1px solid var(--line);
    border-radius: 16px;
    overflow:hidden;
    background:#fff;
    box-shadow: 0 14px 32px rgba(2,6,23,.07);
    margin-bottom:12px;
}
.filter-acc .acc-btn{
    width:100%;
    border:0;
    background: var(--brand-gradient);
    color: var(--brand-text);
    font-weight:1000;
    letter-spacing:.08em;
    text-transform:uppercase;
    font-size:13px;
    padding:12px 12px;
    display:flex; align-items:center; justify-content:space-between;
    cursor:pointer;
    -webkit-tap-highlight-color: transparent;
    touch-action: manipulation;
}
.filter-acc .acc-btn i{ transition:.2s ease; color: var(--brand-text); }
.filter-acc.is-open .acc-btn i{ transform: rotate(180deg); }

/* ✅ IMPORTANT: body default hidden */
.filter-acc .acc-body{ padding:12px; background:#fff; display:none; }

.pill-list{ display:flex; flex-wrap:wrap; gap:10px; }
.pill{
    display:inline-flex !important;
    align-items:center !important;
    justify-content:center !important;
    min-height:36px !important;
    min-width:54px !important;
    padding:9px 12px !important;
    border-radius:999px;
    border:1px solid rgba(11,62,168,.18);
    background: rgba(11,62,168,.05);
    color: var(--text) !important;
    font-weight:1000;
    font-size:13px;
    cursor:pointer;
    transition:.16s ease;
    line-height:1 !important;
    white-space:nowrap !important;
    user-select:none;
    -webkit-tap-highlight-color: transparent;
    touch-action: manipulation;
}
.pill.active{
    background: var(--brand-gradient);
    color: var(--brand-text) !important;
    border-color: transparent;
}

/* price */
.price-box{
    border:1px solid rgba(2,6,23,.10);
    border-radius:16px;
    padding:12px;
    background: linear-gradient(180deg, rgba(255,255,255,1), rgba(255,255,255,.92));
}
.price-row{
    display:flex; justify-content:space-between; align-items:center;
    padding:8px 10px;
    border-radius:14px;
    background: rgba(2,6,23,.03);
    border:1px solid rgba(2,6,23,.06);
    margin-bottom:10px;
}
.price-row .tag{ font-weight:1000; font-size:12px; letter-spacing:.08em; color: var(--muted); }
.price-row .val{ font-weight:1000; font-size:14px; color: var(--text); }

.range-wrap{ position:relative; height:38px; margin-top:4px; }
.range-wrap input[type=range]{
    position:absolute; left:0; right:0;
    width:100%; height:38px; margin:0;
    background:transparent;
    pointer-events:none;
    -webkit-appearance:none;
}
.range-wrap input[type=range]::-webkit-slider-thumb{
    -webkit-appearance:none;
    width:22px; height:22px;
    border-radius:999px;
    background: var(--brand-text);
    border:2px solid rgba(0,0,0,.10);
    box-shadow: 0 12px 18px rgba(2,6,23,.20);
    pointer-events:auto;
}
.track{
    position:absolute; left:0; right:0;
    top:50%; transform: translateY(-50%);
    height:8px; border-radius:999px;
    background: rgba(2,6,23,.14);
    overflow:hidden;
}
.track .fill{
    position:absolute; top:0; bottom:0;
    background: var(--brand-gradient);
    border-radius:999px;
    left:0%; right:0%;
}

.btn-apply{
    width:100%;
    border:0;
    border-radius: 16px;
    padding:13px 14px;
    font-weight:1000;
    letter-spacing:.10em;
    text-transform:uppercase;
    background: var(--brand-gradient);
    color: var(--brand-text);
    box-shadow: var(--shadow);
    transition:.18s ease;
    -webkit-tap-highlight-color: transparent;
    touch-action: manipulation;
}

/* products */
.products-wrap{ margin-top:12px; }
.product-loading{ position:relative; min-height:520px; }
.product-loading:after{
    content:"Loading...";
    position:absolute; inset:0;
    display:none;
    align-items:center; justify-content:center;
    background: rgba(255,255,255,.72);
    backdrop-filter: blur(2px);
    border-radius: 12px;
    font-weight: 1000;
    color: #00276C;
    z-index: 5;
}
.product-loading.loading:after{ display:flex; }

/* pagination */
.nice-pagination{ width:100%; display:flex; justify-content:center; }
.nice-pagination .pagination{
    display:flex; gap:10px;
    justify-content:center; align-items:center;
    flex-wrap:wrap;
    padding:0; margin:16px 0 0;
}
.nice-pagination .pagination li{ list-style:none; }
.nice-pagination .pagination a,
.nice-pagination .pagination span{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:44px;
    height:44px;
    padding:0 14px;
    border-radius:16px;
    border:1px solid rgba(2,6,23,.10);
    background:#fff;
    font-weight:1000;
    color: var(--text);
    text-decoration:none;
    box-shadow: 0 12px 22px rgba(2,6,23,.07);
}
.nice-pagination .pagination li.active span{
    background: var(--brand-gradient);
    color: var(--brand-text);
    border-color: transparent;
}
.nice-pagination .pagination li.disabled span{ opacity:.45; }

@media (max-width: 991.98px){
    .filters-col{ display:none !important; }
    .mob-top{ display:flex; }
    .shop-topbar{ flex-direction:column; align-items:stretch; }
    .shop-topbar select{ width:100%; min-width:unset; }
    body{ padding-bottom: 0 !important; }
}
</style>

@include('frontend.products.partials.shop_filters_assets')

<main class="main-wrapper">

    <div class="axil-breadcrumb-area">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg-12">

                    <div class="inner d-none d-lg-block">
                        <ul class="axil-breadcrumb">
                            <li class="axil-breadcrumb-item"><a href="{{ route('front.home') }}">Home</a></li>
                            <li class="separator"></li>
                            <li class="axil-breadcrumb-item active" aria-current="page">{{ $cat->name }}</li>
                        </ul>
                    </div>

                    <div class="mob-top d-lg-none">
                        <div class="crumb">
                            <a href="{{ route('front.home') }}">Home</a>
                            <span style="opacity:.45;">/</span>
                            <span style="color:var(--text);">{{ $cat->name }}</span>
                        </div>

                        <button class="dots-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileFilters" aria-controls="mobileFilters">
                            <i class="fas fa-sliders-h"></i> Filters <span class="sf-count" data-sf-count hidden></span>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="axil-shop-area bg-color-white">
        <div class="container-fluid py-3">
            <div class="row g-3">

                {{-- Desktop Filters --}}
                <div class="col-lg-3 filters-col">
                    @include('frontend.products.partials.shop_filters')
                </div>

                {{-- Products --}}
                <div class="col-lg-9">
                    <div class="shop-topbar" id="topbar">
                        <div class="result-text" id="resultText">
                            Showing
                            <span>
                                {{ $items->firstItem() ?? 0 }} – {{ $items->lastItem() ?? 0 }}
                                of {{ $items->total() ?? 0 }} results
                            </span>
                        </div>

                        <div class="sort-wrap">
                            <label for="sort">Sort</label>
                            <select id="sort" class="single-select">
                                <option value="latest"     {{ $sort=='latest' ? 'selected':'' }}>Sort by Latest</option>
                                <option value="oldest"     {{ $sort=='oldest' ? 'selected':'' }}>Sort by Oldest</option>
                                <option value="name"       {{ $sort=='name' ? 'selected':'' }}>Sort by Name</option>
                                <option value="best_selling" {{ $sort=='best_selling' ? 'selected':'' }}>Best Selling</option>
                                <option value="price_low"  {{ $sort=='price_low' ? 'selected':'' }}>Price: Low to High</option>
                                <option value="price_high" {{ $sort=='price_high' ? 'selected':'' }}>Price: High to Low</option>
                            </select>
                        </div>
                    </div>

                    <div class="products-wrap">
                        <div class="row row--15 product-loading" id="product_data">
                            @include('frontend.products.partials.category_products', ['items'=>$items])
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Mobile Offcanvas Filters --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="mobileFilters" aria-labelledby="mobileFiltersLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="mobileFiltersLabel" style="font-weight:1000;">Filters</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body">
            @include('frontend.products.partials.shop_filters')
        </div>
        <div class="sf-drawer-foot">
            <button class="btn-apply" type="button" data-sf-done data-bs-dismiss="offcanvas">Show results</button>
        </div>
    </div>

</main>

@endsection
