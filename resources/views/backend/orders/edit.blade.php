@extends('backend.app')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
  :root{
    --bg:#f8fafc;
    --card:#ffffff;
    --muted:#6b7280;
    --text:#111827;
    --primary:#0ea5e9;
    --ring:0 0 0 .2rem rgba(14,165,233,.25);
    --border:#e5e7eb;
  }

  body{background:var(--bg)}

  .page-title-box h4{
    font-weight:700;
    color:var(--text);
  }

  .order-layout{
    max-width:1180px;
    margin:0 auto;
  }

  .order-card{
    border:0;
    border-radius:20px;
    box-shadow:0 10px 24px rgba(2,6,23,.06);
    overflow:hidden;
  }
  .order-card .card-body{
    background:linear-gradient(to bottom,#f9fafb,#ffffff);
    padding:20px 18px 22px;
  }
  @media (min-width:768px){
    .order-card .card-body{
      padding:26px 26px 26px;
    }
  }

  label.form-label{
    font-weight:600;
    color:var(--text);
    font-size:.8rem; 
  }

  .form-control,
  .form-select{
    border-radius:.7rem;
    padding:.55rem .8rem;
    border:1px solid #d1d5db;
    font-size:.84rem; 
  }
  .form-control:focus,
  .form-select:focus{
    box-shadow:var(--ring);
    border-color:#7dd3fc;
  }

  .select2-container{
    width:100% !important;
  }

  .search{
    position:relative;
  }
  .search input{
    height:46px;
    border-radius:999px;
    padding-left:40px;
    font-size: .84rem;
  }
  .search::before{
    content:'🔍';
    position:absolute;
    left:14px;
    top:50%;
    transform:translateY(-50%);
    font-size:13px;
    opacity:.65;
    pointer-events:none;
  }

  .note-muted{
    color:var(--muted);
    font-size:.74rem; 
  }

  .card{border:0;}

  .table-wrap{position:relative;margin-top:10px;}
  
  .table-responsive{
    border-radius:.9rem;
    background:var(--card);
    padding:6px;
    border:1px solid var(--border);
    overflow-x: hidden !important;
    width: 100%;
  }

  .responsive-table{
    width: 100%;
    table-layout: auto;
  }

  .table thead th{
    font-weight:700;
    color:#0f172a;
    font-size:.72rem; 
    border-bottom:1px solid #e5e7eb;
    white-space: normal;
  }
  
  .table td,.table th{
    vertical-align:middle;
    font-size:.8rem; 
    white-space: normal !important;
    word-wrap: break-word;
  }
  .table-light th{background:#f1f5f9}

  .responsive-table td[data-label="Product"],
  .responsive-table td[data-label="Product"] .prod-name{
    white-space:normal !important;
    word-break:break-word;
    overflow-wrap:anywhere;
    text-overflow:unset !important;
  }
  .responsive-table td[data-label="Product"]{max-width:100%;}

  .responsive-table img{
    border-radius:12px;
    box-shadow:0 4px 10px rgba(15,23,42,.15);
  }

  .quantity{
    max-width:90px;
  }

  .row_total{
    font-weight:600;
    color:#111827;
  }

  .sticky-actions{
    text-align:right;
    margin-top:16px;
  }
  .sticky-actions .btn{
    border-radius:999px;
    padding:.55rem 1.6rem;
    font-weight:600;
    letter-spacing:.03em;
    font-size: .84rem;
  }

  /* Free Shipping Alert Box */
  .free-shipping-alert {
      display: none;
      font-size: 0.75rem;
      color: #10b981;
      font-weight: bold;
      margin-top: 4px;
  }

  @media (max-width:768px){
    .table-responsive{
      padding:0;
      border:none;
      background:transparent;
      overflow-x: hidden !important;
    }
    .responsive-table{
      width: 100%;
    }
    .responsive-table thead{display:none;}
    .responsive-table tbody tr{
      display:block;
      margin:0 0 12px;
      border:1px solid #e5e7eb;
      border-radius:12px;
      background:#fff;
      padding:10px 12px;
      box-shadow:0 8px 18px rgba(2,6,23,.05);
    }
    .responsive-table tbody tr td{
      display:flex;
      justify-content:space-between;
      gap:10px;
      border:0 !important;
      padding:.25rem .1rem;
      align-items:flex-start;
      white-space: normal !important;
    }
    .responsive-table tbody tr td::before{
      content:attr(data-label);
      font-weight:600;
      color:var(--muted);
      min-width:35%;
      font-size:.74rem;
      padding-right:4px;
    }
    .responsive-table tbody tr td[data-label="Image"]{
      justify-content:flex-start;
    }
    .responsive-table tbody tr td[data-label="Image"]::before{
      content:"Image";
      min-width:auto;
      margin-right:8px;
    }

    .col-md-4, .col-md-3{margin-bottom:10px}

    .sticky-actions{
      position:sticky;
      bottom:0;
      z-index:30;
      background:#fff;
      padding:10px;
      border-top:1px solid #e5e7eb;
      box-shadow:0 -8px 18px rgba(2,6,23,.05);
      text-align:center;
    }
    .sticky-actions .btn{
      width:100%;
      height:46px;
      border-radius:.9rem;
    }
  }

  @media (max-width:576px){
    .page-title-box{
      padding-bottom:4px;
    }
  }
</style>
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="page-title-box">
      <div class="page-title-right">
        <ol class="breadcrumb m-0" style="font-size: .8rem;">
          <li class="breadcrumb-item"><a href="javascript:void(0)">SIS</a></li>
          <li class="breadcrumb-item"><a href="javascript:void(0)">CRM</a></li>
          <li class="breadcrumb-item active">Order Edit</li>
        </ol>
      </div>
      <h4 class="page-title">Order Edit</h4>
    </div>
  </div>
</div>

<div class="row order-layout">
  <div class="col-12">
    <div class="card order-card">
      <div class="card-body">
        <form method="POST" action="{{ route('admin.orders.update', $item->id) }}" id="ajax_form">
          @csrf
          @method('PUT')

          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Pick a Date</label>
              <input type="date" class="form-control" value="{{ $item->date }}" required name="date"/>
            </div>
            <div class="col-md-4">
              <label class="form-label">Reference Number</label>
              <input type="text" class="form-control" value="{{ $item->ref }}" name="ref"/>
            </div>
            <div class="col-md-4">
              <label class="form-label">Order Status</label>
              <select class="form-select" name="status">
                {{-- '' (All Order) is a filter label, not a real status — never offer it while editing --}}
                @foreach($status as $key=>$s)
                  @if($key === '') @continue @endif
                  <option value="{{ $key }}" {{ strcasecmp($key, trim($item->status ?? '')) === 0 ? 'selected':'' }}>{{ $s }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-md-8 mx-auto">
              <div class="search">
                <input type="text" id="search" class="form-control" placeholder="Search & add product…">
              </div>
              <div class="note-muted mt-1">
                Type at least 2 characters to search by product name or SKU.
              </div>
            </div>
          </div>

          <div class="table-wrap">
            <div class="table-responsive">
              <table class="table table-centered table-nowrap mb-0 responsive-table" id="product_table">
                <thead class="table-light">
                  <tr>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Variant</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody id="data">
                  @foreach($item->details as $line)
                    @php
                      // products-এ কোনো FK নেই, তাই কোনো প্রোডাক্ট ডিলিট হলে order_details-এ
                      // মৃত product_id থেকে যায় আর $line->product null আসে। আগে এখানেই পুরো
                      // অর্ডার edit পেজ 500 হয়ে যেত — অর্থাৎ ওই অর্ডার আর কখনো এডিট করা যেত না।
                      $product   = $line->product;
                      $variants  = $product?->variations ?? collect();
                      $selectedV = $line->variation;
                      $priceNow  = $line->unit_price;
                      $lineRawTotal = $priceNow * $line->quantity;
                    @endphp
                    <tr>
                      <td data-label="Image">
                        @php $__img = $product?->image ?? 'default-product.png'; @endphp
                        <img src="{{ function_exists('getImage') ? getImage('products', $__img) : asset('uploads/products/'.$__img) }}" height="50" width="50" alt="product"/>
                      </td>

                      <td data-label="Product" title="{{ $product?->name ?? 'Product Deleted' }}">
                        <div class="prod-name">{{ $product?->name ?? 'Product Deleted' }}</div>
                        @if($product?->sku)
                          <div class="text-muted small" style="font-size: .74rem;">SKU: {{ $product->sku }}</div>
                        @endif
                        
                        <!-- ✅ Free Shipping Existing Status -->
                        @if($product?->is_free_shipping == 1)
                            <span class="badge bg-success" style="font-size: 9px;"><i class="mdi mdi-truck-fast"></i> Free Shipping</span>
                        @endif
                        <input type="hidden" class="is_free_shipping" value="{{ $product?->is_free_shipping ?? 0 }}">
                      </td>

                      <td data-label="Variant">
                        @if($variants->count() > 0)
                          <select class="form-select form-select-sm variant-select" style="font-size: .8rem;" name="variant_display[]" data-line="{{ $line->id }}">
                            @foreach($variants as $v)
                              @php
                                $text = $v->display_title
                                        ?? trim(($v->size_label ?? $v->size ?? '') . (($v->color_label ?? $v->color ?? '') ? ' - ' . ($v->color_label ?? $v->color) : ''))
                                        ?: 'Default';
                                $vPrice = $v->discount_price ?? $v->price ?? $priceNow;
                              @endphp
                              <option
                                value="{{ $v->id }}"
                                data-price="{{ $vPrice }}"
                                data-rawprice="{{ $v->price ?? $priceNow }}"
                                data-stock="{{ $v->stocks->sum('quantity') ?? 0 }}"
                                {{ $selectedV && $selectedV->id === $v->id ? 'selected' : '' }}>
                                {{ $text }}
                              </option>
                            @endforeach
                          </select>
                        @else
                          <span class="text-muted" style="font-size: .8rem;">No Variants</span>
                        @endif

                        <input type="hidden" name="variation_id[]" class="hidden-variation-id" value="{{ $line->variation_id }}"/>
                        <input type="hidden" name="order_line_id[]" value="{{ $line->id }}"/>
                        <input type="hidden" name="product_id[]" value="{{ $line->product_id }}" required/>
                        <input type="hidden" name="is_stock" value="{{ $product?->is_stock ?? 0 }}">
                      </td>

                      <td data-label="Qty">
                        <input class="form-control quantity" name="quantity[]" type="number"
                               value="{{ $line->quantity }}" required min="1"
                               data-qty="{{ $line->quantity + ($selectedV? $selectedV->stocks->sum('quantity') : 0) }}"/>
                      </td>

                      <td data-label="Price">
                        <input class="form-control unit_price"
                               name="unit_price[]"
                               type="number"
                               step="0.01"
                               min="0"
                               value="{{ $priceNow }}" />
                      </td>

                      <td class="row_total" data-label="Total">
                        {{ number_format($lineRawTotal, 2, '.', '') }}
                      </td>

                      <td data-label="Action">
                        <a class="remove btn btn-sm btn-danger"><i class="mdi mdi-delete"></i></a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>

          <div class="row g-3 mt-3">
            <div class="col-md-4">
              <label class="form-label">Customer Name</label>
              <input type="text" class="form-control" value="{{ $item->first_name }}" name="first_name"/>
            </div>
            <div class="col-md-4">
              <label class="form-label">Customer Mobile</label>
              <input type="text" class="form-control" value="{{ $item->mobile }}" name="mobile"/>
            </div>
            <div class="col-md-4">
              <label class="form-label">Delivery Charge</label>
              <select class="form-select" name="delivery_charge_id" id="delevery_charge">
                <option value="" data-charge="0">Select One</option>
                @foreach($charges as $charge)
                  <option value="{{ $charge->id }}" {{ $charge->id==$item->delivery_charge_id ?'selected':'' }} data-charge="{{ $charge->amount }}">{{ $charge->title }}</option>
                @endforeach
              </select>
              <!-- ✅ Free Shipping Indicator -->
              <div class="free-shipping-alert" id="free_shipping_notice"><i class="mdi mdi-check-circle"></i> Free Shipping Applied (Charge: ৳0)</div>
            </div>

            <div class="col-12">
              <label class="form-label">Customer Address</label>
              <textarea rows="3" name="shipping_address" id="shipping_address" class="form-control" style="font-size: .84rem;">{{ $item->shipping_address }}</textarea>
            </div>

            <div class="col-md-4 d-none">
              <label class="form-label">Courier Tracking ID</label>
              <input type="text" class="form-control" value="{{ $item->courier_tracking_id }}" name="courier_tracking_id"/>
            </div>

            <div class="col-md-4">
              <label class="form-label">Courier</label>
              <select class="form-select" name="courier_id" id="courier_select">
                <option value="" data-charge="0">Select One</option>
                @foreach($couriers as $courier)
                  <option value="{{ $courier->id }}" {{ $courier->id==$item->courier_id ? 'selected' : '' }}>{{ $courier->name }}</option>
                @endforeach
              </select>
            </div>

            @php
                $itemsTotal = 0;
                foreach($item->details as $line) {
                    $itemsTotal += ($line->unit_price * $line->quantity);
                }
                $actual_coupon_discount = ($itemsTotal + $item->shipping_charge) - $item->final_amount;
                if($actual_coupon_discount < 0) $actual_coupon_discount = 0;
            @endphp

            <div class="col-md-2">
              <label class="form-label text-danger">Discount (৳)</label>
              <input type="number" class="form-control" value="{{ round($actual_coupon_discount, 2) }}" name="discount" id="order_discount" step="0.01"/>
            </div>
            <div class="col-md-2">
              <label class="form-label">Total</label>
              <input type="text" class="form-control fw-bold" value="{{ $item->final_amount }}" name="final_amount" id="purchase_total" readonly/>
              <input type="hidden" value="{{ $item->shipping_charge }}" name="shipping_charge" id="shipping_charge"/>
            </div>
          </div>

          @php
              $paymentMethod = $item->payment_method ?? '';
              $transactionId = $item->transaction_id ?? '';
              $paymentRecord = \App\Models\OrderPayment::where('order_id', $item->id)->latest()->first();
              
              if ($paymentRecord && str_contains(strtolower($paymentRecord->payment_method), 'bkash')) {
                  $paymentMethod = 'bKash';
                  $transactionId = $paymentRecord->transaction_id;
              }
          @endphp

          @if($paymentMethod != 'Cash on Delivery' && $paymentMethod != 'online' && $paymentMethod != null)
          <div class="row mt-3">
            <div class="col-12">
              <div class="alert alert-info mb-0">
                <h6 class="fw-bold mb-2" style="font-size: .84rem;">Payment Info:</h6>
                <table class="table table-sm table-borderless mb-0" style="font-size: .8rem;">
                  <tr>
                    <td width="20%"><strong>Method:</strong></td>
                    <td>{{ $paymentMethod }}</td>
                  </tr>
                  @if($item->sender_number)
                  <tr>
                    <td><strong>Sender Number:</strong></td>
                    <td>{{ $item->sender_number }}</td>
                  </tr>
                  @endif
                  <tr>
                    <td><strong>TrxID:</strong></td>
                    <td><span class="badge bg-success">{{ $transactionId }}</span></td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
          @endif

          <div class="row g-3 for_redx {{ $item->courier_id != 1 ? 'd-none' : '' }} mt-2">
            <h5 class="text-danger mt-3" style="font-size: .9rem;">These fields only for Redx Courier Service</h5>
            <div class="col-md-3">
              <label class="form-label">Choose Area</label>
              <select class="form-control select2" id="area_select">
                <option value="">Select One</option>
                @if($areas!==null)
                  @foreach($areas as $area)
                    <option value="{{ $area['id'] }}" {{ $item->area_id ==  $area['id'] ? 'selected' : '' }}>{{ $area['name'] }}</option>
                  @endforeach
                @endif
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Area ID</label>
              <input type="text" readonly class="form-control" id="area_id" name="redx_area_id" value="{{ $item->area_id }}"/>
            </div>
            <div class="col-md-3">
              <label class="form-label">Area Name</label>
              <input type="text" readonly class="form-control" id="area_name" name="area_name" value="{{ $item->area_name }}"/>
            </div>
          </div>

          <div class="row g-3 for_pathao {{ $item->courier_id != 2 ? 'd-none' : '' }} mt-2">
            <h5 class="text-danger mt-3" style="font-size: .9rem;">These fields only for Pathao Courier Service</h5>
            <div class="col-md-3">
              <label class="form-label">Choose City</label>
              <select class="form-select" id="city_select" name="city">
                <option value="">Select One</option>
                @foreach($cities as $city)
                  <option value="{{ $city['city_id'] }}" {{ $item->city ==  $city['city_id'] ? 'selected' : '' }}>{{ $city['city_name'] }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Choose Zone</label>
              <select class="form-select" id="zone_select" name="state">
                <option value="{{ $item->state }}">Select One</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Choose Area</label>
              <select class="form-select" id="pathao_area_id" name="pathao_area_id">
                <option value="{{ $item->area_id }}">Select One</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Item Weight</label>
              <input type="number" class="form-control" id="weight" step="0.5" min="0.5" max="10" name="weight" value="{{ $item->weight != null ? $item->weight : '0.5' }}"/>
            </div>
          </div>

          <div class="row mt-2">
            <div class="col-12">
              <label class="form-label">Note</label>
              <textarea class="form-control" name="note" placeholder="note" style="font-size: .84rem;">{{ $item->note }}</textarea>
            </div>
          </div>

          <div class="sticky-actions">
            <button class="btn btn-success" type="submit">Update</button>
          </div>

          <div class="table-wrap mt-3">
            <div class="table-responsive">
              <table class="table table-centered table-nowrap mb-0 responsive-table" id="order_by_table">
                <thead class="table-light">
                  <tr>
                    <th>Order Id</th>
                    <th>Product</th>
                    <th>Customer</th>
                    <th>IP Address</th>
                    <th>Status</th>
                    <th>Assign User</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($orderbyNumber as $orbynum)
                    <tr>
                      <td data-label="Order Id">{{ $orbynum->id }}</td>
                      <td data-label="Product">
                        <?php 
                          foreach($orbynum->details as $l){
                            if(!isset($l->product->name) || $l->product->name == ''){
                              echo '<span style="color:red">Unavailable</span>';
                            }else{
                              echo e($l->product->name);
                            }
                          }
                        ?>
                      </td>
                      <td data-label="Customer">{{ $orbynum->first_name }} {{ $orbynum->last_name }}</td>
                      <td data-label="IP Address">{{ $orbynum->ip_address }}</td>
                      <td data-label="Status">{{ $orbynum->status }}</td>
                      <td data-label="Assign User">{{ $orbynum->assign ? $orbynum->assign->username : '' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(function(){
  $('.select2').select2({width:'resolve'});

  function bestPrice(obj){
    const cands = [
      obj?.discount_price,
      obj?.after_discount_price,
      obj?.after_discount,
      obj?.price,
      obj?.sell_price,
      obj?.regular_price
    ];
    for (const v of cands){
      const n = parseFloat(v);
      if(!isNaN(n) && n > 0) return n;
    }
    return 0;
  }

  function escapeHtml(str){
    return String(str ?? '')
      .replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;')
      .replaceAll('"','&quot;').replaceAll("'","&#39;");
  }

  $("#search").autocomplete({
    minLength: 2,
    source: function(request, response){
      $.getJSON("{{ route('admin.products.search') }}", { q: request.term }, function(list){
        response($.map(list, function(p){
          return {
            label: (p.sku ? '['+p.sku+'] ' : '') + p.name,
            value: p.name,
            data : p
          };
        }));
      });
    },
    select: function(e, ui){
      e.preventDefault();
      addProductRow(ui.item.data);
      $(this).val('');
    }
  });

  function addProductRow(p){
    const hasVar     = Array.isArray(p.variations) && p.variations.length > 0;
    const defPrice   = hasVar ? bestPrice(p.variations[0]) : bestPrice(p);
    const firstVarId = hasVar ? (p.variations[0].id || '') : '';
    const imgUrl     = p.image_url || p.image || '';

    // ✅ Free Shipping Badge and Hidden Input Logic
    const isFreeShipping = (p.is_free_shipping == 1) ? 1 : 0;
    const freeBadge = isFreeShipping ? `<span class="badge bg-success mt-1" style="font-size: 9px;"><i class="mdi mdi-truck-fast"></i> Free Shipping</span>` : ``;

    const varSelectHtml = hasVar
      ? `<select class="form-select form-select-sm variant-select" style="font-size:.8rem;" name="variant_display[]">
           ${p.variations.map(v => {
             const vPrice = bestPrice(v);
             const stock  = parseInt(v.stock ?? v.stocks ?? 0, 10) || 0;
             const title  = v.title || v.text || `${v.size || v.size_label || ''}${(v.color || v.color_label) ? ' - ' + (v.color || v.color_label) : ''}` || 'Default';
             return `
               <option value="${v.id}"
                       data-price="${vPrice}"
                       data-rawprice="${v.price ?? v.sell_price ?? v.regular_price ?? 0}"
                       data-stock="${stock}">
                 ${escapeHtml(title)}
               </option>`;
           }).join('')}
         </select>`
      : `<span class="text-muted" style="font-size:.8rem;">No Variants</span>`;

    const row = $(`
      <tr>
        <td data-label="Image">
          <img src="${imgUrl}" height="50" width="50" alt="product"/>
        </td>

        <td data-label="Product" title="${escapeHtml(p.name)}">
          <div class="prod-name">${escapeHtml(p.name)}</div>
          ${p.sku ? `<div class="text-muted small" style="font-size:.74rem;">SKU: ${escapeHtml(p.sku)}</div>` : ``}
          ${freeBadge}
          <input type="hidden" class="is_free_shipping" value="${isFreeShipping}">
        </td>

        <td data-label="Variant">
          ${varSelectHtml}
          <input type="hidden" name="variation_id[]" class="hidden-variation-id" value="${firstVarId}"/>
          <input type="hidden" name="order_line_id[]" value=""/>
          <input type="hidden" name="product_id[]" value="${p.id}" required/>
          <input type="hidden" name="is_stock" value="1">
        </td>

        <td data-label="Qty">
          <input class="form-control quantity" name="quantity[]" type="number" value="1" required min="1"/>
        </td>

        <td data-label="Price">
          <input class="form-control unit_price"
                 name="unit_price[]"
                 type="number"
                 step="0.01"
                 min="0"
                 value="${defPrice}"/>
        </td>

        <td class="row_total" data-label="Total">${(+defPrice).toFixed(2)}</td>

        <td data-label="Action">
          <a class="remove btn btn-sm btn-danger"><i class="mdi mdi-delete"></i></a>
        </td>
      </tr>
    `);

    $('#product_table #data').prepend(row);
    recalcTotal();
  }

  $('#product_table').on('change', '.variant-select', function(){
    const $row  = $(this).closest('tr');
    const $opt  = $(this).find('option:selected');
    const varId = $(this).val();
    const price = parseFloat($opt.data('price'));

    $row.find('.hidden-variation-id').val(varId);

    if(!isNaN(price)){
      $row.find('.unit_price').val(price);
    }

    recalcRow($row);
    recalcTotal();
  });

  $('#product_table').on('input', '.quantity', function(){
    const $row = $(this).closest('tr');
    recalcRow($row);
    recalcTotal();
  });

  $('#product_table').on('input', '.unit_price', function(){
    const $row = $(this).closest('tr');
    recalcRow($row);
    recalcTotal();
  });

  $('#product_table').on('click', '.remove', function(e){
    e.preventDefault();
    $(this).closest('tr').remove();
    recalcTotal();
  });

  function recalcRow($row){
    const qty   = parseFloat($row.find('.quantity').val() || 0);
    const price = parseFloat($row.find('.unit_price').val() || 0);

    let subtotal = (qty * price);
    if (isNaN(subtotal) || subtotal < 0) subtotal = 0;

    $row.find('.row_total').text(subtotal.toFixed(2));
  }

  // ✅ Updated recalcTotal logic for Free Shipping
  function recalcTotal(){
    let itemsTotal = 0;
    let hasFreeShippingProduct = false;

    $('#product_table tbody tr').each(function(){
      const $row = $(this);
      const v = parseFloat($row.find('.row_total').text() || 0);
      if(!isNaN(v)) itemsTotal += v;

      // Check if this row has free shipping active
      if($row.find('.is_free_shipping').val() == '1'){
          hasFreeShippingProduct = true;
      }
    });

    let delivery = parseFloat($('#delevery_charge option:selected').data('charge') || 0);

    // If free shipping exists, force delivery to 0 and show alert
    if(hasFreeShippingProduct){
        delivery = 0;
        $('#delevery_charge').css('border', '1px solid #10b981'); // Green outline
        $('#free_shipping_notice').show();
    } else {
        $('#delevery_charge').css('border', '1px solid #d1d5db'); // Normal outline
        $('#free_shipping_notice').hide();
    }

    $('#shipping_charge').val(isNaN(delivery) ? 0 : delivery);

    const discount = parseFloat($('#order_discount').val() || 0);

    let grand = itemsTotal + (isNaN(delivery) ? 0 : delivery) - discount;
    if(grand < 0) grand = 0;

    $('#purchase_total').val(grand.toFixed(2));
  }

  $('#delevery_charge').on('change', recalcTotal);
  $('#order_discount').on('input', recalcTotal);

  function toggleCourierFields(){
    const $sel  = $('#courier_select');
    const val   = $sel.val();
    const text  = ($sel.find('option:selected').text() || '').toLowerCase().trim();

    const isRedx   = (val == '1') || text.includes('redx');
    const isPathao = (val == '2') || text.includes('pathao');

    if(isRedx){
      $('.for_redx').removeClass('d-none');
      $('.for_pathao').addClass('d-none');
    }else if(isPathao){
      $('.for_pathao').removeClass('d-none');
      $('.for_redx').addClass('d-none');
    }else{
      $('.for_redx, .for_pathao').addClass('d-none');
    }
  }

  $('#courier_select').on('change', toggleCourierFields);

  $(document).on('change', '#area_select', function(){
    const id   = $(this).val() || '';
    const name = $(this).find('option:selected').text() || '';
    $('#area_id').val(id);
    $('#area_name').val(name);
  });

  function updateAddressDropdowns(address) {
    if(address.length < 3) return;

    $.ajax({
      url: "{{ route('admin.fetch.address.details') }}",
      type: "POST",
      data: {
        _token: "{{ csrf_token() }}",
        address: address
      },
      success: function(res) {
        if(res.city_id) {
          $("#city_select").val(res.city_id).trigger("change");

          $.get("{{ url('/admin/zones-by-city') }}/" + res.city_id, function(zoneRes) {
            let zones = zoneRes.zones || [];
            let $zone = $("#zone_select");
            $zone.empty().append('<option value="">Select One</option>');
            zones.forEach(function(z) {
              $zone.append('<option value="'+z.zone_id+'">'+z.zone_name+'</option>');
            });

            if(res.zone_id) {
              $zone.val(res.zone_id).trigger("change");

              $.get("{{ url('/admin/areas-by-zone') }}/" + res.zone_id, function(areaRes) {
                let areas = areaRes.areas || [];
                let $area = $("#pathao_area_id");
                $area.empty().append('<option value="">Select One</option>');
                areas.forEach(function(a) {
                  $area.append('<option value="'+a.area_id+'">'+a.area_name+'</option>');
                });

                if(res.area_id) {
                  $area.val(res.area_id).trigger("change");
                }
              });
            }
          });
        }
      },
      error: function(err) {
        console.error("Fetch address details failed:", err);
      }
    });
  }

  $(document).on("change", "#shipping_address", function() {
    updateAddressDropdowns($(this).val());
  });

  $(document).on('change', '#city_select', function(){
    let city = $(this).val();
    var url = "{{ route('admin.zonesByCity', ':city') }}";
    url = url.replace(':city', city);

    $('#zone_select').html("<option value=''>Select One</option>");
    $('#pathao_area_id').html("<option value=''>Select One</option>");

    if(!city) return;

    $.ajax({
      url,
      type: 'GET',
      dataType: "json",
      success: function(res){
        if(res.success){
          let html = "<option value=''>Select One</option>";
          for(let i = 0; i < res.zones.length; i++){
            html += "<option value='"+res.zones[i].zone_id+"' >"+res.zones[i].zone_name+"</option>";
          }
          $('#zone_select').html(html);
        }
      }
    });
  });

  $(document).on('change', '#zone_select', function(){
    let zone = $(this).val();
    var url = "{{ route('admin.areasByZone', ':zone') }}";
    url = url.replace(':zone', zone);

    $('#pathao_area_id').html("<option value=''>Select One</option>");
    if(!zone) return;

    $.ajax({
      url,
      type: 'GET',
      dataType: "json",
      success: function(res){
        if(res.success){
          let html = "<option value=''>Select One</option>";
          for(let j = 0; j < res.areas.length; j++){
            html += "<option value='"+res.areas[j].area_id+"' >"+res.areas[j].area_name+"</option>";
          }
          $('#pathao_area_id').html(html);
        }
      }
    });
  });

  (function init(){
    toggleCourierFields();

    const addr = $("#shipping_address").val();
    if(addr && addr.length > 3){
      updateAddressDropdowns(addr);
    }

    const areaIdSel = $('#area_select').val();
    if(areaIdSel){
      $('#area_id').val(areaIdSel);
      $('#area_name').val($('#area_select option:selected').text());
    }

    recalcTotal();
  })();

});
</script>
@endpush