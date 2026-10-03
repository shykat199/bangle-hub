@extends('backend.app')
@section('title', 'Edit Landing Page (Design 10 — Skincare)')
@section('content')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
<style>
    .premium-card { background:#fff; border-radius:12px; box-shadow:0 4px 6px rgba(0,0,0,0.05); margin-bottom:20px; }
    .card-header-custom { padding:15px 20px; border-bottom:1px solid #eee; background:#f8fafc; font-weight:bold; color:#2563eb; border-radius:12px 12px 0 0; }
    .section-row { background:#f8fafc; padding:10px; border-radius:8px; margin-bottom:10px; }
    .img-box { position:relative; display:inline-block; margin-right:10px; margin-bottom:10px; }
    .img-box img { width:60px; height:60px; object-fit:cover; border-radius:6px; border:1px solid #ddd; }
    .img-box .btn-del { position:absolute; top:-5px; right:-5px; background:red; color:#fff; border-radius:50%; width:20px; height:20px; display:flex; align-items:center; justify-content:center; text-decoration:none; font-size:12px; }
</style>
@endpush

<div class="content-wrapper">
    <div class="container-fluid py-3">
        <form action="{{ route('admin.landing_pages_eleven.update', $item->id) }}" method="POST" enctype="multipart/form-data" id="ajax_form">
            @csrf
            @method('PATCH')
            <input type="hidden" name="page_type" value="11">

            <div class="row">
                <div class="col-lg-8">

                    {{-- 1. HEADER / HERO --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-heading"></i> ১. হেডার ও হিরো</div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="fw-bold">মেইন হেডলাইন <span class="text-danger">*</span></label>
                                <input type="text" name="title1" class="form-control" value="{{ $item->title1 }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">URL Slug <small class="text-muted">(optional — খালি রাখলে আগেরটাই থাকবে)</small></label>
                                <input type="text" name="slug" class="form-control" value="{{ $item->slug }}" placeholder="e.g. summer-sale-2026" pattern="[a-z0-9]+(-[a-z0-9]+)*" title="শুধু ছোট হাতের অক্ষর, সংখ্যা ও হাইফেন">
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">সাব-হেডলাইন</label>
                                <input type="text" name="title2" class="form-control" value="{{ $item->title2 }}">
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="fw-bold">যোগাযোগের নাম্বার <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control" value="{{ $item->phone }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="fw-bold">কল বাটন টেক্সট</label>
                                    <input type="text" name="call_text" class="form-control" value="{{ $item->call_text }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="fw-bold">রেটিং</label>
                                    <input type="text" name="hero_rating" class="form-control" value="{{ $item->hero_rating }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-bold">কাস্টমার সংখ্যা</label>
                                    <input type="text" name="hero_rating_count" class="form-control" value="{{ $item->hero_rating_count }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-bold">রেটিং লেবেল</label>
                                    <input type="text" name="hero_rating_label" class="form-control" value="{{ $item->hero_rating_label }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="fw-bold">সাশ্রয় টেক্সট</label>
                                    <input type="text" name="discount_save_text" class="form-control" value="{{ $item->discount_save_text }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="fw-bold">পেমেন্ট লেবেল</label>
                                    <input type="text" name="pay_text" class="form-control" value="{{ $item->pay_text }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">YouTube Embed URL / Iframe</label>
                                <input type="text" name="video_url" class="form-control" value="{{ $item->video_url }}" placeholder="https://www.youtube.com/embed/xxxxx">
                            </div>
                        </div>
                    </div>

                    {{-- 2. COUNTDOWN --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-stopwatch"></i> ২. কাউন্টডাউন</div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-8"><label class="fw-bold">কাউন্টডাউন টাইটেল</label><input type="text" name="countdown_title" class="form-control" value="{{ $item->countdown_title }}"></div>
                                <div class="col-md-4"><label class="fw-bold">ঘণ্টা</label><input type="number" name="countdown_hours" class="form-control" value="{{ $item->countdown_hours ?? 20 }}"></div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6"><label class="fw-bold">BG কালার</label><input type="color" name="countdown_bg_color" class="form-control" value="{{ $item->countdown_bg_color ?? '#0f172a' }}"></div>
                                <div class="col-md-6"><label class="fw-bold">টেক্সট কালার</label><input type="color" name="countdown_text_color" class="form-control" value="{{ $item->countdown_text_color ?? '#ffffff' }}"></div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. FEATURES (6 cards via id_X) --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-star"></i> ৩. প্রোডাক্ট ফিচার্স (৬টি কার্ড)</div>
                        <div class="card-body p-4">
                            <div class="mb-3"><label class="fw-bold">সেকশন টাইটেল</label><input type="text" name="feature_title" class="form-control" value="{{ $item->feature_title }}"></div>
                            @for($i=1; $i<=6; $i++)
                            <div class="section-row">
                                <div class="row g-2">
                                    <div class="col-md-2"><label class="small fw-bold">আইকন</label><input type="text" name="id_{{ $i }}_icon" class="form-control" value="{{ $item->{'id_'.$i.'_icon'} }}"></div>
                                    <div class="col-md-4"><label class="small fw-bold">টাইটেল</label><input type="text" name="id_{{ $i }}_title" class="form-control" value="{{ $item->{'id_'.$i.'_title'} }}"></div>
                                    <div class="col-md-6"><label class="small fw-bold">বর্ণনা</label><input type="text" name="id_{{ $i }}_desc" class="form-control" value="{{ $item->{'id_'.$i.'_desc'} }}"></div>
                                </div>
                            </div>
                            @endfor
                        </div>
                    </div>

                    {{-- 4. SPECIFICATIONS --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-list-alt"></i> ৪. স্পেসিফিকেশন</div>
                        <div class="card-body p-4">
                            <div class="mb-3"><label class="fw-bold">সেকশন টাইটেল</label><input type="text" name="spec_title" class="form-control" value="{{ $item->spec_title ?? 'প্রোডাক্ট স্পেসিফিকেশন' }}"></div>
                            @for($i=1; $i<=7; $i++)
                            <div class="row g-2 section-row">
                                <div class="col-md-5"><label class="small fw-bold">লেবেল {{ $i }}</label><input type="text" name="spec_{{ $i }}_label" class="form-control" value="{{ $item->{'spec_'.$i.'_label'} }}"></div>
                                <div class="col-md-7"><label class="small fw-bold">ভ্যালু {{ $i }}</label><input type="text" name="spec_{{ $i }}_value" class="form-control" value="{{ $item->{'spec_'.$i.'_value'} }}"></div>
                            </div>
                            @endfor
                        </div>
                    </div>

                    {{-- 5. REVIEWS --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-comments"></i> ৫. কাস্টমার রিভিউ</div>
                        <div class="card-body p-4">
                            <div class="row mb-3">
                                <div class="col-md-6"><label class="fw-bold">রিভিউ টাইটেল</label><input type="text" name="review_title" class="form-control" value="{{ $item->review_title }}"></div>
                                <div class="col-md-6"><label class="fw-bold">রিভিউ সাবটাইটেল</label><input type="text" name="review_subtitle" class="form-control" value="{{ $item->review_subtitle }}"></div>
                            </div>
                            <div class="row mb-3">
                                @for($i=1; $i<=3; $i++)
                                <div class="col-md-4">
                                    <label class="small fw-bold">Stat {{ $i }} Num</label>
                                    <input type="text" name="stat_{{ $i }}_num" class="form-control" value="{{ $item->{'stat_'.$i.'_num'} }}">
                                    <label class="small fw-bold mt-2">Stat {{ $i }} Text</label>
                                    <input type="text" name="stat_{{ $i }}_text" class="form-control" value="{{ $item->{'stat_'.$i.'_text'} }}">
                                </div>
                                @endfor
                            </div>
                            @for($i=1; $i<=3; $i++)
                            <div class="section-row">
                                <label class="fw-bold">রিভিউ {{ $i }}</label>
                                <textarea name="rev_{{ $i }}_text" class="form-control mb-2" rows="2">{{ $item->{'rev_'.$i.'_text'} }}</textarea>
                                <div class="row g-2">
                                    <div class="col-md-6"><input type="text" name="rev_{{ $i }}_name" class="form-control" value="{{ $item->{'rev_'.$i.'_name'} }}" placeholder="নাম"></div>
                                    <div class="col-md-6"><input type="text" name="rev_{{ $i }}_loc" class="form-control" value="{{ $item->{'rev_'.$i.'_loc'} }}" placeholder="ঠিকানা"></div>
                                </div>
                            </div>
                            @endfor
                        </div>
                    </div>

                    {{-- 6. STOCK URGENCY --}}
                    <div class="premium-card" style="border:2px solid #ef4444;">
                        <div class="card-header-custom" style="background:#fef2f2;color:#dc2626;"><i class="fas fa-fire"></i> ৬. স্টক আর্জেন্সি</div>
                        <div class="card-body p-4">
                            <div class="row mb-3">
                                <div class="col-md-4"><label class="fw-bold">স্টক সংখ্যা</label><input type="number" name="stock_count" class="form-control" value="{{ $item->stock_count ?? 24 }}"></div>
                                <div class="col-md-8"><label class="fw-bold">স্টক টেক্সট</label><input type="text" name="stock_text" class="form-control" value="{{ $item->stock_text }}"></div>
                            </div>
                            <div class="mb-3"><label class="fw-bold">আর্জেন্সি টাইটেল</label><input type="text" name="urgency_title" class="form-control" value="{{ $item->urgency_title }}"></div>
                            <div class="mb-3"><label class="fw-bold">আর্জেন্সি সাবটাইটেল</label><textarea name="urgency_subtitle" class="form-control" rows="2">{{ $item->urgency_subtitle }}</textarea></div>
                        </div>
                    </div>

                    {{-- 7. FAQ --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-question-circle"></i> ৭. FAQ</div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6"><label class="fw-bold">FAQ Title</label><input type="text" name="faq_title" class="form-control" value="{{ $item->faq_title }}"></div>
                                <div class="col-md-6"><label class="fw-bold">FAQ Badge</label><input type="text" name="faq_badge" class="form-control" value="{{ $item->faq_badge }}"></div>
                                @for($i=1; $i<=4; $i++)
                                <div class="col-md-12 border-bottom pb-2 mt-3">
                                    <label class="fw-bold">প্রশ্ন {{ $i }}</label>
                                    <input type="text" name="faq_{{ $i }}_q" class="form-control mb-2" value="{{ $item->{'faq_'.$i.'_q'} }}">
                                    <textarea name="faq_{{ $i }}_a" class="form-control" rows="2">{{ $item->{'faq_'.$i.'_a'} }}</textarea>
                                </div>
                                @endfor
                            </div>
                        </div>
                    </div>

                    {{-- 8. FINAL CTA + FOOTER --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-rocket"></i> ৮. ফাইনাল CTA ও ফুটার</div>
                        <div class="card-body p-4">
                            <div class="mb-3"><label class="fw-bold">CTA টাইটেল</label><input type="text" name="final_cta_title" class="form-control" value="{{ $item->final_cta_title }}"></div>
                            <div class="mb-3"><label class="fw-bold">CTA সাবটাইটেল</label><textarea name="final_cta_subtitle" class="form-control" rows="2">{{ $item->final_cta_subtitle }}</textarea></div>
                            <div class="mb-3"><label class="fw-bold">CTA বাটন টেক্সট</label><input type="text" name="final_cta_btn_text" class="form-control" value="{{ $item->final_cta_btn_text }}"></div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-md-6"><label class="fw-bold">ফুটার কোম্পানি</label><input type="text" name="footer_company" class="form-control" value="{{ $item->footer_company }}"></div>
                                <div class="col-md-6"><label class="fw-bold">ফুটার ইমেইল</label><input type="email" name="footer_email" class="form-control" value="{{ $item->footer_email }}"></div>
                            </div>
                            <div class="mb-3"><label class="fw-bold">কপিরাইট</label><input type="text" name="footer_copyright" class="form-control" value="{{ $item->footer_copyright }}"></div>
                            <div class="mb-3"><label class="fw-bold">সিকিউরিটি ব্যাজ টেক্সট</label><input type="text" name="security_badge_text" class="form-control" value="{{ $item->security_badge_text }}"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-box"></i> প্রোডাক্ট</div>
                        <div class="card-body p-4">
                            <div class="form-group mb-3 position-relative">
                                <label class="fw-bold">প্রোডাক্ট পরিবর্তন</label>
                                <input type="text" id="search_product" class="form-control" placeholder="নাম বা SKU..." autocomplete="off">
                                <input type="hidden" name="new_product_id" id="new_product_id" value="{{ $item->product_id }}" required>
                            </div>
                            <div id="selected_product_preview" class="mb-3 p-2 border rounded bg-light">
                                <b class="text-success"><i class="fas fa-check-circle"></i> সিলেক্টেড:</b>
                                <span id="selected_name" class="fw-bold">{{ $single_product ? $single_product->name : '' }}</span>
                            </div>
                            <div class="mb-3"><label class="fw-bold">New Price</label><input type="number" name="new_price" id="new_price_input" class="form-control" value="{{ $item->new_price }}"></div>
                            <div class="mb-3"><label class="fw-bold">Old Price</label><input type="number" name="old_price" class="form-control" value="{{ $item->old_price }}"></div>
                        </div>
                    </div>

                    <div class="premium-card" style="border:2px solid #2563eb;">
                        <div class="card-header-custom" style="background:#eff6ff;"><i class="fas fa-boxes"></i> প্যাকেজ</div>
                        <div class="card-body p-4">
                            <div id="packageTable">
                                @if($item->packages && $item->packages->count() > 0)
                                    @foreach($item->packages as $k => $pkg)
                                    <div class="pkg-edit-card mb-3 p-3" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; position:relative;">
                                        <button type="button" class="btn btn-sm btn-danger remove-row" style="position:absolute; top:8px; right:8px; padding:4px 8px;"><i class="fas fa-times"></i></button>
                                        <input type="radio" name="pkg_is_default" value="{{ $k }}" {{ $pkg->is_default ? 'checked' : '' }} class="d-none">
                                        <div class="mb-2"><label class="form-label small fw-bold mb-1">পরিমাণ (Qty) *</label><input type="number" name="pkg_qty[]" class="form-control form-control-sm" value="{{ $pkg->qty }}" required></div>
                                        <div class="mb-2"><label class="form-label small fw-bold mb-1">প্যাকেজ মূল্য (৳) *</label><input type="number" name="pkg_price[]" class="form-control form-control-sm" value="{{ $pkg->price }}" required></div>
                                        <div><label class="form-label small fw-bold mb-1">ডিসকাউন্ট টেক্সট</label><input type="text" name="pkg_discount_text[]" class="form-control form-control-sm" value="{{ $pkg->discount_text }}" placeholder="যেমন: ১৯০ সাশ্রয়"></div>
                                    </div>
                                    @endforeach
                                @else
                                    <div id="no_pkg_row" class="text-center text-muted py-3" style="background:#f8fafc; border:1px dashed #cbd5e1; border-radius:8px;">
                                        <i class="fas fa-info-circle"></i><br><small>কোন প্যাকেজ নেই। + Add Package চাপুন</small>
                                    </div>
                                @endif
                            </div>
                            <button type="button" id="addPkgRow" class="btn btn-sm btn-success"><i class="fas fa-plus"></i> Add Package</button>
                        </div>
                    </div>

                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-edit"></i> ফর্ম টেক্সট</div>
                        <div class="card-body p-4">
                            <div class="mb-2"><label class="small fw-bold">Form Title</label><input type="text" name="form_title" class="form-control" value="{{ $item->form_title }}"></div>
                            <div class="mb-2"><label class="small fw-bold">Form Subtitle</label><input type="text" name="form_subtitle" class="form-control" value="{{ $item->form_subtitle }}"></div>
                            <div class="mb-2"><label class="small fw-bold">Name Label</label><input type="text" name="name_label" class="form-control" value="{{ $item->name_label }}"></div>
                            <div class="mb-2"><label class="small fw-bold">Phone Label</label><input type="text" name="phone_label" class="form-control" value="{{ $item->phone_label }}"></div>
                            <div class="mb-2"><label class="small fw-bold">Address Label</label><input type="text" name="address_label" class="form-control" value="{{ $item->address_label }}"></div>
                            <div class="mb-2"><label class="small fw-bold">Delivery Label</label><input type="text" name="delivery_label" class="form-control" value="{{ $item->delivery_label }}"></div>
                            <div class="mb-2"><label class="small fw-bold">Order Btn Text</label><input type="text" name="btn_text_form" class="form-control" value="{{ $item->btn_text_form }}"></div>
                            <div class="mb-2"><label class="small fw-bold">Total Bill Label</label><input type="text" name="total_bill_label" class="form-control" value="{{ $item->total_bill_label }}"></div>
                        </div>
                    </div>

                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-palette"></i> থিম কালার</div>
                        <div class="card-body p-4">
                            <div class="mb-2"><label class="small fw-bold">Primary</label><input type="color" name="theme_primary_col" class="form-control" value="{{ $item->theme_primary_col ?? '#2563eb' }}"></div>
                            <div class="mb-2"><label class="small fw-bold">Button BG</label><input type="color" name="btn_bg_color" class="form-control" value="{{ $item->btn_bg_color ?? '#dc2626' }}"></div>
                            <div class="mb-2"><label class="small fw-bold">Button Text</label><input type="color" name="btn_text_color" class="form-control" value="{{ $item->btn_text_color ?? '#ffffff' }}"></div>
                        </div>
                    </div>

                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-images"></i> ছবি</div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="fw-bold d-block">প্রোডাক্ট ছবি</label>
                                @if($item->right_product_image)
                                    <div class="img-box mb-2"><img src="{{ asset('landing_pages/'.$item->right_product_image)}}"></div>
                                @endif
                                <input type="file" name="right_product_image" class="form-control" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold d-block">স্লাইডার ছবি (Multiple)</label>
                                @if($item->images && $item->images->count() > 0)
                                    <div class="mb-2">
                                        @foreach($item->images as $sl)
                                        <div class="img-box">
                                            <a href="{{ route('admin.delete_slider', $sl->id) }}" class="btn-del">&times;</a>
                                            <img src="{{ asset('landing_sliders/'.$sl->image) }}">
                                        </div>
                                        @endforeach
                                    </div>
                                @endif
                                <input type="file" name="sliderimage[]" class="form-control" accept="image/*" multiple>
                            </div>
                        </div>
                    </div>

                    <button type="submit" id="save_btn" class="btn btn-primary btn-lg w-100 fw-bold"><i class="fas fa-sync"></i> Update Page</button>
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
        $("#search_product").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "{{ route('admin.products.search') }}", type: 'GET', dataType: "json", data: { q: request.term },
                    success: function(data) { response(data); }
                });
            },
            select: function(event, ui) {
                $('#new_product_id').val(ui.item.id);
                $('#selected_name').text(ui.item.name);
                $('#new_price_input').val(ui.item.price);
                $('#search_product').val(''); return false;
            }
        }).autocomplete("instance")._renderItem = function(ul, item) {
            return $("<li>").append(`<div class="d-flex align-items-center p-2 border-bottom"><img src="${item.image}" width="40" height="40" class="me-2 rounded"><div><h6 class="m-0">${item.name}</h6><small class="text-danger fw-bold">৳${item.price}</small></div></div>`).appendTo(ul);
        };

        let pkgIndex = 99;
        $(document).on('click', '#addPkgRow', function(e) {
            e.preventDefault();
            let tr = `<div class="pkg-edit-card mb-3 p-3" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; position:relative;">
                <button type="button" class="btn btn-sm btn-danger remove-row" style="position:absolute; top:8px; right:8px; padding:4px 8px;"><i class="fas fa-times"></i></button>
                <input type="radio" name="pkg_is_default" value="${pkgIndex}" class="d-none">
                <div class="mb-2"><label class="form-label small fw-bold mb-1">পরিমাণ (Qty) *</label><input type="number" name="pkg_qty[]" class="form-control form-control-sm" required></div>
                <div class="mb-2"><label class="form-label small fw-bold mb-1">প্যাকেজ মূল্য (৳) *</label><input type="number" name="pkg_price[]" class="form-control form-control-sm" required></div>
                <div><label class="form-label small fw-bold mb-1">ডিসকাউন্ট টেক্সট</label><input type="text" name="pkg_discount_text[]" class="form-control form-control-sm" placeholder="যেমন: ১৯০ সাশ্রয়"></div>
            </div>`;
            $('#packageTable').append(tr);
            pkgIndex++;
        });
        $(document).on('click', '.remove-row', function() { $(this).closest('.pkg-edit-card, tr').remove(); });

        $('#ajax_form').submit(function(e) {
            e.preventDefault();
            let btn = $('#save_btn');
            if(btn.data('submitting')) return;
            btn.data('submitting', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
            let formData = new FormData(this);
            $.ajax({
                url: $(this).attr('action'), type: 'POST', data: formData, processData: false, contentType: false,
                success: function(res) {
                    window.location.href = res.url || "{{ route('admin.landing_pages_eleven') }}";
                },
                error: function(xhr) {
                    toastr.error('Error occurred!');
                    btn.data('submitting', false).html('<i class="fas fa-sync"></i> Update Page');
                }
            });
        });
    });
</script>
@endpush
