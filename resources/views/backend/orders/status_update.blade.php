<div class="modal-dialog">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel">Order Status Update invoice #{{$item->invoice_no}}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form action="{{ route('admin.orderStatusUPdate',[$item->id]) }}" method="POST" id="ajax_form">
      @csrf
      <div class="modal-body">
          
          <div class="div-group">
              <label>Status</label>
              <select class="form-control" name="status">
                   @foreach($status as $key=>$s)
                   @if($key === '') @continue @endif
                   <option value="{{$key}}" {{ strcasecmp($key, trim($item->status ?? '')) === 0 ?'selected':''}}> {{ $s}} </option>
                   @endforeach
              </select>
          </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary"> Update</button>
      </div>
    </form>
  </div>
</div>