{{-- resources/views/frontend/products/partials/product_section.blade.php --}}

@php
    $data = getProductInfo($product);

    use App\Models\Information;
    use App\Models\Variation;
    use App\Models\ProductStock;
    use Illuminate\Support\Str;

    $info = getInfo(); // memoized — this partial renders once per card
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

    // "New" badge for products added in the last 30 days (when there is no discount to show)
    $isNew = $product->created_at && $product->created_at->gt(now()->subDays(30));
    $minQty = method_exists($product, 'minOrderQty') ? (int) $product->minOrderQty() : 1;
    $cartIcon = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 3.5h2.3l2.3 11.6a1.7 1.7 0 0 0 1.7 1.4h8.4a1.7 1.7 0 0 0 1.6-1.3L21 7H5.5"/><circle cx="9.3" cy="20.4" r="1.3"/><circle cx="17" cy="20.4" r="1.3"/><path d="M13.1 9.75v4M11.1 11.75h4" stroke-width="1.6"/></svg>';
@endphp

@once
<style>
    /* ============================================================
       PRODUCT CARD — one design for every page (home, shop, category,
       product page, wishlist...). Picture on top, then name, price and
       an outlined cart button. Accent = the site's brand colour.
       ============================================================ */
    :root{
        --pc-accent: {{ themeAccent('#be1e30') }};
        --pc-accent-dark: color-mix(in srgb, var(--pc-accent) 80%, #000);
        --pc-accent-tint: color-mix(in srgb, var(--pc-accent) 7%, #fff);
        --pc-line: #e8eaee;
        --pc-ink: #111827;
        --pc-muted: #6b7280;
    }
    .axil-product.product-style-one{
        position: relative; display: flex; flex-direction: column; height: 100%;
        margin: 0 !important; padding: 0 !important; overflow: hidden;
        background: #fff; border: 1px solid var(--pc-line); border-radius: 10px; box-shadow: none;
        transition: border-color .25s ease, box-shadow .25s ease, transform .25s ease;
    }
    .axil-product.product-style-one:hover{
        border-color: color-mix(in srgb, var(--pc-accent) 35%, var(--pc-line));
        box-shadow: 0 14px 30px -16px rgba(15,23,42,.28); transform: translateY(-3px);
    }

    /* picture */
    .axil-product.product-style-one .thumbnail{
        position: relative; margin: 0 !important; padding: 0 !important; border-radius: 0 !important;
        aspect-ratio: 1 / 1; overflow: hidden; background: #f6f7f9;
    }
    .axil-product.product-style-one .thumbnail::before, .axil-product.product-style-one .thumbnail::after{ display: none !important; }
    .axil-product.product-style-one .thumbnail > a{ position: relative; display: block; width: 100%; height: 100%; overflow: hidden; }
    .axil-product.product-style-one .thumbnail img.product_img,
    .axil-product.product-style-one .thumbnail img.product_img_hover{
        position: absolute; inset: 0; width: 100% !important; height: 100% !important; max-height: none !important;
        object-fit: cover; border-radius: 0 !important; transition: transform .5s ease, opacity .35s ease;
    }
    .axil-product.product-style-one .thumbnail img.product_img_hover{ opacity: 0; }

    /* High-quality look: crisp, vivid photos (a little more contrast and colour). Sharp
       screens get the full-size photo through srcset, so nothing is blurry from upscaling. */
    .axil-product.product-style-one .thumbnail img.product_img,
    .axil-product.product-style-one .thumbnail img.product_img_hover{
        filter: contrast(1.06) saturate(1.12) brightness(1.02);
        image-rendering: high-quality;
        backface-visibility: hidden; -webkit-backface-visibility: hidden;
    }

    .axil-product.product-style-one .thumbnail img.product_img_hover:not([src]){ display: none; }
    .axil-product.product-style-one:hover .thumbnail img.product_img{ transform: scale(1.05); }
    @media (hover: hover){
        .axil-product.product-style-one:hover .thumbnail img.product_img_hover.is-loaded{ opacity: 1; }
    }

    /* badges: discount or "New" on the left, free shipping on the right */
    .axil-product.product-style-one .label-block{ position: absolute; top: 8px; z-index: 3; margin: 0; padding: 0; }
    .axil-product.product-style-one .label-block.label-left{ left: 8px; }
    .axil-product.product-style-one .label-block.label-right{ right: 8px; }
    .axil-product.product-style-one .product-badget,
    .axil-product.product-style-one .free-shipping-badge{
        display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 5px;
        font-size: 12px !important; font-weight: 700; line-height: 1.4; color: #fff !important; background: var(--pc-accent) !important;
        box-shadow: 0 4px 10px -4px rgba(0,0,0,.35); animation: none;
    }
    .axil-product.product-style-one .product-badget::before{ display: none; }
    .axil-product.product-style-one .free-shipping-badge{ background: #16a34a !important; font-size: 11px !important; }
    .axil-product.product-style-one .free-shipping-badge i{ font-size: 10px; }

    /* badge shine: a light streak sweeps across every badge while the card is hovered
       (the "Out of Stock" ribbon is not a badge, so it stays still) */
    .axil-product.product-style-one .label-block > *{ position: relative; overflow: hidden; }
    .axil-product.product-style-one .label-block > *::after{
        content: ""; position: absolute; top: 0; bottom: 0; left: 0; width: 55%; pointer-events: none;
        background: linear-gradient(100deg, transparent 0%, rgba(255,255,255,.75) 50%, transparent 100%);
        transform: translateX(-130%) skewX(-20deg);
    }
    @media (hover: hover){
        .axil-product.product-style-one:hover .label-block > *::after{ animation: pcBadgeShine 1.5s ease-in-out infinite; }
    }
    @keyframes pcBadgeShine{
        0%{ transform: translateX(-130%) skewX(-20deg); }
        60%, 100%{ transform: translateX(320%) skewX(-20deg); }
    }

    /* out of stock: a diagonal ribbon right across the picture */
    .axil-product.product-style-one .stock-out-overlay{
        position: absolute; inset: 0; z-index: 2; overflow: hidden; pointer-events: none; container-type: inline-size;
    }
    .axil-product.product-style-one .stock-out-stamp{
        position: absolute; top: 50%; left: -12%; right: -12%; padding: .6em 0; transform: translateY(-50%) rotate(-12deg);
        background: rgba(229,62,80,.84); color: #fff; text-align: center; white-space: nowrap; text-shadow: 0 1px 2px rgba(0,0,0,.18);
        font-size: 13px; font-size: clamp(11px, 6.2cqw, 20px); font-weight: 800; line-height: 1.2; letter-spacing: .14em; text-transform: uppercase;
    }

    /* text */
    .axil-product.product-style-one .product-content{
        display: flex; flex-direction: column; flex: 1 1 auto; gap: 2px;
        margin: 0 !important; padding: 10px 12px 12px !important; text-align: left; background: #fff;
    }
    .axil-product.product-style-one .product-content .title{
        margin: 0 !important; font-size: 14.5px !important; font-weight: 600 !important; line-height: 1.35 !important; color: var(--pc-ink);
        min-height: 0 !important; height: auto !important; display: block !important;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .axil-product.product-style-one .product-content .title a{ color: var(--pc-ink) !important; text-decoration: none !important; }
    .axil-product.product-style-one:hover .product-content .title a{ color: var(--pc-accent) !important; }
    .axil-product.product-style-one .pc-sub{ font-size: 12.5px; color: var(--pc-muted); line-height: 1.35; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .axil-product.product-style-one .product-price-variant{
        display: flex; align-items: center; justify-content: space-between; gap: 8px; margin: auto 0 0 !important; padding-top: 6px;
    }
    .axil-product.product-style-one .price-wrap{ display: flex; flex-wrap: wrap; align-items: baseline; gap: 0 6px; min-width: 0; }
    /* the theme styles span.price with stronger selectors, hence !important */
    .axil-product.product-style-one .product-price-variant span.current-price{
        margin: 0 !important; font-size: 21px !important; font-weight: 800 !important; line-height: 1.2 !important; color: var(--pc-accent) !important; white-space: nowrap;
    }
    .axil-product.product-style-one .product-price-variant span.old-price{
        margin: 0 !important; font-size: 14.5px !important; font-weight: 500 !important; color: #9ca3af !important; text-decoration: line-through; white-space: nowrap;
    }
    .axil-product.product-style-one[data-is-out="1"] .product-price-variant span.current-price{ color: #9ca3af !important; }

    /* cart button: outlined square, filled on hover */
    .axil-product.product-style-one .add-to-cart-btn{
        flex: 0 0 auto; width: 38px; height: 38px; padding: 0; margin: 0; border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center; cursor: pointer;
        border: 1.5px solid var(--pc-accent); background: #fff; color: var(--pc-accent) !important;
        text-decoration: none !important; box-shadow: none; transition: background .2s ease, color .2s ease, transform .15s ease;
    }
    .axil-product.product-style-one .add-to-cart-btn::before, .axil-product.product-style-one .add-to-cart-btn::after{ display: none; }
    .axil-product.product-style-one .add-to-cart-btn:hover{ background: var(--pc-accent); color: #fff !important; }
    .axil-product.product-style-one .add-to-cart-btn:active{ transform: scale(.94); }
    .axil-product.product-style-one .add-to-cart-btn svg{ width: 19px; height: 19px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .axil-product.product-style-one .add-to-cart-btn i{ font-size: 15px; color: inherit; }
    .axil-product.product-style-one .add-to-cart-btn .fa-spinner{ animation: cartSpin .8s linear infinite; }
    @keyframes cartSpin{ to{ transform: rotate(360deg); } }
    .axil-product.product-style-one .add-to-cart-btn:disabled,
    .axil-product.product-style-one[data-is-out="1"] .add-to-cart-btn{
        border-color: #e5e7eb; background: #f3f4f6; color: #9ca3af !important; cursor: not-allowed;
    }

    @media (max-width: 575.98px){
        .axil-product.product-style-one .product-content{ padding: 8px 9px 10px !important; }
        .axil-product.product-style-one .product-content .title{ font-size: 13px !important; }
        .axil-product.product-style-one .pc-sub{ font-size: 11.5px; }
        .axil-product.product-style-one .product-price-variant span.current-price{ font-size: 18px !important; }
        .axil-product.product-style-one .product-price-variant span.old-price{ font-size: 12.5px !important; }
        .axil-product.product-style-one .add-to-cart-btn{ width: 34px; height: 34px; border-radius: 7px; }
        .axil-product.product-style-one .add-to-cart-btn svg{ width: 17px; height: 17px; }
        .axil-product.product-style-one .product-badget{ font-size: 10.5px; padding: 2px 7px; }
        .axil-product.product-style-one .free-shipping-badge{ font-size: 10px; padding: 2px 6px; }
    }
    @media (prefers-reduced-motion: reduce){
        .axil-product.product-style-one, .axil-product.product-style-one *{ transition: none !important; }
        .axil-product.product-style-one .label-block > *::after{ animation: none !important; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if(window.ajaxCartInitialized) return;
    window.ajaxCartInitialized = true;


    // Hover photo is downloaded on first hover only, so cards never load two
    // images up front. Delegated, so AJAX-loaded cards work too.
    document.body.addEventListener('mouseover', function(e) {
        const card = e.target.closest && e.target.closest('.axil-product.product-style-one');
        if (!card) return;
        const img = card.querySelector('img.product_img_hover[data-hover-src]');
        if (!img) return;
        img.addEventListener('load', () => img.classList.add('is-loaded'), { once: true });
        img.src = img.dataset.hoverSrc;
        img.removeAttribute('data-hover-src');
    });

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
            @php
                $thumbSrc = getImage('thumb_products', $product->image);
                $fullSrc  = $product->image && file_exists(public_path('products/' . $product->image)) ? getImage('products', $product->image) : null;
            @endphp
            <img src="{{ $thumbSrc }}" class="product_img" alt="{{ $product->name }}" loading="lazy" decoding="async"
                 @if($fullSrc) srcset="{{ $thumbSrc }} 500w, {{ $fullSrc }} 1200w" sizes="(max-width: 575px) 50vw, (max-width: 991px) 33vw, 260px" @endif>
            {{-- Second photo shown on hover: the first gallery image, fetched only when hovered --}}
            @php $hoverImage = optional($product->images->first())->image; @endphp
            @if($hoverImage && file_exists(public_path('products/' . $hoverImage)))
                <img data-hover-src="{{ getImage('products', $hoverImage) }}" class="product_img_hover" alt="" aria-hidden="true">
            @endif
        </a>

        {{-- Wishlist heart (styles + click handling live in partials/header) --}}
        <button type="button" class="wl-toggle wl-heart {{ inWishlist($product->id) ? 'is-saved' : '' }}"
                data-product="{{ $product->id }}" aria-pressed="{{ inWishlist($product->id) ? 'true' : 'false' }}"
                title="{{ inWishlist($product->id) ? 'Remove from Wishlist' : 'Add to Wishlist' }}" aria-label="Wishlist">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20.5s-7.5-4.4-9.6-9.1C1 8.1 2.9 4.5 6.5 4.5c2 0 3.6 1 4.6 2.5.3.4.6.4.9 0 1-1.5 2.6-2.5 4.6-2.5 3.6 0 5.5 3.6 4.1 6.9-2.1 4.7-9.6 9.1-9.6 9.1z"/></svg>
        </button>

        {{-- OUT OF STOCK ribbon across the image --}}
        @if($isOut)
            <div class="stock-out-overlay" aria-label="Out of Stock">
                <span class="stock-out-stamp">Out of Stock</span>
            </div>
        @endif

        {{-- LEFT badge: discount, otherwise "New" --}}
        @if($hasDiscount && $discountPercent > 0 && !$isOut)
            <div class="label-block label-left">
                <div class="product-badget">{{ $discountPercent }}% Off</div>
            </div>
        @elseif($isNew && !$isOut)
            <div class="label-block label-left">
                <div class="product-badget">New</div>
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
        <h5 class="title">
            <a href="{{ route('front.products.show', ['product' => $productParam]) }}"
               title="{{ $product->name }}">
                {{ $product->name }}
            </a>
        </h5>
        <span class="pc-sub">{{ $minQty > 1 ? 'Wholesale · Min '.$minQty.' pcs' : ($isVariable ? 'More options available' : (($product->relationLoaded('category') ? optional($product->category)->name : null) ?: 'Wholesale Price')) }}</span>

        <div class="product-price-variant">
            <div class="price-wrap">
                <span class="price current-price" style="font-family:'Hind Siliguri', sans-serif;">
                    @if($curr == 'BDT') ৳ {{ number_format((float)($data['price'] ?? 0), 2) }}
                    @elseif($curr == 'Dollar') $ {{ number_format((float)($data['price'] ?? 0), 2) }}
                    @elseif($curr == 'Euro') {{ number_format((float)($data['price'] ?? 0), 2) }}
                    @elseif($curr == 'Rupee') {{ number_format((float)($data['price'] ?? 0), 2) }}
                    @endif
                </span>

                @if($hasDiscount)
                    <span class="price old-price" style="font-family:'Hind Siliguri', sans-serif;">
                        @if($curr == 'BDT') ৳ {{ number_format((float)($data['old_price'] ?? 0), 2) }}
                        @elseif($curr == 'Dollar') $ {{ number_format((float)($data['old_price'] ?? 0), 2) }}
                        @elseif($curr == 'Euro') {{ number_format((float)($data['old_price'] ?? 0), 2) }}
                        @elseif($curr == 'Rupee') {{ number_format((float)($data['old_price'] ?? 0), 2) }}
                        @endif
                    </span>
                @endif
            </div>

            @if($isOut)
                <button type="button" class="add-to-cart-btn" disabled aria-disabled="true" title="Out of Stock" aria-label="Out of Stock">
                    {!! $cartIcon !!}
                </button>
            @elseif($isVariable)
                <a href="{{ route('front.products.show', ['product' => $productParam]) }}"
                   class="add-to-cart-btn is-variable" title="Select Options" aria-label="Select Options">
                    {!! $cartIcon !!}
                </a>
            @else
                <button type="button" class="add-to-cart-btn ajax-add-btn"
                    data-url="{{ route('front.carts.store') }}"
                    data-product="{{ $product->id }}"
                    data-variation="{{ $variationId }}"
                    data-token="{{ csrf_token() }}"
                    title="Add to Cart" aria-label="Add to Cart">
                    {!! $cartIcon !!}
                </button>
            @endif
        </div>
    </div>

</div>