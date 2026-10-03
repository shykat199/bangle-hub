@extends('backend.app')
@section('content')

@php
    use App\Services\ProfitCalculatorService as PS;
    $r = $result ?? [];
    $health = PS::healthLabel($r['health_status'] ?? 'loss');
    $d = $defaults;

    // Render empty for null / blank values instead of "0"
    $val = function ($v) {
        if ($v === null || $v === '') return '';
        return $v;
    };
@endphp

<style>
    .pc-wrap { background:#f6f8fc; min-height:calc(100vh - 100px); padding: 22px 8px; }
    .pc-card { background:#fff; border-radius:16px; padding:24px; box-shadow:0 1px 3px rgba(15,23,42,.05); }
    .pc-title { font-size:24px; font-weight:800; color:#0f172a; margin:0; }
    .pc-sub { color:#64748b; font-size:13px; margin-top:2px; }
    .pc-section { font-weight:700; color:#0f172a; margin-top:18px; margin-bottom:10px; font-size:14px; letter-spacing:.2px; }
    .pc-label { font-size:12px; color:#475569; font-weight:600; margin-bottom:6px; display:block; }
    .pc-input { width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:10px; font-size:14px; background:#fff; transition:all .15s; }
    .pc-input:focus { outline:none; border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.12); }
    .pc-input::placeholder { color:#cbd5e1; }
    .pc-help { font-size:11px; color:#94a3b8; margin-top:4px; }
    .pc-help.live { color:#3b82f6; font-weight:600; }
    .pc-net-card { background:#fff5f5; border:1px solid #fee2e2; border-radius:14px; padding:18px 20px; position:relative; transition:all .15s; }
    .pc-net-card.good { background:#f0fdf4; border-color:#bbf7d0; }
    .pc-net-card.excellent { background:#ecfdf5; border-color:#a7f3d0; }
    .pc-net-card.risky { background:#fffbeb; border-color:#fde68a; }
    .pc-net-label { font-size:11px; color:#64748b; font-weight:700; letter-spacing:1px; text-transform:uppercase; }
    .pc-net-value { font-size:34px; font-weight:800; color:#10b981; margin-top:4px; line-height:1; font-variant-numeric: tabular-nums; }
    .pc-net-value.neg { color:#ef4444; }
    .pc-badge { position:absolute; top:18px; right:18px; padding:4px 12px; border-radius:999px; font-size:11px; font-weight:700; color:#fff; }
    .pc-mini { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:12px 14px; }
    .pc-mini-label { font-size:10px; color:#64748b; font-weight:700; letter-spacing:1px; text-transform:uppercase; }
    .pc-mini-value { font-size:20px; font-weight:700; color:#0f172a; margin-top:6px; }
    .pc-break-row { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px dashed #f1f5f9; font-size:13.5px; color:#334155; }
    .pc-break-row:last-child { border-bottom:0; }
    .pc-break-row .lbl { color:#475569; }
    .pc-break-row .val { font-weight:600; color:#0f172a; font-variant-numeric: tabular-nums; }
    .pc-break-row .val.pos { color:#16a34a; }
    .pc-break-row .val.neg { color:#dc2626; }
    .pc-insight { background:#0f172a; color:#e2e8f0; border-radius:14px; padding:16px 18px; }
    .pc-insight .ttl { color:#fbbf24; font-weight:700; font-size:13px; display:flex; align-items:center; gap:8px; }
    .pc-insight .msg { color:#cbd5e1; font-size:13px; margin-top:8px; line-height:1.55; }
    .pc-btn { padding:10px 16px; border:1px solid #e2e8f0; background:#fff; border-radius:10px; font-size:13.5px; font-weight:600; color:#475569; display:inline-flex; align-items:center; gap:8px; transition:all .12s; cursor:pointer; text-decoration:none; }
    .pc-btn:hover { background:#f8fafc; color:#0f172a; }
    .pc-btn-primary { background:#3b82f6; color:#fff; border-color:#3b82f6; }
    .pc-btn-primary:hover { background:#2563eb; color:#fff; }
    .pc-btn-primary:disabled { background:#cbd5e1; border-color:#cbd5e1; cursor:not-allowed; color:#fff; }
    .pc-divider { border-top:1px solid #f1f5f9; margin:18px 0; }
    .alert-flash { padding:10px 14px; background:#dcfce7; color:#166534; border:1px solid #bbf7d0; border-radius:10px; margin-bottom:14px; font-size:13px; }
    .pc-saved-tag { font-size:12px; color:#16a34a; font-weight:600; display:inline-flex; align-items:center; gap:4px; }
    .pc-saved-tag.saving { color:#3b82f6; }
</style>

<div class="pc-wrap">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert-flash">{{ session('success') }}</div>
        @endif
        <div id="pc_flash" class="alert-flash" style="display:none;"></div>

        <form id="pc_form" class="row g-3" autocomplete="off">
            @csrf
            <input type="hidden" name="id" id="pc_id" value="{{ $editing->id ?? '' }}">

            {{-- ============ LEFT: FORM ============ --}}
            <div class="col-lg-7">
                <div class="pc-card">
                    <h2 class="pc-title">{{ $editing ? 'Edit Calculation' : 'New Calculation' }}</h2>
                    <div class="pc-sub">Formula version: <code>{{ $r['formula_version'] ?? 'v1.1' }}</code> · Auto-saves once product name, cost, selling &amp; quantity are filled</div>

                    <div class="row g-3 mt-1">
                        <div class="col-12">
                            <label class="pc-label">Product Name *</label>
                            <input type="text" class="pc-input pc-live" name="product_name" value="{{ $d['product_name'] }}" placeholder="Smart watch X" required>
                        </div>
                        <div class="col-12">
                            <label class="pc-label">Product Category (optional)</label>
                            <input type="text" class="pc-input pc-live" name="product_category" value="{{ $d['product_category'] }}" placeholder="Electronics">
                        </div>
                    </div>

                    <div class="pc-section">Cost &amp; Pricing</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="pc-label">Cost Price / Unit *</label>
                            <input type="number" step="0.01" class="pc-input pc-live" name="cost_price" value="{{ $val($d['cost_price']) }}" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="pc-label">Selling Price / Unit *</label>
                            <input type="number" step="0.01" class="pc-input pc-live" name="selling_price" value="{{ $val($d['selling_price']) }}" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="pc-label">Marketing Cost / Unit *</label>
                            <input type="number" step="0.01" class="pc-input pc-live" name="marketing_cost" value="{{ $val($d['marketing_cost']) }}" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="pc-label">Fixed Cost / Unit (shipping etc.) *</label>
                            <input type="number" step="0.01" class="pc-input pc-live" name="shipping_cost" value="{{ $val($d['shipping_cost']) }}" placeholder="0.00">
                        </div>
                    </div>

                    <div class="pc-section">Order &amp; Return</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="pc-label">Target Delivered Units</label>
                            <input type="number" class="pc-input pc-live" name="target_delivered_units" id="pc_target" value="{{ $val($d['target_delivered_units']) }}" placeholder="e.g. 100">
                            <div class="pc-help" id="pc_target_help">Goal — used to compute required orders</div>
                        </div>
                        <div class="col-md-6">
                            <label class="pc-label">Quantity / Total Orders *</label>
                            <input type="number" class="pc-input pc-live" name="quantity" id="pc_qty" value="{{ $val($d['quantity']) }}" placeholder="Auto-fills from target">
                            <div class="pc-help" id="pc_qty_help">Total orders placed</div>
                        </div>
                        <div class="col-md-6">
                            <label class="pc-label">Call Cancel %</label>
                            <input type="number" step="0.01" min="0" max="100" class="pc-input pc-live" name="call_cancel_percentage" value="{{ $val($d['call_cancel_percentage']) }}" placeholder="0">
                            <div class="pc-help">Orders cancelled during confirmation call</div>
                        </div>
                        <div class="col-md-6">
                            <label class="pc-label">Return % *</label>
                            <input type="number" step="0.01" min="0" max="100" class="pc-input pc-live" name="return_percentage" value="{{ $val($d['return_percentage']) }}" placeholder="0">
                            <div class="pc-help">Between 0–100</div>
                        </div>
                        <div class="col-md-6">
                            <label class="pc-label">Delivery Profit / Sold Unit</label>
                            <input type="number" step="0.01" class="pc-input pc-live" name="extra_shipping_profit" value="{{ $val($d['extra_shipping_profit']) }}" placeholder="0.00">
                            <div class="pc-help">Extra profit per delivered product</div>
                        </div>
                        <div class="col-md-6">
                            <label class="pc-label">Loss / Returned Unit</label>
                            <input type="number" step="0.01" class="pc-input pc-live" name="per_return_loss" value="{{ $val($d['per_return_loss']) }}" placeholder="0.00">
                            <div class="pc-help">Extra loss per returned product</div>
                        </div>

                        <div class="col-12">
                            <label class="pc-label">Note (optional)</label>
                            <textarea class="pc-input pc-live" rows="2" name="note" placeholder="May Facebook campaign test...">{{ $d['note'] }}</textarea>
                        </div>
                    </div>

                    <div class="pc-divider"></div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <button type="submit" class="pc-btn pc-btn-primary" id="pc_save_btn">
                            <i class="mdi mdi-content-save-outline"></i> Save Calculation
                        </button>

                        @if($editing)
                            <a href="{{ route('admin.profit_calculator.print', $editing->id) }}" target="_blank" class="pc-btn">
                                <i class="mdi mdi-printer-outline"></i> Print / PDF
                            </a>
                            <a href="{{ route('admin.profit_calculator.export_csv_one', $editing->id) }}" class="pc-btn">
                                <i class="mdi mdi-file-export-outline"></i> Export CSV
                            </a>
                        @else
                            <button type="button" class="pc-btn" id="pc_print_btn" disabled><i class="mdi mdi-printer-outline"></i> Print / PDF</button>
                            <button type="button" class="pc-btn" id="pc_csv_btn"   disabled><i class="mdi mdi-file-export-outline"></i> Export CSV</button>
                        @endif

                        <button type="button" class="pc-btn" id="pc_reset_btn">
                            <i class="mdi mdi-refresh"></i> Reset
                        </button>

                        <span class="ms-auto pc-saved-tag" id="pc_saved_at" style="display:none;">
                            <i class="mdi mdi-check-circle"></i> <span class="pc-saved-text">Saved</span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- ============ RIGHT: RESULT PANEL ============ --}}
            <div class="col-lg-5">

                <div id="pc_net_card" class="pc-net-card {{ $r['health_status'] ?? 'loss' }} mb-3">
                    <span class="pc-badge" id="pc_badge" style="background: {{ $health['bg'] }};">{{ $health['label'] }}</span>
                    <div class="pc-net-label">Net Profit</div>
                    <div class="pc-net-value {{ ($r['net_profit'] ?? 0) < 0 ? 'neg' : '' }}" id="pc_net_value">
                        {{ PS::money($r['net_profit'] ?? 0) }}
                    </div>

                    <div class="row g-2 mt-3">
                        <div class="col-6">
                            <div class="pc-mini">
                                <div class="pc-mini-label">Margin</div>
                                <div class="pc-mini-value" id="pc_margin">{{ PS::percent($r['profit_margin'] ?? 0) }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="pc-mini">
                                <div class="pc-mini-label">ROI</div>
                                <div class="pc-mini-value" id="pc_roi">{{ PS::percent($r['roi'] ?? 0) }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="pc-mini">
                                <div class="pc-mini-label">Sold Units</div>
                                <div class="pc-mini-value" id="pc_sold">{{ PS::number($r['sold_units'] ?? 0, 0) }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="pc-mini">
                                <div class="pc-mini-label">Return Units</div>
                                <div class="pc-mini-value" id="pc_return">{{ PS::number($r['return_units'] ?? 0, 0) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pc-card mb-3">
                    <div class="pc-section" style="margin-top:0;">Breakdown</div>
                    <div class="pc-break-row"><span class="lbl">Total Sell</span><span class="val" data-bk="total_selling">{{ PS::money($r['total_selling'] ?? 0) }}</span></div>
                    <div class="pc-break-row"><span class="lbl">Total Cost</span><span class="val" data-bk="total_cost">{{ PS::money($r['total_cost'] ?? 0) }}</span></div>
                    <div class="pc-break-row"><span class="lbl">Total Marketing Cost</span><span class="val" data-bk="total_marketing_cost">{{ PS::money($r['total_marketing_cost'] ?? 0) }}</span></div>
                    <div class="pc-break-row"><span class="lbl">Total Product Cost</span><span class="val" data-bk="total_cost_price">{{ PS::money($r['total_cost_price'] ?? 0) }}</span></div>
                    <div class="pc-break-row"><span class="lbl">Total Fixed Cost</span><span class="val" data-bk="total_shipping_cost">{{ PS::money($r['total_shipping_cost'] ?? 0) }}</span></div>
                    <div class="pc-break-row"><span class="lbl">Gross Profit</span><span class="val pos" data-bk="gross_profit">{{ PS::money($r['gross_profit'] ?? 0) }}</span></div>
                    <div class="pc-break-row"><span class="lbl">Return Product Qty</span><span class="val" data-bk="return_units" data-num="0">{{ PS::number($r['return_units'] ?? 0, 0) }}</span></div>
                    <div class="pc-break-row"><span class="lbl">Return Product Value</span><span class="val neg" data-bk="return_deduction">{{ PS::money($r['return_deduction'] ?? 0) }}</span></div>
                    <div class="pc-break-row"><span class="lbl">Return Courier Loss</span><span class="val neg" data-bk="return_courier_loss">{{ PS::money($r['return_courier_loss'] ?? 0) }}</span></div>
                    <div class="pc-break-row"><span class="lbl">Success Delivery Profit</span><span class="val pos" data-bk="success_delivery_profit">{{ PS::money($r['success_delivery_profit'] ?? 0) }}</span></div>
                    <div class="pc-break-row"><span class="lbl">Net Profit</span><span class="val" data-bk="net_profit">{{ PS::money($r['net_profit'] ?? 0) }}</span></div>
                    <div class="pc-break-row"><span class="lbl">Profit Margin</span><span class="val" data-bk="profit_margin" data-fmt="pct">{{ PS::percent($r['profit_margin'] ?? 0) }}</span></div>
                    <div class="pc-break-row"><span class="lbl">ROI</span><span class="val" data-bk="roi" data-fmt="pct">{{ PS::percent($r['roi'] ?? 0) }}</span></div>
                    <div class="pc-break-row"><span class="lbl">Per Product Profit</span><span class="val" data-bk="per_product_profit">{{ PS::money($r['per_product_profit'] ?? 0) }}</span></div>
                    <div class="pc-break-row"><span class="lbl">Per Product Marketing Cost</span><span class="val" data-bk="per_product_marketing">{{ PS::money($r['per_product_marketing'] ?? 0) }}</span></div>
                    <div class="pc-break-row"><span class="lbl">Per Product Purchase Cost</span><span class="val" data-bk="per_product_purchase">{{ PS::money($r['per_product_purchase'] ?? 0) }}</span></div>
                    <div class="pc-break-row"><span class="lbl">Call Cancel %</span><span class="val" data-bk="call_cancel_percentage_pct" data-fmt="pct">{{ PS::percent($defaults['call_cancel_percentage'] ?? 0) }}</span></div>
                    <div class="pc-break-row" id="pc_required_row" style="{{ ($r['required_orders'] ?? 0) > 0 ? '' : 'display:none;' }}">
                        <span class="lbl">Required Orders (for target)</span><span class="val" data-bk="required_orders" data-num="0">{{ PS::number($r['required_orders'] ?? 0, 0) }}</span>
                    </div>
                    <div class="pc-break-row"><span class="lbl">Cancelled Marketing Loss</span><span class="val neg" data-bk="cancelled_marketing_loss">{{ PS::money($r['cancelled_marketing_loss'] ?? 0) }}</span></div>
                    <div class="pc-break-row"><span class="lbl">Required Ad Budget</span><span class="val" data-bk="required_ad_budget">{{ PS::money($r['required_ad_budget'] ?? 0) }}</span></div>
                    <div class="pc-break-row"><span class="lbl">Available Ad Budget</span><span class="val" data-bk="available_ad_budget">{{ PS::money($r['available_ad_budget'] ?? 0) }}</span></div>
                    <div class="pc-break-row"><span class="lbl">Budget Gap</span><span class="val" data-bk="budget_gap">{{ PS::money($r['budget_gap'] ?? 0) }}</span></div>
                    <div class="pc-break-row"><span class="lbl">Cash Locked in Courier</span><span class="val" data-bk="cash_locked_in_courier">{{ PS::money($r['cash_locked_in_courier'] ?? 0) }}</span></div>
                    <div class="pc-break-row"><span class="lbl">Reinvestment Capacity (days)</span><span class="val" data-bk="reinvestment_capacity_days" data-fmt="num1">{{ PS::number($r['reinvestment_capacity_days'] ?? 0, 1) }}</span></div>
                    <div class="pc-break-row"><span class="lbl">Marketing % of Revenue</span><span class="val" data-bk="marketing_cost_percent_of_revenue" data-fmt="pct">{{ PS::percent($r['marketing_cost_percent_of_revenue'] ?? 0) }}</span></div>
                </div>

                <div class="pc-insight">
                    <div class="ttl"><i class="mdi mdi-lightbulb-on-outline"></i> BIZ CARE Insight</div>
                    <div id="pc_insights">
                        @foreach(($r['insights'] ?? []) as $msg)
                            <div class="msg"><i class="mdi mdi-flash-outline text-info"></i> {{ $msg }}</div>
                        @endforeach
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
(function () {
    'use strict';
    const form     = document.getElementById('pc_form');
    const live     = form.querySelectorAll('.pc-live');
    const liveUrl  = @json(route('admin.profit_calculator.live'));
    const saveUrl  = @json(route('admin.profit_calculator.save'));
    const csrf     = form.querySelector('input[name="_token"]').value;
    const isEditing = !!document.getElementById('pc_id').value;

    // ----- track whether user has manually typed in Quantity -----
    let quantityTouched = isEditing && parseInt(document.getElementById('pc_qty').value) > 0;

    // ----- Indian-style formatter (matches PS::money/percent) -----
    function inum(v, decimals) {
        if (decimals === undefined) decimals = 2;
        v = parseFloat(v);
        if (!isFinite(v)) v = 0;
        const negative = v < 0;
        v = Math.abs(v);
        const parts = v.toFixed(decimals).split('.');
        let intPart = parts[0];
        const decPart = parts[1];
        if (intPart.length > 3) {
            const last3 = intPart.slice(-3);
            let rest = intPart.slice(0, -3);
            rest = rest.replace(/\B(?=(\d{2})+(?!\d))/g, ',');
            intPart = rest + ',' + last3;
        }
        let s = intPart + (decPart !== undefined ? '.' + decPart : '');
        return negative ? '-' + s : s;
    }
    const money = (v) => '৳ ' + inum(v, 2);
    const pct   = (v) => inum(v, 2) + '%';

    const healthMap = {
        excellent: { label:'Excellent',  bg:'#10b981' },
        good:      { label:'Good Zone',  bg:'#22c55e' },
        risky:     { label:'Risky Zone', bg:'#f59e0b' },
        loss:      { label:'Loss Zone',  bg:'#ef4444' },
    };

    function getField(name) { return form.querySelector('[name="' + name + '"]'); }
    function val(name) {
        const el = getField(name);
        return el ? el.value : '';
    }

    // ----- sync Quantity from Target Delivered Units (mirrors Livewire syncQuantityFromTarget) -----
    function syncQuantityFromTarget() {
        if (quantityTouched) return;
        const target = parseInt(val('target_delivered_units')) || 0;
        if (target <= 0) {
            // Target cleared → don't change qty (keeps any previously auto-filled value)
            return;
        }
        const cc = parseFloat(val('call_cancel_percentage')) || 0;
        const rp = parseFloat(val('return_percentage'))      || 0;
        const eff = (1 - cc / 100) * (1 - rp / 100);
        if (eff > 0) {
            const newQty = Math.ceil(target / eff);
            const qtyEl = document.getElementById('pc_qty');
            qtyEl.value = newQty;
            const help = document.getElementById('pc_qty_help');
            help.innerHTML = 'Auto-filled from target — clear to edit manually';
            help.classList.add('live');
        }
    }

    // ----- check whether all required fields are filled (for auto-save gate) -----
    function allRequiredFilled() {
        return val('product_name').trim() !== ''
            && parseFloat(val('cost_price'))    > 0
            && parseFloat(val('selling_price')) > 0
            && parseInt(val('quantity'))        > 0;
    }

    // ----- live recalc + auto-save (debounced) -----
    let recalcTimer = null;
    let saveTimer   = null;
    let isSaving    = false;

    function scheduleRecalc() {
        clearTimeout(recalcTimer);
        recalcTimer = setTimeout(recalculate, 300);
    }

    function scheduleAutoSave() {
        if (!allRequiredFilled()) return;
        clearTimeout(saveTimer);
        saveTimer = setTimeout(() => autoSave(), 800);
    }

    function recalculate() {
        const data = new FormData(form);
        fetch(liveUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept':'application/json' },
            body: data
        })
        .then(r => r.json())
        .then(r => render(r))
        .catch(() => {});
    }

    function render(r) {
        const netCard = document.getElementById('pc_net_card');
        netCard.className = 'pc-net-card ' + (r.health_status || 'loss') + ' mb-3';
        const netVal = document.getElementById('pc_net_value');
        netVal.textContent = money(r.net_profit);
        netVal.classList.toggle('neg', (r.net_profit || 0) < 0);

        const hb = healthMap[r.health_status] || healthMap.loss;
        const badge = document.getElementById('pc_badge');
        badge.style.background = hb.bg;
        badge.textContent = hb.label;

        document.getElementById('pc_margin').textContent = pct(r.profit_margin);
        document.getElementById('pc_roi').textContent    = pct(r.roi);
        document.getElementById('pc_sold').textContent   = inum(r.sold_units, 0);
        document.getElementById('pc_return').textContent = inum(r.return_units, 0);

        // Pull current Call Cancel % from input so live-display mirrors it
        const ccInput = form.querySelector('[name="call_cancel_percentage"]');
        const ccVal   = ccInput ? (parseFloat(ccInput.value) || 0) : 0;

        document.querySelectorAll('[data-bk]').forEach(el => {
            const key = el.getAttribute('data-bk');
            const fmt = el.getAttribute('data-fmt');
            const isNum = el.getAttribute('data-num') === '0';

            // Special: call_cancel_percentage_pct mirrors the form input (not in $r)
            if (key === 'call_cancel_percentage_pct') {
                el.textContent = pct(ccVal);
                return;
            }
            if (r[key] === undefined) return;

            if (fmt === 'pct')        el.textContent = pct(r[key]);
            else if (fmt === 'num1')  el.textContent = inum(r[key], 1);
            else if (isNum)           el.textContent = inum(r[key], 0);
            else                      el.textContent = money(r[key]);
        });

        const reqRow = document.getElementById('pc_required_row');
        if ((r.required_orders || 0) > 0) {
            reqRow.style.display = '';
            document.getElementById('pc_target_help').innerHTML = 'Required orders to hit target: <strong>' + inum(r.required_orders, 0) + '</strong>';
        } else {
            reqRow.style.display = 'none';
            document.getElementById('pc_target_help').textContent = 'Goal — used to compute required orders';
        }

        const insBox = document.getElementById('pc_insights');
        insBox.innerHTML = '';
        (r.insights || []).forEach(msg => {
            const d = document.createElement('div');
            d.className = 'msg';
            d.innerHTML = '<i class="mdi mdi-flash-outline text-info"></i> ' + escapeHtml(msg);
            insBox.appendChild(d);
        });
    }

    function autoSave() {
        if (isSaving) return;
        if (!allRequiredFilled()) return;
        isSaving = true;
        showSavingState();

        const data = new FormData(form);
        data.append('auto', '1');

        fetch(saveUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept':'application/json' },
            body: data
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                if (res.id) document.getElementById('pc_id').value = res.id;
                showSavedState();
                // Don't reload the page during auto-save — just keep editing
                enableLockedButtons(res.id);
            }
        })
        .catch(() => {})
        .finally(() => { isSaving = false; });
    }

    function enableLockedButtons(id) {
        // Activate Print/CSV buttons after first auto-save
        const printBtn = document.getElementById('pc_print_btn');
        const csvBtn   = document.getElementById('pc_csv_btn');
        if (printBtn && printBtn.tagName === 'BUTTON') {
            const a = document.createElement('a');
            a.href = '{{ url("admin/profit-calculator") }}/' + id + '/print';
            a.target = '_blank';
            a.className = 'pc-btn';
            a.innerHTML = '<i class="mdi mdi-printer-outline"></i> Print / PDF';
            printBtn.replaceWith(a);
        }
        if (csvBtn && csvBtn.tagName === 'BUTTON') {
            const a = document.createElement('a');
            a.href = '{{ url("admin/profit-calculator") }}/' + id + '/export/csv';
            a.className = 'pc-btn';
            a.innerHTML = '<i class="mdi mdi-file-export-outline"></i> Export CSV';
            csvBtn.replaceWith(a);
        }
    }

    function showSavingState() {
        const t = document.getElementById('pc_saved_at');
        t.style.display = 'inline-flex';
        t.classList.add('saving');
        t.querySelector('i').className = 'mdi mdi-loading mdi-spin';
        t.querySelector('.pc-saved-text').textContent = 'Saving...';
    }
    function showSavedState() {
        const t = document.getElementById('pc_saved_at');
        t.classList.remove('saving');
        t.querySelector('i').className = 'mdi mdi-check-circle';
        t.querySelector('.pc-saved-text').textContent = 'Saved at ' + new Date().toLocaleTimeString();
    }

    function escapeHtml(s) {
        return String(s || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
    }

    // ----- bind every live input -----
    live.forEach(el => {
        el.addEventListener('input', function () {
            const name = el.getAttribute('name');

            // Quantity touched tracking
            if (name === 'quantity') {
                const v = el.value;
                quantityTouched = !(v === '' || v === null || parseInt(v) === 0);
                const help = document.getElementById('pc_qty_help');
                if (quantityTouched) {
                    help.textContent = 'Manually overridden — clear to re-enable auto-fill';
                    help.classList.add('live');
                } else {
                    help.textContent = 'Total orders placed';
                    help.classList.remove('live');
                }
            }

            // Target/cc/return changes → sync Quantity
            if (name === 'target_delivered_units' || name === 'call_cancel_percentage' || name === 'return_percentage') {
                syncQuantityFromTarget();
            }

            scheduleRecalc();
            scheduleAutoSave();
        });
    });

    // ----- Manual save (button) -----
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = document.getElementById('pc_save_btn');
        btn.disabled = true;
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Saving...';

        const data = new FormData(form);
        fetch(saveUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept':'application/json' },
            body: data
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                if (res.id) document.getElementById('pc_id').value = res.id;
                const flash = document.getElementById('pc_flash');
                flash.textContent = res.msg;
                flash.style.display = 'block';
                showSavedState();
                setTimeout(() => { flash.style.display = 'none'; }, 2500);
                enableLockedButtons(res.id);
            } else {
                alert(res.msg || 'Save failed');
            }
        })
        .catch(() => alert('Network error'))
        .finally(() => { btn.disabled = false; btn.innerHTML = orig; });
    });

    // ----- Reset -----
    document.getElementById('pc_reset_btn').addEventListener('click', () => {
        if (!confirm('Reset all fields?')) return;
        form.reset();
        document.getElementById('pc_id').value = '';
        quantityTouched = false;
        document.getElementById('pc_saved_at').style.display = 'none';
        document.getElementById('pc_qty_help').textContent = 'Total orders placed';
        document.getElementById('pc_qty_help').classList.remove('live');
        document.getElementById('pc_target_help').textContent = 'Goal — used to compute required orders';
        recalculate();
    });

    // initial sync if loading edit with target value
    if (isEditing) syncQuantityFromTarget();
})();
</script>

@endsection
