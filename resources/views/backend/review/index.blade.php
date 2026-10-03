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
        cursor: pointer;
    }
    .btn-action-delete {
        background-color: rgba(250, 92, 124, 0.1);
        color: #fa5c7c;
    }
    .btn-action-delete:hover {
        background-color: #fa5c7c;
        color: #fff;
    }

    /* Search Box */
    .search-box .form-control {
        border-radius: 30px;
        padding-left: 40px;
        border: 1px solid #e0e0e0;
        background-color: #f8f9fa;
    }
    .search-box .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #74788d;
    }

    /* Product Image */
    .review-img {
        width: 45px;
        height: 45px;
        border-radius: 8px;
        object-fit: cover;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border: 2px solid #fff;
    }

    /* Status Badges */
    .badge-soft-success {
        color: #34c38f;
        background-color: rgba(52, 195, 143, 0.18);
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 11px;
    }
    .badge-soft-warning {
        color: #f1b44c;
        background-color: rgba(241, 180, 76, 0.18);
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 11px;
    }
    
    /* Star Rating Color */
    .text-warning { color: #f1b44c !important; }
</style>
@endpush

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between py-3">
            <h4 class="page-title mb-0">Product Reviews</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);" class="text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item active">Review List</li>
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
                    
                    <div class="d-flex align-items-center w-100" style="max-width: 300px;">
                        <form action="" method="GET" class="w-100">
                            <div class="position-relative search-box">
                                <i class="mdi mdi-magnify search-icon"></i>
                                <input type="text" name="q" class="form-control" placeholder="Search..." value="{{ request('q') }}">
                            </div>
                        </form>
                    </div>
                    
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.reviews.action', ['status' => 1]) }}" class="btn btn-outline-success btn-sm action_btn rounded-pill px-3">
                            <i class="mdi mdi-check-circle-outline me-1"></i> Approve
                        </a>
                        <a href="{{ route('admin.reviews.action', ['status' => 0]) }}" class="btn btn-outline-warning btn-sm action_btn rounded-pill px-3">
                            <i class="mdi mdi-clock-outline me-1"></i> Pending
                        </a>
                        <a href="{{ route('admin.reviews.action', ['delete' => 1]) }}" class="btn btn-outline-danger btn-sm action_btn rounded-pill px-3">
                            <i class="mdi mdi-trash-can-outline me-1"></i> Delete Selected
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-centered table-nowrap table-premium mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px;">
                                    <input type="checkbox" class="form-check-input" id="parent_item">
                                </th>
                                <th>Product Info</th>
                                <th>Customer</th>
                                <th>Rating & Review</th>
                                <th>Status</th>
                                <th class="text-end" style="width: 100px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $review)
                                <tr>
                                    <td>
                                        <input type="checkbox" value="{{ $review->id }}" class="form-check-input user_status">
                                    </td>
                                    
                                    <td>
                                        <h6 class="text-truncate mb-0 text-dark" style="max-width: 200px;" title="{{ $review->product ? $review->product->name : '' }}">
                                            {{ $review->product ? $review->product->name : 'Unknown Product' }}
                                        </h6>
                                    </td>

                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($review->image)
                                                <img src="{{ asset($review->image) }}" alt="" class="review-img me-2">
                                            @else
                                                <div class="avatar-sm me-2">
                                                    <span class="avatar-title bg-soft-primary text-primary font-size-16 rounded-circle p-2">
                                                        {{ substr($review->name, 0, 1) }}
                                                    </span>
                                                </div>
                                            @endif
                                            <div>
                                                <h6 class="mb-0 font-size-14 text-dark">{{ $review->name }}</h6>
                                                <small class="text-muted">{{ $review->created_at->format('d M, Y') }}</small>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="mb-1">
                                            @php
                                                $rating = intval(round($review->review ?? 0));
                                            @endphp
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="mdi mdi-star {{ $i <= $rating ? 'text-warning' : 'text-muted opacity-25' }}"></i>
                                            @endfor
                                        </div>
                                        <p class="mb-0 text-dark" style="max-width: 250px; white-space: normal; line-height: 1.4;">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($review->message), 80) }}
                                        </p>
                                    </td>

                                    <td>
                                        @if($review->status == 1)
                                            <span class="badge badge-soft-success">Approved</span>
                                        @else
                                            <span class="badge badge-soft-warning">Pending</span>
                                        @endif
                                    </td>

                                    <td class="text-end">
                                        {{-- Delete Button using Class Selector --}}
                                        <button type="button" 
                                                class="btn-action btn-action-delete delete-single-review"
                                                data-url="{{ route('admin.reviews.destroy', $review->id) }}"
                                                title="Delete Review">
                                            <i class="mdi mdi-trash-can-outline"></i>
                                        </button>

                                        {{-- Hidden Fallback Form --}}
                                        <form action="{{ route('admin.reviews.destroy', $review->id) }}" 
                                              method="POST" 
                                              class="d-none delete-form">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="mdi mdi-comment-search-outline text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
                                            <h5 class="text-muted mt-2">No reviews found</h5>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div> @if($data->hasPages())
                <div class="card-footer bg-white border-top-0 py-3">
                    <div class="d-flex justify-content-end">
                        {{ $data->links() }}
                    </div>
                </div>
                @endif

            </div> </div> </div> </div> @endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
