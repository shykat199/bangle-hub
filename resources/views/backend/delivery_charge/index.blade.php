@extends('backend.app')
@section('content')

<style>
    :root{
        --bg:#f3f4f6;
        --card:#ffffff;
        --border:#e5e7eb;
        --primary:#0ea5e9;
        --primary-soft:rgba(14,165,233,.15);
        --text:#111827;
        --muted:#6b7280;
        --shadow:0 10px 28px rgba(15,23,42,.08);
    }

    body{
        background: var(--bg);
    }

    .page-title-box h4{
        font-weight:700;
    }

    .card-modern{
        border:0;
        border-radius:20px;
        background:var(--card);
        box-shadow:var(--shadow);
        overflow:hidden;
    }
    .card-modern .card-header{
        background:linear-gradient(135deg,#eff6ff,#dbeafe);
        padding:12px 20px;
        border-bottom:1px solid rgba(15,23,42,.05);
    }
    .card-modern .card-header h4{
        margin:0;
        font-weight:700;
        color:#1e293b;
        font-size:1rem;
    }

    .form-label{
        font-size:.85rem;
        color:var(--muted);
        font-weight:600;
    }
    .form-control{
        border-radius:10px;
        border-color:var(--border);
    }
    .form-control:focus{
        border-color:var(--primary);
        box-shadow:0 0 0 .15rem var(--primary-soft);
    }

    .btn-primary{
        border:none;
        border-radius:10px;
        padding:.45rem 1.3rem;
        font-weight:600;
        background:linear-gradient(to right,#0ea5e9,#2563eb);
    }

    /* Modern table */
    .table-modern thead{
        background:#f8fafc;
    }
    .table-modern thead th{
        font-size:.8rem;
        color:var(--muted);
        text-transform:uppercase;
        letter-spacing:.05em;
        border-bottom:1px solid var(--border);
    }
    .table-modern tbody td{
        border-top:1px solid #f1f5f9;
        font-size:.9rem;
    }
    .status-badge{
        display:inline-block;
        padding:.25rem .7rem;
        border-radius:999px;
        font-size:.75rem;
        font-weight:600;
    }
    .status-active{
        background:rgba(16,185,129,.15);
        color:#047857;
    }
    .status-inactive{
        background:rgba(239,68,68,.15);
        color:#b91c1c;
    }

    /* Mobile view for table */
    @media(max-width:768px){
        .table-modern thead{ display:none; }
        .table-modern tbody tr{
            display:block;
            margin-bottom:12px;
            background:#fff;
            border-radius:14px;
            padding:10px;
            box-shadow:0 4px 10px rgba(0,0,0,.06);
        }
        .table-modern tbody td{
            display:grid;
            grid-template-columns:130px 1fr;
            border:none !important;
            padding:6px 4px !important;
        }
        .table-modern tbody td::before{
            content:attr(data-label);
            font-size:.75rem;
            color:var(--muted);
            text-transform:uppercase;
            font-weight:600;
        }
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Delivery Charge Manage</h4>
        </div>
    </div>
</div>

@if(session('success'))
<div class="row">
    <div class="col-12">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
</div>
@endif

<div class="row mb-4">
    <div class="col-12">
        <div class="card card-modern">
            <div class="card-header bg-primary text-white" style="background: linear-gradient(135deg, #0ea5e9, #2563eb);">
                <h4 class="text-white mb-0">Global Delivery Settings</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.delivery_charge.global_update') }}" method="POST">
                    @csrf
                    <div class="row align-items-center mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark">Select Delivery System</label>
                            <select name="charge_type" id="system_type_selector" class="form-control">
                                <option value="flat" {{ (isset($chargeType) && $chargeType == 'flat') ? 'selected' : '' }}>Flat Rate (Area Based)</option>
                                <option value="weight_based" {{ (isset($chargeType) && $chargeType == 'weight_based') ? 'selected' : '' }}>Weight Based (Courier API)</option>
                            </select>
                        </div>
                    </div>

                    <div id="weight_based_config" style="display: none; background: #f8fafc; padding: 15px; border-radius: 10px; border: 1px solid #e2e8f0;">
                        <h5 class="mb-3 text-dark fw-bold">Configure Courier Rates (Weight Based)</h5>
                        <div class="row">
                            @php
                                $couriers = [
                                    'steadfast' => 'Courier',
                                    // 'pathao'    => 'Pathao Courier',
                                    // 'redx'      => 'RedX Courier'
                                ];
                            @endphp

                            @foreach($couriers as $key => $name)
                            <div class="col-md-12 mb-4">
                                <div class="p-3 bg-white rounded border shadow-sm">
                                    <h6 class="text-primary fw-bold border-bottom pb-2">{{ $name }}</h6>
                                    <div class="row mt-3">
                                        <div class="col-md-6 border-end">
                                            <p class="badge bg-success mb-2" style="font-size: 13px;">Inside Dhaka</p>
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold text-dark">Base Charge (1st KG)</label>
                                                <input type="number" step="any" name="{{ $key }}_inside_base" value="{{ $courierRates[$key.'_inside']->base_charge ?? 0 }}" class="form-control" placeholder="e.g. 70">
                                            </div>
                                            <div>
                                                <label class="form-label small fw-bold text-dark">Extra Charge (Per KG)</label>
                                                <input type="number" step="any" name="{{ $key }}_inside_extra" value="{{ $courierRates[$key.'_inside']->extra_per_kg_charge ?? 0 }}" class="form-control" placeholder="e.g. 20">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="badge bg-danger mb-2" style="font-size: 13px;">Outside Dhaka</p>
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold text-dark">Base Charge (1st KG)</label>
                                                <input type="number" step="any" name="{{ $key }}_outside_base" value="{{ $courierRates[$key.'_outside']->base_charge ?? 0 }}" class="form-control" placeholder="e.g. 130">
                                            </div>
                                            <div>
                                                <label class="form-label small fw-bold text-dark">Extra Charge (Per KG)</label>
                                                <input type="number" step="any" name="{{ $key }}_outside_extra" value="{{ $courierRates[$key.'_outside']->extra_per_kg_charge ?? 0 }}" class="form-control" placeholder="e.g. 30">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-2 px-4">Save Global Settings</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row flat_rate_area">
    <div class="col-lg-5 col-md-12 mb-3">
        <div class="card card-modern">
            <div class="card-header">
                <h4>Add Flat Delivery Charge</h4>
            </div>
            <div class="card-body">

                @can('delivery_charge.create')
                <form method="POST" action="{{ route('admin.delivery_charge.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Delivery Charge Title</label>
                        <input type="text" name="title" class="form-control" placeholder="Title" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Charge Amount</label>
                        <input type="text" name="amount" class="form-control" value="0" required>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="status" class="form-check-input" value="1" checked>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Save</button>

                </form>
                @endcan
            </div>
        </div>
    </div>

    <div class="col-lg-7 col-md-12 mb-3">
        <div class="card card-modern">
            <div class="card-header">
                <h4>Flat Delivery Charge List</h4>
            </div>
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-modern table-centered mb-0">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Title</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th style="width:100px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $key => $item)
                            <tr>
                                <td data-label="SL">{{ $key+1 }}</td>

                                <td data-label="Title">{{ $item->title }}</td>

                                <td data-label="Amount">{{ $item->amount }}</td>

                                <td data-label="Status">
                                    @if($item->status == '1')
                                        <span class="status-badge status-active">Active</span>
                                    @else
                                        <span class="status-badge status-inactive">Inactive</span>
                                    @endif
                                </td>

                                <td data-label="Action">
                                    @can('delivery_charge.edit')
                                    <a href="{{ route('admin.delivery_charge.edit', $item->id) }}" class="action-icon me-1">
                                        <i class="mdi mdi-square-edit-outline"></i>
                                    </a>
                                    @endcan

                                    @can('delivery_charge.delete')
                                    <a href="{{ route('admin.delivery_charge.destroy', $item->id) }}" class="delete action-icon">
                                        <i class="mdi mdi-delete"></i>
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

</div>

@endsection

@push('js')
<script>
    $(document).ready(function() {
        // Function to toggle between Flat Rate and Weight Based UI
        function toggleSystem() {
            let type = $('#system_type_selector').val();
            
            if(type === 'weight_based') {
                $('#weight_based_config').slideDown();
                // Hide flat rate management
                $('.flat_rate_area').slideUp(); 
            } else {
                $('#weight_based_config').slideUp();
                // Show flat rate management
                $('.flat_rate_area').slideDown(); 
            }
        }

        // Initialize on page load
        toggleSystem();

        // Trigger on selection change
        $('#system_type_selector').on('change', function() {
            toggleSystem();
        });
    });
</script>
@endpush