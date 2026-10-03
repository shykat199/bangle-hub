<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $ln_pg->title1 ?? 'Premium Landing Page' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- ✅ FontAwesome 5.15.4 ✅ -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    @php
        $information = \App\Models\Information::first();
        $activeManuals = \App\Models\ManualPayment::where('status', 1)->get();
        
        $charges = \App\Models\DeliveryCharge::whereNotNull('status')->get();
        $sslActive = $information->ssl_active ?? 0;
        $sslTermsActive = $information->ssl_terms_active ?? 0;
        
        $globalSetting = DB::table('delivery_charges')->first();
        $isWeightBased = $globalSetting && $globalSetting->charge_type == 'weight_based';

        $brandSolid   = $ln_pg->theme_primary_col ?? (optional($information)->primary_color ?? '#00276C');
        $btnBgColor   = $ln_pg->btn_bg_color ?? '#e11d48'; 
        $brandGradient= $ln_pg->theme_gradient_col ?? 'linear-gradient(90deg, '.$brandSolid.', #000000)';
        
        $btnTextColor = $ln_pg->btn_text_color ?? '#ffffff';
        $bodyBg       = $ln_pg->landing_bg_color ?? '#f5f6f8'; 
        
        $productId = $product->id ?? 0;
        $productName = $product->name ?? 'Product';
        
        $phoneNumber = $ln_pg->phone ?? $ln_pg->phone_number ?? $ln_pg->whatsapp ?? optional($information)->phone ?? '';
        $callText = $ln_pg->call_text ?? 'সরাসরি কথা বলুন এবং অর্ডার নিশ্চিত করুন';
        $waNumberClean = preg_replace('/\D+/', '', $phoneNumber);

        $defaultPrice = ($product && $product->after_discount > 0) ? $product->after_discount : ($product->sell_price ?? 0);
        
        if(!empty($ln_pg->new_price)) {
            $defaultPrice = $ln_pg->new_price;
        }

        $variations = collect();
        if($product){
            try{
                $product->loadMissing(['variations.size','variations.color', 'variations.stocks', 'category']);
                $variations = $product->variations ?? collect();
            }catch(\Throwable $e){
                $variations = $product->variations ?? collect();
            }
        }
        
        $defaultVar = $variations->first();
        $defaultVarId = $defaultVar->id ?? null;
        // Stock source: variation stock if any; else the higher of product stock_quantity column and product_stocks table sum
        $defaultStock = $defaultVar
            ? (int) $defaultVar->stocks->sum('quantity')
            : ($product ? max((int) ($product->stock_quantity ?? 0), (int) $product->stocks()->sum('quantity')) : 0);
        $contentCategory = $product?->category?->name ?? 'Landing Page';

        $pixelId = setting('fb_pixel_id') ?? null;
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
        /* ✅✅✅ FIXED: Font Issue Resolved ✅✅✅ */
        body, h1, h2, h3, h4, h5, h6, p, div, span, a, button, input, select, textarea, label, td, th { 
            font-family: 'Hind Siliguri', sans-serif; 
        }

        /* Protect FontAwesome Icons from being overwritten */
        .fas, .far, .fa, .fab {
            font-family: "Font Awesome 5 Free" !important;
        }
        .fab {
            font-family: "Font Awesome 5 Brands" !important;
        }

        :root{
            --brand-gradient: {!! $brandGradient !!};
            --brand-solid: {{ $brandSolid }};
            --btn-bg: {{ $btnBgColor }};
            --btn-text: {{ $btnTextColor }};
            
            --bg: {{ $bodyBg }};
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
        body{ background: var(--bg); color: var(--text); padding-bottom: 90px; }

        #toast-container { z-index: 9999999 !important; }
        #toast-container > .toast { opacity: 1 !important; box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important; }
        #toast-container > .toast-success { background-color: #28a745 !important; color: #ffffff !important; }
        #toast-container > .toast-error { background-color: #dc3545 !important; color: #ffffff !important; }
        
        .header-title { font-size: clamp(24px, 4vw, 32px); font-weight: 800; text-align: center; color: #111827; margin: 20px 0; padding: 0 15px; line-height: 1.4; }
        .header-title span { color: var(--btn-bg); }

        .content-box { 
            background: #fff; border-radius: 8px; padding: 20px; margin-bottom: 20px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; 
            word-wrap: break-word; overflow-wrap: break-word; overflow: hidden; clear: both;
        }
        .content-box p, .content-box span, .content-box div, .content-box li, .feature-list li {
            line-height: 1.7 !important; height: auto !important; white-space: normal !important; position: relative !important; margin-bottom: 8px;
        }
        .content-box img { max-width: 100% !important; height: auto !important; border-radius: 6px; margin: 10px 0; }

        .section-title { font-size: 20px; font-weight: 800; color: #fff; background-color: var(--brand-solid); padding: 12px; margin-bottom: 20px; text-align: center; border-radius: 4px; line-height: 1.4; }
        
        .video-container { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px; clear: both;}
        .video-container iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
        
        .feature-list { list-style: none; padding: 0; margin: 0; display: block; }
        .premium-feature-override, .premium-feature-override p, .premium-feature-override span, .premium-feature-override div, .premium-feature-override ul, .premium-feature-override li {
            color: #1e293b !important; font-size: 16px !important; font-weight: 600 !important; line-height: 1.8 !important; visibility: visible !important; opacity: 1 !important;
        }
        .premium-feature-override ul { list-style: none !important; padding: 0 !important; margin: 0 !important; }
        .premium-feature-override li { position: relative !important; padding-left: 32px !important; margin-bottom: 14px !important; display: block !important; }
        
        .premium-feature-override li::before {
            content: "\f058" !important; font-family: "Font Awesome 5 Free" !important; font-weight: 900 !important;
            position: absolute !important; left: 0 !important; top: 0 !important; color: var(--brand-solid) !important; font-size: 19px !important; line-height: 1 !important;
        }

        .trust-images { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 15px; }
        .trust-images img { width: 100%; aspect-ratio: 1/1; object-fit: cover; border-radius: 8px; border: 2px solid #ddd; }

        .btn-order-floating { 
            background: var(--btn-bg); color: var(--btn-text) !important; font-weight: 700; font-size: 18px; 
            padding: 12px 30px; border-radius: 6px; border: none; display: flex; align-items: center; justify-content: center; 
            gap: 8px; width: fit-content; max-width: 90%; margin: 25px auto; transition: 0.3s; text-decoration: none; 
            box-shadow: 0 6px 15px rgba(0,0,0, 0.2); clear: both; text-align: center; line-height: 1.4; white-space: normal;
        }
        .btn-order-floating:hover { filter: brightness(0.9); transform: translateY(-2px); }

        .sticky-form-wrapper { position: sticky; top: 20px; z-index: 100; }
        .order-form-card { background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); border: 1px solid #ddd;}
        .form-header { font-size: 22px; font-weight: 800; text-align: center; color: #fff; background: var(--brand-solid); padding: 12px; margin: -20px -20px 20px -20px; border-radius: 8px 8px 0 0; }
        
        .package-option { display: block; border: 2px solid #e5e7eb; border-radius: 6px; padding: 12px 15px; margin-bottom: 15px; cursor: pointer; transition: 0.3s; background: #f9fafb; }
        .package-option:hover { border-color: var(--brand-solid); background: #fff; }
        .package-input:checked + .package-option { border-color: var(--brand-solid); background: #f0fff4; box-shadow: 0 0 0 1px var(--brand-solid); }
        .package-input { display: none; }
        
        .pkg-flex { display: flex; align-items: center; justify-content: space-between; }
        .pkg-title { font-weight: 700; font-size: 16px; color: #1f2937; display: flex; align-items: center; gap: 10px; }
        .pkg-radio-circle { width: 20px; height: 20px; border: 2px solid #d1d5db; border-radius: 50%; display: inline-block; position: relative; flex-shrink: 0;}
        .package-input:checked + .package-option .pkg-radio-circle::after { content: ''; position: absolute; top: 3px; left: 3px; width: 10px; height: 10px; background: var(--brand-solid); border-radius: 50%; }
        .pkg-price { font-weight: 800; color: var(--brand-solid); font-size: 18px; }
        .pkg-discount { display: block; font-size: 13px; color: #ef4444; font-weight: 600; margin-top: 5px; margin-left: 30px; }

        .form-control, .form-select { padding: 10px 15px; border-radius: 4px; border: 1px solid #ccc; font-weight: 500; font-size: 15px;}
        .form-control:focus, .form-select:focus { border-color: var(--brand-solid); box-shadow: 0 0 0 3px rgba(0,0,0,0.05); }
        .form-label { font-weight: 700; color: #333; font-size: 14px; margin-bottom: 6px; }
        
        .payment-radio-box { cursor: pointer; border: 1px solid #ccc; padding: 12px; border-radius: 6px; width: 100%; background: #f9fafb; display: flex; align-items: center; transition: 0.2s;}
        .payment-radio-box input { margin-right: 10px; accent-color: var(--brand-solid); width: 18px; height: 18px; flex-shrink: 0;}
        .payment-radio-box.active { border-color: var(--brand-solid); background: #f0fff4; }

        #manual_payment_area { border: 1px dashed #cbd5e1; background-color: #f8fafc; }
        .manual-instruction-box { background: rgba(226, 19, 110, 0.08); border: 1px solid rgba(226, 19, 110, 0.2); color: #C90D5E; }
        .input-icon-wrap { position: relative; }
        .input-icon-wrap i { position: absolute; top: 50%; left: 16px; transform: translateY(-50%); color: #64748b; font-size: 15px; }
        .input-icon-wrap input { padding-left: 45px !important; }

        .calculation-table { width: 100%; margin: 15px 0; font-weight: 600; font-size: 15px; color: #444;}
        .calculation-table td { padding: 8px 0; }
        .calc-total { font-size: 20px; font-weight: 800; color: #000; border-top: 1px solid #ddd; }

        .btn-submit { background: var(--btn-bg); color: var(--btn-text); font-size: 20px; font-weight: 800; border: none; padding: 12px; width: 100%; border-radius: 4px; transition: 0.3s; animation: pulse 1.5s infinite; }
        .btn-submit:hover { filter: brightness(0.9); transform: translateY(-2px); color: var(--btn-text); }
        .btn-submit.is-disabled { opacity: 0.65; pointer-events: none; animation: none; }
        
        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.02); } 100% { transform: scale(1); } }

        .contact-banner { background-color: var(--brand-solid); color: #fff; text-align: center; padding: 25px 15px; border-radius: 6px; margin: 20px 0; box-shadow: 0 4px 15px rgba(0,0,0, 0.2); }
        .contact-banner h4 { font-weight: 800; font-size: 20px; margin-bottom: 8px; }
        .contact-banner .phone { font-size: 26px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 10px; }
        .contact-banner .phone a { color: #fff; text-decoration: none; transition: 0.3s; }
        .contact-banner .phone a:hover { opacity: 0.8; }

        .bottom-timer-banner { background: var(--brand-solid); color: #fff; padding: 15px; text-align: center; border-radius: 8px; margin-top: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.15); }
        .timer-box { font-size: 28px; font-weight: 800; color: #fff; font-family: monospace !important; letter-spacing: 2px; }

        .faq-section { margin-top: 40px; margin-bottom: 40px; }
        .faq-item { border: 1px solid #ddd; border-radius: 8px; margin-bottom: 10px; background: #fff; overflow: hidden; }
        .accordion-button { font-weight: 700; color: #333; background-color: #fff; box-shadow: none !important; font-size: 15px;}
        .accordion-button:not(.collapsed) { color: var(--brand-solid); background-color: #f0fdf4; }
        .accordion-body { color: #555; font-size: 15px; line-height: 1.6; }

        .whats_btn { position: fixed; right: 20px; bottom: 80px; z-index: 9999; width: 60px; height: 60px; background: #25D366; display: flex; align-items: center; justify-content: center; border-radius: 50%; box-shadow: 0 10px 25px rgba(0,0,0,0.25); text-decoration: none; transition: transform 0.3s ease; }
        .whats_btn:hover { transform: scale(1.1); }
        .whats_btn img { width: 35px; height: 35px; }

        .premium-notice-box { display: flex; align-items: flex-start; background: #fff5f5; border: 1px dashed #ef4444; border-radius: 12px; padding: 14px; gap: 12px; margin-bottom: 15px; }
        .notice-icon { font-size: 24px; color: #ef4444; flex-shrink: 0; margin-top: 2px; }
        .notice-text { font-size: 14px; color: #7f1d1d; font-weight: 600; line-height: 1.5; margin-bottom: 0; }
        
        .pro-qty { display: flex; align-items: center; border: 1px solid #ccc; border-radius: 4px; overflow: hidden; max-width: 130px; }
        .quantity-button { width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; background: #f1f5f9; font-weight: bold; cursor: pointer; user-select: none; transition: 0.2s; color: #333; }
        .quantity-button:hover { background: #e2e8f0; }
        .inner_qty { width: 50px; height: 35px; text-align: center; border: none; border-left: 1px solid #ccc; border-right: 1px solid #ccc; font-weight: 700; color: #000; background: #fff; pointer-events: none; }
        
        .stock-status { font-size: 14px; font-weight: 800; padding: 6px 12px; border-radius: 4px; display: inline-block; }
        .in-stock { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
        .out-stock { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }

        .coupon-section { background-color: #f8f9fa; border: 1px dashed #cbd5e1; border-radius: 6px; padding: 15px; margin-bottom: 20px; }
        .coupon-input-group { display: flex; overflow: hidden; border: 1px solid #e2e8f0; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
        .coupon-input-group input { border: none; padding: 10px 15px; flex-grow: 1; font-size: 14px; outline: none; background: #fff; }
        .coupon-input-group button { border: none; background: #1e293b; color: #fff; padding: 0 20px; font-weight: 700; font-size: 14px; cursor: pointer; transition: background 0.3s; }
        .coupon-input-group button:hover { background: #0f172a; }

        #otpModal { z-index: 99999 !important; }
        .otp-modal-content { border: none !important; border-radius: 20px !important; background: #ffffff; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2); text-align: center; overflow: hidden; position: relative; }
        .otp-modal-content::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #E2136E, #F6921E); }
        .otp-icon-box { width: 80px; height: 80px; background: #fdf2f7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 10px auto 20px; color: #E2136E; }
        .otp-input { width: 100%; letter-spacing: 15px; text-align: center; font-size: 28px; font-weight: bold; color: #333; border: 2px solid #eee !important; border-radius: 12px !important; background: #fafafa; height: 65px; transition: all 0.3s ease; position: relative; z-index: 999999 !important; }
        .otp-input:focus { border-color: #E2136E !important; background: #fff; box-shadow: 0 5px 15px rgba(226, 19, 110, 0.1) !important; outline: none; }
        .btn-verify { background: linear-gradient(135deg, #E2136E 0%, #C90D5E 100%); border: none; padding: 12px; font-size: 18px; border-radius: 12px; box-shadow: 0 8px 20px rgba(226, 19, 110, 0.3); width: 100%; color: white; font-family: 'Hind Siliguri', sans-serif; }
        .btn-verify:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(226, 19, 110, 0.4); }
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

    @if(!empty($pixelId))
    <script>
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
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
</head>
<body>
@if(!empty($__gtmId))
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $__gtmId }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
@endif

<div class="container py-4">
    <h1 class="header-title">{!! $ln_pg->title1 ?? 'Murdha Moaharee মুসলমানের শেষ গোসল ও শেষ বিদায় হোক পরিপূর্ণ শরীয়া মোতাবেক' !!}</h1>
    
    @if($ln_pg->title2)
    <h4 class="text-center text-muted fw-bold mb-4">{{ $ln_pg->title2 }}</h4>
    @endif

    <div class="row">
        <div class="col-lg-7 col-xl-8 mb-4">
            
            @if($ln_pg->right_product_image)
            <div class="content-box p-0 overflow-hidden text-center bg-light">
                <img src="{{ asset('landing_pages/'.$ln_pg->right_product_image) }}" class="img-fluid w-100" style="max-height: 500px; object-fit: contain;">
            </div>
            <a href="#orderForm" class="btn-order-floating"><i class="fas fa-shopping-cart"></i> {{ $ln_pg->btn_text_hero ?? 'অর্ডার করতে ক্লিক করুন' }}</a>
            @endif

            @if($ln_pg->video_url)
            <div class="section-title">অর্ডারের জন্য ভিডিওটি দেখুন</div>
            <div class="content-box p-0 overflow-hidden">
                <div class="video-container text-center bg-dark">
                    @if(strpos($ln_pg->video_url, 'iframe') !== false)
                        {!! $ln_pg->video_url !!}
                    @else
                        <iframe src="{{ $ln_pg->video_url }}" frameborder="0" allowfullscreen></iframe>
                    @endif
                </div>
            </div>
            <a href="#orderForm" class="btn-order-floating"><i class="fas fa-shopping-cart"></i> {{ $ln_pg->btn_text_hero ?? 'অর্ডার করতে ক্লিক করুন' }}</a>
            @endif

            <div class="contact-banner">
                <h4>{{ $callText }}</h4>
                <p class="mb-2">যেকোনো তথ্য জানতে কল করুন</p>
                <div class="phone">
                    @if(!empty($phoneNumber))
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phoneNumber) }}" style="color: #fff; text-decoration: none;">
                            <i class="fas fa-phone-volume"></i> {{ $phoneNumber }}
                        </a>
                    @else
                        <i class="fas fa-exclamation-triangle"></i> নাম্বার দেওয়া নেই
                    @endif
                </div>
            </div>

            @if($ln_pg->feature_list)
            <div class="section-title">{{ $ln_pg->feature_title ?? 'কেন আমাদের প্রোডাক্ট কেনা উচিত?' }}</div>
            <div class="content-box">
                <div class="feature-list premium-feature-override">
                    @php
                        $formattedFeatures = str_replace(['<ul>', '<ol>'], ['<ul class="feature-list">', '<ol class="feature-list">'], $ln_pg->feature_list);
                    @endphp
                    {!! $formattedFeatures !!} 
                </div>
            </div>
            <a href="#orderForm" class="btn-order-floating"><i class="fas fa-shopping-cart"></i> {{ $ln_pg->btn_text_hero ?? 'অর্ডার করতে ক্লিক করুন' }}</a>
            @endif

            @if($ln_pg->left_side_desc)
            <div class="content-box">
                {!! $ln_pg->left_side_desc !!}
            </div>
            @endif

            @if($ln_pg->review_images && $ln_pg->review_images->count() > 0)
            <div class="section-title">{{ $ln_pg->review_title ?? 'কাস্টমার রিভিউ' }}</div>
            <div class="content-box">
                <div class="trust-images">
                    @foreach($ln_pg->review_images as $rv)
                        <img src="{{ asset('review_landing_sliders/'.$rv->review_image) }}" alt="Review">
                    @endforeach
                </div>
            </div>
            @endif

            <div class="bottom-timer-banner">
                <h4 class="mb-2 fw-bold text-white">{{ $ln_pg->countdown_title ?? '৬০ মিনিটের মধ্যে অর্ডার করলে ডেলিভারি ফ্রি' }}</h4>
                <div class="timer-box" id="bottom_timer">60:00</div>
            </div>
        </div>

        <div class="col-lg-5 col-xl-4">
            <div class="sticky-form-wrapper" id="orderForm">
                <div class="order-form-card">
                    <div class="form-header">
                        {{ $ln_pg->form_title ?? 'অর্ডারটি কনফার্ম করুন' }}
                    </div>

                    <form id="checkout_land_form" action="{{ route('front.storelandData') }}" method="POST">
                        @csrf
                        {{-- কোন landing page থেকে order এলো (Order Management-এর Source-এ দেখায়) --}}
                        <input type="hidden" name="landing_page_type" value="8">
                        <input type="hidden" name="prd_id" value="{{ $productId }}">
                        <input type="hidden" name="amount" id="subtotal_input" value="{{ $defaultPrice }}">
                        <input type="hidden" name="final_amount" id="final_total_input" value="{{ $defaultPrice }}">
                        <input type="hidden" name="quantity" id="form_qty" value="1">
                        <input type="hidden" name="selected_package_id" id="selected_package_id" value="">
                        <input type="hidden" name="purchase_event_id" id="purchase_event_id" value="">
                        
                        <input type="hidden" name="coupon_code" id="hidden_coupon_code" value="">
                        <input type="hidden" name="discount" id="hidden_discount" value="0">

                        @if($ln_pg->packages && $ln_pg->packages->count() > 0)
                        <div class="mb-4" id="package_selection_area">
                            <label class="form-label fw-bold">প্যাকেজ সিলেক্ট করুন</label>
                            @foreach($ln_pg->packages as $key => $pkg)
                                <label class="w-100">
                                    <input type="radio" name="pkg_selection" class="package-input" value="{{ $pkg->id }}" data-price="{{ $pkg->price }}" data-qty="{{ $pkg->qty }}" {{ $key == 0 ? 'checked' : '' }}>
                                    <div class="package-option">
                                        <div class="pkg-flex">
                                            <div class="pkg-title">
                                                <span class="pkg-radio-circle"></span>
                                                {{ $product ? $product->name : 'Product' }} ({{ $pkg->qty }} পিস)
                                            </div>
                                            <div class="pkg-price">৳ {{ $pkg->price }}</div>
                                        </div>
                                        @if($pkg->discount_text)
                                            <span class="pkg-discount"><i class="fas fa-gift"></i> {{ $pkg->discount_text }}</span>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @endif

                        @if($variations->count() > 0)
                            @if($variations->count() == 1)
                                @php 
                                    $singleVar = $variations->first(); 
                                    $vBase = $singleVar->price ?? $product->sell_price ?? 0;
                                    $vDisc = $singleVar->after_discount_price ?? null;
                                    $vPrice = ((float)$vDisc > 0) ? $vDisc : $vBase;
                                    $vStock = $singleVar->stocks->sum('quantity');
                                    $label = trim(($singleVar->size->name ?? '') . ' ' . ($singleVar->color->name ?? ''));
                                    $finalLabel = $label ?: ('Variation #'.$singleVar->id);
                                @endphp
                                <input type="hidden" name="variation_id" id="variation_select" value="{{ $singleVar->id }}" data-price="{{ $vPrice }}" data-stock="{{ $vStock }}">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">সাইজ/কালার</label>
                                    <input type="text" class="form-control" style="background-color: #f8f9fa; cursor: not-allowed;" value="{{ $finalLabel }}" readonly>
                                </div>
                            @else
                                <div class="mb-3">
                                    <label class="form-label fw-bold">সাইজ/কালার সিলেক্ট করুন <span class="text-danger">*</span></label>
                                    <select name="variation_id" id="variation_select" class="form-select border-success" required>
                                        <option value="" disabled selected data-price="{{ $defaultPrice }}" data-stock="{{ $defaultStock }}">সিলেক্ট করুন...</option>
                                        @foreach($variations as $v)
                                            @php
                                                $vBase = $v->price ?? $product->sell_price ?? 0;
                                                $vDisc = $v->after_discount_price ?? null;
                                                $vPrice = ((float)$vDisc > 0) ? $vDisc : $vBase;
                                                $vStock = $v->stocks->sum('quantity');
                                                $label = trim(($v->size->name ?? '') . ' ' . ($v->color->name ?? ''));
                                            @endphp
                                            <option value="{{ $v->id }}" data-price="{{ $vPrice }}" data-stock="{{ $vStock }}" {{ ($defaultVarId == $v->id) ? 'selected' : '' }}>
                                                {{ $label ?: ('Variation #'.$v->id) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        @else
                            <input type="hidden" name="variation_id" id="variation_select" value="">
                        @endif

                        <div id="stock_status" class="stock-status {{ $defaultStock > 0 ? 'in-stock' : 'out-stock' }} mb-3">
                            {{ $defaultStock > 0 ? 'In Stock: '.$defaultStock : 'Out of Stock' }}
                        </div>
                        <input type="hidden" id="max_stock" value="{{ $defaultStock }}">

                        <div class="mb-3">
                            <label class="form-label fw-bold">আপনার নাম <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" id="name" class="form-control" placeholder="আপনার নাম লিখুন" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">মোবাইল নাম্বার <span class="text-danger">*</span></label>
                            <input type="tel" name="mobile" id="mobile" class="form-control" placeholder="01XXXXXXXXX" maxlength="11" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">সম্পূর্ণ ঠিকানা <span class="text-danger">*</span></label>
                            <textarea name="shipping_address" id="address" class="form-control" rows="2" placeholder="বাসা নং, রোড, এলাকা, থানা" required></textarea>
                        </div>

                        <div class="mb-3 d-flex align-items-center justify-content-between border p-2 rounded bg-light" id="qty_control_area">
                            <label class="form-label fw-bold mb-0 text-dark">পরিমাণ (Quantity)</label>
                            <div class="pro-qty">
                                <span class="decrease-qty quantity-button">-</span>
                                <input type="text" class="inner_qty text-center" value="1" readonly>
                                <span class="increase-qty quantity-button">+</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">ডেলিভারি এলাকা <span class="text-danger">*</span></label>
                            <select name="delivery_charge_id" id="delivery_charge" class="form-select border-success" required>
                                <option value="" disabled selected>এলাকা নির্বাচন করুন...</option>
                                @foreach($charges as $charge) 
                                    <option value="{{ $charge->id }}" data-charge="{{ $isFreeShipping ? 0 : intval($charge->amount) }}">
                                        {{ $charge->title }} - {!! $isFreeShipping ? '<span class="text-success">ফ্রি ডেলিভারি (0 ৳)</span>' : intval($charge->amount) . ' ৳' !!}
                                    </option> 
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">পেমেন্ট মাধ্যম</label>
                            
                            @if(isset($information->cod_active) && $information->cod_active == 1)
                            <label class="payment-radio-box mb-2 active">
                                <input type="radio" name="payment_method" value="cod" checked onchange="togglePaymentAction('cod')">
                                <span class="fw-bold">ক্যাশ অন ডেলিভারি</span>
                            </label>
                            @endif
                            
                            @if(isset($information->ssl_active) && $information->ssl_active == 1)
                            <label class="payment-radio-box mb-2">
                                <input type="radio" name="payment_method" value="sslcommerz" onchange="togglePaymentAction('sslcommerz')">
                                <span class="fw-bold">অনলাইন পেমেন্ট (কার্ড/SSL)</span>
                            </label>
                            @endif

                            @if(isset($information->bkash_active) && $information->bkash_active == 1)
                            <label class="payment-radio-box mb-2" style="border-color: #E2136E;">
                                <input type="radio" name="payment_method" value="bkash" class="me-2 payment-radio" onchange="togglePaymentAction('bkash')">
                                <span class="fw-bold d-flex justify-content-between w-100 align-items-center">
                                    <span>বিকাশ পেমেন্ট (bKash)</span>
                                    <img src="{{ asset('frontend/images/bkash_logo.png') }}" alt="bKash" style="height: 20px; width: auto; object-fit: contain;">
                                </span>
                            </label>
                            @endif

                            @if(isset($information->eps_active) && $information->eps_active == 1)
                            <label class="payment-radio-box mb-2" style="border-color: #17a2b8;">
                                <input type="radio" name="payment_method" value="eps" class="me-2 payment-radio" onchange="togglePaymentAction('eps')">
                                <span class="fw-bold">EPS পেমেন্ট (Easy Payment System)</span>
                            </label>
                            @endif

                            @if(isset($information->nagad_active) && $information->nagad_active == 1)
                            <label class="payment-radio-box mb-2" style="border-color: #ED1C24;">
                                <input type="radio" name="payment_method" value="nagad" class="me-2 payment-radio" onchange="togglePaymentAction('nagad')">
                                <span class="fw-bold d-flex justify-content-between w-100 align-items-center">
                                    <span>নগদ পেমেন্ট (Nagad)</span>
                                    <img src="{{ asset('frontend/images/nagad.png') }}" alt="Nagad" style="height: 20px; width: auto; object-fit: contain;">
                                </span>
                            </label>
                            @endif

                            @if(isset($information->uddoktapay_active) && $information->uddoktapay_active == 1)
                            <label class="payment-radio-box mb-2" style="border-color: #28a745;">
                                <input type="radio" name="payment_method" value="uddoktapay" class="me-2 payment-radio" onchange="togglePaymentAction('uddoktapay')">
                                <span class="fw-bold">উদ্দোক্তাপে (UddoktaPay)</span>
                            </label>
                            @endif

                            @foreach($activeManuals as $mp)
                            <label class="payment-radio-box mb-2">
                                <input type="radio" name="payment_method" value="{{ $mp->name }}" 
                                       data-number="{{ $mp->number }}" data-type="{{ $mp->type }}"
                                       onchange="togglePaymentAction('manual', '{{ $mp->name }}', '{{ $mp->number }}', '{{ $mp->type }}')">
                                <span class="fw-bold">{{ $mp->name }} ({{ $mp->type }})</span>
                            </label>
                            @endforeach
                        </div>

                        <div id="manual_payment_area" style="display: none;" class="mb-4 p-3 border rounded bg-light" style="border-style: dashed !important; border-color: #cbd5e1 !important;">
                            <div class="manual-instruction-box mb-3 d-flex align-items-center p-2 rounded">
                                <i class="fas fa-info-circle fa-2x me-2"></i>
                                <div>
                                    <p id="payment_instruction" class="mb-0 fw-bold hind" style="font-size: 14px;"></p>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-dark fw-bold hind" style="font-size: 13px;">যে নাম্বার থেকে টাকা পাঠিয়েছেন <span class="text-danger">*</span></label>
                                    <div class="input-icon-wrap">
                                        <i class="fas fa-phone-alt"></i>
                                        <input type="text" name="sender_number" id="sender_number" class="form-control" placeholder="017XXXXXXXX">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-dark fw-bold hind" style="font-size: 13px;">Transaction ID (TrxID) <span class="text-danger">*</span></label>
                                    <div class="input-icon-wrap">
                                        <i class="fas fa-receipt"></i>
                                        <input type="text" name="transaction_id" id="transaction_id" class="form-control" placeholder="TRX123456789">
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(isset($information->ssl_terms_active) && $information->ssl_terms_active == 1)
                        <div class="mb-3 mt-3" id="terms_checkbox_area" style="display: none;">
                            <div class="form-check d-flex align-items-start gap-2">
                                <input class="form-check-input mt-1" type="checkbox" id="agree_terms" name="agree_terms" value="1" style="width: 18px; height: 18px; cursor: pointer; accent-color: var(--brand-solid);">
                                <label class="form-check-label text-dark mb-0" for="agree_terms" style="cursor: pointer; font-size: 14px; font-weight: 600; line-height: 1.5;">
                                    আমি এই ওয়েবসাইটের 
                                    <a href="{{ route('front.privacyPolicy') ?? '#' }}" target="_blank" style="color: var(--brand-solid); text-decoration: underline;">প্রাইভেসি পলিসি</a>, 
                                    <a href="{{ url('/page/terms-condition') ?? '#' }}" target="_blank" style="color: var(--brand-solid); text-decoration: underline;">শর্তাবলী</a> এবং 
                                    <a href="{{ route('front.returnPolicy') ?? '#' }}" target="_blank" style="color: var(--brand-solid); text-decoration: underline;">রিটার্ন পলিসি</a> 
                                    পড়েছি এবং এর সাথে একমত।
                                </label>
                            </div>
                            <small class="text-danger d-none fw-bold mt-2" id="terms_error">অনলাইন পেমেন্ট করতে হলে আপনাকে শর্তাবলীতে সম্মত হতে হবে।</small>
                        </div>
                        @endif

                        @if(isset($information->coupon_visibility) && $information->coupon_visibility == 1)
                        <div class="coupon-section">
                            <label class="fw-bold mb-2 text-dark" style="font-size:14px;">কুপন কোড (যদি থাকে)</label>
                            <div class="coupon-input-group">
                                <input type="text" id="coupon_code" placeholder="Enter coupon code">
                                <button type="button" id="coupon_btn_submit" onclick="applyCouponLand()">APPLY</button>
                            </div>
                            <small id="coupon_msg" class="d-block mt-2 fw-bold"></small>
                        </div>
                        @endif

                        <table class="calculation-table">
                            <tr>
                                <td>সাবটোটাল</td>
                                <td class="text-end">৳ <span id="display_subtotal">{{ $defaultPrice }}</span></td>
                            </tr>
                            <tr>
                                <td>ডেলিভারি চার্জ</td>
                                <td class="text-end" id="calc_shipping_text">৳ <span id="display_delivery">0</span></td>
                            </tr>
                            <tr id="discount_row" style="display: none;">
                                <td class="text-success fw-bold">ডিসকাউন্ট</td>
                                <td class="text-end text-success fw-bold">- ৳ <span id="discount_display">0</span></td>
                            </tr>
                            <tr class="calc-total">
                                <td class="pt-3">সর্বমোট বিল</td>
                                <td class="text-end pt-3">৳ <span id="display_total">{{ $defaultPrice }}</span></td>
                            </tr>
                        </table>

                        {{-- ✅✅✅ ALERT BOX PLACED EXACTLY ABOVE SUBMIT BUTTON ✅✅✅ --}}
                        <div class="premium-notice-box mt-3 mb-3">
                            <div class="notice-icon">
                                <i class="fas fa-bell fa-shake"></i>
                            </div>
                            <div class="notice-text">
                                {!! function_exists('BanglaText') ? BanglaText('alert') : 'সতর্কতা: সঠিক তথ্য দিয়ে ফর্মটি পূরণ করুন।' !!}
                            </div>
                        </div>

                        <button type="submit" id="submit_btn" class="btn-submit">
                            {{ $ln_pg->btn_text_form ?? 'অর্ডার কনফার্ম করুন' }} <i class="fas fa-arrow-right ms-1"></i>
                        </button>

                    </form>
                </div>
                
            </div>
        </div>
    </div>
    
    @if(!empty($ln_pg->faq_title) || !empty($ln_pg->faq_1_q))
    <div class="row">
        <div class="col-lg-8">
            <div class="faq-section">
                <div class="section-title">{{ $ln_pg->faq_title ?? 'সচরাচর জিজ্ঞাসা (FAQ)' }}</div>
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
        </div>
    </div>
    @endif

</div>

<div style="background: #fff; padding-top: 30px; border-top: 1px solid #e5e7eb; margin-top: 40px; clear: both;">
    @include('frontend.partials.footer')
</div>

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

@if(!empty($waNumberClean))
    <a href="https://wa.me/{{ $waNumberClean }}" target="_blank" class="whats_btn" aria-label="WhatsApp">
        <img src="https://img.icons8.com/windows/96/ffffff/whatsapp--v1.png" alt="whatsapp">
    </a>
@endif

@if(isset($information->bkash_active) && $information->bkash_active == 1)
    <button id="bKash_button" style="display: none;"></button>
    @php
        $bkashScriptUrl = (isset($information->bkash_sandbox) && $information->bkash_sandbox == 1) 
            ? 'https://scripts.sandbox.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout-sandbox.js' 
            : 'https://scripts.pay.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout.js';
    @endphp
    <script src="{{ $bkashScriptUrl }}"></script>
@endif

<script>
    var paymentID;
    var dynamicOrderId;
    var successUrl = '';
    var isFreeShipping = {{ $isFreeShipping }};
    var isWeightBased = {{ $isWeightBased ? 'true' : 'false' }};
    var isTermsEnabled = {{ (isset($information->ssl_terms_active) && $information->ssl_terms_active == 1) ? 'true' : 'false' }};
    var otpSystemEnabled = {{ $information->otp_system ?? 0 }};
    
    var current_discount_val = 0;
    var current_discount_type = "fixed";
    var baseUnitPrice = {{ $defaultPrice }};
    
    // ✅ Identify if Packages exist
    var hasPackages = {{ ($ln_pg->packages && $ln_pg->packages->count() > 0) ? 'true' : 'false' }};

    function scrollToForm() {
        document.getElementById('orderForm').scrollIntoView({ behavior: 'smooth' });
    }

    // ✅✅✅ 60 MINUTE BOTTOM TIMER LOGIC ✅✅✅
    function startBottomTimer() {
        var timerKey = 'lp_timer_' + '{{ $productId }}';
        var endTime = localStorage.getItem(timerKey);
        var now = new Date().getTime();

        if (!endTime || now > endTime) {
            endTime = now + 60 * 60 * 1000; // 60 minutes from now
            localStorage.setItem(timerKey, endTime);
        }

        var x = setInterval(function() {
            var currentTime = new Date().getTime();
            var distance = endTime - currentTime;

            if (distance < 0) {
                clearInterval(x);
                document.getElementById("bottom_timer").innerHTML = "00:00";
                return;
            }

            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

            document.getElementById("bottom_timer").innerHTML = minutes + ":" + seconds;
        }, 1000);
    }
    startBottomTimer();

    $(document).ready(function() {
        toastr.options = { "closeButton": true, "progressBar": true, "positionClass": "toast-top-right", "timeOut": "3000" };

        var isOtpVerified = false;
        var otpTimerInterval;
        var isSendingOtp = false;

        // ✅ Updated Package Price function
        function updatePackagePrice() {
            let $activePkg = $('.package-input:checked');
            if ($activePkg.length > 0 && $activePkg.val() !== 'default') {
                let pkgPrice = parseFloat($activePkg.data('price'));
                let pkgQty = parseInt($activePkg.data('qty'));
                
                baseUnitPrice = pkgPrice;
                $('#form_qty').val(1);
                $('.inner_qty').val(pkgQty);
            } else {
                let $sel = $('#variation_select');
                let price = {{ $defaultPrice }};
                if($sel.is('select')) {
                    let $opt = $sel.find('option:selected');
                    if($opt.length && $opt.val() !== "") price = parseFloat($opt.data('price')) || price;
                } else if ($sel.is('input[type="hidden"]')) {
                    price = parseFloat($sel.attr('data-price')) || price;
                }
                baseUnitPrice = price;
            }
            calculateTotal();
        }

        // ✅ Quantity Buttons Lock Logic for Packages
        $(document).on('click', '.increase-qty', function(e) {
            e.preventDefault();
            if(hasPackages) {
                toastr.warning('প্যাকেজ থেকে নির্বাচন করুন, পরিমাণ পরিবর্তন করা যাবে না।');
                return;
            }
            var q = parseInt($('.inner_qty').val() || 1);
            var maxStock = parseInt($('#max_stock').val()) || 0;
            if(q >= maxStock && maxStock > 0) {
                toastr.error('সর্বোচ্চ ' + maxStock + ' টি অর্ডার করতে পারবেন');
                return;
            }
            $('.inner_qty').val(q + 1);
            $('#form_qty').val(q + 1);
            calculateTotal();
        });

        $(document).on('click', '.decrease-qty', function(e) {
            e.preventDefault();
            if(hasPackages) {
                toastr.warning('প্যাকেজ থেকে নির্বাচন করুন, পরিমাণ পরিবর্তন করা যাবে না।');
                return;
            }
            var q = parseInt($('.inner_qty').val() || 1);
            if (q > 1) {
                $('.inner_qty').val(q - 1);
                $('#form_qty').val(q - 1);
                calculateTotal();
            }
        });

        $('.package-input').on('change', function() {
            updatePackagePrice();
        });

        function handleVariationChange() {
            let price = {{ $defaultPrice }};
            let stock = {{ $defaultStock }};
            let $sel = $('#variation_select');

            if($sel.is('select')) {
                let $opt = $sel.find('option:selected');
                if($opt.length && $opt.val() !== "") {
                    price = parseFloat($opt.data('price')) || price;
                    var rawStock8 = parseInt($opt.data('stock')); stock = isNaN(rawStock8) ? stock : rawStock8;  // FIX: data-stock না থাকলে আগের stock রাখে, 0 নয়
                }
            } else if ($sel.is('input[type="hidden"]')) {
                price = parseFloat($sel.attr('data-price')) || price;
                var hs = parseInt($sel.attr('data-stock'));
                stock = isNaN(hs) ? stock : hs;
            }

            $('.package-input[value="default"]').data('price', price);
            $('.default_pkg_price_display').text(price);
            $('#max_stock').val(stock);

            let $stockDiv = $('#stock_status');
            let $btn = $('#submit_btn');

            if(stock > 0) {
                $stockDiv.text('In Stock: ' + stock).removeClass('out-stock').addClass('in-stock');
                $btn.prop('disabled', false).html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-1"></i>');
            } else {
                $stockDiv.text('Out of Stock').removeClass('in-stock').addClass('out-stock');
                $btn.prop('disabled', true).html('Out of Stock');
            }
            updatePackagePrice();
        }

        $('#variation_select').on('change', function() {
            handleVariationChange();
        });
        handleVariationChange();

        function calculateTotal() {
            let qty = parseInt($('#form_qty').val()) || 1;
            let subtotal = baseUnitPrice * qty;
            let selectedDelivery = $('#delivery_charge').find(':selected');
            let cid = selectedDelivery.val();

            $('#subtotal_input').val(subtotal);
            $('#display_subtotal').text(subtotal);

            var discount = 0;
            if(parseFloat(current_discount_val) > 0) {
                if (current_discount_type === 'percentage' || current_discount_type === 'percent') {
                    discount = (subtotal * parseFloat(current_discount_val)) / 100;
                } else {
                    discount = parseFloat(current_discount_val);
                }
            }

            if(discount > 0) {
                $('#discount_row').show();
                $('#discount_display').text(Math.round(discount));
            } else {
                $('#discount_row').hide();
            }

            function setFinalCalc(deliveryCharge) {
                $('#calc_shipping_text').html('৳ <span id="display_delivery">' + Math.round(deliveryCharge) + '</span>');
                let total = (subtotal + deliveryCharge) - discount;
                if(total < 0) total = 0;
                $('#display_total').text(Math.round(total));
                $('#final_total_input').val(Math.round(total));
            }

            if(isFreeShipping == 1) {
                $('#calc_shipping_text').html('<span class="text-success fw-bold">ফ্রি ডেলিভারি</span>');
                let total = subtotal - discount;
                if(total < 0) total = 0;
                $('#display_total').text(Math.round(total));
                $('#final_total_input').val(Math.round(total));
            } else if(isWeightBased && cid) {
                $.ajax({
                    url: "{{ route('front.getDeliveryChargeAjax') }}",
                    type: "POST",
                    data: { delivery_charge_id: cid, product_id: "{{ $productId }}", quantity: qty, _token: "{{ csrf_token() }}" },
                    success: function(res) {
                        if(res.success) {
                            setFinalCalc(parseFloat(res.charge));
                        }
                    }
                });
            } else {
                let delivery = parseFloat(selectedDelivery.data('charge')) || 0;
                setFinalCalc(delivery);
            }
        }

        $('#delivery_charge').on('change', calculateTotal);

        var autoSaveTimer;
        function saveIncompleteData() {
            var mobile = $('#mobile').val();
            if (mobile && mobile.length >= 11) {
                $.ajax({
                    url: "{{ route('incompleteStore') }}", 
                    type: "POST",
                    data: {
                        mobile: mobile,
                        name: $('#name').val(),
                        address: $('#address').val(),
                        prd_id: "{{ $productId }}",
                        variation_id: $('#variation_select').val() || '',
                        quantity: $('#form_qty').val(),
                        amount: $('#subtotal_input').val(),
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) { }
                });
            }
        }
        $(document).on('keyup', '#mobile', function() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(saveIncompleteData, 2000); 
        });
        $(document).on('blur', '#mobile, #name, #address', saveIncompleteData);
    });

    window.togglePaymentAction = function(method, name = '', number = '', type = '') {
        var form = document.getElementById('checkout_land_form');
        var btn = $('#submit_btn');
        var termsArea = $('#terms_checkbox_area'); 
        var manualArea = $('#manual_payment_area');
        var sNum = $('#sender_number');
        var tId = $('#transaction_id');

        $('.payment-radio-box').removeClass('active');
        
        var radioVal = method === 'manual' ? name : method;
        $('input[name="payment_method"][value="'+radioVal+'"]').parent('.payment-radio-box').addClass('active');

        if(method === 'sslcommerz' || method === 'bkash' || method === 'eps' || method === 'nagad' || method === 'uddoktapay') {
            if (method === 'sslcommerz') {
                form.action = "{{ url('/pay') }}"; 
                btn.html('Pay Now (Card/SSL) <i class="fas fa-arrow-right ms-1"></i>');
            } else {
                form.action = "{{ route('front.storelandData') }}";
                btn.html('Pay with ' + method.toUpperCase() + ' <i class="fas fa-arrow-right ms-1"></i>');
            }
            if(isTermsEnabled) termsArea.slideDown(); 
            manualArea.slideUp();
            sNum.removeAttr('required');
            tId.removeAttr('required');
        } else if(method === 'manual') {
            form.action = "{{ route('front.storelandData') }}";
            btn.html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-1"></i>');
            if(isTermsEnabled) termsArea.slideUp(); 
            
            $('#payment_instruction').html(`দয়া করে আপনার টোটাল বিল <b>${number} (${type})</b> নাম্বারে Send Money করুন। এরপর নিচের তথ্যগুলো দিন।`);
            manualArea.slideDown();
            sNum.attr('required', 'required');
            tId.attr('required', 'required');
        } else {
            form.action = "{{ route('front.storelandData') }}";
            btn.html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-1"></i>');
            if(isTermsEnabled) termsArea.slideUp(); 
            manualArea.slideUp();
            sNum.removeAttr('required');
            tId.removeAttr('required');
        }
    };

    function applyCouponLand() {
        var code = $('#coupon_code').val();
        if(!code) { toastr.error('কুপন কোড লিখুন'); return; }

        var current_total = parseFloat($('#subtotal_input').val()) || 0;
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
                    
                    $('#hidden_coupon_code').val(code);
                    $('#hidden_discount').val(res.amount);
                    
                    $('#delivery_charge').trigger('change');
                } else {
                    $('#coupon_msg').text(res.msg).css('color', 'red');
                    toastr.error(res.msg);
                    $btn.prop('disabled', false).text('APPLY');

                    current_discount_val = 0;
                    $('#hidden_coupon_code').val('');
                    $('#hidden_discount').val('0');
                    $('#delivery_charge').trigger('change');
                }
            },
            error: function() {
                toastr.error('Error applying coupon');
                $btn.prop('disabled', false).text('APPLY');
            }
        });
    }

    function startTimer(duration, display) {
        var timer = duration, seconds;
        clearInterval(otpTimerInterval);
        $('#resendOtpBtn').prop('disabled', true).addClass('text-muted').removeClass('text-primary');
        otpTimerInterval = setInterval(function () {
            seconds = parseInt(timer % 60, 10);
            seconds = seconds < 10 ? "0" + seconds : seconds;
            display.html("Wait (" + seconds + "s)");
            if (--timer < 0) {
                clearInterval(otpTimerInterval);
                display.html("Resend Code");
                $('#resendOtpBtn').prop('disabled', false).removeClass('text-muted').addClass('text-primary');
            }
        }, 1000);
    }

    window.sendOtpBeforeSubmit = function(isResend = false) {
        if(isSendingOtp) return;
        var mobile = $('#mobile').val();
        if(mobile.length !== 11) { toastr.error('সঠিক মোবাইল নাম্বার দিন'); return; }
          
        isSendingOtp = true;
        $('#submit_btn').addClass('is-disabled').text('Sending OTP...');
          
        $.ajax({
            url: "{{ route('sendOtp') }}", type: "POST", data: { mobile: mobile, _token: "{{ csrf_token() }}" },
            success: function(res) {
                isSendingOtp = false;
                $('#submit_btn').removeClass('is-disabled').html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-1"></i>');
                if(res.success) {
                    $('#otp_sent_number').text(mobile);
                    var myModal = new bootstrap.Modal(document.getElementById('otpModal'));
                    myModal.show();
                    setTimeout(function() { $('#otp_input').focus(); }, 500);
                    startTimer(30, $('#resendOtpBtn'));
                } else { toastr.error(res.msg); }
            },
            error: function() { 
                isSendingOtp = false; 
                $('#submit_btn').removeClass('is-disabled').html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-1"></i>');
            }
        });
    }

    window.verifyOtpNow = function() {
        var code = $('#otp_input').val();
        var mobile = $('#mobile').val();
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
    }

    $('#checkout_land_form').submit(function(e) {
        e.preventDefault();
        
        let paymentMethod = $('input[name="payment_method"]:checked').val() || 'cod'; 

        if((paymentMethod === 'online' || paymentMethod === 'sslcommerz' || paymentMethod === 'bkash' || paymentMethod === 'eps' || paymentMethod === 'nagad' || paymentMethod === 'uddoktapay') && isTermsEnabled) {
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
        
        let selectedPkgId = $('.package-input:checked').val() !== 'default' ? $('.package-input:checked').val() : '';
        $('#selected_package_id').val(selectedPkgId);

        var maxStock = parseInt($('#max_stock').val()) || 0;
        if(maxStock <= 0) {
            toastr.error('দুঃখিত, প্রোডাক্টটি স্টকে নেই!');
            return;
        }

        if (paymentMethod === 'sslcommerz' || paymentMethod === 'online') {
            if(otpSystemEnabled == 1 && !isOtpVerified) {
                sendOtpBeforeSubmit();
            } else {
                this.action = "{{ url('/pay') }}";
                this.submit();
            }
            return;
        }

        if(otpSystemEnabled == 1 && !isOtpVerified) {
            sendOtpBeforeSubmit();
        } else {
            submitOrderFinal();
        }
    });

    function submitOrderFinal() {
        let $form = $('#checkout_land_form');
        let paymentMethod = $('input[name="payment_method"]:checked').val() || 'cod'; 
        
        var purchaseEventId = "PUR_{{ $productId }}_" + Date.now();
        $('#purchase_event_id').val(purchaseEventId);

        $('#submit_btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> প্রসেসিং...');

        $.ajax({
            url: "{{ route('front.storelandData') }}", 
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
                    $('#submit_btn').removeClass('is-disabled').removeAttr('disabled').html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-1"></i>'); 
                }
            },
            error: function () { 
                $('#submit_btn').removeClass('is-disabled').removeAttr('disabled').html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-1"></i>'); 
                toastr.error('সার্ভারে সমস্যা হচ্ছে।'); 
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
                            $('#submit_btn').removeClass('is-disabled').removeAttr('disabled').html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-1"></i>');
                        }
                    },
                    error: function (err) {
                        bKash.create().onError();
                        toastr.error("Server error while connecting to bKash.");
                        $('#submit_btn').removeClass('is-disabled').removeAttr('disabled').html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-1"></i>');
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
                            $('#submit_btn').removeClass('is-disabled').removeAttr('disabled').html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-1"></i>');
                        }
                    },
                    error: function () {
                        bKash.execute().onError();
                        toastr.error("Failed to execute bKash payment.");
                        $('#submit_btn').removeClass('is-disabled').removeAttr('disabled').html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-1"></i>');
                    }
                });
            },
            onClose: function () {
                window.location.href = successUrl;
            }
        });
    }
    @endif
    
    $(document).ready(function(){
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