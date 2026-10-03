<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $ln_pg->title1 ?? 'GlowC Oil — ম্যাজিক ফর্মূলা' }}</title>
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
        // GlowC Oil theme: Yellow/Amber + Pink/Red CTA
        $brandColor = $ln_pg->theme_primary_col ?? '#f59e0b';
        $btnBg      = $ln_pg->btn_bg_color ?? '#e11d48';
        $btnTextCol = $ln_pg->btn_text_color ?? '#ffffff';
        $cdBg       = $ln_pg->countdown_bg_color ?? '#dc2626';
        $cdTxt      = $ln_pg->countdown_text_color ?? '#ffffff';
        $cdHours    = (int)($ln_pg->countdown_hours ?? 6);

        $productId   = $product->id ?? 0;
        $productName = $product->name ?? 'GlowC Oil';
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
        $lpEventBase = $lpEventBase ?? ('LP11_' . $productId . '_' . now()->format('YmdHis'));
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
        .fab { font-family: "Font Awesome 5 Brands" !important; }
        :root {
            --brand: {{ $brandColor }}; --btn-bg: {{ $btnBg }}; --btn-text: {{ $btnTextCol }};
            --amber-50: #fffbeb; --amber-100: #fef3c7; --amber-200: #fde68a;
            --amber-300: #fcd34d; --amber-400: #fbbf24; --amber-500: #f59e0b;
            --amber-600: #d97706; --amber-700: #b45309; --amber-800: #92400e;
            --rose-500: #f43f5e; --rose-600: #e11d48; --rose-700: #be123c;
            --orange-500: #f97316; --orange-600: #ea580c;
            --warm-bg: #fffaf0;
        }
        body { background: var(--warm-bg); color: #1f2937; line-height: 1.6; }

        /* ==== TOP COUNTDOWN ==== */
        .top-countdown { background: {{ $cdBg }}; color: {{ $cdTxt }}; padding: 9px 0; text-align: center; font-weight: 700; font-size: 0.9rem; }
        .top-countdown .timer { display: inline-flex; gap: 4px; margin-left: 8px; font-variant-numeric: tabular-nums; }
        .cd-box { background: rgba(0,0,0,0.2); padding: 2px 8px; border-radius: 4px; min-width: 32px; }

        /* ==== HEADER (Yellow gradient bar) ==== */
        .top-bar {
            background: linear-gradient(90deg, var(--amber-200) 0%, var(--amber-300) 50%, var(--amber-200) 100%);
            padding: 14px 0; position: sticky; top: 0; z-index: 100;
            box-shadow: 0 2px 8px rgba(180,83,9,0.1);
        }
        .brand-name { font-weight: 800; font-size: 1.25rem; color: var(--amber-800); display: inline-flex; align-items: center; gap: 8px; }
        .brand-icon { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--amber-500), var(--orange-600)); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 16px; }
        .header-cta {
            background: linear-gradient(135deg, var(--rose-500), var(--rose-600));
            color: #fff; padding: 8px 18px; border-radius: 50px;
            font-weight: 700; text-decoration: none; font-size: 0.92rem;
            display: inline-flex; align-items: center; gap: 6px;
            box-shadow: 0 4px 10px rgba(225,29,72,0.3);
        }
        .header-cta:hover { color: #fff; transform: translateY(-1px); }

        /* ==== HERO (Yellow/Amber gradient, CENTERED) ==== */
        .hero {
            padding: 36px 0 50px;
            background: linear-gradient(180deg, var(--amber-200) 0%, var(--amber-100) 60%, var(--amber-50) 100%);
            text-align: center; position: relative;
        }
        .hero-content { max-width: 720px; margin: 0 auto; padding: 0 16px; }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: #fff; color: var(--amber-800);
            padding: 6px 16px; border-radius: 50px;
            font-size: 0.82rem; font-weight: 700; margin-bottom: 16px;
            box-shadow: 0 3px 10px rgba(180,83,9,0.1); border: 1px solid var(--amber-300);
        }
        .hero-badge::before { content: '✦'; color: var(--amber-600); }
        .hero-title {
            font-size: 2.2rem; font-weight: 800; line-height: 1.25;
            color: var(--amber-900, #78350f); margin-bottom: 18px; letter-spacing: -0.5px;
        }
        .hero-title .accent { color: var(--orange-600); }
        .hero-video-wrap { max-width: 580px; margin: 0 auto 20px; background: #fff; border-radius: 16px; padding: 10px; box-shadow: 0 18px 36px rgba(180,83,9,0.18); }
        .hero-video-wrap .ratio { border-radius: 10px; overflow: hidden; }
        .hero-img-wrap { max-width: 380px; margin: 0 auto 20px; background: #fff; border-radius: 16px; padding: 16px; box-shadow: 0 18px 36px rgba(180,83,9,0.18); }
        .hero-img-wrap img { max-width: 100%; max-height: 320px; object-fit: contain; }

        .rating-row { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 14px; background: #fff; padding: 6px 14px; border-radius: 50px; box-shadow: 0 3px 10px rgba(0,0,0,0.06); }
        .rating-row .stars { color: var(--amber-500); font-weight: 700; }
        .rating-row .count { color: #64748b; font-size: 0.88rem; font-weight: 600; }

        .price-block { display: inline-flex; align-items: baseline; gap: 12px; flex-wrap: wrap; justify-content: center; margin-bottom: 18px; }
        .price-new { color: var(--orange-600); font-size: 2.3rem; font-weight: 800; }
        .price-old { color: #94a3b8; text-decoration: line-through; font-size: 1.2rem; font-weight: 600; }
        .price-save { background: var(--rose-600); color: #fff; padding: 5px 12px; border-radius: 50px; font-size: 0.85rem; font-weight: 800; }

        .hero-ctas { display: inline-flex; gap: 12px; flex-wrap: wrap; justify-content: center; margin-bottom: 20px; }
        .btn-primary-cta {
            background: linear-gradient(135deg, var(--rose-500), var(--rose-600));
            color: #fff; border: none; border-radius: 50px; padding: 14px 28px;
            font-weight: 800; font-size: 1rem; text-decoration: none;
            display: inline-flex; align-items: center; gap: 10px;
            box-shadow: 0 10px 22px rgba(225,29,72,0.3); transition: all .2s ease;
        }
        .btn-primary-cta:hover { color: #fff; transform: translateY(-2px); box-shadow: 0 14px 28px rgba(225,29,72,0.4); }
        .btn-secondary-cta {
            background: linear-gradient(135deg, var(--amber-400), var(--amber-500));
            color: var(--amber-900, #78350f); border: none; border-radius: 50px; padding: 14px 28px;
            font-weight: 800; font-size: 1rem; text-decoration: none;
            display: inline-flex; align-items: center; gap: 10px;
            box-shadow: 0 8px 18px rgba(245,158,11,0.3);
        }
        .btn-secondary-cta:hover { color: var(--amber-900, #78350f); transform: translateY(-2px); }

        .hero-trust { display: flex; flex-wrap: wrap; gap: 14px; justify-content: center; color: var(--amber-800); font-size: 0.88rem; font-weight: 600; }
        .hero-trust span { display: inline-flex; align-items: center; gap: 6px; }
        .hero-trust i { color: var(--orange-600); }

        /* ==== SECTION COMMON ==== */
        .sec { padding: 50px 0; }
        .sec-title-wrap { text-align: center; margin-bottom: 32px; }
        .sec-title { font-size: 1.65rem; font-weight: 800; color: #1f2937; margin-bottom: 6px; }
        .sec-sub { color: #6b7280; font-size: 0.94rem; max-width: 560px; margin: 0 auto; }

        /* ==== BENEFITS (6 cards) DEEP THEME ==== */
        .benefits { 
            background: linear-gradient(135deg, var(--amber-600), var(--orange-600)); 
            padding: 80px 0; /* প্যাডিং বাড়ানো হয়েছে */
        }
        .benefits .sec-title { color: #ffffff; font-size: 2rem; } /* টাইটেল সাইজ ও কালার আপডেট */
        .benefits .sec-sub { color: #fffbeb; font-size: 1.05rem; opacity: 0.9; }
        .ben-card {
            background: #fff; border-radius: 14px; padding: 24px 18px; height: 100%;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1); border: none;
            transition: all .3s ease;
        }
        .ben-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.2); }
        .ben-icon {
            width: 52px; height: 52px; border-radius: 12px;
            background: var(--amber-100); color: var(--orange-600);
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 24px; margin-bottom: 16px;
        }
        .ben-title { font-weight: 800; font-size: 1.08rem; margin-bottom: 8px; color: #1f2937; }
        .ben-desc { color: #6b7280; font-size: 0.9rem; margin: 0; }

        /* ==== 4-STEP USAGE (Horizontal numbered cards) ==== */
        .usage-sec { background: var(--amber-50); }
        .step-card {
            background: #fff; border-radius: 14px; padding: 22px 18px;
            height: 100%; box-shadow: 0 3px 14px rgba(0,0,0,0.05);
            border: 1px solid var(--amber-100); transition: all .2s ease;
            text-align: left; position: relative;
        }
        .step-card:hover { transform: translateY(-3px); box-shadow: 0 12px 24px rgba(245,158,11,0.15); }
        .step-num {
            width: 38px; height: 38px; border-radius: 50%;
            background: linear-gradient(135deg, var(--orange-500), var(--rose-600));
            color: #fff; display: inline-flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 1.05rem; margin-bottom: 12px;
            box-shadow: 0 4px 10px rgba(234,88,12,0.3);
        }
        .step-title { font-weight: 800; font-size: 1rem; margin-bottom: 5px; color: #1f2937; }
        .step-desc { color: #6b7280; font-size: 0.86rem; margin: 0; }

        /* ==== INGREDIENTS (Image + Pills) ==== */
        .ingredients-sec { background: #fff; }
        .ing-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 36px; align-items: center; max-width: 980px; margin: 0 auto; }
        @media (max-width: 768px) { .ing-grid { grid-template-columns: 1fr; gap: 22px; } }
        .ing-img-box {
            background: linear-gradient(135deg, var(--amber-50), var(--amber-100));
            border-radius: 20px; padding: 30px; text-align: center;
            box-shadow: 0 6px 18px rgba(180,83,9,0.10);
        }
        .ing-img-box img { max-width: 100%; max-height: 320px; object-fit: contain; }
        .ing-info h3 { font-size: 1.4rem; font-weight: 800; color: var(--amber-800); margin-bottom: 8px; }
        .ing-info p { color: #6b7280; font-size: 0.92rem; margin-bottom: 18px; }
        .ing-pills-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        @media (max-width: 480px) { .ing-pills-grid { grid-template-columns: 1fr; } }
        .ing-pill {
            background: var(--amber-50); border: 1px solid var(--amber-200);
            border-radius: 50px; padding: 10px 16px;
            font-weight: 700; font-size: 0.92rem; color: var(--amber-800);
            display: flex; align-items: center; gap: 8px;
        }
        .ing-pill i { color: #16a34a; font-size: 14px; }

        /* ==== REVIEWS ==== */
        .reviews-sec { background: var(--amber-50); }
        .review-card {
            background: #fff; border-radius: 14px; padding: 20px 18px;
            height: 100%; box-shadow: 0 4px 14px rgba(0,0,0,0.06);
            border: 1px solid var(--amber-100);
        }
        .review-stars { color: var(--amber-500); margin-bottom: 8px; font-size: 0.9rem; }
        .review-text { color: #44403c; font-size: 0.9rem; line-height: 1.6; margin-bottom: 12px; }
        .review-author { display: flex; align-items: center; gap: 10px; padding-top: 10px; border-top: 1px solid var(--amber-100); }
        .review-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: linear-gradient(135deg, var(--amber-500), var(--orange-600));
            color: #fff; display: inline-flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 0.92rem;
        }
        .reviewer-name { font-weight: 700; color: #1f2937; font-size: 0.88rem; }
        .reviewer-loc { color: #94a3b8; font-size: 0.78rem; }

        /* ==== ORDER FORM ==== */
        .order-form-section { background: var(--amber-50); padding: 50px 0; }
        .order-card {
            background: #fff; border-radius: 18px; padding: 28px;
            box-shadow: 0 14px 36px rgba(180,83,9,0.15);
            max-width: 560px; margin: 0 auto; border: 1px solid var(--amber-200);
        }
        .order-header { text-align: center; margin-bottom: 18px; }
        .order-header .sec-pill { display: inline-block; background: var(--amber-100); color: var(--amber-800); padding: 5px 14px; border-radius: 50px; font-size: 0.78rem; font-weight: 700; margin-bottom: 8px; }
        .form-control, .form-select { border-radius: 10px; padding: 11px 14px; border: 1px solid #e5e7eb; background: #fff; font-size: 0.94rem; }
        .form-control:focus, .form-select:focus { border-color: var(--amber-500); box-shadow: 0 0 0 3px rgba(245,158,11,0.12); }
        .form-label { font-weight: 700; color: #1f2937; margin-bottom: 6px; font-size: 0.9rem; }

        .delivery-radio-group { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        @media (max-width: 480px) { .delivery-radio-group { grid-template-columns: 1fr; } }
        .delivery-radio-box {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 14px; border: 2px solid #e5e7eb;
            border-radius: 10px; cursor: pointer; background: #fff;
            transition: all .2s ease;
        }
        .delivery-radio-box.active { border-color: var(--amber-500); background: var(--amber-50); }
        .delivery-radio-box input { accent-color: var(--amber-500); }
        .delivery-radio-box .label-text { display: flex; flex-direction: column; }
        .delivery-radio-box .area { font-weight: 700; font-size: 0.9rem; color: #1f2937; }
        .delivery-radio-box .charge { color: var(--orange-600); font-size: 0.82rem; font-weight: 700; }

        .package-card {
            display: flex; justify-content: space-between; align-items: center;
            gap: 12px; padding: 12px 14px; border: 2px solid #e5e7eb;
            border-radius: 10px; margin-bottom: 8px; cursor: pointer;
            background: #fff; transition: all .2s ease;
        }
        .package-card.active-pkg { border-color: var(--amber-500); background: var(--amber-50); }
        .package-card .pkg-left { display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0; }
        .package-card .pkg-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
        .pkg-radio { width: 17px; height: 17px; accent-color: var(--amber-500); cursor: pointer; flex-shrink: 0; }
        .pkg-title { font-weight: 700; font-size: 0.92rem; color: #1f2937; }
        .pkg-qty-box { display: inline-flex; align-items: center; justify-content: center; border: 1px solid #e5e7eb; border-radius: 6px; background: #f9fafb; min-width: 34px; height: 28px; font-weight: 800; color: var(--amber-700); font-size: 0.85rem; }
        .pkg-price { font-weight: 800; color: var(--amber-700); font-size: 0.98rem; }

        .qty-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 10px; background: #fff; }
        .pro-qty { display: inline-flex; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
        .pro-qty span { padding: 6px 14px; cursor: pointer; user-select: none; font-weight: 800; background: #f9fafb; color: var(--amber-700); }
        .pro-qty input { width: 44px; border: none; text-align: center; font-weight: 700; }

        .price-summary { background: var(--amber-50); border-radius: 12px; padding: 14px 16px; margin: 14px 0; border: 1px solid var(--amber-200); }
        .price-summary .row-line { display: flex; justify-content: space-between; padding: 3px 0; color: #57534e; font-size: 0.92rem; font-weight: 600; }
        .price-summary .grand { font-size: 1.05rem; color: var(--amber-800); border-top: 1px dashed var(--amber-300); margin-top: 6px; padding-top: 10px; font-weight: 800; }

        .coupon-section { background: #f9fafb; border: 1px dashed #e5e7eb; padding: 12px; border-radius: 10px; margin: 12px 0; }
        .coupon-input-group { display: flex; overflow: hidden; border: 1px solid #d1d5db; border-radius: 8px; background: #fff; }
        .coupon-input-group input { border: none; padding: 9px 12px; flex-grow: 1; outline: none; font-size: 0.9rem; }
        .coupon-input-group button { border: none; background: var(--amber-700); color: #fff; padding: 0 20px; font-weight: 700; cursor: pointer; font-size: 0.88rem; }

        .payment-radio-box { display: flex; align-items: center; gap: 10px; padding: 11px 14px; border: 2px solid #e5e7eb; border-radius: 10px; margin-bottom: 6px; cursor: pointer; background: #fff; font-size: 0.92rem; }
        .payment-radio-box.active { border-color: var(--amber-500); background: var(--amber-50); }
        .payment-radio-box img { height: 20px; }
        .manual-payment-box { background: #fef3c7; border-radius: 8px; padding: 10px; margin-top: 6px; display: none; }
        .manual-payment-box.show { display: block; }

        .btn-order-confirm {
            width: 100%;
            background: linear-gradient(135deg, var(--orange-500), var(--orange-600));
            color: #fff; border: none; border-radius: 12px; padding: 15px;
            font-weight: 800; font-size: 1.1rem;
            box-shadow: 0 10px 24px rgba(234,88,12,0.32); transition: all .2s ease;
        }
        .btn-order-confirm:hover { transform: translateY(-2px); }
        .btn-order-confirm:disabled { opacity: 0.7; cursor: wait; }

        .security-note { background: #ecfdf5; color: #065f46; border-radius: 10px; padding: 10px 14px; font-size: 0.84rem; font-weight: 600; text-align: center; margin-top: 12px; }

        /* ==== FAQ ==== */
        .faq-sec { background: var(--amber-50); }
        .faq-card { background: #fff; border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.04); margin-bottom: 10px; border: 1px solid var(--amber-100); }
        .faq-q { padding: 15px 18px; cursor: pointer; font-weight: 700; display: flex; justify-content: space-between; align-items: center; color: #1f2937; font-size: 0.95rem; }
        .faq-q i { color: var(--amber-700); transition: transform .2s ease; }
        .faq-card.open .faq-q i { transform: rotate(180deg); }
        .faq-a { padding: 0 18px 15px; color: #57534e; display: none; line-height: 1.65; font-size: 0.9rem; }
        .faq-card.open .faq-a { display: block; }

        /* ==== FOOTER ==== */
        footer { background: linear-gradient(135deg, var(--amber-200), var(--amber-300)); color: var(--amber-800); padding: 30px 0 22px; text-align: center; border-top: 2px solid var(--amber-400); }
        footer .fcompany { font-weight: 800; font-size: 1.1rem; margin-bottom: 8px; display: inline-flex; align-items: center; gap: 8px; }
        footer .fcompany i { color: var(--orange-600); }
        footer a { color: var(--amber-900, #78350f); text-decoration: none; font-weight: 600; }
        footer .copyright { color: var(--amber-700); font-size: 0.84rem; margin-top: 14px; padding-top: 14px; border-top: 1px solid rgba(180,83,9,0.15); }

        /* ==== STICKY CTA BUTTON DESIGN ==== */
        .mobile-sticky {
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 99; 
            background: #fff; padding: 12px; 
            box-shadow: 0 -4px 15px rgba(0,0,0,0.1); 
            border-top: 1px solid var(--amber-200);
        }
        .mobile-sticky .d-flex { gap: 10px; }
        .btn-outline-sticky {
            flex: 1; display: flex; justify-content: center; align-items: center; gap: 8px;
            border: 2px solid var(--amber-600); color: var(--amber-800);
            background: #fff; border-radius: 50px;
            font-weight: 800; font-size: 0.95rem; text-decoration: none;
            padding: 12px 10px; transition: all .2s ease;
        }
        .btn-outline-sticky:hover { background: var(--amber-50); color: var(--amber-800); }
        .sticky-order-btn {
            flex: 1; padding: 12px 10px; margin: 0; width: auto; justify-content: center;
        }

        /* Mobile */
        @media (max-width: 768px) {
            .top-countdown { padding: 8px 6px; font-size: 0.82rem; }
            .top-countdown .timer { font-size: 0.8rem; gap: 3px; margin-left: 6px; }
            .cd-box { min-width: 28px; padding: 2px 6px; }
            .hero { padding: 28px 0 36px; }
            .hero-title { font-size: 1.55rem; }
            .hero-video-wrap, .hero-img-wrap { padding: 8px; }
            .hero-img-wrap img { max-height: 260px; }
            .price-new { font-size: 1.65rem; }
            .sec { padding: 32px 0; }
            .sec-title { font-size: 1.3rem; }
            .ben-card { padding: 18px 14px; }
            .step-card { padding: 18px 14px; }
            .ing-img-box { padding: 22px; }
            .order-card { padding: 20px; }
            body { padding-bottom: 76px; }
        }
        @media (min-width: 769px) { .mobile-sticky { display: none; } }
        /* ✅ Single variation hide */
        .single-variation .variation-wrap { display: none !important; }
        .variation-wrap.single-product-hidden { display: none !important; }
        /* ✅ UNIVERSAL MOBILE IMAGE FIX */
        body { overflow-x: hidden !important; max-width: 100vw !important; }
        img { max-width: 100% !important; height: auto !important; }
        * { box-sizing: border-box; }
        .hero-img-wrap, .hero-video-wrap {
            overflow: hidden !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .hero-img-wrap img {
            max-width: 100% !important;
            width: auto !important;
            height: auto !important;
            object-fit: contain !important;
            display: block !important;
            margin: 0 auto !important;
        }
        .hero-video-wrap .ratio, .hero-video-wrap iframe {
            width: 100% !important;
            max-width: 100% !important;
        }
        @media (max-width: 768px) {
            .hero, .container, .row, section { overflow-x: hidden !important; }
            .hero-img-wrap, .hero-video-wrap { padding: 10px !important; margin-left: 0 !important; margin-right: 0 !important; }
            .hero-img-wrap img { max-height: 260px !important; width: 100% !important; }
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

{{-- TOP COUNTDOWN --}}
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

{{-- HEADER --}}
<header class="top-bar">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="brand-name">
            <span class="brand-icon"><i class="fas fa-leaf"></i></span>
            {{ $ln_pg->footer_company ?? 'GlowC Oil' }}
        </div>
        <a href="#order-form" class="header-cta">
            <i class="fas fa-shopping-cart"></i> {{ $ln_pg->btn_text_hero ?? 'এখনই অর্ডার' }}
        </a>
    </div>
</header>

{{-- HERO (CENTERED) --}}
<section class="hero">
    <div class="hero-content">
        <div class="hero-badge">{{ $ln_pg->promise_img_badge ?? '১০০% অরিজিনাল প্রোডাক্ট' }}</div>

        <h1 class="hero-title">{{ $ln_pg->title1 ?? 'উজ্জ্বল, মসৃণ ও দাগমুক্ত ত্বকের ম্যাজিক ফর্মূলা' }}</h1>
        @if($ln_pg->title2)<p style="color:#57534e; font-size:1.02rem; max-width:560px; margin:0 auto 16px;">{{ $ln_pg->title2 }}</p>@endif

        @if(!empty($ln_pg->video_url))
            <div class="hero-video-wrap">
                @php $videoUrl = trim($ln_pg->video_url); @endphp
                @if(stripos($videoUrl, '<iframe') !== false)
                    <div class="ratio ratio-16x9">{!! $videoUrl !!}</div>
                @else
                    <div class="ratio ratio-16x9"><iframe src="{{ $videoUrl }}" frameborder="0" allowfullscreen></iframe></div>
                @endif
            </div>
        @elseif(!empty($ln_pg->right_product_image) || !empty($product->image))
            <div class="hero-img-wrap"><img src="{{ $heroImage }}" alt="{{ $productName }}"></div>
        @endif

        @if($ln_pg->hero_rating)
        <div class="rating-row">
            <span class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></span>
            <span style="font-weight:700;">{{ $ln_pg->hero_rating }}</span>
            <span class="count">· {{ $ln_pg->hero_rating_count }}</span>
        </div>
        @endif

        <div class="price-block">
            <span class="price-new">৳{{ $defaultPrice }}</span>
            @if($oldPrice)<span class="price-old">৳{{ $oldPrice }}</span>@endif
            @if($ln_pg->discount_save_text)<span class="price-save">{{ $ln_pg->discount_save_text }}</span>@endif
        </div>

        <div class="hero-ctas">
            <a href="#order-form" class="btn-primary-cta">
                <i class="fas fa-shopping-cart"></i> এখনই অর্ডার করুন
            </a>
            @if($phoneNumber)
            <a href="tel:{{ $phoneNumber }}" class="btn-secondary-cta">
                <i class="fas fa-phone"></i> মূল্যেও সাশ্রয়ী
            </a>
            @endif
        </div>

        <div class="hero-trust">
            <span><i class="fas fa-check-circle"></i> {{ $ln_pg->pay_text ?? 'ক্যাশ অন ডেলিভারি' }}</span>
            <span><i class="fas fa-shield-alt"></i> ১০০% অরিজিনাল</span>
            <span>১০০% নিরাপদ</span>
        </div>
    </div>
</section>

{{-- BENEFITS (6 cards) - MOVED ABOVE GALLERY & STYLED --}}
@if($ln_pg->id_1_title || $ln_pg->id_2_title)
<section class="sec benefits">
    <div class="container">
        <div class="sec-title-wrap">
            <h2 class="sec-title">{{ $ln_pg->feature_title ?? 'কেন এই অয়েল সবার পছন্দ?' }}</h2>
            <p class="sec-sub">প্রকৃতির সেরা উপাদান দিয়ে তৈরি — যা সবচেয়ে ভালো</p>
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

{{-- ✅ GALLERY SLIDER (Bootstrap Carousel) - FIXED CONTAINER --}}
@if(isset($ln_pg->images) && $ln_pg->images->count() > 0)
<section class="sec" style="background:#f8fafc; padding: 36px 0;">
    <div class="container">
        <div class="sec-title-wrap mb-4">
            <h2 class="sec-title">প্রোডাক্ট গ্যালারি</h2>
            <p class="sec-sub">বিভিন্ন অ্যাঙ্গেল থেকে দেখুন</p>
        </div>
        
        {{-- Slider Box: No white padding, image will cover the full box --}}
        <div class="mx-auto" style="max-width: 750px; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.15);">
            <div id="productGallerySlider" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2500">
                <div class="carousel-inner">
                    @foreach($ln_pg->images as $key => $img)
                        <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                            <img src="{{ asset('landing_sliders/'.$img->image) }}" class="d-block w-100" alt="Gallery {{ $key + 1 }}" style="height: 450px; object-fit: cover; width: 100%;">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- 4-STEP USAGE --}}
@if($ln_pg->promise_1_title || $ln_pg->promise_2_title || $ln_pg->id_7_title)
<section class="sec usage-sec">
    <div class="container">
        <div class="sec-title-wrap">
            <h2 class="sec-title">কীভাবে ব্যবহার করবেন</h2>
            <p class="sec-sub">মাত্র ৪টি সহজ ধাপে — দিনে ২ বার ব্যবহার দৃশ্যমান ফল</p>
        </div>
        <div class="row g-3">
            @php
                $steps = [
                    ['t' => $ln_pg->promise_1_title, 'd' => $ln_pg->promise_1_desc],
                    ['t' => $ln_pg->promise_2_title, 'd' => $ln_pg->promise_2_desc],
                    ['t' => $ln_pg->promise_3_title, 'd' => $ln_pg->promise_3_desc],
                    ['t' => $ln_pg->id_7_title,      'd' => $ln_pg->id_7_desc],
                ];
                $stepNumBn = ['১', '২', '৩', '৪'];
            @endphp
            @foreach($steps as $idx => $st)
                @if($st['t'])
                <div class="col-md-3 col-sm-6">
                    <div class="step-card">
                        <div class="step-num">{{ $stepNumBn[$idx] }}</div>
                        <div class="step-title">{{ $st['t'] }}</div>
                        <p class="step-desc">{{ $st['d'] }}</p>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- INGREDIENTS (Image + Pills) --}}
@if($ln_pg->spec_1_label)
<section class="sec ingredients-sec">
    <div class="container">
        <div class="ing-grid">
            <div class="ing-img-box"><img src="{{ $heroImage }}" alt="{{ $productName }}"></div>
            <div class="ing-info">
                <h3>{{ $ln_pg->spec_title ?? '১০০% প্রাকৃতিক উপাদান' }}</h3>
                <p>প্রকৃতির সেরা প্রাকৃতিক উপাদানের সমন্বয়ে তৈরি, কোনো কেমিক্যাল ও ক্ষতিকর উপাদান নেই</p>
                <div class="ing-pills-grid">
                    @for($i=1; $i<=6; $i++)
                        @if($ln_pg->{'spec_'.$i.'_label'})
                        <div class="ing-pill"><i class="fas fa-check-circle"></i> {{ $ln_pg->{'spec_'.$i.'_label'} }}</div>
                        @endif
                    @endfor
                </div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- REVIEWS --}}
@if($ln_pg->rev_1_text)
<section class="sec reviews-sec">
    <div class="container">
        <div class="sec-title-wrap">
            <h2 class="sec-title">{{ $ln_pg->review_title ?? 'গ্রাহকদের রিভিউ' }}</h2>
            <p class="sec-sub">{{ $ln_pg->review_subtitle ?? '২,৩০০+ গ্রাহকের অভিজ্ঞতা' }}</p>
        </div>
        <div class="row g-3">
            @for($i=1; $i<=3; $i++)
                @if($ln_pg->{'rev_'.$i.'_text'})
                <div class="col-md-4">
                    <div class="review-card">
                        <div class="review-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <p class="review-text">"{{ $ln_pg->{'rev_'.$i.'_text'} }}"</p>
                        <div class="review-author">
                            <div class="review-avatar">{{ mb_substr($ln_pg->{'rev_'.$i.'_name'} ?? 'A', 0, 1) }}</div>
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
            <h2 class="sec-title">{{ $ln_pg->form_title ?? 'অর্ডার ফর্ম পূরণ করুন' }}</h2>
            <p class="sec-sub">{{ $ln_pg->form_subtitle ?? 'আমাদের কাস্টমারকেয়ার থেকে কনফার্মেশন কল আসবে' }}</p>
        </div>

        <div class="order-card">
            <form id="checkout_land_form" action="{{ route('front.storelandData') }}" method="POST">
                @csrf
                        {{-- কোন landing page থেকে order এলো (Order Management-এর Source-এ দেখায়) --}}
                        <input type="hidden" name="landing_page_type" value="11">
                <input type="hidden" name="prd_id" value="{{ $productId }}">
                <input type="hidden" name="amount" id="subtotal_input" value="{{ $defaultPrice }}">
                <input type="hidden" name="final_amount" id="final_total_input" value="{{ $defaultPrice }}">
                <input type="hidden" name="quantity" id="form_qty" value="1">
                <input type="hidden" name="purchase_event_id" id="purchase_event_id" value="">
                <input type="hidden" name="coupon_code" id="hidden_coupon_code" value="">
                <input type="hidden" name="discount" id="hidden_discount" value="0">

                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label">{{ $ln_pg->name_label ?? 'আপনার নাম *' }}</label>
                        <input type="text" name="first_name" class="form-control" placeholder="{{ $ln_pg->name_placeholder ?? 'পূর্ণ নাম লিখুন' }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ $ln_pg->phone_label ?? 'মোবাইল নম্বর *' }}</label>
                        <input type="tel" name="mobile" class="form-control" placeholder="01XXXXXXXXX" maxlength="11" required>
                    </div>
                </div>

                <div class="mb-3 mt-2">
                    <label class="form-label">{{ $ln_pg->address_label ?? 'সম্পূর্ণ ঠিকানা *' }}</label>
                    <textarea name="shipping_address" class="form-control" rows="2" placeholder="{{ $ln_pg->address_placeholder ?? 'বাসা, রোড, এলাকা, থানা' }}" required></textarea>
                </div>

                <div class="mb-3">
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

                <div class="mb-3">
                    <label class="form-label">প্যাকেজ:</label>
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

                @if($variations->count() > 1)
                <div class="mb-3">
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
                        $svPrice = (($sv->after_discount_price ?? 0) > 0) ? $sv->after_discount_price : ($sv->price ?? $product->sell_price ?? 0);
                        $svStock = $sv->stocks->sum('quantity');
                    @endphp
                    <input type="hidden" name="variation_id" id="variation_select" value="{{ $sv->id }}" data-price="{{ $svPrice }}" data-stock="{{ $svStock }}">
                @else
                    <input type="hidden" name="variation_id" id="variation_select" value="">
                @endif
                <input type="hidden" id="max_stock" value="{{ $defaultStock }}">

                <div class="mb-3">
                    <label class="form-label">পরিমাণ</label>
                    <div class="qty-row">
                        <span style="font-size:0.9rem; color:#6b7280;">পরিমাণ সিলেক্ট করুন</span>
                        <div class="pro-qty">
                            <span class="decrease-qty">−</span>
                            <input type="text" class="inner_qty" value="1" readonly>
                            <span class="increase-qty">+</span>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">নোট (ঐচ্ছিক)</label>
                    <textarea name="order_note" class="form-control" rows="2" placeholder="বিশেষ কোনো নির্দেশনা থাকলে লিখুন"></textarea>
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
                    <label class="payment-radio-box active">
                        <input type="radio" name="payment_method" value="cod" checked onchange="togglePaymentAction('cod')">
                        <i class="fas fa-money-bill-wave text-success"></i>
                        <span class="fw-bold">{{ $ln_pg->cod_title ?? 'ক্যাশ অন ডেলিভারি' }}</span>
                    </label>
                    @endif
                    @if(isset($information->ssl_active) && $information->ssl_active == 1)
                    <label class="payment-radio-box">
                        <input type="radio" name="payment_method" value="sslcommerz" onchange="togglePaymentAction('sslcommerz')">
                        <i class="fas fa-credit-card text-primary"></i>
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
                    @if(isset($information->uddoktapay_active) && $information->uddoktapay_active == 1)
                    <label class="payment-radio-box">
                        <input type="radio" name="payment_method" value="uddoktapay" onchange="togglePaymentAction('uddoktapay')">
                        <span class="fw-bold">UddoktaPay</span>
                    </label>
                    @endif
                    @foreach($activeManuals as $mp)
                    <label class="payment-radio-box">
                        <input type="radio" name="payment_method" value="{{ $mp->name }}" data-number="{{ $mp->number }}" data-type="{{ $mp->type }}" onchange="togglePaymentAction('manual', '{{ $mp->name }}', '{{ $mp->number }}', '{{ $mp->type }}')">
                        <span class="fw-bold">{{ $mp->name }}</span>
                    </label>
                    @endforeach
                    <div class="manual-payment-box" id="manualPaymentBox">
                        <p class="mb-2 small fw-bold">পেমেন্ট পাঠান <span id="manual_number" class="text-danger"></span> (<span id="manual_type"></span>) এ</p>
                        <input type="text" name="sender_number" id="sender_number" class="form-control mb-2" placeholder="যে নাম্বার থেকে পাঠিয়েছেন">
                        <input type="text" name="transaction_id" id="transaction_id" class="form-control" placeholder="ট্রানজেকশন ID">
                    </div>
                </div>

                <div class="price-summary">
                    <div class="row-line"><span>সাবটোটাল (১×<span id="subtotal_disp">{{ $defaultPrice }}</span>):</span> <span>৳<span id="subtotal_disp2">{{ $defaultPrice }}</span></span></div>
                    <div class="row-line" id="discount_row" style="display:none;"><span>ছাড়:</span> <span>- ৳<span id="discount_disp">0</span></span></div>
                    <div class="row-line"><span>ডেলিভারি চার্জ:</span> <span>৳<span id="delivery_disp">0</span></span></div>
                    <div class="row-line grand"><span>{{ $ln_pg->total_bill_label ?? 'মোট পরিশোধ' }}:</span> <span>৳<span id="grand_total_disp">{{ $defaultPrice }}</span></span></div>
                </div>

                <button type="submit" id="submit_btn" class="btn-order-confirm">
                    <i class="fas fa-check me-1"></i> {{ $ln_pg->btn_text_form ?? 'অর্ডার কনফার্ম করুন' }} — ৳<span id="btn_total">{{ $defaultPrice }}</span>
                </button>

                @if($ln_pg->security_badge_text)
                <div class="security-note">{{ $ln_pg->security_badge_text }}</div>
                @else
                <div class="security-note">আপনার তথ্য সম্পূর্ণ সুরক্ষিত</div>
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
            <h2 class="sec-title">{{ $ln_pg->faq_title ?? 'সাধারণ জিজ্ঞাসা' }}</h2>
            <p class="sec-sub">আপনার জিজ্ঞাসার প্রয়োজনীয় উত্তর</p>
        </div>
        <div class="mx-auto" style="max-width:720px;">
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

{{-- FOOTER --}}
<footer>
    <div class="container">
        <div class="fcompany"><i class="fas fa-leaf"></i> {{ $ln_pg->footer_company ?? 'GlowC Oil' }}</div>
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
        
        <a href="#order-form" class="btn-primary-cta sticky-order-btn">
            অর্ডার করুন
        </a>
    </div>
</div>

<script>
    (function() {
        var hours = {{ $cdHours }};
        if(hours <= 0) return;
        var key = 'lp11_cd_end_{{ $ln_pg->id }}';
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

    // ✅ প্যাকেজ চেক করার লজিক
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

    // ✅ ফিক্সড recalc ফাংশন
    function recalc() {
        var pkg = $('input[name="selected_package_id"]:checked');
        var qty = parseInt($('.inner_qty').val()) || 1;
        var unit;

        if (hasPackages) {
            // প্যাকেজ থাকলে কোয়ান্টিটি এবং দাম সিলেক্ট করা প্যাকেজ থেকেই আসবে
            unit = parseFloat(pkg.data('price')) || 0;
            qty = parseInt(pkg.data('qty')) || 1;
            
            // UI-তে এবং হিডেন ইনপুটে কোয়ান্টিটি সাথে সাথে আপডেট করবে
            $('.inner_qty').val(qty); 
            $('#form_qty').val(qty);
        } else {
            // প্যাকেজ না থাকলে ভেরিয়েশন বা ডিফল্ট দামের সাথে ম্যানুয়াল কোয়ান্টিটি গুণ হবে
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

    function applyCouponLand() {
        var code = $('#coupon_code').val();
        if(!code) { $('#coupon_msg').text('কুপন কোড দিন').css('color','red'); return; }
        var unit = parseFloat($('#subtotal_input').val()) || 0;
        $('#coupon_btn_submit').text('...').prop('disabled', true);
        $.ajax({
            url: "{{ route('front.getCouponDiscount') }}", type:'POST',
            data: { coupon_code: code, amount: unit, _token: "{{ csrf_token() }}" },
            success: function(res) {
                if(res.success) { $('#hidden_coupon_code').val(code); $('#hidden_discount').val(res.discount); $('#coupon_msg').text(res.msg).css('color','green'); }
                else { $('#hidden_coupon_code').val(''); $('#hidden_discount').val(0); $('#coupon_msg').text(res.msg).css('color','red'); }
                recalc();
            },
            complete: function() { $('#coupon_btn_submit').text('APPLY').prop('disabled', false); }
        });
    }
    window.applyCouponLand = applyCouponLand;

    function togglePaymentAction(method, mName, mNumber, mType) {
        $('.payment-radio-box').removeClass('active');
        $('input[name="payment_method"]:checked').closest('.payment-radio-box').addClass('active');
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