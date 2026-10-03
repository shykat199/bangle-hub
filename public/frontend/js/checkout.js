$(document).ready(function(){
    $(".via_card2").click(function(){
        $(".via_card_box").slideDown("fast");
        $(".paypal_box").slideUp("fast");
    });
    $("#debitorcredit").click(function(){
        $(".via_card_box").slideDown("fast");
        $(".paypal_box").slideUp("fast");
    });
    $(".via_paypal").click(function(){
        $(".via_card_box").slideUp("fast");
        $(".paypal_box").slideDown("fast");
    });
    $(".case_on_delivery2").click(function(){
        $(".via_card_box").slideUp("fast");
        $(".paypal_box").slideUp("fast");
    });
    $(".cuppon").click(function(){
        $(".cuppon-input").slideToggle("fast");
    })
});


// $(document).on('submit','form#checkout_form', function(e) {
//     e.preventDefault(); 
//     $('span.textdanger').text('');

//     let ele=$('form#checkout_form');
//     var url=ele.attr('action');
//     var method=ele.attr('method');
//     var formData = ele.serialize();

//     $.ajax({
//         type: method,
//         url: url,
//         data: formData,
//         success: function(res) {
//             if(res.success==true){
//                 toastr.success(res.msg);
//                 if(res.url){
//                     document.location.href = res.url;
//                 }else{
//                     window.location.reload();
//                 }
                
//             }else if(res.success==false){
//                 toastr.error(res.msg);
//             }
            
//         },
//         error:function (response){
//             $.each(response.responseJSON.errors,function(field_name,error){
//                 $(document).find('[name='+field_name+']').after('<span class="textdanger" style="color:red">' +error+ '</span>');
//             })
//         }
//     });
// });

$(document).on('click','button#coupon_apply', function(e) {
    e.preventDefault(); 
    $('span.textdanger').text('');

    var url='/coupon-discount';
    var code=$(document).find('input#coupon_code').val();
    var method='GET';
    
    if(code.length>0){
        $.ajax({
            type: method,
            url: url,
            data: {code},
            success: function(res) {
                if(res.success){
                    toastr.success(res.msg);
                    window.location.reload();
                }
                else if(res.success===false){
                    toastr.error(res.msg);
                }
                
            },
            error:function (response){
                $.each(response.responseJSON.errors,function(field_name,error){
                    $(document).find('[name='+field_name+']').after('<span class="textdanger" style="color:red">' +error+ '</span>');
                })
            }
        });
    }
    
});