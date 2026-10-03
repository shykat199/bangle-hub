@extends('backend.app')
@section('content')
<div class="row mt-3">
    <div class="col-12">
           <div class="card">
           <div class="card-body">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active">Product List</li>
                </ol>
            </div>
            <h4 class="page-title">Product Details</h4>
            <div class="row">
                <div class="col-md-6"><strong>Product :</strong> {{$product->name}}</div><br>
                <div class="col-md-6"><strong>Sku :</strong> {{$product->sku}}</div><br>
                <div class="col-md-6"><strong>Category :</strong> {{ $product->category?$product->category->name:''}}</div><br>
                <div class="col-md-6"><strong>Brand :</strong> {{ $product->brand?$product->brand->name:''}}</div><br>
                <div class="col-md-6"><strong>Type :</strong> {{ $product->type}}</div><br>
            </div>
            <div class="row">
                <div class="col-md-6"><strong>Purchase  Price :</strong> {{$product->purchase_price}}</div>
                <div class="col-md-6"><strong>Selling Price :</strong> {{$product->sell_price}}</div>
                <div class="col-md-6"><strong>Color :</strong> {{$product->color}}</div>
            </div>
            <hr>
             <div class="row mb-3">
                @if($product->image)
                <div class="col-md-3">
                    <strong>Product Image :</strong> <br>
                    <img src="{{ getImage('products',$product->image)}}" height=200 width=200>
                </div>
                @endif
                

                @foreach ($product->images as $key => $image) 
                <div class="col-md-3">
                    <strong><strong>Multi Image : <a href="{{ route('admin.deleteImage',[$image->id])}}" class="btn btn-sm btn-danger" onclick="return confirm(' you want to delete?');">Delete</a></strong><br> 
                    <img src="{{ getImage('products',$image->image)}}" height="200" width="200">
                </div>
                @endforeach
            </div>
             @if($product->description) 
             
            
                <strong>Product Description : </strong>{{$product->description}}
             </div>
             @endif
            @if($product->body)
             <div>
                <strong>Body : </strong>{!!$product->body!!}
             </div>
            @endif 

            @if($product->feature)
             <div>
                <strong>Feature : </strong>{!!$product->feature!!}
             </div>
            @endif 
            @if($product->discount_type)
             <div>
                <strong>Discount Type : </strong>{{$product->discount_type}}
             </div>
            @endif
            @if($product->dicount_amount !=0)
             <div>
                <strong>Discount Amount : </strong>{{$product->dicount_amount}}
             </div>
            @endif
            @if($product->after_discount !=0)
             <div>
                <strong>After Discount : </strong>{{$product->after_discount}}
             </div>
            @endif 
        </div>

    </div>
</div>  
</div>
</div> 
<hr>
<!-- end page title --> 

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-xl-8">
                                                  
                    </div>
                    <div class="col-xl-4">
                        <div class="text-xl-end mt-xl-0 mt-2"> 
                        </div>
                    </div><!-- end col-->
                </div>
                <h4 class="text-center">Product-Size Wise Available Stock Quantity</h4><br>
                <div class="table-responsive">
                    <table class="table table-centered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Sl</th>
                                <th>Size</th>
                                <th>Color</th>
                                <th>Stock Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->variations as $key=>$v)
                                <tr>
                                    <td>{{$key+1}}</td>
                                    {{-- single প্রোডাক্টে size_id/color_id দুটোই NULL থাকে, আর
                                         variable-এও শুধু size বা শুধু color দেওয়া যায় — তখন
                                         রিলেশন null আসে বলে পুরো পেজ 500 হয়ে যেত। --}}
                                    <td>{{ $v->size?->title ?? '—' }}</td>
                                    <td>{{ $v->color?->name ?? '—' }}</td>
                                    <td>{{$v->stocks->sum('quantity')}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div> <!-- end col -->
</div> <!-- end row -->
@endsection 