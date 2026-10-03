<div class="modal-dialog">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel">Courier Update</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form action="{{ route('admin.couriers.update',[$item->id]) }}" method="POST" id="ajax_form">
      @csrf
      @method('PATCH')
      <div class="modal-body">
          <div class="col-12">
              <div class="form-group">
                  <label>Courier Name</label>
                  <input type="text" name="name" value="{{ $item->name}}" class="form-control">
              </div>
          </div>
          <div class="col-12">
              <div class="form-group">
                  <label>Courier Email</label>
                  <input type="email" name="email" value="{{ $item->email}}" class="form-control">
              </div>
          </div>
          <div class="col-12">
              <div class="form-group">
                  <label>Courier Phone</label>
                  <input type="text" name="phone" value="{{ $item->phone}}" class="form-control">
              </div>
          </div>
          <div class="col-12">
              <div class="form-group">
                  <label>Courier Address</label>
                  <input type="text" name="address" value="{{ $item->address}}" class="form-control">
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