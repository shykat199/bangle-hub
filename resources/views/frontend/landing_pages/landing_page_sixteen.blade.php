<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $ln_pg->title1 ?: ($product->name ?? "") }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    @php
        $information = \App\Models\Information::first();
        $currentProduct = $product ?? $ln_pg->product ?? null;
        $variations = collect();

        $globalSetting = DB::table('delivery_charges')->first();
        $isWeightBased = $globalSetting && $globalSetting->charge_type == 'weight_based';
        $isFreeShipping = (!empty($currentProduct->is_free_shipping) && $currentProduct->is_free_shipping == 1) ? 1 : 0;
        $pixelId   = setting('fb_pixel_id') ?? null;
        $ttPixelId = setting('tt_pixel_id') ?? null;

        $sizes = collect();
        $colors = collect();
        $varMatrix = [];

        // 🔒 Single product → never show size/color UI, even if legacy variation rows
        // carry hardcoded size_id=3 / color_id=1 from older code.
        $isSingle = $currentProduct && strtolower((string)($currentProduct->type ?? '')) === 'single';

        if($currentProduct) {
            try {
                $currentProduct->loadMissing(['variations.size','variations.color', 'variations.stocks', 'category']);
                $variations = $currentProduct->variations ?? collect();
                if($variations->count() > 0) {
                    foreach($variations as $v) {
                        if(!$isSingle) {
                            if($v->size && isset($v->size->id)) $sizes->push($v->size);
                            if($v->color && isset($v->color->id)) $colors->push($v->color);
                        }
                        $sId = $isSingle ? 0 : ($v->size_id ?? 0);
                        $cId = $isSingle ? 0 : ($v->color_id ?? 0);
                        $vPrice = ((float)($v->after_discount_price ?? 0) > 0) ? $v->after_discount_price : ($v->price ?? $currentProduct->sell_price ?? 0);
                        $sumStock = (int)($v->stocks ? $v->stocks->sum('quantity') : 0);
                        $finalStock = $sumStock > 0 ? $sumStock : (int)($currentProduct->stock_quantity ?? 0);
                        if($sId > 0 || $cId > 0) {
                            $varMatrix["{$sId}_{$cId}"] = ['id' => $v->id, 'price' => $vPrice, 'stock' => $finalStock];
                        }
                    }
                }
                $sizes = $sizes->unique('id')->values();
                $colors = $colors->unique('id')->values();
            } catch(\Throwable $e) {}
        }

        // ?? only falls back on NULL, so an offer-price field the admin
        // cleared came through as '' — number_format() then threw a
        // TypeError and the whole landing page 500'd, taking every
        // pixel event with it. Cast to float and fall back on 0 too.
        $defaultPrice = (float) ($ln_pg->new_price ?: ($currentProduct->after_discount ?: ($currentProduct->sell_price ?: 1290)));
        $oldPrice     = (float) ($ln_pg->old_price ?: 1990);
        $savedAmount  = max(0, $oldPrice - $defaultPrice);
        $defaultStock = $currentProduct->stock_quantity ?? 99;
        $productId    = $currentProduct->id ?? 0;
        $productName  = $currentProduct->name ?? ($ln_pg->title1);
        $contentCategory = $currentProduct->category->name ?? '';

        $sliderImages = $ln_pg->images ?? collect();
        $mainImg = !empty($ln_pg->right_product_image) ? asset('landing_pages/'.$ln_pg->right_product_image) : ($currentProduct && $currentProduct->image ? (function_exists('getImage') ? getImage('products', $currentProduct->image) : asset('products/'.$currentProduct->image)) : '');
        $specImg = !empty($ln_pg->landing_bg) ? asset('landing_pages/'.$ln_pg->landing_bg) : $mainImg;

        // Process video embed
        $videoEmbed = '';
        $rawVideo = $ln_pg->video_url ?? '';
        if (!empty($rawVideo)) {
            if (stripos($rawVideo, '<iframe') !== false) {
                $videoEmbed = $rawVideo;
            } elseif (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/', $rawVideo, $m)) {
                $videoEmbed = '<iframe src="https://www.youtube.com/embed/'.$m[1].'?rel=0&modestbranding=1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
            } else {
                $videoEmbed = '<iframe src="'.$rawVideo.'" frameborder="0" allowfullscreen></iframe>';
            }
        }

        $brandName = $ln_pg->title2;
        $brandParts = explode('/', $brandName);
        $brandMain = trim($brandParts[0] ?? '');
        $brandSub = trim($brandParts[1] ?? '');

    @endphp

    {{-- TikTok --}}
    @if(!empty($ttPixelId))
    <script>
    !function (w, d, t) { w.TiktokAnalyticsObject = t; var ttq = w[t] = w[t] || []; ttq.methods = ["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"]; ttq.setAndDefer = function (t, e) { t[e] = function () { t.push([e].concat(Array.prototype.slice.call(arguments, 0))); }; }; for (var i = 0; i < ttq.methods.length; i++) ttq.setAndDefer(ttq, ttq.methods[i]); ttq.instance = function (t) { for (var e = ttq._i[t] || [], n = 0; n < ttq.methods.length; n++) ttq.setAndDefer(e, ttq.methods[n]); return e; }; ttq.load = function (e, n) { var r = "https://analytics.tiktok.com/i18n/pixel/events.js"; var o = n && n.partner; ttq._i = ttq._i || {}; ttq._i[e] = []; ttq._i[e]._u = r; ttq._t = ttq._t || {}; ttq._t[e] = +new Date; ttq._o = ttq._o || {}; ttq._o[e] = n || {}; var s = document.createElement("script"); s.type = "text/javascript"; s.async = !0; s.src = r + "?sdkid=" + e + "&lib=" + t; var x = document.getElementsByTagName("script")[0]; x.parentNode.insertBefore(s, x); }; ttq.load('{{ $ttPixelId }}'); @if(!$__ttFromGtm) ttq.page(); @endif }(window, document, 'ttq');
    </script>
    @endif

    {{-- ✅ GTM + Tracking Code --}}
    {!! optional($information)->tracking_code !!}

    {{-- FB Pixel --}}
    @if(!empty($pixelId))
    <script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init', '{{ $pixelId }}');@if(!$__hasGtm)fbq('track', 'PageView');@endif</script>
    <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1"/></noscript>
    @endif

    {{-- Event ids + GA4 dataLayer + browser pixel events.
         NOT nested inside the FB @if — clearing the Facebook Pixel ID must never kill GTM/TikTok. --}}
    <script>
    (function(){
        if (window.__LP_FIRED__) { return; }
        window.__LP_FIRED__ = true;

        window.LP_EVENT_BASE  = {!! json_encode($lpEventBase ?? null) !!} || ('LP16_{{ $productId }}_' + Date.now());
        window.LP_EVENT_ID_VC = {!! json_encode($lpEventIdVC ?? null) !!} || (window.LP_EVENT_BASE + '_VC');
        window.LP_EVENT_ID_IC = {!! json_encode($lpEventIdIC ?? null) !!} || (window.LP_EVENT_BASE + '_IC');

        // GA4 ecommerce — never gated: dataLayer is how GTM receives data.
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({ ecommerce: null });
        window.dataLayer.push({
            event: 'view_item',
            event_id: window.LP_EVENT_ID_VC,
            ecommerce: { currency: 'BDT', value: {{ $defaultPrice }}, items: [{ item_id: '{{ $productId }}', item_name: @json($productName), item_category: @json($contentCategory), price: {{ $defaultPrice }}, quantity: 1 }] }
        });
        window.dataLayer.push({ ecommerce: null });
        window.dataLayer.push({
            event: 'begin_checkout',
            event_id: window.LP_EVENT_ID_IC,
            ecommerce: { currency: 'BDT', value: {{ $defaultPrice }}, items: [{ item_id: '{{ $productId }}', item_name: @json($productName), item_category: @json($contentCategory), price: {{ $defaultPrice }}, quantity: 1 }] }
        });

        window.addEventListener('load', function(){
            if(@json(!$__hasGtm) && typeof fbq === 'function') {
                fbq('track', 'ViewContent', { content_ids: ['{{ $productId }}'], content_name: @json($productName), content_type: 'product', content_category: @json($contentCategory), value: {{ $defaultPrice }}, currency: 'BDT' }, {eventID: window.LP_EVENT_ID_VC});
                fbq('track', 'InitiateCheckout', { content_ids: ['{{ $productId }}'], content_name: @json($productName), content_type: 'product', value: {{ $defaultPrice }}, currency: 'BDT', num_items: 1 }, {eventID: window.LP_EVENT_ID_IC});
            }
            if (@json(!$__ttFromGtm) && typeof ttq !== 'undefined' && ttq.track) {
                ttq.track('ViewContent', { content_type: 'product', value: {{ $defaultPrice }}, currency: 'BDT', contents: [{ content_id: '{{ $productId }}', content_type: 'product', content_name: @json($productName), price: {{ $defaultPrice }}, quantity: 1 }] }, { event_id: window.LP_EVENT_ID_VC });
                ttq.track('InitiateCheckout', { content_type: 'product', value: {{ $defaultPrice }}, currency: 'BDT', contents: [{ content_id: '{{ $productId }}', content_type: 'product', content_name: @json($productName), price: {{ $defaultPrice }}, quantity: 1 }] }, { event_id: window.LP_EVENT_ID_IC });
            }
        });

        var timeSteps = [10, 30, 60, 120];
        timeSteps.forEach(function(seconds) {
            setTimeout(function() {
                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({ 'event': 'time_on_page', 'time_spent': seconds, 'page_path': window.location.pathname });
                if (@json(!$__hasGtm) && typeof fbq === 'function') { fbq('trackCustom', 'TimeSpent', { time_in_seconds: seconds }); }
            }, seconds * 1000);
        });
        var scrollSteps = [25, 50, 75, 90, 100]; var scrolled = [];
        window.addEventListener('scroll', function() {
            var s = window.scrollY, d = document.documentElement.scrollHeight, c = window.innerHeight;
            var scrollPercent = (s / (d - c)) * 100;
            scrollSteps.forEach(function(step) {
                if (scrollPercent >= step && !scrolled.includes(step)) {
                    scrolled.push(step);
                    window.dataLayer = window.dataLayer || [];
                    window.dataLayer.push({ 'event': 'scroll_depth', 'scroll_percent': step, 'page_path': window.location.pathname });
                    if (@json(!$__hasGtm) && typeof fbq === 'function') { fbq('trackCustom', 'ScrollDepth', { scroll_percent: step }); }
                }
            });
        });
    })();
    </script>

    <style>
        :root {
            --cream: #f7f1de;
            --cream-2: #f1e9cc;
            --pri: #14532d;
            --pri-dark: #0f3d1f;
            --pri-darker: #082818;
            --pri-soft: #e8efd9;
            --gold: #d97706;
            --text: #1a2e15;
            --text-muted: #65725a;
            --border: rgba(20, 83, 45, 0.15);
            --card-bg: #fefbf3;
        }

        * { box-sizing: border-box; }
        html, body { overflow-x: hidden; scroll-behavior: smooth; }
        body {
            font-family: 'Hind Siliguri', sans-serif;
            background: var(--cream);
            color: var(--text);
            margin: 0; padding: 0;
            line-height: 1.65;
            padding-bottom: 64px; /* sticky bar space */
        }
        a { text-decoration: none; }
        .container-x { max-width: 1140px; margin: 0 auto; padding: 0 20px; }

        /* ===== TOP STRIPE ===== */
        .top-stripe {
            background: var(--pri-dark);
            color: var(--cream);
            padding: 9px 0;
            font-size: 12.5px;
            font-weight: 600;
            text-align: center;
        }
        .top-stripe i { color: var(--cream-2); margin-right: 5px; }
        .top-stripe span { background: rgba(247,241,222,0.15); color: var(--cream); padding: 2px 8px; border-radius: 4px; font-weight: 700; letter-spacing: 1px; margin-left: 5px; }

        /* ===== HERO ===== */
        .hero-section { padding: 36px 0 40px; text-align: center; }
        .hero-title-main {
            font-size: clamp(28px, 4.5vw, 44px);
            font-weight: 800;
            color: var(--pri-darker);
            margin: 0 0 18px;
            line-height: 1.2;
        }
        .video-wrapper {
            position: relative;
            max-width: 700px; margin: 0 auto 22px;
            aspect-ratio: 16/9;
            border-radius: 16px; overflow: hidden;
            background: #000;
            box-shadow: 0 18px 40px -20px rgba(0,0,0,0.4);
        }
        .video-wrapper iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; }
        .video-placeholder { display: flex; align-items: center; justify-content: center; height: 100%; background: linear-gradient(135deg, #1a3b1f, #082818); color: var(--cream); }
        .video-placeholder i { font-size: 50px; opacity: 0.5; }

        .hero-price-box {
            display: inline-flex; align-items: center; gap: 12px;
            margin: 16px 0 22px;
        }
        .hero-price-new {
            font-size: 38px; font-weight: 800;
            color: var(--pri-darker);
            line-height: 1;
        }
        .hero-saved-badge {
            background: #fef3c7; color: var(--gold);
            padding: 5px 10px; border-radius: 50px;
            font-size: 11px; font-weight: 700;
        }
        .hero-cta-btn {
            background: var(--pri-dark); color: var(--cream) !important;
            padding: 14px 36px; border-radius: 12px;
            font-weight: 700; font-size: 15px;
            display: inline-flex; align-items: center; gap: 8px;
            transition: .2s;
            min-width: 280px; justify-content: center;
        }
        .hero-cta-btn:hover { background: var(--pri); transform: translateY(-2px); box-shadow: 0 8px 20px rgba(20,83,45,0.3); }

        /* ===== SECTION HEAD ===== */
        section.cream-sec { padding: 50px 0; background: var(--cream); }
        section.cream-sec.alt { background: var(--cream-2); }
        .sec-head { text-align: center; margin-bottom: 30px; }
        .sec-title {
            font-size: clamp(22px, 3.5vw, 32px); font-weight: 800;
            color: var(--pri-darker); margin: 0 0 6px;
        }
        .sec-sub { color: var(--text-muted); font-size: 13px; margin: 0; }

        /* ===== 4 FEATURES ===== */
        .features-2x2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            max-width: 720px; margin: 0 auto;
        }
        .feat-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            display: flex; align-items: center; gap: 14px;
        }
        .feat-icon {
            width: 42px; height: 42px;
            background: var(--pri-soft);
            color: var(--pri-dark);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .feat-card h5 { color: var(--pri-darker); font-size: 14px; font-weight: 700; margin: 0 0 3px; }
        .feat-card p { color: var(--text-muted); font-size: 11.5px; margin: 0; line-height: 1.5; }

        /* ===== GALLERY ===== */
        .gallery-x {
            display: grid;
            grid-template-columns: 1fr 100px;
            gap: 14px;
            max-width: 760px; margin: 0 auto;
        }
        .gallery-main-img {
            background: var(--pri-darker);
            border-radius: 14px; overflow: hidden;
            aspect-ratio: 1/1;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid var(--border);
        }
        .gallery-main-img img { width: 100%; height: 100%; object-fit: cover; }
        .gallery-main-img .ph { color: var(--cream); font-size: 80px; opacity: 0.3; }
        .gallery-thumbs-x { display: flex; flex-direction: column; gap: 10px; }
        .gallery-thumb-x {
            background: var(--card-bg);
            border: 2px solid var(--border);
            border-radius: 10px; overflow: hidden;
            aspect-ratio: 1/1;
            cursor: pointer; transition: .2s;
            display: flex; align-items: center; justify-content: center;
        }
        .gallery-thumb-x img { width: 100%; height: 100%; object-fit: cover; }
        .gallery-thumb-x.active, .gallery-thumb-x:hover { border-color: var(--pri); }

        .gallery-badge {
            background: var(--pri-soft); color: var(--pri-dark);
            font-size: 10px; font-weight: 700;
            padding: 3px 10px; border-radius: 50px;
            display: inline-block;
            margin-bottom: 8px;
        }

        /* ===== SPEC ===== */
        .spec-grid-x {
            display: grid;
            grid-template-columns: 1fr 1.3fr;
            gap: 24px;
            max-width: 880px; margin: 0 auto;
        }
        .spec-img-x {
            background: var(--cream-2);
            border-radius: 14px;
            border: 1px solid var(--border);
            aspect-ratio: 1/1;
            overflow: hidden;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .spec-img-x img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .spec-table-x {
            background: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border);
            overflow: hidden;
        }
        .spec-table-x .row-s {
            display: grid;
            grid-template-columns: 150px 1fr;
            padding: 12px 18px;
            border-top: 1px solid var(--border);
            font-size: 13.5px;
        }
        .spec-table-x .row-s:first-child { border-top: none; }
        .spec-table-x .lbl { color: var(--text-muted); font-weight: 500; }
        .spec-table-x .val { color: var(--pri-darker); font-weight: 600; }

        /* ===== REVIEWS ===== */
        .reviews-grid-x {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            max-width: 980px; margin: 0 auto;
        }
        .review-x-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px;
        }
        .review-x-card .stars { color: var(--gold); font-size: 13px; margin-bottom: 8px; letter-spacing: 2px; }
        .review-x-card .stars small { color: var(--text-muted); margin-left: 8px; font-size: 11px; }
        .review-x-card p { font-size: 12.5px; color: var(--text); line-height: 1.7; margin: 0 0 12px; }
        .review-x-card .reviewer { display: flex; align-items: center; gap: 8px; padding-top: 10px; border-top: 1px solid var(--border); font-size: 12px; color: var(--pri-darker); font-weight: 700; }
        .review-x-card .reviewer i { color: var(--pri); font-size: 14px; }

        /* ===== SIZE GUIDE ===== */
        .size-table {
            background: var(--card-bg);
            border-radius: 12px; overflow: hidden;
            border: 1px solid var(--border);
            max-width: 720px; margin: 0 auto;
        }
        .size-table table { width: 100%; border-collapse: collapse; }
        .size-table thead {
            background: var(--pri-dark);
            color: var(--cream);
        }
        .size-table th, .size-table td {
            padding: 12px 16px;
            font-size: 13px;
            text-align: center;
        }
        .size-table th { font-weight: 600; font-size: 12px; letter-spacing: 1px; }
        .size-table tbody tr { border-top: 1px solid var(--border); }
        .size-table tbody tr:first-child { border-top: none; }
        .size-table tbody td:first-child { font-weight: 700; color: var(--pri-darker); }

        /* ===== TRUST BADGES ===== */
        .trust-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            max-width: 920px; margin: 0 auto;
        }
        .trust-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px;
            text-align: left;
            display: flex; align-items: center; gap: 12px;
        }
        .trust-icon {
            width: 38px; height: 38px;
            background: var(--pri-soft);
            color: var(--pri-dark);
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }
        .trust-card h6 { color: var(--pri-darker); font-weight: 700; font-size: 12.5px; margin: 0 0 2px; }
        .trust-card p { color: var(--text-muted); font-size: 11px; margin: 0; }

        /* ===== ORDER FORM ===== */
        .order-card-x {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 30px;
            max-width: 640px; margin: 0 auto;
            box-shadow: 0 10px 30px -15px rgba(20,83,45,0.2);
        }
        .order-card-x .form-label { font-size: 12px; font-weight: 600; color: var(--text); margin-bottom: 5px; }
        .order-card-x .form-control,
        .order-card-x .form-select {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 11px 14px;
            font-size: 14px;
            background: #fff;
            font-family: inherit;
        }
        .order-card-x .form-control:focus,
        .order-card-x .form-select:focus {
            border-color: var(--pri);
            box-shadow: 0 0 0 3px rgba(20,83,45,0.08);
            outline: none;
        }
        .size-pick { display: flex; gap: 6px; flex-wrap: wrap; }
        .size-pick .sz-btn {
            min-width: 48px; height: 38px;
            background: #fff; border: 1.5px solid var(--border); border-radius: 8px;
            font-weight: 700; font-size: 13px; color: var(--text);
            cursor: pointer; transition: .2s;
        }
        .size-pick .sz-btn.active { background: var(--pri-dark); color: var(--cream); border-color: var(--pri-dark); }

        .qty-x { display: inline-flex; align-items: center; border: 1px solid var(--border); border-radius: 10px; overflow: hidden; background: #fff; }
        .qty-x button { background: var(--cream-2); border: none; width: 38px; height: 38px; font-size: 16px; font-weight: 700; cursor: pointer; }
        .qty-x input { width: 50px; height: 38px; border: none; text-align: center; font-weight: 700; }

        .pay-section { margin-top: 14px; }
        .pay-section .label { display: block; font-weight: 600; font-size: 12px; color: var(--text); margin-bottom: 8px; }
        .payment-box {
            border: 1.5px solid var(--border); border-radius: 10px;
            padding: 11px 13px; cursor: pointer; transition: .2s; background: #fff;
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 7px; font-size: 13px;
        }
        .payment-box i { font-size: 17px; color: var(--pri); }
        .payment-box img { height: 22px; width: 22px; object-fit: contain; }
        input[name="payment_method"]:checked + .payment-box { border-color: var(--pri-dark); background: var(--pri-soft); }

        .coupon-box { margin-top: 12px; padding: 12px; background: var(--cream-2); border: 1px dashed var(--pri); border-radius: 10px; }
        .coupon-box .form-label { color: var(--pri-darker); font-weight: 700; font-size: 12px; }
        .coupon-box .input-group { display: flex; gap: 6px; }
        .coupon-box input.form-control { flex: 1; }
        .coupon-box button { background: var(--pri-dark); color: var(--cream); border: none; padding: 0 16px; border-radius: 8px; font-weight: 700; font-size: 12px; cursor: pointer; }
        .manual-pay-area { display: none; margin-top: 10px; padding: 10px; background: #f9fafb; border-radius: 10px; }
        .manual-pay-area .alert { background: var(--pri-soft); border: 1px solid var(--border); color: var(--pri-darker); padding: 8px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; margin-bottom: 8px; }

        .order-summary-x { padding: 14px 0; border-top: 1px dashed var(--border); margin-top: 14px; }
        .order-summary-x .row-l { display: flex; justify-content: space-between; padding: 6px 0; font-size: 13px; }
        .order-summary-x .row-l.total { font-weight: 800; font-size: 18px; color: var(--pri-darker); border-top: 1px solid var(--border); padding-top: 12px; margin-top: 8px; }

        .order-submit-x {
            width: 100%; background: var(--pri-dark); color: var(--cream);
            border: none; padding: 14px; border-radius: 50px;
            font-weight: 700; font-size: 15px;
            cursor: pointer; transition: .2s;
            margin-top: 16px;
        }
        .order-submit-x:hover:not(:disabled) { background: var(--pri); transform: translateY(-2px); }
        .order-submit-x:disabled { background: #9ca3af; cursor: not-allowed; }

        /* ===== FAQ ===== */
        .faq-list-x { max-width: 720px; margin: 0 auto; }
        .faq-item-x {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: .2s;
        }
        .faq-item-x:hover { border-color: var(--pri); }
        .faq-item-x .faq-q { display: flex; justify-content: space-between; align-items: center; color: var(--pri-darker); font-weight: 600; font-size: 13.5px; }
        .faq-item-x .faq-q i { color: var(--pri); transition: transform .25s; }
        .faq-item-x .faq-a { display: none; padding-top: 10px; margin-top: 10px; border-top: 1px solid var(--border); color: var(--text-muted); font-size: 12.5px; line-height: 1.7; }
        .faq-item-x.open .faq-a { display: block; }
        .faq-item-x.open .faq-q i { transform: rotate(45deg); }

        /* ===== FOOTER ===== */
        .site-footer { background: #fff; padding: 28px 0; border-top: 1px solid var(--border); text-align: center; }
        .site-footer p { font-size: 12px; color: var(--text-muted); margin: 4px 0; }
        .site-footer .footer-brand { font-weight: 800; color: var(--pri-darker); font-size: 14px; }

        /* ===== STICKY BOTTOM BAR ===== */
        .sticky-bar {
            position: fixed; bottom: 0; left: 0; right: 0;
            background: var(--pri-dark);
            padding: 12px 20px;
            display: flex; align-items: center; justify-content: space-between;
            gap: 12px;
            z-index: 9998;
            box-shadow: 0 -4px 20px -5px rgba(0,0,0,0.3);
        }
        .sticky-bar .price-stack { color: var(--cream); }
        .sticky-bar .price-stack .p-now { font-weight: 800; font-size: 18px; }
        .sticky-bar .price-stack .p-old { text-decoration: line-through; opacity: 0.65; font-size: 12px; margin-left: 6px; }
        .sticky-bar a {
            background: var(--cream); color: var(--pri-dark) !important;
            padding: 10px 22px; border-radius: 50px;
            font-weight: 800; font-size: 13px;
            display: inline-flex; align-items: center; gap: 6px;
            transition: .2s;
        }
        .sticky-bar a:hover { background: #fff; transform: translateY(-1px); }

        /* ===== WHATSAPP ===== */
        @keyframes wa-glow { 0%,100% { box-shadow: 0 0 0 0 rgba(37,211,102,0.6); } 50% { box-shadow: 0 0 0 12px rgba(37,211,102,0); } }
        .whats_btn {
            position: fixed; right: 16px; bottom: 84px; z-index: 9999;
            width: 50px; height: 50px; border-radius: 50%;
            background: #25D366;
            display: flex; align-items: center; justify-content: center;
            text-decoration: none;
            animation: wa-glow 2s infinite;
        }
        .whats_btn img { width: 28px; height: 28px; }

        /* OTP MODAL */
        #otpModal { z-index: 99999 !important; }
        .otp-modal-content { border: none !important; border-radius: 16px !important; background: #fff; text-align: center; overflow: hidden; position: relative; }
        .otp-modal-content::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 5px; background: var(--pri-dark); }
        .otp-icon-box { width: 70px; height: 70px; background: var(--pri-soft); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 10px auto 16px; color: var(--pri-dark); font-size: 26px; }
        .otp-input { width: 100%; letter-spacing: 12px; text-align: center; font-size: 24px; font-weight: bold; color: #333; border: 2px solid #eee !important; border-radius: 10px !important; background: #fafafa; height: 58px; }
        .otp-input:focus { border-color: var(--pri) !important; background: #fff; outline: none; }
        .btn-verify { background: var(--pri-dark); border: none; padding: 12px; font-size: 15px; border-radius: 10px; width: 100%; color: var(--cream); font-weight: 700; }
        .btn-verify:hover { background: var(--pri); }

        @media (max-width: 900px) {
            .features-2x2 { grid-template-columns: 1fr; }
            .spec-grid-x { grid-template-columns: 1fr; }
            .reviews-grid-x { grid-template-columns: 1fr; }
            .trust-grid { grid-template-columns: 1fr 1fr; }
            .gallery-x { grid-template-columns: 1fr; }
            .gallery-thumbs-x { flex-direction: row; overflow-x: auto; }
            .gallery-thumb-x { min-width: 80px; }
        }
        @media (max-width: 540px) {
            .top-stripe { font-size: 11px; padding: 7px 0; }
            .hero-cta-btn { min-width: auto; width: 100%; }
            .order-card-x { padding: 20px; }
            .trust-grid { grid-template-columns: 1fr; }
            .sticky-bar { padding: 10px 14px; }
            .sticky-bar a { padding: 8px 16px; font-size: 12px; }
            .whats_btn { right: 12px; bottom: 78px; width: 44px; height: 44px; }
            .whats_btn img { width: 24px; height: 24px; }
        }
    </style>
<style>
/* 🔒 Hide all admin-blank elements — text, rows, sections — so নo static text shows when admin জা boshay নাই */
h1:empty, h2:empty, h3:empty, h4:empty, h5:empty, h6:empty,
p:empty, span:empty,
.sec-title:empty, .sec-sub:empty,
.hero-title-main:empty, .gallery-badge:empty,
.feat-card h5:empty, .feat-card p:empty,
.row-s .lbl:empty, .row-s .val:empty,
.review-x-card p:empty, .review-x-card .reviewer span:empty,
.trust-card h6:empty, .trust-card p:empty,
.faq-q span:empty, .faq-a:empty,
.footer-brand:empty { display: none !important; }

/* Hide repeated rows / cards when their main field is blank */
.feat-card:has(h5:empty),
.row-s:has(.lbl:empty),
.review-x-card:has(p:empty),
.trust-card:has(h6:empty),
.size-table tbody tr:has(td:first-child:empty),
.faq-item-x:has(.faq-q > span:empty) { display: none !important; }
</style>
</head>
<body>

@if(!empty($__gtmId))
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $__gtmId }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
@endif

{{-- TOP STRIPE --}}
<div class="top-stripe">
    <i class="fas fa-clock"></i> {{ $ln_pg->countdown_title ?? '' }}: <span id="countdown-timer">--:--:--</span>
</div>

{{-- HERO --}}
<section class="hero-section">
    <div class="container-x">
        <h1 class="hero-title-main">{{ $ln_pg->title1 ?? '' }}</h1>

        @if(!empty($videoEmbed))
        <div class="video-wrapper">{!! $videoEmbed !!}</div>
        @elseif(!empty($mainImg))
        <div class="video-wrapper" style="aspect-ratio:1/1; max-width: 480px;">
            <img src="{{ $mainImg }}" alt="{{ $productName }}" style="width:100%; height:100%; object-fit:cover;">
        </div>
        @else
        <div class="video-wrapper">
            <div class="video-placeholder"><i class="fas fa-play-circle"></i></div>
        </div>
        @endif

        <div class="hero-price-box">
            <div class="hero-price-new">৳{{ number_format($defaultPrice, 0) }}</div>
            @if($savedAmount > 0)
            <span class="hero-saved-badge">{{ $ln_pg->discount_save_text ?? number_format($savedAmount, 0).'৳ বাঁচান' }}</span>
            @endif
        </div>

        <div>
            <a href="#order_section" class="hero-cta-btn">
                {{ $ln_pg->btn_text_hero ?? '' }} <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

{{-- 4 FEATURES --}}
<section class="cream-sec">
    <div class="container-x">
        <div class="sec-head">
            <h2 class="sec-title">{{ $ln_pg->feature_title ?? '' }}</h2>
        </div>

        <div class="features-2x2">
            @php
                $fd = [
                    ['fas fa-shield-alt','প্রিমিয়াম ফেব্রিক','উন্নতমানের কটন এবং লিনেন কাপড়ের তৈরি।'],
                    ['fas fa-cut','নিখুঁত স্টিচিং','ডাবল-সিম স্টিচিং, দীর্ঘস্থায়ী ও নিখুঁত।'],
                    ['fas fa-shopping-bag','একচুয়েটিং ডিজাইন','ক্ল্যাসিক ও মডার্ন স্টাইলের সমন্বয়।'],
                    ['fas fa-tint','সফট ফ্যাব্রিক','সফট ও কুল, সারা দিন কম্ফোর্টেবল।'],
                ];
            @endphp
            @foreach([1,2,3,4] as $n)
            <div class="feat-card">
                <div class="feat-icon"><i class="{{ $ln_pg->{'id_'.$n.'_icon'} ?? '' }}"></i></div>
                <div>
                    <h5>{{ $ln_pg->{'id_'.$n.'_title'} ?? '' }}</h5>
                    <p>{{ $ln_pg->{'id_'.$n.'_desc'} ?? '' }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- GALLERY ===== --}}
<section class="cream-sec alt">
    <div class="container-x">
        <div class="sec-head">
            <span class="gallery-badge">{{ $ln_pg->gallery_badge ?? '' }}</span>
            <h2 class="sec-title">{{ $ln_pg->identify_title ?? '' }}</h2>
            <p class="sec-sub">{{ $ln_pg->identify_subtitle ?? '' }}</p>
        </div>

        <div class="gallery-x">
            <div class="gallery-main-img">
                @if($mainImg)
                    <img id="gallery-main-x" src="{{ $mainImg }}" alt="Punjabi">
                @elseif($sliderImages->count() > 0)
                    <img id="gallery-main-x" src="{{ asset('landing_sliders/'.$sliderImages->first()->image) }}" alt="Punjabi">
                @else
                    <i class="fas fa-tshirt ph"></i>
                @endif
            </div>
            <div class="gallery-thumbs-x">
                @forelse($sliderImages as $idx => $img)
                    @if($idx < 4)
                    <div class="gallery-thumb-x {{ $idx == 0 && empty($mainImg) ? 'active' : '' }}" onclick="document.getElementById('gallery-main-x').src='{{ asset('landing_sliders/'.$img->image) }}'; document.querySelectorAll('.gallery-thumb-x').forEach(t=>t.classList.remove('active')); this.classList.add('active');">
                        <img src="{{ asset('landing_sliders/'.$img->image) }}" alt="thumb">
                    </div>
                    @endif
                @empty
                    @for($i = 0; $i < 4; $i++)
                    <div class="gallery-thumb-x"><i class="fas fa-image" style="color:var(--text-muted); opacity:.4; font-size:24px;"></i></div>
                    @endfor
                @endforelse
            </div>
        </div>
    </div>
</section>

{{-- SPEC ===== --}}
<section class="cream-sec">
    <div class="container-x">
        <div class="sec-head">
            <h2 class="sec-title">{{ $ln_pg->spec_title ?? '' }}</h2>
        </div>

        <div class="spec-grid-x">
            <div class="spec-img-x">
                @if($specImg)
                    <img src="{{ $specImg }}" alt="Spec">
                @else
                    <i class="fas fa-tshirt" style="font-size:80px; color:var(--pri); opacity:.4;"></i>
                @endif
            </div>
            <div class="spec-table-x">
                @php $sd = [['টিস্যু','১০০% প্রিমিয়াম কটন'],['কাটিং / টাইপ','সেমি-ফিট রেগুলার'],['সাইজ','M, L, XL, XXL'],['ফিট','রেগুলার ফিট'],['কালার','সবুজ ও অন্যান্য'],['ওজন','সাধারণ ব্যবহার'],['প্রিন্টিং','৭ দিন গ্যারান্টি']]; @endphp
                @foreach([1,2,3,4,5,6,7] as $n)
                <div class="row-s">
                    <div class="lbl">{{ $ln_pg->{'spec_'.$n.'_label'} ?? '' }}</div>
                    <div class="val">{{ $ln_pg->{'spec_'.$n.'_value'} ?? '' }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- REVIEWS ===== --}}
<section class="cream-sec alt">
    <div class="container-x">
        <div class="sec-head">
            <h2 class="sec-title">{{ $ln_pg->review_title ?? '' }}</h2>
            <p class="sec-sub">{{ $ln_pg->review_subtitle ?? '' }}</p>
        </div>

        <div class="reviews-grid-x">
            @php
                $rd = [
                    ['যা প্রত্যাশা করেছিলাম তার চেয়ে অনেক ভালো। কাপড় চমৎকার নরম।','মুহাম্মদ রহিম','চট্টগ্রাম'],
                    ['ফিটিং নিখুঁত, স্টিচিং দারুণ। আমি খুশি।','রাশিদুল আনোয়ার','ঢাকা'],
                    ['এই দামে এত ভালো পাঞ্জাবি — সেরা ডিল।','সাকিব ইসলাম','সিলেট'],
                ];
            @endphp
            @foreach([1,2,3] as $n)
            <div class="review-x-card">
                <div class="stars">★★★★★ <small>৫/৫</small></div>
                <p>"{{ $ln_pg->{'rev_'.$n.'_text'} ?? '' }}"</p>
                <div class="reviewer">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ $ln_pg->{'rev_'.$n.'_name'} ?? '' }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- SIZE GUIDE section removed: size/bust/length/sleeve (inch) measurements are not applicable to these three-piece products --}}

{{-- TRUST BADGES ===== --}}
<section class="cream-sec alt">
    <div class="container-x">
        <div class="trust-grid">
            @php
                $td = [
                    ['fas fa-truck','দ্রুত ডেলিভারি','২-৪ কর্মদিবস'],
                    ['fas fa-money-bill','ক্যাশ অন ডেলিভারি','বাসায় বসেই দিন'],
                    ['fas fa-credit-card','সিউর পেমেন্ট','১০০% নিরাপদ'],
                    ['fas fa-check-double','১০০% অরিজিনাল','রিপ্লেসমেন্ট গ্যারান্টি'],
                ];
            @endphp
            @foreach([5,6,7,8] as $idx => $n)
            <div class="trust-card">
                <div class="trust-icon"><i class="{{ $ln_pg->{'id_'.$n.'_icon'} ?? '' }}"></i></div>
                <div>
                    <h6>{{ $ln_pg->{'id_'.$n.'_title'} ?? '' }}</h6>
                    <p>{{ $ln_pg->{'id_'.$n.'_desc'} ?? '' }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ORDER FORM ===== --}}
<section id="order_section" class="cream-sec">
    <div class="container-x">
        <div class="sec-head">
            <h2 class="sec-title">{{ $ln_pg->form_title ?? '' }}</h2>
            <p class="sec-sub">{{ $ln_pg->form_subtitle ?? '' }}</p>
        </div>

        <div class="order-card-x">
            <form action="{{ route('front.storelandData') }}" method="POST" id="checkout_form">
                @csrf
                <input type="hidden" name="prd_id" value="{{ $productId }}">
                <input type="hidden" name="landing_page_type" value="16">
                <input type="hidden" name="purchase_event_id" id="purchase_event_id" value="">
                <input type="hidden" id="unit_price" value="{{ $defaultPrice }}">
                <input type="hidden" id="max_stock" value="{{ $defaultStock }}">
                <input type="hidden" name="variation_id" id="variation_id" value="">

                <div class="mb-3">
                    <label class="form-label">আপনার নাম *</label>
                    <input type="text" name="first_name" class="form-control" placeholder="পুরো নাম লিখুন" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">মোবাইল নাম্বার *</label>
                    <input type="tel" name="mobile" id="customer_mobile" class="form-control" placeholder="01XXXXXXXXX" minlength="11" maxlength="11" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">সম্পূর্ণ ঠিকানা *</label>
                    <textarea name="shipping_address" class="form-control" rows="2" placeholder="বাসা, রাস্তা, এলাকা, থানা, জেলা" required></textarea>
                </div>

                <div class="row g-3">
                    @if($sizes->count() > 0)
                    <div class="col-md-6">
                        <label class="form-label">সাইজ *</label>
                        <div class="size-pick">
                            @foreach($sizes as $size)
                                <button type="button" class="sz-btn var-size-btn" data-id="{{ $size->id }}">{{ $size->name ?? $size->title }}</button>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    <div class="col-md-{{ $sizes->count() > 0 ? '6' : '12' }}">
                        <label class="form-label">পরিমাণ</label>
                        <div class="qty-x">
                            <button type="button" id="qty_minus">-</button>
                            <input type="number" name="quantity" id="qty_input" value="1" readonly>
                            <button type="button" id="qty_plus">+</button>
                        </div>
                    </div>
                </div>

                @if($colors->count() > 0)
                <div class="mt-3">
                    <label class="form-label">কালার *</label>
                    <div class="size-pick">
                        @foreach($colors as $color)
                            <button type="button" class="sz-btn var-color-btn" data-id="{{ $color->id }}">{{ $color->name }}</button>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="mt-3">
                    <label class="form-label">ডেলিভারি এলাকা *</label>
                    <select name="delivery_charge_id" id="delivery_charge" class="form-select" required>
                        <option value="" disabled selected>এলাকা সিলেক্ট করুন</option>
                        @foreach($charges ?? [] as $charge)
                            <option value="{{ $charge->id }}" data-charge="{{ $isFreeShipping ? 0 : intval($charge->amount) }}">
                                {{ $charge->title }} - {!! $isFreeShipping ? '<span style="color:#15803d;font-weight:700;">ফ্রি</span>' : intval($charge->amount).' ৳' !!}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- PAYMENT --}}
                <div class="pay-section">
                    <span class="label">পেমেন্ট মেথড *</span>

                    @if(isset($information->cod_active) && $information->cod_active == 1)
                    <label class="d-block" style="cursor:pointer;">
                        <input type="radio" name="payment_method" value="cod" class="d-none" onchange="togglePaymentAction('cod')" checked>
                        <div class="payment-box"><i class="fas fa-money-bill-wave"></i><span class="fw-bold">ক্যাশ অন ডেলিভারি</span></div>
                    </label>
                    @endif

                    @if(isset($information->ssl_active) && $information->ssl_active == 1)
                    <label class="d-block" style="cursor:pointer;">
                        <input type="radio" name="payment_method" value="sslcommerz" class="d-none" onchange="togglePaymentAction('online')">
                        <div class="payment-box"><i class="fas fa-credit-card"></i><span class="fw-bold">অনলাইন পেমেন্ট</span></div>
                    </label>
                    @endif

                    @if(isset($information->bkash_active) && $information->bkash_active == 1 && Route::has('bkash.create'))
                    <label class="d-block" style="cursor:pointer;">
                        <input type="radio" name="payment_method" value="bkash" class="d-none" onchange="togglePaymentAction('bkash')">
                        <div class="payment-box"><img src="{{ asset('frontend/images/bkash_logo.png') }}" alt="bKash" onerror="this.style.display='none'"><span class="fw-bold">বিকাশ পেমেন্ট (bKash)</span></div>
                    </label>
                    @endif

                    @if(isset($information->nagad_active) && $information->nagad_active == 1 && Route::has('nagad.pay'))
                    <label class="d-block" style="cursor:pointer;">
                        <input type="radio" name="payment_method" value="nagad" class="d-none" onchange="togglePaymentAction('nagad')">
                        <div class="payment-box"><img src="{{ asset('frontend/images/nagad.png') }}" alt="Nagad" onerror="this.style.display='none'"><span class="fw-bold">নগদ পেমেন্ট (Nagad)</span></div>
                    </label>
                    @endif

                    @if(isset($information->eps_active) && $information->eps_active == 1 && Route::has('eps.pay'))
                    <label class="d-block" style="cursor:pointer;">
                        <input type="radio" name="payment_method" value="eps" class="d-none" onchange="togglePaymentAction('eps')">
                        <div class="payment-box"><i class="fas fa-wallet"></i><span class="fw-bold">EPS পেমেন্ট</span></div>
                    </label>
                    @endif

                    @if(isset($information->uddoktapay_active) && $information->uddoktapay_active == 1 && Route::has('uddoktapay.pay'))
                    <label class="d-block" style="cursor:pointer;">
                        <input type="radio" name="payment_method" value="uddoktapay" class="d-none" onchange="togglePaymentAction('uddoktapay')">
                        <div class="payment-box"><i class="fas fa-money-check-alt"></i><span class="fw-bold">উদ্যোক্তাপে (UddoktaPay)</span></div>
                    </label>
                    @endif

                    @if(isset($information->manual_payments) && $information->manual_payments == 1)
                        @php $activeManuals = \DB::table('manual_payments')->where('status', 1)->get(); @endphp
                        @foreach($activeManuals as $mp)
                        <label class="d-block" style="cursor:pointer;">
                            <input type="radio" name="payment_method" value="{{ $mp->name }}" class="d-none" onchange="togglePaymentAction('manual', '{{ $mp->name }}', '{{ $mp->number }}', '{{ $mp->type }}')">
                            <div class="payment-box"><i class="fas fa-mobile-alt"></i><span class="fw-bold">{{ $mp->name }} ({{ $mp->type }})</span></div>
                        </label>
                        @endforeach

                        <div id="manual_payment_area" class="manual-pay-area" style="display:none;">
                            <div class="alert alert-info"><i class="fas fa-info-circle me-1"></i> <span id="payment_instruction"></span></div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size:12px;">যে নাম্বার থেকে পাঠিয়েছেন <span style="color:#dc2626;">*</span></label>
                                    <input type="text" name="sender_number" id="sender_number" class="form-control" placeholder="017XXXXXXXX">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size:12px;">Transaction ID <span style="color:#dc2626;">*</span></label>
                                    <input type="text" name="transaction_id" id="transaction_id" class="form-control" placeholder="TRX123456">
                                </div>
                            </div>
                        </div>
                    @endif

                </div>

                {{-- COUPON --}}
                @if(isset($information->coupon_visibility) && $information->coupon_visibility == 1)
                <div class="coupon-box">
                    <label class="form-label"><i class="fas fa-ticket-alt me-1"></i> কুপন কোড থাকলে এখানে দিন</label>
                    <div class="input-group">
                        <input type="text" id="coupon_code" class="form-control" placeholder="কোড লিখুন">
                        <button type="button" id="coupon_btn_submit" onclick="applyCouponLand()">APPLY</button>
                    </div>
                    <small id="coupon_msg" class="d-block mt-2 fw-bold"></small>
                </div>
                @endif
                @php $sessionDiscount = session('coupon_discount') ?? 0; @endphp
                <input type="hidden" id="discount_amount" value="{{ $sessionDiscount }}">

                <div class="order-summary-x">
                    <div class="row-l"><span>প্রোডাক্ট প্রাইস (১ × <span id="unit_price_display">{{ $defaultPrice }}</span>)</span><span><span id="calc_subtotal">{{ $defaultPrice }}</span> ৳</span></div>
                    <div class="row-l"><span>ডেলিভারি চার্জ</span><span id="calc_shipping_text">+ <span id="calc_shipping">0</span> ৳</span></div>
                    <div class="row-l" id="discount_row" style="{{ $sessionDiscount > 0 ? '' : 'display:none;' }}">
                        <span style="color:#15803d;">ডিসকাউন্ট</span>
                        <span style="color:#15803d;">- <span id="calc_discount">{{ $sessionDiscount }}</span> ৳</span>
                    </div>
                    <div class="row-l total"><span>মোট</span><span>৳<span id="calc_total">{{ $defaultPrice }}</span></span></div>
                </div>
                <input type="hidden" id="final_amount" name="final_amount" value="{{ $defaultPrice }}">
                <input type="hidden" name="amount" id="amount" value="{{ $defaultPrice }}">

                <button type="submit" id="submit_btn" class="order-submit-x">
                    <i class="fas fa-shield-alt me-2"></i> {{ $ln_pg->btn_text_form ?? '' }}
                </button>

                <p class="text-center mt-3 mb-0" style="font-size:11px; color:#6b7280;">
                    <i class="fas fa-lock me-1"></i> ১০০% সিকিউর — আপনার তথ্য নিরাপদ
                </p>
            </form>
        </div>
    </div>
