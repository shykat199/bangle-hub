@extends('backend.app')

@section('content')
@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
<style>
    .premium-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.03); margin-bottom: 24px; overflow: hidden; }
    .card-header-custom { padding: 20px 24px; border-bottom: 1px solid #e5e7eb; background: #fff; display: flex; align-items: center; gap: 10px; color: #f59e0b; }
    .card-header-custom h5 { margin: 0; font-weight: 700; color: #111827; }
    .card-body-custom { padding: 24px; }
    
    /* Form Control Responsiveness */
    .form-control, .form-select { border-radius: 8px; border: 1px solid #d1d5db; padding: 10px 14px; width: 100%; box-sizing: border-box; }
    .form-control:focus, .form-select:focus { border-color: #f59e0b; box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1); }
    
    /* Image Upload Area */
    .file-upload-wrapper { position: relative; border: 2px dashed #d1d5db; border-radius: 12px; padding: 20px; text-align: center; background: #fffbeb; cursor: pointer; transition: 0.3s; margin-bottom: 10px; }
    .file-upload-wrapper:hover { border-color: #f59e0b; }
    .file-upload-wrapper input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
    .file-upload-text { color: #d97706; font-weight: 600; font-size: 14px; }
    
    /* Image Preview Container (Both Existing & New) */
    .img-preview-container, .existing-img-container { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; margin-bottom: 15px; justify-content: flex-start; }
    .existing-img-box, .preview-box { position: relative; border: 2px solid #e5e7eb; border-radius: 8px; padding: 2px; background: #fff; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; }
    .existing-img-box img, .preview-box img { border-radius: 6px; width: 100%; height: 100%; object-fit: cover; }
    
    .btn-delete-img { position: absolute; top: -8px; right: -8px; background: #ef4444; color: white; border-radius: 50%; width: 22px; height: 22px; font-size: 12px; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 2px 4px rgba(0,0,0,0.2); z-index: 10; }
    .btn-delete-img:hover { color: #fff; background: #dc2626; transform: scale(1.1); }
    
    .btn-save { background: #f59e0b; color: white; padding: 12px 30px; border-radius: 8px; border: none; width: 100%; font-weight: 600; transition: 0.3s; font-size: 16px; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .btn-save:hover { background: #d97706; color: white !important; transform: translateY(-2px); }
    
    #product_table td small, #product_table td .text-muted { display: none !important; }

    /* MOBILE RESPONSIVE FIXES */
    @media (max-width: 768px) {
        .card-body-custom { padding: 15px; }
        .card-header-custom { padding: 15px; }
        .card-header-custom h5 { font-size: 16px; }
        .existing-img-box, .preview-box { width: 60px; height: 60px; }
        .btn-delete-img { width: 20px; height: 20px; font-size: 10px; top: -6px; right: -6px; }
        .file-upload-wrapper { padding: 15px; }
    }
</style>
@endpush

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between mb-3">
                <h4 class="page-title fw-bold">Edit Landing Page (Type 6)</h4>
                <div class="page-title-right">
                    <a href="{{ route('admin.landing_pages_six') }}" class="btn btn-sm btn-secondary fw-bold">Back to List</a>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.landing_pages_six.update', [$item->id]) }}" id="ajax_form">
        @csrf
        @method('PATCH')
        
        <div class="row">
            {{-- ================== LEFT COLUMN ================== --}}
            <div class="col-lg-8">
                
                {{-- 1. HEADER & CONTENT --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-heading"></i><h5 class="m-0 fw-bold">Header & Content</h5></div>
                    <div class="card-body-custom">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">Main Headline *</label>
                                <input type="text" value="{{ $item->title1 }}" name="title1" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">URL Slug <small class="text-muted">(optional — খালি রাখলে আগেরটাই থাকবে)</small></label>
                                <input type="text" value="{{ $item->slug }}" name="slug" class="form-control" placeholder="e.g. summer-sale-2026" pattern="[a-z0-9]+(-[a-z0-9]+)*" title="শুধু ছোট হাতের অক্ষর, সংখ্যা ও হাইফেন">
                                <input type="hidden" name="page_type" value="6">
                                <input type="hidden" name="product_id" id="product_id" value="{{ $item->product_id }}">
                                <input type="hidden" id="new_product_id" name="new_product_id" value="{{ $item->product_id }}">
                                <input type="hidden" name="variation_id" id="variation_id" value="{{ $item->variation_id }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Sub-Headline</label>
                                <input type="text" value="{{ $item->title2 }}" name="title2" class="form-control">
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-bold">Phone Number</label>
                                <input type="text" value="{{ $item->phone }}" name="phone" class="form-control">
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-bold">YouTube Video URL (Embed Code)</label>
                                <input type="text" value="{{ $item->video_url }}" name="video_url" class="form-control" placeholder='<iframe width="560" height="315" src="..."></iframe>'>
                                <small class="text-muted mt-1 d-block">ইউটিউব থেকে কপি করা পুরো <b>&lt;iframe&gt;</b> কোডটি এখানে বসান।</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. FEATURES SECTION --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-th-list"></i><h5 class="m-0 fw-bold">Features Section</h5></div>
                    <div class="card-body-custom">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Features Title</label>
                            <input type="text" value="{{ $item->feature_title }}" name="feature_title" class="form-control" placeholder="যেমন: কেন এই পণ্যটি সেরা?">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Feature Description (CKEditor/Summernote)</label>
                            <textarea class="form-control summernote" name="feature_list">{!! $item->feature_list !!}</textarea>
                        </div>
                    </div>
                </div>

                {{-- 3. PROMISES --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-handshake"></i><h5 class="m-0 fw-bold">Promises Section</h5></div>
                    <div class="card-body-custom">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">Promise Section Title</label>
                                <input type="text" value="{{ $item->promise_title }}" name="promise_title" class="form-control">
                            </div>
                            @for($i=1; $i<=3; $i++)
                                @php 
                                    $pTitle = 'promise_'.$i.'_title';
                                    $pDesc  = 'promise_'.$i.'_desc';
                                @endphp
                                <div class="col-12 border-bottom pb-3">
                                    <label class="fw-bold">Point {{ $i }}</label>
                                    <input type="text" value="{{ $item->$pTitle ?? '' }}" name="{{ $pTitle }}" class="form-control mb-2" placeholder="Title">
                                    <textarea name="{{ $pDesc }}" class="form-control" rows="2" placeholder="Description">{{ $item->$pDesc ?? '' }}</textarea>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>

                {{-- 4. COUNTDOWN & PRICING TEXT --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-clock text-danger"></i><h5 class="m-0 fw-bold text-danger">Countdown & Pricing Text</h5></div>
                    <div class="card-body-custom">
                        <div class="row g-3">
                            <div class="col-md-6 col-12">
                                <label class="fw-bold">কাউন্টডাউন টাইটেল</label>
                                <input type="text" name="countdown_title" value="{{ $item->countdown_title ?? 'অফারটি শেষ হতে আর বাকি মাত্র' }}" class="form-control">
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="fw-bold">টাইমার (ঘণ্টা)</label>
                                <input type="number" name="countdown_hours" value="{{ $item->countdown_hours ?? 5 }}" class="form-control" placeholder="যেমন: 5">
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="fw-bold">পূর্বের মূল্য টেক্সট</label>
                                <input type="text" name="old_price_text" value="{{ $item->old_price_text ?? 'পূর্বের মূল্যঃ' }}" class="form-control">
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="fw-bold">বর্তমান মূল্য টেক্সট</label>
                                <input type="text" name="new_price_text" value="{{ $item->new_price_text ?? 'বর্তমান মূল্যঃ' }}" class="form-control">
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="fw-bold">অর্ডার বাটন টেক্সট</label>
                                <input type="text" name="order_btn_text" value="{{ $item->order_btn_text ?? 'অর্ডার করতে ক্লিক করুন' }}" class="form-control">
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="fw-bold">রিভিউ ব্যাজ টেক্সট</label>
                                <input type="text" name="review_badge" value="{{ $item->review_badge ?? '৫,০০০+ পরিবার ইতোমধ্যে ব্যবহার করছেন!' }}" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 5. FORM & BUTTON CUSTOMIZATION --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-edit text-secondary"></i><h5 class="m-0 fw-bold text-secondary">Form & Button Customization</h5></div>
                    <div class="card-body-custom">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="fw-bold">ফর্মের উপরের টাইটেল</label>
                                <input type="text" name="form_title" value="{{ $item->form_title ?? 'অর্ডার করতে নিচের ফর্মটি সঠিক তথ্য দিয়ে পূরণ করুন' }}" class="form-control">
                            </div>
                            <div class="col-md-4 col-12">
                                <label class="fw-bold">নামের লেবেল</label>
                                <input type="text" name="name_label" value="{{ $item->name_label ?? 'আপনার নাম *' }}" class="form-control">
                            </div>
                            <div class="col-md-4 col-12">
                                <label class="fw-bold">ফোন নাম্বারের লেবেল</label>
                                <input type="text" name="phone_label" value="{{ $item->phone_label ?? 'মোবাইল নাম্বার *' }}" class="form-control">
                            </div>
                            <div class="col-md-4 col-12">
                                <label class="fw-bold">ঠিকানার লেবেল</label>
                                <input type="text" name="address_label" value="{{ $item->address_label ?? 'সম্পূর্ণ ঠিকানা *' }}" class="form-control">
                            </div>
                            <div class="col-md-4 col-12">
                                <label class="fw-bold">ক্যাশ অন ডেলিভারি টাইটেল</label>
                                <input type="text" name="cod_title" value="{{ $item->cod_title ?? 'ক্যাশ অন ডেলিভারি' }}" class="form-control">
                            </div>
                            <div class="col-md-4 col-12">
                                <label class="fw-bold">সাবমিট বাটন টেক্সট</label>
                                <input type="text" name="btn_text_form" value="{{ $item->btn_text_form ?? 'অর্ডার কনফার্ম করুন' }}" class="form-control">
                            </div>
                            <div class="col-md-4 col-12">
                                <label class="fw-bold">প্রসেসিং টেক্সট (অর্ডারের সময়)</label>
                                <input type="text" name="processing_text" value="{{ $item->processing_text ?? 'প্রসেসিং হচ্ছে...' }}" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 6. FAQ SECTION --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-question-circle text-info"></i><h5 class="m-0 fw-bold text-info">FAQ (প্রশ্ন ও উত্তর)</h5></div>
                    <div class="card-body-custom">
                        <div class="mb-3">
                            <label class="fw-bold">FAQ মেইন টাইটেল</label>
                            <input type="text" name="faq_title" value="{{ $item->faq_title ?? 'সচরাচর জিজ্ঞাসিত প্রশ্ন (FAQ)' }}" class="form-control">
                        </div>
                        <div class="row g-3">
                            @for($i=1; $i<=4; $i++)
                                @php 
                                    $fQ = 'faq_'.$i.'_q';
                                    $fA = 'faq_'.$i.'_a';
                                @endphp
                                <div class="col-md-6 col-12 border-bottom pb-3">
                                    <label class="fw-bold">প্রশ্ন {{ $i }}</label>
                                    <input type="text" name="{{ $fQ }}" value="{{ $item->$fQ ?? '' }}" class="form-control mb-2" placeholder="প্রশ্ন লিখুন">
                                    <label class="fw-bold">উত্তর {{ $i }}</label>
                                    <textarea name="{{ $fA }}" class="form-control" rows="2" placeholder="উত্তর লিখুন">{{ $item->$fA ?? '' }}</textarea>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>

            </div>

            {{-- ================== RIGHT COLUMN ================== --}}
            <div class="col-lg-4">
                
                {{-- 7. PRODUCT INTEGRATION --}}
                <div class="premium-card" style="border: 1px solid #f59e0b;">
                    <div class="card-header-custom" style="background: #fffbeb;"><i class="fas fa-box-open text-warning"></i><h5 class="m-0 fw-bold text-warning">Product Selection</h5></div>
                    <div class="card-body-custom">
                        <div id="product_search" style="display: {{ $single_product ? 'none' : 'block' }};">
                            <input type="text" id="search2" class="form-control form-control-lg" placeholder="Search product by name or SKU...">
                        </div>
                        <div class="table-responsive mt-3" id="product_container" style="display: {{ $single_product ? 'block' : 'none' }};">
                            <table class="table table-bordered table-centered mb-0" id="product_table">
                                <tbody id="data">
                                    @if($single_product)
                                    <tr>
                                        <td><img src="{{ getImage('products', $single_product->image) }}" height="50" width="50" class="rounded"/></td>
                                        <td class="fw-bold">{{ $single_product->name }}</td>
                                        <td>{{ $single_product->sell_price }} Tk</td>
                                        <td><a class="remove-product btn btn-sm btn-soft-danger"><i class="fas fa-trash"></i> Remove</a></td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row mt-3 g-2">
                            <div class="col-6">
                                <label class="form-label text-danger fw-bold small">Regular Price</label>
                                <input type="number" value="{{ $item->old_price }}" name="old_price" class="form-control">
                            </div>
                            <div class="col-6">
                                <label class="form-label text-success fw-bold small">Offer Price</label>
                                <input type="number" value="{{ $item->new_price }}" name="new_price" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 8. প্যাকেজ সেটআপ --}}
                <div class="premium-card">
                    <div class="card-header-custom bg-light border-bottom-0"><i class="fas fa-boxes text-info"></i><h5 class="text-info m-0 fw-bold">প্যাকেজ সেটআপ (ঐচ্ছিক)</h5></div>
                    <div class="card-body-custom pt-0">
                        <div class="alert alert-secondary text-center small py-2 mb-3 mt-3">১ পিসের প্যাকেজ অটোমেটিক তৈরি হবে। নিচে অতিরিক্ত প্যাকেজ অ্যাড বা এডিট করতে পারেন।</div>
                        
                        {{-- আগে থেকে থাকা প্যাকেজগুলো --}}
                        @if($item->packages && $item->packages->count() > 0)
                            @foreach($item->packages as $index => $pkg)
                            <div class="border p-3 rounded mb-3 bg-light">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="small fw-bold">পরিমাণ (Qty)</label>
                                        <input type="number" name="pkg_qty[]" value="{{ $pkg->qty }}" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-6">
                                        <label class="small fw-bold">মোট দাম</label>
                                        <input type="number" name="pkg_price[]" value="{{ $pkg->price }}" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-12 mt-1">
                                        <label class="small fw-bold">ডিসকাউন্ট টেক্সট (ঐচ্ছিক)</label>
                                        <input type="text" name="pkg_discount_text[]" value="{{ $pkg->discount_text }}" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-12 mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="pkg_is_default" value="{{ $index }}" id="pkg_def_{{ $index }}" {{ $pkg->is_default == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label small fw-bold" for="pkg_def_{{ $index }}">এই প্যাকেজটি সিলেক্টেড রাখুন</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @endif

                        {{-- নতুন প্যাকেজ অ্যাড করার জন্য ফাঁকা ঘর (Safe Loop) --}}
                        @php 
                            $startIdx = ($item->packages && $item->packages->count() > 0) ? $item->packages->count() : 0; 
                            $endIdx = $startIdx + 2;
                        @endphp

                        @for($p = $startIdx; $p < $endIdx; $p++)
                        <div class="border p-3 rounded mb-3 bg-light">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="small fw-bold">পরিমাণ (Qty)</label>
                                    <input type="number" name="pkg_qty[]" class="form-control form-control-sm" placeholder="New">
                                </div>
                                <div class="col-6">
                                    <label class="small fw-bold">মোট দাম</label>
                                    <input type="number" name="pkg_price[]" class="form-control form-control-sm">
                                </div>
                                <div class="col-12 mt-1">
                                    <label class="small fw-bold">ডিসকাউন্ট টেক্সট (ঐচ্ছিক)</label>
                                    <input type="text" name="pkg_discount_text[]" class="form-control form-control-sm">
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

                {{-- 9. VISUAL ASSETS --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-images"></i><h5 class="m-0 fw-bold">Images & Media</h5></div>
                    <div class="card-body-custom">
                        
                        {{-- Main Image --}}
                        <div class="mb-4">
                            <label class="fw-bold mb-2">Main Product Image *</label>
                            @if($item->right_product_image)
                            <div class="existing-img-container">
                                <div class="existing-img-box"><img src="{{ asset('landing_pages/'.$item->right_product_image)}}"></div>
                            </div>
                            @endif
                            <div class="file-upload-wrapper">
                                <div class="file-upload-text"><i class="fas fa-cloud-upload-alt mb-1 d-block"></i> Change Main Image</div>
                                <input type="file" name="right_product_image" class="upload-preview" data-target="main_img_preview" accept="image/*">
                            </div>
                            <div id="main_img_preview" class="img-preview-container"></div>
                        </div>

                        {{-- গ্যালারি / স্লাইডার ইমেজ (Multiple) --}}
                        <div class="form-group mb-4 border-top pt-4">
                            <label class="fw-bold mb-2 text-primary">প্রোডাক্ট গ্যালারি ছবি (Multiple)</label>
                            
                            @if($item->images && $item->images->count() > 0)
                            <div class="existing-img-container">
                                @foreach ($item->images as $sl)
                                    <div class="existing-img-box">
                                        <a href="{{ route('admin.delete_slider', [$sl->id]) }}" class="btn-delete-img"><i class="fas fa-times"></i></a>
                                        <img src="{{ asset('landing_sliders/'.$sl->image) }}">
                                    </div>
                                @endforeach
                            </div>
                            @endif

                            <div class="file-upload-wrapper" style="border-color: #0d6efd; background: #f0f8ff;">
                                <div class="file-upload-text text-primary"><i class="fas fa-layer-group mb-1 d-block"></i> Add More Gallery Images</div>
                                <input type="file" name="sliderimage[]" class="upload-preview" data-target="slider_img_preview" accept="image/*" multiple>
                            </div>
                            <div id="slider_img_preview" class="img-preview-container"></div>
                        </div>

                        {{-- Review Images --}}
                        <div class="mb-4 border-top pt-4">
                            <label class="fw-bold mb-2 text-warning">Customer Reviews (Multiple)</label>
                            <div class="existing-img-container">
                                @foreach ($review_images as $rv)
                                    <div class="existing-img-box">
                                        <a href="{{ route('admin.delete_review',[$rv->id])}}" class="btn-delete-img"><i class="fas fa-times"></i></a>
                                        <img src="{{ asset('review_landing_sliders/'.$rv->review_image)}}">
                                    </div>
                                @endforeach
                            </div>
                            <div class="file-upload-wrapper">
                                <div class="file-upload-text"><i class="fas fa-star mb-1 d-block text-warning"></i> Add More Review Images</div>
                                <input type="file" name="review_product_image[]" class="upload-preview" data-target="review_img_preview" accept="image/*" multiple>
                            </div>
                            <div id="review_img_preview" class="img-preview-container"></div>
                        </div>

                    </div>
                </div>

                {{-- 10. DESIGN SETTINGS --}}
                <div class="premium-card">
                    <div class="card-header-custom border-bottom-0"><i class="fas fa-palette text-dark"></i><h5 class="text-dark m-0 fw-bold">Design Settings</h5></div>
                    <div class="card-body-custom pt-0">
                        
                        {{-- ✅ FIXED: Loaded dynamically from $item variable --}}
                        <div class="mb-3 mt-3">
                            <label class="form-label fw-bold">Page Background Color</label>
                            <input type="color" value="{{ $item->theme_primary_col ?? '#fafafa' }}" name="theme_primary_col" class="form-control form-control-color w-100 p-1" style="height: 40px;">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Order Button BG Color</label>
                            <input type="color" value="{{ $item->btn_bg_color ?? '#e65100' }}" name="btn_bg_color" class="form-control form-control-color w-100 p-1" style="height: 40px;">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Order Button Text Color</label>
                            <input type="color" value="{{ $item->btn_text_color ?? '#ffffff' }}" name="btn_text_color" class="form-control form-control-color w-100 p-1" style="height: 40px;">
                        </div>
                    </div>
                </div>

                <button type="submit" id="save_btn" class="btn-save mb-5"><i class="fas fa-save"></i> Update Landing Page</button>

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
                toastr.success('Landing Page Updated successfully!'); 
                window.location.href = res.url || "{{ route('admin.landing_pages_six') }}"; 
            },
            error: function(xhr){ 
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Update Landing Page');
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