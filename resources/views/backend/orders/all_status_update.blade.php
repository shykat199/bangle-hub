<div class="modal-dialog">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel">Multi Status Update </h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form action="{{ route('admin.multuOrderStatusUpdate')}}" method="POST" id="order_status_update_form">
      @csrf
      <div class="modal-body">
          
          <div class="div-group">
              <label>Status</label>
              <select class="form-control" name="status" id="multi_status" required>
                   @foreach($status as $key=>$s)
                   @if($key === '') @continue @endif
                   <option value="{{$key}}"> {{ $s}} </option>
                   @endforeach
              </select>
          </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary"> Submit</button>
      </div>
    </form>
  </div>
</div>