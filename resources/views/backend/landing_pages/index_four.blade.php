@extends('backend.app')

@section('content')

@push('css')
{{-- SweetAlert2 for Delete Confirmation --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    /* Page Title & Breadcrumb */
    .page-title-box .breadcrumb { background: transparent; padding: 0; margin-bottom: 0; }
    .page-title { font-weight: 700; color: #343a40; font-size: 1.25rem; margin-top: 10px; }
    
    /* Card & Header Formatting */
    .card-premium { border: none; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); border-radius: 12px; background: #fff; overflow: hidden; margin-bottom: 1.5rem; }
    .card-header-premium { background-color: #fff; border-bottom: 1px solid #f1f3fa; padding: 1rem 1.5rem; }
    
    /* ✅ RESPONSIVE TABLE STYLES */
    .table-premium { width: 100%; table-layout: auto; } /* Auto layout to fit content */
    
    .table-premium thead th { 
        background-color: #f8f9fa; 
        color: #6c757d; 
        font-weight: 600; 
        text-transform: uppercase; 
        font-size: 0.75rem; 
        letter-spacing: 0.02em; 
        border-bottom: 0; 
        padding: 0.75rem 1rem; 
        white-space: nowrap; /* Keep headers single line */
    }

    .table-premium tbody td { 
        padding: 0.75rem 1rem; 
        vertical-align: middle; 
        border-bottom: 1px solid #f1f3fa; 
        color: #495057; 
        font-weight: 500; 
        font-size: 0.9rem;
        /* ✅ Text Wrap Enabled */
        white-space: normal !important; 
        word-wrap: break-word; 
    }
    
    /* Action Buttons Configuration */
    .btn-action { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; transition: all 0.2s ease; border: none; margin: 0 2px; text-decoration: none !important; }
    .btn-action-edit { background-color: rgba(255, 193, 7, 0.1); color: #ffc107; } 
    .btn-action-edit:hover { background-color: #ffc107; color: #000; }
    .btn-action-delete { background-color: rgba(250, 92, 124, 0.1); color: #fa5c7c; cursor: pointer; }
    .btn-action-delete:hover { background-color: #fa5c7c; color: #fff; }
    
    /* View Link Button */
    .btn-view-link { display: inline-flex; align-items: center; gap: 5px; font-size: 0.8rem; font-weight: 600; color: #343a40; background: #f8f9fa; border: 1px solid #e9ecef; padding: 5px 12px; border-radius: 6px; transition: all 0.2s; text-decoration: none; white-space: nowrap; }
    .btn-view-link:hover { background: #343a40; color: #ffffff !important; border-color: #343a40; }

    /* Custom Black Button */
    .btn-dark-custom { background-color: #212529 !important; color: #ffffff !important; border: none; font-weight: 600; padding: 8px 20px; transition: 0.3s; display: inline-flex; align-items: center; justify-content: center; }
    .btn-dark-custom:hover { background-color: #000000 !important; color: #ffffff !important; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
    .btn-dark-custom i { color: #ffffff !important; }

    /* ✅ MOBILE RESPONSIVE MEDIA QUERIES */
    @media (max-width: 768px) {
        /* Title Adjustment */
        .page-title-box { display: block !important; margin-bottom: 15px; }
        .page-title-right { margin-top: 5px; display: none; /* Hide breadcrumb on mobile to save space */ }
        .page-title { font-size: 1.1rem; margin-bottom: 5px; }

        /* Card Header Stack */
        .card-header-premium { flex-direction: column !important; align-items: flex-start !important; padding: 1rem; gap: 10px; }
        .header-actions { width: 100%; }
        .header-actions a { width: 100%; display: flex; justify-content: center; }
        
        /* Table Adjustments */
        .table-premium thead th { font-size: 0.7rem; padding: 0.5rem; }
        .table-premium tbody td { padding: 0.5rem; font-size: 0.85rem; }
        
        /* Compact Buttons for Mobile */
        .btn-view-link { padding: 4px 8px; font-size: 0.75rem; }
        .btn-view-link span { display: none; } /* Hide text, show icon only on super small screens if needed */
        .btn-view-link::after { content: "View"; margin-left: 3px; } /* Add short text */
        
        .btn-action { width: 28px; height: 28px; font-size: 0.8rem; }
    }
</style>
@endpush

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between py-3">
            <h4 class="page-title mb-0">Landing Page 4</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);" class="text-muted">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);" class="text-muted">CRM</a></li>
                    <li class="breadcrumb-item active">Type 4 Manage</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card card-premium">
            <div class="card-body p-0">
                <div class="card-header-premium d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <h5 class="text-dark mb-0 fw-bold">Manage Dark Pages</h5>
                        <span class="badge bg-dark text-white ms-2 rounded-pill">{{ method_exists($items, 'total') ? $items->total() : count($items) }} Total</span>
                    </div>
                    
                    {{-- Header Action Button --}}
                    <div class="header-actions">
                        @can('product.create')
                            <a href="{{ route('admin.landing_pages_four.create') }}" class="btn-dark-custom rounded-pill shadow-sm">
                                <i class="mdi mdi-moon-waning-crescent me-1"></i> Create New Page
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-centered table-premium mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px;">SL</th>
                                {{-- Min-width ensures title column doesn't get too squashed on mobile --}}
                                <th style="min-width: 150px;">Page Title</th>
                                <th style="width: 100px;">Public Link</th>
                                <th style="width: 100px;" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $key => $item)
                                <tr>
                                    {{-- Correct Pagination SL Number --}}
                                    <td><span class="fw-semibold text-muted">{{ (method_exists($items, 'firstItem') ? $items->firstItem() : 1) + $key }}</span></td>
                                    
                                    <td>
                                        <h6 class="mb-0 text-dark" style="line-height: 1.4;">{{ $item->title1 }}</h6>
                                        <small class="text-muted d-block mt-1">
                                            <i class="mdi mdi-calendar-clock me-1"></i>{{ $item->created_at ? $item->created_at->format('d M, Y') : 'N/A' }}
                                        </small>
                                    </td>
                                    
                                    <td>
                                        <a href="{{ route('front.landing_pages_four.view_page', ['id' => $item->slug ?: $item->id]) }}" target="_blank" class="btn-view-link shadow-sm">
                                            <i class="mdi mdi-open-in-new"></i> 
                                        </a>
                                    </td>
                                    
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            @if(auth()->user()->can('permission.view'))
                                                <a href="{{ route('admin.landing_pages_four.edit', [$item->id]) }}" class="btn-action btn-action-edit" data-bs-toggle="tooltip" title="Edit Page">
                                                    <i class="mdi mdi-pencil-outline"></i>
                                                </a>
                                            @endif
                                            
                                            @if(auth()->user()->can('permission.view'))
                                                <button type="button" data-url="{{ route('admin.landing_pages_four.destroy', [$item->id]) }}" class="btn-action btn-action-delete delete-page" data-bs-toggle="tooltip" title="Delete Page">
                                                    <i class="mdi mdi-trash-can-outline"></i>
                                                </button>
                                                <form action="{{ route('admin.landing_pages_four.destroy', [$item->id]) }}" method="POST" class="d-none delete-form">
                                                    @csrf @method('DELETE')
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="mdi mdi-moon-waning-crescent text-muted" style="font-size: 3rem; opacity: 0.4;"></i>
                                            <h5 class="text-muted mt-3">No Dark Pages found</h5>
                                            <a href="{{ route('admin.landing_pages_four.create') }}" class="btn-dark-custom rounded-pill mt-3 px-4">Create your first page</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- Pagination Links --}}
                @if(method_exists($items, 'links') && $items->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    <div class="d-flex justify-content-end">
                        {{ $items->links('pagination::bootstrap-5') }}
                    </div>
                </div>
                @endif
                
            </div> 
        </div> 
    </div> 
</div> 
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function($){
    "use strict";
    
    // Initialize Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) { return new bootstrap.Tooltip(tooltipTriggerEl) });

    // SweetAlert Delete Logic
    $(document).off('click.lpfour_delete', '.delete-page').on('click.lpfour_delete', '.delete-page', function(e){
        e.preventDefault(); e.stopImmediatePropagation();
        let btn = $(this); let url = btn.data('url'); let row = btn.closest('tr'); let form = btn.next('.delete-form'); 
        if(btn.data('deleting') === 1) return;

        Swal.fire({
            title: 'Are you sure?', 
            text: "This page and all its uploaded images will be permanently deleted!", 
            icon: 'warning',
            showCancelButton: true, 
            confirmButtonColor: '#fa5c7c', 
            cancelButtonColor: '#6c757d', 
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.data('deleting', 1);
                $.ajax({
                    url: url, type: 'POST', dataType: 'json',
                    data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                    success: function(res){
                        if(res.status === true){ 
                            Swal.fire('Deleted!', res.msg, 'success'); 
                            row.fadeOut(400, function(){ $(this).remove(); }); 
                        } else { 
                            Swal.fire('Error', (res.msg) ? res.msg : 'Delete failed', 'error'); 
                            btn.data('deleting', 0); 
                        }
                    },
                    error: function(xhr){ 
                        console.error(xhr); 
                        btn.data('deleting', 0); 
                        if(form.length){ form.submit(); } 
                    }
                });
            }
        });
    });
})(jQuery);
</script>
@endpush