@php
    $invoiceType = (int)($info->invoice_type ?? 1);
@endphp

@if($invoiceType === 1)
    {{-- ======================================================
         DESIGN 1: STANDARD A4 INVOICE (Classic)
         ====================================================== --}}
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
        :root{ --ink:#111827; --muted:#6b7280; --border:#e5e7eb; --soft:#fafafa; }
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
        @page { size: A4 portrait; margin: 14mm 12mm; }
        @media print{
          .no-print, .no-print * { display:none !important; }
          body{ -webkit-print-color-adjust: exact; print-color-adjust: exact; }
          .invoice-card{ border:0; padding:0; max-width: none; margin-bottom: 20px; }
        }
      </style>
    </head>
    <body>
      <div class="container py-3 no-print">
        <div class="d-flex justify-content-center">
          <button class="btn btn-secondary" onclick="window.print()"><i class="fa fa-print"></i> Print Design 1</button>
        </div>
      </div>

      @foreach($items as $item)
        @php $subTotal = 0; @endphp
        <div class="invoice-card">
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
              <div class="mt-2">
                  <img src="https://bwipjs-api.metafloor.com/?bcid=code128&text={{ $item->invoice_no }}&scale=2&height=10&includetext=true&textxalign=center" alt="Barcode" style="height: 35px; max-width: 150px;">
              </div>
            </div>
          </div>

          <div class="header-box rounded mb-3">
            <div class="header container py-2"><span class="px-3">Order Summary</span></div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-7">
              <h6 class="bold mb-2">Invoice To</h6>
              <div class="border rounded p-3">
                <div class="bold">{{ $item->first_name }} {{ $item->last_name }}</div>
                <div class="mini-muted">{{ $item->shipping_address }}</div>
                <div><span class="bold">Phone:</span> {{ $item->mobile }}</div>
              </div>
            </div>
            <div class="col-5">
              <div class="border rounded p-3 h-100">
                <div class="d-flex justify-content-between mb-1"><span class="bold">Invoice</span><span>#{{ $item->invoice_no }}</span></div>
                <div class="d-flex justify-content-between mb-1"><span class="bold">Date</span><span>{{ dateFormate($item->date) }}</span></div>
                
                <div class="border-top pt-2 mt-2">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="bold">Payment Method</span>
                        <span class="badge bg-light text-dark border">{{ $item->payment_method ?? 'COD' }}</span>
                    </div>
                    @if($item->payment_status)
                    <div class="d-flex justify-content-between mb-1">
                        <span class="bold">Status</span>
                        <span class="text-uppercase fw-bold text-success">{{ $item->payment_status }}</span>
                    </div>
                    @endif
                    @if($item->payment_method != 'Cash on Delivery' && $item->payment_method != 'online' && $item->payment_method != null)
                        <div class="d-flex justify-content-between mb-1">
                            <span class="bold">TrxID</span>
                            <span class="bold" style="color: #00276C;">{{ $item->transaction_id }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="bold">Sender A/C</span>
                            <span>{{ $item->sender_number }}</span>
                        </div>
                    @endif
                </div>
              </div>
            </div>
          </div>

          <div class="table-responsive border rounded">
            <table class="table table-striped table-borderless mb-0">
              <thead>
                <tr>
                  <th>SL.</th>
                  <th>Item</th>
                  <th>Color</th>
                  <th>Size</th>
                  <th class="t-right">Price</th>
                  <th class="t-right">Qty</th>
                  <th class="t-right">Total</th>
                </tr>
              </thead>
              <tbody>
              @foreach($item->details as $idx => $detail)
                @php
                  $unitPrice = $detail->unit_price ?? 0;
                  $qty = (int)($detail->quantity ?? 1);
                  $rowTotal = $unitPrice * $qty;
                  $subTotal += $rowTotal;
                @endphp
                <tr>
                  <td>{{ $idx+1 }}</td>
                  <td>{{ $detail->product->name ?? '' }}</td>
                  <td>{{ (optional($detail->variation?->color)->name !== 'Default') ? optional($detail->variation?->color)->name : '' }}</td>
                  <td>{{ (optional($detail->variation?->size)->title !== 'free') ? optional($detail->variation?->size)->title : '' }}</td>
                  <td class="t-right">{{ priceFormate($unitPrice) }}</td>
                  <td class="t-right">{{ $qty }}</td>
                  <td class="t-right">{{ priceFormate($rowTotal) }}</td>
                </tr>
              @endforeach
              </tbody>
            </table>
          </div>

          <div class="row mt-3 g-3">
            <div class="col-6">
              @if($item->note)<div class="border rounded p-3 mb-2"><strong>Note:</strong> {{ $item->note }}</div>@endif
              <div class="mt-2 text-center text-md-start">
                  <img src="https://api.qrserver.com/v1/create-qr-code/?size=70x70&data={{ $item->mobile }}" alt="QR Code" style="height: 70px;">
              </div>
            </div>
            <div class="col-6">
              @php
                $delivery = (float)($item->shipping_charge ?? 0);
                $grandTotal = (float)($item->final_amount ?? ($subTotal + $delivery));
                
                // Actual Coupon Discount Calculation
                $discount = ($subTotal + $delivery) - $grandTotal;
                if ($discount < 0) $discount = 0;
              @endphp
              <div class="border rounded p-3">
                <div class="d-flex justify-content-between"><span>Sub Total</span><span>{{ priceFormate($subTotal) }}</span></div>
                <div class="d-flex justify-content-between"><span>Delivery</span><span>{{ priceFormate($delivery) }}</span></div>
                
                @if($discount > 0)
                <div class="d-flex justify-content-between text-danger"><span>Discount</span><span>-{{ priceFormate($discount) }}</span></div>
                @endif
                
                <hr class="my-1">
                <div class="d-flex justify-content-between bold"><h5>Total</h5><h5>{{ priceFormate($grandTotal) }}</h5></div>
              </div>
            </div>
          </div>
          <div class="footer-line my-4 pe-3 d-flex justify-content-end">
            <div class="signature text-center"><div class="signature-line"></div><h6 class="mt-2">Authorised Sign</h6></div>
          </div>
        </div>
        <div style="page-break-after:always;"></div>
      @endforeach
    </body>
    </html>

@elseif($invoiceType === 2)
    {{-- ======================================================
         DESIGN 2: 75mm x 100mm STICKER INVOICE
         ====================================================== --}}
    <!DOCTYPE html>
    <html lang="bn">
    <head>
      <meta charset="UTF-8">
      <title>Order Sticker</title>
      <style>
        *{box-sizing:border-box; margin:0; padding:0;}
        html,body{ color:#000; font-family:Arial, sans-serif; font-weight:600; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
        @page{ size:75mm 100mm; margin:0 }
        .no-print{display:flex;justify-content:center;margin:10px auto;}
        @media print{.no-print{display:none!important}}
        .label{ width:75mm; height:100mm; padding:2mm; margin:0 auto; background:#fff; border:1.2px solid #000; page-break-after:always; display:flex; flex-direction:column; overflow:hidden; }
        .sf-top{ display:flex; flex-direction:column; align-items:center; gap:2px; border-bottom:2px solid #000; padding-bottom:3px; }
        .sf-logo{ height:32px; max-width:60%; object-fit:contain; }
        .sf-hotline{ font-size:9.5px; font-weight:700; }
        .sf-barcode{ text-align:center; padding:3px 0; border-bottom:1.2px dashed #000; }
        .sf-barcode img{ height:30px; width:88%; object-fit:contain; }
        .sf-barcode .cid{ font-size:11px; font-weight:900; letter-spacing:1.5px; }
        .sf-sec{ padding:3px 1px; border-bottom:1.2px dashed #000; font-size:9.5px; line-height:1.35; }
        .sf-sec .lbl{ font-size:7px; font-weight:900; text-transform:uppercase; letter-spacing:.5px; }
        .sf-name{ font-size:11.5px; font-weight:900; }
        .sf-phone{ font-size:11.5px; font-weight:900; }
        .sf-items{ width:100%; border-collapse:collapse; font-size:8.5px; margin-top:2px; }
        .sf-items th, .sf-items td{ border:1px solid #000; padding:2px 3px; }
        .sf-items th{ background:#000 !important; color:#fff !important; text-align:left; font-size:8px; }
        .sf-items .c{ text-align:center; }
        .sf-items .r{ text-align:right; }
        .sf-totals{ font-size:9.5px; padding:3px 2px; border-bottom:1.2px dashed #000; }
        .sf-totals .row{ display:flex; justify-content:space-between; line-height:1.4; }
        .sf-cod{ margin-top:auto; border:2px solid #000; text-align:center; padding:2px; }
        .sf-cod .k{ font-size:8.5px; font-weight:900; text-transform:uppercase; letter-spacing:.5px; }
        .sf-cod .v{ font-size:23px; font-weight:900; line-height:1.1; }
        .sf-note{ font-size:8.5px; padding:2px 1px; border-bottom:1.2px dashed #000; }
        .sf-foot{ text-align:center; font-size:8px; font-weight:700; padding-top:3px; }
      </style>
    </head>
    <body style="background:#e9e9e9;">
      <div class="no-print"><button onclick="window.print()" style="padding:8px 15px; background:#000; color:#fff; border:none; border-radius:4px; cursor:pointer;">🖨️ Print Sticker (Steadfast Style)</button></div>
      @foreach($items as $item)
        @php
          $st = 0;
          foreach($item->details as $l){ $st += ($l->unit_price ?? 0) * ($l->quantity ?? 1); }
          $dc = (float)($item->shipping_charge ?? 0);
          $grand = (float)($item->final_amount ?? ($st + $dc));
          $disc = ($st + $dc) - $grand; if ($disc < 0) $disc = 0;
          $trackId = $item->courier_tracking_id ?: $item->invoice_no;
        @endphp
      <div class="label">
        <div class="sf-top">
          <img class="sf-logo" src="{{ getImage('uploads/img', $info->site_logo) }}" alt="logo">
          <div class="sf-hotline">Hotline: {{ $info->owner_phone }}</div>
        </div>

        <div class="sf-barcode">
          <img src="https://bwipjs-api.metafloor.com/?bcid=code128&text={{ $trackId }}&scale=3&height=12&includetext=false" alt="Barcode">
          <div class="cid">{{ $trackId }}</div>
        </div>

        <div class="sf-sec">
          <span class="lbl">Recipient</span>
          <div class="sf-name">{{ trim(($item->first_name ?? '').' '.($item->last_name ?? '')) }}</div>
          <div class="sf-phone">{{ $item->mobile }}</div>
          <div style="font-weight:600">{{ $item->shipping_address }}</div>
        </div>

        <div class="sf-sec">
          <span class="lbl">Invoice #{{ $item->invoice_no }} &nbsp;|&nbsp; {{ dateFormate($item->date) }} &nbsp;|&nbsp; Weight: {{ $item->weight ?: '0.5' }} kg</span>
          <table class="sf-items">
            <thead><tr><th>Product</th><th class="c">Price</th><th class="c">Qty</th><th class="r">Total</th></tr></thead>
            <tbody>
              @foreach($item->details as $line)
                @php $rt = ($line->unit_price ?? 0) * ($line->quantity ?? 1); @endphp
                <tr>
                  <td>{{ \Illuminate\Support\Str::limit($line->product->name ?? '', 20) }}</td>
                  <td class="c">{{ number_format($line->unit_price ?? 0, 0) }}</td>
                  <td class="c">{{ $line->quantity }}</td>
                  <td class="r">{{ number_format($rt, 0) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="sf-totals">
          <div class="row"><span>Sub Total</span><span>{{ number_format($st, 0) }}</span></div>
          <div class="row"><span>Delivery Charge</span><span>{{ number_format($dc, 0) }}</span></div>
          @if($disc > 0)
          <div class="row"><span>Discount (Coupon)</span><span>-{{ number_format($disc, 0) }}</span></div>
          @endif
        </div>

        @if($item->note)
        <div class="sf-note"><b>Note:</b> {{ \Illuminate\Support\Str::limit($item->note, 55) }}</div>
        @endif

        <div class="sf-cod">
          <div class="k">Collectable Amount (COD)</div>
          <div class="v">&#2547; {{ number_format($grand, 0) }}</div>
        </div>

        <div class="sf-foot">Delivery all over Bangladesh &middot; Thank you for your order</div>
      </div>
      @endforeach
    </body>
    </html>

@elseif($invoiceType === 3)
    {{-- ======================================================
         DESIGN 3: PREMIUM CORPORATE A4 INVOICE
         ====================================================== --}}
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Premium Corporate Invoice</title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap');
            body { font-family: 'Outfit', sans-serif; background-color: #f1f5f9; margin: 0; padding: 20px; color: #1e293b; }
            .no-print { text-align: center; margin-bottom: 20px; }
            .btn-print { background: #3b82f6; color: #fff; border: none; padding: 12px 25px; font-size: 16px; border-radius: 8px; cursor: pointer; font-weight: 600; box-shadow: 0 4px 6px rgba(59,130,246,0.2); }
            
            .invoice-wrapper { max-width: 850px; margin: 0 auto 30px; background: #fff; box-shadow: 0 10px 40px rgba(0,0,0,0.08); border-radius: 12px; overflow: hidden; page-break-after: always; position: relative; }
            .invoice-wrapper::before { content: ''; display: block; height: 8px; width: 100%; background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899); }
            .inv-header { padding: 40px 50px 20px; display: flex; justify-content: space-between; align-items: flex-start; }
            .inv-header-left img { max-height: 50px; margin-bottom: 10px; }
            .inv-header-left h2 { margin: 0 0 5px; font-size: 22px; font-weight: 800; color: #0f172a; }
            .inv-header-left p { margin: 2px 0; font-size: 13px; color: #64748b; }
            .inv-header-right { text-align: right; }
            .inv-header-right h1 { margin: 0 0 5px; font-size: 36px; font-weight: 800; color: #3b82f6; letter-spacing: 2px; text-transform: uppercase; }
            .inv-header-right .inv-number { font-size: 16px; font-weight: 600; color: #475569; background: #f1f5f9; padding: 4px 12px; border-radius: 6px; display: inline-block; margin-bottom: 15px;}
            .inv-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; padding: 0 50px 30px; margin-top: 20px; }
            .info-box { background: #f8fafc; padding: 20px; border-radius: 10px; border: 1px solid #e2e8f0; }
            .info-box h4 { margin: 0 0 10px; font-size: 12px; text-transform: uppercase; color: #94a3b8; letter-spacing: 1px; }
            .info-box strong { font-size: 16px; color: #0f172a; display: block; margin-bottom: 5px; }
            .info-box p { margin: 3px 0; font-size: 14px; color: #475569; }
            .stamp { position: absolute; top: 150px; right: 50px; font-size: 40px; font-weight: 800; text-transform: uppercase; border: 4px solid; padding: 5px 20px; border-radius: 10px; transform: rotate(-15deg); opacity: 0.15; z-index: 0; pointer-events: none; }
            .stamp.paid { color: #10b981; border-color: #10b981; }
            .stamp.unpaid { color: #ef4444; border-color: #ef4444; }
            .inv-table-wrapper { padding: 0 50px 30px; position: relative; z-index: 1; }
            .prem-table { width: 100%; border-collapse: collapse; }
            .prem-table th { background: #1e293b; color: #fff; padding: 14px 15px; font-size: 13px; font-weight: 600; text-transform: uppercase; text-align: left; }
            .prem-table th:first-child { border-top-left-radius: 8px; border-bottom-left-radius: 8px; }
            .prem-table th:last-child { border-top-right-radius: 8px; border-bottom-right-radius: 8px; }
            .prem-table td { padding: 15px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #334155; vertical-align: middle; }
            .text-center { text-align: center !important; }
            .text-right { text-align: right !important; }
            .inv-summary-wrapper { display: flex; justify-content: flex-end; padding: 0 50px 40px; align-items: flex-end; }
            .inv-summary { width: 350px; }
            .inv-summary table { width: 100%; border-collapse: collapse; }
            .inv-summary td { padding: 10px 0; font-size: 14px; color: #475569; border-bottom: 1px solid #f1f5f9; }
            .inv-summary .grand-row td { font-size: 20px; font-weight: 800; color: #0f172a; border-bottom: none; border-top: 2px solid #cbd5e1; padding-top: 15px; }
            .inv-summary .grand-row .amount { color: #3b82f6; }
            .inv-footer { background: #f8fafc; padding: 25px 50px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
            .inv-footer p { margin: 0; font-size: 13px; color: #64748b; }
            .auth-sign { text-align: center; }
            .auth-sign div { width: 150px; border-bottom: 1px solid #94a3b8; margin-bottom: 8px; }
            .auth-sign span { font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; }

            @media print {
                body { background: #fff; padding: 0; }
                .no-print { display: none; }
                .invoice-wrapper { box-shadow: none; max-width: 100%; border-radius: 0; margin-bottom: 0; border: none; }
                .invoice-wrapper::before { display: none; }
                .prem-table th { background: #1e293b !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                .info-box { background: #f8fafc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            }
        </style>
    </head>
    <body>
        <div class="no-print">
            <button class="btn-print" onclick="window.print()">🖨️ Print Premium Invoice</button>
        </div>

        @foreach($items as $item)
        @php $subTotal = 0; @endphp
        <div class="invoice-wrapper">
            
            @if(strtolower($item->payment_status) == 'paid')
                <div class="stamp paid">PAID</div>
            @else
                <div class="stamp unpaid">DUE</div>
            @endif

            <div class="inv-header">
                <div class="inv-header-left">
                    @if(isset($info->site_logo))
                        <img src="{{ asset('uploads/img/'.$info->site_logo) }}" alt="Logo">
                    @else
                        <h2>{{ $info->site_name ?? 'COMPANY NAME' }}</h2>
                    @endif
                    <p>{{ $info->address }}</p>
                    <p>Tel: {{ $info->owner_phone }}</p>
                    <p>Email: {{ $info->owner_email }}</p>
                </div>
                <div class="inv-header-right">
                    <h1>INVOICE</h1>
                    <div class="inv-number">#{{ $item->invoice_no }}</div>
                    <p style="margin: 0; font-size: 13px; color: #64748b;"><strong>Date:</strong> {{ date('d M, Y', strtotime($item->date)) }}</p>
                    <div style="margin-top: 15px;">
                        <img src="https://bwipjs-api.metafloor.com/?bcid=code128&text={{ $item->invoice_no }}&scale=2&height=10&includetext=true&textxalign=center" alt="Barcode" style="height: 35px; max-width:180px;">
                    </div>
                </div>
            </div>

            <div class="inv-info-grid">
                <div class="info-box">
                    <h4>Billed To</h4>
                    <strong>{{ trim(($item->first_name ?? '') . ' ' . ($item->last_name ?? '')) }}</strong>
                    <p>{{ $item->shipping_address }}</p>
                    <p>📞 {{ $item->mobile }}</p>
                </div>
                <div class="info-box">
                    <h4>Payment & Shipping</h4>
                    <p><strong>Order Status:</strong> <span style="text-transform: capitalize;">{{ $item->status }}</span></p>
                    @if($item->payment_status)
                    <p><strong>Pay Status:</strong> <span style="text-transform: uppercase;">{{ $item->payment_status }}</span></p>
                    @endif
                    <p><strong>Method:</strong> {{ $item->payment_method ?? 'COD' }}</p>
                    
                    @if($item->payment_method != 'Cash on Delivery' && $item->payment_method != 'online' && $item->payment_method != null)
                        <p><strong>TrxID:</strong> <span style="color: #3b82f6; font-weight:bold;">{{ $item->transaction_id }}</span></p>
                        <p><strong>Sender:</strong> {{ $item->sender_number }}</p>
                    @endif
                    
                    @if($item->courier)
                    <p><strong>Courier:</strong> {{ $item->courier->name ?? '' }}</p>
                    @endif
                </div>
            </div>

            <div class="inv-table-wrapper">
                <table class="prem-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 45%;">Item Description</th>
                            <th class="text-center" style="width: 15%;">Qty</th>
                            <th class="text-right" style="width: 15%;">Price</th>
                            <th class="text-right" style="width: 20%;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($item->details as $idx => $detail)
                        @php
                            $unitPrice = $detail->unit_price ?? 0;
                            $qty = (int)($detail->quantity ?? 1);
                            $rowTotal = $unitPrice * $qty;
                            $subTotal += $rowTotal;
                            
                            $variantInfo = "";
                            if(isset($detail->variation)){
                                $c = optional($detail->variation->color)->name;
                                $s = optional($detail->variation->size)->title;
                                if($c && $c != 'Default') $variantInfo .= "Color: $c ";
                                if($s && $s != 'free') $variantInfo .= " | Size: $s";
                            }
                        @endphp
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>
                                <strong style="color: #0f172a; display: block; margin-bottom: 3px;">{{ $detail->product->name ?? '' }}</strong>
                                <span style="font-size: 12px; color: #64748b;">{{ trim($variantInfo, ' |') }}</span>
                            </td>
                            <td class="text-center fw-bold">{{ $qty }}</td>
                            <td class="text-right">{{ number_format($unitPrice, 2) }}</td>
                            <td class="text-right fw-bold" style="color: #0f172a;">{{ number_format($rowTotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="inv-summary-wrapper">
                <div class="inv-summary">
                    <table>
                        <tr>
                            <td>Subtotal</td>
                            <td class="text-right">{{ number_format($subTotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Delivery Charge</td>
                            <td class="text-right">{{ number_format($item->shipping_charge ?? 0, 2) }}</td>
                        </tr>
                        
                        @php
                            $delivery = (float)($item->shipping_charge ?? 0);
                            $grandTotal = (float)($item->final_amount ?? ($subTotal + $delivery));
                            
                            // Actual Coupon Discount Calculation
                            $discount = ($subTotal + $delivery) - $grandTotal;
                            if ($discount < 0) $discount = 0;
                        @endphp
                        
                        {{-- ✅ শুধুমাত্র ডিসকাউন্ট 0 এর বেশি হলেই লাইনটি দেখাবে --}}
                        @if($discount > 0)
                        <tr>
                            <td>Discount</td>
                            <td class="text-right" style="color: #ef4444;">- {{ number_format($discount, 2) }}</td>
                        </tr>
                        @endif
                        
                        <tr class="grand-row">
                            <td>Grand Total</td>
                            <td class="text-right amount">{{ number_format($grandTotal, 2) }} BDT</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="inv-footer">
                <div>
                    <p><strong>Notes:</strong> {{ $item->note ?? 'Thank you for your business. It is a pleasure to serve you!' }}</p>
                </div>
                <div>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ $item->mobile }}" alt="QR Code" style="height: 60px;">
                </div>
                <div class="auth-sign">
                    <div></div>
                    <span>Authorized Signatory</span>
                </div>
            </div>

        </div>
        @endforeach
    </body>
    </html>

@elseif($invoiceType === 4)
    {{-- ======================================================
         DESIGN 4: PREMIUM POS / THERMAL RECEIPT (80mm) WITH SCANNER
         ====================================================== --}}
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>POS Receipt</title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&display=swap');
            body { font-family: 'Roboto Mono', monospace; background: #e0e0e0; margin: 0; padding: 20px; display: flex; flex-direction: column; align-items: center; color: #000; }
            .no-print { margin-bottom: 20px; text-align: center; }
            .btn-print { background: #000; color: #fff; padding: 10px 20px; border: none; cursor: pointer; font-family: sans-serif; font-weight: bold; border-radius: 5px; font-size: 15px;}
            
            .pos-receipt { 
                width: 80mm; 
                background: #fff; 
                padding: 6mm; 
                margin-bottom: 15px; 
                font-size: 12px;
                line-height: 1.4;
                box-sizing: border-box;
                page-break-after: always;
                box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            }
            .pos-hdr { text-align: center; margin-bottom: 8px; }
            .pos-hdr img.shop-logo { max-width: 60%; max-height: 45px; margin-bottom: 5px; object-fit: contain; } /* ✅ Logo CSS added */
            .pos-hdr h2 { margin: 0 0 3px 0; font-size: 18px; text-transform: uppercase; font-weight: 700; letter-spacing: 1px;}
            .pos-hdr p { margin: 0; font-size: 11px; }
            
            .barcode-box { text-align: center; margin: 8px 0; }
            .barcode-box img { width: 90%; height: 40px; display: block; margin: 0 auto; }
            
            .dashed-line { border-bottom: 1px dashed #000; margin: 8px 0; }
            .solid-line { border-bottom: 1px solid #000; margin: 8px 0; }
            
            .order-meta { font-size: 11px; margin-bottom: 5px; }
            .order-meta div { display: flex; justify-content: space-between; margin-bottom: 2px;}
            
            table { width: 100%; border-collapse: collapse; font-size: 11px; }
            th { text-align: left; padding-bottom: 4px; border-bottom: 1px dashed #000; font-weight: 700; text-transform: uppercase;}
            td { padding: 4px 0; vertical-align: top; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            
            .item-name { font-weight: 700; display: block; margin-bottom: 2px;}
            
            .totals-block { margin-top: 5px; font-size: 12px; }
            .totals-block div { display: flex; justify-content: space-between; margin-bottom: 3px; }
            .totals-block .grand-total { font-weight: bold; font-size: 15px; border-top: 1px dashed #000; padding-top: 6px; margin-top: 4px; }
            
            .qr-box { text-align: center; margin: 15px 0 10px; }
            .qr-box img { width: 65px; height: 65px; }
            
            .footer-msg { text-align: center; font-size: 11px; margin-top: 10px; font-weight: 700;}

            @media print {
                body { background: #fff; padding: 0; align-items: flex-start; }
                .no-print { display: none; }
                .pos-receipt { margin: 0; box-shadow: none; width: 80mm; padding: 2mm;}
                @page { margin: 0; width: 80mm; }
            }
        </style>
    </head>
    <body>
        <div class="no-print">
            <button class="btn-print" onclick="window.print()">🖨️ Print POS Receipt</button>
        </div>

        @foreach($items as $item)
        <div class="pos-receipt">
            
            <div class="pos-hdr">
                {{-- ✅ Shop Logo Added for Design 4 --}}
                @if(isset($info->site_logo) && !empty($info->site_logo))
                    <img src="{{ asset('uploads/img/'.$info->site_logo) }}" alt="Logo" class="shop-logo">
                @endif
                <p>{{ $info->address }}</p>
                <p>Phone: {{ $info->owner_phone }}</p>
            </div>
            
            <div class="barcode-box">
                <img src="https://bwipjs-api.metafloor.com/?bcid=code128&text={{ $item->invoice_no }}&scale=3&height=12&includetext=true&textxalign=center" alt="Barcode">
            </div>
            
            <div class="dashed-line"></div>
            
            <div class="order-meta">
                <div><span>Date:</span> <span>{{ date('d/m/Y h:i A', strtotime($item->created_at)) }}</span></div>
                <div><span>Customer:</span> <span>{{ trim(($item->first_name ?? '') . ' ' . ($item->last_name ?? '')) }}</span></div>
                <div><span>Phone:</span> <span>{{ $item->mobile }}</span></div>
                {{-- ✅ Customer Address Added Here --}}
                <div><span>Address:</span> <span style="text-align: right; max-width: 70%;">{{ $item->shipping_address }}</span></div>
                
                <div><span>Method:</span> <span>{{ $item->payment_method ?? 'COD' }}</span></div>
                @if($item->payment_method != 'Cash on Delivery' && $item->payment_method != 'online' && $item->payment_method != null)
                    <div><span>TrxID:</span> <span>{{ $item->transaction_id }}</span></div>
                    <div><span>Sender:</span> <span>{{ $item->sender_number }}</span></div>
                @endif

                @if($item->payment_status)
                <div><span>Status:</span> <span style="text-transform: uppercase;">{{ $item->payment_status }}</span></div>
                @endif
            </div>
            
            <div class="solid-line"></div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 50%;">Item</th>
                        <th class="text-center" style="width: 15%;">Qty</th>
                        <th class="text-right" style="width: 35%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $subTotal = 0; @endphp
                    @foreach($item->details as $detail)
                    @php
                        $unitPrice = $detail->unit_price ?? 0;
                        $qty = (int)($detail->quantity ?? 1);
                        $rowTotal = $unitPrice * $qty;
                        $subTotal += $rowTotal;
                    @endphp
                    <tr>
                        <td>
                            <span class="item-name">{{ \Illuminate\Support\Str::limit($detail->product->name ?? '', 20) }}</span>
                            @if(isset($detail->variation) && isset($detail->variation->size) && $detail->variation->size->title != 'free')
                                <span style="font-size: 9px;">Size: {{ $detail->variation->size->title }}</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $qty }}<br><span style="font-size: 9px;">x{{ number_format($unitPrice, 0) }}</span></td>
                        <td class="text-right">{{ number_format($rowTotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="dashed-line"></div>
            
            @php
                $delivery = (float)($item->shipping_charge ?? 0);
                $grandTotal = (float)($item->final_amount ?? ($subTotal + $delivery));
                
                // Actual Coupon Discount Calculation
                $discount = ($subTotal + $delivery) - $grandTotal;
                if ($discount < 0) $discount = 0;
            @endphp
            <div class="totals-block">
                <div><span>Subtotal:</span> <span>{{ number_format($subTotal, 2) }}</span></div>
                <div><span>Delivery:</span> <span>{{ number_format($item->shipping_charge ?? 0, 2) }}</span></div>
                
                {{-- ✅ শুধুমাত্র ডিসকাউন্ট 0 এর বেশি হলেই লাইনটি দেখাবে --}}
                @if($discount > 0)
                <div><span>Discount:</span> <span>-{{ number_format($discount, 2) }}</span></div>
                @endif
                
                <div class="grand-total"><span>TOTAL:</span> <span>Tk {{ number_format($grandTotal, 2) }}</span></div>
            </div>
            
            <div class="solid-line"></div>
            
            <div class="qr-box">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ $item->mobile }}" alt="QR Code">
            </div>
            
            <div class="footer-msg">
                *** THANK YOU FOR SHOPPING ***<br>
                Please visit again.
            </div>
            
        </div>
        @endforeach
    </body>
    </html>

@endif