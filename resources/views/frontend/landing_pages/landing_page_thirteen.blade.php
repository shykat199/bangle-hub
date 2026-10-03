<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $ln_pg->title1 ?? 'Honeyraj' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700;800&display=swap" rel="stylesheet">
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
        $brandColor = $ln_pg->theme_primary_col ?? '#d97706';
        $btnBg      = $ln_pg->btn_bg_color ?? '#f59e0b';
        $btnTextCol = $ln_pg->btn_text_color ?? '#78350f';
        $cdBg       = $ln_pg->countdown_bg_color ?? '#dc2626';
        $cdTxt      = $ln_pg->countdown_text_color ?? '#ffffff';
        $cdHours    = (int)($ln_pg->countdown_hours ?? 12);

        $productId   = $product->id ?? 0;
        $productName = $product->name ?? 'Honeyraj';
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
        $defaultStock = ($variations->first()) ? (int) $variations->first()->stocks->sum('quantity') : ($product ? max((int) ($product->stock_quantity ?? 0), (int) $product->stocks()->sum('quantity')) : 0);

        $phoneNumber = $ln_pg->phone ?? optional($information)->phone ?? '';
        
        // ✅ PERFECTED IMAGE LOGIC
        $productFallback = (!empty($product) && !empty($product->image)) ? getImage('products', $product->image) : asset('frontend/images/no-image.png');
        $heroImage = $productFallback;

        if (!empty($ln_pg->right_product_image)) {
            $heroImage = asset('landing_pages/' . str_replace('landing_pages/', '', $ln_pg->right_product_image));
        } elseif (!empty($ln_pg->image)) {
            $heroImage = asset('landing_pages/' . str_replace('landing_pages/', '', $ln_pg->image));
        }

        $isFreeShipping = (!empty($product->is_free_shipping) && $product->is_free_shipping == 1) ? 1 : 0;
        $pixelId = setting('fb_pixel_id') ?? null;
        $ttPixelId       = setting('tt_pixel_id') ?? null;
        $contentCategory = optional(optional($product ?? null)->category)->name ?? 'Landing Page';
        // Ids the server already used for the Meta Conversions API + TikTok Events API.
        $lpEventBase = $lpEventBase ?? ('LP13_' . $productId . '_' . now()->format('YmdHis'));
        $lpEventIdVC = $lpEventIdVC ?? ($lpEventBase . '_VC');
        $lpEventIdIC = $lpEventIdIC ?? ($lpEventBase . '_IC');
        
        // ✅ Icon sanitize helper
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
        :root {
            --brand: {{ $brandColor }}; --btn-bg: {{ $btnBg }}; --btn-text: {{ $btnTextCol }};
            --honey-50: #fffbeb; --honey-100: #fef3c7; --honey-200: #fde68a;
            --honey-300: #fcd34d; --honey-400: #fbbf24; --honey-500: #f59e0b;
            --honey-600: #d97706; --honey-700: #b45309; --honey-800: #92400e; --honey-900: #78350f;
            --brown: #5b2c0a; --brown-dark: #3f1f06;
            --cream: #fff9ec; --warm-bg: #fffaf0;
        }
        body { background: var(--warm-bg); color: #1f2937; line-height: 1.6; }

        /* Top countdown */
        .top-countdown { background: {{ $cdBg }}; color: {{ $cdTxt }}; padding: 10px 0; text-align: center; font-weight: 700; font-size: 0.92rem; }
        .top-countdown .timer { display: inline-flex; gap: 4px; margin-left: 8px; font-variant-numeric: tabular-nums; }
        .cd-box { background: rgba(0,0,0,0.22); padding: 2px 8px; border-radius: 4px; min-width: 34px; }

        /* Hero */
        .hero {
            padding: 36px 0 50px;
            background: linear-gradient(180deg, var(--honey-100) 0%, var(--cream) 70%, var(--warm-bg) 100%);
            text-align: center;
        }
        .hero-content { max-width: 720px; margin: 0 auto; padding: 0 16px; }
        .hero-title { font-size: 2.15rem; font-weight: 800; line-height: 1.28; color: var(--honey-900); margin-bottom: 14px; letter-spacing: -0.5px; }
        .hero-title .accent { color: var(--honey-700); }
        .hero-sub { color: #57534e; font-size: 1rem; margin-bottom: 22px; }
        
        .hero-video-wrap { max-width: 580px; margin: 0 auto 22px; background: #fff; border-radius: 16px; padding: 10px; box-shadow: 0 18px 36px rgba(180,83,9,0.20); border: 1px solid var(--honey-200); }
        .hero-video-wrap .ratio { border-radius: 10px; overflow: hidden; }
        
        .hero-img-wrap { max-width: 380px; margin: 0 auto 22px; background: #fff; border-radius: 16px; padding: 18px; box-shadow: 0 18px 36px rgba(180,83,9,0.20); border: 1px solid var(--honey-200); }
        .hero-img-wrap img { max-width: 100%; max-height: 320px; object-fit: contain; }

        .price-block { display: inline-flex; align-items: baseline; gap: 14px; flex-wrap: wrap; margin-bottom: 20px; justify-content: center; }
        .price-new { color: var(--honey-700); font-size: 2.3rem; font-weight: 800; }
        .price-old { color: #94a3b8; text-decoration: line-through; font-size: 1.2rem; }
        .price-save { background: var(--honey-100); color: var(--honey-800); padding: 5px 12px; border-radius: 50px; font-size: 0.86rem; font-weight: 800; border: 1px dashed var(--honey-500); }

        .hero-ctas { display: inline-flex; gap: 12px; flex-wrap: wrap; justify-content: center; margin-bottom: 20px; }
        .btn-primary-cta {
            background: linear-gradient(135deg, var(--honey-400), var(--honey-600));
            color: var(--honey-900); border: none; border-radius: 50px; padding: 14px 28px;
            font-weight: 800; font-size: 1rem; text-decoration: none;
            display: inline-flex; align-items: center; gap: 10px;
            box-shadow: 0 10px 22px rgba(217,119,6,0.3); transition: all .2s ease;
        }
        .btn-primary-cta:hover { color: var(--honey-900); transform: translateY(-2px); }
        .btn-secondary-cta {
            background: #fff; color: var(--honey-800); border: 2px solid var(--honey-300);
            border-radius: 50px; padding: 12px 22px; font-weight: 700; font-size: 0.96rem;
            text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-secondary-cta:hover { color: var(--honey-900); background: var(--honey-100); }

        .hero-trust { display: flex; flex-wrap: wrap; gap: 14px; justify-content: center; color: var(--honey-800); font-size: 0.88rem; font-weight: 600; }
        .hero-trust span { display: inline-flex; align-items: center; gap: 6px; }
        .hero-trust i { color: var(--honey-700); }

        /* Section common */
        .sec { padding: 48px 0; }
        .sec-title-wrap { text-align: center; margin-bottom: 32px; }
        .sec-pill { display: inline-block; background: var(--honey-100); color: var(--honey-800); padding: 5px 14px; border-radius: 50px; font-size: 0.78rem; font-weight: 700; margin-bottom: 8px; border: 1px solid var(--honey-200); }
        .sec-title { font-size: 1.75rem; font-weight: 800; color: var(--honey-900); margin-bottom: 6px; }
        .sec-title .accent { color: var(--honey-700); }
        .sec-sub { color: #6b7280; font-size: 0.95rem; max-width: 600px; margin: 0 auto; }

        /* 6 BENEFIT CARDS */
        .benefits { background: #fff; }
        .ben-card { background: #fff; border-radius: 14px; padding: 22px 18px; height: 100%; box-shadow: 0 3px 14px rgba(180,83,9,0.06); border: 1px solid var(--honey-100); transition: all .2s ease; }
        .ben-card:hover { transform: translateY(-3px); box-shadow: 0 12px 26px rgba(217,119,6,0.15); border-color: var(--honey-300); }
        .ben-icon { width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, var(--honey-100), var(--honey-200)); color: var(--honey-700); display: inline-flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 12px; }
        .ben-title { font-weight: 800; font-size: 1rem; margin-bottom: 5px; color: var(--honey-900); }
        .ben-desc { color: #6b7280; font-size: 0.86rem; margin: 0; }

        /* AUTHENTICITY 2-COL */
        .authenticity { background: var(--cream); }
        .auth-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 22px; max-width: 980px; margin: 0 auto; }
        @media (max-width: 768px) { .auth-grid { grid-template-columns: 1fr; } }
        .auth-card { background: #fff; border-radius: 16px; padding: 26px 22px; box-shadow: 0 8px 22px rgba(180,83,9,0.08); border: 1px solid var(--honey-100); }
        .auth-card .head { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; padding-bottom: 14px; border-bottom: 1px dashed var(--honey-200); }
        .auth-card .head .ic { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, var(--honey-400), var(--honey-700)); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
        .auth-card .head h3 { font-size: 1.08rem; font-weight: 800; color: var(--honey-900); margin: 0; }
        .auth-card .body { color: #44403c; font-size: 0.94rem; line-height: 1.75; }
        .auth-card .body ul { padding-left: 0; list-style: none; margin: 0; }
        .auth-card .body ul li { padding: 6px 0 6px 24px; position: relative; }
        .auth-card .body ul li::before { content: '✓'; position: absolute; left: 0; top: 6px; color: var(--honey-600); font-weight: 800; }

        /* BIG STATS BAND (DARK BROWN) */
        .big-stats-sec { background: linear-gradient(135deg, var(--brown-dark), var(--brown)); padding: 40px 0; }
        .big-stats-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 18px; text-align: center; max-width: 880px; margin: 0 auto; }
        @media (max-width: 576px) { .big-stats-row { grid-template-columns: 1fr; } }
        .big-stat { background: rgba(255,255,255,0.06); border: 1px solid rgba(254,243,199,0.18); border-radius: 14px; padding: 22px 18px; backdrop-filter: blur(4px); }
        .big-stat-num { font-size: 2.2rem; font-weight: 800; color: var(--honey-200); margin-bottom: 4px; line-height: 1; }
        .big-stat-text { color: var(--honey-100); font-size: 0.94rem; font-weight: 600; }

        /* PRODUCT DETAILS 2-COL */
        .product-details-sec { background: #fff; }
        .pd-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 36px; align-items: center; max-width: 980px; margin: 0 auto; }
        @media (max-width: 768px) { .pd-grid { grid-template-columns: 1fr; gap: 22px; } }
        .pd-img { background: #fff; border-radius: 20px; padding: 26px; text-align: center; box-shadow: 0 8px 24px rgba(180,83,9,0.10); border: 1px solid var(--honey-200); }
        .pd-img img { max-width: 100%; max-height: 320px; object-fit: contain; }
        .pd-info h3 { font-size: 1.4rem; font-weight: 800; color: var(--honey-900); margin-bottom: 14px; }
        .spec-list { list-style: none; padding: 0; margin: 0; }
        .spec-list li { padding: 8px 0; display: flex; align-items: flex-start; gap: 10px; color: #1f2937; font-size: 0.94rem; }
        .spec-list li i { color: var(--honey-600); margin-top: 4px; flex-shrink: 0; }
        .spec-list li strong { color: var(--honey-800); font-weight: 700; }

        /* ✅ GALLERY CSS (Desktop 3 Items, No Borders) */
        .gallery-slider-css {
            display: flex; gap: 15px; overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth;
            padding-bottom: 15px;
        }
        .gallery-slider-css::-webkit-scrollbar { height: 6px; }
        .gallery-slider-css::-webkit-scrollbar-thumb { background: var(--honey-300); border-radius: 10px; }
        .gallery-slide-item { flex: 0 0 100%; scroll-snap-align: start; text-align: center; }
        .gallery-slide-item img {
            width: 100%; height: 350px; object-fit: contain; 
            border: none !important; border-radius: 0 !important; 
            background: transparent !important; box-shadow: none !important;
        }
        @media (min-width: 768px) {
            .gallery-slide-item { flex: 0 0 calc(33.333% - 10px); }
            .gallery-slide-item img { height: 300px; }
        }

        /* REVIEWS */
        .reviews-sec { background: var(--cream); }
        .review-card { background: #fff; border-radius: 14px; padding: 22px 20px; height: 100%; border: 1px solid var(--honey-200); }
        .review-stars { color: var(--honey-500); margin-bottom: 8px; font-size: 0.92rem; }
        .review-text { color: #44403c; font-size: 0.92rem; line-height: 1.6; margin-bottom: 12px; }
        .reviewer-info { display: flex; align-items: center; gap: 10px; }
        .reviewer-avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--honey-500), var(--honey-700)); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; }
        .reviewer-name { font-weight: 700; color: var(--honey-900); font-size: 0.92rem; }
        .reviewer-loc { color: #94a3b8; font-size: 0.8rem; }

        /* ORDER FORM */
        .order-form-section { background: var(--cream); padding: 50px 0; }
        .order-card { background: #fff; border-radius: 18px; padding: 28px; box-shadow: 0 14px 36px rgba(180,83,9,0.15); max-width: 560px; margin: 0 auto; border: 1px solid var(--honey-200); }
        .form-control, .form-select { border-radius: 10px; padding: 11px 14px; border: 1px solid #e5e7eb; background: #fff; font-size: 0.94rem; }
        .form-control:focus, .form-select:focus { border-color: var(--honey-500); box-shadow: 0 0 0 3px rgba(245,158,11,0.12); }
        .form-label { font-weight: 700; color: var(--honey-900); margin-bottom: 6px; font-size: 0.9rem; }
        .delivery-radio-group { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        @media (max-width: 480px) { .delivery-radio-group { grid-template-columns: 1fr; } }
        .delivery-radio-box { display: flex; align-items: center; gap: 10px; padding: 12px 14px; border: 2px solid #e5e7eb; border-radius: 10px; cursor: pointer; background: #fff; }
        .delivery-radio-box.active { border-color: var(--honey-500); background: var(--honey-50); }
        .delivery-radio-box input { accent-color: var(--honey-500); }
        .delivery-radio-box .area { font-weight: 700; font-size: 0.9rem; color: var(--honey-900); display: block; }
        .delivery-radio-box .charge { color: var(--honey-700); font-size: 0.82rem; font-weight: 700; }

        .package-card { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 12px 14px; border: 2px solid #e5e7eb; border-radius: 10px; margin-bottom: 8px; cursor: pointer; background: #fff; }
        .package-card.active-pkg { border-color: var(--honey-500); background: var(--honey-50); }
        .package-card .pkg-left { display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0; }
        .package-card .pkg-right { display: flex; align-items: center; gap: 8px; }
        .pkg-radio { width: 17px; height: 17px; accent-color: var(--honey-500); }
        .pkg-title { font-weight: 700; font-size: 0.92rem; color: var(--honey-900); }
        .pkg-qty-box { border: 1px solid #e5e7eb; border-radius: 6px; background: #f9fafb; min-width: 34px; height: 28px; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; color: var(--honey-700); font-size: 0.85rem; }
        .pkg-price { font-weight: 800; color: var(--honey-700); font-size: 0.98rem; }

        .qty-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 10px; background: #fff; }
        .pro-qty { display: inline-flex; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
        .pro-qty span { padding: 6px 14px; cursor: pointer; user-select: none; font-weight: 800; background: #f9fafb; color: var(--honey-700); }
        .pro-qty input { width: 44px; border: none; text-align: center; font-weight: 700; }

        .price-summary { background: var(--honey-50); border-radius: 12px; padding: 14px 16px; margin: 14px 0; border: 1px solid var(--honey-200); }
        .price-summary .row-line { display: flex; justify-content: space-between; padding: 3px 0; color: #57534e; font-size: 0.92rem; font-weight: 600; }
        .price-summary .grand { font-size: 1.05rem; color: var(--honey-800); border-top: 1px dashed var(--honey-300); margin-top: 6px; padding-top: 10px; font-weight: 800; }

        .coupon-section { background: #f9fafb; border: 1px dashed #e5e7eb; padding: 12px; border-radius: 10px; margin: 12px 0; }
        .coupon-input-group { display: flex; overflow: hidden; border: 1px solid #d1d5db; border-radius: 8px; background: #fff; }
        .coupon-input-group input { border: none; padding: 9px 12px; flex-grow: 1; outline: none; font-size: 0.9rem; }
        .coupon-input-group button { border: none; background: var(--honey-700); color: #fff; padding: 0 20px; font-weight: 700; cursor: pointer; font-size: 0.88rem; }

        .payment-radio-box { display: flex; align-items: center; gap: 10px; padding: 11px 14px; border: 2px solid #e5e7eb; border-radius: 10px; margin-bottom: 6px; cursor: pointer; background: #fff; font-size: 0.92rem; }
        .payment-radio-box.active { border-color: var(--honey-500); background: var(--honey-50); }
        .manual-payment-box { background: var(--honey-100); border-radius: 8px; padding: 10px; margin-top: 6px; display: none; }
        .manual-payment-box.show { display: block; }

        .btn-order-confirm {
            width: 100%; background: linear-gradient(135deg, var(--honey-500), var(--honey-700));
            color: #fff; border: none; border-radius: 12px; padding: 15px;
            font-weight: 800; font-size: 1.1rem; box-shadow: 0 10px 24px rgba(217,119,6,0.32);
        }
        .btn-order-confirm:hover { transform: translateY(-2px); }
        .btn-order-confirm:disabled { opacity: 0.7; cursor: wait; }
        .security-note { background: #ecfdf5; color: #065f46; border-radius: 10px; padding: 10px 14px; font-size: 0.84rem; font-weight: 600; text-align: center; margin-top: 12px; }

        /* FAQ */
        .faq-sec { background: #fff; }
        .faq-card { background: var(--cream); border-radius: 12px; box-shadow: 0 3px 10px rgba(180,83,9,0.06); margin-bottom: 10px; border: 1px solid var(--honey-200); }
        .faq-q { padding: 14px 18px; cursor: pointer; font-weight: 700; display: flex; justify-content: space-between; align-items: center; color: var(--honey-900); font-size: 0.95rem; }
        .faq-q i { color: var(--honey-700); transition: transform .2s ease; }
        .faq-card.open .faq-q i { transform: rotate(180deg); }
        .faq-a { padding: 0 18px 14px; color: #57534e; display: none; line-height: 1.65; font-size: 0.9rem; }
        .faq-card.open .faq-a { display: block; }

        /* FOOTER */
        footer { background: var(--cream); color: #57534e; padding: 32px 0 22px; text-align: center; border-top: 1px solid var(--honey-200); }
        footer .fcompany { font-weight: 800; color: var(--honey-800); font-size: 1.2rem; margin-bottom: 6px; }
        footer a { color: var(--honey-700); text-decoration: none; font-weight: 600; }
        footer .copyright { color: #8a857d; font-size: 0.84rem; margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--honey-200); }

        /* Mobile Adjustments & Sticky CTA */
        @media (max-width: 768px) {
            .hero { padding: 26px 0 36px; }
            .hero-title { font-size: 1.55rem; }
            .hero-video-wrap, .hero-img-wrap { padding: 8px; }
            .price-new { font-size: 1.65rem; }
            .sec { padding: 32px 0; }
            .sec-title { font-size: 1.3rem; }
            .auth-card { padding: 20px; }
            .big-stats-sec { padding: 32px 0; }
            .big-stat-num { font-size: 1.8rem; }
            .pd-img { padding: 20px; }
            .order-card { padding: 20px; }
            
            /* ✅ MOBILE STICKY CSS */
            .mobile-sticky { position: fixed; bottom: 0; left: 0; right: 0; z-index: 99; background: #fff; padding: 10px 12px; box-shadow: 0 -4px 12px rgba(0,0,0,0.1); border-top: 1px solid var(--honey-200); }
            .mobile-sticky .d-flex { gap: 10px; }
            .btn-outline-sticky {
                flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px;
                background: #fff; color: var(--honey-700); border: 2px solid var(--honey-500);
                border-radius: 50px; font-weight: 800; font-size: 1rem; text-decoration: none; padding: 12px;
            }
            .sticky-order-btn {
                flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px;
                background: linear-gradient(135deg, var(--honey-500), var(--honey-700)); color: #fff; 
                border: none; border-radius: 50px; font-weight: 800; font-size: 1rem; text-decoration: none; padding: 12px;
            }
            body { padding-bottom: 76px; }
        }
        @media (min-width: 769px) { .mobile-sticky { display: none; } }
        
        .single-variation .variation-wrap { display: none !important; }
        .variation-wrap.single-product-hidden { display: none !important; }
        
        /* UNIVERSAL MOBILE IMAGE FIX */
        body { overflow-x: hidden !important; max-width: 100vw !important; }
        img { max-width: 100% !important; height: auto !important; }
        * { box-sizing: border-box; }
        .hero-img-wrap, .hero-video-wrap {
            overflow: hidden !important; width: 100% !important; box-sizing: border-box !important;
        }
        .hero-img-wrap img {
            max-width: 100% !important; width: auto !important; height: auto !important;
            object-fit: contain !important; display: block !important; margin: 0 auto !important;
        }
        .hero-video-wrap .ratio, .hero-video-wrap iframe { width: 100% !important; max-width: 100% !important; }
        @media (max-width: 768px) {
            .hero, .container, .row, section { overflow-x: hidden !important; }
            .hero-img-wrap, .hero-video-wrap { padding: 10px !important; margin-left: 0 !important; margin-right: 0 !important; }
            .hero-img-wrap img, .pd-img img { max-height: 280px !important; width: 100% !important; object-fit: contain !important; }
            .hero-video-wrap iframe { max-height: 220px !important; }
            .row { margin-left: 0 !important; margin-right: 0 !important; }
            .col-md-6, .col-lg-6, .col-12 { padding-left: 12px !important; padding-right: 12px !important; }
        }
    </style>
</head>
<body>
@if(!empty($__gtmId))
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $__gtmId }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
@endif

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
        <h1 class="hero-title">{{ $ln_pg->title1 ?? 'Honeyraj কম্ব হানি — প্রকৃতির সরাসরি মিষ্টি উপহার' }}</h1>
        <p class="hero-sub">{{ $ln_pg->title2 ?? 'প্রকৃতির সেরা উপাদান দিয়ে তৈরি, যা আপনার ভেতরের শক্তি যোগায়।' }}</p>

        @if(!empty($ln_pg->video_url))
            <div class="hero-video-wrap">
                @php 
                    $videoUrl = trim($ln_pg->video_url); 
                    if (strpos($videoUrl, 'watch?v=') !== false) {
                        $videoUrl = str_replace('watch?v=', 'embed/', $videoUrl);
                    } elseif (strpos($videoUrl, 'youtu.be/') !== false) {
                        $videoUrl = str_replace('youtu.be/', 'youtube.com/embed/', $videoUrl);
                    }
                    if(strpos($videoUrl, '?') !== false) {
                        $videoUrl .= '&cc_load_policy=1';
                    } else {
                        $videoUrl .= '?cc_load_policy=1';
                    }
                @endphp
                @if(stripos($videoUrl, '<iframe') !== false)
                    <div class="ratio ratio-16x9">{!! $videoUrl !!}</div>
                @else
                    <div class="ratio ratio-16x9"><iframe src="{{ $videoUrl }}" frameborder="0" allowfullscreen></iframe></div>
                @endif
            </div>
        @elseif(!empty($heroImage))
            <div class="hero-img-wrap"><img src="{{ $heroImage }}" alt="{{ $productName }}" onerror="this.onerror=null; this.src='{{ $productFallback ?? asset('frontend/images/no-image.png') }}';"></div>
        @endif

        <div class="price-block mt-3">
            <span class="price-new">৳{{ $defaultPrice }}</span>
            @if($oldPrice)<span class="price-old">৳{{ $oldPrice }}</span>@endif
            @if($ln_pg->discount_save_text)<span class="price-save">{{ $ln_pg->discount_save_text }}</span>@endif
        </div>

        <div class="hero-ctas">
            <a href="#order-form" class="btn-primary-cta"><i class="fas fa-shopping-cart"></i> {{ $ln_pg->btn_text_hero ?? 'এখনই অর্ডার করুন' }}</a>
            @if($phoneNumber)<a href="tel:{{ $phoneNumber }}" class="btn-secondary-cta"><i class="fas fa-phone"></i> {{ $ln_pg->call_text ?? 'কল করুন' }}</a>@endif
        </div>

        <div class="hero-trust">
            <span><i class="fas fa-check-circle"></i> {{ $ln_pg->pay_text ?? 'ক্যাশ অন ডেলিভারি' }}</span>
            <span><i class="fas fa-truck"></i> সারা দেশে ডেলিভারি</span>
            <span><i class="fas fa-shield-alt"></i> ১০০% মানি ব্যাক</span>
        </div>
    </div>
</section>

@if($ln_pg->id_1_title)
<section class="sec benefits">
    <div class="container">
        <div class="sec-title-wrap">
            <h2 class="sec-title">কেন <span class="accent">{{ $ln_pg->footer_company ?? 'Honeyraj' }}</span> বেছে নেবেন?</h2>
        </div>
        <div class="row g-3">
            @for($i=1; $i<=6; $i++)
                @if($ln_pg->{'id_'.$i.'_title'})
                <div class="col-md-4 col-sm-6">
                    <div class="ben-card">
                        <div class="ben-icon"><i class="fas {{ $cleanIcon($ln_pg->{'id_'.$i.'_icon'}, $defaultFeatIcons[$i-1] ?? 'fa-leaf') }}"></i></div>
                        <div class="ben-title">{{ $ln_pg->{'id_'.$i.'_title'} }}</div>
                        <p class="ben-desc">{{ $ln_pg->{'id_'.$i.'_desc'} }}</p>
                    </div>
                </div>
                @endif
            @endfor
        </div>
    </div>
</section>
@endif

{{-- AUTHENTICITY (2-COL) --}}
@if(!empty($ln_pg->left_side_desc) || !empty($ln_pg->right_side_desc))
<section class="sec authenticity">
    <div class="container">
        <div class="sec-title-wrap">
            <span class="sec-pill">১০০% অর্গানিক প্রমাণিত</span>
            <h2 class="sec-title">কেন আমাদের মধু <span class="accent">সত্যিকারের অর্গানিক?</span></h2>
            <p class="sec-sub">{{ $ln_pg->identify_subtitle ?? 'প্রমাণ ও উপকারিতা একসাথে দেখুন' }}</p>
        </div>
        <div class="auth-grid">
            <div class="auth-card">
                <div class="head"><div class="ic"><i class="fas fa-check-circle"></i></div><h3>{{ $ln_pg->left_side_title ?? 'অর্গানিকের প্রমাণ' }}</h3></div>
                <div class="body">{!! $ln_pg->left_side_desc ?? '' !!}</div>
            </div>
            <div class="auth-card">
                <div class="head"><div class="ic"><i class="fas fa-heart"></i></div><h3>{{ $ln_pg->right_side_title ?? 'নিয়মিত খেলে যে উপকার' }}</h3></div>
                <div class="body">{!! $ln_pg->right_side_desc ?? '' !!}</div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- BIG STATS BAND (DARK BROWN) --}}
@if($ln_pg->stat_1_num || $ln_pg->stat_2_num || $ln_pg->stat_3_num)
<section class="big-stats-sec">
    <div class="container">
        <div class="big-stats-row">
            @for($i=1; $i<=3; $i++)
                @if($ln_pg->{'stat_'.$i.'_num'})
                <div class="big-stat">
                    <div class="big-stat-num">{{ $ln_pg->{'stat_'.$i.'_num'} }}</div>
                    <div class="big-stat-text">{{ $ln_pg->{'stat_'.$i.'_text'} }}</div>
                </div>
                @endif
            @endfor
        </div>
    </div>
</section>
@endif

{{-- PRODUCT DETAILS (2-COL) --}}
@if($ln_pg->spec_1_label)
<section class="sec product-details-sec">
    <div class="container">
        <div class="sec-title-wrap">
            <h2 class="sec-title">{{ $ln_pg->spec_title ?? 'প্রোডাক্ট ডিটেইলস' }}</h2>
        </div>
        <div class="pd-grid">
            <div class="pd-img"><img src="{{ $heroImage }}" alt="{{ $productName }}" onerror="this.onerror=null; this.src='{{ $productFallback ?? asset('frontend/images/no-image.png') }}';"></div>
            <div class="pd-info">
                <h3>{{ $productName }}</h3>
                <ul class="spec-list">
                    @for($i=1; $i<=7; $i++)
                        @if($ln_pg->{'spec_'.$i.'_label'})
                        <li><i class="fas fa-check-circle"></i> <span><strong>{{ $ln_pg->{'spec_'.$i.'_label'} }}:</strong> {{ $ln_pg->{'spec_'.$i.'_value'} }}</span></li>
                        @endif
                    @endfor
                </ul>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ✅ GALLERY SLIDER (Moved Below Specification & Updated for Desktop 3 Images & No Borders) --}}
@if(isset($ln_pg->images) && $ln_pg->images->count() > 0)
<section class="sec" style="background:#f8fafc;">
    <div class="container">
        <div class="sec-title-wrap mb-4">
            <h2 class="sec-title">প্রোডাক্ট গ্যালারি</h2>
            <p class="sec-sub">বিভিন্ন অ্যাঙ্গেল থেকে দেখুন</p>
        </div>
        
        <div class="gallery-slider-css">
            @foreach($ln_pg->images as $key => $img)
                <div class="gallery-slide-item">
                    <img src="{{ asset('landing_sliders/'.$img->image) }}" alt="Gallery {{ $key + 1 }}" onerror="this.onerror=null; this.src='{{ $productFallback ?? asset('frontend/images/no-image.png') }}';">
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- REVIEWS --}}
@if($ln_pg->rev_1_text)
<section class="sec reviews-sec">
    <div class="container">
        <div class="sec-title-wrap">
            <h2 class="sec-title">{{ $ln_pg->review_title ?? 'আমাদের ক্রেতাদের মতামত' }}</h2>
            @if($ln_pg->review_subtitle)<p class="sec-sub">{{ $ln_pg->review_subtitle }}</p>@endif
        </div>
        <div class="row g-3">
            @for($i=1; $i<=3; $i++)
                @if($ln_pg->{'rev_'.$i.'_text'})
                <div class="col-md-4">
                    <div class="review-card">
                        <div class="review-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <p class="review-text">"{{ $ln_pg->{'rev_'.$i.'_text'} }}"</p>
                        <div class="reviewer-info">
                            <div class="reviewer-avatar">{{ mb_substr($ln_pg->{'rev_'.$i.'_name'} ?? 'A', 0, 1) }}</div>
                            <div>
                                <div class="reviewer-name">{{ $ln_pg->{'rev_'.$i.'_name'} }}</div>
                                <div class="reviewer-loc">{{ $ln_pg->{'rev_'.$i.'_loc'} }}</div>
                            </div>
                        </div>
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
            <span class="sec-pill"><i class="fas fa-clock me-1"></i> ১৫ — ৩০ মিনিট আগে</span>
            <h2 class="sec-title">{{ $ln_pg->form_title ?? 'এখনই অর্ডার করুন' }}</h2>
            <p class="sec-sub">{{ $ln_pg->form_subtitle ?? 'নিচের ফর্মটি পূরণ করুন — ক্যাশ অন ডেলিভারিতে পণ্য পেয়ে যাবেন' }}</p>
        </div>
        <div class="order-card">
            <form id="checkout_land_form" action="{{ route('front.storelandData') }}" method="POST">
                @csrf
                        {{-- কোন landing page থেকে order এলো (Order Management-এর Source-এ দেখায়) --}}
                        <input type="hidden" name="landing_page_type" value="13">
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
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ $ln_pg->phone_label ?? 'মোবাইল নম্বর *' }}</label>
                        <input type="tel" name="mobile" class="form-control" placeholder="01XXXXXXXXX" maxlength="11" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">পরিমাণ</label>
                        <div class="qty-row">
                            <div class="pro-qty">
                                <span class="decrease-qty">−</span>
                                <input type="text" class="inner_qty" value="1" readonly>
                                <span class="increase-qty">+</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ $ln_pg->address_label ?? 'সম্পূর্ণ ঠিকানা *' }}</label>
                    <textarea name="shipping_address" class="form-control" rows="2" placeholder="{{ $ln_pg->address_placeholder ?? 'বাসা, রোড, এলাকা, থানা' }}" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ $ln_pg->delivery_label ?? 'ডেলিভারি এরিয়া *' }}</label>
                    <div class="delivery-radio-group">
                        @foreach($charges as $idx => $ch)
                        <label class="delivery-radio-box {{ $idx == 0 ? 'active' : '' }}">
                            <input type="radio" name="delivery_charge_id" value="{{ $ch->id }}" data-charge="{{ $isFreeShipping ? 0 : intval($ch->amount) }}" {{ $idx == 0 ? 'checked' : '' }} required>
                            <div><span class="area">{{ $ch->title }}</span><span class="charge">{!! $isFreeShipping ? 'ফ্রি' : intval($ch->amount).' ৳' !!}</span></div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">পেমেন্ট প্যাকেজ:</label>
                    <label class="package-card active-pkg">
                        <div class="pkg-left"><input type="radio" name="selected_package_id" value="" data-price="{{ $defaultPrice }}" data-qty="1" class="pkg-radio" checked><span class="pkg-title">(১ পিস) {{ $productName }}</span></div>
                        <div class="pkg-right"><div class="pkg-qty-box"><span>1</span></div><span class="pkg-price">{{ $defaultPrice }} ৳</span></div>
                    </label>
                    @if($ln_pg->packages && $ln_pg->packages->count() > 0)
                        @foreach($ln_pg->packages as $pkg)
                        <label class="package-card">
                            <div class="pkg-left"><input type="radio" name="selected_package_id" value="{{ $pkg->id }}" data-price="{{ $pkg->price }}" data-qty="{{ $pkg->qty }}" class="pkg-radio"><div><span class="pkg-title">({{ $pkg->qty }} পিস) {{ $productName }}</span>@if($pkg->discount_text)<small class="d-block text-danger fw-bold mt-1">{{ $pkg->discount_text }}</small>@endif</div></div>
                            <div class="pkg-right"><div class="pkg-qty-box"><span>{{ $pkg->qty }}</span></div><span class="pkg-price">{{ intval($pkg->price) }} ৳</span></div>
                        </label>
                        @endforeach
                    @endif
                </div>

                @if($variations->count() > 1)
                <div class="mb-3">
                    <label class="form-label">{{ $ln_pg->variation_label ?? 'ভেরিয়েশন *' }}</label>
                    <select name="variation_id" id="variation_select" class="form-select" required>
                        <option value="" disabled selected data-price="{{ $defaultPrice }}" data-stock="{{ $defaultStock }}">সিলেক্ট করুন...</option>
                        @foreach($variations as $v)
                            @php $vPrice = (($v->after_discount_price ?? 0) > 0) ? $v->after_discount_price : ($v->price ?? $product->sell_price ?? 0); $vStock = $v->stocks->sum('quantity'); $vLabel = trim(($v->size->name ?? '') . ' ' . ($v->color->name ?? '')); @endphp
                            <option value="{{ $v->id }}" data-price="{{ $vPrice }}" data-stock="{{ $vStock }}">{{ $vLabel ?: ('Variation #'.$v->id) }}</option>
                        @endforeach
                    </select>
                </div>
                @elseif($variations->count() == 1)
                    @php $sv = $variations->first(); $svPrice = (($sv->after_discount_price ?? 0) > 0) ? $sv->after_discount_price : ($sv->price ?? $product->sell_price ?? 0); $svStock = $sv->stocks->sum('quantity'); @endphp
                    <input type="hidden" name="variation_id" id="variation_select" value="{{ $sv->id }}" data-price="{{ $svPrice }}" data-stock="{{ $svStock }}">
                @else
                    <input type="hidden" name="variation_id" id="variation_select" value="">
                @endif
                <input type="hidden" id="max_stock" value="{{ $defaultStock }}">

                <div class="mb-3">
                    <label class="form-label">অতিরিক্ত নোট (ঐচ্ছিক)</label>
                    <textarea name="order_note" class="form-control" rows="2" placeholder="যেকোনো বিশেষ নির্দেশনা থাকলে লিখুন"></textarea>
                </div>

                @if(isset($information->coupon_visibility) && $information->coupon_visibility == 1)
                <div class="coupon-section">
                    <label class="form-label mb-1" style="font-size:0.85rem;"><i class="fas fa-ticket-alt me-1"></i> কুপন কোড</label>
                    <div class="coupon-input-group">
                        <input type="text" id="coupon_code" placeholder="Enter coupon code">
                        <button type="button" id="coupon_btn_submit" onclick="applyCouponLand()">APPLY</button>
                    </div>
                    <small id="coupon_msg" class="d-block mt-1 fw-bold" style="font-size:0.8rem;"></small>
                </div>
                @endif

                <div class="mb-3">
                    <label class="form-label">পেমেন্ট মাধ্যম</label>
                    @if(isset($information->cod_active) && $information->cod_active == 1)
                    <label class="payment-radio-box active"><input type="radio" name="payment_method" value="cod" checked onchange="togglePaymentAction('cod')"><i class="fas fa-money-bill-wave text-success"></i><span class="fw-bold">{{ $ln_pg->cod_title ?? 'ক্যাশ অন ডেলিভারি' }}</span></label>
                    @endif
                    @if(isset($information->ssl_active) && $information->ssl_active == 1)
                    <label class="payment-radio-box"><input type="radio" name="payment_method" value="sslcommerz" onchange="togglePaymentAction('sslcommerz')"><i class="fas fa-credit-card text-primary"></i><span class="fw-bold">অনলাইন (SSL)</span></label>
                    @endif
                    @if(isset($information->bkash_active) && $information->bkash_active == 1)
                    <label class="payment-radio-box"><input type="radio" name="payment_method" value="bkash" onchange="togglePaymentAction('bkash')"><span class="fw-bold">বিকাশ</span></label>
                    @endif
                    @if(isset($information->nagad_active) && $information->nagad_active == 1)
                    <label class="payment-radio-box"><input type="radio" name="payment_method" value="nagad" onchange="togglePaymentAction('nagad')"><span class="fw-bold">নগদ</span></label>
                    @endif
                    @if(isset($information->uddoktapay_active) && $information->uddoktapay_active == 1)
                    <label class="payment-radio-box"><input type="radio" name="payment_method" value="uddoktapay" onchange="togglePaymentAction('uddoktapay')"><span class="fw-bold">UddoktaPay</span></label>
                    @endif
                    @foreach($activeManuals as $mp)
                    <label class="payment-radio-box"><input type="radio" name="payment_method" value="{{ $mp->name }}" data-number="{{ $mp->number }}" data-type="{{ $mp->type }}" onchange="togglePaymentAction('manual', '{{ $mp->name }}', '{{ $mp->number }}', '{{ $mp->type }}')"><span class="fw-bold">{{ $mp->name }}</span></label>
                    @endforeach
                    <div class="manual-payment-box" id="manualPaymentBox">
                        <p class="mb-2 small fw-bold">পেমেন্ট পাঠান <span id="manual_number" class="text-danger"></span> (<span id="manual_type"></span>) এ</p>
                        <input type="text" name="sender_number" id="sender_number" class="form-control mb-2" placeholder="যে নাম্বার থেকে পাঠিয়েছেন">
                        <input type="text" name="transaction_id" id="transaction_id" class="form-control" placeholder="ট্রানজেকশন ID">
                    </div>
                </div>

                <div class="price-summary">
                    <div class="row-line"><span>প্রোডাক্ট (১ × <span id="subtotal_disp">{{ $defaultPrice }}</span>):</span> <span>৳<span id="subtotal_disp2">{{ $defaultPrice }}</span></span></div>
                    <div class="row-line" id="discount_row" style="display:none;"><span>ছাড়:</span> <span>- ৳<span id="discount_disp">0</span></span></div>
                    <div class="row-line"><span>ডেলিভারি:</span> <span>৳<span id="delivery_disp">0</span></span></div>
                    <div class="row-line grand"><span>{{ $ln_pg->total_bill_label ?? 'সর্বমোট' }}:</span> <span>৳<span id="grand_total_disp">{{ $defaultPrice }}</span></span></div>
                </div>

                <button type="submit" id="submit_btn" class="btn-order-confirm"><i class="fas fa-check me-1"></i> {{ $ln_pg->btn_text_form ?? 'অর্ডার কনফার্ম করুন' }} — ৳<span id="btn_total">{{ $defaultPrice }}</span></button>

                <div class="security-note">{{ $ln_pg->security_badge_text ?? 'আপনার তথ্য সম্পূর্ণ সুরক্ষিত' }}</div>
            </form>
        </div>
    </div>
</section>

{{-- FAQ --}}
@if($ln_pg->faq_1_q)
<section class="sec faq-sec">
    <div class="container">
        <div class="sec-title-wrap"><h2 class="sec-title">{{ $ln_pg->faq_title ?? 'সাধারণ জিজ্ঞাসা (FAQ)' }}</h2></div>
        <div class="mx-auto" style="max-width:720px;">
            @for($i=1; $i<=4; $i++)
                @if($ln_pg->{'faq_'.$i.'_q'})
                <div class="faq-card">
                    <div class="faq-q" onclick="this.parentElement.classList.toggle('open')"><span>{{ $ln_pg->{'faq_'.$i.'_q'} }}</span><i class="fas fa-chevron-down"></i></div>
                    <div class="faq-a">{{ $ln_pg->{'faq_'.$i.'_a'} }}</div>
                </div>
                @endif
            @endfor
        </div>
    </div>
</section>
@endif

{{-- FOOTER --}}
<footer>
    <div class="container">
        <div class="fcompany">{{ $ln_pg->footer_company ?? 'Honeyraj' }}</div>
        <div>
            @if($phoneNumber)<a href="tel:{{ $phoneNumber }}"><i class="fas fa-phone me-1"></i>{{ $phoneNumber }}</a>@endif
            @if($ln_pg->footer_email) &nbsp;|&nbsp; <a href="mailto:{{ $ln_pg->footer_email }}"><i class="fas fa-envelope me-1"></i>{{ $ln_pg->footer_email }}</a>@endif
        </div>
        @if($ln_pg->footer_copyright)<div class="copyright">{{ $ln_pg->footer_copyright }}</div>@endif
    </div>
</footer>

{{-- ✅ REDESIGNED MOBILE STICKY CTA WITH TWO EQUAL BUTTONS --}}
<div class="mobile-sticky">
    <div class="d-flex w-100">
        @if($phoneNumber)
        <a href="tel:{{ $phoneNumber }}" class="btn-outline-sticky">
            <i class="fas fa-phone-alt"></i> কল করুন
        </a>
        @endif
        
        <a href="#order-form" class="sticky-order-btn">
            <i class="fas fa-shopping-cart"></i> অর্ডার করুন
        </a>
    </div>
</div>

<script>
    (function() {
        var hours = {{ $cdHours }};
        if(hours <= 0) return;
        var key = 'lp12_cd_end_{{ $ln_pg->id }}';
        var end = parseInt(localStorage.getItem(key) || '0');
        if(!end || end < Date.now()) { end = Date.now() + hours*3600*1000; localStorage.setItem(key, end); }
        function pad(n){ return n<10?'0'+n:''+n; }
        function tick() {
            var diff = end - Date.now();
            if(diff <= 0) { localStorage.removeItem(key); end = Date.now() + hours*3600*1000; localStorage.setItem(key, end); diff = end - Date.now(); }
            var h=Math.floor(diff/3600000),m=Math.floor((diff%3600000)/60000),s=Math.floor((diff%60000)/1000);
            var $h=document.getElementById('cd-h'),$m=document.getElementById('cd-m'),$s=document.getElementById('cd-s');
            if($h)$h.innerText=pad(h); if($m)$m.innerText=pad(m); if($s)$s.innerText=pad(s);
        }
        setInterval(tick, 1000); tick();
    })();

    var hasPackages = {{ (isset($ln_pg->packages) && $ln_pg->packages->count() > 0) ? 'true' : 'false' }};
    
    function selectDefaultPackage() {
        var $def = $('input[name="selected_package_id"][value=""]');
        $('input[name="selected_package_id"]').prop('checked', false);
        $def.prop('checked', true);
        $('.package-card').removeClass('active-pkg');
        $def.closest('.package-card').addClass('active-pkg');
    }

    function resetCoupon() {
        $('#hidden_coupon_code').val('');
        $('#hidden_discount').val(0);
        $('#coupon_msg').text('');
        $('#coupon_code').val('');
    }

    $(document).on('click', '.increase-qty', function() { 
        if(hasPackages) { toastr.warning('অনুগ্রহ করে প্যাকেজ সিলেক্ট করুন, পরিমাণ ম্যানুয়ালি পরিবর্তন করা যাবে না।'); return; }
        var $i=$(this).siblings('.inner_qty'); 
        $i.val(parseInt($i.val())+1); 
        selectDefaultPackage();
        resetCoupon();
        recalc(); 
    });

    $(document).on('click', '.decrease-qty', function() { 
        if(hasPackages) { toastr.warning('অনুগ্রহ করে প্যাকেজ সিলেক্ট করুন, পরিমাণ ম্যানুয়ালি পরিবর্তন করা যাবে না।'); return; }
        var $i=$(this).siblings('.inner_qty'); 
        var v=parseInt($i.val()); 
        if(v>1) {
            $i.val(v-1); 
            selectDefaultPackage();
            resetCoupon();
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
        resetCoupon();
        recalc(); 
    });
    
    $(document).on('change', 'input[name="delivery_charge_id"]', function() { 
        $('.delivery-radio-box').removeClass('active'); 
        $(this).closest('.delivery-radio-box').addClass('active'); 
        recalc(); 
    });
    
    $(document).on('change', '#variation_select', recalc);
    $(document).ready(recalc);

    function applyCouponLand() {
        var code = $('#coupon_code').val();
        if(!code) { $('#coupon_msg').text('কুপন কোড দিন').css('color','red'); return; }
        var unit = parseFloat($('#subtotal_input').val()) || 0;
        $('#coupon_btn_submit').text('...').prop('disabled', true);
        $.ajax({
            url: "{{ route('front.getCouponDiscount') }}", type:'POST', data: { coupon_code: code, amount: unit, _token: "{{ csrf_token() }}" },
            success: function(res) { 
                if(res.success) { 
                    $('#hidden_coupon_code').val(code); 
                    $('#hidden_discount').val(res.discount); 
                    $('#coupon_msg').text(res.msg).css('color','green'); 
                } else { 
                    $('#hidden_coupon_code').val(''); 
                    $('#hidden_discount').val(0); 
                    $('#coupon_msg').text(res.msg).css('color','red'); 
                } 
                recalc(); 
            },
            complete: function() { $('#coupon_btn_submit').text('APPLY').prop('disabled', false); }
        });
    }
    window.applyCouponLand = applyCouponLand;

    function togglePaymentAction(method, mName, mNumber, mType) {
        $('.payment-radio-box').removeClass('active'); $('input[name="payment_method"]:checked').closest('.payment-radio-box').addClass('active');
        if(method === 'manual') { $('#manualPaymentBox').addClass('show'); $('#manual_number').text(mNumber || ''); $('#manual_type').text(mType || ''); }
        else { $('#manualPaymentBox').removeClass('show'); }
    }
    window.togglePaymentAction = togglePaymentAction;

    $('#checkout_land_form').submit(function(e) {
        e.preventDefault();
        var maxStock = parseInt($('#max_stock').val()) || 0;
        if(maxStock <= 0) { toastr.error('স্টকে নেই!'); return; }
        var paymentMethod = $('input[name="payment_method"]:checked').val() || 'cod';
        if(paymentMethod !== 'cod' && paymentMethod !== 'sslcommerz' && paymentMethod !== 'bkash' && paymentMethod !== 'nagad' && paymentMethod !== 'uddoktapay' && paymentMethod !== 'eps') {
            if(!$('#sender_number').val() || !$('#transaction_id').val()) { toastr.warning('পেমেন্ট নাম্বার + Transaction ID দিন'); return; }
        }
        var purchaseId = "PUR_{{ $productId }}_" + Date.now();
        $('#purchase_event_id').val(purchaseId);
        var $btn = $('#submit_btn');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> প্রসেসিং...');
        if(paymentMethod === 'sslcommerz' || paymentMethod === 'online') { this.action = "{{ url('/pay') }}"; this.submit(); return; }
        $.ajax({
            url: "{{ route('front.storelandData') }}", method:'POST', data: $(this).serialize(),
            success: function(res) {
                if(res.success) {
                    toastr.success(res.msg);
                    // Purchase (Meta + GA4) fires on the thank-you page with 'PUR_' + order_id — the id the server Conversions API uses.
                    if(paymentMethod === 'nagad') { var oid = res.order_id || res.url.split('/').pop(); window.location.href = "{{ url('nagad/pay') }}/" + oid; return; }
                    if(paymentMethod === 'uddoktapay') { var oid = res.order_id || res.url.split('/').pop(); window.location.href = "{{ url('uddoktapay/pay') }}/" + oid; return; }
                    if(paymentMethod === 'eps') { window.location.href = res.url; return; }
                    setTimeout(function() { window.location.href = res.url; }, 700);
                } else { toastr.error(res.msg || 'সমস্যা'); $btn.prop('disabled', false).text("{{ $ln_pg->btn_text_form ?? 'অর্ডার কনফার্ম করুন' }}"); }
            },
            error: function() { toastr.error('সার্ভারে সমস্যা'); $btn.prop('disabled', false).text("{{ $ln_pg->btn_text_form ?? 'অর্ডার কনফার্ম করুন' }}"); }
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