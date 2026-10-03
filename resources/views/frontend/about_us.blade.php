@extends('frontend.app')
@push('css')
<style>	

  about-content content-right.h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6, address, p, pre, blockquote, menu, ol, ul, table, hr{
    margin-bottom: 9px !important;
  }
  
  	.axil-about-area .about-content.content-right {
 	   padding-left: 0px !important; 
      
	}
  
    .axil-about-area .about-content p {
      font-size:16px !important;
      color:#000
  	}
</style>
@endpush
@section('content')
<main class="main-wrapper">
        <!-- Start Breadcrumb Area  -->
        <div class="axil-breadcrumb-area">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="inner">
                            <ul class="axil-breadcrumb">
                                <li class="axil-breadcrumb-item"><a href="{{ route('front.home')}}">Home</a></li>
                                <li class="separator"></li>
                                <li class="axil-breadcrumb-item active" aria-current="page">{{ $page?$page->title:''}} </li>
                            </ul>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Breadcrumb Area  -->

        <!-- Start About Area  -->
        <div class="axil-about-area about-style-1" style="background-color:#f8f8f8;">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-xl-12 col-lg-12">
                        <div class="about-content content-right pt-3">
                            <h2 class="text-center m-1">{{ $page?$page->title:''}}</h2>
                            {!! $page?$page->body:'' !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End About Area  -->
    </main>
@endsection
  