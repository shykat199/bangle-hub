@extends('frontend.app')
@section('content')
<!-- cover start  -->
<section>
    <h2 class="serif text-center pb-5 mt-5"> Combo Offer </h2>
    <div class="container">
      <!-- Product filter Start  -->
      <!-- Product filter end  -->
      <!-- product List Start -->
      <div class="product_section">
          <div class="products row pt-4">
          @foreach($items as $item)
          @php
            $product=$item->product;
          	$data=getProductInfo($product);
          @endphp
          <div class="col-lg-3 col-md-4 col-12 font-sm pt-3">
            <a href="{{ route('front.products.show',[$product->id])}}" class="text-dark a-herf">
                <div style="height: 20rem; overflow: hidden;">
                    <img src="{{ getImage('products',$product->image)}}" alt="Product" class="col-12 pb-3">
                </div>
              </a>
            <span class="p-0">{{ $product->category_name}}</span><br>
            <a class="p-0 text-dark">{{ $product->name}}</a><br>
            <span class="p-0">
            	@if($data['discount_amount'] >0)
            	<del>${{ $product->sell_price}}</del>
            	@endif 
            ${{ $data['price']}}</span>
          </div>
          @endforeach
        <p>{!! urldecode(str_replace("/?","?",$items->appends(Request::all())->render())) !!}</p>
        </div>
      </div>
      <!-- product List End -->
    </div>
</section>
<!-- cover end  -->
<!-- recently veiw section  -->
<section class="mt-5 pt-5">
  <div class="container">
    <x-recent-view-product/>
  </div>
</section>
<!-- recently veiw section  -->
@endsection

@push('js')

@endpush