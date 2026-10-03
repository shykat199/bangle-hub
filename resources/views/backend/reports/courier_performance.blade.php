@extends('backend.app')

@section('content')
<style>
    /* ✅ Premium Responsive Table Styling */
    .premium-table {
        width: 100%;
        min-width: 700px; /* মোবাইলে স্ক্রল আনার জন্য নির্দিষ্ট ওয়াইড */
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
    .premium-table tbody tr:hover { background-color: #f8fafc; }
    .stat-badge {
        padding: 4px 6px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
        margin-top: 3px;
    }
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="card-title fw-bold text-dark mb-0">
                    <i class="mdi mdi-truck-fast text-primary me-2"></i>Courier Performance
                </h5>
            </div>
            
            <div class="card-body">
                <form action="{{ route('admin.report.courier_performance') }}" method="GET" class="mb-3">
                    <div class="row g-2 align-items-end bg-light p-2 rounded-3 border">
                        <div class="col-md-4 col-sm-6">
                            <label for="start_date" class="form-label text-muted small fw-bold mb-1">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <label for="end_date" class="form-label text-muted small fw-bold mb-1">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ request('end_date', $endDate->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-2 col-sm-6 mt-2 mt-md-0">
                            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">Filter</button>
                        </div>
                        <div class="col-md-2 col-sm-6 mt-2 mt-md-0">
                            <a href="{{ route('admin.report.courier_performance') }}" class="btn btn-secondary btn-sm w-100 fw-bold">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive border rounded-3">
                    <table class="table premium-table">
                        <colgroup>
                            <col style="width: 5%;">
                            <col style="width: 25%;">
                            <col style="width: 15%;">
                            <col style="width: 15%;">
                            <col style="width: 20%;">
                            <col style="width: 20%;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th>Courier Name</th>
                                <th class="text-center">Assigned</th>
                                <th class="text-center">On Transit</th>
                                <th class="text-center">Delivered</th>
                                <th class="text-center">Returned</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($courierPerformance as $key => $courier)
                                @php
                                    $courierName = $courier->courier_name ?? 'Unknown / Self Pick';
                                    $successRatio = ($courier->total_assigned > 0) ? round(($courier->total_delivered / $courier->total_assigned) * 100, 1) : 0;
                                    $returnRatio = ($courier->total_assigned > 0) ? round(($courier->total_returned / $courier->total_assigned) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td class="text-center text-muted fw-bold">{{ $key + 1 }}</td>
                                    <td class="fw-bold text-dark">{{ $courierName }}</td>
                                    <td class="text-center fw-bold">{{ $courier->total_assigned }}</td>
                                    <td class="text-center text-info fw-bold">{{ $courier->total_processing }}</td>
                                    
                                    <td class="text-center">
                                        <span class="fw-bold text-success">{{ $courier->total_delivered }}</span><br>
                                        <span class="stat-badge bg-success text-white">{{ $successRatio }}%</span>
                                    </td>
                                    
                                    <td class="text-center">
                                        <span class="fw-bold text-danger">{{ $courier->total_returned }}</span><br>
                                        @if($returnRatio > 15) 
                                            <span class="stat-badge bg-danger text-white">{{ $returnRatio }}%</span>
                                        @else 
                                            <span class="stat-badge bg-warning text-dark">{{ $returnRatio }}%</span> 
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No courier data found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-2 text-muted" style="font-size: 11px;">
                    * <strong>On Transit</strong> includes Shipped, Courier, Processing.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection