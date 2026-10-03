{{-- resources/views/frontend/products/partials/product_section.blade.php --}}

@php
    $data = getProductInfo($product);

    use App\Models\Information;
    use App\Models\Variation;
    use App\Models\ProductStock;
    use Illuminate\Support\Str;

    $info = Information::first();
    $curr = $info->currency ?? 'BDT';

    $productParam = $product->slug ?: $product->id;

    $variationId = optional($product->variation)->id
        ?? optional($product->variations->first())->id
        ?? Variation::where('product_id', $product->id)->orderBy('id','asc')->value('id');

    $isVariable = $product->type === 'variable';

    // ✅ Stock logic — same rule the cart uses (helpers.php::productIsOrderable):
    // stock not managed or resolved stock 0 → out. Covers variable products too.
    $isOut = !productIsOrderable($product);

    // Discount calc
    $hasDiscount = ($product->after_discount ?? 0) > 0;
    $discountPercent = 0;
    if($hasDiscount){
        $p = (float)($product->sell_price ?? 0);
        $a = (float)($product->after_discount ?? 0);
        $discountPercent = $p > 0 ? round((($p - $a) / $p) * 100, 0) : 0;
    }

    // ✅ Free Shipping check
    $isFreeShipping = (int)($product->is_free_shipping ?? 0) === 1;
@endphp

