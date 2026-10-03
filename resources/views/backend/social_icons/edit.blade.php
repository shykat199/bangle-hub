<div class="modal-dialog">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel">Socila Icon Update</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form action="{{ route('admin.social_icons.update',[$item->id]) }}" method="POST" id="ajax_form">
      @csrf
      {{ method_field('PATCH') }}
        <div class="modal-body">
            
            <div class="row">
                <div class="col-lg-12">
                    <div class="mb-3">
                        <label  class="form-label">Socila Title</label>
                        <input type="text" name="title" class="form-control" value="{{ $item->title}}">
                    </div>

                </div>
                
                <div class="col-lg-12">
                    <div class="mb-3">
                        <label  class="form-label">Socila Icon</label>
                        <input type="file" name="icon" class="form-control">
                    </div>

                </div>
                
                <div class="col-lg-12">
                    <div class="mb-3">
                        <label  class="form-label">Socila Link</label>
                        <input type="text" name="link" class="form-control" value="{{ $item->link}}">
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