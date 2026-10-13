<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $heading }}</title>
</head>
@php
    $accent   = themeAccent('#be1e30');
    $siteName = $settings->site_name ?? config('app.name');
    $money    = fn ($amount) => biz_format_currency((float) $amount);
    $label    = 'font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#95a5a6;font-weight:600;';
    $cell     = 'padding:10px 0;border-bottom:1px solid #eeeeee;font-size:14px;color:#34495e;vertical-align:top;';
@endphp
<body style="margin:0;padding:0;background-color:#f4f6f9;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f9;padding:32px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:8px;overflow:hidden;">
                <tr>
                    <td style="background-color:{{ $accent }};padding:26px 30px;text-align:center;">
                        <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:600;letter-spacing:.5px;">{{ $siteName }}</h1>
                    </td>
                </tr>

                <tr>
                    <td style="padding:30px;">
                        <span style="display:inline-block;background-color:#fff4e5;color:#b45309;border:1px solid #fcd9a8;padding:4px 10px;border-radius:4px;font-size:11px;font-weight:700;letter-spacing:1px;">PRE-ORDER</span>

                        <h2 style="margin:14px 0 12px;color:#2c3e50;font-size:20px;">{{ $heading }}</h2>

                        @foreach($lines as $line)
                            <p style="margin:0 0 10px;color:#4a5568;font-size:14px;line-height:1.6;">{{ $line }}</p>
                        @endforeach

                        @if(!empty($highlight))
                            <p style="margin:16px 0 0;padding:12px 14px;background-color:#f0f9ff;border-left:4px solid {{ $accent }};color:#1f2937;font-size:15px;font-weight:600;border-radius:4px;">{{ $highlight }}</p>
                        @endif

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:24px;">
                            <tr>
                                <td style="{{ $label }}padding-bottom:6px;">Order</td>
                                <td style="{{ $label }}padding-bottom:6px;text-align:right;">Status</td>
                            </tr>
                            <tr>
                                <td style="font-size:14px;color:#2c3e50;font-weight:600;">#{{ $order->invoice_no ?: $order->id }}</td>
                                <td style="font-size:14px;color:#2c3e50;font-weight:600;text-align:right;">{{ $order->status }}</td>
                            </tr>
                        </table>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px;border-collapse:collapse;">
                            <tr>
                                <th align="left" style="{{ $label }}padding:8px 0;border-bottom:2px solid #eeeeee;">Product</th>
                                <th align="center" style="{{ $label }}padding:8px 0;border-bottom:2px solid #eeeeee;">Qty</th>
                                <th align="right" style="{{ $label }}padding:8px 0;border-bottom:2px solid #eeeeee;">Total</th>
                            </tr>
                            @foreach($order->details as $line)
                                @php
                                    $variant = trim(collect([
                                        optional(optional($line->variation)->size)->title,
                                        optional(optional($line->variation)->color)->name,
                                    ])->filter()->implode(' / '));
                                @endphp
                                <tr>
                                    <td style="{{ $cell }}">
                                        {{ optional($line->product)->name ?? 'Product' }}
                                        @if($variant !== '')
                                            <br><span style="font-size:12px;color:#7f8c8d;">{{ $variant }}</span>
                                        @endif
                                        @if($line->is_preorder && $line->deliveryLabel())
                                            <br><span style="font-size:12px;color:#b45309;">{{ $line->deliveryLabel() }}</span>
                                        @endif
                                    </td>
                                    <td align="center" style="{{ $cell }}">{{ (int) $line->quantity }}</td>
                                    <td align="right" style="{{ $cell }}">{{ $money($line->unit_price * $line->quantity) }}</td>
                                </tr>
                            @endforeach
                        </table>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:18px;background-color:#f8f9fa;border-radius:6px;">
                            <tr>
                                <td style="padding:14px 16px 4px;font-size:13px;color:#7f8c8d;">Delivery charge</td>
                                <td align="right" style="padding:14px 16px 4px;font-size:13px;color:#7f8c8d;">{{ $money($order->shipping_charge) }}</td>
                            </tr>
                            <tr>
                                <td style="padding:4px 16px;font-size:15px;color:#2c3e50;font-weight:700;">Order total</td>
                                <td align="right" style="padding:4px 16px;font-size:15px;color:#2c3e50;font-weight:700;">{{ $money($order->final_amount) }}</td>
                            </tr>
                            <tr>
                                <td style="padding:4px 16px;font-size:13px;color:#7f8c8d;">Payment mode</td>
                                <td align="right" style="padding:4px 16px;font-size:13px;color:#7f8c8d;">{{ $order->payment_mode === 'advance' ? 'Advance payment' : 'Full payment' }}</td>
                            </tr>
                            @if($order->isAdvancePreorder() && !$order->advanceIsPaid())
                                <tr>
                                    <td style="padding:4px 16px;font-size:13px;color:#7f8c8d;">Advance payable</td>
                                    <td align="right" style="padding:4px 16px;font-size:13px;color:#7f8c8d;">{{ $money($order->advance_amount) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td style="padding:4px 16px;font-size:13px;color:#15803d;">Paid</td>
                                <td align="right" style="padding:4px 16px;font-size:13px;color:#15803d;">{{ $money($order->advance_paid) }}</td>
                            </tr>
                            <tr>
                                <td style="padding:4px 16px 14px;font-size:15px;color:#b91c1c;font-weight:700;">Due amount</td>
                                <td align="right" style="padding:4px 16px 14px;font-size:15px;color:#b91c1c;font-weight:700;">{{ $money($order->due_amount) }}</td>
                            </tr>
                        </table>

                        <p style="text-align:center;margin:26px 0 0;">
                            <a href="{{ $orderUrl }}" style="display:inline-block;background-color:{{ $accent }};color:#ffffff;padding:12px 26px;text-decoration:none;border-radius:4px;font-weight:700;font-size:14px;">View your order</a>
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="background-color:#f8f9fa;padding:18px 30px;text-align:center;font-size:12px;color:#95a5a6;border-top:1px solid #eeeeee;">
                        {{ $siteName }}@if(!empty($settings->owner_phone)) &middot; {{ $settings->owner_phone }}@endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
