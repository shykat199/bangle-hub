@extends('backend.app')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />

<style>
    /* Premium Autocomplete Styling */
    .ui-autocomplete {
        background: #ffffff;
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border-radius: 0.5rem;
        padding: 10px;
        max-height: 300px;
        overflow-y: auto;
        z-index: 1050; /* Above everything */
    }
    .ui-menu-item .ui-menu-item-wrapper {
        padding: 10px 15px;
        border-radius: 6px;
        font-size: 14px;
        color: #495057;
        transition: all 0.2s;
    }
    .ui-menu-item .ui-menu-item-wrapper.ui-state-active {
        background: #f8f9fa;
        color: #556ee6; /* Primary Color */
        border: none;
        font-weight: 600;
    }

    /* Input & Table Styling */
    .search-box .form-control {
        background-color: #f3f6f9;
        border: none;
        padding-left: 45px;
        height: 50px;
        font-size: 15px;
    }
    .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #adb5bd;
        font-size: 20px;
        z-index: 10;
    }
    .table-premium thead th {
        background-color: #eff2f7;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        border-top: none;
    }
    .table-premium tbody td {
        vertical-align: middle;
        padding: 15px;
    }
    
    /* Loading Spinner */
    .spinner-overlay {
        position: absolute;
        right: 15px;
        top: 15px;
        display: none;
    }
</style>
@endpush

@section('content')

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between py-3">
            <h4 class="page-title mb-0">Create Free Shipping</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active">Free Shipping Create</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <form method="POST" action="{{ route('admin.store-free-shipping')}}" id="ajax_form">
            @csrf
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h5 class="text-primary"><i class="mdi mdi-basket-plus me-2"></i>Add Products to List</h5>
                        <p class="text-muted">Search and select products to enable free shipping.</p>
                    </div>

                    <div class="row justify-content-center">
                        <div class="col-md-8 col-lg-6 position-relative search-box">
                            <i class="mdi mdi-magnify search-icon"></i>
                            <input type="text" id="search" class="form-control rounded-pill" placeholder="Type product name or SKU...">
                            <div class="spinner-border spinner-border-sm text-primary spinner-overlay" id="search_spinner" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="card-title mb-0">Selected Products</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap table-hover table-premium mb-0">
                            <thead>
                                <tr>
                                    <th>Product Info</th>
                                    <th>Category</th>
                                    <th>Sell Price</th>
                                    <th class="text-end" style="width: 100px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="product_tbody">
                                <tr id="empty_state">
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="mdi mdi-cart-outline display-4 d-block mb-3 opacity-25"></i>
                                        <span>No products added yet. Use the search box above.</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light p-3">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ url()->previous() }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="mdi mdi-content-save me-1"></i> Save Changes</button>
                    </div>
                </div>
            </div>

        </form>
    </div> </div> @endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript">
    var path = "{{ route('admin.getDiscountProduct') }}";
    const products = []; // To prevent duplicates

    $(document).ready(function() {

        // 1. Prevent form submission on Enter key in search box
        $("#search").on('keydown', function(e) {
            if (e.keyCode === 13) { // 13 is Enter key
                e.preventDefault();
                return false;
            }
        });

        $("#search").autocomplete({
            source: function(request, response) {
                // Show spinner
                $('#search_spinner').show();
                
                $.ajax({
                    url: path,
                    type: 'GET',
                    dataType: "json",
                    data: {
                        search: request.term
                    },
                    success: function(data) {
                        $('#search_spinner').hide(); // Hide spinner
                        
                        if (data.length == 0) {
                            // toastr.warning('Product Not Found'); 
                            response([]);
                        } else {
                            response(data);
                        }
                    },
                    error: function() {
                        $('#search_spinner').hide();
                    }
                });
            },
            select: function(event, ui) {
                event.preventDefault(); // Prevent default select behavior
                addProductToTable(ui.item);
                $('#search').val('');
                return false;
            },
            minLength: 2
        }).data("ui-autocomplete")._renderItem = function(ul, item) {
            return $("<li>")
                .append("<div class='ui-menu-item-wrapper'>" + item.label + "</div>") 
                .appendTo(ul);
        };

        // Function to handle adding product
        function addProductToTable(item) {
            // Check for duplicates
            if (products.indexOf(item.id) !== -1) {
                toastr.info('This product is already in the list.');
                return;
            }

            // AJAX call to get just the table row HTML
            $.ajax({
                url: '{{ route("admin.productEntry2")}}',
                type: 'GET',
                dataType: "json",
                data: { product_id: item.id },
                success: function(res) {
                    if (res.data) {
                        $('#empty_state').hide(); // Hide the empty state message
                        $('tbody#product_tbody').append(res.data);
                        products.push(item.id); // Add to tracking array
                        
                        // ✅ Message Changed: "Added to table" instead of "Added successfully"
                        // This avoids confusion about database saving.
                        // You can comment this line out if you don't want ANY message.
                        // toastr.success('Product added to table'); 
                    }
                }
            });
        }

        // Remove Row Functionality
        $(document).on('click', ".remove", function(e) {
            e.preventDefault();
            var whichtr = $(this).closest("tr");
            
            // Logic to remove ID from 'products' array
            var idInput = whichtr.find('input[name="product_ids[]"]');
            if(idInput.length > 0) {
                var idToRemove = parseInt(idInput.val());
                var index = products.indexOf(idToRemove);
                if (index > -1) {
                    products.splice(index, 1);
                }
            }
            
            whichtr.fadeOut(300, function(){
                $(this).remove();
                if($('tbody#product_tbody tr').length <= 1) { // 1 because empty_state is hidden
                    $('#empty_state').show();
                }
            });
        });

    });
</script>
@endpush