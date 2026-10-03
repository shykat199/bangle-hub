<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $ln_pg->title1 ?? 'Murdha Moaharee - Order Now' }}</title>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    @php
        $information = \App\Models\Information::first();
        $activeManuals = \App\Models\ManualPayment::where('status', 1)->get();
        
        $headerBg    = '#006400';
        $btnBg       = '#8b0000';
        $contactBg   = '#1877f2';
        $bodyBg      = '#f5f6f8';
        
        $productId = $product->id ?? 0;
        $productName = $product->name ?? '';
        $pixelId = setting('fb_pixel_id') ?? null;
        $phoneNumber = $ln_pg->phone ?? '01XXX-XXXXXX';
        
        $waNumberClean = preg_replace('/\D+/', '', $phoneNumber);

        $contentCategory = $product?->category?->name ?? 'Landing Page';

        $globalSetting = DB::table('delivery_charges')->first();
        $isWeightBased = $globalSetting && $globalSetting->charge_type == 'weight_based';

        $variations = collect();
        if($product){
            try{
                $product->loadMissing(['variations.size','variations.color', 'variations.stocks']);
                $variations = $product->variations ?? collect();
            }catch(\Throwable $e){
                $variations = $product->variations ?? collect();
            }
        }
        
        $defaultPrice = ($product->after_discount > 0) ? intval($product->after_discount) : intval($product->sell_price ?? $product->price ?? 0);
        if(!empty($ln_pg->new_price)) $defaultPrice = $ln_pg->new_price;

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
        body { font-family: 'Hind Siliguri', sans-serif; background-color: {{ $bodyBg }}; color: #333; margin: 0; padding: 0; }
        .main-container { max-width: 850px; margin: 0 auto; padding: 30px 15px; background: transparent; }
        
        .headline { color: #000; font-weight: 800; font-size: 22px; text-align: center; margin-bottom: 20px; line-height: 1.5; }
        
        .main-img { width: 100%; max-width: 500px; margin: 0 auto; display: block; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        
        .btn-order { background-color: {{ $btnBg }}; color: #fff !important; font-weight: 700; font-size: 18px; padding: 12px 30px; border-radius: 4px; border: none; display: flex; align-items: center; justify-content: center; gap: 8px; width: max-content; margin: 20px auto; transition: 0.3s; text-decoration: none; box-shadow: 0 4px 6px rgba(139, 0, 0, 0.3); }
        .btn-order:hover { transform: translateY(-2px); background-color: #660000; color: #fff; }

        .section-header { background-color: {{ $headerBg }}; color: #fff; text-align: center; font-weight: 700; font-size: 18px; padding: 12px; border-radius: 4px; margin: 30px 0 20px 0; }
        
        .video-container { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); margin-bottom: 20px;}
        .video-container iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }

        .gallery-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .gallery-grid img { width: 100%; height: 250px; object-fit: cover; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }

        .contact-banner { background-color: {{ $contactBg }}; color: #fff; text-align: center; padding: 25px 15px; border-radius: 6px; margin: 30px 0; box-shadow: 0 4px 15px rgba(24, 119, 242, 0.3); }
        .contact-banner h4 { font-weight: 800; font-size: 20px; margin-bottom: 10px; }
        .contact-banner p { font-size: 15px; margin-bottom: 5px; }
        .contact-banner .phone { font-size: 26px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 10px; }

        .feature-list { list-style: none; padding: 0; margin: 0; background: transparent; }
        .feature-list li { margin-bottom: 10px; font-size: 15px; display: flex; align-items: flex-start; color: #111; font-weight: 600;}
        .feature-list li i { color: {{ $headerBg }}; margin-right: 10px; margin-top: 4px; font-size: 15px; }

        .review-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; }
        .review-grid img { width: 100%; aspect-ratio: 1/1; object-fit: cover; border-radius: 8px; border: 2px solid #ddd; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }

        .package-item { background: #fff; border: 1px solid #e5e7eb; padding: 15px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; margin-bottom: 10px; transition: 0.3s; border-radius: 6px;}
        .package-item.active { background: #fdfdfd; border-color: {{ $headerBg }}; }
        
        .pkg-left { display: flex; align-items: center; gap: 15px; flex-grow: 1; }
        .pkg-checkbox { width: 18px; height: 18px; cursor: pointer; accent-color: {{ $headerBg }};}
        .pkg-img { width: 55px; height: 55px; border-radius: 4px; object-fit: cover; border: 1px solid #eee; }
        .pkg-name { font-weight: 600; font-size: 15px; color: #333; margin: 0; }
        
        .pkg-right { display: flex; align-items: center; gap: 15px; }
        .qty-box { display: flex; align-items: center; border: 1px solid #ddd; background: #fff; border-radius: 4px; overflow: hidden;}
        .qty-btn { background: #f8f9fa; border: none; padding: 5px 12px; font-weight: bold; font-size: 18px; cursor: pointer; color: #333; }
        .qty-btn:hover { background: #e9ecef; }
        .qty-input { width: 40px; border: none; text-align: center; font-weight: 700; font-size: 15px; background: transparent; pointer-events: none; }
        .pkg-price { font-weight: 700; font-size: 16px; color: {{ $headerBg }}; min-width: 80px; text-align: right; }

        .checkout-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start; margin-top: 20px;}
        
        .form-control, .form-select { border-radius: 4px; padding: 10px 15px; border: 1px solid #ddd; font-size: 14px; background: #fff; }
        .form-control:focus, .form-select:focus { border-color: {{ $headerBg }}; box-shadow: none; outline: none; }
        .form-label { font-weight: 600; color: #333; font-size: 14px; margin-bottom: 5px; display: block;}
        
        .payment-radio { display: none; } 
        .payment-card { border: 1px solid #ddd; border-radius: 6px; padding: 10px 15px; display: flex; align-items: center; cursor: pointer; margin-bottom: 10px; background: #fff; transition: 0.2s; }
        .payment-card i { font-size: 20px; margin-right: 12px; color: #94a3b8; }
        .payment-card b { font-size: 14px; color: #333; display: block; font-weight: 700;}
        .payment-radio:checked + .payment-card { border-color: {{ $headerBg }}; background: #f0fdf4; }
        .payment-radio:checked + .payment-card i { color: {{ $headerBg }}; }

        .summary-box { background: #fdfbf7; border: 1px solid #eee; padding: 20px; border-radius: 8px;}
        .summary-title { font-weight: 700; font-size: 16px; border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 15px; text-align: center; color: #333;}
        .calc-row { display: flex; justify-content: space-between; font-weight: 600; font-size: 14px; color: #555; padding: 6px 0; }
        .calc-row.total { border-top: 1px dashed #ccc; padding-top: 10px; margin-top: 5px; font-size: 16px; font-weight: 700; color: {{ $headerBg }}; }
        
        .btn-submit { background-color: {{ $headerBg }}; color: #fff; font-weight: 700; font-size: 16px; padding: 12px; width: 100%; border: none; margin-top: 15px; border-radius: 4px; transition: 0.3s; }
        .btn-submit:hover { background-color: #004d00; color: #fff; }

        .coupon-section { background-color: #fff; border: 1px dashed #ccc; border-radius: 6px; padding: 15px; margin-bottom: 15px; }
        .coupon-input-group { display: flex; border-radius: 4px; overflow: hidden; border: 1px solid #ddd; }
        .coupon-input-group input { border: none; padding: 10px 15px; flex-grow: 1; font-size: 13px; outline: none; }
        .coupon-input-group button { border: none; background: #333; color: #fff; padding: 0 20px; font-weight: 700; font-size: 13px; cursor: pointer; transition: 0.3s; }
        .coupon-input-group button:hover { background: #000; }

        .faq-section { margin-top: 40px; margin-bottom: 40px; }
        .faq-item { border: 1px solid #ddd; border-radius: 8px; margin-bottom: 10px; background: #fff; overflow: hidden; }
        .accordion-button { font-weight: 700; color: #333; background-color: #fff; box-shadow: none !important; font-size: 15px;}
        .accordion-button:not(.collapsed) { color: {{ $headerBg }}; background-color: #f0fdf4; }
        .accordion-body { color: #555; font-size: 15px; line-height: 1.6; }

        .whats_btn { position: fixed; right: 20px; bottom: 50px; z-index: 9999; width: 60px; height: 60px; background: #25D366; display: flex; align-items: center; justify-content: center; border-radius: 50%; box-shadow: 0 10px 25px rgba(0,0,0,0.25); text-decoration: none; transition: transform 0.3s ease; }
        .whats_btn:hover { transform: scale(1.1); }
        .whats_btn img { width: 35px; height: 35px; }

        #otpModal { z-index: 99999 !important; }
        .otp-modal-content { border: none !important; border-radius: 12px !important; background: #ffffff; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2); text-align: center; overflow: hidden; position: relative; }
        .otp-modal-content::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 5px; background: {{ $headerBg }}; }
        .otp-icon-box { width: 60px; height: 60px; background: #f0fdf4; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 10px auto 15px; color: {{ $headerBg }}; }
        .otp-input { width: 100%; letter-spacing: 12px; text-align: center; font-size: 24px; font-weight: bold; color: #333; border: 2px solid #eee !important; border-radius: 8px !important; background: #fafafa; height: 55px; transition: all 0.3s ease; }
        .otp-input:focus { border-color: {{ $headerBg }} !important; background: #fff; outline: none; box-shadow: 0 0 0 3px rgba(0,100,0,0.1) !important;}
        .btn-verify { background: {{ $headerBg }}; border: none; padding: 10px; font-size: 16px; border-radius: 8px; width: 100%; color: white; font-weight: 700;}

        @media (max-width: 768px) {
            .gallery-grid { grid-template-columns: 1fr; }
            .review-grid { grid-template-columns: repeat(2, 1fr); }
            .checkout-grid { grid-template-columns: 1fr; }
            .headline { font-size: 18px; }
            .package-item { flex-direction: column; align-items: flex-start; gap: 10px; }
            .pkg-right { width: 100%; justify-content: space-between; }
            .contact-banner h4 { font-size: 18px; }
            .contact-banner .phone { font-size: 22px; }
            .section-header { font-size: 16px; padding: 8px; }
            .whats_btn { width: 50px; height: 50px; right: 10px; bottom: 80px; }
            .whats_btn img { width: 28px; height: 28px; }
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
        <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1"/></noscript>
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

<div class="main-container">
    
    <h1 class="headline">{{ $ln_pg->title1 ?? 'Murdha Moaharee মুসলমানের শেষ গোসল ও শেষ বিদায় হোক পরিপূর্ণ শরীয়া মোতাবেক, পর্দা এবং সম্মানের সহিত' }}</h1>

    @if($ln_pg->right_product_image)
        <img src="{{ asset('landing_pages/'.$ln_pg->right_product_image) }}" alt="Main Product" class="main-img">
    @endif

    <a href="#orderForm" class="btn-order"><i class="fas fa-shopping-cart"></i> অর্ডার করতে ক্লিক করুন</a>

    @if(!empty($ln_pg->video_url))
    <div class="section-header">অর্ডারের জন্য ভিডিওটি দেখুন</div>
    <div class="video-container">
        {!! $ln_pg->video_url !!}
    </div>
    <a href="#orderForm" class="btn-order"><i class="fas fa-shopping-cart"></i> অর্ডার করতে ক্লিক করুন</a>
    @endif

    @php $sliderImages = $ln_pg->images ?? collect([]); @endphp
    @if($sliderImages->count() > 0)
    <div class="section-header">পণ্যগুলোর বিস্তারিত ছবি ও সাইজ দেখুন</div>
    <div class="gallery-grid">
        @foreach($sliderImages->take(2) as $img)
            <img src="{{ asset('landing_sliders/'.$img->image) }}" alt="Gallery">
        @endforeach
    </div>
    <a href="#orderForm" class="btn-order"><i class="fas fa-shopping-cart"></i> অর্ডার করতে ক্লিক করুন</a>
    @endif

    <div class="contact-banner">
        <h4>সরাসরি কথা বলুন এবং অর্ডার নিশ্চিত করুন</h4>
        <p>যেকোনো তথ্য জানতে কল করুন</p>
        <div class="phone"><i class="fas fa-phone-alt"></i> {{ $phoneNumber }}</div>
    </div>

    @if(!empty($ln_pg->feature_title))
    <div class="section-header">{{ $ln_pg->feature_title }}</div>
    @endif
    @if(!empty($ln_pg->feature_list))
        <div style="background: transparent; padding: 10px;">
            {!! str_replace('<ul>', '<ul class="feature-list">', $ln_pg->feature_list) !!} 
        </div>
    @endif
    <a href="#orderForm" class="btn-order"><i class="fas fa-shopping-cart"></i> অর্ডার করতে ক্লিক করুন</a>

    @if($ln_pg->review_images && $ln_pg->review_images->count() > 0)
    <div class="section-header">কাস্টমার রিভিউ</div>
    <div class="review-grid">
        @foreach($ln_pg->review_images->take(4) as $review)
            <img src="{{ asset('review_landing_sliders/'.$review->review_image) }}" alt="Review">
        @endforeach
    </div>
    <a href="#orderForm" class="btn-order"><i class="fas fa-shopping-cart"></i> অর্ডার করতে ক্লিক করুন</a>
    @endif

    <div class="order-wrapper" id="orderForm">
        <div class="section-header">অর্ডারটি কনফার্ম করুন</div>
        
        <form id="checkout_form" action="{{ route('front.storelandData') }}" method="POST">
            @csrf
                        {{-- কোন landing page থেকে order এলো (Order Management-এর Source-এ দেখায়) --}}
                        <input type="hidden" name="landing_page_type" value="7">
            <input type="hidden" name="prd_id" value="{{ $productId }}">
            <input type="hidden" name="amount" id="subtotal_amount" value="0">
            <input type="hidden" name="final_amount" id="final_amount" value="0">
            <input type="hidden" name="purchase_event_id" id="purchase_event_id" value="">
            
            <div class="package-list">
                @if($variations->count() > 0)
                    @foreach($variations as $key => $v)
                        @php
                            $vPrice = intval((isset($v->after_discount_price) && $v->after_discount_price > 0) ? $v->after_discount_price : ($v->price ?? $defaultPrice));
                            $label = trim(($v->size->name ?? '') . ' ' . ($v->color->name ?? ''));
                            $imgSrc = $product->image ? getImage('products', $product->image) : asset('frontend/images/no-image.png');
                            $isChecked = ($key === 0) ? 'checked' : ''; 
                        @endphp
                        <div class="package-item {{ $isChecked ? 'active' : '' }}">
                            <div class="pkg-left">
                                <input type="checkbox" class="pkg-checkbox" value="{{ $v->id }}" data-price="{{ $vPrice }}" data-name="{{ $label }}" {{ $isChecked }}>
                                <img src="{{ $imgSrc }}" class="pkg-img" alt="Img">
                                <p class="pkg-name">{{ $productName }} ({{ $label }})</p>
                            </div>
                            <div class="pkg-right">
                                <div class="qty-box">
                                    <button type="button" class="qty-btn minus">-</button>
                                    <input type="text" class="qty-input" value="1" readonly>
                                    <button type="button" class="qty-btn plus">+</button>
                                </div>
                                <div class="pkg-price"><span class="item-price-display">{{ $vPrice }}</span> ৳</div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="package-item active">
                        <div class="pkg-left">
                            <input type="checkbox" class="pkg-checkbox" value="default" data-price="{{ $defaultPrice }}" data-name="{{ $productName }}" checked>
                            <img src="{{ $product->image ? getImage('products', $product->image) : asset('frontend/images/no-image.png') }}" class="pkg-img" alt="Img">
                            <p class="pkg-name">{{ $productName }}</p>
                        </div>
                        <div class="pkg-right">
                            <div class="qty-box">
                                <button type="button" class="qty-btn minus">-</button>
                                <input type="text" class="qty-input" value="1" readonly>
                                <button type="button" class="qty-btn plus">+</button>
                            </div>
                            <div class="pkg-price"><span class="item-price-display">{{ $defaultPrice }}</span> ৳</div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="checkout-grid">
                
                <div class="form-part">
                    <div class="p-3 border rounded mb-4" style="background: #fff;">
                        <label class="form-label text-start fw-bold mb-3 border-bottom pb-2"><i class="fas fa-user-edit text-muted"></i> বিলিং ও শিপিং তথ্য:</label>
                        <div class="mb-3">
                            <input type="text" name="first_name" class="form-control" placeholder="আপনার নাম *" required>
                        </div>
                        <div class="mb-3">
                            <input type="tel" name="mobile" class="form-control" placeholder="মোবাইল নাম্বার দিন *" required maxlength="11">
                        </div>
                        <div class="mb-3">
                            <textarea name="shipping_address" class="form-control" rows="2" placeholder="আপনার সম্পূর্ণ ঠিকানা *" required></textarea>
                        </div>
                        <div class="mb-3">
                            <select name="delivery_charge_id" id="delivery_charge" class="form-select" required>
                                <option value="" disabled selected>ডেলিভারি এরিয়া সিলেক্ট করুন *</option>
                                @foreach($charges as $charge) 
                                    <option value="{{ $charge->id }}" data-charge="{{ $isFreeShipping ? 0 : intval($charge->amount) }}">
                                        {{ $charge->title }} - {!! $isFreeShipping ? '<span class="text-success">ফ্রি ডেলিভারি (0 ৳)</span>' : intval($charge->amount) . ' ৳' !!}
                                    </option> 
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="p-3 border rounded" style="background: #fff;">
                        <label class="form-label text-start fw-bold mb-3 border-bottom pb-2"><i class="fas fa-wallet text-muted"></i> পেমেন্ট মাধ্যম:</label>
                        
                        <label class="d-flex align-items-center mb-2 w-100">
                            <input type="radio" name="payment_method" value="cod" checked class="me-2 payment-radio">
                            <div class="payment-card w-100 mb-0">
                                <i class="fas fa-hand-holding-usd"></i>
                                <div><b>ক্যাশ অন ডেলিভারি</b><small class="text-muted d-block" style="font-size:12px;">পণ্য হাতে পেয়ে টাকা পরিশোধ করুন</small></div>
                            </div>
                        </label>
                        
                        @if(isset($information->ssl_active) && $information->ssl_active == 1)
                        <label class="d-flex align-items-center mb-2 w-100">
                            <input type="radio" name="payment_method" value="sslcommerz" class="me-2 payment-radio">
                            <div class="payment-card w-100 mb-0">
                                <i class="fas fa-credit-card"></i>
                                <div><b>অনলাইন পেমেন্ট</b><small class="text-muted d-block" style="font-size:12px;">বিকাশ, নগদ, রকেট বা কার্ডের মাধ্যমে পেমেন্ট</small></div>
                            </div>
                        </label>
                        @endif

                        @if(isset($information->bkash_active) && $information->bkash_active == 1)
                        <label class="d-flex align-items-center mb-2 w-100">
                            <input type="radio" name="payment_method" value="bkash" class="me-2 payment-radio">
                            <div class="payment-card w-100 mb-0" style="border-color: #E2136E;">
                                <i class="fas fa-mobile-alt" style="color: #E2136E;"></i>
                                <div class="w-100 d-flex justify-content-between align-items-center">
                                    <div><b>বিকাশ পেমেন্ট</b><small class="text-muted d-block" style="font-size:12px;">নিরাপদ বিকাশ পেমেন্ট (bKash)</small></div>
                                    <img src="{{ asset('frontend/images/bkash_logo.png') }}" alt="bKash" style="height: 20px; width: auto; object-fit: contain;">
                                </div>
                            </div>
                        </label>
                        @endif

                        @if(isset($information->eps_active) && $information->eps_active == 1)
                        <label class="d-flex align-items-center mb-2 w-100">
                            <input type="radio" name="payment_method" value="eps" class="me-2 payment-radio">
                            <div class="payment-card w-100 mb-0">
                                <i class="fas fa-wallet" style="color: #17a2b8;"></i>
                                <div><b>EPS পেমেন্ট</b><small class="text-muted d-block" style="font-size:12px;">Easy Payment System</small></div>
                            </div>
                        </label>
                        @endif

                        @if(isset($information->nagad_active) && $information->nagad_active == 1)
                        <label class="d-flex align-items-center mb-2 w-100">
                            <input type="radio" name="payment_method" value="nagad" class="me-2 payment-radio">
                            <div class="payment-card w-100 mb-0">
                                <i class="fas fa-mobile-alt" style="color: #ED1C24;"></i>
                                <div class="w-100 d-flex justify-content-between align-items-center">
                                    <div><b>নগদ পেমেন্ট</b><small class="text-muted d-block" style="font-size:12px;">নিরাপদ নগদ পেমেন্ট (Nagad)</small></div>
                                    <img src="{{ asset('frontend/images/nagad.png') }}" alt="Nagad" style="height: 20px; width: auto; object-fit: contain;">
                                </div>
                            </div>
                        </label>
                        @endif

                        @if(isset($information->uddoktapay_active) && $information->uddoktapay_active == 1)
                        <label class="d-flex align-items-center mb-2 w-100">
                            <input type="radio" name="payment_method" value="uddoktapay" class="me-2 payment-radio">
                            <div class="payment-card w-100 mb-0">
                                <i class="fas fa-money-check" style="color: #28a745;"></i>
                                <div><b>উদ্দোক্তাপে</b><small class="text-muted d-block" style="font-size:12px;">UddoktaPay Payment</small></div>
                            </div>
                        </label>
                        @endif

                        @foreach($activeManuals as $mp)
                        <label class="d-flex align-items-center mb-2 w-100">
                            <input type="radio" name="payment_method" value="{{ $mp->name }}" class="me-2 payment-radio" data-number="{{ $mp->number }}" data-type="{{ $mp->type }}">
                            <div class="payment-card w-100 mb-0">
                                <i class="fas fa-mobile-alt"></i>
                                <div>
                                    <b>{{ $mp->name }} ({{ $mp->type }})</b>
                                    <small class="text-muted d-block" style="font-size:12px;">ম্যানুয়ালি টাকা পাঠিয়ে অর্ডার করুন</small>
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>

                    <div id="manual_payment_area" style="display: none;" class="mt-3 p-3 border rounded bg-light" style="border-style: dashed !important; border-color: #cbd5e1 !important;">
                        <div class="manual-instruction-box mb-3 d-flex align-items-center p-2 rounded" style="background: rgba(226, 19, 110, 0.08); border: 1px solid rgba(226, 19, 110, 0.2); color: #C90D5E;">
                            <i class="fas fa-info-circle fa-2x me-2"></i>
                            <div>
                                <p id="payment_instruction" class="mb-0 fw-bold hind" style="font-size: 14px;"></p>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-dark fw-bold hind" style="font-size: 13px;">যে নাম্বার থেকে টাকা পাঠিয়েছেন <span class="text-danger">*</span></label>
                                <div class="input-icon-wrap position-relative">
                                    <i class="fas fa-phone-alt position-absolute" style="top:50%; left:12px; transform:translateY(-50%); color:#6c757d; font-size:14px;"></i>
                                    <input type="text" name="sender_number" id="sender_number" class="form-control" placeholder="017XXXXXXXX" style="padding-left:35px;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-dark fw-bold hind" style="font-size: 13px;">Transaction ID (TrxID) <span class="text-danger">*</span></label>
                                <div class="input-icon-wrap position-relative">
                                    <i class="fas fa-receipt position-absolute" style="top:50%; left:12px; transform:translateY(-50%); color:#6c757d; font-size:14px;"></i>
                                    <input type="text" name="transaction_id" id="transaction_id" class="form-control" placeholder="TRX123456789" style="padding-left:35px;">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>

                <div class="summary-part">
                    <div class="summary-box sticky-top" style="top: 20px;">
                        <div class="summary-title"><i class="fas fa-shopping-basket"></i> অর্ডারের সারাংশ</div>
                        
                        <div id="selected_items_list">
                        </div>
                        
                        @if(isset($information->coupon_visibility) && $information->coupon_visibility == 1)
                        <div class="coupon-section">
                            <label class="fw-bold mb-2 text-dark" style="font-size:13px;"><i class="fas fa-ticket-alt"></i> কুপন কোড (যদি থাকে)</label>
                            <div class="coupon-input-group">
                                <input type="text" id="coupon_code" placeholder="কোড লিখুন">
                                <button type="button" id="coupon_btn_submit" onclick="applyCouponLand()">APPLY</button>
                            </div>
                            <small id="coupon_msg" class="d-block mt-2 fw-bold"></small>
                        </div>
                        @endif

                        <div class="calc-row mt-3">
                            <span>Subtotal</span>
                            <span><span id="calc_subtotal">0</span> TK</span>
                        </div>
                        <div class="calc-row">
                            <span>Shipping Charge</span>
                            <span id="calc_shipping_text"><span id="calc_shipping">0</span> TK</span>
                        </div>
                        
                        <div class="calc-row text-success" id="discount_row" style="display:none;">
                            <span>Discount</span>
                            <span>- <span id="calc_discount">0</span> TK</span>
                        </div>

                        <div class="calc-row total">
                            <span>Payable Amount</span>
                            <span><span id="calc_total">0</span> TK</span>
                        </div>

                        @if(isset($information->ssl_terms_active) && $information->ssl_terms_active == 1)
                        <div class="mb-3 mt-3 p-2 bg-white border rounded" id="terms_checkbox_area" style="display: none;">
                            <div class="form-check d-flex align-items-center gap-2 m-0">
                                <input class="form-check-input mt-0" type="checkbox" id="agree_terms" name="agree_terms" value="1" style="width: 18px; height: 18px; cursor: pointer;">
                                <label class="form-check-label text-dark mb-0" for="agree_terms" style="cursor: pointer; font-size: 13px; line-height:1.4;">
                                    I agree to the <a href="{{ url('/page/terms-condition') }}" target="_blank" class="text-primary fw-bold text-decoration-none">Terms & Conditions</a> & <a href="{{ route('front.returnPolicy') }}" target="_blank" class="text-primary fw-bold text-decoration-none">Return Policy</a>.
                                </label>
                            </div>
                            <small class="text-danger d-none fw-bold mt-1" id="terms_error" style="font-size:12px;">You must agree to proceed.</small>
                        </div>
                        @endif

                        <button type="submit" id="submit_btn" class="btn-submit"><i class="fas fa-check-circle"></i> অর্ডার কনফার্ম করুন</button>
                    </div>
                </div>

            </div>
            
            <input type="hidden" name="selected_items" id="selected_items_data">
        </form>
    </div>
    
    @if(!empty($ln_pg->faq_title) || !empty($ln_pg->faq_1_q))
    <div class="faq-section">
        <div class="section-header">{{ $ln_pg->faq_title ?? 'সচরাচর জিজ্ঞাসা (FAQ)' }}</div>
        <div class="accordion" id="faqAccordion">
            @for($i = 1; $i <= 4; $i++)
                @php 
                    $question = $ln_pg->{'faq_'.$i.'_q'};
                    $answer = $ln_pg->{'faq_'.$i.'_a'};
                @endphp
                @if(!empty($question) && !empty($answer))
                <div class="accordion-item faq-item">
                    <h2 class="accordion-header" id="heading{{$i}}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{$i}}" aria-expanded="false" aria-controls="collapse{{$i}}">
                            {{ $question }}
                        </button>
                    </h2>
                    <div id="collapse{{$i}}" class="accordion-collapse collapse" aria-labelledby="heading{{$i}}" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            {{ $answer }}
                        </div>
                    </div>
                </div>
                @endif
            @endfor
        </div>
    </div>
    @endif

</div> 

<div style="background: #fff; padding-top: 30px; border-top: 1px solid #e5e7eb; margin-top: 40px;">
    @include('frontend.partials.footer')
</div>

@if(!empty($waNumberClean))
    <a href="https://wa.me/{{ $waNumberClean }}" target="_blank" class="whats_btn" aria-label="WhatsApp">
        <img src="https://img.icons8.com/color/96/whatsapp--v1.png" alt="whatsapp">
    </a>
@endif

<div class="modal fade" id="otpModal" data-bs-backdrop="static" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content otp-modal-content p-4 mx-3">
      <div class="modal-header border-0 pb-0 justify-content-end">
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center pt-0 pb-4">
        <div class="otp-icon-box"><i class="fas fa-shield-alt fa-2x"></i></div>
        <h4 class="fw-bold mb-2 otp-title" style="font-size: 20px;">মোবাইল ভেরিফিকেশন</h4>
        <p class="otp-subtitle" style="font-size: 14px;">আপনার <span class="fw-bold text-dark" id="otp_sent_number"></span> নাম্বারে কোড পাঠানো হয়েছে।</p>
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

@if(isset($information->bkash_active) && $information->bkash_active == 1)
    <button id="bKash_button" style="display: none;"></button>
    @php
        $bkashScriptUrl = (isset($information->bkash_sandbox) && $information->bkash_sandbox == 1) 
            ? 'https://scripts.sandbox.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout-sandbox.js' 
            : 'https://scripts.pay.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout.js';
    @endphp
    <script src="{{ $bkashScriptUrl }}"></script>
@endif

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    var paymentID;
    var dynamicOrderId;
    var successUrl = '';
    var isFreeShipping = {{ $isFreeShipping }};

    function scrollToForm() {
        document.getElementById('orderForm').scrollIntoView({ behavior: 'smooth' });
    }

    $(document).ready(function() {
        toastr.options = { "closeButton": true, "progressBar": true, "positionClass": "toast-top-right", "timeOut": "3000" };

        var isWeightBased = {{ $isWeightBased ? 'true' : 'false' }};
        var isTermsEnabled = {{ (isset($information->ssl_terms_active) && $information->ssl_terms_active == 1) ? 'true' : 'false' }};
        
        var current_discount_val = 0;
        var current_discount_type = "fixed";
        var isOtpVerified = false;
        var otpSystemEnabled = {{ $information->otp_system ?? 0 }};
        var otpTimerInterval;
        var isSendingOtp = false;

        $('.pkg-checkbox').on('change', function() {
            if($(this).is(':checked')) {
                $(this).closest('.package-item').addClass('active');
            } else {
                $(this).closest('.package-item').removeClass('active');
            }
            calculateTotals();
        });

        $('.qty-btn.plus').on('click', function() {
            let $input = $(this).siblings('.qty-input');
            let qty = parseInt($input.val()) || 1;
            $input.val(qty + 1);
            
            let $checkbox = $(this).closest('.package-item').find('.pkg-checkbox');
            if(!$checkbox.is(':checked')) {
                $checkbox.prop('checked', true).trigger('change');
            } else {
                calculateTotals();
            }
        });

        $('.qty-btn.minus').on('click', function() {
            let $input = $(this).siblings('.qty-input');
            let qty = parseInt($input.val()) || 1;
            if(qty > 1) {
                $input.val(qty - 1);
                calculateTotals();
            } else {
                let $checkbox = $(this).closest('.package-item').find('.pkg-checkbox');
                $checkbox.prop('checked', false).trigger('change');
            }
        });

        $('#delivery_charge').on('change', calculateTotals);

        function calculateTotals() {
            let subtotal = 0;
            let totalQty = 0;
            let itemsData = [];
            let $summaryList = $('#selected_items_list');
            $summaryList.empty();

            $('.package-item').each(function() {
                let $checkbox = $(this).find('.pkg-checkbox');
                if($checkbox.is(':checked')) {
                    let id = $checkbox.val();
                    let price = parseFloat($checkbox.data('price')) || 0;
                    let qty = parseInt($(this).find('.qty-input').val()) || 1;
                    let name = $checkbox.data('name') || 'Product';
                    
                    let itemTotal = price * qty;
                    subtotal += itemTotal;
                    totalQty += qty;
                    
                    itemsData.push({ id: id, quantity: qty, price: price });
                    
                    $summaryList.append(`
                        <div style="display:flex; justify-content:space-between; font-size:14px; margin-bottom:8px; color:#444; border-bottom:1px solid #f1f3f5; padding-bottom:8px;">
                            <span style="flex-grow:1; padding-right:10px; display:flex; align-items:center; gap:8px;">
                                <img src="${$(this).find('.pkg-img').attr('src')}" style="width:30px; height:30px; border-radius:4px; object-fit:cover;">
                                <span>${name} <b class="text-danger">x${qty}</b></span>
                            </span>
                            <span style="font-weight:700; color:#111;">${itemTotal} TK</span>
                        </div>
                    `);
                }
            });

            $('#selected_items_data').val(JSON.stringify(itemsData));
            $('#subtotal_amount').val(subtotal);
            $('#calc_subtotal').text(subtotal);

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

            let $opt = $('#delivery_charge').find("option:selected");
            let cid = $opt.val();

            if (subtotal === 0) {
                $('#calc_shipping').text(0);
                $('#calc_total').text(0);
                $('#final_amount').val(0);
                return;
            }

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
                        quantity: totalQty,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        if(res.success) {
                            let charge = Math.round(parseFloat(res.charge));
                            $('#calc_shipping').text(charge); 
                            
                            let total = (subtotal + charge) - discount;
                            if(total < 0) total = 0;
                            
                            $('#calc_total').text(total);
                            $('#final_amount').val(total); 
                        }
                    }
                });
            } else {
                let charge = Math.round(parseFloat($opt.data('charge')) || 0);
                $('#calc_shipping').text(charge); 
                
                let total = (subtotal + charge) - discount;
                if(total < 0) total = 0;
                
                $('#calc_total').text(total);
                $('#final_amount').val(total); 
            }
        }

        calculateTotals();

        window.applyCouponLand = function() {
            var code = $('#coupon_code').val();
            var $btn = $('#coupon_btn_submit');
            if(!code) { toastr.error('কুপন কোড লিখুন'); return; }

            var subtotal = parseFloat($('#subtotal_amount').val()) || 0;
            $btn.prop('disabled', true).text('...');

            $.ajax({
                url: "{{ route('front.getCouponDiscount') }}", 
                method: "GET",
                data: { code: code, total_price: subtotal },
                success: function(res) {
                    if(res.success) {
                        toastr.success(res.msg);
                        $('#coupon_msg').text(res.msg).css('color', 'green');
                        $btn.prop('disabled', false).text('Applied');
                        current_discount_val = parseFloat(res.amount);
                        current_discount_type = res.discount_type;
                        calculateTotals(); 
                    } else {
                        $('#coupon_msg').text(res.msg).css('color', 'red');
                        toastr.error(res.msg);
                        $btn.prop('disabled', false).text('APPLY');
                        current_discount_val = 0;
                        calculateTotals(); 
                    }
                },
                error: function() {
                    toastr.error('Error applying coupon');
                    $btn.prop('disabled', false).text('APPLY');
                }
            });
        };

        $('input[name="mobile"]').on('blur', function() {
            let mobile = $(this).val();
            if(mobile.length === 11) {
                let itemsStr = $('#selected_items_data').val();
                let items = itemsStr ? JSON.parse(itemsStr) : [];
                let firstItem = items.length > 0 ? items[0] : null;
                
                $.post("{{ route('incompleteStore') }}", {
                    mobile: mobile,
                    name: $('input[name="first_name"]').val(),
                    address: $('input[name="shipping_address"]').val(),
                    prd_id: "{{ $productId }}",
                    variation_id: firstItem ? firstItem.id : '',
                    quantity: firstItem ? firstItem.quantity : 1,
                    amount: $('#subtotal_amount').val(),
                    _token: "{{ csrf_token() }}"
                });
            }
        });

        $(document).on('change', 'input[name="payment_method"]', function() {
            var method = $(this).val();
            var manualArea = $('#manual_payment_area');
            var sNum = $('#sender_number');
            var tId = $('#transaction_id');
            var termsArea = $('#terms_checkbox_area');

            if (method === 'sslcommerz' || method === 'bkash' || method === 'eps' || method === 'nagad' || method === 'uddoktapay') {
                if(isTermsEnabled) termsArea.slideDown();
                manualArea.slideUp();
                sNum.removeAttr('required');
                tId.removeAttr('required');
            } else if (method !== 'cod' && method !== 'sslcommerz') {
                var number = $(this).data('number');
                var type = $(this).data('type');
                $('#payment_instruction').html(`দয়া করে আপনার টোটাল বিল <b>${number} (${type})</b> নাম্বারে Send Money করুন। এরপর নিচের তথ্যগুলো দিন।`);

                manualArea.slideDown();
                if(isTermsEnabled) termsArea.slideUp();
                sNum.attr('required', 'required'); 
                tId.attr('required', 'required');
            } else {
                if(isTermsEnabled) termsArea.slideUp();
                manualArea.slideUp();
                sNum.removeAttr('required');
                tId.removeAttr('required');
            }
        });
        $('input[name="payment_method"]:checked').trigger('change');

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
            var mobile = $('input[name="mobile"]').val();
            if(!mobile || mobile.length !== 11) { toastr.error('১১ ডিজিটের সঠিক মোবাইল নাম্বার দিন'); return; }
            
            isSendingOtp = true;
            if(!isResend) $('#submit_btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Sending OTP...');
            
            $.ajax({
                url: "{{ route('sendOtp') }}", type: "POST", data: { mobile: mobile, _token: "{{ csrf_token() }}" },
                success: function(res) {
                    isSendingOtp = false;
                    if(!isResend) $('#submit_btn').prop('disabled', false).html('<i class="fas fa-check-circle"></i> অর্ডার কনফার্ম করুন');
                    
                    if(res.success) {
                        $('#otp_sent_number').text(mobile);
                        var myModal = new bootstrap.Modal(document.getElementById('otpModal'));
                        myModal.show();
                        setTimeout(function() { $('#otp_input').focus(); }, 500);
                        startOtpTimer(30, $('#resendOtpBtn'));
                    } else { toastr.error(res.msg); }
                },
                error: function() { 
                    isSendingOtp = false; 
                    if(!isResend) $('#submit_btn').prop('disabled', false).html('<i class="fas fa-check-circle"></i> অর্ডার কনফার্ম করুন'); 
                }
            });
        };

        window.verifyOtpNow = function() {
            var code = $('#otp_input').val();
            var mobile = $('input[name="mobile"]').val();
            $.ajax({
                url: "{{ route('verifyOtp') }}", type: "POST", data: { otp: code, mobile: mobile, _token: "{{ csrf_token() }}" },
                success: function(res) {
                    if(res.success) {
                        isOtpVerified = true;
                        bootstrap.Modal.getInstance(document.getElementById('otpModal')).hide();
                        toastr.success('ভেরিফিকেশন সফল!');
                        submitOrderFinal();
                    } else { $('#otp_error').text(res.msg); }
                }
            });
        };

        $('#checkout_form').submit(function(e) {
            e.preventDefault();
            
            let itemsStr = $('#selected_items_data').val();
            let items = itemsStr ? JSON.parse(itemsStr) : [];
            
            if(items.length === 0) {
                toastr.error('দয়া করে অন্তত একটি প্রোডাক্ট সিলেক্ট করুন!');
                return false;
            }

            if(!$('#delivery_charge').val()){ 
                toastr.warning('ডেলিভারি এরিয়া সিলেক্ট করুন!'); 
                return false; 
            }

            let paymentMethod = $('input[name="payment_method"]:checked').val() || 'cod'; 

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
                sendOtpBeforeSubmit();
            } else {
                submitOrderFinal();
            }
        });

        function submitOrderFinal() {
            let $form = $('#checkout_form');
            let paymentMethod = $('input[name="payment_method"]:checked').val() || 'cod'; 
            
            var purchaseEventId = "PUR_{{ $productId }}_" + Date.now();
            $('#purchase_event_id').val(purchaseEventId);

            if (paymentMethod === 'sslcommerz') {
                $form.attr('action', "{{ url('/pay') }}").attr('method', 'POST')[0].submit();
                return;
            }

            $('#submit_btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> প্রসেসিং...');

            var total = parseFloat($('#final_amount').val()) || 0;
            var ship_charge = parseFloat($('#calc_shipping').text()) || 0;
            
            let itemsStr = $('#selected_items_data').val();
            let items = itemsStr ? JSON.parse(itemsStr) : [];

            let fbItems = items.map(function(item) {
                return {
                    item_id: "{{ $productId }}",
                    item_name: @json($productName),
                    price: item.price,
                    quantity: item.quantity
                };
            });

            $.ajax({
                url: $form.attr('action'), 
                method: "POST", 
                data: $form.serialize(),
                success: function(res){
                    if(res.success){ 
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
                            setTimeout(function(){ window.location.href = res.url; }, 800);
                        }
                    } else { 
                        toastr.error(res.msg); 
                        $('#submit_btn').prop('disabled', false).html('<i class="fas fa-check-circle"></i> অর্ডার কনফার্ম করুন'); 
                    }
                },
                error: function () { 
                    $('#submit_btn').prop('disabled', false).html('<i class="fas fa-check-circle"></i> অর্ডার কনফার্ম করুন'); 
                    toastr.error('সার্ভারে সমস্যা হচ্ছে।'); 
                }
            });
        }
    });

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
                            $('#submit_btn').prop('disabled', false).html('<i class="fas fa-check-circle"></i> অর্ডার কনফার্ম করুন');
                        }
                    },
                    error: function (err) {
                        bKash.create().onError();
                        toastr.error("Server error while connecting to bKash.");
                        $('#submit_btn').prop('disabled', false).html('<i class="fas fa-check-circle"></i> অর্ডার কনফার্ম করুন');
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
                            $('#submit_btn').prop('disabled', false).html('<i class="fas fa-check-circle"></i> অর্ডার কনফার্ম করুন');
                        }
                    },
                    error: function () {
                        bKash.execute().onError();
                        toastr.error("Failed to execute bKash payment.");
                        $('#submit_btn').prop('disabled', false).html('<i class="fas fa-check-circle"></i> অর্ডার কনফার্ম করুন');
                    }
                });
            },
            onClose: function () {
                window.location.href = successUrl;
            }
        });
    }
    @endif
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