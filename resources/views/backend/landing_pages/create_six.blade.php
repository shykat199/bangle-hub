@extends('backend.app')
@section('title', 'Create Landing Page (Type 6)')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />

<style>
    .premium-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.03); margin-bottom: 24px; overflow: hidden; }
    .card-header-custom { padding: 20px 24px; border-bottom: 1px solid #e5e7eb; background: #fff; display: flex; align-items: center; gap: 10px; }
    .card-header-custom h5 { margin: 0; font-weight: 700; color: #111827; }
    .card-body-custom { padding: 24px; }
    .form-control { border-radius: 8px; border: 1px solid #d1d5db; padding: 10px 14px; width: 100%; box-sizing: border-box; }
    .form-control:focus { border-color: #f59e0b; box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1); }
    .file-upload-wrapper { position: relative; border: 2px dashed #d1d5db; border-radius: 12px; padding: 20px; text-align: center; background: #f9fafb; cursor: pointer; margin-bottom: 10px; transition: 0.3s; }
    .file-upload-wrapper:hover { border-color: #f59e0b; }
    .file-upload-wrapper input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
    .file-upload-text { color: #d97706; font-weight: 600; font-size: 14px; }
    
    /* ✅ FIXED: Image Preview Responsiveness */
    .img-preview-container { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px; justify-content: flex-start; }
    .preview-box { width: 80px; height: 80px; border-radius: 8px; overflow: hidden; border: 2px solid #e5e7eb; position: relative; background: #fff; display: flex; align-items: center; justify-content: center;}
    .preview-box img { width: 100%; height: 100%; object-fit: cover; }
    
    .btn-save { background: #22c55e; color: #ffffff !important; padding: 12px 30px; border-radius: 8px; border: none; width: 100%; font-weight: 600; box-shadow: 0 4px 6px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.3s; }
    .btn-save:hover { background: #16a34a; transform: translateY(-2px); color: #ffffff !important; }
    .btn-save i { color: #ffffff !important; }
    #product_table td small, #product_table td .text-muted { display: none !important; }

    /* ✅ MOBILE RESPONSIVE FIXES */
    @media (max-width: 768px) {
        .card-body-custom { padding: 15px; }
        .card-header-custom { padding: 15px; }
        .card-header-custom h5 { font-size: 16px; }
        .preview-box { width: 60px; height: 60px; }
        .file-upload-wrapper { padding: 15px; }
    }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between mb-3">
                    <h4 class="page-title fw-bold text-dark">Create Landing Page (Type 6)</h4>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.landing_pages_six.store') }}" method="POST" enctype="multipart/form-data" id="ajax_form">
            @csrf
            <input type="hidden" name="page_type" value="6">
            
            <div class="row mt-3">
                {{-- ================== LEFT COLUMN ================== --}}
                <div class="col-lg-8">
                    
                    {{-- ১. বেসিক ইনফরমেশন --}}
                    <div class="premium-card">
                        <div class="card-header-custom border-bottom-0"><i class="fas fa-heading text-primary"></i><h5 class="text-primary">১. বেসিক ইনফরমেশন ও হেডলাইন</h5></div>
                        <div class="card-body-custom pt-0">
                            <div class="form-group mb-3">
                                <label class="fw-bold">মেইন হেডলাইন (Title 1) <span class="text-danger">*</span></label>
                                <input type="text" name="title1" class="form-control" placeholder="যেমন: ফ্লোর, কিচেন ও বাথরুম পরিষ্কার রাখুন" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">URL Slug <small class="text-muted">(optional — খালি রাখলে টাইটেল থেকে বানানো হবে)</small></label>
                                <input type="text" name="slug" class="form-control" placeholder="e.g. summer-sale-2026" pattern="[a-z0-9]+(-[a-z0-9]+)*" title="শুধু ছোট হাতের অক্ষর, সংখ্যা ও হাইফেন">
                            </div>
                            <div class="form-group mb-3">
                                <label class="fw-bold">সাব-হেডলাইন (Title 2)</label>
                                <input type="text" name="title2" class="form-control" placeholder="যেমন: ৭ পিসের স্মার্ট ক্লিনিং কম্বো">
                            </div>
                            <div class="form-group mb-3">
                                <label class="fw-bold">YouTube Video URL (Embed Code)</label>
                                <input type="text" name="video_url" class="form-control" placeholder='<iframe width="560" height="315" src="..."></iframe>'>
                                <small class="text-muted">ইউটিউব থেকে কপি করা পুরো <b>&lt;iframe&gt;</b> কোডটি এখানে বসান।</small>
                            </div>
                        </div>
                    </div>

                    {{-- ২. ফিচার --}}
                    <div class="premium-card">
                        <div class="card-header-custom border-bottom-0"><i class="fas fa-list text-primary"></i><h5 class="text-primary">২. ফিচার ও বিবরণ</h5></div>
                        <div class="card-body-custom pt-0">
                            <div class="form-group mb-3">
                                <label class="fw-bold">ফিচার সেকশন টাইটেল</label>
                                <input type="text" name="feature_title" value="কেন এই পণ্যটি আপনার প্রয়োজন?" class="form-control" placeholder="যেমন: কেন এই কম্বোটি আপনার প্রয়োজন?">
                            </div>
                            <div class="form-group mb-3">
                                <label class="fw-bold">ফিচার লিস্ট (Summernote)</label>
                                <textarea name="feature_list" class="form-control summernote">
                                    <ul>
                                        <li><i class="fas fa-check-circle"></i> একসাথে সম্পূর্ণ প্যাকেজ</li>
                                        <li><i class="fas fa-check-circle"></i> সহজ স্টোরেজ সুবিধা</li>
                                    </ul>
                                </textarea>
                            </div>
                        </div>
                    </div>

                    {{-- ৩. কেন আমাদের থেকে কিনবেন --}}
                    <div class="premium-card">
                        <div class="card-header-custom border-bottom-0"><i class="fas fa-shield-alt text-primary"></i><h5 class="text-primary">৩. কেন আমাদের থেকে কিনবেন?</h5></div>
                        <div class="card-body-custom pt-0">
                            <div class="form-group mb-3">
                                <label class="fw-bold">প্রমিস টাইটেল</label>
                                <input type="text" name="promise_title" value="কেন আমাদের থেকে কিনবেন?" class="form-control" placeholder="যেমন: কেন Falgun Shop থেকেই কিনবেন?">
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="fw-bold">পয়েন্ট ১</label>
                                    <input type="text" name="promise_1_title" value="সারা বাংলাদেশে ক্যাশ অন ডেলিভারি" class="form-control mb-2" placeholder="টাইটেল">
                                    <textarea name="promise_1_desc" class="form-control" rows="2" placeholder="বিস্তারিত">হাতে পাওয়ার পরই টাকা দিন। কোন অগ্রিম পেমেন্টের ঝামেলা নেই।</textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-bold">পয়েন্ট ২</label>
                                    <input type="text" name="promise_2_title" value="নো টেনশন রিটার্ন পলিসি" class="form-control mb-2" placeholder="টাইটেল">
                                    <textarea name="promise_2_desc" class="form-control" rows="2" placeholder="বিস্তারিত">আপনি চাইলে ডেলিভারি ম্যানের সামনেই চেক করতে পারবেন। অপছন্দ হলে সাথে সাথে রিটার্ন করতে পারবেন।</textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-bold">পয়েন্ট ৩</label>
                                    <input type="text" name="promise_3_title" class="form-control mb-2" placeholder="টাইটেল">
                                    <textarea name="promise_3_desc" class="form-control" rows="2" placeholder="বিস্তারিত"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ৪. কাউন্টডাউন ও প্রাইসিং (NEW) --}}
                    <div class="premium-card">
                        <div class="card-header-custom border-bottom-0"><i class="fas fa-clock text-danger"></i><h5 class="text-danger">৪. কাউন্টডাউন ও প্রাইসিং টেক্সট</h5></div>
                        <div class="card-body-custom pt-0">
                            <div class="row g-3">
                                <div class="col-md-6 col-12">
                                    <label class="fw-bold">কাউন্টডাউন টাইটেল</label>
                                    <input type="text" name="countdown_title" value="অফারটি শেষ হতে আর বাকি মাত্র" class="form-control">
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="fw-bold">টাইমার (ঘণ্টা)</label>
                                    <input type="number" name="countdown_hours" value="5" class="form-control" placeholder="যেমন: 5">
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="fw-bold">পূর্বের মূল্য টেক্সট</label>
                                    <input type="text" name="old_price_text" value="পূর্বের মূল্যঃ" class="form-control">
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="fw-bold">বর্তমান মূল্য টেক্সট</label>
                                    <input type="text" name="new_price_text" value="বর্তমান মূল্যঃ" class="form-control">
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="fw-bold">অর্ডার বাটন টেক্সট</label>
                                    <input type="text" name="order_btn_text" value="অর্ডার করতে ক্লিক করুন" class="form-control">
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="fw-bold">রিভিউ ব্যাজ টেক্সট</label>
                                    <input type="text" name="review_badge" value="৫,০০০+ পরিবার ইতোমধ্যে ব্যবহার করছেন!" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ৫. ফর্ম ও বাটন কাস্টমাইজেশন (NEW) --}}
                    <div class="premium-card">
                        <div class="card-header-custom border-bottom-0"><i class="fas fa-edit text-secondary"></i><h5 class="text-secondary">৫. ফর্ম ও বাটন কাস্টমাইজেশন</h5></div>
                        <div class="card-body-custom pt-0">
                            <div class="row g-3">
                                <div class="col-md-12 col-12">
                                    <label class="fw-bold">ফর্মের উপরের টাইটেল</label>
                                    <input type="text" name="form_title" value="অর্ডার করতে নিচের ফর্মটি সঠিক তথ্য দিয়ে পূরণ করুন" class="form-control">
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="fw-bold">নামের লেবেল</label>
                                    <input type="text" name="name_label" value="আপনার নাম *" class="form-control">
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="fw-bold">ফোন নাম্বারের লেবেল</label>
                                    <input type="text" name="phone_label" value="মোবাইল নাম্বার *" class="form-control">
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="fw-bold">ঠিকানার লেবেল</label>
                                    <input type="text" name="address_label" value="সম্পূর্ণ ঠিকানা *" class="form-control">
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="fw-bold">ক্যাশ অন ডেলিভারি টাইটেল</label>
                                    <input type="text" name="cod_title" value="ক্যাশ অন ডেলিভারি" class="form-control">
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="fw-bold">সাবমিট বাটন টেক্সট</label>
                                    <input type="text" name="btn_text_form" value="অর্ডার কনফার্ম করুন" class="form-control">
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="fw-bold">প্রসেসিং টেক্সট</label>
                                    <input type="text" name="processing_text" value="প্রসেসিং হচ্ছে..." class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ৬. FAQ (NEW) --}}
                    <div class="premium-card">
                        <div class="card-header-custom border-bottom-0"><i class="fas fa-question-circle text-info"></i><h5 class="text-info">৬. FAQ (প্রশ্ন ও উত্তর)</h5></div>
                        <div class="card-body-custom pt-0">
                            <div class="mb-3">
                                <label class="fw-bold">FAQ মেইন টাইটেল</label>
                                <input type="text" name="faq_title" value="সচরাচর জিজ্ঞাসিত প্রশ্ন (FAQ)" class="form-control">
                            </div>
                            <div class="row g-3">
                                @for($i=1; $i<=4; $i++)
                                <div class="col-md-6 col-12 border-bottom pb-3">
                                    <label class="fw-bold">প্রশ্ন {{ $i }}</label>
                                    <input type="text" name="faq_{{ $i }}_q" class="form-control mb-2" placeholder="প্রশ্ন লিখুন">
                                    <label class="fw-bold">উত্তর {{ $i }}</label>
                                    <textarea name="faq_{{ $i }}_a" class="form-control" rows="2" placeholder="উত্তর লিখুন"></textarea>
                                </div>
                                @endfor
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ================== RIGHT COLUMN ================== --}}
                <div class="col-lg-4">
                    
                    {{-- প্রোডাক্ট সিলেক্ট --}}
                    <div class="premium-card border-warning">
                        <div class="card-header-custom bg-light border-bottom-0"><i class="fas fa-box-open text-warning"></i><h5 class="text-warning m-0 fw-bold">Product Selection</h5></div>
                        <div class="card-body-custom pt-0">
                            <div class="mb-2 position-relative">
                                <input type="text" id="search2" class="form-control form-control-lg border-warning" placeholder="নাম বা SKU লিখে সার্চ করুন..." autocomplete="off">
                            </div>
                            
                            <input type="hidden" name="new_product_id" id="new_product_id" required>
                            <input type="hidden" name="variation_id" id="variation_id">

                            <div id="data" class="mt-3"></div>

                            <div class="row mt-3 g-2">
                                <div class="col-6">
                                    <label class="form-label text-danger fw-bold small">রেগুলার মূল্য</label>
                                    <input type="number" name="old_price" class="form-control" placeholder="যেমন: ১৮৯০">
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-success fw-bold small">অফার মূল্য</label>
                                    <input type="number" name="new_price" class="form-control" placeholder="যেমন: ১২৪৯">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- প্যাকেজ সেটআপ --}}
                    <div class="premium-card">
                        <div class="card-header-custom bg-light border-bottom-0"><i class="fas fa-boxes text-info"></i><h5 class="text-info m-0 fw-bold">প্যাকেজ সেটআপ (ঐচ্ছিক)</h5></div>
                        <div class="card-body-custom pt-0">
                            <div class="alert alert-secondary text-center small py-2 mb-3 mt-3">১ পিসের প্যাকেজ অটোমেটিক তৈরি হবে। নিচে অতিরিক্ত প্যাকেজ অ্যাড করতে পারেন।</div>
                            
                            @for($p=0; $p<2; $p++)
                            <div class="border p-3 rounded mb-3 bg-light">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="small fw-bold">পরিমাণ (Qty)</label>
                                        <input type="number" name="pkg_qty[]" class="form-control form-control-sm" placeholder="যেমন: {{ $p+2 }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="small fw-bold">মোট দাম</label>
                                        <input type="number" name="pkg_price[]" class="form-control form-control-sm" placeholder="Total Price">
                                    </div>
                                    <div class="col-12 mt-1">
                                        <label class="small fw-bold">ডিসকাউন্ট টেক্সট (ঐচ্ছিক)</label>
                                        <input type="text" name="pkg_discount_text[]" class="form-control form-control-sm" placeholder="যেমন: ২০০ টাকা ছাড়!">
                                    </div>
                                    <div class="col-12 mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="pkg_is_default" value="{{ $p }}" id="pkg_def_{{ $p }}">
                                            <label class="form-check-label small fw-bold" for="pkg_def_{{ $p }}">এই প্যাকেজটি সিলেক্টেড রাখুন</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endfor
                        </div>
                    </div>

                    {{-- ছবি আপলোড (মাল্টিপল গ্যালারি সহ) --}}
                    <div class="premium-card">
                        <div class="card-header-custom border-bottom-0"><i class="fas fa-image text-success"></i><h5 class="text-success m-0 fw-bold">Images & Media</h5></div>
                        <div class="card-body-custom pt-0">
                            
                            {{-- Main Product Image --}}
                            <div class="form-group mb-4 mt-3">
                                <label class="fw-bold mb-2">মেইন প্রোডাক্ট ছবি *</label>
                                <div class="file-upload-wrapper">
                                    <div class="file-upload-text"><i class="fas fa-cloud-upload-alt mb-1 d-block"></i> Upload Main Image</div>
                                    <input type="file" name="right_product_image" class="upload-preview" data-target="main_img_preview" accept="image/*" required>
                                </div>
                                <div id="main_img_preview" class="img-preview-container"></div>
                            </div>
                            
                            {{-- Multiple Gallery Images --}}
                            <div class="form-group mb-4 border-top pt-3">
                                <label class="fw-bold mb-2 text-primary">প্রোডাক্ট গ্যালারি ছবি (Multiple)</label>
                                <div class="file-upload-wrapper" style="border-color: #0d6efd; background: #f0f8ff;">
                                    <div class="file-upload-text text-primary"><i class="fas fa-layer-group mb-1 d-block"></i> Add Gallery Images</div>
                                    <input type="file" name="sliderimage[]" class="upload-preview" data-target="slider_img_preview" accept="image/*" multiple>
                                </div>
                                <div id="slider_img_preview" class="img-preview-container"></div>
                            </div>

                            {{-- Review Images --}}
                            <div class="form-group mb-3 border-top pt-3">
                                <label class="fw-bold mb-2 text-warning">কাস্টমার রিভিউ স্ক্রিনশট (Multiple)</label>
                                <div class="file-upload-wrapper">
                                    <div class="file-upload-text"><i class="fas fa-star mb-1 d-block text-warning"></i> Add Review Images</div>
                                    <input type="file" name="review_product_image[]" class="upload-preview" data-target="review_img_preview" accept="image/*" multiple>
                                </div>
                                <div id="review_img_preview" class="img-preview-container"></div>
                            </div>

                        </div>
                    </div>

                    {{-- ✅ NEW: Design Customization --}}
                    <div class="premium-card">
                        <div class="card-header-custom border-bottom-0"><i class="fas fa-palette text-dark"></i><h5 class="text-dark m-0 fw-bold">Design Settings</h5></div>
                        <div class="card-body-custom pt-0">
                            
                            {{-- ✅ Page Background Color --}}
                            <div class="mb-3 mt-3">
                                <label class="form-label fw-bold">Page Background Color</label>
                                <input type="color" value="#fafafa" name="theme_primary_col" class="form-control form-control-color w-100 p-1" style="height: 40px;">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Order Button BG Color</label>
                                <input type="color" value="#e65100" name="btn_bg_color" class="form-control form-control-color w-100 p-1" style="height: 40px;">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Order Button Text Color</label>
                                <input type="color" value="#ffffff" name="btn_text_color" class="form-control form-control-color w-100 p-1" style="height: 40px;">
                            </div>
                        </div>
                    </div>

                    <button type="submit" id="save_btn" class="btn-save mb-4"><i class="fas fa-save"></i> Save Landing Page</button>

                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script>
$(document).ready(function() {
    
    $('.summernote').summernote({ 
        height: 200, 
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

    // Handle File Upload Previews (Works for multiple files too)
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
                $('#new_product_id').val(res.pr_id); 
            });
            $(this).val(''); 
            return false;
        }
    });

    $(document).on('click', '.remove-product', function() { 
        $(this).closest('table').remove(); 
        $('#new_product_id').val(''); 
    });

    // Form Submission
    $('#save_btn').click(function(e){
        e.preventDefault();
        
        if ($('#new_product_id').val() === '') {
            toastr.error('দয়া করে সার্চ করে একটি প্রোডাক্ট সিলেক্ট করুন!');
            $('#search2').focus();
            return false;
        }

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
                window.location.href = res.url || "{{ route('admin.landing_pages_six') }}"; 
            },
            error: function(xhr){ 
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Landing Page');
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