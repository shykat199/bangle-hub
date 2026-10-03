@extends('backend.app')
@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
@endpush
@section('content')
	<div class="container card mt-3">
		<div class="row">
		    <div class="col-12">
		        <div class="page-title-box">
		            <div class="page-title-right">
		                <ol class="breadcrumb m-0">
		                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
		                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
		                    <li class="breadcrumb-item active">Purchase Details</li>
		                </ol>
		            </div>
		            <h4 class="page-title">Purchase Details</h4>
		        </div>
		    </div>
		</div>  
		<div class="row table-responsive">
			<table class="table table-bordered table-hover">
				<thead class="table-light">
					<tr>
						<th>Date</th>
						<th>Supplier</th>
						<th>User</th>
						<th>Note</th>
						<th>Reference No.</th>
						<th>Status</th>
						<th>Discount Type</th>
						<th>Discount Amount</th>
						<th>Shipping Cost</th>
						<th>Total Amount</th>
					</tr>
				</thead>
				<tbody>
					@foreach($purchases as $purchase)
						<tr>
							<td>{{$purchase->date}}</td>
							<td>{{$purchase->name}}</td>
							<td>{{$purchase->first_name}} {{$purchase->last_name}}</td>
							<td>{{$purchase->note}}</td>
							<td>{{$purchase->ref}}</td>
							<td>{{$purchase->status}}</td>
							<td>{{$purchase->discount_type}}</td>
							<td>{{$purchase->discount_amount}}</td>
							<td>{{$purchase->shipping_cost}}</td>
							<td>{{$purchase->amount}}</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
		<hr>
		<h4>Purchase Product List</h4>
		<div class="row table-responsive">
			<table class="table table-bordered table-hover">
				<thead class="table-light">
					<tr>
						<th>Product</th>
						<!-- <th>Size</th> -->
						<th>Quantity</th>
						<th>Unit Pirce</th>
						<th>Total Price</th>
					</tr>
				</thead>
				<tbody>
					@foreach($items as $item)
						<tr>
							<td>{{$item->products->name}}</td>
						
							<td>{{$item->quantity}}</td>
							<td>{{$item->unit_price}}</td>
							<td>{{$item->quantity * $item->unit_price}}.00</td>
						</tr>
					@endforeach
				</tbody>
			</table>
			<hr>
		</div>
	</div> 
@endsection