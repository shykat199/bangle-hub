@extends('frontend.app')
@section('content')


<main class="main-wrapper">
    <!-- Start Breadcrumb Area  -->
    <div class="axil-breadcrumb-area">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-12">
                    <div class="inner">
                        <ul class="axil-breadcrumb">
                            <li class="axil-breadcrumb-item"><a href="{{ route('front.home')}}">Home</a></li>
                            <li class="separator"></li>
                            <li class="axil-breadcrumb-item active" aria-current="page">Brand</li>
                        </ul>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    <!-- End Breadcrumb Area  -->
    
    <div class="container-fluid">
        <div class="cat-blocks-container">
            <div class="row">
                
                @foreach($items as $item)
                <div class="col-6 col-sm-4 col-lg-2">
                    <a title="{{$item->name}}" href="{{ route('front.products.index')}}?brand_id={{ $item->id}}" class="cat-block">
                        <figure>
                            <span>
                                <img src="{{ getImage('types', $item->image)}}" />
                            </span>
                        </figure>
                    </a>
                </div>
                @endforeach
                <!-- End .col-sm-4 col-lg-2 -->
                
            </div>
            <!-- End .row -->
        </div>
        <!-- End .cat-blocks-container -->
    </div>
</main>

@endsection