$(document).on('click','.btn_modal', function(e){
    e.preventDefault();
    var url = $(this).attr('href');

    console.log(url);
    $.ajax({
       type:'GET',
       url:url,
       data:{},
       success:function(res){
          $('div#common_modal').html(res).modal('show');
       }
    });
});



$(document).on('submit','form#ajax_form', function(e) {
    e.preventDefault(); 
    $('span.textdanger').text('');
    var url=$(this).attr('action');
    var method=$(this).attr('method');
    var formData = new FormData($(this)[0]);
    $.ajax({
        type: method,
        url: url,
        data: formData,
        async: false,
        processData: false,
        contentType: false,
        success: function(res) {
            
            if(res.status==true){
                toastr.success(res.msg);
                if(res.url){
                    document.location.href = res.url;
                }else{
                    window.location.reload();
                }
            }else if(res.status==false){
                toastr.error(res.msg);
            }
            
        },
        error:function (response){
            var errs = response.responseJSON && response.responseJSON.errors;
            if (errs) {
                $.each(errs,function(field_name,error){
                    $(document).find('[name='+field_name+']').after('<span style="color:red" class="textdanger">' +error+ '</span>')
                    toastr.error(error);
                })
            } else {
                // 500 / 403 / 419 etc: previously failed silently, now shows the reason
                var msg = (response.responseJSON && response.responseJSON.message) || response.statusText || 'Something went wrong';
                toastr.error('Error ' + response.status + ': ' + msg);
                console.error(response.responseText);
            }
        }
    });
});

// ajax request for delete data
