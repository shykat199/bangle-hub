@extends('frontend.app')
@section('content')
<main class="main-wrapper">
    <!-- Start Shop Area  -->
    <div class="axil-single-product-area axil-section-gap pb--0 bg-color-white">
        <div class="single-product-thumb mb--40">
            <div class="container">
                <div class="row">
                    <div class="col-lg-7 mb--40">
                        <div class="row">
                            <div class="col-lg-10 order-lg-2">
                                <div class="single-product-thumbnail-wrap zoom-gallery">
                                    <div class="single-product-thumbnail product-large-thumbnail-3 axil-product">
                                        <div class="thumbnail">
                                            <a href="{{ getImage('products', $product->image)}}" class="popup-zoom">
                                                <img src="{{ getImage('products', $product->image)}}" alt="{{ $product->name}} Images">
                                            </a>
                                        </div>

                                        @foreach($product->images as $im)
                                        <div class="thumbnail">
                                            <a href="{{ getImage('products', $im->image)}}" class="popup-zoom">
                                                <img src="{{ getImage('products', $im->image)}}" alt="{{ $product->name}} Images">
                                            </a>
                                        </div>
                                        @endforeach
                                        
                                    </div>
                                    <div class="label-block">
                                        <div class="product-badget">20% OFF</div>
                                    </div>
                                    <div class="product-quick-view position-view">
                                        <a href="{{ getImage('products', $product->image)}}" class="popup-zoom">
                                            <i class="far fa-search-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 order-lg-1">
                                <div class="product-small-thumb-3 small-thumb-wrapper">
                                    <div class="small-thumb-img">
                                        <img src="{{ getImage('products', $product->image)}}" alt="{{ $product->name}} image">
                                    </div>
                                    @foreach($product->images as $im)
                                    <div class="small-thumb-img">
                                        <img src="{{ getImage('products', $im->image)}}" alt="{{ $product->name}} image">
                                    </div>
                                    @endforeach
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5 mb--30">
                        <div class="single-product-content">
                            <div class="inner">
                                <h2 class="product-title">{{ $product->name}}</h2>
                                <span class="price-amount"> {{ priceFormate($product->sell_price)}}</span>
                                <h6>Catgeory : {{ $product->category?$product->category->name:''}}</h6>
                                <h6>Brand : {{ $product->brand?$product->brand->name:''}}</h6>
                                <div class="product-rating">
                                    <div class="star-rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                    <div class="review-link">
                                        <a href="#">(<span>2</span> customer reviews)</a>
                                    </div>
                                </div>
                                <ul class="product-meta">
                                    {!! $product->feature !!}
                                </ul>

                                <form method="POST" action="{{ route('front.carts.store')}}" id="cart_form">
                                    @csrf

                                    @if($product->type=='single')
                                    <input type="hidden" name="variation_id" value="{{ $product->variation->id}}">
                                    @else
                                    <!-- <div class="product-variations-wrapper">
                                        <div class="product-variation product-size-variation">
                                            <h6 class="title">Color:</h6>
                                            <ul class="range-variant">
                                                @php
                                                    $colors=[];
                                                @endphp
                                                @foreach($product->variations as $v)
                                                    @if(!in_array($v->color_id,$colors))
                                                    <li class="color"> {{ $v->color->name}} </li>
                                                    @endif

                                                    @php
                                                    if(!in_array($v->color_id,$colors)){
                                                        array_push($colors,$v->color_id);
                                                        
                                                    }
                                                    @endphp
                                                @endforeach
                                            </ul>
                                        </div>

                                        <div class="product-variation product-size-variation">
                                            <h6 class="title">Size:</h6>
                                            <ul class="range-variant">
                                                @php
                                                    $sizes=[];
                                                @endphp
                                                @foreach($product->variations as $v)
                                                    @if(!in_array($v->size_id,$sizes))
                                                    <li class="size"> {{ $v->size->title}} </li>
                                                    @endif

                                                    @php
                                                    if(!in_array($v->size_id,$sizes)){
                                                        array_push($sizes,$v->size_id);
                                                        
                                                    }
                                                    @endphp

                                                @endforeach
                                            </ul>
                                        </div>
                                        
                                    </div> -->

                                    @endif

                                

                                    <div class="product-action-wrapper d-flex-center">
                                        <!-- Start Quentity Action  -->
                                        <input type="hidden" name="id" value="{{ $product->id}}">
                                        <div class="pro-qty item-quantity">
                                            <span class="dec qtybtn">-</span>
                                            <input type="number" class="quantity-input" value="1" name="quantity" />
                                            <span class="inc qtybtn">+</span>
                                        </div>
                                        
                                        <!-- End Quentity Action  -->
                                        <!-- Start Product Action  -->
                                        <ul class="product-action d-flex-center mb--0">
                                            <li class="add-to-cart">
                                                <button class="axil-btn btn-bg-primary">Add to Cart</button>
                                            </li>
                                            <li class="wishlist">
                                                <button class="axil-btn wishlist-btn"><i class="far fa-heart"></i></button>
                                            </li>
                                        </ul>
                                        <!-- End Product Action  -->
                                    </div>
                                </form>
                                <!-- End Product Action Wrapper  -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End .single-product-thumb -->

        <div class="woocommerce-tabs wc-tabs-wrapper bg-vista-white">
            <div class="container">
                <ul class="nav tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="active" id="description-tab" data-bs-toggle="tab" href="#description" role="tab" aria-controls="description" aria-selected="true">Description</a>
                    </li>
                    <li class="nav-item " role="presentation">
                        <a id="additional-info-tab" data-bs-toggle="tab" href="#additional-info" role="tab" aria-controls="additional-info" aria-selected="false">Additional Information</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a id="reviews-tab" data-bs-toggle="tab" href="#reviews" role="tab" aria-controls="reviews" aria-selected="false">Reviews</a>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
                        <div class="product-desc-wrapper">
                            <div class="row">
                                <div class="col-lg-12 mb--30">
                                    <div class="single-desc">
                                        <h5 class="title">Specifications:</h5>
                                        {!! $product->body !!}
                                    </div>
                                </div>
                                
                            </div>
                            <!-- End .row -->
                            
                            <!-- End .row -->
                        </div>
                        <!-- End .product-desc-wrapper -->
                    </div>
                    <div class="tab-pane fade" id="additional-info" role="tabpanel" aria-labelledby="additional-info-tab">
                        <div class="product-additional-info">
                            <div class="col-lg-12">
                                {!! $product->body !!}
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                        <div class="reviews-wrapper">
                            <div class="row">
                                <div class="col-lg-6 mb--40">
                                    <div class="axil-comment-area pro-desc-commnet-area">
                                        <h5 class="title">01 Review for this product</h5>
                                        <ul class="comment-list">
                                            <!-- Start Single Comment  -->
                                            <li class="comment">
                                                <div class="comment-body">
                                                    <div class="single-comment">
                                                        <div class="comment-img">
                                                            <img src="{{ asset('frontend/images/blog/author-image-4.png')}}" alt="Author Images">
                                                        </div>
                                                        <div class="comment-inner">
                                                            <h6 class="commenter">
                                                                <a class="hover-flip-item-wrapper" href="#">
                                                                    <span class="hover-flip-item">
                                                                        <span data-text="Cameron Williamson">Eleanor Pena</span>
                                                                    </span>
                                                                </a>
                                                                <span class="commenter-rating ratiing-four-star">
                                                                    <a href="#"><i class="fas fa-star"></i></a>
                                                                    <a href="#"><i class="fas fa-star"></i></a>
                                                                    <a href="#"><i class="fas fa-star"></i></a>
                                                                    <a href="#"><i class="fas fa-star"></i></a>
                                                                    <a href="#"><i class="fas fa-star empty-rating"></i></a>
                                                                </span>
                                                            </h6>
                                                            <div class="comment-text">
                                                                <p>“We’ve created a full-stack structure for our working workflow processes, were from the funny the century initial all the made, have spare to negatives. ” </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <!-- End Single Comment  -->
                                        </ul>
                                    </div>
                                    <!-- End .axil-commnet-area -->
                                </div>
                                <!-- End .col -->
                                <div class="col-lg-6 mb--40">
                                    <!-- Start Comment Respond  -->
                                    <div class="comment-respond pro-des-commend-respond mt--0">
                                        <h5 class="title mb--30">Add a Review</h5>
                                        <p>Your email address will not be published. Required fields are marked *</p>
                                        <div class="rating-wrapper d-flex-center mb--40">
                                            Your Rating <span class="require">*</span>
                                            <div class="reating-inner ml--20">
                                                <a href="#"><i class="fal fa-star"></i></a>
                                                <a href="#"><i class="fal fa-star"></i></a>
                                                <a href="#"><i class="fal fa-star"></i></a>
                                                <a href="#"><i class="fal fa-star"></i></a>
                                                <a href="#"><i class="fal fa-star"></i></a>
                                            </div>
                                        </div>

                                        <form action="#">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label>Other Notes (optional)</label>
                                                        <textarea name="message" placeholder="Your Comment"></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-12">
                                                    <div class="form-group">
                                                        <label>Name <span class="require">*</span></label>
                                                        <input id="name" type="text">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-12">
                                                    <div class="form-group">
                                                        <label>Email <span class="require">*</span> </label>
                                                        <input id="email" type="email">
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="form-submit">
                                                        <button type="submit" id="submit" class="axil-btn btn-bg-primary w-auto">Submit Comment</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <!-- End Comment Respond  -->
                                </div>
                                <!-- End .col -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- woocommerce-tabs -->

    </div>
    <!-- End Shop Area  -->

    <!-- Start Recently Viewed Product Area  -->
    <div class="axil-product-area bg-color-white axil-section-gap pb--50 pb_sm--30">
        <div class="container">
            <div class="section-title-wrapper">
                <span class="title-highlighter highlighter-primary"><i class="far fa-shopping-basket"></i> Your Recently</span>
                <h2 class="title">Viewed Items</h2>
            </div>
            <x-recent-view-product/>
        </div>
    </div>
    <!-- End Recently Viewed Product Area  -->
</main>
@endsection

@push('js')
<script type="text/javascript">
    
    $('li.size').click(function(){

        $('li.size').removeClass('active');
   
        $(this).addClass('active');
        
    });


    $('li.color').click(function(){

        $('li.color').removeClass('active');
        $(this).addClass('active');
    
    });
</script>
@endpush
