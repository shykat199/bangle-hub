<div class="modal-dialog">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel">Color Update</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form action="{{ route('admin.colors.update',[$item->id]) }}" method="POST" id="ajax_form" class="color-fields">
      @csrf
      {{ method_field('PATCH') }}
      <div class="modal-body">
          <div class="form-group mb-3">
              <label class="form-label">Color Name</label>
              <input type="text" class="form-control color-name-input" name="name" value="{{$item->name}}">
          </div>

          <div class="form-group mb-3">
              <label class="form-label">Color Code</label>
              <div class="input-group">
                  <input type="color" class="form-control form-control-color color-code-picker" value="#000000" title="Pick a color">
                  <input type="text" name="code" class="form-control color-code-input" placeholder="#FFFFFF or rgb(...)" autocomplete="off" value="{{$item->code}}">
              </div>
              <small class="color-detect-hint text-muted"></small>
          </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary"> Update</button>
      </div>
    </form>
  </div>
</div>