@extends('backend.app')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<style>
  .section-title{
    font-weight:700; font-size:1.05rem; color:#111; margin:2px 0 10px;
    display:flex; align-items:center; gap:8px;
  }
  .section-title:before{
    content:""; width:6px; height:18px; border-radius:4px; background:#0d6efd; display:inline-block;
  }
  .soft-card{ border:1px solid #eef0f2; border-radius:12px; padding:14px; background:#fff; }

  .preview-wrap img{ width:54px; height:54px; object-fit:cover; border-radius:8px; border:1px solid #eee; margin-right:8px; margin-top:6px; }

  @media (max-width: 576px){
    .table-responsive{ border:0; }
    table.responsive-table thead{ display:none; }
    table.responsive-table tbody tr{
      display:block; margin-bottom:12px; border:1px solid #eee; border-radius:12px; padding:12px;
    }
    table.responsive-table tbody td{
      display:flex; justify-content:space-between; gap:10px; border:0 !important; padding:.3rem 0 !important;
      font-size:13px !important;
    }
    table.responsive-table tbody td::before{
      content: attr(data-label);
      font-weight:600; color:#333;
    }
  }

  .form-label{ font-weight:600; color:#222; }
  .select2-container--default .select2-selection--single{ height:38px; }
  .select2-container--default .select2-selection--single .select2-selection__rendered{ line-height:38px; }
  .select2-container--default .select2-selection--single .select2-selection__arrow{ height:36px; }

  .var-img-preview {
      width: 40px;
      height: 40px;
      object-fit: cover;
      border-radius: 6px;
      border: 1px solid #ddd;
      margin-top: 5px;
      display: block;
  }
</style>
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="page-title-box">
      <div class="page-title-right">
        <ol class="breadcrumb m-0">
          <li class="breadcrumb-item"><a href="javascript:void(0);">SIS</a></li>
          <li class="breadcrumb-item"><a href="javascript:void(0);">CRM</a></li>
          <li class="breadcrumb-item active">Product Create</li>
        </ol>
      </div>
      <h4 class="page-title">Product Create</h4>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">

        <form method="POST" action="{{ route('admin.products.store') }}" id="ajax_form" enctype="multipart/form-data">
          @csrf

          <div class="soft-card mb-3">
            <div class="section-title">Basic Information</div>
            <div class="row g-3">
              <div class="col-lg-4 col-md-6">
                <label class="form-label">Product Name</label>
                <input type="text" name="name" class="form-control" placeholder="Product Name">
              </div>

              <div class="col-lg-4 col-md-6">
                <label class="form-label">Product SKU</label>
                <input type="text" name="sku" class="form-control" placeholder="Product SKU">
              </div>

              <div class="col-lg-4 col-md-6">
                <label class="form-label">Product Brand</label>
                <select class="form-select" name="type_id" id="type_id">
                  <option value="">Select One</option>
                  @foreach($types as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-lg-4 col-md-6">
                <label class="form-label">Product Category</label>
                <select class="form-select" name="category_id" id="category_id">
                  <option value="">Select One</option>
                  @foreach($cats as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-lg-4 col-md-6">
                <label class="form-label">Sub Category</label>
                <select class="form-select" name="sub_category_id" id="sub_category_id">
                  <option value="">Select One</option>
                </select>
              </div>

              <div class="col-lg-4 col-md-6">
                <label class="form-label">Video Embedded Code</label>
                <textarea name="video_link" class="form-control" rows="1" placeholder="<iframe>...</iframe>"></textarea>
              </div>

              <div class="col-lg-4 col-md-6">
                <label class="form-label">Is Video Active?</label>
                <select name="is_video_active" id="is_video_active" class="form-control">
                  <option value="1" selected>Yes</option>
                  <option value="0">No</option>
                </select>
              </div>
            </div>
          </div>

          <div class="soft-card mb-3">
            <div class="section-title">Media</div>
            <div class="row g-3">
              <div class="col-lg-4 col-md-6">
                <label class="form-label">Image</label>
                <input type="file" name="image" id="image_single" class="form-control" accept="image/*">
                <div id="preview_single" class="preview-wrap d-flex"></div>
              </div>

              <div class="col-lg-8 col-md-6">
                <label class="form-label">Multi Image</label>
                <input type="file" name="images[]" id="images_multi" class="form-control" multiple accept="image/*">
                <div id="preview_multi" class="preview-wrap d-flex flex-wrap"></div>
              </div>
            </div>
          </div>

          <div class="soft-card mb-3">
            <div class="section-title">Pricing & Stock</div>
            <div class="row g-3">
              <div class="col-lg-3 col-md-6">
                <label class="form-label">Purchase Price</label>
                <input type="number" step="any" name="purchase_prices" class="form-control" placeholder="Purchase Price">
              </div>

              <div class="col-lg-3 col-md-6">
                <label class="form-label">Sell Price</label>
                <input type="number" step="any" name="sell_price" id="sell_price" class="form-control" placeholder="Sell Price">
              </div>

              <div class="col-lg-3 col-md-6">
                <label class="form-label">After Discount</label>
                <input type="number" step="any" name="after_discount" id="after_discount" class="form-control after_discount" placeholder="After Discount">
              </div>

              <div class="col-lg-3 col-md-6">
                <label class="form-label">Product Weight (KG)</label>
                <input type="number" step="0.01" name="weight" class="form-control" placeholder="Ex: 0.5 or 1.2" value="{{ old('weight', 0) }}">
              </div>

              <div class="col-lg-4 col-md-6">
                <label class="form-label">Product Type</label>
                <select name="type" id="prod_type" class="form-control">
                  <option value="single" selected>Single</option>
                  <option value="variable">Variable</option>
                </select>
              </div>

              <div class="col-lg-4 col-md-6">
                <label class="form-label">Manage Stock</label>
                <select name="is_stock" class="form-control" id="is_stock">
                  <option value="0">No</option>
                  <option value="1" selected>Yes</option>
                </select>
              </div>

              <div id="stock_qty" class="col-lg-4 col-md-6">
                <label class="form-label">Stock Quantity</label>
                <input type="number" step="any" name="pro_quantity" class="form-control quantity" value="1">
              </div>
            </div>
          </div>

          <div id="variable_table_two" class="soft-card mb-3">
            <div class="section-title">Variations</div>
            <div class="table-responsive">
              <table class="table table-centered table-nowrap table-bordered text-center align-middle responsive-table">
                <thead class="table-light">
                  <tr>
                    <th>Size</th>
                    <th>Color</th>
                    <th>Image</th>
                    <th style="width:15%;">Purchase Price</th>
                    <th style="width:15%;">Price</th>
                    <th style="width:15%;">Discount Price</th>
                    <th class="stock-col" style="width:15%;">Stock Quantity</th>
                    <th style="width:10%;">Action</th>
                  </tr>
                </thead>
                <tbody id="variant_tbody">
                  <tr>
                    <td data-label="Size">
                      <select name="size_id[]" class="form-control">
                        <option value="">-- No Size --</option>
                        @foreach($sizes as $size)
                          <option {{ $size->is_default==1 ? 'selected' : '' }} value="{{ $size->id }}">{{ $size->title }}</option>
                        @endforeach
                      </select>
                    </td>
                    <td data-label="Color">
                      <select name="color_id[]" class="form-control">
                        <option value="">-- No Color --</option>
                        @foreach($colors as $color)
                          <option {{ $color->is_default==1 ? 'selected' : '' }} value="{{ $color->id }}">{{ $color->name }}</option>
                        @endforeach
                      </select>
                    </td>
                    <td data-label="Image">
                      <input type="file" name="variation_image[]" class="form-control var-img-input" accept="image/*">
                      <img src="" class="var-img-preview" style="display:none;" alt="Preview">
                    </td>
                    <td data-label="Purchase"><input class="variable_purchase_price form-control" type="number" step="any" name="purchase_price[]" placeholder="Purchase Price"></td>
                    <td data-label="Price"><input class="variable_sell_price form-control" type="number" step="any" name="price[]" placeholder="Price"></td>
                    <td data-label="Discount"><input class="variable_dis_price form-control" type="number" step="any" name="after_discount_price[]" placeholder="Discount Price"></td>
                    <td data-label="Qty" class="stock-col"><input class="variant_qty form-control" type="number" step="any" name="quantity[]" value="1" placeholder="Stock Quantity"></td>
                    <td data-label="Action">
                      <a class="btn btn-sm btn-primary add_row"><i class="mdi mdi-plus"></i></a>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="soft-card mb-3">
            <div class="section-title">Content</div>
            <div class="row g-3">
              <div class="col-12 d-none">
                <label class="form-label">Feature</label>
                <textarea id="feature" class="form-control" name="feature" rows="5"></textarea>
              </div>
              
              <div class="col-12 mb-3">
                <label class="form-label">Short Description (Optional)</label>
                <textarea class="form-control" name="short_description" rows="3" placeholder="Write a short summary about the product..."></textarea>
              </div>

              <div class="col-12">
                <label class="form-label">Product Body</label>
                <textarea id="body" class="form-control" name="body" rows="6"></textarea>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-success px-4">Save</button>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script>
$(function () {
  $('#type_id, #category_id, #sub_category_id, #prod_type, #is_stock, #is_video_active').select2({ width: '100%' });

  initSummernote('#feature', 200);
  initSummernote('#body', 300);
  function initSummernote(selector, height){
    $(selector).summernote({
      height,
      toolbar: [
        ['style', ['style']],
        ['font', ['bold','italic','underline','clear']],
        ['fontsize', ['fontsize']],
        ['color', ['color']],
        ['para', ['ul','ol','paragraph']],
        ['insert', ['link','picture','video','hr']],
        ['view', ['fullscreen','codeview','help']]
      ],
      callbacks:{
        onImageUpload: function(files){
          let editor = $(this), data = new FormData();
          data.append('upload', files[0]);
          data.append('_token','{{ csrf_token() }}');
          $.ajax({
            url: "{{ route('admin.ckeditor.upload') }}",
            type: "POST", data, processData:false, contentType:false,
            success: function(resp){ resp.url ? editor.summernote('insertImage', resp.url) : alert('Image upload failed'); },
            error: function(){ alert('Upload error'); }
          });
        }
      }
    });
  }

  $('#category_id').on('change', function(){
    let cat_id = $(this).val();
    if(!cat_id){ $('#sub_category_id').html('<option value="">Select One</option>').trigger('change'); return; }
    $.get('{{ route("admin.getSubcategory") }}', {cat_id}, function(data){
      let html = '<option value="">Select One</option>';
      $.each(data, function(k,v){ html += `<option value="${k}">${v}</option>`; });
      $('#sub_category_id').html(html).trigger('change');
    }, 'json');
  });

  function toggleVariantTable(){
    const type = $('#prod_type').val();
    (type === 'variable') ? $('#variable_table_two').slideDown(150) : $('#variable_table_two').slideUp(150);
  }
  $('#prod_type').on('change', toggleVariantTable); toggleVariantTable();

  function toggleStock(){
    const on = ($('#is_stock').val() === '1');
    
    if(on){
        $('#stock_qty').show();
        $('.stock-col').show();
    } else {
        $('#stock_qty').hide();
        $('.stock-col').hide(); 
    }
  }
  $('#is_stock').on('change', toggleStock); toggleStock();

  $('#image_single').on('change', function(e){
    const f = e.target.files[0]; if(!f) return;
    const url = URL.createObjectURL(f);
    $('#preview_single').html(`<img src="${url}" alt="preview">`);
  });
  $('#images_multi').on('change', function(e){
    $('#preview_multi').empty();
    [...e.target.files].forEach(f=>{
      const url = URL.createObjectURL(f);
      $('#preview_multi').append(`<img src="${url}" alt="preview">`);
    });
  });

  $(document).on('change', '.var-img-input', function(e) {
      const file = e.target.files[0];
      const previewTag = $(this).siblings('.var-img-preview');
      
      if(file) {
          previewTag.attr('src', URL.createObjectURL(file)).show();
      } else {
          previewTag.hide();
      }
  });

  $('input[name="sell_price"]').on('blur', function(){ $('.variable_sell_price').val($(this).val()); });
  
  $('input[name="pro_quantity"]').on('input blur', function(){ 
      $('.variant_qty').val($(this).val()); 
  });

  $('input.after_discount').on('blur', function(){ $('.variable_dis_price').val($(this).val()); });

  $(document).on('click','.add_row', function(){
    const row = $(this).closest('tr');
    const p = row.find('.variable_purchase_price').val() || '';
    const s = row.find('.variable_sell_price').val() || '';
    const d = row.find('.variable_dis_price').val() || '';
    const q = row.find('.variant_qty').val() || '';
    
    const tpl = `
      <tr>
        <td data-label="Size">
          <select name="size_id[]" class="form-control">
            <option value="">-- No Size --</option>
            @foreach($sizes as $size)
              <option value="{{ $size->id }}">{{ $size->title }}</option>
            @endforeach
          </select>
        </td>
        <td data-label="Color">
          <select name="color_id[]" class="form-control">
            <option value="">-- No Color --</option>
            @foreach($colors as $color)
              <option value="{{ $color->id }}">{{ $color->name }}</option>
            @endforeach
          </select>
        </td>
        <td data-label="Image">
          <input type="file" name="variation_image[]" class="form-control var-img-input" accept="image/*">
          <img src="" class="var-img-preview" style="display:none;" alt="Preview">
        </td>
        <td data-label="Purchase"><input class="variable_purchase_price form-control" type="number" step="any" name="purchase_price[]" value="${p}" placeholder="Purchase Price"></td>
        <td data-label="Price"><input class="variable_sell_price form-control" type="number" step="any" name="price[]" value="${s}" placeholder="Price"></td>
        <td data-label="Discount"><input class="variable_dis_price form-control" type="number" step="any" name="after_discount_price[]" value="${d}" placeholder="Discount Price"></td>
        <td data-label="Qty" class="stock-col"><input class="variant_qty form-control" type="number" step="any" name="quantity[]" value="${q || 1}" placeholder="Stock Quantity"></td>
        <td data-label="Action">
          <a class="btn btn-sm btn-primary add_row"><i class="mdi mdi-plus"></i></a>
          <a class="btn btn-sm btn-danger remove_row"><i class="mdi mdi-delete"></i></a>
        </td>
      </tr>`;
    $('#variant_tbody').append(tpl);
    
    if($('#is_stock').val() !== '1') {
        $('.stock-col').hide();
    }
  });
  
  $(document).on('click','.remove_row', function(){
    if($('#variant_tbody tr').length <= 1) return;
    $(this).closest('tr').remove();
  });

});
</script>
@endpush