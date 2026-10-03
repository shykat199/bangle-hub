<div class="modal-dialog modal-sm">
  <div class="modal-content shadow" style="border-radius: 8px; border: none;">
    <div class="modal-header bg-light py-2 px-3 border-bottom">
      <h6 class="modal-title fw-bold text-dark mb-0" style="font-size: 14px;">
          <i class="mdi mdi-swap-horizontal"></i> Transfer Orders
      </h6>
      <button type="button" class="btn-close" style="font-size: 10px;" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    
    <form action="{{ route('admin.assignUserStore') }}" method="POST" id="order_assign_form">
      @csrf
      <div class="modal-body p-2">
          
          <div class="mb-2 pb-2 border-bottom">
              <label class="fw-bold mb-1 text-primary" style="font-size: 12px;">কাকে দিবেন? (To Worker)</label>
              <select class="form-select form-select-sm border-primary fw-bold" name="assign_user_id" id="assign_user_id" style="font-size: 12px;">
                  <option value="">-- Select Destination --</option>
                  @foreach($users as $user)
                      <option value="{{ $user->id }}">{{ $user->username }}</option>
                  @endforeach
              </select>
          </div>

          {{-- ইনফো বক্স --}}
          <div id="checked_orders_info" class="alert alert-info border-0 shadow-sm py-1 px-2 mb-2" style="display: none; border-radius: 4px; font-size: 11px;">
              <i class="mdi mdi-information-outline me-1"></i>
              আপনি <strong><span id="checked_count">0</span></strong> টি অর্ডার সিলেক্ট করেছেন।
          </div>

          {{-- ম্যানুয়ালি ট্রান্সফার বক্স --}}
          <div id="manual_assign_box" class="p-2" style="background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
              
              <div class="mb-2">
                  <label class="fw-bold mb-1 text-secondary" style="font-size: 11px;">কার থেকে নিবেন? (From)</label>
                  <select name="from_user_id" id="from_user_id" class="form-select form-select-sm border-secondary" style="font-size: 12px;">
                      <option value="unassigned">নতুন/ফাঁকা (Unassigned)</option>
                      <option value="all">সবার থেকে (All Workers)</option>
                      @foreach($users as $user)
                          <option value="{{ $user->id }}">{{ $user->username }}</option>
                      @endforeach
                  </select>
              </div>

              <div class="row gx-2">
                  <div class="col-7 mb-1">
                      <label class="fw-bold mb-1" style="font-size: 11px;">স্ট্যাটাস?</label>
                      <select name="assign_status" id="assign_status" class="form-select form-select-sm" style="font-size: 12px;">
                          <option value="">যেকোনো (All)</option>
                          @if(function_exists('getOrderStatus'))
                              @foreach(getOrderStatus() as $key => $val)
                                  @if($key != '')
                                      <option value="{{ $key }}">{{ $val }}</option>
                                  @endif
                              @endforeach
                          @endif
                      </select>
                  </div>

                  <div class="col-5 mb-1">
                      <label class="fw-bold mb-1 text-danger" style="font-size: 11px;">কয়টি?</label>
                      <input type="number" name="assign_qty" id="assign_qty" class="form-control form-control-sm fw-bold border-danger" placeholder="সব (All)" min="1" style="font-size: 12px;">
                  </div>
              </div>
          </div>

      </div>
      <div class="modal-footer bg-light py-1 px-2 border-top">
        <button type="button" class="btn btn-secondary btn-sm" style="font-size: 11px; padding: 4px 10px;" data-bs-dismiss="modal">Close</button>
        <button type="submit" id="submit_transfer_btn" class="btn btn-primary btn-sm fw-bold" style="font-size: 11px; padding: 4px 15px;">Transfer Now</button>
      </div>
    </form>
  </div>
</div>

<script>
$(document).ready(function(){
    let checked_ids = [];
    
    // চেকবক্স থেকে ভ্যালু নিয়ে হিডেন ইনপুট তৈরি করা
    $('.order_checkbox:checked').each(function(){
        checked_ids.push($(this).val());
        $('#order_assign_form').append('<input type="hidden" class="hidden_order_id" name="order_ids[]" value="'+$(this).val()+'">');
    });

    // যদি চেকবক্স সিলেক্ট করা থাকে
    if(checked_ids.length > 0) {
        $('#checked_orders_info').show();
        $('#checked_count').text(checked_ids.length);
    }

    // ফিল্টার ইনপুটে পরিবর্তন হলে লজিক আপডেট
    $('#assign_qty, #from_user_id, #assign_status').on('input change', function() {
        let qty = $('#assign_qty').val();
        let fromUser = $('#from_user_id').val();
        let status = $('#assign_status').val();
        
        if(qty > 0 || fromUser !== 'unassigned' || status !== '') {
            $('.hidden_order_id').prop('disabled', true);
            $('#checked_orders_info')
                .removeClass('alert-info text-info')
                .addClass('alert-warning text-dark')
                .html('<i class="mdi mdi-alert me-1"></i> Bulk অপশনে পেজের টিক কাজ করবে না।');
        } else {
            $('.hidden_order_id').prop('disabled', false);
            if(checked_ids.length > 0) {
                $('#checked_orders_info')
                    .removeClass('alert-warning text-dark')
                    .addClass('alert-info text-info')
                    .html('<i class="mdi mdi-information-outline me-1"></i> আপনি <strong>' + checked_ids.length + '</strong> টি অর্ডার সিলেক্ট করেছেন।');
            }
        }
    });

    // বিঃদ্রঃ ফর্ম সাবমিটের (AJAX) লজিকটি index.blade.php তে লেখা আছে।
});
</script>