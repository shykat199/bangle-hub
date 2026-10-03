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
                                <li class="axil-breadcrumb-item active" aria-current="page">Offer</li>
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
                <div class="row p-4">
                    <div class="col-lg-12" id="product_data">                        
                        
                    </div>
                </div>
            </div>
            <!-- End .container -->
        </div>
        <!-- End Shop Area  -->

    </main>

@endsection

@push('js')
<script type="text/javascript">

  $(document).on('click', ".pagination a", function(e) {
      e.preventDefault();
      $('li').removeClass('active');
      $(this).parent('li').addClass('active');
      var page = $(this).attr('href').split('page=')[1];
      fetchData(page);
  });



  $(document).ready(function(){
    fetchData();
  });

  function fetchData(page=null){
    
    var q=$('input#search2').val();

    $.ajax({
       type:'GET',
       url:'{{ route("front.discountProduct")}}?page='+page,
       data:{},
       success:function(res){
            if (res.success==true) {
                $('div#product_data').html(res.html);
            }             
       }
    });

  }
</script>
@endpush