@once
<style>
    /* ============================================================
       ✨ PREMIUM CARD
       ============================================================ */
    .axil-product.product-style-one {
        position: relative;
        height: 100%;
        display: flex;
        flex-direction: column;
        background: #ffffff !important;
        border: 1px solid rgba(15,23,42,.06) !important;
        border-radius: 14px !important;
        overflow: hidden !important;
        box-shadow:
            0 1px 2px rgba(15,23,42,.03),
            0 0 0 1px rgba(15,23,42,.02) !important;
        transition:
            transform .45s cubic-bezier(.22,.61,.36,1),
            box-shadow .45s cubic-bezier(.22,.61,.36,1),
            border-color .35s ease !important;
        isolation: isolate;
    }
    .axil-product.product-style-one::before{
        content:"";
        position: absolute; inset: 0;
        border-radius: 14px;
        padding: 1.5px;
        background: linear-gradient(135deg,
            rgba(13,110,253,0) 0%,
            rgba(13,110,253,.45) 35%,
            rgba(0,39,108,.45) 65%,
            rgba(13,110,253,0) 100%);
        -webkit-mask:
            linear-gradient(#fff 0 0) content-box,
            linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
                mask-composite: exclude;
        opacity: 0;
        transition: opacity .4s ease;
        pointer-events: none;
        z-index: 3;
    }
    .axil-product.product-style-one:hover {
        transform: translateY(-6px) !important;
        border-color: transparent !important;
        box-shadow:
            0 22px 40px -18px rgba(0,39,108,.22),
            0 8px 18px -8px rgba(15,23,42,.08) !important;
    }
    .axil-product.product-style-one:hover::before{ opacity: 1; }

    /* ============================================================
       ✨ THUMBNAIL
       ============================================================ */
    .axil-product .thumbnail {
        position: relative !important;
        background: linear-gradient(180deg, #fbfcfe 0%, #f4f6fa 100%) !important;
        border-radius: 0 !important;
        overflow: hidden !important;
        padding: 0 !important;
        aspect-ratio: 1 / 1;
    }
    .axil-product .thumbnail a{
        display: block; width: 100%; height: 100%;
        position: relative; z-index: 2;
    }
    .axil-product .thumbnail::before{
        content:"";
        position: absolute;
        width: 220%; height: 220%;
        top: -60%; left: -60%;
        background: radial-gradient(circle at 30% 30%, rgba(13,110,253,.10), transparent 45%);
        opacity: 0;
        transition: opacity .5s ease;
        pointer-events: none;
        z-index: 1;
    }
    .axil-product.product-style-one:hover .thumbnail::before{ opacity: 1; }

    .axil-product .thumbnail img.product_img {
        width: 100% !important;
        height: 100% !important;
        aspect-ratio: 1 / 1;
        object-fit: cover !important;
        transition:
            transform .9s cubic-bezier(.22,.61,.36,1),
            filter .35s ease !important;
        display: block;
    }
    .axil-product.product-style-one:hover .thumbnail img.product_img {
        transform: scale(1.08) !important;
    }

    /* ============================================================
       ✨ BADGES (LEFT — Discount / Stock Out)
       ============================================================ */
    .axil-product .label-block.label-left {
        position: absolute !important;
        top: 6px !important;
        left: 6px !important;
        z-index: 6 !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 4px !important;
    }
    .axil-product .product-badget {
        position: relative;
        display: inline-flex;
        align-items: center;
        padding: 3px 7px !important;
        border-radius: 999px !important;
        font-weight: 800 !important;
        font-size: 9.5px !important;
        letter-spacing: .2px;
        text-transform: uppercase;
        line-height: 1 !important;
        background: linear-gradient(135deg, #0d6efd, #00276C) !important;
        color: #ffffff !important;
        border: 1px solid rgba(255,255,255,.10) !important;
        box-shadow: 0 4px 10px rgba(13,110,253,.28) !important;
        font-family: 'Hind Siliguri', sans-serif !important;
        overflow: hidden;
        animation: badgeIn .5s cubic-bezier(.22,.61,.36,1) both;
    }
    .axil-product .product-badget::before{
        content:""; position: absolute; top:0; left:-150%;
        width: 60%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.55), transparent);
        transform: skewX(-20deg);
        animation: badgeShine 3.5s ease-in-out infinite;
        animation-delay: 1.5s;
    }
    @keyframes badgeIn{
        from{ opacity:0; transform: translateX(-8px) scale(.92); }
        to  { opacity:1; transform: translateX(0) scale(1); }
    }
    @keyframes badgeShine{
        0%, 60% { left:-150%; }
        80%, 100% { left: 150%; }
    }
    .axil-product .product-badget.is-out{
        background: linear-gradient(135deg, #ef4444, #b91c1c) !important;
        box-shadow: 0 4px 10px rgba(239,68,68,.30) !important;
        animation: badgeIn .5s cubic-bezier(.22,.61,.36,1) both, outPulse 2.2s ease-in-out infinite;
    }
    @keyframes outPulse{
        0%, 100%{ box-shadow: 0 0 0 0 rgba(239,68,68,.45), 0 4px 10px rgba(239,68,68,.30) !important; }
        50%    { box-shadow: 0 0 0 7px rgba(239,68,68,0),    0 4px 10px rgba(239,68,68,.30) !important; }
    }

    /* ============================================================
       ✨ FREE SHIPPING BADGE (RIGHT — Top corner)
       ============================================================ */
    .axil-product .label-block.label-right {
        position: absolute !important;
        top: 6px !important;
        right: 6px !important;
        z-index: 6 !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 4px !important;
    }
    .axil-product .free-shipping-badge {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 999px;
        font-weight: 800;
        font-size: 9.5px;
        letter-spacing: .3px;
        text-transform: uppercase;
        line-height: 1;
        background: linear-gradient(135deg, #10b981, #059669);
        color: #ffffff;
        border: 1px solid rgba(255,255,255,.15);
        box-shadow:
            0 4px 12px rgba(16,185,129,.32),
            inset 0 1px 0 rgba(255,255,255,.18);
        font-family: 'Hind Siliguri', sans-serif;
        overflow: hidden;
        animation:
            badgeInRight .5s cubic-bezier(.22,.61,.36,1) both,
            freeShipPulse 2.4s ease-in-out infinite;
    }
    .axil-product .free-shipping-badge i {
        font-size: 9px;
        animation: truckMove 2.5s ease-in-out infinite;
    }
    .axil-product .free-shipping-badge::before{
        content:""; position: absolute; top:0; left:-150%;
        width: 55%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.6), transparent);
        transform: skewX(-20deg);
        animation: badgeShine 3.5s ease-in-out infinite;
        animation-delay: 2.5s;
    }
    @keyframes badgeInRight{
        from{ opacity:0; transform: translateX(10px) scale(.9); }
        to  { opacity:1; transform: translateX(0) scale(1); }
    }
    @keyframes freeShipPulse{
        0%, 100%{
            box-shadow:
                0 0 0 0 rgba(16,185,129,.40),
                0 4px 12px rgba(16,185,129,.32),
                inset 0 1px 0 rgba(255,255,255,.18);
        }
        50%{
            box-shadow:
                0 0 0 7px rgba(16,185,129,0),
                0 4px 12px rgba(16,185,129,.32),
                inset 0 1px 0 rgba(255,255,255,.18);
        }
    }
    @keyframes truckMove{
        0%, 100% { transform: translateX(0); }
        50%      { transform: translateX(2px); }
    }
    /* Greyed out when out of stock */
    .axil-product.product-style-one[data-is-out="1"] .free-shipping-badge{
        background: #94a3b8 !important;
        box-shadow: none !important;
        animation: badgeInRight .5s cubic-bezier(.22,.61,.36,1) both !important;
    }
    .axil-product.product-style-one[data-is-out="1"] .free-shipping-badge i{
        animation: none !important;
    }

    /* ============================================================
       ✨ CONTENT — TIGHT SPACING
       ============================================================ */
    .axil-product .product-content {
        padding: 12px 14px 12px !important;
        text-align: left !important;
        display: flex;
        flex-direction: column;
        justify-content: flex-start !important;
        flex-grow: 1;
        background: #ffffff;
        gap: 0; 
    }

    .product-type-indicator {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        align-self: flex-start;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #475569;
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        padding: 3px 9px;
        border-radius: 999px;
        border: 1px solid rgba(15,23,42,.05);
        margin: 0 0 6px 0 !important;
        transition: background .3s ease, color .3s ease, transform .3s ease;
    }
    .product-type-indicator i{ font-size: 9px; }
    .product-type-indicator.is-variable{
        color: #0d6efd;
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        border-color: rgba(13,110,253,.18);
    }
    .axil-product.product-style-one:hover .product-type-indicator{
        transform: translateY(-1px);
    }

    /* ============================================================
       ✨ TITLE
       ============================================================ */
    .axil-product .product-content .title {
        margin: 0 0 4px 0 !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        font-size: 14px !important;
        min-height: unset !important;
        height: auto !important;
    }
    .axil-product .product-content .title a {
        color: #0f172a !important;
        text-decoration: none !important;
        display: block !important;
        overflow: hidden !important;
        white-space: nowrap !important;
        text-overflow: ellipsis !important;
        max-width: 100%;
        font-family: 'Hind Siliguri', sans-serif !important;
        transition: color .25s ease;
    }
    .axil-product.product-style-one:hover .product-content .title a {
        color: #0d6efd !important;
    }

    /* ============================================================
       ✨ PRICE + ACTION
       ============================================================ */
    .axil-product .product-price-variant {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 8px !important;
        margin: 0 !important; 
        padding: 0 !important;
    }
    .axil-product .price-wrap{
        display: flex;
        align-items: baseline;
        flex-wrap: nowrap;
        gap: 4px;
        flex: 1;
        min-width: 0;
        overflow: hidden;
    }
    .axil-product .product-price-variant .current-price {
        font-family: 'Hind Siliguri', sans-serif !important;
        font-size: 16px !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        letter-spacing: -.2px;
        background: linear-gradient(135deg, #0f172a 0%, #0d6efd 120%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .axil-product .product-price-variant .old-price {
        font-family: 'Hind Siliguri', sans-serif !important;
        font-size: 12px !important;
        color: #94a3b8 !important;
        text-decoration: line-through !important;
        font-weight: 500 !important;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ============================================================
       ✨ ACTION BUTTON
       ============================================================ */
    .axil-product .add-to-cart-btn{
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        padding: 0;
        border: 1px solid rgba(15,23,42,.08);
        border-radius: 11px;
        cursor: pointer;
        color: #0f172a;
        background: linear-gradient(135deg, #ffffff, #f8fafc);
        text-decoration: none !important;
        box-shadow:
            0 6px 14px rgba(15,23,42,.08),
            inset 0 1px 0 rgba(255,255,255,1);
        overflow: hidden;
        isolation: isolate;
        transition:
            transform .35s cubic-bezier(.22,.61,.36,1),
            box-shadow .35s ease,
            color .25s ease,
            border-color .25s ease;
        flex-shrink: 0;
    }
    .axil-product .add-to-cart-btn::before{
        content:""; position: absolute; inset: 0;
        border-radius: 11px;
        background: linear-gradient(135deg, #0d6efd, #00276C);
        opacity: 0;
        transition: opacity .35s ease;
        z-index: -1;
    }
    .axil-product .add-to-cart-btn::after{
        content:""; position: absolute; top: 0; left: -130%;
        width: 60%; height: 100%;
        background: linear-gradient(120deg, transparent, rgba(255,255,255,.50), transparent);
        transform: skewX(-20deg);
        transition: left .8s cubic-bezier(.22,.61,.36,1);
        z-index: 0;
    }
    .axil-product .add-to-cart-btn:hover{
        transform: translateY(-2px) scale(1.05);
        color: #ffffff;
        border-color: transparent;
        box-shadow:
            0 14px 26px rgba(13,110,253,.32),
            inset 0 1px 0 rgba(255,255,255,.15);
    }
    .axil-product .add-to-cart-btn:hover::before{ opacity: 1; }
    .axil-product .add-to-cart-btn:hover::after { left: 130%; }
    .axil-product .add-to-cart-btn:active{ transform: translateY(0) scale(.96); }

    .axil-product .add-to-cart-btn i{
        position: relative;
        z-index: 2;
        font-size: 14px;
        transition: transform .35s cubic-bezier(.34,1.56,.64,1);
    }
    .axil-product .add-to-cart-btn:hover i{
        transform: rotate(-8deg) scale(1.15);
    }

    .axil-product .add-to-cart-btn.is-variable{
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        color: #0d6efd;
        border-color: rgba(13,110,253,.20);
    }
    .axil-product .add-to-cart-btn.is-variable:hover{ color: #ffffff; }

    .axil-product.product-style-one[data-is-out="1"] .add-to-cart-btn{
        background: #f1f5f9 !important;
        color: #94a3b8 !important;
        cursor: not-allowed;
        pointer-events: none;
        box-shadow: none !important;
        border-color: #e2e8f0 !important;
    }
    .axil-product.product-style-one[data-is-out="1"] .add-to-cart-btn::before,
    .axil-product.product-style-one[data-is-out="1"] .add-to-cart-btn::after{ display: none !important; }

    /* ============================================================
       ✨ STOCK OUT
       ============================================================ */
    .axil-product.product-style-one[data-is-out="1"]:hover .thumbnail img.product_img {
        transform: none !important;
    }
    .axil-product.product-style-one[data-is-out="1"] .product-content .title a {
        color: #94a3b8 !important;
    }
    .axil-product.product-style-one[data-is-out="1"] .product-price-variant .current-price{
        color: #94a3b8 !important;
        background: none !important;
        -webkit-text-fill-color: #94a3b8 !important;
    }
    .axil-product.product-style-one[data-is-out="1"] .product-price-variant .old-price{
        color: #cbd5e1 !important;
    }
    .axil-product.product-style-one[data-is-out="1"]:hover{
        transform: none !important;
        box-shadow:
            0 1px 2px rgba(15,23,42,0.03),
            0 0 0 1px rgba(15,23,42,0.02) !important;
    }
    .axil-product.product-style-one[data-is-out="1"]::before{ opacity: 0 !important; }

    .axil-product .thumbnail{ position: relative; }
    .axil-product.product-style-one .thumbnail > .stock-out-overlay{
        position: absolute !important;
        inset: 0;
        z-index: 7 !important;
        pointer-events: none;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.18);
    }
    /* Full-width translucent red band across the image, slightly tilted */
    .axil-product .stock-out-stamp{
        display: block;
        width: 140%;
        flex: 0 0 auto;
        padding: 9px 0;
        background: rgba(232,72,85,.78);
        color: #ffffff !important;
        font-weight: 700;
        font-size: 17px;
        letter-spacing: 1.5px;
        line-height: 1;
        text-align: center;
        text-transform: uppercase;
        white-space: nowrap;
        transform: rotate(-12deg);
        text-shadow: 0 1px 2px rgba(0,0,0,.15);
    }
    @media (max-width: 575px){
        .axil-product .stock-out-stamp{ font-size: 13px; padding: 7px 0; letter-spacing: 1px; }
    }
    .axil-product.product-style-one[data-is-out="1"] .label-block.label-right{ z-index: 8 !important; }

    .axil-product .add-to-cart-btn .fa-spinner{ animation: cartSpin .8s linear infinite; }
    @keyframes cartSpin{ from { transform: rotate(0); } to { transform: rotate(360deg); } }

    /* ============================================================
       ✨ MOBILE
       ============================================================ */
    @media (max-width: 575.98px){
        .axil-product.product-style-one{ border-radius: 12px !important; }
        .axil-product .product-content{ padding: 10px 11px 10px !important; }
        .product-type-indicator{ font-size: 9px; padding: 2px 7px; margin-bottom: 5px !important; }
        .axil-product .product-content .title{ font-size: 13px !important; margin-bottom: 3px !important; }
        .axil-product .product-price-variant .current-price{ font-size: 14px !important; }
        .axil-product .product-price-variant .old-price{ font-size: 11px !important; }
        .axil-product .add-to-cart-btn{ width: 34px; height: 34px; border-radius: 10px; }
        .axil-product .add-to-cart-btn i{ font-size: 13px; }
        .axil-product .label-block.label-left,
        .axil-product .label-block.label-right{ top: 4px !important; gap: 3px !important; }
        .axil-product .label-block.label-left{ left: 4px !important; }
        .axil-product .label-block.label-right{ right: 4px !important; }
        .axil-product .product-badget,
        .axil-product .free-shipping-badge{ font-size: 8.5px !important; padding: 2.5px 6px !important; }
        .axil-product .free-shipping-badge i{ font-size: 8px; }
    }

    @media (prefers-reduced-motion: reduce){
        .axil-product, .axil-product *, .axil-product *::before, .axil-product *::after{
            animation-duration: .001ms !important;
            transition-duration: .001ms !important;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if(window.ajaxCartInitialized) return;
    window.ajaxCartInitialized = true;

    // কার্ডের বোতামে কার্টে যোগ হত ঠিকই, কিন্তু সাইডবার খুলত না — নিচে
    // res.html বসানোর পরেও কেউ ওটা দেখাত না। হেডারের ট্রিগারে ক্লিক করলে
    // main.js এর sideOffcanvasToggle ওভারলে (.closeMask), body overflow আর
    // ক্লোজ হ্যান্ডলার একসাথে সামলায়, তাই নিজে ক্লাস বসানোর চেয়ে ওটাই নিরাপদ।
    // ওটা টগল, তাই আগে থেকে খোলা থাকলে ক্লিক করলে উল্টো বন্ধ হয়ে যেত।
    function openCartSidebar() {
        const dd = document.querySelector('#cart_section, #cart-dropdown, .cart-dropdown-wrap');
        if (!dd || dd.classList.contains('open')) return;

        const trigger = document.querySelector('.cart-dropdown-btn');
        if (trigger) { trigger.click(); return; }

        document.body.classList.add('open');
        dd.classList.add('open');
    }

    document.body.addEventListener('click', function(e) {
        let btn = e.target.closest('.ajax-add-btn');
        if (!btn) return;

        e.preventDefault();
        e.stopImmediatePropagation();

        if (btn.disabled) return;

        let originalIcon = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner"></i>';
        btn.disabled = true;

        let url = btn.getAttribute('data-url');
        let formData = new FormData();
        formData.append('_token', btn.getAttribute('data-token'));
        formData.append('product_id', btn.getAttribute('data-product'));
        let variationId = btn.getAttribute('data-variation');
        if(variationId) formData.append('variation_id', variationId);
        formData.append('quantity', 1);

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(r => r.json().catch(() => ({})))
        .then(res => {
            btn.innerHTML = '<i class="fas fa-check" style="color:#16a34a;"></i>';

            if (res && typeof res.item !== 'undefined') {
                document.querySelectorAll('.cart-count, .cart-item-count, .pro-count')
                    .forEach(el => el.textContent = res.item);
            }
            if (res && res.amount) {
                document.querySelectorAll('.cart-amount').forEach(el => {
                    const t = String(res.amount);
                    el.textContent = (t.includes('৳') || t.includes('$')) ? t : '৳ ' + t;
                });
            }
            if (res && (res.view || res.html)) {
                const target = document.querySelector('#cart_section, #cart-dropdown, .cart-dropdown-wrap');
                if(target) target.innerHTML = res.view || res.html;
                openCartSidebar();
            }

            if (window.toastr) toastr.success((res && res.msg) || 'Added to cart');

            setTimeout(() => {
                btn.innerHTML = originalIcon;
                btn.disabled = false;
            }, 1500);
        })
        .catch(error => {
            console.error("Cart Add Error:", error);
            btn.innerHTML = originalIcon;
            btn.disabled = false;
            if (window.toastr) toastr.error('Failed to add to cart');
        });
    });
});
</script>
@endonce

<div class="axil-product product-style-one" data-is-out="{{ $isOut ? 1 : 0 }}" data-is-variable="{{ $isVariable ? 1 : 0 }}">

    <div class="thumbnail">
        <a href="{{ route('front.products.show', ['product' => $productParam]) }}">
            <img src="{{ getImage('thumb_products', $product->image) }}" class="product_img" alt="{{ $product->name }}">
        </a>

        {{-- Big STOCK OUT overlay across the image --}}
        @if($isOut)
            <div class="stock-out-overlay" aria-label="Stock Out">
                <span class="stock-out-stamp">Out of Stock</span>
            </div>
        @endif

        {{-- LEFT badge: Discount --}}
        @if($hasDiscount && !$isOut)
            <div class="label-block label-left">
                <div class="product-badget">{{ $discountPercent }}% Off</div>
            </div>
        @endif

        {{-- ✅ RIGHT badge: Free Shipping --}}
        @if($isFreeShipping)
            <div class="label-block label-right">
                <div class="free-shipping-badge" title="Free Shipping">
                    <i class="fas fa-shipping-fast"></i> Free Ship
                </div>
            </div>
        @endif
    </div>

    <div class="product-content">
        <div class="product-type-indicator {{ $isVariable ? 'is-variable' : '' }}">
            @if($isVariable)
                <i class="fas fa-layer-group"></i> Variable
            @else
                <i class="fas fa-box"></i> Single
            @endif
        </div>

        <h5 class="title">
            <a href="{{ route('front.products.show', ['product' => $productParam]) }}"
               title="{{ $product->name }}">
                {{ $product->name }}
            </a>
        </h5>

        <div class="product-price-variant">
            <div class="price-wrap">
                <span class="price current-price" style="font-family:'Hind Siliguri', sans-serif;">
                    @if($curr == 'BDT') ৳ {{ number_format((int)($data['price'] ?? 0)) }}
                    @elseif($curr == 'Dollar') $ {{ $data['price'] ?? 0 }}
                    @elseif($curr == 'Euro') {{ $data['price'] ?? 0 }}
                    @elseif($curr == 'Rupee') {{ $data['price'] ?? 0 }}
                    @endif
                </span>

                @if($hasDiscount)
                    <span class="price old-price" style="font-family:'Hind Siliguri', sans-serif;">
                        @if($curr == 'BDT') ৳ {{ number_format((int)($data['old_price'] ?? 0)) }}
                        @elseif($curr == 'Dollar') $ {{ $data['old_price'] ?? 0 }}
                        @elseif($curr == 'Euro') {{ $data['old_price'] ?? 0 }}
                        @elseif($curr == 'Rupee') {{ $data['old_price'] ?? 0 }}
                        @endif
                    </span>
                @endif
            </div>

            @if($isOut)
                <button type="button" class="add-to-cart-btn" disabled aria-disabled="true" title="Out of Stock">
                    <i class="fas fa-ban"></i>
                </button>
            @elseif($isVariable)
                <a href="{{ route('front.products.show', ['product' => $productParam]) }}"
                   class="add-to-cart-btn is-variable" title="Select Options">
                    <i class="far fa-eye"></i>
                </a>
            @else
                <button type="button" class="add-to-cart-btn ajax-add-btn"
                    data-url="{{ route('front.carts.store') }}"
                    data-product="{{ $product->id }}"
                    data-variation="{{ $variationId }}"
                    data-token="{{ csrf_token() }}"
                    title="Add to Cart">
                    <i class="fas fa-cart-plus"></i>
                </button>
            @endif
        </div>
    </div>

</div>