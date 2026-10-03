@extends('backend.app') 

@section('title', 'Monthly Profit Report')

@section('content')

<style>
    /* Global Soft UI Colors for Backgrounds (Text remains dark) */
    .bg-soft-primary { background-color: rgba(59, 130, 246, 0.1) !important; color: #111827 !important; }
    .bg-soft-success { background-color: rgba(16, 185, 129, 0.1) !important; color: #111827 !important; }
    .bg-soft-warning { background-color: rgba(245, 158, 11, 0.1) !important; color: #111827 !important; }
    .bg-soft-danger  { background-color: rgba(239, 68, 68, 0.1) !important;  color: #111827 !important; }
    .bg-soft-info    { background-color: rgba(6, 182, 212, 0.1) !important;  color: #111827 !important; }
    .bg-soft-purple  { background-color: rgba(139, 92, 246, 0.1) !important; color: #111827 !important; }
    
    .premium-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        transition: all 0.3s ease;
        background: #fff;
    }
    .premium-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    }
    .filter-box {
        background: #fff; border-radius: 16px;
        padding: 20px; box-shadow: 0 2px 12px rgba(0,0,0,0.02);
    }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid mt-4">
            
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 fw-bold text-dark">
                        <i class="mdi mdi-calendar-month-outline text-primary"></i> Monthly Profit Report
                    </h4>
                </div>
            </div>

            {{-- Filter Form --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="filter-box premium-card">
                        <form action="{{ route('admin.report.monthly_profit') }}" method="GET">
                            <div class="row align-items-end g-3">
                                <div class="col-md-4">
                                    <label class="form-label text-muted fw-bold small text-uppercase">Select Year</label>
                                    <select name="year" class="form-select bg-light border-0">
                                        @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                                            <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted fw-bold small text-uppercase">Select Month</label>
                                    <select name="month" class="form-select bg-light border-0">
                                        @for($m = 1; $m <= 12; $m++)
                                            @php $monthName = date('F', mktime(0, 0, 0, $m, 10)); @endphp
                                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $monthName }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm rounded">
                                        <i class="mdi mdi-filter me-1"></i> Filter Data
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Report Summary Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card premium-card bg-soft-info h-100 p-2">
                        <div class="card-body">
                            <h6 class="text-dark fw-bold mb-2">Total Sales <span class="small fw-normal">(Inc. Delivery)</span></h6>
                            <h4 class="mb-0 text-dark fw-bold">৳ {{ number_format($monthlySales, 2) }}</h4>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card premium-card bg-soft-primary h-100 p-2">
                        <div class="card-body">
                            <h6 class="text-dark fw-bold mb-2">Net Sales <span class="small fw-normal">(Exc. Delivery)</span></h6>
                            <h4 class="mb-0 text-dark fw-bold">৳ {{ number_format($monthlyNetSales, 2) }}</h4>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card premium-card bg-soft-warning h-100 p-2">
                        <div class="card-body">
                            <h6 class="text-dark fw-bold mb-2">Total Product Cost</h6>
                            <h4 class="mb-0 text-dark fw-bold">৳ {{ number_format($monthlyProductCost, 2) }}</h4>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card premium-card bg-soft-purple h-100 p-2">
                        <div class="card-body">
                            <h6 class="text-dark fw-bold mb-2">Gross Profit</h6>
                            <h4 class="mb-0 text-dark fw-bold">৳ {{ number_format($monthlyGrossProfit, 2) }}</h4>
                            <small class="text-dark fw-medium">(Net Sales - Cost)</small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="card premium-card bg-soft-danger h-100 p-2">
                        <div class="card-body">
                            <h6 class="text-dark fw-bold mb-2">Total Ad Cost</h6>
                            <h4 class="mb-0 text-dark fw-bold">৳ {{ number_format($monthlyAdCost, 2) }}</h4>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="card premium-card bg-light border h-100 p-2">
                        <div class="card-body">
                            <h6 class="text-dark fw-bold mb-2">Total Other Expenses</h6>
                            <h4 class="mb-0 text-dark fw-bold">৳ {{ number_format($monthlyOtherExpense, 2) }}</h4>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <div class="card premium-card bg-soft-success border border-success border-opacity-25 h-100 p-2">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                            <h5 class="mb-2 text-dark fw-bold"><i class="mdi mdi-cash-multiple text-success"></i> Final Net Profit</h5>
                            <h2 class="mb-0 fw-bold text-dark">৳ {{ number_format($monthlyNetProfit, 2) }}</h2>
                            <small class="mt-1 text-dark fw-medium">(Gross - All Expenses)</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Daily Sales Chart --}}
            <div class="card premium-card mt-2">
                <div class="card-header bg-white border-bottom p-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="mdi mdi-chart-areaspline text-primary me-2"></i> Daily Sales Overview ({{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }})</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlySalesChart" height="100"></canvas>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('monthlySalesChart');
    if(ctx) {
        const context = ctx.getContext('2d');
        
        // PHP ডাটাগুলো জাভাস্ক্রিপ্ট এরে তে কনভার্ট করা
        const labels = @json($chartLabels ?? []);
        const dataPoints = @json($chartData ?? []);

        new Chart(context, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Daily Sales Amount (৳)',
                    data: dataPoints,
                    backgroundColor: 'rgba(59, 130, 246, 0.15)',
                    borderColor: '#3b82f6',
                    borderWidth: 2,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#3b82f6',
                    pointHoverBackgroundColor: '#3b82f6',
                    pointHoverBorderColor: '#fff',
                    fill: true,
                    tension: 0.3 
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '৳ ' + value;
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Sales: ৳ ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endsection