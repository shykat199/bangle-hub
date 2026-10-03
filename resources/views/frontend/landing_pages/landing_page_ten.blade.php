<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $ln_pg->title1 ?? 'পোর্টেবল মিনি জুসার ব্লেন্ডার' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    @php
        $information   = \App\Models\Information::first();
        // $__hasGtm / $__ttFromGtm / $__gtmId are shared by the global View composer (AppServiceProvider).
        $activeManuals = \App\Models\ManualPayment::where('status', 1)->get();
        // Juicy Boost theme deep colors
        $brandColor = $ln_pg->theme_primary_col ?? '#ec4899';
        $btnBg      = $ln_pg->btn_bg_color ?? '#e11d48';
        $btnTextCol = $ln_pg->btn_text_color ?? '#ffffff';
        $cdBg       = $ln_pg->countdown_bg_color ?? '#dc2626';
        $cdTxt      = $ln_pg->countdown_text_color ?? '#ffffff';
        $cdHours    = (int)($ln_pg->countdown_hours ?? 12);

        $productId   = $product->id ?? 0;
        $productName = $product->name ?? 'পোর্টেবল মিনি জুসার ব্লেন্ডার';
        // $lpDisplayPrice comes from the controller and is the exact figure the
        // server sent to Meta's CAPI — package price first, then new_price,
        // then the product's own price. Working it out again here (without the
        // packages) made the browser pixel disagree with the server whenever a
        // landing page had packages configured.
        $defaultPrice = (float) ($lpDisplayPrice
            ?? (!empty($ln_pg->new_price)
                ? $ln_pg->new_price
                : (($product && $product->after_discount > 0) ? $product->after_discount : ($product->sell_price ?? 0))));
        $oldPrice = $ln_pg->old_price ?? '';

        $variations = collect();
        if($product){
            try { $product->loadMissing(['variations.size','variations.color','variations.stocks','category']); $variations = $product->variations ?? collect(); }
            catch(\Throwable $e) { $variations = $product->variations ?? collect(); }
        }
        $defaultVar   = $variations->first();
        $defaultStock = $defaultVar ? (int) $defaultVar->stocks->sum('quantity') : ($product ? max((int) ($product->stock_quantity ?? 0), (int) $product->stocks()->sum('quantity')) : 0);

        $phoneNumber = $ln_pg->phone ?? optional($information)->phone ?? '';
        $heroImage = !empty($ln_pg->right_product_image)
            ? asset('landing_pages/'.$ln_pg->right_product_image)
            : (($product && $product->image) ? getImage('products', $product->image) : asset('frontend/images/no-image.png'));
        $isFreeShipping = (!empty($product->is_free_shipping) && $product->is_free_shipping == 1) ? 1 : 0;
        $pixelId = setting('fb_pixel_id') ?? null;
        $ttPixelId       = setting('tt_pixel_id') ?? null;
        $contentCategory = optional(optional($product ?? null)->category)->name ?? 'Landing Page';
        // Ids the server already used for the Meta Conversions API + TikTok Events API.
        $lpEventBase = $lpEventBase ?? ('LP10_' . $productId . '_' . now()->format('YmdHis'));
        $lpEventIdVC = $lpEventIdVC ?? ($lpEventBase . '_VC');
        $lpEventIdIC = $lpEventIdIC ?? ($lpEventBase . '_IC');
        
        // Icon sanitize helper
        $cleanIcon = function($raw, $default) {
            $fa6to5 = [
                'fa-jar' => 'fa-flask', 'fa-flower' => 'fa-leaf', 'fa-bottle-water' => 'fa-wine-bottle',
                'fa-face-smile' => 'fa-smile', 'fa-face-grin' => 'fa-grin', 'fa-mug-tea' => 'fa-coffee',
                'fa-house' => 'fa-home', 'fa-arrows-up-down-left-right' => 'fa-arrows-alt',
                'fa-rotate' => 'fa-sync', 'fa-xmark' => 'fa-times',
                'fa-magnifying-glass' => 'fa-search', 'fa-circle-info' => 'fa-info-circle',
                'fa-circle-check' => 'fa-check-circle', 'fa-trash-can' => 'fa-trash',
                'fa-pen-to-square' => 'fa-edit', 'fa-droplet' => 'fa-tint',
                'fa-fire-flame-curved' => 'fa-fire', 'fa-cart-shopping' => 'fa-shopping-cart',
                'fa-shield-halved' => 'fa-shield-alt', 'fa-truck-fast' => 'fa-shipping-fast',
            ];
            if (empty($raw)) return $default;
            $raw = trim($raw);
            $raw = preg_replace('/\b(fas|far|fab|fa-solid|fa-regular|fa-brands)\s+/i', '', $raw);
            $raw = trim($raw);
            if (!str_starts_with($raw, 'fa-')) return $default;
            if (isset($fa6to5[$raw])) return $fa6to5[$raw];
            return $raw;
        };
        $defaultFeatIcons = ['fa-bolt','fa-battery-full','fa-feather-alt','fa-charging-station','fa-shield-alt','fa-volume-mute','fa-star','fa-gem'];
    @endphp

    {!! optional($information)->tracking_code !!}

    @if($pixelId)
    <script>
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ $pixelId }}');
        @if(!$__hasGtm)
        fbq('track', 'PageView');
        @endif
    </script>
    @endif

    {{-- TikTok Pixel --}}
    @if(!empty($ttPixelId))
    <script>
    !function (w, d, t) { w.TiktokAnalyticsObject = t; var ttq = w[t] = w[t] || []; ttq.methods = ["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"]; ttq.setAndDefer = function (t, e) { t[e] = function () { t.push([e].concat(Array.prototype.slice.call(arguments, 0))); }; }; for (var i = 0; i < ttq.methods.length; i++) ttq.setAndDefer(ttq, ttq.methods[i]); ttq.instance = function (t) { for (var e = ttq._i[t] || [], n = 0; n < ttq.methods.length; n++) ttq.setAndDefer(e, ttq.methods[n]); return e; }; ttq.load = function (e, n) { var r = "https://analytics.tiktok.com/i18n/pixel/events.js"; var o = n && n.partner; ttq._i = ttq._i || {}; ttq._i[e] = []; ttq._i[e]._u = r; ttq._t = ttq._t || {}; ttq._t[e] = +new Date; ttq._o = ttq._o || {}; ttq._o[e] = n || {}; var s = document.createElement("script"); s.type = "text/javascript"; s.async = !0; s.src = r + "?sdkid=" + e + "&lib=" + t; var x = document.getElementsByTagName("script")[0]; x.parentNode.insertBefore(s, x); }; ttq.load('{{ $ttPixelId }}'); @if(!$__ttFromGtm)ttq.page();@endif }(window, document, 'ttq');
    </script>
    @endif

    {{-- Browser events reuse the ids the server already sent to the Meta Conversions API
         and the TikTok Events API, so browser/server pairs deduplicate. GA4 (dataLayer)
         pushes are never gated on the GTM source flags. --}}
    <script>
    (function(){
        window.LP_EVENT_BASE  = {!! json_encode($lpEventBase) !!};
        window.LP_EVENT_ID_VC = {!! json_encode($lpEventIdVC) !!};
        window.LP_EVENT_ID_IC = {!! json_encode($lpEventIdIC) !!};

        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({ ecommerce: null });
        window.dataLayer.push({
            event: 'view_item',
            event_id: '{{ $lpEventIdVC }}',
            ecommerce: { currency: 'BDT', value: {{ (float)$defaultPrice }}, items: [{ item_id: '{{ $productId }}', item_name: @json($productName), item_category: @json($contentCategory), price: {{ (float)$defaultPrice }}, quantity: 1 }] }
        });
        window.dataLayer.push({ ecommerce: null });
        window.dataLayer.push({
            event: 'begin_checkout',
            event_id: '{{ $lpEventIdIC }}',
            ecommerce: { currency: 'BDT', value: {{ (float)$defaultPrice }}, items: [{ item_id: '{{ $productId }}', item_name: @json($productName), item_category: @json($contentCategory), price: {{ (float)$defaultPrice }}, quantity: 1 }] }
        });

        @if(!$__hasGtm)
        if (typeof fbq === 'function') {
            fbq('track', 'ViewContent', { content_ids: ['{{ $productId }}'], content_name: @json($productName), content_type: 'product', content_category: @json($contentCategory), currency: 'BDT', value: {{ (float)$defaultPrice }} }, { eventID: window.LP_EVENT_ID_VC });
            fbq('track', 'InitiateCheckout', { content_ids: ['{{ $productId }}'], content_name: @json($productName), content_type: 'product', currency: 'BDT', value: {{ (float)$defaultPrice }}, num_items: 1 }, { eventID: window.LP_EVENT_ID_IC });
        }
        @endif

        @if(!$__ttFromGtm)
        if (typeof ttq !== 'undefined' && ttq.track) {
            ttq.track('ViewContent', { content_type: 'product', value: {{ (float)$defaultPrice }}, currency: 'BDT', contents: [{ content_id: '{{ $productId }}', content_type: 'product', content_name: @json($productName), price: {{ (float)$defaultPrice }}, quantity: 1 }] }, { event_id: '{{ $lpEventIdVC }}' });
            ttq.track('InitiateCheckout', { content_type: 'product', value: {{ (float)$defaultPrice }}, currency: 'BDT', contents: [{ content_id: '{{ $productId }}', content_type: 'product', content_name: @json($productName), price: {{ (float)$defaultPrice }}, quantity: 1 }] }, { event_id: '{{ $lpEventIdIC }}' });
        }
        @endif
    })();
    </script>

    <style>
        body, h1,h2,h3,h4,h5,h6, p, div, span, a, button, input, select, textarea, label { font-family: 'Hind Siliguri', sans-serif; }
        .fas,.far,.fa,.fab { font-family: "Font Awesome 5 Free" !important; }
        .fab { font-family: "Font Awesome 5 Brands" !important; }
        :root {
            --brand: {{ $brandColor }}; --btn-bg: {{ $btnBg }}; --btn-text: {{ $btnTextCol }};
            /* Deepened theme colors */
            --peach-50: #fff0e5; --peach-100: #ffe3cd; --peach-200: #fcdcb6;
            --peach-300: #fbd9b9; --rose-500: #f43f5e; --rose-600: #e11d48; --rose-700: #be123c;
            --orange-100: #ffedd5; --orange-500: #f97316; --orange-600: #ea580c;
            --warm-bg: #fffbf8;
        }
        body { background: var(--warm-bg); color: #1f2937; line-height: 1.6; }

        /* ==== TOP COUNTDOWN BAR (RED) ==== */
        .top-countdown { background: {{ $cdBg }}; color: {{ $cdTxt }}; padding: 12px 0; text-align: center; font-weight: 800; font-size: 1.05rem; }
        .top-countdown .timer { display: inline-flex; gap: 4px; margin-left: 8px; font-variant-numeric: tabular-nums; }
        .cd-box { background: rgba(0,0,0,0.25); padding: 2px 8px; border-radius: 4px; min-width: 36px; }

        /* ==== HERO SECTION (DEEP PEACH BG, BOLD TEXT) ==== */
        .hero { padding: 40px 0 50px; background: linear-gradient(180deg, var(--peach-100) 0%, var(--peach-50) 100%); text-align: center; }
        .hero-content { max-width: 760px; margin: 0 auto; padding: 0 16px; }
        
        .hero-title { font-size: 2.6rem; font-weight: 900; line-height: 1.25; color: #000; margin-bottom: 18px; letter-spacing: -0.5px; }
        .hero-title .accent { color: var(--rose-600); }
        .hero-sub { color: #111; font-size: 1.15rem; font-weight: 700; margin-bottom: 28px; max-width: 650px; margin-left: auto; margin-right: auto; line-height: 1.5; }
        
        .hero-video-wrap { max-width: 640px; margin: 0 auto 30px; background: transparent; padding: 0; box-shadow: none; border: none; }
        .hero-video-wrap .ratio { border-radius: 16px; overflow: hidden; box-shadow: 0 18px 40px rgba(0,0,0,0.15); }
        .hero-img-wrap { max-width: 480px; margin: 0 auto 30px; background: transparent; padding: 0; box-shadow: none; border: none; }
        .hero-img-wrap img { max-width: 100%; max-height: 400px; object-fit: contain; border-radius: 16px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
        
        .hero-ctas { display: inline-flex; gap: 14px; flex-wrap: wrap; justify-content: center; align-items: center; }
        .btn-primary-cta {
            background: linear-gradient(135deg, var(--rose-500), var(--rose-600));
            color: #fff; border: none; border-radius: 50px; padding: 16px 32px;
            font-weight: 800; font-size: 1.1rem; text-decoration: none;
            display: inline-flex; align-items: center; gap: 10px;
            box-shadow: 0 12px 24px rgba(225,29,72,0.35); transition: all .2s ease;
        }
        .btn-primary-cta:hover { color: #fff; transform: translateY(-3px); box-shadow: 0 16px 32px rgba(225,29,72,0.45); }
        .btn-secondary-link { color: #000; background: #fff; border-radius: 50px; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; padding: 14px 24px; box-shadow: 0 8px 20px rgba(0,0,0,0.06); }
        .btn-secondary-link:hover { color: var(--rose-600); }

        /* ==== SECTION COMMON ==== */
        .sec { padding: 55px 0; }
        .sec-title-wrap { text-align: center; margin-bottom: 36px; }
        .sec-title { font-size: 1.8rem; font-weight: 900; color: #111; margin-bottom: 8px; }
        .sec-sub { color: #444; font-size: 1.05rem; font-weight: 600; max-width: 600px; margin: 0 auto; }

        /* ==== WHY SPECIAL (6 CARDS) ==== */
        .why-special { background: #fff; }
        .feat-card { background: #fff; border-radius: 16px; padding: 24px 20px; height: 100%; box-shadow: 0 4px 16px rgba(0,0,0,0.05); border: 1px solid #f3f4f6; transition: all .3s ease; }
        .feat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(228,68,68,0.15); border-color: var(--peach-200); }
        .feat-icon { width: 54px; height: 54px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 16px; }
        .feat-card:nth-child(odd) .feat-icon { background: #fff0f2; color: var(--rose-600); }
        .feat-card:nth-child(even) .feat-icon { background: var(--orange-100); color: var(--orange-600); }
        .feat-title { font-weight: 800; font-size: 1.1rem; margin-bottom: 8px; color: #111; }
        .feat-desc { color: #555; font-size: 0.95rem; margin: 0; font-weight: 500; }

        /* ==== USE CASES (DEEP PEACH BLOCK) ==== */
        .use-cases { background: var(--peach-300); padding: 50px 0; }
        .uc-inner { max-width: 800px; margin: 0 auto; padding: 0 20px; text-align: center; }
        .uc-title { color: #9f1239; font-weight: 900; font-size: 1.6rem; margin-bottom: 16px; }
        .uc-title .accent { color: var(--rose-700); }
        .uc-text { color: #222; font-size: 1.1rem; line-height: 1.8; font-weight: 600; }

        /* ==== SPECIFICATION (2-COL) ==== */
        .spec-sec { background: #fff; }
        .spec-row-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center; max-width: 980px; margin: 0 auto; }
        @media (max-width: 768px) { .spec-row-grid { grid-template-columns: 1fr; gap: 24px; } }
        .spec-img-box { background: var(--peach-50); border-radius: 20px; padding: 30px; text-align: center; border: 1px solid var(--peach-200); }
        .spec-img-box img { max-width: 100%; max-height: 360px; object-fit: contain; }
        .spec-card { background: #fff; border-radius: 16px; box-shadow: 0 6px 20px rgba(0,0,0,0.06); overflow: hidden; border: 1px solid #f3f4f6; }
        .spec-row { display: flex; padding: 14px 20px; border-bottom: 1px solid #f3f4f6; }
        .spec-row:last-child { border-bottom: 0; }
        .spec-row:nth-child(even) { background: var(--peach-50); }
        .spec-label { flex: 0 0 45%; font-weight: 800; color: #333; font-size: 1rem; }
        .spec-value { flex: 1; color: #111; font-size: 1rem; text-align: right; font-weight: 600;}

        /* ==== KITCHEN APPLICATIONS (6 CARDS) ==== */
        .kitchen-apps { background: var(--peach-50); }
        .kapp-card { background: #fff; border-radius: 16px; padding: 24px 20px; height: 100%; box-shadow: 0 4px 14px rgba(0,0,0,0.05); border: 1px solid var(--peach-200); transition: all .3s ease; }
        .kapp-card:hover { transform: translateY(-4px); box-shadow: 0 14px 28px rgba(228,68,68,0.12); }
        .kapp-icon { width: 48px; height: 48px; border-radius: 12px; background: #fff0f2; color: var(--rose-600); display: inline-flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 14px; }
        .kapp-title { font-weight: 800; font-size: 1.05rem; margin-bottom: 6px; color: #111; }
        .kapp-desc { color: #555; font-size: 0.9rem; margin: 0; font-weight: 500;}

        /* ==== REVIEWS ==== */
        .reviews-sec { background: #fff; }
        .review-card { background: var(--peach-50); border-radius: 16px; padding: 26px 22px; height: 100%; border: 1px solid var(--peach-200); }
        .review-stars { color: #f59e0b; margin-bottom: 12px; font-size: 1rem; }
        .review-text { color: #333; font-size: 0.98rem; line-height: 1.7; margin-bottom: 16px; font-weight: 600; }
        .reviewer-name { font-weight: 800; color: #111; font-size: 1rem; }
        .reviewer-loc { color: #6b7280; font-size: 0.85rem; font-weight: 600; }

        /* ==== ORDER FORM ==== */
        .order-form-section { background: var(--peach-50); padding: 50px 0; }
        .order-card { background: #fff; border-radius: 20px; padding: 30px; box-shadow: 0 15px 40px rgba(228,68,68,0.12); max-width: 560px; margin: 0 auto; border: 2px solid var(--peach-200); }
        .form-control, .form-select { border-radius: 10px; padding: 12px 16px; border: 1px solid #d1d5db; background: #fff; font-size: 0.98rem; font-weight: 500; }
        .form-control:focus, .form-select:focus { border-color: var(--rose-500); box-shadow: 0 0 0 3px rgba(244,63,94,0.15); }
        .form-label { font-weight: 800; color: #111; margin-bottom: 8px; font-size: 0.95rem; }

        /* Delivery radio */
        .delivery-radio-group { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .delivery-radio-box { display: flex; align-items: center; gap: 12px; padding: 14px 16px; border: 2px solid #e5e7eb; border-radius: 12px; cursor: pointer; transition: all .2s ease; background: #fff; }
        .delivery-radio-box.active { border-color: var(--rose-500); background: var(--peach-50); }
        .delivery-radio-box input { accent-color: var(--rose-500); width: 18px; height: 18px; }
        .delivery-radio-box .label-text { display: flex; flex-direction: column; }
        .delivery-radio-box .area { font-weight: 800; font-size: 0.95rem; color: #111; }
        .delivery-radio-box .charge { color: var(--rose-600); font-size: 0.85rem; font-weight: 800; }

        /* Package card */
        .package-card { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 14px 16px; border: 2px solid #e5e7eb; border-radius: 12px; margin-bottom: 10px; cursor: pointer; transition: all .2s ease; background: #fff; }
        .package-card.active-pkg { border-color: var(--rose-500); background: var(--peach-50); box-shadow: 0 4px 12px rgba(228,68,68,0.1); }
        .package-card .pkg-left { display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0; }
        .package-card .pkg-right { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
        .pkg-radio { width: 18px; height: 18px; accent-color: var(--rose-500); cursor: pointer; flex-shrink: 0; }
        .pkg-title { font-weight: 800; font-size: 0.95rem; color: #111; }
        .pkg-qty-box { display: inline-flex; align-items: center; justify-content: center; border: 1px solid #e5e7eb; border-radius: 8px; background: #f9fafb; min-width: 36px; height: 32px; font-weight: 800; color: var(--rose-600); font-size: 0.9rem; }
        .pkg-price { font-weight: 900; color: var(--rose-600); font-size: 1.05rem; }
        @media (max-width: 480px) {
            .package-card { flex-direction: column; align-items: flex-start; gap: 10px; }
            .package-card .pkg-right { width: 100%; justify-content: space-between; }
            .delivery-radio-group { grid-template-columns: 1fr; }
        }

        /* Qty selector */
        .qty-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 12px; background: #fff; }
        .pro-qty { display: inline-flex; border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden; }
        .pro-qty span { padding: 8px 16px; cursor: pointer; user-select: none; font-weight: 900; background: #f3f4f6; color: var(--rose-600); font-size: 1.1rem; }
        .pro-qty input { width: 50px; border: none; text-align: center; font-weight: 800; font-size: 1rem; }

        /* Price summary table */
        .price-summary { background: var(--peach-50); border-radius: 12px; padding: 16px 20px; margin: 16px 0; border: 1px solid var(--peach-200); }
        .price-summary .row-line { display: flex; justify-content: space-between; padding: 4px 0; color: #333; font-size: 0.95rem; font-weight: 700; }
        .price-summary .grand { font-size: 1.15rem; color: var(--rose-700); border-top: 2px dashed var(--peach-200); margin-top: 8px; padding-top: 12px; font-weight: 900; }

        /* Payment methods */
        .payment-radio-box { display: flex; align-items: center; gap: 12px; padding: 14px 16px; border: 2px solid #e5e7eb; border-radius: 12px; margin-bottom: 8px; cursor: pointer; background: #fff; font-size: 0.95rem; }
        .payment-radio-box.active { border-color: var(--rose-500); background: var(--peach-50); }
        .payment-radio-box input { accent-color: var(--rose-500); width: 18px; height: 18px; }

        /* Final order button */
        .btn-order-confirm {
            width: 100%; background: linear-gradient(135deg, var(--rose-500), var(--rose-600));
            color: #fff; border: none; border-radius: 14px; padding: 18px;
            font-weight: 900; font-size: 1.2rem; box-shadow: 0 12px 28px rgba(225,29,72,0.35);
            transition: all .3s ease; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .btn-order-confirm:hover { transform: translateY(-3px); box-shadow: 0 16px 32px rgba(225,29,72,0.45); }
        .btn-order-confirm:disabled { opacity: 0.7; cursor: wait; }

        /* Return notice */
        .return-notice {
            background: #fffbdf; color: #b45309;
            border-radius: 12px; padding: 14px;
            font-size: 0.95rem; font-weight: 800;
            text-align: center; margin-top: 16px;
            border: 2px dashed #fcd34d;
        }

        /* ==== FAQ ==== */
        .faq-sec { background: var(--peach-50); }
        .faq-card { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 12px; border: 1px solid var(--peach-200); }
        .faq-q { padding: 18px 20px; cursor: pointer; font-weight: 800; display: flex; justify-content: space-between; align-items: center; color: #111; font-size: 1rem; }
        .faq-q i { color: var(--rose-600); transition: transform .3s ease; }
        .faq-card.open .faq-q i { transform: rotate(180deg); }
        .faq-a { padding: 0 20px 18px; color: #444; display: none; line-height: 1.7; font-size: 0.95rem; font-weight: 500; }
        .faq-card.open .faq-a { display: block; }

        /* ==== FOOTER ==== */
        footer {
            background: transparent;
            padding: 30px 0 110px; /* added bottom padding to prevent overlap with sticky mobile buttons */
            text-align: center;
            border-top: none;
        }
        footer .fcompany { font-size: 1.6rem; font-weight: 900; color: #000; margin-bottom: 15px; }
        footer .contact-info { margin-bottom: 20px; }
        footer .contact-info a { color: #333; text-decoration: none; font-weight: 700; font-size: 1.05rem; display: inline-block; margin: 0 10px; transition: color 0.3s; }
        footer .contact-info a:hover { color: var(--rose-600); }
        footer .copyright { color: #6b7280; font-size: 0.95rem; font-weight: 600; }

        /* Mobile Adjustments & Sticky CTA */
        @media (max-width: 768px) {
            .top-countdown { padding: 10px 6px; font-size: 0.9rem; }
            .hero { padding: 30px 0 40px; }
            .hero-title { font-size: 1.8rem; }
            .hero-sub { font-size: 1rem; }
            .btn-primary-cta, .btn-secondary-link { width: 100%; justify-content: center; padding: 14px 20px; }
            .sec { padding: 40px 0; }
            .sec-title { font-size: 1.5rem; }
            .order-card { padding: 20px; }
            
            /* ✅ MOBILE STICKY TWO BUTTONS */
            .mobile-sticky { position: fixed; bottom: 0; left: 0; right: 0; z-index: 999; background: #fff; padding: 12px; box-shadow: 0 -4px 15px rgba(0,0,0,0.1); border-top: 1px solid #f3f4f6; }
            .sticky-btn-group { display: flex; gap: 10px; width: 100%; justify-content: space-between; }
            .btn-outline-sticky, .btn-primary-sticky {
                flex: 1; display: inline-flex; justify-content: center; align-items: center; gap: 6px;
                padding: 12px 8px; font-weight: 800; font-size: 1rem; border-radius: 50px; text-decoration: none; text-align: center;
                transition: all 0.2s ease;
            }
            .btn-outline-sticky { background: #fff; border: 2px solid var(--rose-600); color: var(--rose-600); }
            .btn-outline-sticky:active { background: #fff0f2; }
            .btn-primary-sticky { background: linear-gradient(135deg, var(--rose-500), var(--rose-600)); color: #fff; border: 2px solid transparent; }
            .btn-primary-sticky:active { transform: scale(0.98); }
        }
        @media (min-width: 769px) { .mobile-sticky { display: none; } }
        
        /* ✅ GALLERY CSS ADDITIONS */
        .carousel-item { transition: transform 0.6s ease-in-out; }
        
        /* ✅ Single variation hide */
        .single-variation .variation-wrap { display: none !important; }
        .variation-wrap.single-product-hidden { display: none !important; }
        
        /* ✅ UNIVERSAL MOBILE IMAGE FIX */
        body { overflow-x: hidden !important; max-width: 100vw !important; }
        img { max-width: 100% !important; height: auto !important; }
        * { box-sizing: border-box; }
        .hero-img-wrap, .hero-video-wrap { overflow: hidden !important; width: 100% !important; box-sizing: border-box !important; }
        .hero-img-wrap img { max-width: 100% !important; width: auto !important; height: auto !important; object-fit: contain !important; display: block !important; margin: 0 auto !important; }
        .hero-video-wrap .ratio, .hero-video-wrap iframe { width: 100% !important; max-width: 100% !important; }
        @media (max-width: 768px) {
            .hero, .container, .row, section { overflow-x: hidden !important; }
            .hero-img-wrap, .hero-video-wrap { padding: 0 !important; margin-left: 0 !important; margin-right: 0 !important; }
            .hero-img-wrap img { max-height: 280px !important; width: 100% !important; }
            .hero-video-wrap iframe { max-height: 240px !important; }
            .row { margin-left: 0 !important; margin-right: 0 !important; }
            .col-md-6, .col-lg-6, .col-12 { padding-left: 12px !important; padding-right: 12px !important; }
        }
    </style>
</head>
<body>
@if(!empty($__gtmId))
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $__gtmId }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
@endif

{{-- TOP COUNTDOWN BAR --}}
@if($ln_pg->countdown_title)
<div class="top-countdown">
    <i class="fas fa-bullhorn me-1"></i>
    <span>{{ $ln_pg->countdown_title }}</span>
    <span class="timer">
        <span class="cd-box" id="cd-h">00</span>:
        <span class="cd-box" id="cd-m">00</span>:
        <span class="cd-box" id="cd-s">00</span>
    </span>
</div>
@endif

{{-- HERO (CENTERED) --}}
<section class="hero">
    <div class="hero-content">

        <h1 class="hero-title">{{ $ln_pg->title1 ?? 'পোর্টেবল মিনি জুসার ব্লেন্ডার' }}</h1>
        @if($ln_pg->title2)<p class="hero-sub">{{ $ln_pg->title2 }}</p>@endif

        {{-- ✅ HERO IMAGE OR VIDEO --}}
        @if(!empty($ln_pg->video_url))
            <div class="hero-video-wrap">
                @php 
                    $videoUrl = trim($ln_pg->video_url); 
                    if (strpos($videoUrl, 'watch?v=') !== false) {
                        $videoUrl = str_replace('watch?v=', 'embed/', $videoUrl);
                    } elseif (strpos($videoUrl, 'youtu.be/') !== false) {
                        $videoUrl = str_replace('youtu.be/', 'youtube.com/embed/', $videoUrl);
                    }
                @endphp
                @if(stripos($videoUrl, '<iframe') !== false)
                    <div class="ratio ratio-16x9">{!! $videoUrl !!}</div>
                @else
                    <div class="ratio ratio-16x9"><iframe src="{{ $videoUrl }}" frameborder="0" allowfullscreen></iframe></div>
                @endif
            </div>
        @elseif(!empty($heroImage))
            <div class="hero-img-wrap"><img src="{{ $heroImage }}" alt="{{ $productName }}"></div>
        @endif

        <div class="hero-ctas">
            <a href="#order-form" class="btn-primary-cta">
                <i class="fas fa-shopping-cart me-2"></i> {{ $ln_pg->btn_text_hero ?? 'অর্ডার করতে ক্লিক করুন' }} — ৳{{ $defaultPrice }}
            </a>
            @if($phoneNumber)
            <a href="tel:{{ $phoneNumber }}" class="btn-secondary-link">
                <i class="fas fa-phone-alt me-2"></i> কল করুন
            </a>
            @endif
        </div>
    </div>
</section>

{{-- ✅ SMOOTH FULL-WIDTH GALLERY SLIDER (No Buttons, No White Borders) --}}
@if(isset($ln_pg->images) && $ln_pg->images->count() > 0)
<section class="sec" style="background:var(--warm-bg);">
    <div class="container">
        <div class="sec-title-wrap mb-4">
            <h2 class="sec-title">প্রোডাক্ট গ্যালারি</h2>
            <p class="sec-sub">বিভিন্ন অ্যাঙ্গেল থেকে দেখুন</p>
        </div>
        
        <div class="mx-auto" style="max-width: 650px; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div id="productGallerySlider" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                <div class="carousel-inner" style="border-radius: 16px;">
                    @foreach($ln_pg->images as $key => $img)
                        <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                            <img src="{{ asset('landing_sliders/'.$img->image) }}" class="d-block w-100" alt="Gallery {{ $key + 1 }}" style="width: 100%; max-height: 450px; object-fit: cover;">
                        </div>
                    @endforeach
                </div>
                {{-- Arrow Buttons Removed --}}
            </div>
        </div>
    </div>
</section>
@endif

{{-- WHY SPECIAL (6 FEATURES) --}}
@if($ln_pg->id_1_title || $ln_pg->id_2_title)
<section class="sec why-special">
    <div class="container" style="max-width: 800px;">
        <div class="sec-title-wrap">
            <h2 class="sec-title">{{ $ln_pg->feature_title ?? 'কেন এই ব্লেন্ডারটি বিশেষ?' }}</h2>
            <p class="sec-sub">আধুনিক টেকনোলজি, পার্সোনাল ব্যবহার ও জ্বালানির সমন্বয় — যা সব প্রয়োজনে পারফেক্ট</p>
        </div>
        <div class="row g-3 justify-content-center">
            @for($i=1; $i<=6; $i++)
                @if($ln_pg->{'id_'.$i.'_title'})
                <div class="col-6 col-md-6">
                    <div class="feat-card text-center d-flex flex-column align-items-center">
                        <div class="feat-icon"><i class="fas {{ $cleanIcon($ln_pg->{'id_'.$i.'_icon'}, $defaultFeatIcons[$i-1] ?? 'fa-check-circle') }}"></i></div>
                        <div class="feat-title">{{ $ln_pg->{'id_'.$i.'_title'} }}</div>
                        <p class="feat-desc">{{ $ln_pg->{'id_'.$i.'_desc'} }}</p>
                    </div>
                </div>
                @endif
            @endfor
        </div>
    </div>
</section>
@endif

{{-- USE CASES TEXT BLOCK --}}
@if(!empty($ln_pg->left_side_title) || !empty($ln_pg->left_side_desc))
<section class="use-cases">
    <div class="uc-inner">
        <h3 class="uc-title">{{ $ln_pg->left_side_title ?? 'গরমে যেখানেই যান, তাজা জুস সাথে নিন' }}</h3>
        <div class="uc-text">
            @if(!empty($ln_pg->left_side_desc))
                {!! $ln_pg->left_side_desc !!}
            @else
                অফিসে, কলেজে, জিমে, ট্রাভেলে, পিকনিকে — যেকোনো জায়গায় মাত্র এক চার্জেই ব্লেন্ডার নিয়ে যান। প্রিয় ফল, সবজি বা বাদাম দিয়ে যেকোনো জায়গায় তাজা জুস তৈরি করে নিন।
            @endif
        </div>
    </div>
</section>
@endif

{{-- SPECIFICATION (2-COL) --}}
@if($ln_pg->spec_1_label)
<section class="sec spec-sec">
    <div class="container">
        <div class="sec-title-wrap">
            <h2 class="sec-title">{{ $ln_pg->spec_title ?? 'প্রোডাক্ট স্পেসিফিকেশন' }}</h2>
        </div>
        <div class="spec-row-grid">
            <div class="spec-img-box"><img src="{{ $heroImage }}" alt="{{ $productName }}"></div>
            <div class="spec-card">
                @for($i=1; $i<=7; $i++)
                    @if($ln_pg->{'spec_'.$i.'_label'})
                    <div class="spec-row">
                        <div class="spec-label">{{ $ln_pg->{'spec_'.$i.'_label'} }}</div>
                        <div class="spec-value">{{ $ln_pg->{'spec_'.$i.'_value'} }}</div>
                    </div>
                    @endif
                @endfor
            </div>
        </div>
    </div>
</section>
@endif

{{-- KITCHEN APPLICATIONS (6 CARDS) --}}
@if($ln_pg->id_7_title || $ln_pg->promise_1_title)
<section class="sec kitchen-apps">
    <div class="container" style="max-width: 800px;">
        <div class="sec-title-wrap">
            <h2 class="sec-title">শুধু বাইরে নয় — <span style="color:var(--rose-600);">বাসার কাজেও সমান উপযোগী</span></h2>
            <p class="sec-sub">এই মিনি ব্লেন্ডারটি শুধু জুসের জন্য নয় — এটি সংসারের অনেক কাজে ব্যবহারযোগ্য</p>
        </div>
        <div class="row g-3 justify-content-center">
            @if($ln_pg->id_7_title)
            <div class="col-6 col-md-6"><div class="kapp-card text-center d-flex flex-column align-items-center"><div class="kapp-icon"><i class="fas {{ $ln_pg->id_7_icon ?? 'fa-mortar-pestle' }}"></i></div><div class="kapp-title">{{ $ln_pg->id_7_title }}</div><p class="kapp-desc">{{ $ln_pg->id_7_desc }}</p></div></div>
            @endif
            @if($ln_pg->id_8_title)
            <div class="col-6 col-md-6"><div class="kapp-card text-center d-flex flex-column align-items-center"><div class="kapp-icon"><i class="fas {{ $ln_pg->id_8_icon ?? 'fa-baby' }}"></i></div><div class="kapp-title">{{ $ln_pg->id_8_title }}</div><p class="kapp-desc">{{ $ln_pg->id_8_desc }}</p></div></div>
            @endif
            @if($ln_pg->promise_1_title)
            <div class="col-6 col-md-6"><div class="kapp-card text-center d-flex flex-column align-items-center"><div class="kapp-icon"><i class="fas fa-smile"></i></div><div class="kapp-title">{{ $ln_pg->promise_1_title }}</div><p class="kapp-desc">{{ $ln_pg->promise_1_desc }}</p></div></div>
            @endif
            @if($ln_pg->promise_2_title)
            <div class="col-6 col-md-6"><div class="kapp-card text-center d-flex flex-column align-items-center"><div class="kapp-icon"><i class="fas fa-graduation-cap"></i></div><div class="kapp-title">{{ $ln_pg->promise_2_title }}</div><p class="kapp-desc">{{ $ln_pg->promise_2_desc }}</p></div></div>
            @endif
            @if($ln_pg->promise_3_title && $ln_pg->promise_3_title !== 'kitchen_apps_marker')
            <div class="col-6 col-md-6"><div class="kapp-card text-center d-flex flex-column align-items-center"><div class="kapp-icon"><i class="fas fa-dumbbell"></i></div><div class="kapp-title">{{ $ln_pg->promise_3_title }}</div><p class="kapp-desc">{{ $ln_pg->promise_3_desc }}</p></div></div>
            @endif
        </div>
    </div>
</section>
@endif

{{-- REVIEWS --}}
@if($ln_pg->rev_1_text)
<section class="sec reviews-sec">
    <div class="container">
        <div class="sec-title-wrap">
            <h2 class="sec-title">{{ $ln_pg->review_title ?? 'কাস্টমার রিভিউ' }}</h2>
            @if($ln_pg->review_subtitle)<p class="sec-sub">{{ $ln_pg->review_subtitle }}</p>@endif
        </div>
        <div class="row g-3">
            @for($i=1; $i<=3; $i++)
                @if($ln_pg->{'rev_'.$i.'_text'})
                <div class="col-md-4">
                    <div class="review-card">
                        <div class="review-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <p class="review-text">"{{ $ln_pg->{'rev_'.$i.'_text'} }}"</p>
                        <div class="reviewer-name">— {{ $ln_pg->{'rev_'.$i.'_name'} }}</div>
                        <div class="reviewer-loc"><i class="fas fa-map-marker-alt me-1"></i>{{ $ln_pg->{'rev_'.$i.'_loc'} }}</div>
                    </div>
                </div>
                @endif
            @endfor
        </div>
    </div>
</section>
@endif

{{-- ORDER FORM --}}
<section class="order-form-section" id="order-form">
    <div class="container">
        <div class="sec-title-wrap">
            <h2 class="sec-title">{{ $ln_pg->form_title ?? 'অর্ডার করুন' }}</h2>
            <p class="sec-sub">{{ $ln_pg->form_subtitle ?? 'নিচের ফর্মটি সঠিকভাবে পূরণ করুন — আমাদের কাস্টমারকেয়ার থেকে কল আসবে' }}</p>
        </div>

        <div class="order-card">
            <form id="checkout_land_form" action="{{ route('front.storelandData') }}" method="POST">
                @csrf
                        {{-- কোন landing page থেকে order এলো (Order Management-এর Source-এ দেখায়) --}}
                        <input type="hidden" name="landing_page_type" value="10">
                <input type="hidden" name="prd_id" value="{{ $productId }}">
                <input type="hidden" name="amount" id="subtotal_input" value="{{ $defaultPrice }}">
                <input type="hidden" name="final_amount" id="final_total_input" value="{{ $defaultPrice }}">
                <input type="hidden" name="quantity" id="form_qty" value="1">
                <input type="hidden" name="purchase_event_id" id="purchase_event_id" value="">
                <input type="hidden" name="coupon_code" id="hidden_coupon_code" value="">
                <input type="hidden" name="discount" id="hidden_discount" value="0">

                <div class="mb-3">
                    <label class="form-label">{{ $ln_pg->name_label ?? 'আপনার নাম *' }}</label>
                    <input type="text" name="first_name" class="form-control" placeholder="{{ $ln_pg->name_placeholder ?? 'পূর্ণ নাম লিখুন' }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ $ln_pg->phone_label ?? 'মোবাইল নাম্বার *' }}</label>
                    <input type="tel" name="mobile" class="form-control" placeholder="01XXXXXXXXX" maxlength="11" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">{{ $ln_pg->address_label ?? 'সম্পূর্ণ ঠিকানা *' }}</label>
                    <textarea name="shipping_address" class="form-control" rows="2" placeholder="{{ $ln_pg->address_placeholder ?? 'বাসা/হোল্ডিং, রোড, এলাকা, থানা, জেলা' }}" required></textarea>
                </div>

                {{-- Delivery Area as RADIO buttons --}}
                <div class="mb-4">
                    <label class="form-label">{{ $ln_pg->delivery_label ?? 'ডেলিভারি এরিয়া *' }}</label>
                    <div class="delivery-radio-group">
                        @foreach($charges as $idx => $ch)
                        <label class="delivery-radio-box {{ $idx == 0 ? 'active' : '' }}">
                            <input type="radio" name="delivery_charge_id" value="{{ $ch->id }}" data-charge="{{ $isFreeShipping ? 0 : intval($ch->amount) }}" {{ $idx == 0 ? 'checked' : '' }} required>
                            <span class="label-text">
                                <span class="area">{{ $ch->title }}</span>
                                <span class="charge">{!! $isFreeShipping ? 'ফ্রি' : intval($ch->amount).' ৳' !!}</span>
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Package Selection --}}
                <div class="mb-4">
                    <label class="form-label">প্যাকেজ নির্বাচন করুন:</label>
                    <label class="package-card active-pkg">
                        <div class="pkg-left">
                            <input type="radio" name="selected_package_id" value="" data-price="{{ $defaultPrice }}" data-qty="1" class="pkg-radio" checked autocomplete="off">
                            <span class="pkg-title">(১ পিস) {{ $productName }}</span>
                        </div>
                        <div class="pkg-right">
                            <div class="pkg-qty-box"><span>1</span></div>
                            <span class="pkg-price"><span id="regular_pkg_price_display">{{ $defaultPrice }}</span> ৳</span>
                        </div>
                    </label>
                    @if($ln_pg->packages && $ln_pg->packages->count() > 0)
                        @foreach($ln_pg->packages as $pkg)
                        <label class="package-card">
                            <div class="pkg-left">
                                <input type="radio" name="selected_package_id" value="{{ $pkg->id }}" data-price="{{ $pkg->price }}" data-qty="{{ $pkg->qty }}" class="pkg-radio" autocomplete="off">
                                <div>
                                    <span class="pkg-title">({{ $pkg->qty }} পিস) {{ $productName }}</span>
                                    @if($pkg->discount_text)<small class="d-block text-danger fw-bold mt-1">{{ $pkg->discount_text }}</small>@endif
                                </div>
                            </div>
                            <div class="pkg-right">
                                <div class="pkg-qty-box"><span>{{ $pkg->qty }}</span></div>
                                <span class="pkg-price">{{ intval($pkg->price) }} ৳</span>
                            </div>
                        </label>
                        @endforeach
                    @endif
                </div>

                {{-- Variations --}}
                @if($variations->count() > 0 && $variations->count() > 1)
                <div class="mb-4">
                    <label class="form-label">{{ $ln_pg->variation_label ?? 'ভেরিয়েশন *' }}</label>
                    <select name="variation_id" id="variation_select" class="form-select" required>
                        <option value="" disabled selected data-price="{{ $defaultPrice }}" data-stock="{{ $defaultStock }}">সিলেক্ট করুন...</option>
                        @foreach($variations as $v)
                            @php
                                $vBase = $v->price ?? $product->sell_price ?? 0;
                                $vDisc = $v->after_discount_price ?? null;
                                $vPrice = ((float)$vDisc > 0) ? $vDisc : $vBase;
                                $vStock = $v->stocks->sum('quantity');
                                $vLabel = trim(($v->size->name ?? '') . ' ' . ($v->color->name ?? ''));
                            @endphp
                            <option value="{{ $v->id }}" data-price="{{ $vPrice }}" data-stock="{{ $vStock }}">{{ $vLabel ?: ('Variation #'.$v->id) }}</option>
                        @endforeach
                    </select>
                </div>
                @elseif($variations->count() == 1)
                    @php
                        $sv = $variations->first();
                        $svBase = $sv->price ?? $product->sell_price ?? 0;
                        $svDisc = $sv->after_discount_price ?? null;
                        $svPrice = ((float)$svDisc > 0) ? $svDisc : $svBase;
                        $svStock = $sv->stocks->sum('quantity');
                    @endphp
                    <input type="hidden" name="variation_id" id="variation_select" value="{{ $sv->id }}" data-price="{{ $svPrice }}" data-stock="{{ $svStock }}">
                @else
                    <input type="hidden" name="variation_id" id="variation_select" value="">
                @endif
                <input type="hidden" id="max_stock" value="{{ $defaultStock }}">

                <div class="mb-4">
                    <label class="form-label">পরিমাণ</label>
                    <div class="qty-row">
                        <span style="font-size:0.95rem; color:#555; font-weight: 600;">অর্ডারের পরিমাণ পরিবর্তন করুন</span>
                        <div class="pro-qty">
                            <span class="decrease-qty">−</span>
                            <input type="text" class="inner_qty" value="1" readonly>
                            <span class="increase-qty">+</span>
                        </div>
                    </div>
                </div>

                {{-- Optional Note --}}
                <div class="mb-4">
                    <label class="form-label">নোট (ঐচ্ছিক)</label>
                    <textarea name="order_note" class="form-control" rows="2" placeholder="বিশেষ কোনো নির্দেশনা থাকলে লিখুন"></textarea>
                </div>

                {{-- Payment Methods --}}
                <div class="mb-4">
                    <label class="form-label">পেমেন্ট মাধ্যম</label>
                    @if(isset($information->cod_active) && $information->cod_active == 1)
                    <label class="payment-radio-box active">
                        <input type="radio" name="payment_method" value="cod" checked onchange="togglePaymentAction('cod')">
                        <i class="fas fa-money-bill-wave text-success" style="font-size: 1.2rem;"></i>
                        <span class="fw-bold">{{ $ln_pg->cod_title ?? 'ক্যাশ অন ডেলিভারি' }}</span>
                    </label>
                    @endif
                    @if(isset($information->ssl_active) && $information->ssl_active == 1)
                    <label class="payment-radio-box">
                        <input type="radio" name="payment_method" value="sslcommerz" onchange="togglePaymentAction('sslcommerz')">
                        <i class="fas fa-credit-card text-primary" style="font-size: 1.2rem;"></i>
                        <span class="fw-bold">অনলাইন পেমেন্ট (SSL)</span>
                    </label>
                    @endif
                    @if(isset($information->bkash_active) && $information->bkash_active == 1)
                    <label class="payment-radio-box">
                        <input type="radio" name="payment_method" value="bkash" onchange="togglePaymentAction('bkash')">
                        <span class="fw-bold">বিকাশ</span>
                    </label>
                    @endif
                    @if(isset($information->nagad_active) && $information->nagad_active == 1)
                    <label class="payment-radio-box">
                        <input type="radio" name="payment_method" value="nagad" onchange="togglePaymentAction('nagad')">
                        <span class="fw-bold">নগদ</span>
                    </label>
                    @endif
                </div>

                {{-- Coupon --}}
                @if(isset($information->coupon_visibility) && $information->coupon_visibility == 1)
                <div style="background:#f9fafb; border:1px dashed #e5e7eb; padding:12px; border-radius:10px; margin:12px 0;">
                    <div style="display:flex; overflow:hidden; border:1px solid #d1d5db; border-radius:8px; background:#fff;">
                        <input type="text" id="coupon_code" placeholder="কুপন কোড লিখুন" style="border:none; padding:9px 12px; flex-grow:1; outline:none; font-size:0.9rem; min-width:0;">
                        <button type="button" id="coupon_btn_submit" onclick="applyCouponLand()" style="border:none; background:#212529; color:#fff; padding:0 20px; font-weight:700; cursor:pointer; font-size:0.88rem;">APPLY</button>
                    </div>
                    <small id="coupon_msg" class="d-block mt-1 fw-bold" style="font-size:0.8rem;"></small>
                </div>
                @endif

                {{-- Price Summary --}}
                <div class="price-summary">
                    <div class="row-line"><span>ব্লেন্ডার (১×<span id="subtotal_disp">{{ $defaultPrice }}</span>):</span> <span>৳<span id="subtotal_disp2">{{ $defaultPrice }}</span></span></div>
                    <div class="row-line" id="discount_row" style="display:none;"><span>ছাড়:</span> <span class="text-danger">- ৳<span id="discount_disp">0</span></span></div>
                    <div class="row-line"><span>ডেলিভারি চার্জ:</span> <span>৳<span id="delivery_disp">0</span></span></div>
                    <div class="row-line grand"><span>{{ $ln_pg->total_bill_label ?? 'মোট বিল' }}:</span> <span>৳<span id="grand_total_disp">{{ $defaultPrice }}</span></span></div>
                </div>

                <button type="submit" id="submit_btn" class="btn-order-confirm">
                    <i class="fas fa-shopping-cart me-2"></i> {{ $ln_pg->btn_text_form ?? 'অর্ডার কনফার্ম করুন' }} — ৳<span id="btn_total">{{ $defaultPrice }}</span>
                </button>

                @if($ln_pg->security_badge_text)
                    <div class="return-notice"><i class="fas fa-undo me-1"></i> {{ $ln_pg->security_badge_text }}</div>
                @else
                    <div class="return-notice"><i class="fas fa-undo me-1"></i> এই অফারে — ১৪ দিন রিটার্ন গ্যারান্টি</div>
                @endif
            </form>
        </div>
    </div>
</section>

{{-- FAQ --}}
@if($ln_pg->faq_1_q)
<section class="sec faq-sec">
    <div class="container">
        <div class="sec-title-wrap">
            <h2 class="sec-title">{{ $ln_pg->faq_title ?? 'সাধারণ প্রশ্ন' }}</h2>
            <p class="sec-sub">আপনার জিজ্ঞাসার প্রয়োজনীয় উত্তর</p>
        </div>
        <div class="mx-auto" style="max-width:760px;">
            @for($i=1; $i<=4; $i++)
                @if($ln_pg->{'faq_'.$i.'_q'})
                <div class="faq-card">
                    <div class="faq-q" onclick="this.parentElement.classList.toggle('open')">
                        <span>{{ $ln_pg->{'faq_'.$i.'_q'} }}</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-a">{{ $ln_pg->{'faq_'.$i.'_a'} }}</div>
                </div>
                @endif
            @endfor
        </div>
    </div>
</section>
@endif

{{-- FOOTER (UPDATED DESIGN) --}}
<footer>
    <div class="container">
        @if($ln_pg->footer_company)<div class="fcompany">{{ $ln_pg->footer_company }}</div>@else <div class="fcompany">{{ $productName }}</div> @endif
        <div class="contact-info">
            @if($phoneNumber)<a href="tel:{{ $phoneNumber }}"><i class="fas fa-phone-alt me-2"></i>{{ $phoneNumber }}</a>@endif
            @if($ln_pg->footer_email) <span class="d-none d-md-inline text-muted mx-2">|</span> <a href="mailto:{{ $ln_pg->footer_email }}"><i class="fas fa-envelope me-2"></i>{{ $ln_pg->footer_email }}</a>@endif
        </div>
        @if($ln_pg->footer_copyright)
            <div class="copyright">{{ $ln_pg->footer_copyright }}</div>
        @else
            <div class="copyright">© 2024 All Rights Reserved.</div>
        @endif
    </div>
</footer>

{{-- ✅ REDESIGNED MOBILE STICKY CTA WITH TWO EQUAL BUTTONS --}}
<div class="mobile-sticky">
    <div class="sticky-btn-group">
        @if($phoneNumber)
        <a href="tel:{{ $phoneNumber }}" class="btn-outline-sticky">
            <i class="fas fa-phone-alt"></i> কল করুন
        </a>
        @endif
        
        <a href="#order-form" class="btn-primary-sticky">
            <i class="fas fa-shopping-cart"></i> অর্ডার করুন
        </a>
    </div>
</div>

<script>
    (function() {
        var hours = {{ $cdHours }};
        if(hours <= 0) return;
        var key = 'lp10_cd_end_{{ $ln_pg->id }}';
        var end = parseInt(localStorage.getItem(key) || '0');
        if(!end || end < Date.now()) { end = Date.now() + hours*3600*1000; localStorage.setItem(key, end); }
        function pad(n){ return n < 10 ? '0'+n : ''+n; }
        function tick() {
            var diff = end - Date.now();
            if(diff <= 0) { localStorage.removeItem(key); end = Date.now() + hours*3600*1000; localStorage.setItem(key, end); diff = end - Date.now(); }
            var h = Math.floor(diff/3600000), m = Math.floor((diff%3600000)/60000), s = Math.floor((diff%60000)/1000);
            var $h=document.getElementById('cd-h'), $m=document.getElementById('cd-m'), $s=document.getElementById('cd-s');
            if($h) $h.innerText=pad(h); if($m) $m.innerText=pad(m); if($s) $s.innerText=pad(s);
        }
        setInterval(tick, 1000); tick();
    })();

    // ✅ প্যাকেজ লজিক
    var hasPackages = {{ (isset($ln_pg->packages) && $ln_pg->packages->count() > 0) ? 'true' : 'false' }};
    
    function selectDefaultPackage() {
        var $def = $('input[name="selected_package_id"][value=""]');
        $('input[name="selected_package_id"]').prop('checked', false);
        $def.prop('checked', true);
        $('.package-card').removeClass('active-pkg');
        $def.closest('.package-card').addClass('active-pkg');
    }

    $(document).on('click', '.increase-qty', function() { 
        if(hasPackages) { toastr.warning('অনুগ্রহ করে প্যাকেজ সিলেক্ট করুন, পরিমাণ ম্যানুয়ালি পরিবর্তন করা যাবে না।'); return; }
        var $i=$(this).siblings('.inner_qty'); 
        $i.val(parseInt($i.val())+1); 
        selectDefaultPackage();
        recalc(); 
    });

    $(document).on('click', '.decrease-qty', function() { 
        if(hasPackages) { toastr.warning('অনুগ্রহ করে প্যাকেজ সিলেক্ট করুন, পরিমাণ ম্যানুয়ালি পরিবর্তন করা যাবে না।'); return; }
        var $i=$(this).siblings('.inner_qty'); 
        var v=parseInt($i.val()); 
        if(v>1) {
            $i.val(v-1); 
            selectDefaultPackage();
            recalc(); 
        }
    });

    function recalc() {
        var pkg = $('input[name="selected_package_id"]:checked');
        var qty = parseInt($('.inner_qty').val()) || 1;
        var unit;

        if (hasPackages) {
            unit = parseFloat(pkg.data('price')) || 0;
            qty = parseInt(pkg.data('qty')) || 1;
            $('.inner_qty').val(qty); 
            $('#form_qty').val(qty);
        } else {
            var v = $('#variation_select');
            var basePrice = parseFloat(v.find(':selected').data('price') || v.data('price') || {{ (float)$defaultPrice }});
            unit = basePrice * qty;
            $('#form_qty').val(qty);
        }

        var deliv = parseFloat($('input[name="delivery_charge_id"]:checked').data('charge') || 0);
        var discount = parseFloat($('#hidden_discount').val()) || 0;
        var grand = Math.max(0, unit - discount + deliv);
        
        $('#subtotal_input').val(unit); 
        $('#final_total_input').val(grand);
        
        $('#subtotal_disp').text(unit); 
        $('#subtotal_disp2').text(unit);
        $('#delivery_disp').text(deliv); 
        $('#grand_total_disp').text(grand);
        $('#btn_total').text(grand);
        
        if(discount > 0) { 
            $('#discount_row').show(); 
            $('#discount_disp').text(discount); 
        } else { 
            $('#discount_row').hide(); 
        }
        
        var v2 = $('#variation_select');
        var stock = parseInt(v2.length ? (v2.find(':selected').data('stock') || v2.data('stock')) : 0) || {{ (int)$defaultStock }};
        $('#max_stock').val(stock);
    }

    $(document).on('change', 'input[name="selected_package_id"]', function() {
        $('.package-card').removeClass('active-pkg');
        $(this).closest('.package-card').addClass('active-pkg');
        recalc();
    });
    $(document).on('change', 'input[name="delivery_charge_id"]', function() {
        $('.delivery-radio-box').removeClass('active');
        $(this).closest('.delivery-radio-box').addClass('active');
        recalc();
    });
    $(document).on('change', '#variation_select', recalc);
    $(document).ready(recalc);

    function togglePaymentAction(method) {
        $('.payment-radio-box').removeClass('active');
        $('input[name="payment_method"]:checked').closest('.payment-radio-box').addClass('active');
    }
    window.togglePaymentAction = togglePaymentAction;

    $('#checkout_land_form').submit(function(e) {
        e.preventDefault();
        var maxStock = parseInt($('#max_stock').val()) || 0;
        if(maxStock <= 0) { toastr.error('স্টকে নেই!'); return; }
        
        var purchaseId = "PUR_{{ $productId }}_" + Date.now();
        $('#purchase_event_id').val(purchaseId);
        
        var $btn = $('#submit_btn');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> প্রসেসিং...');
        
        var paymentMethod = $('input[name="payment_method"]:checked').val() || 'cod';
        if(paymentMethod === 'sslcommerz' || paymentMethod === 'online') { this.action = "{{ url('/pay') }}"; this.submit(); return; }
        
        $.ajax({
            url: "{{ route('front.storelandData') }}", method:'POST', data: $(this).serialize(),
            success: function(res) {
                if(res.success) {
                    toastr.success(res.msg);
                    // Purchase (Meta + GA4) fires on the thank-you page with 'PUR_' + order_id — the id the server Conversions API uses.
                    
                    if(paymentMethod === 'nagad') { var oid = res.order_id || res.url.split('/').pop(); window.location.href = "{{ url('nagad/pay') }}/" + oid; return; }
                    if(paymentMethod === 'bkash') { window.location.href = res.url; return; }
                    setTimeout(function() { window.location.href = res.url; }, 700);
                } else { 
                    toastr.error(res.msg || 'সমস্যা'); 
                    $btn.prop('disabled', false).html("<i class='fas fa-shopping-cart me-2'></i> {{ $ln_pg->btn_text_form ?? 'অর্ডার কনফার্ম করুন' }} — ৳" + $('#final_total_input').val()); 
                }
            },
            error: function() { 
                toastr.error('সার্ভারে সমস্যা'); 
                $btn.prop('disabled', false).html("<i class='fas fa-shopping-cart me-2'></i> {{ $ln_pg->btn_text_form ?? 'অর্ডার কনফার্ম করুন' }} — ৳" + $('#final_total_input').val()); 
            }
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
<script>
// Coupon apply — the page's recalc() already honours #hidden_discount;
// only the visible input and this function were missing.
function applyCouponLand() {
    var code = $.trim($('#coupon_code').val());
    if (!code) { $('#coupon_msg').text('কুপন কোড দিন').css('color', 'red'); return; }
    var unit = parseFloat($('#subtotal_input').val()) || 0;
    $('#coupon_btn_submit').text('...').prop('disabled', true);
    $.ajax({
        url: "{{ route('front.getCouponDiscount') }}",
        type: 'POST',
        data: { coupon_code: code, amount: unit, _token: "{{ csrf_token() }}" },
        success: function(res) {
            if (res.success) {
                $('#hidden_coupon_code').val(code);
                $('#hidden_discount').val(res.discount);
                $('#discount_disp').text(res.discount);
                $('#discount_row').show();
                $('#coupon_msg').text(res.msg).css('color', 'green');
            } else {
                $('#hidden_coupon_code').val('');
                $('#hidden_discount').val(0);
                $('#discount_row').hide();
                $('#coupon_msg').text(res.msg).css('color', 'red');
            }
            if (typeof recalc === 'function') recalc();
        },
        complete: function() { $('#coupon_btn_submit').text('APPLY').prop('disabled', false); }
    });
}
window.applyCouponLand = applyCouponLand;
</script>
    @include('frontend.partials.exit_popup', ['epLandingMode' => true])
</body>
</html>