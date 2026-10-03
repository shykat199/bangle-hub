<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $ln_pg->title1 ?? 'Special Offer' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- Fonts & Icons --}}
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    {{-- AOS Animation CSS for Premium Feel --}}
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    @php
        $information = \App\Models\Information::first();
        $primaryColor = $ln_pg->theme_primary_col ?? '#e11d48'; 
        $gradient = $ln_pg->theme_gradient_col ?? 'linear-gradient(135deg, #fff1f2 0%, #fce7f3 100%)';
        $btnColor = $ln_pg->btn_bg_color ?? '#be123c';
        $btnText = $ln_pg->btn_text_color ?? '#ffffff';

        $currentProduct = $product ?? $ln_pg->product ?? null;
        $variations = collect();
        
        $globalSetting = DB::table('delivery_charges')->first();
        $isWeightBased = $globalSetting && $globalSetting->charge_type == 'weight_based';
        $isFreeShipping = (!empty($currentProduct->is_free_shipping) && $currentProduct->is_free_shipping == 1) ? 1 : 0;
        $pixelId = setting('fb_pixel_id') ?? null;
        
        // Size and Color Seperation Logic
        $sizes = collect();
        $colors = collect();
        $varMatrix = [];

        if($currentProduct) {
            try {
                $currentProduct->loadMissing(['variations.size','variations.color', 'variations.stocks', 'category']);
                $variations = $currentProduct->variations ?? collect();
                
                if($variations->count() > 0) {
                    foreach($variations as $v) {
                        if($v->size) $sizes->push($v->size);
                        if($v->color) $colors->push($v->color);
                        
                        $sId = $v->size_id ?? 0;
                        $cId = $v->color_id ?? 0;
                        
                        $vPrice = ((float)($v->after_discount_price ?? 0) > 0) ? $v->after_discount_price : ($v->price ?? $currentProduct->sell_price ?? 0);
                        
                        // 🟢 Show Page-এর মতো অ্যাডভান্স স্টক লজিক সিঙ্ক করা হলো 🟢
                        $sumStock = (int)($v->stocks ? $v->stocks->sum('quantity') : 0);
                        $varStock = (int)($v->stock_quantity ?? 0);
                        $finalStock = $sumStock > 0 ? $sumStock : ($varStock > 0 ? $varStock : (int)($currentProduct->stock_quantity ?? 0));

                        $varMatrix["{$sId}_{$cId}"] = [
                            'id' => $v->id,
                            'price' => $vPrice,
                            'stock' => $finalStock
                        ];
                    }
                }
                
                $sizes = $sizes->unique('id')->values();
                $colors = $colors->unique('id')->values();
                
            } catch(\Throwable $e) {}
        }

        $defaultPrice = $currentProduct->after_discount ?? $currentProduct->sell_price ?? 0;
        $defaultStock = $currentProduct ? max((int) ($currentProduct->stock_quantity ?? 0), (int) $currentProduct->stocks()->sum('quantity')) : 0;
        
        $productId = $currentProduct->id ?? 0;
        $productName = $currentProduct->name ?? '';
        $contentCategory = $currentProduct->category->name ?? 'Landing Page';

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
        :root { 
            --primary: {{ $primaryColor }}; 
            --btn-bg: {{ $btnColor }}; 
            --btn-text: {{ $btnText }}; 
            --text-color: #1f2937;
        }

        html, body {
            overflow-x: hidden;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        body { 
            font-family: 'Hind Siliguri', sans-serif; 
            color: var(--text-color);
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        a {
            text-decoration: none !important;
        }

        .page-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1; 
            
            @if(!empty($ln_pg->landing_bg))
                background-image: linear-gradient(rgba(17, 24, 39, 0.7), rgba(17, 24, 39, 0.85)), url('{{ asset("landing_pages/".$ln_pg->landing_bg) }}');
            @else
                background: {{ $gradient }};
            @endif
            
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            will-change: transform; 
        }

        .main-content-wrap {
            position: relative;
            z-index: 1;
            padding-bottom: 40px;
        }

        .container-box { max-width: 950px; margin: 0 auto; padding: 15px; }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 25px;
            transition: transform 0.2s;
        }

        .hero-title { font-weight: 800; font-size: clamp(24px, 5vw, 42px); color: var(--primary); line-height: 1.3; margin-bottom: 15px; text-shadow: 1px 1px 2px rgba(255,255,255,0.8); }
        .section-heading { background: var(--primary); color: #fff; padding: 10px 25px; border-radius: 50px; display: inline-block; font-weight: 700; font-size: 20px; text-align: center; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }

        .video-wrapper { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 16px; border: 3px solid #fff; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .video-wrapper iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
        
        .slider-img { border-radius: 12px; width: 100%; border: 2px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1); transition: transform 0.3s ease;}
        .slider-img:hover { transform: scale(1.02); }

        .hero-banner-img {
            width: 100%;
            height: auto; 
            object-fit: contain; 
            border-radius: 16px; 
            border: 3px solid #fff; 
            box-shadow: 0 8px 20px rgba(0,0,0,0.1); 
            margin-top: 15px;
            margin-bottom: 25px;
            display: block;
        }

        @keyframes phone-glow {
            0% { box-shadow: 0 0 5px rgba(37, 211, 102, 0.4); transform: scale(1); }
            50% { box-shadow: 0 0 20px rgba(37, 211, 102, 0.8); transform: scale(1.03); }
            100% { box-shadow: 0 0 5px rgba(37, 211, 102, 0.4); transform: scale(1); }
        }

        .call-now-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(37, 211, 102, 0.1);
            color: #16a34a !important; 
            padding: 10px 25px;
            border-radius: 50px;
            border: 1px solid rgba(37, 211, 102, 0.4);
            font-weight: 800;
            font-size: 1.1rem;
            animation: phone-glow 2s infinite ease-in-out;
            transition: all 0.3s ease;
            text-shadow: 0 1px 1px rgba(255,255,255,0.8);
        }
        .call-now-btn:hover {
            background: rgba(37, 211, 102, 0.2);
            color: #15803d !important;
        }
        .call-now-btn i { font-size: 1.2rem; }

        .form-label { font-weight: 700; color: #374151; font-size: 14px; margin-bottom: 6px; }
        .form-control, .form-select { border-radius: 10px; padding: 12px 15px; border: 1px solid #d1d5db; font-size: 15px; background: #fff; transition: all 0.3s ease; }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(0,0,0,0.05); outline: none; }

        @keyframes btn-glow {
            0% { box-shadow: 0 0 0 0 {{ $btnColor }}; }
            50% { box-shadow: 0 0 20px {{ $btnColor }}; }
            100% { box-shadow: 0 0 0 0 {{ $btnColor }}; }
        }

        @keyframes whatsapp-glow {
            0% { box-shadow: 0 0 0 0 #25D366; }
            50% { box-shadow: 0 0 20px #25D366; }
            100% { box-shadow: 0 0 0 0 #25D366; }
        }

        .btn-brand { 
            background: var(--btn-bg); 
            color: var(--btn-text); 
            font-weight: 800; 
            padding: 14px 30px; 
            border-radius: 50px; 
            border: none; 
            width: 100%; 
            font-size: 18px; 
            transition: 0.3s; 
            animation: btn-glow 2s infinite; 
            display: inline-block;
            text-align: center;
        }
        
        .btn-brand:hover { transform: translateY(-2px); filter: brightness(1.1); color: var(--btn-text); }
        .btn-brand:disabled { background: #9ca3af; transform: none; box-shadow: none; cursor: not-allowed; animation: none; }
        
        .payment-box { border: 2px solid #e5e7eb; border-radius: 10px; padding: 12px 15px; cursor: pointer; transition: 0.2s; background: #fff; display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
        input[name="payment_method"]:checked + .payment-box { border-color: var(--primary); background: rgba(0,0,0,0.02); }

        .order-summary { background: #f9fafb; border-radius: 16px; padding: 20px; border: 1px solid #e5e7eb; }
        .summary-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px dashed #d1d5db; font-size: 15px; }
        .summary-row.total { font-size: 18px; font-weight: 800; color: var(--primary); border-bottom: none; padding-top: 15px; }
        
        .qty-box { display: flex; align-items: center; background: #fff; border-radius: 8px; border: 1px solid #d1d5db; overflow: hidden; }
        .qty-btn { background: #f3f4f6; border: none; padding: 5px 15px; font-weight: 800; font-size: 18px; color: var(--primary); cursor: pointer; transition: background 0.2s;}
        .qty-btn:hover { background: #e5e7eb; }
        .qty-input { width: 40px; border: none; text-align: center; font-weight: 800; font-size: 15px; }
        
        .var-btn { border: 2px solid #e5e7eb; padding: 8px 12px; border-radius: 8px; cursor: pointer; margin: 0 5px 8px 0; background: #fff; font-weight: 600; font-size: 13px; display: inline-block; transition: all 0.2s; color: #4b5563; }
        .var-btn:hover { border-color: #adb5bd; }
        .var-btn.active { border-color: var(--primary); color: var(--primary); background: rgba(99, 102, 241, 0.05); box-shadow: 0 2px 8px rgba(0,0,0,0.05); }

        .whats_btn { 
            position: fixed; 
            right: 16px; 
            bottom: 80px; 
            z-index: 9999; 
            width: 54px; 
            height: 54px; 
            border-radius: 999px; 
            background: #25D366; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            text-decoration: none; 
            animation: whatsapp-glow 2s infinite;
        }
        .whats_btn img { width: 32px; height: 32px; }
        
        .footer-wrapper { background: #fff; position: relative; z-index: 5; }

        /* ✅✅✅ OTP Modal CSS ✅✅✅ */
        #otpModal { z-index: 99999 !important; }
        .otp-modal-content { border: none !important; border-radius: 20px !important; background: #ffffff; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2); text-align: center; overflow: hidden; position: relative; }
        .otp-modal-content::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #E2136E, #F6921E); }
        .otp-icon-box { width: 80px; height: 80px; background: #fdf2f7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 10px auto 20px; color: #E2136E; }
        .otp-input { width: 100%; letter-spacing: 15px; text-align: center; font-size: 28px; font-weight: bold; color: #333; border: 2px solid #eee !important; border-radius: 12px !important; background: #fafafa; height: 65px; transition: all 0.3s ease; position: relative; z-index: 999999 !important; }
        .otp-input:focus { border-color: #E2136E !important; background: #fff; box-shadow: 0 5px 15px rgba(226, 19, 110, 0.1) !important; outline: none; }
        .btn-verify { background: linear-gradient(135deg, #E2136E 0%, #C90D5E 100%); border: none; padding: 12px; font-size: 18px; border-radius: 12px; box-shadow: 0 8px 20px rgba(226, 19, 110, 0.3); width: 100%; color: white; font-family: 'Hind Siliguri', sans-serif; }
        .btn-verify:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(226, 19, 110, 0.4); }

        @media (max-width: 768px) {
            .glass-card { 
                padding: 20px 15px; 
                border-radius: 16px;
                backdrop-filter: none !important;        
                -webkit-backdrop-filter: none !important;
                background: #ffffff !important; 
                border: 1px solid #e5e7eb !important;
                box-shadow: 0 5px 15px rgba(0,0,0,0.05) !important;
                transform: translateZ(0); 
            }
            .hero-title { font-size: 24px; }
            .order-summary { padding: 15px; }
            .payment-box span { font-size: 14px; }
            .summary-row { font-size: 14px; flex-wrap: wrap; }
            .qty-box { margin-top: 5px; }
            .footer-wrapper { padding-bottom: 70px; }
            .btn-brand:hover { transform: none; }
            .whats_btn { display: none !important; }
        }
    </style>

    {{-- ✅ GTM + Tracking Code --}}
    {!! optional($information)->tracking_code !!}

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

    {{-- FB Pixel Setup --}}
    @if(!empty($pixelId))
        <script>
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ $pixelId }}');
        @if(!$__hasGtm)
        fbq('track', 'PageView');
        @endif
        </script>
        <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1"/></noscript>
    @endif

    {{-- Landing page ViewContent / InitiateCheckout + GA4 ecommerce.
         The ids are the exact ones the server used for the Meta CAPI and the TikTok
         Events API calls, so both platforms can deduplicate browser against server.
         Deliberately outside the FB pixel @if: GTM/TikTok must not depend on it. --}}
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
                if(@json(!$__hasGtm) && typeof fbq === 'function') {
                    fbq('track', 'ViewContent', { content_ids: ['{{ $productId }}'], content_name: @json($productName), content_type: 'product', content_category: @json($contentCategory), value: {{ $lpTrackValue }}, currency: 'BDT' }, {eventID: window.LP_EVENT_ID_VC});
                    fbq('track', 'InitiateCheckout', { content_ids: ['{{ $productId }}'], content_name: @json($productName), content_type: 'product', value: {{ $lpTrackValue }}, currency: 'BDT', num_items: 1 }, {eventID: window.LP_EVENT_ID_IC});
                }
            }catch(e){}
            try{
                if(@json(!$__ttFromGtm) && typeof ttq !== 'undefined' && ttq.track) {
                    ttq.track('ViewContent', { content_type: 'product', value: {{ $lpTrackValue }}, currency: 'BDT', contents: [{ content_id: '{{ $productId }}', content_type: 'product', content_name: @json($productName), price: {{ $lpTrackValue }}, quantity: 1 }] }, { event_id: window.LP_EVENT_ID_VC });
                    ttq.track('InitiateCheckout', { content_type: 'product', value: {{ $lpTrackValue }}, currency: 'BDT', contents: [{ content_id: '{{ $productId }}', content_type: 'product', content_name: @json($productName), price: {{ $lpTrackValue }}, quantity: 1 }] }, { event_id: window.LP_EVENT_ID_IC });
                }
            }catch(e){}
        }
        if(document.readyState === 'complete'){ fireLPEvents(); }
        else { window.addEventListener('load', fireLPEvents); }
    })();
    </script>

    {{-- Engagement signals (dataLayer + optional pixel custom events) --}}
    <script>
        // Time Tracking (DataLayer & Pixel)
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

        // Scroll Tracking (DataLayer & Pixel)
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

<div class="page-background"></div>

<div class="main-content-wrap">
    <div class="container-box">
        
        {{-- 1. Hero Section --}}
        <div class="glass-card text-center mt-3" data-aos="zoom-in" data-aos-duration="800">
            <h1 class="hero-title bg-white bg-opacity-75 d-inline-block px-3 py-1 rounded">{{ $ln_pg->title1 }}</h1>
            
            {{-- ✅✅✅ Updated Phone Number Logic ✅✅✅ --}}
            @php 
                // Checking all possible column names for the phone number
                $displayPhone = $ln_pg->phone_number ?? $ln_pg->phone ?? $ln_pg->call_text ?? $ln_pg->mobile ?? $ln_pg->whatsapp ?? optional($information)->phone ?? ''; 
            @endphp
            
            @if(!empty($displayPhone)) 
                <div class="mb-3 mt-2">
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $displayPhone) }}" class="call-now-btn">
                        <i class="fas fa-phone-alt fa-shake"></i> {{ $displayPhone }}
                    </a>
                </div> 
            @else
                <div class="mb-3 mt-2">
                    <a href="tel:01700000000" class="call-now-btn" style="border-color: #fca5a5; color: #e11d48 !important; background: #fef2f2;">
                        <i class="fas fa-exclamation-circle fa-shake"></i> কলামের নাম মেলেনি
                    </a>
                </div>
            @endif
            
            @if(!empty($ln_pg->landing_bg))
                <img src="{{ asset('landing_pages/'.$ln_pg->landing_bg) }}" class="hero-banner-img" alt="Special Offer">
            @endif

            @if($ln_pg->video_url)
                <div class="video-wrapper mt-2 mb-4">{!! $ln_pg->video_url !!}</div>
            @endif
            
            <a href="#order_section" class="btn-brand mx-auto" style="max-width: 350px;">
                <i class="fas fa-shopping-cart"></i> {{ $ln_pg->btn_text_hero ?? 'অর্ডার করতে ক্লিক করুন' }}
            </a>
        </div>

        {{-- 2. Details Section --}}
        @if($ln_pg->left_side_desc || $ln_pg->left_side_title)
        <div class="glass-card" data-aos="fade-up" data-aos-delay="100">
            @if($ln_pg->left_side_title)
                <div class="text-center"><div class="section-heading">{{ $ln_pg->left_side_title }}</div></div>
            @endif
            <div style="font-size: 15px; line-height: 1.6; word-wrap: break-word;">
                {!! $ln_pg->left_side_desc !!}
            </div>
        </div>
        @endif

        {{-- 3. Product Gallery --}}
        @if($ln_pg->images && count($ln_pg->images) > 0)
        <div class="glass-card text-center" data-aos="fade-up" data-aos-delay="150">
            @if($ln_pg->feature) <div class="section-heading">{{ $ln_pg->feature }}</div> @endif
            <div class="owl-carousel img-carousel mt-3">
                @foreach($ln_pg->images as $slider)
                    <img src="{{ asset('landing_sliders/'.$slider->image) }}" class="slider-img" alt="Product Image">
                @endforeach
            </div>
        </div>
        @endif

        {{-- 4. Review Section --}}
        @if($ln_pg->review_images && count($ln_pg->review_images) > 0)
        <div class="glass-card text-center" data-aos="fade-up" data-aos-delay="200">
            <div class="section-heading"><i class="fas fa-star text-warning"></i> {{ $ln_pg->review_top_text ?? 'কাস্টমার রিভিউ' }}</div>
            <div class="owl-carousel img-carousel mt-3">
                @foreach($ln_pg->review_images as $rv)
                    <img src="{{ asset('review_landing_sliders/'.$rv->review_image) }}" class="slider-img" alt="Review">
                @endforeach
            </div>
        </div>
        @endif

        {{-- 5. Checkout Form --}}
        <div id="order_section" class="glass-card" style="border: 2px solid var(--primary); margin-bottom: 0;" data-aos="fade-up" data-aos-delay="250">
            <div class="text-center mb-4">
                <h3 class="fw-bold" style="color: var(--primary);">অর্ডার কনফার্ম করুন</h3>
                <p class="text-muted small mb-0">ফর্মটি পূরণ করে অর্ডার সম্পন্ন করুন</p>
            </div>

            <form action="{{ route('front.storelandData') }}" method="POST" id="checkout_form">
                @csrf
                        {{-- কোন landing page থেকে order এলো (Order Management-এর Source-এ দেখায়) --}}
                        <input type="hidden" name="landing_page_type" value="3">
                <input type="hidden" name="prd_id" value="{{ $productId }}">
                <input type="hidden" name="amount" id="amount" value="">
                <input type="hidden" name="purchase_event_id" id="purchase_event_id" value="">

                <div class="row g-4">
                    
                    {{-- Left Side: Billing Details --}}
                    <div class="col-lg-6 col-md-12">
                        <h5 class="fw-bold border-bottom pb-2 mb-3">শিপিং ঠিকানা</h5>
                        
                        <div class="mb-3">
                            <label class="form-label">আপনার নাম <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" placeholder="সম্পূর্ণ নাম" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">মোবাইল নাম্বার <span class="text-danger">*</span></label>
                            <input type="tel" name="mobile" id="customer_mobile" class="form-control" placeholder="01XXXXXXXXX" minlength="11" maxlength="11" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ডেলিভারি এলাকা <span class="text-danger">*</span></label>
                            <select name="delivery_charge_id" id="delivery_charge" class="form-select" required>
                                <option value="" disabled selected>এলাকা নির্বাচন করুন...</option>
                                @foreach($charges ?? [] as $charge)
                                    <option value="{{ $charge->id }}" data-charge="{{ $isFreeShipping ? 0 : intval($charge->amount) }}">
                                        {{ $charge->title }} - {!! $isFreeShipping ? '<span class="text-success fw-bold">ফ্রি ডেলিভারি (0 ৳)</span>' : intval($charge->amount) . ' Tk' !!}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">সম্পূর্ণ ঠিকানা <span class="text-danger">*</span></label>
                            <textarea name="shipping_address" class="form-control" rows="2" placeholder="বাসা নং, রাস্তা, থানা, জেলা" required></textarea>
                        </div>

                        {{-- Payment Methods (Advanced Gateway Integrations) --}}
                        <div class="mt-4">
                            <label class="form-label mb-2">পেমেন্ট মেথড <span class="text-danger">*</span></label>
                            
                            @if(isset($information->cod_active) && $information->cod_active == 1)
                            <label class="d-block w-100">
                                <input type="radio" name="payment_method" value="cod" class="d-none" onchange="togglePaymentAction('cod')" checked>
                                <div class="payment-box">
                                    <i class="fas fa-money-bill-wave fs-4 text-success"></i>
                                    <span class="fw-bold">ক্যাশ অন ডেলিভারি</span>
                                </div>
                            </label>
                            @endif

                            @if(isset($information->ssl_active) && $information->ssl_active == 1)
                            <label class="d-block w-100">
                                <input type="radio" name="payment_method" value="sslcommerz" class="d-none" onchange="togglePaymentAction('online')">
                                <div class="payment-box">
                                    <i class="fas fa-credit-card fs-4 text-primary"></i>
                                    <span class="fw-bold">অনলাইন পেমেন্ট (bKash/Card)</span>
                                </div>
                            </label>
                            @endif

                            @if(isset($information->bkash_active) && $information->bkash_active == 1)
                            <label class="d-block w-100">
                                <input type="radio" name="payment_method" value="bkash" class="d-none" onchange="togglePaymentAction('bkash')">
                                <div class="payment-box">
                                    <img src="{{ asset('frontend/images/bkash_logo.png') }}" alt="bKash" style="height: 24px; width: 24px; object-fit: contain;">
                                    <span class="fw-bold">বিকা বিকাশ পেমেন্ট (bKash)</span>
                                </div>
                            </label>
                            @endif

                            @if(isset($information->nagad_active) && $information->nagad_active == 1)
                            <label class="d-block w-100">
                                <input type="radio" name="payment_method" value="nagad" class="d-none" onchange="togglePaymentAction('nagad')">
                                <div class="payment-box">
                                    <img src="{{ asset('frontend/images/nagad.png') }}" alt="Nagad" style="height: 24px; width: 24px; object-fit: contain;">
                                    <span class="fw-bold">নগদ পেমেন্ট (Nagad)</span>
                                </div>
                            </label>
                            @endif

                            @if(isset($information->eps_active) && $information->eps_active == 1)
                            <label class="d-block w-100">
                                <input type="radio" name="payment_method" value="eps" class="d-none" onchange="togglePaymentAction('eps')">
                                <div class="payment-box">
                                    <i class="fas fa-wallet fs-4 text-info"></i>
                                    <span class="fw-bold">EPS পেমেন্ট</span>
                                </div>
                            </label>
                            @endif

                            @if(isset($information->uddoktapay_active) && $information->uddoktapay_active == 1)
                            <label class="d-block w-100">
                                <input type="radio" name="payment_method" value="uddoktapay" class="d-none" onchange="togglePaymentAction('uddoktapay')">
                                <div class="payment-box">
                                    <i class="fas fa-money-check-alt fs-4 text-success"></i>
                                    <span class="fw-bold">উদ্দোক্তাপে (UddoktaPay)</span>
                                </div>
                            </label>
                            @endif

                            @php
                                $activeManuals = \DB::table('manual_payments')->where('status', 1)->get();
                            @endphp
                            @foreach($activeManuals as $mp)
                            <label class="d-block w-100">
                                <input type="radio" name="payment_method" value="{{ $mp->name }}" class="d-none" onchange="togglePaymentAction('manual', '{{ $mp->name }}', '{{ $mp->number }}', '{{ $mp->type }}')">
                                <div class="payment-box">
                                    <i class="fas fa-mobile-alt fs-4 text-secondary"></i>
                                    <span class="fw-bold">{{ $mp->name }} ({{ $mp->type }})</span>
                                </div>
                            </label>
                            @endforeach

                            {{-- Manual Payment Fields --}}
                            <div id="manual_payment_area" style="display: none;" class="mt-3 p-3 bg-light border rounded-3">
                                <div class="d-flex align-items-center mb-3 p-2 rounded-2" style="background: rgba(226, 19, 110, 0.08); border: 1px solid rgba(226, 19, 110, 0.2); color: #C90D5E;">
                                    <i class="fas fa-info-circle fa-lg me-2"></i>
                                    <p id="payment_instruction" class="mb-0 fw-bold" style="font-size: 14px;"></p>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label text-dark fw-bold" style="font-size: 12px;">যে নাম্বার থেকে টাকা পাঠিয়েছেন <span class="text-danger">*</span></label>
                                        <input type="text" name="sender_number" id="sender_number" class="form-control form-control-sm border-secondary" placeholder="017XXXXXXXX">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-dark fw-bold" style="font-size: 12px;">Transaction ID (TrxID) <span class="text-danger">*</span></label>
                                        <input type="text" name="transaction_id" id="transaction_id" class="form-control form-control-sm border-secondary" placeholder="TRX123456789">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- Right Side: Order Summary --}}
                    <div class="col-lg-6 col-md-12">
                        <h5 class="fw-bold border-bottom pb-2 mb-3">অর্ডার সামারি</h5>
                        
                        <div class="order-summary">
                            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                <img src="{{ function_exists('getImage') ? getImage('products', $currentProduct->image ?? '') : '' }}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;" class="border me-3 shadow-sm">
                                <div class="flex-grow-1" style="min-width: 0;">
                                    <h6 class="mb-1 fw-bold text-truncate" style="font-size: 15px;">{{ $productName }}</h6>
                                    <span class="text-danger fw-bold fs-5"><span id="unit_price_display">{{ $defaultPrice }}</span> Tk</span>
                                    <input type="hidden" id="unit_price" value="{{ $defaultPrice }}">
                                </div>
                            </div>

                            @if($variations->count() > 0)
                                <input type="hidden" name="variation_id" id="variation_id" value="">
                                
                                @if($sizes->count() > 0)
                                <div class="mb-3">
                                    <label class="fw-bold small mb-2 text-muted">সাইজ সিলেক্ট করুন <span class="text-danger">*</span></label>
                                    <div class="d-flex flex-wrap">
                                        @foreach($sizes as $size)
                                            <div class="var-btn var-size-btn" data-id="{{ $size->id }}">{{ $size->name ?? $size->title }}</div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                @if($colors->count() > 0)
                                <div class="mb-3">
                                    <label class="fw-bold small mb-2 text-muted">কালার সিলেক্ট করুন <span class="text-danger">*</span></label>
                                    <div class="d-flex flex-wrap">
                                        @foreach($colors as $color)
                                            <div class="var-btn var-color-btn" data-id="{{ $color->id }}">{{ $color->name ?? $color->title }}</div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            @else
                                <input type="hidden" name="variation_id" id="variation_id" value="{{ $defaultVarId ?? '' }}">
                            @endif
                            <input type="hidden" id="max_stock" value="{{ $defaultStock }}">

                            {{-- ✅ COUPON SECTION START ✅ --}}
                            @if(isset($information->coupon_visibility) && $information->coupon_visibility == 1)
                            <div class="mt-3 mb-3 p-3 bg-white" style="border: 1px dashed var(--primary); border-radius: 8px;">
                                <label class="form-label mb-2" style="font-size: 14px; font-weight: 700; color: var(--primary);"><i class="fas fa-ticket-alt me-1"></i> কুপন কোড (যদি থাকে)</label>
                                <div class="input-group">
                                    <input type="text" id="coupon_code" class="form-control" placeholder="কোড লিখুন" style="box-shadow: none;">
                                    <button type="button" class="btn text-white px-3" id="coupon_btn_submit" onclick="applyCouponLand()" style="background: var(--primary); font-weight: 700;">APPLY</button>
                                </div>
                                <small id="coupon_msg" class="d-block mt-2 fw-bold"></small>
                            </div>
                            @endif

                            @php $sessionDiscount = session('coupon_discount') ?? 0; @endphp
                            <input type="hidden" id="discount_amount" value="{{ $sessionDiscount }}">
                            {{-- ✅ COUPON SECTION END ✅ --}}

                            <div class="summary-row mt-3">
                                <span class="fw-bold">Quantity</span>
                                <div class="qty-box">
                                    <button type="button" class="qty-btn" id="qty_minus">-</button>
                                    <input type="number" name="quantity" id="qty_input" class="qty-input" value="1" readonly>
                                    <button type="button" class="qty-btn" id="qty_plus">+</button>
                                </div>
                            </div>
                            
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span class="fw-bold"><span id="calc_subtotal">{{ $defaultPrice }}</span> Tk</span>
                            </div>
                            
                            <div class="summary-row">
                                <span>Shipping</span>
                                <span id="calc_shipping_text" class="fw-bold">+ <span id="calc_shipping">0</span> Tk</span>
                            </div>

                            {{-- ✅ DISCOUNT CALCULATION ROW ✅ --}}
                            <div class="summary-row" id="discount_row" style="{{ $sessionDiscount > 0 ? '' : 'display:none;' }}">
                                <span class="text-success fw-bold">Discount</span>
                                <span class="text-success fw-bold">- <span id="calc_discount">{{ $sessionDiscount }}</span> Tk</span>
                            </div>

                            <div class="summary-row total">
                                <span>Total Payable</span>
                                <span><span id="calc_total">{{ $defaultPrice }}</span> Tk</span>
                                <input type="hidden" id="final_amount" name="final_amount" value="{{ $defaultPrice }}">
                            </div>
                        </div>
                        
                        {{-- Submit Button --}}
                        <div class="mt-4">
                            <button type="submit" id="submit_btn" class="btn-brand">
                                {{ $ln_pg->btn_text_form ?? 'অর্ডার কনফার্ম করুন' }} <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>

                        <div class="text-center mt-3 text-muted">
                            <p class="mb-1 fw-bold text-success" style="font-size: 13px;"><i class="fas fa-lock"></i> 100% Secure Checkout</p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> 

{{-- ✅✅✅ OTP MODAL ✅✅✅ --}}
<div class="modal fade" id="otpModal" data-bs-backdrop="static" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content otp-modal-content p-4">
      <div class="modal-header border-0 pb-0 justify-content-end">
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center pt-0 pb-4">
        <div class="otp-icon-box"><i class="fas fa-shield-alt fa-2x"></i></div>
        <h4 class="fw-bold mb-2 otp-title">মোবাইল ভেরিফিকেশন</h4>
        <p class="otp-subtitle">আপনার <span class="fw-bold text-dark" id="otp_sent_number"></span> নাম্বারে কোড পাঠানো হয়েছে।</p>
        <div class="form-group mb-4">
            <input type="text" id="otp_input" maxlength="4" class="form-control otp-input" placeholder="____" autocomplete="one-time-code" inputmode="numeric">
            <small class="text-danger mt-2 d-block fw-bold" id="otp_error"></small>
        </div>
        <button type="button" class="btn-verify" onclick="verifyOtpNow()">যাচাই করুন (Verify)</button>
        <div class="text-center mt-3">
             <button type="button" class="btn btn-link text-decoration-none text-muted p-0 small" id="resendOtpBtn" onclick="sendOtpBeforeSubmit(true)">
                 কোড পাননি? <span class="text-primary fw-bold">আবার পাঠান</span>
             </button>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="footer-wrapper">
    @include('frontend.partials.footer')
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

@php
    $waNumber = $ln_pg->whatsapp ?? $ln_pg->phone_number ?? $ln_pg->phone ?? $ln_pg->call_text ?? $ln_pg->mobile ?? '';
    $waNumberClean = preg_replace('/\D+/', '', $waNumber);
@endphp
@if(!empty($waNumberClean))
    <a href="https://wa.me/{{ $waNumberClean }}" target="_blank" class="whats_btn" aria-label="WhatsApp">
        <img src="https://img.icons8.com/windows/96/ffffff/whatsapp--v1.png" alt="whatsapp">
    </a>
@endif

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
// JavaScript Setup Variables
var current_discount_val = 0;
var current_discount_type = "fixed";
var isWeightBased = {{ $isWeightBased ? 'true' : 'false' }};
var isFreeShipping = {{ $isFreeShipping }};
var paymentID;
var dynamicOrderId;
var successUrl = '';

function toNumber(v){ v = (v ?? '').toString().replace(/[^\d.]/g,''); var n = parseFloat(v); return isNaN(n) ? 0 : n; }

// Manual Payment Toggle Logic
window.togglePaymentAction = function(method, name = '', number = '', type = '') {
    var manualArea = $('#manual_payment_area');
    var sNum = $('#sender_number');
    var tId = $('#transaction_id');

    if(method === 'manual') {
        $('#payment_instruction').html(`দয়া করে আপনার টোটাল বিল <b>${number} (${type})</b> নাম্বারে Send Money করুন। এরপর নিচের তথ্যগুলো দিন।`);
        manualArea.slideDown();
        sNum.attr('required', 'required');
        tId.attr('required', 'required');
    } else {
        manualArea.slideUp();
        sNum.removeAttr('required');
        tId.removeAttr('required');
    }
};

// ✅✅✅ APPLY COUPON FUNCTION ✅✅✅
window.applyCouponLand = function() {
    var code = $('#coupon_code').val();
    var $btn = $('#coupon_btn_submit');
    
    if(!code) { toastr.error('কুপন কোড লিখুন'); return; }

    var unitPrice = toNumber($('#unit_price').val());
    var qty = parseInt($('#qty_input').val()) || 1;
    var current_total = unitPrice * qty;

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
                calculate(); 
            } else {
                $('#coupon_msg').text(res.msg).css('color', 'red');
                toastr.error(res.msg);
                $btn.prop('disabled', false).text('APPLY');
                current_discount_val = 0;
                calculate(); 
            }
        },
        error: function() {
            toastr.error('Error applying coupon');
            $btn.prop('disabled', false).text('APPLY');
        }
    });
};

// ✅✅✅ OTP GLOBAL VARIABLES & FUNCTIONS ✅✅✅
var isOtpVerified = false;
var otpSystemEnabled = {{ $information->otp_system ?? 0 }};
var otpTimerInterval;
var isSendingOtp = false;

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

window.sendOtpBeforeSubmit = function(isResend = false) {
    if(isSendingOtp) return;
    var mobile = $('#customer_mobile').val();
    if(!mobile || mobile.length !== 11) { toastr.error('সঠিক মোবাইল নাম্বার দিন'); return; }
    
    isSendingOtp = true;
    if(!isResend) {
        $('#submit_btn').addClass('processing').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Sending OTP...');
    }
    
    $.ajax({
        url: "{{ route('sendOtp') }}", type: "POST", data: { mobile: mobile, _token: "{{ csrf_token() }}" },
        success: function(res) {
            isSendingOtp = false;
            if(!isResend) {
                $('#submit_btn').removeClass('processing').prop('disabled', false).html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-2"></i>');
            }
            
            if(res.success) {
                $('#otp_sent_number').text(mobile);
                $('#otpModal').appendTo('body'); 
                var myModal = new bootstrap.Modal(document.getElementById('otpModal'));
                myModal.show();
                
                setTimeout(function() { $('#otp_input').focus(); }, 500);
                startOtpTimer(30, $('#resendOtpBtn'));
            } else { toastr.error(res.msg); }
        },
        error: function() { 
            isSendingOtp = false; 
            if(!isResend) $('#submit_btn').removeClass('processing').prop('disabled', false).html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-2"></i>'); 
        }
    });
};

window.verifyOtpNow = function() {
    var code = $('#otp_input').val();
    var mobile = $('#customer_mobile').val();
    $.ajax({
        url: "{{ route('verifyOtp') }}", type: "POST", data: { otp: code, mobile: mobile, _token: "{{ csrf_token() }}" },
        success: function(res) {
            if(res.success) {
                isOtpVerified = true;
                bootstrap.Modal.getInstance(document.getElementById('otpModal')).hide();
                submitOrderFinal(); // Proceed to final submit
            } else { $('#otp_error').text(res.msg); }
        }
    });
};

// ✅ FINAL SUBMIT FUNCTION WITH ADVANCED GATEWAYS
function submitOrderFinal() {
    let $form = $('#checkout_form');
    let payMethod = $('input[name="payment_method"]:checked').val() || 'cod'; 
    
    // Purchase Event ID
    var purchaseEventId = "PUR_{{ $productId }}_" + Date.now();
    $('#purchase_event_id').val(purchaseEventId);
    
    if(payMethod === 'sslcommerz'){ 
        $form.attr('action', "{{ url('/pay') }}")[0].submit(); 
        return; 
    }
    
    $('#submit_btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> প্রসেসিং হচ্ছে...');
    
    $.ajax({
        url: "{{ route('front.storelandData') }}", 
        method: "POST", 
        data: $form.serialize(),
        success: function(res){
            if(res.success){
                if(payMethod === 'bkash') {
                    dynamicOrderId = res.order_id || res.url.split('/').pop();
                    successUrl = res.url;
                    @if(isset($information->bkash_active) && $information->bkash_active == 1)
                        initBkash();
                        setTimeout(() => { $('#bKash_button').click(); }, 300);
                    @endif
                } else if(payMethod === 'nagad') {
                    let finalOrderId = res.order_id || res.url.split('/').pop();
                    window.location.href = "{{ url('nagad/pay') }}/" + finalOrderId;
                } else if(payMethod === 'uddoktapay') {
                    let finalOrderId = res.order_id || res.url.split('/').pop();
                    window.location.href = "{{ url('uddoktapay/pay') }}/" + finalOrderId;
                } else if(payMethod === 'eps') {
                    window.location.href = res.url;
                } else {
                    toastr.success(res.msg); 
                    setTimeout(function(){ 
                        window.location.href = res.url; 
                    }, 800);
                }
            } else { 
                toastr.error(res.msg); 
                $('#submit_btn').prop('disabled', false).html('অর্ডার কনফার্ম করুন <i class="fas fa-check-circle ms-2"></i>'); 
            }
        },
        error: function (response) { 
            $('#submit_btn').prop('disabled', false).html('অর্ডার কনফার্ম করুন <i class="fas fa-check-circle ms-2"></i>'); 
            toastr.error('ফর্ম সাবমিট করতে সমস্যা হচ্ছে।'); 
        }
    });
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
                        $('#submit_btn').prop('disabled', false).html('অর্ডার কনফার্ম করুন <i class="fas fa-check-circle ms-2"></i>');
                    }
                },
                error: function (err) {
                    bKash.create().onError();
                    toastr.error("Server error while connecting to bKash.");
                    $('#submit_btn').prop('disabled', false).html('অর্ডার কনফার্ম করুন <i class="fas fa-check-circle ms-2"></i>');
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
                        $('#submit_btn').prop('disabled', false).html('অর্ডার কনফার্ম করুন <i class="fas fa-check-circle ms-2"></i>');
                    }
                },
                error: function () {
                    bKash.execute().onError();
                    toastr.error("Failed to execute bKash payment.");
                    $('#submit_btn').prop('disabled', false).html('অর্ডার কনফার্ম করুন <i class="fas fa-check-circle ms-2"></i>');
                }
            });
        },
        onClose: function () {
            window.location.href = successUrl;
        }
    });
}
@endif

