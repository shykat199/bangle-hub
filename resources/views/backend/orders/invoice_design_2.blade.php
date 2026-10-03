<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $item->invoice_no }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; margin: 0; padding: 20px; color: #1f2937; }
        .invoice-container { max-width: 800px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #f3f4f6; padding-bottom: 20px; margin-bottom: 30px; }
        .logo img { max-height: 60px; }
        .company-details { text-align: right; }
        .company-details h2 { margin: 0 0 5px 0; color: #111827; font-size: 24px; text-transform: uppercase; letter-spacing: 1px; }
        .company-details p { margin: 2px 0; color: #4b5563; font-size: 13px; }
        
        .invoice-meta { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .billing-to h3 { margin: 0 0 10px 0; color: #374151; font-size: 14px; text-transform: uppercase; font-weight: 700; }
        .billing-to p { margin: 3px 0; font-size: 14px; }
        .inv-details table { border-collapse: collapse; text-align: right; }
        .inv-details th { padding: 4px 10px; font-size: 13px; color: #6b7280; font-weight: 500; }
        .inv-details td { padding: 4px 10px; font-size: 14px; font-weight: 600; color: #111827; }
        
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { background-color: #f9fafb; padding: 12px; text-align: left; font-size: 13px; text-transform: uppercase; color: #374151; border-bottom: 1px solid #e5e7eb; }
        .items-table th.text-right { text-align: right; }
        .items-table th.text-center { text-align: center; }
        .items-table td { padding: 12px; border-bottom: 1px solid #e5e7eb; font-size: 14px; vertical-align: top; }
        .items-table td.text-right { text-align: right; }
        .items-table td.text-center { text-align: center; }
        
        .product-name { font-weight: 600; color: #111827; margin-bottom: 4px; display: block; }
        .product-meta { font-size: 12px; color: #6b7280; }
        
        .summary-box { width: 350px; float: right; margin-bottom: 40px; }
        .summary-box table { width: 100%; border-collapse: collapse; }
        .summary-box th, .summary-box td { padding: 8px 12px; font-size: 14px; border-bottom: 1px solid #f3f4f6; }
        .summary-box th { text-align: left; color: #4b5563; font-weight: 500; }
        .summary-box td { text-align: right; font-weight: 600; color: #111827; }
        .summary-box tr.total th, .summary-box tr.total td { font-size: 18px; color: #2563eb; border-bottom: none; border-top: 2px solid #e5e7eb; font-weight: 700; padding-top: 12px; }
        
        .footer { clear: both; padding-top: 40px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .notes p { margin: 0; font-size: 13px; color: #6b7280; }
        .signature { text-align: center; }
        .signature-line { width: 150px; border-bottom: 1px solid #111827; margin-bottom: 5px; }
        .signature p { font-size: 12px; color: #4b5563; margin: 0; }
        
        .btn-print { display: block; width: 150px; margin: 20px auto; padding: 10px; background: #2563eb; color: #fff; text-align: center; text-decoration: none; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; }
        .btn-print:hover { background: #1d4ed8; }

        @media print {
            body { background-color: #fff; padding: 0; }
            .invoice-container { box-shadow: none; padding: 0; max-width: 100%; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="btn-print no-print">🖨️ Print Invoice</button>

    <div class="invoice-container">
        <div class="header">
            <div class="logo">
                @if(isset($info->site_logo))
                    <img src="{{ asset('uploads/img/'.$info->site_logo) }}" alt="Logo">
                @else
                    <h2 style="margin:0; font-size:24px; color:#2563eb;">{{ $info->site_name ?? 'FAST2BUYS' }}</h2>
                @endif
            </div>
            <div class="company-details">
                <h2>INVOICE</h2>
                <p><strong>{{ $info->site_name ?? 'Fast2Buys' }}</strong></p>
                <p>{{ $info->address ?? 'Bangladesh' }}</p>
                <p>Phone: {{ $info->owner_phone ?? '' }}</p>
                <p>Email: {{ $info->owner_email ?? '' }}</p>
            </div>
        </div>

        <div class="invoice-meta">
            <div class="billing-to">
                <h3>Billed To:</h3>
                <p><strong>{{ trim(($item->first_name ?? '') . ' ' . ($item->last_name ?? '')) }}</strong></p>
                <p>{{ $item->shipping_address }}</p>
                <p>Mobile: {{ $item->mobile }}</p>
            </div>
            <div class="inv-details">
                <table>
                    <tr>
                        <th>Invoice No:</th>
                        <td>#{{ $item->invoice_no }}</td>
                    </tr>
                    <tr>
                        <th>Order Date:</th>
                        <td>{{ date('d M, Y', strtotime($item->date)) }}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td style="color: #2563eb; text-transform:uppercase;">{{ $item->status }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>SL.</th>
                    <th>Item Description</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @foreach($item->details as $key => $detail)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>
                        <span class="product-name">{{ $detail->product->name ?? 'Unknown Product' }}</span>
                        <span class="product-meta">
                            @if(isset($detail->variation))
                                @if(isset($detail->variation->color) && $detail->variation->color->name != 'Default')
                                    Color: {{ $detail->variation->color->name }}
                                @endif
                                @if(isset($detail->variation->size) && $detail->variation->size->title != 'free')
                                    | Size: {{ $detail->variation->size->title }}
                                @endif
                            @endif
                        </span>
                    </td>
                    <td class="text-center">{{ $detail->quantity }}</td>
                    <td class="text-right">{{ number_format($detail->unit_price, 2) }}</td>
                    @php 
                        $row_total = $detail->unit_price * $detail->quantity;
                        $total += $row_total;
                    @endphp
                    <td class="text-right">{{ number_format($row_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @php
            // ⚠️ আগে হিসাব হতো: subtotal − discount(কলাম) + delivery — কিন্তু অনেক
            // অর্ডারে discount কলামটি final_amount-এ ধরা থাকত না, ফলে ইনভয়েসে
            // অর্ডারের আসল টাকার চেয়ে কম/বেশি দেখাত (১০টির ৭টিতেই ভুল ছিল)।
            // এখন অর্ডারের প্রকৃত final_amount-ই চূড়ান্ত, আর ডিসকাউন্ট সেখান থেকেই বের করা হয় —
            // তাই ইনভয়েসের যোগফল সবসময় মিলবে।
            $delivery_amt = (float) ($item->shipping_charge ?? 0);
            $grand_total  = (float) ($item->final_amount ?? ($total + $delivery_amt));
            $invoiceDiscount = ($total + $delivery_amt) - $grand_total;
            if ($invoiceDiscount < 0) $invoiceDiscount = 0;
        @endphp

        <div class="summary-box">
            <table>
                <tr>
                    <th>Subtotal:</th>
                    <td>{{ number_format($total, 2) }}</td>
                </tr>
                <tr>
                    <th>Delivery Charge:</th>
                    <td>{{ number_format($delivery_amt, 2) }}</td>
                </tr>
                @if($invoiceDiscount > 0)
                <tr>
                    <th>Discount:</th>
                    <td style="color: #dc2626;">- {{ number_format($invoiceDiscount, 2) }}</td>
                </tr>
                @endif
                <tr class="total">
                    <th>Grand Total:</th>
                    <td>{{ number_format($grand_total, 2) }} TK</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <div class="notes">
                <p><strong>Note:</strong> {{ $item->note ?? 'Thank you for shopping with us!' }}</p>
                <p style="margin-top: 5px;">If you have any questions, please contact our support.</p>
            </div>
            <div class="signature">
                <div class="signature-line"></div>
                <p>Authorized Signature</p>
            </div>
        </div>
    </div>
</body>
</html>