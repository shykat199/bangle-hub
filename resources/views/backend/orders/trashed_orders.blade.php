@extends('backend.app')
@section('content')

<style>
  .ps-2, .page-title {
      color: black !important;
  }
 .disable-click{
    pointer-events:none;
}
</style>

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active">Order List</li>
                </ol>
            </div>
            <h4 class="page-title">Trashed Order List</h4>
        </div>
    </div>
</div>
<!-- end page title -->
<style>
    .btn.btn-sm{
        font-size: 12px;
    }
</style>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row mb-2">

                    <div class="col-xl-6 text-left">
                        <div class="#" style="">
                            @can('product.create')
                                <a href="{{ route('admin.orders.create')}}" class="btn btn-sm btn-danger mb-1"><i class="mdi mdi-basket me-1"></i> Add New Order</a>
                            @endcan
                          
                        </div>
                    </div>
                    <div class="col-md-6 text-right">
                             <div class="d-none d-md-block d-lg-block">
                <form class="row gy-2 gx-2 align-items-center justify-content-xl-start justify-content-between" id="filter_form">
              <div class="col-12 d-flex justify-content-between align-items-center">
                 
                <div class="col-10" style="float: left;">
                    <label for="inputPassword2" class="visually-hidden">Search</label>
                    <input type="search" class="form-control" id="inputPassword2" placeholder="Search..." name="q">
                </div>
                
                  <div class="col-auto">
                    <label for="submit" class="visually-hidden">Submit</label>
                    <input type="button" class="form-control btn btn-sm btn-dark py-1" id="submit_search" value="Submit">
                  	</div>
     				</div>
                </form>   
              </div>
                     </div> 
                    <div class="col-xl-4 d-none">
                        <div class="text-xl-end mt-xl-0 mt-2">
                            <a type="button" href="{{ route('admin.orderExport')}}" class="btn btn-light mb-2">Export</a>
                        </div>
                    </div><!-- end col-->
                </div>

              <div class="col-sm-12 mt-2" id="rcvd_order">
                <div class="table-responsive">
                    <table class="table table-centered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>
                                    <div class="form-check">
                                      <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input check_all" value="">
                                      </label>
                                    </div>
                                </th>

                              	<th style="width:7%">Action</th>
                                <th>Invoice ID</th>
                                <th>Date Order</th>
                                <th>Customers</th>
                                <th>Product SKU</th>
                                <th>Status</th>
                                <th>Payment Status</th>
                                <th>Assign User</th>
                              	<th style="width:15%">Courier</th>
                                <th>Amount</th>
                               <!-- <th>Discount</th> -->

                               <!-- <th>Due</th> -->

                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trashed_orders as $item)
                            <tr>
                                <td>
                                    <input type="checkbox" class="order_checkbox" value="{{ $item->id}}">
                                </td>

                              	<td>
                                    <a href="{{$item->status === 'pending' ? 'javascript:void(0)' : route('admin.orders.show',[$item->id])}}" target="{{$item->status === 'pending' ? '' : '_blank'}}" class="action-icon " title="{{$item->status === 'pending' ? 'pending invoice will not be printed' : 'Print Invoice'}}"> <i class="fa fa-print" aria-hidden="true"></i></a>
                                    <a href="{{ route('admin.orders.edit',[$item->id])}}" class="action-icon"> <i class="mdi mdi-square-edit-outline"></i></a>
                                    <a href="" data-id="{{ $item->id }}" class="restore_tag action-icon" title="Restore"> <i class="fas fa-trash-restore"></i></a>
                                    @can('order.delete')
                                    <a href="{{ route('admin.forceDel',[$item->id])}}" class="force_delete action-icon"> <i class="mdi mdi-delete"></i></a>
                                    @endcan
                                </td>
                                <td style="color: #000;">#{{$item->invoice_no}}</td>
                              	<td style="color: #000;">{{ dateFormate($item->date)}}</td>
                                <td style="color: #000;">{{$item->first_name.' '.$item->last_name}}<br>
                                    {{$item->shipping_address}}<br>
                                    {{$item->mobile}}
                                </td>
                              <td>

                                  <?php
                                    foreach($item->details as $detail)
                                    {
                                        if(!isset($detail->product['sku']) || $detail->product['sku'] == '')
                                        {
                                            ?> <span style="color: red;">Unavailable</span> <?php

                                        } else
                                        {
                                            echo $detail->product['sku'];
                                        }
                                    }
                                  ?>

                                </td>
                                <td><a class="btn_modal" href="{{ route('admin.orderStatus', $item->id)}}">
                                        <h5 class="my-0"><span class="badge badge-info-lighten">{{$item->status}}</span></h5>
                                    </a>
                                </td>
                                <td><a class="btn_modal" href="{{ route('admin.order_payments.edit', $item->id)}}">
                                        <h5 class="my-0"><span class="badge badge-danger-lighten">{{$item->payment_status}}</span></h5>
                                    </a>
                                </td>

                                <td style="color: #000;">{{ $item->assign?$item->assign->username:''}}</td>
                                <td style="color: #000;">{{ $item->courier?$item->courier->name:''}} <br> {{ $item->courier_tracking_id ?? ''}}
                              		<br> {{ $item->area_name ?? ''}}
                              	</td>
								<td style="color: #000;">
                                  @php
                                    $final_amount = $item->final_amount;
                                    $fa = intval($final_amount);
                                   echo $fa;
                                  @endphp
                              </td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                  	 </div>
                    <p>{!! urldecode(str_replace("/?","?",$trashed_orders->appends(Request::all())->render())) !!}</p>
                </div>
            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div> <!-- end col -->
</div> <!-- end row -->
@endsection

@push('js')
<script src="{{ asset('backend/js/order.js')}}"></script>
<script>

$(document).ready(function(){

  $("select[name='redx_status']").on('change', function(){
      getOrderList();
  });

  $("select[name='courier_type']").on('change', function(){
      getOrderList();
  });

  $('.order_sts').on('click', function(){
      getOrderList();
  });

  $(document).on('click', '.restore_tag', function(e) {
    e.preventDefault();
    let id = $(this).data('id');
    $.ajax({
        type: 'GET',
        url: "{{ route('admin.restore_order')}}",
        data: {id},
        success: function(res) {
            if (res.success) {
                toastr.success(res.msg);
            }
            if(!res.url) {
                window.location.reload();
            }
        }
    });
  });

  function getOrderList()
  {
   	  var statusValue = $("input[name='status']:checked").val();
      var redx_status = $("select[name='redx_status']").val();
      var courier_type = $("select[name='courier_type']").val();
      $.ajax({
        type: 'GET',
      	url: "{{ route('admin.status_wise_order') }}",
        data: {statusValue, redx_status, courier_type},
        success: function(res){
      		if(res.success == true){
              $('#rcvd_order').html(res.view);
            }
        }
      });
  }

    $('#submit_search').on('click', function(){
      var searchValue = $("input[name='q']").val();
      $.ajax({
        type: 'GET',
      	url: "{{ route('admin.searchOrder') }}",
        data: {searchValue},
        success: function(res){
      		if(res.success == true){
              $('#rcvd_order').html(res.view);
            }
        }
      });

    });

    $(".check_all").on('change',function(){
      $(".order_checkbox").prop('checked',$(this).is(":checked"));
    });

    $(document).on('submit', 'form#order_status_update_form', function(e){
        e.preventDefault();
        var url = $(this).attr('action');
        var method = $(this).attr('method');
        let status=$(document).find('select#multi_status').val();

        var order = $('input.order_checkbox:checked').map(function(){
          return $(this).val();
        });
        var order_ids=order.get();

        if(order_ids.length ==0){
            toastr.error('Please Select An Order First !');
            return ;
        }

        $.ajax({
           type:'GET',
           url:url,
           data:{status,order_ids},
           success:function(res){
               if(res.status==true){
                toastr.success(res.msg);
                window.location.reload();

            }else if(res.status==false){
                toastr.error(res.msg);
            }
           }
        });

    });

    $(document).on('submit', 'form#order_assign_form', function(e){
        e.preventDefault();
        var url = $(this).attr('action');
        var method = $(this).attr('method');
        let assign_user_id=$(document).find('select#assign_user_id').val();

        var order = $('input.order_checkbox:checked').map(function(){
          return $(this).val();
        });
        var order_ids=order.get();

        if(order_ids.length ==0){
            toastr.error('Please Select An Order First !');
            return ;
        }

        $.ajax({
           type:'GET',
           url:url,
           data:{assign_user_id,order_ids},
           success:function(res){
               if(res.status==true){
                toastr.success(res.msg);
                window.location.reload();

            }else if(res.status==false){
                toastr.error(res.msg);
            }
           }
        });

    });
    
    $(document).on('click', '.force_delete', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');

        swal({
            title: "স্থায়ীভাবে ডিলিট?",
            text: "এই অর্ডার চিরতরে মুছে যাবে — আর কখনোই ফেরানো যাবে না!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "হ্যাঁ, চিরতরে মুছুন",
            cancelButtonText: "না",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function(isConfirm){
            if(!isConfirm) return;
            $.ajax({
               type:'GET',
               url:url,
               data:{},
               success:function(res){
                   if(res.status==true){
                    toastr.success(res.msg);
                    window.location.reload();

                }else if(res.status==false){
                    toastr.error(res.msg);
                }
               }
            });
        });
    });

    $(document).on('click', 'a.multi_order_delete', function(e){
        e.preventDefault();
        var url = $(this).attr('href');

        var order = $('input.order_checkbox:checked').map(function(){
          return $(this).val();
        });
        var order_ids=order.get();

        if(order_ids.length ==0){
            toastr.error('Please Select An Order First !');
            return ;
        }

        $.ajax({
           type:'GET',
           url:url,
           data:{order_ids},
           success:function(res){
               if(res.status==true){
                toastr.success(res.msg);
                window.location.reload();

            }else if(res.status==false){
                toastr.error(res.msg);
            }
           }
        });

    });

    $(document).on('click', 'a.multi_order_print', function(e){
        e.preventDefault();
        var url = $(this).attr('href');

        var order = $('input.order_checkbox:checked').map(function(){
          return $(this).val();
        });
        var order_ids=order.get();

        if(order_ids.length ==0){
            toastr.error('Please Select Atleast One Order!');
            return ;
        }

        $.ajax({
           type:'GET',
           url,
           data:{order_ids},
           success:function(res){
               if(res.status==true){
                   console.log(res.items, res.info);
                   var myWindow = window.open("", "_blank");
  				   myWindow.document.write(res.view);
                // toastr.success(res.msg);
                // window.location.reload();

            }else if(res.status==false){
                toastr.error(res.msg);
            }
           }
        });

    });


  //Redx Courier Service
  $(document).on('click', 'a.send_to_redx', function(e){
        e.preventDefault();
    // 	var statusValue = $("input[name='status']:checked").val();
        var url = $(this).attr('href');
    	let link = $(this);
        var order = $('input.order_checkbox:checked').map(function(){
          return $(this).val();
        });
        var order_ids=order.get();

        if(order_ids.length ==0){
            toastr.error('Please Select Atleast One Order!');
            return ;
        }

        // else if(statusValue != 'on_the_way'){
        //     toastr.error('Only On The Way Orders are Accepted!');
        //     return ;
        // }


        $.ajax({
           type:'GET',
           url,
           data:{order_ids},
           beforeSend: function(){
             link.addClass('disable-click');
             link.text('Please wait...');
           },
           success:function(res){
               link.removeClass('disable-click');
               link.text('Send to Redx');
               if(res.status){
                toastr.success(res.msg);

            }else{
                toastr.error(res.msg);
            }
           }
        });

    });

  //Pathao Courier Service
  $(document).on('click', 'a.send_to_pathao', function(e){
        e.preventDefault();
    	var statusValue = $("input[name='status']:checked").val();
        var url = $(this).attr('href');
    	let link = $(this);
        var order = $('input.order_checkbox:checked').map(function(){
          return $(this).val();
        });
        var order_ids=order.get();

        if(order_ids.length ==0){
            toastr.error('Please Select Atleast One Order!');
            return ;
        }

        else if(statusValue != 'on_the_way'){
            toastr.error('Only On The Way Orders are Accepted!');
            return ;
        }

        $.ajax({
           type:'GET',
           url,
           data:{order_ids},
           beforeSend: function(){
             link.addClass('disable-click');
             link.text('Please wait...');
           },
           success:function(res){
               link.removeClass('disable-click');
               link.text('Send to Pathao');
               if(res.status){
                toastr.success(res.msg);

            }else{
                toastr.error(`Invoice :${res.invoice} something went wrong!`);
              	console.log(res.errors);
            }
           }
        });

    });

  //Steadfast Courier Service
  $(document).on('click', 'a.send_to_steadfast', function(e){
        e.preventDefault();
    	var statusValue = $("input[name='status']:checked").val();
        var url = $(this).attr('href');
    	let link = $(this);
        var order = $('input.order_checkbox:checked').map(function(){
          return $(this).val();
        });
        var order_ids=order.get();

        if(order_ids.length ==0){
            toastr.error('Please Select Atleast One Order!');
            return ;
        }

        // else if(statusValue != '0'){
        //     toastr.error('Only On The Way Orders are Accepted!');
        //     return ;
        // }


        $.ajax({
           type:'GET',
           url,
           data:{order_ids},
           beforeSend: function(){
             link.addClass('disable-click');
             link.text('Please wait...');
           },
           success:function(res){
               link.removeClass('disable-click');
               link.text('Send to Steadfast');
               if(res.status){
                toastr.success(res.msg);

            }else{
                toastr.error(`Invoice :${res.invoice} something went wrong!`);
              	console.log(res.errors);
            }
           }
        });

    });

})

</script>
@endpush
