<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $ln_pg->title1 ?: ($product->name ?? '') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700;800&family=Bebas+Neue&display=swap" rel="stylesheet">
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
        $defaultPrice = (float) ($ln_pg->new_price ?: ($currentProduct->after_discount ?: ($currentProduct->sell_price ?: 899)));
        $oldPrice     = (float) ($ln_pg->old_price ?: 1499);
        $defaultStock = $currentProduct->stock_quantity ?? 99;
        $productId    = $currentProduct->id ?? 0;
        $productName  = $currentProduct->name ?? ($ln_pg->title1);
        $contentCategory = $currentProduct->category->name ?? 'Jersey';

        $sliderImages = $ln_pg->images ?? collect();
        $heroBg = !empty($ln_pg->landing_bg) ? asset('landing_pages/'.$ln_pg->landing_bg) : '';
        $mainProductImg = !empty($ln_pg->right_product_image) ? asset('landing_pages/'.$ln_pg->right_product_image) : ($currentProduct && $currentProduct->image ? (function_exists('getImage') ? getImage('products', $currentProduct->image) : asset('products/'.$currentProduct->image)) : '');

        // Helper: admin জা boshay নাই, কোনো fallback text দেখাবে না।
        // (২য় parameter accept করি backwards-compatibility-র জন্য, কিন্তু ignore করি।)
        $clean = function($val, $fallback = null) {
            if($val === null) return '';
            $trim = trim($val);
            if($trim === '') return '';
            $stripped = trim(html_entity_decode(strip_tags($val)));
            if($stripped === '') return '';
            if(preg_match('/^[\?\s\.,\-_!]+$/u', $stripped)) return '';
            return $val;
        };

        // Brand: title2 থেকে dynamic — / সিগ্ন্যাল দিয়ে split
        $brandRaw = $clean($ln_pg->title2 ?? null, 'প্রিমিয়াম ফুটবল জার্সি / জার্সি হাব');
        $brandParts = explode('/', $brandRaw);
        $brandMain = trim($brandParts[0] ?? '');
        $brandLogo = isset($brandParts[1]) ? trim($brandParts[1]) : $brandMain;
        if($brandMain === '') $brandMain = 'প্রিমিয়াম ফুটবল জার্সি';
        if($brandLogo === '') $brandLogo = $brandMain;

        // Hero Title parse — split on × or x for two-team layout
        $heroTitle = $clean($ln_pg->title1 ?? null, '');
        // Only treat as a two-team jersey page when the admin's title actually contains × or x.
        $hasTeamSeparator = !empty($heroTitle) && (bool) preg_match('/\s+[×x]\s+/u', $heroTitle);
        $teamParts = $hasTeamSeparator ? preg_split('/\s*[×x]\s*/u', $heroTitle, 2) : [$heroTitle];
        $team1 = trim($teamParts[0] ?? '');
        $team2 = trim($teamParts[1] ?? '');

    @endphp

    {{-- ===== TikTok Pixel ===== --}}
    @if(!empty($ttPixelId))
    <script>
    !function (w, d, t) {
        w.TiktokAnalyticsObject = t;
        var ttq = w[t] = w[t] || [];
        ttq.methods = ["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"];
        ttq.setAndDefer = function (t, e) { t[e] = function () { t.push([e].concat(Array.prototype.slice.call(arguments, 0))); }; };
        for (var i = 0; i < ttq.methods.length; i++) ttq.setAndDefer(ttq, ttq.methods[i]);
        ttq.instance = function (t) { for (var e = ttq._i[t] || [], n = 0; n < ttq.methods.length; n++) ttq.setAndDefer(e, ttq.methods[n]); return e; };
        ttq.load = function (e, n) {
            var r = "https://analytics.tiktok.com/i18n/pixel/events.js"; var o = n && n.partner;
            ttq._i = ttq._i || {}; ttq._i[e] = []; ttq._i[e]._u = r;
            ttq._t = ttq._t || {}; ttq._t[e] = +new Date;
            ttq._o = ttq._o || {}; ttq._o[e] = n || {};
            var s = document.createElement("script"); s.type = "text/javascript"; s.async = !0;
            s.src = r + "?sdkid=" + e + "&lib=" + t;
            var x = document.getElementsByTagName("script")[0]; x.parentNode.insertBefore(s, x);
        };
        ttq.load('{{ $ttPixelId }}');
        @if(!$__ttFromGtm)
        ttq.page();
        @endif
    }(window, document, 'ttq');
    </script>
    @endif

    {{-- ✅ GTM + Tracking Code --}}
    {!! optional($information)->tracking_code !!}

    {{-- ===== FB Pixel + Tracking ===== --}}
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

    {{-- Event ids + GA4 dataLayer + browser pixel events.
         NOT nested inside the FB @if — clearing the Facebook Pixel ID must never kill GTM/TikTok. --}}
    <script>
    (function(){
        if (window.__LP_FIRED__) { return; }
        window.__LP_FIRED__ = true;

        window.LP_EVENT_BASE  = {!! json_encode($lpEventBase ?? null) !!} || ('LP15_{{ $productId }}_' + Date.now());
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
            --bg: #082818;
            --bg-2: #0a3318;
            --bg-3: #0f3d1f;
            --bg-card: #11401e;
            --gold: #fdd930;
            --gold-2: #f8c93c;
            --gold-dark: #c89615;
            --argentina: #75aadb;
            --brazil: #fcd116;
            --brazil-green: #009c3b;
            --text: #e8e9d8;
            --text-muted: #9ab09a;
            --border: rgba(253, 217, 48, 0.18);
            --border-soft: rgba(255, 255, 255, 0.08);
        }

        * { box-sizing: border-box; }
        html, body { overflow-x: hidden; scroll-behavior: smooth; }
        body {
            font-family: 'Hind Siliguri', sans-serif;
            background: var(--bg);
            color: var(--text);
            margin: 0; padding: 0;
            line-height: 1.65;
        }
        a { text-decoration: none; }

        .container-x { max-width: 1180px; margin: 0 auto; padding: 0 20px; }

        /* ===== TOP STRIPE ===== */
        .top-stripe {
            background: linear-gradient(90deg, var(--gold), var(--gold-2));
            color: #1a1a00;
            padding: 9px 0;
            font-size: 12.5px;
            font-weight: 700;
        }
        .top-stripe .container-x { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
        .top-stripe .countdown { display: flex; align-items: center; gap: 6px; }
        .top-stripe .countdown span { background: #1a3318; color: var(--gold); padding: 2px 8px; border-radius: 4px; font-family: 'Bebas Neue', sans-serif; letter-spacing: 1px; }
        .top-stripe .btn-top {
            background: #1a3318; color: var(--gold); padding: 5px 14px; border-radius: 50px;
            font-weight: 700; font-size: 11px; transition: .2s; display: inline-flex; align-items: center; gap: 5px;
        }
        .top-stripe .btn-top:hover { background: #0a1a08; }

        /* ===== HEADER ===== */
        .site-header {
            background: var(--bg-2);
            padding: 14px 0;
            border-bottom: 1px solid var(--border-soft);
        }
        .header-row { display: flex; justify-content: space-between; align-items: center; gap: 10px; }
        .brand-logo { display: flex; align-items: center; gap: 10px; }
        .brand-icon {
            width: 38px; height: 38px; background: var(--gold);
            color: #1a3318; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
        }
        .brand-text { color: #fff; font-weight: 700; font-size: 20px; line-height: 1.1; }
        .brand-text small { display: block; color: var(--text-muted); font-size: 12px; font-weight: 500; letter-spacing: 1px; margin-top: 4px; }
        .btn-hero-cta {
            background: var(--gold); color: #1a3318 !important;
            padding: 11px 22px; border-radius: 50px; font-weight: 700; font-size: 16px;
            transition: .25s; display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-hero-cta:hover { background: #fff; }

        /* ===== HERO ===== */
        .hero-section {
            background: var(--bg-2);
            padding: 50px 0 80px;
            position: relative;
            overflow: hidden;
        }
        .hero-section::before {
            content: '';
            position: absolute; inset: 0;
            background-image: linear-gradient(rgba(8,40,24,0.85), rgba(10,51,24,0.95)){{ !empty($heroBg) ? ", url('".$heroBg."')" : '' }};
            background-size: cover; background-position: center;
            opacity: 1;
        }
        .hero-content { position: relative; z-index: 2; text-align: center; }
        .hero-badge {
            display: inline-flex;
            align-items: center; gap: 6px;
            background: rgba(253, 217, 48, 0.15);
            color: var(--gold);
            padding: 6px 14px; border-radius: 50px;
            font-size: 11px; font-weight: 700;
            letter-spacing: 1.5px;
            border: 1px solid var(--border);
            margin-bottom: 16px;
        }
        .hero-title-x {
            font-weight: 800;
            font-size: clamp(34px, 6vw, 56px);
            line-height: 1.1;
            margin: 0 0 8px;
            color: #fff;
        }
        .hero-title-x .team-a { color: var(--argentina); }
        .hero-title-x .team-b { color: var(--brazil); }
        .hero-title-x .vs { color: #fff; opacity: 0.5; margin: 0 8px; font-weight: 400; }
        .hero-subtitle {
            color: var(--gold);
            font-size: 28px; font-weight: 700;
            margin: 6px 0 18px;
        }
        .hero-desc {
            max-width: 680px; margin: 0 auto 28px;
            color: #ffffff !important;
            font-size: 18px;
            line-height: 1.75;
            font-weight: 400;
        }
        /* ⚠️ Summernote থেকে আসা inline color/background style override করতে !important লাগে */
        .hero-desc *,
        .hero-desc p,
        .hero-desc span,
        .hero-desc div,
        .hero-desc li,
        .hero-desc strong,
        .hero-desc b,
        .hero-desc em,
        .hero-desc a {
            color: #ffffff !important;
            background: transparent !important;
            background-color: transparent !important;
            font-family: 'Hind Siliguri', sans-serif !important;
        }
        .hero-desc strong, .hero-desc b { font-weight: 700 !important; }

        /* HERO JERSEY DISPLAY */
        .hero-jerseys {
            position: relative;
            max-width: 700px;
            margin: 30px auto 24px;
            aspect-ratio: 1.8/1;
            border-radius: 20px;
            overflow: hidden;
            background: linear-gradient(180deg, #1a4a30 0%, #0f3d1f 100%);
            border: 1px solid var(--border);
            box-shadow: 0 20px 50px -25px rgba(0,0,0,0.7);
        }
        .hero-jerseys-bg {
            position: absolute; inset: 0;
            background: radial-gradient(ellipse at center, rgba(253,217,48,0.15) 0%, transparent 60%);
        }
        .hero-jerseys-img {
            position: relative; z-index: 2;
            width: 100%; height: 100%;
            object-fit: contain;
            padding: 18px;
        }
        .hero-corner-tag {
            position: absolute; top: 14px;
            z-index: 3;
            padding: 5px 12px; border-radius: 6px;
            font-size: 10px; font-weight: 800; letter-spacing: 1.5px;
            color: #1a1a00;
        }
        .hero-corner-tag.left { left: 14px; background: var(--argentina); color: #fff; }
        .hero-corner-tag.right { right: 14px; background: var(--brazil); }

        /* HERO PRICE */
        .hero-price-box { text-align: center; margin: 20px auto 24px; }
        .hero-price-new {
            font-family: 'Bebas Neue', sans-serif;
            color: var(--gold);
            font-size: 56px; font-weight: 400;
            letter-spacing: 1px; line-height: 1;
            text-shadow: 0 4px 20px rgba(253,217,48,0.3);
        }
        .hero-price-new sub { font-size: 26px; vertical-align: 14px; margin-left: 4px; }
        .hero-price-old {
            color: var(--text-muted); font-size: 17px;
            text-decoration: line-through; margin-top: 4px;
        }
        .btn-order-cta {
            background: var(--gold); color: #1a3318 !important;
            padding: 16px 38px; border-radius: 50px;
            font-weight: 800; font-size: 18px;
            display: inline-flex; align-items: center; gap: 8px;
            transition: .25s;
            box-shadow: 0 8px 24px -6px rgba(253,217,48,0.4);
        }
        .btn-order-cta:hover { background: #fff; transform: translateY(-2px); }
        .hero-small-stats { color: var(--text-muted); font-size: 14px; margin-top: 14px; }
        .hero-small-stats i { color: var(--gold); }

        /* ===== TAB BAR ===== */
        .tab-bar {
            background: var(--bg-2);
            border-top: 1px solid var(--border-soft);
            border-bottom: 1px solid var(--border-soft);
            padding: 14px 0;
        }
        .tab-bar-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            font-size: 15px;
            color: var(--text-muted);
            font-weight: 600;
        }
        .tab-bar-grid .tb-item { display: flex; align-items: center; gap: 6px; }
        .tab-bar-grid .tb-item i { color: var(--gold); font-size: 17px; }

        /* ===== SECTIONS ===== */
        section.green-sec { background: var(--bg); padding: 70px 0; }
        section.green-sec.alt { background: var(--bg-2); }

        .sec-head { text-align: center; margin-bottom: 40px; }
        .sec-icon {
            display: inline-block; padding: 10px 22px;
            border: 1px solid var(--border); border-radius: 50px;
            color: var(--gold); font-size: 14px; font-weight: 700;
            letter-spacing: 1.5px; margin-bottom: 14px;
        }
        .sec-icon i { margin-right: 6px; }
        .sec-title { color: #fff; font-size: clamp(32px, 4.5vw, 44px); font-weight: 700; margin: 0 0 10px; }
        .sec-sub { color: var(--text-muted); font-size: 17px; max-width: 680px; margin: 0 auto; line-height: 1.7; }

        /* ===== TEAM CARDS ===== */
        .teams-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            max-width: 850px; margin: 0 auto;
        }
        .team-card {
            background: var(--bg-card);
            border: 2px solid var(--border-soft);
            border-radius: 16px;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            transition: .25s;
            position: relative;
            overflow: hidden;
        }
        .team-card:hover { border-color: var(--gold); transform: translateY(-3px); }
        .team-card.selected { border-color: var(--gold); background: rgba(253,217,48,0.05); }
        .team-card-badge {
            position: absolute; top: 12px; right: 12px;
            background: var(--gold); color: #1a3318;
            padding: 3px 8px; border-radius: 4px;
            font-size: 9px; font-weight: 800; letter-spacing: 1px;
        }
        .team-card img {
            width: 100%; max-width: 220px;
            aspect-ratio: 1/1;
            object-fit: contain;
            margin: 6px auto 14px;
            display: block;
        }
        .team-card h4 { color: #fff; font-size: 26px; font-weight: 700; margin: 0 0 4px; }
        .team-card p { color: var(--text-muted); font-size: 16px; margin: 0 0 14px; }
        .team-card .pick-btn {
            display: inline-flex; align-items: center; gap: 6px;
            background: transparent; color: var(--gold);
            border: 1px solid var(--gold);
            padding: 9px 22px; border-radius: 50px;
            font-size: 15px; font-weight: 700;
            transition: .2s;
        }
        .team-card.selected .pick-btn { background: var(--gold); color: #1a3318; }
        .team-card.selected .pick-btn::after { content: ' ✓'; }

        /* ===== GALLERY ===== */
        .gallery-grid {
            display: grid;
            grid-template-columns: 1fr 110px;
            gap: 16px;
            max-width: 850px; margin: 30px auto 0;
        }
        .gallery-main {
            background: var(--bg-card);
            border-radius: 16px;
            border: 1px solid var(--border-soft);
            aspect-ratio: 4/3;
            overflow: hidden;
            position: relative;
            display: flex; align-items: center; justify-content: center;
        }
        .gallery-main img { width: 100%; height: 100%; object-fit: contain; padding: 30px; }
        .gallery-main .star-badge {
            position: absolute; top: 12px; right: 12px;
            background: var(--gold); color: #1a3318;
            border-radius: 50%; width: 36px; height: 36px;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
        }
        .gallery-thumbs { display: flex; flex-direction: column; gap: 10px; }
        .gallery-thumb {
            background: var(--bg-card);
            border-radius: 10px;
            border: 2px solid var(--border-soft);
            aspect-ratio: 1/1;
            overflow: hidden;
            cursor: pointer; transition: .2s;
            display: flex; align-items: center; justify-content: center;
        }
        .gallery-thumb img { width: 100%; height: 100%; object-fit: contain; padding: 4px; }
        .gallery-thumb:hover, .gallery-thumb.active { border-color: var(--gold); }

        .gallery-toggle { display: flex; justify-content: center; gap: 10px; margin-top: 20px; }
        .gallery-toggle .gt-btn {
            background: var(--bg-card); color: var(--text);
            border: 1px solid var(--border-soft);
            padding: 10px 26px; border-radius: 50px;
            font-size: 15px; font-weight: 600;
            cursor: pointer; transition: .2s;
        }
        .gallery-toggle .gt-btn.active { background: var(--gold); color: #1a3318; border-color: var(--gold); }

        /* ===== FEATURES 6 ===== */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-top: 30px;
        }
        .feature-card {
            background: var(--bg-card);
            border: 1px solid var(--border-soft);
            border-radius: 14px;
            padding: 22px 18px;
            text-align: left;
            transition: .25s;
        }
        .feature-card:hover { border-color: var(--gold); transform: translateY(-2px); }
        .feature-icon {
            width: 44px; height: 44px;
            background: rgba(253,217,48,0.12);
            color: var(--gold);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 19px;
            margin-bottom: 14px;
        }
        .feature-card h5 { color: #fff; font-size: 17px; font-weight: 700; margin: 0 0 5px; }
        .feature-card p { color: var(--text-muted); font-size: 15px; margin: 0; line-height: 1.65; }

        /* ===== SPEC ===== */
        .spec-grid {
            display: grid;
            grid-template-columns: 0.9fr 1.1fr;
            gap: 30px; align-items: center;
            margin-top: 30px;
        }
        .spec-img-box {
            background: var(--bg-card);
            border: 1px solid var(--border-soft);
            border-radius: 16px;
            aspect-ratio: 1/1;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden; padding: 30px;
        }
        .spec-img-box img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .spec-table-x {
            background: var(--bg-card);
            border-radius: 14px;
            border: 1px solid var(--border-soft);
            overflow: hidden;
        }
        .spec-row {
            display: grid;
            grid-template-columns: 200px 1fr;
            padding: 16px 24px;
            border-top: 1px solid var(--border-soft);
            font-size: 16px;
        }
        .spec-row:first-child { border-top: none; }
        .spec-row .label { color: var(--text-muted); font-weight: 500; }
        .spec-row .value { color: var(--gold); font-weight: 600; }

        /* ===== REVIEWS ===== */
        .reviews-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-top: 30px;
        }
        .review-card {
            background: var(--bg-card);
            border: 1px solid var(--border-soft);
            border-radius: 14px;
            padding: 22px;
        }
        .review-stars { color: var(--gold); font-size: 16px; margin-bottom: 10px; letter-spacing: 2px; }
        .review-text { color: var(--text); font-size: 15px; line-height: 1.7; margin-bottom: 14px; }
        .review-name { color: #fff; font-weight: 700; font-size: 15px; }
        .review-loc { color: var(--text-muted); font-size: 13px; }

        /* ===== ORDER FORM ===== */
        .order-card {
            background: #fff;
            border-radius: 20px;
            padding: 30px;
            max-width: 720px;
            margin: 30px auto 0;
            color: #1f2937;
            box-shadow: 0 30px 60px -30px rgba(0,0,0,0.6);
        }
        .order-card .form-label {
            font-size: 15px; font-weight: 700; color: #374151;
            margin-bottom: 6px;
        }
        .order-card .form-control,
        .order-card .form-select {
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 16px;
            background: #fff;
            font-family: inherit;
        }
        .order-card .form-control:focus,
        .order-card .form-select:focus {
            border-color: var(--bg-3);
            box-shadow: 0 0 0 3px rgba(15,61,31,0.1);
            outline: none;
        }
        .team-pick { display: flex; gap: 8px; margin-bottom: 10px; }
        .team-pick button {
            flex: 1; padding: 12px;
            background: #f3f4f6; border: 2px solid #e5e7eb;
            border-radius: 10px; font-weight: 600; font-size: 15px; color: #1f2937;
            cursor: pointer; transition: .2s;
        }
        .team-pick button.active { background: var(--bg-3); color: var(--gold); border-color: var(--bg-3); }

        .size-pick { display: flex; gap: 8px; flex-wrap: wrap; }
        .size-pick button {
            min-width: 52px; height: 44px;
            background: #fff; border: 1.5px solid #d1d5db; border-radius: 8px;
            font-weight: 700; font-size: 15px; color: #1f2937;
            cursor: pointer; transition: .2s;
            padding: 0 14px;
        }
        .size-pick button.active { background: var(--bg-3); color: var(--gold); border-color: var(--bg-3); }

        .qty-x { display: flex; align-items: center; border: 1px solid #d1d5db; border-radius: 10px; overflow: hidden; width: fit-content; }
        .qty-x button { background: #f3f4f6; border: none; width: 38px; height: 38px; font-size: 16px; font-weight: 700; cursor: pointer; }
        .qty-x input { width: 50px; height: 38px; border: none; text-align: center; font-weight: 700; }

        .order-summary { padding: 16px 0; border-top: 1px dashed #d1d5db; margin-top: 14px; }
        .order-summary .row-l { display: flex; justify-content: space-between; padding: 8px 0; font-size: 16px; }
        .order-summary .row-l.total { font-weight: 800; font-size: 20px; color: var(--bg-3); border-top: 1px solid #e5e7eb; padding-top: 12px; margin-top: 8px; }

        .order-submit-btn {
            width: 100%; background: linear-gradient(90deg, var(--gold), var(--gold-2)); color: #1a3318;
            border: none; padding: 17px; border-radius: 50px; font-weight: 800; font-size: 18px;
            cursor: pointer; transition: .2s; margin-top: 18px;
        }
        .order-submit-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(253,217,48,0.3); }
        .order-submit-btn:disabled { background: #9ca3af; color: #fff; cursor: not-allowed; }

        /* PAYMENT */
        .pay-section { margin-top: 14px; }
        .pay-section .label { display: block; font-weight: 700; font-size: 15px; color: var(--bg-3); margin-bottom: 8px; }
        .payment-box {
            border: 1.5px solid #d1d5db; border-radius: 10px;
            padding: 12px 14px; cursor: pointer; transition: .2s; background: #fff;
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 8px; font-size: 15px;
        }
        .payment-box i { font-size: 17px; color: var(--bg-3); }
        .payment-box img { height: 22px; width: 22px; object-fit: contain; }
        input[name="payment_method"]:checked + .payment-box { border-color: var(--bg-3); background: #f0fdf4; }

        .coupon-box { margin-top: 10px; padding: 10px; background: #fffbeb; border: 1px dashed var(--gold-dark); border-radius: 10px; }
        .coupon-box .form-label { color: var(--bg-3); font-weight: 700; font-size: 12px; }
        .coupon-box .input-group { display: flex; gap: 6px; }
        .coupon-box input.form-control { flex: 1; }
        .coupon-box button { background: var(--bg-3); color: var(--gold); border: none; padding: 0 16px; border-radius: 8px; font-weight: 700; font-size: 12px; cursor: pointer; }
        .manual-pay-area { display: none; margin-top: 10px; padding: 10px; background: #f9fafb; border-radius: 10px; }
        .manual-pay-area .alert { background: #f0fdf4; border: 1px solid #bbf7d0; color: #14532d; padding: 8px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; margin-bottom: 8px; }

        /* ===== FAQ ===== */
        .faq-list { max-width: 720px; margin: 30px auto 0; }
        .faq-item {
            background: var(--bg-card);
            border: 1px solid var(--border-soft);
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: .2s;
        }
        .faq-item:hover { border-color: var(--gold); }
        .faq-q { display: flex; justify-content: space-between; align-items: center; color: #fff; font-weight: 600; font-size: 17px; }
        .faq-q i { color: var(--gold); transition: transform .25s; }
        .faq-a { display: none; padding-top: 12px; margin-top: 12px; border-top: 1px solid var(--border-soft); color: var(--text-muted); font-size: 15px; line-height: 1.75; }
        .faq-item.open .faq-a { display: block; }
        .faq-item.open .faq-q i { transform: rotate(45deg); }

        /* ===== FOOTER ===== */
        .site-footer { background: var(--bg-3); padding: 40px 0 20px; }
        .footer-grid { display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 30px; margin-bottom: 24px; }
        .footer-brand-block .brand-logo { margin-bottom: 12px; }
        .footer-col h6 { color: var(--gold); font-size: 13px; letter-spacing: 2px; text-transform: uppercase; font-weight: 700; margin-bottom: 12px; }
        .footer-col p { color: var(--text); font-size: 16px; margin: 0; line-height: 1.7; }
        .footer-bottom { border-top: 1px solid var(--border-soft); padding-top: 16px; display: flex; justify-content: center; align-items: center; font-size: 14px; color: var(--text-muted); text-align: center; }

        /* WHATSAPP */
        @keyframes wa-glow {
            0%,100% { box-shadow: 0 0 0 0 rgba(37,211,102,0.6); }
            50% { box-shadow: 0 0 0 12px rgba(37,211,102,0); }
        }
        .whats_btn {
            position: fixed; right: 16px; bottom: 80px; z-index: 9999;
            width: 54px; height: 54px; border-radius: 50%;
            background: #25D366;
            display: flex; align-items: center; justify-content: center;
            text-decoration: none;
            animation: wa-glow 2s infinite;
        }
        .whats_btn img { width: 32px; height: 32px; }

        /* OTP MODAL */
        #otpModal { z-index: 99999 !important; }
        .otp-modal-content { border: none !important; border-radius: 16px !important; background: #fff; text-align: center; overflow: hidden; position: relative; }
        .otp-modal-content::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 5px; background: var(--gold); }
        .otp-icon-box { width: 70px; height: 70px; background: #fffbeb; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 10px auto 16px; color: var(--gold-dark); font-size: 26px; }
        .otp-input { width: 100%; letter-spacing: 12px; text-align: center; font-size: 24px; font-weight: bold; color: #333; border: 2px solid #eee !important; border-radius: 10px !important; background: #fafafa; height: 58px; }
        .otp-input:focus { border-color: var(--gold) !important; background: #fff; outline: none; }
        .btn-verify { background: var(--bg-3); border: none; padding: 12px; font-size: 15px; border-radius: 10px; width: 100%; color: var(--gold); font-weight: 700; }
        .btn-verify:hover { background: var(--bg); }

        @media (max-width: 900px) {
            .features-grid { grid-template-columns: repeat(2, 1fr); }
            .reviews-grid { grid-template-columns: 1fr; }
            .spec-grid { grid-template-columns: 1fr; }
            .gallery-grid { grid-template-columns: 1fr; }
            .gallery-thumbs { flex-direction: row; overflow-x: auto; }
            .gallery-thumb { min-width: 80px; }
            .footer-grid { grid-template-columns: 1fr; }
            .spec-row { grid-template-columns: 1fr; }
            .spec-row .value { margin-top: 4px; }
        }
        @media (max-width: 540px) {
            .top-stripe { font-size: 11px; padding: 7px 0; }
            .top-stripe .container-x { flex-wrap: wrap; }
            .teams-grid { grid-template-columns: 1fr; gap: 14px; }
            .features-grid { grid-template-columns: 1fr; }
            .hero-price-new { font-size: 42px; }
            .tab-bar-grid { gap: 12px; }
            .whats_btn { right: 12px; bottom: 14px; width: 48px; height: 48px; }
            .whats_btn img { width: 28px; height: 28px; }
            .footer-bottom { flex-direction: column; gap: 6px; text-align: center; }
            .order-card { padding: 20px; }

            /* ===== Mobile Header Compact ===== */
            .site-header { padding: 10px 0; }
            .header-row { gap: 6px; flex-wrap: nowrap; }
            .brand-icon { width: 32px; height: 32px; font-size: 16px; flex-shrink: 0; }
            .brand-text { font-size: 14px; line-height: 1.15; min-width: 0; }
            .brand-text small { font-size: 10px; margin-top: 2px; letter-spacing: 0.5px; }
            .brand-logo { gap: 8px; min-width: 0; flex: 1; overflow: hidden; }

            /* Order button — compact, single-line, icon only on extra small */
            .btn-hero-cta {
                padding: 8px 14px;
                font-size: 13px;
                white-space: nowrap;
                flex-shrink: 0;
                gap: 5px;
            }
            .btn-hero-cta i { font-size: 13px; }
        }
        @media (max-width: 380px) {
            /* এক্সট্রা ছোট ফোনে শুধু icon দেখাব, text হাইড */
            .btn-hero-cta { padding: 9px 12px; font-size: 0; gap: 0; }
            .btn-hero-cta i { font-size: 16px; }
            .brand-text { font-size: 13px; }
        }
    </style>
<style>
/* 🔒 Hide all admin-blank elements — text, rows, sections — so নo static text shows when admin জা boshay নাই */
h1:empty, h2:empty, h3:empty, h4:empty, h5:empty, h6:empty,
p:empty, span:empty,
.sec-title:empty, .sec-sub:empty, .sec-icon:empty,
.hero-title-x:empty, .hero-subtitle:empty, .hero-desc:empty,
.hero-small-stats:empty,
.tb-item span:empty,
.feature-card h5:empty, .feature-card p:empty,
.spec-row .label:empty, .spec-row .value:empty,
.review-card .review-text:empty, .review-card .review-name:empty,
.team-card h4:empty, .team-card p:empty,
.faq-q span:empty, .faq-a:empty,
.brand-text-sub:empty, .brand-text:empty,
.footer-col h6:empty, .footer-col p:empty { display: none !important; }

/* Hide repeated rows / cards when their main field is blank */
.tb-item:has(span:empty),
.feature-card:has(h5:empty),
.spec-row:has(.label:empty),
.review-card:has(.review-text:empty),
.team-card:has(h4:empty),
.faq-item:has(.faq-q > span:empty) { display: none !important; }

.sec-icon:not(:has(*:not(:empty))):not(:has(i)) { display: none !important; }
</style>
</head>
<body>

@if(!empty($__gtmId))
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $__gtmId }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
@endif

{{-- ===== TOP STRIPE ===== --}}
<div class="top-stripe">
    <div class="container-x">
        <div class="countdown">
            <i class="fas fa-clock"></i>
            {{ $clean($ln_pg->countdown_title ?? null, 'বিশেষ অফার শেষ হবে') }}: <span id="countdown-timer">--:--:--</span>
        </div>
        <a href="#order_section" class="btn-top">
            <i class="fas fa-shopping-cart"></i> {{ $clean($ln_pg->btn_text_top ?? null, 'অর্ডার করুন') }}
        </a>
    </div>
</div>

{{-- ===== HEADER ===== --}}
<header class="site-header">
    <div class="container-x">
        <div class="header-row">
            <a href="#" class="brand-logo">
                <span class="brand-icon"><i class="fas fa-tshirt"></i></span>
                <div class="brand-text">
                    {{ $brandLogo }}
                    <small>{{ $brandMain }}</small>
                </div>
            </a>
            <a href="#order_section" class="btn-hero-cta">
                <i class="fas fa-shopping-bag"></i> {{ $clean($ln_pg->btn_text_video ?? null, 'অর্ডার করুন') }}
            </a>
        </div>
    </div>
</header>

{{-- ===== HERO ===== --}}
<section class="hero-section">
    <div class="container-x">
        <div class="hero-content">
            <div class="hero-badge"><i class="fas fa-trophy"></i> {{ $clean($ln_pg->feature ?? null, 'WORLD CUP EDITION - ২০২৬') }}</div>
            <h1 class="hero-title-x">
                <span class="team-a">{{ $team1 }}</span>
                <span class="vs">×</span>
                <span class="team-b">{{ $team2 }}</span>
            </h1>
            <div class="hero-subtitle">{{ $brandMain }}</div>
            <div class="hero-desc">
                {!! $clean($ln_pg->left_side_desc ?? null, 'অরিজিনাল কোয়ালিটি ফ্যাব্রিক, নিখুঁত স্টিচিং এবং স্ট্যান্ডার্ড ফিট। নিজের প্রিয় দলের জার্সি নিজের কাছে রেখে দিন।') !!}
            </div>

            <div class="hero-jerseys">
                <div class="hero-jerseys-bg"></div>
                <span class="hero-corner-tag left">{{ strtoupper($team1) }}</span>
                <span class="hero-corner-tag right">{{ strtoupper($team2) }}</span>
                @if($mainProductImg)
                    <img src="{{ $mainProductImg }}" class="hero-jerseys-img" alt="{{ $productName }}">
                @elseif($sliderImages->count() > 0)
                    <img src="{{ asset('landing_sliders/'.$sliderImages->first()->image) }}" class="hero-jerseys-img" alt="{{ $productName }}">
                @else
                    <div style="display:flex; align-items:center; justify-content:center; color:var(--text-muted); width:100%; height:100%;">
                        <i class="fas fa-tshirt" style="font-size:80px; opacity:.3;"></i>
                    </div>
                @endif
            </div>

            <div class="hero-price-box">
                <div class="hero-price-new">{{ number_format($defaultPrice, 0) }}<sub>৳</sub></div>
                <div class="hero-price-old">৳{{ number_format($oldPrice, 0) }}</div>
            </div>

            <a href="#order_section" class="btn-order-cta">
                <i class="fas fa-shopping-cart"></i> {{ $clean($ln_pg->btn_text_hero ?? null, 'এখনই অর্ডার করুন') }}
            </a>
            <div class="hero-small-stats">
                <i class="fas fa-truck"></i> {{ $clean($ln_pg->hero_small_stats ?? null, 'সারা দেশে ক্যাশ-অন ডেলিভারি') }}
            </div>
        </div>
    </div>
</section>

{{-- ===== TAB BAR (TRUST) ===== --}}
<div class="tab-bar">
    <div class="container-x">
        <div class="tab-bar-grid">
            @php
                $tbDefaults = ['১০০% অরিজিনাল','দ্রুত ডেলিভারি','ক্যাশ অন ডেলিভারি','১০০% নিরাপদ'];
                $tbIcons    = ['fas fa-check-circle','fas fa-bolt','fas fa-money-bill-wave','fas fa-shield-alt'];
            @endphp
            @foreach([1,2,3,4] as $n)
            <div class="tb-item">
                <i class="{{ $ln_pg->{'trust_'.$n.'_icon'} ?? $tbIcons[$n-1] }}"></i>
                <span>{{ $clean($ln_pg->{'trust_'.$n.'_title'} ?? null, $tbDefaults[$n-1]) }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ===== TEAM CARDS — শুধু jersey landing-এর জন্য (title-এ × আছে) ===== --}}
@if($hasTeamSeparator)
<section class="green-sec alt">
    <div class="container-x">
        <div class="sec-head">
            <div class="sec-icon"><i class="fas fa-trophy"></i> {{ $clean($ln_pg->team_badge ?? null, 'টিম সিলেকশন') }}</div>
            <h2 class="sec-title">{{ $clean($ln_pg->feature_title ?? null, 'আপনার প্রিয় দল বেছে নিন') }}</h2>
            <p class="sec-sub">{{ $clean($ln_pg->right_side_title ?? null, 'দুটি জার্সির মধ্যে আপনার পছন্দের দল সিলেক্ট করুন') }}</p>
        </div>

        <div class="teams-grid">
            <div class="team-card selected" data-team="team1">
                <span class="team-card-badge">{{ $clean($ln_pg->promise_1_badge ?? null, 'প্রিমিয়াম') }}</span>
                @if($sliderImages->count() > 0)
                    <img src="{{ asset('landing_sliders/'.$sliderImages->get(0)->image) }}" alt="{{ $team1 }}">
                @else
                    <img src="https://via.placeholder.com/300/75aadb/ffffff?text={{ urlencode($team1) }}" alt="{{ $team1 }}">
                @endif
                <h4>{{ $clean($ln_pg->promise_1_title ?? null, $team1) }}</h4>
                <p>{{ $clean($ln_pg->promise_1_desc ?? null, 'প্রিমিয়াম এডিশন') }}</p>
                <button type="button" class="pick-btn">{{ $clean($ln_pg->team_pick_btn ?? null, 'বেছে নিন') }}</button>
            </div>
            <div class="team-card" data-team="team2">
                <span class="team-card-badge">{{ $clean($ln_pg->promise_2_badge ?? null, 'প্রিমিয়াম') }}</span>
                @if($sliderImages->count() > 1)
                    <img src="{{ asset('landing_sliders/'.$sliderImages->get(1)->image) }}" alt="{{ $team2 }}">
                @else
                    <img src="https://via.placeholder.com/300/fcd116/000000?text={{ urlencode($team2) }}" alt="{{ $team2 }}">
                @endif
                <h4>{{ $clean($ln_pg->promise_2_title ?? null, $team2) }}</h4>
                <p>{{ $clean($ln_pg->promise_2_desc ?? null, 'বিশ্বচ্যাম্পিয়ন') }}</p>
                <button type="button" class="pick-btn">{{ $clean($ln_pg->team_pick_btn ?? null, 'বেছে নিন') }}</button>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ===== GALLERY ===== --}}
<section class="green-sec">
    <div class="container-x">
        <div class="sec-head">
            <div class="sec-icon"><i class="fas fa-image"></i> {{ $clean($ln_pg->gallery_badge ?? null, 'গ্যালারি') }}</div>
            <h2 class="sec-title">{{ $clean($ln_pg->identify_title ?? null, 'প্রোডাক্ট গ্যালারি') }}</h2>
            <p class="sec-sub">{{ $clean($ln_pg->identify_subtitle ?? null, '৩৬০° দেখুন প্রোডাক্টের প্রতিটি ডিটেইল') }}</p>
        </div>

        <div class="gallery-grid">
            <div class="gallery-main">
                <div class="star-badge"><i class="fas fa-star"></i></div>
                <img id="gallery-main-img" src="{{ $mainProductImg ?: ($sliderImages->count() > 0 ? asset('landing_sliders/'.$sliderImages->first()->image) : 'https://via.placeholder.com/600/0f3d1f/fdd930?text=Jersey') }}" alt="Jersey">
            </div>
            <div class="gallery-thumbs">
                @forelse($sliderImages as $idx => $img)
                    @if($idx < 4)
                    <div class="gallery-thumb {{ $idx == 0 ? 'active' : '' }}" onclick="document.getElementById('gallery-main-img').src='{{ asset('landing_sliders/'.$img->image) }}'; document.querySelectorAll('.gallery-thumb').forEach(t=>t.classList.remove('active')); this.classList.add('active');">
                        <img src="{{ asset('landing_sliders/'.$img->image) }}" alt="thumb">
                    </div>
                    @endif
                @empty
                    @for($i = 0; $i < 4; $i++)
                    <div class="gallery-thumb"><img src="https://via.placeholder.com/100/0f3d1f/fdd930?text={{ $i+1 }}" alt="thumb"></div>
                    @endfor
                @endforelse
            </div>
        </div>

        <div class="gallery-toggle">
            <button type="button" class="gt-btn active" onclick="this.classList.add('active'); this.nextElementSibling.classList.remove('active');">{{ $team1 }} জার্সি</button>
            <button type="button" class="gt-btn" onclick="this.classList.add('active'); this.previousElementSibling.classList.remove('active');">{{ $team2 }} জার্সি</button>
        </div>
    </div>
</section>

{{-- ===== 6 FEATURES ===== --}}
<section class="green-sec alt">
    <div class="container-x">
        <div class="sec-head">
            <div class="sec-icon"><i class="fas fa-star"></i> {{ $clean($ln_pg->promise_badge ?? null, 'কেন আমাদের') }}</div>
            <h2 class="sec-title">{{ $clean($ln_pg->left_side_title ?? null, 'কেন এই জার্সিটি বিশেষ?') }}</h2>
        </div>

        <div class="features-grid">
            @php
                $fd = [
                    ['fas fa-tshirt','প্রিমিয়াম পলিয়েস্টার','উন্নত মানের ফ্যাব্রিক, দীর্ঘস্থায়ী।'],
                    ['fas fa-wind','ব্রিদেবল জোন','আরামদায়ক হাঁটাচলায় ঘাম শোষক।'],
                    ['fas fa-check-circle','অফিসিয়াল ফিট','রিয়েল ম্যাচ জার্সির ফিটিং।'],
                    ['fas fa-palette','ইউনিক ডিজাইন','১:১ অরিজিনাল কপি প্রিন্ট।'],
                    ['fas fa-ruler','পারফেক্ট ফিটিং','S থেকে XXL সব সাইজে পাবেন।'],
                    ['fas fa-feather','হালকা ও কম্ফোর্টেবল','ঘণ্টার পর ঘণ্টা পরে থাকা যায়।'],
                ];
            @endphp
            @foreach([1,2,3,4,5,6] as $n)
            <div class="feature-card">
                <div class="feature-icon"><i class="{{ $ln_pg->{'id_'.$n.'_icon'} ?? '' }}"></i></div>
                <h5>{{ $ln_pg->{'id_'.$n.'_title'} ?? '' }}</h5>
                <p>{{ $ln_pg->{'id_'.$n.'_desc'} ?? '' }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== SPEC ===== --}}
<section class="green-sec">
    <div class="container-x">
        <div class="sec-head">
            <div class="sec-icon"><i class="fas fa-list"></i> {{ $clean($ln_pg->spec_badge ?? null, 'স্পেক') }}</div>
            <h2 class="sec-title">{{ $clean($ln_pg->spec_title ?? null, 'প্রোডাক্ট স্পেসিফিকেশন') }}</h2>
        </div>

        <div class="spec-grid">
            <div class="spec-img-box">
                @if($mainProductImg)
                    <img src="{{ $mainProductImg }}" alt="Jersey">
                @else
                    <i class="fas fa-tshirt" style="font-size:120px; color:var(--gold); opacity:.4;"></i>
                @endif
            </div>
            <div class="spec-table-x">
                @php
                    $sd = [
                        ['ম্যাটেরিয়াল','প্রিমিয়াম পলিয়েস্টার'],
                        ['ফ্যাব্রিক','১০০% পলিয়েস্টার ব্লেন্ড'],
                        ['সাইজ','S / M / L / XL / XXL'],
                        ['ফিট','রেগুলার ফিটিং'],
                        ['কালার','টিম-ভিত্তিক কাস্টোমাইজড'],
                        ['ওজন','লেডিস ও স্ট্যান্ডার্ড'],
                        ['প্রিন্টিং','৭ দিনের গ্যারান্টি'],
                    ];
                @endphp
                @foreach([1,2,3,4,5,6,7] as $n)
                <div class="spec-row">
                    <div class="label">{{ $ln_pg->{'spec_'.$n.'_label'} ?? '' }}</div>
                    <div class="value">{{ $ln_pg->{'spec_'.$n.'_value'} ?? '' }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ===== REVIEWS ===== --}}
<section class="green-sec alt">
    <div class="container-x">
        <div class="sec-head">
            <div class="sec-icon"><i class="fas fa-comment-dots"></i> {{ $clean($ln_pg->review_badge ?? null, 'রিভিউ') }}</div>
            <h2 class="sec-title">{{ $clean($ln_pg->review_title ?? null, 'কাস্টমারদের মতামত') }}</h2>
        </div>

        <div class="reviews-grid">
            @php
                $rd = [
                    ['জার্সিটি কোয়ালিটি অসাধারণ। সেলাই ও প্রিন্ট খুবই ভালো।','রাশেদ হাসান','Dhaka'],
                    ['ফ্যাব্রিক সফট এবং ঘাম শোষক। দাম অনুযায়ী সেরা।','সাইফুল আনোয়ার','Chittagong'],
                    ['আমার দেখা সবচেয়ে ভালো জার্সি। সাইজ পারফেক্ট।','সাকিব ইসলাম','Sylhet'],
                ];
            @endphp
            @foreach([1,2,3] as $n)
            <div class="review-card">
                <div class="review-stars">★★★★★</div>
                <div class="review-text">"{{ $ln_pg->{'rev_'.$n.'_text'} ?? '' }}"</div>
                <div class="review-name">{{ $ln_pg->{'rev_'.$n.'_name'} ?? '' }}</div>
                <div class="review-loc">{{ $ln_pg->{'rev_'.$n.'_loc'} ?? '' }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== ORDER FORM ===== --}}
<section id="order_section" class="green-sec">
    <div class="container-x">
        <div class="sec-head">
            <div class="sec-icon"><i class="fas fa-shopping-bag"></i> {{ $clean($ln_pg->form_badge ?? null, 'অর্ডার') }}</div>
            <h2 class="sec-title">{{ $clean($ln_pg->form_title ?? null, 'অর্ডার করুন এখনই') }}</h2>
            <p class="sec-sub">{{ $clean($ln_pg->form_subtitle ?? null, 'ফর্মটি পূরণ করুন এবং ক্যাশ অন ডেলিভারিতে প্রোডাক্ট গ্রহণ করুন') }}</p>
        </div>

        <div class="order-card">
            <form action="{{ route('front.storelandData') }}" method="POST" id="checkout_form">
                @csrf
                <input type="hidden" name="prd_id" value="{{ $productId }}">
                <input type="hidden" name="landing_page_type" value="15">
                <input type="hidden" name="purchase_event_id" id="purchase_event_id" value="">
                <input type="hidden" id="unit_price" value="{{ $defaultPrice }}">
                <input type="hidden" id="max_stock" value="{{ $defaultStock }}">

                {{-- TEAM SELECTOR — শুধু যখন admin title-এ দুই team (× বা x দিয়ে separated) দিয়েছে তখনই দেখাব --}}
                @if($hasTeamSeparator && $colors->count() == 0 && $sizes->count() == 0)
                <div class="mb-3">
                    <label class="form-label">টিম বেছে নিন</label>
                    <div class="team-pick" id="team-pick">
                        <button type="button" class="active" data-team="team1">{{ $team1 }}</button>
                        <button type="button" data-team="team2">{{ $team2 }}</button>
                    </div>
                </div>
                @endif

                <input type="hidden" name="variation_id" id="variation_id" value="">

                {{-- SIZE SELECTOR --}}
                @if($sizes->count() > 0)
                <div class="mb-3">
                    <label class="form-label">সাইজ বেছে নিন *</label>
                    <div class="size-pick">
                        @foreach($sizes as $size)
                            <button type="button" class="var-size-btn" data-id="{{ $size->id }}">{{ $size->name ?? $size->title }}</button>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($colors->count() > 0)
                <div class="mb-3">
                    <label class="form-label">কালার বেছে নিন *</label>
                    <div class="size-pick">
                        @foreach($colors as $color)
                            <button type="button" class="var-color-btn" data-id="{{ $color->id }}">{{ $color->name }}</button>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="mb-3">
                    <label class="form-label">পরিমাণ</label>
                    <div class="qty-x">
                        <button type="button" id="qty_minus">-</button>
                        <input type="number" name="quantity" id="qty_input" value="1" readonly>
                        <button type="button" id="qty_plus">+</button>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">আপনার নাম *</label>
                        <input type="text" name="first_name" class="form-control" placeholder="পুরো নাম" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">মোবাইল নম্বর *</label>
                        <input type="tel" name="mobile" id="customer_mobile" class="form-control" placeholder="01XXXXXXXXX" minlength="11" maxlength="11" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">সম্পূর্ণ ঠিকানা *</label>
                        <textarea name="shipping_address" class="form-control" rows="2" placeholder="বাসা, রাস্তা, থানা, জেলা" required></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">ডেলিভারি এলাকা *</label>
                        <select name="delivery_charge_id" id="delivery_charge" class="form-select" required>
                            <option value="" disabled selected>এলাকা নির্বাচন করুন</option>
                            @foreach($charges ?? [] as $charge)
                                <option value="{{ $charge->id }}" data-charge="{{ $isFreeShipping ? 0 : intval($charge->amount) }}">
                                    {{ $charge->title }} - {!! $isFreeShipping ? '<span style="color:#15803d;font-weight:700;">ফ্রি</span>' : intval($charge->amount).' ৳' !!}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- PAYMENT METHODS --}}
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
                        <div class="payment-box"><i class="fas fa-credit-card"></i><span class="fw-bold">অনলাইন পেমেন্ট (bKash/Card)</span></div>
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

                {{-- SUMMARY --}}
                <div class="order-summary">
                    <div class="row-l"><span>সাবটোটাল (১ × <span id="unit_price_display">{{ $defaultPrice }}</span>)</span><span><span id="calc_subtotal">{{ $defaultPrice }}</span> ৳</span></div>
                    <div class="row-l"><span>ডেলিভারি</span><span id="calc_shipping_text">+ <span id="calc_shipping">0</span> ৳</span></div>
                    <div class="row-l" id="discount_row" style="{{ $sessionDiscount > 0 ? '' : 'display:none;' }}">
                        <span style="color:#15803d;">ডিসকাউন্ট</span>
                        <span style="color:#15803d;">- <span id="calc_discount">{{ $sessionDiscount }}</span> ৳</span>
                    </div>
                    <div class="row-l total"><span>মোট পেমেন্ট</span><span>৳<span id="calc_total">{{ $defaultPrice }}</span></span></div>
                </div>
                <input type="hidden" id="final_amount" name="final_amount" value="{{ $defaultPrice }}">
                <input type="hidden" name="amount" id="amount" value="{{ $defaultPrice }}">

                <button type="submit" id="submit_btn" class="order-submit-btn">
                    <i class="fas fa-shield-alt me-2"></i> {{ $ln_pg->btn_text_form ?? '' }}
                </button>

                <p class="text-center mt-3 mb-0" style="font-size:11px; color:#6b7280;">
                    <i class="fas fa-lock me-1"></i> ১০০% সিকিউর — আপনার তথ্য সম্পূর্ণ নিরাপদ
                </p>
            </form>
        </div>
    </div>
</section>

{{-- ===== FAQ ===== --}}
<section class="green-sec alt">
    <div class="container-x">
        <div class="sec-head">
            <div class="sec-icon"><i class="fas fa-question-circle"></i> {{ $clean($ln_pg->faq_badge ?? null, 'হেল্প') }}</div>
            <h2 class="sec-title">{{ $clean($ln_pg->faq_title ?? null, 'সচরাচর জিজ্ঞাসা') }}</h2>
        </div>

        @php
            $faqd = [
                ['জার্সি কি অরিজিনাল?','১০০% অরিজিনাল ফেব্রিকের তৈরি। আমরা ১:১ কপি দিয়ে থাকি যা মার্কেটে সেরা মানের।'],
                ['ডেলিভারিতে কত দিন লাগে?','ঢাকার ভিতরে ১-২ দিন এবং ঢাকার বাইরে ২-৩ দিন সময় লাগে।'],
                ['সাইজ চেঞ্জ করা যাবে?','অবশ্যই, ৭ দিনের মধ্যে সাইজ চেঞ্জ করা যাবে। জার্সিটি unused অবস্থায় থাকতে হবে।'],
                ['পেমেন্ট কিভাবে করব?','ক্যাশ অন ডেলিভারিতে পেমেন্ট করতে পারবেন। অথবা বিকাশ/নগদে পেমেন্ট করেও অর্ডার দিতে পারবেন।'],
                ['কোন কোন সাইজে পাওয়া যাবে?','S, M, L, XL, XXL — পাঁচটি সাইজে অ্যাভেইলেবল। অর্ডার ফর্মে সাইজ সিলেক্ট করতে পারবেন।'],
            ];
        @endphp
        <div class="faq-list">
            @foreach([1,2,3,4,5] as $n)
            <div class="faq-item">
                <div class="faq-q">
                    <span>{{ $clean($ln_pg->{'faq_'.$n.'_q'} ?? null, $faqd[$n-1][0]) }}</span>
                    <i class="fas fa-plus"></i>
                </div>
                <div class="faq-a">{{ $clean($ln_pg->{'faq_'.$n.'_a'} ?? null, $faqd[$n-1][1]) }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== FOOTER ===== --}}
<footer class="site-footer">
    <div class="container-x">
        <div class="footer-grid">
            <div class="footer-brand-block">
                <a href="#" class="brand-logo">
                    <span class="brand-icon"><i class="fas fa-tshirt"></i></span>
                    <div class="brand-text">
                        {{ $ln_pg->footer_company ?? $brandLogo }}
                        <small>{{ $brandMain }}</small>
                    </div>
                </a>
                <p style="color: var(--text-muted); font-size: 12.5px; margin-top: 12px;">{{ $clean($ln_pg->footer_desc ?? null, 'প্রিমিয়াম কোয়ালিটি ফুটবল জার্সি — সারা দেশে দ্রুত ডেলিভারি।') }}</p>
            </div>
            <div class="footer-col">
                <h6>{{ $clean($ln_pg->footer_contact_label ?? null, 'যোগাযোগ') }}</h6>
                <p>
                    <i class="fas fa-phone me-2"></i> {{ $ln_pg->phone ?? ($information->phone ?? '') }}<br>
                    <i class="fas fa-envelope me-2"></i> {{ $clean($ln_pg->footer_email ?? null, ($information->email ?? 'hello@jersey.com')) }}
                </p>
            </div>
            <div class="footer-col">
                <h6>{{ $clean($ln_pg->footer_address_label ?? null, 'ঠিকানা') }}</h6>
                <p><i class="fas fa-map-marker-alt me-2"></i> {{ $clean($ln_pg->dhamaka_title ?? null, ($information->address ?? 'ঢাকা, বাংলাদেশ')) }}</p>
            </div>
        </div>
        <div class="footer-bottom">
            <div>{{ $clean($ln_pg->footer_copyright ?? null, '© '.date('Y').' '.($ln_pg->footer_company ?? $brandLogo).' — সকল অধিকার সংরক্ষিত।') }}</div>
        </div>
    </div>
</footer>

{{-- OTP MODAL --}}
<div class="modal fade" id="otpModal" data-bs-backdrop="static" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content otp-modal-content p-4">
      <div class="modal-header border-0 pb-0 justify-content-end"><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body text-center pt-0 pb-3">
        <div class="otp-icon-box"><i class="fas fa-shield-alt"></i></div>
        <h4 class="fw-bold mb-2" style="color:var(--bg-3);">মোবাইল ভেরিফিকেশন</h4>
        <p style="font-size:13px; color:#6b7280;">আপনার <span class="fw-bold" id="otp_sent_number"></span> নাম্বারে কোড পাঠানো হয়েছে।</p>
        <div class="form-group mb-3">
            <input type="text" id="otp_input" maxlength="4" class="form-control otp-input" placeholder="____" autocomplete="one-time-code" inputmode="numeric">
            <small class="text-danger mt-2 d-block fw-bold" id="otp_error"></small>
        </div>
        <button type="button" class="btn-verify" onclick="verifyOtpNow()">যাচাই করুন</button>
        <div class="text-center mt-3">
             <button type="button" class="btn btn-link text-decoration-none text-muted p-0 small" id="resendOtpBtn" onclick="sendOtpBeforeSubmit(true)">
                 কোড পাননি? <span style="color:var(--bg-3); font-weight:700;">আবার পাঠান</span>
             </button>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- WhatsApp Floating --}}
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

var current_discount_val = 0;
var current_discount_type = "fixed";
var isWeightBased = {{ $isWeightBased ? 'true' : 'false' }};
var isFreeShipping = {{ $isFreeShipping }};
var isOtpVerified = false;
var otpSystemEnabled = {{ $information->otp_system ?? 0 }};
var otpTimerInterval;
var isSendingOtp = false;
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

// ===== COUNTDOWN =====
(function(){
    var hours = {{ (int)($ln_pg->countdown_hours ?? 3) }};
    var endTime = new Date().getTime() + (hours * 3600000);
    var saved = sessionStorage.getItem('lp15_countdown_end');
    if (saved && parseInt(saved) > new Date().getTime()) endTime = parseInt(saved);
    else sessionStorage.setItem('lp15_countdown_end', endTime);

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

// ===== FAQ =====
document.querySelectorAll('.faq-item').forEach(function(item){
    item.addEventListener('click', function(){ this.classList.toggle('open'); });
});

// ===== SMOOTH SCROLL =====
document.querySelectorAll('a[href^="#"]').forEach(function(a){
    a.addEventListener('click', function(e){
        var tgt = document.querySelector(this.getAttribute('href'));
        if (tgt) { e.preventDefault(); window.scrollTo({ top: tgt.offsetTop - 20, behavior: 'smooth' }); }
    });
});

// ===== TEAM CARD SELECT =====
document.querySelectorAll('.team-card').forEach(function(card){
    card.addEventListener('click', function(){
        document.querySelectorAll('.team-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
    });
});

// ===== STATIC SIZE PICK (when no variations) =====
document.querySelectorAll('.size-static-btn').forEach(function(b){
    b.addEventListener('click', function(){
        document.querySelectorAll('.size-static-btn').forEach(x => x.classList.remove('active'));
        this.classList.add('active');
    });
});

// ===== PAYMENT TOGGLE =====
window.togglePaymentAction = function(method, name = '', number = '', type = '') {
    var manualArea = $('#manual_payment_area');
    var sNum = $('#sender_number'), tId = $('#transaction_id');
    if(method === 'manual') {
        $('#payment_instruction').html(`আপনার টোটাল বিল <b>${number} (${type})</b> নাম্বারে Send Money করে নিচের তথ্য দিন।`);
        manualArea.slideDown();
        sNum.attr('required', 'required'); tId.attr('required', 'required');
    } else {
        manualArea.slideUp();
        sNum.removeAttr('required'); tId.removeAttr('required');
    }
};

// ===== COUPON =====
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

// ===== OTP =====
function startOtpTimer(duration, display) {
    var timer = duration; clearInterval(otpTimerInterval);
    $('#resendOtpBtn').prop('disabled', true);
    otpTimerInterval = setInterval(function () {
        var seconds = parseInt(timer % 60, 10); seconds = seconds<10?"0"+seconds:seconds;
        display.html("Wait (" + seconds + "s)");
        if (--timer < 0) {
            clearInterval(otpTimerInterval);
            display.html("কোড পাননি? <span style='color:var(--bg-3); font-weight:700;'>আবার পাঠান</span>");
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

// ===== FINAL SUBMIT =====
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

// ===== ORDER FORM CORE =====
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
            discount = (current_discount_type === 'percentage' || current_discount_type === 'percent')
                ? (subtotal * current_discount_val) / 100 : current_discount_val;
        }
        if(discount > 0) { $('#discount_row').show(); $('#calc_discount').text(Math.round(discount)); }
        else { $('#discount_row').hide(); }

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

    // Team pick syncs to first color variation if available
    $('#team-pick button').click(function(){
        $('#team-pick button').removeClass('active');
        $(this).addClass('active');
        let teamIdx = $(this).data('team') === 'team1' ? 0 : 1;
        let colorBtns = $('.var-color-btn');
        if(colorBtns.length > teamIdx) colorBtns.eq(teamIdx).trigger('click');
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
