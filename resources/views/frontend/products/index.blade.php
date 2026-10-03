{{-- resources/views/frontend/products/index.blade.php --}}
@extends('frontend.app')
@section('content')

@php
    use App\Models\Information;

    $info = Information::first();

    $minDb = (float)($minDb ?? 0);
    $maxDb = (float)($maxDb ?? 0);
    if($maxDb <= $minDb){ $minDb = 0; $maxDb = 5000; }

    // ✅ keep selected sort from query (ajax + normal)
    $sort  = $sort ?? request('sort', request('shorting', 'latest'));

    $brandGradient = $info->gradient_code ?? 'linear-gradient(90deg,#0d6efd,#00276C)';
    $brandText     = $info->primary_color ?? '#ffffff';

    $hasSizes = isset($sizes) && $sizes->count() > 0;
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

/* ✅ keep default hidden */
.filter-acc .acc-body{ padding:12px; background:#fff; display:none; }
.filter-acc.is-open .acc-body{ display:block; }

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
}

/* ❌ Extra Mobile Footer removed */
</style>

<main class="main-wrapper">

    <div class="axil-breadcrumb-area">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg-12">
                
                    {{-- ✅ Desktop breadcrumb --}}
                    <div class="inner d-none d-lg-block">
                        <ul class="axil-breadcrumb">
                            <li class="axil-breadcrumb-item active" aria-current="page">Shop</li>
                        </ul>
                    </div>

                    {{-- ✅ Mobile top --}}
                    <div class="mob-top d-lg-none">
                        <div class="crumb">
                            <span style="color:var(--text);">Shop</span>
                        </div>

                        <button class="dots-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileFilters" aria-controls="mobileFilters">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="axil-shop-area bg-color-white">
        <div class="container-fluid py-3">
            <div class="row g-3">

                {{-- ✅ Desktop Filters --}}
                <div class="col-lg-3 filters-col">
                    <div class="filter-card">
                        <div class="filter-head">
                            <div class="title">Filters</div>
                            <div style="color:var(--muted); font-weight:900; font-size:12px;">Set options then Apply</div>
                        </div>

                        <div class="filter-section">

                            {{-- ✅ Categories --}}
                            <div class="filter-acc" id="acc-cat">
                                <button type="button" class="acc-btn">
                                    CATEGORIES 
                                    <i class="fas fa-chevron-up"></i>
                                </button>
                                <div class="acc-body">
                                    <div class="pill-list">
                                        @foreach($cats as $c)
                                            <span class="pill cat-pill" data-id="{{ $c->id }}">{{ $c->name }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- ✅ Price --}}
                            <div class="filter-acc" id="acc-price">
                                <button type="button" class="acc-btn">
                                    PRICE 
                                    <i class="fas fa-chevron-up"></i>
                                </button>

                                <div class="acc-body">
                                    <div class="price-box">
                                        <div class="price-row">
                                            <span class="tag">MIN</span>
                                            <span class="val" id="minVal">৳ {{ (int)$minDb }}</span>
                                        </div>
                                        <div class="price-row">
                                            <span class="tag">MAX</span>
                                            <span class="val" id="maxVal">৳ {{ (int)$maxDb }}</span>
                                        </div>

                                        <div class="range-wrap">
                                            <div class="track"><div class="fill" id="fillTrack"></div></div>
                                            <input type="range" id="minRange" min="{{ (int)$minDb }}" max="{{ (int)$maxDb }}" value="{{ (int)$minDb }}" step="1">
                                            <input type="range" id="maxRange" min="{{ (int)$minDb }}" max="{{ (int)$maxDb }}" value="{{ (int)$maxDb }}" step="1">
                                        </div>
                                        
                                        {{-- shared hidden --}}
                                        <input type="hidden" id="min_price" value="{{ (int)$minDb }}">
                                        <input type="hidden" id="max_price" value="{{ (int)$maxDb }}">
                                    </div>
                                </div>
                            </div>

                            {{-- ✅ Brand --}}
                            <div class="filter-acc" id="acc-brand">
                                <button type="button" class="acc-btn">
                                    BRAND 
                                    <i class="fas fa-chevron-up"></i>
                                </button>
                                <div class="acc-body">
                                    <div class="pill-list">
                                        @foreach($types as $t)
                                            <span class="pill brand-pill" data-id="{{ $t->id }}">{{ $t->name }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- ✅ Size --}}
                            @if($hasSizes)
                            <div class="filter-acc" id="acc-size">
                                <button type="button" class="acc-btn">
                                    SIZE 
                                    <i class="fas fa-chevron-up"></i>
                                </button>
                                <div class="acc-body">
                                    <div class="pill-list">
                                        @foreach($sizes as $s)
                                            @php $label = $s->name ?? $s->title ?? $s->size ?? $s->value ?? ''; @endphp
                                            @if(trim((string)$label) !== '')
                                                <span class="pill size-pill" data-id="{{ $s->id }}">{{ $label }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            <button class="btn-apply apply_filter" type="button">Apply Filter</button>
                        </div>
                    </div>
                </div>

                {{-- ✅ Products --}}
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

    {{-- ✅ Mobile Offcanvas Filters --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="mobileFilters" aria-labelledby="mobileFiltersLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="mobileFiltersLabel" style="font-weight:1000;">Filters</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body">
            <div class="filter-card">
                <div class="filter-head">
                    <div class="title">Filters</div>
                    <div style="color:var(--muted); font-weight:900; font-size:12px;">Set options then Apply</div>
                </div>

                <div class="filter-section">

                    {{-- ✅ Categories --}}
                    <div class="filter-acc" id="m-acc-cat">
                        <button type="button" class="acc-btn">
                            CATEGORIES 
                            <i class="fas fa-chevron-up"></i>
                        </button>
                        <div class="acc-body">
                            <div class="pill-list">
                                @foreach($cats as $c)
                                    <span class="pill m-cat-pill" data-id="{{ $c->id }}">{{ $c->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- ✅ Price --}}
                    <div class="filter-acc" id="m-acc-price">
                        <button type="button" class="acc-btn">
                            PRICE 
                            <i class="fas fa-chevron-up"></i>
                        </button>

                        <div class="acc-body">
                            <div class="price-box">
                                <div class="price-row">
                                    <span class="tag">MIN</span>
                                    <span class="val" id="m_minVal">৳ {{ (int)$minDb }}</span>
                                </div>
                                <div class="price-row">
                                    <span class="tag">MAX</span>
                                    <span class="val" id="m_maxVal">৳ {{ (int)$maxDb }}</span>
                                </div>

                                <div class="range-wrap">
                                    <div class="track"><div class="fill" id="m_fillTrack"></div></div>
                                    <input type="range" id="m_minRange" min="{{ (int)$minDb }}" max="{{ (int)$maxDb }}" value="{{ (int)$minDb }}" step="1">
                                    <input type="range" id="m_maxRange" min="{{ (int)$minDb }}" max="{{ (int)$maxDb }}" value="{{ (int)$maxDb }}" step="1">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ✅ Brand --}}
                    <div class="filter-acc" id="m-acc-brand">
                        <button type="button" class="acc-btn">
                            BRAND 
                            <i class="fas fa-chevron-up"></i>
                        </button>
                        <div class="acc-body">
                            <div class="pill-list">
                                @foreach($types as $t)
                                    <span class="pill m-brand-pill" data-id="{{ $t->id }}">{{ $t->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- ✅ Size --}}
                    @if($hasSizes)
                    <div class="filter-acc" id="m-acc-size">
                        <button type="button" class="acc-btn">
                            SIZE 
                            <i class="fas fa-chevron-up"></i>
                        </button>
                        <div class="acc-body">
                            <div class="pill-list">
                                @foreach($sizes as $s)
                                    @php $label = $s->name ?? $s->title ?? $s->size ?? $s->value ?? ''; @endphp
                                    @if(trim((string)$label) !== '')
                                        <span class="pill m-size-pill" data-id="{{ $s->id }}">{{ $label }}</span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <button class="btn-apply" id="mApplyBtn" type="button">Apply Filter</button>
                </div>
            </div>
        </div>
    </div>

</main>

{{-- ✅ The duplicate mobile-bottom-nav DIV has been REMOVED from here --}}

@endsection

@push('js')
<script>
(function(){

    // =====================================
    // ✅ ACCORDION (same as another_index)
    // =====================================
    function initAccordions(scope){
        const root = scope || document;
        
        // force close all
        root.querySelectorAll('.filter-acc').forEach(acc=>{
            acc.classList.remove('is-open');
            const body = acc.querySelector('.acc-body');
            if(body) body.style.display = 'none';
        });

        // bind click (override conflicts)
        root.querySelectorAll('.filter-acc .acc-btn').forEach(btn=>{
            btn.onclick = function(e){
                e.preventDefault();
                e.stopPropagation();

                const acc = btn.closest('.filter-acc');
                if(!acc) return;

                // single-open within same container
                const parent = acc.parentElement;
                if(parent){
                    parent.querySelectorAll('.filter-acc').forEach(other=>{
                        if(other !== acc){
                            other.classList.remove('is-open');
                            const b = other.querySelector('.acc-body');
                            if(b) b.style.display = 'none';
                        }
                    });
                }

                const body = acc.querySelector('.acc-body');
                const isOpen = acc.classList.contains('is-open');

                if(isOpen){
                    acc.classList.remove('is-open');
                    if(body) body.style.display = 'none';
                }else{
                    acc.classList.add('is-open');
                    if(body) body.style.display = 'block';
                }
            };
        });
    }

    // on load
    initAccordions(document);

    // offcanvas open -> re-init
    const mobileEl = document.getElementById('mobileFilters');
    if(mobileEl){
        mobileEl.addEventListener('shown.bs.offcanvas', function(){
            initAccordions(mobileEl);
        });
    }

    // =====================================
    // ✅ pills toggles (FIXED: no click+touchend double fire)
    // =====================================
    $(document).off('pointerup', '.brand-pill, .cat-pill');
    $(document).off('pointerup', '.m-brand-pill, .m-cat-pill');
    $(document).off('pointerup', '.size-pill');
    $(document).off('pointerup', '.m-size-pill');

    $(document).on('pointerup', '.brand-pill, .cat-pill', function(e){
        e.preventDefault();
        $(this).toggleClass('active');
    });
    $(document).on('pointerup', '.m-brand-pill, .m-cat-pill', function(e){
        e.preventDefault();
        $(this).toggleClass('active');
    });

    // ✅ size = single select
    $(document).on('pointerup', '.size-pill', function(e){
        e.preventDefault();
        $('.size-pill').not(this).removeClass('active');
        $(this).toggleClass('active');
    });
    $(document).on('pointerup', '.m-size-pill', function(e){
        e.preventDefault();
        $('.m-size-pill').not(this).removeClass('active');
        $(this).toggleClass('active');
    });

    // =====================================
    // ✅ price slider clamp + fill
    // =====================================
    function clampRanges(prefix=''){
        const minR   = document.getElementById(prefix + 'minRange');
        const maxR   = document.getElementById(prefix + 'maxRange');
        const minVal = document.getElementById(prefix + 'minVal');
        const maxVal = document.getElementById(prefix + 'maxVal');
        const fill   = document.getElementById(prefix + 'fillTrack');
        if(!minR || !maxR) return;

        let minV = parseInt(minR.value || 0);
        let maxV = parseInt(maxR.value || 0);

        if(minV > maxV - 1){ minV = maxV - 1; minR.value = minV; }
        if(maxV < minV + 1){ maxV = minV + 1; maxR.value = maxV; }

        if(minVal) minVal.textContent = '৳ ' + minV;
        if(maxVal) maxVal.textContent = '৳ ' + maxV;

        const min = parseInt(minR.min || 0);
        const max = parseInt(maxR.max || 100);
        const leftPct  = ((minV - min) / (max - min)) * 100;
        const rightPct = 100 - (((maxV - min) / (max - min)) * 100);

        if(fill){
            fill.style.left  = leftPct + '%';
            fill.style.right = rightPct + '%';
        }

        // ✅ sync hidden for ajax (desktop)
        if(prefix === ''){
            $('#min_price').val(minV);
            $('#max_price').val(maxV);
        }
    }

    clampRanges('');
    clampRanges('m_');

    document.addEventListener('input', function(e){
        if(e.target && (e.target.id === 'minRange' || e.target.id === 'maxRange')) clampRanges('');
        if(e.target && (e.target.id === 'm_minRange' || e.target.id === 'm_maxRange')) clampRanges('m_');
    }, {passive:true});

    // =====================================
    // ✅ AJAX filter fetch (same logic)
    // =====================================
    let applied = buildCurrentFilters();

    function buildCurrentFilters(){
        const sort = $('#sort').val() || 'latest';
        
        const cat_id    = $('.cat-pill.active, .m-cat-pill.active').map(function(){ return $(this).data('id'); }).get();
        const brand_id = $('.brand-pill.active, .m-brand-pill.active').map(function(){ return $(this).data('id'); }).get();
        const size_id  = $('.size-pill.active, .m-size-pill.active').map(function(){ return $(this).data('id'); }).get();
        
        // ✅ prefer desktop hidden (always exists)
        const min_price = $('#min_price').val();
        const max_price = $('#max_price').val();

        return { sort, cat_id, brand_id, size_id, min_price, max_price };
    }

    function smoothToProducts(){
        const top = $('#topbar').offset()?.top || 0;
        $('html,body').stop(true).animate({ scrollTop: Math.max(top - 90, 0) }, 260);
    }

    function fetchProducts(pageUrl=null){
        let url = pageUrl ? pageUrl : "{{ route('front.products.index') }}";

        $.ajax({
            type: 'GET',
            url: url,
            data: { 
                shorting: applied.sort,
                sort: applied.sort,
                cat_id: applied.cat_id,
                brand_id: applied.brand_id,
                size_id: applied.size_id,
                min_price: applied.min_price,
                max_price: applied.max_price
            },
            beforeSend: function(){
                $('#product_data').addClass('loading');
            },
            success: function(res){
                $('#product_data').html(res);
                
                // ✅ update result text if partial provides meta
                const meta = $('#product_data').find('[data-first][data-last][data-total]').first();
                if(meta.length){
                    const first = meta.data('first') ?? 0;
                    const last  = meta.data('last') ?? 0;
                    const total = meta.data('total') ?? 0;
                    $('#resultText').html('Showing <span>' + first + ' – ' + last + ' of ' + total + ' results</span>');
                }

                smoothToProducts();
            },
            complete: function(){
                $('#product_data').removeClass('loading');
            }
        });
    }

    // apply filter (desktop)
    $(document).on('click', '.apply_filter', function(e){
        e.preventDefault();
        applied = buildCurrentFilters();
        fetchProducts(null);
    });

    // mobile apply (FIXED: only one event)
    $(document).off('pointerup', '#mApplyBtn');
    $(document).on('pointerup', '#mApplyBtn', function(e){
        e.preventDefault();
        
        const mMin = parseInt($('#m_minRange').val() || $('#min_price').val() || 0);
        const mMax = parseInt($('#m_maxRange').val() || $('#max_price').val() || 0);

        // sync to desktop hidden inputs
        $('#min_price').val(mMin);
        $('#max_price').val(mMax);
        
        // sync desktop sliders too
        $('#minRange').val(mMin);
        $('#maxRange').val(mMax);
        clampRanges('');

        applied = buildCurrentFilters();
        fetchProducts(null);

        const el = document.getElementById('mobileFilters');
        if(el && window.bootstrap){
            const ins = bootstrap.Offcanvas.getInstance(el) || new bootstrap.Offcanvas(el);
            ins.hide();
        }
    });

    // sort change
    $(document).on('change', '#sort', function(){
        applied = buildCurrentFilters();
        fetchProducts(null);
    });

    // pagination
    $(document).on('click', '#product_data .pagination a', function(e){
        e.preventDefault();
        applied = buildCurrentFilters();
        fetchProducts($(this).attr('href'));
    });

})();
</script>
@endpush