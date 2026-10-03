@php use App\Services\ProfitCalculatorService as PS; $h = PS::healthLabel($result['health_status']); @endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Profit Report — {{ $rec->product_name }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    * { box-sizing:border-box; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    body { font-family: 'Helvetica Neue', Arial, sans-serif; background:#fff; margin:0; color:#0f172a; font-size:13px; }

    .toolbar { background:#f6f8fc; padding:12px 24px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e2e8f0; }
    .toolbar .meta { color:#64748b; font-size:12px; }
    .toolbar .meta b { color:#0f172a; }
    .toolbar .btns button { padding:8px 18px; border:1px solid #cbd5e1; border-radius:6px; background:#fff; font-weight:600; cursor:pointer; font-size:13px; margin-left:6px; }
    .toolbar .btns button.p { background:#3b82f6; color:#fff; border-color:#3b82f6; }

    .page { max-width:780px; margin:0 auto; padding:0; background:#fff; }
    .page + .page { page-break-before: always; margin-top:30px; }

    /* ============ Page 1: Navy Header Banner ============ */
    .banner {
        background:#1a3554; color:#fff;
        padding:24px 32px;
        display:flex; justify-content:space-between; align-items:flex-start;
    }
    .banner h1 { font-size:24px; font-weight:800; margin:0; letter-spacing:.3px; }
    .banner .sub { font-size:13px; opacity:.85; margin-top:4px; font-weight:400; }
    .banner .meta-r { text-align:right; font-size:12px; opacity:.9; line-height:1.6; }

    .body { padding:24px 32px; }

    /* Product strip with badge */
    .prod-strip { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; }
    .prod-name { font-size:18px; font-weight:800; color:#0f172a; }
    .badge-btn {
        padding:10px 28px; border-radius:6px; font-size:14px; font-weight:700; color:#fff;
        text-transform:none; display:inline-block; min-width:120px; text-align:center;
    }
    .badge-loss      { background:#dc2626; }
    .badge-risky     { background:#ea580c; }
    .badge-good      { background:#16a34a; }
    .badge-excellent { background:#0d9488; }

    /* Data table */
    table.data {
        width:100%; border-collapse:collapse; font-size:13px;
        border:1px solid #cbd5e1;
    }
    table.data thead th {
        background:#1d4ed8; color:#fff; font-weight:700;
        text-align:left; padding:10px 14px;
        border:1px solid #1d4ed8;
    }
    table.data thead th:last-child { text-align:right; }
    table.data tbody td {
        padding:9px 14px; border:1px solid #cbd5e1;
        color:#1e3a8a;
    }
    table.data tbody td:last-child { text-align:right; font-weight:700; }
    table.data tbody tr:nth-child(odd) td { background:#eef4fb; }
    table.data tbody tr:nth-child(even) td { background:#ffffff; }

    /* ============ Page 2: Footer-style header on top ============ */
    .pg-top {
        display:flex; justify-content:space-between; align-items:center;
        padding:14px 32px 10px; font-size:11px; color:#64748b;
    }
    .pg-top .l { flex:1; text-align:center; }
    .pg-top .r { color:#0f172a; font-weight:500; }
    .pg-bar { background:#0f172a; height:8px; }

    /* Insight section */
    .insight-block { margin-top:22px; }
    .insight-block h3 { font-size:15px; font-weight:800; color:#0f172a; margin:0 0 8px; }
    .insight-block .m {
        font-size:13px; color:#1e293b; margin:6px 0;
        line-height:1.5;
    }

    /* CTA Box */
    .cta-box {
        background:#2563eb; color:#fff;
        padding:16px 22px; border-radius:6px; margin-top:18px;
        font-size:14px; font-weight:700; text-align:left;
    }

    @media print {
        body { background:#fff; }
        .toolbar { display:none; }
        .page { max-width:none; margin:0; box-shadow:none; }
        .banner, table.data thead th, .pg-bar, .cta-box, .badge-btn { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
        .page + .page { page-break-before: always; }
    }
    @page { size: A4; margin: 0; }
</style>
</head>
<body>

<div class="toolbar no-print">
    <div class="meta">
        <b>Report #:</b> PC-{{ str_pad($rec->id, 5, '0', STR_PAD_LEFT) }} ·
        <b>Generated:</b> {{ now()->format('d M, Y h:i A') }}
    </div>
    <div class="btns">
        <button onclick="window.history.back()">← Back</button>
        <button class="p" onclick="window.print()">🖨 Save / Print PDF</button>
    </div>
</div>

{{-- ============================================= --}}
{{-- ============== PAGE 1 ======================= --}}
{{-- ============================================= --}}
<div class="page">

    <div class="banner">
        <div>
            <h1>BIZ CARE LTD.</h1>
            <div class="sub">Profit Calculation Report</div>
        </div>
        <div class="meta-r">
            <div>{{ now()->format('d/m/Y, H:i:s') }}</div>
            <div>Formula {{ $result['formula_version'] }}</div>
        </div>
    </div>

    <div class="body">
        <div class="prod-strip">
            <div class="prod-name">{{ $rec->product_name }}</div>
            <span class="badge-btn badge-{{ $result['health_status'] }}">{{ $h['label'] === 'Good Zone' ? 'Good' : ($h['label'] === 'Loss Zone' ? 'Loss' : str_replace(' Zone','', $h['label'])) }}</span>
        </div>

        <table class="data">
            <thead>
                <tr><th>Metric</th><th>Value</th></tr>
            </thead>
            <tbody>
                <tr><td>Total Sell</td><td>{{ PS::moneyTk($result['total_selling']) }}</td></tr>
                <tr><td>Total Cost</td><td>{{ PS::moneyTk($result['total_cost']) }}</td></tr>
                <tr><td>Total Marketing Cost</td><td>{{ PS::moneyTk($result['total_marketing_cost']) }}</td></tr>
                <tr><td>Total Product Cost</td><td>{{ PS::moneyTk($result['total_cost_price']) }}</td></tr>
                <tr><td>Total Fixed Cost</td><td>{{ PS::moneyTk($result['total_shipping_cost']) }}</td></tr>
                <tr><td>Gross Profit</td><td>{{ PS::moneyTk($result['gross_profit']) }}</td></tr>
                <tr><td>Return Product Qty</td><td>{{ PS::number($result['return_units'], 2) }}</td></tr>
                <tr><td>Return Product Value</td><td>{{ PS::moneyTk($result['return_deduction']) }}</td></tr>
                <tr><td>Return Courier Loss</td><td>{{ PS::moneyTk($result['return_courier_loss']) }}</td></tr>
                <tr><td>Success Delivery Profit</td><td>{{ PS::moneyTk($result['success_delivery_profit']) }}</td></tr>
                <tr><td>Net Profit</td><td>{{ PS::moneyTk($result['net_profit']) }}</td></tr>
                <tr><td>Profit Margin</td><td>{{ PS::percent($result['profit_margin']) }}</td></tr>
                <tr><td>ROI</td><td>{{ PS::percent($result['roi']) }}</td></tr>
                <tr><td>Per Product Profit</td><td>{{ PS::moneyTk($result['per_product_profit']) }}</td></tr>
                <tr><td>Per Product Marketing Cost</td><td>{{ PS::moneyTk($result['per_product_marketing']) }}</td></tr>
                <tr><td>Per Product Purchase Cost</td><td>{{ PS::moneyTk($result['per_product_purchase']) }}</td></tr>
                <tr><td>Call Cancel %</td><td>{{ PS::percent($rec->call_cancel_percentage) }}</td></tr>
                <tr><td>Required Orders (for target)</td><td>{{ $result['required_orders'] }}</td></tr>
                <tr><td>Cancelled Marketing Loss</td><td>{{ PS::moneyTk($result['cancelled_marketing_loss']) }}</td></tr>
                <tr><td>Required Ad Budget</td><td>{{ PS::moneyTk($result['required_ad_budget']) }}</td></tr>
                <tr><td>Available Ad Budget</td><td>{{ PS::moneyTk($result['available_ad_budget']) }}</td></tr>
                <tr><td>Budget Gap</td><td>{{ PS::moneyTk($result['budget_gap']) }}</td></tr>
                <tr><td>Cash Locked in Courier</td><td>{{ PS::moneyTk($result['cash_locked_in_courier']) }}</td></tr>
                <tr><td>Reinvestment Capacity (days)</td><td>{{ PS::number($result['reinvestment_capacity_days'], 1) }}</td></tr>
                <tr><td>Marketing % of Revenue</td><td>{{ PS::percent($result['marketing_cost_percent_of_revenue']) }}</td></tr>
            </tbody>
        </table>
    </div>

</div>

{{-- ============================================= --}}
{{-- ============== PAGE 2 ======================= --}}
{{-- ============================================= --}}
<div class="page">

    <div class="pg-top">
        <div class="l">Generated by BIZ CARE Profit Calculator | Powered by BIZ CARE LTD.</div>
        <div class="r">Page 2 / 2</div>
    </div>
    <div class="pg-bar"></div>

    <div class="body">

        <table class="data">
            <thead>
                <tr><th>Input</th><th>Value</th></tr>
            </thead>
            <tbody>
                <tr><td>Cost Price / Unit</td><td>{{ PS::moneyTk($rec->cost_price) }}</td></tr>
                <tr><td>Selling Price / Unit</td><td>{{ PS::moneyTk($rec->selling_price) }}</td></tr>
                <tr><td>Marketing Cost / Unit</td><td>{{ PS::moneyTk($rec->marketing_cost) }}</td></tr>
                <tr><td>Fixed Cost / Unit</td><td>{{ PS::moneyTk($rec->shipping_cost) }}</td></tr>
                <tr><td>Quantity</td><td>{{ $rec->quantity }}</td></tr>
                <tr><td>Return %</td><td>{{ PS::percent($rec->return_percentage) }}</td></tr>
                <tr><td>Delivery Profit / Sold Unit</td><td>{{ PS::moneyTk($rec->extra_shipping_profit) }}</td></tr>
                <tr><td>Loss / Returned Unit</td><td>{{ PS::moneyTk($rec->per_return_loss) }}</td></tr>
                <tr><td>Sold Units</td><td>{{ PS::number($result['sold_units'], 2) }}</td></tr>
                <tr><td>Return Units</td><td>{{ PS::number($result['return_units'], 2) }}</td></tr>
                <tr><td>Total Selling</td><td>{{ PS::moneyTk($result['total_selling']) }}</td></tr>
                <tr><td>Total Cost</td><td>{{ PS::moneyTk($result['total_cost']) }}</td></tr>
            </tbody>
        </table>

        <div class="insight-block">
            <h3>BIZ CARE Business Insight</h3>
            @foreach($result['insights'] as $msg)
                <div class="m">• {{ $msg }}</div>
            @endforeach
        </div>

        <div class="cta-box">
            Talk to BIZ CARE LTD. team for personalized advice.
        </div>

    </div>

</div>

</body>
</html>
