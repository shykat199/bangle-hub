@extends('backend.app')

@section('content')
@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
<style>
    .premium-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.03); margin-bottom: 24px; overflow: hidden; }
    .card-header-custom { padding: 20px 24px; border-bottom: 1px solid #e5e7eb; background: #fff; display: flex; align-items: center; gap: 10px; color: #0f766e; }
    .card-body-custom { padding: 24px; }
    .form-control:focus { border-color: #0d9488; box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1); }
    .file-upload-wrapper { position: relative; border: 2px dashed #d1d5db; border-radius: 12px; padding: 20px; text-align: center; background: #f0fdf4; cursor: pointer; transition: 0.3s; }
    .file-upload-wrapper:hover { border-color: #0d9488; }
    .img-preview-container { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 15px; justify-content: center; }
    .preview-box { width: 80px; height: 80px; border-radius: 8px; overflow: hidden; border: 1px solid #ddd; }
    .preview-box img { width: 100%; height: 100%; object-fit: cover; }
    .btn-save { background: #0d9488; color: white; padding: 12px 30px; border-radius: 8px; border: none; width: 100%; font-weight: 600; transition: 0.3s; font-size: 16px; }
    .btn-save:hover { background: #0f766e; }
    #product_table td small, #product_table td .text-muted { display: none !important; }
    .section-title { font-size: 1.1rem; font-weight: 700; color: #374151; margin-bottom: 15px; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px; }
</style>
@endpush

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="page-title">Create Custom Page (Type 5)</h4>
                <div class="page-title-right">
                    <a href="{{ route('admin.landing_pages_five') }}" class="btn btn-sm btn-secondary">Back to List</a>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.landing_pages_five.store') }}" id="ajax_form">
        @csrf
        <div class="row">
            {{-- LEFT COLUMN --}}
            <div class="col-lg-8">
                
                {{-- 1. BASIC INFORMATION --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-info-circle"></i><h5 class="m-0 fw-bold">Basic Information</h5></div>
                    <div class="card-body-custom">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Page Title *</label>
                                <input type="text" name="title1" class="form-control" required placeholder="e.g. Premium Honey Offer">
                            </div>
                            <div class="col-12">
                                <label class="form-label">URL Slug <small class="text-muted">(optional — খালি রাখলে টাইটেল থেকে বানানো হবে)</small></label>
                                <input type="text" name="slug" class="form-control" placeholder="e.g. summer-sale-2026" pattern="[a-z0-9]+(-[a-z0-9]+)*" title="শুধু ছোট হাতের অক্ষর, সংখ্যা ও হাইফেন">
                                <input type="hidden" name="page_type" value="5">
                            </div>
                            <div class="col-md-6"><label class="form-label">Phone Number</label><input type="text" name="phone" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label">WhatsApp Number</label><input type="text" name="call_text" class="form-control"></div>
                        </div>
                    </div>
                </div>

                {{-- 2. HERO & COUNTDOWN --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-clock"></i><h5 class="m-0 fw-bold">Hero & Countdown</h5></div>
                    <div class="card-body-custom">
                        <div class="row g-3">
                            <div class="col-md-12"><label class="form-label">Countdown Title</label><input type="text" name="countdown_title" class="form-control" value="অফারটি শেষ হতে আর বাকি মাত্র"></div>
                            <div class="col-md-4"><label class="form-label">Countdown Hours</label><input type="number" name="countdown_hours" class="form-control" value="5"></div>
                            <div class="col-md-4"><label class="form-label">Countdown BG Color</label><input type="color" name="countdown_bg_color" class="form-control form-control-color w-100" value="#e67e22"></div>
                            <div class="col-md-4"><label class="form-label">Countdown Text Color</label><input type="color" name="countdown_text_color" class="form-control form-control-color w-100" value="#ffffff"></div>
                            
                            <div class="col-md-6"><label class="form-label">Hero Button BG</label><input type="color" name="hero_btn_bg_color" class="form-control form-control-color w-100" value="#d35400"></div>
                            <div class="col-md-6"><label class="form-label">Hero Button Text Color</label><input type="color" name="hero_btn_text_color" class="form-control form-control-color w-100" value="#ffffff"></div>
                        </div>
                    </div>
                </div>

                {{-- 3. PRICE & PROMISES (Texts Removed) --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-tags"></i><h5 class="m-0 fw-bold">Price & Promises</h5></div>
                    <div class="card-body-custom">
                        
                        {{-- Removed Price Texts Section Here --}}

                        <h6 class="section-title">Promises Header</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4"><label class="form-label">Badge Text</label><input type="text" name="promise_badge" class="form-control" value="কেন আমাদের প্রোডাক্ট সেরা?"></div>
                            <div class="col-md-4"><label class="form-label">Section Title</label><input type="text" name="promise_title" class="form-control" value="আমাদের প্রতিশ্রুতি"></div>
                            <div class="col-md-4"><label class="form-label">Image Badge</label><input type="text" name="promise_img_badge" class="form-control" value="১০০% খাঁটি"></div>
                        </div>

                        <h6 class="section-title">Promise Details (3 Boxes)</h6>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Promise 1</label>
                                <input type="text" name="promise_1_title" class="form-control mb-2" placeholder="Title">
                                <textarea name="promise_1_desc" class="form-control" rows="2" placeholder="Description"></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Promise 2</label>
                                <input type="text" name="promise_2_title" class="form-control mb-2" placeholder="Title">
                                <textarea name="promise_2_desc" class="form-control" rows="2" placeholder="Description"></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Promise 3</label>
                                <input type="text" name="promise_3_title" class="form-control mb-2" placeholder="Title">
                                <textarea name="promise_3_desc" class="form-control" rows="2" placeholder="Description"></textarea>
                            </div>
                        </div>

                        <h6 class="section-title mt-4">Negative List (আমাদের পণ্যে যা নেই)</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Negative Title</label>
                                <input type="text" value="আমাদের পণ্যে যা নেই:" name="negative_title" class="form-control">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Negative Tags (Comma Separated)</label>
                                <input type="text" value="কোনো কেমিক্যাল, কোনো ভেজাল, কৃত্রিম রং" name="negative_tags" class="form-control" placeholder="যেমন: কোনো কেমিক্যাল, কোনো ভেজাল">
                            </div>
                        </div>

                    </div>
                </div>

                {{-- 4. IDENTIFY & TRUST BADGES --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-shield-alt"></i><h5 class="m-0 fw-bold">Identify & Trust</h5></div>
                    <div class="card-body-custom">
                        <h6 class="section-title">Identify Product Section</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4"><label class="form-label">Badge</label><input type="text" name="identify_badge" class="form-control" value="খাঁটি প্রোডাক্ট চেনার উপায়"></div>
                            <div class="col-md-4"><label class="form-label">Title</label><input type="text" name="identify_title" class="form-control" value="আসল পণ্য চেনার উপায়"></div>
                            <div class="col-md-4"><label class="form-label">Subtitle</label><input type="text" name="identify_subtitle" class="form-control" value="এই লক্ষণগুলো দেখলেই বুঝবেন পণ্য খাঁটি কিনা"></div>
                        </div>

                        <h6 class="section-title">Trust Badges (Footer)</h6>
                        <div class="mb-3"><label class="form-label">Section Title</label><input type="text" name="trust_sec_title" class="form-control" value="ঝুঁকিমুক্ত অর্ডার করুন"></div>
                        <div class="row g-3">
                            <div class="col-md-3"><input type="text" name="trust_1_icon" class="form-control mb-1" placeholder="Icon (fa-thumbs-up)" value="fa-thumbs-up"><input type="text" name="trust_1_title" class="form-control" placeholder="Title"></div>
                            <div class="col-md-3"><input type="text" name="trust_2_icon" class="form-control mb-1" placeholder="Icon (fa-hand-holding-usd)" value="fa-hand-holding-usd"><input type="text" name="trust_2_title" class="form-control" placeholder="Title"></div>
                            <div class="col-md-3"><input type="text" name="trust_3_icon" class="form-control mb-1" placeholder="Icon (fa-truck-fast)" value="fa-truck-fast"><input type="text" name="trust_3_title" class="form-control" placeholder="Title"></div>
                            <div class="col-md-3"><input type="text" name="trust_4_icon" class="form-control mb-1" placeholder="Icon (fa-headset)" value="fa-headset"><input type="text" name="trust_4_title" class="form-control" placeholder="Title"></div>
                        </div>
                    </div>
                </div>

                {{-- 5. FEATURES & REVIEW SECTION --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-list"></i><h5 class="m-0 fw-bold">Features & Reviews</h5></div>
                    <div class="card-body-custom">
                        <h6 class="section-title">Review Section Headers</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4"><label class="form-label">Badge</label><input type="text" name="review_badge" class="form-control" value="গ্রাহকদের মতামত"></div>
                            <div class="col-md-4"><label class="form-label">Title</label><input type="text" name="review_title" class="form-control" value="গ্রাহকদের মতামত"></div>
                            <div class="col-md-4"><label class="form-label">Subtitle</label><input type="text" name="review_subtitle" class="form-control" value="হাজারো সন্তুষ্ট পরিবার আমাদের সাথে আছে"></div>
                        </div>

                        <h6 class="section-title">Statistics (3 Items)</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4"><input type="text" name="stat_1_num" class="form-control mb-1" placeholder="Number (e.g. 1500+)"><input type="text" name="stat_1_text" class="form-control" placeholder="Text (Happy Customer)"></div>
                            <div class="col-md-4"><input type="text" name="stat_2_num" class="form-control mb-1" placeholder="Number"><input type="text" name="stat_2_text" class="form-control" placeholder="Text"></div>
                            <div class="col-md-4"><input type="text" name="stat_3_num" class="form-control mb-1" placeholder="Number"><input type="text" name="stat_3_text" class="form-control" placeholder="Text"></div>
                        </div>

                        <h6 class="section-title">Customer Reviews (4 Boxes)</h6>
                        @for($i=1; $i<=4; $i++)
                        <div class="mb-3 border p-3 rounded">
                            <label class="fw-bold">Review {{ $i }}</label>
                            <input type="text" name="rev_{{ $i }}_name" class="form-control mb-2" placeholder="Customer Name">
                            <input type="text" name="rev_{{ $i }}_loc" class="form-control mb-2" placeholder="Location">
                            <textarea name="rev_{{ $i }}_text" class="form-control" rows="2" placeholder="Review Text"></textarea>
                        </div>
                        @endfor
                    </div>
                </div>

                {{-- 6. FAQ SECTION --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-question-circle"></i><h5 class="m-0 fw-bold">FAQ Section</h5></div>
                    <div class="card-body-custom">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6"><label class="form-label">Badge</label><input type="text" name="faq_badge" class="form-control" value="জিজ্ঞাসা"></div>
                            <div class="col-md-6"><label class="form-label">Title</label><input type="text" name="faq_title" class="form-control" value="সচরাচর জিজ্ঞাসিত প্রশ্ন (FAQ)"></div>
                        </div>
                        @for($i=1; $i<=4; $i++)
                        <div class="mb-3">
                            <input type="text" name="faq_{{ $i }}_q" class="form-control mb-2 fw-bold" placeholder="Question {{ $i }}">
                            <textarea name="faq_{{ $i }}_a" class="form-control" rows="2" placeholder="Answer {{ $i }}"></textarea>
                        </div>
                        @endfor
                    </div>
                </div>
                
                {{-- 7. TOP HEADLINE & DESC --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-heading"></i><h5 class="m-0 fw-bold">Main Content Area</h5></div>
                    <div class="card-body-custom">
                        <div class="mb-3"><label class="form-label">Top Headline</label><input type="text" name="left_side_title" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Main Description</label><textarea class="form-control summernote" name="left_side_desc"></textarea></div>
                    </div>
                </div>

                {{-- 8. PRODUCT INTEGRATION --}}
                <div class="premium-card" style="border: 1px solid #14b8a6;">
                    <div class="card-header-custom" style="background: #f0fdfa;"><i class="fas fa-box-open"></i><h5 class="m-0 fw-bold">Product Integration</h5></div>
                    <div class="card-body-custom">
                        <div id="product_search">
                            <input type="text" id="search2" class="form-control form-control-lg" placeholder="Search Product by Name/SKU...">
                        </div>
                        <input type="hidden" id="product_id" name="product_id">
                        <input type="hidden" id="variation_id" name="variation_id">
                        
                        <div class="table-responsive mt-3" id="product_container" style="display: none;">
                            <table class="table table-bordered table-centered mb-0" id="product_table">
                                <tbody id="data"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN --}}
            <div class="col-lg-4">
                {{-- 9. VISUAL ASSETS --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-images"></i><h5 class="m-0 fw-bold">Visual Assets</h5></div>
                    <div class="card-body-custom">
                        <div class="mb-4">
                            <label class="form-label">Video Embed Code (iframe)</label>
                            <input type="text" name="video_url" class="form-control" placeholder="<iframe>...</iframe>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Slider Images</label>
                            <div class="file-upload-wrapper">
                                <div class="file-upload-text">Upload Images</div>
                                <input type="file" name="sliderimage[]" multiple class="upload-preview" data-target="slider_preview" style="position:absolute; top:0; left:0; width:100%; height:100%; opacity:0; cursor:pointer;">
                            </div>
                            <div id="slider_preview" class="img-preview-container"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Background Image</label>
                            <input type="file" name="landing_bg" class="form-control">
                        </div>
                    </div>
                </div>

                {{-- 10. ICON FEATURES (1-8) --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-star"></i><h5 class="m-0 fw-bold">Icon Features</h5></div>
                    <div class="card-body-custom" style="max-height: 600px; overflow-y: auto;">
                        @for($i=1; $i<=8; $i++)
                        <div class="mb-3 border-bottom pb-3">
                            <label class="fw-bold text-muted small">Feature {{ $i }}</label>
                            <input type="text" name="id_{{ $i }}_icon" class="form-control mb-1 form-control-sm" placeholder="Icon (e.g. fa-star)">
                            <input type="text" name="id_{{ $i }}_title" class="form-control mb-1 form-control-sm" placeholder="Title">
                            <textarea name="id_{{ $i }}_desc" class="form-control form-control-sm" rows="2" placeholder="Description"></textarea>
                        </div>
                        @endfor
                    </div>
                </div>

                <button type="submit" id="save_btn" class="btn-save mt-3"><i class="fas fa-save me-2"></i> Save & Publish</button>
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
        $('.summernote').summernote({ height: 200 });

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
        
        // Product Search Logic
        var path2 = "{{ route('admin.getOrderProduct2') }}";
        const products = [];

        $("#search2").autocomplete({
            selectFirst: true, minLength: 2,
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
                    if (res.pr_id) { $('#product_id').val(res.pr_id); }
                    let newVarId = '';
                    if (res.variations && res.variations.length > 0) { newVarId = res.variations[0].id; } 
                    $('#variation_id').val(newVarId);
                }
            });
        }

        // Remove Product
        $(document).off('click.lp_remove', '.remove-product').on('click.lp_remove', ".remove-product", function(e){
            e.preventDefault(); e.stopImmediatePropagation();
            $(this).closest("tr").remove();
            $('#product_search').show();
            $('#product_id').val('');
            $('#variation_id').val(''); 
            products.pop();
        });

        // ✅ FIX: Changed event to form submit instead of button click
        $(document).off('submit', '#ajax_form').on('submit', '#ajax_form', function(e){
            e.preventDefault();
            
            const form = this;
            const btn  = $('#save_btn');
            
            if (btn.data('submitting') === 1) return false;
            btn.data('submitting', 1);
            const originalText = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            let formData = new FormData(form);
            
            $.ajax({
                url: $(form).attr('action'), 
                type: 'POST', 
                data: formData, 
                processData: false, 
                contentType: false,
                success: function(res) { 
                    toastr.success('Saved Successfully!'); 
                    window.location.href = res.url || "{{ route('admin.landing_pages_five') }}"; 
                },
                error: function(err) { 
                    toastr.error('Error Occurred! Please check required fields.');
                    btn.data('submitting', 0);
                    btn.prop('disabled', false).html(originalText);
                }
            });
        });
    });
</script>
@endpush