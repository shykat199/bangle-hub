@extends('backend.app')

@section('content')
<style>
    /* ✅ Premium Responsive Table Styling */
    .premium-table {
        width: 100%;
        min-width: 900px;
        margin-bottom: 0;
    }
    .premium-table th {
        background-color: #f8fafc;
        color: #475569;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e2e8f0;
        padding: 12px 10px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .premium-table td {
        vertical-align: middle;
        padding: 12px 10px;
        color: #334155;
        font-size: 13px;
        border-bottom: 1px solid #f1f5f9;
    }
    .premium-table tbody tr:hover {
        background-color: #f8fafc;
    }
    .product-name-text {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.4;
        font-weight: 600;
        color: #1e293b;
        font-size: 13px;
        white-space: normal;
    }
    .stat-badge {
        padding: 4px 6px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
        margin-top: 3px;
    }
    .img-thumbnail-custom {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
</style>

<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="card-title fw-bold text-dark mb-0">
                    <i class="mdi mdi-chart-areaspline text-primary me-2"></i>Advanced Product Performance
                </h5>
            </div>
            
            <div class="card-body">
                <form action="{{ route('admin.report.product_performance') }}" method="GET" class="mb-3">
                    <div class="row g-2 align-items-end bg-light p-2 rounded-3 border">
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                            <label for="search" class="form-label text-muted small fw-bold mb-1">Search Product</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="mdi mdi-magnify text-muted"></i></span>
                                <input type="text" name="q" id="search" class="form-control border-start-0 ps-0" placeholder="Product name or SKU..." value="{{ request('q') }}">
                            </div>
                        </div>

                        <div class="col-xl-2 col-lg-2 col-md-3 col-sm-6">
                            <label for="start_date" class="form-label text-muted small fw-bold mb-1">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
                        </div>

                        <div class="col-xl-2 col-lg-2 col-md-3 col-sm-6">
                            <label for="end_date" class="form-label text-muted small fw-bold mb-1">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ request('end_date', $endDate->format('Y-m-d')) }}">
                        </div>

                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-6">
                            <label class="form-label text-muted small fw-bold mb-1">Order Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="all">All Status</option>
                                @foreach($orderStatuses as $key => $val)
                                    <option value="{{ $key }}" {{ $statusFilter == $key ? 'selected' : '' }}>{{ $val }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-2 col-lg-2 col-md-3 col-sm-6">
                            <label class="form-label text-muted small fw-bold mb-1">View By</label>
                            <select name="group_by" class="form-select form-select-sm">
                                <option value="product" {{ $groupBy == 'product' ? 'selected' : '' }}>Overall</option>
                                <option value="date" {{ $groupBy == 'date' ? 'selected' : '' }}>Daily</option>
                                <option value="month" {{ $groupBy == 'month' ? 'selected' : '' }}>Monthly</option>
                            </select>
                        </div>

                        <div class="col-xl-1 col-lg-1 col-md-3 col-sm-12 d-flex gap-1 mt-2 mt-md-0">
                            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold" title="Filter"><i class="mdi mdi-filter"></i></button>
                            <a href="{{ route('admin.report.product_performance') }}" class="btn btn-secondary btn-sm w-100 fw-bold" title="Reset"><i class="mdi mdi-refresh"></i></a>
                        </div>
                    </div>
                </form>

                <div class="row mb-3">
                    <div class="col-12">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($orderStatuses as $key => $val)
                                @php
                                    // ✅ FIX: Case-sensitivity fix using strtolower
                                    $qty = $statusSummary[strtolower($key)] ?? 0;
                                    $bgClass = 'bg-white border text-dark';
                                    $textClass = 'text-muted';
                                    
                                    $s = strtolower($key);
                                    if(in_array($s, ['delivered', 'completed'])) { $bgClass = 'bg-success-subtle border-success'; $textClass = 'text-success'; }
                                    elseif(in_array($s, ['pending', 'on hold'])) { $bgClass = 'bg-warning-subtle border-warning'; $textClass = 'text-warning'; }
                                    elseif(in_array($s, ['cancelled', 'returning', 'return received', 'return missing', 'cancell'])) { $bgClass = 'bg-danger-subtle border-danger'; $textClass = 'text-danger'; }
                                    elseif(in_array($s, ['shipped', 'courier', 'processing', 'confirmed', 'scheduled'])) { $bgClass = 'bg-primary-subtle border-primary'; $textClass = 'text-primary'; }
                                @endphp
                                
                                <div class="{{ $bgClass }} rounded px-2 py-1 d-flex align-items-center justify-content-between shadow-sm" style="flex: 1 1 auto; min-width: 130px; font-size: 11px;">
                                    <span class="fw-bold {{ $textClass }}">{{ $val }}</span>
                                    <span class="badge bg-white text-dark border shadow-sm fs-6 px-2 py-1">{{ $qty }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="table-responsive border rounded-3">
                    <table class="table premium-table">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 5%;">#</th>
                                @if($groupBy == 'date' || $groupBy == 'month')
                                    <th class="text-center" style="width: 10%;">{{ $groupBy == 'date' ? 'Date' : 'Month' }}</th>
                                @endif
                                <th style="width: 30%;">Product Details</th>
                                <th class="text-center">Ordered</th>
                                <th class="text-center">Delivered</th>
                                <th class="text-center">Returned</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-end">Net Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productPerformance as $key => $item)
                                @php
                                    $ratio = ($item->total_ordered_qty > 0) ? round(($item->returned_qty / $item->total_ordered_qty) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td class="text-center text-muted fw-bold">{{ $productPerformance->firstItem() + $key }}</td>
                                    
                                    @if($groupBy == 'date')
                                        <td class="text-center fw-bold text-primary">
                                            {{ \Carbon\Carbon::parse($item->report_date)->format('d M, Y') }}
                                        </td>
                                    @elseif($groupBy == 'month')
                                        <td class="text-center fw-bold text-primary">
                                            {{ \Carbon\Carbon::parse($item->report_month.'-01')->format('F, Y') }}
                                        </td>
                                    @endif

                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($item->image)
                                                <img src="{{ asset('products/'.$item->image) }}" alt="img" class="img-thumbnail-custom me-3">
                                            @endif
                                            <div>
                                                <div class="product-name-text" title="{{ $item->name }}">
                                                    {{ $item->name }}
                                                </div>
                                                <small class="text-muted" style="font-size: 11px;">ID: #{{ $item->id }}</small>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="text-center fw-bold">{{ $item->total_ordered_qty }}</td>
                                    
                                    <td class="text-center text-success fw-bold">{{ $item->delivered_qty }}</td>
                                    
                                    <td class="text-center">
                                        <span class="fw-bold text-danger">{{ $item->returned_qty }}</span><br>
                                        @if($ratio > 30) 
                                            <span class="stat-badge bg-danger text-white">{{ $ratio }}%</span>
                                        @elseif($ratio > 0) 
                                            <span class="stat-badge bg-warning text-dark">{{ $ratio }}%</span>
                                        @else 
                                            <span class="stat-badge bg-success text-white">0%</span> 
                                        @endif
                                    </td>

                                    <td class="text-end text-secondary fw-bold">
                                        ৳{{ number_format($item->total_revenue, 0) }}
                                    </td>
                                    
                                    <td class="text-end text-primary fw-bold">
                                        ৳{{ number_format($item->total_profit, 0) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No performance data found for the selected criteria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
                <div class="text-muted" style="font-size: 12px;">
                    Showing {{ $productPerformance->firstItem() ?? 0 }} - {{ $productPerformance->lastItem() ?? 0 }} of {{ $productPerformance->total() }}
                </div>
                <div>
                    {{ $productPerformance->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection