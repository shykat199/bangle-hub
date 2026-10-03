@forelse($items as $product)
<div class="col-xl-3 col-md-3 col-sm-6 col-6 mb--30">
    @include('frontend.products.partials.product_section')
</div>
@empty
<div class="col-lg-3 col-md-3 col-sm-6 col-6 mb--30">
    <div class="alert alert-warning"> No Products Found !!</div>
</div>
@endforelse

<div class="text-center pt--20">
    <p>{!! urldecode(str_replace("/?","?",$items->appends(Request::all())->render())) !!}</p>
</div>