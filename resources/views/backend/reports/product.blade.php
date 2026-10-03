@extends('backend.app')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active">Product Stock Report</li>
                </ol>
            </div>
            <h4 class="page-title">Product Stock Report</h4> 
        </div>
    </div>
</div>   
<!-- end page title --> 

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                 <div class="row mb-2">
                    <div class="col-md-12 no-print">
                        <form class="row gy-2 gx-2 align-items-center justify-content-xl-start justify-content-between" id="order_report_form" method="GET" action="{{ route('admin.report.product.search') }}">
                            @csrf
                            <div class="col-md-4">
                                <label for="query" class="visually-hidden">Search</label>
                                <input type="search" class="form-control" id="query" placeholder="Search..." name="query" value="{{ old('query') }}">
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <label for="status-select" class="me-2">Status</label>
                                    <select class="form-select" id="status-select" name="status">
                                        <option selected value="">Choose...</option>
                                        @foreach(getOrderStatus() as $key=>$value)
                                        <option value="{{$key}}" >{{$value}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>                            
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <label for="status-select" class="me-2">Assign By</label>
                                    <select class="form-select" id="assign" name="assign">
                                        <option selected value="">Choose...</option>
                                        @foreach($users as $user)
                                        <option value="{{$user->id}}" >{{full_name($user)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>                                
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <label for="status-select" class="me-2">From:</label>
                                    <input type="date" name="from" id="from" class="form-control"/>
                                </div>
                            </div>                            
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <label for="status-select" class="me-2">To:</label>
                                    <input type="date" name="to" id="to" class="form-control"/>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <label for="status-select" class="me-2">Courier</label>
                                    <select class="form-select" id="courier" name="courier">
                                        <option selected value="">Choose...</option>
                                        @foreach($couriers as $courier)
                                        <option value="{{$courier->id}}" >{{$courier->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>    
                            <div class="col-auto">
                                <label for="submit" class="visually-hidden">Submit</label>
                                <input type="submit" class="form-control btn btn-sm btn-primary" value="Submit">
                                
                            </div>
                        </form>                            
                    </div>
                    <div align="right" class="no-print">
                        <button class="btn btn-info print-btn">Print</button>
                    </div>
              <div class="col-sm-12">
                <div class="table-responsive">
                    <table class="table table-centered mb-0" id="order_report_table">
                        <thead class="table-light">
                            <tr>
                                <th width="12%">Product Name</th>
                                <th width="12%">Product Price</th>
                                <th width="15%">Product Quantity</th>                               
                                <th width="10%">Total Price</th>
                            </tr>
                        </thead>
                        <tbody>
                          @php $totalPrice = 0;$totalQuantity = 0;  @endphp
                          
                            @forelse($details as $item)
                            <tr>                             
                              
                                <td>{{$item->name}}</td> 
                              <td>{{$item->unit_price}}</td>        
                                  
                               <td>{{$item->total_qty}}</td>        
                              <td>{{$item->unit_price * $item->total_qty}}</td>     
                                @php 
                                $totalPrice += $item->unit_price * $item->total_qty;
                                $totalQuantity += $item->total_qty
                              @endphp                              
                            </tr>                    
                          
                            @empty
                            <center>
                                <h3 class='text-danger'>No data found</h3>
                            </center>
                            @endforelse
                          
                          <tr style="font-weight:bold">
                              <td class="text-center" colspan="2">Total</td>  
                               <td>{{ $totalQuantity }}</td>  
                               <td>{{ $totalPrice }}</td>  
                          </tr>  
                          
                        </tbody>
                        <br/>
                       <tfoot >
                           
                       </tfoot>
                    </table>
                  	 </div>
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
/*
   $("#order_report_form").submit(function(e){
       e.preventDefault();
       let method = $(this).attr('method');
       let url = $(this).attr('action');
       let data = $(this).serialize();
       
       $.ajax({
           url,
           method,
           data,
           beforeSend: function()
           {
               
           },
           success: function(res)
           {
               if(res.status === 'success')
               {
                console.log(res.details);
                    $("body").html(res.data);                
               }
               else{
                   
               }
           }
       });
   });
   
*/
    
    $(".print-btn").click(function(){
        print();
    })    

});
  
</script>
@endpush