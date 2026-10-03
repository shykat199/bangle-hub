@extends('frontend.app')

@push('css')
<style>	
about-content content-right.h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6, address, p, pre, blockquote, menu, ol, ul, table, hr{
	margin-bottom: 5px; 
  color:#000;
}
  
  h3, .h3 {
    font-size: 20px; 
}
  
</style>
@endpush

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
                            <li class="axil-breadcrumb-item active" aria-current="page">Terms Of Use</li>
                        </ul>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    <!-- End Breadcrumb Area  -->

    <!-- Start Privacy Policy Area  -->
    <div class="axil-privacy-area">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 offset-md-1">
                    <div class="axil-privacy-policy">
                        
                    	<h2 class="text-center m-1">{{ $page?$page->title:''}}</h2>
                        {!! $page?$page->body:'' !!}
                    
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Privacy Policy Area  -->

</main>



@endsection