$(document).ready(function() {
    AOS.init({ duration: 800, once: true, offset: 50 });

    toastr.options = { "closeButton": true, "progressBar": true, "positionClass": "toast-top-right", "showDuration": "200",  "hideDuration": "300", "timeOut": "2000", "extendedTimeOut": "500" };

    $(".img-carousel").owlCarousel({ loop: true, autoplay: true, margin: 10, nav: false, dots: true, responsive: { 0:{items:1}, 600:{items:2}, 1000:{items:3} } });

    let varMatrix = @json($varMatrix);
    let selectedSize = 0; let selectedColor = 0;

    if($('.var-size-btn').length > 0) { selectedSize = $('.var-size-btn').first().data('id'); $('.var-size-btn').first().addClass('active'); }
    if($('.var-color-btn').length > 0) { selectedColor = $('.var-color-btn').first().data('id'); $('.var-color-btn').first().addClass('active'); }

    // ✅✅✅ CALCULATE FUNCTION FOR DISCOUNT & FREE SHIPPING ✅✅✅
    function calculate() {
        let price = Math.round(parseFloat($('#unit_price').val()) || 0); 
        let qty = parseInt($('#qty_input').val()) || 1;
        let subtotal = price * qty; 
        
        let discount = 0;
        if(current_discount_val > 0) {
            if (current_discount_type === 'percentage' || current_discount_type === 'percent') {
                discount = (subtotal * current_discount_val) / 100;
            } else {
                discount = current_discount_val;
            }
        }
        
        if(discount > 0) {
            $('#discount_row').attr('style', 'display: flex !important; justify-content: space-between;');
            $('#calc_discount').text(Math.round(discount));
        } else {
            $('#discount_row').hide();
        }

        $('#calc_subtotal').text(subtotal); 
        $('input[name="amount"]').val(subtotal); 

        let $opt = $('#delivery_charge').find("option:selected");
        let cid = $opt.val();

        // Check if Free Shipping
        if(isFreeShipping == 1) {
            $('#calc_shipping_text').html('<span class="text-success fw-bold">ফ্রি ডেলিভারি</span>');
            let total = subtotal - discount;
            if(total < 0) total = 0;
            $('#calc_total').text(total);
            $('#final_amount').val(total);
        } else if(isWeightBased && cid && cid !== '') {
            $.ajax({
                url: "{{ route('front.getDeliveryChargeAjax') }}",
                type: "POST",
                data: {
                    delivery_charge_id: cid,
                    product_id: "{{ $productId }}",
                    quantity: qty,
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    if(res.success) {
                        let charge = Math.round(parseFloat(res.charge));
                        $('#calc_shipping_text').html('+ <span id="calc_shipping">' + charge + '</span> Tk');
                        
                        let total = (subtotal + charge) - discount;
                        if(total < 0) total = 0;
                        
                        $('#calc_total').text(total);
                        $('#final_amount').val(total); 
                    }
                }
            });
        } else {
            let charge = Math.round(parseFloat($opt.data('charge')) || 0);
            $('#calc_shipping_text').html('+ <span id="calc_shipping">' + charge + '</span> Tk'); 
            
            let total = (subtotal + charge) - discount;
            if(total < 0) total = 0;
            
            $('#calc_total').text(total);
            $('#final_amount').val(total); 
        }
    }

    function checkVariation() {
        if(Object.keys(varMatrix).length === 0) { calculate(); return; }
        let key = selectedSize + '_' + selectedColor;
        let matchedVar = varMatrix[key];

        if(matchedVar) {
            $('#variation_id').val(matchedVar.id);
            $('#unit_price').val(matchedVar.price);
            $('#unit_price_display').text(matchedVar.price);
            $('#max_stock').val(matchedVar.stock);

            if(matchedVar.stock <= 0) {
                toastr.error('Out of stock!');
                $('#submit_btn').prop('disabled', true).text('Out of Stock');
                $('#qty_input').val(0);
            } else {
                $('#submit_btn').prop('disabled', false).html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-2"></i>');
                if($('#qty_input').val() == 0) $('#qty_input').val(1);
            }
        } else {
            $('#variation_id').val('');
            $('#submit_btn').prop('disabled', true).text('Out of Stock / Unavailable');
            toastr.error('এই সাইজ এবং কালারের প্রোডাক্টটি স্টকে নেই!');
            $('#qty_input').val(0);
        }
        calculate();
    }

    // 🟢 Size Click Event (Auto Filter Colors) 🟢
    $('.var-size-btn').click(function() {
        $('.var-size-btn').removeClass('active');
        $(this).addClass('active');
        selectedSize = $(this).data('id');

        if ($('.var-color-btn').length > 0) {
            let hasValidColor = false;
            let firstValidColor = null;

            $('.var-color-btn').each(function() {
                let cId = $(this).data('id');
                let checkKey = selectedSize + '_' + cId;

                if (varMatrix[checkKey]) {
                    $(this).show();
                    if (firstValidColor === null) firstValidColor = $(this);
                    if ($(this).hasClass('active')) hasValidColor = true;
                } else {
                    $(this).hide().removeClass('active');
                }
            });

            if (!hasValidColor && firstValidColor) {
                firstValidColor.addClass('active');
                selectedColor = firstValidColor.data('id');
            }
        }
        checkVariation();
    });

    // 🟢 Color Click Event (Auto Filter Sizes) 🟢
    $('.var-color-btn').click(function() {
        $('.var-color-btn').removeClass('active');
        $(this).addClass('active');
        selectedColor = $(this).data('id');

        if ($('.var-size-btn').length > 0) {
            let hasValidSize = false;
            let firstValidSize = null;

            $('.var-size-btn').each(function() {
                let sId = $(this).data('id');
                let checkKey = sId + '_' + selectedColor;

                if (varMatrix[checkKey]) {
                    $(this).show();
                    if (firstValidSize === null) firstValidSize = $(this);
                    if ($(this).hasClass('active')) hasValidSize = true;
                } else {
                    $(this).hide().removeClass('active');
                }
            });

            if (!hasValidSize && firstValidSize) {
                firstValidSize.addClass('active');
                selectedSize = firstValidSize.data('id');
            }
        }
        checkVariation();
    });

    // 🟢 First Time Auto Trigger Logic 🟢
    if($('.var-size-btn.active').length > 0) {
        $('.var-size-btn.active').trigger('click');
    } else if($('.var-color-btn.active').length > 0) {
        $('.var-color-btn.active').trigger('click');
    } else {
        checkVariation();
    }

    $('#delivery_charge').on('change', calculate);
    
    $('#qty_plus').click(function(){ let q = parseInt($('#qty_input').val()) || 1; let max = parseInt($('#max_stock').val()) || 1; if(q < max) { $('#qty_input').val(q + 1); calculate(); } else { toastr.warning('Maximum stock limit reached'); } });
    $('#qty_minus').click(function(){ let q = parseInt($('#qty_input').val()) || 1; if(q > 1) { $('#qty_input').val(q - 1); calculate(); } });

    // Incomplete order auto-save functionality
    $('#customer_mobile').on('blur', function() {
        let mobile = $(this).val();
        if(mobile.length === 11) {
            $.post("{{ route('incompleteStore') }}", {
                mobile: mobile,
                name: $('input[name="first_name"]').val(),
                address: $('input[name="shipping_address"]').val(),
                prd_id: $('input[name="prd_id"]').val(),
                variation_id: $('#variation_id').val(),
                quantity: $('#qty_input').val(),
                amount: $('#unit_price').val(),
                _token: "{{ csrf_token() }}"
            });
        }
    });

    // Form Submit Routing
    $('#checkout_form').submit(function(e) {
        e.preventDefault();
        
        if(!$('#delivery_charge').val()){ toastr.warning('ডেলিভারি এলাকা নির্বাচন করুন'); return false; }
        if(!$('#variation_id').val() && Object.keys(varMatrix).length > 0) { toastr.error('সঠিক সাইজ এবং কালার সিলেক্ট করুন!'); return false; }
        if(parseInt($('#max_stock').val()) <= 0) { toastr.error('Product is out of stock!'); return false; }

        let paymentMethod = $('input[name="payment_method"]:checked').val() || 'cod';

        // Check if manual payment and empty fields
        if(paymentMethod !== 'online' && paymentMethod !== 'bkash' && paymentMethod !== 'eps' && paymentMethod !== 'nagad' && paymentMethod !== 'uddoktapay' && paymentMethod !== 'Cash on Delivery' && paymentMethod !== 'cod' && paymentMethod !== 'sslcommerz') {
            if(!$('#sender_number').val() || !$('#transaction_id').val()) {
                toastr.warning('দয়া করে পেমেন্ট নাম্বার এবং Transaction ID দিন');
                return false;
            }
        }

        // Online Payment bypassing OTP
        if (paymentMethod === 'sslcommerz' && !otpSystemEnabled) {
            let purchaseId = "PUR_{{ $productId }}_" + Date.now();
            $('#purchase_event_id').val(purchaseId);
            $(this).attr('action', "{{ url('/pay') }}").attr('method', 'POST')[0].submit();
            return;
        }

        // OTP Trigger
        if(otpSystemEnabled == 1 && !isOtpVerified) {
            sendOtpBeforeSubmit();
        } else {
            submitOrderFinal();
        }
    });

    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        $('html, body').stop().animate({ scrollTop: $($(this).attr('href')).offset().top - 20 }, 500);
    });
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