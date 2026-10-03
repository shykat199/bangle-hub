<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $ln_pg->title1 ?? 'Premium Product Landing Page' }}</title>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    @php
        $information    = \App\Models\Information::first();

        $brandGradient  = $ln_pg->theme_gradient_col ?? (optional($information)->gradient_code ?? 'linear-gradient(90deg,#0d6efd,#00276C)');
        $brandSolid     = $ln_pg->theme_primary_col ?? (optional($information)->primary_color ?? '#00276C');

        $btnBgColor     = $ln_pg->btn_bg_color ?? $brandSolid;
        $btnTextColor   = $ln_pg->btn_text_color ?? '#ffffff';

        $txtHero        = $ln_pg->order_btn_text ?: 'অর্ডার করতে ক্লিক করুন';
        $txtVideo       = $ln_pg->btn_text_video ?: 'অর্ডার করতে চাই';
        $txtFeature     = $ln_pg->btn_text_feature ?: 'অর্ডার করুন';
        $txtForm        = $ln_pg->btn_text_form ?: 'অর্ডার কনফার্ম করুন';

        $pageTitle      = $title ?? ($ln_pg->title1 ?? 'Landing Page');

        $product    = $ln_pg->product ?? null;

        $variations = collect();
        if($product){
            try{
                $product->loadMissing(['variations.size','variations.color', 'variations.stocks', 'category']);
                $variations = $product->variations ?? collect();
            }catch(\Throwable $e){
                $variations = $product->variations ?? collect();
            }
        }

        $defaultVar     = $variations->first();
        $defaultVarId   = $defaultVar->id ?? null;

        $defaultBase = $defaultVar->price ?? null;
        $defaultDisc = $defaultVar->after_discount_price ?? null;

        $defaultPrice = null;
        if($defaultDisc !== null && $defaultDisc !== '' && (float)$defaultDisc > 0){
            $defaultPrice = $defaultDisc;
        }elseif($defaultBase !== null && $defaultBase !== '' && (float)$defaultBase > 0){
            $defaultPrice = $defaultBase;
        }else{
            $defaultPrice = ($product && (float)($product->after_discount ?? 0) > 0)
                ? $product->after_discount
                : ($product->sell_price ?? 0);
        }

        $defaultStock = 0;
        if($defaultVar) {
            $defaultStock = $defaultVar->stocks->sum('quantity');
        } elseif($product) {
            $defaultStock = $product ? max((int) ($product->stock_quantity ?? 0), (int) $product->stocks()->sum('quantity')) : 0;
        }

        $pixelId = setting('fb_pixel_id') ?? null;
        $contentCategory = $product?->category?->name ?? 'Landing Page';
        $productId   = $product->id ?? 0;
        $productName = $product->name ?? '';

        $globalSetting = DB::table('delivery_charges')->first();
        $isWeightBased = $globalSetting && $globalSetting->charge_type == 'weight_based';
        
        $isFreeShipping = (!empty($product->is_free_shipping) && $product->is_free_shipping == 1) ? 1 : 0;

        $ttPixelId = setting('tt_pixel_id') ?? null;

        // Browser `value` has to match the payload the server already pushed to the
        // Meta CAPI / TikTok Events API under the same event id, i.e. the price this
        // page shows by default: the default package, else new_price, else the
        // product price computed above. See landingDisplayPrice() in ProductController.
        $lpPackages   = $ln_pg->packages ?? collect();
        $lpDefaultPkg = $lpPackages->firstWhere('is_default', 1) ?: $lpPackages->first();
        $lpTrackValue = (float) (($lpDefaultPkg && (float) $lpDefaultPkg->price > 0)
            ? $lpDefaultPkg->price
            : (!empty($ln_pg->new_price) ? $ln_pg->new_price : $defaultPrice));
    @endphp

    <style>
        :root{
            --brand-gradient: {!! $brandGradient !!};
            --brand-solid: {{ $brandSolid }};
            --btn-bg: {{ $btnBgColor }};
            --btn-text: {{ $btnTextColor }};
            
            --bg: #f6f8ff;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --border: rgba(15,23,42,.10);
            --radius-xl: 26px;
            --radius: 18px;
            --shadow: 0 18px 45px rgba(2,6,23,.14);
            --shadow-soft: 0 14px 30px rgba(2,6,23,.08);
        }

        html,body{ width:100%; overflow-x:hidden; }
        body{
            font-family:'Hind Siliguri', sans-serif !important;
            background: var(--bg);
            color: var(--text);
        }

        .container-fluid-landing{ width: 100%; max-width: 1160px; margin: 0 auto; padding: 0 14px; }
        .container-premium{ max-width:1160px; margin:0 auto; padding: 0 14px; }

        .top-div{
            width:100%; margin:0; position:relative; background: var(--brand-gradient);
            display:flex; align-items:center; justify-content:center;
            min-height: 340px; padding: 26px 0; overflow:hidden;
            border-bottom-left-radius: 32px; border-bottom-right-radius: 32px;
        }
        .hero-inner{
            width:100%; max-width: 980px; margin: 0 auto; padding: 22px 18px;
            text-align:center; background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.22); border-radius: var(--radius-xl);
            box-shadow: 0 18px 55px rgba(0,0,0,.25); backdrop-filter: blur(8px);
            position:relative; z-index:2;
        }
        .hero-title{ margin:0; color:#fff; font-weight:900; line-height:1.2; font-size: clamp(22px, 3.0vw, 44px); text-shadow: 0 10px 22px rgba(0,0,0,.22); }
        .hero-sub{ margin-top:10px; color: rgba(255,255,255,.92); font-weight: 700; font-size: 16px; }
        .hero-actions{ margin-top: 16px; display:flex; gap:12px; justify-content:center; flex-wrap:wrap; align-items:center; }

        .btn-primary-brand{
            background: var(--btn-bg) !important; color: var(--btn-text) !important;
            border: 0 !important; font-weight: 900 !important; border-radius: 999px !important;
            padding: 12px 18px !important; box-shadow: 0 14px 30px rgba(2,6,23,.18);
            letter-spacing: .2px; transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
        }
        .btn-primary-brand:hover{ transform: translateY(-1px); filter: brightness(1.1); box-shadow: 0 18px 40px rgba(2,6,23,.22); }

        .section-title{ background: var(--brand-solid) !important; color: var(--btn-text) !important; border-radius: 999px; padding: 12px 16px; text-align:center; font-weight:900; font-size: 22px; box-shadow: 0 14px 30px rgba(2,6,23,.10); margin: 0 auto 14px; display:inline-block; text-shadow: 0 1px 2px rgba(0,0,0,0.1); }

        .cardx{ background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-xl); box-shadow: var(--shadow-soft); overflow:hidden; }
        .cardx-pad{ padding: 16px; }

        .top_section_img{ width:100%; border-radius: var(--radius-xl); box-shadow: var(--shadow-soft); border: 1px solid rgba(255,255,255,.35); }

        .video-16x9{ position: relative; width: 100%; padding-top: 56.25%; border-radius: var(--radius-xl); overflow: hidden; background: #000; box-shadow: var(--shadow-soft); }
        .video-16x9 iframe, .video-16x9 video{ position:absolute; inset:0; width:100% !important; height:100% !important; border:0; }

        .call-box{ background: linear-gradient(135deg, rgba(255,255,255,.96), rgba(255,255,255,.92)); border: 1px solid var(--border); border-radius: var(--radius-xl); text-align:center; padding: 16px 18px; box-shadow: var(--shadow-soft); }
        .call-pill{ display:inline-flex; gap:10px; align-items:center; padding: 10px 14px; border-radius: 999px; background: rgba(2,6,23,.04); border: 1px solid rgba(2,6,23,.08); font-weight: 900; }
        .call-pill a{ text-decoration:none; color: var(--brand-solid); }

        .form-wrapper{ background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-xl); padding: 18px; box-shadow: var(--shadow-soft); }
        .form-control{ border-radius: 14px !important; border: 1px solid rgba(0,39,108,.22) !important; box-shadow: 0 10px 24px rgba(0,39,108,.06); min-height: 48px; }
        .form-control:focus{ border-color: rgba(0,39,108,.45) !important; box-shadow: 0 14px 28px rgba(0,39,108,.12) !important; outline: none !important; }
        .form-select { border-radius: 14px !important; border: 1px solid var(--brand-solid) !important; padding: 12px; font-weight: 700; background-color: #fcfcfc; }

        @media (min-width: 992px){ .order-sticky{ position: sticky; top: 18px; } }

        /* ======== Table Responsive Fixes ======== */
        .review-order-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .review-order-table th.product-name { width: 60%; }
        .review-order-table th.product-total { width: 40%; text-align: right; }
        .review-order-table td, .review-order-table th { 
            padding: 12px 0; vertical-align: middle; 
            word-wrap: break-word; font-weight: 800; color: var(--text);
        }
        
        .product-image { display: flex; align-items: center; gap: 12px; }
        .product-thumbnail { 
            width: 55px; height: 55px; border-radius: 12px; overflow: hidden; 
            border: 1px solid var(--border); flex-shrink: 0; 
        }
        .product-thumbnail img{ width:100%; height:100%; object-fit:cover; }
        .product-name-td { font-size: 15px; font-weight: 700; line-height: 1.3; }
        /* ======================================== */
        
        .pro-qty{ display:flex; align-items:center; gap:8px; }
        .quantity-button{ width:38px; height:38px; border-radius: 12px; display:flex; align-items:center; justify-content:center; background: rgba(2,6,23,.06); border: 1px solid rgba(2,6,23,.10); cursor:pointer; font-weight: 900; user-select:none; }
        .inner_qty{ width:68px !important; height:38px; border-radius: 12px; border: 1px solid rgba(2,6,23,.12); text-align:center; font-weight: 900; box-shadow: 0 10px 24px rgba(2,6,23,.05); }

        .place-order .button.btn-primary-brand{ width:100% !important; padding: 16px 18px !important; border-radius: 22px !important; font-size: 20px !important; line-height: 1.1; }

        .whats_btn{ position: fixed; right: 16px; bottom: 16px; z-index: 9999; width: 54px; height: 54px; border-radius: 999px; background: #25D366; display:flex; align-items:center; justify-content:center; box-shadow: 0 10px 25px rgba(0,0,0,.25); text-decoration:none; }
        .whats_btn img{ width: 28px; height: 28px; }

        .scrollTopBtn{ position: fixed; right: 16px; bottom: 84px; z-index: 9999; width: 48px; height: 48px; border-radius: 999px; background: var(--brand-solid); color: var(--btn-text); border:0; display:none; align-items:center; justify-content:center; box-shadow: 0 10px 25px rgba(0,0,0,.20); cursor:pointer; }

        .payment-label { cursor: pointer; transition: all 0.2s; border: 1px solid rgba(0,0,0,0.1); }
        .payment-label:hover { background-color: #f8f9fa !important; border-color: rgba(0,0,0,0.2); }
        input[name="payment_method"]:checked + span { color: var(--brand-solid); }
        input[name="payment_method"]:checked ~ .payment-label { border-color: var(--brand-solid) !important; background-color: rgba(0, 39, 108, 0.05) !important; }

        .feature-content ul { list-style: none; padding-left: 0; }
        .feature-content ul li { padding: 8px 0; border-bottom: 1px dashed #e2e8f0; font-size: 16px; font-weight: 600; color: #334155; display: flex; align-items: center; gap: 10px; }
        .feature-content ul li:last-child { border-bottom: none; }
        .feature-content ul li i { color: #10b981; font-size: 18px; }

        #manual_payment_area { background: #f8fafc; border: 1px dashed #cbd5e1; padding: 15px; margin-top: 15px; border-radius: 8px; }
        .manual-instruction-box { background: rgba(226, 19, 110, 0.08); border: 1px solid rgba(226, 19, 110, 0.2); color: #C90D5E; padding: 10px; border-radius: 6px; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
        .input-icon-wrap { position: relative; }
        .input-icon-wrap i { position: absolute; top: 50%; left: 16px; transform: translateY(-50%); color: var(--muted); z-index: 10; font-size: 15px; }
        .input-icon-wrap input { padding-left: 45px !important; }

        /* ======== Package Design CSS ======== */
        .product-package-card {
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 15px; border: 1px solid var(--border);
            border-radius: 12px; background: #fff; margin-bottom: 12px; 
            cursor: pointer; transition: all 0.3s ease;
        }
        /* Flex constraints for responsive text wrapping */
        .product-package-card > div:first-child { flex: 1; min-width: 0; padding-right: 10px; }
        
        .product-package-card.active-pkg {
            border-color: var(--brand-solid); 
            background: rgba(0, 39, 108, 0.03);
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .pkg-radio { 
            width: 18px; height: 18px; accent-color: var(--brand-solid); 
            cursor: pointer; margin-top: 2px; flex-shrink: 0;
        }
        .pkg-title { 
            display: block; font-weight: 700; font-size: 15px; 
            color: var(--text); word-wrap: break-word; white-space: normal; line-height: 1.3;
        }
        .pkg-price { font-weight: 900; font-size: 16px; color: var(--brand-solid); white-space: nowrap; }
        .pkg-qty-box { 
            background: rgba(2,6,23,.04); padding: 4px 8px; 
            border-radius: 8px; border: 1px solid rgba(2,6,23,.08); 
        }
        .pkg-qty-input { 
            width: 25px; text-align: center; border: none; 
            background: transparent; font-weight: bold; font-size: 14px; 
            outline: none; pointer-events: none;
        }

        /* ======== Premium Coupon Box Design ======== */
        .coupon-box-wrapper {
            background: rgba(0, 39, 108, 0.03); border: 1px dashed rgba(0, 39, 108, 0.3);
            border-radius: 12px; padding: 16px; margin-top: 5px;
        }
        .coupon-input-group {
            display: flex; align-items: center; background: #fff;
            border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02); transition: all 0.3s ease; height: 45px;
        }
        .coupon-input-group:focus-within {
            border-color: var(--brand-solid); box-shadow: 0 0 0 3px rgba(0, 39, 108, 0.1);
        }
        .coupon-input-group .icon { padding: 0 12px; color: var(--brand-solid); font-size: 16px; }
        .coupon-input-group input {
            flex: 1; border: none; padding: 0; height: 100%; outline: none;
            font-weight: 600; color: #334155; font-size: 15px; background: transparent; min-width: 0;
        }
        .coupon-input-group button {
            background: var(--brand-solid); color: #fff; border: none; padding: 0 20px;
            font-weight: 800; height: 100%; cursor: pointer; transition: all 0.3s ease;
        }
        .coupon-input-group button:hover { background: #0f172a; }

        /* ======== Premium Notice/Alert Box Design ======== */
        .premium-notice-box {
            display: flex; align-items: center; gap: 14px;
            background: linear-gradient(to right, #fff4f2, #fff1f2);
            border-left: 4px solid #e11d48; padding: 14px 18px;
            border-radius: 8px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(225, 29, 72, 0.08);
        }
        .premium-notice-box .notice-icon {
            color: #e11d48; font-size: 22px; background: #ffe4e6;
            width: 42px; height: 42px; display: flex; align-items: center; 
            justify-content: center; border-radius: 50%; flex-shrink: 0;
        }
        .premium-notice-box .notice-text { font-size: 15px; font-weight: 700; color: #1e293b; line-height: 1.5; }

        /* ======== Customizing Toastr Notifications ======== */
        #toast-container { z-index: 9999999 !important; }
        #toast-container > div {
            border-radius: 12px !important; padding: 16px 16px 16px 50px !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important; opacity: 1 !important;
            font-family: 'Hind Siliguri', sans-serif !important; font-weight: 700 !important; font-size: 15px !important;
        }
        #toast-container > .toast-success { background-color: #10b981 !important; color: #ffffff !important; }
        #toast-container > .toast-error { background-color: #ef4444 !important; color: #ffffff !important; }
        #toast-container > .toast-warning { background-color: #f59e0b !important; color: #ffffff !important; }

        /* ======== MOBILE RESPONSIVE MEDIA QUERIES ======== */
        @media (max-width: 767px){
            .hero-inner{ padding: 18px 14px; }
            .top-div{ border-bottom-left-radius: 22px; border-bottom-right-radius: 22px; }
            .whats_btn, .scrollTopBtn { display: none !important; }
            
            /* Box Padding */
            .cardx-pad { padding: 12px; }
            .form-wrapper { padding: 14px 12px; }
            
            /* Table Texts */
            .review-order-table td, .review-order-table th { font-size: 14px; padding: 8px 0; }
            .product-thumbnail { width: 45px; height: 45px; border-radius: 10px; }
            .product-image { gap: 8px; }
            .product-name-td { font-size: 13px; }
            
            /* Package Sizes */
            .product-package-card { padding: 10px 8px; }
            .pkg-title { font-size: 13px; }
            .pkg-price { font-size: 14px; }
            .pkg-qty-box { padding: 2px 6px; }
            .pkg-qty-input { width: 18px; font-size: 12px; }
            
            /* Coupon Box Sizes */
            .coupon-box-wrapper { padding: 12px; }
            .coupon-input-group { height: 40px; }
            .coupon-input-group .icon { padding: 0 8px; font-size: 14px; }
            .coupon-input-group input { font-size: 13px; }
            .coupon-input-group button { padding: 0 12px; font-size: 12px; }
            
            /* Alert Box Sizes */
            .premium-notice-box { padding: 12px; gap: 10px; }
            .premium-notice-box .notice-icon { width: 34px; height: 34px; font-size: 18px; }
            .premium-notice-box .notice-text { font-size: 13px; }
        }
    </style>

    @if(!empty(optional($information)->tracking_code))
        {!! $information->tracking_code !!}
    @endif

    {{-- TikTok Pixel base --}}
    @if(!empty($ttPixelId))
        <script>
        !function (w, d, t) { w.TiktokAnalyticsObject = t; var ttq = w[t] = w[t] || []; ttq.methods = ["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"]; ttq.setAndDefer = function (t, e) { t[e] = function () { t.push([e].concat(Array.prototype.slice.call(arguments, 0))); }; }; for (var i = 0; i < ttq.methods.length; i++) ttq.setAndDefer(ttq, ttq.methods[i]); ttq.instance = function (t) { for (var e = ttq._i[t] || [], n = 0; n < ttq.methods.length; n++) ttq.setAndDefer(e, ttq.methods[n]); return e; }; ttq.load = function (e, n) { var r = "https://analytics.tiktok.com/i18n/pixel/events.js"; var o = n && n.partner; ttq._i = ttq._i || {}; ttq._i[e] = []; ttq._i[e]._u = r; ttq._t = ttq._t || {}; ttq._t[e] = +new Date; ttq._o = ttq._o || {}; ttq._o[e] = n || {}; var s = document.createElement("script"); s.type = "text/javascript"; s.async = !0; s.src = r + "?sdkid=" + e + "&lib=" + t; var x = document.getElementsByTagName("script")[0]; x.parentNode.insertBefore(s, x); }; ttq.load('{{ $ttPixelId }}');
        @if(!$__ttFromGtm)
        ttq.page();
        @endif
        }(window, document, 'ttq');
        </script>
    @endif

    @if(!empty($pixelId))
        <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ $pixelId }}');
        @if(!$__hasGtm)
        fbq('track', 'PageView');
        @endif
        </script>
    @endif

    {{-- Landing page ViewContent / InitiateCheckout.
         The ids are the exact ones the server used for the Meta CAPI and the TikTok
         Events API calls, so both platforms can deduplicate browser against server. --}}
    <script>
    (function(){
        window.LP_EVENT_BASE  = {!! json_encode($lpEventBase ?? null) !!} || ('LP_{{ $productId }}_' + Date.now());
        window.LP_EVENT_ID_VC = {!! json_encode($lpEventIdVC ?? null) !!} || (window.LP_EVENT_BASE + '_VC');
        window.LP_EVENT_ID_IC = {!! json_encode($lpEventIdIC ?? null) !!} || (window.LP_EVENT_BASE + '_IC');

        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({ ecommerce: null });
        window.dataLayer.push({
            event: 'view_item',
            event_id: window.LP_EVENT_ID_VC,
            ecommerce: {
                currency: 'BDT',
                value: {{ $lpTrackValue }},
                items: [{
                    item_id: '{{ $productId }}',
                    item_name: @json($productName),
                    item_category: @json($contentCategory),
                    price: {{ $lpTrackValue }},
                    quantity: 1
                }]
            }
        });
        window.dataLayer.push({ ecommerce: null });
        window.dataLayer.push({
            event: 'begin_checkout',
            event_id: window.LP_EVENT_ID_IC,
            ecommerce: {
                currency: 'BDT',
                value: {{ $lpTrackValue }},
                items: [{
                    item_id: '{{ $productId }}',
                    item_name: @json($productName),
                    item_category: @json($contentCategory),
                    price: {{ $lpTrackValue }},
                    quantity: 1
                }]
            }
        });

        var lpFired = false;
        function fireLPEvents(){
            if(lpFired) return;
            lpFired = true;
            try{
                if(@json(!$__hasGtm) && typeof fbq === 'function'){
                    fbq('track', 'ViewContent', { content_ids: ['{{ $productId }}'], content_name: @json($productName), content_type: 'product', content_category: @json($contentCategory), value: {{ $lpTrackValue }}, currency: 'BDT' }, { eventID: window.LP_EVENT_ID_VC });
                    fbq('track', 'InitiateCheckout', { content_ids: ['{{ $productId }}'], content_name: @json($productName), content_type: 'product', value: {{ $lpTrackValue }}, currency: 'BDT', num_items: 1 }, { eventID: window.LP_EVENT_ID_IC });
                }
            }catch(e){}
            try{
                if(@json(!$__ttFromGtm) && typeof ttq !== 'undefined' && ttq.track){
                    ttq.track('ViewContent', { content_type: 'product', value: {{ $lpTrackValue }}, currency: 'BDT', contents: [{ content_id: '{{ $productId }}', content_type: 'product', content_name: @json($productName), price: {{ $lpTrackValue }}, quantity: 1 }] }, { event_id: window.LP_EVENT_ID_VC });
                    ttq.track('InitiateCheckout', { content_type: 'product', value: {{ $lpTrackValue }}, currency: 'BDT', contents: [{ content_id: '{{ $productId }}', content_type: 'product', content_name: @json($productName), price: {{ $lpTrackValue }}, quantity: 1 }] }, { event_id: window.LP_EVENT_ID_IC });
                }
            }catch(e){}
        }
        if(document.readyState === 'complete'){ fireLPEvents(); }
        else { window.addEventListener('load', fireLPEvents); }
    })();
    </script>
</head>

<body>
@if(!empty($__gtmId))
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $__gtmId }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
@endif
<div class="main-wrapper">

    <div class="top-div">
        <div class="container-premium">
            <div class="hero-inner" data-aos="fade-down" data-aos-duration="900">
                <h2 class="hero-title">{{ $ln_pg->title1 }}</h2>
                <div class="hero-sub">{{ $ln_pg->title2 ?? $ln_pg->call_text ?? '' }}</div>
                <div class="hero-actions" data-aos="fade-up" data-aos-duration="900">
                    <a href="javascript:void(0)" class="btn btn-primary-brand js-scroll-order">
                        <i class="fa fa-shopping-cart"></i>&nbsp;&nbsp;{{ $txtHero }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid-landing" style="margin-top:12px;">
        <div class="cardx" style="padding:14px;">
            @if(!empty($ln_pg->right_product_image))
                <img src="{{ asset('landing_pages/'.$ln_pg->right_product_image) }}" class="top_section_img" alt="Main Product Image">
            @elseif(!empty($ln_pg->landing_bg))
                <img src="{{ asset('landing_pages/'.$ln_pg->landing_bg) }}" class="top_section_img" alt="Main Product Image">
            @endif

            @if(!empty($ln_pg->title1))
            <div style="text-align:center; margin-top:14px;">
                <span class="section-title">{{ $ln_pg->title1 }}</span>
            </div>
            @endif
        </div>
    </div>

    <div class="container-fluid-landing" style="margin-top:18px; margin-bottom:22px;">

        @if(!empty($ln_pg->video_url))
        <div class="cardx cardx-pad" data-aos="zoom-in">
            <div class="video-16x9">{!! $ln_pg->video_url !!}</div>
            <div style="margin-top:16px; text-align:center;">
                <button type="button" class="btn btn-primary-brand js-scroll-order">
                    {{ $txtVideo }} <img src="{{ asset('frontend/images/hand.png') }}" style="width:22px; height:auto; margin-left:6px;" alt="">
                </button>
            </div>
        </div>
        @endif

        @if(!empty($ln_pg->phone))
        <div class="call-box" style="margin-top:16px;">
            <div class="call-pill">
                <i class="fa-solid fa-phone"></i>
                <a href="tel:{{ $ln_pg->phone }}">{{ $ln_pg->phone }}</a>
            </div>
        </div>
        @endif

        @if(!empty($ln_pg->feature_list))
        <div class="cardx cardx-pad" style="margin-top:16px;">
            <div style="text-align:center;">
                <span class="section-title">{{ $ln_pg->feature_title ?? 'কেন এই পণ্যটি আপনার প্রয়োজন?' }}</span>
            </div>
            <div class="feature-content mt-3 px-2">
                {!! $ln_pg->feature_list !!}
            </div>
            <div style="margin-top:16px; text-align:center;">
                <button type="button" class="btn btn-primary-brand js-scroll-order">
                    {{ $ln_pg->order_btn_text ?? $txtFeature }} <img src="{{ asset('frontend/images/hand.png') }}" style="width:22px; height:auto; margin-left:6px;" alt="">
                </button>
            </div>
        </div>
        @endif

        @if(isset($ln_pg->images) && $ln_pg->images->count() > 0)
        <div class="cardx cardx-pad" style="margin-top:16px;">
            <div style="text-align:center;">
                <span class="section-title">{{ $ln_pg->feature ?? 'আমাদের গ্যালারি' }}</span>
            </div>
            <div class="owl-carousel img-gallery" style="margin-top:12px;">
                @foreach($ln_pg->images as $slider)
                    <div>
                        <img src="{{ asset('landing_sliders/'.$slider->image) }}"
                             style="border-radius:18px; width:100%; height:auto; box-shadow: var(--shadow-soft); border:1px solid rgba(2,6,23,.06);"
                             alt="img">
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        @if(!empty($ln_pg->promise_title))
        <div class="cardx cardx-pad" style="margin-top:16px;">
            <div style="text-align:center;">
                <span class="section-title">{{ $ln_pg->promise_title }}</span>
            </div>
            <div class="row g-3 mt-2 text-center">
                @for($i=1; $i<=3; $i++)
                    @php 
                        $pTitle = 'promise_'.$i.'_title'; 
                        $pDesc = 'promise_'.$i.'_desc'; 
                    @endphp
                    @if(!empty($ln_pg->$pTitle))
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light h-100" style="border-color: rgba(15,23,42,.1) !important;">
                            <i class="fas fa-check-circle text-success fs-2 mb-2"></i>
                            <h5 class="fw-bold fs-6" style="color: var(--brand-solid);">{{ $ln_pg->$pTitle }}</h5>
                            <p class="small text-muted mb-0">{{ $ln_pg->$pDesc }}</p>
                        </div>
                    </div>
                    @endif
                @endfor
            </div>
        </div>
        @endif

        @if(isset($ln_pg->review_images) && $ln_pg->review_images->count() > 0)
        <div class="cardx cardx-pad" style="margin-top:16px;">
            @if(isset($ln_pg->review_top_text))
                <div style="text-align:center;">
                    <span class="section-title">{{ $ln_pg->review_top_text }}</span>
                </div>
            @endif
            <div class="owl-carousel img-gallery2" style="margin-top:12px;">
                @foreach($ln_pg->review_images as $review_slider)
                    <div style="padding:4px;">
                        <img src="{{ asset('review_landing_sliders/'.$review_slider->review_image) }}"
                             style="width:100%; height: auto; border-radius:18px; box-shadow: var(--shadow-soft); border:1px solid rgba(2,6,23,.06);"
                             alt="img">
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        @if(!empty($ln_pg->faq_title))
        <div class="cardx cardx-pad" style="margin-top:16px;">
            <div style="text-align:center;">
                <span class="section-title">{{ $ln_pg->faq_title }}</span>
            </div>
            <div class="accordion mt-3" id="faqAccordion">
                @for($i=1; $i<=4; $i++)
                    @php 
                        $fQ = 'faq_'.$i.'_q'; 
                        $fA = 'faq_'.$i.'_a'; 
                    @endphp
                    @if(!empty($ln_pg->$fQ))
                    <div class="accordion-item mb-2 border rounded" style="overflow: hidden;">
                        <h2 class="accordion-header" id="heading{{ $i }}">
                            <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $i }}" style="background: #f8fafc; box-shadow: none;">
                                {{ $ln_pg->$fQ }}
                            </button>
                        </h2>
                        <div id="collapse{{ $i }}" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted fw-bold">
                                {{ $ln_pg->$fA }}
                            </div>
                        </div>
                    </div>
                    @endif
                @endfor
            </div>
        </div>
        @endif

        @if(!empty($ln_pg->new_price) || !empty($ln_pg->old_price))
        <div class="cardx cardx-pad text-center" style="margin-top:16px; border: 2px solid #ef4444;">
            <h4 class="text-danger fw-bold">{{ $ln_pg->countdown_title ?? 'অফারটি শেষ হতে আর বাকি মাত্র' }}</h4>
            <div class="d-flex justify-content-center gap-3 my-3">
                <div class="bg-danger text-white rounded p-2" style="min-width: 60px;">
                    <h3 id="cd_hours" class="m-0 fw-bold">00</h3><small>ঘণ্টা</small>
                </div>
                <div class="bg-danger text-white rounded p-2" style="min-width: 60px;">
                    <h3 id="cd_minutes" class="m-0 fw-bold">00</h3><small>মিনিট</small>
                </div>
                <div class="bg-danger text-white rounded p-2" style="min-width: 60px;">
                    <h3 id="cd_seconds" class="m-0 fw-bold">00</h3><small>সেকেন্ড</small>
                </div>
            </div>
            
            <div class="pricing-box my-3 p-3 rounded" style="background: #fdf2f8;">
                @if(!empty($ln_pg->old_price))
                    <h5 class="text-muted text-decoration-line-through mb-1">{{ $ln_pg->old_price_text ?? 'পূর্বের মূল্যঃ' }} {{ $ln_pg->old_price }} ৳</h5>
                @endif
                <h2 class="text-success fw-bold mb-0" style="font-size: 2.2rem;">{{ $ln_pg->new_price_text ?? 'বর্তমান মূল্যঃ' }} {{ $ln_pg->new_price ?? $defaultPrice }} ৳</h2>
            </div>
            
            <button type="button" class="btn btn-primary-brand js-scroll-order w-100 fs-5 mt-2" style="padding: 14px !important;">
                <i class="fas fa-shopping-cart"></i> {{ $ln_pg->order_btn_text ?? 'অর্ডার করতে ক্লিক করুন' }}
            </button>
            <p class="mt-3 text-muted fw-bold small mb-0"><i class="fas fa-star text-warning"></i> {{ $ln_pg->review_badge ?? '৫,০০০+ পরিবার ইতোমধ্যে ব্যবহার করছেন!' }}</p>
        </div>
        @endif

        <div id="element_widget" style="margin-top:16px;">
            <div class="cardx cardx-pad">
                <div style="text-align:center; margin-bottom:10px;">
                    <span class="section-title">{{ $ln_pg->form_title ?? BanglaText('land_instruction') }}</span>
                </div>

                <div class="form-wrapper">
                    <form action="{{ route('front.storelandData') }}" method="POST" id="checkout_land_form">
                        @csrf
                        {{-- কোন landing page থেকে order এলো (Order Management-এর Source-এ দেখায়) --}}
                        <input type="hidden" name="landing_page_type" value="6">
                        <input type="hidden" name="purchase_event_id" id="purchase_event_id" value="">
                        
                        <div class="form-group mb-4">
                            <label style="font-weight:900; margin-bottom: 10px; display:block; font-size: 18px;">
                                পেমেন্ট মেথড সিলেক্ট করুন:
                            </label>
                            
                            <div class="d-flex flex-column gap-2">
                                @if(isset($information->cod_active) && $information->cod_active == 1)
                                <label class="payment-label p-3 border rounded-3 d-flex align-items-center gap-3 bg-white shadow-sm" style="cursor:pointer;">
                                    <input class="form-check-input mt-0" type="radio" name="payment_method" id="payment_cod" value="cod" checked style="width: 20px; height: 20px;" onchange="togglePaymentAction('cod')">
                                    <span class="fw-bold fs-5 text-dark">{{ $ln_pg->cod_title ?? 'ক্যাশ অন ডেলিভারি (Cash on Delivery)' }}</span>
                                </label>
                                @endif

                                @if(isset($information->ssl_active) && $information->ssl_active == 1)
                                <label class="payment-label p-3 border rounded-3 d-flex align-items-center gap-3 bg-white shadow-sm" style="border-color: #00276C !important; cursor:pointer;">
                                    <input class="form-check-input mt-0" type="radio" name="payment_method" id="payment_ssl" value="sslcommerz" style="width: 20px; height: 20px;" onchange="togglePaymentAction('sslcommerz')">
                                    <span class="fw-bold fs-5 text-dark">অনলাইন পেমেন্ট (Card / SSL)</span>
                                </label>
                                @endif

                                @if(isset($information->bkash_active) && $information->bkash_active == 1)
                                <label class="payment-label p-3 border rounded-3 d-flex align-items-center gap-3 bg-white shadow-sm" style="border-color: #E2136E !important; cursor:pointer;">
                                    <input class="form-check-input mt-0" type="radio" name="payment_method" id="payment_bkash" value="bkash" style="width: 20px; height: 20px;" onchange="togglePaymentAction('bkash')">
                                    <span class="fw-bold fs-5 text-dark" style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                                        বিকাশ পেমেন্ট (bKash)
                                        <img src="{{ asset('frontend/images/bkash_logo.png') }}" alt="bKash" style="height: 20px; width: auto; object-fit: contain;">
                                    </span>
                                </label>
                                @endif

                                @if(isset($information->eps_active) && $information->eps_active == 1)
                                <label class="payment-label p-3 border rounded-3 d-flex align-items-center gap-3 bg-white shadow-sm" style="border-color: #17a2b8 !important; cursor:pointer;">
                                    <input class="form-check-input mt-0" type="radio" name="payment_method" id="payment_eps" value="eps" style="width: 20px; height: 20px;" onchange="togglePaymentAction('eps')">
                                    <span class="fw-bold fs-5 text-dark">EPS পেমেন্ট (Easy Payment System)</span>
                                </label>
                                @endif

                                @if(isset($information->nagad_active) && $information->nagad_active == 1)
                                <label class="payment-label p-3 border rounded-3 d-flex align-items-center gap-3 bg-white shadow-sm" style="border-color: #ED1C24 !important; cursor:pointer;">
                                    <input class="form-check-input mt-0" type="radio" name="payment_method" id="payment_nagad" value="nagad" style="width: 20px; height: 20px;" onchange="togglePaymentAction('nagad')">
                                    <span class="fw-bold fs-5 text-dark" style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                                        নগদ পেমেন্ট (Nagad)
                                        <img src="{{ asset('frontend/images/nagad.png') }}" alt="Nagad" style="height: 20px; width: auto; object-fit: contain;">
                                    </span>
                                </label>
                                @endif

                                @if(isset($information->uddoktapay_active) && $information->uddoktapay_active == 1)
                                <label class="payment-label p-3 border rounded-3 d-flex align-items-center gap-3 bg-white shadow-sm" style="border-color: #28a745 !important; cursor:pointer;">
                                    <input class="form-check-input mt-0" type="radio" name="payment_method" id="payment_uddoktapay" value="uddoktapay" style="width: 20px; height: 20px;" onchange="togglePaymentAction('uddoktapay')">
                                    <span class="fw-bold fs-5 text-dark">উদ্দোক্তাপে (UddoktaPay)</span>
                                </label>
                                @endif

                                @php
                                    $activeManuals = \App\Models\ManualPayment::where('status', 1)->get();
                                @endphp
                                @foreach($activeManuals as $mp)
                                <label class="payment-label p-3 border rounded-3 d-flex align-items-center gap-3 bg-white shadow-sm" style="cursor:pointer;">
                                    <input class="form-check-input mt-0" type="radio" name="payment_method" value="{{ $mp->name }}" style="width: 20px; height: 20px;" data-number="{{ $mp->number }}" data-type="{{ $mp->type }}" onchange="togglePaymentAction('manual', '{{ $mp->name }}', '{{ $mp->number }}', '{{ $mp->type }}')">
                                    <span class="fw-bold fs-5 text-dark">{{ $mp->name }} ({{ $mp->type }})</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <div id="manual_payment_area" style="display: none;">
                            <div class="manual-instruction-box">
                                <i class="fas fa-info-circle fa-2x"></i>
                                <div>
                                    <p id="payment_instruction" class="mb-0 fw-bold hind" style="font-size: 15px;"></p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="form-label text-dark hind fw-bold" style="font-size: 14px;">যে নাম্বার থেকে টাকা পাঠিয়েছেন <span class="text-danger">*</span></label>
                                        <div class="input-icon-wrap">
                                            <i class="fas fa-phone-alt"></i>
                                            <input type="text" name="sender_number" id="sender_number" class="form-control" placeholder="017XXXXXXXX">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="form-label text-dark hind fw-bold" style="font-size: 14px;">Transaction ID (TrxID) <span class="text-danger">*</span></label>
                                        <div class="input-icon-wrap">
                                            <i class="fas fa-receipt"></i>
                                            <input type="text" name="transaction_id" id="transaction_id" class="form-control" placeholder="TRX123456789">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mt-2">
                            <div class="col-lg-6">
                                <div class="cardx cardx-pad" style="height:100%;">
                                    <h3 class="billing-title">Billing Address</h3>

                                    <div class="form-group mb-3">
                                        <label style="font-weight:800;">{{ $ln_pg->name_label ?? BanglaText('name') }} <span style="color:#ef4444">*</span></label>
                                        <input type="text" name="first_name" class="form-control">
                                        
                                        <input type="hidden" value="{{ $productId }}" name="prd_id">
                                    </div>

                                    <div class="form-group mb-3">
                                        <label style="font-weight:800;">{{ $ln_pg->phone_label ?? BanglaText('mobile') }} <span style="color:#ef4444">*</span></label>
                                        <input type="tel" name="mobile" class="form-control" maxlength="11" placeholder="017XXXXXXXX">
                                    </div>

                                    <input type="hidden" id="variation_id" name="variation_id" value="{{ $defaultVarId }}">
                                    <input type="hidden" id="total_price_val" name="final_amount" value="">
                                    <input type="hidden" id="shipping_cost" value="0">
                                    <input type="hidden" name="amount" value="">
                                    <input type="hidden" id="product_price" value="{{ $ln_pg->new_price ?? $defaultPrice }}">
                                    <input type="hidden" id="product_quantity" name="quantity" value="1">
                                    <input type="hidden" id="max_stock" value="{{ $defaultStock }}">

                                    <div class="form-group mb-3">
                                        <label style="font-weight:800;">{{ $ln_pg->address_label ?? BanglaText('address') }} <span style="color:#ef4444">*</span></label>
                                        <input type="text" name="shipping_address" class="form-control">
                                    </div>

                                    <div class="form-group mb-0">
                                        <label style="font-weight:800;">{{ BanglaText('delivery_zone') }}</label>
                                        <select required name="delivery_charge_id" id="delivery_charge_id" class="form-control form-select">
                                            <option value="" disabled selected>এলাকা নির্বাচন করুন...</option>
                                            @foreach($charges as $charge)
                                                <option value="{{ $charge->id }}" data-charge="{{ $isFreeShipping ? 0 : intval($charge->amount) }}">
                                                    {{ $charge->title }} - {!! $isFreeShipping ? '<span class="text-success">ফ্রি ডেলিভারি (0 ৳)</span>' : intval($charge->amount) . ' ৳' !!}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="cardx cardx-pad order-sticky">
                                    <h3 class="order-title">Your Order</h3>

                                    @if($ln_pg->packages && $ln_pg->packages->count() > 0)
                                    <div class="mb-4">
                                        <h4 style="font-weight: 900; font-size: 18px; margin-bottom: 12px; color: var(--text);">প্যাকেজ সিলেক্ট করুন:</h4>
                                        
                                        @php
                                            $defaultPkgId = null;
                                        @endphp
                                        
                                        <label class="product-package-card {{ !$defaultPkgId ? 'active-pkg' : '' }}">
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="radio" name="selected_package_id" value="" data-price="{{ $ln_pg->new_price ?? $defaultPrice }}" data-qty="1" class="pkg-radio" {{ !$defaultPkgId ? 'checked' : '' }} autocomplete="off">
                                                <span class="pkg-title">
                                                    (১ পিস) {{ $productName }}
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="pkg-qty-box">
                                                    <input type="text" class="pkg-qty-input" value="1" readonly>
                                                </div>
                                                <span class="pkg-price"><span id="regular_pkg_price_display">{{ $ln_pg->new_price ?? $defaultPrice }}</span> ৳</span>
                                            </div>
                                        </label>

                                        @foreach($ln_pg->packages as $pkg)
                                        <label class="product-package-card {{ $pkg->is_default == 1 ? 'active-pkg' : '' }}">
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="radio" name="selected_package_id" value="{{ $pkg->id }}" data-price="{{ $pkg->price }}" data-qty="{{ $pkg->qty }}" class="pkg-radio" {{ $pkg->is_default == 1 ? 'checked' : '' }} autocomplete="off">
                                                <span class="pkg-title">
                                                    ({{ $pkg->qty }} পিস) {{ $productName }} 
                                                    @if($pkg->discount_text)
                                                        <small class="d-block text-danger mt-1">{{ $pkg->discount_text }}</small>
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="pkg-qty-box">
                                                    <input type="text" class="pkg-qty-input" value="{{ $pkg->qty }}" readonly>
                                                </div>
                                                <span class="pkg-price">{{ intval($pkg->price) }} ৳</span>
                                            </div>
                                        </label>
                                        @endforeach
                                    </div>
                                    @endif

                                    <table class="review-order-table">
                                        <thead>
                                            <tr>
                                                <th class="product-name">Product</th>
                                                <th class="product-total">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="cart_item">
                                                <td class="product-name">
                                                    <div class="product-image">
                                                        @if(!empty($product))
                                                            <div class="product-thumbnail">
                                                                <img src="{{ getImage('products', $product->image) }}" alt="">
                                                            </div>
                                                            <div class="product-name-td">{{ $productName }}</div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="product-total">
                                                    <span id="price" class="price-amount amount">{{ $ln_pg->new_price ?? $defaultPrice }}</span>
                                                    <input type="hidden" id="price_val" value="{{ $ln_pg->new_price ?? $defaultPrice }}">

                                                    <div id="stock_status" class="stock-status {{ $defaultStock > 0 ? 'in-stock' : 'out-stock' }}">
                                                        {{ $defaultStock > 0 ? 'In Stock: '.$defaultStock : 'Out of Stock' }}
                                                    </div>
                                                </td>
                                            </tr>

                                            @if($variations->count())
                                            <tr style="display: {{ $variations->count() <= 1 ? 'none !important' : 'table-row' }};">
                                                <td colspan="2">
                                                    <div class="variation-wrap">
                                                        <div style="font-weight:900; margin-bottom:6px;">
                                                            Select Variation (Size / Color) <span class="text-danger">*</span>
                                                        </div>

                                                        <div class="form-group">
                                                            <select id="variation_select" class="form-control form-select">
                                                                @foreach($variations as $v)
                                                                    @php
                                                                        $vBase  = $v->price ?? $product->sell_price ?? 0;
                                                                        $vDisc  = $v->after_discount_price ?? null;
                                                                        $vPrice = ((float)$vDisc > 0) ? $vDisc : $vBase;
                                                                        $vStock = $v->stocks->sum('quantity');
                                                                        $sizeName  = $v->size->name ?? $v->size->title ?? '';
                                                                        $colorName = $v->color->name ?? '';
                                                                        $label = trim(($sizeName ?: '') . (($sizeName && $colorName) ? ' - ' : '') . ($colorName ?: ''));
                                                                    @endphp

                                                                    <option value="{{ $v->id }}"
                                                                            data-price="{{ $vPrice }}"
                                                                            data-stock="{{ $vStock }}"
                                                                            {{ ($defaultVarId && $v->id == $defaultVarId) ? 'selected' : '' }}>
                                                                        {{ $label ?: ('Variation #'.$v->id) }} - {{ $vPrice }} Tk
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endif

                                            {{-- প্যাকেজ থাকলে এই Select Quantity অংশটি হাইড থাকবে --}}
                                            <tr style="{{ ($ln_pg->packages && $ln_pg->packages->count() > 0) ? 'display: none;' : '' }}">
                                                <td style="font-weight:900;">Select Quantity</td>
                                                <td style="text-align:right;">
                                                    <div class="pro-qty item-quantity" style="justify-content:flex-end;">
                                                        <span class="decrease-qty quantity-button">-</span>
                                                        <input type="text" class="inner_qty qty-input quantity-input" value="1" inputmode="numeric">
                                                        <span class="increase-qty quantity-button">+</span>
                                                    </div>
                                                </td>
                                            </tr>

                                            @if(isset($information->coupon_visibility) && $information->coupon_visibility == 1)
                                            <tr>
                                                <td colspan="2" style="padding-bottom: 10px;">
                                                    <div class="coupon-box-wrapper">
                                                        <label class="fw-bold mb-2" style="font-size:15px; color: var(--text);">
                                                            <i class="fa-solid fa-tags me-1" style="color: var(--brand-solid);"></i> কুপন কোড (যদি থাকে)
                                                        </label>
                                                        <div class="coupon-input-group">
                                                            <div class="icon"><i class="fa-solid fa-ticket-simple"></i></div>
                                                            <input type="text" id="coupon_code" placeholder="কোড লিখুন...">
                                                            <button type="button" id="coupon_btn_submit" onclick="applyCouponLand()">APPLY</button>
                                                        </div>
                                                        <small id="coupon_msg" class="d-block mt-2 fw-bold"></small>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endif

                                            <tr class="totals-row">
                                                <td style="font-weight:900;">Subtotal</td>
                                                <td style="text-align:right;"><span class="final-price-amount amount"></span></td>
                                            </tr>
                                            
                                            <tr>
                                                <td style="font-weight:900;">Shipping</td>
                                                <td style="text-align:right;"><span id="delvry_charge_text"><span id="delvry_charge">0</span> ৳</span></td>
                                            </tr>
                                            
                                            <tr id="discount_row" style="display: none;">
                                                <td style="font-weight:900; color:green;">Discount</td>
                                                <td style="text-align:right; color:green;">- <span id="discount_display">0</span> ৳</td>
                                            </tr>

                                            <tr>
                                                <td style="font-weight:900; font-size:18px;">Total</td>
                                                <td style="text-align:right;"><strong style="font-size:18px;"><span id="total" class="Price-amount amount"></span> ৳</strong></td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <div style="margin-top:12px;">
                                        
                                        <div class="premium-notice-box">
                                            <div class="notice-icon">
                                                <i class="fa-solid fa-bell fa-shake"></i>
                                            </div>
                                            <div class="notice-text">
                                                {!! BanglaText('alert') !!}
                                            </div>
                                        </div>

                                        @if(isset($information->ssl_terms_active) && $information->ssl_terms_active == 1)
                                        <div class="mb-3 mt-4" id="terms_checkbox_area" style="display: none;">
                                            <div class="form-check d-flex align-items-center gap-2">
                                                <input class="form-check-input mt-0" type="checkbox" id="agree_terms" name="agree_terms" value="1" style="width: 20px; height: 20px; cursor: pointer; flex-shrink: 0;">
                                                <label class="form-check-label text-dark mb-0" for="agree_terms" style="cursor: pointer; font-size: 14px; line-height: 1.4;">
                                                    I agree to the 
                                                    <a href="{{ route('front.privacyPolicy') }}" target="_blank" class="text-primary text-decoration-none fw-bold">Privacy Policy</a>, 
                                                    <a href="{{ url('/page/terms-condition') }}" target="_blank" class="text-primary text-decoration-none fw-bold">Terms & Conditions</a>, 
                                                    and 
                                                    <a href="{{ route('front.returnPolicy') }}" target="_blank" class="text-primary text-decoration-none fw-bold">Return Policy</a>.
                                                </label>
                                            </div>
                                            <small class="text-danger d-none fw-bold mt-2 d-block" id="terms_error">You must agree to the terms and policies to proceed.</small>
                                        </div>
                                        @endif

                                        <div class="form-row place-order mt-3">
                                            <button type="submit" id="submit_btn" class="button btn-primary-brand" form="checkout_land_form">
                                                {{ $ln_pg->btn_text_form ?? $txtForm }}
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @php
        $waNumber = $ln_pg->whatsapp ?? $ln_pg->phone ?? '';
        $waNumberClean = preg_replace('/\D+/', '', $waNumber);
    @endphp
    @if(!empty($waNumberClean))
        <a href="https://wa.me/{{ $waNumberClean }}" target="_blank" class="whats_btn" aria-label="WhatsApp">
            <img src="https://img.icons8.com/windows/96/ffffff/whatsapp--v1.png" alt="whatsapp">
        </a>
    @endif

    <button type="button" class="scrollTopBtn" id="scrollTopBtn" aria-label="Scroll to top">
        <i class="fa-solid fa-arrow-up"></i>
    </button>

    <div class="modal fade" id="otpModal" data-bs-backdrop="static" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4 rounded-0 border-0 shadow-lg"> 
          <div class="modal-header border-0 p-0 mb-3">
             <h5 class="modal-title fw-bold">মোবাইল ভেরিফিকেশন</h5>
             <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body text-center p-0">
            <p class="text-muted mb-3">আমরা আপনার নাম্বারে ৪ ডিজিটের কোড পাঠিয়েছি</p>
            <input type="text" id="otp_input" class="form-control text-center fs-3 fw-bold mb-3" placeholder="____" maxlength="4">
            <div class="d-grid">
                <button type="button" class="btn btn-primary btn-lg rounded-0" onclick="verifyOtpLand()">যাচাই করুন</button>
            </div>
            <div class="mt-3">
                 <button type="button" class="btn btn-link text-decoration-none" id="resendOtpBtn" onclick="sendOtpLand(true)">আবার পাঠান</button>
            </div>
            <p class="text-danger mt-2 fw-bold" id="otp_error"></p>
          </div>
        </div>
      </div>
    </div>

</div>

@if(isset($information->bkash_active) && $information->bkash_active == 1)
    <button id="bKash_button" style="display: none;"></button>
    @php
        $bkashScriptUrl = (isset($information->bkash_sandbox) && $information->bkash_sandbox == 1) 
            ? 'https://scripts.sandbox.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout-sandbox.js' 
            : 'https://scripts.pay.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout.js';
    @endphp
    <script src="{{ $bkashScriptUrl }}"></script>
@endif

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="{{ asset('backend/landing_page/js/carousel.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="{{ asset('backend/landing_page/js/main.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>

<script>
    var isTermsEnabled = {{ (isset($information->ssl_terms_active) && $information->ssl_terms_active == 1) ? 'true' : 'false' }};
    var current_discount_val = 0;
    var current_discount_type = "fixed";
    var isWeightBased = {{ $isWeightBased ? 'true' : 'false' }};
    var isFreeShipping = {{ $isFreeShipping }};
    var paymentID;
    var dynamicOrderId;
    var successUrl = '';
    var processBtnText = '{{ $ln_pg->processing_text ?? "প্রসেসিং হচ্ছে..." }}';

    // Countdown Timer Logic
    let cdHours = {{ $ln_pg->countdown_hours ?? 5 }};
    let endTime = new Date().getTime() + (cdHours * 60 * 60 * 1000);

    setInterval(function() {
        let now = new Date().getTime();
        let distance = endTime - now;
        
        if (distance < 0) {
            endTime = new Date().getTime() + (cdHours * 60 * 60 * 1000);
            distance = endTime - now;
        }
        
        let h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        let m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        let s = Math.floor((distance % (1000 * 60)) / 1000);
        
        $('#cd_hours').text(h < 10 ? '0'+h : h);
        $('#cd_minutes').text(m < 10 ? '0'+m : m);
        $('#cd_seconds').text(s < 10 ? '0'+s : s);
    }, 1000);

    function toNumber(v){
        v = (v ?? '').toString().replace(/[^\d.]/g,'');
        var n = parseFloat(v);
        return isNaN(n) ? 0 : n;
    }

    function getCharge(){
        var unitPrice = toNumber($('#price_val').val());
        var qty = toNumber($('.inner_qty').val());
        var maxStock = toNumber($('#max_stock').val());
        
        if(maxStock <= 0) {
            qty = 0; $('.inner_qty').val(0);
        } else if(qty <= 0) {
            qty = 1; $('.inner_qty').val(1);
        } else if(qty > maxStock) {
            toastr.error('Max quantity available: ' + maxStock);
            qty = maxStock; $('.inner_qty').val(maxStock);
        }

        var subTotal = unitPrice * qty;
        var discount = 0;
        var rawDiscount = parseFloat(current_discount_val) || 0;

        if(rawDiscount > 0) {
            if (current_discount_type === 'percentage' || current_discount_type === 'percent') {
                discount = (subTotal * rawDiscount) / 100;
            } else {
                discount = rawDiscount;
            }
        }

        if(discount > 0) {
            $('#discount_row').show();
            $('#discount_display').text(discount.toFixed(0));
        } else {
            $('#discount_row').hide();
        }

        $('span.final-price-amount').text(subTotal);
        $('#product_quantity').val(qty);
        $('input[name="amount"]').val(subTotal);

        let $opt = $('#delivery_charge_id').find("option:selected");
        let cid = $opt.val();

        if(isFreeShipping == 1) {
            $('#delvry_charge_text').html('<span class="text-success fw-bold">ফ্রি ডেলিভারি</span>');
            var total = subTotal - discount;
            if(total < 0) total = 0;
            $('#total').text(total.toFixed(0));
            $('#total_price_val').val(total);
        } else if(isWeightBased && cid && cid !== '0') {
            let prd_id = $('input[name="prd_id"]').val();
            $.ajax({
                url: "{{ route('front.getDeliveryChargeAjax') }}",
                type: "POST",
                data: {
                    delivery_charge_id: cid,
                    product_id: prd_id,
                    quantity: qty,
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    if(res.success) {
                        let charge = parseFloat(res.charge);
                        $('#delvry_charge_text').html('<span id="delvry_charge">' + charge.toFixed(0) + '</span> ৳');
                        var total = (subTotal + charge) - discount;
                        if(total < 0) total = 0;
                        $('#total').text(total.toFixed(0));
                        $('#total_price_val').val(total);
                    }
                }
            });
        } else {
            var charge = toNumber($opt.data('charge'));
            $('#delvry_charge_text').html('<span id="delvry_charge">' + charge + '</span> ৳');
            var total = (subTotal + charge) - discount;
            if(total < 0) total = 0;
            $('#total').text(total);
            $('#total_price_val').val(total);
        }
    }

    function buildPurchaseEventId(){
        var pid = "{{ $productId }}";
        return "PUR_" + pid + "_" + Date.now() + "_" + Math.floor(Math.random() * 1000000);
    }
    
    var isOtpVerified = false;
    var otpSystemEnabled = {{ $information->otp_system ?? 0 }};
    var isSendingOtp = false;
    var otpTimerInterval;

    window.togglePaymentAction = function(method, name = '', number = '', type = '') {
        var manualArea = $('#manual_payment_area');
        var sNum = $('#sender_number');
        var tId = $('#transaction_id');
        var termsArea = $('#terms_checkbox_area');

        if(method === 'manual') {
            $('#payment_instruction').html(`দয়া করে আপনার টোটাল বিল <b>${number} (${type})</b> নাম্বারে Send Money করুন। এরপর নিচের তথ্যগুলো দিন।`);
            manualArea.slideDown();
            if(isTermsEnabled) termsArea.slideUp();
            sNum.attr('required', 'required');
            tId.attr('required', 'required');
        } else if (method === 'sslcommerz' || method === 'bkash' || method === 'eps' || method === 'nagad' || method === 'uddoktapay') {
            manualArea.slideUp();
            if(isTermsEnabled) termsArea.slideDown();
            sNum.removeAttr('required');
            tId.removeAttr('required');
        } else {
            manualArea.slideUp();
            if(isTermsEnabled) termsArea.slideUp();
            sNum.removeAttr('required');
            tId.removeAttr('required');
        }
    };

    function applyCouponLand() {
        var code = $('#coupon_code').val();
        if(!code) { toastr.error('কুপন কোড লিখুন'); return; }

        var unitPrice = toNumber($('#price_val').val());
        var qty = toNumber($('.inner_qty').val());
        var current_total = unitPrice * qty;

        var $btn = $('#coupon_btn_submit');
        $btn.prop('disabled', true).text('Checking...');

        $.ajax({
            url: "{{ route('front.getCouponDiscount') }}", 
            method: "GET",
            data: { code: code, total_price: current_total },
            success: function(res) {
                if(res.success) {
                    toastr.success(res.msg);
                    $('#coupon_msg').text(res.msg).css('color', 'green');
                    $btn.prop('disabled', false).text('Applied');
                    current_discount_val = parseFloat(res.amount);
                    current_discount_type = res.discount_type;
                    getCharge(); 
                } else {
                    $('#coupon_msg').text(res.msg).css('color', 'red');
                    toastr.error(res.msg);
                    $btn.prop('disabled', false).text('APPLY');
                    current_discount_val = 0;
                    getCharge(); 
                }
            },
            error: function() {
                toastr.error('Error applying coupon');
                $btn.prop('disabled', false).text('APPLY');
            }
        });
    }

    function startOtpTimer(duration, display) {
        var timer = duration, seconds;
        clearInterval(otpTimerInterval);
        $('#resendOtpBtn').prop('disabled', true).addClass('text-muted').removeClass('text-primary');
        otpTimerInterval = setInterval(function () {
            seconds = parseInt(timer % 60, 10);
            seconds = seconds < 10 ? "0" + seconds : seconds;
            display.html("Wait (" + seconds + "s)");
            if (--timer < 0) {
                clearInterval(otpTimerInterval);
                display.html("কোড পাননি? <span class='text-primary fw-bold'>আবার পাঠান</span>");
                $('#resendOtpBtn').prop('disabled', false).removeClass('text-muted').addClass('text-primary');
            }
        }, 1000);
    }

    function sendOtpLand(isResend = false) {
        if(isSendingOtp) return;
        var mobile = $('input[name="mobile"]').val();
        if(mobile.length !== 11) { toastr.error('১১ ডিজিটের মোবাইল নাম্বার দিন'); return; }

        isSendingOtp = true;
        var $btn = $('#submit_btn');
        if(!isResend) $btn.prop('disabled', true).text('Sending OTP...');

        $.ajax({
            url: "{{ route('sendOtp') }}", 
            type: "POST", 
            data: { mobile: mobile, _token: "{{ csrf_token() }}" },
            success: function(res) {
                isSendingOtp = false;
                if(!isResend) $btn.prop('disabled', false).text("{{ $ln_pg->btn_text_form ?? $txtForm }}");
                
                if(res.success) {
                    $('#otp_sent_number').text(mobile);
                    $('#otpModal').modal('show');
                    toastr.success(res.msg);
                    setTimeout(function() { $('#otp_input').focus(); }, 500);
                    startOtpTimer(30, $('#resendOtpBtn'));
                } else {
                    toastr.error(res.msg);
                }
            },
            error: function(err) {
                isSendingOtp = false;
                if(!isResend) $btn.prop('disabled', false).text("{{ $ln_pg->btn_text_form ?? $txtForm }}");
            }
        });
    }

    function verifyOtpLand() {
        var code = $('#otp_input').val();
        var mobile = $('input[name="mobile"]').val();

        $.ajax({
            url: "{{ route('verifyOtp') }}", 
            type: "POST", 
            data: { otp: code, mobile: mobile, _token: "{{ csrf_token() }}" },
            success: function(res) {
                if(res.success) {
                    isOtpVerified = true;
                    $('#otpModal').modal('hide');
                    toastr.success('ভেরিফিকেশন সফল!');
                    $('form#checkout_land_form').submit();
                } else {
                    $('#otp_error').text(res.msg);
                }
            }
        });
    }

    function resendOtpLand() {
        $('#otp_input').val('');
        $('#otp_error').text('');
        $('#otpModal').modal('hide');
        sendOtpLand(true);
    }

    @if(isset($information->bkash_active) && $information->bkash_active == 1)
    function initBkash() {
        bKash.init({
            paymentMode: 'checkout',
            paymentRequest: { "amount": "0", "intent": "sale" },
            createRequest: function (request) {
                $.ajax({
                    url: "{{ route('bkash.create') }}",
                    type: 'POST',
                    data: { _token: "{{ csrf_token() }}", order_id: dynamicOrderId },
                    success: function (data) {
                        if (data && data.paymentID != null) {
                            paymentID = data.paymentID;
                            bKash.create().onSuccess(data);
                        } else {
                            bKash.create().onError();
                            toastr.error("Payment Error: " + (data.errorMessage || "Something went wrong"));
                            $('#submit_btn').prop('disabled', false).html("{{ $ln_pg->btn_text_form ?? $txtForm }}");
                        }
                    },
                    error: function (err) {
                        bKash.create().onError();
                        toastr.error("Server error while connecting to bKash.");
                        $('#submit_btn').prop('disabled', false).html("{{ $ln_pg->btn_text_form ?? $txtForm }}");
                    }
                });
            },
            executeRequestOnAuthorization: function () {
                $.ajax({
                    url: "{{ route('bkash.execute') }}",
                    type: 'POST',
                    data: { _token: "{{ csrf_token() }}", paymentID: paymentID },
                    success: function (data) {
                        if (data && data.paymentID != null && data.transactionStatus === 'Completed') {
                            window.location.href = successUrl;
                        } else {
                            bKash.execute().onError();
                            toastr.error("Payment Failed! " + (data.errorMessage || ""));
                            $('#submit_btn').prop('disabled', false).html("{{ $ln_pg->btn_text_form ?? $txtForm }}");
                        }
                    },
                    error: function () {
                        bKash.execute().onError();
                        toastr.error("Failed to execute bKash payment.");
                        $('#submit_btn').prop('disabled', false).html("{{ $ln_pg->btn_text_form ?? $txtForm }}");
                    }
                });
            },
            onClose: function () {
                window.location.href = successUrl;
            }
        });
    }
    @endif

    $(document).ready(function () {
        AOS.init({ duration: 900 });

        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000",
        };

        $(".img-gallery").owlCarousel({
            loop: true, autoplay: true, dots: false, margin: 10, nav: false,
            responsive: { 0:{items:1}, 700:{items:3}, 1200:{items:3} }
        });

        $(".img-gallery2").owlCarousel({
            loop: true, autoplay: true, dots: false, margin: 10, nav: false,
            responsive: { 0:{items:1}, 700:{items:1}, 1200:{items:1} }
        });

        $(document).on('click', '.js-scroll-order', function(){
            $('html,body').animate({ scrollTop: $("#element_widget").offset().top - 8 }, 'slow');
        });

        function updatePackagePrice() {
            let activePkg = $('.pkg-radio:checked');
            if (activePkg.length > 0) {
                let pkgPrice = parseFloat(activePkg.attr('data-price'));
                let pkgQty = parseInt(activePkg.attr('data-qty'));
                
                let unitPrice = pkgPrice / pkgQty;
                
                $('#price_val').val(unitPrice);
                $('.price-amount').text(unitPrice.toFixed(2));
                $('.inner_qty').val(pkgQty);
            }
            getCharge();
        }

        if($('.pkg-radio').length > 0) {
            if($('.pkg-radio:checked').length === 0){
               $('.product-package-card').first().addClass('active-pkg').find('.pkg-radio').prop('checked', true);
            }
            updatePackagePrice();
        }

        $('.pkg-radio').on('change', function() {
            $('.product-package-card').removeClass('active-pkg');
            $(this).closest('.product-package-card').addClass('active-pkg');
            updatePackagePrice();
        });

        // ডাবল-ক্লিক ইস্যু ফিক্স করা হয়েছে: (e.preventDefault() ব্যবহার করে)
        $('.product-package-card').on('click', function(e) {
            if (!$(e.target).is('.pkg-radio')) {
                e.preventDefault();
                $(this).find('.pkg-radio').prop('checked', true).trigger('change');
            }
        });

        // ✅ FIX: quantity +/- বাটনের handler ছিল না — বাটন দেখা যেত কিন্তু কাজ করত না।
        // প্যাকেজ সিলেক্ট থাকা অবস্থায় qty বদলালে regular দামে ফিরিয়ে qty ঠিক রাখা হয়,
        // নাহলে প্যাকেজের qty রয়ে গিয়ে দাম কয়েকগুণ দেখাত।
        function resetToRegularIfPackage() {
            var $cur = $('.pkg-radio:checked');
            if ($cur.length && $cur.val() !== '') {
                var $regular = $('.pkg-radio[value=""]');
                if ($regular.length) {
                    $('.product-package-card').removeClass('active-pkg');
                    $regular.prop('checked', true).closest('.product-package-card').addClass('active-pkg');
                    var basePrice = parseFloat($regular.attr('data-price')) || 0;
                    $('#price_val').val(basePrice);
                    $('.price-amount').text(basePrice.toFixed(2));
                }
                $('.inner_qty').val(1);
                return true;
            }
            return false;
        }

        $(document).on('click', '.increase-qty', function () {
            if (resetToRegularIfPackage()) { getCharge(); return; }
            var q = toNumber($('.inner_qty').val());
            $('.inner_qty').val((q > 0 ? q : 1) + 1);
            getCharge();
        });

        $(document).on('click', '.decrease-qty', function () {
            if (resetToRegularIfPackage()) { getCharge(); return; }
            var q = toNumber($('.inner_qty').val());
            if (q > 1) { $('.inner_qty').val(q - 1); }
            getCharge();
        });

        $(document).on('change', '.inner_qty', function () { getCharge(); });

        $(document).on('change', '#variation_select', function(){
            let selectedOption = $(this).find('option:selected');
            let variation_id = selectedOption.val();
            let price = parseFloat(selectedOption.data('price')) || 0;
            // FIX: data-stock না থাকলে আগে 0 ধরে নিয়ে ভুল করে "স্টক নেই" দেখাত।
                let rawStock = parseInt(selectedOption.data('stock'));
                let stock = isNaN(rawStock) ? (parseInt($('#max_stock').val()) || 0) : rawStock;

            $("#variation_id").val(variation_id);
            
            let $regularPkgRadio = $('.pkg-radio[value=""]');
            if($regularPkgRadio.length > 0) {
                $regularPkgRadio.attr('data-price', price);
                $('#regular_pkg_price_display').text(price);
            }

            if($('.pkg-radio').length === 0 || $('.pkg-radio:checked').val() === "") {
                $('#product_price').val(price);
                $('#price_val').val(price);
                $('.price-amount').text(price);
            }
            
            $('#max_stock').val(stock);

            let $stockDiv = $('#stock_status');
            let $submitBtn = $('#submit_btn');

            if(stock > 0){
                $stockDiv.text('In Stock: ' + stock).removeClass('out-stock').addClass('in-stock');
                $submitBtn.prop('disabled', false).html("{{ $ln_pg->btn_text_form ?? $txtForm }}");
                
                if($('.pkg-radio').length === 0) {
                    $('.inner_qty').val(1);
                }
            } else {
                $stockDiv.text('Out of Stock').removeClass('in-stock').addClass('out-stock');
                $submitBtn.prop('disabled', true).html('Out of Stock');
                $('.inner_qty').val(0);
            }

            updatePackagePrice(); 
        });

        if($('#variation_select').length > 0) {
            $('#variation_select').trigger('change');
        } else {
            getCharge();
        }

        $(document).on('change', '#delivery_charge_id', function(){
            getCharge();
        });

        $(window).on('scroll', function(){
            if($(this).scrollTop() > 400) $('#scrollTopBtn').fadeIn(150);
            else $('#scrollTopBtn').fadeOut(150);
        });
        $('#scrollTopBtn').on('click', function(){
            $('html,body').animate({scrollTop: 0}, 400);
        });

        $(document).on('blur', 'input[name="mobile"]', function () {
            let mobile = $(this).val();
            let name = $('input[name="first_name"]').val();
            let address = $('input[name="shipping_address"]').val();
            let prd_id = $('input[name="prd_id"]').val(); 
            let variation_id = $('#variation_id').val();
            let quantity = $('.inner_qty').val();
            let amount = $('#price_val').val();
            let selected_package_id = $('input[name="selected_package_id"]:checked').val() || '';

            if (mobile && mobile.length === 11) {
                $.post("{{ route('incompleteStore') }}", {
                    mobile: mobile,
                    name: name,
                    address: address,
                    prd_id: prd_id,
                    variation_id: variation_id,
                    quantity: quantity,
                    amount: amount,
                    selected_package_id: selected_package_id,
                    _token: "{{ csrf_token() }}"
                });
            }
        });

        $(document).on('change', 'input[name="payment_method"]', function() {
            var method = $(this).val();
            var termsArea = $('#terms_checkbox_area');
            if (method === 'sslcommerz' || method === 'bkash' || method === 'eps' || method === 'nagad' || method === 'uddoktapay') {
                if(isTermsEnabled) termsArea.slideDown();
            } else {
                if(isTermsEnabled) termsArea.slideUp();
            }
        });
        
        $('input[name="payment_method"]:checked').trigger('change');

        $(document).on('submit', 'form#checkout_land_form', function (e) {
            e.preventDefault();
            
            var paymentMethod = $('input[name="payment_method"]:checked').val();

            if((paymentMethod === 'sslcommerz' || paymentMethod === 'bkash' || paymentMethod === 'eps' || paymentMethod === 'nagad' || paymentMethod === 'uddoktapay') && isTermsEnabled) {
                if(!$('#agree_terms').is(':checked')) {
                    $('#terms_error').removeClass('d-none');
                    return false;
                } else {
                    $('#terms_error').addClass('d-none');
                }
            }

            if(paymentMethod !== 'online' && paymentMethod !== 'Cash on Delivery' && paymentMethod !== 'cod' && paymentMethod !== 'sslcommerz' && paymentMethod !== 'bkash' && paymentMethod !== 'eps' && paymentMethod !== 'nagad' && paymentMethod !== 'uddoktapay') {
                if(!$('#sender_number').val() || !$('#transaction_id').val()) {
                    toastr.warning('দয়া করে পেমেন্ট নাম্বার এবং Transaction ID দিন');
                    return false;
                }
            }

            if(otpSystemEnabled == 1 && !isOtpVerified) {
                sendOtpLand();
                return;
            }

            var maxStock = toNumber($('#max_stock').val());
            if(maxStock <= 0) {
                toastr.error('Sorry, product is out of stock!');
                return;
            }

            var q = toNumber($('.inner_qty').val());
            if(q <= 0) q = 1;
            $('#product_quantity').val(q);

            getCharge();

            let $form = $(this);

            if (paymentMethod === 'sslcommerz') {
                $form.attr('action', "{{ url('/pay') }}");
                $form.attr('method', 'POST');
                $('#submit_btn').prop('disabled', true).html(processBtnText);
                e.currentTarget.submit();
                return;
            }

            var purchaseEventId = buildPurchaseEventId();
            $('#purchase_event_id').val(purchaseEventId);

            var url = "{{ route('front.storelandData') }}"; 
            var formData = $form.serialize();

            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            var $btn = $('#submit_btn');
            $btn.prop('disabled', true).html(processBtnText);

            $.ajax({
                type: 'POST',
                url: url,
                data: formData,
                success: function (res) {
                    if (res.success == true) {
                        if(paymentMethod === 'bkash') {
                            dynamicOrderId = res.order_id || res.url.split('/').pop();
                            successUrl = res.url;
                            @if(isset($information->bkash_active) && $information->bkash_active == 1)
                                initBkash();
                                setTimeout(() => { $('#bKash_button').click(); }, 300);
                            @endif
                        } else if(paymentMethod === 'nagad') {
                            let finalOrderId = res.order_id || res.url.split('/').pop();
                            window.location.href = "{{ url('nagad/pay') }}/" + finalOrderId;
                        } else if(paymentMethod === 'uddoktapay') {
                            let finalOrderId = res.order_id || res.url.split('/').pop();
                            window.location.href = "{{ url('uddoktapay/pay') }}/" + finalOrderId;
                        } else if(paymentMethod === 'eps') {
                            window.location.href = res.url;
                        } else {
                            toastr.success(res.msg); 
                            if (res.url) {
                                setTimeout(function(){ document.location.href = res.url; }, 800);
                            } else {
                                setTimeout(function(){ window.location.reload(); }, 800);
                            }
                        }
                    } else {
                        toastr.error(res.msg || 'Something went wrong!');
                        $btn.prop('disabled', false).html("{{ $ln_pg->btn_text_form ?? $txtForm }}");
                    }
                },
                error: function (response) {
                    $btn.prop('disabled', false).html("{{ $ln_pg->btn_text_form ?? $txtForm }}");
                    if(response.responseJSON && response.responseJSON.errors){
                        toastr.error('ফর্মের তথ্যগুলো সঠিকভাবে পূরণ করুন');
                    } else {
                        toastr.error('অর্ডার প্রসেস করতে সমস্যা হচ্ছে');
                    }
                }
            });
        });
        
        var selectedPaymentInit = $('input[name="payment_method"]:checked');
        if(selectedPaymentInit.length > 0){ 
            var initialMethod = selectedPaymentInit.val();
            if(initialMethod !== 'cod' && initialMethod !== 'sslcommerz' && initialMethod !== 'bkash' && initialMethod !== 'eps' && initialMethod !== 'nagad' && initialMethod !== 'uddoktapay') {
                var initialName = selectedPaymentInit.val();
                var initialNumber = selectedPaymentInit.data('number') || '';
                var initialType = selectedPaymentInit.data('type') || '';
                togglePaymentAction('manual', initialName, initialNumber, initialType);
            } else {
                togglePaymentAction(initialMethod);
            }
        }
    });
</script>

<script>
// ✅ ডাবল-অর্ডার গার্ড: এক ক্লিকে (বা OTP ভেরিফাইয়ে ডাবল কল হলে) দুইটা অর্ডার তৈরি হওয়া ঠেকায়।
// একটি অর্ডার-POST চলাকালীন দ্বিতীয় POST বাতিল হয়; সফল হলে আর নতুন POST যেতে দেয় না।
(function () {
    if (typeof $ === 'undefined' || window.__orderGuardReady) return;
    window.__orderGuardReady = true;
    var inFlight = false, completed = false;
    $.ajaxPrefilter(function (options, originalOptions, jqXHR) {
        var url = String(options.url || '');
        if (url.indexOf('store/landing/data') === -1 && url.indexOf('storelandData') === -1) return;
        if (inFlight || completed) { jqXHR.abort(); return; }
        inFlight = true;
        jqXHR.done(function (res) { if (res && (res.success === true || res.status === true)) completed = true; })
             .always(function () { inFlight = false; });
    });
})();
</script>
    @include('frontend.partials.exit_popup', ['epLandingMode' => true])
</body> 
</html>