</section>

{{-- FAQ ===== --}}
<section class="cream-sec alt">
    <div class="container-x">
        <div class="sec-head">
            <h2 class="sec-title">{{ $ln_pg->faq_title ?? '' }}</h2>
        </div>

        @php
            $faqd = [
                ['পাঞ্জাবিটি কত দিন ব্যবহার করা যাবে?','প্রিমিয়াম কোয়ালিটি ফ্যাব্রিক হওয়ায় দীর্ঘ সময় ব্যবহার করা যাবে। সঠিক যত্ন নিলে ২-৩ বছর পর্যন্ত নতুনের মতো থাকবে।'],
                ['লেখাটি কীভাবে ধোয়া যাবে?','ঠাণ্ডা পানিতে হাতে অথবা মেশিনে জেন্টল মোডে ধুতে পারেন। ব্লিচ ব্যবহার করবেন না।'],
                ['সাইজ পরিবর্তন করা যাবে?','অবশ্যই, ৭ দিনের মধ্যে সাইজ পরিবর্তন করা যাবে। প্রোডাক্ট ব্যবহার না করা অবস্থায় থাকতে হবে।'],
                ['ক্যাশ অন ডেলিভারি পাওয়া যাবে?','হ্যাঁ, সারাদেশে ক্যাশ অন ডেলিভারি সুবিধা পাওয়া যাবে। অগ্রিম পেমেন্ট দরকার নেই।'],
                ['রিটার্ন পলিসি কী?','প্রোডাক্ট হাতে পাওয়ার ৭ দিনের মধ্যে রিটার্ন বা এক্সচেঞ্জ করা যাবে। প্রোডাক্ট unused অবস্থায় থাকতে হবে।'],
            ];
        @endphp
        <div class="faq-list-x">
            @foreach([1,2,3,4,5] as $n)
            <div class="faq-item-x">
                <div class="faq-q">
                    <span>{{ $ln_pg->{'faq_'.$n.'_q'} ?? '' }}</span>
                    <i class="fas fa-plus"></i>
                </div>
                <div class="faq-a">{{ $ln_pg->{'faq_'.$n.'_a'} ?? '' }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- FOOTER --}}
