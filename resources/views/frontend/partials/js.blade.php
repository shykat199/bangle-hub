<script src=" {{ asset('frontend/js/vendor/jquery.js')}}"></script>
<script src=" {{ asset('frontend/js/vendor/modernizr.min.js')}}"></script>
<!-- Bootstrap JS -->
<script src=" {{ asset('frontend/js/vendor/popper.min.js')}}"></script>
<script src=" {{ asset('frontend/js/vendor/bootstrap.min.js')}}"></script>
<script src=" {{ asset('frontend/js/vendor/slick.min.js')}}"></script>
<script src=" {{ asset('frontend/js/vendor/js.cookie.js')}}"></script>
<script src=" {{ asset('frontend/js/vendor/jquery-ui.min.js')}}"></script>
<script src=" {{ asset('frontend/js/vendor/jquery.countdown.min.js')}}"></script>
<script src=" {{ asset('frontend/js/vendor/sal.js')}}"></script>
<script src=" {{ asset('frontend/js/vendor/jquery.magnific-popup.min.js')}}"></script>
<script src=" {{ asset('frontend/js/vendor/imagesloaded.pkgd.min.js')}}"></script>
<script src=" {{ asset('frontend/js/vendor/isotope.pkgd.min.js')}}"></script>
<script src=" {{ asset('frontend/js/vendor/counterup.js')}}"></script>
<script src=" {{ asset('frontend/js/vendor/waypoints.min.js')}}"></script>

<!-- Main JS -->
<script src=" {{ asset('frontend/js/main.js')}}"></script>
<script src=" {{ asset('frontend/js/cart.js')}}"></script>
<script src=" {{ asset('frontend/js/signin.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>


<script type="text/javascript">
	$.ajaxSetup({
	    headers: {
	        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	    }
	});

    function calculate_total()
    {

      // Get selected delivery charge safely
      let charge = Number($("#selectCourier option:selected").data("charge")) || 0;
      let prev_subtotal = Number($(document).find("input#subtotal").val());
      let final_total = prev_subtotal + charge;
      $(document).find("span.delivery_charge").text(""+charge);
      $(document).find("span.total").text(""+final_total);
      $(document).find("input#amount").val(final_total); 
    }

    $(document).on('click','span.qtybtn', function() {
        var $button = $(this);

        var oldValue = $button.parent().find('input').val();
        if ($button.hasClass('inc')) {
            var newVal = parseFloat(oldValue) + 1;
        } else {
            if (oldValue > 0) {
                var newVal = parseFloat(oldValue) - 1;
            } else {
                newVal = 1;
            }
        }
        $button.parent().find('input').val(newVal);

        let url=$button.closest('div').attr('data-href');
        let segment=$button.closest('div').attr('data-segment');
	    if (typeof url !== "undefined") {
		    $.ajax({
		        url: url,
		        method: "GET",
		        data: {quantity:newVal,segment:segment},
		        success: function (res) {
		            if (res.success) {
		                toastr.success(res.msg);
		                $('div#cart-dropdown').html(res.html);
		                $(document).find('div.orderDetails').html(res.html2);
		                $(document).find('div.cart_other_details').html(res.html3);
		                // No reload here: the AJAX above already refreshed the cart sidebar.
		            }else{
		                toastr.error(res.msg);
		                $button.parent().find('input').val(oldValue);
		            }
                  	calculate_total();
		        }
		    });
		}

    });
    
    
    toastr.options = {
      "debug": false,
      "positionClass": "toast-bottom-right",
      "onclick": null,
      "fadeIn": 300,
      "fadeOut": 1000,
      "timeOut": 5000,
      "extendedTimeOut": 1000
    }


</script>

@stack('js')