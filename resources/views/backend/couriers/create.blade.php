<div class="modal-dialog">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel">Courier Update</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form action="{{ route('admin.couriers.store') }}" method="POST" id="ajax_form">
      @csrf
      
      <div class="modal-body">
          <div class="col-12">
              <div class="form-group">
                  <label>Courier Name</label>
                  <input type="text" name="name" placeholder="Courier Name" class="form-control">
              </div>
          </div>
          <div class="col-12">
              <div class="form-group">
                  <label>Courier Email</label>
                  <input type="email" name="email" placeholder="Courier Email" class="form-control">
              </div>
          </div>
          <div class="col-12">
              <div class="form-group">
                  <label>Courier Phone</label>
                  <input type="text" name="phone" placeholder="Courier phone" class="form-control">
              </div>
          </div>
          <div class="col-12">
              <div class="form-group">
                  <label>Courier Address</label>
                  <input type="text" name="address" placeholder="Courier Address" class="form-control">
              </div>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary"> Save</button>
      </div>
    </form>
  </div>
</div>