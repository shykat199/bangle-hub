<div class="modal-dialog">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel">Supplier Update</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form action="{{ route('admin.suppliers.update',[$item->id]) }}" method="POST" id="ajax_form">
      @csrf
      {{ method_field('PATCH') }}
        <div class="modal-body">
            
            <div class="row">
                <div class="col-lg-12">
                    <div class="mb-3">
                        <label  class="form-label">Supplier Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $item->name}}">
                    </div>
                    
                    <div class="mb-3">
                        <label  class="form-label">Supplier Mobile</label>
                        <input type="text" name="mobile" class="form-control" value="{{ $item->mobile}}">
                    </div>
                    
                    <div class="mb-3">
                        <label  class="form-label">Supplier Address</label>
                        <textarea name="address" class="form-control" >{{ $item->address}}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label  class="form-label">Supplier Unique Id </label>
                        <input type="text" name="contact_id" class="form-control" value="{{ $item->contact_id}}">
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