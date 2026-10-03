<div class="modal-dialog">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel">Supplier Add</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form action="{{ route('admin.suppliers.store') }}" method="POST" id="ajax_form">
      @csrf
        <div class="modal-body">
            
            <div class="row">
                <div class="col-lg-12">
                    <div class="mb-3">
                        <label  class="form-label">Supplier Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Supplier Name">
                    </div>
                    
                    <div class="mb-3">
                        <label  class="form-label">Supplier Mobile</label>
                        <input type="text" name="mobile" class="form-control" placeholder="Supplier Mobile">
                    </div>
                    
                    <div class="mb-3">
                        <label  class="form-label">Supplier Address</label>
                        <textarea name="address" class="form-control" placeholder="Supplier Address"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label  class="form-label">Supplier Unique Id </label>
                        <input type="text" name="contact_id" class="form-control" placeholder="Supplier Unique Id">
                    </div>
                    
        
                </div>
            </div>
                    

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary"> Create</button>
      </div>
    </form>
  </div>
</div>