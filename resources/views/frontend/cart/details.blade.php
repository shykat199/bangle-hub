@php 
    $cart = session()->get('cart', []); 
    use App\Models\Information;
    use Illuminate\Support\Str;
    $info = Information::first();
@endphp

<style>
    /* ✅ BEAUTIFUL BADGE STYLES */
    .product-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        font-family: 'Hind Siliguri', sans-serif;
        margin-top: 5px;
        letter-spacing: 0.3px;
        line-height: 1.2;
    }
    .badge-single { background-color: #e6fffa; color: #047481; border: 1px solid #b2f5ea; }
    .badge-variable { background-color: #ebf8ff; color: #2b6cb0; border: 1px solid #bee3f8; }
    .badge-color { background-color: #faf5ff; color: #6b46c1; border: 1px solid #e9d8fd; }

    /* ✅ QUANTITY SELECTOR STYLES */
    .pro-qty {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        border: 1px solid #ddd;
        border-radius: 30px;
        padding: 3px;
        background: #fff;
        min-width: 90px;
    }
    /* ক্লাসের নাম আগের মতো qtybtn করে দেওয়া হলো */
    .pro-qty .qtybtn {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        background: #f1f3f5;
        border-radius: 50%;
        font-weight: bold;
        font-size: 16px;
        color: #333;
        user-select: none;
        transition: all 0.2s ease-in-out;
    }
    .pro-qty .qtybtn:hover { background: #e2e6ea; color: #000; }
    
    .pro-qty .quantity-input {
        width: 35px;
        text-align: center !important;
        border: none;
        background: transparent;
        font-weight: 700;
        font-size: 15px;
        color: #333;
        outline: none;
        -moz-appearance: textfield;
    }
    .pro-qty .quantity-input::-webkit-outer-spin-button,
    .pro-qty .quantity-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .product-name-text { font-size: 14px; }

    /* ✅ MOBILE OPTIMIZATION */
    @media (max-width: 768px) {
        .table-responsive { overflow-x: hidden !important; }
        .table { table-layout: fixed !important; width: 100% !important; }
        .table th, .table td { 
            font-size: 11px !important; 
            padding: 5px 2px !important; 
            vertical-align: middle; 
            white-space: normal !important; 
            word-wrap: break-word !important;
        }
        
        .product-name-text { font-size: 11px !important; line-height: 1.3 !important; }
        
        .col-img { width: 45px !important; }
        .col-prod { width: auto !important; } 
        .col-price { width: 45px !important; }
        .col-qty { width: 62px !important; }
        .col-total { width: 50px !important; }
        .col-action { width: 30px !important; text-align: center; }

        .pro-qty { min-width: 55px !important; padding: 2px; }
        .pro-qty .qtybtn { width: 18px !important; height: 18px !important; font-size: 12px !important; }
        .pro-qty .quantity-input { width: 20px !important; font-size: 11px !important; }
        
        .cart-item img { max-width: 35px !important; }
        .product-badge { font-size: 9px !important; padding: 2px 4px !important; }
    }
</style>

<aside class="card border-0 shadow-sm rounded-4">
  <article class="card-body">
    <header class="mb-3">
      <h4 class="card-title fw-bold" style="font-family: 'Hind Siliguri', sans-serif; font-size: 18px;">
        অর্ডার ডিটেইলস
      </h4>
    </header>

    <div class="table-responsive">
      <table class="table align-middle">
        <thead class="table-light">
          <tr>
            <th class="fw-bold text-dark col-img" style="font-family: 'Hind Siliguri', sans-serif;">Image</th>
            <th class="fw-bold text-dark col-prod" style="font-family: 'Hind Siliguri', sans-serif;">Product</th>
            <th class="fw-bold text-dark col-price" style="font-family: 'Hind Siliguri', sans-serif;">Price</th>
            <th class="fw-bold text-dark text-center col-qty" style="font-family: 'Hind Siliguri', sans-serif;">QTY</th>
            <th class="fw-bold text-dark col-total" style="font-family: 'Hind Siliguri', sans-serif;">Total</th>
            <th class="col-action"></th>
          </tr>
        </thead>
        <tbody>
          @php
            $total = 0;
            $discount = 0;
          @endphp

          @foreach($cart as $key => $item)
            @php
              $price = $item['price'] * $item['quantity'];
              $total += $price;
              $discount += ($item['discount'] ?? 0) * $item['quantity'];
              
              $productSize  = $item['size'] ?? null;
              $productColor = $item['color'] ?? null;
            @endphp
            <tr class="cart-item">

              <input type="hidden" name="product_id[]" value="{{ $item['product_id'] }}">
              <input type="hidden" name="quantity[]" value="{{ $item['quantity'] }}">
              <input type="hidden" name="unit_price[]" value="{{ $item['price'] }}">
              <input type="hidden" name="unit_discount[]" value="{{ $item['discount'] ?? 0 }}">
              <input type="hidden" name="variation_id[]" value="{{ $item['variation_id'] ?? '' }}">

              <td class="align-middle col-img">
                <a href="{{ route('front.products.show', [$item['product_id']]) }}">
                  <img src="{{ getThumbImage($item['image']) }}" class="img-fluid rounded" style="max-width:50px;">
                </a>
              </td>

              <td class="align-middle col-prod" style="font-family: 'Hind Siliguri', sans-serif;">
                <div class="d-flex flex-column">
                    <span class="fw-bold text-dark product-name-text" style="word-wrap: break-word; white-space: normal;" title="{{ $item['name'] }}">
                        {{ Str::words($item['name'], 2, '...') }}
                    </span>
                    
                    @if(isset($item['is_free_shipping']) && $item['is_free_shipping'])
                        <span class="badge bg-info mt-1" style="width: fit-content; font-size: 10px;">Free Ship</span>
                    @endif

                    <div class="d-flex flex-column gap-1">
                        @if(!empty($productSize))
                            @if(strtolower(trim($productSize)) === 'single')
                                <div><span class="product-badge badge-single">Single</span></div>
                            @else
                                <div><span class="product-badge badge-variable">Size: {{ $productSize }}</span></div>
                            @endif
                        @endif

                        @if(!empty($productColor))
                            <div>
                                <span class="product-badge badge-color">Color: {{ $productColor }}</span>
                            </div>
                        @endif
                    </div>
                </div>
              </td>

              <td class="align-middle col-price" style="font-family: 'Hind Siliguri', sans-serif;">
                {{ priceFormate($item['price']) }}
              </td>

              <td class="product-quantity text-center col-qty" data-title="Qty">
                <div class="pro-qty" data-segment="{{ request()->segment(1)}}" data-href="{{ route('front.carts.edit',[$key])}}">
                  <span class="dec qtybtn">-</span>
                  <input type="number" class="quantity-input" value="{{ $item['quantity'] }}" style="font-family: 'Hind Siliguri', sans-serif">
                  <span class="inc qtybtn">+</span>
                </div>
              </td>

              <td class="align-middle col-total" style="font-family: 'Hind Siliguri', sans-serif;">
                {{ priceFormate($price) }}
              </td>

              <td class="align-middle text-center col-action">
                 <button type="button" 
                         onclick="deleteCartItem('{{ route('front.carts.destroy', [$key]) }}')" 
                         class="btn btn-danger btn-sm" 
                         style="border-radius: 50px; padding: 4px 8px;">
                    <i class="fa fa-trash"></i>
                 </button>
              </td>

            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </article>

  <article class="card-body border-top bg-light rounded-bottom">
    <div class="d-flex justify-content-between mb-2" style="font-family: 'Hind Siliguri', sans-serif;">
      <span class="fs-4 fw-bold text-dark">Subtotal:</span>
      <span class="fs-4 fw-bold text-dark">{{ priceFormate($total) }}</span>
    </div>

    <div class="d-flex justify-content-between mb-2" style="font-family: 'Hind Siliguri', sans-serif;">
      <span class="fs-4 fw-bold text-dark">Delivery Charge:</span>
      <span class="text-danger delivery_charge fs-4 fw-bold text-dark" id="d_charge">৳0</span>
    </div>

    @php
        $hasCoupon = session()->has('coupon_discount') && session('coupon_discount') > 0;
    @endphp
    <div class="coupon_row justify-content-between mb-2 {{ $hasCoupon ? 'd-flex' : 'd-none' }}" style="font-family: 'Hind Siliguri', sans-serif;">
        <span class="fs-4 fw-bold text-success">Coupon Discount:</span>
        <span class="coupon_discount_display fs-4 fw-bold text-success">
            {{ $hasCoupon ? '- ' . priceFormate(session('coupon_discount')) : '' }}
        </span>
    </div>

    <div class="d-flex justify-content-between fw-bold fs-5 mt-3 pt-2 border-top" style="font-family: 'Hind Siliguri', sans-serif;">
      <span class="fs-3 fw-bold text-dark">Total:</span>
      <span class="total fs-3 fw-bold text-dark">
          {{ priceFormate($total - (session('coupon_discount') ?? 0)) }}
      </span>
    </div>
    
    <input type="hidden" value="{{ $total }}" id="subtotal">
    <input type="hidden" value="{{ $total }}" name="amount" id="amount">
  </article>
</aside>