@extends('backend.app')

@section('content')
@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
<style>
    :root { --pri:#15803d; --pri-dark:#14532d; --pri-light:#f0fdf4; --pri-bdr:#bbf7d0; }
    .premium-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(20,83,45,0.05); border: 1px solid #dcfce7; margin-bottom: 24px; overflow: hidden; }
    .card-header-custom { padding: 16px 22px; border-bottom: 1px solid #dcfce7; background: var(--pri-light); display: flex; align-items: center; gap: 10px; }
    .card-header-custom h5 { margin: 0; font-weight: 700; color: var(--pri-dark); font-size: 1.05rem; }
    .card-header-custom i { color: var(--pri); font-size: 1.1rem; }
    .card-body-custom { padding: 22px; }
    .form-label { font-weight: 600; color: #374151; margin-bottom: 6px; font-size: 0.88rem; }
    .form-control, .form-select { border-radius: 8px; border: 1px solid #d1d5db; padding: 9px 13px; font-size: 0.92rem; }
    .form-control:focus, .form-select:focus { border-color: var(--pri); box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1); }
    .product-search-box { background: var(--pri-light); padding: 16px; border-radius: 12px; border: 1px solid var(--pri-bdr); }
    .btn-save { background: linear-gradient(135deg, var(--pri), var(--pri-dark)); color: white; padding: 12px 30px; border-radius: 50px; font-weight: 700; border: none; width: 100%; box-shadow: 0 4px 12px rgba(22,101,52,0.25); }
    .btn-save:hover { color: white; transform: translateY(-1px); }
    .section-tabs .nav-link { color: #4b5563; font-weight: 600; border: none; padding: 10px 16px; border-radius: 8px 8px 0 0; }
    .section-tabs .nav-link.active { background: var(--pri-light); color: var(--pri-dark); border-bottom: 3px solid var(--pri); }
    .mini-row { background: #fafafa; border: 1px solid #e5e7eb; border-radius: 10px; padding: 14px; margin-bottom: 12px; }
    .mini-row .form-label { font-size: 0.8rem; margin-bottom: 4px; color: #6b7280; }
</style>
@endpush

<div class="container-fluid">
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.landing_pages_sixteen.store') }}" id="ajax_form">
        @csrf
        <input type="hidden" name="page_type" value="16">

        <div class="row">
            <div class="col-lg-8">

                <ul class="nav section-tabs mb-3 flex-wrap" role="tablist">
                    <li class="nav-item"><button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-hero">Hero</button></li>
                    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-features">4 Features & Gallery</button></li>
                    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-spec">Spec Table</button></li>
                    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-reviews">Reviews</button></li>
                    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-sizeguide">Size Guide / Trust</button></li>
                    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-faq">FAQ</button></li>
                    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-footer">Order / Footer</button></li>
                </ul>

                <div class="tab-content">
                    {{-- HERO TAB --}}
                    <div class="tab-pane fade show active" id="tab-hero">
                        <div class="premium-card">
                            <div class="card-header-custom"><i class="fas fa-star"></i><h5>Hero Section</h5></div>
                            <div class="card-body-custom">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Hero Main Title *</label>
                                        <input type="text" name="title1" class="form-control" value="প্রিমিয়াম কোয়ালিটির পাঞ্জাবি" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">URL Slug <small class="text-muted">(optional — খালি রাখলে টাইটেল থেকে বানানো হবে)</small></label>
                                        <input type="text" name="slug" class="form-control" placeholder="e.g. summer-sale-2026" pattern="[a-z0-9]+(-[a-z0-9]+)*" title="শুধু ছোট হাতের অক্ষর, সংখ্যা ও হাইফেন">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Brand Name (Logo)</label>
                                        <input type="text" name="title2" class="form-control" placeholder="প্রিমিয়াম পাঞ্জাবি / PUNJABI HUB">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Top Bar Countdown Text</label>
                                        <input type="text" name="countdown_title" class="form-control" placeholder="বিশেষ অফার শেষ হবে">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Countdown Hours</label>
                                        <input type="number" name="countdown_hours" class="form-control" value="3">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" name="phone" class="form-control" placeholder="01XXX-XXXXXX">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">WhatsApp Number</label>
                                        <input type="text" name="whatsapp" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Old Price (৳)</label>
                                        <input type="number" name="old_price" class="form-control" value="1990">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">New Price (৳)</label>
                                        <input type="number" name="new_price" class="form-control" value="1290">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Saved Badge Text</label>
                                        <input type="text" name="discount_save_text" class="form-control" placeholder="700৳ বাঁচান">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Hero CTA Button Text</label>
                                        <input type="text" name="btn_text_hero" class="form-control" placeholder="এখনই অর্ডার করুন">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">YouTube / Video URL or Embed</label>
                                        <input type="text" name="video_url" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 4 FEATURES & GALLERY --}}
                    <div class="tab-pane fade" id="tab-features">
                        <div class="premium-card">
                            <div class="card-header-custom"><i class="fas fa-th"></i><h5>"Why Special?" 4 Cards & Gallery</h5></div>
                            <div class="card-body-custom">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Section Title (Why Special)</label>
                                        <input type="text" name="feature_title" class="form-control" placeholder="কেন এই পাঞ্জাবিটি বিশেষ?">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Gallery Section Title</label>
                                        <input type="text" name="identify_title" class="form-control" placeholder="প্রতিটি ডিটেইলে অভিজাত্য">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Gallery Subtitle</label>
                                        <input type="text" name="identify_subtitle" class="form-control" placeholder="নিজের চোখে দেখুন প্রতিটি ডিটেইল">
                                    </div>
                                </div>
                                @php $fd = [['fas fa-shield-alt','প্রিমিয়াম ফেব্রিক','উন্নতমানের কটন এবং লিনেন কাপড়।'],['fas fa-cut','নিখুঁত স্টিচিং','ডাবল-সিম স্টিচিং, দীর্ঘস্থায়ী।'],['fas fa-shopping-bag','একচুয়েটিং ডিজাইন','ক্ল্যাসিক ও মডার্ন স্টাইল।'],['fas fa-tint','সফট ফ্যাব্রিক','সফট ও কুল, সারা দিন আরাম।']]; @endphp
                                @foreach([1,2,3,4] as $n)
                                <div class="mini-row">
                                    <h6 class="text-success fw-bold mb-2">Feature #{{ $n }}</h6>
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <label class="form-label">Icon (FA class)</label>
                                            <input type="text" name="id_{{ $n }}_icon" class="form-control" placeholder="{{ $fd[$n-1][0] }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Title</label>
                                            <input type="text" name="id_{{ $n }}_title" class="form-control" placeholder="{{ $fd[$n-1][1] }}">
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">Description</label>
                                            <input type="text" name="id_{{ $n }}_desc" class="form-control" placeholder="{{ $fd[$n-1][2] }}">
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- SPEC --}}
                    <div class="tab-pane fade" id="tab-spec">
                        <div class="premium-card">
                            <div class="card-header-custom"><i class="fas fa-table"></i><h5>Product Specification</h5></div>
                            <div class="card-body-custom">
                                <div class="mb-3">
                                    <label class="form-label">Spec Section Title</label>
                                    <input type="text" name="spec_title" class="form-control" placeholder="প্রোডাক্ট স্পেসিফিকেশন">
                                </div>
                                @php $sd = [['টিস্যু','১০০% প্রিমিয়াম কটন'],['কাটিং / টাইপ','সেমি-ফিট রেগুলার'],['সাইজ','M, L, XL, XXL'],['ফিট','রেগুলার ফিট'],['কালার','সবুজ ও অন্যান্য'],['ওজন','সাধারণ ব্যবহার'],['প্রিন্টিং','৭ দিন গ্যারান্টি']]; @endphp
                                @foreach([1,2,3,4,5,6,7] as $n)
                                <div class="mini-row">
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <label class="form-label">Row {{ $n }} - Label</label>
                                            <input type="text" name="spec_{{ $n }}_label" class="form-control" placeholder="{{ $sd[$n-1][0] }}">
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label">Row {{ $n }} - Value</label>
                                            <input type="text" name="spec_{{ $n }}_value" class="form-control" placeholder="{{ $sd[$n-1][1] }}">
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- REVIEWS --}}
                    <div class="tab-pane fade" id="tab-reviews">
                        <div class="premium-card">
                            <div class="card-header-custom"><i class="fas fa-star"></i><h5>Customer Reviews (3 cards)</h5></div>
                            <div class="card-body-custom">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Section Badge</label>
                                        <input type="text" name="review_badge" class="form-control" placeholder="রিভিউ">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Section Title</label>
                                        <input type="text" name="review_title" class="form-control" placeholder="কাস্টমার রিভিউ">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Section Subtitle (rating line)</label>
                                        <input type="text" name="review_subtitle" class="form-control" placeholder="★★★★★ ৪.৯/৫ — ৪৫০০+ রিভিউ">
                                    </div>
                                </div>
                                @php $rd = [['যা প্রত্যাশা করেছিলাম তার চেয়ে অনেক ভালো।','মুহাম্মদ রহিম','চট্টগ্রাম'],['ফিটিং নিখুঁত, স্টিচিং দারুণ।','রাশিদুল আনোয়ার','ঢাকা'],['এই দামে এত ভালো পাঞ্জাবি — সেরা ডিল।','সাকিব ইসলাম','সিলেট']]; @endphp
                                @foreach([1,2,3] as $n)
                                <div class="mini-row">
                                    <h6 class="text-success fw-bold mb-2">Review #{{ $n }}</h6>
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <label class="form-label">Review Text</label>
                                            <textarea name="rev_{{ $n }}_text" class="form-control" rows="2" placeholder="{{ $rd[$n-1][0] }}"></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Customer Name</label>
                                            <input type="text" name="rev_{{ $n }}_name" class="form-control" placeholder="{{ $rd[$n-1][1] }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Location</label>
                                            <input type="text" name="rev_{{ $n }}_loc" class="form-control" placeholder="{{ $rd[$n-1][2] }}">
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- SIZE GUIDE + TRUST --}}
                    <div class="tab-pane fade" id="tab-sizeguide">
                        <div class="premium-card">
                            <div class="card-header-custom"><i class="fas fa-ruler"></i><h5>Size Guide Table</h5></div>
                            <div class="card-body-custom">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Size Guide Title</label>
                                        <input type="text" name="urgency_title" class="form-control" placeholder="সাইজ গাইড">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Size Guide Subtitle</label>
                                        <input type="text" name="urgency_subtitle" class="form-control" placeholder="অর্ডারের আগে সঠিক সাইজটি বেছে নিন">
                                    </div>
                                </div>
                                @php $sg = [['M','৪০','২৭','৩৩'],['L','৪২','২৮','৩৪'],['XL','৪৪','২৯','৩৫'],['XXL','৪৬','৩০','৩৬']]; @endphp
                                @foreach([1,2,3,4] as $n)
                                <div class="mini-row">
                                    <h6 class="text-success fw-bold mb-2">Size Row {{ $n }}</h6>
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <label class="form-label">Size Name</label>
                                            <input type="text" name="trust_{{ $n }}_title" class="form-control" placeholder="{{ $sg[$n-1][0] }}">
                                        </div>
                                        <div class="col-md-9">
                                            <label class="form-label">Measurements (Chest|Length|Sleeve pipe-separated)</label>
                                            <input type="text" name="trust_{{ $n }}_icon" class="form-control" placeholder="{{ $sg[$n-1][1] }}|{{ $sg[$n-1][2] }}|{{ $sg[$n-1][3] }}">
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="premium-card">
                            <div class="card-header-custom"><i class="fas fa-shield-alt"></i><h5>Trust Badges (4 — bottom row)</h5></div>
                            <div class="card-body-custom">
                                @php $td = [['fas fa-truck','দ্রুত ডেলিভারি','২-৪ কর্মদিবস'],['fas fa-money-bill','ক্যাশ অন ডেলিভারি','বাসায় বসেই দিন'],['fas fa-credit-card','সিউর পেমেন্ট','১০০% নিরাপদ'],['fas fa-check-double','১০০% অরিজিনাল','রিপ্লেসমেন্ট গ্যারান্টি']]; @endphp
                                @foreach([5,6,7,8] as $idx => $n)
                                <div class="mini-row">
                                    <h6 class="text-success fw-bold mb-2">Trust Card #{{ $idx+1 }}</h6>
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <label class="form-label">Icon (FA class)</label>
                                            <input type="text" name="id_{{ $n }}_icon" class="form-control" placeholder="{{ $td[$idx][0] }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Title</label>
                                            <input type="text" name="id_{{ $n }}_title" class="form-control" placeholder="{{ $td[$idx][1] }}">
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">Description</label>
                                            <input type="text" name="id_{{ $n }}_desc" class="form-control" placeholder="{{ $td[$idx][2] }}">
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- FAQ --}}
                    <div class="tab-pane fade" id="tab-faq">
                        <div class="premium-card">
                            <div class="card-header-custom"><i class="fas fa-question-circle"></i><h5>FAQ Section</h5></div>
                            <div class="card-body-custom">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">FAQ Badge</label>
                                        <input type="text" name="faq_badge" class="form-control" placeholder="হেল্প">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">FAQ Title</label>
                                        <input type="text" name="faq_title" class="form-control" placeholder="সাধারণ প্রশ্ন (Q&A)">
                                    </div>
                                </div>
                                @foreach([1,2,3,4,5] as $n)
                                <div class="mini-row">
                                    <label class="form-label">FAQ {{ $n }} Question</label>
                                    <input type="text" name="faq_{{ $n }}_q" class="form-control mb-2" placeholder="প্রশ্ন">
                                    <label class="form-label">FAQ {{ $n }} Answer</label>
                                    <textarea name="faq_{{ $n }}_a" class="form-control" rows="2" placeholder="উত্তর"></textarea>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- FOOTER --}}
                    <div class="tab-pane fade" id="tab-footer">
                        <div class="premium-card">
                            <div class="card-header-custom"><i class="fas fa-shoe-prints"></i><h5>Order Form & Footer</h5></div>
                            <div class="card-body-custom">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Order Form Title</label>
                                        <input type="text" name="form_title" class="form-control" placeholder="এখনই অর্ডার করুন">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Order Form Subtitle</label>
                                        <input type="text" name="form_subtitle" class="form-control" placeholder="ফর্মটি পূরণ করুন">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Order Submit Button Text</label>
                                        <input type="text" name="btn_text_form" class="form-control" placeholder="অর্ডার কনফার্ম করুন">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Sticky Bar CTA Text</label>
                                        <input type="text" name="final_cta_btn_text" class="form-control" placeholder="এখনই অর্ডার করুন">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Footer Company Name</label>
                                        <input type="text" name="footer_company" class="form-control" placeholder="প্রিমিয়াম পাঞ্জাবি">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Footer Email</label>
                                        <input type="text" name="footer_email" class="form-control" placeholder="hello@punjabi.com">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Footer Copyright</label>
                                        <input type="text" name="footer_copyright" class="form-control" placeholder="© 2026 Punjabi Hub">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Footer Address</label>
                                        <input type="text" name="dhamaka_title" class="form-control" placeholder="ঢাকা, বাংলাদেশ">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PRODUCT --}}
                <div class="premium-card border-success">
                    <div class="card-header-custom" style="background: #f0fdf4;">
                        <i class="fas fa-box-open text-success"></i>
                        <h5 class="text-success">Product Integration</h5>
                    </div>
                    <div class="card-body-custom">
                        <div class="product-search-box mb-2">
                            <label class="form-label"><i class="fas fa-search me-1"></i> Search & Attach Product</label>
                            <input type="text" id="search2" class="form-control form-control-lg" placeholder="Search by name or SKU...">
                            <input type="hidden" id="product_id" name="product_id">
                        </div>
                        <div id="data" class="mt-3"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-images"></i><h5>Visual Assets</h5></div>
                    <div class="card-body-custom">
                        <div class="mb-3">
                            <label class="form-label">Main Hero Image (video alternative)</label>
                            <input type="file" name="right_product_image" class="form-control" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Spec / Second Image</label>
                            <input type="file" name="landing_bg" class="form-control" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gallery / Slider Images</label>
                            <input type="file" name="sliderimage[]" multiple class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>

                <div class="premium-card">
                    <div class="card-body-custom text-center">
                        <button type="button" id="save_btn" class="btn-save">
                            <i class="fas fa-shopping-bag me-2"></i> Save & Publish
                        </button>
                        <small class="d-block text-muted mt-2">Blank field gula default Bangla text dekhabe — pore edit kore override korte parben.</small>
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
    var path2 = "{{ route('admin.lp.getOrderProduct2') }}";
    $("#search2").autocomplete({
        source: function(request, response) {
            $.ajax({ url: path2, type: 'GET', dataType: "json", data: { search: request.term }, success: function(data) { response(data); } });
        },
        select: function(event, ui) {
            landingProductEntry(ui.item); $('#search2').val(''); return false;
        }
    });

    function landingProductEntry(item) {
        $.ajax({
            url: '{{ route("admin.lp.landingProductEntry")}}', type: 'GET', dataType: "json", data: { id: item.id },
            success: function(res) {
                $('div#data').html('<table class="table table-bordered"><tbody>' + res.html + '</tbody></table>');
                $('#product_id').val(res.pr_id);
            }
        });
    }

    $(document).on('click', '.remove-product', function() {
        $(this).closest('tr').remove();
        $('#product_id').val('');
    });

    $(document).on('click', '#save_btn', function(e) {
        e.preventDefault();
        let form = document.getElementById('ajax_form');
        let formData = new FormData(form);
        const btn = $(this);
        const origText = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: $(form).attr('action'), type: 'POST', data: formData, processData: false, contentType: false,
            success: function(res) {
                toastr.success(res.msg || 'Saved!');
                setTimeout(() => { window.location.href = res.url; }, 500);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(origText);
                if(xhr.responseJSON && xhr.responseJSON.errors) {
                    let first = Object.keys(xhr.responseJSON.errors)[0];
                    toastr.error(xhr.responseJSON.errors[first][0]);
                } else { toastr.error('Error saving page'); }
            }
        });
    });
});
</script>
@endpush
