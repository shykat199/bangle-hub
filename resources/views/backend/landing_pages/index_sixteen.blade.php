@extends('backend.app')

@section('content')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .page-title-box .breadcrumb { background: transparent; padding: 0; margin-bottom: 0; }
    .page-title { font-weight: 700; color: #14532d; font-size: 1.25rem; margin-top: 10px; }
    .card-premium { border: none; box-shadow: 0 0.75rem 1.5rem rgba(20, 83, 45, 0.06); border-radius: 16px; background: #fff; overflow: hidden; }
    .card-header-premium { background-color: #f0fdf4; border-bottom: 1px solid #dcfce7; padding: 1.5rem; }
    .table-premium thead th { background-color: #f0fdf4; color: #14532d; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.02em; border-bottom: 0; padding: 1rem; }
    .table-premium tbody td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid #f1f3fa; color: #495057; font-weight: 500; font-size: 0.9rem; }
    .btn-action { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; transition: all 0.2s ease; border: none; margin: 0 2px; text-decoration: none !important; }
    .btn-action-edit { background-color: rgba(20, 83, 45, 0.1); color: #14532d; }
    .btn-action-edit:hover { background-color: #14532d; color: #fff; }
    .btn-action-delete { background-color: rgba(239, 68, 68, 0.1); color: #ef4444; cursor: pointer; }
    .btn-action-delete:hover { background-color: #ef4444; color: #fff; }
    .btn-view-link { display: inline-flex; align-items: center; gap: 5px; font-size: 0.85rem; font-weight: 600; color: #14532d; background: rgba(20, 83, 45, 0.1); padding: 6px 12px; border-radius: 6px; transition: all 0.2s; text-decoration: none; }
    .btn-view-link:hover { background: #14532d; color: #fff; }
    .btn-create-punjabi { background: linear-gradient(135deg, #14532d, #166534); color: #f7f1de; border: none; padding: 10px 24px; border-radius: 50px; font-weight: 600; box-shadow: 0 4px 12px rgba(22, 101, 52, 0.25); }
    .btn-create-punjabi:hover { color: #f7f1de; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(22, 101, 52, 0.35); }
</style>
@endpush

<div class="row">
    <div class="col-12">
        <div class="card card-premium">
            <div class="card-body p-0">
                <div class="card-header-premium d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center">
                        <h5 class="text-dark mb-0 fw-bold">Landing Page 15</h5>
                        <span class="badge bg-success ms-2 rounded-pill">{{ count($items) }} Total</span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.landing_pages_sixteen.create') }}" class="btn btn-create-punjabi">
                            <i class="mdi mdi-hanger me-1"></i> Create Punjabi Page
                        </a>
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
                                    <td><span class="fw-semibold text-muted">{{ $key + 1 }}</span></td>
                                    <td><h6 class="mb-0 text-dark">{{ $item->title1 }}</h6></td>
                                    <td>
                                        <a href="{{ route('front.landing_pages_sixteen.view_page', ['id' => $item->slug ?: $item->id]) }}" target="_blank" class="btn-view-link">
                                            <i class="mdi mdi-open-in-new"></i> View Page
                                        </a>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="{{ route('admin.landing_pages_sixteen.edit', [$item->id]) }}" class="btn-action btn-action-edit" target="_blank" data-bs-toggle="tooltip" title="Edit Page">
                                                <i class="mdi mdi-pencil-outline"></i>
                                            </a>
                                            <button type="button" data-url="{{ route('admin.landing_pages_sixteen.destroy', [$item->id]) }}" class="btn-action btn-action-delete delete-page" data-bs-toggle="tooltip" title="Delete Page">
                                                <i class="mdi mdi-trash-can-outline"></i>
                                            </button>
                                            <form action="{{ route('admin.landing_pages_sixteen.destroy', [$item->id]) }}" method="POST" class="d-none delete-form">
                                                @csrf @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="mdi mdi-hanger text-success" style="font-size: 3rem; opacity: 0.5;"></i>
                                            <h5 class="text-muted mt-2">No Punjabi pages found</h5>
                                            <p class="text-muted small">Click "Create Punjabi Page" to add your first one</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(method_exists($items, 'links'))
                <div class="card-footer bg-white border-top-0 py-3">{{ $items->links() }}</div>
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
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (el) { return new bootstrap.Tooltip(el); });

    $(document).off('click.lp16_delete', '.delete-page').on('click.lp16_delete', '.delete-page', function(e){
        e.preventDefault(); e.stopImmediatePropagation();
        let btn = $(this); let url = btn.data('url'); let row = btn.closest('tr'); let form = btn.next('.delete-form');
        if(btn.data('deleting') === 1) return;

        Swal.fire({
            title: 'Are you sure?', text: "You won't be able to revert this!", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#6c757d', confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.data('deleting', 1);
                $.ajax({
                    url: url, type: 'POST', dataType: 'json',
                    data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                    success: function(res){
                        if(res.status === true){ Swal.fire('Deleted!', res.msg, 'success'); row.fadeOut(400, function(){ $(this).remove(); }); }
                        else { Swal.fire('Error', (res.msg) ? res.msg : 'Delete failed', 'error'); btn.data('deleting', 0); }
                    },
                    error: function(){ btn.data('deleting', 0); if(form.length){ form.submit(); } }
                });
            }
        });
    });
})(jQuery);
</script>
@endpush
