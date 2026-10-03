@extends('backend.app')
@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush
@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active">Combo Offer Create</li>
                </ol>
            </div>
            <h4 class="page-title">Combo Offer Create</h4>
        </div>
    </div>
</div>   
<!-- end page title --> 

<div class="row">
    <div class="col-12 p-5">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.combos.update',[$item->id])}}" id="ajax_form">
                    @csrf
                    @method('PATCH')
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label  class="form-label">Select Combo Offer</label>
                                <select class="form-control" name="product_id">
                                    <option hidden value=""> Select One</option>
                                    
                                    @foreach($products as $product)
                                    <option value="{{$product->id}}" {{$item->product_id==$product->id ?'selected':''}}> {{$product->name}} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-lg-12">
                            
                            <div class="table-responsive">
                                <table class="table table-centered table-nowrap mb-0">
                                    <thead>
                                        <tr>
                                            <th>Produt</th>
                                            <th>Quantity</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    
                                   <tbody>
                                       
                                       @forelse($item->products as $p)
                                       
                                       <tr>
                                            <td>
                                                <select class="form-control select2" name="products[]">
                                                    <option hidden value=""> Select One</option>
                                                    
                                                    @foreach($items as $item_product)
                                                    @if($item_product->product && $item_product->size)
                                                    <option value="{{$item_product->id}}" {{ ($p->product_id==$item_product->product_id && $p->size_id==$item_product->size_id) ?'selected':''}}> {{$item_product->product->name}} ({{$item_product->size->title}})</option>
                                                    @endif
                                                    @endforeach
                                                </select>
                                            </td>
                                            
                                            <td>
                                                <input type="number" class="form-control" value="{{ $p->quantity}}" name="quantity[]">
                                            </td>
                                            
                                            <td>
                                                <a class="btn btn-success btn-sm add"> <i class="mdi mdi-plus"></i> </a>
                                                <a class="btn btn-danger btn-sm remove"> <i class="mdi mdi-delete"></i> </a>
                                            </td>
                                        </tr>
                                        
                                       @empty
                                       
                                       <tr>
                                            <td>
                                                <select class="form-control select2" name="products[]">
                                                    <option hidden value=""> Select One</option>
                                                    
                                                    @foreach($items as $item)
                                                    @if($item->product && $item->size)
                                                    <option value="{{$item->id}}"> {{$item->product->name}} ({{$item->size->title}})</option>
                                                    @endif
                                                    @endforeach
                                                </select>
                                            </td>
                                            
                                            <td>
                                                <input type="number" class="form-control" value="1" name="quantity[]">
                                            </td>
                                            
                                            <td>
                                                <a class="btn btn-success btn-sm add"> <i class="mdi mdi-plus"></i> </a>
                                                <a class="btn btn-danger btn-sm remove"> <i class="mdi mdi-delete"></i> </a>
                                            </td>
                                        </tr>
                                        
                                       @endforelse
                                       
                                        
                                    
                                   </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="mt-3">
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function(){
        
        $(document).on('click','a.add', function(){
            
            let row =`<tr>
                        <td>
                            <select class="form-control select2" name="products[]">
                                <option hidden value=""> Select One</option>
                                
                                @foreach($items as $item)
                                @if($item->product && $item->size)
                                <option value="{{$item->id}}"> {{$item->product->name}} ({{$item->size->title}})</option>
                                @endif
                                @endforeach
                            </select>
                        </td>
                        
                        <td>
                            <input type="number" class="form-control" value="1" name="quantity[]">
                        </td>
                        
                        <td>
                            <a class="btn btn-success btn-sm add"> <i class="mdi mdi-plus"></i> </a>
                            <a class="btn btn-danger btn-sm remove"> <i class="mdi mdi-delete"></i> </a>
                        </td>
                    </tr>`;
            $('.table tbody').append(row);
        });
        
        $(document).on('click','a.remove', function(){
            var whichtr = $(this).closest("tr");
            whichtr.remove();      
        });
    });
</script>


@endpush