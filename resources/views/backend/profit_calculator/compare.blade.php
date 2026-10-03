@extends('backend.app')
@section('content')

@php use App\Services\ProfitCalculatorService as PS; @endphp

<style>
    .pc-wrap { background:#f6f8fc; min-height:calc(100vh - 100px); padding:22px 8px; }
    .pc-card { background:#fff; border-radius:16px; padding:22px; box-shadow:0 1px 3px rgba(15,23,42,.05); }
    .pc-title { font-size:24px; font-weight:800; color:#0f172a; margin:0; display:flex; align-items:center; gap:10px; }
    .pc-title i { color:#3b82f6; }
    .pc-sub { color:#64748b; font-size:13px; margin-top:2px; }
    .pc-counter { background:#fff; border:1px solid #e2e8f0; border-radius:999px; padding:6px 16px; font-size:13px; font-weight:600; color:#475569; display:inline-flex; align-items:center; gap:6px; text-decoration:none; }
    .pc-pick { display:flex; align-items:center; justify-content:space-between; padding:10px 14px; border:1px solid #e2e8f0; border-radius:10px; margin-bottom:8px; cursor:pointer; transition:all .12s; background:#fff; }
    .pc-pick:hover { border-color:#cbd5e1; }
    .pc-pick.picked { border-color:#3b82f6; background:#eff6ff; }
    .pc-pick-name { font-weight:600; color:#0f172a; font-size:14px; }
    .pc-pick-cat { font-size:11px; color:#64748b; }
    .pc-pick-net { font-weight:700; font-size:14px; font-variant-numeric: tabular-nums; }
    .pc-pick-net.pos { color:#10b981; }
    .pc-pick-net.neg { color:#ef4444; }
    .pc-cmp-table { width:100%; border-collapse:collapse; font-size:13.5px; }
    .pc-cmp-table th, .pc-cmp-table td { padding:11px 14px; text-align:left; border-bottom:1px solid #f1f5f9; }
    .pc-cmp-table th { color:#475569; font-weight:600; background:#f8fafc; }
    .pc-cmp-table td.label { font-weight:600; color:#475569; background:#fafbfd; width:240px; }
    .pc-cmp-table td.val { font-weight:600; color:#0f172a; font-variant-numeric: tabular-nums; }
    .pc-cmp-table td.val.pos { color:#10b981; }
    .pc-cmp-table td.val.neg { color:#ef4444; }
    .pc-cmp-table td.best { background:#f0fdf4; }
    .pc-badge-h { padding:3px 12px; border-radius:999px; font-size:11px; font-weight:700; color:#fff; display:inline-block; }
    .alert-warn { padding:10px 14px; background:#fef3c7; color:#92400e; border:1px solid #fde68a; border-radius:10px; margin-bottom:14px; font-size:13px; }
</style>

<div class="pc-wrap">
    <div class="container-fluid">

        <form id="pc_compare_form" method="GET" action="{{ route('admin.profit_calculator.compare') }}">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                <div>
                    <h2 class="pc-title"><i class="mdi mdi-source-branch"></i> Compare Products</h2>
                    <div class="pc-sub">Compare 2–4 calculations side by side</div>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <span class="pc-counter"><span id="pc_count">{{ count($selected) }}</span>/4 selected</span>
                    @if(count($selected) > 0)
                        <a href="{{ route('admin.profit_calculator.compare') }}" class="pc-counter">
                            <i class="mdi mdi-close"></i> Clear
                        </a>
                    @endif
                    <button type="submit" class="pc-counter" style="background:#3b82f6;color:#fff;border-color:#3b82f6;">
                        <i class="mdi mdi-compare-horizontal"></i> Compare
                    </button>
                </div>
            </div>

            <div class="pc-card mb-3">
                <h6 class="mb-3" style="font-weight:700;">Select calculations</h6>
                @if($all->isEmpty())
                    <div class="text-center text-muted py-4">No saved calculations yet.</div>
                @else
                    <div class="row g-2">
                        @foreach($all as $rec)
                            @php $isPicked = in_array($rec->id, $selected); @endphp
                            <div class="col-md-6 col-lg-4">
                                <label class="pc-pick {{ $isPicked ? 'picked' : '' }}" data-id="{{ $rec->id }}">
                                    <input type="checkbox" name="ids[]" value="{{ $rec->id }}" {{ $isPicked ? 'checked' : '' }} class="d-none pc-check">
                                    <div>
                                        <div class="pc-pick-name">
                                            <span class="pc-check-mark">{{ $isPicked ? '✓ ' : '' }}</span>{{ $rec->product_name }}
                                        </div>
                                        <div class="pc-pick-cat">{{ $rec->product_category ?: '—' }}</div>
                                    </div>
                                    <div class="pc-pick-net {{ $rec->net_profit < 0 ? 'neg' : 'pos' }}">
                                        {{ PS::money($rec->net_profit) }}
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </form>

        @if($picked->count() >= 2)
            @php
                $maxNet = $picked->max('net_profit');
                $maxMargin = $picked->max('profit_margin');
                $maxRoi = $picked->max('roi');
                $minCost = $picked->min('total_cost');
            @endphp
            <div class="pc-card">
                <h6 class="mb-3" style="font-weight:700;">Side-by-side comparison</h6>
                <div class="table-responsive">
                    <table class="pc-cmp-table">
                        <thead>
                            <tr>
                                <th>Metric</th>
                                @foreach($picked as $p)
                                    <th>{{ $p->product_name }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="label">Health</td>
                                @foreach($picked as $p)
                                    @php $h = PS::healthLabel($p->health_status); @endphp
                                    <td><span class="pc-badge-h" style="background:{{ $h['bg'] }};">{{ $h['label'] }}</span></td>
                                @endforeach
                            </tr>
                            <tr>
                                <td class="label">Net Profit</td>
                                @foreach($picked as $p)
                                    <td class="val {{ $p->net_profit < 0 ? 'neg' : 'pos' }} {{ $p->net_profit == $maxNet ? 'best' : '' }}">{{ PS::money($p->net_profit) }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td class="label">Profit Margin</td>
                                @foreach($picked as $p)
                                    <td class="val {{ $p->profit_margin == $maxMargin ? 'best' : '' }}">{{ PS::percent($p->profit_margin) }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td class="label">ROI</td>
                                @foreach($picked as $p)
                                    <td class="val {{ $p->roi == $maxRoi ? 'best' : '' }}">{{ PS::percent($p->roi) }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td class="label">Total Selling</td>
                                @foreach($picked as $p)<td class="val">{{ PS::money($p->total_selling) }}</td>@endforeach
                            </tr>
                            <tr>
                                <td class="label">Total Cost</td>
                                @foreach($picked as $p)<td class="val {{ $p->total_cost == $minCost ? 'best' : '' }}">{{ PS::money($p->total_cost) }}</td>@endforeach
                            </tr>
                            <tr>
                                <td class="label">Gross Profit</td>
                                @foreach($picked as $p)<td class="val">{{ PS::money($p->gross_profit) }}</td>@endforeach
                            </tr>
                            <tr>
                                <td class="label">Sold Units</td>
                                @foreach($picked as $p)<td class="val">{{ PS::number($p->sold_units, 0) }}</td>@endforeach
                            </tr>
                            <tr>
                                <td class="label">Return Units</td>
                                @foreach($picked as $p)<td class="val">{{ PS::number($p->return_units, 0) }}</td>@endforeach
                            </tr>
                            <tr>
                                <td class="label">Total Orders</td>
                                @foreach($picked as $p)<td class="val">{{ $p->quantity }}</td>@endforeach
                            </tr>
                            <tr>
                                <td class="label">Cost / Unit</td>
                                @foreach($picked as $p)<td class="val">{{ PS::money($p->cost_price) }}</td>@endforeach
                            </tr>
                            <tr>
                                <td class="label">Selling / Unit</td>
                                @foreach($picked as $p)<td class="val">{{ PS::money($p->selling_price) }}</td>@endforeach
                            </tr>
                            <tr>
                                <td class="label">Marketing / Unit</td>
                                @foreach($picked as $p)<td class="val">{{ PS::money($p->marketing_cost) }}</td>@endforeach
                            </tr>
                            <tr>
                                <td class="label">Return %</td>
                                @foreach($picked as $p)<td class="val">{{ PS::percent($p->return_percentage) }}</td>@endforeach
                            </tr>
                            <tr>
                                <td class="label">Call Cancel %</td>
                                @foreach($picked as $p)<td class="val">{{ PS::percent($p->call_cancel_percentage) }}</td>@endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="pc-sub mt-2"><i class="mdi mdi-information-outline"></i> Green cells show the best value in each row.</div>
            </div>
        @elseif($picked->count() === 1)
            <div class="alert-warn"><i class="mdi mdi-alert-outline"></i> Select at least 2 calculations to compare.</div>
        @endif

    </div>
</div>

<script>
    // Visual toggle when checkbox state changes — limit to 4
    document.querySelectorAll('.pc-check').forEach(cb => {
        cb.addEventListener('change', function () {
            const checked = document.querySelectorAll('.pc-check:checked');
            if (checked.length > 4) {
                this.checked = false;
                alert('Maximum 4 items can be compared at once.');
                return;
            }
            const card = this.closest('.pc-pick');
            card.classList.toggle('picked', this.checked);
            card.querySelector('.pc-check-mark').textContent = this.checked ? '✓ ' : '';
            document.getElementById('pc_count').textContent = checked.length;
        });
    });
</script>

@endsection
