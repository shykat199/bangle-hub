@extends('backend.app')

@section('content')
@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />

<style>
    /* Card Styling */
    .premium-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.03); margin-bottom: 24px; overflow: hidden; }
    .card-header-custom { padding: 20px 24px; border-bottom: 1px solid #e5e7eb; background: #fff; display: flex; align-items: center; gap: 10px; }
    .card-header-custom h5 { margin: 0; font-weight: 700; color: #111827; }
    .card-body-custom { padding: 24px; }
    .form-control { border-radius: 8px; border: 1px solid #d1d5db; padding: 10px 14px; }
    
    /* File Upload Area */
    .file-upload-wrapper { position: relative; border: 2px dashed #d1d5db; border-radius: 12px; padding: 20px; text-align: center; background: #f9fafb; cursor: pointer; margin-bottom: 10px; }
    .file-upload-wrapper input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
    .file-upload-text { color: #6b7280; font-weight: 500; font-size: 14px; }
    
    /* Image Preview */
    .img-preview-container { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 5px; justify-content: center; }
    .preview-box { width: 60px; height: 60px; border-radius: 6px; overflow: hidden; border: 1px solid #ddd; }
    .preview-box img { width: 100%; height: 100%; object-fit: cover; }
    
    /* ✅ Custom Save Button Style */
    .btn-save { 
        background: #000000; /* Black Background */
        color: #ffffff !important; /* White Text */
        padding: 12px 30px; 
        border-radius: 8px; 
        border: none; 
        width: 100%; 
        font-weight: 600; 
        box-shadow: 0 4px 6px rgba(0,0,0,0.3); 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        gap: 8px; 
        transition: 0.3s;
    }
    
    .btn-save:hover { 
        background: #212529; /* Slightly Lighter Black */
        transform: translateY(-2px); 
        color: #ffffff !important;
    }

    .btn-save i {
        color: #ffffff !important; /* White Icon */
    }
</style>
@endpush

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="page-title">Create Dark Page (Type 4)</h4>
            </div>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.landing_pages_four.store') }}" id="ajax_form">
        @csrf
        <input type="hidden" name="page_type" value="4">
        
        <div class="row">
            <div class="col-lg-8">
                
                {{-- Hero & Contact Info --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-info-circle"></i><h5>Hero & Contact Information</h5></div>
                    <div class="card-body-custom">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Page Title <span class="text-danger">*</span></label>
                                <input type="text" name="title1" class="form-control" required placeholder="e.g. Premium Polo Shirt Sale">
                            </div>
                            <div class="col-12">
                                <label class="form-label">URL Slug <small class="text-muted">(optional — খালি রাখলে টাইটেল থেকে বানানো হবে)</small></label>
                                <input type="text" name="slug" class="form-control" placeholder="e.g. summer-sale-2026" pattern="[a-z0-9]+(-[a-z0-9]+)*" title="শুধু ছোট হাতের অক্ষর, সংখ্যা ও হাইফেন">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Call Number (Support)</label>
                                <input type="text" name="phone" class="form-control" placeholder="017XXXXXXXX">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">WhatsApp Number</label>
                                <input type="text" name="call_text" class="form-control" placeholder="018XXXXXXXX">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sticky Button Text</label>
                                <input type="text" name="btn_text_hero" class="form-control" placeholder="অর্ডার করুন">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Form Button Text</label>
                                <input type="text" name="btn_text_form" class="form-control" placeholder="অর্ডার কনফার্ম করুন">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Content Section --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-align-left"></i><h5>Product Description</h5></div>
                    <div class="card-body-custom">
                        <div class="mb-3">
                            <label class="form-label">Description (Summernote)</label>
                            <textarea class="form-control summernote" name="left_side_desc"></textarea>
                        </div>
                    </div>
                </div>

                {{-- ✅ Dynamic Text Configuration Section --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-language text-primary"></i><h5>Dynamic Texts Configuration</h5></div>
                    <div class="card-body-custom">
                        <h6 class="text-primary border-bottom pb-2 fw-bold">Slider & Offer Section</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4"><label class="form-label">HOT Badge Text</label><input type="text" name="hot_badge_text" class="form-control" placeholder="HOT"></div>
                            <div class="col-md-4"><label class="form-label">Warranty Text</label><input type="text" name="warranty_text" class="form-control" placeholder="OFFICIAL WARRANTY"></div>
                            <div class="col-md-4"><label class="form-label">Order Button Text</label><input type="text" name="order_btn_text" class="form-control" placeholder="অর্ডার করুন"></div>
                            
                            <div class="col-md-6"><label class="form-label">Dhamaka Title</label><input type="text" name="dhamaka_title" class="form-control" placeholder="আজকের জন্য ধামাকা অফার!!!"></div>
                            <div class="col-md-6"><label class="form-label">Offer Price Label</label><input type="text" name="offer_price_label" class="form-control" placeholder="আজকের অফার প্রাইস"></div>
                            
                            <div class="col-md-6"><label class="form-label">Currency Text</label><input type="text" name="currency_text" class="form-control" placeholder="টাকা"></div>
                            <div class="col-md-6"><label class="form-label">Call Action Text</label><input type="text" name="call_to_action_text" class="form-control" placeholder="যেকোন প্রয়োজনে ফোন করুন"></div>
                        </div>

                        <h6 class="text-primary border-bottom pb-2 fw-bold">Feature & Review Section</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6"><label class="form-label">Feature Title</label><input type="text" name="feature_title" class="form-control" placeholder="প্রোডাক্টের বিশেষ সুবিধাসমূহ"></div>
                            <div class="col-md-6"><label class="form-label">Feature Button Text</label><input type="text" name="feature_btn_text" class="form-control" placeholder="অর্ডার করতে এখানে ক্লিক করুন"></div>
                            <div class="col-md-6"><label class="form-label">Details Section Title</label><input type="text" name="details_title" class="form-control" placeholder="প্রোডাক্টের বিস্তারিত"></div>
                            <div class="col-md-6"><label class="form-label">Review Section Title</label><input type="text" name="review_title" class="form-control" placeholder="কাস্টমার রিভিউ"></div>
                            <div class="col-12"><label class="form-label">Feature List (Use Bullet Points)</label><textarea class="form-control summernote" name="feature_list"></textarea></div>
                        </div>

                        <h6 class="text-primary border-bottom pb-2 fw-bold">Checkout Form Section</h6>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Form Title</label><input type="text" name="form_title" class="form-control" placeholder="অর্ডার কনফার্ম করুন"></div>
                            <div class="col-md-6"><label class="form-label">Form Subtitle</label><input type="text" name="form_subtitle" class="form-control" placeholder="ফরমটি সঠিক তথ্য দিয়ে পূরণ করে আপনার অর্ডারটি নিশ্চিত করুন"></div>
                            
                            <div class="col-md-3"><label class="form-label">Name Label</label><input type="text" name="name_label" class="form-control" placeholder="আপনার নাম *"></div>
                            <div class="col-md-3"><label class="form-label">Name Placeholder</label><input type="text" name="name_placeholder" class="form-control" placeholder="আপনার সম্পূর্ণ নাম লিখুন"></div>
                            <div class="col-md-3"><label class="form-label">Phone Label</label><input type="text" name="phone_label" class="form-control" placeholder="মোবাইল নাম্বার *"></div>
                            <div class="col-md-3"><label class="form-label">Delivery Label</label><input type="text" name="delivery_label" class="form-control" placeholder="ডেলিভারি এলাকা *"></div>
                            
                            <div class="col-md-6"><label class="form-label">Address Label</label><input type="text" name="address_label" class="form-control" placeholder="সম্পূর্ণ ঠিকানা *"></div>
                            <div class="col-md-6"><label class="form-label">Address Placeholder</label><input type="text" name="address_placeholder" class="form-control" placeholder="গ্রাম/মহল্লা, থানা, জেলা"></div>
                            
                            <div class="col-md-4"><label class="form-label">Payment Title</label><input type="text" name="payment_title" class="form-control" placeholder="পেমেন্ট মেথড নির্বাচন করুন"></div>
                            <div class="col-md-4"><label class="form-label">COD Title</label><input type="text" name="cod_title" class="form-control" placeholder="ক্যাশ অন ডেলিভারি"></div>
                            <div class="col-md-4"><label class="form-label">Online Payment Title</label><input type="text" name="online_payment_title" class="form-control" placeholder="অনলাইন পেমেন্ট"></div>
                            
                            <div class="col-md-4"><label class="form-label">Order Summary Title</label><input type="text" name="order_summary_title" class="form-control" placeholder="অর্ডারের সারাংশ"></div>
                            <div class="col-md-4"><label class="form-label">Variation Label</label><input type="text" name="variation_label" class="form-control" placeholder="সাইজ বা কালার সিলেক্ট করুন *"></div>
                            <div class="col-md-4"><label class="form-label">Total Bill Label</label><input type="text" name="total_bill_label" class="form-control" placeholder="সর্বমোট বিল:"></div>
                        </div>
                    </div>
                </div>

                {{-- Product Search --}}
                <div class="premium-card border-dark">
                    <div class="card-header-custom" style="background: #212529; color: #fff;"><i class="fas fa-box-open text-warning me-2"></i><h5 class="text-white mb-0">Product Integration</h5></div>
                    <div class="card-body-custom">
                        <div class="mb-2">
                            <input type="text" id="search2" class="form-control form-control-lg" placeholder="Search Product by Name or SKU...">
                        </div>
                        <input type="hidden" id="product_id" name="product_id">
                        <div id="data" class="mt-3"></div>
                    </div>
                </div>

            </div>

            <div class="col-lg-4">
                <div class="premium-card">
                    <div class="card-header-custom"><h5>Media Assets</h5></div>
                    <div class="card-body-custom">
                        
                        {{-- Background Image --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">Hero Background Image</label>
                            <div class="file-upload-wrapper">
                                <div class="file-upload-text"><i class="fas fa-image mb-1 d-block"></i> Upload BG Image</div>
                                <input type="file" name="landing_bg" class="upload-preview" data-target="bg_preview">
                            </div>
                            <div id="bg_preview" class="img-preview-container"></div>
                        </div>

                        {{-- Video URL --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Video Embed URL</label>
                            <input type="text" name="video_url" class="form-control" placeholder="YouTube Embed Link">
                        </div>
                        
                        {{-- Slider Images --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">Product Slider Images</label>
                            <div class="file-upload-wrapper">
                                <div class="file-upload-text"><i class="fas fa-cloud-upload-alt mb-1 d-block"></i> Upload Slider Images</div>
                                <input type="file" name="sliderimage[]" multiple class="upload-preview" data-target="slider_preview">
                            </div>
                            <div id="slider_preview" class="img-preview-container"></div>
                        </div>

                        {{-- Review Images --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">Customer Review Images</label>
                            <div class="file-upload-wrapper">
                                <div class="file-upload-text"><i class="fas fa-images mb-1 d-block"></i> Upload Review Images</div>
                                <input type="file" name="review_product_image[]" multiple class="upload-preview" data-target="review_preview">
                            </div>
                            <div id="review_preview" class="img-preview-container"></div>
                        </div>

                    </div>
                </div>
                
                {{-- ✅ SAVE BUTTON --}}
                <button type="button" id="save_btn" class="btn-save"><i class="fas fa-save"></i> Save Dark Page</button>
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
    // Summernote Initialization
    $('.summernote').summernote({ 
        height: 250, 
        placeholder: 'Write here...',
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link']],
            ['view', ['codeview']]
        ] 
    });

    // Multiple Image Preview Logic
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

    // Product Search Autocomplete
    var path = "{{ route('admin.getOrderProduct2') }}";
    $("#search2").autocomplete({
        source: function(req, res) { $.getJSON(path, {search: req.term}, res); },
        select: function(e, ui) {
            $.getJSON('{{ route("admin.landingProductEntry")}}', {id: ui.item.id}, function(res){ 
                $('div#data').html(res.html); 
                $('#product_id').val(res.pr_id); 
            });
            $(this).val(''); 
            return false;
        }
    });

    // Remove Selected Product
    $(document).on('click', '.remove-product', function() { 
        $(this).closest('tr').remove(); 
        $('#product_id').val(''); 
    });

    // Ajax Form Submission
    $('#save_btn').click(function(e){
        e.preventDefault();
        let form = $('#ajax_form')[0];
        let btn = $(this); 
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        
        $.ajax({
            url: $(form).attr('action'), 
            type: 'POST', 
            data: new FormData(form), 
            processData: false, 
            contentType: false,
            success: function(res){ 
                toastr.success('Landing Page Created successfully!'); 
                window.location.href = res.url; 
            },
            error: function(xhr){ 
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Dark Page');
                if(xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        toastr.error(value[0]);
                    });
                } else {
                    toastr.error('Something went wrong. Please try again.');
                }
            }
        });
    });
});
</script>
@endpush