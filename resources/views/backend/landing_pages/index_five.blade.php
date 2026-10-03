@extends('backend.app')

@section('content')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .page-title-box .breadcrumb { background: transparent; padding: 0; margin-bottom: 0; }
    .page-title { font-weight: 700; color: #343a40; font-size: 1.25rem; margin-top: 10px; }
    .card-custom { border: none; box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, 0.03); border-radius: 16px; background: #fff; overflow: hidden; border-top: 4px solid #0d9488; }
    .card-header-custom { background-color: #fff; border-bottom: 1px solid #f1f3fa; padding: 1.5rem; }
    .table-custom thead th { background-color: #f0fdf4; color: #0f766e; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.02em; padding: 1rem; }
    .table-custom tbody td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid #f1f3fa; color: #495057; font-weight: 500; font-size: 0.9rem; }
    .btn-action { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; transition: all 0.2s ease; border: none; margin: 0 2px; text-decoration: none !important; }
    .btn-action-edit { background-color: rgba(14, 165, 233, 0.1); color: #0ea5e9; }
    .btn-action-edit:hover { background-color: #0ea5e9; color: #fff; }
    .btn-action-delete { background-color: rgba(250, 92, 124, 0.1); color: #fa5c7c; cursor: pointer; }
    .btn-action-delete:hover { background-color: #fa5c7c; color: #fff; }
    .btn-view-link { display: inline-flex; align-items: center; gap: 5px; font-size: 0.85rem; font-weight: 600; color: #0d9488; background: rgba(13, 148, 136, 0.1); padding: 6px 12px; border-radius: 6px; transition: all 0.2s; text-decoration: none; }
    .btn-view-link:hover { background: #0d9488; color: #fff; }
    .pagination { justify-content: flex-end; margin-bottom: 0; }
</style>
@endpush

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between py-3">
            <h4 class="page-title mb-0">Landing Page 1</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item active">Type 5 Manage</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-body p-0">
                {{-- Card Header --}}
                <div class="card-header-custom d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center">
                        <h5 class="text-dark mb-0 fw-bold">Custom Pages List</h5>
                        <span class="badge bg-soft-success text-success ms-2 rounded-pill">{{ $items->total() ?? 0 }} Total</span>
                    </div>
                    <div class="d-flex gap-2">
                        @can('product.create')
                            <a href="{{ route('admin.landing_pages_five.create') }}" class="btn btn-success rounded-pill shadow-sm px-4" style="background-color: #0d9488; border-color: #0d9488;">
                                <i class="mdi mdi-plus-circle-outline me-1"></i> Create Custom Page
                            </a>
                        @endcan
                    </div>
                </div>

                {{-- Table Section --}}
                <div class="table-responsive">
                    <table class="table table-hover table-centered table-nowrap table-custom mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px;">SL</th>
                                <th>Page Title</th>
                                <th>Public Link</th>
                                <th style="width: 150px;" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $key => $item)
                                <tr>
                                    <td>
                                        <span class="fw-semibold text-muted">
                                            {{ $items->firstItem() + $key }}
                                        </span>
                                    </td>
                                    <td>
                                        <h6 class="mb-0 text-dark">{{ $item->title1 }}</h6>
                                        <small class="text-muted">ID: {{ $item->id }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('front.landing_pages_five.view_page', ['id' => $item->slug ?: $item->id]) }}" target="_blank" class="btn-view-link">
                                            <i class="mdi mdi-open-in-new"></i> View Live Page
                                        </a>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            {{-- Edit Button --}}
                                            @if(auth()->user()->can('product.edit') || auth()->user()->can('permission.view'))
                                                <a href="{{ route('admin.landing_pages_five.edit', [$item->id]) }}" class="btn-action btn-action-edit" data-bs-toggle="tooltip" title="Edit Page">
                                                    <i class="mdi mdi-pencil-outline"></i>
                                                </a>
                                            @endif

                                            {{-- Delete Button --}}
                                            @if(auth()->user()->can('product.delete') || auth()->user()->can('permission.view'))
                                                <button type="button" data-url="{{ route('admin.landing_pages_five.destroy', [$item->id]) }}" class="btn-action btn-action-delete delete-page" data-bs-toggle="tooltip" title="Delete Page">
                                                    <i class="mdi mdi-trash-can-outline"></i>
                                                </button>
                                                <form action="{{ route('admin.landing_pages_five.destroy', [$item->id]) }}" method="POST" class="d-none delete-form">
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
                                            <i class="mdi mdi-file-document-alert-outline text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
                                            <h5 class="text-muted mt-2">No Type 5 landing pages found</h5>
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