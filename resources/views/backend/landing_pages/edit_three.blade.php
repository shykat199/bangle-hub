@extends('backend.app')

@section('content')
@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />

<style>
    :root {
        --primary-color: #e11d48; /* Premium Red/Rose */
        --primary-hover: #be123c;
        --bg-light: #fdf2f8;
        --text-dark: #111827;
        --text-muted: #6b7280;
    }

    /* Premium Card */
    .premium-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0,0,0,0.05);
        margin-bottom: 24px;
        overflow: hidden;
    }
    .card-header-custom {
        padding: 20px 24px;
        border-bottom: 1px solid #f3f4f6;
        background: #fff;
        display: flex; align-items: center; gap: 10px;
    }
    .card-header-custom h5 { margin: 0; font-weight: 700; color: var(--text-dark); font-size: 1.1rem; }
    .card-header-custom i { color: var(--primary-color); font-size: 1.2rem; }
    .card-body-custom { padding: 24px; }

    /* Form Elements */
    .form-label { font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 0.9rem; }
    .form-control {
        border-radius: 8px; border: 1px solid #d1d5db; padding: 10px 14px;
        font-size: 0.95rem; transition: all 0.2s;
    }
    .form-control:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.1); }

    /* File Upload & Existing Images */
    .file-upload-wrapper {
        position: relative; border: 2px dashed #d1d5db; border-radius: 12px;
        padding: 20px; text-align: center; background: #fff1f2; transition: all 0.2s; cursor: pointer;
    }
    .file-upload-wrapper:hover { border-color: var(--primary-color); background: #ffe4e6; }
    .file-upload-wrapper input[type="file"] {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10;
    }
    
    .existing-img-container { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 15px; }
    .existing-img-box { position: relative; border: 1px solid #e5e7eb; border-radius: 8px; padding: 4px; background: #fff; }
    .existing-img-box img { border-radius: 6px; width: 70px; height: 70px; object-fit: cover; }
    .btn-delete-img {
        position: absolute; top: -8px; right: -8px; background: #ef4444; color: white;
        border-radius: 50%; width: 22px; height: 22px; font-size: 12px;
        display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .btn-delete-img:hover { background: #dc2626; color: white; }

    /* Image Preview */
    .img-preview-container { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 15px; justify-content: center; }
    .preview-box { position: relative; width: 80px; height: 80px; border-radius: 8px; overflow: hidden; border: 1px solid #ddd; }
    .preview-box img { width: 100%; height: 100%; object-fit: cover; }

    /* Product Section */
    .product-search-box { background: #fff1f2; padding: 20px; border-radius: 12px; border: 1px solid #fecdd3; }
    
    /* Save Button */
    .btn-save {
        background: var(--primary-color); color: white; padding: 12px 30px;
        border-radius: 8px; font-weight: 600; border: none; transition: 0.3s;
        box-shadow: 0 4px 6px rgba(225, 29, 72, 0.25);
    }
    .btn-save:hover { background: var(--primary-hover); transform: translateY(-2px); }

    /* Table Fixes */
    #product_table td small, #product_table td .text-muted { display: none !important; }
</style>
@endpush

<div class="container-fluid">
    
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="page-title">Edit Premium Page (Type 3)</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.landing_pages_three') }}">Premium Pages</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.landing_pages_three.update', [$item->id]) }}" id="ajax_form">
        @csrf
        @method('PATCH')
        
        <div class="row">
            <div class="col-lg-8">
                
                <div class="premium-card">
                    <div class="card-header-custom">
                        <i class="fas fa-edit"></i>
                        <h5>Basic Information</h5>
                    </div>
                    <div class="card-body-custom">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Page Title <span class="text-danger">*</span></label>
                                <input type="text" value="{{ $item->title1 }}" name="title1" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">URL Slug <small class="text-muted">(optional — খালি রাখলে আগেরটাই থাকবে)</small></label>
                                <input type="text" value="{{ $item->slug }}" name="slug" class="form-control" placeholder="e.g. summer-sale-2026" pattern="[a-z0-9]+(-[a-z0-9]+)*" title="শুধু ছোট হাতের অক্ষর, সংখ্যা ও হাইফেন">
                                
                                {{-- Hidden Fields --}}
                                <input type="hidden" name="page_type" value="3">
                                <input type="hidden" name="product_id" id="product_id" value="{{ $item->product_id }}">
                                <input type="hidden" id="new_product_id" name="new_product_id">
                                <input type="hidden" name="variation_id" id="variation_id" value="{{ $item->variation_id }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" value="{{ $item->phone }}" name="phone" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sticky Button Text</label>
                                <input type="text" value="{{ $item->btn_text_hero ?? 'অর্ডার করুন' }}" name="btn_text_hero" class="form-control" placeholder="e.g. Order Now">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="premium-card">
                    <div class="card-header-custom">
                        <i class="fas fa-align-left"></i>
                        <h5>Page Content</h5>
                    </div>
                    <div class="card-body-custom">
                        <div class="mb-3">
                            <label class="form-label">Top Headline (Optional)</label>
                            <input type="text" value="{{ $item->left_side_title }}" name="left_side_title" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Main Description</label>
                            <textarea class="form-control summernote" name="left_side_desc">{!! $item->left_side_desc !!}</textarea>
                        </div>
                    </div>
                </div>

                <div class="premium-card border-danger">
                    <div class="card-header-custom" style="background: #fff1f2;">
                        <i class="fas fa-box-open text-danger"></i>
                        <h5 class="text-danger">Product Integration</h5>
                    </div>
                    <div class="card-body-custom">
                        
                        <div class="product-search-box mb-2" id="product_search" style="display: {{ $single_product ? 'none' : 'block' }};">
                            <label class="form-label"><i class="fas fa-search me-1"></i> Add/Change Product</label>
                            <input type="text" id="search2" class="form-control form-control-lg" placeholder="Search by name or SKU...">
                        </div>

                        <div class="table-responsive mb-3" id="product_container" style="display: {{ $single_product ? 'block' : 'none' }};">
                            <table class="table table-bordered table-centered mb-0" id="product_table">
                                <thead class="table-light"></thead>
                                <tbody id="data">
                                    @if($single_product)
                                    <tr>
                                        <td>
                                            <img src="{{ getImage('products', $single_product->image) }}" height="50" width="50" class="rounded"/>
                                        </td>
                                        <td class="fw-bold">
                                            {{ $single_product->name }}
                                        </td>
                                        <td>{{ $single_product->sell_price }} Tk</td>
                                        <td>
                                            <a class="remove btn btn-sm btn-soft-danger"><i class="fas fa-trash"></i> Remove</a>
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

            </div>

            <div class="col-lg-4">
                
                <div class="premium-card">
                    <div class="card-header-custom">
                        <i class="fas fa-images"></i>
                        <h5>Visual Assets</h5>
                    </div>
                    <div class="card-body-custom">
                        
                        {{-- Video --}}
                        <div class="mb-4">
                            <label class="form-label">Video Embed Code (iframe)</label>
                            <input type="text" value="{{ $item->video_url }}" name="video_url" class="form-control" placeholder="<iframe>...</iframe>">
                        </div>

                        {{-- Sliders --}}
                        <div class="mb-3">
                            <label class="form-label">Slider Images</label>
                            <div class="existing-img-container">
                                @foreach ($item->images as $image)
                                    <div class="existing-img-box">
                                        <a href="{{ route('admin.delete_slider',[$image->id])}}" class="btn-delete-img" onclick="return confirm('Delete image?');">&times;</a>
                                        <img src="{{ getImage('landing_sliders',$image->image)}}">
                                    </div>
                                @endforeach
                            </div>
                            <div class="file-upload-wrapper">
                                <div class="file-upload-text">Upload New Images</div>
                                <input type="file" name="sliderimage[]" multiple class="upload-preview" data-target="slider_preview">
                            </div>
                            <div id="slider_preview" class="img-preview-container"></div>
                        </div>

                        {{-- Background (Optional) --}}
                        <div class="mb-3">
                            <label class="form-label">Background Image (Optional)</label>
                            @if($item->landing_bg)
                            <div class="existing-img-container">
                                <div class="existing-img-box">
                                    <img src="{{ asset('landing_pages/'.$item->landing_bg)}}" alt="bg">
                                </div>
                            </div>
                            @endif
                            <input type="file" name="landing_bg" class="form-control">
                        </div>

                    </div>
                </div>

                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-star"></i><h5>Reviews (Optional)</h5></div>
                    <div class="card-body-custom">
                        <div class="existing-img-container">
                            @foreach ($review_images as $rv)
                                <div class="existing-img-box">
                                    <a href="{{ route('admin.delete_review',[$rv->id])}}" class="btn-delete-img" onclick="return confirm('Delete review?');">&times;</a>
                                    <img src="{{ asset('review_landing_sliders/'.$rv->review_image) }}">
                                </div>
                            @endforeach
                        </div>
                        <input type="file" name="review_product_image[]" multiple class="form-control">
                    </div>
                </div>

                <div class="premium-card">
                    <div class="card-body-custom text-center">
                        <button type="button" id="save_btn" class="btn-save w-100">
                            <i class="fas fa-save me-2"></i> Update Page
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<script>
$(document).ready(function() {
    $('.summernote').summernote({
        height: 250,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link', 'picture']],
            ['view', ['fullscreen', 'codeview']]
        ]
    });

    // Image Preview
    $('.upload-preview').on('change', function() {
        var targetId = $(this).data('target');
        var files = this.files;
        var $container = $('#' + targetId);
        $container.empty(); 
        if (files) {
            $.each(files, function(index, file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $container.append(`<div class="preview-box"><img src="${e.target.result}"></div>`);
                }
                reader.readAsDataURL(file);
            });
        }
    });
});

// Product Search Logic
var path2 = "{{ route('admin.getOrderProduct2') }}";
const products = [];

$("#search2").autocomplete({
    selectFirst: true,
    minLength: 2,
    source: function(request, response) {
        $.ajax({
            url: path2, type: 'GET', dataType: "json", data: { search: request.term },
            success: function(data) {
                if (data.length == 0) { toastr.error('Product Not Found'); } 
                else if (data.length == 1) {
                    if (products.indexOf(data[0].id) == -1) { landingProductEntry(data[0]); products.push(data[0].id); }
                    $('#search2').val('');
                } else { response(data); }
            }
        });
    },
    select: function(event, ui) {
        if (products.indexOf(ui.item.id) == -1) { landingProductEntry(ui.item); products.push(ui.item.id); }
        $('#search2').val(''); return false;
    }
});

function landingProductEntry(item) {
    $.ajax({
        url: '{{ route("admin.landingProductEntry")}}', type: 'GET', dataType: "json", data: { id: item.id },
        success: function(res) {
            if (res.html) {
                $('div#product_container').show();
                $('tbody#data').html(res.html);
                $('#product_search').hide();
            }
            if (res.pr_id) { $('#new_product_id').val(res.pr_id); }
            
            // Auto Select Variation
            let newVarId = '';
            if (res.variations && res.variations.length > 0) { newVarId = res.variations[0].id; } 
            $('#variation_id').val(newVarId);
        }
    });
}

// Remove Product
$(document).off('click.lp_remove', '.remove').on('click.lp_remove', ".remove", function(e){
    e.preventDefault(); e.stopImmediatePropagation();
    $(this).closest("tr").remove();
    $('#product_search').show();
    $('#new_product_id').val('');
    $('#variation_id').val(''); 
});

// Save Button
$(document).off('click.lp_update', '#save_btn').on('click.lp_update', '#save_btn', function(e){
    e.preventDefault();
    const form = document.getElementById('ajax_form');
    const btn  = $('#save_btn');
    if (btn.data('submitting') === 1) return false;
    btn.data('submitting', 1);
    const originalText = btn.html();
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

    let formData = new FormData(form);
    let varId = $('#variation_id').val();
    if(varId && varId !== "") { formData.set('variation_id', varId); } else { formData.delete('variation_id'); }

    $.ajax({
        url: $(form).attr('action'), type: 'POST', data: formData, processData: false, contentType: false,
        success: function(res){
            toastr.success('Page Updated Successfully!');
            window.location.href = res.url || "{{ route('admin.landing_pages_three') }}";
        },
        error: function(xhr){
            if (xhr.status === 422 && xhr.responseJSON.errors) {
                let firstKey = Object.keys(xhr.responseJSON.errors)[0];
                toastr.error(xhr.responseJSON.errors[firstKey][0]);
            } else { toastr.error('Error occurred'); }
            btn.data('submitting', 0);
            btn.prop('disabled', false).html(originalText);
        }
    });
});
</script>
@endpush