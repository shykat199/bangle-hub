@extends('frontend.app')
@section('content')
<main class="main-wrapper">
        <!-- Start Breadcrumb Area  -->
        <div class="axil-breadcrumb-area">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-lg-6 col-md-8">
                        <div class="inner">
                            <ul class="axil-breadcrumb">
                                <li class="axil-breadcrumb-item"><a href="index.php">Home</a></li>
                                <li class="separator"></li>
                                <li class="axil-breadcrumb-item active" aria-current="page">Free Shipping</li>
                            </ul>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
        <!-- End Breadcrumb Area  -->

        <!-- Start Shop Area  -->
        <div class="axil-shop-area axil-section-gap bg-color-white">
            <div class="container-fluid">
                <div class="row p-2">
                    <style>
                        @media only screen and (max-width: 767px){
                            .products-block .row>[class*=col] {
                                padding-left: 5px;
                                padding-right: 5px;
                            }
                            .axil-shop-area.axil-section-gap.bg-color-white {
                                padding: 10px 0;
                            }
                        }
                    </style>
                    <div class="col-lg-12 products-block">
                        
                        <!-- End .row -->
                        <div class="row row--15" id="product_data">
                            @forelse($items as $product)
                                <div class="col-xl-2 col-md-2 col-sm-6 col-6 mb--30">
                                    @include('frontend.products.partials.product_section')
                                </div>
                                @empty
                                <div class="col-lg-2 col-md-2 col-sm-6 col-6 mb--30">
                                    <div class="alert alert-warning"> No Products Found !!</div>
                                </div>
                                @endforelse
                                
                                
                        </div>
                    </div>
                </div>
            </div>
            <!-- End .container -->
        </div>
        <!-- End Shop Area  -->

    </main>

@endsection
