<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $ln_pg->title1 ?? 'Premium Product Landing Page' }}</title>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">

    @php
        $information = \App\Models\Information::first();
        $primaryColor    = $ln_pg->theme_primary_col ?? '#d35400'; 
        $darkBg          = '#2c1e16'; 
        $lightBg         = '#fdfbf7'; 
        
        $productId = $product->id ?? 0;
        $productName = $product->name ?? '';
        $contentCategory = $product?->category?->name ?? 'Landing Page';
        $sslActive = $information->ssl_active ?? 0;
        $pixelId   = setting('fb_pixel_id') ?? null;
        $ttPixelId = setting('tt_pixel_id') ?? null;
        $phoneNumber = $ln_pg->phone ?? '';

        $globalSetting = DB::table('delivery_charges')->first();
        $isWeightBased = $globalSetting && $globalSetting->charge_type == 'weight_based';
        
        // ১. ভেরিয়েবল আপডেট করা হলো
        $isFreeShipping = (!empty($product->is_free_shipping) && $product->is_free_shipping == 1) ? 1 : 0;

        $variations = collect();
        if($product){
            try{
                $product->loadMissing(['variations.size','variations.color', 'variations.stocks']);
                $variations = $product->variations ?? collect();
            }catch(\Throwable $e){
                $variations = $product->variations ?? collect();
            }
        }
        $defaultVar = $variations->first();
        $defaultVarId = $defaultVar->id ?? null;

        $defaultPrice = 0; $regularStrikePrice = 0; 
        if (isset($product->after_discount) && $product->after_discount > 0) {
            $defaultPrice = intval($product->after_discount); 
            $regularStrikePrice = intval($product->sell_price); 
        } elseif (isset($product->sell_price) && $product->sell_price > 0) {
            $defaultPrice = intval($product->sell_price); 
            $regularStrikePrice = ($product->price > $product->sell_price) ? intval($product->price) : 0;
        } else { 
            $defaultPrice = intval($product->price ?? 0); 
        }

        $defaultStock = $defaultVar ? (int) $defaultVar->stocks->sum('quantity') : ($product ? max((int) ($product->stock_quantity ?? 0), (int) $product->stocks()->sum('quantity')) : 0);

        // Tracking-only price. Mirrors ProductController::landingDisplayPrice() so the
        // browser value matches the value the Conversions API already sent.
        $lpTrackPrice = 0.0;
        $lpPackages   = $ln_pg->packages ?? null;
        if ($lpPackages && $lpPackages->isNotEmpty()) {
            $lpDefaultPk = $lpPackages->firstWhere('is_default', 1) ?: $lpPackages->first();
            if ($lpDefaultPk && (float) $lpDefaultPk->price > 0) $lpTrackPrice = (float) $lpDefaultPk->price;
        }
        if ($lpTrackPrice <= 0 && !empty($ln_pg->new_price) && (float) $ln_pg->new_price > 0) {
            $lpTrackPrice = (float) $ln_pg->new_price;
        }
        if ($lpTrackPrice <= 0 && $product) {
            $lpTrackPrice = (float) ((!empty($product->after_discount) && $product->after_discount > 0)
                ? $product->after_discount
                : ($product->sell_price ?? 0));
        }
        if ($lpTrackPrice <= 0) $lpTrackPrice = (float) ($defaultPrice ?: 0);

        // The controller already fired Meta CAPI + TikTok Events API with exactly these ids
        // ($lpEventBase . '_VC' / '_IC'); echo the same ones so the events deduplicate.
        $lpEventIdVC = $lpEventIdVC ?? (!empty($lpEventBase) ? $lpEventBase . '_VC' : null);
        $lpEventIdIC = $lpEventIdIC ?? (!empty($lpEventBase) ? $lpEventBase . '_IC' : null);

        $sizes = collect(); 
        $colors = collect(); 
        $varMatrix = [];
        if($variations->count() > 0) {
            foreach($variations as $v) {
                if($v->size) $sizes->push($v->size); 
                if($v->color) $colors->push($v->color);
                
                $vPrice = (isset($v->after_discount_price) && $v->after_discount_price > 0) ? intval($v->after_discount_price) : intval($v->price ?? $defaultPrice);
                
                $sId = $v->size_id ?? 0;
                $cId = $v->color_id ?? 0;
                
                $varMatrix["{$sId}_{$cId}"] = [
                    'variation_id' => $v->id, 
                    'price' => $vPrice, 
                    'stock' => 999999
                ];
            }
            $sizes = $sizes->unique('id')->values(); 
            $colors = $colors->unique('id')->values();
        }
        
        $rawSliderImages = collect($ln_pg->images ? $ln_pg->images->all() : []);
        $sliderImages = $rawSliderImages;
        if($rawSliderImages->count() > 0 && $rawSliderImages->count() < 4) {
            $sliderImages = $rawSliderImages->merge($rawSliderImages)->merge($rawSliderImages);
        }

        $reviewImages = collect($ln_pg->review_images ? $ln_pg->review_images->all() : []);
        $bgImage = $ln_pg->landing_bg ? asset('landing_pages/'.$ln_pg->landing_bg) : asset('frontend/images/default-bg.jpg');
    @endphp

    <style>
        :root { --brand-color: {{ $primaryColor }}; --dark-bg: {{ $darkBg }}; --light-bg: {{ $lightBg }}; }
        html { height: 100%; }
        body { 
            font-family: 'Hind Siliguri', sans-serif; 
            background: var(--light-bg); 
            color: #2c3e50; 
            overflow-x: hidden; 
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .flex-grow-1 { flex: 1; width: 100%; }
        footer { margin-top: auto; margin-bottom: 0 !important; }

        .hero-fullscreen { position: relative; width: 100%; min-height: 100vh; display: flex; align-items: center; justify-content: center; flex-direction: column; padding: 80px 15px; overflow: hidden; background-color: var(--dark-bg); }
        .hero-bg-blur { position: absolute; top: -5%; left: -5%; width: 110%; height: 110%; background-image: url('{{ $bgImage }}'); background-size: cover; background-position: center; filter: blur(12px); z-index: 1; }
        .hero-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); z-index: 2; }
        .hero-content-wrapper { position: relative; z-index: 3; width: 100%; max-width: 800px; text-align: center; }

        .hero-title { font-size: clamp(28px, 6vw, 48px); font-weight: 800; color: #f39c12; margin-bottom: 10px; line-height: 1.4; text-shadow: 2px 2px 10px rgba(0,0,0,0.8); }
        .hero-subtitle { font-size: clamp(16px, 4vw, 22px); font-weight: 500; color: #fff; margin-bottom: 40px; line-height: 1.5; text-shadow: 1px 1px 5px rgba(0,0,0,0.8); }

        .floating-price-card { background: rgba(255, 255, 255, 0.95); border-radius: 20px; padding: 40px 25px; text-align: center; border: 2px solid #f39c12; max-width: 500px; margin: 0 auto; animation: cardGlow 2s infinite alternate; }
        @keyframes cardGlow { 0% { box-shadow: 0 0 15px rgba(243, 156, 18, 0.4); transform: scale(1); } 100% { box-shadow: 0 0 35px rgba(243, 156, 18, 0.9); transform: scale(1.02); } }

        .countdown-container { background: {{ $ln_pg->countdown_bg_color ?? '#fff3e0' }}; border: 2px dashed #e67e22; border-radius: 12px; padding: 15px; margin-bottom: 20px; width: 100%; }
        .countdown-title { font-size: 16px; font-weight: 800; color: #d35400; margin-bottom: 10px; display: block; animation: pulse 1.5s infinite;}
        .timer-box { display: flex; gap: 8px; justify-content: center; }
        .time-block { background: linear-gradient(to bottom, #e67e22, #d35400); color: {{ $ln_pg->countdown_text_color ?? '#fff' }}; padding: 10px 15px; border-radius: 8px; font-weight: 800; font-size: 26px; min-width: 65px; box-shadow: 0 3px 10px rgba(211, 84, 0, 0.4);}
        .time-label { font-size: 11px; font-weight: 600; margin-top: 5px; display: block;}
        
        .regular-price-crossed { text-decoration: line-through; color: #95a5a6; font-size: 18px; font-weight: 700; display: block; margin-bottom: 5px;}
        .offer-price-highlight { font-size: clamp(32px, 8vw, 45px); font-weight: 900; color: #d35400; line-height: 1; margin-bottom: 15px; display: block; }
        
        .btn-glowing { background: {{ $ln_pg->hero_btn_bg_color ?? 'linear-gradient(135deg, #f39c12, #d35400)' }}; color: {{ $ln_pg->hero_btn_text_color ?? '#fff' }} !important; font-weight: 800; font-size: clamp(18px, 5vw, 22px); padding: 15px 25px; border-radius: 50px; border: none; display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; text-decoration: none; position: relative; z-index: 1; cursor: pointer; animation: btnGlow 1.5s infinite alternate; transition: all 0.3s ease; }
        @keyframes btnGlow { 0% { box-shadow: 0 0 5px #f39c12; } 100% { box-shadow: 0 0 25px #f39c12; } }
        @keyframes pulse { 0% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.05); opacity: 0.8; } 100% { transform: scale(1); opacity: 1; }}
        .btn-glowing:hover { transform: translateY(-3px); }

        .description-card { background: #fff; max-width: 900px; margin: -50px auto 60px auto; position: relative; z-index: 10; border-radius: 20px; padding: 40px 30px; box-shadow: 0 15px 40px rgba(0,0,0,0.08); border-top: 5px solid #d35400; font-size: 17px; line-height: 1.8; color: #444; text-align: center; }
        .description-card img { max-width: 100%; border-radius: 12px; margin: 15px 0;}

        .section-title-wrapper { text-align: center; margin: 20px 0 40px 0; }
        .section-badge { display: inline-block; background: #fdebd0; color: #d35400; font-size: 14px; font-weight: 700; padding: 6px 20px; border-radius: 50px; margin-bottom: 15px; border: 1px solid #fad7a1;}
        .section-title { font-size: clamp(24px, 6vw, 32px); font-weight: 800; color: var(--dark-bg); margin: 0; line-height: 1.3;}
        .feature-container { max-width: 1100px; margin: 0 auto 60px auto; padding: 0 15px;}

        .promise-slider-wrapper { position: relative; border-radius: 20px; padding: 10px; background: #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.05);}
        .single-product-img { width: 100%; border-radius: 15px; border: 2px solid #eee;}
        .badge-khati { position: absolute; bottom: -10px; right: 20px; background: #f39c12; color: #fff; padding: 8px 20px; border-radius: 50px; font-weight: 800; font-size: 16px; box-shadow: 0 5px 15px rgba(243, 156, 18, 0.4); z-index: 10;}
        
        .promise-item { background: #fff; border: 1px solid #eee; border-radius: 12px; padding: 15px 20px; display: flex; align-items: center; gap: 15px; margin-bottom: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); transition: 0.3s;}
        .promise-item:hover { border-color: #f39c12; transform: translateX(5px); }
        .promise-icon { background: #fdebd0; color: #d35400; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
        .promise-text h6 { font-weight: 800; color: #2c3e50; margin-bottom: 2px; font-size: 16px;}
        .promise-text p { font-size: 13px; color: #7f8c8d; margin: 0; line-height: 1.4;}

        .negative-box { background: #fffaf6; border: 1px dashed #e67e22; border-radius: 12px; padding: 20px; margin-top: 25px; }
        .negative-box h6 { font-weight: 800; color: #2c3e50; font-size: 15px; margin-bottom: 15px; }
        .negative-pill { background: #fff; border: 1px solid #eee; padding: 6px 15px; border-radius: 50px; font-size: 13px; font-weight: 700; color: #555; box-shadow: 0 2px 5px rgba(0,0,0,0.02); display: inline-flex; align-items: center; gap: 5px;}
        .negative-pill i { color: #e74c3c; font-size: 14px;}

        .identify-row { display: flex; flex-wrap: wrap; align-items: stretch; }
        .identify-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; height: 100%; align-content: space-between;}
        .identify-box { background: #fff; border: 1px solid #eee; border-radius: 15px; padding: 25px 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.03); transition: 0.3s; display: flex; flex-direction: column; align-items: center; text-align: center; justify-content: center; height: 100%;}
        .identify-box:hover { border-color: #f39c12; transform: translateY(-3px); box-shadow: 0 8px 20px rgba(243, 156, 18, 0.1); }
        .identify-icon { width: 45px; height: 45px; background: #fdf2e9; color: #d35400; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 15px; }
        .identify-box h6 { font-weight: 800; color: #2c3e50; font-size: 16px; margin-bottom: 5px; }
        .identify-box p { font-size: 13px; color: #7f8c8d; margin: 0; line-height: 1.4; }

        .slider-wrapper-h100 { height: 100%; display: flex; align-items: stretch; justify-content: center; background: #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-radius: 15px; overflow: hidden; padding: 0;}
        .slider-wrapper-h100 .owl-carousel { height: 100%; display: flex; flex-direction: column;}
        .slider-wrapper-h100 .owl-stage-outer, .slider-wrapper-h100 .owl-stage, .slider-wrapper-h100 .owl-item { height: 100%; }
        .slider-wrapper-h100 .item { height: 100%; width: 100%; }
        .product-slider-img { width: 100%; height: 100%; object-fit: cover; border-radius: 15px; display: block;} 
        
        .owl-theme .owl-dots .owl-dot span { width: 10px; height: 10px; margin: 5px; background: #d6d6d6; }
        .owl-theme .owl-dots .owl-dot.active span { background: #e67e22; width: 25px; transition: 0.3s; }

        .dark-trust-section { background: var(--dark-bg); padding: 70px 15px; color: #fff; text-align: center; margin-bottom: 60px; border-radius: 20px; margin-left: 15px; margin-right: 15px;}
        .trust-badge-box { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 15px; padding: 25px 15px; transition: 0.3s; height: 100%;}
        .trust-badge-box:hover { background: rgba(255,255,255,0.15); transform: translateY(-5px); border-color: #e67e22;}
        .trust-badge-box i { font-size: 40px; color: #f39c12; margin-bottom: 15px; }
        .trust-badge-box h6 { font-weight: 700; margin: 0; font-size: 16px; }

        .stats-row { display: flex; justify-content: center; gap: 40px; flex-wrap: wrap; margin-bottom: 40px; }
        .stat-box { text-align: center; }
        .stat-box h3 { font-size: 38px; font-weight: 900; color: #d35400; margin-bottom: 5px; }
        .stat-box p { font-size: 15px; color: #475569; font-weight: 600; margin: 0; }

        #textReviewCarousel .owl-stage { display: flex; align-items: stretch; }
        #textReviewCarousel .owl-item { display: flex; }
        .item-flex { width: 100%; display: flex; padding: 10px; }
        
        .testimonial-card { width: 100%; background: #fff; border: 1px solid #e2e8f0; border-radius: 15px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); transition: 0.3s; display: flex; flex-direction: column; justify-content: space-between; text-align: left; }
        .testimonial-card:hover { transform: translateY(-5px); border-color: #fdebd0; box-shadow: 0 10px 25px rgba(0,0,0,0.08);}
        .quote-icon { font-size: 35px; color: #fef0d7; margin-bottom: 10px; line-height: 1; }
        .stars { color: #0f172a; font-size: 13px; margin-bottom: 15px; }
        .review-text { font-size: 15px; color: #0f172a; font-weight: 600; line-height: 1.6; margin-bottom: 25px; flex-grow: 1; }
        .reviewer-info { display: flex; align-items: center; gap: 15px; margin-top: auto;}
        .avatar-initial { width: 45px; height: 45px; background: #fdf2e9; color: #d35400; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 800; }
        .reviewer-details h6 { margin: 0; font-size: 15px; font-weight: 800; color: #0f172a;}
        .reviewer-details span { font-size: 13px; color: #64748b; font-weight: 500;}

        .faq-item { background: #fff; border-radius: 10px; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #eee;}
        .faq-header { padding: 18px 20px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-weight: 700; color: #2c3e50; font-size: 16px; transition: 0.3s;}
        .faq-header:hover { background: #fff3e0; color: #d35400; }
        .faq-header i { color: #e67e22; transition: 0.3s;}
        .faq-header.active i { transform: rotate(180deg); }
        .faq-body { padding: 0 20px 20px 20px; color: #555; display: none; line-height: 1.6; font-size: 15px;}

        .order-section-wrapper { max-width: 1000px; margin: 0 auto; padding: 0 15px; scroll-margin-top: 30px;}
        .order-form-container { background: #fdfbf7; border-radius: 20px; box-shadow: 0 15px 40px rgba(0,0,0,0.1); overflow: hidden; border: 3px solid #d35400;}
        .order-header { background: linear-gradient(to right, #d35400, #e67e22); color: #fff; padding: 18px 10px; text-align: center; font-size: clamp(18px, 5vw, 24px); font-weight: 800; line-height: 1.4; word-break: break-word; }
        .order-body { padding: 30px 20px; }
        
        .form-control, .form-select { border-radius: 8px; padding: 12px 15px; border: 1px solid #cbd5e1; background: #fff; font-size: 15px; font-weight: 500; transition: 0.3s;}
        .form-control:focus, .form-select:focus { border-color: #d35400; box-shadow: 0 0 0 3px rgba(211, 84, 0, 0.1); background: #fff; }
        .form-label { font-weight: 700; color: #2c3e50; font-size: 14px; margin-bottom: 8px;}
        
        .summary-box { background: #fff; border-radius: 12px; padding: 25px; border: 1px solid #cbd5e1; height: 100%; box-shadow: 0 5px 15px rgba(0,0,0,0.03);}
        .summary-img { width: 65px; height: 65px; border-radius: 8px; object-fit: cover; border: 1px solid #fad7a1; }
        .summary-title-text { font-size: 16px; font-weight: 700; color: #2c3e50; line-height: 1.4;}
        
        .qty-box { display: inline-flex; align-items: center; background: #fff; border-radius: 6px; border: 1px solid #cbd5e1; overflow: hidden; margin-top: 5px;}
        .qty-btn { background: #f8fafc; border: none; padding: 5px 15px; font-weight: bold; color: #d35400; font-size: 18px; cursor: pointer;}
        .qty-input { width: 40px; border: none; text-align: center; font-weight: 800; pointer-events: none; font-size: 15px; color: #2c3e50;}

        .calc-line { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px dashed #cbd5e1; font-weight: 600; color: #475569; font-size: 14px;}
        .calc-line.total { border-bottom: none; border-top: 2px solid #cbd5e1; font-size: 20px; font-weight: 800; color: #d35400; padding-top: 15px; margin-top: 5px;}

        .var-btn { border: 1px solid #cbd5e1; padding: 6px 12px; border-radius: 6px; cursor: pointer; background: #fff; font-weight: 600; font-size: 13px; transition: 0.2s; color: #475569;}
        .var-btn.active { background: #d35400; color: #fff; border-color: #d35400; }

        .info-box-left { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.02);}

        .payment-radio { display: none; } 
        .payment-card { border: 1px solid #cbd5e1; border-radius: 8px; padding: 12px; display: flex; align-items: center; cursor: pointer; margin-bottom: 10px; background: #fff; transition: 0.2s; }
        .payment-card i { font-size: 20px; margin-right: 10px; color: #94a3b8; }
        .payment-card b { font-size: 14px; color: #2c3e50; display: block; }
        .payment-radio:checked + .payment-card { border-color: #d35400; background: #fffaf6; }
        .payment-radio:checked + .payment-card i { color: #d35400; }

        #otpModal { z-index: 99999 !important; }
        .otp-modal-content { border: none !important; border-radius: 20px !important; background: #ffffff; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2); text-align: center; overflow: hidden; position: relative; }
        .otp-modal-content::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #E2136E, #F6921E); }
        .otp-icon-box { width: 80px; height: 80px; background: #fdf2f7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 10px auto 20px; color: #E2136E; }
        .otp-input { width: 100%; letter-spacing: 15px; text-align: center; font-size: 28px; font-weight: bold; color: #333; border: 2px solid #eee !important; border-radius: 12px !important; background: #fafafa; height: 65px; transition: all 0.3s ease; position: relative; z-index: 99999 !important; }
        .otp-input:focus { border-color: #E2136E !important; background: #fff; box-shadow: 0 5px 15px rgba(226, 19, 110, 0.1) !important; outline: none; }
        .btn-verify { background: linear-gradient(135deg, #E2136E 0%, #C90D5E 100%); border: none; padding: 12px; font-size: 18px; border-radius: 12px; box-shadow: 0 8px 20px rgba(226, 19, 110, 0.3); width: 100%; color: white; font-family: 'Hind Siliguri', sans-serif; }
        .btn-verify:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(226, 19, 110, 0.4); }

        @media (max-width: 991px) {
            .identify-grid { grid-template-columns: 1fr; } 
            .identify-row { flex-direction: column; }
            .slider-wrapper-h100 { height: 350px; margin-top: 20px;} 
        }

        @media (max-width: 768px) {
            .hero-fullscreen { min-height: 100vh; padding: 40px 15px 0 15px; }
            .description-card { margin-top: -30px; padding: 25px 15px; font-size: 15px;}
            .floating-price-card { padding: 25px 15px; }
            .offer-price-highlight { font-size: 35px; }
            .time-block { padding: 5px 8px; font-size: 22px; min-width: 55px; }
            
            .stats-row { gap: 20px; flex-direction: row; align-items: center; }
            .stat-box h3 { font-size: 28px; }
            
            .order-body { padding: 20px 15px; }
            .dark-trust-section { padding: 40px 15px; margin-left: 0; margin-right: 0; border-radius: 0;}
            
            body { padding-bottom: 10px !important; } 
        }
    </style>
    
    {{-- ✅ GTM + Tracking Code --}}
    @if(!empty(optional($information)->tracking_code))
        {!! $information->tracking_code !!}
    @endif

    {{-- TikTok base pixel --}}
    @if(!empty($ttPixelId))
    <script>
    !function (w, d, t) { w.TiktokAnalyticsObject = t; var ttq = w[t] = w[t] || []; ttq.methods = ["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"]; ttq.setAndDefer = function (t, e) { t[e] = function () { t.push([e].concat(Array.prototype.slice.call(arguments, 0))); }; }; for (var i = 0; i < ttq.methods.length; i++) ttq.setAndDefer(ttq, ttq.methods[i]); ttq.instance = function (t) { for (var e = ttq._i[t] || [], n = 0; n < ttq.methods.length; n++) ttq.setAndDefer(e, ttq.methods[n]); return e; }; ttq.load = function (e, n) { var r = "https://analytics.tiktok.com/i18n/pixel/events.js"; var o = n && n.partner; ttq._i = ttq._i || {}; ttq._i[e] = []; ttq._i[e]._u = r; ttq._t = ttq._t || {}; ttq._t[e] = +new Date; ttq._o = ttq._o || {}; ttq._o[e] = n || {}; var s = document.createElement("script"); s.type = "text/javascript"; s.async = !0; s.src = r + "?sdkid=" + e + "&lib=" + t; var x = document.getElementsByTagName("script")[0]; x.parentNode.insertBefore(s, x); }; ttq.load('{{ $ttPixelId }}'); @if(!$__ttFromGtm) ttq.page(); @endif }(window, document, 'ttq');
    </script>
    @endif

    {{-- FB Pixel --}}
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
        <noscript>
        <img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1"/>
        </noscript>
    @endif

    {{-- Event ids + GA4 dataLayer + browser pixel events.
         NOT nested inside the FB @if — clearing the Facebook Pixel ID must never kill GTM/TikTok. --}}
    @if(!empty($lpEventIdVC))
    <script>
    (function(){
        if (window.__LP_FIRED__) { return; }
        window.__LP_FIRED__ = true;

        window.LP_EVENT_BASE  = {!! json_encode($lpEventBase ?? null) !!};
        window.LP_EVENT_ID_VC = {!! json_encode($lpEventIdVC) !!};
        window.LP_EVENT_ID_IC = {!! json_encode($lpEventIdIC) !!};

        // GA4 ecommerce — never gated: dataLayer is how GTM receives data.
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({ ecommerce: null });
        window.dataLayer.push({
            event: 'view_item',
            event_id: window.LP_EVENT_ID_VC,
            ecommerce: { currency: 'BDT', value: {{ $lpTrackPrice }}, items: [{ item_id: '{{ $productId }}', item_name: @json($productName), item_category: @json($contentCategory), price: {{ $lpTrackPrice }}, quantity: 1 }] }
        });
        window.dataLayer.push({ ecommerce: null });
        window.dataLayer.push({
            event: 'begin_checkout',
            event_id: window.LP_EVENT_ID_IC,
            ecommerce: { currency: 'BDT', value: {{ $lpTrackPrice }}, items: [{ item_id: '{{ $productId }}', item_name: @json($productName), item_category: @json($contentCategory), price: {{ $lpTrackPrice }}, quantity: 1 }] }
        });

        window.addEventListener('load', function(){
            if (@json(!$__hasGtm) && typeof fbq === 'function') {
                fbq('track', 'ViewContent', { content_ids: ['{{ $productId }}'], content_name: @json($productName), content_type: 'product', content_category: @json($contentCategory), value: {{ $lpTrackPrice }}, currency: 'BDT' }, { eventID: '{{ $lpEventIdVC }}' });
                fbq('track', 'InitiateCheckout', { content_ids: ['{{ $productId }}'], content_name: @json($productName), content_type: 'product', value: {{ $lpTrackPrice }}, currency: 'BDT', num_items: 1 }, { eventID: '{{ $lpEventIdIC }}' });
            }
            if (@json(!$__ttFromGtm) && typeof ttq !== 'undefined' && ttq.track) {
                ttq.track('ViewContent', { content_type: 'product', value: {{ $lpTrackPrice }}, currency: 'BDT', contents: [{ content_id: '{{ $productId }}', content_type: 'product', content_name: @json($productName), price: {{ $lpTrackPrice }}, quantity: 1 }] }, { event_id: '{{ $lpEventIdVC }}' });
                ttq.track('InitiateCheckout', { content_type: 'product', value: {{ $lpTrackPrice }}, currency: 'BDT', contents: [{ content_id: '{{ $productId }}', content_type: 'product', content_name: @json($productName), price: {{ $lpTrackPrice }}, quantity: 1 }] }, { event_id: '{{ $lpEventIdIC }}' });
            }
        });
    })();
    </script>
    @endif

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

<script>
    // Global (unconditional) — scroll hero button to the order form
    function scrollToForm(){
        var el = document.getElementById('orderForm');
        if(el){ el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    }
    window.scrollToForm = scrollToForm;
</script>

<main class="flex-grow-1">

<div class="hero-fullscreen">
    <div class="hero-bg-blur"></div>
    <div class="hero-overlay"></div>
    
    <div class="hero-content-wrapper">
        @if(!empty($ln_pg->title1)) <h1 class="hero-title">{{ $ln_pg->title1 }}</h1> @endif
        @if(!empty($ln_pg->left_side_title)) <p class="hero-subtitle">{{ $ln_pg->left_side_title }}</p> @endif
        
        <div class="floating-price-card">
            <div class="countdown-container">
                <span class="countdown-title"><i class="fas fa-bolt"></i> {{ $ln_pg->countdown_title ?? 'অফারটি শেষ হতে আর বাকি মাত্র' }}</span>
                <div class="timer-box" id="countdown">
                    <div class="time-block"><span id="hours">00</span><span class="time-label">ঘণ্টা</span></div>
                    <div class="time-block"><span id="minutes">00</span><span class="time-label">মিনিট</span></div>
                    <div class="time-block"><span id="seconds">00</span><span class="time-label">সেকেন্ড</span></div>
                </div>
            </div>

            @if($regularStrikePrice > 0) <span class="regular-price-crossed">{{ $ln_pg->old_price_text ?? 'পূর্বের মূল্যঃ' }} {{ $regularStrikePrice }} ৳</span> @endif
            <span class="offer-price-highlight">{{ $ln_pg->new_price_text ?? 'বর্তমান মূল্যঃ' }} {{ $defaultPrice }} ৳</span>
            
            <button onclick="scrollToForm()" class="btn-glowing mt-3">
                <i class="fas fa-shopping-cart"></i> {{ $ln_pg->btn_text_hero ?: 'অর্ডার করতে ক্লিক করুন' }}
            </button>
        </div>
    </div>
</div>

@if(!empty($ln_pg->left_side_desc))
<div class="container px-3">
    <div class="description-card">
        {!! $ln_pg->left_side_desc !!}
    </div>
</div>
@endif

@php
    $rawVideo = trim($ln_pg->video_url ?? '');
    $videoEmbed = '';
    if($rawVideo !== ''){
        if(stripos($rawVideo, '<iframe') !== false){
            // already a full embed code
            $videoEmbed = $rawVideo;
        } elseif(preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/|v/)|youtu\.be/)([A-Za-z0-9_-]{11})~i', $rawVideo, $m)){
            // any YouTube URL -> build embed
            $videoEmbed = '<iframe src="https://www.youtube.com/embed/'.$m[1].'" title="Product Video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
        } elseif(preg_match('~^[A-Za-z0-9_-]{11}$~', $rawVideo)){
            // bare 11-char video id
            $videoEmbed = '<iframe src="https://www.youtube.com/embed/'.$rawVideo.'" title="Product Video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
        }
    }
@endphp
@if($videoEmbed !== '')
<div class="container px-3">
    <div class="lp5-video-wrap">
        <div class="lp5-video-16x9">{!! $videoEmbed !!}</div>
    </div>
</div>
<style>
    .lp5-video-wrap{ max-width:820px; margin:22px auto; }
    .lp5-video-16x9{ position:relative; width:100%; padding-top:56.25%; overflow:hidden; border-radius:16px; background:#000; box-shadow:0 10px 30px rgba(0,0,0,.15); }
    .lp5-video-16x9 iframe, .lp5-video-16x9 video{ position:absolute; inset:0; width:100% !important; height:100% !important; border:0; }
</style>
@endif

@if(!empty($ln_pg->promise_title))
<div class="feature-container" style="margin-top: 20px;">
    <div class="section-title-wrapper">
        <div class="section-badge">{{ $ln_pg->promise_badge ?? 'কেন আমাদের প্রোডাক্ট সেরা?' }}</div>
        <h2 class="section-title">{{ $ln_pg->promise_title ?? 'আমাদের প্রতিশ্রুতি' }}</h2>
    </div>

    <div class="row g-4 align-items-center">
        <div class="col-lg-5">
            <div class="promise-slider-wrapper">
                @if($ln_pg->right_product_image)
                    <img src="{{ asset('landing_pages/'.$ln_pg->right_product_image) }}" class="single-product-img" alt="Product">
                @else
                    <img src="{{ getImage('products', $product->image ?? '') }}" class="single-product-img" alt="Product">
                @endif
                <div class="badge-khati"><i class="fas fa-medal"></i> {{ $ln_pg->promise_img_badge ?? '১০০% খাঁটি' }}</div>
            </div>
        </div>

        <div class="col-lg-7 pl-lg-4">
            @if(!empty($ln_pg->promise_1_title))
            <div class="promise-item">
                <div class="promise-icon"><i class="fas fa-check"></i></div>
                <div class="promise-text">
                    <h6>{{ $ln_pg->promise_1_title }}</h6>
                    <p>{{ $ln_pg->promise_1_desc }}</p>
                </div>
            </div>
            @endif

            @if(!empty($ln_pg->promise_2_title))
            <div class="promise-item">
                <div class="promise-icon"><i class="fas fa-check"></i></div>
                <div class="promise-text">
                    <h6>{{ $ln_pg->promise_2_title }}</h6>
                    <p>{{ $ln_pg->promise_2_desc }}</p>
                </div>
            </div>
            @endif

            @if(!empty($ln_pg->promise_3_title))
            <div class="promise-item">
                <div class="promise-icon"><i class="fas fa-check"></i></div>
                <div class="promise-text">
                    <h6>{{ $ln_pg->promise_3_title }}</h6>
                    <p>{{ $ln_pg->promise_3_desc }}</p>
                </div>
            </div>
            @endif

            @if(!empty($ln_pg->negative_tags))
            <div class="negative-box">
                <h6>{{ $ln_pg->negative_title ?: 'আমাদের পণ্যে যা নেই:' }}</h6>
                <div class="d-flex flex-wrap gap-2">
                    @php $tags = explode(',', $ln_pg->negative_tags); @endphp
                    @foreach($tags as $tag)
                        @if(trim($tag) != '')
                            <span class="negative-pill"><i class="fas fa-times-circle text-danger"></i> {{ trim($tag) }}</span>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

@if(!empty($ln_pg->identify_title))
<div class="feature-container" style="margin-top: 80px;">
    <div class="section-title-wrapper">
        <div class="section-badge">{{ $ln_pg->identify_badge ?? 'খাঁটি প্রোডাক্ট চেনার উপায়' }}</div>
        <h2 class="section-title">{{ $ln_pg->identify_title ?? 'আসল পণ্য চেনার উপায়' }}</h2>
        <p class="text-muted mt-2 fw-bold">{{ $ln_pg->identify_subtitle ?? 'এই লক্ষণগুলো দেখলেই বুঝবেন পণ্য খাঁটি কিনা' }}</p>
    </div>

    <div class="row g-4 identify-row">
        <div class="col-lg-6 order-2 order-lg-1">
            <div class="identify-grid">
                @for($i=1; $i<=8; $i++)
                    @if(!empty($ln_pg->{'id_'.$i.'_title'}))
                    @php 
                        $iconClass = $ln_pg->{'id_'.$i.'_icon'} ?: 'fa-check';
                        if(!preg_match('/^(fas|far|fab|fal|fa-solid|fa-regular|fa-brands)\s/', $iconClass)) {
                            $iconClass = 'fas ' . $iconClass;
                        }
                    @endphp
                    <div class="identify-box">
                        <div class="identify-icon"><i class="{{ $iconClass }} fa-fw"></i></div>
                        <h6>{{ $ln_pg->{'id_'.$i.'_title'} }}</h6>
                        <p>{{ $ln_pg->{'id_'.$i.'_desc'} }}</p>
                    </div>
                    @endif
                @endfor
            </div>
        </div>

        <div class="col-lg-6 order-1 order-lg-2">
            <div class="slider-wrapper-h100">
                @if($sliderImages->count() > 0)
                    <div class="owl-carousel owl-theme h-100-carousel" id="productGallery">
                        @foreach($sliderImages as $img)
                            <div class="item"><img src="{{ asset('landing_sliders/'.$img->image) }}" class="product-slider-img" alt="Product Image"></div>
                        @endforeach
                    </div>
                @else
                    <img src="{{ getImage('products', $product->image ?? '') }}" class="product-slider-img" alt="Product">
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<div class="dark-trust-section">
    <div class="container">
        <h2 class="section-title text-white mb-5">{{ $ln_pg->dhamaka_title ?: 'ঝুঁকিমুক্ত অর্ডার করুন' }}</h2>
        
        <div class="row g-4 justify-content-center mb-5">
            <div class="col-6 col-md-3">
                <div class="trust-badge-box">
                    <i class="fas fa-thumbs-up fa-fw"></i>
                    <h6>উন্নত মান</h6>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="trust-badge-box">
                    <i class="fas fa-hand-holding-usd fa-fw"></i>
                    <h6>ক্যাশ অন ডেলিভারি</h6>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="trust-badge-box">
                    <i class="fas fa-shipping-fast fa-fw"></i>
                    <h6>দ্রুত ডেলিভারি</h6>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="trust-badge-box">
                    <i class="fas fa-headset fa-fw"></i>
                    <h6>২৪/৭ সাপোর্ট</h6>
                </div>
            </div>
        </div>
    </div>
</div>

@if(!empty($ln_pg->review_title))
<div class="feature-container" style="margin-top: 80px;">
    <div class="section-title-wrapper">
        <div class="section-badge">{{ $ln_pg->review_badge ?? 'গ্রাহকদের মতামত' }}</div>
        <h2 class="section-title mb-2">{{ $ln_pg->review_title ?? 'গ্রাহকদের মতামত' }}</h2>
        <p class="text-muted fw-bold">{{ $ln_pg->review_subtitle ?? 'হাজারো সন্তুষ্ট পরিবার আমাদের সাথে আছে' }}</p>
    </div>

    <div class="stats-row">
        @for($i=1; $i<=3; $i++)
            @if(!empty($ln_pg->{'stat_'.$i.'_num'}))
            <div class="stat-box">
                <h3>{{ $ln_pg->{'stat_'.$i.'_num'} }}</h3>
                <p>{{ $ln_pg->{'stat_'.$i.'_text'} }}</p>
            </div>
            @endif
        @endfor
    </div>

    <div class="owl-carousel owl-theme mt-4" id="textReviewCarousel">
        @for($i=1; $i<=4; $i++)
            @if(!empty($ln_pg->{'rev_'.$i.'_text'}))
            <div class="item-flex">
                <div class="testimonial-card">
                    <div>
                        <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                        <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <p class="review-text">"{{ $ln_pg->{'rev_'.$i.'_text'} }}"</p>
                    </div>
                    <div class="reviewer-info">
                        <div class="avatar-initial">{{ mb_substr($ln_pg->{'rev_'.$i.'_name'} ?? 'C', 0, 1) }}</div>
                        <div class="reviewer-details">
                            <h6>{{ $ln_pg->{'rev_'.$i.'_name'} }}</h6>
                            <span>{{ $ln_pg->{'rev_'.$i.'_loc'} }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @endfor
    </div>
</div>
@endif

<div class="order-section-wrapper" id="orderForm">
    <div class="order-form-container">
        <div class="order-header"><i class="fas fa-clipboard-list me-2"></i> {{ $ln_pg->form_title ?: 'অর্ডার কনফার্ম করতে নিচের ফর্মটি পূরণ করুন' }}</div>
        <div class="order-body">
            <form id="checkout_land_form" action="{{ route('front.storelandData') }}" method="POST">
                @csrf
                        {{-- কোন landing page থেকে order এলো (Order Management-এর Source-এ দেখায়) --}}
                        <input type="hidden" name="landing_page_type" value="1">
                <input type="hidden" name="prd_id" value="{{ $productId }}">
                <input type="hidden" name="variation_id" id="variation_id" value="">
                <input type="hidden" name="amount" id="amount" value="">
                <input type="hidden" name="final_amount" id="final_amount" value="">
                <input type="hidden" name="purchase_event_id" id="purchase_event_id" value="">
                
                <script> window.varMatrix = @json($varMatrix); </script>
                <input type="hidden" id="max_stock" value="{{ $defaultStock }}">

                <div class="row g-4">
                    
                    <div class="col-lg-6 col-md-12">
                        <div class="info-box-left">
                            <h5 class="fw-bold mb-4" style="color: #d35400;"><i class="fas fa-user-edit me-2"></i> ১. বিলিং ও শিপিং তথ্য:</h5>
                            <div class="mb-3"><label class="form-label">আপনার নাম *</label><input type="text" name="first_name" class="form-control" placeholder="নাম লিখুন" required></div>
                            <div class="mb-3"><label class="form-label">মোবাইল নাম্বার *</label><input type="tel" name="mobile" id="customer_mobile" class="form-control" placeholder="017XXXXXXXX" required maxlength="11"></div>
                            <div class="mb-3"><label class="form-label">সম্পূর্ণ ঠিকানা *</label><textarea name="shipping_address" id="customer_address" class="form-control" rows="2" placeholder="বাসা নং, রোড নং, এলাকা, থানা, জেলা" required></textarea></div>
                            
                            <!-- ২. ডেলিভারি চার্জ ড্রপডাউন আপডেট করা হলো -->
                            <div class="mb-3"><label class="form-label">ডেলিভারি এলাকা নির্বাচন করুন *</label>
                                <select name="delivery_charge_id" id="delivery_charge" class="form-select" required>
                                    <option value="" disabled selected>এলাকা নির্বাচন করুন...</option>
                                    @foreach($charges as $charge) 
                                        <option value="{{ $charge->id }}" data-charge="{{ $isFreeShipping ? 0 : intval($charge->amount) }}">
                                            {{ $charge->title }} - {!! $isFreeShipping ? '<span class="text-success">ফ্রি ডেলিভারি (0 ৳)</span>' : intval($charge->amount) . ' ৳' !!}
                                        </option> 
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="info-box-left">
                            @php
                                $activeManuals = \App\Models\ManualPayment::where('status', 1)->get();
                            @endphp
                            <h5 class="fw-bold mb-3" style="color: #d35400;"><i class="fas fa-money-check-alt me-2"></i> {{ ($variations->count() > 0) ? '৩' : '২' }}. পেমেন্ট মাধ্যম:</h5>
                            
                            @if(isset($information->cod_active) && $information->cod_active == 1)
                            <div class="card bg-white border shadow-sm rounded-3 mb-2 payment-card-label">
                                <div class="card-body d-flex align-items-center gap-3 p-3">
                                    <input type="radio" value="Cash on Delivery" id="payment_cod" checked class="form-check-input" name="payment_method" style="width: 20px; height: 20px; cursor: pointer;" onchange="togglePaymentAction('cod')">
                                    <label for="payment_cod" class="fw-bold text-dark mb-0 w-100" style="font-family: 'Hind Siliguri', sans-serif; cursor: pointer;">
                                        ক্যাশ অন ডেলিভারি (Cash on Delivery)
                                    </label>
                                </div>
                            </div>
                            @endif

                            @if(isset($information->ssl_active) && $information->ssl_active == 1)
                            <div class="card bg-white border shadow-sm rounded-3 mb-2 payment-card-label">
                                <div class="card-body d-flex align-items-center gap-3 p-3">
                                    <input type="radio" value="online" id="payment_online" {{ (!isset($information->cod_active) || $information->cod_active == 0) ? 'checked' : '' }} class="form-check-input" name="payment_method" style="width: 20px; height: 20px; cursor: pointer;" onchange="togglePaymentAction('online')">
                                    <label for="payment_online" class="fw-bold text-dark mb-0 w-100" style="font-family: 'Hind Siliguri', sans-serif; cursor: pointer;">
                                        অনলাইন পেমেন্ট (bKash / Nagad / Card)
                                    </label>
                                </div>
                            </div>
                            @endif

                            @if(isset($information->bkash_active) && $information->bkash_active == 1)
                            <div class="card bg-white border shadow-sm rounded-3 mb-2 payment-card-label" style="border: 1px solid #E2136E !important;">
                                <div class="card-body d-flex align-items-center gap-3 p-3">
                                    <input type="radio" value="bkash" id="payment_bkash" class="form-check-input" name="payment_method" style="width: 20px; height: 20px; cursor: pointer;" onchange="togglePaymentAction('bkash')">
                                    <label for="payment_bkash" class="fw-bold text-dark mb-0 w-100" style="font-family: 'Hind Siliguri', sans-serif; cursor: pointer; display: flex; align-items: center; justify-content: space-between;">
                                        বিকাশ পেমেন্ট (bKash)
                                        <img src="{{ asset('frontend/images/bkash_logo.png') }}" alt="bKash" style="height: 20px; width: auto; object-fit: contain;">
                                    </label>
                                </div>
                            </div>
                            @endif

                            @if(isset($information->eps_active) && $information->eps_active == 1)
                            <div class="card bg-white border shadow-sm rounded-3 mb-2 payment-card-label" style="border: 1px solid #17a2b8 !important;">
                                <div class="card-body d-flex align-items-center gap-3 p-3">
                                    <input type="radio" value="eps" id="payment_eps" class="form-check-input" name="payment_method" style="width: 20px; height: 20px; cursor: pointer;" onchange="togglePaymentAction('eps')">
                                    <label for="payment_eps" class="fw-bold text-dark mb-0 w-100" style="font-family: 'Hind Siliguri', sans-serif; cursor: pointer;">
                                        EPS পেমেন্ট (Easy Payment System)
                                    </label>
                                </div>
                            </div>
                            @endif

                            @if(isset($information->nagad_active) && $information->nagad_active == 1)
                            <div class="card bg-white border shadow-sm rounded-3 mb-2 payment-card-label" style="border: 1px solid #ED1C24 !important;">
                                <div class="card-body d-flex align-items-center gap-3 p-3">
                                    <input type="radio" value="nagad" id="payment_nagad" class="form-check-input" name="payment_method" style="width: 20px; height: 20px; cursor: pointer;" onchange="togglePaymentAction('nagad')">
                                    <label for="payment_nagad" class="fw-bold text-dark mb-0 w-100" style="font-family: 'Hind Siliguri', sans-serif; cursor: pointer; display: flex; align-items: center; justify-content: space-between;">
                                        নগদ পেমেন্ট (Nagad)
                                        <img src="{{ asset('frontend/images/nagad.png') }}" alt="Nagad" style="height: 20px; width: auto; object-fit: contain;">
                                    </label>
                                </div>
                            </div>
                            @endif

                            @if(isset($information->uddoktapay_active) && $information->uddoktapay_active == 1)
                            <div class="card bg-white border shadow-sm rounded-3 mb-2 payment-card-label" style="border: 1px solid #28a745 !important;">
                                <div class="card-body d-flex align-items-center gap-3 p-3">
                                    <input type="radio" value="uddoktapay" id="payment_uddoktapay" class="form-check-input" name="payment_method" style="width: 20px; height: 20px; cursor: pointer;" onchange="togglePaymentAction('uddoktapay')">
                                    <label for="payment_uddoktapay" class="fw-bold text-dark mb-0 w-100" style="font-family: 'Hind Siliguri', sans-serif; cursor: pointer;">
                                        উদ্দোক্তাপে (UddoktaPay)
                                    </label>
                                </div>
                            </div>
                            @endif

                            @foreach($activeManuals as $mp)
                            <div class="card bg-white border shadow-sm rounded-3 mb-2 payment-card-label">
                                <div class="card-body d-flex align-items-center gap-3 p-3">
                                    <input type="radio" value="{{ $mp->name }}" id="payment_{{ $mp->id }}" class="form-check-input" name="payment_method" style="width: 20px; height: 20px; cursor: pointer;" onchange="togglePaymentAction('manual', '{{ $mp->name }}', '{{ $mp->number }}', '{{ $mp->type }}')">
                                    <label for="payment_{{ $mp->id }}" class="fw-bold text-dark mb-0 w-100" style="font-family: 'Hind Siliguri', sans-serif; cursor: pointer;">
                                        {{ $mp->name }} ({{ $mp->type }})
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div id="manual_payment_area" style="display: none;" class="mt-3 mb-4 p-4 bg-light border rounded-4">
                            <div class="d-flex align-items-center mb-3 p-3 rounded-3" style="background: rgba(226, 19, 110, 0.08); border: 1px solid rgba(226, 19, 110, 0.2); color: #C90D5E;">
                                <i class="fas fa-info-circle fa-2x me-3"></i>
                                <p id="payment_instruction" class="mb-0 fw-bold" style="font-family: 'Hind Siliguri', sans-serif; font-size: 15px;"></p>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-dark fw-bold hind" style="font-size: 13px;">যে নাম্বার থেকে টাকা পাঠিয়েছেন <span class="text-danger">*</span></label>
                                    <input type="text" name="sender_number" id="sender_number" class="form-control border-secondary" placeholder="017XXXXXXXX">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-dark fw-bold hind" style="font-size: 13px;">Transaction ID (TrxID) <span class="text-danger">*</span></label>
                                    <input type="text" name="transaction_id" id="transaction_id" class="form-control border-secondary" placeholder="TRX123456789">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 col-md-12">
                        <div class="summary-box sticky-top" style="top: 20px;">
                            <h5 class="fw-bold mb-4" style="color: #d35400;"><i class="fas fa-shopping-basket me-2"></i> অর্ডারের সারাংশ:</h5>
                            
                            <div class="d-flex align-items-center mb-4 bg-white p-3 border rounded-3 shadow-sm">
                                <img src="{{ getImage('products', $product->image ?? '') }}" class="summary-img me-3">
                                <div class="flex-grow-1" style="min-width: 0;">
                                    <div class="summary-title-text text-truncate">{{ $productName }}</div>
                                    <div class="text-danger fw-bold mt-2 fs-5"><span id="unit_price_display">{{ $defaultPrice }}</span> ৳</div>
                                    <input type="hidden" id="unit_price" value="{{ $defaultPrice }}">
                                    
                                    <div id="stock_status" class="stock-status mt-2 {{ $defaultStock > 0 ? 'in-stock' : 'out-stock' }}">
                                        {{ $defaultStock > 0 ? 'In Stock' : 'Out of Stock' }}
                                    </div>
                                </div>
                            </div>
                            
                            @if($variations->count() > 0)
                            <div class="info-box-left" style="box-shadow: none; border: 1px dashed #cbd5e1; padding: 15px;">
                                <h5 class="fw-bold mb-3" style="color: #d35400; font-size: 16px;"><i class="fas fa-palette me-2"></i> ২. প্রোডাক্ট অপশন:</h5>
                                <div class="mb-3">
                                    <label class="form-label mb-2">ভেরিয়েশন সিলেক্ট করুন (Size / Color): <span class="text-danger">*</span></label>
                                    <select id="variation_select" class="form-select form-control" style="font-weight: 700;">
                                        <option value="" disabled selected>সিলেক্ট করুন...</option>
                                        @foreach($variations as $v)
                                            @php
                                                $vBase  = $v->price ?? $product->sell_price ?? 0;
                                                $vDisc  = $v->after_discount_price ?? null;
                                                $vPrice = intval(((float)$vDisc > 0) ? $vDisc : $vBase);
                                                
                                                $sizeName  = $v->size->name ?? $v->size->title ?? '';
                                                $colorName = $v->color->name ?? '';
                                                $label = trim(($sizeName ?: '') . (($sizeName && $colorName) ? ' - ' : '') . ($colorName ?: ''));
                                            @endphp
                                            <option value="{{ $v->id }}" data-price="{{ $vPrice }}" data-stock="999999" {{ ($defaultVarId && $v->id == $defaultVarId) ? 'selected' : '' }}>
                                                {{ $label ?: ('Variation #'.$v->id) }} - {{ $vPrice }} ৳
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @endif
                            
                            @if(isset($information->coupon_visibility) && $information->coupon_visibility == 1)
                            <div class="coupon-box mt-3 mb-3 p-3" style="background: #fdfbf7; border: 1px dashed #d35400; border-radius: 8px;">
                                <label class="form-label mb-2" style="font-size: 14px; font-weight: 700; color: #d35400;"><i class="fas fa-ticket-alt me-1"></i> কুপন কোড (যদি থাকে)</label>
                                <div class="input-group">
                                    <input type="text" id="coupon_code" class="form-control" placeholder="কোড লিখুন" style="border-right: 0; box-shadow: none;">
                                    <button type="button" class="btn text-white px-3" id="coupon_btn_submit" onclick="applyCouponLand()" style="background: #d35400; font-weight: 700;">APPLY</button>
                                </div>
                                <small id="coupon_msg" class="d-block mt-2 fw-bold"></small>
                            </div>
                            @endif

                            @php $sessionDiscount = session('coupon_discount') ?? 0; @endphp
                            <input type="hidden" id="discount_amount" value="{{ $sessionDiscount }}">

                            <div class="calc-line mt-3"><span>পরিমাণ</span><div class="qty-box"><button type="button" class="qty-btn" id="qty_minus">-</button><input type="number" name="quantity" id="qty_input" class="qty-input" value="1" readonly><button type="button" class="qty-btn" id="qty_plus">+</button></div></div>
                            <div class="calc-line"><span>সাবটোটাল</span><span><span id="calc_subtotal">{{ $defaultPrice }}</span> ৳</span></div>
                            
                            <!-- ৩. সামারি এবং calc_shipping_text আপডেট করা হলো -->
                            <div class="calc-line"><span>ডেলিভারি চার্জ</span><span id="calc_shipping_text">+ <span id="calc_shipping">0</span> ৳</span></div>
                            
                            <div class="calc-line" id="discount_row" style="{{ $sessionDiscount > 0 ? '' : 'display:none;' }}">
                                <span style="color: #27ae60;">ডিসকাউন্ট</span>
                                <span style="color: #27ae60;">- <span id="calc_discount">{{ $sessionDiscount }}</span> ৳</span>
                            </div>

                            <div class="calc-line total"><span>সর্বমোট বিল</span><span><span id="calc_total">{{ $defaultPrice }}</span> ৳</span></div>

                            <button type="submit" id="submit_btn" class="btn-glowing mt-4" style="width: 100%; border-radius: 12px; font-size: 20px; padding: 15px;">অর্ডার কনফার্ম করুন <i class="fas fa-check-circle ms-2"></i></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@if(!empty($ln_pg->faq_title))
<div class="feature-container" style="max-width: 800px; margin-top: 80px; margin-bottom: 60px;">
    <div class="section-title-wrapper">
        <div class="section-badge"><i class="fas fa-question-circle"></i> {{ $ln_pg->faq_badge ?? 'জিজ্ঞাসা' }}</div>
        <h2 class="section-title">{{ $ln_pg->faq_title ?? 'সচরাচর জিজ্ঞাসিত প্রশ্ন (FAQ)' }}</h2>
    </div>
    @for($i=1; $i<=4; $i++)
        @if(!empty($ln_pg->{'faq_'.$i.'_q'}))
        <div class="faq-item">
            <div class="faq-header">{{ $ln_pg->{'faq_'.$i.'_q'} }} <i class="fas fa-chevron-down"></i></div>
            <div class="faq-body">{{ $ln_pg->{'faq_'.$i.'_a'} }}</div>
        </div>
        @endif
    @endfor
</div>
@endif

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

</main> 

@include('frontend.partials.footer')

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>

<script>
    var current_discount_val = 0;
    var current_discount_type = "fixed";
    var isWeightBased = {{ $isWeightBased ? 'true' : 'false' }};
    var hasVariations = {{ $variations->count() > 0 ? 'true' : 'false' }};
    var isFreeShipping = {{ $isFreeShipping }};

    var paymentID;
    var dynamicOrderId;
    var successUrl = '';

    function toNumber(v){ v = (v ?? '').toString().replace(/[^\d.]/g,''); var n = parseFloat(v); return isNaN(n) ? 0 : n; }

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
            data: { 
                code: code,
                total_price: current_total 
            },
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
        if(!isResend) $('#submit_btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Sending OTP...');
        
        $.ajax({
            url: "{{ route('sendOtp') }}", type: "POST", data: { mobile: mobile, _token: "{{ csrf_token() }}" },
            success: function(res) {
                isSendingOtp = false;
                if(!isResend) $('#submit_btn').prop('disabled', false).html('অর্ডার কনফার্ম করুন <i class="fas fa-check-circle ms-2"></i>');
                
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
                if(!isResend) $('#submit_btn').prop('disabled', false).html('অর্ডার কনফার্ম করুন <i class="fas fa-check-circle ms-2"></i>'); 
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

    function submitOrderFinal() {
        let $form = $('#checkout_land_form');
        let payMethod = $('input[name="payment_method"]:checked').val() || 'cod'; 
        
        // Purchase event_id is derived server-side as 'PUR_<order_id>' so the browser
        // and Conversions API agree; a client-invented id here caused double counting.

        if(payMethod === 'sslcommerz'){
            $form.attr('action', "{{ url('/pay') }}")[0].submit(); 
            return; 
        }
        
        $('#submit_btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> প্রসেসিং হচ্ছে...');

        var total = toNumber($('#final_amount').val());
        var ship_charge = toNumber($('#calc_shipping').text() || 0);
        var unitPrice = toNumber($('#unit_price').val());
        var q = toNumber($('#qty_input').val());
        
        var items = [{
            item_id: "{{ $productId }}",
            item_name: @json($productName),
            item_category: @json($contentCategory),
            price: unitPrice,
            quantity: q
        }];
        
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
        toastr.options = { "closeButton": true, "progressBar": true, "positionClass": "toast-top-right", "showDuration": "200",  "hideDuration": "300", "timeOut": "2000", "extendedTimeOut": "500" };

        $("#productGallery").owlCarousel({ 
            loop: true, 
            autoplay: true, 
            margin: 10, 
            nav: false, 
            dots: true, 
            items: 1 
        });

        $("#textReviewCarousel").owlCarousel({ 
            loop: true, 
            autoplay: true, 
            margin: 15, 
            nav: false, 
            dots: true, 
            responsive: { 
                0: { items: 1 }, 
                768: { items: 2 }, 
                1000: { items: 2 } 
            } 
        });

        $('.faq-header').on('click', function() {
            $(this).toggleClass('active');
            $(this).next('.faq-body').slideToggle(300);
        });

        function initCountdown() {
            var targetDate = localStorage.getItem('lp_countdown_{{ $productId }}');
            if (!targetDate || new Date().getTime() > targetDate) {
                targetDate = new Date().getTime() + (5 * 60 * 60 * 1000);
                localStorage.setItem('lp_countdown_{{ $productId }}', targetDate);
            }

            setInterval(function() {
                var now = new Date().getTime();
                var distance = targetDate - now;

                if (distance < 0) {
                    $('#hours').text('00');
                    $('#minutes').text('00');
                    $('#seconds').text('00');
                    return;
                }

                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                $('#hours').text(hours < 10 ? '0' + hours : hours);
                $('#minutes').text(minutes < 10 ? '0' + minutes : minutes);
                $('#seconds').text(seconds < 10 ? '0' + seconds : seconds);
            }, 1000);
        }
        initCountdown();

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

            // ৪. JavaScript-এ isFreeShipping এর চেকিং আপডেট করা হলো
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

        function checkVariation() {
            if(!hasVariations) {
                let stock = parseInt($('#max_stock').val()) || 0;
                if(stock <= 0) {
                    $('#stock_status').text('Out of Stock').removeClass('in-stock').addClass('out-stock');
                    $('#submit_btn').prop('disabled', true).text('Out of Stock / স্টকে নেই');
                    $('#qty_input').val(0);
                } else {
                    $('#stock_status').text('In Stock').removeClass('out-stock').addClass('in-stock');
                    $('#submit_btn').prop('disabled', false).html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-2"></i>');
                    if($('#qty_input').val() == 0) $('#qty_input').val(1);
                }
                calculate(); 
                return; 
            }

            let selectedOption = $('#variation_select').find('option:selected');
            if(selectedOption.length > 0) {
                let variation_id = selectedOption.val();
                let price = parseFloat(selectedOption.data('price')) || 0;
                // FIX: data-stock না থাকলে আগে 0 ধরে নিয়ে ভুল করে "স্টক নেই" দেখাত।
                let rawStock = parseInt(selectedOption.data('stock'));
                let stock = isNaN(rawStock) ? (parseInt($('#max_stock').val()) || 0) : rawStock;

                $("#variation_id").val(variation_id);
                $('#unit_price').val(price);
                $('#unit_price_display').text(price);
                $('#max_stock').val(stock);

                let $submitBtn = $('#submit_btn');

                if(stock > 0){
                    $('#stock_status').text('In Stock').removeClass('out-stock').addClass('in-stock');
                    $submitBtn.prop('disabled', false).html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-2"></i>');
                    if($('#qty_input').val() == 0) $('#qty_input').val(1);
                } else {
                    toastr.error('Out of Stock!');
                    $('#stock_status').text('Out of Stock').removeClass('in-stock').addClass('out-stock');
                    $submitBtn.prop('disabled', true).text('স্টক আউট');
                    $('#qty_input').val(0);
                }
            } else {
                $('#variation_id').val('');
                $('#submit_btn').prop('disabled', true).text('Unavailable / স্টকে নেই');
                $('#qty_input').val(0);
            }
            calculate();
        }

        $(document).on('change', '#variation_select', function(){
            checkVariation();
        });

        $('#delivery_charge').on('change', calculate);
        
        $('#qty_plus').click(function(){ let q = parseInt($('#qty_input').val()) || 1; let max = parseInt($('#max_stock').val()) || 1; if(q < max) { $('#qty_input').val(q + 1); calculate(); } else { toastr.warning('Maximum Stock Limit Reached!'); } });
        $('#qty_minus').click(function(){ let q = parseInt($('#qty_input').val()) || 1; if(q > 1) { $('#qty_input').val(q - 1); calculate(); } });

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

        $('#checkout_land_form').submit(function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            
            $('span.textdanger').remove();
            
            var q = parseInt($('#qty_input').val()) || 1;
            $('#product_quantity').val(q);
            
            if(hasVariations) {
                if(!$('#variation_id').val()) {
                    toastr.error('সঠিক সাইজ এবং কালার সিলেক্ট করুন!');
                    return false;
                }
            }

            if(!$('#delivery_charge').val()){ toastr.warning('ডেলিভারি এলাকা নির্বাচন করুন!'); return false; }

            let paymentMethod = $('input[name="payment_method"]:checked').val() || 'cod'; 
            
            if(paymentMethod !== 'online' && paymentMethod !== 'bkash' && paymentMethod !== 'eps' && paymentMethod !== 'nagad' && paymentMethod !== 'uddoktapay' && paymentMethod !== 'Cash on Delivery' && paymentMethod !== 'cod' && paymentMethod !== 'sslcommerz') {
                if(!$('#sender_number').val() || !$('#transaction_id').val()) {
                    toastr.warning('দয়া করে পেমেন্ট নাম্বার এবং Transaction ID দিন');
                    return false;
                }
            }

            if (paymentMethod === 'sslcommerz' && !otpSystemEnabled) {
                $(this).attr('action', "{{ url('/pay') }}").attr('method', 'POST')[0].submit();
                return;
            }

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

        checkVariation();
        
        var selectedPayment = $('input[name="payment_method"]:checked');
        if(selectedPayment.length > 0){ 
            selectedPayment.trigger('change'); 
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