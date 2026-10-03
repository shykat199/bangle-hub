@extends('backend.app')

@section('content')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<style>
    /* Premium Page Styling */
    .page-title-box .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 0;
    }
    .page-title {
        font-weight: 700;
        color: #343a40;
        font-size: 1.25rem;
        margin-top: 10px;
    }

    /* Premium Card Design */
    .card-premium {
        border: none;
        box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, 0.03);
        border-radius: 16px;
        background: #fff;
        overflow: hidden;
    }
    .card-header-premium {
        background-color: #fff;
        border-bottom: 1px solid #f1f3fa;
        padding: 1.5rem;
    }

    /* Modern Table Styling */
    .table-premium thead th {
        background-color: #f8f9fa;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.02em;
        border-bottom: 0;
        padding: 1rem;
    }
    .table-premium tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f3fa;
        color: #495057;
        font-weight: 500;
        font-size: 0.9rem;
    }
    .table-premium tbody tr:last-child td {
        border-bottom: none;
    }
    .table-hover tbody tr:hover {
        background-color: #f9fbfd;
    }

    /* Action Buttons */
    .btn-action {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s ease;
        border: none;
        margin: 0 2px;
        text-decoration: none !important;
    }
    .btn-action-view {
        background-color: rgba(57, 175, 209, 0.1);
        color: #39afd1;
    }
    .btn-action-view:hover {
        background-color: #39afd1;
        color: #fff;
    }
    .btn-action-edit {
        background-color: rgba(114, 124, 245, 0.1);
        color: #727cf5;
    }
    .btn-action-edit:hover {
        background-color: #727cf5;
        color: #fff;
    }
    .btn-action-delete {
        background-color: rgba(250, 92, 124, 0.1);
        color: #fa5c7c;
        cursor: pointer;
    }
    .btn-action-delete:hover {
        background-color: #fa5c7c;
        color: #fff;
    }

    /* View Link Button */
    .btn-view-link {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #0acf97;
        background: rgba(10, 207, 151, 0.1);
        padding: 6px 12px;
        border-radius: 6px;
        transition: all 0.2s;
        text-decoration: none;
    }
    .btn-view-link:hover {
        background: #0acf97;
        color: #fff;
    }
</style>
@endpush

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between py-3">
            <h4 class="page-title mb-0">Landing Page 2</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);" class="text-muted">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);" class="text-muted">CRM</a></li>
                    <li class="breadcrumb-item active">Page Manage</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card card-premium">
            <div class="card-body p-0">
                
                <div class="card-header-premium d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center">
                        <h5 class="text-dark mb-0 fw-bold">All Landing Pages</h5>
                        <span class="badge bg-soft-primary text-primary ms-2 rounded-pill">{{ count($items) }} Total</span>
                    </div>
                    
                    <div class="d-flex gap-2">
                        @can('product.create')
                            <a href="{{ route('admin.landing_pages_two.create') }}" class="btn btn-danger rounded-pill shadow-sm px-4">
                                <i class="mdi mdi-plus-circle-outline me-1"></i> Add New Page
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-centered table-nowrap table-premium mb-0">
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
                                        <span class="fw-semibold text-muted">{{ $key + 1 }}</span>
                                    </td>
                                    
                                    <td>
                                        <h6 class="mb-0 text-dark">{{ $item->title1 }}</h6>
                                    </td>

                                    <td>
                                        <a href="{{ route('front.landing_pages_two.view_page', ['id' => $item->slug ?: $item->id]) }}" target="_blank" class="btn-view-link">
                                            <i class="mdi mdi-open-in-new"></i> View Page
                                        </a>
                                    </td>

                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            @if(auth()->user()->can('permission.view'))
                                                <a href="{{ route('admin.landing_pages_two.edit', [$item->id]) }}" 
                                                   class="btn-action btn-action-edit" 
                                                   target="_blank" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Edit Page">
                                                    <i class="mdi mdi-pencil-outline"></i>
                                                </a>
                                            @endif

                                            @if(auth()->user()->can('permission.view'))
                                                {{-- AJAX Delete Button --}}
                                                <button type="button"
                                                        data-url="{{ route('admin.landing_pages_two.destroy', [$item->id]) }}"
                                                        class="btn-action btn-action-delete delete-page"
                                                        data-bs-toggle="tooltip" 
                                                        title="Delete Page">
                                                    <i class="mdi mdi-trash-can-outline"></i>
                                                </button>

                                                {{-- Fallback Form --}}
                                                <form action="{{ route('admin.landing_pages_two.destroy', [$item->id]) }}"
                                                      method="POST"
                                                      class="d-none delete-form">
                                                    @csrf
                                                    @method('DELETE')
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
                                            <h5 class="text-muted mt-2">No landing pages found</h5>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div> 
                
                @if(method_exists($items, 'links'))
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

    // Initialize Bootstrap Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Delete Action Handler
    $(document).off('click.lptwo_delete', '.delete-page').on('click.lptwo_delete', '.delete-page', function(e){
        e.preventDefault();
        e.stopImmediatePropagation();

        let btn = $(this);
        let url = btn.data('url');
        
        // Find row & form dynamically
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
                    data: { 
                        _token: '{{ csrf_token() }}', 
                        _method: 'DELETE' 
                    },
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

    // ✅ FIXED PIXEL CODE HERE
    if (typeof fbq === 'function') {
        const eventID = "IC_" + "{{ now()->format('Ymdhi') }}";
        
        // Dummy data for admin panel view
        let total = 0; 
        let items = []; 

        fbq('track', 'InitiateCheckout', {
            value: total,
            currency: 'BDT',
            contents: [],
            content_type: 'product',
            content_ids: []
        }, { eventID: eventID });
        
        console.log("InitiateCheckout EventID:", eventID);
    }

})(jQuery);
</script>
@endpush