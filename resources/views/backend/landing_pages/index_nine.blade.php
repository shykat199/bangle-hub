@extends('backend.app')
@section('title', 'Landing Page 8')')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
    /* ==== BASE PREMIUM STYLE ==== */
    .premium-card {
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(149, 157, 165, 0.1);
        border: 1px solid rgba(0,0,0,0.02);
        background: #ffffff;
        overflow: hidden;
    }
    .premium-header {
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        padding: 20px 24px;
    }
    .premium-title { font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 0; }
    
    .btn-create {
        background: #4318FF; color: white; border: none; border-radius: 10px;
        padding: 10px 20px; font-weight: 600; font-size: 14px;
        box-shadow: 0 4px 12px rgba(67, 24, 255, 0.2); transition: all 0.3s ease;
        display: inline-flex; align-items: center; justify-content: center;
        white-space: nowrap;
    }
    .btn-create:hover { background: #320bc4; color: white; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(67, 24, 255, 0.3); }
    
    /* ==== TABLE STYLES ==== */
    .custom-table { margin-bottom: 0; width: 100%; }
    .custom-table th {
        background: #f8fafc; text-transform: uppercase; font-size: 0.75rem; font-weight: 700;
        letter-spacing: 0.5px; color: #64748b; border-bottom: 1px solid #e2e8f0; padding: 16px 24px;
        white-space: nowrap;
    }
    .custom-table td { 
        padding: 16px 24px; 
        border-bottom: 1px solid #f1f5f9; 
        vertical-align: middle; 
        color: #334155; 
    }
    
    .img-premium {
        width: 65px; height: 65px; object-fit: cover; border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.08); transition: transform 0.3s ease; background: #eee;
        flex-shrink: 0;
    }
    .img-premium:hover { transform: scale(1.1) rotate(2deg); }
    
    .badge-soft-danger {
        background-color: #fef2f2; color: #ef4444; padding: 6px 12px; border-radius: 8px;
        font-weight: 600; font-size: 0.75rem; border: 1px solid #fecaca;
        display: inline-block; white-space: nowrap;
    }
    
    /* ==== ACTION BUTTONS ==== */
    .action-btn {
        width: 38px; height: 38px; display: inline-flex; align-items: center;
        justify-content: center; border-radius: 10px; transition: all 0.2s ease;
        border: none; margin: 0 3px; font-size: 15px; text-decoration: none !important;
        flex-shrink: 0;
    }
    .action-btn.edit { background: #eff6ff; color: #3b82f6; }
    .action-btn.edit:hover { background: #3b82f6; color: #fff; transform: translateY(-2px); }
    .action-btn.color { background: #f1f5f9; color: #475569; }
    .action-btn.color:hover { background: #475569; color: #fff; transform: translateY(-2px); }
    .action-btn.delete { background: #fef2f2; color: #ef4444; }
    .action-btn.delete:hover { background: #ef4444; color: #fff; transform: translateY(-2px); }
    
    .link-view {
        color: #4318FF; background: rgba(67, 24, 255, 0.08); padding: 6px 12px;
        border-radius: 8px; font-weight: 600; font-size: 13px; text-decoration: none;
        display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;
    }
    .link-view:hover { background: #4318FF; color: #fff; }

    /* ==== ENHANCED MOBILE RESPONSIVE ==== */
    @media (max-width: 991px) {
        .premium-header { padding: 14px 16px; }
        .premium-header h4 { font-size: 1.05rem; }
        .btn-create { padding: 8px 14px; font-size: 13px; }
    }

    @media (max-width: 767px) {
        .custom-table th { padding: 10px 12px; font-size: 0.7rem; }
        .custom-table td { padding: 10px 12px; }
        .img-premium { width: 50px; height: 50px; }
        .action-btn { width: 34px; height: 34px; font-size: 13px; margin: 0 2px; }
        .premium-header { flex-direction: column; align-items: flex-start !important; gap: 12px !important; padding: 16px; }
        .premium-header .btn-create { width: 100%; }
    }

    /* ==== MOBILE CARD LAYOUT (Below 576px) ==== */
    @media (max-width: 575px) {
        .custom-table thead { display: none; }
        .custom-table, .custom-table tbody, .custom-table tr, .custom-table td { display: block; width: 100% !important; }
        
        .custom-table tbody tr {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
            margin-bottom: 16px; padding: 16px; box-shadow: 0 4px 10px rgba(0,0,0,0.04);
        }
        
        .custom-table td {
            padding: 10px 0 !important; border: 0 !important;
            display: flex !important; justify-content: space-between; align-items: center;
            border-bottom: 1px dashed #f1f5f9 !important;
        }
        
        .custom-table td:before {
            content: attr(data-label); font-weight: 700;
            color: #64748b; font-size: 0.8rem; text-transform: uppercase;
            letter-spacing: 0.5px; text-align: left; white-space: nowrap;
            flex-basis: 35%; /* Fixed width for labels */
        }

        .td-content {
            flex-basis: 65%; /* Fixed width for content */
            text-align: right;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: center;
        }
        
        .custom-table td .img-premium { margin-left: auto; }
        
        .custom-table td:last-child { 
            border-bottom: 0 !important; 
            padding-top: 16px !important; 
            margin-top: 8px; 
            justify-content: center !important; 
        }
        
        .custom-table td:last-child:before { display: none; }
        .badge-soft-danger { margin-top: 6px; font-size: 0.7rem; padding: 4px 8px;}
    }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-12">
                <div class="premium-card">
                    <div class="premium-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary flex-shrink-0">
                                <i class="fas fa-fan fs-4"></i>
                            </div>
                            <h4 class="premium-title">Landing Page 8</h4>
                        </div>
                        <a href="{{ route('admin.landing_pages_nine.create') }}" class="btn-create">
                            <i class="fas fa-plus-circle me-1"></i> Add New Page
                        </a>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table custom-table align-middle">
                                <thead>
                                    <tr>
                                        <th width="5%">ID</th>
                                        <th width="10%">Preview</th>
                                        <th width="35%">Headline</th>
                                        <th width="20%">Product</th>
                                        <th width="15%">Live URL</th>
                                        <th width="15%" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $key => $item)
                                    <tr>
                                        <td data-label="ID" class="fw-bold text-muted">
                                            #{{ $items->firstItem() + $key }}
                                        </td>
                                        <td data-label="Preview">
                                            <div class="td-content">
                                                @php
                                                    if (!empty($item->right_product_image)) {
                                                        $cleanName = str_replace('landing_pages/', '', $item->right_product_image);
                                                        $finalPath = asset('landing_pages/' . $cleanName);
                                                    } elseif (!empty($item->image)) {
                                                        $cleanName = str_replace('landing_pages/', '', $item->image);
                                                        $finalPath = asset('landing_pages/' . $cleanName);
                                                    } elseif ($item->product && !empty($item->product->image)) {
                                                        $finalPath = getImage('products', $item->product->image);
                                                    } else {
                                                        $finalPath = asset('frontend/images/no-image.png');
                                                    }
                                                @endphp
                                                <img src="{{ $finalPath }}"
                                                     onerror="this.onerror=null; this.src='{{ asset('frontend/images/no-image.png') }}';"
                                                     alt="Preview" class="img-premium">
                                            </div>
                                        </td>
                                        <td data-label="Headline">
                                            <div class="td-content">
                                                <div class="fw-bold fs-6 mb-1 text-dark" title="{{ $item->title1 }}">
                                                    {{ \Illuminate\Support\Str::words($item->title1, 3, '...') }}
                                                </div>
                                                <span class="badge-soft-danger"><i class="fas fa-layer-group me-1"></i> Cool Conversion</span>
                                            </div>
                                        </td>
                                        <td data-label="Product">
                                            <div class="td-content">
                                                @if($item->product)
                                                    <span class="text-dark fw-medium" title="{{ $item->product->name }}">
                                                        <i class="fas fa-box text-muted me-1"></i> 
                                                        {{ \Illuminate\Support\Str::words($item->product->name, 3, '...') }}
                                                    </span>
                                                @else
                                                    <span class="text-muted fst-italic">Not Linked</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td data-label="Live URL">
                                            <div class="td-content">
                                                <a href="{{ route('front.landing_pages_nine.view_page', $item->slug ?: $item->id) }}" target="_blank" class="link-view">
                                                    Visit Page <i class="fas fa-external-link-alt" style="font-size: 11px;"></i>
                                                </a>
                                            </div>
                                        </td>
                                        <td data-label="Actions" class="text-center">
                                            <div class="d-flex justify-content-center flex-nowrap align-items-center w-100">
                                                <a href="{{ route('admin.landing_pages_nine.edit', $item->id) }}" class="action-btn edit" data-bs-toggle="tooltip" title="Edit Content">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                                <a href="{{ route('admin.landing_pages.color', $item->id) }}" class="action-btn color" data-bs-toggle="tooltip" title="Settings">
                                                    <i class="fas fa-paint-roller"></i>
                                                </a>
                                                <form action="{{ route('admin.landing_pages_nine.destroy', $item->id) }}" method="POST" class="d-inline delete-form m-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="action-btn delete btn-delete-confirm" data-bs-toggle="tooltip" title="Delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <div class="d-flex flex-column align-items-center justify-content-center">
                                                <i class="fas fa-inbox fs-1 text-light mb-3"></i>
                                                <p class="mb-0">No data found.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($items->hasPages())
                        <div class="p-4 border-top">
                            {{ $items->links('pagination::bootstrap-5') }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        $('.btn-delete-confirm').click(function(e){
            e.preventDefault();
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Are you sure?',
                text: "This landing page will be deleted permanently!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush