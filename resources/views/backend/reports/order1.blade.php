@extends('backend.app')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active">Order report</li>
                </ol>
            </div>
            <h4 class="page-title">Order Report</h4> 
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
                        <form class="row gy-2 gx-2 align-items-center justify-content-xl-start justify-content-between" id="order_report_form" method="GET" action="{{ route('admin.report.order.search') }}">
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
                                <th width="12%">Invoice No</th>
                                <th width="12%">Customer</th>
                                <th width="15%">Phone</th>
                                <th width="13%">Address</th>
                                <th width="15%">Product</th>
                                <th>Quantity</th>
                                <th width="10%">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($details_data as $item)
                            <tr>                               
                                <td><a href="{{ route('admin.orders.show',[$item->order->id])}}" target="_blank" class="text-body fw-bold">#{{$item->order->invoice_no}}</a> </td>
                                <td>{{$item->order->first_name}}</td>
                                <td>{{$item->order->mobile}}</td>
                                <td>{{$item->order->shipping_address}}</td>
                                <td>{{$item->product->name}}</td>
                                <td>{{$item->quantity}}</td>
                                <td>{{$item->unit_price * $item->quantity}}</td>                             
                            </tr>
                            @empty
                            <center>
                                <h3 class='text-danger'>No data found</h3>
                            </center>
                            @endforelse
                        </tbody>
                        <br/>
                       <tfoot>
                            {{ $details_data->links() }}
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

    
    $(".print-btn").click(function(){
        print();
    })
    

});
  
</script>
@endpush