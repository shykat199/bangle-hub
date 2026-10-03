@extends('backend.app')
@section('title', 'Create Landing Page (Type 7)')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
<style>
    .premium-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.03); margin-bottom: 20px; overflow: hidden; border-top: 3px solid #006400; }
    .card-header-custom { padding: 15px 20px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 10px; background: #f8fafc;}
    .card-header-custom h5 { margin: 0; font-weight: 700; color: #006400; font-size: 16px;}
    .card-body-custom { padding: 20px; }
    .form-control { border-radius: 6px; border: 1px solid #d1d5db; padding: 10px 14px; }
    .file-upload-wrapper { position: relative; border: 2px dashed #006400; border-radius: 8px; padding: 20px; text-align: center; background: #f0fdf4; cursor: pointer; margin-bottom: 10px; }
    .file-upload-wrapper input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
    .img-preview-container { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 5px; justify-content: center; }
    .preview-box { width: 60px; height: 60px; border-radius: 6px; overflow: hidden; border: 1px solid #ddd; }
    .preview-box img { width: 100%; height: 100%; object-fit: cover; }
    .btn-save { background: #006400; color: #fff !important; padding: 12px 30px; border-radius: 8px; border: none; width: 100%; font-weight: 700; box-shadow: 0 4px 6px rgba(0,100,0,0.2); transition: 0.3s; }
    .btn-save:hover { background: #004d00; }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <div class="container-fluid">
        <div class="page-title-box py-3">
            <h4 class="page-title fw-bold text-dark mb-0">Create Landing Page (Type 7 - Multi-Select)</h4>
        </div>

        <form action="{{ route('admin.landing_pages_seven.store') }}" method="POST" enctype="multipart/form-data" id="ajax_form">
            @csrf
            <input type="hidden" name="page_type" value="7">
            
            <div class="row">
                <div class="col-lg-8">
                    
                    {{-- 1. BASIC INFO --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-heading text-success"></i><h5>১. বেসিক ইনফরমেশন ও হেডলাইন</h5></div>
                        <div class="card-body-custom">
                            <div class="form-group mb-3">
                                <label class="fw-bold">মেইন হেডলাইন (Title 1) *</label>
                                <input type="text" name="title1" class="form-control" required placeholder="যেমন: Murdha Moaharee মুসলমানের শেষ গোসল...">
                            </div>
                            <div class="col-12">
                                <label class="form-label">URL Slug <small class="text-muted">(optional — খালি রাখলে টাইটেল থেকে বানানো হবে)</small></label>
                                <input type="text" name="slug" class="form-control" placeholder="e.g. summer-sale-2026" pattern="[a-z0-9]+(-[a-z0-9]+)*" title="শুধু ছোট হাতের অক্ষর, সংখ্যা ও হাইফেন">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">যোগাযোগের নাম্বার</label>
                                    <input type="text" name="phone" class="form-control" placeholder="017XXXXXXXX">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">YouTube Video (Embed Code)</label>
                                    <input type="text" name="video_url" class="form-control" placeholder='<iframe src="..."></iframe>'>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. FEATURES --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-list text-success"></i><h5>২. প্রোডাক্ট ফিচার ও বিবরণ</h5></div>
                        <div class="card-body-custom">
                            <div class="form-group mb-3">
                                <label class="fw-bold">ফিচার সেকশন টাইটেল</label>
                                <input type="text" name="feature_title" class="form-control" placeholder="যেমন: কেন আমাদের প্রোডাক্ট কেনা উচিত?">
                            </div>
                            <div class="form-group mb-3">
                                <label class="fw-bold">ফিচার লিস্ট (Summernote)</label>
                                <textarea name="feature_list" class="form-control summernote">
                                    <ul><li><i class="fas fa-check"></i> সম্পূর্ণ শরীয়া মোতাবেক</li></ul>
                                </textarea>
                            </div>
                        </div>
                    </div>

                    {{-- 3. PROMISES & NEGATIVE TAGS --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-shield-alt text-success"></i><h5>৩. আমাদের প্রতিশ্রুতি</h5></div>
                        <div class="card-body-custom">
                            <div class="form-group mb-3">
                                <label class="fw-bold">Promise Title</label>
                                <input type="text" name="promise_title" class="form-control" placeholder="যেমন: আমাদের প্রতিশ্রুতি">
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="fw-bold">পয়েন্ট ১</label>
                                    <input type="text" name="promise_1_title" class="form-control mb-2" placeholder="টাইটেল">
                                    <textarea name="promise_1_desc" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-bold">পয়েন্ট ২</label>
                                    <input type="text" name="promise_2_title" class="form-control mb-2" placeholder="টাইটেল">
                                    <textarea name="promise_2_desc" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-bold">পয়েন্ট ৩</label>
                                    <input type="text" name="promise_3_title" class="form-control mb-2" placeholder="টাইটেল">
                                    <textarea name="promise_3_desc" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            <div class="form-group">
                                <label class="fw-bold text-danger"><i class="fas fa-times-circle"></i> নেগেটিভ ট্যাগ (Negative Points)</label>
                                <input type="text" name="negative_tags" class="form-control" placeholder="যেমন: কেমিক্যাল নেই, ভেজাল নেই (কমা দিয়ে লিখুন)">
                            </div>
                        </div>
                    </div>

                    {{-- 4. FAQ SECTION --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-question-circle text-success"></i><h5>৪. সচরাচর জিজ্ঞাসা (FAQ)</h5></div>
                        <div class="card-body-custom">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="fw-bold">FAQ Title</label>
                                    <input type="text" name="faq_title" class="form-control" placeholder="যেমন: সচরাচর জিজ্ঞাসিত প্রশ্ন">
                                </div>
                                @for($i=1; $i<=4; $i++)
                                <div class="col-md-12 border-bottom pb-2 mt-3">
                                    <label class="fw-bold">প্রশ্ন {{ $i }}</label>
                                    <input type="text" name="faq_{{ $i }}_q" class="form-control mb-2" placeholder="প্রশ্ন লিখুন">
                                    <textarea name="faq_{{ $i }}_a" class="form-control" rows="2" placeholder="উত্তর লিখুন"></textarea>
                                </div>
                                @endfor
                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-lg-4">
                    {{-- PRODUCT SELECT --}}
                    <div class="premium-card border-success">
                        <div class="card-header-custom"><i class="fas fa-box text-success"></i><h5>প্রোডাক্ট সিলেক্ট করুন *</h5></div>
                        <div class="card-body-custom">
                            <input type="text" id="search2" class="form-control mb-3" placeholder="Search product...">
                            <input type="hidden" name="new_product_id" id="new_product_id" required>
                            <div id="data" class="mb-3"></div>

                            <div class="row g-2">
                                <div class="col-6"><label class="fw-bold text-danger">Regular Price</label><input type="number" name="old_price" class="form-control"></div>
                                <div class="col-6"><label class="fw-bold text-success">Offer Price</label><input type="number" name="new_price" class="form-control"></div>
                            </div>
                            <small class="text-muted d-block mt-2">ভেরিয়েশন থাকলে ফ্রন্টএন্ডে মাল্টি-সিলেক্ট বক্স হিসেবে শো করবে।</small>
                        </div>
                    </div>

                    {{-- IMAGES --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-images text-success"></i><h5>ছবি আপলোড</h5></div>
                        <div class="card-body-custom">
                            <div class="mb-4">
                                <label class="fw-bold mb-2">Main Product Image</label>
                                <div class="file-upload-wrapper">
                                    <div><i class="fas fa-cloud-upload-alt"></i> Upload Main Image</div>
                                    <input type="file" name="right_product_image" class="upload-preview" data-target="main_img_preview">
                                </div>
                                <div id="main_img_preview" class="img-preview-container"></div>
                            </div>
                            <div class="mb-4">
                                <label class="fw-bold mb-2">Gallery Images (Multiple)</label>
                                <input type="file" name="sliderimage[]" multiple class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold mb-2">Review Screenshots (Multiple)</label>
                                <input type="file" name="review_product_image[]" multiple class="form-control">
                            </div>
                        </div>
                    </div>

                    <button type="submit" id="save_btn" class="btn-save"><i class="fas fa-save"></i> Save Landing Page</button>
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
    $('.summernote').summernote({ height: 150 });

    $('.upload-preview').on('change', function() {
        var targetId = $(this).data('target'); 
        var files = this.files; 
        var $container = $('#' + targetId); 
        $container.empty();
        if (files) { 
            $.each(files, function(i, file) { 
                var reader = new FileReader(); 
                reader.onload = function(e) { $container.append(`<div class="preview-box"><img src="${e.target.result}"></div>`); }; 
                reader.readAsDataURL(file); 
            }); 
        }
    });

    var path = "{{ route('admin.getOrderProduct2') }}";
    $("#search2").autocomplete({
        source: function(req, res) { $.getJSON(path, {search: req.term}, res); },
        select: function(e, ui) {
            $.getJSON('{{ route("admin.landingProductEntry")}}', {id: ui.item.id}, function(res){ 
                $('div#data').html(res.html); 
                $('#new_product_id').val(res.pr_id); 
            });
            $(this).val(''); return false;
        }
    });

    $(document).on('click', '.remove-product', function() { 
        $(this).closest('table').remove(); 
        $('#new_product_id').val(''); 
    });

    $('#ajax_form').submit(function(e) {
        if ($('#new_product_id').val() === '') {
            e.preventDefault();
            toastr.error('দয়া করে একটি প্রোডাক্ট সিলেক্ট করুন!');
            $('#search2').focus();
        } else {
            $('#save_btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        }
    });
});
</script>
@endpush