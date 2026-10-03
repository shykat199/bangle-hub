@extends('backend.app')

@section('content')

{{-- ✅ Premium Custom CSS (Responsive Optimized) --}}
<style>
    /* Global Soft UI Colors */
    .bg-soft-primary { background-color: rgba(59, 130, 246, 0.1); color: #3b82f6 !important; }
    .bg-soft-success { background-color: rgba(16, 185, 129, 0.1); color: #10b981 !important; }
    .bg-soft-warning { background-color: rgba(245, 158, 11, 0.1); color: #f59e0b !important; }
    .bg-soft-danger  { background-color: rgba(239, 68, 68, 0.1);  color: #ef4444 !important; }
    .bg-soft-info    { background-color: rgba(6, 182, 212, 0.1);  color: #06b6d4 !important; }
    .bg-soft-purple  { background-color: rgba(139, 92, 246, 0.1); color: #8b5cf6 !important; }
    .bg-soft-dark    { background-color: rgba(55, 65, 81, 0.1);   color: #374151 !important; }

    /* Card Styling */
    .premium-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        transition: all 0.3s ease;
        background: #fff;
        overflow: hidden;
    }
    .premium-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    }

    /* Icon Box */
    .icon-box {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; margin-bottom: 8px;
    }

    /* Table Styling */
    .table-premium {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        table-layout: auto;
    }
    .table-premium thead th {
        background-color: #f9fafb;
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding: 12px 16px; 
        border-bottom: 1px solid #f3f4f6;
        white-space: nowrap; 
    }
    .table-premium tbody tr td {
        padding: 12px 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
        color: #374151;
        font-size: 0.9rem;
        white-space: nowrap; 
    }
    .table-premium tbody tr:last-child td {
        border-bottom: none;
    }
    .table-premium tbody tr:hover {
        background-color: #f9fafb;
    }

    /* User Avatar in Table */
    .user-avatar {
        width: 35px; height: 35px; 
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 13px;
        margin-right: 10px;
        flex-shrink: 0; 
    }

    /* Filter Box */
    .filter-box {
        background: #fff; border-radius: 16px;
        padding: 20px; box-shadow: 0 2px 12px rgba(0,0,0,0.02);
    }

    /* ✅ Responsive & Truncation Helpers */
    .text-truncate-custom {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
        max-width: 180px;
    }

    .transaction-truncate {
        max-width: 100px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
        vertical-align: bottom;
    }

    /* 📱 Mobile Responsive Tweaks */
    @media (max-width: 768px) {
        .page-title { font-size: 1.2rem !important; }
        .icon-box { width: 38px; height: 38px; font-size: 18px; }
        .table-premium thead th { padding: 8px 10px; font-size: 0.65rem; }
        .table-premium tbody tr td { padding: 8px 10px; font-size: 0.75rem; }
        .user-avatar { width: 30px; height: 30px; font-size: 11px; margin-right: 8px; }
        .text-truncate-custom { max-width: 120px; font-size: 0.75rem; }
        .badge { padding: 0.35em 0.65em; font-size: 0.65em !important; }
    }
    
    /* Scrollbar */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch; 
    }
    .floating-scrollbar {
        position: fixed;
        bottom: 0;
        height: 18px;
        overflow-x: auto;
        overflow-y: hidden;
        z-index: 99999;
        background: rgba(255, 255, 255, 0.8);
        border-top: 1px solid #e2e8f0;
        display: none;
    }
    .floating-scrollbar-content { height: 1px; }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            {{-- Header Section --}}
            <div class="row mt-4 mb-4 align-items-center">
                <div class="col-md-8 col-12 mb-3 mb-md-0">
                    <h4 class="page-title mb-1" style="font-weight: 700; color: #111827;">
                        Sales Analytics
                    </h4>
                    <p class="text-muted mb-0 small">Monitor your sales, profit, and order performance.</p>
                </div>
                <div class="col-md-4 col-12 text-md-end">
                    <div class="d-inline-flex align-items-center bg-white px-3 py-2 rounded-pill shadow-sm border">
                        <i class="uil uil-calendar-alt text-primary me-2"></i>
                        <span class="fw-semibold text-dark" style="font-size: 12px;">
                            {{ \Carbon\Carbon::parse($startDate)->format('d M') }} — {{ \Carbon\Carbon::parse($endDate)->format('d M, Y') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Filter Section --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="filter-box">
                        <form action="{{ route('admin.report.sales') }}" method="GET">
                            <div class="row align-items-end g-2">
                                <div class="col-6 col-lg-3">
                                    <label class="form-label text-muted fw-bold small text-uppercase" style="font-size: 10px;">Start Date</label>
                                    <input type="date" name="start_date" class="form-control form-control-sm bg-light border-0" value="{{ $startDate->format('Y-m-d') }}">
                                </div>
                                <div class="col-6 col-lg-3">
                                    <label class="form-label text-muted fw-bold small text-uppercase" style="font-size: 10px;">End Date</label>
                                    <input type="date" name="end_date" class="form-control form-control-sm bg-light border-0" value="{{ $endDate->format('Y-m-d') }}">
                                </div>
                                <div class="col-12 col-lg-3">
                                    <label class="form-label text-muted fw-bold small text-uppercase" style="font-size: 10px;">Status</label>
                                    <select name="status" class="form-select form-select-sm bg-light border-0">
                                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Orders</option>
                                        
                                        {{-- ✅ ডাইনামিক ভাবে সব স্ট্যাটাস লোড করা হচ্ছে --}}
                                        @if(isset($orderStatuses))
                                            @foreach($orderStatuses as $key => $value)
                                                <option value="{{ $key }}" {{ $status == $key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        @else
                                            {{-- Fallback Options --}}
                                            <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="processing" {{ $status == 'processing' ? 'selected' : '' }}>Processing</option>
                                            <option value="shipped" {{ $status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                            <option value="courier" {{ $status == 'courier' ? 'selected' : '' }}>Courier</option>
                                            <option value="delivered" {{ $status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                            <option value="canceled" {{ $status == 'canceled' ? 'selected' : '' }}>Canceled</option>
                                            <option value="return" {{ $status == 'return' ? 'selected' : '' }}>Returned</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-12 col-lg-3 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold shadow-sm rounded-pill">
                                        Filter
                                    </button>
                                    <a href="{{ route('admin.report.sales') }}" class="btn btn-light btn-sm rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Reset">
                                        <i class="uil uil-refresh"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Stat Cards --}}
            <div class="row g-3">
                {{-- Total Sales --}}
                <div class="col-xl-3 col-md-6 col-6">
                    <div class="card premium-card">
                        <div class="card-body p-3">
                            <div class="icon-box bg-soft-primary rounded-3 mb-2">
                                <i class="uil uil-money-bill"></i>
                            </div>
                            <p class="stat-title mb-0 text-muted small text-uppercase fw-bold">Total Sales</p>
                            <h4 class="stat-value text-dark mb-0 fw-bold">৳{{ number_format($totalSales, 0) }}</h4>
                        </div>
                    </div>
                </div>

                {{-- Online Paid --}}
                <div class="col-xl-3 col-md-6 col-6">
                    <div class="card premium-card">
                        <div class="card-body p-3">
                            <div class="icon-box bg-soft-purple rounded-3 mb-2">
                                <i class="uil uil-credit-card"></i>
                            </div>
                            <p class="stat-title mb-0 text-muted small text-uppercase fw-bold">Online Paid</p>
                            <h4 class="stat-value text-dark mb-0 fw-bold">৳{{ number_format($totalOnlinePaid, 0) }}</h4>
                        </div>
                    </div>
                </div>

                {{-- Net Sales --}}
                <div class="col-xl-3 col-md-6 col-6">
                    <div class="card premium-card">
                        <div class="card-body p-3">
                            <div class="icon-box bg-soft-info rounded-3 mb-2">
                                <i class="uil uil-invoice"></i>
                            </div>
                            <p class="stat-title mb-0 text-muted small text-uppercase fw-bold">Net Sales</p>
                            <h4 class="stat-value text-dark mb-0 fw-bold">৳{{ number_format($netSales, 0) }}</h4>
                        </div>
                    </div>
                </div>

                {{-- Net Profit --}}
                <div class="col-xl-3 col-md-6 col-6">
                    <div class="card premium-card {{ $grossProfit >= 0 ? 'bg-success' : 'bg-danger' }}">
                        <div class="card-body p-3">
                            <div class="icon-box bg-white bg-opacity-25 text-white rounded-3 mb-2">
                                <i class="uil uil-chart-line"></i>
                            </div>
                            <p class="stat-title text-white-50 mb-0 small text-uppercase fw-bold">Net Profit</p>
                            <h4 class="stat-value text-white mb-0 fw-bold">৳{{ number_format($grossProfit, 0) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ✅ DYNAMIC: All Status Cards --}}
            <div class="row g-2 mt-1">
                @if(isset($orderStatuses))
                    @foreach($orderStatuses as $key => $label)
                        @php
                            $count = $orders->filter(fn($q) => strtolower($q->status) == strtolower($key))->count();
                            
                            $colorClass = 'bg-soft-dark'; // Default
                            $k = strtolower($key);
                            
                            if (in_array($k, ['pending'])) {
                                $colorClass = 'bg-soft-warning';
                            } elseif (in_array($k, ['processing', 'confirmed', 'on_hold'])) {
                                $colorClass = 'bg-soft-info';
                            } elseif (in_array($k, ['shipped', 'courier'])) {
                                $colorClass = 'bg-soft-primary';
                            } elseif (in_array($k, ['complete', 'delivered', 'courier_complete'])) {
                                $colorClass = 'bg-soft-success';
                            } elseif (in_array($k, ['return', 'returned', 'return received', 'return missing', 'canceled', 'cancelled', 'returning'])) {
                                $colorClass = 'bg-soft-danger';
                            }
                        @endphp
                        
                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                            <div class="card premium-card p-2 text-center {{ $colorClass }} border-0 h-100">
                                <span class="text-dark small fw-bold d-block text-truncate" title="{{ $label }}">{{ $label }}</span>
                                <h5 class="mb-0 fw-bold text-dark">{{ $count }}</h5>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="row mt-3">
                {{-- Left Side Stats --}}
                <div class="col-xl-4">
                    <div class="card premium-card mb-3">
                        <div class="card-body py-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-soft-purple rounded-3 me-3 mb-0" style="width: 40px; height: 40px; font-size: 18px;">
                                    <i class="uil uil-truck"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold">৳{{ number_format($totalDelivery, 0) }}</h5>
                                    <span class="text-muted small">Delivery Cost</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card premium-card mb-3">
                        <div class="card-body py-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-soft-primary rounded-3 me-3 mb-0" style="width: 40px; height: 40px; font-size: 18px;">
                                    <i class="uil uil-clipboard-notes"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold">{{ $totalOrders }}</h5>
                                    <span class="text-muted small">Total Orders</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @php 
                        $margin = $netSales > 0 ? ($grossProfit / $netSales) * 100 : 0;
                    @endphp
                    <div class="card premium-card bg-primary text-white text-center p-3">
                        <h2 class="mb-0 text-white fw-bold">{{ number_format($margin, 1) }}%</h2>
                        <span class="text-white-50 text-uppercase fw-bold" style="font-size: 10px;">Net Profit Margin</span>
                    </div>
                </div>

                {{-- Chart --}}
                <div class="col-xl-8 mt-3 mt-xl-0">
                    <div class="card premium-card h-100">
                        <div class="card-body">
                            <h5 class="header-title mb-3 text-uppercase text-muted small fw-bold">Revenue Trend</h5>
                            <div style="height: 250px;">
                                <canvas id="salesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ✅ TOP SOLD PRODUCTS TABLE --}}
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card premium-card">
                        <div class="card-header bg-white border-bottom p-3">
                            <h5 class="text-dark fw-bold mb-0" style="font-size: 1rem;">🔥 Top Selling Products</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-premium w-100 mb-0 align-middle">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">Product Name</th>
                                            <th class="text-center">Sold Qty</th>
                                            <th class="text-end pe-4">Total Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($soldProducts as $item)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    @if(optional($item->product)->image)
                                                        <img src="{{ asset('products/' . $item->product->image) }}" class="rounded me-2 border" width="35" height="35" alt="">
                                                    @else
                                                        <div class="rounded me-2 border bg-light d-flex align-items-center justify-content-center" style="width:35px; height:35px;">
                                                            <i class="uil uil-image"></i>
                                                        </div>
                                                    @endif
                                                    <span class="fw-semibold text-dark text-truncate-custom" title="{{ $item->product->name ?? 'Deleted Product' }}">
                                                        {{ $item->product->name ?? 'Deleted Product' }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-soft-primary text-primary rounded-pill px-2">{{ $item->total_qty }}</span>
                                            </td>
                                            <td class="text-end pe-4 fw-bold text-dark">
                                                ৳{{ number_format($item->total_amount, 0) }}
                                            </td>
                                        </tr>
                                        @endforeach
                                        @if($soldProducts->isEmpty())
                                        <tr><td colspan="3" class="text-center py-4 text-muted small">No data available.</td></tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Order Breakdown Table --}}
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card premium-card">
                        <div class="card-body p-0">
                            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0 fw-bold text-dark" style="font-size: 1rem;">Order List</h5>
                                </div>
                                <div>
                                    <a href="{{ route('admin.report.sales.export', request()->all()) }}" class="btn btn-xs btn-light border fw-bold text-muted">
                                        <i class="uil uil-file-download-alt me-1"></i> CSV
                                    </a>
                                </div>
                            </div>

                            <div class="table-responsive" id="salesTableContainer">
                                <table class="table table-premium w-100 mb-0 align-middle" id="salesTable">
                                    <thead>
                                        <tr>
                                            <th class="ps-3">Inv.</th>
                                            <th>Customer</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Payment</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Total</th>
                                            <th class="text-end">Profit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($orders as $order)
                                        @php
                                            $orderCost = 0;
                                            foreach($order->details as $d) {
                                                $pp = $d->product ? ($d->product->purchase_price ?? 0) : 0;
                                                $orderCost += ($pp * $d->quantity);
                                            }
                                            $delivery = $order->shipping_charge ?? 0;
                                            $orderProfit = ($order->final_amount - $delivery) - $orderCost;
                                            $colors = ['bg-soft-primary text-primary', 'bg-soft-success text-success', 'bg-soft-danger text-danger', 'bg-soft-warning text-warning', 'bg-soft-info text-info'];
                                            $randColor = $colors[$order->id % count($colors)];
                                            $isOnline = !empty($order->transaction_id);
                                        @endphp
                                        <tr>
                                            <td class="ps-3">
                                                <a href="{{ route('admin.orders.show', $order->id) }}" class="fw-bold text-primary text-decoration-none">
                                                    #{{ $order->invoice_no }}
                                                </a>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center" style="max-width: 150px;">
                                                    <div class="user-avatar {{ $randColor }}">
                                                        {{ strtoupper(substr($order->first_name, 0, 1)) }}
                                                    </div>
                                                    <div class="text-truncate">
                                                        <span class="d-block fw-bold text-dark text-truncate" style="font-size: 0.85rem;">{{ $order->first_name }}</span>
                                                        <span class="d-block text-muted small">{{ $order->mobile }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="d-block text-dark fw-medium" style="font-size: 0.8rem;">{{ $order->created_at->format('d M') }}</span>
                                                <span class="text-muted" style="font-size: 0.7rem;">{{ $order->created_at->format('h:i A') }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $statusMap = [
                                                        'pending' => ['bg-soft-warning', 'text-warning', 'Pending'],
                                                        'processing' => ['bg-soft-info', 'text-info', 'Process'],
                                                        'courier' => ['bg-soft-primary', 'text-primary', 'Courier'],
                                                        'complete' => ['bg-soft-success', 'text-success', 'Done'],
                                                        'delivered' => ['bg-soft-success', 'text-success', 'Done'],
                                                        'courier_complete' => ['bg-soft-success', 'text-success', 'Done'],
                                                        'return' => ['bg-soft-danger', 'text-danger', 'Return'],
                                                        'returned' => ['bg-soft-danger', 'text-danger', 'Return'],
                                                        'canceled' => ['bg-soft-dark', 'text-dark', 'Cancel'],
                                                        'cancelled' => ['bg-soft-dark', 'text-dark', 'Cancel'],
                                                        'return missing' => ['bg-soft-danger', 'text-danger', 'Missing'],
                                                        'return received' => ['bg-soft-danger', 'text-danger', 'Return'],
                                                    ];
                                                    $currStatus = strtolower($order->status);
                                                    $badge = $statusMap[$currStatus] ?? ['bg-soft-dark', 'text-dark', substr($order->status, 0, 6)];
                                                @endphp
                                                <span class="badge {{ $badge[0] }} {{ $badge[1] }} border border-0 rounded-pill px-2 py-1 fw-bold" style="font-size: 10px;">
                                                    {{ strtoupper($badge[2]) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($isOnline)
                                                    <span class="badge bg-soft-success text-success border-0 px-1" style="font-size: 9px;">SSL</span>
                                                    <div class="transaction-truncate text-muted" style="font-size: 9px;" title="{{ $order->transaction_id }}">
                                                        {{ $order->transaction_id }}
                                                    </div>
                                                @else
                                                    <span class="badge bg-light text-muted border px-1" style="font-size: 9px;">COD</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border rounded-pill px-2">{{ $order->details->sum('quantity') }}</span>
                                            </td>
                                            <td class="text-end fw-bold text-dark">
                                                ৳{{ number_format($order->final_amount, 0) }}
                                            </td>
                                            <td class="text-end pe-3">
                                                @if($orderProfit >= 0)
                                                    <span class="text-success fw-bold text-nowrap" style="font-size: 0.8rem;">
                                                        <i class="uil uil-arrow-up small"></i>{{ number_format($orderProfit, 0) }}
                                                    </span>
                                                @else
                                                    <span class="text-danger fw-bold text-nowrap" style="font-size: 0.8rem;">
                                                        <i class="uil uil-arrow-down small"></i>{{ number_format($orderProfit, 0) }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                        
                                        @if($orders->isEmpty())
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <div class="py-3">
                                                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" alt="No Data" height="40" class="mb-2 opacity-25 grayscale">
                                                    <h6 class="text-muted fw-bold small">No orders found.</h6>
                                                </div>
                                            </td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            
                            @if(!$orders->isEmpty())
                            <div class="p-2 border-top bg-light text-center">
                                <small class="text-muted" style="font-size: 10px;">Showing {{ $orders->count() }} records</small>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div> 
    </div> 
</div>

{{-- ✅ FLOATING SCROLLBAR --}}
<div id="floating-scrollbar" class="floating-scrollbar">
    <div id="floating-scrollbar-content" class="floating-scrollbar-content"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // --- Sticky Scrollbar Logic ---
        const tableContainer = document.getElementById('salesTableContainer');
        const table = document.getElementById('salesTable');
        const floatingScrollbar = document.getElementById('floating-scrollbar');
        const floatingContent = document.getElementById('floating-scrollbar-content');

        if(tableContainer && table && floatingScrollbar) {
            function syncScroll() {
                const tableWidth = table.scrollWidth;
                const containerWidth = tableContainer.clientWidth;
                if (tableWidth > containerWidth) {
                    floatingScrollbar.style.display = 'block';
                    floatingScrollbar.style.width = containerWidth + 'px';
                    floatingContent.style.width = tableWidth + 'px';
                    const rect = tableContainer.getBoundingClientRect();
                    floatingScrollbar.style.left = rect.left + 'px';
                } else {
                    floatingScrollbar.style.display = 'none';
                }
            }
            floatingScrollbar.addEventListener('scroll', function() { tableContainer.scrollLeft = floatingScrollbar.scrollLeft; });
            tableContainer.addEventListener('scroll', function() { floatingScrollbar.scrollLeft = tableContainer.scrollLeft; });
            syncScroll();
            window.addEventListener('resize', syncScroll);
            window.addEventListener('scroll', function() {
                const rect = tableContainer.getBoundingClientRect();
                const windowHeight = window.innerHeight;
                if (rect.bottom < windowHeight || rect.top > windowHeight) {
                      floatingScrollbar.style.display = 'none'; 
                      if(rect.bottom > windowHeight && rect.top < windowHeight) { floatingScrollbar.style.display = 'block'; }
                } else { floatingScrollbar.style.display = 'none'; }
                if (rect.top < windowHeight && rect.bottom > windowHeight) { floatingScrollbar.style.display = 'block'; } 
                else { floatingScrollbar.style.display = 'none'; }
                const rectUpdate = tableContainer.getBoundingClientRect();
                floatingScrollbar.style.left = rectUpdate.left + 'px';
            });
        }

        // --- Chart Logic ---
        const ctx = document.getElementById('salesChart').getContext('2d');
        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.25)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.01)');

        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Revenue',
                    data: @json($chartData),
                    backgroundColor: gradient,
                    borderColor: '#3b82f6',
                    borderWidth: 2,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#3b82f6',
                    pointBorderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 10,
                        cornerRadius: 6,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return ' Sales: ৳' + new Intl.NumberFormat('en-BD').format(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f3f4f6', borderDash: [5, 5] },
                        ticks: { font: { size: 10, family: "'Inter', sans-serif" }, color: '#9ca3af', padding: 8 },
                        border: { display: false }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10, family: "'Inter', sans-serif" }, color: '#9ca3af', padding: 8 },
                        border: { display: false }
                    }
                },
                interaction: { intersect: false, mode: 'index' },
            }
        });
    });
</script>
@endsection