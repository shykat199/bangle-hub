@extends('backend.app')

@section('content')
@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />

<style>
    /* Card & Header Formatting */
    .premium-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); border: 1px solid rgba(0,0,0,0.03); margin-bottom: 1.5rem; overflow: hidden; }
    .card-header-custom { padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 10px; background-color: #f8f9fa; }
    .card-header-custom h5 { margin: 0; font-weight: 700; color: #343a40; font-size: 1.1rem; }
    .card-header-custom i { color: #6c757d; }
    .card-body-custom { padding: 1.5rem; }
    
    /* Form Elements */
    .form-control { border-radius: 8px; border: 1px solid #d1d5db; padding: 0.6rem 1rem; font-size: 0.9rem; }
    label { font-weight: 600; color: #495057; font-size: 0.85rem; margin-bottom: 0.3rem; }
    
    /* Section Headings */
    .text-primary.border-bottom { color: #D31A50 !important; font-size: 1rem; margin-top: 1rem; margin-bottom: 1.5rem !important; border-color: rgba(211, 26, 80, 0.2) !important; }
    
    /* File Upload & Preview */
    .existing-img-box { position: relative; display: inline-block; margin: 5px; border: 1px solid #ddd; padding: 3px; border-radius: 5px; background: #fff; }
    .existing-img-box img { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; }
    .btn-delete-img { position: absolute; top: -8px; right: -8px; background: #fa5c7c; color: white; border-radius: 50%; width: 20px; height: 20px; text-align: center; font-size: 12px; line-height: 20px; cursor: pointer; text-decoration: none; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
    .btn-delete-img:hover { background: #d31a50; color: white; }
    
    .file-upload-wrapper { position: relative; border: 2px dashed #d1d5db; border-radius: 12px; padding: 15px; text-align: center; background: #f9fafb; cursor: pointer; margin-bottom: 10px; transition: all 0.3s; }
    .file-upload-wrapper:hover { border-color: #6c757d; background: #f1f3fa; }
    .file-upload-wrapper input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
    .file-upload-text { color: #6b7280; font-weight: 500; font-size: 0.85rem; }
    .img-preview-container { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 5px; justify-content: center; }
    .preview-box { width: 60px; height: 60px; border-radius: 6px; overflow: hidden; border: 1px solid #ddd; padding: 2px; background: #fff; }
    .preview-box img { width: 100%; height: 100%; object-fit: cover; border-radius: 4px; }
    
    /* ✅ Custom Black Button with STRICT White Text */
    .btn-dark-custom { 
        background-color: #000000 !important; /* Pure Black */
        color: #ffffff !important; /* Pure White Text */
        border: none; 
        font-weight: 600; 
        padding: 12px 20px; 
        border-radius: 8px; 
        width: 100%; 
        transition: 0.3s; 
        display: inline-flex; 
        align-items: center; 
        justify-content: center; 
        gap: 8px; 
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .btn-dark-custom:hover { 
        background-color: #333333 !important; /* Slightly lighter on hover */
        transform: translateY(-2px); 
        box-shadow: 0 6px 12px rgba(0,0,0,0.2); 
        color: #ffffff !important;
    }
    .btn-dark-custom i { 
        color: #ffffff !important; /* Icon White */
    }

    /* Layout adjustments */
    .row.g-3 { --bs-gutter-y: 1rem; --bs-gutter-x: 1rem; }
    .mb-4 { margin-bottom: 1.5rem !important; }

    /* MOBILE RESPONSIVE FIXES */
    @media (max-width: 768px) {
        .card-header-custom { padding: 0.8rem 1rem; }
        .card-body-custom { padding: 1rem; }
        .form-control { padding: 0.5rem 0.8rem; }
        .row.g-3 { --bs-gutter-y: 0.8rem; }
    }
</style>
@endpush

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h4 class="page-title py-3">Edit Dark Page (Type 4)</h4>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.landing_pages_four.update', [$item->id]) }}" id="ajax_form">
        @csrf @method('PATCH')
        <input type="hidden" name="page_type" value="4">
        
        <div class="row">
            <div class="col-lg-8">
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-edit"></i><h5>Basic Info & Support</h5></div>
                    <div class="card-body-custom">
                        <div class="row g-3">
                            <div class="col-12">
                                <label>Page Title *</label>
                                <input type="text" name="title1" value="{{ $item->title1 }}" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">URL Slug <small class="text-muted">(optional — খালি রাখলে আগেরটাই থাকবে)</small></label>
                                <input type="text" name="slug" value="{{ $item->slug }}" class="form-control" placeholder="e.g. summer-sale-2026" pattern="[a-z0-9]+(-[a-z0-9]+)*" title="শুধু ছোট হাতের অক্ষর, সংখ্যা ও হাইফেন">
                                <input type="hidden" name="product_id" id="product_id" value="{{ $item->product_id }}">
                                <input type="hidden" id="new_product_id" name="new_product_id">
                                <input type="hidden" name="variation_id" id="variation_id" value="{{ $item->variation_id }}">
                            </div>
                            <div class="col-md-6">
                                <label>Sticky Button Text</label>
                                <input type="text" name="btn_text_hero" value="{{ $item->btn_text_hero }}" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>Video Button Text</label>
                                <input type="text" name="btn_text_video" value="{{ $item->btn_text_video }}" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>Call Number (Support)</label>
                                <input type="text" name="phone" value="{{ $item->phone }}" class="form-control" placeholder="017XXXXXXXX">
                            </div>
                            <div class="col-md-6">
                                <label>WhatsApp Number</label>
                                <input type="text" name="call_text" value="{{ $item->call_text }}" class="form-control" placeholder="018XXXXXXXX">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-align-left"></i><h5>Content</h5></div>
                    <div class="card-body-custom">
                        <div class="mb-3"><label>Top Heading</label><input type="text" name="left_side_title" value="{{ $item->left_side_title }}" class="form-control"></div>
                        <div class="mb-3"><label>Description</label><textarea class="summernote" name="left_side_desc">{!! $item->left_side_desc !!}</textarea></div>
                    </div>
                </div>

                {{-- ✅ Dynamic Text Configuration Section (Updated) --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-language"></i><h5>Dynamic Texts Configuration</h5></div>
                    <div class="card-body-custom">
                        <h6 class="text-primary border-bottom pb-2 fw-bold">Slider & Offer Section</h6>
                        <div class="row g-3 mb-4">
                            {{-- Adjusted Columns to fill space (Removed Offer Price Text) --}}
                            <div class="col-md-4"><label class="form-label">HOT Badge Text</label><input type="text" name="hot_badge_text" value="{{ $item->hot_badge_text }}" class="form-control" placeholder="HOT"></div>
                            <div class="col-md-4"><label class="form-label">Warranty Text</label><input type="text" name="warranty_text" value="{{ $item->warranty_text }}" class="form-control" placeholder="OFFICIAL WARRANTY"></div>
                            <div class="col-md-4"><label class="form-label">Order Button Text</label><input type="text" name="order_btn_text" value="{{ $item->order_btn_text }}" class="form-control" placeholder="অর্ডার করুন"></div>
                            
                            {{-- Adjusted Columns to fill space (Removed Regular Price Label) --}}
                            <div class="col-md-6"><label class="form-label">Dhamaka Title</label><input type="text" name="dhamaka_title" value="{{ $item->dhamaka_title }}" class="form-control" placeholder="আজকের জন্য ধামাকা অফার!!!"></div>
                            <div class="col-md-6"><label class="form-label">Offer Price Label</label><input type="text" name="offer_price_label" value="{{ $item->offer_price_label }}" class="form-control" placeholder="আজকের অফার প্রাইস"></div>
                            
                            <div class="col-md-6"><label class="form-label">Currency Text</label><input type="text" name="currency_text" value="{{ $item->currency_text }}" class="form-control" placeholder="টাকা"></div>
                            <div class="col-md-6"><label class="form-label">Call Action Text</label><input type="text" name="call_to_action_text" value="{{ $item->call_to_action_text }}" class="form-control" placeholder="যেকোন প্রয়োজনে ফোন করুন"></div>
                        </div>

                        <h6 class="text-primary border-bottom pb-2 fw-bold">Feature & Review Section</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6"><label class="form-label">Feature Title</label><input type="text" name="feature_title" value="{{ $item->feature_title }}" class="form-control" placeholder="প্রোডাক্টের বিশেষ সুবিধাসমূহ"></div>
                            <div class="col-md-6"><label class="form-label">Feature Button Text</label><input type="text" name="feature_btn_text" value="{{ $item->feature_btn_text }}" class="form-control" placeholder="অর্ডার করতে এখানে ক্লিক করুন"></div>
                            <div class="col-md-6"><label class="form-label">Details Section Title</label><input type="text" name="details_title" value="{{ $item->details_title }}" class="form-control" placeholder="প্রোডাক্টের বিস্তারিত"></div>
                            <div class="col-md-6"><label class="form-label">Review Section Title</label><input type="text" name="review_title" value="{{ $item->review_title }}" class="form-control" placeholder="কাস্টমার রিভিউ"></div>
                            <div class="col-12"><label class="form-label">Feature List (Use Bullet Points)</label><textarea class="form-control summernote" name="feature_list">{!! $item->feature_list !!}</textarea></div>
                        </div>

                        <h6 class="text-primary border-bottom pb-2 fw-bold">Checkout Form Section</h6>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Form Title</label><input type="text" name="form_title" value="{{ $item->form_title }}" class="form-control" placeholder="অর্ডার কনফার্ম করুন"></div>
                            <div class="col-md-6"><label class="form-label">Form Subtitle</label><input type="text" name="form_subtitle" value="{{ $item->form_subtitle }}" class="form-control" placeholder="ফরমটি সঠিক তথ্য দিয়ে পূরণ করে আপনার অর্ডারটি নিশ্চিত করুন"></div>
                            
                            <div class="col-md-3"><label class="form-label">Name Label</label><input type="text" name="name_label" value="{{ $item->name_label }}" class="form-control" placeholder="আপনার নাম *"></div>
                            <div class="col-md-3"><label class="form-label">Name Placeholder</label><input type="text" name="name_placeholder" value="{{ $item->name_placeholder }}" class="form-control" placeholder="আপনার সম্পূর্ণ নাম লিখুন"></div>
                            <div class="col-md-3"><label class="form-label">Phone Label</label><input type="text" name="phone_label" value="{{ $item->phone_label }}" class="form-control" placeholder="মোবাইল নাম্বার *"></div>
                            <div class="col-md-3"><label class="form-label">Delivery Label</label><input type="text" name="delivery_label" value="{{ $item->delivery_label }}" class="form-control" placeholder="ডেলিভারি এলাকা *"></div>
                            
                            <div class="col-md-6"><label class="form-label">Address Label</label><input type="text" name="address_label" value="{{ $item->address_label }}" class="form-control" placeholder="সম্পূর্ণ ঠিকানা *"></div>
                            <div class="col-md-6"><label class="form-label">Address Placeholder</label><input type="text" name="address_placeholder" value="{{ $item->address_placeholder }}" class="form-control" placeholder="গ্রাম/মহল্লা, থানা, জেলা"></div>
                            
                            <div class="col-md-4"><label class="form-label">Payment Title</label><input type="text" name="payment_title" value="{{ $item->payment_title }}" class="form-control" placeholder="পেমেন্ট মেথড নির্বাচন করুন"></div>
                            <div class="col-md-4"><label class="form-label">COD Title</label><input type="text" name="cod_title" value="{{ $item->cod_title }}" class="form-control" placeholder="ক্যাশ অন ডেলিভারি"></div>
                            <div class="col-md-4"><label class="form-label">Online Payment Title</label><input type="text" name="online_payment_title" value="{{ $item->online_payment_title }}" class="form-control" placeholder="অনলাইন পেমেন্ট"></div>
                            
                            <div class="col-md-4"><label class="form-label">Order Summary Title</label><input type="text" name="order_summary_title" value="{{ $item->order_summary_title }}" class="form-control" placeholder="অর্ডারের সারাংশ"></div>
                            <div class="col-md-4"><label class="form-label">Variation Label</label><input type="text" name="variation_label" value="{{ $item->variation_label }}" class="form-control" placeholder="সাইজ বা কালার সিলেক্ট করুন *"></div>
                            <div class="col-md-4"><label class="form-label">Total Bill Label</label><input type="text" name="total_bill_label" value="{{ $item->total_bill_label }}" class="form-control" placeholder="সর্বমোট বিল:"></div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-lg-4">
                <div class="premium-card border-dark">
                    <div class="card-header-custom" style="background: #212529; color:#fff; border-bottom: none;"><i class="fas fa-box-open text-warning"></i><h5 class="text-white">Product Integration</h5></div>
                    <div class="card-body-custom pt-0">
                        <input type="text" id="search2" class="form-control mt-3" placeholder="Change Product...">
                        <div id="data" class="mt-3">
                            @if($single_product)
                            <div class="alert alert-dark d-flex justify-content-between align-items-center mb-0 p-2">
                                <span style="font-size: 0.85rem;">Current: <b>{{ $single_product->name }}</b></span>
                                <a class="btn btn-sm btn-danger remove-product text-white py-0 px-2"><i class="fas fa-times"></i></a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-images"></i><h5>Media Assets</h5></div>
                    <div class="card-body-custom">
                        
                        <div class="mb-4">
                            <label>Background Image (Hero Section)</label>
                            @if($item->landing_bg)
                            <div class="mb-2 text-center bg-light p-2 rounded">
                                <img src="{{ asset('landing_pages/'.$item->landing_bg) }}" style="max-width: 100%; max-height: 100px; object-fit: contain;">
                            </div>
                            @endif
                            <input type="file" name="landing_bg" class="form-control">
                        </div>

                        <div class="mb-4">
                            <label>Video URL</label>
                            <input type="text" name="video_url" value="{{ $item->video_url }}" class="form-control">
                        </div>
                        
                        {{-- Slider Images Section --}}
                        <div class="mb-4">
                            <label>Product Slider Images</label>
                            @if($item->images->count() > 0)
                            <div class="mb-2 bg-light p-2 rounded text-center">
                                @foreach($item->images as $img)
                                    <div class="existing-img-box">
                                        <a href="{{ route('admin.delete_slider',[$img->id])}}" class="btn-delete-img" onclick="return confirm('Delete?');" title="Delete Image"><i class="fas fa-times"></i></a>
                                        <img src="{{ getImage('landing_sliders',$img->image)}}">
                                    </div>
                                @endforeach
                            </div>
                            @endif
                            <div class="file-upload-wrapper">
                                <div class="file-upload-text"><i class="fas fa-cloud-upload-alt mb-1 d-block"></i> Upload New Slider Images</div>
                                <input type="file" name="sliderimage[]" multiple class="upload-preview" data-target="slider_preview">
                            </div>
                            <div id="slider_preview" class="img-preview-container"></div>
                        </div>

                        {{-- Review Images Section --}}
                        <div class="mb-3">
                            <label>Customer Review Images</label>
                            @if(isset($review_images) && $review_images->count() > 0)
                            <div class="mb-2 bg-light p-2 rounded text-center">
                                @foreach($review_images as $review)
                                    <div class="existing-img-box">
                                        <a href="{{ route('admin.delete_review',[$review->id])}}" class="btn-delete-img" onclick="return confirm('Delete?');" title="Delete Image"><i class="fas fa-times"></i></a>
                                        <img src="{{ asset('review_landing_sliders/'.$review->review_image) }}">
                                    </div>
                                @endforeach
                            </div>
                            @endif
                            <div class="file-upload-wrapper">
                                <div class="file-upload-text"><i class="fas fa-images mb-1 d-block"></i> Upload New Review Images</div>
                                <input type="file" name="review_product_image[]" multiple class="upload-preview" data-target="review_preview">
                            </div>
                            <div id="review_preview" class="img-preview-container"></div>
                        </div>

                    </div>
                </div>
                
                {{-- ✅ SAVE BUTTON (Black with White Text) --}}
                <button type="button" id="save_btn" class="btn-dark-custom"><i class="fas fa-save"></i> Update Dark Page</button>
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
    $('.summernote').summernote({ height: 150 });

    // Live Image Preview Function
    $('.upload-preview').on('change', function() {
        var targetId = $(this).data('target'); 
        var files = this.files; 
        var $container = $('#' + targetId); 
        $container.empty();
        if (files) { 
            $.each(files, function(i, file) { 
                var reader = new FileReader(); 
                reader.onload = function(e) { 
                    $container.append(`<div class="preview-box"><img src="${e.target.result}"></div>`); 
                }; 
                reader.readAsDataURL(file); 
            }); 
        }
    });

    // Product Autocomplete
    var path2 = "{{ route('admin.getOrderProduct2') }}";
    $("#search2").autocomplete({
        source: function(req, res) { $.getJSON(path2, {search: req.term}, res); },
        select: function(e, ui) {
            $.getJSON('{{ route("admin.landingProductEntry")}}', {id: ui.item.id}, function(res){
                $('div#data').html(res.html); $('#new_product_id').val(res.pr_id);
                if(res.variations.length > 0) $('#variation_id').val(res.variations[0].id);
            });
            $(this).val(''); return false;
        }
    });

    $(document).on('click', '.remove-product', function(){ 
        $('#data').empty(); $('#product_id').val(''); $('#new_product_id').val(''); $('#variation_id').val(''); 
    });

    // Save Action
    $('#save_btn').click(function(e){
        e.preventDefault();
        let form = $('#ajax_form')[0];
        let btn = $(this); btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
        $.ajax({
            url: $(form).attr('action'), type: 'POST', data: new FormData(form), processData:false, contentType:false,
            success: function(res){ toastr.success('Updated!'); window.location.href = res.url || "{{ route('admin.landing_pages_four') }}"; },
            error: function(){ toastr.error('Error!'); btn.prop('disabled', false).html('<i class="fas fa-save"></i> Update Dark Page'); }
        });
    });
});
</script>
@endpush