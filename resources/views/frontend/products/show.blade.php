@extends('frontend.app')

@php
    use App\Models\Information;
    use App\Models\BanglaText;
    use App\Models\Page;
    use App\Models\ProductStock;
    use App\Models\AdminText;

    $aboutUs        = Page::where('page','about')->first();
    $termsCondition = Page::where('page','term')->first();
    $info           = Information::first();
    $bangla_text    = BanglaText::first();
    
    $singleProduct->loadMissing(['variations.size','variations.color','variations.stocks']);

    $dt = AdminText::first();

    $DEFAULT_SIZE_ID  = 0; 
    $DEFAULT_COLOR_ID = 0; 

    $varMap   = [];
    $sizesMap = [];
    $colorsMap= [];

    if($singleProduct->variations && $singleProduct->variations->count() > 0){
        foreach($singleProduct->variations as $v){
            $sid = (int)($v->size_id ?? 0);
            $cid = (int)($v->color_id ?? 0);

            $sizeLabel  = $v->size_label  ?? optional($v->size)->name ?? ($v->size ?? 'Default');
            $colorLabel = $v->color_label ?? optional($v->color)->name ?? ($v->color ?? 'Default');

            if($sid !== 0) $sizesMap[$sid]  = $sizeLabel;
            if($cid !== 0) $colorsMap[$cid] = $colorLabel;

            $base  = (float)($v->price ?? 0);
            $after = (float)($v->after_discount_price ?? 0);
            $disc  = (float)($v->discount_price ?? 0);

            if($after > 0 && $after < $base) $final = $after;
            elseif($disc > 0 && $disc < $base) $final = $disc;
            else $final = $base;

            // স্টক পড়ার একটাই নিয়ম — helpers.php::resolveStock()। আগে এই পেজ, popup
            // আর প্রোডাক্ট কার্ড তিন আলাদা উৎস পড়ত বলে এক প্রোডাক্টের তিন রকম সংখ্যা দেখাত।
            // single টাইপে একটাই ভ্যারিয়েশন — তার স্টক না থাকলে প্রোডাক্টের স্টকে নামে
            // (resolveStock-এর নিয়ম); variable হলে প্রতিটা ভ্যারিয়েশনের নিজের স্টক।
            $stock = ($singleProduct->type ?? 'single') === 'variable'
                ? resolveStock($singleProduct, $v)
                : resolveStock($singleProduct);

            $varImage = $v->image ? getImage('products', $v->image) : null;

            $key = $sid.'|'.$cid;
            $varMap[$key] = [
                'id' => (int)$v->id,
                'size_id' => $sid,
                'color_id'=> $cid,
               'size' => $sid !== 0 ? $sizeLabel : 'Default',
                'color'=> $cid !== 0 ? $colorLabel : 'Default',
                'raw'  => $base,
                'price'=> $final,
                'stock'=> $stock,
                'image'=> $varImage, 
            ];
        }
    }

    $defaultVar = $singleProduct->variations->first();
    if($defaultVar){
        foreach($singleProduct->variations as $v){
            if(!empty($v->is_default) && (int)$v->is_default === 1){
                $defaultVar = $v; break;
            }
        }
    }
    
    $defaultSizeId  = (int)($defaultVar->size_id ?? 0);
    $defaultColorId = (int)($defaultVar->color_id ?? 0);

    if(count($varMap) === 0) {
        $key = $DEFAULT_SIZE_ID.'|'.$DEFAULT_COLOR_ID;
        
        $mPrice = $singleProduct->sell_price;
        $mAfter = $singleProduct->after_discount;
        $mFinal = ($mAfter > 0 && $mAfter < $mPrice) ? $mAfter : $mPrice;

        $varMap[$key] = [
            'id'       => null, 
            'size_id'  => $DEFAULT_SIZE_ID,
            'color_id' => $DEFAULT_COLOR_ID,
            'size'     => 'Regular',
            'color'    => 'Regular',
            'raw'      => (float)$mPrice,
            'price'    => (float)$mFinal,
            'stock'    => (int)$singleProduct->stock_quantity,
            'image'    => null,
        ];
        
        $defaultSizeId  = $DEFAULT_SIZE_ID;
        $defaultColorId = $DEFAULT_COLOR_ID;
    }

    $initialKey = $defaultSizeId.'|'.$defaultColorId;
    $initialVar = $varMap[$initialKey] ?? (count($varMap) ? reset($varMap) : null);

    // --- Safe Initial Stock Logic ---
    // আগে ডিফল্ট ভ্যারিয়েশনের স্টক ০ হলে প্রোডাক্টের মোট স্টক দেখানো হতো, ফলে
    // স্টক-আউট ভ্যারিয়েশনেও বাটন চালু থাকত। এখন নির্বাচিত ভ্যারিয়েশনের স্টকই চূড়ান্ত।
    $initialStock = $initialVar ? (int)($initialVar['stock'] ?? 0) : (int)($singleProduct->stock_quantity ?? 0);
    $inStock = ($initialStock > 0);

    // Wholesale products must be bought in at least this many pieces.
    $minOrderQty = $singleProduct->minOrderQty();

    // Whole product unavailable — same rule the cart and product cards use
    $productOut = !productIsOrderable($singleProduct);
    if ($productOut) $inStock = false;
    // --------------------------------

    $curr = $info->currency;

    $initFinal = (float)($initialVar['price'] ?? ($singleProduct->after_discount > 0 ? $singleProduct->after_discount : $singleProduct->sell_price));
    $initRaw   = (float)($initialVar['raw']   ?? ($singleProduct->sell_price ?? 0));

    // Accent for price / active states: the site's brand colour. Some sites use a
    // white brand text on a coloured gradient — there fall back to the Order Now colour.
    $pdAccent = themeAccent($info->order_now_btn_color ?? '#0f172a');

    $totalReviews  = $singleProduct->reviews->count();
    $averageRating = $totalReviews > 0 ? round($singleProduct->reviews->avg('review'), 2) : 0;
    $initSavePct   = ($initRaw > $initFinal && $initRaw > 0) ? (int) round((($initRaw - $initFinal) / $initRaw) * 100) : 0;
    $currSymbol    = $info->currency_symbol ?? '৳';
    // "170 ৳ off" — whole amounts without decimals, otherwise two.
    $fmtOff        = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
    $initOff       = max(0, $initRaw - $initFinal);
    // At or below this many pieces the "left" badge switches to the urgent style.
    $lowStockLimit = max(1, (int) ($info->stock_warning_limit ?? 5));

    $hasMultipleVariants = count($varMap) > 1;
    $showSize  = $hasMultipleVariants && (count($sizesMap) > 0);
    $showColor = $hasMultipleVariants && (count($colorsMap) > 0);
@endphp

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>

