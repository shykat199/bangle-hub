@extends('backend.app')

@section('title', 'Daily Profit Report')

@section('content')

{{-- Premium Custom CSS --}}
<style>
    /* Global Soft UI Colors for Backgrounds (Text remains dark) */
    .bg-soft-primary { background-color: rgba(59, 130, 246, 0.1) !important; color: #111827 !important; }
    .bg-soft-success { background-color: rgba(16, 185, 129, 0.1) !important; color: #111827 !important; }
    .bg-soft-warning { background-color: rgba(245, 158, 11, 0.1) !important; color: #111827 !important; }
    .bg-soft-danger  { background-color: rgba(239, 68, 68, 0.1) !important;  color: #111827 !important; }
    .bg-soft-info    { background-color: rgba(6, 182, 212, 0.1) !important;  color: #111827 !important; }

    .premium-card {
        border: none;
        border-radius: 15px;
        transition: all 0.3s ease-in-out;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        background: #fff;
    }
    .premium-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    }
    
    .table-premium {
        background-color: #ffffff !important;
        border-radius: 10px;
        overflow: hidden;
    }
    .table-premium thead {
        background-color: #f8fafc !important;
        color: #111827 !important; 
    }
    .table-premium tbody tr { background-color: #ffffff !important; }
    .table-premium tbody td { color: #111827 !important; font-weight: 500; }
    
    .custom-header {
        background-color: #f1f5f9 !important; 
        color: #111827 !important;
        border-radius: 10px 10px 0 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    /* Modal Text Fix */
    .modal-content { background-color: #ffffff !important; border-radius: 15px; border: none; }
    .modal-header.soft-blue { background-color: rgba(59, 130, 246, 0.1); color: #111827; border-bottom: 1px solid #bfdbfe; border-radius: 15px 15px 0 0; }
    .modal-header.soft-red { background-color: rgba(239, 68, 68, 0.1); color: #111827; border-bottom: 1px solid #fecaca; border-radius: 15px 15px 0 0; }
    .modal-body label { color: #111827 !important; font-weight: 600; }
    .modal-body input, .modal-body select { 
        background-color: #f8fafc !important; 
        color: #111827 !important; 
        border: 1px solid #cbd5e1;
    }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid mt-4">
            
            {{-- Success Message --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" style="border-radius: 10px;" role="alert">
                    <strong class="text-dark"><i class="fas fa-check-circle me-1 text-success"></i> Success!</strong> <span class="text-dark">{{ session('success') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- 1. Date Filter Section --}}
            <div class="card mb-4 premium-card">
                <div class="card-body bg-white" style="border-radius: 15px;">
                    <form action="{{ route('admin.report.daily_profit') }}" method="GET" class="d-flex align-items-center flex-wrap gap-3">
                        <label for="date" class="fw-bold text-dark mb-0" style="font-size: 1.1rem;">Select Date:</label>
                        <input type="date" name="date" id="date" class="form-control" style="width: 200px; border-radius: 8px;" value="{{ $date }}">
                        <button type="submit" class="btn btn-primary shadow-sm fw-bold" style="border-radius: 8px; padding: 8px 20px;">
                            <i class="fas fa-filter me-1"></i> Filter Report
                        </button>
                    </form>
                </div>
            </div>

            {{-- 2. Summary Cards Section --}}
            <div class="row mb-4 text-center g-3">
                <div class="col-md-3 col-6">
                    <div class="card premium-card bg-soft-primary h-100 p-3">
                        <div class="card-body p-0 d-flex flex-column justify-content-center">
                            <h6 class="text-uppercase mb-2 fw-bold text-dark" style="letter-spacing: 1px;">Total Sales</h6>
                            <h3 class="fw-bold mb-1 text-dark">৳ {{ number_format($totalSales, 2) }}</h3>
                            <small class="text-dark"><i class="fas fa-shopping-cart"></i> Total Orders: {{ $orders->count() }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card premium-card bg-soft-info h-100 p-3">
                        <div class="card-body p-0 d-flex flex-column justify-content-center">
                            <h6 class="text-uppercase mb-2 fw-bold text-dark" style="letter-spacing: 1px;">Gross Profit</h6>
                            <h3 class="fw-bold mb-1 text-dark">৳ {{ number_format($grossProfit, 2) }}</h3>
                            <small class="text-dark"><i class="fas fa-calculator"></i> Net Sales - Cost</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card premium-card bg-soft-warning h-100 p-3">
                        <div class="card-body p-0 d-flex flex-column justify-content-center">
                            <h6 class="text-uppercase mb-2 fw-bold text-dark" style="letter-spacing: 1px;">Total Costs</h6>
                            <h3 class="fw-bold mb-1 text-dark">৳ {{ number_format($totalAdCost + $totalOtherExpense, 2) }}</h3>
                            <small class="text-dark"><i class="fas fa-bullhorn"></i> Ad + Expenses</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card premium-card bg-soft-success h-100 p-3 border border-success border-opacity-25">
                        <div class="card-body p-0 d-flex flex-column justify-content-center">
                            <h6 class="text-uppercase mb-2 fw-bold text-dark" style="letter-spacing: 1px;">Final Net Profit</h6>
                            <h3 class="fw-bold mb-1 text-dark">৳ {{ number_format($finalNetProfit, 2) }}</h3>
                            <small class="text-dark"><i class="fas fa-wallet"></i> Gross - Total Costs</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Ad Costs & Other Expenses Input Section --}}
            <div class="row g-4">
                
                {{-- Ad Cost Form & Table --}}
                <div class="col-md-6">
                    <div class="card premium-card h-100">
                        <div class="card-header custom-header d-flex justify-content-between align-items-center p-3">
                            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-ad me-2 text-primary"></i>Ad Costs <span class="badge bg-white text-dark border ms-2">{{ \Carbon\Carbon::parse($date)->format('d M') }}</span></h5>
                            <button class="btn btn-sm btn-primary fw-bold shadow-sm" style="border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#adCostModal">+ Add Cost</button>
                        </div>
                        <div class="card-body bg-white p-0" style="border-radius: 0 0 15px 15px; overflow: hidden;">
                            <div class="table-responsive">
                                <table class="table table-hover table-premium mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">Platform</th>
                                            <th>USD ($)</th>
                                            <th>Rate (৳)</th>
                                            <th class="pe-4 text-end">Total (৳)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($adCosts as $ad)
                                        <tr>
                                            <td class="ps-4 fw-bold">
                                                @if($ad->platform == 'Facebook') <i class="fab fa-facebook text-primary me-1"></i> 
                                                @elseif($ad->platform == 'Google') <i class="fab fa-google text-danger me-1"></i>
                                                @elseif($ad->platform == 'TikTok') <i class="fab fa-tiktok text-dark me-1"></i>
                                                @else <i class="fas fa-bullhorn text-secondary me-1"></i> @endif
                                                {{ $ad->platform }}
                                            </td>
                                            <td>${{ $ad->usd_amount }}</td>
                                            <td>৳{{ $ad->dollar_rate }}</td>
                                            <td class="fw-bold text-danger pe-4 text-end">৳{{ $ad->total_cost }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted"><i class="fas fa-inbox fs-4 mb-2 d-block"></i> No Ad Costs recorded.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="bg-soft-primary">
                                        <tr>
                                            <th colspan="3" class="text-end text-dark py-3 fw-bold">Total Ad Cost:</th>
                                            <th class="text-danger fs-6 py-3 pe-4 text-end fw-bold">৳{{ number_format($totalAdCost, 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Other Expenses Form & Table --}}
                <div class="col-md-6">
                    <div class="card premium-card h-100">
                        <div class="card-header custom-header d-flex justify-content-between align-items-center p-3">
                            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-receipt me-2 text-warning"></i>Other Expenses <span class="badge bg-white text-dark border ms-2">{{ \Carbon\Carbon::parse($date)->format('d M') }}</span></h5>
                            <button class="btn btn-sm btn-primary fw-bold shadow-sm" style="border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#expenseModal">+ Add Expense</button>
                        </div>
                        <div class="card-body bg-white p-0" style="border-radius: 0 0 15px 15px; overflow: hidden;">
                            <div class="table-responsive">
                                <table class="table table-hover table-premium mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">Details</th>
                                            <th class="pe-4 text-end">Amount (৳)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($otherExpenses as $exp)
                                        <tr>
                                            <td class="ps-4 fw-bold"><i class="fas fa-arrow-right text-muted me-2" style="font-size: 0.8rem;"></i>{{ $exp->details }}</td>
                                            <td class="fw-bold text-danger pe-4 text-end">৳{{ $exp->amount }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="2" class="text-center py-4 text-muted"><i class="fas fa-inbox fs-4 mb-2 d-block"></i> No other expenses recorded.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="bg-soft-warning">
                                        <tr>
                                            <th class="text-end text-dark py-3 fw-bold">Total Other Expenses:</th>
                                            <th class="text-danger fs-6 py-3 pe-4 text-end fw-bold">৳{{ number_format($totalOtherExpense, 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ================= MODALS ================= --}}

{{-- Add Ad Cost Modal --}}
<div class="modal fade" id="adCostModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.report.store_ad_cost') }}" method="POST" style="width: 100%;">
            @csrf
            <div class="modal-content shadow">
                <div class="modal-header soft-blue">
                    <h5 class="modal-title fw-bold text-dark"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Ad Cost</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="date" value="{{ $date }}">
                    
                    <div class="mb-4">
                        <label class="mb-2">Platform</label>
                        <select name="platform" class="form-select shadow-sm" required style="border-radius: 8px;">
                            <option value="Facebook">Facebook</option>
                            <option value="Google">Google</option>
                            <option value="TikTok">TikTok</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="mb-2">Spent Amount (USD $)</label>
                        <div class="input-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                            <span class="input-group-text bg-white text-dark fw-bold">$</span>
                            <input type="number" step="0.01" name="usd_amount" class="form-control" required placeholder="15.50">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="mb-2">Current Dollar Rate (৳)</label>
                        <div class="input-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                            <span class="input-group-text bg-white text-dark fw-bold">৳</span>
                            <input type="number" step="0.01" name="dollar_rate" class="form-control" required placeholder="120">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-radius: 0 0 15px 15px;">
                    <button type="button" class="btn btn-light border" style="border-radius: 8px;" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold" style="border-radius: 8px;">Save Ad Cost</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Add Other Expense Modal --}}
<div class="modal fade" id="expenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.report.store_other_expense') }}" method="POST" style="width: 100%;">
            @csrf
            <div class="modal-content shadow">
                <div class="modal-header soft-red">
                    <h5 class="modal-title fw-bold text-dark"><i class="fas fa-plus-circle me-2 text-danger"></i>Add Other Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="date" value="{{ $date }}">
                    
                    <div class="mb-4">
                        <label class="mb-2">Expense Details</label>
                        <input type="text" name="details" class="form-control shadow-sm" style="border-radius: 8px;" required placeholder="e.g. Packaging, Staff Food">
                    </div>
                    <div class="mb-3">
                        <label class="mb-2">Amount (৳)</label>
                        <div class="input-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                            <span class="input-group-text bg-white text-dark fw-bold">৳</span>
                            <input type="number" step="0.01" name="amount" class="form-control" required placeholder="500">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-radius: 0 0 15px 15px;">
                    <button type="button" class="btn btn-light border" style="border-radius: 8px;" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger px-4 fw-bold" style="border-radius: 8px;">Save Expense</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection