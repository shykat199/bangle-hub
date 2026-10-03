{{-- resources/views/admin/orders/invoice.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order Invoice</title>

  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="{{ asset('frontend/css/vendor/bootstrap.min.css') }}">

  <style>
    :root{
      --ink:#111827; --muted:#6b7280; --border:#e5e7eb; --soft:#fafafa;
    }
    html, body{ color:var(--ink); font-size:14px; }
    .header-box{ background:var(--soft); border:1px solid var(--border); }
    .header span{ display:inline-block; font-weight:700; letter-spacing:.5px; padding:.35rem .75rem; }
    .t-right{ text-align:right; }
    .bold{ font-weight:700; }
    .mini-muted{ color:var(--muted); font-size:12px; }
    .invoice-card{ border:1px solid var(--border); border-radius:8px; padding:16px; margin:0 auto 18px; max-width:900px; }
    .logo-wrap{ display:flex; align-items:center; gap:10px; }
    .logo-wrap img{ height:28px; object-fit:contain; background:#f0f0f0; }
    .table thead th{ background:#f8f9fa; }
    .footer-line .signature-line{ height:1px; background:#000; width:220px; }
    .totals h6, .totals h5{ margin:0; }

    /* A4 print setup */
    @page { size: A4 portrait; margin: 14mm 12mm; }
    @media print{
      .no-print, .no-print * { display:none !important; }
      body{ -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .invoice-card{ border:0; padding:0; max-width:none; margin:0 0 14mm; }
    }
    @media (max-width:576px){
      .logo-wrap{ align-items:flex-end; }
      .logo-wrap img{ height:24px; }
      .header span{ padding:.25rem .5rem; }
      .table thead th, .table tbody td{ font-size:12px; }
    }
  </style>
</head>

<body>
  {{-- Top Print Button (screen only) --}}
  <div class="container py-3 no-print">
    <div class="d-flex justify-content-end">
      <button class="btn btn-secondary btn-sm" id="printBtn"><i class="fa fa-print"></i> Print Invoice</button>
    </div>
  </div>

  {{-- ===== Normalize: $items or $item/$order -> $orders (collection) ===== --}}
  @php
    $orders = collect();
    if (isset($items)) {
        $orders = collect($items);
    } elseif (isset($item)) {
        $orders = collect([$item]);
    } elseif (isset($order)) {
        $orders = collect([$order]);
    }
  @endphp

  @forelse($orders as $item)
    @php $subTotal = 0; @endphp

    <div class="invoice-card">
      {{-- Header / Logo --}}
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="logo-wrap">
          <img src="{{ getImage('uploads/img', $info->site_logo ?? '') }}" alt="logo">
          <div>
            <div class="bold">{{ $info->site_name ?? config('app.name') }}</div>
            <div class="mini-muted">{{ $info->address ?? '' }}</div>
          </div>
        </div>
        <div class="text-end">
          <div class="bold">Invoice</div>
          <div class="mini-muted">ID: #{{ $item->invoice_no }}</div>
        </div>
      </div>

      <div class="header-box rounded mb-3">
        <div class="header container py-2">
          <span>Order Summary</span>
        </div>
      </div>

      {{-- Bill To / Meta --}}
      <div class="row g-3 mb-3">
        <div class="col-lg-7">
          <h6 class="bold mb-2">Invoice To</h6>
          <div class="border rounded p-3">
            <div class="bold">{{ $item->first_name }} {{ $item->last_name }}</div>
            <div class="mini-muted">{{ $item->shipping_address }}</div>
            <div>
              <span class="bold">Phone:</span>
              <a href="tel:{{ $item->mobile }}">{{ $item->mobile }}</a>
            </div>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="border rounded p-3 h-100">
            <div class="d-flex justify-content-between"><span class="bold">Invoice</span><span>#{{ $item->invoice_no }}</span></div>
            <div class="d-flex justify-content-between"><span class="bold">Date</span><span>{{ dateFormate($item->date) }}</span></div>
            <div class="d-flex justify-content-between"><span class="bold">Created</span><span>{{ $item->created_at->diffForHumans() }}</span></div>
          </div>
        </div>
      </div>

      {{-- Items --}}
      <div class="table-responsive border rounded">
        <table class="table table-striped table-borderless mb-0">
          <thead>
            <tr>
              <th style="width:60px;">SL.</th>
              <th>Item Description</th>
              <th style="width:110px;">Color</th>
              <th style="width:110px;">Size</th>
              <th class="t-right" style="width:110px;">Price</th>
              <th class="t-right" style="width:90px;">Qty</th>
              <th class="t-right" style="width:130px;">Total</th>
            </tr>
          </thead>
          <tbody>
          @foreach($item->details as $idx => $detail)
            @php
              $var       = optional($detail->variation);
              // Unit price precedence: order_details.unit_price > variation.after_discount_price > variation.price
              $unitPrice = $detail->unit_price ?? ($var->after_discount_price ?? ($var->price ?? 0));
              $qty        = (int)($detail->quantity ?? 0);
              $rowTotal  = $unitPrice * $qty;
              $subTotal += $rowTotal;

              $colorLabel = optional($var->color)->name;
              $sizeLabel  = optional($var->size)->title;
              if($colorLabel === 'Default'){ $colorLabel = ''; }
              if($sizeLabel === 'free'){ $sizeLabel = ''; }

              $desc = trim( ($detail->product->name ?? '').' '.($detail->productsize->title ?? '') );
            @endphp
            <tr>
              <td>{{ $idx+1 }}</td>
              <td>{{ $desc }}</td>
              <td>{{ $colorLabel }}</td>
              <td>{{ $sizeLabel }}</td>
              <td class="t-right">{{ priceFormate($unitPrice) }}</td>
              <td class="t-right">{{ $qty }}</td>
              <td class="t-right">{{ priceFormate($rowTotal) }}</td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>

      {{-- Note + Totals --}}
      <div class="row mt-3 g-3 align-items-start">
        <div class="col-lg-6">
          @php $orderNote = trim($item->note ?? ''); @endphp
          @if($orderNote !== '')
            <div class="border rounded p-3">
              <div class="bold mb-1">Note</div>
              <div>{{ $orderNote }}</div>
            </div>
          @endif
        </div>
        
        {{-- ✅✅✅ BACKEND CALCULATION FIX START ✅✅✅ --}}
        <div class="col-lg-6">
          @php
            // ১. ডেলিভারি চার্জ
            $deliveryAmount = optional($item->delivery_charge)->amount ?? ($item->shipping_charge ?? 0);
            
            // ২. গ্র্যান্ড টোটাল (ডাটাবেসের final_amount থাকলে সেটা নিবে, না থাকলে ক্যালকুলেট করবে)
            $grandTotal = $item->final_amount ?? (($subTotal + $deliveryAmount) - ($item->discount ?? 0));

            // ৩. ডিসকাউন্ট বের করা (সাবটোটাল + ডেলিভারি - ফাইনাল এমাউন্ট)
            $payableBeforeDiscount = $subTotal + $deliveryAmount;
            $discountAmount = $payableBeforeDiscount - $grandTotal;

            // নেগেটিভ ভ্যালু এড়াতে
            if($discountAmount < 0) { $discountAmount = 0; }
          @endphp

          <div class="border rounded p-3 totals">
            <div class="d-flex justify-content-between">
              <h6>Sub Total</h6>
              <h6>{{ priceFormate($subTotal) }}</h6>
            </div>
            
            <div class="d-flex justify-content-between">
              <h6>Delivery Charge</h6>
              <h6>{{ priceFormate($deliveryAmount) }}</h6>
            </div>
            
            {{-- ডিসকাউন্ট অপশন যোগ করা হয়েছে --}}
            @if($discountAmount > 0)
            <div class="d-flex justify-content-between text-danger">
              <h6>Discount / Coupon</h6>
              <h6>- {{ priceFormate($discountAmount) }}</h6>
            </div>
            @endif

            <hr class="my-2">
            
            <div class="d-flex justify-content-between fw-bold">
              <h5>Total</h5>
              <h5>{{ priceFormate($grandTotal) }}</h5>
            </div>
          </div>
        </div>
        {{-- ✅✅✅ BACKEND CALCULATION FIX END ✅✅✅ --}}

      </div>

      {{-- Signature & Footer --}}
      <div class="row g-2 align-items-center mb-2">
        <div class="col-md-6">
          <div class="d-flex gap-2">
            <div class="bold">Phone:</div>
            <div>{{ $info->owner_phone ?? '' }}</div>
          </div>
        </div>
        <div class="col-md-6 text-md-end mini-muted">
          Company: {{ $info->site_name ?? config('app.name') }} | Address: {{ $info->address ?? '' }}
        </div>
      </div>
    </div>

    {{-- Page break between orders (print only) --}}
    @if(!$loop->last)
      <div style="break-after:page"></div>
    @endif
  @empty
    <div class="container">
      <div class="alert alert-warning">No order found.</div>
    </div>
  @endforelse

  <script>
    (function(){
      const btn = document.getElementById('printBtn');
      if(btn){ btn.addEventListener('click', function(){ window.print(); }); }
    })();
  </script>
</body>
</html>