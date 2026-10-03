<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $ln_pg->title1 ?? 'Premium Product Landing Page' }}</title>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- ✅ FontAwesome 6 Updated Here ✅ -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">

    @php
        $information    = \App\Models\Information::first();

        $brandGradient  = $ln_pg->theme_gradient_col ?? (optional($information)->gradient_code ?? 'linear-gradient(90deg,#0d6efd,#00276C)');
        $brandSolid     = $ln_pg->theme_primary_col ?? (optional($information)->primary_color ?? '#00276C');

        $btnBgColor     = $ln_pg->btn_bg_color ?? $brandSolid;
        $btnTextColor   = $ln_pg->btn_text_color ?? '#ffffff';

        $txtHero        = $ln_pg->btn_text_hero ?: 'অর্ডার করতে ক্লিক করুন';
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

        #toast-container { z-index: 9999999 !important; }
        #toast-container > .toast { opacity: 1 !important; box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important; }
        #toast-container > .toast-success { background-color: #28a745 !important; color: #ffffff !important; }
        #toast-container > .toast-error { background-color: #dc3545 !important; color: #ffffff !important; }
        .toast-message, .toast-title { color: #ffffff !important; }
        .toast-close-button { color: #ffffff !important; opacity: 0.8 !important; }

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

        button:disabled, .btn-primary-brand:disabled { background-color: #94a3b8 !important; border-color: #94a3b8 !important; cursor: not-allowed !important; transform: none !important; box-shadow: none !important; filter: none !important; }

        .offer-badge{ background: rgba(255,255,255,.92) !important; color: var(--text) !important; border: 0 !important; padding: 10px 14px; border-radius: 999px; font-size:16px; font-weight:900; box-shadow: 0 14px 30px rgba(2,6,23,.14); display:flex; gap:8px; align-items:center; }
        .offer-badge i{ color: var(--brand-solid); }

        .section-title{ background: var(--brand-solid) !important; color:#fff !important; border-radius: 999px; padding: 12px 16px; text-align:center; font-weight:900; font-size: 22px; box-shadow: 0 14px 30px rgba(2,6,23,.10); margin: 0 auto 14px; display:inline-block; }

        .cardx{ background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-xl); box-shadow: var(--shadow-soft); overflow:hidden; }
        .cardx-pad{ padding: 16px; }

        .top_section_img{ width:100%; border-radius: var(--radius-xl); box-shadow: var(--shadow-soft); border: 1px solid rgba(255,255,255,.35); }

        .video-16x9{ position: relative; width: 100%; padding-top: 56.25%; border-radius: var(--radius-xl); overflow: hidden; background: #000; box-shadow: var(--shadow-soft); }
        .video-16x9 iframe, .video-16x9 video{ position:absolute; inset:0; width:100% !important; height:100% !important; border:0; }

        .call-box{ background: linear-gradient(135deg, rgba(255,255,255,.96), rgba(255,255,255,.92)); border: 1px solid var(--border); border-radius: var(--radius-xl); text-align:center; padding: 16px 18px; box-shadow: var(--shadow-soft); }
        .call-pill{ display:inline-flex; gap:10px; align-items:center; padding: 10px 14px; border-radius: 999px; background: rgba(2,6,23,.04); border: 1px solid rgba(2,6,23,.08); font-weight: 900; }
        .call-pill a{ text-decoration:none; color: var(--brand-solid); }

        .left_side_text{ background: var(--brand-solid) !important; color:#fff !important; text-align:center; padding: 14px 16px; font-size: 22px; font-weight: 900; }
        .left_side_details{ padding: 14px 16px; color: var(--text); }

        .form-wrapper{ background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-xl); padding: 18px; box-shadow: var(--shadow-soft); }
        .billing-title{ font-weight:900; font-size: 34px; margin:0 0 14px 0; }
        .order-title{ font-weight:900; font-size: 34px; margin:0 0 14px 0; }

        .form-control{ border-radius: 14px !important; border: 1px solid rgba(0,39,108,.22) !important; box-shadow: 0 10px 24px rgba(0,39,108,.06); min-height: 48px; }
        .form-control:focus{ border-color: rgba(0,39,108,.45) !important; box-shadow: 0 14px 28px rgba(0,39,108,.12) !important; outline: none !important; }
        .form-select { border-radius: 14px !important; border: 1px solid var(--brand-solid) !important; padding: 12px; font-weight: 700; background-color: #fcfcfc; }

        @media (min-width: 992px){ .order-sticky{ position: sticky; top: 18px; } }

        .review-order-table{ width:100%; border-collapse: collapse; }
        .review-order-table thead th{ font-weight: 900; padding: 10px 0; border-bottom: 1px solid rgba(2,6,23,.10); color: var(--text); }
        .review-order-table td, .review-order-table th{ padding: 10px 0; vertical-align: top; }
        .product-image{ display:flex; align-items:center; gap:12px; }
        .product-thumbnail{ width: 58px; height: 58px; border-radius: 16px; overflow:hidden; border: 1px solid rgba(2,6,23,.08); background:#fff; box-shadow: 0 10px 25px rgba(2,6,23,.06); flex: 0 0 auto; }
        .product-thumbnail img{ width:100%; height:100%; object-fit:cover; }
        .product-name-td{ font-weight: 900; font-size: 16px; color: var(--text); }
        .price-amount{ font-weight: 900; font-size: 18px; color: var(--text); }

        .pro-qty{ display:flex; align-items:center; gap:8px; }
        .quantity-button{ width:38px; height:38px; border-radius: 12px; display:flex; align-items:center; justify-content:center; background: rgba(2,6,23,.06); border: 1px solid rgba(2,6,23,.10); cursor:pointer; font-weight: 900; user-select:none; }
        .inner_qty{ width:68px !important; height:38px; border-radius: 12px; border: 1px solid rgba(2,6,23,.12); text-align:center; font-weight: 900; box-shadow: 0 10px 24px rgba(2,6,23,.05); }

        .totals-row{ border-top: 1px dashed rgba(2,6,23,.18); padding-top: 10px; }
        #total{ font-size: 22px; font-weight: 900; color: var(--brand-solid); }

        .place-order .button.btn-primary-brand{ width:100% !important; padding: 16px 18px !important; border-radius: 22px !important; font-size: 20px !important; line-height: 1.1; }

        .whats_btn{ position: fixed; right: 16px; bottom: 16px; z-index: 9999; width: 54px; height: 54px; border-radius: 999px; background: #25D366; display:flex; align-items:center; justify-content:center; box-shadow: 0 10px 25px rgba(0,0,0,.25); text-decoration:none; }
        .whats_btn img{ width: 28px; height: 28px; }

        .scrollTopBtn{ position: fixed; right: 16px; bottom: 84px; z-index: 9999; width: 48px; height: 48px; border-radius: 999px; background: var(--brand-solid); color:#fff; border:0; display:none; align-items:center; justify-content:center; box-shadow: 0 10px 25px rgba(0,0,0,.20); cursor:pointer; }

        .stock-status { font-size: 14px; font-weight: 800; margin-top: 4px; display: block; }
        .in-stock { color: #10b981; }
        .out-stock { color: #ef4444; }
        
        .coupon-section { background-color: #f8f9fa; border: 1px dashed #ced4da; border-radius: 12px; padding: 15px; margin-top: 15px; }
        .coupon-input-group { display: flex; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-radius: 50px; overflow: hidden; border: 1px solid #e9ecef; }
        .coupon-input-group input { border: none; padding: 12px 20px; flex-grow: 1; font-size: 14px; outline: none; min-width: 0; }
        .coupon-input-group button { border: none; background: #212529; color: #fff; padding: 0 25px; font-weight: 700; font-size: 14px; cursor: pointer; transition: background 0.3s; flex-shrink: 0; }
        .coupon-input-group button:hover { background: #000; }
        
        .payment-label { cursor: pointer; transition: all 0.2s; border: 1px solid rgba(0,0,0,0.1); }
        .payment-label:hover { background-color: #f8f9fa !important; border-color: rgba(0,0,0,0.2); }
        input[name="payment_method"]:checked + span { color: var(--brand-solid); }
        input[name="payment_method"]:checked ~ .payment-label { border-color: var(--brand-solid) !important; background-color: rgba(0, 39, 108, 0.05) !important; }

        /* ✅✅✅ FIXED ALERT BOX CSS ✅✅✅ */
        .premium-notice-box {
            display: flex;
            align-items: flex-start;
            background: #fff5f5;
            border: 1px dashed #ef4444;
            border-radius: 12px;
            padding: 14px;
            gap: 12px;
        }
        .notice-icon {
            font-size: 24px;
            color: #ef4444;
            flex-shrink: 0;
            margin-top: 2px;
        }
        .notice-text {
            font-size: 14px;
            color: #7f1d1d;
            font-weight: 600;
            line-height: 1.5;
            margin-bottom: 0;
        }
        .notice-text p {
            margin-bottom: 0 !important;
        }

        #otpModal { z-index: 99999 !important; }
        .otp-modal-content { border: none !important; border-radius: 20px !important; background: #ffffff; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2); text-align: center; overflow: hidden; position: relative; }
        .otp-modal-content::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #E2136E, #F6921E); }
        .otp-icon-box { width: 80px; height: 80px; background: #fdf2f7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 10px auto 20px; color: #E2136E; }
        .otp-input { width: 100%; letter-spacing: 15px; text-align: center; font-size: 28px; font-weight: bold; color: #333; border: 2px solid #eee !important; border-radius: 12px !important; background: #fafafa; height: 65px; transition: all 0.3s ease; position: relative; z-index: 999999 !important; }
        .otp-input:focus { border-color: #E2136E !important; background: #fff; box-shadow: 0 5px 15px rgba(226, 19, 110, 0.1) !important; outline: none; }
        .btn-verify { background: linear-gradient(135deg, #E2136E 0%, #C90D5E 100%); border: none; padding: 12px; font-size: 18px; border-radius: 12px; box-shadow: 0 8px 20px rgba(226, 19, 110, 0.3); width: 100%; color: white; font-family: 'Hind Siliguri', sans-serif; }
        .btn-verify:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(226, 19, 110, 0.4); }

        @media (max-width: 767px){
            .billing-title, .order-title{ font-size: 28px; }
            .hero-inner{ padding: 18px 14px; }
            .top-div{ border-bottom-left-radius: 22px; border-bottom-right-radius: 22px; }
            .review-order-table { table-layout: fixed; width: 100%; }
            .variation-wrap { width: 100%; overflow: hidden; }
            #variation_select, .form-select { max-width: 100%; width: 100%; white-space: nowrap; text-overflow: ellipsis; overflow: hidden; }
            .whats_btn, .scrollTopBtn { display: none !important; }
        }

        #manual_payment_area {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            padding: 15px;
            margin-top: 15px;
            border-radius: 8px;
        }
        .manual-instruction-box {
            background: rgba(226, 19, 110, 0.08);
            border: 1px solid rgba(226, 19, 110, 0.2);
            color: #C90D5E;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .input-icon-wrap {
            position: relative;
        }
        .input-icon-wrap i {
            position: absolute;
            top: 50%;
            left: 16px;
            transform: translateY(-50%);
            color: var(--muted);
            z-index: 10;
            font-size: 15px;
        }
        .input-icon-wrap input {
            padding-left: 45px !important;
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
        <noscript>
        <img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1"/>
        </noscript>
    @endif

    {{-- Landing page ViewContent / InitiateCheckout + GA4 ecommerce.
         The ids are the exact ones the server used for the Meta CAPI and the TikTok
         Events API calls, so both platforms can deduplicate browser against server. --}}
    <script>
    (function(){
        window.LP_EVENT_BASE  = {!! json_encode($lpEventBase ?? null) !!} || ('LP_{{ $productId }}_' + Date.now());
        window.LP_EVENT_ID_VC = window.LP_EVENT_BASE + '_VC';
        window.LP_EVENT_ID_IC = window.LP_EVENT_BASE + '_IC';

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
                    fbq('track', 'ViewContent', { content_ids: ['{{ $productId }}'], content_name: @json($productName), content_type: 'product', content_category: @json($contentCategory), value: {{ $lpTrackValue }}, currency: 'BDT' }, {eventID: window.LP_EVENT_ID_VC});
                    fbq('track', 'InitiateCheckout', { content_ids: ['{{ $productId }}'], content_name: @json($productName), content_type: 'product', value: {{ $lpTrackValue }}, currency: 'BDT', num_items: 1 }, {eventID: window.LP_EVENT_ID_IC});
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

    <script>
        var timeSteps = [10, 30, 60, 120]; 
        timeSteps.forEach(function(seconds) {
            setTimeout(function() {
                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({
                    'event': 'time_on_page',
                    'time_spent': seconds,
                    'page_path': window.location.pathname
                });
                if (typeof fbq === 'function') {
                    @if(!$__hasGtm) fbq('trackCustom', 'TimeSpent', { time_in_seconds: seconds }); @endif
                }
            }, seconds * 1000);
        });

        var scrollSteps = [25, 50, 75, 90, 100];
        var scrolled = [];
        window.addEventListener('scroll', function() {
            var s = window.scrollY,
                d = document.documentElement.scrollHeight,
                c = window.innerHeight;
            var scrollPercent = (s / (d - c)) * 100;

            scrollSteps.forEach(function(step) {
                if (scrollPercent >= step && !scrolled.includes(step)) {
                    scrolled.push(step);
                    window.dataLayer = window.dataLayer || [];
                    window.dataLayer.push({
                        'event': 'scroll_depth',
                        'scroll_percent': step,
                        'page_path': window.location.pathname
                    });
                    if (typeof fbq === 'function') {
                        @if(!$__hasGtm) fbq('trackCustom', 'ScrollDepth', { scroll_percent: step }); @endif
                    }
                }
            });
        });
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
                <div class="hero-sub">{{ $ln_pg->call_text ?? '' }}</div>
                <div class="hero-actions" data-aos="fade-up" data-aos-duration="900">
                    <a href="javascript:void(0)" class="btn btn-primary-brand js-scroll-order">
                        <i class="fas fa-shopping-cart"></i>&nbsp;&nbsp;{{ $txtHero }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid-landing" style="margin-top:12px;">
        <div class="cardx" style="padding:14px;">
            <img src="{{ asset('landing_pages/'.$ln_pg->landing_bg) }}" class="top_section_img" alt="img">
            <div style="text-align:center; margin-top:14px;">
                <span class="section-title">{{ $ln_pg->title1 }}</span>
            </div>
        </div>
    </div>

    <div class="container-fluid-landing" style="margin-top:18px; margin-bottom:22px;">

        <div class="cardx cardx-pad" data-aos="zoom-in">
            <div class="video-16x9">{!! $ln_pg->video_url !!}</div>
            <div style="margin-top:16px; text-align:center;">
                <button type="button" class="btn btn-primary-brand js-scroll-order">
                    {{ $txtVideo }} <img src="{{ asset('frontend/images/hand.png') }}" style="width:22px; height:auto; margin-left:6px;" alt="">
                </button>
            </div>
        </div>

        <div class="call-box" style="margin-top:16px;">
            <div class="call-pill">
                <i class="fa-solid fa-phone"></i>
                <a href="tel:{{ $ln_pg->phone }}">{{ $ln_pg->phone }}</a>
            </div>
        </div>

        <div class="cardx" style="margin-top:16px;">
            <div class="left_side_text">{{ $ln_pg->left_side_title }}</div>
            <div class="left_side_details">{!! $ln_pg->left_side_desc !!}</div>
        </div>

        <div class="cardx cardx-pad" style="margin-top:16px;">
            <div style="text-align:center;">
                <span class="section-title">{{ $ln_pg->feature }}</span>
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
            <div style="margin-top:16px; text-align:center;">
                <button type="button" class="btn btn-primary-brand js-scroll-order">
                    {{ $txtFeature }} <img src="{{ asset('frontend/images/hand.png') }}" style="width:22px; height:auto; margin-left:6px;" alt="">
                </button>
            </div>
        </div>

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

        <div id="element_widget" style="margin-top:16px;">
            <div class="cardx cardx-pad">
                <div style="text-align:center; margin-bottom:10px;">
                    <span class="section-title">{{ BanglaText('land_instruction') }}</span>
                </div>

                <div class="form-wrapper">
                    <form action="{{ route('front.storelandData') }}" method="POST" id="checkout_land_form">
                        @csrf
                        {{-- কোন landing page থেকে order এলো (Order Management-এর Source-এ দেখায়) --}}
                        <input type="hidden" name="landing_page_type" value="4">
                        <input type="hidden" name="purchase_event_id" id="purchase_event_id" value="">
                        
                        <div class="form-group mb-4">
                            <label style="font-weight:900; margin-bottom: 10px; display:block; font-size: 18px;">
                                পেমেন্ট মেথড সিলেক্ট করুন:
                            </label>
                            
                            <div class="d-flex flex-column gap-2">
                                @if(isset($information->cod_active) && $information->cod_active == 1)
                                <label class="payment-label p-3 border rounded-3 d-flex align-items-center gap-3 bg-white shadow-sm" style="cursor:pointer;">
                                    <input class="form-check-input mt-0" type="radio" name="payment_method" id="payment_cod" value="cod" checked style="width: 20px; height: 20px;" onchange="togglePaymentAction('cod')">
                                    <span class="fw-bold fs-5 text-dark">ক্যাশ অন ডেলিভারি (Cash on Delivery)</span>
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
                                    <input class="form-check-input mt-0" type="radio" name="payment_method" value="{{ $mp->name }}" style="width: 20px; height: 20px;" onchange="togglePaymentAction('manual', '{{ $mp->name }}', '{{ $mp->number }}', '{{ $mp->type }}')">
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
                                        <label style="font-weight:800;">{{ BanglaText('name') }} <span style="color:#ef4444">*</span></label>
                                        <input type="text" name="first_name" class="form-control">
                                        
                                        <input type="hidden" value="{{ $productId }}" name="prd_id">
                                    </div>

                                    <div class="form-group mb-3">
                                        <label style="font-weight:800;">{{ BanglaText('mobile') }} <span style="color:#ef4444">*</span></label>
                                        <input type="tel" name="mobile" class="form-control" maxlength="11" placeholder="017XXXXXXXX">
                                    </div>

                                    <input type="hidden" id="variation_id" name="variation_id" value="{{ $defaultVarId }}">
                                    <input type="hidden" id="total_price_val" name="final_amount" value="">
                                    <input type="hidden" id="shipping_cost" value="0">
                                    
                                    <input type="hidden" name="amount" value="">
                                    
                                    <input type="hidden" id="product_price" value="{{ $defaultPrice }}">
                                    <input type="hidden" id="product_quantity" name="quantity" value="1">
                                    <input type="hidden" id="max_stock" value="{{ $defaultStock }}">

                                    <div class="form-group mb-3">
                                        <label style="font-weight:800;">{{ BanglaText('address') }} <span style="color:#ef4444">*</span></label>
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
                                                <input type="radio" name="selected_package_id" value="" data-price="{{ $defaultPrice }}" data-qty="1" class="pkg-radio" {{ !$defaultPkgId ? 'checked' : '' }} autocomplete="off">
                                                <span class="pkg-title">
                                                    (১ পিস) {{ $productName }}
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="pkg-qty-box">
                                                    <input type="text" class="pkg-qty-input" value="1" readonly>
                                                </div>
                                                <span class="pkg-price"><span id="regular_pkg_price_display">{{ $defaultPrice }}</span> ৳</span>
                                            </div>
                                        </label>

                                        @foreach($ln_pg->packages as $pkg)
                                        <label class="product-package-card {{ $defaultPkgId == $pkg->id ? 'active-pkg' : '' }}">
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="radio" name="selected_package_id" value="{{ $pkg->id }}" data-price="{{ $pkg->price }}" data-qty="{{ $pkg->qty }}" class="pkg-radio" {{ $defaultPkgId == $pkg->id ? 'checked' : '' }} autocomplete="off">
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
                                                <th class="product-total" style="text-align:right;">Subtotal</th>
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
                                                <td class="product-total" style="text-align:right;">
                                                    <span id="price" class="price-amount amount">{{ $defaultPrice }}</span>
                                                    <input type="hidden" id="price_val" value="{{ $defaultPrice }}">

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

                                            <tr>
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
                                                <td colspan="2">
                                                    <div class="coupon-section">
                                                        <label class="fw-bold mb-2 text-dark" style="font-size:15px;">কুপন কোড (যদি থাকে)</label>
                                                        <div class="coupon-input-group">
                                                            <input type="text" id="coupon_code" placeholder="Enter coupon code">
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
                                                <td style="font-weight:900;">Total</td>
                                                <td style="text-align:right;"><strong><span id="total" class="Price-amount amount"></span></strong></td>
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
                                                {{ $txtForm }}
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
    var isFreeShipping = {{ $isFreeShipping ? 1 : 0 }};

    var paymentID;
    var dynamicOrderId;
    var successUrl = '';

    function toNumber(v){
        v = (v ?? '').toString().replace(/[^\d.]/g,'');
        var n = parseFloat(v);
        return isNaN(n) ? 0 : n;
    }

    function waitForFbq(cb, tries){
        tries = tries || 0;
        if(typeof window.fbq === 'function') return cb();
        if(tries > 60) return;
        setTimeout(function(){ waitForFbq(cb, tries+1); }, 100);
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
            data: { 
                code: code,
                total_price: current_total 
            },
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
                if(!isResend) $btn.prop('disabled', false).text("{{ $txtForm }}");
                
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
                if(!isResend) $btn.prop('disabled', false).text("{{ $txtForm }}");
                console.log(err);
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
                            $('#submit_btn').prop('disabled', false).html("{{ $txtForm }}");
                        }
                    },
                    error: function (err) {
                        bKash.create().onError();
                        toastr.error("Server error while connecting to bKash.");
                        $('#submit_btn').prop('disabled', false).html("{{ $txtForm }}");
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
                            $('#submit_btn').prop('disabled', false).html("{{ $txtForm }}");
                        }
                    },
                    error: function () {
                        bKash.execute().onError();
                        toastr.error("Failed to execute bKash payment.");
                        $('#submit_btn').prop('disabled', false).html("{{ $txtForm }}");
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
            "extendedTimeOut": "1000",
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

        // ✅✅✅ Quantity Buttons Logic Fix ✅✅✅
        $(document).on('click', '.increase-qty', function() {
            let maxStock = parseInt($('#max_stock').val()) || 1;
            let qtyInput = $('.inner_qty');
            let currentQty = parseInt(qtyInput.val()) || 1;
            
            if (currentQty < maxStock) {
                qtyInput.val(currentQty + 1);
                getCharge();
            } else {
                toastr.warning('স্টক লিমিট শেষ!');
            }
        });

        $(document).on('click', '.decrease-qty', function() {
            let qtyInput = $('.inner_qty');
            let currentQty = parseInt(qtyInput.val()) || 1;
            
            if (currentQty > 1) {
                qtyInput.val(currentQty - 1);
                getCharge();
            }
        });

        $(document).on('keyup', '.inner_qty', function() {
            let val = parseInt($(this).val());
            let max = parseInt($('#max_stock').val());
            
            if(isNaN(val) || val < 1) {
                $(this).val(1);
            } else if (val > max) {
                $(this).val(max);
                toastr.warning('স্টক লিমিট শেষ!');
            }
            getCharge();
        });

        function resetToBasePackage() {
            if($('.pkg-radio').length > 0) {
                // ✅ FIX: প্যাকেজ (২/৩ পিস) সিলেক্ট থাকা অবস্থায় qty +/- চাপলে দাম regular-এ
                // ফিরত কিন্তু qty প্যাকেজেরটাই (2/3) থেকে যেত → দাম কয়েকগুণ দেখাত।
                let $cur = $('.pkg-radio:checked');
                if($cur.length > 0 && $cur.val() !== '') { $('.inner_qty').val(1); }
                $('.pkg-radio').prop('checked', false);
                $('.product-package-card').removeClass('active-pkg');
                let $regularPkg = $('.pkg-radio[value=""]');
                if($regularPkg.length > 0) {
                    $regularPkg.prop('checked', true);
                    $regularPkg.closest('.product-package-card').addClass('active-pkg');
                    let basePrice = parseFloat($regularPkg.attr('data-price'));
                    $('#price_val').val(basePrice);
                    $('.price-amount').text(basePrice.toFixed(2));
                }
            }
        }

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
            $('.pkg-radio').prop('checked', false);
            $('.product-package-card').removeClass('active-pkg');

            $('.product-package-card').first().addClass('active-pkg').find('.pkg-radio').prop('checked', true);
            
            updatePackagePrice();
        }

        $('.pkg-radio').on('change', function() {
            $('.product-package-card').removeClass('active-pkg');
            $(this).closest('.product-package-card').addClass('active-pkg');
            updatePackagePrice();
        });

        $('.product-package-card').on('click', function(e) {
            if (!$(e.target).is('.pkg-radio')) {
                $(this).find('.pkg-radio').prop('checked', true).trigger('change');
            }
        });

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
                $submitBtn.prop('disabled', false).html("{{ $txtForm }}");
                
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
            $('span.textdanger').remove();
            
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
                $('#submit_btn').prop('disabled', true).html('Processing Payment...');
                e.currentTarget.submit();
                return;
            }

            var purchaseEventId = buildPurchaseEventId();
            $('#purchase_event_id').val(purchaseEventId);

            var url = "{{ route('front.storelandData') }}"; 
            var formData = $form.serialize();

            var total = toNumber($('#total_price_val').val());
            var ship_charge = toNumber($('span#delvry_charge').text() || 0);

            var unitPrice = toNumber($('#price_val').val());
            var pid = "{{ $productId }}";
            var pname = @json($productName);
            var cat = @json($contentCategory);

            var items = [{
                item_id: pid,
                item_name: pname,
                item_category: cat,
                price: unitPrice,
                quantity: q
            }];

            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            var $btn = $('#submit_btn');
            $btn.prop('disabled', true).html('Processing...');

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
                                setTimeout(function(){
                                    document.location.href = res.url;
                                }, 800);
                            } else {
                                setTimeout(function(){
                                    window.location.reload();
                                }, 800);
                            }
                        }
                    } else {
                        toastr.error(res.msg || 'Something went wrong!');
                        $btn.prop('disabled', false).html("{{ $txtForm }}");
                    }
                },
                error: function (response) {
                    $btn.prop('disabled', false).html("{{ $txtForm }}");
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