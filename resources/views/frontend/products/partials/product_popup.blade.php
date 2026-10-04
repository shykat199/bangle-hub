{{-- resources/views/frontend/products/partials/product_popup.blade.php --}}

@php
    use App\Models\AdminText;
    use App\Models\BanglaText;

    $dt = AdminText::first();
    $bangla_text = BanglaText::first();
    
    $fshipText = $bangla_text->fshipping_text ?? 'Free Shipping';
    $curr = $info->currency ?? 'BDT';

    $singleProduct->loadMissing(['variations.size','variations.color','variations.stocks','reviews','images','category']);

    /* Default rows settings (show.blade.php এর মত সেম লজিক) */
    $DEFAULT_SIZE_ID  = 0; 
    $DEFAULT_COLOR_ID = 0; 

    $varMap    = [];
    $sizesMap  = [];
    $colorsMap = [];

    if($singleProduct->variations && $singleProduct->variations->count() > 0){
        foreach($singleProduct->variations as $v){
            $sid = (int)($v->size_id ?? 0);
            $cid = (int)($v->color_id ?? 0);

            $sizeLabel  = $v->size_label  ?? optional($v->size)->name  ?? ($v->size  ?? 'Default');
            $colorLabel = $v->color_label ?? optional($v->color)->name ?? ($v->color ?? 'Default');

            if($sid !== 0) $sizesMap[$sid]  = $sizeLabel;
            if($cid !== 0) $colorsMap[$cid] = $colorLabel;

            $base  = (float)($v->price ?? 0);
            $after = (float)($v->after_discount_price ?? 0);
            $disc  = (float)($v->discount_price ?? 0);

            if($after > 0 && $after < $base) $final = $after;
            elseif($disc > 0 && $disc < $base) $final = $disc;
            else $final = $base;

            // আগে শুধু product_stocks পড়ত, কোনো fallback ছিল না — তাই ডিটেইল পেজে
            // স্টক দেখালেও popup-এ STOCK OUT দেখাত। এখন সবাই resolveStock() পড়ে।
            $stock = resolveStock($singleProduct, $v);

            $key = $sid.'|'.$cid;
            $varMap[$key] = [
                'id'       => (int)$v->id,
                'size_id'  => $sid,
                'color_id' => $cid,
                'size'     => $sid !== 0 ? $sizeLabel : 'Default',
                'color'    => $cid !== 0 ? $colorLabel : 'Default',
                'raw'      => $base,
                'price'    => $final,
                'stock'    => $stock,
            ];
        }
    }

    $showSize  = count($sizesMap)  > 0;
    $showColor = count($colorsMap) > 0;

    /* Default variation pick */
    $defaultVar = $singleProduct->variations->first();
    if($defaultVar){
        foreach($singleProduct->variations as $v){
            if(!empty($v->is_default) && (int)$v->is_default === 1){
                $defaultVar = $v; break;
            }
        }
    }

    $defaultSizeId  = (int)($defaultVar->size_id ?? 0);
    $defaultColorId = (int)($defaultVar->color_id ?? 0);

    /* In case only size or only color exists, set other side to its default id */
    if($showSize && !$showColor){
        $defaultColorId = $DEFAULT_COLOR_ID;
    }
    if(!$showSize && $showColor){
        $defaultSizeId = $DEFAULT_SIZE_ID;
    }

    // সিম্পল প্রোডাক্ট হ্যান্ডেলিং (যদি varMap খালি থাকে)
    if(count($varMap) === 0) {
        $key = $DEFAULT_SIZE_ID.'|'.$DEFAULT_COLOR_ID;
        
        $mPrice = $singleProduct->sell_price;
        $mAfter = $singleProduct->after_discount;
        $mFinal = ($mAfter > 0 && $mAfter < $mPrice) ? $mAfter : $mPrice;

        $varMap[$key] = [
            'id'       => null, 
            'size_id'  => $DEFAULT_SIZE_ID,
            'color_id' => $DEFAULT_COLOR_ID,
            'size'     => 'Regular',
            'color'    => 'Regular',
            'raw'      => (float)$mPrice,
            'price'    => (float)$mFinal,
            'stock'    => (int)$singleProduct->stock_quantity,
        ];
        
        $defaultSizeId  = $DEFAULT_SIZE_ID;
        $defaultColorId = $DEFAULT_COLOR_ID;
    }

    $initialKey = $defaultSizeId.'|'.$defaultColorId;
    $initialVar = $varMap[$initialKey] ?? (count($varMap) ? reset($varMap) : null);

    $fallbackRaw    = (float)($data['old_price'] ?? ($singleProduct->sell_price ?? 0));
    $fallbackFinal = (float)($data['price'] ?? (((float)($singleProduct->after_discount ?? 0) > 0) ? (float)$singleProduct->after_discount : (float)($singleProduct->sell_price ?? 0)));
    if($fallbackRaw <= 0)   $fallbackRaw    = (float)($singleProduct->sell_price ?? 0);
    if($fallbackFinal <= 0) $fallbackFinal = (float)($singleProduct->sell_price ?? 0);

    $baseOld   = (float)($initialVar['raw']    ?? $fallbackRaw);
    $baseFinal = (float)($initialVar['price'] ?? $fallbackFinal);

    $stockQty = (int)($initialVar['stock'] ?? ($singleProduct->stock_quantity ?? 0));
    $inStock  = $stockQty > 0;

    $hasDiscount = ($baseFinal > 0 && $baseOld > 0 && $baseFinal < $baseOld);
    $discPercent = $hasDiscount ? round((($baseOld - $baseFinal) / $baseOld) * 100, 0) : 0;

    $detailsUrl = route('front.products.show', $singleProduct->slug ?: $singleProduct->id);

    $fmt = function($amount) use ($curr){
        $amount = (float)$amount;
        if($curr == 'BDT')    return '৳ '.(int)round($amount);
        if($curr == 'Dollar') return '$ '.number_format($amount, 2);
        if($curr == 'Euro')   return '€ '.number_format($amount, 2);
        return (string)$amount;
    };
@endphp

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>

<style>
  :root {
      --brand-gradient: {!! $info->gradient_code ?? 'linear-gradient(90deg, #0d6efd, #00276C)' !!};
      --brand-text: #ffffff;
  }

  #quick-view-modal{ z-index:20000 !important; }
  .modal-backdrop{ z-index:19990 !important; }
  #quick-view-modal .modal-header button[data-bs-dismiss="modal"]:not(:last-child),
  #quick-view-modal .modal-header .pmodal-close,
  #quick-view-modal .modal-header .close,
  #quick-view-modal .modal-header .btn-close-left{ display:none !important; }

  .pmodal, .pmodal *{ font-family:'Hind Siliguri', sans-serif !important; }

  .pmodal i.fa, .pmodal i.fas, .pmodal i.far, .pmodal i.fal, .pmodal i.fab,
  .pmodal .fa, .pmodal .fas, .pmodal .far, .pmodal .fal, .pmodal .fab{
    font-family:"Font Awesome 5 Free" !important; font-style:normal !important;
    display:inline-block !important; visibility:visible !important; opacity:1 !important; line-height:1 !important;
  }
  .pmodal .fas, .pmodal i.fas{ font-weight:900 !important; }
  .pmodal .far, .pmodal i.far{ font-weight:400 !important; }
  .pmodal .fab, .pmodal i.fab{ font-family:"Font Awesome 5 Brands" !important; font-weight:400 !important; }

  .pmodal-card{ background:#fff; border-radius:16px; border:1px solid rgba(0,0,0,.06); box-shadow:0 14px 40px rgba(2,6,23,.08); overflow:hidden; }
  .pmodal-grid{ display:grid; grid-template-columns: 1.05fr .95fr; gap:14px; align-items:start; }
  @media(max-width:991px){ .pmodal-grid{ grid-template-columns:1fr; } }
  .pmodal-media{ padding:12px; background:linear-gradient(180deg, rgba(0,39,108,.05), rgba(255,255,255,0)); border-right:1px solid rgba(0,0,0,.06); }
  @media(max-width:991px){ .pmodal-media{ border-right:0; border-bottom:1px solid rgba(0,0,0,.06); } }
  .pmodal-media .main-img{ width:100%; border-radius:14px; overflow:hidden; background:#fff; border:1px solid rgba(0,0,0,.06); }
  .pmodal-media .main-img img{ width:100%; height:auto; display:block; object-fit:cover; }
  .pmodal-thumbs{ display:flex; gap:8px; margin-top:10px; flex-wrap:wrap; }
  .pmodal-thumb{ width:58px; height:58px; border-radius:12px; overflow:hidden; border:1px solid rgba(0,0,0,.08); cursor:pointer; background:#fff; flex:0 0 auto; }
  .pmodal-thumb img{ width:100%; height:100%; object-fit:cover; display:block; }
  .pmodal-thumb.active{ outline:3px solid rgba(0,39,108,.25); }
  @media(max-width:575px){ .pmodal-thumbs{ flex-wrap:nowrap; overflow-x:auto; padding-bottom:6px; } .pmodal-thumb{ width:54px; height:54px; } }

  .pmodal-info{ padding:14px; }
  @media(max-width:575px){ .pmodal-info{ padding:12px; } }
  .pname{ font-weight:900; font-size:20px; margin:0 0 8px; }
  @media(max-width:575px){ .pname{ font-size:18px; } }

  .pprice{ margin:6px 0 10px; }
  .pprice-line{ display:flex; align-items:baseline; gap:14px; flex-wrap:wrap; }
  .pold{ color:#dc2626; font-weight:900; font-size:18px; opacity:.7; text-decoration:line-through; }
  .pnew{ color:#00276C; font-weight:900; font-size:30px; letter-spacing:.2px; }
  @media(max-width:575px){ .pnew{ font-size:28px; } .pold{ font-size:17px; } }

  .pbadges{ display:flex; gap:10px; flex-wrap:wrap; margin:14px 0 10px; }
  .pbadge{ padding:10px 16px; border-radius:999px; font-weight:900; font-size:18px; border:1px solid rgba(0,0,0,.10); }
  .pbadge.green{ background:rgba(34,197,94,.12); color:#166534; border-color:rgba(34,197,94,.35); }
  .pbadge.blue{ background:rgba(59,130,246,.12); color:#1d4ed8; border-color:rgba(59,130,246,.30); }
  .pbadge.red{ background:rgba(239,68,68,.12); color:#991b1b; border-color:rgba(239,68,68,.30); }

  .details-ratting-wrapper{ margin:6px 0 10px; }
  .details-ratting-wrapper i{ color:#FFDF00; }
  .details-ratting-wrapper i.far.fa-star{ color:#9ca3af; }

  .product-code{ margin:8px 0 12px; }
  .product-code p{ display:inline-flex; align-items:center; gap:8px; background:#3c7d17; color:#fff; padding:10px 12px; border-radius:12px; margin:0; font-weight:900; line-height:1.2; max-width:100%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  @media(max-width:575px){ .product-code p{ white-space:normal; } }

  #pmodalVariantBox{ margin-top:10px; }
  #pmodalVariantBox label{ font-weight:900; margin:10px 0 6px; display:block; }
  .pvopt{ cursor:pointer; user-select:none; padding:8px 14px; border-radius:999px; border:1px solid rgba(17,24,39,.14); background:#fff; font-weight:900; display:inline-flex; align-items:center; gap:6px; }
  .pvopt.active{ border-color:#00276C; background:rgba(0,39,108,.08); outline:2px solid rgba(0,39,108,.20); }
  .size_name{ margin-top:10px; display:block; font-weight:900; color:#166534; }

  .qty-cart{ display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
  .quantity{ position:relative; border:1px solid #111827; height:42px; overflow:hidden; width:140px; border-radius:10px; flex:0 0 auto; background:#fff; }
  .quantity .minus, .quantity .plus{ position:absolute; top:0; width:42px; height:42px; line-height:42px; text-align:center; cursor:pointer; font-weight:900; user-select:none; }
  .quantity .minus{ left:0; border-right:1px solid #111827; font-size:32px; }
  .quantity .plus{ right:0; border-left:1px solid #111827; font-size:24px; }
  .quantity input{ width:100%; height:100%; border:0; text-align:center; font-weight:900; background:#fff !important; }
  @media(max-width:575px){ .quantity{ width:100%; } }

  .btn-row{ display:flex; gap:10px; margin-top:10px; }

  .add_cart_btn{
      flex:1;
      color: #fff !important; 
      background: linear-gradient(90deg, rgba(34,197,94,1), rgba(16,185,129,1)) !important;
      border: 1px solid transparent !important;
      height: 52px;
      border-radius: 14px;
      font-weight: 900;
      font-size: 18px;
  }

  @keyframes gradientShake {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
  }

  @keyframes glowingPulse {
      0% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.7); transform: scale(1); }
      50% { box-shadow: 0 0 0 12px rgba(13, 110, 253, 0); transform: scale(1.03); }
      100% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); transform: scale(1); }
  }

  .order_now_btn{
      flex:1;
      height: 52px;
      border-radius: 14px;
      font-weight: 900;
      font-size: 18px;
      background: linear-gradient(45deg, #00276C, #2563eb, #00276C, #3b82f6) !important;
      background-size: 300% 300% !important;
      color: #fff !important;
      border: none !important;
      animation: gradientShake 3s ease infinite, glowingPulse 2s infinite !important;
      position: relative;
      z-index: 1;
  }
  .order_now_btn:hover{ filter: brightness(1.1); }

  @media(max-width:575px){ .btn-row{ flex-direction:column; } .add_cart_btn, .order_now_btn{ width:100%; height:52px; font-size:18px; } }
  button:disabled, .btn:disabled{ opacity:.55 !important; cursor:not-allowed !important; }

  .courier-card{ margin-top:14px; border:1px solid rgba(17,24,39,.12); border-radius:14px; overflow:hidden; background:#fff; }
  .courier-head{ background:linear-gradient(90deg, rgba(0,39,108,.10), rgba(60,125,23,.08)); padding:10px 12px; font-weight:900; color:#111827; text-align:center; border-bottom:1px solid rgba(17,24,39,.10); }
  .courier-table{ width:100%; border-collapse:collapse; margin:0; font-weight:900; table-layout:fixed; }
  .courier-table td{ padding:10px 12px; border-bottom:1px solid rgba(17,24,39,.08); vertical-align:middle; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .courier-table tr:last-child td{ border-bottom:0; }
  .courier-table td.c-title{ width:70%; text-align:left; }
  .courier-table td.c-amount{ width:30%; text-align:right; }

  .details-btn-wrap{ display:flex; justify-content:center; margin-top:12px; }
  .details-btn{ width:min(420px,100%); height:46px; border-radius:12px; font-weight:900; font-size:16px; border:1px solid rgba(0,39,108,.25); background:rgba(0,39,108,.07); color:#00276C; display:flex; align-items:center; justify-content:center; gap:8px; text-decoration:none; }
  .details-btn:hover{ background:rgba(0,39,108,.12); color:#00276C; text-decoration:none; }
</style>

<div class="pmodal"
     data-popup="1"
     data-currency="{{ e($curr) }}"
     data-def-size="{{ (int)$defaultSizeId }}"
     data-def-color="{{ (int)$defaultColorId }}"
     data-show-size="{{ $showSize ? 1 : 0 }}"
     data-show-color="{{ $showColor ? 1 : 0 }}"
     data-default-size-id="{{ (int)$DEFAULT_SIZE_ID }}"
     data-default-color-id="{{ (int)$DEFAULT_COLOR_ID }}">

  <script type="application/json" class="pmodalVarJson">@json($varMap)</script>

  <div class="pmodal-card">
    <div class="pmodal-grid">

      <div class="pmodal-media">
        <div class="main-img">
          <img class="pmodalMainImg" src="{{ getImage('products', $singleProduct->image) }}" alt="{{ $singleProduct->name }}"/>
        </div>

        <div class="pmodal-thumbs" id="pmodalThumbs">
          <div class="pmodal-thumb active" data-src="{{ getImage('products', $singleProduct->image) }}">
            <img src="{{ getImage('products', $singleProduct->image) }}" alt="thumb">
          </div>

          @if(isset($singleProduct->images) && $singleProduct->images->count() > 0)
            @foreach($singleProduct->images as $im)
              <div class="pmodal-thumb" data-src="{{ getImage('products', $im->image) }}">
                <img src="{{ getImage('products', $im->image) }}" alt="thumb">
              </div>
            @endforeach
          @endif
        </div>
      </div>

      <div class="pmodal-info">

        <h3 class="pname">{{ $singleProduct->name }}</h3>
        @if($singleProduct->minOrderQty() > 1)
          <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin:4px 0 8px;">
            <span style="background:#0f172a;color:#fff;font-size:12px;font-weight:700;padding:3px 10px;border-radius:999px;">Wholesale</span>
            <span style="font-size:13px;color:#b45309;">Minimum order: <strong>{{ $singleProduct->minOrderQty() }}</strong> pcs{{ $singleProduct->type === 'variable' ? ' per variant' : '' }}</span>
          </div>
        @endif

        <div class="pprice">
          <div class="pprice-line">
            @if($hasDiscount)
              <span class="pold pmodal_old_price">{{ $fmt($baseOld) }}</span>
            @else
              <span class="pold pmodal_old_price" style="display:none;"></span>
            @endif
            <span class="pnew pmodal_final_price">{{ $fmt($baseFinal) }}</span>
          </div>
        </div>

        <div class="pbadges">
          <span class="pbadge pmodal_stock_badge {{ $inStock ? 'green' : 'red' }}">
            {{ $inStock ? $stockQty : 0 }} Items left
          </span>

          @if($hasDiscount)
            <span class="pbadge blue pmodal_disc_badge">{{ $discPercent }}% Off</span>
          @else
            <span class="pbadge blue pmodal_disc_badge" style="display:none;"></span>
          @endif
        </div>

        <div class="details-ratting-wrapper">
          @php
            $totalReviews  = $singleProduct->reviews->count();
            $averageRating = $totalReviews > 0 ? round($singleProduct->reviews->avg('review'), 2) : 0;
          @endphp
          <span style="font-weight:900;">{{ $totalReviews }} Reviews</span>
          @for ($i = 1; $i <= 5; $i++)
            @if ($i <= $averageRating)
              <i class="fas fa-star"></i>
            @elseif ($i - 0.5 <= $averageRating)
              <i class="fas fa-star-half-alt"></i>
            @else
              <i class="far fa-star"></i>
            @endif
          @endfor
          <span style="font-weight:900;">{{ number_format($averageRating, 2) }}/5</span>
        </div>

        <div class="product-code">
          <p>
            <span>{{ $dt->product_code_text ?? 'প্রোডাক্ট কোড :' }}</span>
            {{ $singleProduct->sku }}
          </p>
        </div>

        @if(count($varMap) > 0 && ($showSize || $showColor))
          <div class="mt-2" id="pmodalVariantBox">

            @if($showSize)
              <label class="mb-2">Size:</label>
              <div class="d-flex flex-wrap gap-2 pmodalSizeOptions">
                @foreach($sizesMap as $sid => $slabel)
                  <span class="pvopt pmodal-size-opt {{ ((int)$sid === (int)$defaultSizeId) ? 'active' : '' }}"
                        data-size-id="{{ (int)$sid }}">{{ $slabel }}</span>
                @endforeach
              </div>
            @endif

            @if($showColor)
              <label class="mb-2">Color:</label>
              <div class="d-flex flex-wrap gap-2 pmodalColorOptions">
                @foreach($colorsMap as $cid => $clabel)
                  <span class="pvopt pmodal-color-opt {{ ((int)$cid === (int)$defaultColorId) ? 'active' : '' }}"
                        data-color-id="{{ (int)$cid }}">{{ $clabel }}</span>
                @endforeach
              </div>
            @endif

            <span class="size_name pmodal_variant_label"></span>
          </div>
        @endif

        <form action="{{ route('front.carts.storeCart') }}" class="cart_submit_popup" method="POST">
          @csrf
          <input type="hidden" name="product_id" value="{{ $singleProduct->id }}">
          <input type="hidden" name="product_name" value="{{ $singleProduct->name }}">
          <input type="hidden" name="category_id" value="{{ $singleProduct->category->name ?? '' }}">

          <input type="hidden" name="variation_id" class="pmodal_variation_id" value="{{ $initialVar['id'] ?? '' }}">
          <input type="hidden" name="variant_name" class="pmodal_variant_name" value="">
          <input type="hidden" class="pmodal_size_value"  name="size_value"  value="">
          <input type="hidden" class="pmodal_size_value1" name="size_value1" value="">
          <input type="hidden" class="pmodal_price_val"  name="price_val"  value="{{ $baseFinal }}">
          <input type="hidden" class="pmodal_price_val1" name="price_val1" value="{{ $baseFinal }}">
          <input type="hidden" name="action_type" class="popup_action_type" value="cart">

          <div class="mt-2">
            <div class="qty-cart">
              <div class="quantity pmodalQty">
                <span class="minus">-</span>
                <input type="text" name="quantity" value="{{ $singleProduct->minOrderQty() }}" data-min="{{ $singleProduct->minOrderQty() }}" inputmode="numeric">
                <span class="plus">+</span>
              </div>
            </div>

            <div class="btn-row">
              <button type="button" class="btn add_cart_btn js-popup-cart" {{ $inStock ? '' : 'disabled' }}>
                {{ $dt->add_to_cart_text ?? 'কার্টে যোগ করুন' }}
              </button>

              <button type="button" class="btn order_now_btn js-popup-order" {{ $inStock ? '' : 'disabled' }}>
                @if(($singleProduct->is_free_shipping ?? 0) == 1)
                    <i class="fas fa-shipping-fast"></i> &nbsp; {{ $fshipText }}
                @else
                    {{ $dt->order_now_text ?? 'অর্ডার করুন' }}
                @endif
              </button>
            </div>
          </div>

          {{-- ✅ FIXED: Courier Card Free Shipping Logic same as show.blade.php --}}
          @if(($singleProduct->is_free_shipping ?? 0) == 0)
            <div class="courier-card">
              <div class="courier-head">{{ $dt->courier_delivery_cost_text ?? 'কুরিয়ার ডেলিভারি খরচ' }}</div>
              <table class="courier-table">
                <tbody>
                @foreach($charges as $charge)
                  <tr>
                    <td class="c-title">{{ $charge->title }}</td>
                    <td class="c-amount">৳ {{ number_format($charge->amount, 0) }}</td>
                  </tr>
                @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="courier-card">
                <div class="courier-head">{{ $dt->courier_delivery_cost_text ?? 'কুরিয়ার ডেলিভারি খরচ' }}</div>
                <div class="text-center text-success" style="padding: 12px; font-weight: 900; font-size: 16px;">
                    <i class="fas fa-shipping-fast"></i> {{ $fshipText }}
                </div>
            </div>
          @endif

          <div class="details-btn-wrap">
            <a class="details-btn" href="{{ $detailsUrl }}">
              <i class="fas fa-info-circle"></i> Details
            </a>
          </div>

          <div class="mt-2">
            <ul class="product-metas">{!! $singleProduct->feature !!}</ul>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>

<script>
(function(){
  // Prevent DOM conflicts using specific context selector
  const popups = document.querySelectorAll('.pmodal[data-popup="1"]');
  const root = popups[popups.length - 1]; 
  if(!root) return;

  const varJsonEl = root.querySelector('.pmodalVarJson');
  const VAR_MAP   = varJsonEl ? JSON.parse(varJsonEl.textContent || '{}') : {};

  const showSize  = root.getAttribute('data-show-size') === '1';
  const showColor = root.getAttribute('data-show-color') === '1';
  const DEF_SIZE  = parseInt(root.getAttribute('data-default-size-id') || '0', 10);
  const DEF_COLOR = parseInt(root.getAttribute('data-default-color-id') || '0', 10);

  const oldEl   = root.querySelector('.pmodal_old_price');
  const finalEl = root.querySelector('.pmodal_final_price');
  const stockEl = root.querySelector('.pmodal_stock_badge');
  const discEl  = root.querySelector('.pmodal_disc_badge');

  const vIdEl   = root.querySelector('.pmodal_variation_id');
  const vNameEl = root.querySelector('.pmodal_variant_name');
  const sValEl  = root.querySelector('.pmodal_size_value');
  const sVal1El = root.querySelector('.pmodal_size_value1');
  const pValEl  = root.querySelector('.pmodal_price_val');
  const pVal1El = root.querySelector('.pmodal_price_val1');
  const labelEl = root.querySelector('.pmodal_variant_label');

  const cartBtn  = root.querySelector('.js-popup-cart');
  const orderBtn = root.querySelector('.js-popup-order');
  const actionEl = root.querySelector('.popup_action_type');
  const formEl   = root.querySelector('.cart_submit_popup');

  const fmt = (amount) => {
    const curr = root.getAttribute('data-currency') || 'BDT';
    amount = parseFloat(amount || 0);
    if(curr === 'BDT') return '৳ ' + Math.round(amount);
    if(curr === 'Dollar') return '$ ' + amount.toFixed(2);
    if(curr === 'Euro') return '€ ' + amount.toFixed(2);
    return String(amount);
  };

  function getActiveSizeId(){
    if(!showSize) return DEF_SIZE;
    const a = root.querySelector('.pmodalSizeOptions .pmodal-size-opt.active');
    return a ? parseInt(a.getAttribute('data-size-id') || '0', 10) : 0;
  }
  function getActiveColorId(){
    if(!showColor) return DEF_COLOR;
    const a = root.querySelector('.pmodalColorOptions .pmodal-color-opt.active');
    return a ? parseInt(a.getAttribute('data-color-id') || '0', 10) : 0;
  }

  function resolveVariation(sid, cid){
    const key = String(sid) + '|' + String(cid);
    return VAR_MAP[key] || null;
  }

  function setBtns(disabled){
    if(cartBtn)  cartBtn.disabled  = !!disabled;
    if(orderBtn) orderBtn.disabled = !!disabled;
  }

  function applyVariation(v){
    if(!v) return;

    window.__PMODAL_ACTIVE_VAR__ = v;

    const stock = parseInt(v.stock || 0, 10);
    if(stockEl){
      stockEl.classList.remove('green','red');
      stockEl.classList.add(stock > 0 ? 'green' : 'red');
      stockEl.textContent = (stock > 0 ? stock : 0) + ' Items left';
    }

    if(finalEl) finalEl.textContent = fmt(v.price);

    const hasDisc = (parseFloat(v.raw || 0) > 0 && parseFloat(v.price || 0) > 0 && parseFloat(v.price) < parseFloat(v.raw));
    if(oldEl){
      if(hasDisc){
        oldEl.style.display = '';
        oldEl.textContent = fmt(v.raw);
      }else{
        oldEl.style.display = 'none';
        oldEl.textContent = '';
      }
    }

    if(discEl){
      if(hasDisc){
        const pct = Math.round(((parseFloat(v.raw) - parseFloat(v.price)) / parseFloat(v.raw)) * 100);
        discEl.style.display = '';
        discEl.textContent = pct + '% Off';
      }else{
        discEl.style.display = 'none';
        discEl.textContent = '';
      }
    }

    let labelParts = [];
    if(showSize && v.size && v.size !== 'Default') labelParts.push(v.size);
    if(showColor && v.color && v.color !== 'Default') labelParts.push(v.color);
    const label = labelParts.join(' - ');

    if(labelEl) labelEl.textContent = label;

    if(vIdEl) vIdEl.value = v.id || '';
    if(vNameEl) vNameEl.value = label || '';
    if(sValEl)  sValEl.value  = label || '';
    if(sVal1El) sVal1El.value = label || '';
    if(pValEl)  pValEl.value  = v.price || 0;
    if(pVal1El) pVal1El.value = v.price || 0;

    setBtns(stock <= 0);
  }

  function setInvalidState(){
    window.__PMODAL_ACTIVE_VAR__ = null;
    if(labelEl) labelEl.textContent = 'Not available';
    if(stockEl){
      stockEl.classList.remove('green');
      stockEl.classList.add('red');
      stockEl.textContent = '0 Items left';
    }
    setBtns(true);
  }

  function refreshFromActive(){
    const sid = getActiveSizeId() || DEF_SIZE;
    const cid = getActiveColorId() || DEF_COLOR;

    const v = resolveVariation(sid, cid);
    if(v) applyVariation(v);
    else setInvalidState();
  }

  /* Thumbs */
  const thumbs = root.querySelectorAll('.pmodal-thumbs .pmodal-thumb');
  const mainImg = root.querySelector('.pmodalMainImg');
  thumbs.forEach(t => {
    t.addEventListener('click', function(){
      thumbs.forEach(x => x.classList.remove('active'));
      t.classList.add('active');
      const src = t.getAttribute('data-src');
      if(mainImg && src) mainImg.src = src;
    });
  });

  /* Qty */
  const qty = root.querySelector('.pmodalQty');
  if(qty && !qty.dataset.bound){
    qty.dataset.bound = '1';
    const minus = qty.querySelector('.minus');
    const plus  = qty.querySelector('.plus');
    const inp   = qty.querySelector('input[name="quantity"]');

    if(plus){
      plus.addEventListener('click', function(e){
        e.preventDefault();
        const min = parseInt(inp.dataset.min || '1', 10) || 1;
        let v = parseInt(inp.value || '1', 10);
        if(!v || v < min) v = min;
        inp.value = v + 1;
      });
    }
    if(minus){
      minus.addEventListener('click', function(e){
        e.preventDefault();
        const min = parseInt(inp.dataset.min || '1', 10) || 1;
        let v = parseInt(inp.value || '1', 10);
        if(!v || v < min) v = min;
        inp.value = Math.max(min, v - 1);
      });
    }
  }

  /* Size Click Logic - Filters Colors */
  root.querySelectorAll('.pmodalSizeOptions .pmodal-size-opt').forEach(el => {
    el.addEventListener('click', function(){
      root.querySelectorAll('.pmodalSizeOptions .pmodal-size-opt').forEach(x => x.classList.remove('active'));
      el.classList.add('active');
      
      const sizeId  = parseInt(el.getAttribute('data-size-id') || '0', 10);
      
      if (root.querySelector('.pmodalColorOptions')) {
          let hasValidColor = false;
          let firstValidColor = null;

          root.querySelectorAll('.pmodalColorOptions .pmodal-color-opt').forEach(colorEl => {
              let cid = parseInt(colorEl.getAttribute('data-color-id') || '0', 10);
              let checkKey = sizeId + '|' + cid;

              if (VAR_MAP[checkKey]) {
                  colorEl.style.display = ''; 
                  if (firstValidColor === null) firstValidColor = colorEl;
                  if (colorEl.classList.contains('active')) hasValidColor = true;
              } else {
                  colorEl.style.display = 'none';
                  colorEl.classList.remove('active'); 
              }
          });

          if (!hasValidColor && firstValidColor) {
              firstValidColor.classList.add('active');
          }
      }
      refreshFromActive();
    });
  });

  /* Color Click Logic - Filters Sizes */
  root.querySelectorAll('.pmodalColorOptions .pmodal-color-opt').forEach(el => {
    el.addEventListener('click', function(){
      root.querySelectorAll('.pmodalColorOptions .pmodal-color-opt').forEach(x => x.classList.remove('active'));
      el.classList.add('active');

      const colorId = parseInt(el.getAttribute('data-color-id') || '0', 10);
      
      if (root.querySelector('.pmodalSizeOptions')) {
          let hasValidSize = false;
          let firstValidSize = null;

          root.querySelectorAll('.pmodalSizeOptions .pmodal-size-opt').forEach(sizeEl => {
              let sid = parseInt(sizeEl.getAttribute('data-size-id') || '0', 10);
              let checkKey = sid + '|' + colorId;

              if (VAR_MAP[checkKey]) {
                  sizeEl.style.display = '';
                  if (firstValidSize === null) firstValidSize = sizeEl;
                  if (sizeEl.classList.contains('active')) hasValidSize = true;
              } else {
                  sizeEl.style.display = 'none';
                  sizeEl.classList.remove('active');
              }
          });

          if (!hasValidSize && firstValidSize) {
              firstValidSize.classList.add('active');
          }
      }
      refreshFromActive();
    });
  });

  /* Init active */
  if(showSize){
    const hasActive = root.querySelector('.pmodalSizeOptions .pmodal-size-opt.active');
    if(!hasActive){
      const first = root.querySelector('.pmodalSizeOptions .pmodal-size-opt');
      if(first) {
          first.classList.add('active');
          first.click(); // Trigger click to filter properly
      }
    }
  } else if(showColor){
    const hasActive = root.querySelector('.pmodalColorOptions .pmodal-color-opt.active');
    if(!hasActive){
      const first = root.querySelector('.pmodalColorOptions .pmodal-color-opt');
      if(first) {
          first.classList.add('active');
          first.click(); // Trigger click to filter properly
      }
    }
  }
  refreshFromActive();

  /* Cart / Order buttons -> submit */
  function submitAs(type){
    if(!formEl) return;
    if(actionEl) actionEl.value = type;

    if(!window.__PMODAL_ACTIVE_VAR__){
      if(window.toastr){ toastr.clear(); toastr.warning('❌ এই Size-Color কম্বিনেশনটি নেই'); }
      return;
    }
    const stock = parseInt(window.__PMODAL_ACTIVE_VAR__.stock || 0, 10);
    if(stock <= 0){
      if(window.toastr){ toastr.clear(); toastr.warning('❌ এই ভ্যারিয়েশনটি স্টকে নেই'); }
      return;
    }

    // "Add to Cart" from Quick View never fired a browser pixel at all — this
    // form does a native submit() (full page navigation), not the PDP's AJAX
    // call, so there was no JS callback to hang a client-side event off. The
    // fix mirrors show.blade.php's AddToCart handler exactly: a shared
    // event_id goes into a hidden field the server already knows how to read
    // (CartController::addToCart mirrors it via CAPI), so the browser pixel
    // and the server event deduplicate the same way every other AddToCart in
    // this app does.
    if(type === 'cart'){
      try {
        const qvProductId = "{{ $singleProduct->id }}";
        const qvName       = {!! json_encode($singleProduct->name) !!};
        const qtyInput     = formEl.querySelector('input[name="quantity"]');
        const currentQty   = parseInt((qtyInput && qtyInput.value) || '1', 10) || 1;
        const unitPrice    = parseFloat(window.__PMODAL_ACTIVE_VAR__.price || 0);
        const eventID      = "ATC_" + qvProductId + "_" + Date.now();

        if (formEl.querySelector('input[name="event_id"]')) {
          formEl.querySelector('input[name="event_id"]').value = eventID;
        } else {
          const hidden = document.createElement('input');
          hidden.type = 'hidden'; hidden.name = 'event_id'; hidden.value = eventID;
          formEl.appendChild(hidden);
        }

        window.__PIXEL_GUARD__ = window.__PIXEL_GUARD__ || { viewContent: {}, addToCart: {} };
        if (!window.__PIXEL_GUARD__.addToCart[eventID]) {
          window.__PIXEL_GUARD__.addToCart[eventID] = true;

          window.dataLayer = window.dataLayer || [];
          window.dataLayer.push({ ecommerce: null });
          window.dataLayer.push({
            event: 'add_to_cart',
            event_id: eventID,
            ecommerce: {
              currency: 'BDT',
              value: unitPrice * currentQty,
              items: [{ item_id: qvProductId, item_name: qvName, price: unitPrice, quantity: currentQty }]
            }
          });

          if (@json(!$__hasGtm) && typeof fbq === 'function') {
            fbq('track', 'AddToCart', {
              content_ids: [qvProductId], content_name: qvName, content_type: 'product',
              value: unitPrice * currentQty, currency: 'BDT', quantity: currentQty,
              contents: [{ id: qvProductId, quantity: currentQty, item_price: unitPrice }]
            }, { eventID: eventID });
          }

          if (@json(!$__ttFromGtm) && typeof ttq !== 'undefined' && ttq.track) {
            ttq.track('AddToCart', {
              content_type: 'product', content_id: qvProductId, content_name: qvName,
              value: unitPrice * currentQty, currency: 'BDT',
              contents: [{ content_id: qvProductId, content_type: 'product', content_name: qvName, price: unitPrice, quantity: currentQty }]
            }, { event_id: eventID + '_TT' });
          }
        }
      } catch (e) {}
    }

    formEl.submit();
  }

  if(cartBtn){
    cartBtn.addEventListener('click', function(e){
      e.preventDefault();
      submitAs('cart');
    });
  }
  if(orderBtn){
    orderBtn.addEventListener('click', function(e){
      e.preventDefault();
      submitAs('order');
    });
  }

  // Quick View Popup tracking — the id comes from the controller so the browser
  // and the Conversions API / Events API events deduplicate against each other.
  try {
      var qvProductId = "{{ $singleProduct->id }}";
      var qvName      = {!! json_encode($singleProduct->name) !!};
      var qvCategory  = {!! json_encode($singleProduct->category->name ?? '') !!};
      var qvPrice     = {{ (float) (($singleProduct->after_discount > 0) ? $singleProduct->after_discount : $singleProduct->sell_price) }};
      var qvEventID   = {!! json_encode($popupEventId ?? null) !!} || ("QV_" + qvProductId + "_" + Date.now());

      window.__PIXEL_GUARD__ = window.__PIXEL_GUARD__ || { viewContent: {}, addToCart: {} };
      var qvKey = "qv_" + qvEventID;

      if (!window.__PIXEL_GUARD__.viewContent[qvKey]) {
          window.__PIXEL_GUARD__.viewContent[qvKey] = true;

          window.dataLayer = window.dataLayer || [];
          window.dataLayer.push({ ecommerce: null });
          window.dataLayer.push({
              event: 'view_item',
              event_id: qvEventID,
              ecommerce: {
                  currency: 'BDT',
                  value: qvPrice,
                  items: [{ item_id: qvProductId, item_name: qvName, item_category: qvCategory, price: qvPrice, quantity: 1 }]
              }
          });

          if(@json(!$__hasGtm) && typeof fbq === 'function'){
              fbq('track', 'ViewContent', {
                  content_ids: [qvProductId],
                  content_name: qvName,
                  content_type: 'product',
                  value: qvPrice,
                  currency: 'BDT',
                  content_category: qvCategory,
                  contents: [{ id: qvProductId, quantity: 1, item_price: qvPrice }]
              }, { eventID: qvEventID });
          }

          if(@json(!$__ttFromGtm) && typeof ttq !== 'undefined' && ttq.track){
              ttq.track('ViewContent', {
                  content_type: 'product',
                  content_id: qvProductId,
                  content_name: qvName,
                  content_category: qvCategory,
                  value: qvPrice,
                  currency: 'BDT',
                  contents: [{ content_id: qvProductId, content_type: 'product', content_name: qvName, price: qvPrice, quantity: 1 }]
              }, { event_id: qvEventID });
          }
      }
  } catch(e) { console.log('Pixel Error:', e); }

})();
</script>