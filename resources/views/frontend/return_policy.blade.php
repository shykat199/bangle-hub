@extends('frontend.app')
@section('content')


<style>
    .axil-privacy-policy p {
    
    font-weight: 300;
}
.axil-privacy-policy .title {
    
}
</style>
<main class="main-wrapper">
        <!-- Start Breadcrumb Area  -->
        <div class="axil-breadcrumb-area">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 col-md-8">
                        <div class="inner">
                            <ul class="axil-breadcrumb">
                                <li class="axil-breadcrumb-item"><a href="index.html">Home</a></li>
                                <li class="separator"></li>
                                <li class="axil-breadcrumb-item active" aria-current="page">Return policy</li>
                            </ul>
                            <h1 class="title">Return policy</h1>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-4">
                        <div class="inner">
                            <div class="bradcrumb-thumb">
                                <img src="assets/images/product/product-45.png" alt="Image">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Breadcrumb Area  --> 

        <!-- Start Contact Area  -->
        <div class="axil-privacy-area axil-section-gap">
            <div class="container">
                <div class="row">
                    <div class="col-lg-10">
                        <div class="axil-privacy-policy">
                            <h2 class="text-center m-1">{{ $page?$page->title:''}}</h2>
                            {!! $page?$page->body:'' !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Contact Area  -->
    </main>

@endsection