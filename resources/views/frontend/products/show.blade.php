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
            $stock = resolveStock($singleProduct, $v);

            $varImage = $v->image ? asset('products/'.$v->image) : null;

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
    $initialStock = $initialVar ? (int)($initialVar['stock'] ?? 0) : (int)($singleProduct->stock_quantity ?? 0);
    if ($initialStock <= 0) {
        $initialStock = (int)($singleProduct->stock_quantity ?? 0);
    }
    $inStock = ($initialStock > 0);

    // Whole product unavailable — same rule the cart and product cards use
    $productOut = !productIsOrderable($singleProduct);
    if ($productOut) $inStock = false;
    // --------------------------------

    $curr = $info->currency;

    $initFinal = (float)($initialVar['price'] ?? ($singleProduct->after_discount > 0 ? $singleProduct->after_discount : $singleProduct->sell_price));
    $initRaw   = (float)($initialVar['raw']   ?? ($singleProduct->sell_price ?? 0));

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
        transform: translateX(4px);
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
</style>
@endpush

@section('content')
<main class="main-wrapper">
    <div class="axil-single-product-area p pb--0 bg-color-white">
        <div class="single-product-thumb mb--5">
            <div class="container mt-4 mobile_show">
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
                                                {{$discountPercent}} % Off
                                            </div>
                                        </div>
                                    @endif

                                    @if($productOut)
                                        <div class="pd-stock-out-overlay"><span>Out of Stock</span></div>
                                    @endif

                                    <div class="product-quick-view position-view">
                                        <a href="{{ getImage('products', $singleProduct->image)}}" class="popup-zoom">
                                            <i class="far fa-search-plus"></i>
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
                                    <p class="name">{{ $singleProduct->name}}</p>

                                    <p class="details-price">
                                        @if($initRaw > $initFinal && $initRaw > 0)
                                          <del id="product-old-price" class="price old-price">
                                              {{ biz_format_currency($initRaw) }}
                                          </del>
                                        @else
                                          <del id="product-old-price" class="price old-price" style="display:none;"></del>
                                        @endif

                                        <span class="current-price-product">{{ biz_format_currency($initFinal) }}</span>
                                    </p>

                                    @if($productOut)
                                        <div class="pd-stock-out-alert">
                                            <i class="fas fa-exclamation-circle"></i> This product is currently out of stock.
                                        </div>
                                    @endif

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

                                        <div class="meta-row">
                                            <div class="product-code">
                                                <p><span>{{ $dt->product_code_text ?? 'Product Code :' }} </span>{{ $singleProduct->sku }}</p>
                                            </div>
                                            <div class="product-stock-box">
                                                <p id="stock-text-element">
                                                    @if($inStock)
                                                        <i class="fas fa-check-circle text-success"></i> <span>{{ (int)$initialStock }} Items left</span>
                                                    @else
                                                        <i class="fas fa-times-circle text-danger"></i> <span>0 Items left</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>

                                        <div class="meta-row" style="margin-top: 10px;">
                                            
                                            <div class="qty-cart m-0" style="margin-top: 0; width: auto;">
                                                <div class="quantity" style="margin: 0;">
                                                    <span class="minus">-</span>
                                                    <input type="number" name="quantity" value="1" min="1" readonly>
                                                    <span class="plus">+</span>
                                                </div>
                                            </div>

                                            <div class="details-ratting-wrapper m-0">
                                                @php
                                                    $totalReviews = $singleProduct->reviews->count();
                                                    $averageRating = $totalReviews > 0 ? round($singleProduct->reviews->avg('review'), 2) : 0;
                                                @endphp
                                                <span>{{ $totalReviews }} Reviews</span>
                                                <span style="font-weight: 800; color: #fbbf24;">{{ number_format($averageRating, 2) }}/5</span>
                                                <a class="all-reviews-button" href="#writeReview">See Reviews</a>
                                            </div>
                                        </div>

                                        @if(!empty($singleProduct->short_description))
                                            <div class="premium-short-description">
                                                {!! $singleProduct->short_description !!}
                                            </div>
                                        @endif

                                        @if(isset($singleProduct->variations) && $singleProduct->variations->count() > 0 && ($showSize || $showColor))
                                          <div class="mt-3 d-flex flex-wrap gap-4" id="variantBox">

                                            @if($showSize)
                                              <div class="variant-group">
                                                  <label class="mb-2">Size:</label>
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
                                                  <label class="mb-2">Color:</label>
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

                                        <div class="mt-4">
                                            <div class="d-flex single_product col-sm-12">
                                                @if(!empty($info->whats_num) && ($info->whats_active ?? 0) == 1)
                                                    <a href="https://wa.me/+88{{ $info->whats_num }}?text={{ urlencode($singleProduct->name.' - এই পণ্যটি সম্পর্কে জানতে চাই।') }}"
                                                       target="_blank" class="btn wa_now_btn" title="WhatsApp">
                                                        <i class="fab fa-whatsapp"></i>
                                                    </a>
                                                @endif
                                                @if(!empty($info->owner_phone))
                                                    <a href="tel:{{ $info->owner_phone }}" class="btn call_now_btn" title="Call Now">
                                                        <i class="fas fa-phone-alt"></i>
                                                    </a>
                                                @endif
                                                <button type="submit"
                                                        class="btn add_cart_btn"
                                                        {{ $inStock ? '' : 'disabled' }} title="Add to Cart">
                                                    <i class="fas fa-shopping-cart"></i>
                                                </button>

                                                <button type="submit"
                                                        class="btn px-4 order_now_btn order_now_btn_m"
                                                        {{ $inStock ? '' : 'disabled' }}>
                                                    @if($productOut)
                                                        Out of Stock
                                                    @elseif(($singleProduct->is_free_shipping ?? 0) == 1)
                                                        <i class="fas fa-shipping-fast"></i> &nbsp; {{ $bangla_text->fshipping_text ?? 'Free Shipping' }}
                                                    @else
                                                        {{ $dt->order_now_text ?? 'Order Now' }}
                                                    @endif
                                                </button>
                                            </div>
                                        </div>

                                        <div class="notify-me-wrap" id="notifyMeWrap" style="{{ $inStock ? 'display:none;' : '' }}">
                                            <button type="button" class="notify-me-btn" id="notifyMeOpen">
                                                <i class="far fa-bell"></i> Notify Me When Available
                                            </button>
                                        </div>

                                        @if(($singleProduct->is_free_shipping ?? 0) == 0)
                                            <div class="courier-card" style="font-family: 'Hind Siliguri', sans-serif;">
                                                <div class="courier-title">{{ $dt->courier_delivery_cost_text ?? 'Delivery Cost' }}</div>
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
                                                <div class="courier-title">{{ $dt->courier_delivery_cost_text ?? 'Delivery Cost' }}</div>
                                                <div class="text-center text-success" style="padding: 14px; font-weight: 800; font-size: 16px;">
                                                    <i class="fas fa-shipping-fast"></i> {{ $bangla_text->fshipping_text ?? 'Free Shipping' }}
                                                </div>
                                            </div>
                                        @endif

                                        <ul class="product-metas mt-4" style="font-family: 'Hind Siliguri', sans-serif;">
                                          {!! $singleProduct->feature !!}
                                        </ul>
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
                    {{ $dt->reviews_tab_text ?? 'Reviews' }}
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
                                        <h5 class="title">({{$singleProduct->reviews->count()}}) Relative Product</h5>
                                        <ul class="comment-list">
                                            @include("frontend.products.partials.reviewList")
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-lg-6 mb--20">
                                    <div class="premium-review-card">
                                        <div class="comment-respond pro-des-commend-respond mt--0">
                                            <h5 class="review-header-title">Add a Review</h5>
                                            
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
                                                            <input id="name" type="text" name="name" required placeholder="Your Name"/>
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
                <h2 class="border-bottom border-2 pb-2" style="font-family: 'Arial', sans-serif; font-weight: 800; color: var(--text);">Related Products</h2>
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
    
    document.querySelectorAll('a.all-reviews-button').forEach(function(btn){
      btn.addEventListener('click', function(e){
        e.preventDefault();
        const reviewTabLink = document.querySelector('#review-tab');
        if (reviewTabLink) {
          if (window.bootstrap && bootstrap.Tab) (new bootstrap.Tab(reviewTabLink)).show();
          else reviewTabLink.click();
        }
        setTimeout(function(){
          const target = document.querySelector('#writeReview');
          if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 250);
      });
    });

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
          let v = parseInt(input.value) || 1;
          input.value = v + 1;
          bumpQty();
        }, true);
      }
      if(minus){
        minus.addEventListener('click', (e) => {
          e.preventDefault(); e.stopImmediatePropagation();
          let v = parseInt(input.value) || 1;
          if(v > 1) { input.value = v - 1; bumpQty(); }
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
      if(isNaN(currentQty) || currentQty < 1) { currentQty = 1; qtyInput.val(1); }

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
    return '{{ $info->currency_symbol ?? "৳" }} ' + Math.round(val);
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

    $('.current-price-product').text(moneyText(v.price));
    if(!isFirstLoad) flashPrice();

    if (v.raw > v.price && v.raw > 0) {
      $('#product-old-price').show().text(moneyText(v.raw));
    } else {
      $('#product-old-price').hide();
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
      stockEl.innerHTML = `<i class="fas ${stock > 0 ? 'fa-check-circle text-success' : 'fa-times-circle text-danger'}"></i> <span>${stock > 0 ? stock : '0'} Items left</span>`;
    }

    $('.add_cart_btn, .order_now_btn').prop('disabled', stock <= 0 || window.__PRODUCT_OUT__);
    if(window.toggleNotifyMe) window.toggleNotifyMe(stock <= 0 || window.__PRODUCT_OUT__);
  }

  function setInvalidState(){
    window.__ACTIVE_VAR__ = null;
    $('span.size_name').text('(Not available)').show();
    
    const stockEl = document.querySelector('#stock-text-element');
    if(stockEl){
      stockEl.innerHTML = `<i class="fas fa-times-circle text-danger"></i> <span>0 Items left</span>`;
    }
    
    $('.add_cart_btn, .order_now_btn').prop('disabled', true);
    if(window.toggleNotifyMe) window.toggleNotifyMe(!!window.__PRODUCT_OUT__);
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
</script>
@endpush