@extends('backend.app')
@section('content')

@php use App\Services\ProfitCalculatorService as PS; @endphp

<style>
    .pc-wrap { background:#f6f8fc; min-height:calc(100vh - 100px); padding:22px 8px; }
    .pc-title { font-size:24px; font-weight:800; color:#0f172a; margin:0; }
    .pc-sub { color:#64748b; font-size:13px; margin-top:2px; }
    .pc-btn { padding:10px 16px; border:1px solid #e2e8f0; background:#fff; border-radius:10px; font-size:13.5px; font-weight:600; color:#475569; display:inline-flex; align-items:center; gap:8px; transition:all .12s; cursor:pointer; text-decoration:none; }
    .pc-btn:hover { background:#f8fafc; color:#0f172a; }
    .pc-btn-primary { background:#3b82f6; color:#fff; border-color:#3b82f6; }
    .pc-btn-primary:hover { background:#2563eb; color:#fff; }
    .pc-search { background:#fff; border-radius:14px; padding:14px 18px; box-shadow:0 1px 3px rgba(15,23,42,.05); display:flex; align-items:center; gap:14px; flex-wrap:wrap; }
    .pc-search-input { flex:1; min-width:240px; border:none; padding:10px 14px; background:#f6f8fc; border-radius:10px; font-size:14px; outline:none; }
    .pc-chip { padding:6px 14px; border-radius:999px; font-size:12.5px; font-weight:600; background:#f1f5f9; color:#475569; cursor:pointer; border:none; text-decoration:none; }
    .pc-chip.active { background:#3b82f6; color:#fff; }
    .pc-empty { background:#fff; border-radius:14px; padding:60px 20px; text-align:center; color:#64748b; }
    .pc-card-item { background:#fff; border-radius:14px; padding:18px; box-shadow:0 1px 3px rgba(15,23,42,.05); transition:all .15s; height:100%; display:flex; flex-direction:column; }
    .pc-card-item:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(15,23,42,.08); }
    .pc-item-head { display:flex; justify-content:space-between; align-items:flex-start; gap:8px; margin-bottom:10px; }
    .pc-item-name { font-weight:700; color:#0f172a; font-size:15px; line-height:1.3; }
    .pc-item-cat { font-size:11px; color:#64748b; margin-top:2px; }
    .pc-fav { background:transparent; border:none; cursor:pointer; color:#cbd5e1; font-size:18px; padding:2px; }
    .pc-fav.on { color:#f59e0b; }
    .pc-item-profit { font-size:22px; font-weight:800; margin:6px 0; font-variant-numeric: tabular-nums; }
    .pc-item-profit.pos { color:#10b981; }
    .pc-item-profit.neg { color:#ef4444; }
    .pc-item-meta { display:flex; gap:14px; flex-wrap:wrap; font-size:12px; color:#64748b; margin-top:auto; padding-top:10px; border-top:1px solid #f1f5f9; }
    .pc-item-meta b { color:#0f172a; font-weight:700; }
    .pc-item-actions { display:flex; gap:6px; margin-top:10px; flex-wrap:wrap; }
    .pc-icon-btn { padding:6px 10px; font-size:12px; }
    .pc-badge-h { padding:2px 10px; border-radius:999px; font-size:10px; font-weight:700; color:#fff; }
    .alert-flash { padding:10px 14px; background:#dcfce7; color:#166534; border:1px solid #bbf7d0; border-radius:10px; margin-bottom:14px; font-size:13px; }
</style>

<div class="pc-wrap">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert-flash">{{ session('success') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div>
                <h2 class="pc-title">Calculation History</h2>
                <div class="pc-sub">All your saved profit calculations</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.profit_calculator.export_csv_all') }}" class="pc-btn">
                    <i class="mdi mdi-file-document-outline"></i> Export CSV
                </a>
                <a href="{{ route('admin.profit_calculator.index') }}" class="pc-btn pc-btn-primary">
                    <i class="mdi mdi-calculator"></i> New
                </a>
            </div>
        </div>

        <form id="pc_history_filter" method="GET" action="{{ route('admin.profit_calculator.history') }}">
            <div class="pc-search mb-3">
                <div style="display:flex; align-items:center; gap:8px; flex:1; min-width:240px; background:#f6f8fc; border-radius:10px; padding-left:14px;">
                    <i class="mdi mdi-magnify text-muted"></i>
                    <input type="text" class="pc-search-input" name="search" value="{{ $search }}" placeholder="Search by product name..." autocomplete="off" oninput="this.form.submit()">
                </div>
                <input type="hidden" name="filter" id="pc_filter" value="{{ $filter }}">
                <div class="d-flex gap-1">
                    <a href="javascript:void(0)" onclick="pcSetFilter('all')"        class="pc-chip {{ $filter === 'all' ? 'active' : '' }}">All</a>
                    <a href="javascript:void(0)" onclick="pcSetFilter('profitable')" class="pc-chip {{ $filter === 'profitable' ? 'active' : '' }}">Profitable</a>
                    <a href="javascript:void(0)" onclick="pcSetFilter('loss')"       class="pc-chip {{ $filter === 'loss' ? 'active' : '' }}">Loss</a>
                    <a href="javascript:void(0)" onclick="pcSetFilter('favorites')"  class="pc-chip {{ $filter === 'favorites' ? 'active' : '' }}">Favorites</a>
                </div>
            </div>
        </form>

        @if($items->isEmpty())
            <div class="pc-empty">
                <div style="font-size:36px;"><i class="mdi mdi-calculator-variant-outline text-muted"></i></div>
                <h5 class="mt-2 mb-3">No calculations yet</h5>
                <a href="{{ route('admin.profit_calculator.index') }}" class="pc-btn pc-btn-primary">Get Started</a>
            </div>
        @else
            <div class="row g-3">
                @foreach($items as $it)
                    @php $h = PS::healthLabel($it->health_status); @endphp
                    <div class="col-md-6 col-lg-4" id="pc_row_{{ $it->id }}">
                        <div class="pc-card-item">
                            <div class="pc-item-head">
                                <div>
                                    <div class="pc-item-name">{{ $it->product_name }}</div>
                                    @if($it->product_category)
                                        <div class="pc-item-cat">{{ $it->product_category }}</div>
                                    @endif
                                </div>
                                <button class="pc-fav {{ $it->is_favorite ? 'on' : '' }}" onclick="pcToggleFav({{ $it->id }}, this)">
                                    <i class="mdi {{ $it->is_favorite ? 'mdi-star' : 'mdi-star-outline' }}"></i>
                                </button>
                            </div>

                            <div>
                                <span class="pc-badge-h" style="background:{{ $h['bg'] }};">{{ $h['label'] }}</span>
                            </div>
                            <div class="pc-item-profit {{ $it->net_profit < 0 ? 'neg' : 'pos' }}">
                                {{ PS::money($it->net_profit) }}
                            </div>

                            <div class="pc-item-meta">
                                <span>Margin: <b>{{ PS::percent($it->profit_margin) }}</b></span>
                                <span>ROI: <b>{{ PS::percent($it->roi) }}</b></span>
                                <span>Qty: <b>{{ $it->quantity }}</b></span>
                            </div>

                            <div class="pc-item-actions">
                                <a href="{{ route('admin.profit_calculator.index', ['id' => $it->id]) }}" class="pc-btn pc-icon-btn">
                                    <i class="mdi mdi-pencil-outline"></i> Edit
                                </a>
                                <a href="{{ route('admin.profit_calculator.print', $it->id) }}" target="_blank" class="pc-btn pc-icon-btn">
                                    <i class="mdi mdi-printer-outline"></i> PDF
                                </a>
                                <a href="{{ route('admin.profit_calculator.export_csv_one', $it->id) }}" class="pc-btn pc-icon-btn">
                                    <i class="mdi mdi-file-export-outline"></i> CSV
                                </a>
                                <button type="button" class="pc-btn pc-icon-btn text-danger" onclick="pcDelete({{ $it->id }})">
                                    <i class="mdi mdi-trash-can-outline"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-3">
                {{ $items->links() }}
            </div>
        @endif

    </div>
</div>

<script>
    function pcSetFilter(f) {
        document.getElementById('pc_filter').value = f;
        document.getElementById('pc_history_filter').submit();
    }

    const _pcCsrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    function pcToggleFav(id, btn) {
        fetch('{{ url("admin/profit-calculator") }}/' + id + '/toggle-favorite', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': _pcCsrf, 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                btn.classList.toggle('on', res.is_favorite);
                btn.querySelector('i').className = 'mdi ' + (res.is_favorite ? 'mdi-star' : 'mdi-star-outline');
            }
        });
    }

    function pcDelete(id) {
        if (!confirm('Delete this calculation?')) return;
        fetch('{{ url("admin/profit-calculator") }}/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': _pcCsrf, 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const row = document.getElementById('pc_row_' + id);
                if (row) row.remove();
            }
        });
    }
</script>

@endsection
