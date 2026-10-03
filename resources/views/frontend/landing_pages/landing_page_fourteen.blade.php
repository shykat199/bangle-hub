<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $ln_pg->title1 ?: ($product->name ?? '') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Tiro+Bangla:ital@0;1&display=swap" rel="stylesheet">
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
        $defaultPrice = (float) ($ln_pg->new_price ?: ($currentProduct->after_discount ?: ($currentProduct->sell_price ?: 650)));
        $oldPrice     = (float) ($ln_pg->old_price ?: 850);
        $defaultStock = $currentProduct->stock_quantity ?? 99;
        $productId    = $currentProduct->id ?? 0;
        $productName  = $currentProduct->name ?? ($ln_pg->title1);
        $contentCategory = $currentProduct->category->name ?? 'Landing Page';
        $heroImage    = !empty($ln_pg->landing_bg) ? asset('landing_pages/'.$ln_pg->landing_bg) : ($currentProduct && $currentProduct->image ? (function_exists('getImage') ? getImage('products', $currentProduct->image) : asset('products/'.$currentProduct->image)) : 'https://images.unsplash.com/photo-1610700213493-d9aa1f54aa39?w=600');
        $brandRaw     = $ln_pg->title2 ?? '';
        if(preg_match('/^[\?\s\.,\-_!]+$/u', trim((string)$brandRaw))) {
            $brandRaw = '';
        }
        $brandParts   = explode('/', $brandRaw);
        $brandMain    = trim($brandParts[0] ?? '');
        $brandSub     = isset($brandParts[1]) && trim($brandParts[1]) !== '' ? trim($brandParts[1]) : ($ln_pg->brand_sub ?? '');

        // Helper: admin জা boshay নাই, কোনো fallback text দেখাবে না।
        // (২য় parameter accept করি backwards-compatibility-র জন্য, কিন্তু ignore করি।)
        $clean = function($val, $fallback = null) {
            if($val === null) return '';
            $trim = trim($val);
            if($trim === '') return '';
            // যদি স্ট্রিং এ শুধু ?, space, comma, dot, dash থাকে -> corrupt → empty
            if(preg_match('/^[\?\s\.,\-_!]+$/u', $trim)) return '';
            return $val;
        };
    @endphp

    <style>
        :root {
            --cream: #f7f1de;
            --cream-2: #f5ecd4;
            --green-darkest: #122a17;
            --green-dark: #1f3a1f;
            --green: #2d5016;
            --green-mid: #3d6b22;
            --green-light: #e8efd9;
            --gold: #c89b2e;
            --gold-soft: #f5e9b8;
            --text: #1a2e15;
            --text-muted: #65725a;
            --border: rgba(45, 80, 22, 0.15);
        }

        * { box-sizing: border-box; }
        html, body { overflow-x: hidden; scroll-behavior: smooth; }
        body {
            font-family: 'Hind Siliguri', sans-serif;
            color: var(--text);
            margin: 0;
            padding: 0;
            background: var(--cream);
            line-height: 1.6;
        }
        a { text-decoration: none; }

        /* ===== TOP STRIPE ===== */
        .top-stripe {
            background: var(--cream-2);
            border-bottom: 1px solid var(--border);
            padding: 10px 0;
            font-size: 14px;
            color: var(--text);
            position: relative;
        }
        .top-stripe .container-x { display: flex; justify-content: center; align-items: center; position: relative; }
        .top-stripe .countdown-text { font-weight: 500; text-align: center; }
        .top-stripe .countdown-text span { color: var(--green-dark); font-weight: 700; font-size: 15px; }
        .top-stripe .phone-text {
            font-weight: 600;
            color: var(--green-dark);
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
        }
        @media (max-width: 540px) {
            .top-stripe .container-x { flex-direction: column; gap: 4px; }
            .top-stripe .phone-text { position: static; transform: none; }
        }

        /* ===== HEADER LOGO ===== */
        .site-header {
            padding: 18px 0;
            background: var(--cream);
        }
        .header-row { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
        .brand-logo { display: flex; align-items: center; gap: 10px; }
        .brand-icon {
            width: 36px; height: 36px;
            background: var(--green-dark);
            color: #fff;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
        }
        .brand-text-main { font-weight: 700; color: var(--green-dark); font-size: 19px; line-height: 1.1; }
        .brand-text-sub { font-size: 11px; letter-spacing: 1.5px; color: var(--text-muted); font-weight: 600; }
        .header-phone {
            background: transparent;
            color: var(--green-dark);
            font-weight: 700;
            font-size: 17px;
        }

        .container-x { max-width: 1180px; margin: 0 auto; padding: 0 20px; }

        /* ===== HERO ===== */
        .hero-section {
            padding: 30px 0 60px;
            position: relative;
            background: var(--cream);
            background-image: radial-gradient(circle at 95% 92%, rgba(45,80,22,0.06) 0%, transparent 38%);
        }
        .hero-grid {
            display: grid;
            grid-template-columns: 1.05fr 1fr;
            gap: 50px;
            align-items: center;
        }
        .hero-title {
            font-family: 'Tiro Bangla', 'Hind Siliguri', sans-serif;
            font-weight: 400;
            font-size: clamp(40px, 6vw, 68px);
            color: var(--green-darkest);
            line-height: 1.12;
            letter-spacing: -0.5px;
            margin: 14px 0 22px;
        }
        .hero-desc {
            font-size: 18px;
            color: var(--text);
            line-height: 1.75;
            margin-bottom: 28px;
            max-width: 95%;
        }
        .hero-desc strong, .hero-desc b { color: var(--green-darkest); font-weight: 600; }

        .hero-btn-row { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; margin-bottom: 36px; }
        .btn-primary-x {
            background: var(--green-darkest);
            color: #fff !important;
            padding: 15px 28px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 17px;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all .25s;
        }
        .btn-primary-x:hover { background: var(--green); transform: translateY(-1px); }
        .btn-outline-x {
            background: transparent;
            color: var(--green-darkest) !important;
            padding: 15px 26px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 17px;
            border: 1px solid var(--border);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all .25s;
        }
        .btn-outline-x:hover { background: var(--green-darkest); color: #fff !important; border-color: var(--green-darkest); }

        .hero-stats { display: flex; gap: 28px; flex-wrap: wrap; padding-top: 4px; }
        .hero-stat-item .num {
            font-size: 32px;
            font-weight: 700;
            color: var(--green-darkest);
            line-height: 1;
        }
        .hero-stat-item .lbl {
            font-size: 14px;
            color: var(--text-muted);
            text-transform: lowercase;
            letter-spacing: 0.3px;
            margin-top: 6px;
            font-weight: 500;
        }

        /* HERO IMAGE / PRODUCT CARD */
        .hero-img-wrap { position: relative; }
        .hero-img-card {
            background: #fff;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 18px 50px -25px rgba(45,80,22,0.35);
            position: relative;
            aspect-ratio: 1.05/1;
        }
        .hero-img-card img {
            width: 100%; height: 100%; object-fit: cover; display: block;
        }
        .hero-premium-tag {
            position: absolute;
            top: 16px;
            right: 16px;
            background: var(--green-darkest);
            color: #fff;
            padding: 6px 13px;
            border-radius: 50px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            z-index: 2;
        }
        .hero-price-tag {
            position: absolute;
            left: 16px;
            bottom: 16px;
            background: #fff;
            padding: 10px 18px;
            border-radius: 12px;
            box-shadow: 0 6px 20px -8px rgba(0,0,0,0.2);
            z-index: 2;
            border-left: 4px solid var(--gold);
        }
        .hero-price-new { font-size: 28px; font-weight: 700; color: var(--gold); line-height: 1; }
        .hero-price-old { font-size: 14px; color: var(--text-muted); text-decoration: line-through; margin-top: 4px; }

        /* ===== TAB NAV ===== */
        .tabs-card {
            max-width: 720px;
            margin: -28px auto 0;
            background: #fff;
            border-radius: 50px;
            padding: 6px;
            box-shadow: 0 6px 30px -10px rgba(45,80,22,0.18);
            display: flex;
            gap: 4px;
            position: relative;
            z-index: 5;
            border: 1px solid var(--border);
        }
        .tab-link {
            flex: 1;
            text-align: center;
            padding: 12px 16px;
            border-radius: 50px;
            font-size: 15px;
            font-weight: 600;
            color: var(--text-muted);
            transition: all .2s;
            cursor: pointer;
        }
        .tab-link.active, .tab-link:hover { background: var(--green-darkest); color: #fff; }

        /* ===== SECTION TITLES ===== */
        .sec-label {
            font-size: 13px;
            letter-spacing: 2.5px;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
        }
        .sec-label::before, .sec-label::after {
            content: '';
            display: inline-block;
            width: 22px; height: 1px;
            background: currentColor;
        }
        .sec-label.left::before { display: none; }
        .sec-label.left { gap: 8px; }
        .sec-label.left::after { width: 22px; }

        .sec-title {
            font-family: 'Tiro Bangla', 'Hind Siliguri', sans-serif;
            font-weight: 400;
            font-size: clamp(34px, 4.6vw, 52px);
            color: var(--green-darkest);
            line-height: 1.2;
            letter-spacing: -0.3px;
        }
        .sec-title em { font-style: italic; color: var(--green); }

        /* ===== BENEFITS SECTION ===== */
        .benefits-section { padding: 60px 0; }
        .benefits-header {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            gap: 40px;
            align-items: end;
            margin-bottom: 35px;
        }
        .benefits-desc {
            font-size: 16px;
            color: var(--text-muted);
            line-height: 1.8;
        }
        .benefit-row {
            border-top: 1px solid var(--border);
            padding: 22px 6px;
            display: grid;
            grid-template-columns: 60px 1fr 30px;
            gap: 20px;
            align-items: center;
            transition: background .25s;
        }
        .benefit-row:hover { background: rgba(45,80,22,0.03); }
        .benefit-row:last-child { border-bottom: 1px solid var(--border); }
        .benefit-num {
            font-family: 'Tiro Bangla', serif;
            font-size: 36px;
            color: var(--green);
            font-weight: 600;
        }
        .benefit-title {
            font-size: 19px;
            font-weight: 700;
            color: var(--green-darkest);
            margin-bottom: 4px;
        }
        .benefit-desc {
            font-size: 15px;
            color: var(--text-muted);
        }
        .benefit-arrow { color: var(--green); font-size: 16px; text-align: right; opacity: 0.5; }

        /* ===== STEPS DARK SECTION ===== */
        .steps-section {
            background: var(--green-darkest);
            color: #fff;
            padding: 60px 0;
            margin: 40px 0 0;
        }
        .steps-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }
        .steps-section .sec-label { color: rgba(255,255,255,0.55); }
        .steps-section .sec-title { color: #fff; }
        .steps-section .sec-sub { color: rgba(255,255,255,0.65); font-size: 16px; margin-top: 18px; max-width: 90%; line-height: 1.7; }

        .step-item {
            display: grid;
            grid-template-columns: 60px 1fr;
            gap: 16px;
            padding: 18px 0;
            border-top: 1px solid rgba(255,255,255,0.1);
            align-items: start;
        }
        .step-item:first-child { border-top: none; }
        .step-num {
            font-family: 'Tiro Bangla', serif;
            font-size: 15px;
            color: rgba(255,255,255,0.55);
            font-weight: 500;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .step-num-bn { font-size: 26px; color: #fff; font-family: 'Tiro Bangla', serif; line-height: 1; margin-bottom: 4px; }
        .step-title {
            font-size: 20px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 6px;
        }
        .step-desc { font-size: 15px; color: rgba(255,255,255,0.65); line-height: 1.7; }

        /* ===== SPEC TABLE ===== */
        .spec-section { padding: 70px 0; background: var(--cream); }
        .spec-table {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid var(--border);
            margin-top: 30px;
        }
        .spec-row {
            display: grid;
            grid-template-columns: 220px 1fr;
            border-top: 1px solid var(--border);
            padding: 18px 26px;
            font-size: 17px;
        }
        .spec-row:first-child { border-top: none; }
        .spec-label { color: var(--text-muted); font-weight: 500; }
        .spec-value { color: var(--green-darkest); font-weight: 600; }

        /* ===== ORDER FORM ===== */
        .order-section { padding: 60px 0; background: var(--cream); }
        .order-card {
            background: #fff;
            border-radius: 20px;
            padding: 36px;
            max-width: 720px;
            margin: 30px auto 0;
            border: 1px solid var(--border);
            box-shadow: 0 8px 30px -15px rgba(45,80,22,0.2);
        }
        .order-card .form-label {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 6px;
        }
        .order-card .form-control,
        .order-card .form-select {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 13px 16px;
            font-size: 16px;
            background: #fff;
            font-family: inherit;
        }
        .order-card .form-control:focus,
        .order-card .form-select:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(45,80,22,0.08);
            outline: none;
        }
        .qty-row { display: flex; align-items: center; gap: 16px; margin: 16px 0; padding: 12px 0; border-bottom: 1px solid var(--border); }
        .qty-controller { display: flex; align-items: center; border: 1px solid var(--border); border-radius: 10px; overflow: hidden; background: #fff; }
        .qty-controller button { background: transparent; border: none; width: 38px; height: 38px; font-size: 16px; color: var(--green-darkest); cursor: pointer; }
        .qty-controller button:hover { background: var(--green-light); }
        .qty-controller input { width: 60px; height: 38px; border: none; text-align: center; font-weight: 600; font-size: 15px; }
        .summary-line { display: flex; justify-content: space-between; padding: 9px 0; font-size: 16px; }
        .summary-line.total { font-weight: 700; font-size: 20px; color: var(--green-darkest); padding-top: 14px; border-top: 1px solid var(--border); margin-top: 6px; }
        .order-submit {
            width: 100%;
            background: var(--green-darkest);
            color: #fff;
            border: none;
            padding: 17px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 18px;
            margin-top: 18px;
            cursor: pointer;
            transition: all .2s;
        }
        .order-submit:hover:not(:disabled) { background: var(--green); transform: translateY(-1px); }
        .order-submit:disabled { background: #9ca3af; cursor: not-allowed; }

        .var-btn {
            border: 1.5px solid var(--border);
            padding: 7px 14px;
            border-radius: 8px;
            cursor: pointer;
            margin: 0 6px 6px 0;
            background: #fff;
            font-weight: 600;
            font-size: 12px;
            display: inline-block;
            color: var(--text);
        }
        .var-btn.active { border-color: var(--green-darkest); background: var(--green-light); color: var(--green-darkest); }

        /* ===== FAQ ===== */
        .faq-section { padding: 60px 0 80px; background: var(--cream); }
        .faq-list { max-width: 720px; margin: 30px auto 0; }
        .faq-item {
            background: #fff;
            border-radius: 14px;
            padding: 18px 22px;
            margin-bottom: 10px;
            border: 1px solid var(--border);
            cursor: pointer;
            transition: all .2s;
        }
        .faq-item:hover { border-color: var(--green); }
        .faq-q { display: flex; justify-content: space-between; align-items: center; font-weight: 600; color: var(--green-darkest); font-size: 17px; }
        .faq-q i { transition: transform .25s; color: var(--green); }
        .faq-a {
            display: none;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid var(--border);
            font-size: 15px;
            color: var(--text-muted);
            line-height: 1.7;
        }
        .faq-item.open .faq-a { display: block; }
        .faq-item.open .faq-q i { transform: rotate(45deg); }

        /* ===== FOOTER ===== */
        .site-footer {
            background: var(--green-darkest);
            color: rgba(255,255,255,0.7);
            padding: 40px 0 22px;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 30px;
            align-items: start;
            margin-bottom: 24px;
        }
        .footer-brand-logo { display: flex; align-items: center; gap: 10px; }
        .footer-brand-logo .brand-icon { background: #fff; color: var(--green-darkest); }
        .footer-brand-logo .brand-text-main { color: #fff; }
        .footer-brand-logo .brand-text-sub { color: rgba(255,255,255,0.4); }
        .footer-col h6 { color: rgba(255,255,255,0.4); font-size: 13px; letter-spacing: 2px; text-transform: uppercase; font-weight: 700; margin-bottom: 12px; }
        .footer-col .info { font-size: 16px; color: #fff; font-weight: 500; line-height: 1.7; }
        .footer-bottom {
            padding-top: 18px;
            border-top: 1px solid rgba(255,255,255,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: rgba(255,255,255,0.45);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 900px) {
            .hero-grid { grid-template-columns: 1fr; gap: 30px; }
            .hero-img-wrap { order: -1; }
            .benefits-header, .steps-grid { grid-template-columns: 1fr; gap: 24px; }
            .footer-grid { grid-template-columns: 1fr; }
            .spec-row { grid-template-columns: 1fr; padding: 12px 16px; }
            .spec-row .spec-value { margin-top: 4px; }
            .tabs-card { margin: -28px 16px 0; flex-wrap: wrap; }
            .tab-link { flex: 1 1 calc(50% - 4px); font-size: 11px; padding: 8px; }
            .order-card { padding: 22px; }
            .footer-bottom { flex-direction: column; gap: 8px; text-align: center; }
        }
        @media (max-width: 540px) {
            .hero-stats { gap: 16px; }
            .hero-stat-item .num { font-size: 22px; }
            .benefit-row { grid-template-columns: 40px 1fr 20px; gap: 12px; }
            .benefit-num { font-size: 22px; }
            .benefit-title { font-size: 14px; }
            .benefit-desc { font-size: 12px; }
            .header-phone { font-size: 12px; }
        }

        @keyframes leaf-float { 0%,100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-10px) rotate(5deg); } }
        .leaf-deco {
            position: absolute;
            opacity: 0.08;
            pointer-events: none;
            animation: leaf-float 6s ease-in-out infinite;
        }
        .leaf-deco.l1 { top: 10%; right: 4%; font-size: 80px; color: var(--green); }
        .leaf-deco.l2 { bottom: 8%; left: 5%; font-size: 60px; color: var(--green); animation-delay: -2s; }

        /* ===== PAYMENT METHODS ===== */
        .pay-section { margin-top: 18px; }
        .pay-section .label { font-weight: 600; font-size: 13px; color: var(--green-darkest); margin-bottom: 8px; display: block; }
        .payment-box {
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 11px 14px;
            cursor: pointer;
            transition: .2s;
            background: #fff;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
            font-size: 13px;
        }
        .payment-box:hover { border-color: var(--green); }
        .payment-box i { font-size: 18px; color: var(--green); }
        .payment-box img { height: 22px; width: 22px; object-fit: contain; }
        input[name="payment_method"]:checked + .payment-box { border-color: var(--green-darkest); background: var(--green-light); }

        /* ===== COUPON ===== */
        .coupon-box {
            margin-top: 12px; margin-bottom: 12px;
            padding: 12px;
            background: var(--cream-2);
            border: 1px dashed var(--green);
            border-radius: 10px;
        }
        .coupon-box .form-label { color: var(--green-darkest); font-weight: 700; font-size: 12px; }
        .coupon-box .input-group { display: flex; gap: 6px; }
        .coupon-box input.form-control { flex: 1; }
        .coupon-box button { background: var(--green-darkest); color: #fff; border: none; padding: 0 16px; border-radius: 8px; font-weight: 700; font-size: 12px; cursor: pointer; }

        /* ===== WHATSAPP FLOATING BUTTON ===== */
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

        /* ===== OTP MODAL ===== */
        #otpModal { z-index: 99999 !important; }
        .otp-modal-content { border: none !important; border-radius: 16px !important; background: #fff; box-shadow: 0 10px 40px rgba(0,0,0,0.2); text-align: center; overflow: hidden; position: relative; }
        .otp-modal-content::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 5px; background: var(--green-darkest); }
        .otp-icon-box { width: 70px; height: 70px; background: var(--green-light); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 10px auto 16px; color: var(--green-darkest); font-size: 28px; }
        .otp-input { width: 100%; letter-spacing: 12px; text-align: center; font-size: 24px; font-weight: bold; color: #333; border: 2px solid #eee !important; border-radius: 10px !important; background: #fafafa; height: 58px; }
        .otp-input:focus { border-color: var(--green) !important; background: #fff; outline: none; }
        .btn-verify { background: var(--green-darkest); border: none; padding: 12px; font-size: 15px; border-radius: 10px; width: 100%; color: white; font-weight: 700; }
        .btn-verify:hover { background: var(--green); }

        /* Manual Payment Field Area */
        .manual-pay-area { display: none; margin-top: 10px; padding: 12px; background: var(--green-light); border-radius: 10px; }
        .manual-pay-area .alert { background: rgba(45,80,22,0.08); border: 1px solid var(--green); color: var(--green-darkest); padding: 8px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; margin-bottom: 10px; }

        @media (max-width: 540px) {
            .whats_btn { right: 12px; bottom: 12px; width: 48px; height: 48px; }
            .whats_btn img { width: 28px; height: 28px; }
        }
    </style>

    {{-- TikTok Pixel --}}
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

    {{-- FB Pixel with Full Event Stack (ViewContent, InitiateCheckout, Time + Scroll Tracking) --}}
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

        window.LP_EVENT_BASE  = {!! json_encode($lpEventBase ?? null) !!} || ('LP14_{{ $productId }}_' + Date.now());
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

        // Time Tracking
        var timeSteps = [10, 30, 60, 120];
        timeSteps.forEach(function(seconds) {
            setTimeout(function() {
                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({ 'event': 'time_on_page', 'time_spent': seconds, 'page_path': window.location.pathname });
                if (@json(!$__hasGtm) && typeof fbq === 'function') { fbq('trackCustom', 'TimeSpent', { time_in_seconds: seconds }); }
            }, seconds * 1000);
        });

        // Scroll Tracking
        var scrollSteps = [25, 50, 75, 90, 100];
        var scrolled = [];
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
/* 🔒 Hide all admin-blank elements — text, rows, sections — so নo static text shows when admin জা boshay নাই */
h1:empty, h2:empty, h3:empty, h4:empty, h5:empty, h6:empty,
p:empty, span:empty, div.brand-text-main:empty, div.brand-text-sub:empty,
.sec-title:empty, .sec-sub:empty, .sec-label:empty,
.hero-title:empty, .hero-sub:empty, .hero-desc:empty,
.benefit-title:empty, .benefit-desc:empty,
.step-title:empty, .step-desc:empty,
.spec-label:empty, .spec-value:empty,
.faq-q span:empty, .faq-a:empty { display: none !important; }

/* Hide repeated rows / cards when their main field is blank */
.hero-stat-item:has(.num:empty),
.tabs-card .tab-link:empty,
.benefit-row:has(.benefit-title:empty),
.step-item:has(.step-title:empty),
.spec-row:has(.spec-label:empty),
.faq-item:has(.faq-q > span:empty) { display: none !important; }

/* Hide empty sec-label / promise badge containers (no border-line either) */
.sec-label:not(:has(span:not(:empty))) { display: none !important; }
</style>
</head>
<body>

@if(!empty($__gtmId))
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $__gtmId }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
@endif

{{-- ====== TOP STRIPE ====== --}}
<div class="top-stripe">
    <div class="container-x">
        <div class="countdown-text">
            {{ $clean($ln_pg->countdown_title ?? null, 'বিশেষ অফার শেষ হবে') }}: <span id="countdown-timer">--:--:--</span>
        </div>
        <div class="phone-text">
            @if(!empty($ln_pg->phone))
                <i class="fas fa-phone-alt me-1"></i> {{ $ln_pg->phone }}
            @else
                <i class="fas fa-phone-alt me-1"></i> {{ $information->phone ?? '' }}
            @endif
        </div>
    </div>
</div>

{{-- ====== HEADER ====== --}}
<header class="site-header">
    <div class="container-x">
        <div class="header-row">
            <a href="#" class="brand-logo">
                <span class="brand-icon"><i class="fas fa-leaf"></i></span>
                <div>
                    <div class="brand-text-main">{{ $brandMain }}</div>
                    @if(!empty($brandSub))
                    <div class="brand-text-sub">{{ $brandSub }}</div>
                    @endif
                </div>
            </a>
            <a href="tel:{{ preg_replace('/\D+/', '', $ln_pg->phone ?? ($information->phone ?? '')) }}" class="header-phone">
                {{ $ln_pg->phone ?? ($information->phone ?? '') }}
            </a>
        </div>
    </div>
</header>

{{-- ====== HERO ====== --}}
<section class="hero-section">
    <i class="fas fa-leaf leaf-deco l1"></i>
    <i class="fas fa-seedling leaf-deco l2"></i>

    <div class="container-x">
        <div class="hero-grid">
            <div class="hero-left">
                <div class="sec-label left"><span style="opacity:.65;">{{ $clean($ln_pg->promise_badge ?? null, 'প্রিমিয়াম মরিঙ্গা') }}</span></div>
                <h1 class="hero-title">{{ $clean($ln_pg->title1 ?? null, 'প্রকৃতির সবুজ সুপারফুড।') }}</h1>
                <div class="hero-desc">
                    {!! $ln_pg->left_side_desc ?? '' !!}
                </div>
                <div class="hero-btn-row">
                    <a href="#order_section" class="btn-primary-x">
                        {{ $clean($ln_pg->btn_text_hero ?? null, 'এখনই অর্ডার করুন') }} <i class="fas fa-arrow-right"></i>
                    </a>
                    <a href="#benefits_section" class="btn-outline-x">{{ $clean($ln_pg->btn_text_video ?? null, 'আরো জানুন') }}</a>
                </div>
                <div class="hero-stats">
                    @foreach([1,2,3] as $n)
                        @php $sNum = $clean($ln_pg->{'stat_'.$n.'_num'} ?? null); $sLbl = $clean($ln_pg->{'stat_'.$n.'_text'} ?? null); @endphp
                        @if($sNum !== '' || $sLbl !== '')
                        <div class="hero-stat-item">
                            <div class="num">{{ $sNum }}</div>
                            <div class="lbl">{{ $sLbl }}</div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="hero-img-wrap">
                <div class="hero-img-card">
                    <span class="hero-premium-tag">{{ $clean($ln_pg->feature ?? null, 'PREMIUM QUALITY') }}</span>
                    <img src="{{ $heroImage }}" alt="{{ $productName }}">
                    <div class="hero-price-tag">
                        <div class="hero-price-new">৳{{ number_format($defaultPrice, 0) }}</div>
                        <div class="hero-price-old">৳{{ number_format($oldPrice, 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ====== TABS CARD ====== --}}
@php
    $tabTargets = ['#benefits_section','#benefits_section','#steps_section','#order_section'];
    $tabValues = [];
    foreach([1,2,3,4] as $n) {
        $t = $clean($ln_pg->{'trust_'.$n.'_title'} ?? null);
        if($t !== '') $tabValues[$n] = $t;
    }
@endphp
@if(count($tabValues) > 0)
<div class="container-x">
    <div class="tabs-card">
        @foreach($tabValues as $n => $t)
            <a href="{{ $tabTargets[$n-1] }}" class="tab-link {{ $loop->first ? 'active' : '' }}">{{ $t }}</a>
        @endforeach
    </div>
</div>
@endif

{{-- ====== BENEFITS SECTION ====== --}}
<section id="benefits_section" class="benefits-section">
    <div class="container-x">
        <div class="benefits-header">
            <div>
                <div class="sec-label left"><span>{{ $clean($ln_pg->promise_badge ?? null, 'কেন আমরা') }}</span></div>
                <div class="sec-title">{{ $clean($ln_pg->feature_title ?? null, 'প্রতিদিন এক চামচ,') }}<br>{{ $clean($ln_pg->promise_title ?? null, 'সারাজীবন সুস্থ থাকুন।') }}</div>
            </div>
            <div class="benefits-desc">
                {{ $clean($ln_pg->right_side_desc ?? null, '"Miracle Tree" বলে পরিচিত সজনা পাতা — যা প্রাকৃতিক ভাবে দেহের সকল প্রয়োজনীয় প্রাকৃতিক ভিটামিন, খনিজ এবং অ্যান্টিঅক্সিডেন্ট সরবরাহ করতে পারে।') }}
            </div>
        </div>

        @foreach([1,2,3,4,5,6] as $n)
            @php
                $bTitle = $clean($ln_pg->{'id_'.$n.'_title'} ?? null);
                $bDesc  = $clean($ln_pg->{'id_'.$n.'_desc'} ?? null);
                $bn = ['০১','০২','০৩','০৪','০৫','০৬'][$n-1];
            @endphp
            @if($bTitle !== '' || $bDesc !== '')
            <div class="benefit-row">
                <div class="benefit-num">{{ $bn }}</div>
                <div>
                    <div class="benefit-title">{{ $bTitle }}</div>
                    <div class="benefit-desc">{{ $bDesc }}</div>
                </div>
                <div class="benefit-arrow"><i class="fas fa-chevron-right"></i></div>
            </div>
            @endif
        @endforeach
    </div>
</section>

{{-- ====== 3 STEPS DARK SECTION ====== --}}
<section id="steps_section" class="steps-section">
    <div class="container-x">
        <div class="steps-grid">
            <div>
                <div class="sec-label left"><span>{{ $clean($ln_pg->identify_badge ?? null, 'দৈনিক রুটিন') }}</span></div>
                <h2 class="sec-title">{{ $clean($ln_pg->identify_title ?? null, 'তিনটি সহজ ধাপে,') }}<br>{{ $clean($ln_pg->identify_subtitle ?? null, 'প্রতিদিনের রুটিন।') }}</h2>
                <p class="sec-sub">{{ $clean($ln_pg->identify_desc ?? null, '৪ – ৬ মাস ব্যবহার করলে দীর্ঘস্থায়ী উপকারিতা সম্ভব। প্রতিদিন এক চামচ যথেষ্ট।') }}</p>
            </div>
            <div>
                @foreach([1,2,3] as $n)
                    @php
                        $stTitle = $clean($ln_pg->{'promise_'.$n.'_title'} ?? null);
                        $stDesc  = $clean($ln_pg->{'promise_'.$n.'_desc'} ?? null);
                    @endphp
                    @if($stTitle !== '' || $stDesc !== '')
                    <div class="step-item">
                        <div>
                            <div class="step-num">{{ str_pad($n, 2, '0', STR_PAD_LEFT) }}</div>
                        </div>
                        <div>
                            <div class="step-title">{{ $stTitle }}</div>
                            <div class="step-desc">{{ $stDesc }}</div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ====== SPEC TABLE SECTION ====== --}}
<section class="spec-section">
    <div class="container-x">
        <div class="text-center">
            <div class="sec-label"><span>{{ $clean($ln_pg->spec_badge ?? null, 'স্পেসিফিকেশন') }}</span></div>
            <h2 class="sec-title">{{ $clean($ln_pg->spec_title ?? null, 'যা আপনি পাচ্ছেন') }}</h2>
        </div>

        <div class="spec-table">
            @foreach([1,2,3,4,5,6,7,8] as $n)
                @php
                    $spL = $clean($ln_pg->{'spec_'.$n.'_label'} ?? null);
                    $spV = $clean($ln_pg->{'spec_'.$n.'_value'} ?? null);
                @endphp
                @if($spL !== '' || $spV !== '')
                <div class="spec-row">
                    <div class="spec-label">{{ $spL }}</div>
                    <div class="spec-value">{{ $spV }}</div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
</section>

{{-- ====== ORDER FORM ====== --}}
<section id="order_section" class="order-section">
    <div class="container-x">
        <div class="text-center">
            <div class="sec-label"><span>{{ $clean($ln_pg->form_subtitle ?? null, 'কনফার্ম অর্ডার') }}</span></div>
            <h2 class="sec-title">{{ $clean($ln_pg->form_title ?? null, 'এখনই অর্ডার করুন') }}</h2>
            <p class="text-muted" style="font-size:13px; margin-top:8px;">{{ $clean($ln_pg->form_desc ?? null, 'ফর্মটি পূরণ করুন এবং ক্যাশ অন ডেলিভারিতে প্রোডাক্ট গ্রহণ করুন') }}</p>
        </div>

        <div class="order-card">
            <form action="{{ route('front.storelandData') }}" method="POST" id="checkout_form">
                @csrf
                <input type="hidden" name="prd_id" value="{{ $productId }}">
                <input type="hidden" name="landing_page_type" value="14">
                <input type="hidden" name="purchase_event_id" id="purchase_event_id" value="">
                <input type="hidden" id="unit_price" value="{{ $defaultPrice }}">
                <input type="hidden" id="max_stock" value="{{ $defaultStock }}">

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">আপনার নাম *</label>
                        <input type="text" name="first_name" class="form-control" placeholder="পুরো নাম লিখুন" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">মোবাইল নাম্বার *</label>
                        <input type="tel" name="mobile" id="customer_mobile" class="form-control" placeholder="01XXXXXXXXX" minlength="11" maxlength="11" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">ডেলিভারি এলাকা *</label>
                        <select name="delivery_charge_id" id="delivery_charge" class="form-select" required>
                            <option value="" disabled selected>এলাকা নির্বাচন করুন</option>
                            @foreach($charges ?? [] as $charge)
                                <option value="{{ $charge->id }}" data-charge="{{ $isFreeShipping ? 0 : intval($charge->amount) }}">
                                    {{ $charge->title }} - {!! $isFreeShipping ? '<span style="color:#15803d; font-weight:700;">ফ্রি ডেলিভারি (০ ৳)</span>' : intval($charge->amount).' ৳' !!}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">সম্পূর্ণ ঠিকানা *</label>
                        <textarea name="shipping_address" class="form-control" rows="2" placeholder="বাসা নং, রাস্তা, থানা, জেলা" required></textarea>
                    </div>

                    @if($sizes->count() > 0 || $colors->count() > 0)
                        <input type="hidden" name="variation_id" id="variation_id" value="">
                        @if($sizes->count() > 0)
                        <div class="col-md-6">
                            <label class="form-label">সাইজ নির্বাচন *</label>
                            <div>
                                @foreach($sizes as $size)
                                    <div class="var-btn var-size-btn" data-id="{{ $size->id }}">{{ $size->name ?? $size->title }}</div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @if($colors->count() > 0)
                        <div class="col-md-6">
                            <label class="form-label">কালার নির্বাচন *</label>
                            <div>
                                @foreach($colors as $color)
                                    <div class="var-btn var-color-btn" data-id="{{ $color->id }}">{{ $color->name ?? $color->title }}</div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    @else
                        <input type="hidden" name="variation_id" id="variation_id" value="">
                    @endif
                </div>

                {{-- ===== PAYMENT METHODS (Same as Land Page 3) ===== --}}
                <div class="pay-section">
                    <span class="label">পেমেন্ট মেথড <span style="color:#dc2626;">*</span></span>

                    @if(isset($information->cod_active) && $information->cod_active == 1)
                    <label class="d-block w-100" style="cursor:pointer;">
                        <input type="radio" name="payment_method" value="cod" class="d-none" onchange="togglePaymentAction('cod')" checked>
                        <div class="payment-box">
                            <i class="fas fa-money-bill-wave" style="color:#16a34a;"></i>
                            <span style="font-weight:600;">ক্যাশ অন ডেলিভারি (COD)</span>
                        </div>
                    </label>
                    @endif

                    @if(isset($information->ssl_active) && $information->ssl_active == 1)
                    <label class="d-block w-100" style="cursor:pointer;">
                        <input type="radio" name="payment_method" value="sslcommerz" class="d-none" onchange="togglePaymentAction('online')">
                        <div class="payment-box">
                            <i class="fas fa-credit-card" style="color:#0ea5e9;"></i>
                            <span style="font-weight:600;">অনলাইন পেমেন্ট (bKash/Card/Visa)</span>
                        </div>
                    </label>
                    @endif

                    @if(isset($information->bkash_active) && $information->bkash_active == 1 && Route::has('bkash.create'))
                    <label class="d-block w-100" style="cursor:pointer;">
                        <input type="radio" name="payment_method" value="bkash" class="d-none" onchange="togglePaymentAction('bkash')">
                        <div class="payment-box">
                            <img src="{{ asset('frontend/images/bkash_logo.png') }}" alt="bKash" style="height:20px;width:auto;object-fit:contain;" onerror="this.style.display='none'">
                            <span style="font-weight:600;">বিকাশ পেমেন্ট (bKash)</span>
                        </div>
                    </label>
                    @endif

                    @if(isset($information->nagad_active) && $information->nagad_active == 1 && Route::has('nagad.pay'))
                    <label class="d-block w-100" style="cursor:pointer;">
                        <input type="radio" name="payment_method" value="nagad" class="d-none" onchange="togglePaymentAction('nagad')">
                        <div class="payment-box">
                            <img src="{{ asset('frontend/images/nagad.png') }}" alt="Nagad" style="height:20px;width:auto;object-fit:contain;" onerror="this.style.display='none'">
                            <span style="font-weight:600;">নগদ পেমেন্ট (Nagad)</span>
                        </div>
                    </label>
                    @endif

                    @if(isset($information->eps_active) && $information->eps_active == 1 && Route::has('eps.pay'))
                    <label class="d-block w-100" style="cursor:pointer;">
                        <input type="radio" name="payment_method" value="eps" class="d-none" onchange="togglePaymentAction('eps')">
                        <div class="payment-box">
                            <i class="fas fa-wallet" style="color:#06b6d4;"></i>
                            <span style="font-weight:600;">EPS পেমেন্ট</span>
                        </div>
                    </label>
                    @endif

                    @if(isset($information->uddoktapay_active) && $information->uddoktapay_active == 1 && Route::has('uddoktapay.pay'))
                    <label class="d-block w-100" style="cursor:pointer;">
                        <input type="radio" name="payment_method" value="uddoktapay" class="d-none" onchange="togglePaymentAction('uddoktapay')">
                        <div class="payment-box">
                            <i class="fas fa-money-check-alt" style="color:#16a34a;"></i>
                            <span style="font-weight:600;">উদ্যোক্তাপে (UddoktaPay)</span>
                        </div>
                    </label>
                    @endif

                    @if(isset($information->manual_payments) && $information->manual_payments == 1)
                        @php $activeManuals = \DB::table('manual_payments')->where('status', 1)->get(); @endphp
                        @foreach($activeManuals as $mp)
                        <label class="d-block w-100" style="cursor:pointer;">
                            <input type="radio" name="payment_method" value="{{ $mp->name }}" class="d-none" onchange="togglePaymentAction('manual', '{{ $mp->name }}', '{{ $mp->number }}', '{{ $mp->type }}')">
                            <div class="payment-box">
                                <i class="fas fa-mobile-alt" style="color:#6b7280;"></i>
                                <span style="font-weight:600;">{{ $mp->name }} ({{ $mp->type }})</span>
                            </div>
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

                {{-- ===== COUPON SECTION ===== --}}
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

                <div class="qty-row">
                    <span style="font-weight:600; font-size:14px;">পরিমাণ:</span>
                    <div class="qty-controller">
                        <button type="button" id="qty_minus">-</button>
                        <input type="number" name="quantity" id="qty_input" value="1" readonly>
                        <button type="button" id="qty_plus">+</button>
                    </div>
                </div>

                <div class="summary-line">
                    <span>প্রোডাক্ট প্রাইস (১ × <span id="unit_price_display">{{ $defaultPrice }}</span>)</span>
                    <span><span id="calc_subtotal">{{ $defaultPrice }}</span> ৳</span>
                </div>
                <div class="summary-line">
                    <span>ডেলিভারি চার্জ</span>
                    <span id="calc_shipping_text">+ <span id="calc_shipping">0</span> ৳</span>
                </div>
                <div class="summary-line" id="discount_row" style="{{ $sessionDiscount > 0 ? '' : 'display:none;' }}">
                    <span style="color:#15803d; font-weight:700;">ডিসকাউন্ট</span>
                    <span style="color:#15803d; font-weight:700;">- <span id="calc_discount">{{ $sessionDiscount }}</span> ৳</span>
                </div>
                <div class="summary-line total">
                    <span>মোট পেমেন্ট</span>
                    <span>৳<span id="calc_total">{{ $defaultPrice }}</span></span>
                </div>
                <input type="hidden" id="final_amount" name="final_amount" value="{{ $defaultPrice }}">
                <input type="hidden" name="amount" id="amount" value="{{ $defaultPrice }}">

                <button type="submit" id="submit_btn" class="order-submit">
                    {{ $clean($ln_pg->btn_text_form ?? null, 'অর্ডার কনফার্ম করুন') }} <i class="fas fa-arrow-right ms-1"></i>
                </button>

                <p class="text-center mt-3" style="font-size:11px; color:var(--text-muted);">
                    <i class="fas fa-lock me-1"></i> ১০০% সিকিউর চেকআউট — আপনার তথ্য নিরাপদ
                </p>
            </form>
        </div>
    </div>
</section>

{{-- ====== FAQ ====== --}}
<section class="faq-section">
    <div class="container-x">
        <div class="text-center">
            <div class="sec-label"><span>{{ $clean($ln_pg->faq_badge ?? null, 'হেল্প') }}</span></div>
            <h2 class="sec-title">{{ $clean($ln_pg->faq_title ?? null, 'সাধারণ জিজ্ঞাসা') }}</h2>
        </div>

        <div class="faq-list">
            @foreach([1,2,3,4,5] as $n)
                @php
                    $fq = $clean($ln_pg->{'faq_'.$n.'_q'} ?? null);
                    $fa = $clean($ln_pg->{'faq_'.$n.'_a'} ?? null);
                @endphp
                @if($fq !== '' || $fa !== '')
                <div class="faq-item">
                    <div class="faq-q">
                        <span>{{ $fq }}</span>
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="faq-a">{{ $fa }}</div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
</section>

{{-- ====== FOOTER ====== --}}
<footer class="site-footer">
    <div class="container-x">
        <div class="footer-grid">
            <div class="footer-col">
                <div class="footer-brand-logo">
                    <span class="brand-icon"><i class="fas fa-leaf"></i></span>
                    <div>
                        <div class="brand-text-main">{{ $ln_pg->footer_company ?? $brandMain }}</div>
                        @if(!empty($brandSub))
                        <div class="brand-text-sub">{{ $brandSub }}</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="footer-col">
                <h6>{{ $clean($ln_pg->footer_contact_label ?? null, 'যোগাযোগ') }}</h6>
                <div class="info">
                    {{ $ln_pg->phone ?? ($information->phone ?? '') }}<br>
                    {{ $clean($ln_pg->footer_email ?? null, ($information->email ?? 'hello@info.com')) }}
                </div>
            </div>
            <div class="footer-col">
                <h6>{{ $clean($ln_pg->footer_address_label ?? null, 'ঠিকানা') }}</h6>
                <div class="info">{{ $clean($ln_pg->dhamaka_title ?? null, ($information->address ?? 'ঢাকা, বাংলাদেশ')) }}</div>
            </div>
        </div>
        <div class="footer-bottom">
            <div>{{ $clean($ln_pg->footer_copyright ?? null, '© '.date('Y').' '.($ln_pg->footer_company ?? $brandMain).' — সকল অধিকার সংরক্ষিত।') }}</div>
        </div>
    </div>
</footer>

{{-- ===== OTP MODAL ===== --}}
<div class="modal fade" id="otpModal" data-bs-backdrop="static" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content otp-modal-content p-4">
      <div class="modal-header border-0 pb-0 justify-content-end">
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center pt-0 pb-3">
        <div class="otp-icon-box"><i class="fas fa-shield-alt"></i></div>
        <h4 class="fw-bold mb-2" style="color:var(--green-darkest);">মোবাইল ভেরিফিকেশন</h4>
        <p style="font-size:13px; color:#6b7280;">আপনার <span class="fw-bold" id="otp_sent_number"></span> নাম্বারে কোড পাঠানো হয়েছে।</p>
        <div class="form-group mb-3">
            <input type="text" id="otp_input" maxlength="4" class="form-control otp-input" placeholder="____" autocomplete="one-time-code" inputmode="numeric">
            <small class="text-danger mt-2 d-block fw-bold" id="otp_error"></small>
        </div>
        <button type="button" class="btn-verify" onclick="verifyOtpNow()">যাচাই করুন</button>
        <div class="text-center mt-3">
             <button type="button" class="btn btn-link text-decoration-none text-muted p-0 small" id="resendOtpBtn" onclick="sendOtpBeforeSubmit(true)">
                 কোড পাননি? <span style="color:var(--green-darkest); font-weight:700;">আবার পাঠান</span>
             </button>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ===== WHATSAPP FLOATING BUTTON ===== --}}
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

// ===== GLOBAL VARS =====
var current_discount_val = 0;
var current_discount_type = "fixed";
var isWeightBased = {{ $isWeightBased ? 'true' : 'false' }};
var isFreeShipping = {{ $isFreeShipping }};
var isOtpVerified = false;
var otpSystemEnabled = {{ $information->otp_system ?? 0 }};
var otpTimerInterval;
var isSendingOtp = false;
var paymentID;
var dynamicOrderId;
var successUrl = '';

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

// ===== COUNTDOWN TIMER =====
(function(){
    var hours = {{ (int)($ln_pg->countdown_hours ?? 3) }};
    var endTime = new Date().getTime() + (hours * 60 * 60 * 1000);
    var saved = sessionStorage.getItem('lp14_countdown_end');
    if (saved && parseInt(saved) > new Date().getTime()) { endTime = parseInt(saved); }
    else { sessionStorage.setItem('lp14_countdown_end', endTime); }

    function pad(n){ return n < 10 ? '0'+n : n; }
    function tick() {
        var diff = endTime - new Date().getTime();
        if (diff <= 0) { var el = document.getElementById('countdown-timer'); if(el) el.innerText = '00:00:00'; return; }
        var h = Math.floor(diff / 3600000);
        var m = Math.floor((diff % 3600000) / 60000);
        var s = Math.floor((diff % 60000) / 1000);
        var el = document.getElementById('countdown-timer');
        if(el) el.innerText = pad(h)+':'+pad(m)+':'+pad(s);
    }
    tick(); setInterval(tick, 1000);
})();

// ===== FAQ ACCORDION =====
document.querySelectorAll('.faq-item').forEach(function(item){
    item.addEventListener('click', function(){ this.classList.toggle('open'); });
});

// ===== TABS =====
document.querySelectorAll('.tab-link').forEach(function(t){
    t.addEventListener('click', function(){
        document.querySelectorAll('.tab-link').forEach(function(x){ x.classList.remove('active'); });
        this.classList.add('active');
    });
});

// ===== SMOOTH SCROLL =====
document.querySelectorAll('a[href^="#"]').forEach(function(a){
    a.addEventListener('click', function(e){
        var tgt = document.querySelector(this.getAttribute('href'));
        if (tgt) { e.preventDefault(); window.scrollTo({ top: tgt.offsetTop - 20, behavior: 'smooth' }); }
    });
});

// ===== MANUAL PAYMENT TOGGLE =====
window.togglePaymentAction = function(method, name = '', number = '', type = '') {
    var manualArea = $('#manual_payment_area');
    var sNum = $('#sender_number');
    var tId = $('#transaction_id');

    if(method === 'manual') {
        $('#payment_instruction').html(`আপনার টোটাল বিল <b>${number} (${type})</b> নাম্বারে Send Money করুন এবং নিচের তথ্য দিন।`);
        manualArea.slideDown();
        sNum.attr('required', 'required');
        tId.attr('required', 'required');
    } else {
        manualArea.slideUp();
        sNum.removeAttr('required');
        tId.removeAttr('required');
    }
};

// ===== COUPON APPLY =====
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

// ===== OTP TIMER & FUNCTIONS =====
function startOtpTimer(duration, display) {
    var timer = duration, seconds;
    clearInterval(otpTimerInterval);
    $('#resendOtpBtn').prop('disabled', true);
    otpTimerInterval = setInterval(function () {
        seconds = parseInt(timer % 60, 10);
        seconds = seconds < 10 ? "0" + seconds : seconds;
        display.html("Wait (" + seconds + "s)");
        if (--timer < 0) {
            clearInterval(otpTimerInterval);
            display.html("কোড পাননি? <span style='color:var(--green-darkest); font-weight:700;'>আবার পাঠান</span>");
            $('#resendOtpBtn').prop('disabled', false);
        }
    }, 1000);
}

window.sendOtpBeforeSubmit = function(isResend = false) {
    if(isSendingOtp) return;
    var mobile = $('#customer_mobile').val();
    if(!mobile || mobile.length !== 11) { toastr.error('সঠিক মোবাইল নাম্বার দিন'); return; }

    isSendingOtp = true;
    if(!isResend) {
        $('#submit_btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Sending OTP...');
    }

    $.ajax({
        url: "{{ route('sendOtp') }}", type: "POST", data: { mobile: mobile, _token: "{{ csrf_token() }}" },
        success: function(res) {
            isSendingOtp = false;
            if(!isResend) {
                $('#submit_btn').prop('disabled', false).html('{{ $ln_pg->btn_text_form ?? "" }} <i class="fas fa-arrow-right ms-1"></i>');
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
            if(!isResend) $('#submit_btn').prop('disabled', false).html('{{ $ln_pg->btn_text_form ?? "" }} <i class="fas fa-arrow-right ms-1"></i>');
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
                submitOrderFinal();
            } else { $('#otp_error').text(res.msg); }
        }
    });
};

// ===== FINAL SUBMIT WITH ALL PAYMENT ROUTING =====
function submitOrderFinal() {
    let $form = $('#checkout_form');
    let payMethod = $('input[name="payment_method"]:checked').val() || 'cod';

    var purchaseEventId = "PUR_{{ $productId }}_" + Date.now();
    $('#purchase_event_id').val(purchaseEventId);

    if(payMethod === 'sslcommerz'){
        $form.attr('action', "{{ url('/pay') }}")[0].submit();
        return;
    }

    $('#submit_btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> প্রসেসিং...');

    $.ajax({
        url: "{{ route('front.storelandData') }}",
        method: "POST",
        data: $form.serialize(),
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
                    dynamicOrderId = res.order_id || res.url.split('/').pop();
                    successUrl = res.url;
                    @if(isset($information->bkash_active) && $information->bkash_active == 1 && Route::has('bkash.create'))
                    initBkash();
                    setTimeout(function(){ $('#bKash_button').click(); }, 300);
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
                    setTimeout(function(){ window.location.href = res.url; }, 800);
                }
            } else {
                toastr.error(res.msg);
                $('#submit_btn').prop('disabled', false).html('{{ $ln_pg->btn_text_form ?? "" }} <i class="fas fa-arrow-right ms-1"></i>');
            }
        },
        error: function () {
            $('#submit_btn').prop('disabled', false).html('{{ $ln_pg->btn_text_form ?? "" }} <i class="fas fa-arrow-right ms-1"></i>');
            toastr.error('ফর্ম সাবমিট করতে সমস্যা হচ্ছে');
        }
    });
}

// ===== ORDER FORM SETUP =====
$(document).ready(function() {
    let varMatrix = @json($varMatrix);
    let selectedSize = 0; let selectedColor = 0;

    if($('.var-size-btn').length > 0) { selectedSize = $('.var-size-btn').first().data('id'); $('.var-size-btn').first().addClass('active'); }
    if($('.var-color-btn').length > 0) { selectedColor = $('.var-color-btn').first().data('id'); $('.var-color-btn').first().addClass('active'); }

    // CALCULATE (with discount + free shipping + weight-based delivery)
    window.calculate = function() {
        let price = Math.round(parseFloat($('#unit_price').val()) || 0);
        let qty = parseInt($('#qty_input').val()) || 1;
        let subtotal = price * qty;

        // Discount
        let discount = 0;
        if(current_discount_val > 0) {
            if (current_discount_type === 'percentage' || current_discount_type === 'percent') {
                discount = (subtotal * current_discount_val) / 100;
            } else {
                discount = current_discount_val;
            }
        }
        if(discount > 0) {
            $('#discount_row').show();
            $('#calc_discount').text(Math.round(discount));
        } else {
            $('#discount_row').hide();
        }

        $('#calc_subtotal').text(subtotal);
        $('#unit_price_display').text(price);
        $('input[name="amount"]').val(subtotal);

        let $opt = $('#delivery_charge').find("option:selected");
        let cid = $opt.val();

        // Free Shipping Logic
        if(isFreeShipping == 1) {
            $('#calc_shipping_text').html('<span style="color:#15803d; font-weight:700;">ফ্রি ডেলিভারি</span>');
            let total = subtotal - discount; if(total < 0) total = 0;
            $('#calc_total').text(total);
            $('#final_amount').val(total);
        } else if(isWeightBased && cid && cid !== '') {
            // Weight-based delivery via AJAX
            $.ajax({
                url: "{{ route('front.getDeliveryChargeAjax') }}",
                type: "POST",
                data: { delivery_charge_id: cid, product_id: "{{ $productId }}", quantity: qty, _token: "{{ csrf_token() }}" },
                success: function(res) {
                    if(res.success) {
                        let charge = Math.round(parseFloat(res.charge));
                        $('#calc_shipping_text').html('+ <span id="calc_shipping">' + charge + '</span> ৳');
                        let total = (subtotal + charge) - discount; if(total < 0) total = 0;
                        $('#calc_total').text(total);
                        $('#final_amount').val(total);
                    }
                }
            });
        } else {
            let charge = Math.round(parseFloat($opt.data('charge')) || 0);
            $('#calc_shipping_text').html('+ <span id="calc_shipping">' + charge + '</span> ৳');
            let total = (subtotal + charge) - discount; if(total < 0) total = 0;
            $('#calc_total').text(total);
            $('#final_amount').val(total);
        }
    };

    function checkVariation() {
        if ($('.var-size-btn').length === 0 && $('.var-color-btn').length === 0) {
            $('#variation_id').val('');
            $('#unit_price').val('{{ $defaultPrice }}');
            $('#max_stock').val('{{ $defaultStock }}');
            $('#submit_btn').prop('disabled', false).html('{{ $ln_pg->btn_text_form ?? "" }} <i class="fas fa-arrow-right ms-1"></i>');
            calculate();
            return;
        }
        if(Object.keys(varMatrix).length === 0) { calculate(); return; }
        let key = selectedSize + '_' + selectedColor;
        let matched = varMatrix[key];
        if(matched) {
            $('#variation_id').val(matched.id);
            $('#unit_price').val(matched.price);
            $('#max_stock').val(matched.stock);
            if(matched.stock <= 0) {
                toastr.error('Out of stock!');
                $('#submit_btn').prop('disabled', true).text('স্টকে নেই');
                $('#qty_input').val(0);
            } else {
                $('#submit_btn').prop('disabled', false).html('{{ $ln_pg->btn_text_form ?? "" }} <i class="fas fa-arrow-right ms-1"></i>');
                if($('#qty_input').val() == 0) $('#qty_input').val(1);
            }
        } else {
            $('#variation_id').val('');
            $('#submit_btn').prop('disabled', true).text('Out of Stock');
            toastr.error('এই কম্বিনেশন স্টকে নেই!');
            $('#qty_input').val(0);
        }
        calculate();
    }

    // Variation filter logic (size/color cross-filter like land 3)
    $('.var-size-btn').click(function() {
        $('.var-size-btn').removeClass('active');
        $(this).addClass('active');
        selectedSize = $(this).data('id');
        if ($('.var-color-btn').length > 0) {
            let hasValidColor = false; let firstValidColor = null;
            $('.var-color-btn').each(function() {
                let cId = $(this).data('id');
                if (varMatrix[selectedSize + '_' + cId]) {
                    $(this).show();
                    if (firstValidColor === null) firstValidColor = $(this);
                    if ($(this).hasClass('active')) hasValidColor = true;
                } else { $(this).hide().removeClass('active'); }
            });
            if (!hasValidColor && firstValidColor) { firstValidColor.addClass('active'); selectedColor = firstValidColor.data('id'); }
        }
        checkVariation();
    });
    $('.var-color-btn').click(function() {
        $('.var-color-btn').removeClass('active');
        $(this).addClass('active');
        selectedColor = $(this).data('id');
        if ($('.var-size-btn').length > 0) {
            let hasValidSize = false; let firstValidSize = null;
            $('.var-size-btn').each(function() {
                let sId = $(this).data('id');
                if (varMatrix[sId + '_' + selectedColor]) {
                    $(this).show();
                    if (firstValidSize === null) firstValidSize = $(this);
                    if ($(this).hasClass('active')) hasValidSize = true;
                } else { $(this).hide().removeClass('active'); }
            });
            if (!hasValidSize && firstValidSize) { firstValidSize.addClass('active'); selectedSize = firstValidSize.data('id'); }
        }
        checkVariation();
    });

    if($('.var-size-btn.active').length > 0) $('.var-size-btn.active').trigger('click');
    else if($('.var-color-btn.active').length > 0) $('.var-color-btn.active').trigger('click');
    else checkVariation();

    $('#delivery_charge').on('change', calculate);
    $('#qty_plus').click(function(){ let q = parseInt($('#qty_input').val()) || 1; let max = parseInt($('#max_stock').val()) || 1; if(q < max) { $('#qty_input').val(q + 1); calculate(); } else { toastr.warning('Max stock reached'); } });
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

    // Form Submit Routing
    $('#checkout_form').submit(function(e) {
        e.preventDefault();
        if(!$('#delivery_charge').val()){ toastr.warning('ডেলিভারি এলাকা নির্বাচন করুন'); return false; }

        if(!$('#variation_id').val() && ($('.var-size-btn').length > 0 || $('.var-color-btn').length > 0)) {
            toastr.error('সঠিক সাইজ/কালার সিলেক্ট করুন');
            return false;
        }
        if(parseInt($('#max_stock').val()) <= 0) { toastr.error('Out of stock!'); return false; }

        let paymentMethod = $('input[name="payment_method"]:checked').val() || 'cod';

        // Manual payment field validation
        if(paymentMethod !== 'online' && paymentMethod !== 'bkash' && paymentMethod !== 'eps' && paymentMethod !== 'nagad' && paymentMethod !== 'uddoktapay' && paymentMethod !== 'cod' && paymentMethod !== 'sslcommerz') {
            if(!$('#sender_number').val() || !$('#transaction_id').val()) {
                toastr.warning('পেমেন্ট নাম্বার ও Transaction ID দিন');
                return false;
            }
        }

        // SSLCommerz bypass OTP
        if (paymentMethod === 'sslcommerz' && !otpSystemEnabled) {
            let purchaseId = "PUR_{{ $productId }}_" + Date.now();
            $('#purchase_event_id').val(purchaseId);
            $(this).attr('action', "{{ url('/pay') }}").attr('method', 'POST')[0].submit();
            return;
        }

        // OTP trigger
        if(otpSystemEnabled == 1 && !isOtpVerified) {
            sendOtpBeforeSubmit();
        } else {
            submitOrderFinal();
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
    @include('frontend.partials.exit_popup', ['epLandingMode' => true])
</body>
</html>