<footer class="site-footer">
    <div class="container-x">
        <p class="footer-brand">{{ $ln_pg->footer_company ?? $brandMain }}</p>
        <p>{{ $ln_pg->footer_copyright ?? ''.date('Y').' '.($ln_pg->footer_company ?? $brandMain).' — সকল অধিকার সংরক্ষিত।' }}</p>
        <p style="font-size: 11px; margin-top: 6px;">
            <i class="fas fa-phone me-1"></i> {{ $ln_pg->phone ?? ($information->phone ?? '') }}
            &nbsp;·&nbsp;
            <i class="fas fa-envelope me-1"></i> {{ $ln_pg->footer_email ?? ($information->email ?? '') }}
            &nbsp;·&nbsp;
            <i class="fas fa-map-marker-alt me-1"></i> {{ $ln_pg->dhamaka_title ?? ($information->address ?? '') }}
        </p>
    </div>
</footer>

{{-- STICKY BOTTOM BAR --}}
<div class="sticky-bar">
    <div class="price-stack">
        <span class="p-now">৳{{ number_format($defaultPrice, 0) }}</span>
        <span class="p-old">৳{{ number_format($oldPrice, 0) }}</span>
    </div>
    <a href="#order_section">{{ $ln_pg->final_cta_btn_text ?? '' }} <i class="fas fa-arrow-right"></i></a>
