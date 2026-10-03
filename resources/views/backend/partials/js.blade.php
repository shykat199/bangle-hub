<script src="{{ asset('backend/js/vendor.min.js')}}"></script>
<script src="{{ asset('backend/js/app.min.js')}}"></script>
<script src="{{ asset('backend/js/ajax.js') }}?v={{ @filemtime(public_path('backend/js/ajax.js')) }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

<script type="text/javascript">
// CSRF Token Setup for AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// ==========================================
// 🗑️ GLOBAL DELETE AJAX FIX (With SweetAlert)
// ==========================================
$(document).off('click', 'a.delete, .delete-order-btn').on('click', 'a.delete, .delete-order-btn', function(e) {
    var btn = $(this);
    e.preventDefault(); 
    e.stopImmediatePropagation(); // এটি ajax.js বা অন্য স্ক্রিপ্টকে ডাবল ফায়ার হওয়া থেকে আটকাবে

    swal({
      title: "Are you sure?",
      text: "You want to delete this order!",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33", // ডিলিটের জন্য লাল রঙ
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel plz!",
      closeOnConfirm: true,
      closeOnCancel: true
    },
    function(isConfirm){
      if (isConfirm) {
        var url = btn.attr('href');

        $.ajax({
            type: 'DELETE', // 🔴 FIX: এখানে DELETE মেথড দেওয়া হয়েছে
            url: url,
            success: function(res) {
                if(res.status == true || res.status == 1) {
                    toastr.success(res.msg);
                    
                    // টেবিলের রো (Row) হলে পেজ রিলোড না করে স্মুথলি মুছে ফেলবে
                    let tr = btn.closest('tr');
                    if(tr.length) {
                        tr.fadeOut(300, function() { $(this).remove(); });
                    }

                    // রাইট প্যানেল বা লিস্ট আপডেট করার ফাংশন থাকলে কল করবে
                    if(typeof closeRightPanel === 'function') closeRightPanel(); 
                    if(typeof getOrderList === 'function') getOrderList();
                    
                    // অন্য কোনো পেজের জন্য রিডাইরেক্ট URL থাকলে সেখানে যাবে
                    if(res.url && !tr.length){
                        document.location.href = res.url;
                    }
                } else {
                    toastr.error(res.msg || "Failed to delete!");
                }
            },
            error: function (response){
                toastr.error("Server Error! Please try again.");
            }
        });
      }
    });
});
  
// ==========================================
// 🚀 OTHER SCRIPTS
// ==========================================
$(document).ready(function() {
  $('.select2').select2();
});

$(document).on('change', "#area_select", function(e) {
  let area_id = $(this).val();
  let area_name = $("#area_select option:selected").text();
  $("#area_id").val(area_id);
  $("#area_name").val(area_name);
});

</script>

@stack('js')