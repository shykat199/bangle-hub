@extends('backend.app')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<style>
  :root{
    --bg:#f3f4f6;
    --card:#ffffff;
    --primary:#0ea5e9;
    --primary-soft:rgba(14,165,233,.08);
    --text:#0f172a;
    --muted:#6b7280;
    --border:#e5e7eb;
    --danger:#ef4444;
  }

  body{ background:var(--bg); }

  .page-title-box{ border:0; padding-bottom:0; }
  .page-title-box h4.page-title{ font-weight:700; color:var(--text); }
  .page-title-right .breadcrumb{ background:transparent; }

  .card{
    border:0;
    border-radius:20px;
    overflow:hidden;
    box-shadow:0 18px 45px rgba(15,23,42,.08);
    background:linear-gradient(135deg,#eff6ff 0,var(--card) 40%,var(--card) 100%);
  }
  .card-body{ padding:24px 24px 28px; }

  .section-heading{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    margin-bottom:10px;
    margin-top:8px;
  }
  .section-heading h5{
    font-size:1rem;
    font-weight:700;
    color:var(--text);
    margin:0;
  }
  .section-heading span.badge-tag{
    font-size:.75rem;
    text-transform:uppercase;
    letter-spacing:.06em;
    padding:.25rem .6rem;
    border-radius:999px;
    background:var(--primary-soft);
    color:var(--primary);
    font-weight:600;
  }

  .form-label{
    font-weight:600;
    color:var(--text);
    font-size:.9rem;
    margin-bottom:.25rem;
  }
  .form-control,
  .form-select{
    border-radius:.8rem;
    border-color:var(--border);
    font-size:.9rem;
    padding:.55rem .75rem;
    box-shadow:none;
    background:#f9fafb;
  }
  .form-control:focus,
  .form-select:focus{
    border-color:var(--primary);
    box-shadow:0 0 0 2px var(--primary-soft);
    background:#ffffff;
  }

  .select2-container--default .select2-selection--single{
    border-radius:.8rem !important;
    border:1px solid var(--border) !important;
    height:auto !important;
    padding:.25rem .5rem;
    background:#f9fafb;
  }
  .select2-container--default .select2-selection--single .select2-selection__rendered{
    padding-left:2px;
    padding-right:24px;
    line-height:1.5;
    font-size:.9rem;
    color:var(--text);
  }
  .select2-container--default .select2-selection--single .select2-selection__arrow{ height:100%; }

  .img-box{ position:relative; padding:5px; }
  .img-box a{
    position:absolute;
    top:2px;
    right:8px;
    font-size:18px;
    font-weight:700;
    color:var(--danger);
    line-height:1;
    text-decoration:none;
  }
  .preview-wrap img{
    width:54px;
    height:54px;
    object-fit:cover;
    border-radius:10px;
    border:1px solid #e5e7eb;
    margin-right:8px;
    margin-top:6px;
  }

  .media-box{
    border-radius:16px;
    border:1px dashed #d1d5db;
    padding:14px 14px 8px;
    background:#f9fafb;
  }

  .table-responsive{
    border-radius:14px;
    border:1px solid #e5e7eb;
    background:#f9fafb;
    padding:6px;
  }
  table.responsive-table{ margin-bottom:0; }
  table.responsive-table thead th{
    background:#eef2ff;
    border-color:#e5e7eb;
    font-size:.85rem;
    text-transform:uppercase;
    letter-spacing:.06em;
    color:#4b5563;
  }
  table.responsive-table tbody td{
    vertical-align:middle;
    font-size:.88rem;
  }

  @media (max-width: 576px){
    .card-body{ padding:18px 14px 20px; }
    .page-title-right{ margin-top:4px; }
    .page-title-right .breadcrumb{
      font-size:.78rem;
      flex-wrap:wrap;
    }
    .table-responsive{ border:0; background:transparent; padding:0; }
    table.responsive-table thead{ display:none; }
    table.responsive-table tbody tr{
      display:block;
      margin-bottom:12px;
      border-radius:14px;
      border:1px solid #e5e7eb;
      box-shadow:0 4px 14px rgba(15,23,42,.06);
      background:#ffffff;
      padding:10px 10px 6px;
    }
    table.responsive-table tbody td{
      display:flex;
      justify-content:space-between;
      gap:10px;
      border:0 !important;
      padding:.3rem 0 !important;
      font-size:13px !important;
    }
    table.responsive-table tbody td::before{
      content: attr(data-label);
      font-weight:600;
      color:#111827;
    }
  }

  .btn-primary{
    background:linear-gradient(135deg,#0ea5e9,#2563eb);
    border:0;
    border-radius:999px;
    padding:.55rem 1.4rem;
    font-weight:600;
    box-shadow:0 12px 25px rgba(37,99,235,.35);
  }
  .btn-primary:hover{
    background:linear-gradient(135deg,#0284c7,#1d4ed8);
    box-shadow:0 16px 30px rgba(37,99,235,.45);
  }
  .btn-sm.btn-primary.add_moore{ border-radius:999px; }
  .btn-sm.btn-danger.remove{ border-radius:999px; }
  .text-muted{ font-size:.8rem; }
 
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
    <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-2">
      <div>
        <h4 class="page-title mb-1">Product Update</h4>
        <small class="text-muted">Update product details, pricing, media & variations in one clean view.</small>
      </div>
      <div class="page-title-right">
        <ol class="breadcrumb m-0">
          <li class="breadcrumb-item"><a href="javascript:void(0)">SIS</a></li>
          <li class="breadcrumb-item"><a href="javascript:void(0)">CRM</a></li>
          <li class="breadcrumb-item active">Product Update</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="row mt-2">
  <div class="col-12">
    <div class="card">
      <div class="card-body">

        <form method="POST" action="{{ route('admin.products.update',[$item->id])}}" id="ajax_form" enctype="multipart/form-data">
          @csrf
          @method('PATCH')

          <input type="hidden" name="type" id="type_hidden" value="{{ $item->type }}">

          <div class="section-heading">
            <h5>Basic Information</h5>
            <span class="badge-tag">Step 1</span>
          </div>
          <div class="row g-3 mb-2">
            <div class="col-lg-4 col-md-6">
              <label class="form-label">Product Name</label>
              <input type="text" name="name" class="form-control" placeholder="Product Name" value="{{ $item->name }}">
            </div>

            <div class="col-lg-4 col-md-6">
              <label class="form-label">Product SKU</label>
              <input type="text" name="sku" class="form-control" placeholder="Product SKU" value="{{ $item->sku }}">
            </div>

            <div class="col-lg-4 col-md-6">
              <label class="form-label">Product Brand</label>
              <select class="form-select" name="type_id" id="type_id">
                <option value="">Select One</option>
                @foreach($types as $type)
                  <option value="{{$type->id}}" {{ $type->id == $item->type_id ? 'selected':'' }}>{{ $type->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-lg-4 col-md-6">
              <label class="form-label">Product Category</label>
              <select class="form-select" name="category_id" id="category_id">
                <option value="">Select One</option>
                @foreach($cats as $cat)
                  <option value="{{$cat->id}}" {{$cat->id == $item->category_id ? 'selected':''}}>{{ $cat->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-lg-4 col-md-6">
              <label class="form-label">Sub Category</label>
              <select class="form-select" name="sub_category_id" id="sub_category_id">
                <option value="">Select One</option>
                @foreach($subs as $sub)
                  <option value="{{$sub->id}}" {{ $sub->id == $item->sub_category_id ? 'selected':'' }}>{{ $sub->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-lg-4 col-md-6">
              <label class="form-label">Video Embedded Code</label>
              <textarea name="video_link" class="form-control" rows="2" placeholder="<iframe>...">{{ $item->video_link }}</textarea>
            </div>

            <div class="col-lg-4 col-md-6">
              <label class="form-label">Is Video Active?</label>
              <select name="is_video_active" id="is_video_active" class="form-select">
                <option value="1" {{ (string)($item->is_video_active ?? 1) === '1' ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ (string)($item->is_video_active ?? 1) === '0' ? 'selected' : '' }}>No</option>
              </select>
            </div>
          </div>

          <hr class="my-3">

          <div class="section-heading">
            <h5>Media & Gallery</h5>
            <span class="badge-tag">Step 2</span>
          </div>
          <div class="row g-3 mb-2">
            <div class="col-lg-4 col-md-6">
              <div class="media-box mb-2">
                <small class="d-block mb-1 text-muted">Current Image</small>
                <div class="d-flex align-items-center gap-2">
                  <img src="{{ getImage('products',$item->image)}}" width="60" height="60" class="rounded" style="object-fit:cover;border:1px solid #e5e7eb;">
                  <span class="text-muted" style="font-size:.8rem;">You can replace this image from below.</span>
                </div>
              </div>
              <label class="form-label">New Image (optional)</label>
              <input type="file" name="image" id="image_single" class="form-control" accept="image/*">
              <div id="preview_single" class="preview-wrap d-flex"></div>
            </div>

            <div class="col-lg-8 col-md-6">
              <div class="media-box mb-2">
                <small class="d-block mb-1 text-muted">Current Gallery</small>
                <div class="d-flex flex-wrap mb-1">
                  @foreach ($item->images as $image)
                    <div class="img-box">
                      <a href="{{ route('admin.deleteImage',[$image->id])}}" onclick="return confirm('Delete this image?')">&times;</a>
                      <img src="{{ getImage('products',$image->image)}}" width="54" height="54" style="object-fit:cover;border-radius:10px;border:1px solid #eee;">
                    </div>
                  @endforeach
                  @if($item->images->count() == 0)
                    <span class="text-muted" style="font-size:.8rem;">No gallery images added yet.</span>
                  @endif
                </div>
              </div>
              <label class="form-label">Add / Replace Gallery Images</label>
              <input type="file" name="images[]" id="images_multi" class="form-control" multiple accept="image/*">
              <div id="preview_multi" class="preview-wrap d-flex flex-wrap"></div>
            </div>
          </div>

          <hr class="my-3">

          <div class="section-heading">
            <h5>Pricing & Stock</h5>
            <span class="badge-tag">Step 3</span>
          </div>
          <div class="row g-3 mb-2">
            <div class="col-lg-3 col-md-6">
              <label class="form-label">Purchase Price</label>
              <input type="number" step="any" name="purchase_prices" class="form-control" value="{{ $item->purchase_prices }}">
            </div>

            <div class="col-lg-3 col-md-6">
              <label class="form-label">Sell Price</label>
              <input type="number" step="any" id="sell_price" name="sell_price" class="form-control" value="{{ $item->sell_price }}">
            </div>

            <div class="col-lg-3 col-md-6">
              <label class="form-label">After Discount</label>
              <input type="number" step="any" id="after_discount" name="after_discount" class="form-control after_discount" value="{{ $item->after_discount }}">
            </div>

            <div class="col-lg-3 col-md-6">
              <label class="form-label">Product Weight (KG)</label>
              <input type="number" step="0.01" name="weight" class="form-control" placeholder="Ex: 0.5 or 1.2" value="{{ old('weight', $item->weight ?? 0) }}">
            </div>

            <div class="col-lg-3 col-md-6">
              <label class="form-label">Product Type</label>
              <select name="type_selector" id="prod_type" class="form-control">
                <option value="single" {{ $item->type=='single'?'selected':'' }}>Single</option>
                <option value="variable" {{ $item->type=='variable'?'selected':'' }}>Variable</option>
              </select>
            </div>

            <div class="col-lg-3 col-md-6">
              <label class="form-label">Manage Stock</label>
              <select name="is_stock" class="form-control" id="is_stock">
                <option value="0" {{ (string)($item->is_stock ?? 0) === '0' ? 'selected':'' }}>No</option>
                <option value="1" {{ (string)($item->is_stock ?? 0) === '1' ? 'selected':'' }}>Yes</option>
              </select>
            </div>

            <div id="stock_qty" class="col-lg-3 col-md-6 {{ (string)($item->is_stock ?? 0) === '1' ? '' : 'd-none' }}">
              <label class="form-label">Stock Quantity</label>
              <input type="number" step="any" name="pro_quantity" class="form-control quantity" value="{{ (int)($item->stock_quantity ?? 0) }}">
            </div>

            <div class="col-lg-3 col-md-6">
              <label class="form-label">Discount Type (optional)</label>
              <select class="form-select" name="discount_type" id="discount_type">
                <option value="">Select Discount Type</option>
                <option value="fixed" {{ $item->discount_type == 'fixed' ? 'selected' : '' }}>Fixed</option>
                <option value="percentage" {{ $item->discount_type == 'percentage' ? 'selected' : '' }}>Percentage</option>
              </select>
            </div>

            <div class="col-lg-3 col-md-6">
              <label class="form-label">Discount Amount (optional)</label>
              <input type="number" step="any" name="dicount_amount" id="dicount_amount" class="form-control dicount_amount" value="{{ $item->dicount_amount }}">
            </div>
          </div>

          <hr class="my-3">

          <div id="variable_table_two" class="{{ $item->type=='variable' ? '' : 'd-none' }}">
            <div class="section-heading mb-2">
              <h5>Variations (Size / Color / Image)</h5>
              <span class="badge-tag">Step 4</span>
            </div>

            <div class="table-responsive">
              <table class="table table-centered table-nowrap table-bordered text-center align-middle responsive-table">
                <thead class="table-light">
                  <tr>
                    <th>Size</th>
                    <th>Color</th>
                    <th>Image</th>
                    <th style="width:15%;">Purchase</th>
                    <th style="width:15%;">Price</th>
                    <th style="width:15%;">Discount Price</th>
                    <th class="stock-col" style="width:15%;">Stock Qty</th>
                    <th style="width:10%;">Action</th>
                  </tr>
                </thead>
                <tbody id="varBody">
                @forelse($item->variations as $v)
                  <tr>
                    <td data-label="Size">
                      <input type="hidden" name="variation_id[]" value="{{$v->id}}">
                      <input type="hidden" name="product_id[]" value="{{$item->id}}">
                      <select name="size_id[]" class="form-select">
                        <option value="" {{ is_null($v->size_id) ? 'selected' : '' }}>-- No Size --</option>
                        @foreach($sizes as $size)
                          <option value="{{$size->id}}" {{ $size->id==$v->size_id ? 'selected':'' }}>{{ $size->title }}</option>
                        @endforeach
                      </select>
                    </td>
                    <td data-label="Color">
                      <select name="color_id[]" class="form-select">
                        <option value="" {{ is_null($v->color_id) ? 'selected' : '' }}>-- No Color --</option>
                        @foreach($colors as $color)
                          <option value="{{$color->id}}" {{ $color->id==$v->color_id ? 'selected':'' }}>{{ $color->name }}</option>
                        @endforeach
                      </select>
                    </td>
                    <td data-label="Image">
                      <input type="file" name="variation_image[]" class="form-control var-img-input" accept="image/*">
                      @if($v->image)
                        <img src="{{ asset('products/'.$v->image) }}" class="var-img-preview" alt="var image">
                      @else
                         <img src="" class="var-img-preview" style="display:none;" alt="var image">
                      @endif
                    </td>
                    <td data-label="Purchase">
                      <input class="variable_purchase_price form-control" type="number" step="any" value="{{ $v->purchase_price }}" name="purchase_price[]" placeholder="Purchase">
                    </td>
                    <td data-label="Price">
                      <input class="variable_sell_price form-control" type="number" step="any" value="{{ $v->price }}" name="price[]" placeholder="Price">
                    </td>
                    <td data-label="After Discount">
                      <input class="variable_dis_price form-control" type="number" step="any" value="{{ $v->after_discount_price }}" name="after_discount_price[]" placeholder="After Discount">
                    </td>
                    <td data-label="Qty" class="stock-col">
                      {{-- অর্ডারে স্টক কমে product_stocks-এ, তাই সেটাই আসল হিসাব।
                           আগে variations.stock_quantity দেখাত বলে পুরনো সংখ্যা আসত। --}}
                      <input class="quantity form-control" type="number" step="any" value="{{ (int)($v->stocks->sum('quantity') ?: ($v->stock_quantity ?? 0)) }}" name="quantity[]" placeholder="Qty">
                    </td>
                    <td data-label="Action">
                      <a class="action-icon btn btn-sm btn-primary add_moore"><i class="mdi mdi-plus"></i></a>
                      <a class="action-icon btn btn-sm btn-danger remove"><i class="mdi mdi-delete"></i></a>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td data-label="Size">
                      <select name="size_id[]" class="form-select">
                        <option value="">-- No Size --</option>
                        @foreach($sizes as $size)
                          <option value="{{ $size->id }}">{{ $size->title }}</option>
                        @endforeach
                      </select>
                    </td>
                    <td data-label="Color">
                      <select name="color_id[]" class="form-select">
                        <option value="">-- No Color --</option>
                        @foreach($colors as $color)
                          <option value="{{ $color->id }}">{{ $color->name }}</option>
                        @endforeach
                      </select>
                    </td>
                    <td data-label="Image">
                      <input type="file" name="variation_image[]" class="form-control var-img-input" accept="image/*">
                      <img src="" class="var-img-preview" style="display:none;" alt="var image">
                    </td>
                    <td data-label="Purchase">
                      <input class="variable_purchase_price form-control" type="number" step="any" name="purchase_price[]" placeholder="Purchase">
                    </td>
                    <td data-label="Price">
                      <input class="variable_sell_price form-control" type="number" step="any" name="price[]" placeholder="Price">
                    </td>
                    <td data-label="After Discount">
                      <input class="variable_dis_price form-control" type="number" step="any" name="after_discount_price[]" placeholder="After Discount">
                    </td>
                    <td data-label="Qty" class="stock-col">
                      <input class="quantity form-control" type="number" step="any" name="quantity[]" placeholder="Qty">
                    </td>
                    <td data-label="Action">
                      <a class="action-icon btn btn-sm btn-primary add_moore"><i class="mdi mdi-plus"></i></a>
                      <a class="action-icon btn btn-sm btn-danger remove"><i class="mdi mdi-delete"></i></a>
                    </td>
                  </tr>
                @endforelse
                </tbody>
              </table>
            </div>
          </div>

          <hr class="my-3">

          <div class="section-heading">
            <h5>Product Description</h5>
            <span class="badge-tag">Step 5</span>
          </div>
          <div class="row">
            <div class="col-12 mb-3">
              <label class="form-label">Short Description (Optional)</label>
              <textarea class="form-control" name="short_description" rows="3" placeholder="Write a short summary about the product...">{!! $item->short_description !!}</textarea>
            </div>

            <div class="col-12">
              <label class="form-label">Product Body</label>
              <textarea class="form-control" name="body" id="body" rows="6">{!! $item->body !!}</textarea>
            </div>

            <div class="col-12 mt-3 d-flex justify-content-end">
              <button type="submit" class="btn btn-primary px-4">Update Product</button>
            </div>
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
$(function(){

  $('#type_id,#category_id,#sub_category_id,#prod_type,#is_stock,#discount_type,#is_video_active').select2({ width:'100%' });

  $('#body').summernote({
    height: 300,
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
        let data = new FormData();
        data.append('upload', files[0]);
        data.append('_token', '{{ csrf_token() }}');
        $.ajax({
          url: "{{ route('admin.ckeditor.upload') }}",
          type: "POST",
          data,
          processData:false,
          contentType:false,
          success: function(resp){
            if(resp.url) $('#body').summernote('insertImage', resp.url);
            else alert('Image upload failed.');
          },
          error: function(){ alert('Upload error.'); }
        });
      }
    }
  });

  $('#image_single').on('change', function(e){
    const f = e.target.files[0]; if(!f) return;
    $('#preview_single').html(`<img src="${URL.createObjectURL(f)}" alt="preview">`);
  });
  $('#images_multi').on('change', function(e){
    $('#preview_multi').empty();
    [...e.target.files].forEach(f=>{
      $('#preview_multi').append(`<img src="${URL.createObjectURL(f)}" alt="preview">`);
    });
  });

  $(document).on('change', '.var-img-input', function(e) {
      const file = e.target.files[0];
      const previewTag = $(this).siblings('.var-img-preview');
      
      if(file) {
          previewTag.attr('src', URL.createObjectURL(file)).show();
      } else {
          if(!previewTag.attr('src').includes('products/')) {
              previewTag.hide();
          }
      }
  });

  $('#category_id').on('change', function(){
    const cat_id = $(this).val();
    if(!cat_id){
      $('#sub_category_id').html('<option value="">Select One</option>').trigger('change');
      return;
    }
    $.getJSON('{{ route("admin.getSubcategory")}}', { cat_id }, function(data){
      let html = '<option value="">Select One</option>';
      $.each(data, function(key, value){ html += `<option value="${key}">${value}</option>`; });
      $('#sub_category_id').html(html).trigger('change');
    });
  });

  function toggleVariantTable(){
    const t = $('#prod_type').val();
    $('#type_hidden').val(t);
    (t === 'variable') ? $('#variable_table_two').removeClass('d-none') : $('#variable_table_two').addClass('d-none');
  }
  $('#prod_type').on('change', toggleVariantTable);
  toggleVariantTable();

  // আগে Manage Stock = No হলে এখানেই সব qty ইনপুট ০ বসিয়ে দিত। toggleStock()
  // পেজ লোডেও চলে, তাই এমন প্রোডাক্ট খুলে সেভ করলেই আসল স্টক মুছে ০ হয়ে যেত।
  // এখন শুধু ঘরগুলো লুকানো হয় — সংখ্যা অক্ষত থাকে, Yes করলে আবার ফিরে আসে।
  function toggleStock(){
    const on = ($('#is_stock').val() === '1');

    if(on){
      $('#stock_qty').removeClass('d-none');
      $('.stock-col').show();
    }else{
      $('#stock_qty').addClass('d-none');
      $('.stock-col').hide();
    }
  }
  $('#is_stock').on('change', toggleStock);
  toggleStock();

  $('input[name="sell_price"]').on('blur', function(){
    $('.variable_sell_price').val($(this).val());
  });
  // মেইন Stock Quantity ঘরেরও class="quantity", আর প্রতিটা ভ্যারিয়েশন রো-রও।
  // তাই $('.quantity') সব কটা ধরত — মেইন ঘরে ক্লিক করে সরলেই ভ্যারিয়েবল প্রোডাক্টের
  // প্রতিটা ভ্যারিয়েশনের আলাদা স্টক একই সংখ্যায় চাপা পড়ে যেত।
  // এখন শুধু single প্রোডাক্টে কপি হয়, যেখানে ভ্যারিয়েশন একটাই।
  $('input[name="pro_quantity"]').on('blur', function(){
    if($('#is_stock').val()==='1' && $('#prod_type').val()==='single'){
      $('#varBody .quantity').val($(this).val());
    }
  });

  $(document).on('click','.add_moore', function(e){
    e.preventDefault();
    const row = $(this).closest('tr');
    const p = row.find('.variable_purchase_price').val() || '';
    const s = row.find('.variable_sell_price').val() || '';
    const d = row.find('.variable_dis_price').val() || '';
    const q = row.find('.quantity').val() || '';

    const tpl = `
    <tr>
      <td data-label="Size">
        <select name="size_id[]" class="form-select">
          <option value="">-- No Size --</option>
          @foreach($sizes as $size)
            <option value="{{$size->id}}">{{ $size->title }}</option>
          @endforeach
        </select>
      </td>
      <td data-label="Color">
        <select name="color_id[]" class="form-select">
          <option value="">-- No Color --</option>
          @foreach($colors as $color)
            <option value="{{$color->id}}">{{ $color->name }}</option>
          @endforeach
        </select>
      </td>
      <td data-label="Image">
        <input type="file" name="variation_image[]" class="form-control var-img-input" accept="image/*">
        <img src="" class="var-img-preview" style="display:none;" alt="var image">
      </td>
      <td data-label="Purchase"><input class="variable_purchase_price form-control" type="number" step="any" value="${p}" name="purchase_price[]" placeholder="Purchase"></td>
      <td data-label="Price"><input class="variable_sell_price form-control" type="number" step="any" value="${s}" name="price[]" placeholder="Price"></td>
      <td data-label="After Discount"><input class="variable_dis_price form-control" type="number" step="any" value="${d}" name="after_discount_price[]" placeholder="After Discount"></td>
      <td data-label="Qty" class="stock-col"><input class="quantity form-control" type="number" step="any" value="${q}" name="quantity[]" placeholder="Qty"></td>
      <td data-label="Action">
        <a class="action-icon btn btn-sm btn-primary add_moore"><i class="mdi mdi-plus"></i></a>
        <a class="action-icon btn btn-sm btn-danger remove"><i class="mdi mdi-delete"></i></a>
      </td>
    </tr>`;
    $('#varBody').append(tpl);

    toggleStock();
  });

  $(document).on('click','.remove', function(e){
    e.preventDefault();
    if($('#varBody tr').length <= 1) return;
    $(this).closest('tr').remove();
  });

});
</script>
@endpush