</div>

{{-- OTP MODAL --}}
<div class="modal fade" id="otpModal" data-bs-backdrop="static" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content otp-modal-content p-4">
      <div class="modal-header border-0 pb-0 justify-content-end"><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body text-center pt-0 pb-3">
        <div class="otp-icon-box"><i class="fas fa-shield-alt"></i></div>
        <h4 class="fw-bold mb-2" style="color:var(--pri-dark);">মোবাইল ভেরিফিকেশন</h4>
        <p style="font-size:13px; color:#6b7280;">আপনার <span class="fw-bold" id="otp_sent_number"></span> নাম্বারে কোড পাঠানো হয়েছে।</p>
        <div class="form-group mb-3">
            <input type="text" id="otp_input" maxlength="4" class="form-control otp-input" placeholder="____" autocomplete="one-time-code" inputmode="numeric">
            <small class="text-danger mt-2 d-block fw-bold" id="otp_error"></small>
        </div>
        <button type="button" class="btn-verify" onclick="verifyOtpNow()">যাচাই করুন</button>
        <div class="text-center mt-3">
             <button type="button" class="btn btn-link text-decoration-none text-muted p-0 small" id="resendOtpBtn" onclick="sendOtpBeforeSubmit(true)">
                 কোড পাননি? <span style="color:var(--pri-dark); font-weight:700;">আবার পাঠান</span>
             </button>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- WhatsApp --}}
