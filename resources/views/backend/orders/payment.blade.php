<div class="modal-dialog modal-lg">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel">Order Payments </h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form action="{{ route('admin.order_payments.update',[$order->id]) }}" method="POST" id="ajax_form">
      @csrf
      @method('PATCH')
      <div class="modal-body">
          
          <div class="row">
            <div class="col-md-4">
              <div class="well">
                <strong>Customer : </strong>{{ $order->first_name.' '.$order->last_name }}<br>
                <strong>Mobile : </strong>{{ $order->mobile }} <br/>
                 @if(!empty($order->payments[0]->tnx_id))
                	<strong>Trx id : </strong>{{ $order->payments[0]->tnx_id ?? '' }}
                 @endif
              </div>
            </div>
            <div class="col-md-4">
              <div class="well">
                <strong> Invoice No : </strong>{{ $order->invoice_no }}<br>
                <strong>Status: </strong>{{ $order->status }}
              </div>
            </div>
            <div class="col-md-4">
              <div class="well">
                <strong>Total Amount: </strong><span class="display_currency" data-currency_symbol="true">{{ $order->final_amount }}</span><br>
                <strong>Note : </strong> </strong><span>{{ $order->note }}</span><br>
              </div>
            </div>
          </div>

          <hr>
          <div class="row payment_row">
            <div class="col-md-4">
              <div class="form-group">
                <label>Amount :*</label>
                <div class="input-group">
                  <span class="input-group-addon">
                    <i class="fa fa-money"></i>
                  </span>
                  <input type="number" name="amount" class="form-control" step="any" value="{{ $due}}">
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                
                <label>Paid on:*</label>
                <div class="input-group">
                  <span class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                  </span>
                  <input type="date" name="date" class="form-control" value="{{ date('Y-m-d')}}">
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Pay Via:*</label>
                <div class="input-group">
                  <span class="input-group-addon">
                    <i class="fa fa-money"></i>
                  </span>
                  <select class="form-control" name="method">
                    @foreach($methods as $key=>$method)
                    <option value="{{ $key}}"> {{$method}} </option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            
            
            <div class="col-md-12">
              <div class="form-group">
                <label> Payment Note :</label>
                <textarea rows="3" name="note" class="form-control"></textarea>
              </div>
            </div>

          </div>


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary"> Update</button>
      </div>
    </form>
  </div>
</div>