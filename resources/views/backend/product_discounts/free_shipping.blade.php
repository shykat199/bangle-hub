@extends('backend.app')

@section('content')

<style>
    .card-premium {
        border: none;
        box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, 0.03);
        border-radius: 12px;
        background: #fff;
    }
    .table-premium thead th {
        background-color: #f8f9fa;
        color: #111;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        border-bottom: 2px solid #eff2f7;
        padding: 12px 15px;
    }
    .table-premium tbody td {
        padding: 15px;
        vertical-align: middle;
        color: #495057;
        border-bottom: 1px solid #eff2f7;
    }
    .product-img {
        width: 45px;
        height: 45px;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .action-btn {
        width: 35px;
        height: 35px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.3s;
        cursor: pointer;
    }
    .action-btn:hover {
        transform: translateY(-2px);
    }
    .page-title {
        font-weight: 700;
        color: #343a40;
    }

    /* Premium SaaS Mobile Responsive Table (No Horizontal Scroll) */
    @media (max-width: 767px) {
        .custom-responsive-table thead {
            display: none;
        }
        .custom-responsive-table tbody tr {
            display: block;
            margin-bottom: 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 0.5rem;
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .custom-responsive-table tbody td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f1f1f1;
            padding: 0.75rem 0.5rem;
            text-align: right;
            font-size: 14px;
        }
        .custom-responsive-table tbody td:last-child {
            border-bottom: none;
            justify-content: flex-end;
        }
        .custom-responsive-table tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #111;
            text-align: left;
            flex-basis: 40%;
        }
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between py-3">
            <h4 class="page-title mb-0">Free Shipping Product List</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active">Discount List</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card card-premium">
            <div class="card-body">
                <div class="row mb-4 align-items-center">
                    <div class="col-md-6 col-lg-4 mb-3 mb-md-0">
                        <div class="position-relative">
                            <input type="search" class="form-control rounded-pill ps-4" id="searchInput" placeholder="Search products...">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="position-absolute top-50 end-0 translate-middle-y me-3 text-muted"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-8">
                        <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                            @can('discount.create')
                            <a href="{{ route('admin.create_free_shipping')}}" class="btn btn-dark rounded-pill shadow-sm d-inline-flex align-items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                Add Product
                            </a>
                            @endcan
                            <button type="button" class="btn btn-outline-secondary rounded-pill d-inline-flex align-items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                Export
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-hover table-premium custom-responsive-table mb-0" id="productTable">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Image</th>
                                <th class="text-end" style="width: 100px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                            <tr id="row_{{ $item->id }}">
                                <td data-label="Product Name">
                                    <h5 class="font-size-14 mb-0 text-dark">{{$item->name}}</h5>
                                </td>
                                <td data-label="Image">
                                    <div class="flex-shrink-0">
                                        <img src="{{ getImage('products',$item->image)}}" class="rounded-circle product-img" alt="{{$item->name}}">
                                    </div>
                                </td>
                                <td data-label="Action" class="text-end">
                                    <button type="button" 
                                            class="btn btn-soft-danger text-danger bg-danger bg-opacity-10 action-btn delete-item-btn" 
                                            data-id="{{ $item->id }}" 
                                            title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted d-block w-100 border-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="d-block mx-auto mb-2 opacity-50"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                    No products found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if(method_exists($items, 'links'))
                <div class="mt-4 d-flex justify-content-end">
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

<script type="text/javascript">
$(document).ready(function() {
    
    // Search Functionality
    $("#searchInput").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#productTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Delete Action
    $(document).on('click', '.delete-item-btn', function (e){
        e.preventDefault(); 
        
        let id = $(this).data('id');
        let url = "{{ route('admin.free-shipping.fshippingdestroy')}}";
        let row = $('#row_' + id);

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#111', // Changed to match minimal dark theme
            cancelButtonColor: '#e2e8f0', // Soft cancel button
            confirmButtonText: 'Yes, delete it!',
            customClass: {
                cancelButton: 'text-dark'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                
                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: "json",
                    data: {product_id: id},
                    success: function(res) {
                        if(res.status == true){
                            Swal.fire(
                                'Deleted!',
                                res.msg,
                                'success'
                            );
                            
                            row.fadeOut(500, function(){
                                $(this).remove();
                            });
                        } else {
                            Swal.fire(
                                'Error!',
                                'Something went wrong!',
                                'error'
                            );
                        }
                    },
                    error: function(err){
                         Swal.fire(
                            'Error!',
                            'Server Error occurred.',
                            'error'
                        );
                    }
                });
            }
        });
    });
});
</script>
@endpush