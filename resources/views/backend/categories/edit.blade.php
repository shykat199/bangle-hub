<div class="modal-dialog">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel">Catgeory Update</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form action="{{ route('admin.categories.update',[$item->id]) }}" method="POST" id="ajax_form">
      @csrf
      {{ method_field('PATCH') }}
      <div class="modal-body">
        
          <div class="form-group mb-3">
              <input type="text" class="form-control" name="name" value="{{$item->name}}">
          </div>
          
           <div class="form-group mb-3 d-none">
                <input type="text" name="url" class="form-control" value="{{$item->url}}">
            </div>

          <div class="form-group mb-3">
              <input type="file" class="form-control" name="image">
          </div>

          <div class="mb-3">
            <label  class="form-label">Parent Category</label>
            <select class="form-control select2" name="parent_id">
                <option value="" hidden>Select Category ..</option>
                @foreach($cats as $key=>$cat)
                <option value="{{ $key}}" {{$item->parent_id==$key}}>{{ $cat}}</option>
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