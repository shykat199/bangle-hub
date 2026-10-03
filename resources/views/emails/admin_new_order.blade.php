<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order Notification</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 0; -webkit-font-smoothing: antialiased; }
        .wrapper { width: 100%; background-color: #f4f6f9; padding: 40px 0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .header { background-color: #2c3e50; padding: 30px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: 600; letter-spacing: 1px; }
        .content { padding: 30px; }
        .status-badge { background-color: #e3f2fd; color: #0d47a1; padding: 5px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-grid { display: table; width: 100%; margin-bottom: 25px; border-bottom: 1px solid #eeeeee; padding-bottom: 20px; }
        .info-col { display: table-cell; width: 50%; vertical-align: top; }
        .label { font-size: 11px; text-transform: uppercase; color: #95a5a6; letter-spacing: 0.5px; margin-bottom: 5px; font-weight: 600; }
        .value { font-size: 14px; color: #2c3e50; font-weight: 500; line-height: 1.4; }
        
        .product-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .product-table th { text-align: left; font-size: 11px; text-transform: uppercase; color: #95a5a6; padding: 10px 0; border-bottom: 2px solid #eeeeee; }
        .product-table td { padding: 12px 0; border-bottom: 1px solid #eeeeee; font-size: 14px; color: #34495e; vertical-align: middle; }
        .product-img { width: 40px; height: 40px; border-radius: 4px; object-fit: cover; margin-right: 10px; vertical-align: middle; border: 1px solid #eee; }
        .total-section { background-color: #f8f9fa; padding: 20px; border-radius: 6px; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px; color: #7f8c8d; }
        .total-row.final { border-top: 1px solid #e0e0e0; margin-top: 10px; padding-top: 10px; font-size: 16px; font-weight: 700; color: #2c3e50; }
        .footer { background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #95a5a6; border-top: 1px solid #eeeeee; }
        .btn { display: inline-block; background-color: #3498db; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 14px; margin-top: 20px; text-align: center; }
        .btn:hover { background-color: #2980b9; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>{{ $settings->site_name ?? 'New Order' }}</h1>
            </div>

            <div class="content">
                <div style="text-align: center; margin-bottom: 30px;">
                    <img src="https://cdn-icons-png.flaticon.com/512/1170/1170576.png" alt="Cart" width="50" style="margin-bottom: 15px; opacity: 0.8;">
                    <h2 style="margin: 0 0 5px 0; color: #2c3e50;">New Order Received!</h2>
                    
                    <p style="margin: 0; color: #7f8c8d; font-size: 14px;">
                        Invoice: <strong>#{{ $order->invoice_no }}</strong>
                        <span style="margin: 0 5px; color: #cbd5e0;">|</span>
                        Order ID: <strong>#{{ $order->id }}</strong>
                    </p>
                </div>

                <div class="info-grid">
                    <div class="info-col">
                        <div class="label">Customer Details</div>
                        <div class="value">
                            {{ $order->first_name }} {{ $order->last_name }}<br>
                            {{ $order->mobile }}<br>
                            {{ $order->shipping_address }}
                        </div>
                    </div>
                    <div class="info-col" style="text-align: right;">
                        <div class="label">Order Date</div>
                        <div class="value">{{ date('d M, Y', strtotime($order->created_at)) }}</div>
                        <br>
                        <div class="label">Payment Method</div>
                        <div class="value" style="text-transform: capitalize;">{{ $order->payment_method ?? 'COD' }}</div>
                    </div>
                </div>

                <div class="label">Order Summary</div>
                <table class="product-table">
                    <thead>
                        <tr>
                            <th width="60%">Item</th>
                            <th width="15%" style="text-align: center;">Qty</th>
                            <th width="25%" style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->details as $detail)
                        <tr>
                            <td>
                                <div>
                                    <span style="font-weight: 500;">{{ $detail->product->name ?? 'Product' }}</span>
                                    @if($detail->variation_id)
                                        <br><span style="font-size: 11px; color: #95a5a6;">
                                            Size: {{ $detail->variation->size->title ?? '-' }} | Color: {{ $detail->variation->color->name ?? '-' }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td style="text-align: center;">{{ $detail->quantity }}</td>
                            <td style="text-align: right;">{{ $detail->unit_price * $detail->quantity }} Tk</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="total-section">
                    <div class="total-row">
                        <span>Sub Total = </span>
                        <span>{{ $order->amount - ($order->shipping_charge ?? 0) }} Tk</span>
                    </div>
                    <div class="total-row">
                        <span>Delivery Charge</span>
                        <span>+ {{ $order->shipping_charge }} Tk</span>
                    </div>
                    
                    @if($order->discount > 0)
                    <div class="total-row" style="color: #e74c3c;">
                       <span>Discount</span>
                       <span>- {{ $order->discount }} Tk</span>
                    </div>
                    @endif
                    
                    <div class="total-row final">
                        <span>Total Amount = </span>
                        <span>{{ $order->final_amount }} Tk</span>
                    </div>
                </div>

                <div style="text-align: center;">
                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn">View Order in Admin Panel</a>
                </div>
            </div>

            <div class="footer">
                &copy; {{ date('Y') }} {{ $settings->site_name }}. All rights reserved.<br>
               BIZ CARE LTD.
            </div>
        </div>
    </div>
</body>
</html>