@php
    $waNumber = $ln_pg->whatsapp ?? $ln_pg->phone ?? ($information->whatsapp ?? '');
    $waClean = preg_replace('/\D+/', '', $waNumber);
@endphp
@if(!empty($waClean))
<a href="https://wa.me/{{ $waClean }}" target="_blank" class="whats_btn" aria-label="WhatsApp">
    <img src="https://img.icons8.com/windows/96/ffffff/whatsapp--v1.png" alt="whatsapp">
</a>
@endif

@if(isset($information->bkash_active) && $information->bkash_active == 1 && Route::has('bkash.create'))
<button id="bKash_button" style="display:none;"></button>
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
toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "2500" };
var current_discount_val = 0; var current_discount_type = "fixed";
var isWeightBased = {{ $isWeightBased ? 'true' : 'false' }};
var isFreeShipping = {{ $isFreeShipping }};
var isOtpVerified = false;
var otpSystemEnabled = {{ $information->otp_system ?? 0 }};
var otpTimerInterval; var isSendingOtp = false;
var paymentID, dynamicOrderId, successUrl = '';

function toNumber(v){ v = (v ?? '').toString().replace(/[^\d.]/g,''); var n = parseFloat(v); return isNaN(n) ? 0 : n; }

@if(isset($information->bkash_active) && $information->bkash_active == 1 && Route::has('bkash.create') && Route::has('bkash.execute'))
function initBkash() {
    bKash.init({
        paymentMode: 'checkout',
        paymentRequest: { "amount": "0", "intent": "sale" },
        createRequest: function (request) {
            $.ajax({
                url: "{{ route('bkash.create') }}", type: 'POST',
                data: { _token: "{{ csrf_token() }}", order_id: dynamicOrderId },
                success: function (data) {
                    if (data && data.paymentID != null) { paymentID = data.paymentID; bKash.create().onSuccess(data); }
                    else { bKash.create().onError(); toastr.error("Payment Error"); $('#submit_btn').prop('disabled', false); }
                },
                error: function () { bKash.create().onError(); toastr.error("Server error connecting to bKash"); $('#submit_btn').prop('disabled', false); }
            });
        },
        executeRequestOnAuthorization: function () {
            $.ajax({
                url: "{{ route('bkash.execute') }}", type: 'POST',
                data: { _token: "{{ csrf_token() }}", paymentID: paymentID },
                success: function (data) {
                    if (data && data.paymentID != null && data.transactionStatus === 'Completed') { window.location.href = successUrl; }
                    else { bKash.execute().onError(); toastr.error("Payment Failed"); $('#submit_btn').prop('disabled', false); }
                },
                error: function () { bKash.execute().onError(); toastr.error("Failed to execute bKash"); $('#submit_btn').prop('disabled', false); }
            });
        },
        onClose: function () { window.location.href = successUrl; }
    });
}
@endif

