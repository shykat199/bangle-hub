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
<style>
    /* --- kept: vars --- */
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
    /* --- kept: fadeInUp --- */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    /* --- kept: reviewbtn --- */
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
    .submit-review-btn:hover::after {
        left: 200%;
    }

    /* review buttons use the site's theme colour, like the other buttons on this page */
    .submit-review-btn,
    .submit-review-btn i {
      background: var(--pd-accent) !important;
      color: #fff !important;
    }
    .submit-review-btn{ box-shadow: 0 8px 20px -8px var(--pd-accent) !important; }
    .submit-review-btn i{ background: none !important; }
    .submit-review-btn:hover {
      background: color-mix(in srgb, var(--pd-accent) 82%, #000) !important;
      transform: translateY(-2px);
      box-shadow: 0 14px 26px -10px var(--pd-accent) !important;
    }
    .submit-review-btn:active {
        transform: translateY(-1px) scale(0.98);
    }
    /* --- kept: courier --- */
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
    /* --- kept: review --- */
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

    /* The theme's form rules inflate this form: a 60px line-height on inputs, a fixed
       textarea height and a min-height on the (empty) feedback-tags box. */
    .premium-review-card .feedback-tags{ min-height: 0 !important; }
    .premium-review-card .rating-component{ display: block; }
    .premium-review-card .rating-box-wrapper{ padding: 14px 16px !important; margin-bottom: 16px !important; }
    .premium-review-card .rating-label{ display: block; margin-bottom: 8px; }
    .premium-review-card .stars-box{ margin-bottom: 0 !important; }
    .premium-review-card .premium-input-group input:not([type="file"]){ height: 46px !important; min-height: 0 !important; line-height: 1.4 !important; padding: 10px 14px !important; }
    .premium-review-card .premium-input-group input[type="file"]{ height: auto !important; line-height: 1.4 !important; padding: 8px 10px !important; }
    .premium-review-card .premium-input-group textarea{ height: 96px !important; min-height: 0 !important; line-height: 1.5 !important; padding: 10px 14px !important; }
    .premium-review-card .review-header-title{ margin-bottom: 14px !important; }
    #review .pro-desc-commnet-area .pdx-pane-title{ margin-bottom: 8px; }
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
    /* --- kept: desc --- */
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
    /* --- kept: comments --- */
    .comment-list > li {
        animation: fadeInUp 0.5s ease both;
    }
    .comment-list > li:nth-child(1) { animation-delay: 0.1s; }
    .comment-list > li:nth-child(2) { animation-delay: 0.2s; }
    .comment-list > li:nth-child(3) { animation-delay: 0.3s; }
    /* --- kept: notify --- */
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
    /* --- kept: accentvars --- */
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
    /* --- kept: share --- */
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
    /* --- kept: reviewref --- */
    .pro-desc-commnet-area .title{ font-size: 20px; font-weight: 700; margin-bottom: 0; }
    .single-desc{ font-size: 15px; line-height: 1.8; }
    .view-more-btn{ border-width: 1.5px; }
    .view-more-btn:hover{ transform: none; border-color: var(--pd-accent); color: var(--pd-accent); box-shadow: none; }
    .review-header-title::after{ background: var(--pd-accent); }
    #reviewFormCard{ scroll-margin-top: 100px; }
    .review-login-box{ text-align: center; padding: 18px 8px 8px; }
    /* the tab content forces one text colour on everything inside it, icons included */
    .review-login-box > i{ font-size: 40px; color: var(--pd-accent) !important; }
    .woocommerce-tabs .tab-content .review-login-box .submit-review-btn,
    .woocommerce-tabs .tab-content .review-login-box .submit-review-btn i{ color: #fff !important; }
    .review-login-title{ margin: 12px 0 4px; font-size: 17px; font-weight: 700; color: var(--text); }
    .review-login-text{ margin: 0 auto 16px; max-width: 360px; font-size: 14px; line-height: 1.6; color: var(--muted) !important; }
    .review-login-box .submit-review-btn{ display: inline-flex; width: auto; text-decoration: none; }
    .premium-input-group input:focus, .premium-input-group textarea:focus{
        border-color: var(--pd-accent); box-shadow: 0 0 0 4px var(--pd-accent-ring); transform: none;
    }
    /* --- kept: buybar --- */
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

    /* ==========================================================================
       PRODUCT PAGE LAYOUT (.pdx): gallery with zoom on the left, buying panel on
       the right, then tabs beside related products. Accent = --pd-accent.
       ========================================================================== */
    .pdx{ --pdx-ink: #111827; --pdx-muted: #6b7280; --pdx-line: #e8eaee; --pdx-soft: #f6f7f9;
          --pdx-accent-soft: color-mix(in srgb, var(--pd-accent) 7%, #fff);
          --pdx-accent-dark: color-mix(in srgb, var(--pd-accent) 82%, #000);
          background: #fff; padding-bottom: 40px; color: var(--pdx-ink); }
    @media (min-width: 1400px){ .pdx .container{ max-width: 1360px; } }
    body{ background: #fff; }

    /* breadcrumb */
    .pdx-crumbs{ display: flex; flex-wrap: nowrap; align-items: center; gap: 8px; margin: 0; padding: 16px 0 14px; list-style: none; font-size: 14px; color: var(--pdx-muted); min-width: 0; }
    .pdx-crumbs li{ white-space: nowrap; flex: 0 0 auto; }
    .pdx-crumbs li + li::before{ content: "\203A"; margin-right: 8px; color: #9ca3af; }
    .pdx-crumbs a{ color: var(--pdx-muted); text-decoration: none; }
    .pdx-crumbs a:hover{ color: var(--pd-accent); }
    .pdx-crumbs li.is-current{ flex: 1 1 auto; min-width: 0; overflow: hidden; text-overflow: ellipsis; color: var(--pdx-ink); }

    .pdx-top{ display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 32px; align-items: start; }

    /* ---------- gallery ---------- */
    .pdx-gallery{ display: grid; grid-template-columns: 96px minmax(0, 1fr); gap: 14px; position: sticky; top: 150px; }
    /* desktop: the rail is as tall as the main picture (set by the script) and scrolls */
    .pdx-thumbs{ display: flex; flex-direction: column; gap: 10px; max-height: var(--pdx-stage-h, 560px); overflow-y: auto; scrollbar-width: none; padding: 2px; }
    .pdx-thumbs::-webkit-scrollbar{ display: none; }
    .pdx-thumb{
        position: relative; flex: 0 0 auto; width: 100%; aspect-ratio: 1; padding: 0; overflow: hidden; cursor: pointer;
        border: 1.5px solid var(--pdx-line); border-radius: 10px; background: var(--pdx-soft); transition: border-color .2s ease, box-shadow .2s ease;
    }
    .pdx-thumb img{ width: 100%; height: 100%; object-fit: cover; display: block; }
    .pdx-thumb:hover{ border-color: color-mix(in srgb, var(--pd-accent) 45%, var(--pdx-line)); }
    .pdx-thumb.is-active{ border: 2px solid var(--pd-accent); box-shadow: 0 0 0 3px color-mix(in srgb, var(--pd-accent) 14%, transparent); }
    .pdx-thumb .pdx-play{ position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(17,24,39,.35); color: #fff; font-size: 18px; }
    .pdx-thumb-out{ position: absolute; inset: 0; overflow: hidden; }
    .pdx-thumb-out span{ position: absolute; top: 50%; left: -15%; right: -15%; padding: 2px 0; transform: translateY(-50%) rotate(-12deg); background: rgba(229,62,80,.84); color: #fff; text-align: center; font-size: 9px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }

    .pdx-stage{
        position: relative; aspect-ratio: 1; overflow: hidden; border-radius: 14px; background: var(--pdx-soft);
        border: 1px solid var(--pdx-line); touch-action: pan-y; user-select: none;
    }
    .pdx-slide{ position: absolute; inset: 0; opacity: 0; visibility: hidden; transition: opacity .35s ease, visibility .35s; }
    .pdx-slide.is-active{ opacity: 1; visibility: visible; }
    .pdx-slide img{
        width: 100%; height: 100%; object-fit: contain; display: block; cursor: zoom-in;
        transition: transform .25s ease; transform-origin: 50% 50%; -webkit-user-drag: none;
    }
    .pdx-slide.is-zooming img{ transform: scale(2.3); transition: transform .12s ease-out; }

    /* High-quality look on every product photo of this page, same as the product cards. */
    .pdx-slide img, .pdx-thumb img, .pdx-lb-img img, .pdx-opt-img{
        filter: contrast(1.06) saturate(1.12) brightness(1.02);
        image-rendering: high-quality;
        backface-visibility: hidden; -webkit-backface-visibility: hidden;
    }

    .pdx-slide.is-video iframe{ width: 100%; height: 100%; border: 0; }

    .pdx-badges{ position: absolute; top: 14px; left: 14px; z-index: 3; display: flex; flex-direction: column; gap: 6px; align-items: flex-start; pointer-events: none; }
    .pdx-badge{ padding: 5px 12px; border-radius: 6px; font-size: 13px; font-weight: 700; color: #fff; background: var(--pd-accent); box-shadow: 0 6px 14px -6px rgba(0,0,0,.35); }
    .pdx-badge.is-dark{ background: #111827; }

    .pdx-stage-btn{
        position: absolute; z-index: 4; width: 44px; height: 44px; border-radius: 50%; border: 0; cursor: pointer;
        display: flex; align-items: center; justify-content: center; background: #fff; color: var(--pdx-ink);
        box-shadow: 0 6px 18px -6px rgba(15,23,42,.35); transition: background .2s ease, color .2s ease, opacity .2s ease, transform .2s ease;
    }
    .pdx-stage-btn:hover{ background: var(--pd-accent); color: #fff; }
    .pdx-zoom-btn{ right: 14px; bottom: 14px; font-size: 17px; }
    .pdx-nav-btn{ top: 50%; transform: translateY(-50%); opacity: 0; font-size: 14px; }
    .pdx-nav-btn.is-prev{ left: 12px; } .pdx-nav-btn.is-next{ right: 12px; }
    .pdx-stage:hover .pdx-nav-btn{ opacity: 1; }
    .pdx-stage.is-zoomed-in .pdx-nav-btn, .pdx-stage.is-zoomed-in .pdx-badges{ opacity: 0; }
    .pdx-dots{ display: none; }

    /* out of stock: a diagonal ribbon right across the picture, same as the product cards */
    .pdx-out-overlay{ position: absolute; inset: 0; z-index: 2; overflow: hidden; pointer-events: none; container-type: inline-size; }
    .pdx-out-overlay span{
        position: absolute; top: 50%; left: -12%; right: -12%; padding: .6em 0; transform: translateY(-50%) rotate(-12deg);
        background: rgba(229,62,80,.84); color: #fff; text-align: center; white-space: nowrap; text-shadow: 0 1px 2px rgba(0,0,0,.18);
        font-size: 22px; font-size: clamp(15px, 5.2cqw, 30px); font-weight: 800; line-height: 1.2; letter-spacing: .14em; text-transform: uppercase;
    }

    /* lightbox */
    .pdx-lightbox{ position: fixed; inset: 0; z-index: 100000; display: none; background: rgba(10,12,18,.94); }
    .pdx-lightbox.is-open{ display: block; }
    .pdx-lb-img{ position: absolute; inset: 56px 60px; display: flex; align-items: center; justify-content: center; overflow: hidden; touch-action: none; }
    .pdx-lb-img img{ max-width: 100%; max-height: 100%; object-fit: contain; cursor: zoom-in; transition: transform .2s ease; transform-origin: 50% 50%; -webkit-user-drag: none; user-select: none; }
    .pdx-lb-img.is-zoomed img{ cursor: grab; transform: scale(2.5); transition: none; }
    .pdx-lb-btn{ position: absolute; z-index: 2; width: 46px; height: 46px; border: 0; border-radius: 50%; background: rgba(255,255,255,.12); color: #fff; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .pdx-lb-btn:hover{ background: var(--pd-accent); }
    .pdx-lb-close{ top: 12px; right: 14px; }
    .pdx-lb-prev{ left: 10px; top: 50%; transform: translateY(-50%); }
    .pdx-lb-next{ right: 10px; top: 50%; transform: translateY(-50%); }
    .pdx-lb-count{ position: absolute; top: 22px; left: 20px; color: rgba(255,255,255,.8); font-size: 14px; font-weight: 600; }

    /* ---------- info column ---------- */
    .pdx-chips{ display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
    .pdx-chip{
        display: inline-flex; align-items: center; gap: 6px; padding: 5px 11px; border-radius: 8px;
        border: 1px solid var(--pdx-line); background: #fff; font-size: 13px; font-weight: 500; color: var(--pdx-ink); text-decoration: none !important;
    }
    .pdx-chip i{ color: var(--pd-accent); font-size: 13px; }
    a.pdx-chip:hover{ border-color: var(--pd-accent); color: var(--pd-accent); }
    .pdx-title{ margin: 0 0 8px; font-size: 24px; font-weight: 800; line-height: 1.25; letter-spacing: -.01em; color: var(--pdx-ink); }
    .pdx-meta{ display: flex; flex-wrap: wrap; align-items: center; gap: 8px 14px; font-size: 15px; color: #374151; }
    .pdx-stars{ color: #f59e0b; font-size: 15px; letter-spacing: 1px; }
    .pdx-stars .far{ color: #d1d5db; }
    .pdx-meta .all-reviews-button{ color: var(--pdx-muted); text-decoration: none; font-size: 14px; }
    .pdx-meta .all-reviews-button:hover{ color: var(--pd-accent); text-decoration: underline; }
    .pdx-meta-sep{ width: 1px; height: 16px; background: #d1d5db; }
    .pdx-short{ margin-top: 10px; font-size: 15px; line-height: 1.6; color: #374151; }
    .pdx-short p{ margin: 0 0 6px; } .pdx-short p:last-child{ margin: 0; }

    /* price box: one slim strip on every screen. The price row, the small print and the
       stock / minimum order facts share a line and wrap on their own when it gets narrow. */
    .pdx-pricebox{
        display: flex; flex-wrap: wrap; align-items: baseline; gap: 0 12px;
        margin-top: 10px; padding: 7px 14px 8px; border-radius: 10px; background: var(--pdx-soft); border: 1px solid var(--pdx-line);
    }
    .pdx-price{ display: contents; }
    .pdx-price-row{ display: flex; flex-wrap: wrap; align-items: baseline; gap: 0 8px; }
    .pdx-price-now{ font-size: 26px; font-weight: 800; line-height: 1.2; color: var(--pd-accent); white-space: nowrap; }
    .pdx-price-now.price-flash{ animation: pdxFlash .6s ease; }
    @keyframes pdxFlash{ 0%{ transform: scale(1); } 40%{ transform: scale(1.06); } 100%{ transform: scale(1); } }
    .pdx-price-row del{ font-size: 14.5px; color: #9ca3af; }
    .pdx-save{ padding: 1px 8px; border-radius: 999px; background: #dcfce7; color: #15803d; font-size: 11.5px; font-weight: 700; }
    .pdx-price-note{ font-size: 12.5px; line-height: 1.5; color: var(--pdx-muted); }
    .pdx-facts{ display: flex; flex-wrap: wrap; gap: 0 14px; margin-left: auto; }
    .pdx-fact{ min-width: 0; }
    .pdx-fact > i{ display: none; }
    .pdx-fact small, .pdx-fact strong{ display: inline; font-size: 12.5px; line-height: 1.5; white-space: nowrap; }
    .pdx-fact small{ color: var(--pdx-muted); }
    .pdx-fact small::after{ content: ": "; }
    .pdx-fact strong{ font-weight: 700; color: var(--pdx-ink); }
    .pdx-fact strong.is-low{ color: #ea580c; }
    .pdx-fact strong.is-out{ color: #dc2626; }

    /* variants */
    .pdx-variants{ display: flex; flex-direction: column; gap: 12px; margin-top: 14px; }
    .pdx-vlabel{ display: block; margin-bottom: 7px; font-size: 14.5px; font-weight: 700; color: var(--pdx-ink); }
    .pdx-vlabel .pd-variant-picked{ font-weight: 500; color: var(--pdx-muted); margin-left: 4px; }
    .pdx-opts{ display: flex; flex-wrap: wrap; gap: 9px; }
    .pdx-opt{
        position: relative; display: inline-flex; align-items: center; gap: 9px; min-height: 42px; padding: 6px 16px;
        border: 1.5px solid var(--pdx-line); border-radius: 10px; background: #fff; cursor: pointer; user-select: none;
        font-size: 14.5px; font-weight: 600; color: var(--pdx-ink); transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
    }
    .pdx-opt:hover{ border-color: color-mix(in srgb, var(--pd-accent) 50%, var(--pdx-line)); }
    .pdx-opt.active{ border-color: var(--pd-accent); background: var(--pdx-accent-soft); color: var(--pd-accent); box-shadow: 0 0 0 3px color-mix(in srgb, var(--pd-accent) 12%, transparent); }
    .pdx-opt.active::after{
        content: "\f00c"; font-family: "Font Awesome 5 Free"; font-weight: 900; position: absolute; top: -7px; right: -7px;
        width: 18px; height: 18px; border-radius: 50%; background: var(--pd-accent); color: #fff; font-size: 9px;
        display: flex; align-items: center; justify-content: center; border: 2px solid #fff;
    }
    .pdx-opt.has-img{ padding: 5px 14px 5px 5px; }
    .pdx-opt-img{ width: 40px; height: 40px; border-radius: 7px; object-fit: cover; background: var(--pdx-soft); }
    .pdx-opt-swatch{ width: 20px; height: 20px; border-radius: 50%; border: 1px solid rgba(0,0,0,.12); }
    .pdx-opt.is-out{ color: #9ca3af; background: repeating-linear-gradient(135deg, #fff, #fff 6px, #f6f7f9 6px, #f6f7f9 12px); }
    .pdx-opt.is-out .pdx-opt-name{ text-decoration: line-through; }
    .pdx-opt.is-out .pdx-opt-img{ opacity: .45; filter: grayscale(1); }
    .pdx-opt.is-out.active{ color: #9ca3af; }

    /* quantity + note: a bordered row with the label on the left and a stepper of
       separate round buttons on the right; the wholesale note sits beside it */
    .pdx-qty-row{ display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 16px; align-items: stretch; margin-top: 16px; }
    .pdx-qty-row > div:first-child{
        display: flex; align-items: center; justify-content: space-between; gap: 28px;
        padding: 8px 10px 8px 16px; border: 1px solid var(--pdx-line); border-radius: 12px; background: #fff;
    }
    .pdx-qty-row > div:only-child{ grid-column: 1 / -1; }
    .pdx-qty-label{ display: block; margin: 0; font-size: 15px; font-weight: 700; white-space: nowrap; }
    .pdx-qty{ display: inline-flex; align-items: center; gap: 4px; }
    .pdx-qty button{
        width: 40px; height: 40px; padding: 0; border-radius: 50%; border: 1.5px solid var(--pd-accent);
        background: #fff; color: var(--pd-accent); font-size: 20px; line-height: 1; cursor: pointer; transition: background .2s ease, color .2s ease;
    }
    .pdx-qty button:hover{ background: var(--pd-accent); color: #fff; }
    /* the theme pads every input on its sides, which squeezed the number out of view */
    .pdx-qty input{
        width: 52px; min-width: 52px; height: 40px !important; padding: 0 4px !important; margin: 0; border: 0 !important;
        box-sizing: border-box; text-align: center; font-size: 17px; font-weight: 700; line-height: 1;
        color: var(--pdx-ink); background: none; box-shadow: none !important; -moz-appearance: textfield;
    }
    .pdx-qty input::-webkit-outer-spin-button, .pdx-qty input::-webkit-inner-spin-button{ -webkit-appearance: none; margin: 0; }
    .pdx-qty input.qty-bump{ animation: pdxFlash .3s ease; }
    .pdx-qty.is-disabled{ opacity: .5; pointer-events: none; }
    .pdx-qty-note{ display: flex; align-items: center; gap: 12px; padding: 10px 14px; border-radius: 10px; background: var(--pdx-accent-soft); border: 1px solid color-mix(in srgb, var(--pd-accent) 14%, transparent); font-size: 13.5px; color: #374151; line-height: 1.4; }
    .pdx-qty-note i{ font-size: 20px; color: var(--pd-accent); }
    .pdx-qty-note strong{ display: block; color: var(--pd-accent); font-weight: 700; }

    /* buttons */
    .pdx-actions{ display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-top: 16px; }
    .pdx-btn{
        display: inline-flex; align-items: center; justify-content: center; gap: 10px; height: 54px; padding: 0 16px;
        border-radius: 10px; border: 2px solid var(--pd-accent); font-size: 17px; font-weight: 700; cursor: pointer;
        text-decoration: none !important; white-space: nowrap; transition: background .2s ease, color .2s ease, transform .15s ease, box-shadow .2s ease;
    }
    .pdx-btn i{ font-size: 18px; }
    .pdx-btn.is-solid{ background: var(--pd-accent); color: #fff !important; }
    .pdx-btn.is-solid:hover{ background: var(--pdx-accent-dark); border-color: var(--pdx-accent-dark); box-shadow: 0 10px 22px -10px var(--pd-accent); }
    .pdx-btn.is-outline{ background: #fff; color: var(--pd-accent) !important; }
    .pdx-btn.is-outline:hover{ background: var(--pdx-accent-soft); }
    .pdx-btn.is-wa{ border-color: #16a34a; color: #16a34a !important; }
    .pdx-btn.is-wa:hover{ background: #f0fdf4; }
    .pdx-btn.is-dark{ border-color: #111827; color: #111827 !important; }
    .pdx-btn.is-dark:hover{ background: #f3f4f6; }
    .pdx-btn:active{ transform: scale(.98); }
    .pdx-btn:disabled{ opacity: .5; cursor: not-allowed; filter: grayscale(.6); box-shadow: none; }
    .pdx-btn.cart-success{ animation: pdxFlash .5s ease; }
    .pdx-actions .is-span{ grid-column: 1 / -1; }

    .pdx .notify-me-wrap{ margin-top: 12px; }

    /* delivery charge */
    .pdx-delivery{ margin-top: 16px; padding: 12px 14px; border: 1px solid var(--pdx-line); border-radius: 12px; background: #fff; }
    .pdx-delivery-head{ display: flex; align-items: center; gap: 8px; margin-bottom: 10px; font-size: 14.5px; font-weight: 700; color: var(--pdx-ink); }
    .pdx-delivery-head i{ color: var(--pd-accent); }
    .pdx-delivery-grid{ display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .pdx-delivery-item{ display: flex; align-items: center; gap: 10px; min-width: 0; padding: 9px 12px; border-radius: 10px; background: var(--pdx-soft); }
    .pdx-delivery-item > i{ flex: 0 0 auto; width: 18px; text-align: center; font-size: 16px; color: var(--pd-accent); }
    .pdx-delivery-item small{ display: block; font-size: 12.5px; line-height: 1.25; color: var(--pdx-muted); }
    .pdx-delivery-item strong{ display: block; font-size: 15.5px; font-weight: 800; line-height: 1.3; color: var(--pdx-ink); white-space: nowrap; }
    .pdx-delivery-item strong.is-free{ color: #15803d; }
    .pdx-delivery-note{ margin: 8px 0 0; font-size: 12.5px; line-height: 1.4; color: var(--pdx-muted); }

    /* trust row */
    .pdx-trust{ display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin: 22px 0 0; padding: 16px 0; border-top: 1px solid var(--pdx-line); border-bottom: 1px solid var(--pdx-line); list-style: none; }
    .pdx-trust li{ display: flex; align-items: center; gap: 10px; min-width: 0; }
    .pdx-trust i{ flex: 0 0 auto; font-size: 24px; color: var(--pd-accent); }
    .pdx-trust strong{ display: block; font-size: 14px; font-weight: 700; line-height: 1.25; }
    .pdx-trust small{ display: block; font-size: 12.5px; color: var(--pdx-muted); line-height: 1.3; }
    .pdx .pd-share{ margin-top: 16px; }

    /* ---------- bottom: tabs + related ---------- */
    .pdx-bottom{ display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 24px; align-items: start; margin-top: 40px; }
    .pdx-card{ border: 1px solid var(--pdx-line); border-radius: 14px; background: #fff; }
    .pdx-tabs{ display: flex; gap: 6px; margin: 0; padding: 0 12px; list-style: none; border-bottom: 1px solid var(--pdx-line); overflow-x: auto; scrollbar-width: none; }
    .pdx-tabs::-webkit-scrollbar{ display: none; }
    .pdx-tabs .nav-link{
        position: relative; display: block; padding: 15px 14px; border: 0; background: none !important; white-space: nowrap;
        font-size: 15.5px; font-weight: 600; color: #374151 !important; text-decoration: none;
    }
    .pdx-tabs .nav-link::after{ content: ""; position: absolute; left: 10px; right: 10px; bottom: -1px; height: 3px; border-radius: 3px 3px 0 0; background: var(--pd-accent); transform: scaleX(0); transition: transform .25s ease; }
    .pdx-tabs .nav-link:hover{ color: var(--pd-accent) !important; }
    .pdx-tabs .nav-link.active{ color: var(--pd-accent) !important; }
    .pdx-tabs .nav-link.active::after{ transform: scaleX(1); }
    .pdx-tabs .pd-tab-count{ margin-left: 2px; color: inherit; }
    .pdx-pane{ padding: 22px 24px 26px; }
    .pdx-pane-title{ margin: 0 0 12px; font-size: 22px; font-weight: 800; color: var(--pdx-ink); }
    .pdx .single-desc{ font-size: 15px; line-height: 1.75; color: #374151; }
    .pdx .single-desc img{ max-width: 100%; height: auto; }
    .pdx-features{ margin: 16px 0 0; padding: 0; list-style: none; }
    .pdx-features ul{ margin: 0; padding: 0; list-style: none; }
    .pdx-features li{ position: relative; padding: 4px 0 4px 30px; font-size: 15px; color: #374151; line-height: 1.55; }
    .pdx-features li::before{
        content: "\f00c"; font-family: "Font Awesome 5 Free"; font-weight: 900; position: absolute; left: 0; top: 6px;
        width: 20px; height: 20px; border-radius: 50%; background: var(--pd-accent); color: #fff; font-size: 10px;
        display: flex; align-items: center; justify-content: center;
    }
    .pdx-ship-list{ margin: 16px 0 0; padding: 0; list-style: none; }
    .pdx-ship-list li{ display: flex; gap: 12px; align-items: flex-start; padding: 10px 0; border-top: 1px dashed var(--pdx-line); font-size: 14.5px; color: #374151; }
    .pdx-ship-list li i{ margin-top: 3px; color: var(--pd-accent); width: 18px; text-align: center; }
    .pdx .reviews-wrapper .row > [class*=col]{ flex: 0 0 100%; max-width: 100%; }

    .pdx-related{ padding: 18px 18px 20px; }
    .pdx-related-head{ display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 14px; }
    .pdx-related-head h2{ margin: 0; font-size: 22px; font-weight: 800; color: var(--pdx-ink); }
    .pdx-related-head a{ font-size: 14.5px; font-weight: 700; color: var(--pd-accent); text-decoration: none; white-space: nowrap; }
    .pdx-related-head a i{ font-size: 12px; margin-left: 3px; }
    .pdx-related-grid{ display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
    @media (max-width: 1399.98px){ .pdx-related-grid{ grid-template-columns: repeat(2, minmax(0, 1fr)); } }

    /* ---------- responsive ---------- */
    @media (max-width: 1199.98px){
        .pdx-top{ gap: 24px; }
        .pdx-title{ font-size: 22px; }
        .pdx-trust{ grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 991.98px){
        .pdx-top, .pdx-bottom{ grid-template-columns: minmax(0, 1fr); }
        .pdx-gallery{ position: static; grid-template-columns: 1fr; }

        /* One column: the buying panel is flattened so its parts can be put in a new order.
           picture, variants (so the picture change is seen on tap), title, price,
           Notify Me, quantity, buttons, then everything else as written. */
        .pdx-top{ gap: 0; }
        .pdx-info, .pdx-info > form{ display: contents; }
        .pdx-gallery{ order: -9; }
        .pdx-variants{ order: -8; margin-top: 16px; }
        .pdx-title{ order: -7; margin: 18px 0 8px; font-size: 22px; }
        .pdx-meta{ order: -6; }
        .pdx-pricebox{ order: -5; }
        .pdx .notify-me-wrap{ order: -3; }
        .pdx-qty-row{ order: -2; }
        .pdx-actions{ order: -1; }
        .pdx-delivery{ order: -1; }
        .pdx-chips{ margin: 20px 0 0; }
        /* out of stock: Notify Me stands in for the quantity and the two dead buy buttons */
        .pdx-top.is-stock-out .pdx-qty-row,
        .pdx-top.is-stock-out .pdx-cart-btn,
        .pdx-top.is-stock-out .pdx-order-btn{ display: none; }
        .pdx-thumbs{ order: 2; flex-direction: row; max-height: none; overflow-x: auto; overflow-y: hidden; }
        .pdx-thumb{ width: 72px; }
        .pdx-nav-btn{ opacity: 1; width: 38px; height: 38px; }
        .pdx-related-grid{ grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (min-width: 768px) and (max-width: 991.98px){
        .pdx-gallery{ width: 100%; max-width: 560px; justify-self: center; }
    }
    @media (max-width: 767.98px){
        .pdx-crumbs{ font-size: 13px; padding: 12px 0 10px; }
        .pdx-crumbs li:not(:first-child):not(.is-current){ max-width: 110px; overflow: hidden; text-overflow: ellipsis; }
        .pdx-title{ margin: 14px 0 6px; font-size: 17px; font-weight: 700; line-height: 1.35; letter-spacing: 0; }
        .pdx-meta{ gap: 6px 10px; font-size: 13px; }
        .pdx-stars, .pdx-meta .all-reviews-button{ font-size: 13px; }
        .pdx-meta-sep{ display: none; }
        .pdx-stage{ border-radius: 12px; }
        .pdx-nav-btn{ display: none; }
        .pdx-dots{ display: flex; position: absolute; left: 0; right: 0; bottom: 12px; z-index: 3; justify-content: center; gap: 6px; pointer-events: none; }
        .pdx-dots span{ width: 7px; height: 7px; border-radius: 7px; background: rgba(17,24,39,.25); transition: width .25s ease, background .25s ease; }
        .pdx-dots span.is-active{ width: 20px; background: var(--pd-accent); }
        .pdx-zoom-btn{ width: 38px; height: 38px; font-size: 15px; right: 10px; bottom: 10px; }
        .pdx-gallery{ gap: 10px; }
        .pdx-thumbs{ gap: 8px; }
        .pdx-thumb{ width: 52px; border-radius: 8px; }
        .pdx-variants{ gap: 12px; margin-top: 12px; }
        .pdx-vlabel{ margin-bottom: 7px; font-size: 13.5px; }
        .pdx-opts{ gap: 8px; }
        .pdx-opt{ min-height: 38px; padding: 5px 12px; gap: 7px; border-radius: 9px; font-size: 13.5px; }
        .pdx-opt.has-img{ padding: 4px 11px 4px 4px; }
        .pdx-opt-img{ width: 34px; height: 34px; }
        .pdx-pricebox{ gap: 0 10px; padding: 6px 12px 7px; }
        .pdx-price-note, .pdx-fact small, .pdx-fact strong{ font-size: 12px; }
        .pdx .notify-me-wrap{ margin-top: 10px; }
        /* quantity: a little smaller, and no sticky hover fill after a tap */
        .pdx-qty-row{ margin-top: 12px; }
        .pdx-qty-row > div:first-child{ gap: 12px; padding: 7px 8px 7px 14px; }
        .pdx-qty-label{ font-size: 14px; }
        .pdx-qty button, .pdx-qty button:hover{ width: 38px; height: 38px; background: #fff; color: var(--pd-accent); }
        .pdx-qty button:active{ background: var(--pd-accent); color: #fff; }
        .pdx-qty input{ width: 46px; min-width: 46px; height: 38px !important; font-size: 16px; }
        .pdx-qty-note{ padding: 8px 12px; font-size: 12.5px; }
        .pdx-qty-note i{ font-size: 17px; }
        .pdx-actions{ gap: 10px; margin-top: 14px; }
        .pdx-delivery{ margin-top: 14px; padding: 10px 12px; }
        .pdx-delivery-head{ margin-bottom: 8px; font-size: 14px; }
        .pdx-delivery-grid{ gap: 8px; }
        .pdx-delivery-item{ gap: 8px; padding: 8px 10px; }
        .pdx-delivery-item small{ font-size: 12px; }
        .pdx-delivery-item strong{ font-size: 14.5px; }
        .pdx-chips{ gap: 6px; margin-top: 18px; }
        .pdx-chip{ padding: 5px 10px; font-size: 12.5px; }
        .pdx-short{ font-size: 14.5px; }
        .pdx-price-now{ font-size: 24px; }
        .pdx-qty-row{ grid-template-columns: 1fr; gap: 10px; }
        .pdx-btn{ height: 50px; font-size: 15px; padding: 0 10px; gap: 8px; }
        .pdx-btn i{ font-size: 16px; }
        .pdx-trust strong{ font-size: 13px; } .pdx-trust small{ font-size: 11.5px; } .pdx-trust i{ font-size: 20px; }
        .pdx-pane{ padding: 18px 16px 20px; }
        .pdx-pane-title{ font-size: 19px; }
        .pdx-tabs .nav-link{ padding: 13px 10px; font-size: 14.5px; }
        .pdx-related-grid{ grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .pdx-lb-img{ inset: 56px 0 20px; }
        .pdx-lb-prev, .pdx-lb-next{ display: none; }
    }
    @media (prefers-reduced-motion: reduce){
        .pdx-slide, .pdx-slide img, .pdx-btn, .pdx-opt{ transition: none; }
    }
</style>
@endpush

@section('content')
@php
    $crumbCategory    = $singleProduct->category;
    $crumbSubCategory = $singleProduct->sub_category_id ? \App\Models\Category::find($singleProduct->sub_category_id) : null;

    // Gallery: the main picture first, then the extra pictures. A variant with its own
    // picture swaps it into the first slide (see applyVariation()).
    $mainImage = getImage('products', $singleProduct->image);
    $gallery   = collect([$mainImage])->concat($singleProduct->images->map(fn ($im) => getImage('products', $im->image)))->values();
    $hasVideo  = $singleProduct->video_link && $singleProduct->is_video_active == 1;

    // Colour chips show the variant's own picture when it has one, else a swatch when the colour code is a hex value.
    $colorThumbs = [];
    $colorHex    = [];
    foreach ($singleProduct->variations as $v) {
        $cid = (int) ($v->color_id ?? 0);
        if ($cid && empty($colorThumbs[$cid]) && $v->image) $colorThumbs[$cid] = getImage('products', $v->image);
        $code = (string) optional($v->color)->code;
        if ($cid && preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $code)) $colorHex[$cid] = $code;
    }

    $isNew      = $singleProduct->created_at && $singleProduct->created_at->gt(now()->subDays(30));
    $isWholesale = $minOrderQty > 1;
    $perVariant = $singleProduct->type === 'variable' ? ' per variant' : '';
    $relatedUrl = $crumbCategory && $crumbCategory->url ? route('front.category', [$crumbCategory->url]) : route('front.products.index');
@endphp
<main class="main-wrapper pdx">
<div class="container">

    <nav aria-label="Breadcrumb">
        <ol class="pdx-crumbs">
            <li><a href="{{ route('front.home') }}">Home</a></li>
            @if($crumbCategory && $crumbCategory->url)
                <li><a href="{{ route('front.category', [$crumbCategory->url]) }}">{{ $crumbCategory->name }}</a></li>
            @endif
            @if($crumbSubCategory && $crumbSubCategory->url)
                <li><a href="{{ route('front.category', [$crumbSubCategory->url]) }}">{{ $crumbSubCategory->name }}</a></li>
            @endif
            <li class="is-current" aria-current="page" title="{{ $singleProduct->name }}">{{ $singleProduct->name }}</li>
        </ol>
    </nav>

    <div class="pdx-top {{ $inStock ? '' : 'is-stock-out' }}" id="pdxTop">

        {{-- ===== GALLERY ===== --}}
        <div class="pdx-gallery" id="pdxGallery">
            <div class="pdx-thumbs" role="tablist" aria-label="Product pictures">
                @php $slideNo = 0; @endphp
                @if($hasVideo)
                    <button type="button" class="pdx-thumb is-active" data-slide="{{ $slideNo++ }}" aria-label="Product video">
                        <img src="{{ $mainImage }}" alt="" id="video-thumb-image">
                        <span class="pdx-play"><i class="fas fa-play"></i></span>
                    </button>
                @endif
                @foreach($gallery as $i => $src)
                    <button type="button" class="pdx-thumb {{ !$hasVideo && $i === 0 ? 'is-active' : '' }}" data-slide="{{ $slideNo++ }}" aria-label="Picture {{ $i + 1 }}">
                        <img src="{{ $src }}" alt="" @if($i === 0) id="thumb-image" @else loading="lazy" @endif>
                        @if($i === 0)
                            <span class="pdx-thumb-out" id="pdThumbStockOut" style="{{ $inStock ? 'display:none;' : '' }}"><span>Out</span></span>
                        @endif
                    </button>
                @endforeach
            </div>

            <div class="pdx-stage" id="pdxStage">
                @php $slideNo = 0; @endphp
                @if($hasVideo)
                    @php
                        $video_iframe = preg_replace_callback('/src="([^"]+)"/', function ($m) {
                            $sep = strpos($m[1], '?') !== false ? '&' : '?';
                            return 'src="' . $m[1] . $sep . 'autoplay=1&mute=1"';
                        }, $singleProduct->video_link);
                    @endphp
                    <div class="pdx-slide is-video is-active" data-slide="{{ $slideNo++ }}">
                        {!! str_replace(['<iframe', 'width=', 'height='], ['<iframe allow="autoplay; encrypted-media"', 'data-w=', 'data-h='], $video_iframe) !!}
                    </div>
                @endif
                @foreach($gallery as $i => $src)
                    <div class="pdx-slide {{ !$hasVideo && $i === 0 ? 'is-active' : '' }}" data-slide="{{ $slideNo++ }}">
                        <img src="{{ $src }}" alt="{{ $singleProduct->name }}" @if($i === 0) id="main-image" @else loading="lazy" @endif draggable="false">
                    </div>
                @endforeach

                <div class="pdx-badges">
                    @if($isNew)<span class="pdx-badge">New Arrival</span>@endif
                    <span class="pdx-badge is-dark" id="pdxOffBadge" style="{{ $initSavePct > 0 ? '' : 'display:none;' }}">{{ $initSavePct }}% Off</span>
                </div>
                <div class="pdx-out-overlay" id="pdStockOutOverlay" style="{{ $inStock ? 'display:none;' : '' }}"><span>Out of Stock</span></div>

                <button type="button" class="pdx-stage-btn pdx-nav-btn is-prev" aria-label="Previous picture"><i class="fas fa-chevron-left"></i></button>
                <button type="button" class="pdx-stage-btn pdx-nav-btn is-next" aria-label="Next picture"><i class="fas fa-chevron-right"></i></button>
                <button type="button" class="pdx-stage-btn pdx-zoom-btn" aria-label="View full screen"><i class="fas fa-search-plus"></i></button>
                <div class="pdx-dots" aria-hidden="true">
                    @for($d = 0; $d < $slideNo; $d++)<span class="{{ $d === 0 ? 'is-active' : '' }}"></span>@endfor
                </div>
            </div>
        </div>

        {{-- ===== DETAILS ===== --}}
        <div class="pdx-info">
            <div class="pdx-chips">
                @if($crumbCategory)
                    <a class="pdx-chip" href="{{ $relatedUrl }}"><i class="fas fa-tag"></i> {{ $crumbCategory->name }}</a>
                @endif
                @if($isWholesale)
                    <span class="pdx-chip"><i class="fas fa-boxes"></i> Wholesale</span>
                @endif
                @if(($singleProduct->is_free_shipping ?? 0) == 1)
                    <span class="pdx-chip"><i class="fas fa-shipping-fast"></i> Free Shipping</span>
                @endif
                <span class="pdx-chip"><i class="fas fa-shield-alt"></i> Quality Assured</span>
                <span class="pdx-chip"><i class="far fa-star"></i> Premium Quality</span>
            </div>

            <h1 class="pdx-title">{{ $singleProduct->name }}</h1>

            <div class="pdx-meta">
                <span class="pdx-stars" aria-label="Rated {{ number_format($averageRating, 1) }} out of 5">
                    @for($st = 1; $st <= 5; $st++)
                        <i class="{{ $averageRating >= $st ? 'fas fa-star' : ($averageRating >= $st - 0.5 ? 'fas fa-star-half-alt' : 'far fa-star') }}"></i>
                    @endfor
                </span>
                @if($totalReviews > 0)
                    <a class="all-reviews-button" href="#writeReview">{{ number_format($averageRating, 1) }} ({{ $totalReviews }} {{ $totalReviews === 1 ? 'Review' : 'Reviews' }})</a>
                @else
                    <a class="all-reviews-button" href="#writeReview">No reviews yet &middot; Write one</a>
                @endif
                @if(!empty($singleProduct->sku))
                    <span class="pdx-meta-sep" aria-hidden="true"></span>
                    <span>SKU: {{ $singleProduct->sku }}</span>
                @endif
            </div>

            @if(!empty($singleProduct->short_description))
                <div class="pdx-short">{!! $singleProduct->short_description !!}</div>
            @endif

            <div class="pdx-pricebox" id="pdxPriceBox">
                <div class="pdx-price">
                    <div class="pdx-price-row">
                        <span class="pdx-price-now" id="pdxPriceNow">{{ biz_format_currency($initFinal) }}</span>
                        <del id="product-old-price" style="{{ $initSavePct > 0 ? '' : 'display:none;' }}">{{ $initSavePct > 0 ? biz_format_currency($initRaw) : '' }}</del>
                        <span class="pdx-save" id="pdSaveBadge" style="{{ $initSavePct > 0 ? '' : 'display:none;' }}">{{ $fmtOff($initOff) }} {{ $currSymbol }} off</span>
                    </div>
                    <span class="pdx-price-note">{{ $isWholesale ? 'Wholesale price per piece' : 'Price per piece' }}</span>
                </div>
                <div class="pdx-facts">
                    @if($isWholesale)
                        <div class="pdx-fact"><i class="fas fa-shield-alt"></i><div><small>Minimum Order</small><strong>{{ $minOrderQty }} Pcs</strong></div></div>
                    @endif
                    <div class="pdx-fact"><i class="fas fa-cubes"></i><div><small>Stock Available</small><strong id="stock-text-element" class="{{ !$inStock ? 'is-out' : ((int) $initialStock <= $lowStockLimit ? 'is-low' : '') }}">{{ $inStock ? ((int) $initialStock).' Pcs' : 'Out of stock' }}</strong></div></div>
                </div>
            </div>

            <form action="{{ route('front.carts.storeCart') }}" id="cart_submit" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $singleProduct->id }}">
                <input type="hidden" name="product_name" value="{{ $singleProduct->name }}">
                <input type="hidden" name="category_id" value="{{ $singleProduct->category->name ?? '' }}">
                <input type="hidden" name="variation_id" id="variation_id" value="{{ $initialVar['id'] ?? '' }}">
                <input type="hidden" name="variant_name" id="variant_name" value="">
                <input type="hidden" id="size_value"  name="size_value"  value="">
                <input type="hidden" id="size_value1" name="size_value1" value="">
                <input type="hidden" id="price_val"   name="price_val"   value="{{ $initialVar['price'] ?? ($singleProduct->after_discount > 0 ? $singleProduct->after_discount : $singleProduct->sell_price) }}">
                <input type="hidden" id="price_val1"  name="price_val1"  value="{{ $initialVar['price'] ?? ($singleProduct->after_discount > 0 ? $singleProduct->after_discount : $singleProduct->sell_price) }}">
                <input type="hidden" name="action_type" id="input_action_type" value="cart">

                <script>
                    window.__VAR_MAP__ = @json($varMap);
                    window.__VAR_DEFAULT__ = {
                        size_id: {{ (int) $defaultSizeId }},
                        color_id: {{ (int) $defaultColorId }},
                        default_size_id: {{ (int) $DEFAULT_SIZE_ID }},
                        default_color_id: {{ (int) $DEFAULT_COLOR_ID }},
                        show_size: {{ $showSize ? 'true' : 'false' }},
                        show_color: {{ $showColor ? 'true' : 'false' }},
                    };
                </script>

                @if($showSize || $showColor)
                    <div class="pdx-variants">
                        @if($showColor)
                            <div>
                                <span class="pdx-vlabel">Color: <span class="pd-variant-picked" id="pdPickedColor">{{ $colorsMap[$defaultColorId] ?? '' }}</span></span>
                                <div class="pdx-opts" id="colorOptions" role="radiogroup" aria-label="Color">
                                    @foreach($colorsMap as $cid => $clabel)
                                        <div class="pdx-opt color-opt {{ !empty($colorThumbs[$cid]) ? 'has-img' : '' }} {{ (int) $cid === (int) $defaultColorId ? 'active' : '' }}"
                                             data-color-id="{{ (int) $cid }}" role="radio" tabindex="0" title="{{ $clabel }}">
                                            @if(!empty($colorThumbs[$cid]))
                                                <img class="pdx-opt-img" src="{{ $colorThumbs[$cid] }}" alt="" loading="lazy">
                                            @elseif(!empty($colorHex[$cid]))
                                                <span class="pdx-opt-swatch" style="background: {{ $colorHex[$cid] }};"></span>
                                            @endif
                                            <span class="pdx-opt-name">{{ $clabel }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if($showSize)
                            <div>
                                <span class="pdx-vlabel">Size: <span class="pd-variant-picked" id="pdPickedSize">{{ $sizesMap[$defaultSizeId] ?? '' }}</span></span>
                                <div class="pdx-opts" id="sizeOptions" role="radiogroup" aria-label="Size">
                                    @foreach($sizesMap as $sid => $slabel)
                                        <div class="pdx-opt size-opt {{ (int) $sid === (int) $defaultSizeId ? 'active' : '' }}"
                                             data-size-id="{{ (int) $sid }}" role="radio" tabindex="0">
                                            <span class="pdx-opt-name">{{ $slabel }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <span class="size_name d-none"></span>
                    </div>
                @endif

                <div class="pdx-qty-row">
                    <div>
                        <span class="pdx-qty-label">Quantity (Pcs)</span>
                        <div class="pdx-qty">
                            <button type="button" class="pdx-qty-minus" aria-label="Decrease quantity">&minus;</button>
                            <input type="number" name="quantity" value="{{ $minOrderQty }}" min="{{ $minOrderQty }}" readonly aria-label="Quantity">
                            <button type="button" class="pdx-qty-plus" aria-label="Increase quantity">+</button>
                        </div>
                    </div>
                    @if($isWholesale)
                        <div class="pdx-qty-note">
                            <i class="fas fa-shield-alt"></i>
                            <div><strong>Minimum order {{ $minOrderQty }} pcs{{ $perVariant }}</strong>Wholesale orders only.</div>
                        </div>
                    @endif
                </div>

                <template id="orderNowLabel">@if(($singleProduct->is_free_shipping ?? 0) == 1)<i class="fas fa-shipping-fast"></i> {{ $bangla_text->fshipping_text ?? 'Free Shipping' }}@else<i class="fas fa-bolt"></i> {{ $dt->order_now_text ?? 'Order Now' }}@endif</template>
                <div class="pdx-actions">
                    <button type="submit" class="pdx-btn is-outline pdx-cart-btn" {{ $inStock ? '' : 'disabled' }}>
                        <i class="fas fa-shopping-cart"></i> <span>Add to Cart</span>
                    </button>
                    <button type="submit" class="pdx-btn is-solid pdx-order-btn" {{ $inStock ? '' : 'disabled' }}>
                        @if(!$inStock)
                            Out of Stock
                        @elseif(($singleProduct->is_free_shipping ?? 0) == 1)
                            <i class="fas fa-shipping-fast"></i> {{ $bangla_text->fshipping_text ?? 'Free Shipping' }}
                        @else
                            <i class="fas fa-bolt"></i> {{ $dt->order_now_text ?? 'Order Now' }}
                        @endif
                    </button>
                    @php
                        $hasWa   = !empty($info->whats_num) && ($info->whats_active ?? 0) == 1;
                        $hasCall = !empty($info->owner_phone);
                    @endphp
                    @if($hasWa)
                        <a href="https://wa.me/+88{{ $info->whats_num }}?text={{ urlencode($singleProduct->name.' - এই পণ্যটি সম্পর্কে জানতে চাই।') }}"
                           target="_blank" rel="noopener" class="pdx-btn is-outline is-wa {{ $hasCall ? '' : 'is-span' }}">
                            <i class="fab fa-whatsapp"></i> <span>WhatsApp Order</span>
                        </a>
                    @endif
                    @if($hasCall)
                        <a href="tel:{{ $info->owner_phone }}" class="pdx-btn is-outline is-dark {{ $hasWa ? '' : 'is-span' }}">
                            <i class="fas fa-phone-alt"></i> <span>Call Now</span>
                        </a>
                    @endif
                </div>

                <div class="notify-me-wrap" id="notifyMeWrap" style="{{ $inStock ? 'display:none;' : '' }}">
                    <button type="button" class="notify-me-btn" id="notifyMeOpen">
                        <i class="far fa-bell"></i> Notify Me When Available
                    </button>
                </div>
            </form>

            {{-- Delivery charge: the same areas and amounts the checkout offers --}}
            @php
                $shipCharges = $charges->whereNotNull('status')->values();
                $isFreeShip  = ($singleProduct->is_free_shipping ?? 0) == 1;
                $zoneCount   = $shipCharges->countBy('zone');
                $zoneLabel   = ['inside' => 'Inside Dhaka', 'outside' => 'Outside Dhaka'];
                // weight based mode: checkout works the charge out from the parcel weight instead
                $shipByWeight = optional($charges->first())->charge_type === 'weight_based' && (float) ($singleProduct->weight ?? 0) > 0;
            @endphp
            @if($shipCharges->isNotEmpty())
                <div class="pdx-delivery">
                    <div class="pdx-delivery-head"><i class="fas fa-truck"></i> Delivery Charge</div>
                    <div class="pdx-delivery-grid">
                        @foreach($shipCharges as $charge)
                            <div class="pdx-delivery-item">
                                <i class="fas {{ $charge->zone === 'inside' ? 'fa-map-marker-alt' : 'fa-map-marked-alt' }}"></i>
                                <div>
                                    <small>{{ ($zoneCount[$charge->zone] ?? 0) === 1 && isset($zoneLabel[$charge->zone]) ? $zoneLabel[$charge->zone] : $charge->title }}</small>
                                    @if($isFreeShip)
                                        <strong class="is-free">Free</strong>
                                    @else
                                        <strong>{{ biz_format_currency($charge->amount) }}</strong>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($shipByWeight && !$isFreeShip)
                        <p class="pdx-delivery-note">The final charge depends on the parcel weight and is shown at checkout.</p>
                    @endif
                </div>
            @endif

            <ul class="pdx-trust">
                <li><i class="fas fa-shipping-fast"></i><div><strong>Direct Import</strong><small>China | India | Dubai</small></div></li>
                <li><i class="fas fa-box-open"></i><div><strong>Wide Range</strong><small>Wholesale Products</small></div></li>
                <li><i class="fas fa-users"></i><div><strong>Best Wholesale Price</strong><small>For Resellers</small></div></li>
                <li><i class="fas fa-shield-alt"></i><div><strong>Genuine Quality</strong><small>Trusted Supplier</small></div></li>
            </ul>

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
        </div>
    </div>

    {{-- ===== TABS + RELATED ===== --}}
    <div class="pdx-bottom">
        <div class="pdx-card">
            <ul class="nav pdx-tabs" id="myTab" role="tablist">
                <li role="presentation"><a class="nav-link active" id="home-tab" data-bs-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">{{ $dt->details_tab_text ?? 'Product Details' }}</a></li>
                <li role="presentation"><a class="nav-link" id="shipping-tab" data-bs-toggle="tab" href="#shipping" role="tab" aria-controls="shipping" aria-selected="false">Shipping &amp; Delivery</a></li>
                <li role="presentation"><a class="nav-link" id="review-tab" data-bs-toggle="tab" href="#review" role="tab" aria-controls="review" aria-selected="false">{{ $dt->reviews_tab_text ?? 'Reviews' }} <span class="pd-tab-count">({{ $totalReviews }})</span></a></li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active pdx-pane" id="home" role="tabpanel" aria-labelledby="home-tab">
                    <h3 class="pdx-pane-title">{{ $dt->short_description_text ?? 'Product Details' }}</h3>
                    <div class="single-desc desc-collapse-wrapper" id="descWrapper">
                        {!! $singleProduct->body !!}
                    </div>
                    <button type="button" class="view-more-btn" id="viewMoreBtn" style="display: none;">View More <i class="fas fa-chevron-down ms-1"></i></button>
                    @if(!empty(trim(strip_tags((string) $singleProduct->feature))))
                        <div class="pdx-features">{!! $singleProduct->feature !!}</div>
                    @endif
                </div>

                <div class="tab-pane fade pdx-pane" id="shipping" role="tabpanel" aria-labelledby="shipping-tab">
                    <h3 class="pdx-pane-title">Shipping &amp; Delivery</h3>
                    @if(($singleProduct->is_free_shipping ?? 0) == 0)
                        <div class="courier-card">
                            <div class="courier-title"><i class="fas fa-truck"></i> {{ $dt->courier_delivery_cost_text ?? 'Delivery Cost' }}</div>
                            <table class="table table-bordered border-0">
                                <tbody>
                                    @foreach($charges as $charge)
                                        <tr><td>{{ $charge->title }}</td><td>{{ biz_format_currency($charge->amount) }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="courier-card">
                            <div class="courier-title"><i class="fas fa-truck"></i> {{ $dt->courier_delivery_cost_text ?? 'Delivery Cost' }}</div>
                            <div class="text-center text-success" style="padding: 14px; font-weight: 800; font-size: 16px;">
                                <i class="fas fa-shipping-fast"></i> {{ $bangla_text->fshipping_text ?? 'Free Shipping' }}
                            </div>
                        </div>
                    @endif
                    <ul class="pdx-ship-list">
                        @if($isWholesale)<li><i class="fas fa-boxes"></i><span>Minimum order is {{ $minOrderQty }} pcs{{ $perVariant }}.</span></li>@endif
                        <li><i class="fas fa-shipping-fast"></i><span>We deliver all over Bangladesh through trusted courier partners.</span></li>
                        <li><i class="fas fa-headset"></i><span>Questions about delivery? Call{{ !empty($info->owner_phone) ? ' '.$info->owner_phone : ' us' }} and we will help.</span></li>
                    </ul>
                </div>

                <div class="tab-pane fade pdx-pane" id="review" role="tabpanel" aria-labelledby="review-tab">
                    <div id="writeReview">
                        <div class="reviews-wrapper">
                            <div class="row">
                                <div class="col-lg-6 mb--20">
                                    <div class="axil-comment-area pro-desc-commnet-area">
                                        <h3 class="pdx-pane-title">Customer Reviews ({{ $totalReviews }})</h3>
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
                                                        <div class="status-msg"><input class="rating_msg" type="hidden" name="rating_msg" value="" /></div>
                                                        <div class="stars-box">
                                                            <i class="star fa fa-star" title="1 star" data-message="Poor" data-value="1"></i>
                                                            <i class="star fa fa-star" title="2 stars" data-message="Too bad" data-value="2"></i>
                                                            <i class="star fa fa-star" title="3 stars" data-message="Average quality" data-value="3"></i>
                                                            <i class="star fa fa-star" title="4 stars" data-message="Nice" data-value="4"></i>
                                                            <i class="star fa fa-star" title="5 stars" data-message="Very good quality" data-value="5"></i>
                                                        </div>
                                                        <div class="starrate"><input class="ratevalue" type="hidden" name="rate_value" value="" /></div>
                                                    </div>
                                                    <div class="feedback-tags">
                                                        <div class="tags-container" data-tag-set="1"><div class="question-tag">Why was your experience so bad?</div></div>
                                                        <div class="tags-container" data-tag-set="2"><div class="question-tag">Why was your experience so bad?</div></div>
                                                        <div class="tags-container" data-tag-set="3"><div class="question-tag">Why was your average rating experience?</div></div>
                                                        <div class="tags-container" data-tag-set="4"><div class="question-tag">Why was your experience good?</div></div>
                                                        <div class="tags-container" data-tag-set="5">
                                                            <div class="make-compliment"><div class="compliment-container"><span class="compliment-text">Give a compliment</span><i class="fas fa-smile-wink"></i></div></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 col-12 mb-3">
                                                        <div class="premium-input-group">
                                                            <label>Name <span class="text-danger">*</span></label>
                                                            <input id="name" type="text" name="name" required placeholder="Your Name" value="{{ auth()->user()->name ?? '' }}"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-12 mb-3">
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
                                                    <div class="col-12">
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

        @if($products->isNotEmpty())
        <aside class="pdx-card pdx-related" aria-label="Related products">
            <div class="pdx-related-head">
                <h2>Related Products</h2>
                <a href="{{ $relatedUrl }}">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="pdx-related-grid" id="relative_data">
                @foreach($products->take(6) as $product)
                    @include('frontend.products.partials.product_section')
                @endforeach
            </div>
        </aside>
        @endif
    </div>

</div>
</main>

{{-- ===== Full-screen picture viewer ===== --}}
<div class="pdx-lightbox" id="pdxLightbox" role="dialog" aria-modal="true" aria-label="Product pictures" aria-hidden="true">
    <span class="pdx-lb-count" id="pdxLbCount"></span>
    <button type="button" class="pdx-lb-btn pdx-lb-close" aria-label="Close"><i class="fas fa-times"></i></button>
    <button type="button" class="pdx-lb-btn pdx-lb-prev" aria-label="Previous picture"><i class="fas fa-chevron-left"></i></button>
    <button type="button" class="pdx-lb-btn pdx-lb-next" aria-label="Next picture"><i class="fas fa-chevron-right"></i></button>
    <div class="pdx-lb-img" id="pdxLbStage"><img src="" alt="{{ $singleProduct->name }}" draggable="false"></div>
</div>
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
    const qtyWrap = document.querySelector('.pdx-qty');
    if(qtyWrap && !qtyWrap.dataset.bound){
      qtyWrap.dataset.bound = "1";
      const minus = qtyWrap.querySelector('.pdx-qty-minus');
      const plus  = qtyWrap.querySelector('.pdx-qty-plus');
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
    .off('click.cartaction', '.pdx-cart-btn, .pdx-order-btn')
    .on('click.cartaction', '.pdx-cart-btn, .pdx-order-btn', function(){
      if($(this).hasClass('pdx-cart-btn')) $('#input_action_type').val('cart');
      else if($(this).hasClass('pdx-order-btn')) $('#input_action_type').val('order');

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
  // "Stock Available" value in the price box: pieces left, orange when low, red when out.
  function renderStock(stock){
    const el = document.getElementById('stock-text-element');
    if(!el) return;
    el.classList.toggle('is-out', stock <= 0);
    el.classList.toggle('is-low', stock > 0 && stock <= {{ $lowStockLimit }});
    el.textContent = stock > 0 ? stock + ' Pcs' : 'Out of stock';
  }

  function resolveVariation(sizeId, colorId){
    const key = String(sizeId) + '|' + String(colorId);
    return (window.__VAR_MAP__ && window.__VAR_MAP__[key]) ? window.__VAR_MAP__[key] : null;
  }

  function flashPrice(){
    const $p = $('#pdxPriceNow');
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

    $('#pdxPriceNow').text(moneyText(v.price));
    if(!isFirstLoad) flashPrice();

    if (v.raw > v.price && v.raw > 0) {
      $('#product-old-price').show().text(moneyText(v.raw));
      $('#pdSaveBadge').text(offText(v.raw - v.price)).show();
      $('#pdxOffBadge').text(Math.round((v.raw - v.price) / v.raw * 100) + '% Off').show();
    } else {
      $('#product-old-price').hide();
      $('#pdSaveBadge').hide();
      $('#pdxOffBadge').hide();
    }

    if (v.image) {
      $('#main-image, #thumb-image, #video-thumb-image').attr('src', v.image);
    } else {
      let defaultImg = "{{ getImage('products', $singleProduct->image) }}";
      $('#main-image, #thumb-image, #video-thumb-image').attr('src', defaultImg);
    }

    // only jump the gallery back when the variant has its own picture
    if (!isFirstLoad && v.image && window.pdxGallery) window.pdxGallery.showFirstImage();

    $('#variation_id').val(v.id);
    $('#variant_name').val(label);
    $('#size_value').val(label);
    if($('#size_value1').length) $('#size_value1').val(label);
    $('#price_val').val(v.price);
    if($('#price_val1').length) $('#price_val1').val(v.price);

    const stock = parseInt(v.stock || 0);
    renderStock(window.__PRODUCT_OUT__ ? 0 : stock);

    setStockState(stock <= 0 || window.__PRODUCT_OUT__);
    if(window.toggleNotifyMe) window.toggleNotifyMe(stock <= 0 || window.__PRODUCT_OUT__);
    markOutOfStockOptions();
  }

  // Selected variant out of stock → same look as a stock-out product:
  // image badge, "Out of Stock" button, everything disabled.
  // On small screens the .is-stock-out class swaps quantity + buy buttons for Notify Me.
  function setStockState(isOut){
    $('.pdx-cart-btn, .pdx-order-btn').prop('disabled', isOut);
    $('#pdStockOutOverlay, #pdThumbStockOut').toggle(isOut);
    $('#pdxTop').toggleClass('is-stock-out', isOut);
    $('.pdx-qty').toggleClass('is-disabled', isOut);
    if(isOut) $('.pdx-qty input[name="quantity"]').val({{ $minOrderQty }});

    const $order = $('.pdx-order-btn');
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
    
    renderStock(0);
    
    $('.pdx-cart-btn, .pdx-order-btn').prop('disabled', true);
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

  $(document).off('keydown.optpick').on('keydown.optpick', '.pdx-opt', function(e){
    if(e.key === 'Enter' || e.key === ' '){ e.preventDefault(); $(this).trigger(this.classList.contains('size-opt') ? 'click.sizepick' : 'click.colorpick'); }
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

// Gallery: thumbnails, arrows, swipe, hover zoom (mouse) and a full-screen viewer
// where a click / tap zooms in and the pointer pans around the picture.
(function(){
  const stage = document.getElementById('pdxStage');
  if(!stage) return;
  const slides = Array.prototype.slice.call(stage.querySelectorAll('.pdx-slide'));
  const thumbs = Array.prototype.slice.call(document.querySelectorAll('#pdxGallery .pdx-thumb'));
  const dots   = Array.prototype.slice.call(stage.querySelectorAll('.pdx-dots span'));
  const rail   = document.querySelector('#pdxGallery .pdx-thumbs');
  const isVideo = s => s.classList.contains('is-video');
  const firstImage = Math.max(0, slides.findIndex(s => !isVideo(s)));
  let cur = Math.max(0, slides.findIndex(s => s.classList.contains('is-active')));
  if(slides.length < 2) stage.querySelectorAll('.pdx-nav-btn, .pdx-dots').forEach(el => el.style.display = 'none');
  const gallery = document.getElementById('pdxGallery');
  function fitRail(){ gallery.style.setProperty('--pdx-stage-h', stage.offsetHeight + 'px'); }
  fitRail();
  window.addEventListener('resize', fitRail);

  function unzoom(){
    slides.forEach(s => s.classList.remove('is-zooming'));
    stage.classList.remove('is-zoomed-in');
  }
  function keepThumbVisible(t){
    if(!t || !rail) return;
    const horizontal = rail.scrollWidth > rail.clientWidth;
    if(horizontal) rail.scrollTo({ left: t.offsetLeft - (rail.clientWidth - t.offsetWidth) / 2, behavior: 'smooth' });
    else rail.scrollTo({ top: t.offsetTop - (rail.clientHeight - t.offsetHeight) / 2, behavior: 'smooth' });
  }
  function show(i){
    i = (i + slides.length) % slides.length;
    if(i === cur) return;
    unzoom();
    const leaving = slides[cur];
    if(isVideo(leaving)){ const f = leaving.querySelector('iframe'); if(f) f.src = f.src; }   // stop playback
    leaving.classList.remove('is-active');
    slides[i].classList.add('is-active');
    thumbs.forEach((t, k) => t.classList.toggle('is-active', k === i));
    dots.forEach((d, k) => d.classList.toggle('is-active', k === i));
    cur = i;
    keepThumbVisible(thumbs[i]);
  }

  thumbs.forEach((t, k) => t.addEventListener('click', () => show(k)));
  stage.querySelector('.pdx-nav-btn.is-prev').addEventListener('click', () => show(cur - 1));
  stage.querySelector('.pdx-nav-btn.is-next').addEventListener('click', () => show(cur + 1));

  // hover zoom: the picture enlarges inside its frame and follows the cursor
  const finePointer = window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)');
  stage.addEventListener('mousemove', function(e){
    if(!finePointer || !finePointer.matches) return;
    const s = slides[cur];
    if(isVideo(s) || e.target.closest('.pdx-stage-btn')){ unzoom(); return; }
    const img = s.querySelector('img');
    const r = img.getBoundingClientRect();
    img.style.transformOrigin = ((e.clientX - r.left) / r.width * 100) + '% ' + ((e.clientY - r.top) / r.height * 100) + '%';
    s.classList.add('is-zooming');
    stage.classList.add('is-zoomed-in');
  });
  stage.addEventListener('mouseleave', unzoom);

  // swipe between pictures on touch screens
  let sx = null, sy = null, swiped = false;
  stage.addEventListener('pointerdown', e => { if(e.pointerType !== 'mouse'){ sx = e.clientX; sy = e.clientY; swiped = false; } });
  stage.addEventListener('pointerup', e => {
    if(sx === null) return;
    const dx = e.clientX - sx, dy = e.clientY - sy;
    if(Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)){ swiped = true; show(cur + (dx < 0 ? 1 : -1)); }
    sx = sy = null;
  });
  stage.addEventListener('pointercancel', () => { sx = sy = null; });

  // ---------- full-screen viewer ----------
  const lb = document.getElementById('pdxLightbox');
  const lbStage = document.getElementById('pdxLbStage');
  const lbImg = lbStage.querySelector('img');
  const lbCount = document.getElementById('pdxLbCount');
  let items = [], li = 0, lastFocus = null;

  function lbRender(){
    lbStage.classList.remove('is-zoomed');
    lbImg.style.transformOrigin = '50% 50%';
    lbImg.src = items[li];
    lbCount.textContent = items.length > 1 ? (li + 1) + ' / ' + items.length : '';
    lb.querySelectorAll('.pdx-lb-prev, .pdx-lb-next').forEach(b => b.style.display = items.length > 1 ? '' : 'none');
  }
  function lbOpen(slideIndex){
    const imageSlides = slides.filter(s => !isVideo(s));
    if(!imageSlides.length) return;
    items = imageSlides.map(s => s.querySelector('img').src);   // read now: a variant may have swapped the first picture
    li = Math.max(0, imageSlides.indexOf(slides[slideIndex]));
    lbRender();
    lastFocus = document.activeElement;
    lb.classList.add('is-open');
    lb.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    lb.querySelector('.pdx-lb-close').focus();
  }
  function lbClose(){
    lb.classList.remove('is-open');
    lb.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    if(lastFocus) lastFocus.focus();
  }
  function lbGo(step){ if(items.length > 1){ li = (li + step + items.length) % items.length; lbRender(); } }

  stage.addEventListener('click', function(e){
    if(e.target.closest('.pdx-stage-btn') || swiped || isVideo(slides[cur])) return;
    lbOpen(cur);
  });
  stage.querySelector('.pdx-zoom-btn').addEventListener('click', () => lbOpen(isVideo(slides[cur]) ? firstImage : cur));
  lb.querySelector('.pdx-lb-close').addEventListener('click', lbClose);
  lb.querySelector('.pdx-lb-prev').addEventListener('click', () => lbGo(-1));
  lb.querySelector('.pdx-lb-next').addEventListener('click', () => lbGo(1));
  lbStage.addEventListener('click', function(e){
    if(e.target !== lbImg){ lbClose(); return; }               // click on the dark area closes
    if(lbMoved) return;
    const zoom = !lbStage.classList.contains('is-zoomed');
    lbStage.classList.toggle('is-zoomed', zoom);
    if(zoom) panTo(e);
  });
  function panTo(e){
    const r = lbImg.getBoundingClientRect();
    const x = Math.min(100, Math.max(0, (e.clientX - r.left) / r.width * 100));
    const y = Math.min(100, Math.max(0, (e.clientY - r.top) / r.height * 100));
    lbImg.style.transformOrigin = x + '% ' + y + '%';
  }
  // zoomed: the pointer (mouse or finger) pans; not zoomed: swipe changes picture
  let lx = null, ly = null, lbMoved = false;
  lbStage.addEventListener('pointerdown', e => { lx = e.clientX; ly = e.clientY; lbMoved = false; });
  lbStage.addEventListener('pointermove', e => {
    if(lbStage.classList.contains('is-zoomed') && (e.pointerType === 'mouse' || lx !== null)) panTo(e);
    if(lx !== null && Math.hypot(e.clientX - lx, e.clientY - ly) > 8) lbMoved = true;
  });
  lbStage.addEventListener('pointerup', e => {
    if(lx !== null && !lbStage.classList.contains('is-zoomed')){
      const dx = e.clientX - lx, dy = e.clientY - ly;
      if(Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) lbGo(dx < 0 ? 1 : -1);
    }
    lx = ly = null;
    setTimeout(() => { lbMoved = false; }, 0);
  });
  document.addEventListener('keydown', function(e){
    if(!lb.classList.contains('is-open')) return;
    if(e.key === 'Escape') lbClose();
    else if(e.key === 'ArrowLeft') lbGo(-1);
    else if(e.key === 'ArrowRight') lbGo(1);
  });

  // used by applyVariation(): bring the (swapped) first picture into view
  window.pdxGallery = { showFirstImage: function(){ show(firstImage); } };
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
  const realCart   = document.querySelector('.pdx-cart-btn');
  const realOrder  = document.querySelector('.pdx-order-btn');
  const notifyWrap = document.getElementById('notifyMeWrap');
  const priceBox   = document.getElementById('pdxPriceBox');
  const barCart = document.getElementById('pdBarCart'), barBuy = document.getElementById('pdBarBuy');

  function sync(){
    const now = document.getElementById('pdxPriceNow');
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
