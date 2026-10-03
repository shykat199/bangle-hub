@extends('backend.app')
@section('title', 'Landing Page 5')')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .page-title-box .breadcrumb { background: transparent; padding: 0; margin-bottom: 0; }
    .page-title { font-weight: 700; color: #343a40; font-size: 1.25rem; margin-top: 10px; }
    .card-custom { border: none; box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, 0.03); border-radius: 16px; background: #fff; overflow: hidden; border-top: 4px solid #f59e0b; }
    .card-header-custom { background-color: #fff; border-bottom: 1px solid #f1f3fa; padding: 1.5rem; }
    
    /* ✅ FIXED: Strictly Prevent Horizontal Scrolling & Fix Table Layout */
    .table-responsive { overflow-x: hidden !important; }
    .table-custom { table-layout: fixed; width: 100%; border-collapse: collapse; }
    .table-custom thead th { background-color: #fffbeb; color: #d97706; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.02em; padding: 0.8rem 0.5rem; white-space: nowrap; }
    .table-custom tbody td { padding: 0.8rem 0.5rem; vertical-align: middle; border-bottom: 1px solid #f1f3fa; color: #495057; font-weight: 500; font-size: 0.9rem; overflow: hidden; }
    
    /* ✅ Text Truncation Class (...) */
    .text-ellipsis { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; width: 100%; }
    
    /* Action Buttons */
    .btn-action { width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; transition: all 0.2s ease; border: none; margin: 0; text-decoration: none !important; font-size: 0.9rem; flex-shrink: 0; }
    .btn-action-edit { background-color: rgba(14, 165, 233, 0.1); color: #0ea5e9; }
    .btn-action-edit:hover { background-color: #0ea5e9; color: #fff; }
    .btn-action-color { background-color: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
    .btn-action-color:hover { background-color: #8b5cf6; color: #fff; }
    .btn-action-delete { background-color: rgba(250, 92, 124, 0.1); color: #fa5c7c; cursor: pointer; }
    .btn-action-delete:hover { background-color: #fa5c7c; color: #fff; }
    
    /* View Link - Converted to Icon for space saving */
    .btn-view-link { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; font-size: 1rem; color: #d97706; background: rgba(245, 158, 11, 0.1); border-radius: 6px; transition: all 0.2s; text-decoration: none; }
    .btn-view-link:hover { background: #f59e0b; color: #fff; }
    
    .pagination { justify-content: flex-end; margin-bottom: 0; }

    /* Mobile specific adjustments */
    @media (max-width: 768px) {
        .table-custom tbody td { font-size: 0.8rem; padding: 0.6rem 0.4rem; }
        .card-header-custom { padding: 1rem; }
    }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <div class="container-fluid">
        
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between py-3">
                    <h4 class="page-title mb-0">Landing Page 5</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item active">Type 6 Manage</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                @include('backend.partials.message')
                
                <div class="card card-custom">
                    <div class="card-body p-0">
                        {{-- Card Header --}}
                        <div class="card-header-custom d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                            <div class="d-flex align-items-center">
                                <h5 class="text-dark mb-0 fw-bold">Dynamic Pages List</h5>
                                <span class="badge bg-soft-warning text-warning ms-2 rounded-pill border border-warning">{{ $items->total() ?? 0 }} Total</span>
                            </div>
                            <div class="d-flex gap-2">
                                @if(auth()->user()->can('product.create') || auth()->user()->can('permission.view'))
                                    <a href="{{ route('admin.landing_pages_six.create') }}" class="btn btn-warning text-white rounded-pill shadow-sm px-4 fw-bold">
                                        <i class="mdi mdi-plus-circle-outline me-1"></i> Create Type 6 Page
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- ✅ Table Section (Fixed Layout, No Scroll) --}}
                        <div class="table-responsive">
                            <table class="table table-hover table-centered table-custom mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 40px; padding-left: 15px;">#</th>
                                        <th style="width: 70px;" class="d-none d-md-table-cell text-center">Image</th>
                                        <th style="width: 35%;">Page Title</th>
                                        <th style="width: 30%;">Product</th>
                                        <th style="width: 50px; text-align: center;">Link</th>
                                        <th style="width: 110px; text-align: right; padding-right: 15px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $key => $item)
                                        <tr>
                                            <td style="padding-left: 15px;">
                                                <span class="fw-semibold text-muted">
                                                    {{ $items->firstItem() + $key }}
                                                </span>
                                            </td>
                                            <td class="d-none d-md-table-cell text-center">
                                                @if($item->right_product_image)
                                                    <img src="{{ asset('landing_pages/'.$item->right_product_image) }}" alt="Img" style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb;">
                                                @elseif($item->product && $item->product->image)
                                                    <img src="{{ getImage('products', $item->product->image) }}" alt="Product" style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb;">
                                                @else
                                                    <div style="width: 40px; height: 40px; border-radius: 6px; background: #f3f4f6; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #e5e7eb;">
                                                        <i class="mdi mdi-image-off-outline text-muted" style="font-size:16px;"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            
                                            {{-- ✅ Truncated Text (...) --}}
                                            <td>
                                                <div class="text-ellipsis text-dark fw-bold mb-1" title="{{ $item->title1 }}">{{ $item->title1 }}</div>
                                                <small class="text-muted" style="font-size: 0.75rem;">ID: {{ $item->id }}</small>
                                            </td>
                                            
                                            {{-- ✅ Truncated Text (...) --}}
                                            <td>
                                                <div class="text-ellipsis text-secondary fw-semibold" title="{{ $item->product ? $item->product->name : 'N/A' }}">
                                                    {{ $item->product ? $item->product->name : 'N/A' }}
                                                </div>
                                            </td>
                                            
                                            <td class="text-center">
                                                <a href="{{ route('front.landing_pages_six.view_page', $item->slug ?: $item->id) }}" target="_blank" class="btn-view-link" data-bs-toggle="tooltip" title="View Live Page">
                                                    <i class="mdi mdi-open-in-new"></i>
                                                </a>
                                            </td>
                                            
                                            <td style="padding-right: 15px;">
                                                <div class="d-flex justify-content-end align-items-center" style="gap: 6px; flex-wrap: nowrap;">
                                                    
                                                    {{-- Edit Button --}}
                                                    @if(auth()->user()->can('product.edit') || auth()->user()->can('permission.view'))
                                                        <a href="{{ route('admin.landing_pages_six.edit', [$item->id]) }}" class="btn-action btn-action-edit" data-bs-toggle="tooltip" title="Edit Page">
                                                            <i class="mdi mdi-pencil-outline"></i>
                                                        </a>
                                                    @endif

                                                    {{-- Color Settings Button --}}
                                                    @if(auth()->user()->can('product.edit') || auth()->user()->can('permission.view'))
                                                        <a href="{{ route('admin.landing_pages.color', [$item->id]) }}" class="btn-action btn-action-color" data-bs-toggle="tooltip" title="Color Settings">
                                                            <i class="mdi mdi-palette-outline"></i>
                                                        </a>
                                                    @endif

                                                    {{-- Delete Button --}}
                                                    @if(auth()->user()->can('product.delete') || auth()->user()->can('permission.view'))
                                                        <button type="button" data-url="{{ route('admin.landing_pages_six.destroy', [$item->id]) }}" class="btn-action btn-action-delete delete-page" data-bs-toggle="tooltip" title="Delete Page">
                                                            <i class="mdi mdi-trash-can-outline"></i>
                                                        </button>
                                                        <form action="{{ route('admin.landing_pages_six.destroy', [$item->id]) }}" method="POST" class="d-none delete-form">
                                                            @csrf @method('DELETE')
                                                        </form>
                                                    @endif
                                                    
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center justify-content-center">
                                                    <i class="mdi mdi-file-document-alert-outline text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
                                                    <h5 class="text-muted mt-2">No Type 6 landing pages found</h5>
                                                    <p class="text-muted small mb-0">Click the create button to add a new page.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        @if($items->hasPages())
                        <div class="card-footer bg-white border-top-0 py-3">
                            {{ $items->links() }}
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
(function($){
    "use strict";
    
    // Initialize Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) { 
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // SweetAlert Delete Logic
    $(document).off('click.lp_delete', '.delete-page').on('click.lp_delete', '.delete-page', function(e){
        e.preventDefault(); 
        e.stopImmediatePropagation();
        
        let btn = $(this); 
        let url = btn.data('url'); 
        let row = btn.closest('tr'); 
        let form = btn.next('.delete-form'); 
        
        if(btn.data('deleting') === 1) return;

        Swal.fire({
            title: 'Are you sure?', 
            text: "You won't be able to revert this!", 
            icon: 'warning',
            showCancelButton: true, 
            confirmButtonColor: '#fa5c7c', 
            cancelButtonColor: '#6c757d', 
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.data('deleting', 1);
                
                $.ajax({
                    url: url, 
                    type: 'POST', 
                    dataType: 'json',
                    data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                    success: function(res){
                        if(res.status === true){ 
                            Swal.fire('Deleted!', res.msg, 'success'); 
                            row.fadeOut(400, function(){ $(this).remove(); }); 
                        } 
                        else { 
                            Swal.fire('Error', (res.msg) ? res.msg : 'Delete failed', 'error'); 
                            btn.data('deleting', 0); 
                        }
                    },
                    error: function(xhr){ 
                        console.error(xhr); 
                        btn.data('deleting', 0); 
                        // Fallback to form submit if AJAX fails
                        if(form.length){ form.submit(); } 
                    }
                });
            }
        });
    });
})(jQuery);
</script>
@endpush