(function(){
    var hours = {{ (int)($ln_pg->countdown_hours ?? 4) }};
    var endTime = new Date().getTime() + (hours * 3600000);
    var saved = sessionStorage.getItem('lp16_countdown_end');
    if (saved && parseInt(saved) > new Date().getTime()) endTime = parseInt(saved);
    else sessionStorage.setItem('lp16_countdown_end', endTime);
    function pad(n){ return n<10?'0'+n:n; }
    function tick() {
        var diff = endTime - new Date().getTime();
        var el = document.getElementById('countdown-timer');
        if (diff <= 0) { if(el) el.innerText = '00:00:00'; return; }
        var h = Math.floor(diff/3600000), m = Math.floor((diff%3600000)/60000), s = Math.floor((diff%60000)/1000);
        if(el) el.innerText = pad(h)+':'+pad(m)+':'+pad(s);
    }
    tick(); setInterval(tick, 1000);
})();

document.querySelectorAll('.faq-item-x').forEach(function(item){
    item.addEventListener('click', function(){ this.classList.toggle('open'); });
});

document.querySelectorAll('a[href^="#"]').forEach(function(a){
    a.addEventListener('click', function(e){
        var tgt = document.querySelector(this.getAttribute('href'));
        if (tgt) { e.preventDefault(); window.scrollTo({ top: tgt.offsetTop - 20, behavior: 'smooth' }); }
    });
});

document.querySelectorAll('.sz-static-btn').forEach(function(b){
    b.addEventListener('click', function(){
        document.querySelectorAll('.sz-static-btn').forEach(x => x.classList.remove('active'));
        this.classList.add('active');
    });
});

window.togglePaymentAction = function(method, name = '', number = '', type = '') {
    var manualArea = $('#manual_payment_area');
    var sNum = $('#sender_number'), tId = $('#transaction_id');
    if(method === 'manual') {
        $('#payment_instruction').html(`আপনার টোটাল বিল <b>${number} (${type})</b> নাম্বারে Send Money করুন এবং নিচের তথ্য দিন।`);
        manualArea.slideDown();
        sNum.attr('required', 'required'); tId.attr('required', 'required');
    } else {
        manualArea.slideUp();
        sNum.removeAttr('required'); tId.removeAttr('required');
    }
};

window.applyCouponLand = function() {
    var code = $('#coupon_code').val();
    var $btn = $('#coupon_btn_submit');
    if(!code) { toastr.error('কুপন কোড লিখুন'); return; }
    var unitPrice = toNumber($('#unit_price').val());
    var qty = parseInt($('#qty_input').val()) || 1;
    var current_total = unitPrice * qty;
    $btn.prop('disabled', true).text('Checking...');
    $.ajax({
        url: "{{ route('front.getCouponDiscount') }}", method: "GET",
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
                current_discount_val = 0; calculate();
            }
        },
        error: function() { toastr.error('Error'); $btn.prop('disabled', false).text('APPLY'); }
    });
};

function startOtpTimer(duration, display) {
    var timer = duration; clearInterval(otpTimerInterval);
    $('#resendOtpBtn').prop('disabled', true);
    otpTimerInterval = setInterval(function () {
        var seconds = parseInt(timer % 60, 10); seconds = seconds<10?"0"+seconds:seconds;
        display.html("Wait (" + seconds + "s)");
        if (--timer < 0) {
            clearInterval(otpTimerInterval);
            display.html("কোড পাননি? <span style='color:var(--pri-dark); font-weight:700;'>আবার পাঠান</span>");
            $('#resendOtpBtn').prop('disabled', false);
        }
    }, 1000);
}

window.sendOtpBeforeSubmit = function(isResend = false) {
    if(isSendingOtp) return;
    var mobile = $('#customer_mobile').val();
    if(!mobile || mobile.length !== 11) { toastr.error('সঠিক মোবাইল নাম্বার দিন'); return; }
    isSendingOtp = true;
    if(!isResend) $('#submit_btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Sending OTP...');
    $.ajax({
        url: "{{ route('sendOtp') }}", type: "POST", data: { mobile: mobile, _token: "{{ csrf_token() }}" },
        success: function(res) {
            isSendingOtp = false;
            if(!isResend) $('#submit_btn').prop('disabled', false).html('<i class="fas fa-shield-alt me-2"></i> {{ $ln_pg->btn_text_form ?? "" }}');
            if(res.success) {
                $('#otp_sent_number').text(mobile);
                $('#otpModal').appendTo('body');
                var myModal = new bootstrap.Modal(document.getElementById('otpModal'));
                myModal.show();
                setTimeout(function() { $('#otp_input').focus(); }, 500);
                startOtpTimer(30, $('#resendOtpBtn'));
            } else toastr.error(res.msg);
        },
        error: function() { isSendingOtp = false; if(!isResend) $('#submit_btn').prop('disabled', false).html('<i class="fas fa-shield-alt me-2"></i> {{ $ln_pg->btn_text_form ?? "" }}'); }
    });
};

window.verifyOtpNow = function() {
    var code = $('#otp_input').val(); var mobile = $('#customer_mobile').val();
    $.ajax({
        url: "{{ route('verifyOtp') }}", type: "POST", data: { otp: code, mobile: mobile, _token: "{{ csrf_token() }}" },
        success: function(res) {
            if(res.success) { isOtpVerified = true; bootstrap.Modal.getInstance(document.getElementById('otpModal')).hide(); submitOrderFinal(); }
            else $('#otp_error').text(res.msg);
        }
    });
};

function submitOrderFinal() {
    let $form = $('#checkout_form');
    let payMethod = $('input[name="payment_method"]:checked').val() || 'cod';
    var purchaseEventId = "PUR_{{ $productId }}_" + Date.now();
    $('#purchase_event_id').val(purchaseEventId);

    if(payMethod === 'sslcommerz'){ $form.attr('action', "{{ url('/pay') }}")[0].submit(); return; }

    $('#submit_btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> প্রসেসিং...');

    $.ajax({
        url: "{{ route('front.storelandData') }}", method: "POST", data: $form.serialize(),
        success: function(res){
            if(res.success){
                // Purchase event is fired on thank-you page to avoid double counting in Pixel Helper
                if(@json(!$__ttFromGtm) && typeof ttq !== 'undefined' && ttq.track) {
                    ttq.track('PlaceAnOrder', {
                        content_type: 'product',
                        value: parseFloat($('#final_amount').val()),
                        currency: 'BDT',
                        contents: [{ content_id: '{{ $productId }}', content_type: 'product', content_name: @json($productName), price: parseFloat($('#unit_price').val()) || {{ $defaultPrice }}, quantity: parseInt($('#qty_input').val(), 10) || 1 }]
                    }, { event_id: purchaseEventId });
                }

                if(payMethod === 'bkash') {
                    dynamicOrderId = res.order_id || res.url.split('/').pop(); successUrl = res.url;
                    @if(isset($information->bkash_active) && $information->bkash_active == 1 && Route::has('bkash.create'))
                    initBkash();
                    setTimeout(function(){ $('#bKash_button').click(); }, 300);
                    @endif
                } else if(payMethod === 'nagad') { window.location.href = "{{ url('nagad/pay') }}/" + (res.order_id || res.url.split('/').pop()); }
                else if(payMethod === 'uddoktapay') { window.location.href = "{{ url('uddoktapay/pay') }}/" + (res.order_id || res.url.split('/').pop()); }
                else if(payMethod === 'eps') { window.location.href = res.url; }
                else { toastr.success(res.msg); setTimeout(function(){ window.location.href = res.url; }, 800); }
            } else {
                toastr.error(res.msg);
                $('#submit_btn').prop('disabled', false).html('<i class="fas fa-shield-alt me-2"></i> {{ $ln_pg->btn_text_form ?? "" }}');
            }
        },
        error: function () {
            $('#submit_btn').prop('disabled', false).html('<i class="fas fa-shield-alt me-2"></i> {{ $ln_pg->btn_text_form ?? "" }}');
            toastr.error('ফর্ম সাবমিট করতে সমস্যা');
        }
    });
}

$(document).ready(function() {
    let varMatrix = @json($varMatrix);
    let selectedSize = 0; let selectedColor = 0;

    if($('.var-size-btn').length > 0) { selectedSize = $('.var-size-btn').first().data('id'); $('.var-size-btn').first().addClass('active'); }
    if($('.var-color-btn').length > 0) { selectedColor = $('.var-color-btn').first().data('id'); $('.var-color-btn').first().addClass('active'); }

    window.calculate = function() {
        let price = Math.round(parseFloat($('#unit_price').val()) || 0);
        let qty = parseInt($('#qty_input').val()) || 1;
        let subtotal = price * qty;

        let discount = 0;
        if(current_discount_val > 0) {
            discount = (current_discount_type === 'percentage' || current_discount_type === 'percent') ? (subtotal * current_discount_val) / 100 : current_discount_val;
        }
        if(discount > 0) { $('#discount_row').show(); $('#calc_discount').text(Math.round(discount)); }
        else $('#discount_row').hide();

        $('#calc_subtotal').text(subtotal);
        $('#unit_price_display').text(price);
        $('input[name="amount"]').val(subtotal);

        let $opt = $('#delivery_charge').find("option:selected"); let cid = $opt.val();

        if(isFreeShipping == 1) {
            $('#calc_shipping_text').html('<span style="color:#15803d; font-weight:700;">ফ্রি ডেলিভারি</span>');
            let total = subtotal - discount; if(total < 0) total = 0;
            $('#calc_total').text(total); $('#final_amount').val(total);
        } else if(isWeightBased && cid && cid !== '') {
            $.ajax({
                url: "{{ route('front.getDeliveryChargeAjax') }}", type: "POST",
                data: { delivery_charge_id: cid, product_id: "{{ $productId }}", quantity: qty, _token: "{{ csrf_token() }}" },
                success: function(res) {
                    if(res.success) {
                        let charge = Math.round(parseFloat(res.charge));
                        $('#calc_shipping_text').html('+ <span id="calc_shipping">' + charge + '</span> ৳');
                        let total = (subtotal + charge) - discount; if(total < 0) total = 0;
                        $('#calc_total').text(total); $('#final_amount').val(total);
                    }
                }
            });
        } else {
            let charge = Math.round(parseFloat($opt.data('charge')) || 0);
            $('#calc_shipping_text').html('+ <span id="calc_shipping">' + charge + '</span> ৳');
            let total = (subtotal + charge) - discount; if(total < 0) total = 0;
            $('#calc_total').text(total); $('#final_amount').val(total);
        }
    };

    function checkVariation() {
        if ($('.var-size-btn').length === 0 && $('.var-color-btn').length === 0) {
            $('#variation_id').val('');
            $('#unit_price').val('{{ $defaultPrice }}');
            $('#max_stock').val('{{ $defaultStock }}');
            $('#submit_btn').prop('disabled', false).html('<i class="fas fa-shield-alt me-2"></i> {{ $ln_pg->btn_text_form ?? "" }}');
            calculate(); return;
        }
        if(Object.keys(varMatrix).length === 0) { calculate(); return; }
        let key = selectedSize + '_' + selectedColor;
        let matched = varMatrix[key];
        if(matched) {
            $('#variation_id').val(matched.id);
            $('#unit_price').val(matched.price);
            $('#max_stock').val(matched.stock);
            if(matched.stock <= 0) { toastr.error('স্টকে নেই!'); $('#submit_btn').prop('disabled', true).text('স্টকে নেই'); $('#qty_input').val(0); }
            else {
                $('#submit_btn').prop('disabled', false).html('<i class="fas fa-shield-alt me-2"></i> {{ $ln_pg->btn_text_form ?? "" }}');
                if($('#qty_input').val() == 0) $('#qty_input').val(1);
            }
        } else {
            $('#variation_id').val('');
            $('#submit_btn').prop('disabled', true).text('Out of Stock');
            toastr.error('এই কম্বিনেশন স্টকে নেই'); $('#qty_input').val(0);
        }
        calculate();
    }

    $('.var-size-btn').click(function() {
        $('.var-size-btn').removeClass('active'); $(this).addClass('active');
        selectedSize = $(this).data('id');
        if ($('.var-color-btn').length > 0) {
            let hasValid = false; let firstValid = null;
            $('.var-color-btn').each(function() {
                let cId = $(this).data('id');
                if (varMatrix[selectedSize + '_' + cId]) {
                    $(this).show(); if (firstValid === null) firstValid = $(this);
                    if ($(this).hasClass('active')) hasValid = true;
                } else { $(this).hide().removeClass('active'); }
            });
            if (!hasValid && firstValid) { firstValid.addClass('active'); selectedColor = firstValid.data('id'); }
        }
        checkVariation();
    });

    $('.var-color-btn').click(function() {
        $('.var-color-btn').removeClass('active'); $(this).addClass('active');
        selectedColor = $(this).data('id');
        if ($('.var-size-btn').length > 0) {
            let hasValid = false; let firstValid = null;
            $('.var-size-btn').each(function() {
                let sId = $(this).data('id');
                if (varMatrix[sId + '_' + selectedColor]) {
                    $(this).show(); if (firstValid === null) firstValid = $(this);
                    if ($(this).hasClass('active')) hasValid = true;
                } else { $(this).hide().removeClass('active'); }
            });
            if (!hasValid && firstValid) { firstValid.addClass('active'); selectedSize = firstValid.data('id'); }
        }
        checkVariation();
    });

    if($('.var-size-btn.active').length > 0) $('.var-size-btn.active').trigger('click');
    else if($('.var-color-btn.active').length > 0) $('.var-color-btn.active').trigger('click');
    else checkVariation();

    $('#delivery_charge').on('change', calculate);
    $('#qty_plus').click(function(){ let q = parseInt($('#qty_input').val()) || 1; let max = parseInt($('#max_stock').val()) || 1; if(q < max) { $('#qty_input').val(q + 1); calculate(); } else { toastr.warning('Max stock'); } });
    $('#qty_minus').click(function(){ let q = parseInt($('#qty_input').val()) || 1; if(q > 1) { $('#qty_input').val(q - 1); calculate(); } });

    // 🔔 Incomplete order auto-save — keyup (debounced) + blur + beforeunload (sendBeacon).
    let incompleteSaveTimer;
    let incompleteUnloadFired = false;
    function saveIncomplete(useBeacon) {
        if (window.__orderSubmitted) return;
        let mobile = ($('#customer_mobile').val() || '').trim();
        if(mobile.length < 11) return;
        let payload = {
            mobile: mobile,
            name: $('input[name="first_name"]').val() || '',
            address: $('input[name="shipping_address"], textarea[name="shipping_address"]').val() || '',
            prd_id: $('input[name="prd_id"]').val() || '',
            variation_id: $('#variation_id').val() || '',
            quantity: $('#qty_input').val() || '',
            amount: $('#unit_price').val() || '',
            _token: "{{ csrf_token() }}"
        };
        let url = "{{ route('incompleteStore') }}";
        if(useBeacon && navigator.sendBeacon) {
            let fd = new FormData();
            Object.keys(payload).forEach(k => fd.append(k, payload[k]));
            navigator.sendBeacon(url, fd);
        } else {
            $.post(url, payload);
        }
    }
    $('#customer_mobile').on('keyup change', function() {
        clearTimeout(incompleteSaveTimer);
        incompleteSaveTimer = setTimeout(function(){ saveIncomplete(false); }, 800);
    });
    $('#customer_mobile, input[name="first_name"], input[name="shipping_address"], textarea[name="shipping_address"]').on('blur', function(){ clearTimeout(incompleteSaveTimer); saveIncomplete(false); });
    $(window).on('beforeunload pagehide', function(){ if (incompleteUnloadFired) return; incompleteUnloadFired = true; saveIncomplete(true); });
    document.addEventListener('submit', function(){ window.__orderSubmitted = true; setTimeout(function(){ window.__orderSubmitted = false; }, 10000); }, true);

    $('#checkout_form').submit(function(e) {
        e.preventDefault();
        if(!$('#delivery_charge').val()){ toastr.warning('ডেলিভারি এলাকা সিলেক্ট করুন'); return false; }
        if(!$('#variation_id').val() && ($('.var-size-btn').length > 0 || $('.var-color-btn').length > 0)) { toastr.error('সাইজ/কালার সিলেক্ট করুন'); return false; }
        if(parseInt($('#max_stock').val()) <= 0) { toastr.error('Out of stock!'); return false; }

        let paymentMethod = $('input[name="payment_method"]:checked').val() || 'cod';
        if(paymentMethod !== 'online' && paymentMethod !== 'bkash' && paymentMethod !== 'eps' && paymentMethod !== 'nagad' && paymentMethod !== 'uddoktapay' && paymentMethod !== 'cod' && paymentMethod !== 'sslcommerz') {
            if(!$('#sender_number').val() || !$('#transaction_id').val()) { toastr.warning('পেমেন্ট নাম্বার ও Transaction ID দিন'); return false; }
        }

        if (paymentMethod === 'sslcommerz' && !otpSystemEnabled) {
            let purchaseId = "PUR_{{ $productId }}_" + Date.now();
            $('#purchase_event_id').val(purchaseId);
            $(this).attr('action', "{{ url('/pay') }}").attr('method', 'POST')[0].submit();
            return;
        }

        if(otpSystemEnabled == 1 && !isOtpVerified) sendOtpBeforeSubmit();
        else submitOrderFinal();
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