(function($){
    "use strict";

    // --- 1. Checkbox Select All Logic ---
    $('#parent_item').on('change', function(){
        $('.user_status').prop('checked', this.checked);
    });

    $(document).on('change', '.user_status', function(){
        if($('.user_status:checked').length == $('.user_status').length){
            $('#parent_item').prop('checked', true);
        } else {
            $('#parent_item').prop('checked', false);
        }
    });

    // --- 2. Bulk Actions (Approve/Pending/Delete Selected) ---
    $(document).on('click', '.action_btn', function(e){
        e.preventDefault();

        var url = $(this).attr('href');
        var ids = [];
        
        $('input.user_status:checked').each(function(){
            ids.push($(this).val());
        });

        if (ids.length === 0) {
            Swal.fire('Warning', 'Please select at least one item!', 'warning');
            return;
        }

        let isDelete = url.includes('delete=1');
        let title = isDelete ? 'Are you sure?' : 'Confirm Action?';
        let text = isDelete ? "You won't be able to revert this!" : "Change status for selected items?";
        let confirmBtn = isDelete ? '#fa5c7c' : '#34c38f';

        Swal.fire({
            title: title,
            text: text,
            icon: isDelete ? 'warning' : 'info',
            showCancelButton: true,
            confirmButtonColor: confirmBtn,
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, proceed!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'GET',
                    url: url,
                    data: { ids: ids, _token: '{{ csrf_token() }}' },
                    success: function(res){
                        if(res.status){
                            Swal.fire('Success!', res.msg, 'success');
                            setTimeout(function(){ window.location.reload(); }, 1000);
                        } else {
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        }
                    },
                    error: function(){
                        Swal.fire('Error!', 'Server error occurred.', 'error');
                    }
                });
            }
        });
    });

    // --- 3. Single Item Delete (Fixed) ---
    $(document).on('click', '.delete-single-review', function(e){
        e.preventDefault();
        e.stopImmediatePropagation();

        let btn = $(this);
        let url = btn.data('url');
        let row = btn.closest('tr');
        let form = btn.next('.delete-form');

        // Prevent double click
        if(btn.data('deleting') === 1) return;

        Swal.fire({
            title: 'Are you sure?',
            text: "This review will be permanently deleted!",
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
                        // Logic based on standard JSON response {status: true, msg: "..."}
                        if(res.status === true){
                            Swal.fire('Deleted!', res.msg, 'success');
                            row.fadeOut(400, function(){ $(this).remove(); });
                        } else {
                            Swal.fire('Error', res.msg ? res.msg : 'Delete failed', 'error');
                            btn.data('deleting', 0);
                        }
                    },
                    error: function(xhr){
                        // Fallback: submit hidden form if AJAX fails or returns non-JSON
                        console.error(xhr);
                        btn.data('deleting', 0);
                        if(form.length) form.submit();
                    }
                });
            }
        });
    });

})(jQuery);
</script>
@endpush