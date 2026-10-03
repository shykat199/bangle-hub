@extends('backend.app')
@section('title', 'Landing Page 6')')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .page-title-box .breadcrumb { background: transparent; padding: 0; margin-bottom: 0; }
    .page-title { font-weight: 700; color: #343a40; font-size: 1.25rem; margin-top: 10px; }
    .card-custom { border: none; box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, 0.03); border-radius: 16px; background: #fff; overflow: hidden; border-top: 4px solid #006400; }
    .card-header-custom { background-color: #fff; border-bottom: 1px solid #f1f3fa; padding: 1.5rem; }
    .table-custom thead th { background-color: #f0fdf4; color: #006400; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.02em; padding: 1rem; }
    .table-custom tbody td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid #f1f3fa; color: #495057; font-weight: 500; font-size: 0.9rem; }
    
    .btn-action { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; transition: all 0.2s ease; border: none; margin: 0 2px; text-decoration: none !important; }
    .btn-action-edit { background-color: rgba(14, 165, 233, 0.1); color: #0ea5e9; }
    .btn-action-edit:hover { background-color: #0ea5e9; color: #fff; }
    .btn-action-color { background-color: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
    .btn-action-color:hover { background-color: #8b5cf6; color: #fff; }
    .btn-action-delete { background-color: rgba(250, 92, 124, 0.1); color: #fa5c7c; cursor: pointer; }
    .btn-action-delete:hover { background-color: #fa5c7c; color: #fff; }
    
    .btn-view-link { display: inline-flex; align-items: center; gap: 5px; font-size: 0.85rem; font-weight: 600; color: #006400; background: rgba(0, 100, 0, 0.1); padding: 6px 12px; border-radius: 6px; transition: all 0.2s; text-decoration: none; }
    .btn-view-link:hover { background: #006400; color: #fff; }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between py-3">
                    <h4 class="page-title mb-0">Landing Page 6</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item active">Type 7 Manage</li>
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
                        <div class="card-header-custom d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                            <div class="d-flex align-items-center">
                                <h5 class="text-dark mb-0 fw-bold">Type 7 Pages List</h5>
                                <span class="badge bg-soft-success text-success ms-2 rounded-pill border border-success">{{ $items->total() ?? 0 }} Total</span>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.landing_pages_seven.create') }}" class="btn text-white rounded-pill shadow-sm px-4 fw-bold" style="background: #006400;">
                                    <i class="mdi mdi-plus-circle-outline me-1"></i> Create Type 7 Page
                                </a>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-centered table-nowrap table-custom mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">SL</th>
                                        <th style="width: 80px;">Image</th>
                                        <th>Page Title</th>
                                        <th>Product</th>
                                        <th>Public Link</th>
                                        <th style="width: 160px;" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $key => $item)
                                        <tr>
                                            <td><span class="fw-semibold text-muted">{{ $items->firstItem() + $key }}</span></td>
                                            <td>
                                                @if($item->right_product_image)
                                                    <img src="{{ asset('landing_pages/'.$item->right_product_image) }}" style="width: 45px; height: 45px; object-fit: cover; border-radius: 8px;">
                                                @elseif($item->product && $item->product->image)
                                                    <img src="{{ getImage('products', $item->product->image) }}" style="width: 45px; height: 45px; object-fit: cover; border-radius: 8px;">
                                                @else
                                                    <div style="width: 45px; height: 45px; border-radius: 8px; background: #f3f4f6; display: flex; align-items: center; justify-content: center;"><i class="mdi mdi-image-off-outline text-muted"></i></div>
                                                @endif
                                            </td>
                                            <td>
                                                <h6 class="mb-0 text-dark fw-bold">{{ Str::limit($item->title1, 40) }}</h6>
                                                <span class="badge bg-success mt-1">Type 7</span>
                                            </td>
                                            <td><span class="fw-semibold text-secondary">{{ $item->product ? Str::limit($item->product->name, 30) : 'N/A' }}</span></td>
                                            <td>
                                                <a href="{{ route('front.landing_pages_seven.view_page', $item->slug ?: $item->id) }}" target="_blank" class="btn-view-link"><i class="mdi mdi-open-in-new"></i> View Live Page</a>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-1">
                                                    <a href="{{ route('admin.landing_pages_seven.edit', [$item->id]) }}" class="btn-action btn-action-edit" title="Edit"><i class="mdi mdi-pencil-outline"></i></a>
                                                    <a href="{{ route('admin.landing_pages.color', [$item->id]) }}" class="btn-action btn-action-color" title="Color Settings"><i class="mdi mdi-palette-outline"></i></a>
                                                    <button type="button" data-url="{{ route('admin.landing_pages_seven.destroy', [$item->id]) }}" class="btn-action btn-action-delete delete-page" title="Delete"><i class="mdi mdi-trash-can-outline"></i></button>
                                                    <form action="{{ route('admin.landing_pages_seven.destroy', [$item->id]) }}" method="POST" class="d-none delete-form">@csrf @method('DELETE')</form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center py-5"><h5 class="text-muted mt-2">No Type 7 pages found</h5></td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($items->hasPages()) <div class="card-footer bg-white border-top-0 py-3">{{ $items->links() }}</div> @endif
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
    $(document).on('click', '.delete-page', function(e){
        e.preventDefault(); 
        let btn = $(this); 
        let url = btn.data('url'); 
        let row = btn.closest('tr'); 
        Swal.fire({
            title: 'Are you sure?', text: "You won't be able to revert this!", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#fa5c7c', cancelButtonColor: '#6c757d', confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url, type: 'POST', data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                    success: function(res){
                        if(res.status === true){ Swal.fire('Deleted!', res.msg, 'success'); row.remove(); } 
                    }
                });
            }
        });
    });
})(jQuery);
</script>
@endpush