<style>
    :root{
      --brand-gradient: {!! $info->gradient_code ?? 'linear-gradient(90deg,#0d6efd,#00276C)' !!};
      --brand-text: {{ $info->primary_color ?? '#ffffff' }};
      --brand-dark: #00276C;
      --pd-accent: {{ $pdAccent }};

      --bg: #f4f6f9;
      --card: #ffffff;
      --text: #0f172a;
      --muted: #64748b;

      --border: rgba(0,0,0,0.06);
      
      --premium-shadow: 0 10px 35px rgba(0, 0, 0, 0.04);
      --premium-shadow-hover: 0 20px 45px rgba(0, 0, 0, 0.08);
      --premium-border: #ffffff;

      --radius: 16px;
      --radius2: 20px;

      --success: #10b981;
      --danger: #ef4444;
      --warn: #f59e0b;

      --t: .3s cubic-bezier(0.4, 0, 0.2, 1);
      --t-smooth: .5s cubic-bezier(0.34, 1.56, 0.64, 1);
      --t-elastic: .6s cubic-bezier(0.68, -0.55, 0.265, 1.55);

      --common-btn-bg: {{ $info->common_btn_color ?? 'rgb(25, 135, 84)' }};
      --common-btn-text: {{ $info->common_btn_text_color ?? '#ffffff' }};
      --order-btn-bg: {{ $info->order_now_btn_color ?? '#0f172a' }};
      --order-btn-text: {{ $info->order_now_btn_text_color ?? '#ffffff' }};
    }

    body {
        background-color: var(--bg);
    }
    
    .bg-color-white{ background: var(--bg) !important; }
    .axil-single-product-area{ background: var(--bg) !important; }

    /* Breadcrumb: one line; links keep their width, the product name takes
       the remaining space and is cut with "…" on small screens. */
    .pd-breadcrumb{ padding-top: 16px; }
    .pd-breadcrumb ol{
        display: flex; align-items: center; flex-wrap: nowrap;
        list-style: none; margin: 0; padding: 0;
        font-size: 14px; line-height: 1.4; color: var(--muted);
    }
    .pd-breadcrumb li{ display: flex; align-items: center; flex-shrink: 0; white-space: nowrap; margin: 0; }
    .pd-breadcrumb li + li::before{ content: "/"; margin: 0 8px; color: #cbd5e1; }
    .pd-breadcrumb a{ color: var(--muted); text-decoration: none; transition: color .2s ease; }
    .pd-breadcrumb a:hover{ color: var(--text); }
    .pd-breadcrumb li.is-current{
        display: block; flex: 0 1 auto; min-width: 0;
        overflow: hidden; text-overflow: ellipsis;
        color: var(--text); font-weight: 600;
    }
    @media (max-width: 575px){
        .pd-breadcrumb{ padding-top: 12px; }
        .pd-breadcrumb ol{ font-size: 12.5px; }
        .pd-breadcrumb li + li::before{ margin: 0 5px; }
        /* long category names may shrink too, so the product name stays visible */
        .pd-breadcrumb li:not(:first-child):not(.is-current){ flex-shrink: 1; min-width: 40px; overflow: hidden; }
        .pd-breadcrumb li:not(:first-child):not(.is-current) a{ overflow: hidden; text-overflow: ellipsis; }
    }

    /* --- Added Thumbnail Fix --- */
    .axil-product .thumbnail {
        aspect-ratio: 1 / 1 !important;
    }
    /* --------------------------- */

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeInLeft {
        from { opacity: 0; transform: translateX(-40px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes fadeInRight {
        from { opacity: 0; transform: translateX(40px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes fadeInScale {
        from { opacity: 0; transform: scale(0.92); }
        to { opacity: 1; transform: scale(1); }
    }
    @keyframes shimmerLoad {
        0% { background-position: -1000px 0; }
        100% { background-position: 1000px 0; }
    }
    @keyframes floatBadge {
        0%, 100% { transform: translateY(0) rotate(-3deg); }
        50% { transform: translateY(-6px) rotate(-3deg); }
    }
    @keyframes priceFlash {
        0% { transform: scale(1); background: #f8fafc; }
        50% { transform: scale(1.08); background: #fef3c7; }
        100% { transform: scale(1); background: #f8fafc; }
    }
    @keyframes successPop {
        0% { transform: scale(1); }
        50% { transform: scale(1.15) rotate(5deg); }
        100% { transform: scale(1); }
    }
    @keyframes ripple {
        to { transform: scale(2.5); opacity: 0; }
    }
    @keyframes slideInBlur {
        from { opacity: 0; filter: blur(8px); transform: translateY(20px); }
        to { opacity: 1; filter: blur(0); transform: translateY(0); }
    }
    @keyframes pulseRing {
        0% { box-shadow: 0 0 0 0 rgba(15, 23, 42, 0.4); }
        70% { box-shadow: 0 0 0 14px rgba(15, 23, 42, 0); }
        100% { box-shadow: 0 0 0 0 rgba(15, 23, 42, 0); }
    }
    @keyframes shimmerSweep {
        0% { left: -100%; }
        100% { left: 200%; }
    }
    @keyframes iconSpin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    @keyframes wobble {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-3px) rotate(-2deg); }
        75% { transform: translateX(3px) rotate(2deg); }
    }
    @keyframes glowPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(251, 191, 36, 0.4); }
        50% { box-shadow: 0 0 0 8px rgba(251, 191, 36, 0); }
    }

    .single-product-thumbnail-wrap {
        border-radius: var(--radius2);
        box-shadow: var(--premium-shadow) !important;
        border: 4px solid var(--premium-border);
        background: var(--card);
        transition: all var(--t);
        animation: fadeInLeft 0.7s cubic-bezier(0.4, 0, 0.2, 1) both;
    }
    .single-product-thumbnail-wrap:hover {
        box-shadow: var(--premium-shadow-hover) !important;
        transform: translateY(-4px);
    }

    .single-product-thumbnail-wrap img {
        transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .single-product-thumbnail-wrap:hover img {
        transform: scale(1.04);
    }

    .small-thumb-img {
        position: relative;
        overflow: hidden;
        border-radius: 10px;
        cursor: pointer;
        transition: all var(--t);
        opacity: 1;
    }
    .small-thumb-img:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    }
    .small-thumb-img img {
        transition: transform 0.4s ease;
    }

    /* ✅ CUSTOM SMALL THUMB GALLERY — Slick কে replace করি */
    /* Desktop: Vertical scrollable list */
    .small-thumb-wrapper.custom-gallery-mode {
        max-height: 480px;
        overflow-y: auto;
        overflow-x: hidden;
        scrollbar-width: thin;
        scrollbar-color: rgba(0,0,0,0.15) transparent;
        padding-right: 4px;
        display: block !important;
    }
    .small-thumb-wrapper.custom-gallery-mode::-webkit-scrollbar {
        width: 4px;
    }
    .small-thumb-wrapper.custom-gallery-mode::-webkit-scrollbar-track {
        background: transparent;
    }
    .small-thumb-wrapper.custom-gallery-mode::-webkit-scrollbar-thumb {
        background: rgba(0,0,0,0.15);
        border-radius: 4px;
    }
    .small-thumb-wrapper.custom-gallery-mode .small-thumb-img {
        display: block;
        margin: 0 0 10px 0 !important;
        width: 100%;
        opacity: 1 !important;
        visibility: visible !important;
        animation: none !important;
    }
    .small-thumb-wrapper.custom-gallery-mode .small-thumb-img img {
        width: 100%;
        max-width: 90px;
        height: auto;
        aspect-ratio: 1 / 1;
        /* পোশাকের ছবি লম্বাটে (2:3 / 4:5), বর্গাকার থাম্বে cover দিলে ~৩৩% কেটে
           যেত আর কোন ছবি কোনটা বোঝা যেত না। contain-এ পুরোটা দেখা যায়। */
        object-fit: contain;
        background: #f6f6f6;
        border: 2px solid transparent;
        border-radius: 8px;
        display: block;
        margin: 0 auto;
    }
    .small-thumb-wrapper.custom-gallery-mode .small-thumb-img.active-thumb img,
    .small-thumb-wrapper.custom-gallery-mode .small-thumb-img:hover img {
        border-color: var(--brand-dark, #0f172a);
    }

    /* Mobile / Tablet: Horizontal scroll */
    @media (max-width: 991px) {
        .small-thumb-wrapper.custom-gallery-mode {
            display: flex !important;
            flex-direction: row;
            max-height: none;
            overflow-x: auto;
            overflow-y: hidden;
            gap: 10px;
            padding-bottom: 6px;
            padding-right: 0;
        }
        .small-thumb-wrapper.custom-gallery-mode::-webkit-scrollbar {
            height: 4px;
        }
        .small-thumb-wrapper.custom-gallery-mode .small-thumb-img {
            flex: 0 0 70px;
            margin: 0 !important;
        }
        .small-thumb-wrapper.custom-gallery-mode .small-thumb-img img {
            width: 70px;
            max-width: 70px;
        }
    }

    .label-block {
        animation: floatBadge 3s ease-in-out infinite;
    }
    .product-badget {
        position: relative;
        overflow: hidden;
    }
    .product-badget::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 50%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        animation: shimmerSweep 3s infinite;
    }

    .video-thumb-container {
        position: relative;
        cursor: pointer;
    }
    .video-play-icon {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 16px;
        color: #fff;
        background: rgba(239, 68, 68, 0.9);
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        z-index: 2;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        animation: pulseRing 2s infinite;
    }
    .video-play-icon i {
        transition: transform var(--t);
    }
    .video-thumb-container:hover .video-play-icon i {
        transform: scale(1.2);
    }
    .video-slide {
        display: flex;
        justify-content: center;
        align-items: center;
        background: #000;
        aspect-ratio: 1/1;
    }
    .video-slide iframe {
        width: 100% !important;
        height: 100% !important;
        border: none;
    }

    @media (min-width: 992px){
      .details_right{ margin-left: 20px; }
    }
    .details-price{ margin-bottom: 12px !important; }

    .details_right{
      border: 4px solid var(--premium-border) !important;
      background: var(--card);
      padding: 20px 24px;
      height: 100%;
      border-radius: var(--radius2);
      box-shadow: var(--premium-shadow) !important;
      position: relative;
      overflow: hidden;
      transition: all var(--t);
      animation: fadeInRight 0.7s cubic-bezier(0.4, 0, 0.2, 1) both;
      animation-delay: 0.15s;
    }
    .details_right:hover {
      box-shadow: var(--premium-shadow-hover) !important;
    }

    .woocommerce-tabs .tab-content {
      background: var(--card);
      border-radius: var(--radius2);
      padding: 24px;
      margin-top: 20px;
      box-shadow: var(--premium-shadow) !important;
      border: 4px solid var(--premium-border);
      transition: all var(--t);
      animation: fadeInUp 0.6s ease both;
    }
    .woocommerce-tabs .tab-content:hover {
      box-shadow: var(--premium-shadow-hover) !important;
    }
    .tab-pane.fade {
        transition: opacity 0.4s ease;
    }
    .tab-pane.fade.show.active {
        animation: slideInBlur 0.5s ease both;
    }

    .product-cart .name{
      font-size: 16px;
      font-weight: 800;
      text-transform: capitalize;
      color: var(--text);
      letter-spacing: -0.02em;
      line-height: 1.3;
      margin-bottom: 8px;
      animation: fadeInUp 0.5s ease both;
      animation-delay: 0.3s;
    }

    .details-price{
      font-size: 20px;
      font-weight: 800;
      color: var(--text);
      margin: 8px 0;
      display:flex;
      flex-wrap:wrap;
      align-items: baseline;
      gap: 12px;
      animation: fadeInUp 0.5s ease both;
      animation-delay: 0.4s;
    }
    .details-price del{
      color: var(--muted);
      font-size: 15px;
      font-weight: 600;
      transition: all var(--t);
    }
    .current-price-product{
      background: #f8fafc; 
      border: 1px solid var(--border);
      padding: 4px 12px;
      border-radius: 999px;
      font-size: 16px;
      color: var(--text);
      box-shadow: 0 2px 8px rgba(0,0,0,0.02);
      transition: all var(--t);
      display: inline-block;
    }
    .current-price-product.price-flash {
        animation: priceFlash 0.6s ease;
    }

    .meta-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 12px;
        animation: fadeInUp 0.5s ease both;
        animation-delay: 0.5s;
    }

    .details-ratting-wrapper{
      display: inline-flex;
      align-items: center;
      flex-wrap: nowrap;
      white-space: nowrap;
      gap: 6px;
      padding: 6px 12px;
      border-radius: 10px;
      border: 1px solid var(--border);
      background: var(--card);
      box-shadow: 0 4px 15px rgba(0,0,0,.02);
      font-size: 12px;
      font-weight: 600;
      transition: all var(--t);
    }
    .details-ratting-wrapper:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,.06);
    }
    .details-ratting-wrapper i{ color: #fbbf24; font-size: 12px; }
    .details-ratting-wrapper i.far.fa-star{ color: #cbd5e1; }
    .all-reviews-button{
      text-decoration: none;
      margin-left: 6px;
      cursor: pointer;
      font-weight: 800;
      color: var(--text);
      position: relative;
      font-size: 12px;
    }
    .all-reviews-button:after{
      content:"";
      position:absolute;
      left:0; right:0; bottom:-2px;
      height:2px;
      background: var(--text);
      border-radius: 999px;
      transform: scaleX(0);
      transform-origin:right;
      transition: transform var(--t);
    }
    .all-reviews-button:hover:after{ transform: scaleX(1); transform-origin:left; }

    .product-code p, .product-stock-box p {
      display: inline-flex;
      align-items:center;
      gap: 6px;
      background: #f8fafc;
      color: var(--text) !important;
      padding: 8px 14px;
      border-radius: 10px;
      line-height: 1;
      margin: 0;
      font-weight: 700;
      border: 1px solid var(--border);
      font-size: 13px;
      transition: all var(--t);
    }
    .product-code p:hover, .product-stock-box p:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.05);
    }
    .product-stock-box p i {
        transition: transform var(--t);
    }
    .product-stock-box p:hover i {
        transform: scale(1.2) rotate(10deg);
    }

    .premium-short-description {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-left: 4px solid var(--text);
        border-radius: 10px;
        padding: 12px 16px;
        margin: 12px 0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        font-family: 'Hind Siliguri', sans-serif;
        font-size: 14px;
        line-height: 1.6;
        color: var(--text);
        transition: all var(--t);
        animation: fadeInUp 0.5s ease both;
        animation-delay: 0.6s;
    }
    .premium-short-description:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.06);
        transform: translateY(-2px);
        border-left-width: 6px;
    }
    .premium-short-description p:last-child {
        margin-bottom: 0;
    }

    .hide_span{ display:none; }
    .size{ cursor:pointer; user-select:none; transition: all var(--t); }

    #variantBox{ 
        margin-top: 10px !important; 
        animation: fadeInUp 0.5s ease both;
        animation-delay: 0.7s;
    }
    #variantBox label{ font-size: 13px; margin-bottom: 6px !important; font-weight: 700; color: var(--text); }

    #variantBox .size{
      background: var(--card);
      border: 2px solid #e2e8f0 !important;
      border-radius: 10px !important;
      padding: 6px 14px !important;
      font-weight: 700;
      color: var(--muted);
      font-size: 13px !important;
      transition: all var(--t-smooth);
      position: relative;
      overflow: hidden;
    }
    #variantBox .size::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(15, 23, 42, 0.15);
        transform: translate(-50%, -50%);
        transition: width 0.5s ease, height 0.5s ease;
    }
    #variantBox .size:active::before {
        width: 200px;
        height: 200px;
    }
    #variantBox .size:hover{
      transform: translateY(-3px) scale(1.03);
      border-color: #cbd5e1 !important;
      color: var(--text);
      box-shadow: 0 10px 22px rgba(0,0,0,.06);
    }
    #variantBox .size.is-out{
      color: #94a3b8;
      border-style: dashed !important;
      text-decoration: line-through;
      text-decoration-color: rgba(220,38,38,.7);
      padding-right: 34px !important;
    }
    #variantBox .size.is-out::after{
      content: "Out";
      position: absolute; top: 50%; right: 5px; transform: translateY(-50%);
      background: #dc2626; color: #fff;
      font-size: 9px; font-weight: 700; letter-spacing: .3px; line-height: 1;
      padding: 3px 4px; border-radius: 4px; text-transform: uppercase;
    }
    #variantBox .size.is-out.active{
      background: #64748b !important; border-color: #64748b !important;
    }
    .quantity.is-disabled{ opacity: .5; pointer-events: none; }
    .pd-wholesale{ display:flex; flex-wrap:wrap; align-items:center; gap:8px; margin:6px 0 10px; }
    .pd-wholesale-badge{ background:#0f172a; color:#fff; font-size:12px; font-weight:700; padding:4px 10px; border-radius:999px; letter-spacing:.3px; }
    .pd-wholesale-text{ font-size:13px; color:#b45309; background:#fffbeb; border:1px solid #fde68a; padding:3px 10px; border-radius:999px; }
    #variantBox .size.active{
      border-color: var(--text) !important;
      background: var(--text) !important;
      color: var(--card) !important;
      box-shadow: 0 10px 25px rgba(0,0,0,.15);
      animation: successPop 0.4s ease;
    }

    .size_name{
      margin-top: 8px;
      display:inline-block;
      padding: 6px 14px;
      border-radius: 8px;
      background: #f1f5f9;
      color: var(--text) !important;
      font-weight: 700;
      font-size: 13px;
      width: 100%;
      transition: all var(--t);
    }

    .qty-cart{
      width: auto;
      display: flex;
      align-items: center;
      column-gap: 14px;
      margin-top: 12px;
    }
    .qty-cart .quantity{
      position: relative;
      border: 2px solid #e2e8f0;
      height: 40px;
      overflow: hidden;
      width: 120px;
      margin-top: 0;
      border-radius: 10px;
      background: var(--card);
      box-shadow: 0 4px 15px rgba(0,0,0,.02);
      transition: all var(--t);
    }
    .qty-cart .quantity:hover {
        border-color: #cbd5e1;
        box-shadow: 0 6px 18px rgba(0,0,0,.06);
    }
    .quantity .minus,
    .quantity .plus{
      position: absolute;
      bottom: 0;
      z-index: 2;
      height: 36px;
      line-height: 36px;
      width: 36px;
      text-align: center;
      cursor: pointer;
      transition: all var(--t);
      user-select:none;
      font-weight: 800;
      color: var(--text);
      background: transparent;
    }
    .quantity .minus{ left:0; font-size: 20px; }
    .quantity .plus{ right:0; font-size: 18px; }
    .quantity .minus:hover,
    .quantity .plus:hover{ 
        background: #f1f5f9; 
        color: #000; 
        transform: scale(1.15);
    }
    .quantity .minus:active,
    .quantity .plus:active {
        transform: scale(0.92);
    }
    .quantity input{
      position: relative;
      z-index: 1;
      text-align: center;
      font-size: 14px;
      height: 100%;
      width: 100%;
      font-weight: 800;
      color: var(--text);
      background: var(--card) !important;
      border: none !important;
      outline: none !important;
      pointer-events: none;
      transition: transform 0.3s ease;
    }
    .quantity input.qty-bump {
        animation: successPop 0.3s ease;
    }

    .single_product{
      gap: 12px;
      margin-top: 16px !important;
      display:flex;
      animation: fadeInUp 0.5s ease both;
      animation-delay: 0.8s;
    }

    .add_cart_btn,
    .order_now_btn,
    .submit-review-btn {
      height: 48px !important;
      border-radius: 12px !important;
      font-weight: 800 !important;
      letter-spacing: 0.5px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: all var(--t);
      font-size: 14px !important;
      border: none !important;
      cursor: pointer;
      position: relative;
      overflow: hidden;
    }

    .add_cart_btn::after,
    .order_now_btn::after,
    .submit-review-btn::after {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 50%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.25), transparent);
        transition: left 0.6s ease;
    }
    .add_cart_btn:hover::after,
    .order_now_btn:hover::after,
    .submit-review-btn:hover::after {
        left: 200%;
    }

    .add_cart_btn,
    .submit-review-btn {
      background: var(--common-btn-bg) !important;
      color: var(--common-btn-text) !important;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1) !important;
    }
    
    .add_cart_btn:hover,
    .submit-review-btn:hover {
      transform: translateY(-3px) scale(1.02);
      box-shadow: 0 14px 28px rgba(0, 0, 0, 0.22) !important;
      filter: brightness(1.05);
    }
    .add_cart_btn:active,
    .submit-review-btn:active {
        transform: translateY(-1px) scale(0.98);
    }
    .add_cart_btn i {
        transition: transform var(--t-elastic);
    }
    .add_cart_btn:hover i {
        transform: scale(1.2) rotate(-10deg);
    }
    .add_cart_btn.cart-success i {
        animation: successPop 0.5s ease;
    }

    .add_cart_btn {
        width: 56px !important;
        flex: 0 0 56px;
        padding: 0 !important;
    }
    .add_cart_btn i {
        font-size: 18px;
        margin: 0;
    }
    
    .order_now_btn {
        flex: 1;
        width: auto !important;
    }

    @keyframes premiumPulseGlow {
        0% { box-shadow: 0 0 0 0 rgba(15, 23, 42, 0.7), 0 6px 15px rgba(15, 23, 42, 0.2); }
        70% { box-shadow: 0 0 0 12px rgba(15, 23, 42, 0), 0 12px 25px rgba(15, 23, 42, 0.4); }
        100% { box-shadow: 0 0 0 0 rgba(15, 23, 42, 0), 0 6px 15px rgba(15, 23, 42, 0.2); }
    }

    .order_now_btn {
      background: var(--order-btn-bg) !important;
      color: var(--order-btn-text) !important;
      margin-left: 0 !important;
      position: relative;
      z-index: 1;
      border: 2px solid var(--order-btn-bg) !important;
      animation: premiumPulseGlow 2s infinite !important;
    }
    .order_now_btn:hover { 
        transform: translateY(-3px) scale(1.02); 
        box-shadow: 0 18px 38px rgba(15, 23, 42, 0.55) !important;
        animation-play-state: paused !important;
    }
    .order_now_btn:active {
        transform: translateY(-1px) scale(0.98);
    }
    .order_now_btn i {
        transition: transform var(--t);
    }
    .order_now_btn:hover i {
        transform: scale(1.12);
    }

    .wa_now_btn,
    .call_now_btn {
      height: 48px !important;
      width: 56px !important;
      flex: 0 0 56px;
      padding: 0 !important;
      border-radius: 12px !important;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all var(--t);
      border: none !important;
      cursor: pointer;
    }
    .wa_now_btn i,
    .call_now_btn i {
      font-size: 18px;
      margin: 0;
    }
    .wa_now_btn:hover,
    .call_now_btn:hover {
      transform: translateY(-3px) scale(1.02);
      filter: brightness(1.05);
    }
    .wa_now_btn {
      background: #25D366 !important;
      color: #fff !important;
      box-shadow: 0 8px 20px rgba(37,211,102,0.25) !important;
    }
    .call_now_btn {
      background: var(--common-btn-bg) !important;
      color: var(--common-btn-text) !important;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .courier-card{
      margin-top: 20px;
      background: var(--card);
      border: 2px solid #f1f5f9;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 20px rgba(0,0,0,.02);
      transition: all var(--t);
      animation: fadeInUp 0.5s ease both;
      animation-delay: 0.9s;
    }
    .courier-card:hover {
        box-shadow: 0 10px 30px rgba(0,0,0,.06);
        transform: translateY(-3px);
    }
    .courier-card .courier-title{
      background: #f8fafc;
      font-weight: 800;
      padding: 12px;
      text-align:center;
      border-bottom: 2px solid #f1f5f9;
      font-size: 14px;
      color: var(--text);
    }
    .courier-card table{ margin:0; border: none !important; }
    .courier-card table td{
      padding: 10px 14px !important;
      border-color: #f1f5f9 !important;
      font-weight: 600;
      color: var(--muted);
      font-size: 13px;
      transition: all var(--t);
    }
    .courier-card table tr:hover td {
        background: #f8fafc;
    }
    .courier-card table td:last-child{ font-weight: 800; text-align:right; color: var(--text); }

    .product-metas{ 
        margin-top: 14px !important; 
        font-size: 13px; 
        line-height: 1.6; 
        color: var(--muted);
        animation: fadeInUp 0.5s ease both;
        animation-delay: 1s;
    }
    .product-metas li{ 
        margin-bottom: 4px;
        transition: all var(--t);
        padding-left: 0;
    }
    .product-metas li:hover {
        padding-left: 6px;
        color: var(--text);
    }

    @media (max-width: 575.98px){
      .details_right{ padding: 16px; }
      .product-cart .name{ font-size: 16px; }
      .details-price{ font-size: 20px; }
      
      .product-code p, .product-stock-box p {
        padding: 6px 10px;
        font-size: 12px;
      }
      
      #variantBox .size {
        padding: 4px 10px !important;
        font-size: 12px !important;
        border-radius: 8px !important;
      }
      #variantBox label {
        font-size: 12px !important;
        margin-bottom: 4px !important;
      }

      .qty-cart .quantity{ width: 110px; height: 38px; margin: 0 auto 0 0; }
      .quantity .minus, .quantity .plus { height: 34px; line-height: 34px; width: 34px; }
      
      .single_product{ flex-direction: row; gap: 8px; }
      .add_cart_btn, .wa_now_btn, .call_now_btn { width: 44px !important; flex: 0 0 44px; height: 44px !important; }
      .wa_now_btn i, .call_now_btn i { font-size: 16px; }
      .order_now_btn{ font-size: 13px !important; height: 44px !important; padding: 0 5px !important; }
      
      .details-ratting-wrapper { padding: 4px 10px; font-size: 11px; }
      .details-ratting-wrapper i{ font-size: 11px; }
      .all-reviews-button{ font-size: 11px; }
      
      .nav.nav-tabs .nav-item a{ padding: 8px 12px; font-size: 12px; border-radius: 8px; }
      .mx_0{ margin-left: -5px; margin-right: -5px; }
      .premium-review-card { padding: 16px; }
    }

    .nav.nav-tabs{
      border: 4px solid var(--premium-border);
      border-radius: 16px;
      background: var(--card);
      padding: 8px;
      box-shadow: var(--premium-shadow) !important;
      gap: 8px !important;
      animation: fadeInUp 0.6s ease both;
    }
    .nav.nav-tabs .nav-item a{
      margin: 0; padding: 12px 24px; font-weight: 800; color: var(--muted); border-radius: 10px; border: none; transition: all var(--t);
      position: relative;
      overflow: hidden;
    }
    .nav.nav-tabs .nav-item a:hover{ 
        background: #f1f5f9; 
        color: var(--text);
        transform: translateY(-2px);
    }
    .nav-tabs .nav-link.active{
      color: var(--common-btn-text) !important;
      background: var(--common-btn-bg) !important; 
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15) !important;
      animation: successPop 0.4s ease;
    }

    .single-desc, .single-desc * , .product-desc-wrapper, .product-desc-wrapper * ,
    .woocommerce-tabs .tab-content, .woocommerce-tabs .tab-content * {
      font-family: 'Hind Siliguri', sans-serif !important; color: var(--text);
    }

    .woocommerce-tabs .tab-content i, .woocommerce-tabs .tab-content .fa,
    .woocommerce-tabs .tab-content .fas, .woocommerce-tabs .tab-content .far,
    .woocommerce-tabs .tab-content .fab, .rating-component .stars-box i,
    .details-ratting-wrapper i, .compliment-container i {
      font-family: "Font Awesome 5 Free" !important; font-style: normal !important; display: inline-block !important;
      visibility: visible !important; opacity: 1 !important; font-weight: 900; line-height: 1 !important; text-transform: none !important;
    }
    .woocommerce-tabs .tab-content .fab { font-family: "Font Awesome 5 Brands" !important; font-weight: 400 !important; }

    .premium-review-card { 
        background: var(--card); 
        border: 2px solid #f1f5f9; 
        border-radius: 20px; 
        padding: 24px; 
        box-shadow: var(--premium-shadow); 
        position: relative; 
        overflow: hidden;
        transition: all var(--t);
    }
    .premium-review-card:hover {
        box-shadow: var(--premium-shadow-hover);
        transform: translateY(-3px);
    }
    .review-header-title { font-weight: 800; font-size: 18px; color: var(--text); margin-bottom: 20px; position: relative; display: inline-block; }
    .review-header-title::after {
        content: "";
        position: absolute;
        bottom: -4px;
        left: 0;
        width: 40%;
        height: 3px;
        background: var(--common-btn-bg);
        border-radius: 999px;
        transition: width 0.4s ease;
    }
    .premium-review-card:hover .review-header-title::after {
        width: 100%;
    }

    .rating-box-wrapper { 
        text-align: center; 
        background: #f8fafc; 
        border-radius: 14px; 
        padding: 16px; 
        border: 1px solid #f1f5f9; 
        margin-bottom: 20px;
        transition: all var(--t);
    }
    .rating-box-wrapper:hover {
        background: #f1f5f9;
        transform: scale(1.01);
    }
    .rating-label { display: block; font-weight: 800; font-size: 14px; color: var(--text); margin-bottom: 10px; }
    
    .rating-component .stars-box { display: flex; justify-content: center; gap: 8px; }
    .rating-component .stars-box .star { 
        font-size: 24px; 
        color: #cbd5e1; 
        cursor: pointer; 
        transition: all var(--t-elastic);
    }
    .rating-component .stars-box .star.hover, .rating-component .stars-box .star.selected { 
        color: #fbbf24; 
        filter: drop-shadow(0 4px 6px rgba(251, 191, 36, 0.3)); 
        transform: scale(1.25) rotate(-8deg);
    }
    .rating-component .stars-box .star.selected {
        animation: glowPulse 1.5s infinite;
    }

    .tags-container { display: none; margin-top: 12px; animation: slideUpFade 0.4s ease forwards; }
    .question-tag { 
        background: #fef2f2; 
        color: #ef4444; 
        font-weight: 700; 
        padding: 6px 14px; 
        border-radius: 999px; 
        font-size: 13px; 
        display: inline-block;
        animation: wobble 0.6s ease;
    }
    .tags-container[data-tag-set="4"] .question-tag { background: #ecfdf5; color: #10b981; }

    .make-compliment { text-align: center; }
    .compliment-container { 
        display: inline-flex; 
        align-items: center; 
        gap: 8px; 
        background: #f0fdf4; 
        color: #15803d; 
        padding: 8px 18px; 
        border-radius: 999px; 
        font-weight: 800; 
        font-size: 14px; 
        border: 1px solid #bbf7d0; 
        box-shadow: 0 4px 12px rgba(34, 197, 94, 0.1);
        animation: successPop 0.5s ease;
    }
    .compliment-container i { font-size: 16px; animation: bounceIcon 2s infinite; color: #15803d !important; }

    .premium-input-group label { font-weight: 800; font-size: 12px; text-transform: uppercase; color: var(--text); margin-bottom: 6px; letter-spacing: 0.5px; display: block; }
    .premium-input-group input, .premium-input-group textarea, .premium-input-group .form-control { 
        background: var(--card); 
        border: 2px solid #e2e8f0; 
        border-radius: 12px; 
        padding: 12px 16px; 
        width: 100%; 
        font-weight: 600; 
        font-size: 14px; 
        color: var(--text); 
        transition: all var(--t);
    }
    .premium-input-group input[type="file"] { padding: 9px 16px; font-size: 13px; background: #f8fafc; }
    .premium-input-group input:focus, .premium-input-group textarea:focus { 
        background: var(--card); 
        border-color: var(--text); 
        box-shadow: 0 6px 20px rgba(0,0,0,0.08); 
        outline: none;
        transform: translateY(-2px);
    }

    @keyframes slideUpFade { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes bounceIcon { 0%, 20%, 50%, 80%, 100% {transform: translateY(0);} 40% {transform: translateY(-4px);} 60% {transform: translateY(-2px);} }

    button:disabled, .btn:disabled{ opacity: .50 !important; cursor: not-allowed !important; box-shadow: none !important; transform: none !important; filter: grayscale(100%); animation: none !important; }

    .desc-collapse-wrapper {
        position: relative;
        max-height: 250px;
        overflow: hidden;
        transition: max-height 0.6s ease-in-out;
    }
    .desc-collapse-wrapper::after {
        content: "";
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 80px;
        background: linear-gradient(to bottom, rgba(255,255,255,0), var(--card));
        pointer-events: none;
        transition: opacity 0.3s ease;
    }
    .desc-collapse-wrapper.expanded::after {
        opacity: 0;
    }
    .view-more-btn {
        display: block;
        width: max-content;
        margin: 20px auto 0;
        background: none;
        border: 2px solid #e2e8f0;
        padding: 10px 28px;
        border-radius: 999px;
        font-weight: 800;
        font-size: 14px;
        cursor: pointer;
        color: var(--text);
        transition: all var(--t);
        position: relative;
        overflow: hidden;
    }
    .view-more-btn::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(15, 23, 42, 0.08), transparent);
        transition: left 0.5s ease;
    }
    .view-more-btn:hover::before {
        left: 100%;
    }
    .view-more-btn:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        transform: translateY(-3px);
        box-shadow: 0 10px 22px rgba(0,0,0,.06);
    }
    .view-more-btn i {
        transition: transform var(--t);
    }
    .view-more-btn.is-open i {
        transform: rotate(180deg);
    }

    .axil-product-area .col-lg-2,
    .axil-product-area .col-md-3,
    .axil-product-area .col-6 {
        animation: fadeInUp 0.6s ease both;
    }
    .explore-product-activation > div > .row > div:nth-child(1) { animation-delay: 0.05s; }
    .explore-product-activation > div > .row > div:nth-child(2) { animation-delay: 0.1s; }
    .explore-product-activation > div > .row > div:nth-child(3) { animation-delay: 0.15s; }
    .explore-product-activation > div > .row > div:nth-child(4) { animation-delay: 0.2s; }
    .explore-product-activation > div > .row > div:nth-child(5) { animation-delay: 0.25s; }
    .explore-product-activation > div > .row > div:nth-child(6) { animation-delay: 0.3s; }

    .section-title-wrapper h2 {
        position: relative;
        display: inline-block;
        animation: fadeInLeft 0.6s ease both;
    }
    .section-title-wrapper h2::after {
        content: "";
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 2px;
        background: var(--common-btn-bg);
        animation: drawLine 1s ease forwards;
        animation-delay: 0.4s;
    }
    @keyframes drawLine {
        to { width: 80px; }
    }

    .comment-list > li {
        animation: fadeInUp 0.5s ease both;
    }
    .comment-list > li:nth-child(1) { animation-delay: 0.1s; }
    .comment-list > li:nth-child(2) { animation-delay: 0.2s; }
    .comment-list > li:nth-child(3) { animation-delay: 0.3s; }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }
    /* ===== Stock out ===== */
    .single-product-thumbnail-wrap{ position: relative; }
    .pd-stock-out-overlay{
        position: absolute; inset: 0; z-index: 5;
        display: flex; align-items: center; justify-content: center;
        overflow: hidden; pointer-events: none;
        background: rgba(255,255,255,.18);
    }
    .pd-stock-out-overlay span{
        display: block; width: 150%; flex: 0 0 auto;
        padding: 14px 0;
        background: rgba(232,72,85,.78);
        color: #fff; font-weight: 700; font-size: 32px;
        letter-spacing: 2px; line-height: 1;
        text-align: center; text-transform: uppercase; white-space: nowrap;
        transform: rotate(-12deg);
        text-shadow: 0 1px 2px rgba(0,0,0,.15);
    }
    @media (max-width: 575px){
        .pd-stock-out-overlay span{ font-size: 22px; padding: 10px 0; }
    }
    .pd-thumb-stock-out{
        position: absolute; inset: 0; z-index: 3;
        display: flex; align-items: center; justify-content: center;
        overflow: hidden; pointer-events: none; border-radius: inherit;
        background: rgba(255,255,255,.35);
    }
    .pd-thumb-stock-out span{
        display: block; width: 160%; flex: 0 0 auto;
        padding: 4px 0;
        background: rgba(232,72,85,.85);
        color: #fff; font-weight: 700; font-size: 8px;
        letter-spacing: .5px; line-height: 1;
        text-align: center; text-transform: uppercase; white-space: nowrap;
        transform: rotate(-12deg);
    }
    .pd-stock-out-alert{
        margin: 8px 0 12px; padding: 10px 14px;
        border: 1px solid #fecaca; border-radius: 8px;
        background: #fef2f2; color: #b91c1c;
        font-size: 14px; font-weight: 500;
    }
    .pd-stock-out-alert i{ margin-right: 6px; }

    /* ===== Notify Me When Available ===== */
    .notify-me-wrap{ margin-top: 10px; }
    .notify-me-btn{
        width: 100%; height: 46px;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        border: 1.5px solid #e11d48; border-radius: 8px;
        background: #fff1f2; color: #e11d48;
        font-weight: 600; font-size: 15px; cursor: pointer;
        transition: background .2s, color .2s;
    }
    .notify-me-btn:hover{ background: #e11d48; color: #fff; }

    .notify-modal{ position: fixed; inset: 0; z-index: 100000; display: none; align-items: center; justify-content: center; padding: 16px; }
    .notify-modal.is-open{ display: flex; }
    .notify-modal__backdrop{ position: absolute; inset: 0; background: rgba(15,23,42,.55); }
    .notify-modal__dialog{
        position: relative; width: 100%; max-width: 420px;
        background: #fff; border-radius: 14px; padding: 20px;
        box-shadow: 0 20px 50px rgba(0,0,0,.25);
        animation: notifyIn .2s ease-out;
        max-height: calc(100vh - 32px); overflow-y: auto;
    }
    @keyframes notifyIn{ from{ opacity: 0; transform: translateY(12px); } to{ opacity: 1; transform: none; } }
    .notify-modal__head{ display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
    .notify-modal__head h5{
        flex: 1 1 auto; min-width: 0; margin: 0 !important; padding: 0 !important;
        font-size: 18px !important; font-weight: 700; line-height: 1.3 !important; color: #0f172a;
        display: flex; align-items: center; gap: 10px;
    }
    .notify-modal__head h5 i{
        flex: 0 0 auto; width: 34px; height: 34px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        background: #fff1f2; color: #e11d48; font-size: 16px;
    }
    .notify-modal .notify-modal__close{
        flex: 0 0 34px; width: 34px !important; min-width: 0 !important; height: 34px !important;
        margin: 0 !important; padding: 0 !important; border: 0 !important; border-radius: 50% !important;
        display: inline-flex; align-items: center; justify-content: center;
        background: #f1f5f9 !important; color: #475569 !important; box-shadow: none !important;
        font-size: 22px !important; line-height: 1 !important; cursor: pointer; transition: background .2s, color .2s;
    }
    .notify-modal .notify-modal__close:hover{ background: #e2e8f0 !important; color: #0f172a !important; }
    .notify-modal__text{ font-size: 14px; color: #475569; margin: 0 0 12px; line-height: 1.5; }
    .notify-modal__variant{ font-size: 13px; font-weight: 600; color: #0f172a; margin: -4px 0 12px; }
    .notify-phone{ display: flex; align-items: stretch; border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; }
    .notify-phone:focus-within{ border-color: #e11d48; box-shadow: 0 0 0 3px rgba(225,29,72,.12); }
    .notify-phone__cc{ display: flex; align-items: center; padding: 0 12px; background: #f8fafc; border-right: 1px solid #cbd5e1; font-size: 14px; white-space: nowrap; color: #0f172a; }
    .notify-phone input{ flex: 1; min-width: 0; border: 0 !important; outline: 0; height: 46px; padding: 0 12px; font-size: 15px; box-shadow: none !important; background: #fff; }
    .notify-error{ color: #dc2626; font-size: 13px; min-height: 18px; margin: 6px 0 4px; }
    .notify-error.is-info{ color: #92400e; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 8px 10px; margin: 8px 0; }
    .notify-submit{ width: 100%; height: 46px; border: 0; border-radius: 8px; background: #e11d48; color: #fff; font-weight: 600; font-size: 15px; cursor: pointer; }
    .notify-submit:hover{ background: #be123c; }
    .notify-submit:disabled{ opacity: .7; cursor: wait; }
    .notify-consent{ display: flex; align-items: flex-start; gap: 8px; margin: 12px 0 0; font-size: 13px; color: #475569; cursor: pointer; }
    .notify-modal .notify-consent input[type="checkbox"]{
        -webkit-appearance: checkbox !important; appearance: auto !important;
        position: static !important; opacity: 1 !important; visibility: visible !important; display: inline-block !important;
        width: 16px !important; height: 16px !important; margin: 2px 0 0 !important; padding: 0 !important;
        flex: 0 0 auto; accent-color: #2563eb; cursor: pointer;
    }
    .notify-modal .notify-consent span{ padding: 0 !important; }
    .notify-modal .notify-consent span::before, .notify-modal .notify-consent span::after{ display: none !important; }
    .notify-success{ display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: 10px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #047857; font-weight: 600; font-size: 14px; }
    .notify-success i{ font-size: 26px; color: #10b981; flex: 0 0 auto; }

    @media (max-width: 575px){
        .notify-modal{ align-items: flex-end; padding: 0; }
        .notify-modal__dialog{ max-width: 100%; border-radius: 16px 16px 0 0; padding: 18px 16px 22px; }
        .notify-modal__head h5{ font-size: 16px !important; }
    }

    /* ==========================================================================
       PREMIUM LAYOUT LAYER
       Sits on top of the rules above: calmer surfaces, one accent colour
       (--pd-accent = the site's brand colour), clear type hierarchy. Button
       colours still come from the admin settings (--order-btn-*, --common-btn-*).
       ========================================================================== */
    :root{
        --bg: #f6f7f9;
        --pd-line: #eaecf0;
        --pd-accent-soft: #f7f7f8;
        --pd-accent-ring: rgba(15,23,42,.12);
        --pd-card-shadow: 0 1px 2px rgba(16,24,40,.04), 0 16px 36px -18px rgba(16,24,40,.14);
    }
    @supports (color: color-mix(in srgb, red 10%, white)){
        :root{
            --pd-accent-soft: color-mix(in srgb, var(--pd-accent) 7%, #fff);
            --pd-accent-ring: color-mix(in srgb, var(--pd-accent) 18%, transparent);
        }
    }

    /* Order Now uses the brand colour on this page, same red as the nav. */
    :root{ --order-btn-bg: var(--pd-accent); --order-btn-text: #fff; }

    /* --- surfaces: no lift-on-hover, hairline borders --- */
    .single-product-thumbnail-wrap,
    .details_right,
    .woocommerce-tabs .tab-content,
    .premium-review-card{
        border: 1px solid var(--pd-line) !important;
        border-radius: 22px;
        box-shadow: var(--pd-card-shadow) !important;
    }
    .single-product-thumbnail-wrap:hover,
    .details_right:hover,
    .woocommerce-tabs .tab-content:hover,
    .premium-review-card:hover,
    .courier-card:hover,
    .premium-short-description:hover,
    .rating-box-wrapper:hover,
    .product-code p:hover, .product-stock-box p:hover,
    .details-ratting-wrapper:hover{
        transform: none;
        box-shadow: var(--pd-card-shadow) !important;
    }
    .courier-card:hover, .premium-short-description:hover, .rating-box-wrapper:hover,
    .product-code p:hover, .product-stock-box p:hover, .details-ratting-wrapper:hover{ box-shadow: none !important; }
    .single-product-thumbnail-wrap:hover img{ transform: none; }
    /* Hover zoom (mouse devices, desktop width): a lens on the main image and an
       enlarged pane beside it — see the script at the bottom. */
    .pd-zoom-lens{
        position: absolute; z-index: 4; display: none; pointer-events: none;
        border: 1px solid var(--pd-accent); border-radius: 6px;
        background: rgba(255,255,255,.35); box-shadow: 0 0 0 1px rgba(255,255,255,.6) inset;
    }
    .pd-zoom-pane{
        position: fixed; z-index: 1010; display: none; pointer-events: none;
        background-color: #fff; background-repeat: no-repeat;
        border: 1px solid var(--pd-line); border-radius: 22px;
        box-shadow: 0 24px 60px -12px rgba(16,24,40,.28);
    }
    .pd-zoom-on .product-large-thumbnail-3 .thumbnail{ cursor: crosshair; }

    /* --- gallery --- */
    @media (min-width: 992px){
        .mobile_show > .row{ align-items: flex-start; }
        .mobile_show > .row > .col-lg-6:first-child{ position: sticky; top: 96px; }
        /* thumbnail rail is exactly as tall as the main image and scrolls inside it */
        .mx_0 > .col-lg-2{ position: relative; }
        .mx_0 > .col-lg-2 > .small-thumb-wrapper.custom-gallery-mode{
            position: absolute; top: 0; bottom: 0; left: 0; right: 8px;
            max-height: none; padding-right: 0; scrollbar-width: none;
        }
        .mx_0 > .col-lg-2 > .small-thumb-wrapper.custom-gallery-mode::-webkit-scrollbar{ display: none; }
    }
    /* the theme draws a yellow frame + padding around the main image */
    .single-product-thumbnail.img-section{ border: none !important; padding: 0 !important; }
    .small-thumb-wrapper.custom-gallery-mode .small-thumb-img img{ border-radius: 12px; background: #fff; border: 2px solid var(--pd-line); }
    .small-thumb-wrapper.custom-gallery-mode .small-thumb-img.active-thumb img,
    .small-thumb-wrapper.custom-gallery-mode .small-thumb-img:hover img{ border-color: var(--pd-accent) !important; }
    .small-thumb-img:hover{ transform: none; box-shadow: none; }
    .single-product-thumbnail-wrap .product-quick-view.position-view a{
        width: 44px; height: 44px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        background: rgba(255,255,255,.94); color: var(--text) !important; font-size: 16px;
        box-shadow: 0 6px 18px rgba(16,24,40,.16); transition: transform var(--t), color var(--t);
    }
    .single-product-thumbnail-wrap .product-quick-view.position-view a:hover{ transform: scale(1.06); color: var(--pd-accent) !important; }
    .single-product-thumbnail-wrap .label-block{ animation: none; }
    .single-product-thumbnail-wrap .label-block .product-badget{
        background: var(--pd-accent) !important; color: #fff;
        border-radius: 999px; padding: 6px 14px; font-weight: 700; font-size: 13px; letter-spacing: .02em;
    }

    /* --- details column --- */
    .details_right{ height: auto; padding: 30px 32px; }
    .pd-eyebrow{ min-height: 0; }
    .pd-eyebrow a, .pd-eyebrow span{
        font-size: 12px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase;
        color: var(--pd-accent); text-decoration: none;
    }
    .pd-eyebrow a:hover{ text-decoration: underline; }
    .product-cart h1.name{
        font-size: 28px; font-weight: 700; line-height: 1.25; letter-spacing: -.015em;
        color: var(--text); margin: 8px 0 12px; text-transform: none;
    }
    .details-ratting-wrapper{
        display: flex; flex-wrap: wrap; white-space: normal; gap: 8px;
        padding: 0; border: none; background: none; box-shadow: none;
        font-size: 13.5px; font-weight: 500; color: var(--muted);
    }
    .details-ratting-wrapper .pd-stars{ display: inline-flex; gap: 2px; }
    .details-ratting-wrapper .pd-stars i{ color: #f5a623; font-size: 14px; }
    .details-ratting-wrapper .pd-stars i.far{ color: #d0d5dd; font-weight: 400; }
    .pd-rating-num{ font-weight: 700; color: var(--text); }
    .all-reviews-button{ margin-left: 2px; font-size: 13.5px; font-weight: 600; color: var(--pd-accent); }
    .all-reviews-button:after{ background: var(--pd-accent); }

    .details-price{
        margin: 18px 0 !important; padding: 18px 0;
        border-top: 1px solid var(--pd-line); border-bottom: 1px solid var(--pd-line);
        align-items: center; gap: 12px;
    }
    .current-price-product{
        background: none; border: none; padding: 0; box-shadow: none; border-radius: 0;
        font-size: 34px; font-weight: 800; line-height: 1; letter-spacing: -.02em; color: var(--pd-accent);
    }
    @keyframes pdPriceFlash{ 0%{ opacity: .35; transform: translateY(4px); } 100%{ opacity: 1; transform: translateY(0); } }
    .current-price-product.price-flash{ animation: pdPriceFlash .35s ease; }
    .details-price del{ font-size: 18px; font-weight: 500; color: #98a2b3; }
    .pd-save-badge{
        background: var(--pd-accent); color: #fff; border-radius: 999px;
        padding: 5px 11px; font-size: 12px; font-weight: 700; line-height: 1; letter-spacing: .02em;
    }

    .pd-sku{ margin: 0 0 10px; font-size: 14px; font-weight: 500; color: var(--muted); }
    .pd-sku span{ font-weight: 700; color: var(--text); text-transform: uppercase; letter-spacing: .04em; }
    .pd-price-label{
        font-size: 14px; font-weight: 700; line-height: 1; color: var(--text);
        text-transform: uppercase; letter-spacing: .04em;
    }
    .pd-save-badge{ border-radius: 6px; padding: 6px 10px; }

    .meta-row.pd-meta{ justify-content: flex-start; margin: 18px 0 0; }
    .product-stock-box p{
        background: none; border: none; padding: 0; border-radius: 0; gap: 6px;
        font-size: 14px; font-weight: 500; color: var(--text) !important;
    }
    .pd-status-label{ font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
    .pd-status{ font-weight: 800; text-transform: uppercase; letter-spacing: .04em; }
    .pd-status.is-in{ color: #16a34a; }
    .pd-status.is-out{ color: #dc2626; }
    /* how many pieces are left — a badge, urgent colours when stock is low */
    .pd-stock-left{
        display: inline-flex; align-items: center; gap: 6px; margin-left: 6px;
        padding: 6px 12px; border-radius: 999px; line-height: 1;
        font-size: 13px; font-weight: 600; color: #067647 !important;
        background: #ecfdf3; border: 1px solid #abefc6;
    }
    .pd-stock-left b{ font-size: 15px; font-weight: 800; color: inherit; }
    .pd-stock-left i{ font-size: 12px; color: inherit; }
    .pd-stock-left.is-low{
        color: #fff !important; background: var(--pd-accent); border-color: var(--pd-accent);
        box-shadow: 0 6px 14px -4px var(--pd-accent-ring); animation: pdStockPulse 1.8s ease-in-out infinite;
    }
    @keyframes pdStockPulse{ 0%, 100%{ transform: scale(1); } 50%{ transform: scale(1.06); } }

    .premium-short-description{
        border: none; border-radius: 0; box-shadow: none; background: none;
        padding: 0; margin: 16px 0 0; font-size: 15px; line-height: 1.7; color: #475467;
    }

    /* --- variants --- */
    #variantBox{ margin-top: 20px !important; }
    #variantBox .variant-group{ width: 100%; }
    #variantBox label{
        display: block; font-size: 14px; font-weight: 700; letter-spacing: 0; text-transform: none;
        color: var(--text); margin-bottom: 10px !important;
    }
    #variantBox label .pd-variant-picked{ color: var(--pd-accent); margin-left: 4px; }
    #variantBox .size{
        border: 1.5px solid #e4e7ec !important; border-radius: 999px !important;
        padding: 8px 18px !important; font-size: 13.5px !important; font-weight: 600; color: var(--text);
    }
    #variantBox .size:hover{ transform: none; box-shadow: none; border-color: var(--pd-accent) !important; color: var(--pd-accent); }
    #variantBox .size.active{
        background: var(--pd-accent) !important; border-color: var(--pd-accent) !important;
        color: #fff !important; box-shadow: 0 6px 14px -4px var(--pd-accent-ring); animation: none;
    }
    #variantBox .size.is-out{ color: #98a2b3; padding-right: 40px !important; }
    #variantBox .size.is-out.active{ background: #f2f4f7 !important; border-color: #98a2b3 !important; color: #667085 !important; box-shadow: none; }

    /* --- buy block: quantity + cart, then Order Now, then contact buttons --- */
    .pd-buy{ margin-top: 24px; }
    .single_product{ flex-wrap: wrap; align-items: stretch; gap: 12px; margin-top: 0 !important; }
    .single_product .qty-cart{ order: 1; flex: 0 0 136px; }
    .qty-cart .quantity{ width: 136px; height: 52px; border: 1.5px solid #e4e7ec; border-radius: 14px; box-shadow: none; }
    .quantity .minus, .quantity .plus{ width: 44px; height: 49px; line-height: 49px; font-weight: 600; }
    .quantity .minus:hover, .quantity .plus:hover{ transform: none; background: #f2f4f7; }
    .quantity input{ font-size: 16px; }
    .single_product .add_cart_btn{
        order: 2; flex: 1 1 0; width: auto !important; height: 52px !important;
        padding: 0 18px !important; border-radius: 14px !important; font-size: 15px !important; letter-spacing: 0;
    }
    .single_product .add_cart_btn i{ font-size: 16px; }
    .single_product .order_now_btn{
        order: 3; flex: 1 1 100%; height: 56px !important;
        border-radius: 14px !important; font-size: 16px !important; letter-spacing: .02em;
    }
    .single_product .wa_now_btn,
    .single_product .call_now_btn{
        order: 4; flex: 1 1 0; width: auto !important; height: 46px !important; gap: 8px;
        border-radius: 12px !important; font-size: 14px; font-weight: 600; box-shadow: none !important;
    }
    .single_product .wa_now_btn{ background: #ecfdf3 !important; color: #0e7a43 !important; border: 1px solid #c6f0d6 !important; }
    .single_product .call_now_btn{ background: #fff !important; color: var(--text) !important; border: 1px solid #e4e7ec !important; }
    .single_product .wa_now_btn i, .single_product .call_now_btn i{ font-size: 16px; }
    .single_product .wa_now_btn:hover, .single_product .call_now_btn:hover{ transform: translateY(-1px); filter: none; }
    .single_product .call_now_btn:hover{ border-color: var(--pd-accent) !important; color: var(--pd-accent) !important; }

    /* --- trust strip + delivery --- */
    .pd-trust{
        list-style: none; margin: 22px 0 0; padding: 16px 0;
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;
        border-top: 1px solid var(--pd-line); border-bottom: 1px solid var(--pd-line);
    }
    .pd-trust li{
        display: flex; align-items: center; justify-content: center; gap: 8px; margin: 0;
        font-size: 13px; font-weight: 600; color: var(--text); text-align: center;
    }
    .pd-trust li + li{ border-left: 1px solid var(--pd-line); }
    .pd-trust i{ color: var(--pd-accent); font-size: 15px; }

    .courier-card{ margin-top: 22px; border: 1px solid var(--pd-line); border-radius: 16px; box-shadow: none; }
    .courier-card .courier-title{
        text-align: left; padding: 13px 18px; background: #fafbfc;
        border-bottom: 1px solid var(--pd-line); font-size: 14px; font-weight: 700;
    }
    .courier-card .courier-title i{ color: var(--pd-accent); margin-right: 6px; }
    .courier-card table, .courier-card table > :not(caption) > *,
    .courier-card table > :not(caption) > * > *{ border-width: 0 !important; box-shadow: none !important; }
    .courier-card table td{ padding: 12px 18px !important; font-size: 14px; border-bottom: 1px solid var(--pd-line) !important; background: transparent !important; }
    .courier-card table tr:last-child td{ border-bottom: 0 !important; }
    .courier-card table td:last-child{ font-weight: 700; }
    .product-metas li:hover{ padding-left: 0; }

    /* --- share --- */
    .pd-share{ display: flex; align-items: center; flex-wrap: wrap; gap: 12px; margin-top: 20px; }
    .pd-share-label{ font-size: 13px; font-weight: 700; color: var(--text); }
    .pd-share-sep{ width: 1px; height: 20px; background: var(--pd-line); }
    @media (max-width: 575.98px){ .pd-share-sep{ flex: 0 0 100%; height: 0; } }
    .pd-share-label i{ color: var(--pd-accent); margin-right: 4px; }
    .pd-share-links{ display: flex; flex-wrap: wrap; gap: 8px; }
    .pd-share-btn{
        width: 38px; height: 38px; border-radius: 50%; padding: 0;
        display: inline-flex; align-items: center; justify-content: center;
        background: #fff; border: 1px solid #e4e7ec; color: var(--text); font-size: 15px;
        text-decoration: none; cursor: pointer; transition: background var(--t), color var(--t), border-color var(--t), transform var(--t);
    }
    .pd-share-btn:hover{ transform: translateY(-2px); color: #fff; }
    .pd-share-btn.is-fb:hover{ background: #1877f2; border-color: #1877f2; }
    .pd-share-btn.is-msg:hover{ background: #0084ff; border-color: #0084ff; }
    .pd-share-btn.is-wa:hover{ background: #25d366; border-color: #25d366; }
    .pd-share-btn.is-tg:hover{ background: #229ed9; border-color: #229ed9; }
    .pd-share-btn.is-x:hover{ background: #0f172a; border-color: #0f172a; }
    .pd-share-btn.is-copy:hover, .pd-share-btn.is-copy.is-done{ background: var(--pd-accent); border-color: var(--pd-accent); color: #fff; }
    /* Messenger's share link only opens the app, so show it on touch devices only */
    @media (hover: hover) and (pointer: fine){ .pd-share-btn.is-msg{ display: none; } }

    /* --- tabs: underline style --- */
    .woocommerce-tabs.wc-tabs-wrapper{ margin-top: 12px; }
    .nav.nav-tabs{
        border: none; border-bottom: 1px solid var(--pd-line); border-radius: 0;
        background: none; box-shadow: none !important; padding: 0; gap: 30px !important; margin-bottom: 0 !important;
    }
    .nav.nav-tabs .nav-item a{
        padding: 14px 2px; border-radius: 0; background: none !important;
        font-size: 16px; font-weight: 700; color: var(--muted); overflow: visible;
    }
    .nav.nav-tabs .nav-item a:hover{ transform: none; color: var(--text); }
    .nav-tabs .nav-link.active{ color: var(--text) !important; background: none !important; box-shadow: none !important; animation: none; }
    .nav-tabs .nav-link.active::after{
        content: ""; position: absolute; left: 0; right: 0; bottom: -1px; height: 3px;
        border-radius: 3px 3px 0 0; background: var(--pd-accent);
    }
    .pd-tab-count{
        display: inline-block; margin-left: 4px; padding: 2px 8px; border-radius: 999px;
        background: #f2f4f7; color: var(--muted); font-size: 12px; font-weight: 700; vertical-align: middle;
    }
    .woocommerce-tabs .tab-content{ padding: 30px; margin-top: 22px; }
    .product-desc-wrapper .title,
    .pro-desc-commnet-area .title{ font-size: 20px; font-weight: 700; margin-bottom: 0; }
    .single-desc{ font-size: 15px; line-height: 1.8; }
    .view-more-btn{ border-width: 1.5px; }
    .view-more-btn:hover{ transform: none; border-color: var(--pd-accent); color: var(--pd-accent); box-shadow: none; }
    .review-header-title::after{ background: var(--pd-accent); }
    #reviewFormCard{ scroll-margin-top: 100px; }
    .review-login-box{ text-align: center; padding: 18px 8px 8px; }
    /* the tab content forces one text colour on everything inside it, icons included */
    .woocommerce-tabs .tab-content .review-login-box > i{ font-size: 40px; color: var(--pd-accent) !important; }
    .woocommerce-tabs .tab-content .review-login-box .submit-review-btn,
    .woocommerce-tabs .tab-content .review-login-box .submit-review-btn i{ color: var(--common-btn-text) !important; }
    .review-login-title{ margin: 12px 0 4px; font-size: 17px; font-weight: 700; color: var(--text); }
    .review-login-text{ margin: 0 auto 16px; max-width: 360px; font-size: 14px; line-height: 1.6; color: var(--muted) !important; }
    .review-login-box .submit-review-btn{ display: inline-flex; width: auto; text-decoration: none; }
    .premium-input-group input:focus, .premium-input-group textarea:focus{
        border-color: var(--pd-accent); box-shadow: 0 0 0 4px var(--pd-accent-ring); transform: none;
    }

    /* --- related products --- */
    .axil-product-area .section-title-wrapper{ margin-bottom: 22px !important; }
    .pd-section-kicker{
        display: block; margin-bottom: 4px;
        font-size: 12px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: var(--pd-accent);
    }
    .axil-product-area .section-title-wrapper h2{
        display: block; margin: 0; animation: none;
        font-size: 28px; font-weight: 700; letter-spacing: -.015em; color: var(--text);
    }
    .axil-product-area .section-title-wrapper h2::after{ display: none; }

    /* --- sticky buy bar: small screens only; replaces the site's floating bottom nav on this page --- */
    .pd-buy-bar{ display: none; }
    @media (max-width: 767.98px){
        #footerNav{ display: none !important; }
        body{ padding-bottom: calc(76px + env(safe-area-inset-bottom, 0px)) !important; }
        .pd-buy-bar{
            position: fixed; left: 0; right: 0; bottom: 0; z-index: 1030;
            display: flex; align-items: center; gap: 12px;
            padding: 10px 14px calc(10px + env(safe-area-inset-bottom, 0px));
            background: #fff; border-top: 1px solid var(--pd-line);
            box-shadow: 0 -10px 28px -12px rgba(16,24,40,.22);
        }
        body.hide-header .pd-buy-bar{ display: none; }
        .pd-buy-bar__price{ flex: 0 0 auto; display: flex; flex-direction: column; line-height: 1.15; min-width: 0; }
        .pd-buy-bar__now{ font-size: 18px; font-weight: 800; letter-spacing: -.01em; color: var(--pd-accent); white-space: nowrap; }
        .pd-buy-bar__old{ font-size: 12px; font-weight: 500; color: #98a2b3; white-space: nowrap; }
        .pd-buy-bar__actions{ flex: 1 1 auto; display: flex; gap: 8px; min-width: 0; }
        .pd-buy-bar__btn{
            height: 46px; border-radius: 12px; padding: 0 12px;
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            font-size: 14px; font-weight: 700; line-height: 1; white-space: nowrap; cursor: pointer;
            filter: none !important; transition: transform .15s ease, opacity .15s ease;
        }
        .pd-buy-bar__btn:active{ transform: scale(.97); }
        .pd-buy-bar__btn.is-cart{ flex: 1 1 0; background: #fff; color: var(--pd-accent); border: 1.5px solid var(--pd-accent); }
        .pd-buy-bar__btn.is-buy{ flex: 1 1 0; background: var(--pd-accent); color: #fff; border: 1.5px solid var(--pd-accent); }
        .pd-buy-bar__btn.is-notify{ flex: 1 1 auto; display: none; background: var(--pd-accent); color: #fff; border: 1.5px solid var(--pd-accent); }
        .pd-buy-bar__btn:disabled{ opacity: .45 !important; }
        /* out of stock: the two buy buttons give way to Notify Me */
        .pd-buy-bar.is-out .pd-buy-bar__actions{ display: none; }
        .pd-buy-bar.is-out .pd-buy-bar__btn.is-notify{ display: inline-flex; }
    }
    @media (max-width: 359.98px){
        .pd-buy-bar{ gap: 8px; padding-left: 10px; padding-right: 10px; }
        .pd-buy-bar__btn.is-cart{ flex: 0 0 46px; padding: 0; }
        .pd-buy-bar__btn.is-cart span{ display: none; }
    }

    @media (max-width: 991px){
        .details_right{ padding: 24px 22px; }
        .product-cart h1.name{ font-size: 24px; }
    }
    @media (max-width: 575.98px){
        .details_right{ padding: 20px 16px; border-radius: 18px; }
        .single-product-thumbnail-wrap, .woocommerce-tabs .tab-content{ border-radius: 18px; }
        .product-cart h1.name{ font-size: 21px; margin: 6px 0 10px; }
        .current-price-product{ font-size: 28px; }
        .details-price{ margin: 14px 0 !important; padding: 14px 0; }
        .details-price del{ font-size: 16px; }
        #variantBox label{ font-size: 13.5px !important; margin-bottom: 8px !important; }
        .pd-price-label{ font-size: 13px; }
        #variantBox .size{ padding: 7px 14px !important; font-size: 13px !important; border-radius: 999px !important; }
        .single_product{ gap: 10px; }
        .single_product .qty-cart{ flex: 0 0 116px; }
        .qty-cart .quantity{ width: 116px; height: 48px; margin: 0; }
        .quantity .minus, .quantity .plus{ width: 38px; height: 45px; line-height: 45px; }
        .single_product .add_cart_btn{ flex: 1 1 0; width: auto !important; height: 48px !important; font-size: 14px !important; }
        .single_product .order_now_btn{ height: 52px !important; font-size: 15px !important; padding: 0 12px !important; }
        .single_product .wa_now_btn, .single_product .call_now_btn{ flex: 1 1 0; width: auto !important; height: 44px !important; font-size: 13.5px; }
        .pd-trust{ gap: 4px; padding: 14px 0; }
        .pd-trust li{ flex-direction: column; gap: 6px; font-size: 12px; }
        .nav.nav-tabs{ gap: 22px !important; }
        .nav.nav-tabs .nav-item a{ padding: 12px 2px; font-size: 15px; border-radius: 0; }
        .woocommerce-tabs .tab-content{ padding: 20px 16px; margin-top: 16px; }
        .axil-product-area .section-title-wrapper h2{ font-size: 22px; }
    }
</style>
@endpush

@section('content')
<main class="main-wrapper">
    <div class="axil-single-product-area p pb--0 bg-color-white">
        <div class="single-product-thumb mb--5">
            @php
                $crumbCategory = $singleProduct->category;
                $crumbSubCategory = $singleProduct->sub_category_id
                    ? \App\Models\Category::find($singleProduct->sub_category_id)
                    : null;
            @endphp
            <div class="container">
                <nav class="pd-breadcrumb" aria-label="Breadcrumb">
                    <ol>
                        <li><a href="{{ route('front.home') }}"><i class="fas fa-home"></i> Home</a></li>
                        @if($crumbCategory && $crumbCategory->url)
                            <li><a href="{{ route('front.category', [$crumbCategory->url]) }}">{{ $crumbCategory->name }}</a></li>
                        @endif
                        @if($crumbSubCategory && $crumbSubCategory->url)
                            <li><a href="{{ route('front.category', [$crumbSubCategory->url]) }}">{{ $crumbSubCategory->name }}</a></li>
                        @endif
                        <li class="is-current" aria-current="page" title="{{ $singleProduct->name }}">{{ $singleProduct->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="container mt-2 mobile_show">
                <div class="row">
                    <div class="col-lg-6 mb--10">
                        <div class="row mx_0">
                            <div class="col-lg-10 order-lg-2">
                                <div class="single-product-thumbnail-wrap zoom-gallery overflow-hidden">
                                    <div class="single-product-thumbnail product-large-thumbnail-3 img-section axil-product">
                                        
                                        @if($singleProduct->video_link && $singleProduct->is_video_active == 1)
                                            <div class="thumbnail h-100 overflow-hidden video-slide">
                                                @php
                                                    $video_iframe = $singleProduct->video_link;
                                                    $video_iframe = preg_replace_callback('/src="([^"]+)"/', function($m) {
                                                        $url = $m[1];
                                                        $sep = strpos($url, '?') !== false ? '&' : '?';
                                                        return 'src="' . $url . $sep . 'autoplay=1&mute=1"';
                                                    }, $video_iframe);
                                                @endphp
                                                {!! str_replace(['<iframe', 'width=', 'height='], ['<iframe allow="autoplay; encrypted-media" style="width:100%; height:100%; border:none;"', 'data-w=', 'data-h='], $video_iframe) !!}
                                            </div>
                                        @endif

                                        <div class="thumbnail h-100 overflow-hidden">
                                            <a href="{{ getImage('products', $singleProduct->image)}}" class="popup-zoom" id="main-image-link">
                                                <img src="{{ getImage('products', $singleProduct->image)}}" alt="{{ $singleProduct->name}} Images" id="main-image">
                                            </a>
                                        </div>
                                        @foreach($singleProduct->images as $im)
                                        <div class="thumbnail h-100 overflow-hidden">
                                            <a href="{{ getImage('products', $im->image)}}" class="popup-zoom">
                                                <img src="{{ getImage('products', $im->image)}}" alt="{{ $singleProduct->name}} Images">
                                            </a>
                                        </div>
                                        @endforeach
                                    </div>

                                    @if($singleProduct->after_discount > 0)
                                        @php
                                            $price = $singleProduct->sell_price;
                                            $afterDiscount = $singleProduct->after_discount;
                                            $discountAmount = $price - $afterDiscount;
                                            $discountPercent = $price > 0 ? round(($discountAmount / $price) * 100, 0) : 0;
                                        @endphp
                                        <div class="label-block">
                                            <div class="product-badget" style="background: #0f172a;">
                                                {{$discountPercent}}% Off
                                            </div>
                                        </div>
                                    @endif

                                    <div class="pd-stock-out-overlay" id="pdStockOutOverlay" style="{{ $inStock ? 'display:none;' : '' }}"><span>Out of Stock</span></div>

                                    <div class="product-quick-view position-view">
                                        <a href="{{ getImage('products', $singleProduct->image)}}" class="popup-zoom">
                                            <i class="fas fa-search-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-2 order-lg-1 px-lg-0">
                                <div class="product-small-thumb-3 small-thumb-wrapper">

                                    @if($singleProduct->video_link && $singleProduct->is_video_active == 1)
                                        <div class="small-thumb-img mt-2 video-thumb-container">
                                            <div class="video-play-icon">
                                                <i class="fas fa-play" style="margin-left: 3px;"></i>
                                            </div>
                                            <img src="{{ getImage('products', $singleProduct->image)}}" alt="Video Thumbnail" style="opacity: 0.7;" id="video-thumb-image">
                                        </div>
                                    @endif

                                    <div class="small-thumb-img mt-2">
                                        <img src="{{ getImage('products', $singleProduct->image)}}" alt="{{ $singleProduct->name}} image" id="thumb-image">
                                        {{-- Same "Out of Stock" stamp the product cards use, on the variant's own image --}}
                                        <div class="pd-thumb-stock-out" id="pdThumbStockOut" style="{{ $inStock ? 'display:none;' : '' }}"><span>Out of Stock</span></div>
                                    </div>
                                    @foreach($singleProduct->images as $im)
                                    <div class="small-thumb-img mt-2">
                                        <img src="{{ getImage('products', $im->image)}}" alt="{{ $singleProduct->name}} image">
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 mb--30">
                        <div class="details_right">
                            <div class="product">
                                <div class="product-cart">
                                    <div class="pd-eyebrow">
                                        @if($crumbCategory && $crumbCategory->url)
                                            <a href="{{ route('front.category', [$crumbCategory->url]) }}">{{ $crumbCategory->name }}</a>
                                        @elseif($crumbCategory)
                                            <span>{{ $crumbCategory->name }}</span>
                                        @endif
                                    </div>
                                    <h1 class="name">{{ $singleProduct->name}}</h1>
                                    @if(!empty($singleProduct->sku))
                                        <p class="pd-sku"><span>SKU:</span> {{ $singleProduct->sku }}</p>
                                    @endif

                                    <div class="details-ratting-wrapper">
                                        <span class="pd-stars" aria-hidden="true">
                                            @for($st = 1; $st <= 5; $st++)
                                                <i class="{{ $averageRating >= $st ? 'fas fa-star' : ($averageRating >= $st - 0.5 ? 'fas fa-star-half-alt' : 'far fa-star') }}"></i>
                                            @endfor
                                        </span>
                                        @if($totalReviews > 0)
                                            <span class="pd-rating-num">{{ number_format($averageRating, 1) }}</span>
                                            <span class="pd-rating-count">({{ $totalReviews }} {{ $totalReviews === 1 ? 'Review' : 'Reviews' }})</span>
                                            <a class="all-reviews-button" href="#writeReview">See Reviews</a>
                                        @else
                                            <span class="pd-rating-count">No reviews yet</span>
                                            <a class="all-reviews-button" href="#writeReview">Write a review</a>
                                        @endif
                                    </div>
                                    @if($minOrderQty > 1)
                                        <div class="pd-wholesale">
                                            <span class="pd-wholesale-badge"><i class="fas fa-boxes"></i> Wholesale</span>
                                            <span class="pd-wholesale-text">Minimum order: <strong>{{ $minOrderQty }}</strong> pcs{{ $singleProduct->type === 'variable' ? ' per variant' : '' }}</span>
                                        </div>
                                    @endif

                                    <p class="details-price">
                                        <span class="pd-price-label">Price:</span>
                                        <span class="current-price-product">{{ biz_format_currency($initFinal) }}</span>

                                        @if($initSavePct > 0)
                                          <del id="product-old-price" class="price old-price">
                                              {{ biz_format_currency($initRaw) }}
                                          </del>
                                        @else
                                          <del id="product-old-price" class="price old-price" style="display:none;"></del>
                                        @endif

                                        <span class="pd-save-badge" id="pdSaveBadge" style="{{ $initSavePct > 0 ? '' : 'display:none;' }}">{{ $fmtOff($initOff) }} {{ $currSymbol }} off</span>
                                    </p>

                                    <div class="pd-stock-out-alert" id="pdStockOutAlert" style="{{ $inStock ? 'display:none;' : '' }}">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span id="pdStockOutAlertText">{{ $productOut ? 'This product is currently out of stock.' : 'This variant is currently out of stock. Please choose another option.' }}</span>
                                    </div>

                                    <form action="{{ route('front.carts.storeCart') }}" id="cart_submit" method="POST">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $singleProduct->id }}">
                                        <input type="hidden" name="product_name" value="{{ $singleProduct->name }}">
                                        <input type="hidden" name="category_id" value="{{ $singleProduct->category->name??'' }}">

                                        <input type="hidden" name="variation_id" id="variation_id" value="{{ $initialVar['id'] ?? '' }}">
                                        <input type="hidden" name="variant_name" id="variant_name" value="">

                                        <input type="hidden" id="size_value"  name="size_value"  value="">
                                        <input type="hidden" id="size_value1" name="size_value1" value="">
                                        <input type="hidden" id="price_val"   name="price_val"   value="{{ $initialVar['price'] ?? ($singleProduct->after_discount > 0 ? $singleProduct->after_discount : $singleProduct->sell_price) }}">
                                        <input type="hidden" id="price_val1"  name="price_val1"  value="{{ $initialVar['price'] ?? ($singleProduct->after_discount > 0 ? $singleProduct->after_discount : $singleProduct->sell_price) }}">

                                        <input type="hidden" name="action_type" id="input_action_type" value="cart">

                                        @if(!empty($singleProduct->short_description))
                                            <div class="premium-short-description">
                                                {!! $singleProduct->short_description !!}
                                            </div>
                                        @endif

                                        @if(isset($singleProduct->variations) && $singleProduct->variations->count() > 0 && ($showSize || $showColor))
                                          <div class="mt-3 d-flex flex-column gap-3" id="variantBox">

                                            @if($showSize)
                                              <div class="variant-group">
                                                  <label class="mb-2">Select Your Size: <span class="pd-variant-picked" id="pdPickedSize">{{ $sizesMap[$defaultSizeId] ?? '' }}</span></label>
                                                  <div class="d-flex flex-wrap gap-2" id="sizeOptions">
                                                    @foreach($sizesMap as $sid => $slabel)
                                                      <div class="size size-opt {{ ((int)$sid === (int)$defaultSizeId) ? 'active' : '' }}"
                                                           data-size-id="{{ (int)$sid }}">
                                                        {{ $slabel }}
                                                      </div>
                                                    @endforeach
                                                  </div>
                                              </div>
                                            @endif

                                            @if($showColor)
                                              <div class="variant-group">
                                                  <label class="mb-2">Select Your Color: <span class="pd-variant-picked" id="pdPickedColor">{{ $colorsMap[$defaultColorId] ?? '' }}</span></label>
                                                  <div class="d-flex flex-wrap gap-2" id="colorOptions">
                                                    @foreach($colorsMap as $cid => $clabel)
                                                      <div class="size color-opt {{ ((int)$cid === (int)$defaultColorId) ? 'active' : '' }}"
                                                           data-color-id="{{ (int)$cid }}">
                                                        {{ $clabel }}
                                                      </div>
                                                    @endforeach
                                                  </div>
                                              </div>
                                            @endif

                                            <span class="size_name mt-2 d-none"></span>

                                            <script>
                                              window.__VAR_MAP__ = @json($varMap);
                                              window.__VAR_DEFAULT__ = {
                                                size_id: {{ (int)$defaultSizeId }},
                                                color_id: {{ (int)$defaultColorId }},
                                                default_size_id: {{ (int)$DEFAULT_SIZE_ID }},
                                                default_color_id: {{ (int)$DEFAULT_COLOR_ID }},
                                                show_size: {{ $showSize ? 'true' : 'false' }},
                                                show_color: {{ $showColor ? 'true' : 'false' }},
                                              };
                                            </script>
                                          </div>
                                        @else
                                          <script>
                                            window.__VAR_MAP__ = @json($varMap);
                                            window.__VAR_DEFAULT__ = {
                                              size_id: {{ (int)$defaultSizeId }},
                                              color_id: {{ (int)$defaultColorId }},
                                              default_size_id: {{ (int)$DEFAULT_SIZE_ID }},
                                              default_color_id: {{ (int)$DEFAULT_COLOR_ID }},
                                              show_size: false,
                                              show_color: false,
                                            };
                                          </script>
                                        @endif

                                        <div class="meta-row pd-meta">
                                            <div class="product-stock-box">
                                                <p id="stock-text-element">
                                                    <span class="pd-status-label">Status:</span>
                                                    @if($inStock)
                                                        <strong class="pd-status is-in">Stock In</strong>
                                                        <span class="pd-stock-left {{ (int)$initialStock <= $lowStockLimit ? 'is-low' : '' }}"><i class="fas fa-{{ (int)$initialStock <= $lowStockLimit ? 'fire' : 'box-open' }}"></i> {{ (int)$initialStock <= $lowStockLimit ? 'Only ' : '' }}<b>{{ (int)$initialStock }}</b> left</span>
                                                    @else
                                                        <strong class="pd-status is-out">Stock Out</strong>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>

                                        <div class="pd-buy">
                                            <div class="d-flex single_product col-sm-12">
                                                <div class="qty-cart m-0">
                                                    <div class="quantity" style="margin: 0;">
                                                        <span class="minus" aria-label="Decrease quantity">&minus;</span>
                                                        <input type="number" name="quantity" value="{{ $minOrderQty }}" min="{{ $minOrderQty }}" readonly aria-label="Quantity">
                                                        <span class="plus" aria-label="Increase quantity">+</span>
                                                    </div>
                                                </div>
                                                @if(!empty($info->whats_num) && ($info->whats_active ?? 0) == 1)
                                                    <a href="https://wa.me/+88{{ $info->whats_num }}?text={{ urlencode($singleProduct->name.' - এই পণ্যটি সম্পর্কে জানতে চাই।') }}"
                                                       target="_blank" class="btn wa_now_btn" title="WhatsApp">
                                                        <i class="fab fa-whatsapp"></i> <span>WhatsApp</span>
                                                    </a>
                                                @endif
                                                @if(!empty($info->owner_phone))
                                                    <a href="tel:{{ $info->owner_phone }}" class="btn call_now_btn" title="Call Now">
                                                        <i class="fas fa-phone-alt"></i> <span>Call Now</span>
                                                    </a>
                                                @endif
                                                <button type="submit"
                                                        class="btn add_cart_btn"
                                                        {{ $inStock ? '' : 'disabled' }} title="Add to Cart">
                                                    <i class="fas fa-shopping-cart"></i> <span>Add to Cart</span>
                                                </button>

                                                <template id="orderNowLabel">@if(($singleProduct->is_free_shipping ?? 0) == 1)<i class="fas fa-shipping-fast"></i> &nbsp; {{ $bangla_text->fshipping_text ?? 'Free Shipping' }}@else<i class="fas fa-shopping-bag"></i> {{ $dt->order_now_text ?? 'Order Now' }}@endif</template>
                                                <button type="submit"
                                                        class="btn px-4 order_now_btn order_now_btn_m"
                                                        {{ $inStock ? '' : 'disabled' }}>
                                                    @if(!$inStock)
                                                        Out of Stock
                                                    @elseif(($singleProduct->is_free_shipping ?? 0) == 1)
                                                        <i class="fas fa-shipping-fast"></i> &nbsp; {{ $bangla_text->fshipping_text ?? 'Free Shipping' }}
                                                    @else
                                                        <i class="fas fa-shopping-bag"></i> {{ $dt->order_now_text ?? 'Order Now' }}
                                                    @endif
                                                </button>
                                            </div>
                                        </div>

                                        <div class="notify-me-wrap" id="notifyMeWrap" style="{{ $inStock ? 'display:none;' : '' }}">
                                            <button type="button" class="notify-me-btn" id="notifyMeOpen">
                                                <i class="far fa-bell"></i> Notify Me When Available
                                            </button>
                                        </div>

                                        <ul class="pd-trust">
                                            <li><i class="fas fa-shipping-fast"></i><span>Fast Delivery</span></li>
                                            <li><i class="fas fa-shield-alt"></i><span>Safe Payment</span></li>
                                            <li><i class="fas fa-headset"></i><span>Live Support</span></li>
                                        </ul>

                                        @if(($singleProduct->is_free_shipping ?? 0) == 0)
                                            <div class="courier-card" style="font-family: 'Hind Siliguri', sans-serif;">
                                                <div class="courier-title"><i class="fas fa-truck"></i> {{ $dt->courier_delivery_cost_text ?? 'Delivery Cost' }}</div>
                                                <table class="table table-bordered border-0">
                                                    <tbody>
                                                        @foreach($charges as $charge)
                                                        <tr>
                                                            <td>{{ $charge->title }}</td>
                                                            <td>{{ biz_format_currency($charge->amount) }}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="courier-card" style="font-family: 'Hind Siliguri', sans-serif;">
                                                <div class="courier-title"><i class="fas fa-truck"></i> {{ $dt->courier_delivery_cost_text ?? 'Delivery Cost' }}</div>
                                                <div class="text-center text-success" style="padding: 14px; font-weight: 800; font-size: 16px;">
                                                    <i class="fas fa-shipping-fast"></i> {{ $bangla_text->fshipping_text ?? 'Free Shipping' }}
                                                </div>
                                            </div>
                                        @endif

                                        @php
                                            $shareUrl  = route('front.products.show', ['product' => $singleProduct->slug ?: $singleProduct->id]);
                                            $shareText = $singleProduct->name;
                                        @endphp
                                        <div class="pd-share">
                                            <button type="button" class="wl-toggle wl-btn {{ inWishlist($singleProduct->id) ? 'is-saved' : '' }}"
                                                    data-product="{{ $singleProduct->id }}" aria-pressed="{{ inWishlist($singleProduct->id) ? 'true' : 'false' }}">
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20.5s-7.5-4.4-9.6-9.1C1 8.1 2.9 4.5 6.5 4.5c2 0 3.6 1 4.6 2.5.3.4.6.4.9 0 1-1.5 2.6-2.5 4.6-2.5 3.6 0 5.5 3.6 4.1 6.9-2.1 4.7-9.6 9.1-9.6 9.1z"/></svg>
                                                <span data-wl-label>{{ inWishlist($singleProduct->id) ? 'Saved to Wishlist' : 'Add to Wishlist' }}</span>
                                            </button>
                                            <span class="pd-share-sep"></span>
                                            <span class="pd-share-label"><i class="fas fa-share-alt"></i> Share</span>
                                            <div class="pd-share-links">
                                                <a class="pd-share-btn is-fb" target="_blank" rel="noopener" title="Share on Facebook" aria-label="Share on Facebook"
                                                   href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}"><i class="fab fa-facebook-f"></i></a>
                                                <a class="pd-share-btn is-msg" target="_blank" rel="noopener" title="Share on Messenger" aria-label="Share on Messenger"
                                                   href="fb-messenger://share/?link={{ urlencode($shareUrl) }}"><i class="fab fa-facebook-messenger"></i></a>
                                                <a class="pd-share-btn is-wa" target="_blank" rel="noopener" title="Share on WhatsApp" aria-label="Share on WhatsApp"
                                                   href="https://wa.me/?text={{ urlencode($shareText.' '.$shareUrl) }}"><i class="fab fa-whatsapp"></i></a>
                                                <a class="pd-share-btn is-tg" target="_blank" rel="noopener" title="Share on Telegram" aria-label="Share on Telegram"
                                                   href="https://t.me/share/url?url={{ urlencode($shareUrl) }}&text={{ urlencode($shareText) }}"><i class="fab fa-telegram-plane"></i></a>
                                                <a class="pd-share-btn is-x" target="_blank" rel="noopener" title="Share on X" aria-label="Share on X"
                                                   href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($shareText) }}"><i class="fab fa-twitter"></i></a>
                                                <button type="button" class="pd-share-btn is-copy" id="pdShareCopy" title="Copy link" aria-label="Copy link"
                                                        data-url="{{ $shareUrl }}" data-title="{{ $shareText }}"><i class="fas fa-link"></i></button>
                                            </div>
                                        </div>

                                        @if(!empty(trim(strip_tags((string) $singleProduct->feature))))
                                            <ul class="product-metas mt-4" style="font-family: 'Hind Siliguri', sans-serif;">
                                              {!! $singleProduct->feature !!}
                                            </ul>
                                        @endif
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="woocommerce-tabs wc-tabs-wrapper">
            <div class="container">
            <ul class="nav nav-tabs mb-4 gap-3" id="myTab" role="tablist">
              <li class="nav-item" role="presentation">
                <a class="nav-link active" id="home-tab" data-bs-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">
                    {{ $dt->details_tab_text ?? 'Details' }}
                </a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="review-tab" data-bs-toggle="tab" href="#review" role="tab" aria-controls="review" aria-selected="false">
                    {{ $dt->reviews_tab_text ?? 'Reviews' }} <span class="pd-tab-count">{{ $totalReviews }}</span>
                </a>
              </li>
            </ul>
            <div class="tab-content" id="myTabContent">
              <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                  <div class="product-desc-wrapper">
                    <div class="">
                        <div class="col-lg-12 mb--20">
                            <h5 class="title">{{ $dt->short_description_text ?? 'Description' }}</h5>
                            
                            <div class="single-desc pt-4 desc-collapse-wrapper" id="descWrapper">
                                {!! $singleProduct->body !!}
                            </div>
                            <button type="button" class="view-more-btn" id="viewMoreBtn" style="display: none;">View More <i class="fas fa-chevron-down ms-1"></i></button>

                        </div>
                    </div>
                </div>
              </div>

              <div class="tab-pane fade" id="review" role="tabpanel" aria-labelledby="review-tab">
                <div class="woocommerce-tabs wc-tabs-wrapper" id="writeReview">
                    <div class="container">
                        <div class="reviews-wrapper pt-4">
                            <div class="row">
                                <div class="col-lg-6 mb--20">
                                    <div class="axil-comment-area pro-desc-commnet-area pt-3">
                                        <h5 class="title">Customer Reviews ({{ $totalReviews }})</h5>
                                        <ul class="comment-list">
                                            @include("frontend.products.partials.reviewList")
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-lg-6 mb--20">
                                    <div class="premium-review-card" id="reviewFormCard">
                                        <div class="comment-respond pro-des-commend-respond mt--0">
                                            <h5 class="review-header-title">Add a Review</h5>
                                            
                                            @auth
                                            <form action="{{ route('front.product-reviews.store')}}" method="POST" id="ajax_form2" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{$singleProduct->id}}" />
                                                <input type="hidden" name="review" id="review" value="" />
                                                
                                                <div class="rating-box-wrapper">
                                                    <span class="rating-label">How was your experience?</span>
                                                    <div class="rating-component">
                                                        <div class="status-msg">
                                                            <input class="rating_msg" type="hidden" name="rating_msg" value="" />
                                                        </div>
                                                        <div class="stars-box">
                                                            <i class="star fa fa-star" title="1 star" data-message="Poor" data-value="1"></i>
                                                            <i class="star fa fa-star" title="2 stars" data-message="Too bad" data-value="2"></i>
                                                            <i class="star fa fa-star" title="3 stars" data-message="Average quality" data-value="3"></i>
                                                            <i class="star fa fa-star" title="4 stars" data-message="Nice" data-value="4"></i>
                                                            <i class="star fa fa-star" title="5 stars" data-message="Very good quality" data-value="5"></i>
                                                        </div>
                                                        <div class="starrate">
                                                            <input class="ratevalue" type="hidden" name="rate_value" value="" />
                                                        </div>
                                                    </div>

                                                    <div class="feedback-tags">
                                                        <div class="tags-container" data-tag-set="1">
                                                            <div class="question-tag">Why was your experience so bad?</div>
                                                        </div>
                                                        <div class="tags-container" data-tag-set="2">
                                                            <div class="question-tag">Why was your experience so bad?</div>
                                                        </div>
                                                        <div class="tags-container" data-tag-set="3">
                                                            <div class="question-tag">Why was your average rating experience?</div>
                                                        </div>
                                                        <div class="tags-container" data-tag-set="4">
                                                            <div class="question-tag">Why was your experience good?</div>
                                                        </div>
                                                        <div class="tags-container" data-tag-set="5">
                                                            <div class="make-compliment">
                                                                <div class="compliment-container">
                                                                    <span class="compliment-text">Give a compliment</span>
                                                                    <i class="fas fa-smile-wink"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-lg-6 col-md-6 col-12 mb-3">
                                                        <div class="premium-input-group">
                                                            <label>Name <span class="text-danger">*</span></label>
                                                            <input id="name" type="text" name="name" required placeholder="Your Name" value="{{ auth()->user()->name ?? '' }}"/>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-6 col-md-6 col-12 mb-3">
                                                        <div class="premium-input-group">
                                                            <label>Image (optional)</label>
                                                            <input type="file" class="form-control" name="image">
                                                        </div>
                                                    </div>

                                                    <div class="col-12 mb-4">
                                                        <div class="premium-input-group">
                                                            <label>Other Notes (optional)</label>
                                                            <textarea name="message" rows="3" placeholder="Write your feedback here..."></textarea>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-12">
                                                        <div class="button-box form-submit">
                                                            <button type="submit" class="btn submit-review-btn w-100 py-3 fw-bold" style="border-radius:12px;">
                                                                {{ $dt->submit_review_btn_text ?? 'Submit Review' }} <i class="fas fa-paper-plane ms-2"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                            @else
                                                <div class="review-login-box">
                                                    <i class="far fa-user-circle"></i>
                                                    <p class="review-login-title">Log in to write a review</p>
                                                    <p class="review-login-text">Only customers with an account can review a product. It takes a moment, and you will come straight back here.</p>
                                                    <a href="{{ route('front.reviews.login', ['product' => $singleProduct->slug ?: $singleProduct->id]) }}" class="btn submit-review-btn px-4 fw-bold" style="border-radius:12px;">
                                                        <i class="fas fa-sign-in-alt me-2"></i> Log In
                                                    </a>
                                                </div>
                                            @endauth
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
              </div>
            </div>
          </div>
        </div>

    </div>

    <style>.row>[class*=col]{ padding-left:8px; padding-right:8px; }</style>

    <div class="axil-product-area bg-color-white pt--20 pb--40">
        <div class="container">
            <div class="section-title-wrapper mb-4">
                <span class="pd-section-kicker">You may also like</span>
                <h2>Related Products</h2>
            </div>
            <div class="explore-product-activation slick-layout-wrapper slick-layout-wrapper--15 axil-slick-arrow arrow-top-slide">
                <div class="slick-single-layout" id="relative_data">
                    <div class="row row--15">
                        @foreach($products as $product)
                        <div class="col-lg-2 col-md-3 col-6 mb--30">
                            @include('frontend.products.partials.product_section')
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>
{{-- ===== Sticky buy bar (small screens only) — mirrors the price and buttons above ===== --}}
<div class="pd-buy-bar {{ $inStock ? '' : 'is-out' }}" id="pdBuyBar">
    <div class="pd-buy-bar__price">
        <span class="pd-buy-bar__now" id="pdBarPrice">{{ biz_format_currency($initFinal) }}</span>
        <del class="pd-buy-bar__old" id="pdBarOld" style="{{ $initSavePct > 0 ? '' : 'display:none;' }}">{{ $initSavePct > 0 ? biz_format_currency($initRaw) : '' }}</del>
    </div>
    <div class="pd-buy-bar__actions">
        <button type="button" class="pd-buy-bar__btn is-cart" id="pdBarCart" aria-label="Add to Cart">
            <i class="fas fa-shopping-cart"></i> <span>Add to Cart</span>
        </button>
        <button type="button" class="pd-buy-bar__btn is-buy" id="pdBarBuy"><i class="fas fa-shopping-bag"></i> Buy Now</button>
    </div>
    <button type="button" class="pd-buy-bar__btn is-notify" id="pdBarNotify">
        <i class="far fa-bell"></i> Notify Me When Available
    </button>
</div>

{{-- ===== Notify Me When Available modal ===== --}}
<div class="notify-modal" id="notifyModal" aria-hidden="true">
    <div class="notify-modal__backdrop" data-notify-close></div>
    <div class="notify-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="notifyModalTitle">
        <div class="notify-modal__head">
            <h5 id="notifyModalTitle"><i class="far fa-bell"></i> Notify Me When Available</h5>
            <button type="button" class="notify-modal__close" data-notify-close aria-label="Close">&times;</button>
        </div>

        <form id="notifyForm" novalidate>
            <p class="notify-modal__text">
                This product is currently out of stock. Enter your mobile number and we'll notify you via SMS when it's available again.
            </p>
            <p class="notify-modal__variant" id="notifyVariant" style="display:none;"></p>

            <div class="notify-phone">
                <span class="notify-phone__cc">🇧🇩 +880</span>
                <input type="tel" name="phone" id="notifyPhone" inputmode="numeric" autocomplete="tel"
                       maxlength="14" placeholder="01712 345678" required>
            </div>
            <div class="notify-error" id="notifyError"></div>

            <button type="submit" class="notify-submit" id="notifySubmit">Notify Me</button>

            <label class="notify-consent">
                <input type="checkbox" name="consent" id="notifyConsent" value="1" checked>
                <span>I agree to receive stock update notifications via SMS.</span>
            </label>
        </form>

        <div class="notify-success" id="notifySuccess" style="display:none;">
            <i class="fas fa-check-circle"></i>
            <span id="notifySuccessText">You will be notified when this product is back in stock!</span>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
window.__PRODUCT_OUT__ = @json($productOut);

// ===== Notify Me When Available =====
(function(){
  const modal   = document.getElementById('notifyModal');
  if(!modal) return;
  const form    = document.getElementById('notifyForm');
  const phone   = document.getElementById('notifyPhone');
  const consent = document.getElementById('notifyConsent');
  const errBox  = document.getElementById('notifyError');
  const submit  = document.getElementById('notifySubmit');
  const success = document.getElementById('notifySuccess');
  const variant = document.getElementById('notifyVariant');

  window.toggleNotifyMe = function(show){
    const wrap = document.getElementById('notifyMeWrap');
    if(wrap) wrap.style.display = show ? '' : 'none';
  };

  function open(){
    const v = window.__ACTIVE_VAR__;
    const label = v && [v.size, v.color].filter(x => x && x !== 'Default').join(' / ');
    variant.textContent = label ? ('Variant: ' + label) : '';
    variant.style.display = label ? '' : 'none';
    form.style.display = '';
    success.style.display = 'none';
    errBox.textContent = '';
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    setTimeout(() => phone.focus(), 50);
  }
  function close(){
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  document.addEventListener('click', function(e){
    if(e.target.closest('#notifyMeOpen')){ e.preventDefault(); open(); }
    if(e.target.closest('[data-notify-close]')) close();
  });
  document.addEventListener('keydown', e => { if(e.key === 'Escape' && modal.classList.contains('is-open')) close(); });
  phone.addEventListener('input', () => { phone.value = phone.value.replace(/[^\d+\s-]/g, ''); errBox.textContent = ''; errBox.classList.remove('is-info'); });

  form.addEventListener('submit', function(e){
    e.preventDefault();
    errBox.textContent = '';
    errBox.classList.remove('is-info');

    const digits = phone.value.replace(/\D/g, '').replace(/^880/, '0').replace(/^00/, '0');
    if(!/^01[3-9]\d{8}$/.test(digits)){ errBox.textContent = 'Please enter a valid mobile number (e.g. 01712345678).'; return; }
    if(!consent.checked){ errBox.textContent = 'Please agree to receive SMS notifications.'; return; }

    const v = window.__ACTIVE_VAR__;
    submit.disabled = true;
    submit.textContent = 'Please wait...';

    fetch(@json(route('front.stock_notify.store')), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
      body: JSON.stringify({
        product_id: @json($singleProduct->id),
        variation_id: v && v.id ? v.id : null,
        phone: digits,
        consent: consent.checked ? 1 : 0,
      }),
    })
    .then(r => r.json().then(d => ({ ok: r.ok, d })))
    .then(({ ok, d }) => {
      if(ok && d.status){
        document.getElementById('notifySuccessText').textContent = d.message;
        form.style.display = 'none';
        success.style.display = '';
        phone.value = '';
      } else if(d.already){
        errBox.classList.add('is-info');
        errBox.textContent = d.message;
      } else {
        const first = d.errors ? Object.values(d.errors)[0][0] : null;
        errBox.textContent = first || d.message || 'Something went wrong. Please try again.';
      }
    })
    .catch(() => { errBox.textContent = 'Something went wrong. Please try again.'; })
    .finally(() => { submit.disabled = false; submit.textContent = 'Notify Me'; });
  });
})();
(function(){

  let isFirstLoad = true;

  window.__PIXEL_GUARD__ = window.__PIXEL_GUARD__ || {
    viewContent: {}, 
    addToCart: {}   
  };

  if(window.toastr){
      toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "showDuration": "200",
        "hideDuration": "300",
        "timeOut": "2000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
      };
  }

  function toastUnique(type, msg){
    if(!window.toastr) return;
    toastr.clear();
    toastr[type](msg);
  }

  $(function(){
    setTimeout(function(){
      let product_id = {{ $singleProduct->id }};
      let product_name = {!! json_encode($singleProduct->name) !!};
      let categoryName = {!! json_encode($singleProduct->category->name ?? '') !!};
      let sell_price = {{ (isset($singleProduct->after_discount) && $singleProduct->after_discount > 0) ? $singleProduct->after_discount : $singleProduct->sell_price }};

      const vcKey = "p_" + product_id;
      if (window.__PIXEL_GUARD__.viewContent[vcKey]) return;
      window.__PIXEL_GUARD__.viewContent[vcKey] = true;

      // Same id the controller already sent to the Conversions API — without this
      // the browser and server ViewContent could never be deduplicated by Meta.
      const eventID   = {!! json_encode($eventId ?? null) !!} || ("VC_" + product_id + "_" + Date.now());
      const ttEventID = {!! json_encode($ttEventId ?? null) !!} || (eventID + "_TT");

      window.dataLayer = window.dataLayer || [];
      dataLayer.push({ ecommerce: null });
      dataLayer.push({
          event: "view_item",
          event_id: eventID,
          ecommerce: {
              currency: "BDT",
              value: sell_price,
              items: [{
                  item_id: product_id.toString(),
                  item_name: product_name,
                  item_category: categoryName,
                  price: sell_price,
                  quantity: 1
              }]
          }
      });

      if (@json(!$__hasGtm) && typeof fbq === 'function') {
        fbq('track', 'ViewContent', {
          content_ids: [product_id],
          content_name: product_name,
          content_type: "product",
          value: sell_price,
          currency: "BDT",
          contents: [{ id: product_id, quantity: 1, item_price: sell_price }],
          content_category: categoryName
        }, { eventID: eventID });
      }

      if (@json(!$__ttFromGtm) && typeof ttq !== 'undefined' && ttq.track) {
        ttq.track('ViewContent', {
          content_type: 'product',
          content_id: product_id.toString(),
          content_name: product_name,
          content_category: categoryName,
          value: sell_price,
          currency: 'BDT',
          contents: [{ content_id: product_id.toString(), content_type: 'product', content_name: product_name, price: sell_price, quantity: 1 }]
        }, { event_id: ttEventID });
      }
    }, 500);
  });

  $(document).ready(function() {
      const descWrapper = $('#descWrapper');
      const viewMoreBtn = $('#viewMoreBtn');
      
      if(descWrapper.length && viewMoreBtn.length) {
          
          function toggleViewMore() {
              if (descWrapper[0].scrollHeight > 260) {
                  viewMoreBtn.css('display', 'block');
              } else {
                  viewMoreBtn.css('display', 'none');
              }
          }

          toggleViewMore();
          $(window).on('load', toggleViewMore);
          
          let attempts = 0;
          let interval = setInterval(function() {
              toggleViewMore();
              attempts++;
              if (attempts >= 8) clearInterval(interval);
          }, 500);

          $('.nav-link').on('shown.bs.tab', toggleViewMore);

          $(document).off('click', '#viewMoreBtn').on('click', '#viewMoreBtn', function(e) {
              e.preventDefault();
              if(descWrapper.hasClass('expanded')) {
                  descWrapper.removeClass('expanded');
                  descWrapper.css('max-height', '250px');
                  $(this).html('View More <i class="fas fa-chevron-down ms-1"></i>');
              } else {
                  descWrapper.addClass('expanded');
                  let fullHeight = descWrapper[0].scrollHeight + 150;
                  descWrapper.css('max-height', fullHeight + 'px');
                  $(this).html('View Less <i class="fas fa-chevron-up ms-1"></i>');
              }
          });
      }
  });

  document.addEventListener('DOMContentLoaded', function() {
    
    // Open the Reviews tab and bring the review box (form, or the log-in prompt) into view.
    // The box is inside the tab, so it has no position until the tab has finished
    // opening — scrolling on a fixed delay used to fire too early and do nothing.
    function goToReviewBox(){
      const reviewTabLink = document.querySelector('#review-tab');
      let done = false;
      function scrollToBox(){
        if (done) return;
        const target = document.querySelector('#reviewFormCard') || document.querySelector('#writeReview');
        if (!target || !target.offsetHeight) return;   // still hidden
        done = true;
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }

      if (reviewTabLink && !reviewTabLink.classList.contains('active')) {
        reviewTabLink.addEventListener('shown.bs.tab', scrollToBox, { once: true });
        if (window.bootstrap && bootstrap.Tab) (new bootstrap.Tab(reviewTabLink)).show();
        else reviewTabLink.click();
      }
      // already open, or a theme without the tab event: keep trying briefly
      let tries = 0;
      (function retry(){ scrollToBox(); if (!done && ++tries < 12) setTimeout(retry, 100); })();
    }

    document.querySelectorAll('a.all-reviews-button').forEach(function(btn){
      btn.addEventListener('click', function(e){
        e.preventDefault();
        goToReviewBox();
      });
    });

    // back from logging in (?review=1), or a link straight to the reviews (#writeReview)
    if (new URLSearchParams(window.location.search).get('review') === '1' || window.location.hash === '#writeReview') {
      goToReviewBox();
    }

    const MIN_QTY = {{ $minOrderQty }};
    const qtyWrap = document.querySelector('.quantity');
    if(qtyWrap && !qtyWrap.dataset.bound){
      qtyWrap.dataset.bound = "1";
      const minus = qtyWrap.querySelector('.minus');
      const plus  = qtyWrap.querySelector('.plus');
      const input = qtyWrap.querySelector('input[name="quantity"]');

      function bumpQty(){
        if(!input) return;
        input.classList.remove('qty-bump');
        void input.offsetWidth;
        input.classList.add('qty-bump');
      }

      if(plus){
        plus.addEventListener('click', (e) => {
          e.preventDefault(); e.stopImmediatePropagation();
          let v = Math.max(MIN_QTY, parseInt(input.value) || MIN_QTY);
          input.value = v + 1;
          bumpQty();
        }, true);
      }
      if(minus){
        minus.addEventListener('click', (e) => {
          e.preventDefault(); e.stopImmediatePropagation();
          let v = parseInt(input.value) || MIN_QTY;
          if(v > MIN_QTY) { input.value = v - 1; bumpQty(); }
          else if(MIN_QTY > 1) { toastUnique('warning', 'Minimum order quantity is ' + MIN_QTY + '.'); }
        }, true);
      }
    }
  });

  $(document)
    .off('submit.ajaxreview', 'form#ajax_form2')
    .on('submit.ajaxreview', 'form#ajax_form2', function(e){
      e.preventDefault();
      const reviewVal = $(this).find('input[name="review"]').val();
      if(!reviewVal){ toastUnique('error', 'The review field is required'); return false; }

      $.ajax({
        type: $(this).attr('method'),
        url: $(this).attr('action'),
        data: new FormData(this),
        processData: false,
        contentType: false,
        success: function (res) {
          if (res.status) {
            toastUnique('success', res.msg || 'Success');
            if (res.view) $('.comment-list').empty().append(res.view);
            if(res.url){ document.location.href = res.url; return; }
            setTimeout(function(){ window.location.reload(); }, 700);
          } else { toastUnique('error', res.msg || 'Failed'); }
        },
        error: function (xhr) {
          const res = xhr.responseJSON || {};
          if (xhr.status === 401 || res.login) {
            toastUnique('warning', res.msg || 'Please log in to write a review.');
            setTimeout(function(){ document.location.href = @json(route('front.reviews.login', ['product' => $singleProduct->slug ?: $singleProduct->id])); }, 900);
            return;
          }
          const first = res.errors ? Object.values(res.errors)[0][0] : null;
          toastUnique('error', first || res.msg || 'Could not submit your review. Please try again.');
        }
      });
      return false;
    });

  $(document)
    .off('click.cartaction', '.add_cart_btn, .order_now_btn')
    .on('click.cartaction', '.add_cart_btn, .order_now_btn', function(){
      if($(this).hasClass('add_cart_btn')) $('#input_action_type').val('cart');
      else if($(this).hasClass('order_now_btn')) $('#input_action_type').val('order');

      const $btn = $(this);
      $btn.addClass('cart-success');
      setTimeout(()=> $btn.removeClass('cart-success'), 500);
    });

  function openCartSidebar(){
      const triggers = ['.cart-dropdown-btn', '.header-action .cart-btn', '.cart-btn', '.header-cart'];
      let opened = false;
      triggers.forEach(sel => { if($(sel).length && !opened) { $(sel).click(); opened = true; } });
      if(!opened) {
          $('body').addClass('cart-open');
          $('#cart-dropdown, #cart_section, .cart-dropdown-wrap').addClass('open show active');
      }
  }

  $(document)
    .off('submit.cart', 'form#cart_submit')
    .on('submit.cart', 'form#cart_submit', function(e){
      e.preventDefault();
      let form = $(this);

      if(!window.__ACTIVE_VAR__){
        toastUnique('warning', 'Product variation not found. Please select another.'); return false;
      }
      
      const stock = parseInt((window.__ACTIVE_VAR__ && window.__ACTIVE_VAR__.stock) ? window.__ACTIVE_VAR__.stock : 0);
      if(stock <= 0 || window.__PRODUCT_OUT__){
        toastUnique('warning', 'This product is out of stock.'); return false;
      }

      let qtyInput = form.find('input[name="quantity"]');
      let currentQty = parseInt(qtyInput.val());
      if(isNaN(currentQty) || currentQty < {{ $minOrderQty }}) { currentQty = {{ $minOrderQty }}; qtyInput.val(currentQty); }

      let product_id    = form.find('input[name="product_id"]').val();
      let product_name = form.find('input[name="product_name"]').val();
      let sell_price    = parseFloat($('#price_val').val() || 0);
      let actionType    = $('#input_action_type').val(); 

      const eventID = "ATC_" + product_id + "_" + Date.now();
      
      if (form.find('input[name="event_id"]').length === 0) {
          form.append('<input type="hidden" name="event_id" value="' + eventID + '">');
      } else {
          form.find('input[name="event_id"]').val(eventID);
      }

      if (!window.__PIXEL_GUARD__.addToCart[eventID]) {
        window.__PIXEL_GUARD__.addToCart[eventID] = true;
        
        window.dataLayer = window.dataLayer || [];
        dataLayer.push({ ecommerce: null });
        dataLayer.push({
            event: "add_to_cart",
            event_id: eventID,
            ecommerce: {
                currency: "BDT",
                value: sell_price * currentQty,
                items: [{
                    item_id: product_id.toString(),
                    item_name: product_name,
                    price: sell_price,
                    quantity: currentQty
                }]
            }
        });

        if (@json(!$__hasGtm) && typeof fbq === 'function') {
          fbq('track', 'AddToCart', {
              content_ids: [product_id], content_name: product_name, content_type: 'product',
              value: sell_price * currentQty, currency: 'BDT', quantity: currentQty,
              contents: [{ id: product_id, quantity: currentQty, item_price: sell_price }]
          }, { eventID: eventID });
        }

        if (@json(!$__ttFromGtm) && typeof ttq !== 'undefined' && ttq.track) {
          ttq.track('AddToCart', {
              content_type: 'product',
              content_id: product_id.toString(),
              content_name: product_name,
              value: sell_price * currentQty,
              currency: 'BDT',
              contents: [{ content_id: product_id.toString(), content_type: 'product', content_name: product_name, price: sell_price, quantity: currentQty }]
          }, { event_id: eventID + "_TT" });
        }
      }

      $.ajax({
        url: form.attr('action'),
        method: form.attr('method'),
        data: form.serialize(),
        success: function (res) {
          if (res.success) {
            toastUnique('success', res.msg || 'Added Successfully');
            if (res.view || res.html) $('#cart_section, #cart-dropdown, .cart-dropdown-wrap').html(res.view || res.html);
            if (typeof res.item !== 'undefined') {
                $('.cart-count').text(res.item); $('.cart-item-count').text(res.item); $('.pro-count').text(res.item); 
            }
            if (res.amount) {
                let amountText = res.amount.toString().includes('৳') || res.amount.toString().includes('$') ? res.amount : '৳ ' + res.amount;
                $('.cart-amount').text(amountText);
            }
            if (actionType === 'order') { document.location.href = res.url ? res.url : "{{ url('/checkouts') }}"; return; }
            openCartSidebar();
          } else { toastUnique('error', res.msg || 'Failed'); }
        },
        // The server answers 422 when it refuses the add (stock, variation...). Without this
        // handler the click looked dead.
        error: function (xhr) {
          const res = xhr.responseJSON || {};
          // "Order Now" on something already in the cart at its stock limit: nothing to add,
          // but the customer still wants to check out — the item is waiting there.
          if (actionType === 'order' && res.in_cart) {
            toastUnique('info', 'This item is already in your cart. Taking you to checkout...');
            document.location.href = "{{ url('/checkouts') }}";
            return;
          }
          toastUnique('error', res.msg || 'Could not add this product. Please try again.');
        }
      });
      return false;
    });

  document.addEventListener('click', function(e){
    const cartSection = document.querySelector('#cart_section');
    if(cartSection && cartSection.contains(e.target)){ e.stopPropagation(); }
  }, true);

  function moneyText(val){
    val = parseFloat(val || 0);
    if (isNaN(val)) val = 0;
    return '{{ $info->currency_symbol ?? "৳" }} ' + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
  // "170 ৳ off": whole amounts without decimals, otherwise two.
  function offText(val){
    val = Math.round(parseFloat(val || 0) * 100) / 100;
    return val.toLocaleString('en-US', { minimumFractionDigits: Number.isInteger(val) ? 0 : 2, maximumFractionDigits: 2 }) + ' {{ $info->currency_symbol ?? "৳" }} off';
  }
  function statusHtml(stock){
    return '<span class="pd-status-label">Status:</span> ' + (stock > 0
      ? '<strong class="pd-status is-in">Stock In</strong> '
        + (stock <= {{ $lowStockLimit }}
            ? '<span class="pd-stock-left is-low"><i class="fas fa-fire"></i> Only <b>' + stock + '</b> left</span>'
            : '<span class="pd-stock-left"><i class="fas fa-box-open"></i> <b>' + stock + '</b> left</span>')
      : '<strong class="pd-status is-out">Stock Out</strong>');
  }

  function resolveVariation(sizeId, colorId){
    const key = String(sizeId) + '|' + String(colorId);
    return (window.__VAR_MAP__ && window.__VAR_MAP__[key]) ? window.__VAR_MAP__[key] : null;
  }

  function flashPrice(){
    const $p = $('.current-price-product');
    $p.removeClass('price-flash');
    void $p[0]?.offsetWidth;
    $p.addClass('price-flash');
    setTimeout(()=> $p.removeClass('price-flash'), 600);
  }

  function applyVariation(v){
    if(!v) return;
    window.__ACTIVE_VAR__ = v;

    const cfg = window.__VAR_DEFAULT__ || {};
    const showSize  = !!cfg.show_size;
    const showColor = !!cfg.show_color;

    let labelParts = [];
    if(showSize && v.size && v.size !== 'Default') labelParts.push(v.size);
    if(showColor && v.color && v.color !== 'Default') labelParts.push(v.color);

    const label = labelParts.join(' - ');
    if(label){ $('span.size_name').text(label).show(); }else{ $('span.size_name').hide(); }
    $('#pdPickedSize').text(v.size && v.size !== 'Default' ? v.size : '');
    $('#pdPickedColor').text(v.color && v.color !== 'Default' ? v.color : '');

    $('.current-price-product').text(moneyText(v.price));
    if(!isFirstLoad) flashPrice();

    if (v.raw > v.price && v.raw > 0) {
      $('#product-old-price').show().text(moneyText(v.raw));
      $('#pdSaveBadge').text(offText(v.raw - v.price)).show();
    } else {
      $('#product-old-price').hide();
      $('#pdSaveBadge').hide();
    }

    if (v.image) {
      $('#main-image, #thumb-image, #video-thumb-image').attr('src', v.image);
      $('#main-image-link').attr('href', v.image);
    } else {
      let defaultImg = "{{ getImage('products', $singleProduct->image) }}";
      $('#main-image, #thumb-image, #video-thumb-image').attr('src', defaultImg);
      $('#main-image-link').attr('href', defaultImg);
    }

    // ✅ Only force slider back when variation has its own image
    if (!isFirstLoad && v.image) {
        let sliderClass = $('.product-large-thumbnail-3');
        if(sliderClass.hasClass('slick-initialized')) {
            let targetIndex = $('.video-slide').length > 0 ? 1 : 0;
            sliderClass.slick('slickGoTo', targetIndex);
        }
    }

    $('#variation_id').val(v.id);
    $('#variant_name').val(label);
    $('#size_value').val(label);
    if($('#size_value1').length) $('#size_value1').val(label);
    $('#price_val').val(v.price);
    if($('#price_val1').length) $('#price_val1').val(v.price);

    const stock = parseInt(v.stock || 0);
    const stockEl = document.querySelector('#stock-text-element');
    if(stockEl){
      stockEl.innerHTML = statusHtml(window.__PRODUCT_OUT__ ? 0 : stock);
    }

    setStockState(stock <= 0 || window.__PRODUCT_OUT__);
    if(window.toggleNotifyMe) window.toggleNotifyMe(stock <= 0 || window.__PRODUCT_OUT__);
    markOutOfStockOptions();
  }

  // Selected variant out of stock → same look as a stock-out product:
  // image badge, warning, "Out of Stock" button, everything disabled.
  function setStockState(isOut){
    $('.add_cart_btn, .order_now_btn').prop('disabled', isOut);
    $('#pdStockOutOverlay, #pdThumbStockOut').toggle(isOut);
    $('#pdStockOutAlert').toggle(isOut);
    $('#pdStockOutAlertText').text(window.__PRODUCT_OUT__
      ? 'This product is currently out of stock.'
      : 'This variant is currently out of stock. Please choose another option.');
    $('.quantity').toggleClass('is-disabled', isOut);
    if(isOut) $('.quantity input[name="quantity"]').val({{ $minOrderQty }});

    const $order = $('.order_now_btn');
    if(isOut){
      $order.text('Out of Stock');
    }else{
      const tpl = document.getElementById('orderNowLabel');
      if(tpl) $order.html(tpl.innerHTML);
    }
  }

  // Tag each size/colour chip whose combination with the current other choice has no stock.
  function stockOf(sizeId, colorId){
    const v = resolveVariation(sizeId, colorId);
    return v ? parseInt(v.stock || 0) : null;
  }
  function markOutOfStockOptions(){
    const hasSize  = $('#sizeOptions').length > 0;
    const hasColor = $('#colorOptions').length > 0;
    const activeSize  = hasSize  ? parseInt($('#sizeOptions .size-opt.active').data('size-id') || 0) : 0;
    const activeColor = hasColor ? parseInt($('#colorOptions .color-opt.active').data('color-id') || 0) : 0;

    $('#sizeOptions .size-opt').each(function(){
      const st = stockOf(parseInt($(this).data('size-id') || 0), activeColor);
      $(this).toggleClass('is-out', st !== null && st <= 0)
             .attr('title', st !== null && st <= 0 ? 'Out of stock' : null);
    });
    $('#colorOptions .color-opt').each(function(){
      const st = stockOf(activeSize, parseInt($(this).data('color-id') || 0));
      $(this).toggleClass('is-out', st !== null && st <= 0)
             .attr('title', st !== null && st <= 0 ? 'Out of stock' : null);
    });
  }

  function setInvalidState(){
    window.__ACTIVE_VAR__ = null;
    $('span.size_name').text('(Not available)').show();
    
    const stockEl = document.querySelector('#stock-text-element');
    if(stockEl){
      stockEl.innerHTML = statusHtml(0);
    }
    
    $('.add_cart_btn, .order_now_btn').prop('disabled', true);
    if(window.toggleNotifyMe) window.toggleNotifyMe(!!window.__PRODUCT_OUT__);
    markOutOfStockOptions();
  }

  $(document)
    .off('click.sizepick', '#sizeOptions .size-opt')
    .on('click.sizepick', '#sizeOptions .size-opt', function(){
      $('#sizeOptions .size-opt').removeClass('active');
      $(this).addClass('active');

      const sizeId  = parseInt($(this).data('size-id') || 0);
      
      if ($('#colorOptions').length) {
          let hasValidColor = false;
          let firstValidColor = null;

          $('#colorOptions .color-opt').each(function(){
              let cid = parseInt($(this).data('color-id') || 0);
              let checkKey = sizeId + '|' + cid;

              if (window.__VAR_MAP__[checkKey]) {
                  $(this).show(); 
                  if (firstValidColor === null) firstValidColor = $(this);
                  if ($(this).hasClass('active')) hasValidColor = true;
              } else {
                  $(this).hide().removeClass('active'); 
              }
          });

          if (!hasValidColor && firstValidColor) {
              firstValidColor.addClass('active');
          }
      }

      const colorId = $('#colorOptions').length ? parseInt($('#colorOptions .color-opt.active').data('color-id') || 0) : 0;
      const v = resolveVariation(sizeId, colorId);
      if(v) applyVariation(v); else setInvalidState();
    });

  $(document)
    .off('click.colorpick', '#colorOptions .color-opt')
    .on('click.colorpick', '#colorOptions .color-opt', function(){
      $('#colorOptions .color-opt').removeClass('active');
      $(this).addClass('active');

      const colorId = parseInt($(this).data('color-id') || 0);
      
      if ($('#sizeOptions').length) {
          let hasValidSize = false;
         let firstValidSize = null;

          $('#sizeOptions .size-opt').each(function(){
              let sid = parseInt($(this).data('size-id') || 0);
              let checkKey = sid + '|' + colorId;

              if (window.__VAR_MAP__[checkKey]) {
                  $(this).show();
                  if (firstValidSize === null) firstValidSize = $(this);
                  if ($(this).hasClass('active')) hasValidSize = true;
              } else {
                  $(this).hide().removeClass('active');
              }
          });

          if (!hasValidSize && firstValidSize) {
              firstValidSize.addClass('active');
          }
      }

      const sizeId  = $('#sizeOptions').length ? parseInt($('#sizeOptions .size-opt.active').data('size-id') || 0) : 0;
      const v = resolveVariation(sizeId, colorId);
      if(v) applyVariation(v); else setInvalidState();
    });

  $(function(){
    const cfg = window.__VAR_DEFAULT__ || {};

    if(cfg.show_size && $('#sizeOptions .size-opt.active').length){
        $('#sizeOptions .size-opt.active').trigger('click.sizepick');
    } else if (cfg.show_color && $('#colorOptions .color-opt.active').length) {
        $('#colorOptions .color-opt.active').trigger('click.colorpick');
    } else {
        const v = (function(){
          const keys = Object.keys(window.__VAR_MAP__ || {});
          return keys.length ? window.__VAR_MAP__[keys[0]] : null;
        })();
        if(v) applyVariation(v); else setInvalidState();
    }

    setTimeout(function() {
        isFirstLoad = false;
    }, 500);
  });

  // ✅ CUSTOM GALLERY: Replace slick on small thumb with custom code
  // Solves: Desktop hide bug + Mobile last-to-first double click
  $(document).ready(function() {
      let attempt = 0;
      let waitInterval = setInterval(function() {
          attempt++;
          if (attempt > 30) { clearInterval(waitInterval); return; }

          let $largeSlider = $('.product-large-thumbnail-3');
          let $smallThumbs = $('.product-small-thumb-3');

          if (!$largeSlider.length || !$smallThumbs.length) return;
          if (!$largeSlider.hasClass('slick-initialized')) return;

          clearInterval(waitInterval);

          // Destroy slick on small thumbs (if initialized)
          if ($smallThumbs.hasClass('slick-initialized')) {
              try { $smallThumbs.slick('unslick'); } catch(e) {}
          }

          // Add custom class for our styles
          $smallThumbs.addClass('custom-gallery-mode');

          // Set initial active thumb
          let initialIdx = $largeSlider.slick('slickCurrentSlide') || 0;
          $smallThumbs.find('.small-thumb-img').removeClass('active-thumb slick-current').eq(initialIdx).addClass('active-thumb slick-current');

          // Sync thumb active state with main slider
          $largeSlider.off('beforeChange.thumbsync').on('beforeChange.thumbsync', function(e, slick, currentSlide, nextSlide) {
              $smallThumbs.find('.small-thumb-img').removeClass('active-thumb slick-current').eq(nextSlide).addClass('active-thumb slick-current');

              // Auto-scroll thumb container to keep active thumb visible
              let $activeThumb = $smallThumbs.find('.small-thumb-img').eq(nextSlide);
              if ($activeThumb.length) {
                  let thumbPos = $activeThumb.position();
                  if (thumbPos) {
                      if (window.innerWidth >= 992) {
                          // Desktop: vertical scroll
                          let scrollTop = $smallThumbs.scrollTop() + thumbPos.top - 100;
                          $smallThumbs.stop().animate({ scrollTop: scrollTop }, 300);
                      } else {
                          // Mobile: horizontal scroll
                          let scrollLeft = $smallThumbs.scrollLeft() + thumbPos.left - 80;
                          $smallThumbs.stop().animate({ scrollLeft: scrollLeft }, 300);
                      }
                  }
              }
          });

          // Custom click handler on small thumbs — ALWAYS instant jump
          // Solves: double-click issue when main slider is mid-animation
          let isThumbClicking = false;
          $(document).off('click.customthumb touchend.customthumb').on('click.customthumb touchend.customthumb', '.small-thumb-wrapper .small-thumb-img', function(e) {
              e.preventDefault();
              e.stopPropagation();

              // Debounce rapid double-fires (touch + click events)
              if (isThumbClicking) return;
              isThumbClicking = true;
              setTimeout(function() { isThumbClicking = false; }, 250);

              let $thumbs = $('.small-thumb-wrapper .small-thumb-img');
              let idx = $thumbs.index(this);
              if (idx < 0) return;

              let currentIdx = $largeSlider.slick('slickCurrentSlide');
              if (currentIdx === idx) return; // already on this slide

              // Update active class immediately for instant visual feedback
              $thumbs.removeClass('active-thumb slick-current');
              $(this).addClass('active-thumb slick-current');

              // ALWAYS instant jump — no animation queue, no double-click needed
              $largeSlider.slick('slickGoTo', idx, true);
          });

      }, 150);
  });

  $(".rating-component .star").off('.rate')
    .on("mouseover.rate", function () {
      var onStar = parseInt($(this).data("value"), 10);
      $(this).parent().children("i.star").each(function (e) {
        if (e < onStar) $(this).addClass("hover");
        else $(this).removeClass("hover");
      });
    })
    .on("mouseout.rate", function () {
      $(this).parent().children("i.star").removeClass("hover");
    });

  $(".rating-component .stars-box .star").off('click.rate').on("click.rate", function () {
    var onStar = parseInt($(this).data("value"), 10);
    var stars  = $(this).parent().children("i.star");
    var ratingMessage = $(this).data("message");

    $("input[name='review']").val(onStar);
    $('.rating-component .starrate .ratevalue').val(onStar);
    $(".status-msg .rating_msg").val(ratingMessage);

    for (let i = 0; i < stars.length; i++) $(stars[i]).removeClass("selected");
    for (let i = 0; i < onStar; i++) $(stars[i]).addClass("selected");

    $("[data-tag-set]").css('display', 'none');
    $("[data-tag-set=" + onStar + "]").css('display', 'block');
  });

})();

// Hover zoom on the main product image, marketplace style: a lens follows the
// cursor on the image and the enlarged area shows in a pane beside it (over the
// details column). Mouse devices at desktop width only — touch keeps swipe + tap-to-open.
(function(){
  if(!window.matchMedia || !window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;
  const gallery = document.querySelector('.product-large-thumbnail-3');
  const wrap = document.querySelector('.single-product-thumbnail-wrap');
  if(!gallery || !wrap) return;

  const ZOOM = 2.5, GAP = 18;
  const lens = document.createElement('div'); lens.className = 'pd-zoom-lens';
  const pane = document.createElement('div'); pane.className = 'pd-zoom-pane';
  wrap.appendChild(lens);
  document.body.appendChild(pane);

  function hide(){
    lens.style.display = 'none';
    pane.style.display = 'none';
    wrap.classList.remove('pd-zoom-on');
  }

  // Where the picture itself sits inside the <img> box (object-fit may letterbox or crop it).
  function contentRect(img, box){
    const nw = img.naturalWidth, nh = img.naturalHeight;
    const fit = getComputedStyle(img).objectFit;
    if(!nw || !nh || (fit !== 'contain' && fit !== 'cover')) return { x: 0, y: 0, w: box.width, h: box.height };
    const scale = fit === 'contain' ? Math.min(box.width / nw, box.height / nh) : Math.max(box.width / nw, box.height / nh);
    const w = nw * scale, h = nh * scale;
    return { x: (box.width - w) / 2, y: (box.height - h) / 2, w: w, h: h };
  }

  gallery.addEventListener('mousemove', function(e){
    const slide = e.target.closest ? e.target.closest('.thumbnail') : null;
    const img = slide && !slide.classList.contains('video-slide') ? slide.querySelector('img') : null;
    // no zoom while dragging the slider, on the video, or when there is no room beside the image
    if(!img || e.buttons || window.innerWidth < 992){ hide(); return; }

    const box  = img.getBoundingClientRect();
    const wbox = wrap.getBoundingClientRect();
    const paneW = Math.min(box.width, window.innerWidth - wbox.right - GAP - 16);
    const paneH = Math.min(box.height, window.innerHeight - 16);
    if(paneW < 260 || !box.width || !box.height){ hide(); return; }

    const lensW = paneW / ZOOM, lensH = paneH / ZOOM;
    const lx = Math.min(box.width  - lensW, Math.max(0, e.clientX - box.left - lensW / 2));
    const ly = Math.min(box.height - lensH, Math.max(0, e.clientY - box.top  - lensH / 2));

    lens.style.width  = lensW + 'px';
    lens.style.height = lensH + 'px';
    lens.style.left   = (box.left - wbox.left - wrap.clientLeft + lx) + 'px';
    lens.style.top    = (box.top  - wbox.top  - wrap.clientTop  + ly) + 'px';
    lens.style.display = 'block';

    const c = contentRect(img, box);
    const src = img.currentSrc || img.src;
    if(pane.dataset.src !== src){ pane.dataset.src = src; pane.style.backgroundImage = 'url("' + src.replace(/"/g, '%22') + '")'; }
    pane.style.width  = paneW + 'px';
    pane.style.height = paneH + 'px';
    pane.style.left   = (wbox.right + GAP) + 'px';
    pane.style.top    = Math.max(8, Math.min(wbox.top, window.innerHeight - paneH - 8)) + 'px';
    pane.style.backgroundSize = (c.w * ZOOM) + 'px ' + (c.h * ZOOM) + 'px';
    pane.style.backgroundPosition = ((c.x - lx) * ZOOM) + 'px ' + ((c.y - ly) * ZOOM) + 'px';
    pane.style.display = 'block';
    wrap.classList.add('pd-zoom-on');
  });
  gallery.addEventListener('mouseleave', hide);
  gallery.addEventListener('mousedown', hide);
  window.addEventListener('scroll', hide, { passive: true });
})();

// Share row: "copy link" button (falls back to a hidden textarea on older browsers / http).
(function(){
  const btn = document.getElementById('pdShareCopy');
  if(!btn) return;
  function done(ok){
    if(window.toastr) ok ? toastr.success('Product link copied!') : toastr.error('Could not copy the link');
    if(!ok) return;
    btn.classList.add('is-done');
    btn.innerHTML = '<i class="fas fa-check"></i>';
    setTimeout(function(){ btn.classList.remove('is-done'); btn.innerHTML = '<i class="fas fa-link"></i>'; }, 1800);
  }
  btn.addEventListener('click', function(){
    const url = btn.dataset.url || window.location.href;
    if(navigator.clipboard && window.isSecureContext){
      navigator.clipboard.writeText(url).then(function(){ done(true); }, function(){ done(false); });
      return;
    }
    const ta = document.createElement('textarea');
    ta.value = url; ta.setAttribute('readonly', ''); ta.style.cssText = 'position:fixed;top:-1000px;opacity:0;';
    document.body.appendChild(ta); ta.select();
    let ok = false;
    try { ok = document.execCommand('copy'); } catch (e) {}
    document.body.removeChild(ta);
    done(ok);
  });
})();

// Sticky buy bar (small screens). It owns no cart logic: its buttons press the real
// ones in the form, and it copies price / stock state from them whenever they change,
// so variant switching, stock-out and the notify modal all behave exactly the same.
(function(){
  const bar = document.getElementById('pdBuyBar');
  if(!bar) return;
  const realCart   = document.querySelector('.single_product .add_cart_btn');
  const realOrder  = document.querySelector('.single_product .order_now_btn');
  const notifyWrap = document.getElementById('notifyMeWrap');
  const priceBox   = document.querySelector('.details_right .details-price');
  const barCart = document.getElementById('pdBarCart'), barBuy = document.getElementById('pdBarBuy');

  function sync(){
    const now = priceBox && priceBox.querySelector('.current-price-product');
    const old = document.getElementById('product-old-price');
    if(now) document.getElementById('pdBarPrice').textContent = now.textContent.trim();
    const barOld = document.getElementById('pdBarOld');
    const hasOld = old && old.style.display !== 'none' && old.textContent.trim() !== '';
    barOld.textContent = hasOld ? old.textContent.trim() : '';
    barOld.style.display = hasOld ? '' : 'none';

    bar.classList.toggle('is-out', !!notifyWrap && notifyWrap.style.display !== 'none');
    if(realCart)  barCart.disabled = realCart.disabled;
    if(realOrder) barBuy.disabled  = realOrder.disabled;
  }

  barCart.addEventListener('click', function(){ if(realCart && !realCart.disabled) realCart.click(); });
  barBuy.addEventListener('click',  function(){ if(realOrder && !realOrder.disabled) realOrder.click(); });
  document.getElementById('pdBarNotify').addEventListener('click', function(){
    const open = document.getElementById('notifyMeOpen');
    if(open) open.click();
  });

  if(window.MutationObserver){
    const mo = new MutationObserver(sync);
    if(priceBox)   mo.observe(priceBox,   { subtree: true, childList: true, characterData: true, attributes: true, attributeFilter: ['style'] });
    if(notifyWrap) mo.observe(notifyWrap, { attributes: true, attributeFilter: ['style'] });
    if(realCart)   mo.observe(realCart,   { attributes: true, attributeFilter: ['disabled'] });
    if(realOrder)  mo.observe(realOrder,  { attributes: true, attributeFilter: ['disabled'] });
  }
  document.addEventListener('DOMContentLoaded', sync);
  window.addEventListener('load', sync);
  sync();
})();
</script>
@endpush