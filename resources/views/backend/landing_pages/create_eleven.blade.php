@extends('backend.app')
@section('title', 'Create Landing Page (Design 10 — Skincare)')
@section('content')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
<style>
    .premium-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px; }
    .card-header-custom { padding: 15px 20px; border-bottom: 1px solid #eee; background: #f8fafc; font-weight: bold; color: #2563eb; border-radius: 12px 12px 0 0; }
    .section-row { background:#f8fafc; padding:10px; border-radius:8px; margin-bottom:10px; }
</style>
@endpush

<div class="content-wrapper">
    <div class="container-fluid py-3">
        <form action="{{ route('admin.landing_pages_eleven.store') }}" method="POST" enctype="multipart/form-data" id="ajax_form">
            @csrf
            <input type="hidden" name="page_type" value="11">

            <div class="row">
                <div class="col-lg-8">

                    {{-- 1. HEADER / HERO --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-heading"></i> ১. হেডার ও হিরো সেকশন</div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="fw-bold">মেইন হেডলাইন (Title 1) <span class="text-danger">*</span></label>
                                <input type="text" name="title1" class="form-control" placeholder="যেমন: গরমে যেখানেই যান, ঠাণ্ডা বাতাস সাথেই" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">URL Slug <small class="text-muted">(optional — খালি রাখলে টাইটেল থেকে বানানো হবে)</small></label>
                                <input type="text" name="slug" class="form-control" placeholder="e.g. summer-sale-2026" pattern="[a-z0-9]+(-[a-z0-9]+)*" title="শুধু ছোট হাতের অক্ষর, সংখ্যা ও হাইফেন">
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">সাব-হেডলাইন (Title 2)</label>
                                <input type="text" name="title2" class="form-control" placeholder="যেমন: টার্বো মোটর, ৪০০০mAh ব্যাটারি, ১৮ ঘণ্টা ব্যাকআপ">
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="fw-bold">যোগাযোগের নাম্বার (Phone) <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control" placeholder="০১৭০০-০০০০০০" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="fw-bold">কল বাটন টেক্সট</label>
                                    <input type="text" name="call_text" class="form-control" value="এখনই অর্ডার করুন">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="fw-bold">রেটিং (Hero Rating)</label>
                                    <input type="text" name="hero_rating" class="form-control" value="4.9">
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-bold">কাস্টমার সংখ্যা</label>
                                    <input type="text" name="hero_rating_count" class="form-control" value="2,300+ সন্তুষ্ট কাস্টমার">
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-bold">রেটিং লেবেল</label>
                                    <input type="text" name="hero_rating_label" class="form-control" value="রেটিং">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="fw-bold">সাশ্রয় টেক্সট (Discount Save)</label>
                                    <input type="text" name="discount_save_text" class="form-control" placeholder="যেমন: ৳560 সাশ্রয়">
                                </div>
                                <div class="col-md-6">
                                    <label class="fw-bold">পেমেন্ট লেবেল (pay_text)</label>
                                    <input type="text" name="pay_text" class="form-control" value="ক্যাশ অন ডেলিভারি">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">YouTube Embed URL / Iframe (ঐচ্ছিক)</label>
                                <input type="text" name="video_url" class="form-control" placeholder="https://www.youtube.com/embed/xxxxx">
                                <small class="text-muted">খালি রাখলে শুধু প্রোডাক্ট ছবি দেখাবে। ভিডিও দিলে হিরো সেকশনে ভিডিও বসবে।</small>
                            </div>
                        </div>
                    </div>

                    {{-- 2. COUNTDOWN --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-stopwatch"></i> ২. কাউন্টডাউন টাইমার</div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-8">
                                    <label class="fw-bold">কাউন্টডাউন টাইটেল</label>
                                    <input type="text" name="countdown_title" class="form-control" value="অফার শেষ হতে বাকি:">
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-bold">ঘণ্টা (Hours)</label>
                                    <input type="number" name="countdown_hours" class="form-control" value="20">
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="fw-bold">কাউন্টডাউন BG কালার</label>
                                    <input type="color" name="countdown_bg_color" class="form-control" value="#0f172a">
                                </div>
                                <div class="col-md-6">
                                    <label class="fw-bold">কাউন্টডাউন টেক্সট কালার</label>
                                    <input type="color" name="countdown_text_color" class="form-control" value="#ffffff">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. FEATURES --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-star"></i> ৩. প্রোডাক্ট ফিচার্স (৬টি কার্ড)</div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="fw-bold">ফিচার সেকশন টাইটেল</label>
                                <input type="text" name="feature_title" class="form-control" value="কেন আপনি এই প্রোডাক্ট নেবেন?">
                            </div>
                            @for($i=1; $i<=6; $i++)
                            <div class="section-row">
                                <div class="row g-2">
                                    <div class="col-md-2">
                                        <label class="small fw-bold">আইকন ক্লাস</label>
                                        <input type="text" name="id_{{ $i }}_icon" class="form-control" placeholder="fa-bolt">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small fw-bold">টাইটেল</label>
                                        <input type="text" name="id_{{ $i }}_title" class="form-control" placeholder="ফিচার {{ $i }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small fw-bold">বর্ণনা</label>
                                        <input type="text" name="id_{{ $i }}_desc" class="form-control" placeholder="সংক্ষিপ্ত বর্ণনা">
                                    </div>
                                </div>
                            </div>
                            @endfor
                        </div>
                    </div>

                    {{-- 4. SPECIFICATIONS --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-list-alt"></i> ৪. স্পেসিফিকেশন টেবিল</div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="fw-bold">সেকশন টাইটেল</label>
                                <input type="text" name="spec_title" class="form-control" value="প্রোডাক্ট স্পেসিফিকেশন">
                            </div>
                            @for($i=1; $i<=7; $i++)
                            <div class="row g-2 section-row">
                                <div class="col-md-5">
                                    <label class="small fw-bold">লেবেল {{ $i }}</label>
                                    <input type="text" name="spec_{{ $i }}_label" class="form-control" placeholder="যেমন: ব্যাটারি">
                                </div>
                                <div class="col-md-7">
                                    <label class="small fw-bold">ভ্যালু {{ $i }}</label>
                                    <input type="text" name="spec_{{ $i }}_value" class="form-control" placeholder="যেমন: 4000mAh Lithium-ion">
                                </div>
                            </div>
                            @endfor
                        </div>
                    </div>

                    {{-- 5. REVIEWS --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-comments"></i> ৫. কাস্টমার রিভিউ ও স্ট্যাট</div>
                        <div class="card-body p-4">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="fw-bold">রিভিউ সেকশন টাইটেল</label>
                                    <input type="text" name="review_title" class="form-control" value="কাস্টমার রিভিউ">
                                </div>
                                <div class="col-md-6">
                                    <label class="fw-bold">রিভিউ সাবটাইটেল</label>
                                    <input type="text" name="review_subtitle" class="form-control" value="তাদের অভিজ্ঞতা শুনুন">
                                </div>
                            </div>
                            <div class="row mb-3">
                                @for($i=1; $i<=3; $i++)
                                <div class="col-md-4">
                                    <label class="small fw-bold">Stat {{ $i }} Num</label>
                                    <input type="text" name="stat_{{ $i }}_num" class="form-control" placeholder="যেমন: 2,300+">
                                    <label class="small fw-bold mt-2">Stat {{ $i }} Text</label>
                                    <input type="text" name="stat_{{ $i }}_text" class="form-control" placeholder="যেমন: সন্তুষ্ট কাস্টমার">
                                </div>
                                @endfor
                            </div>
                            @for($i=1; $i<=3; $i++)
                            <div class="section-row">
                                <label class="fw-bold">রিভিউ {{ $i }}</label>
                                <textarea name="rev_{{ $i }}_text" class="form-control mb-2" rows="2" placeholder="কাস্টমারের রিভিউ"></textarea>
                                <div class="row g-2">
                                    <div class="col-md-6"><input type="text" name="rev_{{ $i }}_name" class="form-control" placeholder="নাম"></div>
                                    <div class="col-md-6"><input type="text" name="rev_{{ $i }}_loc" class="form-control" placeholder="ঠিকানা (ঢাকা/চট্টগ্রাম...)"></div>
                                </div>
                            </div>
                            @endfor
                        </div>
                    </div>

                    {{-- 6. STOCK URGENCY --}}
                    <div class="premium-card" style="border:2px solid #ef4444;">
                        <div class="card-header-custom" style="background:#fef2f2;color:#dc2626;"><i class="fas fa-fire"></i> ৬. স্টক আর্জেন্সি সেকশন</div>
                        <div class="card-body p-4">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="fw-bold">স্টক সংখ্যা</label>
                                    <input type="number" name="stock_count" class="form-control" value="24">
                                </div>
                                <div class="col-md-8">
                                    <label class="fw-bold">স্টক টেক্সট (Use {count} placeholder)</label>
                                    <input type="text" name="stock_text" class="form-control" value="মাত্র {count}টি স্টক বাকি">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">আর্জেন্সি টাইটেল</label>
                                <input type="text" name="urgency_title" class="form-control" value="দেরি করবেন না — স্টক সীমিত!">
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">আর্জেন্সি সাবটাইটেল</label>
                                <textarea name="urgency_subtitle" class="form-control" rows="2" placeholder="৩০% ছাড় ও ফ্রি ডেলিভারি অফার"></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- 7. FAQ --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-question-circle"></i> ৭. সচরাচর জিজ্ঞাসা (FAQ)</div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="fw-bold">FAQ Title</label>
                                    <input type="text" name="faq_title" class="form-control" value="সচরাচর জিজ্ঞাসিত প্রশ্ন">
                                </div>
                                <div class="col-md-6">
                                    <label class="fw-bold">FAQ Badge</label>
                                    <input type="text" name="faq_badge" class="form-control" value="FAQ">
                                </div>
                                @for($i=1; $i<=4; $i++)
                                <div class="col-md-12 border-bottom pb-2 mt-3">
                                    <label class="fw-bold">প্রশ্ন {{ $i }}</label>
                                    <input type="text" name="faq_{{ $i }}_q" class="form-control mb-2" placeholder="প্রশ্ন">
                                    <textarea name="faq_{{ $i }}_a" class="form-control" rows="2" placeholder="উত্তর"></textarea>
                                </div>
                                @endfor
                            </div>
                        </div>
                    </div>

                    {{-- 8. FINAL CTA + FOOTER --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-rocket"></i> ৮. ফাইনাল CTA ও ফুটার</div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="fw-bold">ফাইনাল CTA টাইটেল</label>
                                <input type="text" name="final_cta_title" class="form-control" value="অর্ডার করুন এখনই">
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">ফাইনাল CTA সাবটাইটেল</label>
                                <textarea name="final_cta_subtitle" class="form-control" rows="2" placeholder="৩০% ছাড় — সীমিত সময়ের অফার"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">ফাইনাল CTA বাটন টেক্সট</label>
                                <input type="text" name="final_cta_btn_text" class="form-control" value="অর্ডার করুন এখনই">
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="fw-bold">ফুটার কোম্পানি নাম</label>
                                    <input type="text" name="footer_company" class="form-control" placeholder="যেমন: CoolBreeze BD">
                                </div>
                                <div class="col-md-6">
                                    <label class="fw-bold">ফুটার ইমেইল</label>
                                    <input type="email" name="footer_email" class="form-control" placeholder="support@example.bd">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">কপিরাইট টেক্সট</label>
                                <input type="text" name="footer_copyright" class="form-control" value="© {{ date('Y') }} All rights reserved.">
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">সিকিউরিটি ব্যাজ টেক্সট</label>
                                <input type="text" name="security_badge_text" class="form-control" value="আপনার তথ্য ১০০% নিরাপদ">
                            </div>
                        </div>
                    </div>

                </div>

                {{-- RIGHT COLUMN --}}
                <div class="col-lg-4">
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-box"></i> প্রোডাক্ট সিলেক্ট</div>
                        <div class="card-body p-4">
                            <div class="form-group mb-3 position-relative">
                                <label class="fw-bold">প্রোডাক্ট সার্চ করুন <span class="text-danger">*</span></label>
                                <input type="text" id="search_product" class="form-control" placeholder="নাম বা SKU..." autocomplete="off">
                                <input type="hidden" name="product_id" id="new_product_id" required>
                            </div>
                            <div id="selected_product_preview" class="mb-3 p-2 border rounded bg-light" style="display:none;">
                                <b class="text-success"><i class="fas fa-check-circle"></i> সিলেক্টেড:</b> <span id="selected_name" class="fw-bold"></span>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">অফার মূল্য (New Price)</label>
                                <input type="number" name="new_price" id="new_price_input" class="form-control" placeholder="যেমন: 1290">
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">রেগুলার মূল্য (Old Price)</label>
                                <input type="number" name="old_price" class="form-control" placeholder="যেমন: 1850">
                            </div>
                        </div>
                    </div>

                    {{-- Package / Bundle --}}
                    <div class="premium-card" style="border: 2px solid #2563eb;">
                        <div class="card-header-custom" style="background:#eff6ff;"><i class="fas fa-boxes"></i> প্যাকেজ অফার</div>
                        <div class="card-body p-4">
                            <div id="packageTable">
                                {{-- ✅ ডিফল্ট প্যাকেজের ভ্যালুগুলো ফাঁকা করে দেওয়া হয়েছে --}}
                                <div class="pkg-edit-card mb-3 p-3" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; position:relative;">
                                    <button type="button" class="btn btn-sm btn-danger remove-row" style="position:absolute; top:8px; right:8px; padding:4px 8px;"><i class="fas fa-times"></i></button>
                                    <input type="radio" name="pkg_is_default" value="0" checked class="d-none">
                                    <div class="mb-2"><label class="form-label small fw-bold mb-1">পরিমাণ (Qty)</label><input type="number" name="pkg_qty[]" class="form-control form-control-sm" placeholder="যেমন: 1"></div>
                                    <div class="mb-2"><label class="form-label small fw-bold mb-1">প্যাকেজ মূল্য (৳)</label><input type="number" name="pkg_price[]" class="form-control form-control-sm" placeholder="প্যাকেজ মূল্য"></div>
                                    <div><label class="form-label small fw-bold mb-1">ডিসকাউন্ট টেক্সট</label><input type="text" name="pkg_discount_text[]" class="form-control form-control-sm" placeholder="যেমন: ১৯০ সাশ্রয়"></div>
                                </div>
                            </div>
                            <button type="button" id="addPkgRow" class="btn btn-sm btn-success"><i class="fas fa-plus"></i> Add Package</button>
                        </div>
                    </div>

                    {{-- Order Form Labels --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-edit"></i> অর্ডার ফর্ম টেক্সট</div>
                        <div class="card-body p-4">
                            <div class="mb-2"><label class="small fw-bold">Form Title</label><input type="text" name="form_title" class="form-control" value="অর্ডার করুন এখনই"></div>
                            <div class="mb-2"><label class="small fw-bold">Form Subtitle</label><input type="text" name="form_subtitle" class="form-control" value="দ্রুত ডেলিভারির জন্য সঠিক তথ্য দিন"></div>
                            <div class="mb-2"><label class="small fw-bold">Name Label</label><input type="text" name="name_label" class="form-control" value="আপনার নাম *"></div>
                            <div class="mb-2"><label class="small fw-bold">Phone Label</label><input type="text" name="phone_label" class="form-control" value="মোবাইল নাম্বার *"></div>
                            <div class="mb-2"><label class="small fw-bold">Address Label</label><input type="text" name="address_label" class="form-control" value="সম্পূর্ণ ঠিকানা *"></div>
                            <div class="mb-2"><label class="small fw-bold">Delivery Label</label><input type="text" name="delivery_label" class="form-control" value="ডেলিভারি এরিয়া *"></div>
                            <div class="mb-2"><label class="small fw-bold">Order Btn Text</label><input type="text" name="btn_text_form" class="form-control" value="অর্ডার কনফার্ম করুন"></div>
                            <div class="mb-2"><label class="small fw-bold">Total Bill Label</label><input type="text" name="total_bill_label" class="form-control" value="সর্বমোট বিল"></div>
                        </div>
                    </div>

                    {{-- Theme Colors --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-palette"></i> থিম কালার</div>
                        <div class="card-body p-4">
                            <div class="mb-2"><label class="small fw-bold">Primary Color</label><input type="color" name="theme_primary_col" class="form-control" value="#2563eb"></div>
                            <div class="mb-2"><label class="small fw-bold">Button BG</label><input type="color" name="btn_bg_color" class="form-control" value="#dc2626"></div>
                            <div class="mb-2"><label class="small fw-bold">Button Text</label><input type="color" name="btn_text_color" class="form-control" value="#ffffff"></div>
                        </div>
                    </div>

                    {{-- Images --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-images"></i> ছবি</div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="fw-bold">প্রোডাক্ট ছবি (Hero)</label>
                                <input type="file" name="right_product_image" class="form-control" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">স্লাইডার ছবি (Multiple)</label>
                                <input type="file" name="sliderimage[]" class="form-control" accept="image/*" multiple>
                            </div>
                        </div>
                    </div>

                    <button type="submit" id="save_btn" class="btn btn-primary btn-lg w-100 fw-bold"><i class="fas fa-save"></i> Save Landing Page</button>
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
        // Product Live Search
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
                $('#selected_product_preview').fadeIn();
                $('#new_price_input').val(ui.item.price);
                $('#search_product').val(''); return false;
            }
        }).autocomplete("instance")._renderItem = function(ul, item) {
            return $("<li>").append(`<div class="d-flex align-items-center p-2 border-bottom"><img src="${item.image}" width="40" height="40" class="me-2 rounded"><div><h6 class="m-0">${item.name}</h6><small class="text-danger fw-bold">৳${item.price}</small></div></div>`).appendTo(ul);
        };

        // Add Package Row — event delegation (DOM ready agei cholbe)
        let pkgIndex = 1;
        $(document).off('click', '#addPkgRow').on('click', '#addPkgRow', function(e) {
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

        // Submit
        $('#ajax_form').submit(function(e) {
            e.preventDefault();
            let btn = $('#save_btn');
            if(btn.data('submitting')) return;
            btn.data('submitting', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            let formData = new FormData(this);
            $.ajax({
                url: $(this).attr('action'), type: 'POST', data: formData, processData: false, contentType: false,
                success: function(res) {
                    window.location.href = res.url || "{{ route('admin.landing_pages_eleven') }}";
                },
                error: function(xhr) {
                    toastr.error('Error occurred!');
                    btn.data('submitting', false).html('<i class="fas fa-save"></i> Save Landing Page');
                }
            });
        });
    });
</script>
@endpush