<div class="recent-product-activation slick-layout-wrapper--15 axil-slick-arrow arrow-top-slide">
    @foreach($items as $product)
    <div class="slick-single-layout">
        @include('frontend.products.partials.product_section')
    </div>
    @endforeach
</div>