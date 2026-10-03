@extends('backend.app')
@section('content')

@push('css')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --bg-body: #f3f4f6;
        --card-bg: #ffffff;
        --text-heading: #111827;
        --text-muted: #6b7280;
        --border-color: #e5e7eb;
        --primary: #2563eb;
        --success: #10b981;
        --danger: #ef4444;
        --warning: #f59e0b;
        --info: #0ea5e9;
        --radius: 12px;
        --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    body { font-family: 'Inter', sans-serif; background: var(--bg-body); }

    /* ----- Page Header ----- */
    .page-title { color: var(--text-heading); font-weight: 800; font-size: 1.5rem; margin-bottom: 0; letter-spacing: -0.5px; }
    .user-badge {
        background: var(--card-bg); padding: 8px 16px; border-radius: 50px;
        color: var(--text-heading); font-weight: 600; font-size: 0.9rem;
        border: 1px solid var(--border-color); display: inline-flex; align-items: center; gap: 8px;
    }

    /* ----- Filter Section ----- */
    .filter-section {
        background: var(--card-bg); border-radius: var(--radius); padding: 20px;
        border: 1px solid var(--border-color); margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    .form-label { color: var(--text-muted); font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
    .form-control {
        background: #f9fafb; border: 1px solid var(--border-color); border-radius: 8px;
        padding: 8px 12px; color: var(--text-heading); font-weight: 500; font-size: 0.9rem;
    }
    .form-control:focus { background: #fff; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }

    .btn-sync {
        background: var(--primary); color: #fff; border: none; border-radius: 8px;
        padding: 9px 15px; width: 100%; font-weight: 600; font-size: 0.9rem;
        transition: var(--transition);
    }
    .btn-sync:hover { background: #1d4ed8; color: #fff; }

    /* Quick Pills */
    .quick-range { display: flex; gap: 8px; width: 100%; }
    .quick-range .btn {
        background: #f9fafb; border: 1px solid var(--border-color); border-radius: 8px;
        font-size: 0.8rem; font-weight: 600; color: var(--text-muted); padding: 7px 0; flex: 1;
        transition: var(--transition);
    }
    .quick-range .btn:hover { background: #f3f4f6; color: var(--text-heading); }
    .quick-range .btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }

    /* ----- Admin Stat Cards ----- */
    .admin-card {
        background: var(--card-bg); border-radius: var(--radius);
        border: 1px solid var(--border-color); padding: 18px 20px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        display: flex; justify-content: space-between; align-items: center;
        transition: var(--transition); height: 100%; position: relative; overflow: hidden;
    }
    .admin-card:hover { border-color: #d1d5db; box-shadow: 0 8px 15px rgba(0, 0, 0, 0.08); transform: translateY(-4px); }

    .admin-card-content { z-index: 2; width: calc(100% - 55px); }
    .card-label {
        color: var(--text-muted); font-size: 0.78rem; font-weight: 600;
        text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;
    }
    .card-value { font-size: 1.4rem; font-weight: 700; color: var(--text-heading); line-height: 1; }

    .icon-box {
        width: 48px; height: 48px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center; font-size: 22px;
        flex-shrink: 0; z-index: 2;
    }

    /* Icon Colors */
    .icon-primary { background: rgba(37, 99, 235, 0.1); color: var(--primary); }
    .icon-success { background: rgba(16, 185, 129, 0.1); color: var(--success); }
    .icon-danger  { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
    .icon-warning { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
    .icon-info    { background: rgba(14, 165, 233, 0.1); color: var(--info); }
    .icon-purple  { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }

    /* Skeleton Loading */
    .skeleton {
        background: linear-gradient(90deg, #e5e7eb 25%, #f3f4f6 50%, #e5e7eb 75%);
        background-size: 200% 100%; animation: loading 1.5s infinite;
        border-radius: 4px; color: transparent !important; display: inline-block; min-width: 60px;
    }
    @keyframes loading { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

    /* Custom Row Gap */
    .gy-custom { row-gap: 20px; }

    /* Custom Animations */
    @keyframes fadeLeft {
        0% { opacity: 0; transform: translateX(-50px); }
        100% { opacity: 1; transform: translateX(0); }
    }
    @keyframes fadeRight {
        0% { opacity: 0; transform: translateX(50px); }
        100% { opacity: 1; transform: translateX(0); }
    }
    @keyframes fadeUp {
        0% { opacity: 0; transform: translateY(30px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    .animate-left { opacity: 0; animation: fadeLeft 0.6s cubic-bezier(0.25, 0.8, 0.25, 1) forwards; }
    .animate-right { opacity: 0; animation: fadeRight 0.6s cubic-bezier(0.25, 0.8, 0.25, 1) forwards; }
    .animate-up { opacity: 0; animation: fadeUp 0.6s cubic-bezier(0.25, 0.8, 0.25, 1) forwards; }
</style>
@endpush

@php
  $userStart    = optional(auth()->user()->created_at)->format('Y-m-d') ?? '2020-01-01';
  // ✅ ডিফল্ট স্টার্ট ডেট 2026-01-01 সেট করা আছে
  $startDateUi  = request('startDate', '2026-01-01');
  $endDateUi    = request('endDate', \Carbon\Carbon::now()->format('Y-m-d'));
@endphp

{{-- Header --}}
<div class="row mb-4 align-items-center">
  <div class="col-12 col-md-6">
    <div class="page-title-box">
      <h4 class="page-title">Dashboard Overview</h4>
      <span class="text-muted" style="font-size: 0.85rem;">Welcome back, track your metrics here.</span>
    </div>
  </div>
  <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
    <div class="user-badge">
        <i class="uil uil-user-circle text-primary" style="font-size: 1.2rem;"></i>
        {{ auth()->user()->first_name.' '.auth()->user()->last_name }}
    </div>
  </div>
</div>
{{-- ===== MASTER SERVER BROADCAST NOTICES ===== --}}
@if(!empty($masterNotices) && count($masterNotices) > 0)
<style>
    .master-notice {
        background: #fff; border-radius: 12px; padding: 16px 20px; margin-bottom: 14px;
        border: 1px solid var(--border-color); display: flex; align-items: flex-start; gap: 14px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        animation: noticeFadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
        border-left: 4px solid var(--primary);
    }
    .master-notice.notice-success { border-left-color: var(--success); }
    .master-notice.notice-warning { border-left-color: var(--warning); }
    .master-notice.notice-danger  { border-left-color: var(--danger); }
    .master-notice.notice-info    { border-left-color: var(--primary); }
    .master-notice .ico {
        width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
        background: rgba(37, 99, 235, 0.1); color: var(--primary);
    }
    .master-notice.notice-success .ico { background: rgba(16,185,129,0.1); color: var(--success); }
    .master-notice.notice-warning .ico { background: rgba(245,158,11,0.1); color: var(--warning); }
    .master-notice.notice-danger  .ico { background: rgba(239,68,68,0.1); color: var(--danger); }
    .master-notice .body { flex: 1; min-width: 0; }
    .master-notice .title {
        font-size: 0.95rem; font-weight: 700; color: var(--text-heading);
        margin: 0 0 4px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    }
    .master-notice .pill {
        font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        padding: 2px 8px; border-radius: 100px; background: rgba(37,99,235,0.1); color: var(--primary);
    }
    .master-notice.notice-success .pill { background: rgba(16,185,129,0.12); color: var(--success); }
    .master-notice.notice-warning .pill { background: rgba(245,158,11,0.12); color: var(--warning); }
    .master-notice.notice-danger  .pill { background: rgba(239,68,68,0.12); color: var(--danger); }
    .master-notice .text { color: var(--text-muted); font-size: 0.85rem; margin: 0; line-height: 1.5; word-break: break-word; }
    .master-notice .time { color: var(--text-muted); font-size: 0.72rem; margin-top: 6px; display: block; }
    @keyframes noticeFadeIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
</style>
<div class="row mb-3">
    <div class="col-12">
        @foreach($masterNotices as $notice)
            @php
                $type = $notice['type'] ?? 'info';
                $icoMap = ['info' => 'uil-info-circle', 'success' => 'uil-check-circle', 'warning' => 'uil-exclamation-triangle', 'danger' => 'uil-exclamation-octagon'];
                $ico = $icoMap[$type] ?? 'uil-megaphone';
                $pillMap = ['info' => 'Announcement', 'success' => 'Update', 'warning' => 'Warning', 'danger' => 'Critical'];
                $pill = $pillMap[$type] ?? 'Notice';
            @endphp
            <div class="master-notice notice-{{ $type }}">
                <div class="ico"><i class="uil {{ $ico }}"></i></div>
                <div class="body">
                    <div class="title">
                        <span>{{ $notice['title'] }}</span>
                        <span class="pill">{{ $pill }}</span>
                    </div>
                    <p class="text">{!! nl2br(e($notice['content'])) !!}</p>
                    @if(!empty($notice['created_at']))
                        <span class="time"><i class="uil uil-clock"></i> {{ \Carbon\Carbon::parse($notice['created_at'])->diffForHumans() }}</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif
{{-- Filter Section (Fade Up) --}}
<div class="filter-section animate-up" style="animation-delay: 0.05s;">
    <form action="{{ route('admin.dashboard') }}" method="GET">
        <div class="row align-items-end g-3">
            <div class="col-12 col-md-4 col-lg-3">
                <label class="form-label">Start Date</label>
                <input type="date" class="form-control" name="startDate" value="{{ $startDateUi }}" min="{{ $userStart }}">
            </div>
            <div class="col-12 col-md-4 col-lg-3">
                <label class="form-label">End Date</label>
                <input type="date" class="form-control" name="endDate" value="{{ $endDateUi }}" min="{{ $userStart }}">
            </div>
            <div class="col-12 col-md-4 col-lg-4">
                <label class="form-label">Quick Select</label>
                <div class="quick-range" role="group">
                    <button type="button" class="btn" data-range="today">Today</button>
                    <button type="button" class="btn" data-range="7d">7 Days</button>
                    <button type="button" class="btn" data-range="mtd">Month</button>
                    <button type="button" class="btn" data-range="ytd">Year</button>
                </div>
            </div>
            <div class="col-12 col-lg-2">
                <button type="button" id="refreshBtn" class="btn-sync">
                    <i class="uil uil-sync me-1"></i> Sync Data
                </button>
            </div>
        </div>
    </form>
</div>

<div class="row d-none" id="loader">
  <div class="col-12 text-center py-5">
    <div class="spinner-border text-primary" role="status"></div>
  </div>
</div>

{{-- Main Dashboard Data (Structured in Rows) --}}
<div id="dashboard_data_top">

  <h6 class="text-muted fw-bold text-uppercase mb-3 mt-2 animate-up" style="font-size: 0.8rem; letter-spacing: 1px; animation-delay: 0.1s;">Key Metrics</h6>

  {{-- ROW 1: Total Order | Order Value | Sourcing Cost | Gross Profit --}}
  <div class="row gy-custom mb-4">
    {{-- 1. Total Order --}}
    <div class="col-12 col-sm-6 col-lg-3 col-xl-3 animate-left" style="animation-delay: 0.10s;">
        <a href="{{ url('admin/orders') }}" class="text-decoration-none">
            <div class="admin-card">
                <div class="admin-card-content">
                    <div class="card-label">Total Order</div>
                    <div class="card-value total_orders skeleton">0</div>
                </div>
                <div class="icon-box icon-primary"><i class="uil uil-box"></i></div>
            </div>
        </a>
    </div>

    @unlessrole('worker')
    {{-- 2. Order Value --}}
    <div class="col-12 col-sm-6 col-lg-3 col-xl-3 animate-left" style="animation-delay: 0.15s;">
        <div class="admin-card">
            <div class="admin-card-content">
                <div class="card-label">Order Value</div>
                <div class="card-value total_order_val skeleton text-info">0</div>
            </div>
            <div class="icon-box icon-info"><i class="uil uil-shopping-cart-alt"></i></div>
        </div>
    </div>

    {{-- 3. Sourcing Cost --}}
    <div class="col-12 col-sm-6 col-lg-3 col-xl-3 animate-left" style="animation-delay: 0.20s;">
        <div class="admin-card">
            <div class="admin-card-content">
                <div class="card-label">Sourcing Cost</div>
                <div class="card-value sourcing_cost skeleton text-warning">0</div>
            </div>
            <div class="icon-box icon-warning"><i class="uil uil-package"></i></div>
        </div>
    </div>

    {{-- 4. Gross Profit --}}
    <div class="col-12 col-sm-6 col-lg-3 col-xl-3 animate-left" style="animation-delay: 0.25s;">
        <div class="admin-card">
            <div class="admin-card-content">
                <div class="card-label">Gross Profit</div>
                <div class="card-value gross_profit skeleton text-success">0</div>
            </div>
            <div class="icon-box icon-success"><i class="uil uil-chart-line"></i></div>
        </div>
    </div>
    @endunlessrole
  </div>


  {{-- ROW 2: Incomplete Order | Incomplete Value | Delivered Order | Delivered Sale --}}
  <div class="row gy-custom mb-4">
    {{-- 5. Incomplete Order --}}
    <div class="col-12 col-sm-6 col-lg-3 col-xl-3 animate-left" style="animation-delay: 0.30s;">
        <a href="{{ url('admin/orders?status=Incomplete') }}" class="text-decoration-none">
            <div class="admin-card">
                <div class="admin-card-content">
                    <div class="card-label">Incomplete Order</div>
                    <div class="card-value incomplete_orders skeleton text-warning">0</div>
                </div>
                <div class="icon-box icon-warning"><i class="uil uil-exclamation-circle"></i></div>
            </div>
        </a>
    </div>

    @unlessrole('worker')
    {{-- 6. Incomplete Value --}}
    <div class="col-12 col-sm-6 col-lg-3 col-xl-3 animate-left" style="animation-delay: 0.35s;">
        <div class="admin-card">
            <div class="admin-card-content">
                <div class="card-label">Incomplete Value</div>
                <div class="card-value total_incomplete_val skeleton text-danger">0</div>
            </div>
            <div class="icon-box icon-danger"><i class="uil uil-exclamation-octagon"></i></div>
        </div>
    </div>
    @endunlessrole

    {{-- 7. Delivered Order --}}
    <div class="col-12 col-sm-6 col-lg-3 col-xl-3 animate-left" style="animation-delay: 0.40s;">
        <a href="{{ url('admin/orders?status=Delivered') }}" class="text-decoration-none">
            <div class="admin-card">
                <div class="admin-card-content">
                    <div class="card-label">Delivered Order</div>
                    <div class="card-value complete_orders skeleton text-success">0</div>
                </div>
                <div class="icon-box icon-success"><i class="uil uil-check-circle"></i></div>
            </div>
        </a>
    </div>

    @unlessrole('worker')
    {{-- 8. Delivered Sale --}}
    <div class="col-12 col-sm-6 col-lg-3 col-xl-3 animate-left" style="animation-delay: 0.45s;">
        <div class="admin-card">
            <div class="admin-card-content">
                <div class="card-label">Delivered Sale</div>
                <div class="card-value total_sale_val skeleton text-success">0</div>
            </div>
            <div class="icon-box icon-success"><i class="uil uil-money-bill"></i></div>
        </div>
    </div>
    @endunlessrole
  </div>


  {{-- ROW 3: Pending Order | Cancel Order | Return Order | Return Value --}}
  <div class="row gy-custom mb-4">
    {{-- 9. Pending Order --}}
    <div class="col-12 col-sm-6 col-lg-3 col-xl-3 animate-right" style="animation-delay: 0.50s;">
        <a href="{{ url('admin/orders?status=Pending') }}" class="text-decoration-none">
            <div class="admin-card">
                <div class="admin-card-content">
                    <div class="card-label">Pending Order</div>
                    <div class="card-value pending_orders skeleton text-warning">0</div>
                </div>
                <div class="icon-box icon-warning"><i class="uil uil-clock-eight"></i></div>
            </div>
        </a>
    </div>

    {{-- 10. Cancel Order --}}
    <div class="col-12 col-sm-6 col-lg-3 col-xl-3 animate-right" style="animation-delay: 0.55s;">
        <a href="{{ url('admin/orders?status=Cancelled') }}" class="text-decoration-none">
            <div class="admin-card">
                <div class="admin-card-content">
                    <div class="card-label">Cancel Order</div>
                    <div class="card-value cancel_orders skeleton text-danger">0</div>
                </div>
                <div class="icon-box icon-danger"><i class="uil uil-times-circle"></i></div>
            </div>
        </a>
    </div>

    {{-- 11. Return Order --}}
    <div class="col-12 col-sm-6 col-lg-3 col-xl-3 animate-right" style="animation-delay: 0.60s;">
        <a href="{{ url('admin/orders?status=Returning') }}" class="text-decoration-none">
            <div class="admin-card">
                <div class="admin-card-content">
                    <div class="card-label">Return Order</div>
                    <div class="card-value return_orders skeleton text-danger">0</div>
                </div>
                <div class="icon-box icon-danger"><i class="uil uil-corner-up-left"></i></div>
            </div>
        </a>
    </div>

    @unlessrole('worker')
    {{-- 12. Return Value --}}
    <div class="col-12 col-sm-6 col-lg-3 col-xl-3 animate-right" style="animation-delay: 0.65s;">
        <div class="admin-card">
            <div class="admin-card-content">
                <div class="card-label">Return Value</div>
                <div class="card-value total_return_val skeleton text-danger">0</div>
            </div>
            <div class="icon-box icon-danger"><i class="uil uil-money-withdraw"></i></div>
        </div>
    </div>
    @endunlessrole
  </div>


  {{-- ROW 4: Courier Cost | Other Expense | Total Product | Stock Availability --}}
  <div class="row gy-custom mb-4">
    @unlessrole('worker')
    {{-- 13. Courier Cost --}}
    <div class="col-12 col-sm-6 col-lg-3 col-xl-3 animate-right" style="animation-delay: 0.70s;">
        <div class="admin-card">
            <div class="admin-card-content">
                <div class="card-label">Courier Cost</div>
                <div class="card-value total_courier_val skeleton text-warning">0</div>
            </div>
            <div class="icon-box icon-warning"><i class="uil uil-truck"></i></div>
        </div>
    </div>

    {{-- 14. Other Expense --}}
    <div class="col-12 col-sm-6 col-lg-3 col-xl-3 animate-right" style="animation-delay: 0.75s;">
        <a href="{{ url('admin/expenses') }}" class="text-decoration-none">
            <div class="admin-card">
                <div class="admin-card-content">
                    <div class="card-label">Other Expense</div>
                    <div class="card-value total_expense skeleton text-danger">0</div>
                </div>
                <div class="icon-box icon-danger"><i class="uil uil-wallet"></i></div>
            </div>
        </a>
    </div>
    @endunlessrole

    {{-- 15. Total Product --}}
    <div class="col-12 col-sm-6 col-lg-3 col-xl-3 animate-right" style="animation-delay: 0.80s;">
        <a href="{{ url('admin/products') }}" class="text-decoration-none">
            <div class="admin-card">
                <div class="admin-card-content">
                    <div class="card-label">Total Product</div>
                    <div class="card-value total_products skeleton text-success">0</div>
                </div>
                <div class="icon-box icon-success"><i class="uil uil-shopping-bag"></i></div>
            </div>
        </a>
    </div>

    {{-- 16. Stock Availability --}}
    <div class="col-12 col-sm-6 col-lg-3 col-xl-3 animate-right" style="animation-delay: 0.85s;">
        <a href="{{ url('admin/products') }}" class="text-decoration-none">
            <div class="admin-card">
                <div class="admin-card-content">
                    <div class="card-label">Stock Availability</div>
                    <div class="card-value total_stocks skeleton text-purple">0</div>
                </div>
                <div class="icon-box icon-purple"><i class="uil uil-layers"></i></div>
            </div>
        </a>
    </div>
  </div>

  {{-- Chart Row (Fade Up) --}}
  <div class="row mb-4 animate-up" style="animation-delay: 0.95s;">
    <div class="col-12">
        <div class="card border-0" style="border-radius: var(--radius); background: var(--card-bg); border: 1px solid var(--border-color) !important; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-4 text-uppercase" style="color: var(--text-heading); font-size: 0.85rem; letter-spacing: 0.5px;">Revenue Trend</h6>
                <div id="salesChart" style="min-height: 300px;"></div>
            </div>
        </div>
    </div>
  </div>

</div>

<div class="mt-4" id="dashboard_data"></div>

@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
  window.isWorker = @json(auth()->user()->hasRole('worker'));

  const nf = new Intl.NumberFormat('en-BD', { maximumFractionDigits: 0 });

  let salesChart;
  function initChart() {
      const options = {
          series: [{ name: 'Sales Revenue', data: [] }],
          chart: {
              type: 'area',
              height: 320,
              toolbar: { show: false },
              fontFamily: 'Inter, sans-serif'
          },
          colors: ['#2563eb'],
          dataLabels: { enabled: false },
          stroke: { curve: 'smooth', width: 3 },
          xaxis: { categories: [], tooltip: { enabled: false }, labels: { style: { colors: '#6b7280' } } },
          yaxis: {
              labels: {
                  style: { colors: '#6b7280' },
                  formatter: (value) => { return '৳' + nf.format(value) }
              }
          },
          fill: {
              type: 'gradient',
              gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.05, stops: [0, 100] }
          },
          grid: { borderColor: '#f3f4f6', strokeDashArray: 4 }
      };
      salesChart = new ApexCharts(document.querySelector("#salesChart"), options);
      salesChart.render();
  }

  function animateCount(el, to, duration=1000){
    const start = 0;
    const startTime = performance.now();
    el.classList.remove('skeleton');
    function frame(now){
      const p = Math.min((now - startTime) / duration, 1);
      const ease = p === 1 ? 1 : 1 - Math.pow(2, -10 * p);
      const val = Math.floor(start + (to - start) * ease);
      el.textContent = nf.format(val);
      if(p < 1) requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }

  function skeletonAll(){
    ['.total_orders','.pending_orders','.complete_orders','.cancell_orders','.cancel_orders','.return_orders','.incomplete_orders',
     '.total_expense','.total_net_profit','.gross_profit','.sourcing_cost','.low_stock_count',
     '.total_sale_val','.total_order_val','.total_courier_val','.total_incomplete_val',
     '.total_return_val','.total_products','.total_employees','.total_stocks'
    ].forEach(cls=>{
      const el = document.querySelector(cls);
      if(el){ el.classList.add('skeleton'); el.textContent = '0'; }
    });
  }

  function formatYMD(date) {
      const offset = date.getTimezoneOffset() * 60000;
      const localISOTime = (new Date(date.getTime() - offset)).toISOString().slice(0, 10);
      return localISOTime;
  }
  function dateDiffDays(a,b){ const A=new Date(a), B=new Date(b); return Math.floor((B-A)/(1000*60*60*24))+1; }

  function shiftBack(sd, ed){
    if(!sd || !ed) return {psd: '', ped: ''};
    const len = dateDiffDays(sd, ed);
    const s = new Date(sd); s.setDate(s.getDate()-len);
    const e = new Date(ed); e.setDate(e.getDate()-len);
    return {psd: formatYMD(s), ped: formatYMD(e)};
  }

  function applyRange(range){
    const sd = document.querySelector('input[name="startDate"]');
    const ed = document.querySelector('input[name="endDate"]');
    const today = new Date();
    let s, e = new Date();

    if(range==='today'){
      s = new Date();
    }else if(range==='7d'){
      s = new Date(); s.setDate(s.getDate()-6);
    }else if(range==='mtd'){
      s = new Date(today.getFullYear(), today.getMonth(), 1);
    }else if(range==='ytd'){
      s = new Date(today.getFullYear(), 0, 1);
    }else{ return; }

    const minStr = sd.getAttribute('min'); const minDate = new Date(minStr);
    if (s < minDate) s = minDate;

    sd.value = formatYMD(s);
    ed.value = formatYMD(e);

    document.querySelectorAll('.quick-range .btn').forEach(b=>b.classList.remove('active'));
    const btn = document.querySelector(`.quick-range .btn[data-range="${range}"]`);
    if(btn) btn.classList.add('active');

    fetchBoth();
  }

  function fetchJSON(url){ return fetch(url, {headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()); }

  function getRangeDates(){
    let sd = document.querySelector('input[name="startDate"]').value;
    let ed = document.querySelector('input[name="endDate"]').value;
    return {sd, ed};
  }

  function fetchBoth(){
    const url = @json(route('admin.getDashboardData2'));
    const {sd, ed} = getRangeDates();
    const {psd, ped} = shiftBack(sd, ed);

    const curUrl = `${url}?startDate=${encodeURIComponent(sd)}&endDate=${encodeURIComponent(ed)}`;
    const prvUrl = `${url}?startDate=${encodeURIComponent(psd)}&endDate=${encodeURIComponent(ped)}`;

    skeletonAll();

    Promise.all([fetchJSON(curUrl), fetchJSON(prvUrl)]).then(([cur, prv])=>{
      const sel = (s)=>document.querySelector(s);

      if(sel('.total_orders')) animateCount(sel('.total_orders'),    cur.total_orders||0);
      if(sel('.pending_orders')) animateCount(sel('.pending_orders'), cur.pending_orders||0);
      if(sel('.complete_orders')) animateCount(sel('.complete_orders'),cur.complete_orders||0);
      if(sel('.cancell_orders')) animateCount(sel('.cancell_orders'), cur.cancell_orders||0);
      if(sel('.cancel_orders')) animateCount(sel('.cancel_orders'), cur.cancel_orders||0);
      if(sel('.return_orders')) animateCount(sel('.return_orders'), cur.return_orders||0);
      if(sel('.incomplete_orders')) animateCount(sel('.incomplete_orders'), cur.incomplete_orders||0);

      if(!window.isWorker){
        if(sel('.total_sale_val')) animateCount(sel('.total_sale_val'), cur.total_sale_val||0);
        if(sel('.total_order_val')) animateCount(sel('.total_order_val'), cur.total_order_val||0);
        if(sel('.total_courier_val')) animateCount(sel('.total_courier_val'), cur.total_courier_val||0);
        if(sel('.total_incomplete_val')) animateCount(sel('.total_incomplete_val'), cur.total_incomplete_val||0);
        if(sel('.total_return_val')) animateCount(sel('.total_return_val'), cur.total_return_val||0);

        if(sel('.total_expense')) animateCount(sel('.total_expense'),    cur.totalExpense||0);
        if(sel('.total_net_profit')) animateCount(sel('.total_net_profit'), cur.profit||0);
        if(sel('.gross_profit')) animateCount(sel('.gross_profit'), cur.gross_profit||0);
        if(sel('.sourcing_cost')) animateCount(sel('.sourcing_cost'), cur.sourcing_cost||0);

        if(sel('.total_stocks')) animateCount(sel('.total_stocks'), cur.total_stocks||0);
        if(sel('.low_stock_count')) animateCount(sel('.low_stock_count'), cur.low_stock_count||0);
        if(sel('.total_products')) animateCount(sel('.total_products'), cur.total_products||0);
        if(sel('.total_employees')) animateCount(sel('.total_employees'), cur.total_employees||0);

        if(cur.chart_dates && cur.chart_totals && salesChart) {
            salesChart.updateSeries([{ data: cur.chart_totals }]);
            salesChart.updateOptions({ xaxis: { categories: cur.chart_dates } });
        }
      }
    }).catch(err => {
      console.error("Dashboard Data Fetch Error: ", err);
    });
  }

  document.addEventListener('DOMContentLoaded', function(){
    initChart();

    const sd = document.querySelector('input[name="startDate"]');
    const ed = document.querySelector('input[name="endDate"]');
    ed.min = sd.min;

    function clamp(){ if (ed.value && sd.value && ed.value < sd.value) ed.value = sd.value; }

    fetchBoth();

    sd.addEventListener('change', ()=>{
        clamp();
        document.querySelectorAll('.quick-range .btn').forEach(b=>b.classList.remove('active'));
        fetchBoth();
    });
    ed.addEventListener('change', ()=>{
        clamp();
        document.querySelectorAll('.quick-range .btn').forEach(b=>b.classList.remove('active'));
        fetchBoth();
    });

    document.getElementById('refreshBtn').addEventListener('click', ()=>{ fetchBoth(); });

    document.querySelectorAll('.quick-range .btn').forEach(btn=>{
      btn.addEventListener('click', ()=> applyRange(btn.dataset.range));
    });
  });
</script>
@endpush