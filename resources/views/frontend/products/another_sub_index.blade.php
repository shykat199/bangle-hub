{{-- resources/views/frontend/products/another_sub_index.blade.php --}}
@extends('frontend.app')
@section('content')

@php
use App\Models\Information;

$info = Information::first();

/**
 * ✅ keep selected sort from query (ajax + normal)
 * supports both: sort= & shorting=
 */
$sort = $sort ?? request('sort', request('shorting', 'latest'));
@endphp

<style>
.row>[class*=col]{ padding-left:5px; padding-right:5px; }
.row{ margin-right:-12px; margin-left:-12px; }

.products-block{
    background: linear-gradient(180deg, rgba(0,39,108,.04), rgba(255,255,255,1));
    border-radius: 14px;
    padding: 12px 10px;
    box-shadow: 0 10px 28px rgba(0,0,0,.06);
    border: 1px solid rgba(0,0,0,.06);
}

/* ✅ Topbar (dropdown + result) */
.shop-topbar{
    background:#fff;
    border: 1px solid rgba(0,0,0,.08);
    border-radius: 14px;
    box-shadow: 0 10px 28px rgba(0,0,0,.06);
    padding: 12px 12px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    margin-bottom:12px;
}
.shop-topbar .result-text{ font-weight:800; font-size:14px; color:#0f172a; }
.shop-topbar .result-text span{ color:#64748b; font-weight:800; }
.shop-topbar .sort-wrap{ display:flex; align-items:center; gap:10px; }
.shop-topbar label{ margin:0; font-weight:800; color:#64748b; font-size:13px; }

.product-loading{ position:relative; min-height:220px; }
.product-loading:after{
    content:"Loading...";
    position:absolute; inset:0;
    display:none; align-items:center; justify-content:center;
    background: rgba(255,255,255,.75);
    backdrop-filter: blur(2px);
    border-radius:12px;
    font-weight:700;
    color:#00276C;
    letter-spacing:.5px;
    z-index:5;
}
.product-loading.loading:after{ display:flex; }

.single-select{
    border:1px solid rgba(0,0,0,.12);
    border-radius:12px;
    padding:10px 12px;
    background:#fff;
    font-weight:700;
    outline:none !important;
    min-width:220px;
}
@media (max-width: 991.98px){
    .single-select{ font-size:16px; min-width:unset; width:100%; }
    .shop-topbar{ flex-direction:column; align-items:stretch; }
    .shop-topbar .sort-wrap{ justify-content:space-between; }
}

.axil-product>.thumbnail>a img{ transition:1.2s; }
.axil-product:hover>.thumbnail>a img{ transform: scale(1.5) !important; }
.axil-product>.thumbnail{ max-height:280px; overflow:hidden; }

/* ✅ Pagination (center) */
.nice-pagination{ width:100%; display:flex; justify-content:center; }
.nice-pagination .pagination{
    display:flex; gap:10px;
    justify-content:center; align-items:center;
    flex-wrap:wrap;
    padding:0; margin:16px 0 0;
}
.nice-pagination .pagination li{ list-style:none; }
</style>

<main class="main-wrapper">

    <div class="axil-breadcrumb-area">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="inner">
                        <ul class="axil-breadcrumb">
                            {{-- ✅ Home removed --}}
                            <li class="axil-breadcrumb-item active" aria-current="page">{{ $s_cat->name }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="axil-shop-area axil-section-gap bg-color-white">
        <div class="container-fluid">
            <div class="row p-2">
                <div class="col-lg-12 products-block">

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

                    <div class="row row--15 product-loading" id="product_data">
                        @include('frontend.products.partials.category_products', ['items'=>$items])
                    </div>

                </div>
            </div>
        </div>
    </div>

</main>
@endsection

@push('js')
<script>
(function(){
    // ✅ safest: current page URL (route param mismatch e break hobe na)
    const LIST_URL = "{{ url()->current() }}";

    function smoothToTop(){
        const $topbar = $('#topbar');
        const top = $topbar.length ? ($topbar.offset().top || 0) : ($('#product_data').offset()?.top || 0);
        $('html,body').stop(true).animate({ scrollTop: Math.max(top - 90, 0) }, 260);
    }

    function updateResultText(){
        const meta = $('#product_data').find('[data-first][data-last][data-total]').first();
        if(meta.length){
            const first = meta.data('first') ?? 0;
            const last  = meta.data('last') ?? 0;
            const total = meta.data('total') ?? 0;
            $('#resultText').html('Showing <span>' + first + ' – ' + last + ' of ' + total + ' results</span>');
        }
    }

    function fetchData(pageUrl=null){
        const $wrap = $('#product_data');
        $wrap.addClass('loading');

        const sortVal = $('#sort').val() || 'latest';
        const url = pageUrl ? pageUrl : LIST_URL;

        $.ajax({
            type:'GET',
            url: url,
            data:{
                shorting: sortVal,
                sort: sortVal
            },
            success:function(res){
                $wrap.html(res);
                updateResultText();
                smoothToTop();
            },
            complete:function(){
                $wrap.removeClass('loading');
            }
        });
    }

    $(document).on('change', '#sort', function(){
        fetchData(null);
    });

    $(document).on('click', '#product_data .pagination a', function(e){
        e.preventDefault();
        fetchData($(this).attr('href'));
    });

})();
</script>
@endpush
