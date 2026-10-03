<div class="modal-dialog modal-lg">
  <div class="modal-content">
    <div class="modal-header bg-light">
      <h5 class="modal-title" id="exampleModalLabel"><i class="mdi mdi-square-edit-outline me-1"></i> Update Featured Collection</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    
    <form action="{{ route('admin.home_section_images.update',[$item->id]) }}" method="POST" id="ajax_form" enctype="multipart/form-data">
      @csrf
      {{ method_field('PATCH') }}
      
      <div class="modal-body">

          <div class="d-none">
              <div class="mb-3">
                  <label class="form-label">Desktop Image</label>
                  <input type="file" name="image" class="form-control">
              </div>
              <div class="mb-3">
                  <label class="form-label">Mobile Image</label>
                  <input type="file" name="mobile_image" class="form-control">
              </div>
              <div class="mb-3">
                  <label class="form-label">Sections</label>
                  <select class="form-control" name="section">
                      @foreach(getSectionLists() as $key=>$i)
                      <option value="{{ $key}}" {{ $item->section==$key ?'selected':''}}>{{ $i}}</option>
                      @endforeach
                  </select>
              </div>
              <div class="mb-3">
                  <label class="form-label">Link</label>
                  <input type="text" name="link" class="form-control" value="{{$item->link}}">
              </div>
              <div class="mb-3">
                  <input type="checkbox" class="form-check-input" name="is_for_small" value="1" {{$item->is_for_small=='1' ?'checked':''}}>
              </div>
          </div>

          <div class="row px-2">

              <div class="col-12 mb-3">
                  <div class="p-3 border rounded" style="background: #f8f9fa;">
                      <label class="form-label text-dark" style="font-size: 14px; font-weight:bold;">
                          <i class="mdi mdi-video"></i> Banner Background Video (MP4)
                      </label>
                      <input type="file" name="banner_video" class="form-control mb-2" accept="video/mp4,video/x-m4v,video/*">
                      @if(!empty($item->banner_video))
                          <div class="mt-2" style="background: #f8d7da; padding: 10px; border-radius: 5px; display: inline-block;">
                              <strong class="text-success d-block mb-1">✅ Current Video is Active.</strong>
                              <label class="text-danger font-weight-bold" style="cursor: pointer; margin: 0;">
                                  <input type="checkbox" name="remove_banner_video" value="1"> 
                                  Delete Current Video
                              </label>
                              <br>
                              <video src="{{ asset('homeimages/videos/'.$item->banner_video) }}" width="150" class="mt-2" muted></video>
                          </div>
                      @endif
                  </div>
              </div>

              <div class="col-md-6 mb-3">
                  <div class="p-2 bg-light border rounded">
                      <label class="form-label text-primary" style="font-size: 13px; font-weight:bold;">Image 1 (Small)</label>
                      @if($item->left_image_1)
                          <div class="mb-2">
                              <img src="{{ asset('homeimages/'.$item->left_image_1) }}" alt="Img 1" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ccc;">
                          </div>
                      @endif
                      <input type="file" name="left_image_1" class="form-control form-control-sm mb-2">
                      <input type="text" name="left_link_1" value="{{ $item->left_link_1 }}" placeholder="Link 1" class="form-control form-control-sm">
                  </div>
              </div>

              <div class="col-md-6 mb-3">
                  <div class="p-2 bg-light border rounded">
                      <label class="form-label text-primary" style="font-size: 13px; font-weight:bold;">Image 2 (Small)</label>
                      @if($item->left_image_2)
                          <div class="mb-2">
                              <img src="{{ asset('homeimages/'.$item->left_image_2) }}" alt="Img 2" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ccc;">
                          </div>
                      @endif
                      <input type="file" name="left_image_2" class="form-control form-control-sm mb-2">
                      <input type="text" name="left_link_2" value="{{ $item->left_link_2 }}" placeholder="Link 2" class="form-control form-control-sm">
                  </div>
              </div>

              <div class="col-md-6 mb-3">
                  <div class="p-2 bg-light border rounded">
                      <label class="form-label text-primary" style="font-size: 13px; font-weight:bold;">Image 3 (Small)</label>
                      @if($item->left_image_3)
                          <div class="mb-2">
                              <img src="{{ asset('homeimages/'.$item->left_image_3) }}" alt="Img 3" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ccc;">
                          </div>
                      @endif
                      <input type="file" name="left_image_3" class="form-control form-control-sm mb-2">
                      <input type="text" name="left_link_3" value="{{ $item->left_link_3 }}" placeholder="Link 3" class="form-control form-control-sm">
                  </div>
              </div>

              <div class="col-md-6 mb-3">
                  <div class="p-2 bg-light border rounded">
                      <label class="form-label text-primary" style="font-size: 13px; font-weight:bold;">Image 4 (Small)</label>
                      @if($item->left_image_4)
                          <div class="mb-2">
                              <img src="{{ asset('homeimages/'.$item->left_image_4) }}" alt="Img 4" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ccc;">
                          </div>
                      @endif
                      <input type="file" name="left_image_4" class="form-control form-control-sm mb-2">
                      <input type="text" name="left_link_4" value="{{ $item->left_link_4 }}" placeholder="Link 4" class="form-control form-control-sm">
                  </div>
              </div>

              <div class="col-12 mb-3">
                  <div class="p-3 border border-primary rounded" style="background: #eef2ff;">
                      <label class="form-label text-primary" style="font-size: 14px; font-weight:bold;">
                          <i class="mdi mdi-image-size-select-actual"></i> Main Large Image (Right Side)
                      </label>
                      @if($item->right_image)
                          <div class="mb-2">
                              <img src="{{ asset('homeimages/'.$item->right_image) }}" alt="Large Img" style="height: 80px; width: auto; object-fit: cover; border-radius: 4px; border: 1px solid #0d6efd;">
                          </div>
                      @endif
                      <input type="file" name="right_image" class="form-control mb-2">
                      <input type="text" name="right_link" value="{{ $item->right_link }}" placeholder="Main Link" class="form-control">
                  </div>
              </div>
          </div>

      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Update Collection</button>
      </div>
    </form>
  </div>
</div>