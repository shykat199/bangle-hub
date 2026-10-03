<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $ln_pg->title1 ?? 'Premium Landing Page' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    @php
        $information = \App\Models\Information::first();
        // $__hasGtm / $__ttFromGtm / $__gtmId are shared by the global View composer (AppServiceProvider).
        // ✅ Updated default brand color to match the image
        $brandColor  = $ln_pg->theme_primary_col ?? '#0284c7';
        $btnBg       = $ln_pg->btn_bg_color ?? '#ef4444';
        $btnTextCol  = $ln_pg->btn_text_color ?? '#ffffff';
        $cdBg        = $ln_pg->countdown_bg_color ?? '#0f172a';
        $cdTxt       = $ln_pg->countdown_text_color ?? '#ffffff';
        $cdHours     = (int)($ln_pg->countdown_hours ?? 20);

        $productId   = $product->id ?? 0;
        $productName = $product->name ?? 'Product';

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
            try {
                $product->loadMissing(['variations.size','variations.color','variations.stocks','category']);
                $variations = $product->variations ?? collect();
            } catch(\Throwable $e) { $variations = $product->variations ?? collect(); }
        }
        $defaultVar   = $variations->first();
        $defaultVarId = $defaultVar->id ?? null;
        $defaultStock = $defaultVar ? (int) $defaultVar->stocks->sum('quantity') : ($product ? max((int) ($product->stock_quantity ?? 0), (int) $product->stocks()->sum('quantity')) : 0);

        $phoneNumber = $ln_pg->phone ?? optional($information)->phone ?? '';
        $stockCount  = $ln_pg->stock_count ?? 24;
        $stockText   = str_replace('{count}', (string)$stockCount, $ln_pg->stock_text ?? 'মাত্র {count}টি স্টক বাকি');

        // ✅ PERFECTED HERO IMAGE LOGIC
        $productFallback = (!empty($product) && !empty($product->image)) ? getImage('products', $product->image) : asset('frontend/images/no-image.png');
        $heroImage = $productFallback;

        if (!empty($ln_pg->right_product_image)) {
            $heroImage = asset('landing_pages/' . str_replace('landing_pages/', '', $ln_pg->right_product_image));
        } elseif (!empty($ln_pg->image)) {
            $heroImage = asset('landing_pages/' . str_replace('landing_pages/', '', $ln_pg->image));
        }

        $isFreeShipping = (!empty($product->is_free_shipping) && $product->is_free_shipping == 1) ? 1 : 0;
    @endphp

    <style>
        body, h1,h2,h3,h4,h5,h6, p, div, span, a, button, input, select, textarea, label {
            font-family: 'Hind Siliguri', sans-serif;
        }
        .fas, .far, .fa, .fab { font-family: "Font Awesome 5 Free" !important; }
        .fab { font-family: "Font Awesome 5 Brands" !important; }

        :root {
            --brand: {{ $brandColor }};
            --btn-bg: {{ $btnBg }};
            --btn-text: {{ $btnTextCol }};
        }

        /* ✅ Deeper Theme Gradient Background */
        body { 
            background: linear-gradient(180deg, #cffafe 0%, #e0f2fe 15%, #f8fafc 40%, #f8fafc 100%); 
            color: #111827; 
            overflow-x: hidden; 
            max-width: 100vw; 
        }
        img { max-width: 100%; height: auto; }
        * { box-sizing: border-box; }

        /* Top Bar */
        .top-bar {
            background: #fff; border-bottom: 1px solid #e5e7eb;
            padding: 10px 0; position: sticky; top: 0; z-index: 100;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .top-bar-grid { display: flex; align-items: center; justify-content: space-between; }
        .top-bar-left { flex: 1; text-align: left; }
        .top-bar-center { flex: 1; text-align: center; }
        .top-bar-right { flex: 1; text-align: right; }
        
        .brand-name { font-weight: 800; font-size: 1.25rem; color: #0f172a; display: inline-flex; align-items: center; }
        
        .top-order-btn {
            background: var(--btn-bg); color: var(--btn-text);
            padding: 6px 18px; border-radius: 20px; font-weight: 700;
            text-decoration: none; display: inline-block; font-size: 0.9rem;
            transition: all .2s ease;
        }
        .top-order-btn:hover { color: var(--btn-text); transform: translateY(-1px); box-shadow: 0 4px 10px rgba(220, 38, 38, 0.2); }

        /* Countdown */
        .countdown-bar {
            background: {{ $cdBg }}; color: {{ $cdTxt }};
            padding: 14px 0; text-align: center; font-weight: 700;
        }
        .countdown-timer { display:inline-flex; gap:8px; margin-left:8px; font-variant-numeric: tabular-nums; font-size: 1.1rem; }
        .cd-box {
            background: rgba(255,255,255,0.12); padding: 4px 10px; border-radius: 6px;
            min-width: 42px; display: inline-block;
        }

        /* Hero */
        .hero { padding: 50px 0; background: transparent; text-align: center; }
        .hero-title { font-size: 3.5rem; font-weight: 800; line-height: 1.2; color: #0f172a; margin-bottom: 16px; }
        .hero-sub { color: #334155; font-size: 1.4rem; font-weight: 600; margin-top: 0; margin-bottom: 40px; line-height: 1.5; }
        
        .hero-img-wrap, .hero-video-wrap {
            background: transparent !important; border: none !important; box-shadow: none !important; 
            padding: 0 !important; text-align: center; width: 100%; margin: 0 auto;
        }
        .hero-video-wrap { border-radius: 16px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.15) !important; }
        .hero-img-wrap img { max-width: 100%; width: auto; height: auto; max-height: 450px; object-fit: contain; display: block; margin: 0 auto; }
        .hero-video-wrap .ratio { border-radius: 16px; overflow: hidden; width: 100%; }
        .hero-video-wrap iframe { border-radius: 16px; width: 100%; height: 100%; }
        
        .rating-row { display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 14px; color: #f59e0b; font-weight: 700; }
        .rating-row .stars i { font-size: 16px; }
        .rating-row .count { color: #64748b; font-weight: 600; font-size: 0.92rem; margin-left: 6px; }

        .price-block { display: flex; align-items: baseline; justify-content: center; gap: 14px; flex-wrap: wrap; margin-bottom: 16px; }
        .price-new { color: var(--btn-bg); font-size: 2.4rem; font-weight: 800; }
        .price-old { color: #94a3b8; text-decoration: line-through; font-size: 1.2rem; }
        .price-save { background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 6px; font-size: 0.9rem; font-weight: 700; }

        /* Button Animations */
        @keyframes pulse-flow {
            0% { transform: scale(1); box-shadow: 0 4px 15px rgba(220, 38, 38, 0.4); }
            50% { transform: scale(1.04); box-shadow: 0 10px 25px rgba(220, 38, 38, 0.7); }
            100% { transform: scale(1); box-shadow: 0 4px 15px rgba(220, 38, 38, 0.4); }
        }
        @keyframes shine-flow {
            0% { left: -100%; }
            20% { left: 100%; }
            100% { left: 100%; }
        }

        .btn-primary-cta {
            background: var(--btn-bg); color: var(--btn-text);
            border: none; border-radius: 30px; padding: 16px 36px;
            font-weight: 800; font-size: 1.2rem; text-decoration: none;
            display: inline-flex; align-items: center; justify-content: center; gap: 12px;
            box-shadow: 0 8px 20px rgba(220, 38, 38, 0.3);
            transition: all .2s ease;
            position: relative; overflow: hidden;
            animation: pulse-flow 2s infinite ease-in-out;
        }
        .btn-primary-cta::after {
            content: ''; position: absolute; top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.4) 50%, rgba(255,255,255,0) 100%);
            transform: skewX(-25deg); animation: shine-flow 3s infinite;
        }
        .btn-primary-cta:hover { color: var(--btn-text); }
        
        .cod-note { color: #475569; font-size: 1.05rem; font-weight: 700; margin-top: 14px; display: flex; align-items: center; justify-content: center; gap: 8px; }

        /* Section common */
        .sec { padding: 50px 0; }
        .sec-title { text-align: center; font-size: 1.7rem; font-weight: 800; margin-bottom: 8px; color: #0f172a; }
        .sec-sub { text-align: center; color: #64748b; margin-bottom: 36px; }

        /* Features */
        .feat-card {
            background: #fff; border-radius: 12px; padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04); height: 100%; border: 1px solid #e2e8f0;
            transition: transform .2s ease; display: flex; align-items: center; gap: 16px;
        }
        .feat-card:hover { transform: translateY(-4px); border-color: var(--brand); }
        .feat-icon { width: 50px; height: 50px; border-radius: 50%; flex-shrink: 0; background: #e0f2fe; color: #0284c7; display: inline-flex; align-items: center; justify-content: center; font-size: 22px; }
        .feat-text-wrap { text-align: left; }
        .feat-title { font-weight: 700; font-size: 1.05rem; margin-bottom: 4px; color: #0f172a; }
        .feat-desc { color: #64748b; font-size: 0.88rem; margin: 0; }

        /* Spec Table */
        .spec-container { background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 25px rgba(0,0,0,0.06); max-width: 900px; margin: 0 auto; border: 1px solid #f1f5f9; }
        .spec-row { display: flex; padding: 14px 20px; border-bottom: 1px dashed #e2e8f0; }
        .spec-row:last-child { border-bottom: 0; }
        .spec-label { flex: 0 0 40%; font-weight: 700; color: #475569; }
        .spec-value { flex: 1; color: #0f172a; font-weight: 600; }

        /* Reviews */
        .review-stats { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-bottom: 30px; }
        .stat-card { background: #fff; padding: 16px 24px; border-radius: 12px; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.06); min-width: 140px; }
        .stat-num { font-size: 1.6rem; font-weight: 800; color: var(--brand); }
        .stat-text { color: #64748b; font-size: 0.88rem; margin-top: 2px; }

        .review-card { background: #fff; border-radius: 14px; padding: 22px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); border: 1px solid #f1f5f9; height: 100%; position: relative; }
        .verified-badge { display: inline-flex; align-items: center; gap: 6px; background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 700; margin-bottom: 12px; }
        .review-text { color: #334155; font-size: 0.96rem; line-height: 1.6; margin-bottom: 16px; }
        .reviewer-name { font-weight: 700; color: #0f172a; }
        .reviewer-loc { color: #94a3b8; font-size: 0.85rem; }

        /* Order Form */
        .order-form-section { padding: 60px 0; }
        .order-card { background: #fff; border-radius: 20px; padding: 35px; box-shadow: 0 15px 40px rgba(0,0,0,0.08); max-width: 720px; margin: 0 auto; border: 1px solid #f1f5f9; }
        
        .package-card { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 14px 16px; border: 2px solid #e5e7eb; border-radius: 12px; margin-bottom: 10px; cursor: pointer; transition: all .2s ease; background: #fff; }
        .package-card:hover { border-color: var(--brand); }
        .package-card.active-pkg { border-color: var(--brand); background: rgba(37,99,235,0.06); box-shadow: 0 4px 12px rgba(37,99,235,0.1); }
        .package-card .pkg-left { display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0; }
        .package-card .pkg-right { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
        .pkg-radio { width: 18px; height: 18px; accent-color: var(--brand); cursor: pointer; flex-shrink: 0; }
        .pkg-qty-box { display: inline-flex; align-items: center; justify-content: center; border: 1px solid #e5e7eb; border-radius: 6px; background: #f8fafc; min-width: 38px; height: 32px; font-weight: 800; color: var(--brand); }
        
        @media (max-width: 576px) {
            .package-card { flex-direction: column; align-items: flex-start; gap: 10px; }
            .package-card .pkg-right { width: 100%; justify-content: space-between; }
        }
        .pkg-title { font-weight: 700; color: #0f172a; }
        .pkg-price { font-weight: 800; color: var(--btn-bg); font-size: 1.1rem; }
        .pkg-discount { background: #fef3c7; color: #92400e; padding: 3px 8px; border-radius: 6px; font-size: 0.78rem; font-weight: 700; margin-left: 8px; }

        .form-control, .form-select { border-radius: 10px; padding: 11px 14px; border: 1px solid #cbd5e1; font-size: 0.96rem; background: #f8fafc; }
        .form-control:focus, .form-select:focus { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15); background: #fff; }
        .form-label { font-weight: 700; color: #0f172a; margin-bottom: 6px; }

        .total-bill { background: #f0f9ff; border-radius: 12px; padding: 18px; margin: 18px 0; font-weight: 700; border: 1px dashed #bae6fd; }
        .total-bill .row-line { display: flex; justify-content: space-between; padding: 4px 0; }
        .total-bill .grand { font-size: 1.2rem; color: var(--btn-bg); border-top: 1px solid #bae6fd; margin-top: 10px; padding-top: 12px; }

        .security-note { background: #ecfdf5; color: #065f46; border-radius: 10px; padding: 10px 14px; font-size: 0.88rem; font-weight: 600; text-align: center; margin-top: 14px; border: 1px solid #d1fae5; }
        
        .btn-order-confirm { width: 100%; background: var(--btn-bg); color: var(--btn-text); border: none; border-radius: 12px; padding: 18px; font-weight: 800; font-size: 1.2rem; box-shadow: 0 8px 20px rgba(220, 38, 38, 0.25); transition: all .2s ease; }
        .btn-order-confirm:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(220, 38, 38, 0.35); }
        .btn-order-confirm:disabled { opacity: 0.7; cursor: wait; animation: none; }

        /* FAQ */
        .faq-card { background: #fff; border-radius: 10px; border: 1px solid #e2e8f0; margin-bottom: 12px; }
        .faq-q { padding: 16px 20px; cursor: pointer; font-weight: 700; color: #0f172a; display: flex; justify-content: space-between; align-items: center; }
        .faq-card.open .faq-q i { transform: rotate(180deg); }
        .faq-a { padding: 0 20px 16px; color: #475569; display: none; }
        .faq-card.open .faq-a { display: block; }
        .faq-card.open .faq-q { color: var(--brand); }

        /* Urgency */
        .urgency-block { background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 16px; padding: 30px; text-align: center; border: 2px solid #bbf7d0; }
        .stock-bar-wrap { background: #fff; border-radius: 12px; height: 14px; overflow: hidden; margin: 14px auto; max-width: 360px; border: 1px solid #e5e7eb; }
        .stock-bar-fill { height: 100%; background: linear-gradient(90deg, #dc2626, #f59e0b); border-radius: 12px; }

        /* Final CTA */
        .final-cta { text-align: center; margin: 40px auto; }
        .final-cta h2 { font-size: 2rem; font-weight: 800; margin-bottom: 12px; color: #0f172a; }
        .final-cta p { color: #475569; margin-bottom: 22px; }

        /* Footer */
        footer { background: #0f172a; color: #cbd5e1; padding: 40px 0 24px; text-align: center; }
        footer .fcompany { font-size: 1.2rem; font-weight: 800; color: #fff; margin-bottom: 10px; }
        footer a { color: #93c5fd; text-decoration: none; }
        footer .copyright { color: #64748b; font-size: 0.88rem; margin-top: 16px; }

        .pro-qty { display:inline-flex; border:1px solid #d1d5db; border-radius:8px; overflow:hidden; }
        .pro-qty span { padding: 6px 14px; cursor: pointer; user-select: none; font-weight: 800; background: #fff; }
        .pro-qty input { width: 48px; border: none; text-align: center; background: #f8fafc; font-weight: bold; }

        .slider-wrapper { background: transparent; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); overflow: hidden; border: 1px solid #e2e8f0; }
        .slider-img-fix { width: 100%; height: 450px; object-fit: cover; background: #fff; }

        /* ===== Mobile Responsive ===== */
        @media (max-width: 992px) {
            .hero-title { font-size: 2.8rem; }
            .hero-sub { font-size: 1.25rem; }
            .price-new { font-size: 2rem; }
            .sec { padding: 40px 0; }
            .sec-title { font-size: 1.5rem; }
        }
        @media (max-width: 768px) {
            .slider-img-fix { height: 300px !important; object-fit: cover; }
            
            .top-bar-center { display: none; }
            .brand-name { font-size: 1.1rem; }
            .top-order-btn { padding: 6px 14px; font-size: 0.85rem; }

            .countdown-bar { padding: 10px 6px; font-size: 0.92rem; }
            .countdown-timer { font-size: 1rem; gap: 4px; margin-left: 4px; }
            .cd-box { min-width: 34px; padding: 3px 7px; }

            .hero { padding: 30px 0; }
            .hero-img-wrap, .hero-video-wrap { padding: 0 !important; border-radius: 14px; margin-left: auto; margin-right: auto; max-width: 100%; display: block !important; }
            .hero-img-wrap img { width: 100% !important; height: auto !important; max-height: 280px !important; object-fit: contain !important; display: block !important; margin: 0 auto; }
            .hero-video-wrap iframe { max-height: 220px; }
            
            .hero-title { font-size: 2.2rem; line-height: 1.25; margin-bottom: 12px; }
            .hero-sub { font-size: 1.1rem; margin-bottom: 30px; }
            
            .price-block { gap: 8px; }
            .price-new { font-size: 1.8rem; }
            .price-old { font-size: 1.1rem; }
            .btn-primary-cta { padding: 14px 22px; font-size: 1.1rem; width: 100%; justify-content: center; }
            
            .sec { padding: 32px 0; }
            .sec-title { font-size: 1.35rem; }
            .sec-sub { font-size: 0.95rem; margin-bottom: 22px; }

            .feat-card { padding: 16px; flex-direction: column; align-items: center; text-align: center; }
            .feat-icon { margin-bottom: 6px; }
            .feat-text-wrap { text-align: center; }

            .spec-row { padding: 12px 14px; flex-direction: column; gap: 4px; }
            .spec-label { flex: none; font-size: 0.85rem; color: #64748b; }
            
            .order-form-section { padding: 36px 0; }
            .order-card { padding: 22px; border-radius: 16px; }
            
            .total-bill .grand { font-size: 1.1rem; }
            .btn-order-confirm { padding: 16px; font-size: 1.1rem; }
            .faq-q { padding: 14px 16px; font-size: 0.95rem; }
            
            footer { padding: 28px 0 18px; font-size: 0.88rem; }
        }
        @media (max-width: 480px) {
            .hero-title { font-size: 1.8rem; }
            .hero-sub { font-size: 1rem; }
            .price-new { font-size: 1.6rem; }
            .brand-name i { display: none; }
        }

        /* ✅ MOBILE STICKY CTA - EXACT SAME SIZE & HALF HEIGHT */
        @media (max-width: 768px) {
            .mobile-sticky {
                position: fixed; bottom: 0; left: 0; right: 0; z-index: 99;
                background: #fff; padding: 6px 12px; box-shadow: 0 -4px 15px rgba(0,0,0,0.08);
                border-top: 1px solid #e5e7eb; display: block;
            }
            .mobile-sticky .d-flex { gap: 10px; align-items: center; }

            .btn-outline-sticky, .sticky-order-btn {
                flex: 1; /* দুজনকেই একদম সমান ৫০% জায়গা দেবে */
                width: 50%;
                height: 36px !important; /* উচ্চতা একদম ফিক্সড করে দিলাম (আগের অর্ধেক) */
                padding: 0 !important; /* প্যাডিং জিরো করে শুধু height দিয়ে ব্যালান্স করলাম */
                font-size: 0.9rem !important;
                border-radius: 6px !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                text-decoration: none !important;
                margin: 0 !important;
            }

            .btn-outline-sticky {
                background: #fff; color: #0f172a; font-weight: 700;
                border: 1px solid #cbd5e1; gap: 6px;
            }

            .sticky-order-btn {
                white-space: nowrap;
            }

            body { padding-bottom: 55px; } /* Sticky bar onujayi body gap komano holo */
        }
        @media (min-width: 769px) { .mobile-sticky { display: none !important; } }

        .single-variation .variation-wrap { display: none !important; }
        .variation-wrap.single-product-hidden { display: none !important; }
    </style>

    @php
        $pixelId = setting('fb_pixel_id') ?? null;
        $ttPixelId       = setting('tt_pixel_id') ?? null;
        $contentCategory = optional(optional($product ?? null)->category)->name ?? 'Landing Page';
        // Ids the server already used for the Meta Conversions API + TikTok Events API.
        $lpEventBase = $lpEventBase ?? ('LP9_' . $productId . '_' . now()->format('YmdHis'));
        $lpEventIdVC = $lpEventIdVC ?? ($lpEventBase . '_VC');
        $lpEventIdIC = $lpEventIdIC ?? ($lpEventBase . '_IC');
        $activeManuals = \App\Models\ManualPayment::where('status', 1)->get();
    @endphp

    {{-- ✅ GTM + Tracking Code --}}
    {!! optional($information)->tracking_code !!}

    {{-- ✅ Facebook Pixel --}}
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
</head>
<body>
@if(!empty($__gtmId))
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $__gtmId }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
@endif

{{-- ১. COUNTDOWN BAR --}}
@if($ln_pg->countdown_title)
<div class="countdown-bar">
    <div class="container">
        <span>{{ $ln_pg->countdown_title }}</span>
        <span class="countdown-timer">
            <span class="cd-box" id="cd-h">00</span>:
            <span class="cd-box" id="cd-m">00</span>:
            <span class="cd-box" id="cd-s">00</span>
        </span>
    </div>
</div>
@endif

{{-- ২. TOP BAR --}}
<header class="top-bar">
    <div class="container top-bar-grid">
        <div class="top-bar-left">
            <div class="brand-name"><i class="fas fa-fan me-2"></i>{{ $ln_pg->footer_company ?? $productName }}</div>
        </div>
        <div class="top-bar-center d-none d-md-block">
            @if($phoneNumber)
            <a href="tel:{{ $phoneNumber }}" style="color:#1e293b; font-weight:700; text-decoration:none; font-size:1.1rem;">
                <i class="fas fa-phone-alt" style="color:#0f172a; margin-right:4px;"></i> {{ $phoneNumber }}
            </a>
            @endif
        </div>
        <div class="top-bar-right">
            <a href="#order-form" class="top-order-btn">অর্ডার করুন</a>
        </div>
    </div>
</header>

{{-- ৩. HERO SECTION --}}
<section class="hero">
    <div class="container">
        <h1 class="hero-title">{{ $ln_pg->title1 }}</h1>
        @if(!empty(trim($ln_pg->title2)))
        <h6 class="hero-sub">{{ $ln_pg->title2 }}</h6>
        @endif

        <div class="mx-auto" style="max-width: 800px;">
            @if(!empty($ln_pg->video_url))
                <div class="hero-video-wrap mb-4">
                    @php
                        $videoUrl = trim($ln_pg->video_url);
                        $isIframe = stripos($videoUrl, '<iframe') !== false;
                    @endphp
                    @if($isIframe)
                        <div class="ratio ratio-16x9">{!! $videoUrl !!}</div>
                    @else
                        <div class="ratio ratio-16x9">
                            <iframe src="{{ $videoUrl }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                        </div>
                    @endif
                </div>
            @elseif(!empty($ln_pg->image) || !empty($ln_pg->right_product_image) || (!empty($product) && !empty($product->image)))
                <div class="hero-img-wrap mb-4">
                    <img src="{{ $heroImage }}" onerror="this.onerror=null; this.src='{{ $productFallback }}';" alt="{{ $productName }}">
                </div>
            @endif
        </div>

        @if($ln_pg->hero_rating)
        <div class="rating-row">
            <span class="stars">
                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
            </span>
            <span>{{ $ln_pg->hero_rating }}/5</span>
            <span class="count">— {{ $ln_pg->hero_rating_count }}</span>
        </div>
        @endif

        <div class="price-block">
            <span class="price-new">৳{{ $defaultPrice }}</span>
            @if($oldPrice)
                <span class="price-old">৳{{ $oldPrice }}</span>
            @endif
            @if($ln_pg->discount_save_text)
                <span class="price-save">{{ $ln_pg->discount_save_text }}</span>
            @endif
        </div>

        <div class="mt-3">
            <a href="#order-form" class="btn-primary-cta">
                {{ $ln_pg->btn_text_hero ?? 'অর্ডার করুন' }} <i class="fas fa-arrow-right"></i>
            </a>
            <div class="cod-note"><i class="fas fa-money-bill-wave"></i> {{ $ln_pg->pay_text ?? 'ক্যাশ অন ডেলিভারি' }}</div>
        </div>
    </div>
</section>

{{-- ৪. GALLERY SLIDER (✅ Added explicit Gap below Title and Below Section) --}}
@if(isset($ln_pg->images) && $ln_pg->images->count() > 0)
<section class="sec" style="padding: 36px 0; margin-bottom: 60px;"> <!-- ✅ Section এর নিচে গ্যাপ -->
    <div class="container">
        <h2 class="sec-title" style="margin-bottom: 30px;">প্রোডাক্ট গ্যালারি</h2> <!-- ✅ টাইটেলের নিচে গ্যাপ -->
        <div class="mx-auto slider-wrapper" style="max-width: 700px; margin-bottom: 20px;">
            <div id="productGallerySlider" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="3000">
                <div class="carousel-inner">
                    @foreach($ln_pg->images as $key => $img)
                        @php
                            $cleanPath = str_replace(['landing_sliders/', 'landing_pages/'], '', $img->image);
                            $sliderUrl = asset('landing_sliders/' . $cleanPath);
                        @endphp
                        <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                            <img src="{{ $sliderUrl }}" 
                                 class="d-block w-100 slider-img-fix" 
                                 alt="Gallery {{ $key + 1 }}"
                                 onerror="this.onerror=null; this.src='{{ $productFallback ?? asset('frontend/images/no-image.png') }}';">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- REVIEW IMAGES --}}
@if(isset($ln_pg->review_images) && $ln_pg->review_images->count() > 0)
<section class="sec" style="background:#f1f5f9; padding: 36px 0;">
    <div class="container">
        <h2 class="sec-title">{{ $ln_pg->review_top_text ?? 'কাস্টমারদের লাইভ ছবি' }}</h2>
        <div class="row g-3 mt-3 justify-content-center">
            @foreach($ln_pg->review_images as $r_img)
                @php
                    $reviewImgPath = str_replace('review_landing_sliders/', '', $r_img->review_image);
                @endphp
                <div class="col-6 col-md-4 col-lg-3">
                    <div style="background: #fff; padding: 6px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                        <img src="{{ asset('review_landing_sliders/'.$reviewImgPath) }}" class="img-fluid rounded" alt="Review Image" style="width: 100%; aspect-ratio: 1/1; object-fit: cover;">
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ৫. FEATURES --}}
@if($ln_pg->id_1_title || $ln_pg->id_2_title)
@php
    $defaultFeatIcons = ['fa-bolt', 'fa-battery-full', 'fa-feather-alt', 'fa-charging-station', 'fa-shield-alt', 'fa-volume-mute', 'fa-fan', 'fa-wind'];
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
@endphp
<section class="sec">
    <div class="container">
        <h2 class="sec-title">{{ $ln_pg->feature_title ?? 'প্রোডাক্টের বিশেষ ফিচার' }}</h2>
        <div class="row g-3 mt-2">
            @for($i=1; $i<=6; $i++)
                @if($ln_pg->{'id_'.$i.'_title'})
                @php $iconClass = $cleanIcon($ln_pg->{'id_'.$i.'_icon'}, $defaultFeatIcons[$i-1] ?? 'fa-check-circle'); @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="feat-card">
                        <div class="feat-icon"><i class="fas {{ $iconClass }}"></i></div>
                        <div class="feat-text-wrap">
                            <div class="feat-title">{{ $ln_pg->{'id_'.$i.'_title'} }}</div>
                            <p class="feat-desc">{{ $ln_pg->{'id_'.$i.'_desc'} }}</p>
                        </div>
                    </div>
                </div>
                @endif
            @endfor
        </div>
    </div>
</section>
@endif

{{-- ৬. SPEC TABLE --}}
@if($ln_pg->spec_1_label)
<section class="sec">
    <div class="container">
        <h2 class="sec-title" style="margin-bottom: 40px;">{{ $ln_pg->spec_title ?? 'প্রোডাক্ট স্পেসিফিকেশন' }}</h2>
        <div class="spec-container">
            <div class="row g-0 align-items-stretch">
                <div class="col-md-5" style="padding:0;">
                    <img src="{{ $heroImage }}" onerror="this.onerror=null; this.src='{{ $productFallback }}';" alt="Specification Image" style="width: 100%; height: 100%; min-height:300px; object-fit: cover;">
                </div>
                <div class="col-md-7 p-4 p-md-5">
                    <div class="spec-card shadow-none rounded-0">
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
        </div>
    </div>
</section>
@endif

{{-- ৭. REVIEWS --}}
@if($ln_pg->rev_1_text || $ln_pg->stat_1_num)
<section class="sec">
    <div class="container">
        <h2 class="sec-title">{{ $ln_pg->review_title ?? 'কাস্টমার রিভিউ' }}</h2>
        @if($ln_pg->review_subtitle)
        <p class="sec-sub">{{ $ln_pg->review_subtitle }}</p>
        @endif

        @if($ln_pg->stat_1_num || $ln_pg->stat_2_num || $ln_pg->stat_3_num)
        <div class="review-stats">
            @for($i=1; $i<=3; $i++)
                @if($ln_pg->{'stat_'.$i.'_num'})
                <div class="stat-card">
                    <div class="stat-num">{{ $ln_pg->{'stat_'.$i.'_num'} }}</div>
                    <div class="stat-text">{{ $ln_pg->{'stat_'.$i.'_text'} }}</div>
                </div>
                @endif
            @endfor
        </div>
        @endif

        <div class="row g-3 mt-2">
            @for($i=1; $i<=3; $i++)
                @if($ln_pg->{'rev_'.$i.'_text'})
                <div class="col-md-4">
                    <div class="review-card">
                        <div class="verified-badge"><i class="fas fa-check-circle"></i> Verified Buyer</div>
                        <p class="review-text">"{{ $ln_pg->{'rev_'.$i.'_text'} }}"</p>
                        <div class="reviewer-name">{{ $ln_pg->{'rev_'.$i.'_name'} }}</div>
                        <div class="reviewer-loc"><i class="fas fa-map-marker-alt me-1"></i>{{ $ln_pg->{'rev_'.$i.'_loc'} }}</div>
                    </div>
                </div>
                @endif
            @endfor
        </div>
    </div>
</section>
@endif

{{-- ৮. ORDER FORM --}}
<section class="order-form-section" id="order-form">
    <div class="container">
        <h2 class="sec-title">{{ $ln_pg->form_title ?? 'অর্ডার করুন এখনই' }}</h2>
        @if($ln_pg->form_subtitle)
        <p class="sec-sub">{{ $ln_pg->form_subtitle }}</p>
        @endif

        <div class="order-card">
            <form id="checkout_land_form" action="{{ route('front.storelandData') }}" method="POST">
                @csrf
                        {{-- কোন landing page থেকে order এলো (Order Management-এর Source-এ দেখায়) --}}
                        <input type="hidden" name="landing_page_type" value="9">
                <input type="hidden" name="prd_id" value="{{ $productId }}">
                <input type="hidden" name="amount" id="subtotal_input" value="{{ $defaultPrice }}">
                <input type="hidden" name="final_amount" id="final_total_input" value="{{ $defaultPrice }}">
                <input type="hidden" name="quantity" id="form_qty" value="1">
                <input type="hidden" name="purchase_event_id" id="purchase_event_id" value="">
                <input type="hidden" name="coupon_code" value="">
                <input type="hidden" name="discount" value="0">

                {{-- Package Selection --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">প্যাকেজ সিলেক্ট করুন:</label>

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

                {{-- Variation --}}
                @if($variations->count() > 0)
                    @if($variations->count() == 1)
                        @php
                            $sv = $variations->first();
                            $svBase = $sv->price ?? $product->sell_price ?? 0;
                            $svDisc = $sv->after_discount_price ?? null;
                            $svPrice = ((float)$svDisc > 0) ? $svDisc : $svBase;
                            $svStock = $sv->stocks->sum('quantity');
                            $svLabel = trim(($sv->size->name ?? '') . ' ' . ($sv->color->name ?? ''));
                        @endphp
                        <input type="hidden" name="variation_id" id="variation_select" value="{{ $sv->id }}" data-price="{{ $svPrice }}" data-stock="{{ $svStock }}">
                        
                        <div class="mb-3" style="display: none;">
                            <label class="form-label">সাইজ/কালার</label>
                            <input type="text" class="form-control" value="{{ $svLabel ?: ('Variation #'.$sv->id) }}" readonly>
                        </div>
                    @else
                        <div class="mb-3">
                            <label class="form-label">{{ $ln_pg->variation_label ?? 'সাইজ/কালার সিলেক্ট করুন' }} *</label>
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
                    @endif
                @else
                    <input type="hidden" name="variation_id" id="variation_select" value="">
                @endif

                <input type="hidden" id="max_stock" value="{{ $defaultStock }}">

                <div class="mb-3">
                    <label class="form-label">{{ $ln_pg->name_label ?? 'আপনার নাম *' }}</label>
                    <input type="text" name="first_name" id="name" class="form-control" placeholder="{{ $ln_pg->name_placeholder ?? 'সম্পূর্ণ নাম' }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ $ln_pg->phone_label ?? 'মোবাইল নাম্বার *' }}</label>
                    <input type="tel" name="mobile" id="mobile" class="form-control" placeholder="01XXXXXXXXX" maxlength="11" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ $ln_pg->address_label ?? 'সম্পূর্ণ ঠিকানা *' }}</label>
                    <textarea name="shipping_address" id="address" class="form-control" rows="2" placeholder="{{ $ln_pg->address_placeholder ?? 'বাসা নং, রোড, এলাকা, থানা' }}" required></textarea>
                </div>

                <div class="mb-3 d-flex align-items-center justify-content-between border p-2 rounded bg-light">
                    <label class="form-label mb-0">পরিমাণ</label>
                    <div class="pro-qty">
                        <span class="decrease-qty">-</span>
                        <input type="text" class="inner_qty" value="1" readonly>
                        <span class="increase-qty">+</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ $ln_pg->delivery_label ?? 'ডেলিভারি এরিয়া *' }}</label>
                    <select name="delivery_charge_id" id="delivery_charge" class="form-select" required>
                        <option value="" disabled selected>এলাকা নির্বাচন করুন...</option>
                        @foreach($charges as $ch)
                            <option value="{{ $ch->id }}" data-charge="{{ $isFreeShipping ? 0 : intval($ch->amount) }}">
                                {{ $ch->title }} — {!! $isFreeShipping ? '<span style="color:#16a34a">ফ্রি (0৳)</span>' : intval($ch->amount).' ৳' !!}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- ✅ COUPON SECTION --}}
                @if(isset($information->coupon_visibility) && $information->coupon_visibility == 1)
                <div style="background:#f8fafc; border:1px dashed #cbd5e1; padding:14px; border-radius:12px; margin:14px 0;">
                    <label class="form-label fw-bold mb-2"><i class="fas fa-ticket-alt me-1"></i> কুপন কোড আছে?</label>
                    <div style="display:flex; border:1px solid #d1d5db; border-radius:10px; background:#fff; overflow:hidden;">
                        <input type="text" id="coupon_code" placeholder="Enter coupon code" style="border:none; padding:10px 14px; flex-grow:1; outline:none; min-width:0;">
                        <button type="button" id="coupon_btn_submit" onclick="applyCouponLand()" style="border:none; background:#0f172a; color:#fff; padding:10px 22px; font-weight:700; cursor:pointer; flex-shrink:0; white-space:nowrap;">APPLY</button>
                    </div>
                    <small id="coupon_msg" class="d-block mt-2 fw-bold"></small>
                </div>
                @endif

                {{-- ✅ PAYMENT METHODS --}}
                <div class="mb-3 mt-3">
                    <label class="form-label fw-bold">{{ $ln_pg->payment_title ?? 'পেমেন্ট মাধ্যম' }}</label>

                    @if(isset($information->cod_active) && $information->cod_active == 1)
                    <label class="payment-radio-box active" style="display:flex; align-items:center; gap:10px; padding:12px 14px; border:2px solid var(--brand); border-radius:10px; margin-bottom:6px; cursor:pointer; background:rgba(37,99,235,0.05);">
                        <input type="radio" name="payment_method" value="cod" checked onchange="togglePaymentAction('cod')">
                        <i class="fas fa-money-bill-wave text-success"></i>
                        <span class="fw-bold">{{ $ln_pg->cod_title ?? 'ক্যাশ অন ডেলিভারি' }}</span>
                    </label>
                    @endif

                    @if(isset($information->ssl_active) && $information->ssl_active == 1)
                    <label class="payment-radio-box" style="display:flex; align-items:center; gap:10px; padding:12px 14px; border:2px solid #e5e7eb; border-radius:10px; margin-bottom:6px; cursor:pointer; background:#fff;">
                        <input type="radio" name="payment_method" value="sslcommerz" onchange="togglePaymentAction('sslcommerz')">
                        <i class="fas fa-credit-card text-primary"></i>
                        <span class="fw-bold">অনলাইন পেমেন্ট (SSL)</span>
                    </label>
                    @endif

                    @if(isset($information->bkash_active) && $information->bkash_active == 1)
                    <label class="payment-radio-box" style="display:flex; align-items:center; gap:10px; padding:12px 14px; border:2px solid #e5e7eb; border-radius:10px; margin-bottom:6px; cursor:pointer; background:#fff;">
                        <input type="radio" name="payment_method" value="bkash" onchange="togglePaymentAction('bkash')">
                        <span class="fw-bold">বিকাশ (bKash)</span>
                    </label>
                    @endif

                    @if(isset($information->nagad_active) && $information->nagad_active == 1)
                    <label class="payment-radio-box" style="display:flex; align-items:center; gap:10px; padding:12px 14px; border:2px solid #e5e7eb; border-radius:10px; margin-bottom:6px; cursor:pointer; background:#fff;">
                        <input type="radio" name="payment_method" value="nagad" onchange="togglePaymentAction('nagad')">
                        <span class="fw-bold">নগদ (Nagad)</span>
                    </label>
                    @endif

                    @if(isset($information->uddoktapay_active) && $information->uddoktapay_active == 1)
                    <label class="payment-radio-box" style="display:flex; align-items:center; gap:10px; padding:12px 14px; border:2px solid #e5e7eb; border-radius:10px; margin-bottom:6px; cursor:pointer; background:#fff;">
                        <input type="radio" name="payment_method" value="uddoktapay" onchange="togglePaymentAction('uddoktapay')">
                        <span class="fw-bold">উদ্দোক্তাপে (UddoktaPay)</span>
                    </label>
                    @endif

                    @foreach($activeManuals as $mp)
                    <label class="payment-radio-box" style="display:flex; align-items:center; gap:10px; padding:12px 14px; border:2px solid #e5e7eb; border-radius:10px; margin-bottom:6px; cursor:pointer; background:#fff;">
                        <input type="radio" name="payment_method" value="{{ $mp->name }}" data-number="{{ $mp->number }}" data-type="{{ $mp->type }}" onchange="togglePaymentAction('manual', '{{ $mp->name }}', '{{ $mp->number }}', '{{ $mp->type }}')">
                        <span class="fw-bold">{{ $mp->name }}</span>
                    </label>
                    @endforeach

                    <div id="manualPaymentBox" style="background:#fef3c7; border-radius:10px; padding:12px; margin-top:8px; display:none;">
                        <p class="mb-2 small fw-bold">পেমেন্ট পাঠান <span id="manual_number" class="text-danger"></span> (<span id="manual_type"></span>) এ</p>
                        <input type="text" name="sender_number" id="sender_number" class="form-control mb-2" placeholder="যে নাম্বার থেকে পাঠিয়েছেন">
                        <input type="text" name="transaction_id" id="transaction_id" class="form-control" placeholder="ট্রানজেকশন ID">
                    </div>
                </div>

                {{-- Hidden coupon fields --}}
                <input type="hidden" name="coupon_code" id="hidden_coupon_code" value="">
                <input type="hidden" name="discount" id="hidden_discount" value="0">

                <div class="total-bill">
                    <div class="row-line"><span>প্রোডাক্ট:</span> <span>৳<span id="subtotal_disp">{{ $defaultPrice }}</span></span></div>
                    <div class="row-line"><span>ডেলিভারি:</span> <span>৳<span id="delivery_disp">0</span></span></div>
                    <div class="row-line grand"><span>{{ $ln_pg->total_bill_label ?? 'সর্বমোট বিল' }}:</span> <span>৳<span id="grand_total_disp">{{ $defaultPrice }}</span></span></div>
                </div>

                <button type="submit" id="submit_btn" class="btn-order-confirm">
                    {{ $ln_pg->btn_text_form ?? 'অর্ডার কনফার্ম করুন' }} <i class="fas fa-arrow-right ms-1"></i>
                </button>

                @if($ln_pg->security_badge_text)
                <div class="security-note">{{ $ln_pg->security_badge_text }}</div>
                @endif
            </form>
        </div>
    </div>
</section>

{{-- ৯. FAQ --}}
@if($ln_pg->faq_1_q)
<section class="sec">
    <div class="container">
        <h2 class="sec-title">{{ $ln_pg->faq_title ?? 'সচরাচর জিজ্ঞাসিত প্রশ্ন' }}</h2>
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

{{-- ১০. STOCK URGENCY --}}
@if($ln_pg->urgency_title || $ln_pg->stock_count)
<section class="sec">
    <div class="container">
        <div class="urgency-block mx-auto" style="max-width:680px;">
            <h3 style="font-weight:800; color:#92400e;"><i class="fas fa-fire me-2"></i>{{ $ln_pg->urgency_title }}</h3>
            @if($ln_pg->stock_count)
            <div style="font-size:1.15rem; font-weight:700; color:#dc2626; margin-top:8px;">
                <i class="fas fa-exclamation-triangle me-1"></i> {{ $stockText }}
            </div>
            <div class="stock-bar-wrap">
                <div class="stock-bar-fill" style="width: {{ min(100, ($stockCount / 100) * 100) }}%;"></div>
            </div>
            @endif
            @if($ln_pg->urgency_subtitle)
            <p style="color:#78350f; margin-top:10px;">{{ $ln_pg->urgency_subtitle }}</p>
            @endif
        </div>
    </div>
</section>
@endif

{{-- ১১. FINAL CTA --}}
@if($ln_pg->final_cta_title)
<div class="container">
    <div class="final-cta">
        <h2>{{ $ln_pg->final_cta_title }}</h2>
        @if($ln_pg->final_cta_subtitle)<p>{{ $ln_pg->final_cta_subtitle }}</p>@endif
        <a href="#order-form" class="btn-primary-cta">{{ $ln_pg->final_cta_btn_text ?? 'অর্ডার করুন এখনই' }} <i class="fas fa-arrow-right"></i></a>
    </div>
</div>
@endif

{{-- ১২. FOOTER --}}
<footer>
    <div class="container">
        @if($ln_pg->footer_company)<div class="fcompany">{{ $ln_pg->footer_company }}</div>@endif
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
    // Countdown
    (function() {
        var hours = {{ $cdHours }};
        if(hours <= 0) return;
        var key = 'lp9_cd_end_{{ $ln_pg->id }}';
        var end = parseInt(localStorage.getItem(key) || '0');
        if(!end || end < Date.now()) {
            end = Date.now() + hours * 3600 * 1000;
            localStorage.setItem(key, end);
        }
        function pad(n){ return n < 10 ? '0'+n : ''+n; }
        function tick() {
            var diff = end - Date.now();
            if(diff <= 0) { localStorage.removeItem(key); end = Date.now() + hours*3600*1000; localStorage.setItem(key, end); diff = end - Date.now(); }
            var h = Math.floor(diff/3600000);
            var m = Math.floor((diff%3600000)/60000);
            var s = Math.floor((diff%60000)/1000);
            var $h = document.getElementById('cd-h'); var $m = document.getElementById('cd-m'); var $s = document.getElementById('cd-s');
            if($h) $h.innerText = pad(h);
            if($m) $m.innerText = pad(m);
            if($s) $s.innerText = pad(s);
        }
        setInterval(tick, 1000); tick();
    })();

    var hasPackages = {{ ($ln_pg->packages && $ln_pg->packages->count() > 0) ? 'true' : 'false' }};

    function resetCoupon() {
        $('#hidden_coupon_code').val('');
        $('#hidden_discount').val(0);
        $('#coupon_msg').text('');
        $('#coupon_code').val('');
    }

    function selectDefaultPackage() {
        var $def = $('input[name="selected_package_id"][value=""]');
        $('input[name="selected_package_id"]').prop('checked', false);
        $def.prop('checked', true);
        $('.package-card').removeClass('active-pkg');
        $def.closest('.package-card').addClass('active-pkg');
    }

    $(document).on('click', '.increase-qty', function() {
        if(hasPackages) { 
            toastr.warning('অনুগ্রহ করে প্যাকেজ সিলেক্ট করুন, পরিমাণ ম্যানুয়ালি পরিবর্তন করা যাবে গঠন।'); 
            return; 
        }
        var $i = $(this).siblings('.inner_qty'); 
        $i.val(parseInt($i.val()) + 1); 
        selectDefaultPackage(); 
        resetCoupon(); 
        recalc();
    });
    
    $(document).on('click', '.decrease-qty', function() {
        if(hasPackages) { 
            toastr.warning('অনুগ্রহ করে প্যাকেজ সিলেক্ট করুন, পরিমাণ ম্যানুয়ালি পরিবর্তন করা যাবে না।'); 
            return; 
        }
        var $i = $(this).siblings('.inner_qty'); 
        var v = parseInt($i.val()); 
        if(v > 1) { 
            $i.val(v - 1); 
            selectDefaultPackage(); 
            resetCoupon(); 
            recalc();
        }
    });

    $(document).on('change', 'input[name="selected_package_id"]', function() {
        $('.package-card').removeClass('active-pkg');
        $(this).closest('.package-card').addClass('active-pkg');
        resetCoupon();
        recalc();
    });

    function recalc() {
        var pkg = $('input[name="selected_package_id"]:checked');
        var totalProductPrice = 0;
        var totalItems = 1;

        if (hasPackages) {
            totalProductPrice = parseFloat(pkg.data('price')) || 0;
            totalItems = parseInt(pkg.data('qty')) || 1;
            
            $('.inner_qty').val(totalItems); 
            $('#form_qty').val(totalItems);
        } else {
            var manualQty = parseInt($('.inner_qty').val()) || 1; 
            var v = $('#variation_select');
            var basePrice = parseFloat(v.find(':selected').data('price') || v.data('price') || {{ (float)$defaultPrice }});
            
            totalProductPrice = basePrice * manualQty;
            totalItems = manualQty;
            $('#form_qty').val(totalItems);
        }

        $('#subtotal_input').val(totalProductPrice); 
        
        var deliv = parseFloat($('#delivery_charge').find(':selected').data('charge') || 0);
        var discount = parseFloat($('#hidden_discount').val()) || 0;
        
        var grand = (totalProductPrice + deliv) - discount;
        if(grand < 0) grand = 0; 

        $('#final_total_input').val(grand);
        
        $('#subtotal_disp').text(totalProductPrice);
        $('#delivery_disp').text(deliv);
        $('#grand_total_disp').text(grand);

        var v2 = $('#variation_select');
        var stock = parseInt(v2.length ? (v2.find(':selected').data('stock') || v2.data('stock')) : 0) || {{ (int)$defaultStock }};
        $('#max_stock').val(stock);
    }
    
    $(document).on('change', '#variation_select, #delivery_charge', recalc);
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
        $('.payment-radio-box').css('border-color', '#e5e7eb').css('background', '#fff');
        $('input[name="payment_method"]:checked').closest('.payment-radio-box').css('border-color', 'var(--brand)').css('background', 'rgba(37,99,235,0.05)');
        if(method === 'manual') { $('#manualPaymentBox').show(); $('#manual_number').text(mNumber || ''); $('#manual_type').text(mType || ''); }
        else { $('#manualPaymentBox').hide(); }
    }
    window.togglePaymentAction = togglePaymentAction;

    $('#checkout_land_form').submit(function(e) {
        e.preventDefault();
        var maxStock = parseInt($('#max_stock').val()) || 0;
        if(maxStock <= 0) { toastr.error('দুঃখিত, প্রোডাক্টটি স্টকে নেই!'); return; }

        var paymentMethod = $('input[name="payment_method"]:checked').val() || 'cod';
        if(paymentMethod !== 'cod' && paymentMethod !== 'sslcommerz' && paymentMethod !== 'bkash' && paymentMethod !== 'nagad' && paymentMethod !== 'uddoktapay' && paymentMethod !== 'eps') {
            if(!$('#sender_number').val() || !$('#transaction_id').val()) { toastr.warning('পেমেন্ট নাম্বার + Transaction ID দিন'); return; }
        }

        if(paymentMethod === 'sslcommerz' || paymentMethod === 'online') {
            this.action = "{{ url('/pay') }}"; this.submit(); return;
        }

        var purchaseId = "PUR_{{ $productId }}_" + Date.now();
        $('#purchase_event_id').val(purchaseId);

        var $btn = $('#submit_btn');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> {{ $ln_pg->processing_text ?? "প্রসেসিং..." }}');

        $.ajax({
            url: "{{ route('front.storelandData') }}", method: "POST", data: $(this).serialize(),
            success: function(res) {
                if(res.success) {
                    toastr.success(res.msg);
                    // Purchase (Meta + GA4) fires on the thank-you page with 'PUR_' + order_id — the id the server Conversions API uses.
                    if(paymentMethod === 'nagad') { var oid = res.order_id || res.url.split('/').pop(); window.location.href = "{{ url('nagad/pay') }}/" + oid; return; }
                    if(paymentMethod === 'uddoktapay') { var oid = res.order_id || res.url.split('/').pop(); window.location.href = "{{ url('uddoktapay/pay') }}/" + oid; return; }
                    if(paymentMethod === 'eps') { window.location.href = res.url; return; }
                    setTimeout(function() { window.location.href = res.url; }, 700);
                } else {
                    toastr.error(res.msg || 'কিছু একটা সমস্যা হয়েছে');
                    $btn.prop('disabled', false).html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-1"></i>');
                }
            },
            error: function() {
                toastr.error('{{ $ln_pg->error_msg ?? "সার্ভারে সমস্যা হচ্ছে" }}');
                $btn.prop('disabled', false).html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-1"></i>');
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