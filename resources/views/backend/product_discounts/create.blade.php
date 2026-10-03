@extends('backend.app')
@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
@endpush
@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active">Product Disocunt Create</li>
                </ol>
            </div>
            <h4 class="page-title">Product Disocunt Create</h4>
        </div>
    </div>
</div>   
<!-- end page title --> 

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.product_discounts.store')}}" id="ajax_form">
                    @csrf
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="row height d-flex justify-content-center align-items-center">

                                <div class="col-md-8">
                                    <div class="search">
                                        <input type="text" id="search" class="form-control" placeholder="product search here">
                                    </div>
                                </div>
                            </div>
                        </div>
                        


                        <div class="col-lg-12 mt-2">
                            <table class="table table-centered table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Sell Price</th>
                                        <th>Discount Type</th>
                                        <th>Discount Amount</th>
                                        <th>After Discount</th>

                                        <th style="width: 125px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                </tbody>
                            </table>
                        </div>

                        <div class="col-lg-12 mt-2">
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </div>

                </form>
       
            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div> <!-- end col -->
</div> <!-- end row -->
@endsection 

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<script type="text/javascript">
    var path = "{{ route('admin.getDiscountProduct') }}";
    const products=[];
    $( "#search" ).autocomplete({
        selectFirst: true, //here
        minLength: 2,
        source: function( request, response ) {
          $.ajax({
            url: path,
            type: 'GET',
            dataType: "json",
            data: {
               search: request.term
            },
            success: function( data ) {
                
                if (data.length ==0) {
                    toastr.error('Product Not Found');
                }
                else if (data.length ==1) {

                    if(products.indexOf(data[0].id) ==-1){
                        productEntry(data[0]);
                        products.push(data[0].id);
                    }
                    
                    $('#search').val('');


                    
                }else if (data.length >1) {
                    response(data);
                }
            }
          });
        },
        select: function (event, ui) {
           productEntry(ui.item);
           $('#search').val('');
           return false;
        }
      });

    function productEntry(item){

        $.ajax({
            url: '{{ route("admin.productEntry")}}',
            type: 'GET',
            dataType: "json",
            data: {product_id:item.id},
            success: function( res ) {
                    
                if (res.data) {
                    $('tbody').append(res.data);
                }
                
            }
          });
    }

    $(document).on('click',".remove",function(e) {
        var whichtr = $(this).closest("tr");
        whichtr.remove();      
    });

    $(document).on('blur', '.dicount_amount', function(){
        let discount_amount=$(this).val();
        let new_price=0;
        var price=$(this).closest('tr').find('td.sell_price').text();
        var discount_type=$(this).closest('tr').find('select.dicount_type').val();

        if (discount_type=='percentage') {
            new_price= (price / 100) * discount_amount;
            new_price=price - new_price;
        }else{
            new_price= price - discount_amount;
        }
        $(this).closest('tr').find('input.after_discount').val(new_price.toFixed(2));
    });
  
</script>

@endpush