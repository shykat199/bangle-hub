@extends('backend.app')

@section('content')
@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
{{-- FontAwesome for Icons --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />

<style>
    :root {
        --primary-color: #4f46e5;
        --primary-hover: #4338ca;
        --bg-light: #f9fafb;
        --border-color: #e5e7eb;
        --text-dark: #111827;
        --text-muted: #6b7280;
    }

    /* Page Layout */
    .page-title-box { padding: 24px 0; }
    .page-title { font-size: 1.5rem; font-weight: 700; color: var(--text-dark); margin: 0; }
    .breadcrumb-item a { color: var(--text-muted); text-decoration: none; }
    .breadcrumb-item.active { color: var(--primary-color); font-weight: 600; }

    /* Premium Card */
    .premium-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(0,0,0,0.03);
        margin-bottom: 24px;
        overflow: hidden;
    }
    .card-header-custom {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
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
    .form-control:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }

    /* File Upload & Preview */
    .file-upload-wrapper {
        position: relative; border: 2px dashed #d1d5db; border-radius: 12px;
        padding: 20px; text-align: center; background: #f9fafb; transition: all 0.2s;
        cursor: pointer;
    }
    .file-upload-wrapper:hover { border-color: var(--primary-color); background: #eff6ff; }
    .file-upload-wrapper input[type="file"] {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;
        z-index: 10;
    }
    .file-upload-icon { font-size: 2rem; color: var(--text-muted); margin-bottom: 8px; }
    .file-upload-text { font-size: 0.85rem; color: var(--text-muted); }
    
    /* Image Preview Container */
    .img-preview-container {
        display: flex; gap: 10px; flex-wrap: wrap; margin-top: 15px; justify-content: center;
    }
    .preview-box {
        position: relative; width: 80px; height: 80px; border-radius: 8px; overflow: hidden; border: 1px solid #ddd;
    }
    .preview-box img { width: 100%; height: 100%; object-fit: cover; }

    /* Product Section */
    .product-search-box { background: #eff6ff; padding: 20px; border-radius: 12px; border: 1px solid #dbeafe; }
    
    /* Save Button */
    .btn-save {
        background: var(--primary-color); color: white; padding: 12px 30px;
        border-radius: 8px; font-weight: 600; border: none; transition: 0.3s;
        box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);
    }
    .btn-save:hover { background: var(--primary-hover); transform: translateY(-2px); }
    .btn-save:disabled { background: #9ca3af; cursor: not-allowed; }
</style>
@endpush

<div class="container-fluid">
    
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="page-title">Create Landing Page (Type Two)</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">SIS</a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">CRM</a></li>
                        <li class="breadcrumb-item active">Create Page</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.landing_pages_two.store') }}" id="ajax_form">
        @csrf
        <div class="row">
            
            <div class="col-lg-8">
                
                <div class="premium-card">
                    <div class="card-header-custom">
                        <i class="fas fa-info-circle"></i>
                        <h5>Hero Information</h5>
                    </div>
                    <div class="card-body-custom">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Page Title <span class="text-danger">*</span></label>
                                <input type="text" name="title1" class="form-control" placeholder="Enter an attractive title" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">URL Slug <small class="text-muted">(optional — খালি রাখলে টাইটেল থেকে বানানো হবে)</small></label>
                                <input type="text" name="slug" class="form-control" placeholder="e.g. summer-sale-2026" pattern="[a-z0-9]+(-[a-z0-9]+)*" title="শুধু ছোট হাতের অক্ষর, সংখ্যা ও হাইফেন">
                                <input type="hidden" name="page_type" value="2">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" name="phone" class="form-control" placeholder="017xxxxxxxx">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Call To Action Text</label>
                                <input type="text" name="call_text" class="form-control" placeholder="e.g. Order Now">
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
                            <label class="form-label">Left Side Headline</label>
                            <input type="text" name="left_side_title" class="form-control" placeholder="Main Highlight Heading">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Left Side Details</label>
                            <textarea class="form-control summernote" name="left_side_desc" rows="6"></textarea>
                        </div>
                    </div>
                </div>

                <div class="premium-card border-primary">
                    <div class="card-header-custom" style="background: #eff6ff;">
                        <i class="fas fa-box-open text-primary"></i>
                        <h5 class="text-primary">Product Integration</h5>
                    </div>
                    <div class="card-body-custom">
                        
                        <div class="product-search-box mb-2">
                            <label class="form-label"><i class="fas fa-search me-1"></i> Search & Select Product</label>
                            <input type="text" id="search2" class="form-control form-control-lg" placeholder="Type product name (min 2 chars)...">
                        </div>

                        {{-- Instructions --}}
                        <div class="mb-4 text-center">
                             <small class="text-danger fw-bold"><i class="fas fa-exclamation-circle"></i> ভেরিয়েশন আছে এমন প্রোডাক্ট যে কোন একটা সিলেক্ট করলেই হবে।</small>
                        </div>

                        <input type="hidden" id="product_id" name="product_id" value="">
                        
                        {{-- Product Data will load here --}}
                        <div id="data" class="mb-3"></div>

                    </div>
                </div>

                {{-- ✅✅✅ NEW: PACKAGE SETUP SECTION ✅✅✅ --}}
                <div class="premium-card" style="border: 1px solid #d35400;">
                    <div class="card-header-custom" style="background: #fff3e0; border-bottom: 1px solid #fad7a1;">
                        <i class="fas fa-cubes text-warning"></i>
                        <h5 class="text-dark flex-grow-1">প্রোডাক্ট প্যাকেজ সেটআপ (Product Packages)</h5>
                        <button type="button" class="btn btn-sm text-white fw-bold" id="add_package_btn" style="background: #d35400;">
                            <i class="fas fa-plus"></i> নতুন প্যাকেজ
                        </button>
                    </div>
                    <div class="card-body-custom" id="package_container">
                        
                        {{-- Create Mode: ডিফল্টভাবে ১টি ফাঁকা রো --}}
                        <div class="row package-row align-items-end mb-3 pb-3 border-bottom">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Quantity (পিস) *</label>
                                <input type="number" name="pkg_qty[]" class="form-control" required value="1" min="1">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Price (টাকা) *</label>
                                <input type="number" name="pkg_price[]" class="form-control" required min="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Discount Text (ঐচ্ছিক)</label>
                                <input type="text" name="pkg_discount_text[]" class="form-control" placeholder="যেমন: (১৯৮ টাকা ডিসকাউন্ট!)">
                            </div>
                            <div class="col-md-1 text-center">
                                <label class="form-label d-block fw-bold" style="font-size: 11px;">ডিফল্ট</label>
                                <input type="radio" name="pkg_is_default" value="0" class="form-check-input" style="width:20px; height:20px; cursor:pointer;" checked>
                            </div>
                            <div class="col-md-1 text-end">
                                <button type="button" class="btn btn-danger remove-package-btn" disabled><i class="fas fa-trash"></i></button>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <div class="col-lg-4">
                
                <div class="premium-card">
                    <div class="card-header-custom">
                        <i class="fas fa-photo-video"></i>
                        <h5>Visual Assets</h5>
                    </div>
                    <div class="card-body-custom">
                        
                        <div class="mb-4">
                            <label class="form-label">Background Image</label>
                            <div class="file-upload-wrapper">
                                <div class="file-upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                <div class="file-upload-text">Click to upload Main Background</div>
                                <input type="file" name="landing_bg" class="upload-preview" data-target="bg_preview">
                            </div>
                            <div id="bg_preview" class="img-preview-container"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Video URL (Embed Code)</label>
                            <input type="text" name="video_url" class="form-control" placeholder="<iframe>...</iframe>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Slider Top Text</label>
                            <input type="text" name="feature" class="form-control" placeholder="Feature Highlight">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Slider Images</label>
                            <div class="file-upload-wrapper" style="padding: 15px;">
                                <div class="file-upload-text">Select Multiple Images</div>
                                <input type="file" name="sliderimage[]" multiple class="upload-preview" data-target="slider_preview">
                            </div>
                            <div id="slider_preview" class="img-preview-container"></div>
                        </div>

                    </div>
                </div>

                <div class="premium-card">
                    <div class="card-header-custom">
                        <i class="fas fa-star"></i>
                        <h5>Reviews</h5>
                    </div>
                    <div class="card-body-custom">
                        <div class="mb-3">
                            <label class="form-label">Review Heading</label>
                            <input type="text" name="review_top_text" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Review Images</label>
                            <div class="file-upload-wrapper" style="padding: 15px;">
                                <div class="file-upload-text">Upload Review Screenshots</div>
                                <input type="file" name="review_product_image[]" multiple class="upload-preview" data-target="review_preview">
                            </div>
                            <div id="review_preview" class="img-preview-container"></div>
                        </div>
                    </div>
                </div>

                <div class="premium-card">
                    <div class="card-body-custom text-center">
                        <button type="submit" id="save_btn" class="btn-save w-100">
                            <i class="fas fa-save me-2"></i> Save & Publish
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
        // Summernote Init
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

        // --- Image Preview Logic ---
        $('.upload-preview').on('change', function() {
            var targetId = $(this).data('target');
            var files = this.files;
            var $container = $('#' + targetId);
            $container.empty(); 

            if (files) {
                $.each(files, function(index, file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var html = `<div class="preview-box"><img src="${e.target.result}"></div>`;
                        $container.append(html);
                    }
                    reader.readAsDataURL(file);
                });
            }
        });

        // ✅✅✅ NEW: PACKAGE ADD MORE LOGIC ✅✅✅
        let pkgIndex = 1;
        $('#add_package_btn').click(function(e) {
            e.preventDefault();
            let newRow = `
            <div class="row package-row align-items-end mb-3 pb-3 border-bottom">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Quantity (পিস) *</label>
                    <input type="number" name="pkg_qty[]" class="form-control" required value="1" min="1">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Price (টাকা) *</label>
                    <input type="number" name="pkg_price[]" class="form-control" required min="1">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Discount Text (ঐচ্ছিক)</label>
                    <input type="text" name="pkg_discount_text[]" class="form-control" placeholder="যেমন: (১৯৮ টাকা ডিসকাউন্ট!)">
                </div>
                <div class="col-md-1 text-center">
                    <label class="form-label d-block fw-bold" style="font-size: 11px;">ডিফল্ট</label>
                    <input type="radio" name="pkg_is_default" value="${pkgIndex}" class="form-check-input" style="width:20px; height:20px; cursor:pointer;">
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-danger remove-package-btn"><i class="fas fa-trash"></i></button>
                </div>
            </div>`;
            
            $('#package_container').append(newRow);
            pkgIndex++;
        });

        $(document).on('click', '.remove-package-btn', function(e) {
            e.preventDefault();
            if($('.package-row').length > 1) {
                $(this).closest('.package-row').remove();
            } else {
                toastr.warning('অন্তত একটি প্যাকেজ থাকা বাধ্যতামূলক!');
            }
        });
        // ==========================================

    });

    // --- Product Search & Logic ---
    var path2 = "{{ route('admin.getOrderProduct2') }}";
    var products = []; 

    $("#search2").autocomplete({
        selectFirst: true,
        minLength: 2,
        source: function(request, response) {
            $.ajax({
                url: path2,
                type: 'GET',
                dataType: "json",
                data: { search: request.term },
                success: function(data) {
                    if (data.length == 0) {
                        toastr.error('Product Not Found or Out of Stock');
                    } else if (data.length == 1) {
                        if (products.indexOf(data[0].id) == -1) {
                            landingProductEntry(data[0]);
                            products.push(data[0].id);
                        }
                        $('#search2').val('');
                    } else {
                        response(data);
                    }
                }
            });
        },
        select: function(event, ui) {
            if (products.indexOf(ui.item.id) == -1) {
                landingProductEntry(ui.item);
                products.push(ui.item.id);
            }
            $('#search2').val('');
            return false;
        }
    });

    // Load Product HTML
    function landingProductEntry(item) {
        $.ajax({
            url: '{{ route("admin.landingProductEntry")}}',
            type: 'GET',
            dataType: "json",
            data: { id: item.id },
            success: function(res) {
                if (res.html) {
                    $('div#data').html(res.html);
                    
                    setTimeout(function() {
                        $('#data table th').each(function(index) {
                            var text = $(this).text().toLowerCase().trim();
                            if(text === 'size' || text === 'color') {
                                $(this).hide(); 
                                $('#data table tbody tr').each(function() {
                                    $(this).find('td').eq(index).hide(); 
                                });
                            }
                        });
                    }, 100);
                }
                if (res.pr_id) $('#product_id').val(parseInt(res.pr_id, 10));
            }
        });
    }

    // Delete Product Logic
    $(document).on('click', '.remove_item, .btn-remove, .delete-product, .btn-danger:not(.remove-package-btn)', function(e) {
        e.preventDefault();
        $('#data').html('');
        let removedId = $('#product_id').val();
        $('#product_id').val('');
        if(removedId) {
            let index = products.indexOf(parseInt(removedId));
            if (index > -1) {
                products.splice(index, 1);
            }
        }
    });

    // --- Save Button Logic ---
    $(document).off('click', '#save_btn').on('click', '#save_btn', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        const form = document.getElementById('ajax_form');
        const btn = $('#save_btn');

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
            cache: false,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(res) {
                const redirectUrl = (res && res.url) ? res.url : "{{ route('admin.landing_pages_two') }}";
                toastr.success('Page Created Successfully!');
                window.location.href = redirectUrl;
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    let firstKey = Object.keys(xhr.responseJSON.errors)[0];
                    let msg = xhr.responseJSON.errors[firstKey][0];
                    toastr.error(msg);
                } else {
                    toastr.error('Something went wrong! Check Console.');
                    console.error(xhr.responseText);
                }
                btn.data('submitting', 0);
                btn.prop('disabled', false).html(originalText);
            }
        });

        return false;
    });
</script>
@endpush