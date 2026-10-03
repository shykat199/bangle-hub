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
    .existing-img-container { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
    .existing-img-box { position: relative; border: 1px solid #e5e7eb; border-radius: 8px; padding: 4px; background: #fff; }
    .existing-img-box img { border-radius: 6px; width: 64px; height: 64px; object-fit: cover; }
    .btn-delete-img { position: absolute; top: -7px; right: -7px; background: #ef4444; color: white; border-radius: 50%; width: 20px; height: 20px; font-size: 11px; display: flex; align-items: center; justify-content: center; text-decoration: none; }
    .product-search-box { background: var(--pri-light); padding: 16px; border-radius: 12px; border: 1px solid var(--pri-bdr); }
    .btn-save { background: linear-gradient(135deg, var(--pri-dark), var(--pri)); color: #f7f1de; padding: 12px 30px; border-radius: 50px; font-weight: 700; border: none; box-shadow: 0 4px 12px rgba(22,101,52,0.25); }
    .btn-save:hover { color: #f7f1de; transform: translateY(-1px); }
    .section-tabs .nav-link { color: #4b5563; font-weight: 600; border: none; padding: 10px 16px; border-radius: 8px 8px 0 0; font-size: 13px; }
    .section-tabs .nav-link.active { background: var(--pri-light); color: var(--pri-dark); border-bottom: 3px solid var(--pri); }
    .mini-row { background: #fafafa; border: 1px solid #e5e7eb; border-radius: 10px; padding: 14px; margin-bottom: 12px; }
    .mini-row .form-label { font-size: 0.8rem; margin-bottom: 4px; color: #6b7280; }
    #product_table td small, #product_table td .text-muted { display: none !important; }
</style>
@endpush

<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-12 text-end">
            <a href="{{ route('front.landing_pages_sixteen.view_page', $item->slug ?: $item->id) }}" target="_blank" class="btn btn-success btn-sm">
                <i class="fas fa-eye me-1"></i> Preview Page
            </a>
        </div>
    </div>

<form method="POST" enctype="multipart/form-data" action="{{ route('admin.landing_pages_sixteen.update', [$item->id]) }}" id="ajax_form">
        @csrf
        @method('PATCH')

        <div class="row">
            <div class="col-lg-8">
                <ul class="nav section-tabs mb-3 flex-wrap" role="tablist">
                    <li class="nav-item"><button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-hero">Hero & Video</button></li>
                    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-features">4 Features</button></li>
                    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-spec">Spec Table</button></li>
                    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-reviews">Reviews</button></li>
                    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-size">Size Guide</button></li>
                    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-trust">Trust Badges</button></li>
                    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-faq">FAQ</button></li>
                    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-footer">Footer</button></li>
                </ul>

                <div class="tab-content">
                    {{-- HERO TAB --}}
                    <div class="tab-pane fade show active" id="tab-hero">
                        <div class="premium-card">
                            <div class="card-header-custom"><i class="fas fa-star"></i><h5>Hero Section</h5></div>
                            <div class="card-body-custom">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Hero Title *</label>
                                        <input type="text" value="{{ $item->title1 }}" name="title1" class="form-control" required placeholder="প্রিমিয়াম কোয়ালিটির পাঞ্জাবি">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">URL Slug <small class="text-muted">(optional — খালি রাখলে আগেরটাই থাকবে)</small></label>
                                        <input type="text" value="{{ $item->slug }}" name="slug" class="form-control" placeholder="e.g. summer-sale-2026" pattern="[a-z0-9]+(-[a-z0-9]+)*" title="শুধু ছোট হাতের অক্ষর, সংখ্যা ও হাইফেন">
                                        <input type="hidden" name="page_type" value="16">
                                        <input type="hidden" name="product_id" id="product_id" value="{{ $item->product_id }}">
                                        <input type="hidden" id="new_product_id" name="new_product_id">
                                        <input type="hidden" name="variation_id" id="variation_id" value="{{ $item->variation_id }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Brand / Subtitle</label>
                                        <input type="text" value="{{ $item->title2 }}" name="title2" class="form-control" placeholder="প্রিমিয়াম পাঞ্জাবি হাব">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Top Stripe Countdown Text</label>
                                        <input type="text" value="{{ $item->countdown_title }}" name="countdown_title" class="form-control" placeholder="বিশেষ অফার শেষ হবে">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Countdown Hours</label>
                                        <input type="number" value="{{ $item->countdown_hours ?? 4 }}" name="countdown_hours" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" value="{{ $item->phone }}" name="phone" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">WhatsApp Number</label>
                                        <input type="text" value="{{ $item->whatsapp }}" name="whatsapp" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Discount Save Text</label>
                                        <input type="text" value="{{ $item->discount_save_text }}" name="discount_save_text" class="form-control" placeholder="700৳ বাঁচান">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Old Price (৳)</label>
                                        <input type="number" value="{{ $item->old_price }}" name="old_price" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">New Price (৳)</label>
                                        <input type="number" value="{{ $item->new_price }}" name="new_price" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Hero CTA Button Text</label>
                                        <input type="text" value="{{ $item->btn_text_hero }}" name="btn_text_hero" class="form-control" placeholder="এখনই অর্ডার করুন">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Video URL / Embed</label>
                                        <input type="text" value="{{ $item->video_url }}" name="video_url" class="form-control" placeholder='<iframe src="..."></iframe> or YouTube URL'>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Hero Description</label>
                                        <textarea class="form-control summernote" name="left_side_desc">{!! $item->left_side_desc !!}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 4 FEATURES --}}
                    <div class="tab-pane fade" id="tab-features">
                        <div class="premium-card">
                            <div class="card-header-custom"><i class="fas fa-th"></i><h5>"Why Special?" 4 Feature Cards & Gallery</h5></div>
                            <div class="card-body-custom">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Section Title (Why Special)</label>
                                        <input type="text" value="{{ $item->feature_title }}" name="feature_title" class="form-control" placeholder="কেন এই পাঞ্জাবিটি বিশেষ?">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Gallery Section Title</label>
                                        <input type="text" value="{{ $item->identify_title }}" name="identify_title" class="form-control" placeholder="প্রতিটি ডিটেইলে অভিজাত্য">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Gallery Subtitle</label>
                                        <input type="text" value="{{ $item->identify_subtitle ?? '' }}" name="identify_subtitle" class="form-control" placeholder="নিজের চোখে দেখুন প্রতিটি ডিটেইল">
                                    </div>
                                </div>

                                @php $fd = [['fas fa-shield-alt','প্রিমিয়াম ফেব্রিক','উন্নতমানের কটন এবং লিনেন কাপড়ের তৈরি।'],['fas fa-cut','নিখুঁত স্টিচিং','ডাবল-সিম স্টিচিং, দীর্ঘস্থায়ী ও নিখুঁত।'],['fas fa-shopping-bag','একচুয়েটিং ডিজাইন','ক্ল্যাসিক ও মডার্ন স্টাইলের সমন্বয়।'],['fas fa-tint','সফট ফ্যাব্রিক','সফট ও কুল, সারা দিন কম্ফোর্টেবল।']]; @endphp
                                @foreach([1,2,3,4] as $n)
                                <div class="mini-row">
                                    <h6 class="text-success fw-bold mb-2">Feature Card #{{ $n }}</h6>
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <label class="form-label">Icon (FontAwesome)</label>
                                            <input type="text" name="id_{{ $n }}_icon" value="{{ $item->{'id_'.$n.'_icon'} }}" class="form-control" placeholder="{{ $fd[$n-1][0] }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Title</label>
                                            <input type="text" name="id_{{ $n }}_title" value="{{ $item->{'id_'.$n.'_title'} }}" class="form-control" placeholder="{{ $fd[$n-1][1] }}">
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">Description</label>
                                            <input type="text" name="id_{{ $n }}_desc" value="{{ $item->{'id_'.$n.'_desc'} }}" class="form-control" placeholder="{{ $fd[$n-1][2] }}">
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
                                    <input type="text" value="{{ $item->spec_title }}" name="spec_title" class="form-control" placeholder="প্রোডাক্ট স্পেসিফিকেশন">
                                </div>
                                @php $sd = [['টিস্যু','১০০% প্রিমিয়াম কটন'],['কাটিং / টাইপ','সেমি-ফিট রেগুলার'],['সাইজ','M, L, XL, XXL'],['ফিট','রেগুলার ফিট'],['কালার','সবুজ ও অন্যান্য'],['ওজন','সাধারণ ব্যবহার'],['প্রিন্টিং','৭ দিন গ্যারান্টি']]; @endphp
                                @foreach([1,2,3,4,5,6,7] as $n)
                                <div class="mini-row">
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <label class="form-label">Row {{ $n }} - Label</label>
                                            <input type="text" name="spec_{{ $n }}_label" value="{{ $item->{'spec_'.$n.'_label'} }}" class="form-control" placeholder="{{ $sd[$n-1][0] }}">
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label">Row {{ $n }} - Value</label>
                                            <input type="text" name="spec_{{ $n }}_value" value="{{ $item->{'spec_'.$n.'_value'} }}" class="form-control" placeholder="{{ $sd[$n-1][1] }}">
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
                                        <input type="text" value="{{ $item->review_badge }}" name="review_badge" class="form-control" placeholder="রিভিউ">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Section Title</label>
                                        <input type="text" value="{{ $item->review_title }}" name="review_title" class="form-control" placeholder="কাস্টমার রিভিউ">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Section Subtitle (rating line)</label>
                                        <input type="text" value="{{ $item->review_subtitle ?? '' }}" name="review_subtitle" class="form-control" placeholder="★★★★★ ৪.৯/৫ — ৪৫০০+ রিভিউ">
                                    </div>
                                </div>
                                @php $rd = [['যা প্রত্যাশা করেছিলাম তার চেয়ে অনেক ভালো। কাপড় চমৎকার নরম।','মুহাম্মদ রহিম'],['ফিটিং নিখুঁত, স্টিচিং দারুণ। আমি খুশি।','রাশিদুল আনোয়ার'],['এই দামে এত ভালো পাঞ্জাবি — সেরা ডিল।','সাকিব ইসলাম']]; @endphp
                                @foreach([1,2,3] as $n)
                                <div class="mini-row">
                                    <h6 class="text-success fw-bold mb-2">Review #{{ $n }}</h6>
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <label class="form-label">Review Text</label>
                                            <textarea name="rev_{{ $n }}_text" class="form-control" rows="2" placeholder="{{ $rd[$n-1][0] }}">{{ $item->{'rev_'.$n.'_text'} }}</textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Customer Name</label>
                                            <input type="text" name="rev_{{ $n }}_name" value="{{ $item->{'rev_'.$n.'_name'} }}" class="form-control" placeholder="{{ $rd[$n-1][1] }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Location</label>
                                            <input type="text" name="rev_{{ $n }}_loc" value="{{ $item->{'rev_'.$n.'_loc'} }}" class="form-control" placeholder="Dhaka">
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- SIZE GUIDE --}}
                    <div class="tab-pane fade" id="tab-size">
                        <div class="premium-card">
                            <div class="card-header-custom"><i class="fas fa-ruler"></i><h5>Size Guide Table (4 rows)</h5></div>
                            <div class="card-body-custom">
                                <div class="mb-3">
                                    <label class="form-label">Section Title</label>
                                    <input type="text" value="{{ $item->urgency_title }}" name="urgency_title" class="form-control" placeholder="সাইজ গাইড">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Section Subtitle</label>
                                    <input type="text" value="{{ $item->urgency_subtitle }}" name="urgency_subtitle" class="form-control" placeholder="বুকপাতি দিয়ে বিভিন্ন সাইজের সঠিক মাপ">
                                </div>
                                @php $szd = [['M','৪০','২৭','৩৩'],['L','৪২','২৮','৩৪'],['XL','৪৪','২৯','৩৫'],['XXL','৪৬','৩০','৩৬']]; @endphp
                                @foreach([5,6,7] as $idx => $n)
                                {{-- Reusing promise_5,6,7 / spec area or reuse simpler --}}
                                @endforeach
                                <p class="text-muted small">Note: Size guide uses 4 rows with size name + chest + length + sleeve. Defaults auto-shown; admin can override via individual fields.</p>
                                <div class="row g-2">
                                    @php $sizeRows = ['M','L','XL','XXL']; @endphp
                                    @foreach([1,2,3,4] as $n)
                                    <div class="col-md-6">
                                        <div class="mini-row">
                                            <h6 class="text-success fw-bold mb-2">Size {{ $sizeRows[$n-1] }} Row</h6>
                                            <label class="form-label">Size Name</label>
                                            <input type="text" name="trust_{{ $n }}_title" value="{{ $item->{'trust_'.$n.'_title'} }}" class="form-control mb-2" placeholder="{{ $sizeRows[$n-1] }}">
                                            <label class="form-label">Measurements (chest|length|sleeve)</label>
                                            <input type="text" name="trust_{{ $n }}_icon" value="{{ $item->{'trust_'.$n.'_icon'} }}" class="form-control" placeholder="{{ $szd[$n-1][1].'|'.$szd[$n-1][2].'|'.$szd[$n-1][3] }}">
                                            <small class="text-muted">3 values separated by |</small>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TRUST BADGES --}}
                    <div class="tab-pane fade" id="tab-trust">
                        <div class="premium-card">
                            <div class="card-header-custom"><i class="fas fa-shield-alt"></i><h5>Trust Badge Cards (4 boxes)</h5></div>
                            <div class="card-body-custom">
                                <p class="text-muted small mb-3">Order form er upore 4 ti trust badge card.</p>
                                @php $td = [['fas fa-truck','দ্রুত ডেলিভারি','২-৪ কর্মদিবস'],['fas fa-money-bill','ক্যাশ অন ডেলিভারি','বাসায় বসেই দিন'],['fas fa-credit-card','সিউর পেমেন্ট','১০০% নিরাপদ'],['fas fa-check-double','১০০% অরিজিনাল','রিপ্লেসমেন্ট গ্যারান্টি']]; @endphp
                                @foreach([5,6,7,8] as $idx => $n)
                                <div class="mini-row">
                                    <h6 class="text-success fw-bold mb-2">Trust Card #{{ $idx + 1 }}</h6>
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <label class="form-label">Icon</label>
                                            <input type="text" name="id_{{ $n }}_icon" value="{{ $item->{'id_'.$n.'_icon'} }}" class="form-control" placeholder="{{ $td[$idx][0] }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Title</label>
                                            <input type="text" name="id_{{ $n }}_title" value="{{ $item->{'id_'.$n.'_title'} }}" class="form-control" placeholder="{{ $td[$idx][1] }}">
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">Subtitle</label>
                                            <input type="text" name="id_{{ $n }}_desc" value="{{ $item->{'id_'.$n.'_desc'} }}" class="form-control" placeholder="{{ $td[$idx][2] }}">
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
                                        <input type="text" value="{{ $item->faq_badge }}" name="faq_badge" class="form-control" placeholder="হেল্প">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">FAQ Title</label>
                                        <input type="text" value="{{ $item->faq_title }}" name="faq_title" class="form-control" placeholder="সাধারণ প্রশ্ন (Q&A)">
                                    </div>
                                </div>
                                @foreach([1,2,3,4,5] as $n)
                                <div class="mini-row">
                                    <label class="form-label">FAQ {{ $n }} Question</label>
                                    <input type="text" name="faq_{{ $n }}_q" value="{{ $item->{'faq_'.$n.'_q'} }}" class="form-control mb-2" placeholder="প্রশ্ন">
                                    <label class="form-label">FAQ {{ $n }} Answer</label>
                                    <textarea name="faq_{{ $n }}_a" class="form-control" rows="2" placeholder="উত্তর">{{ $item->{'faq_'.$n.'_a'} }}</textarea>
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
                                        <input type="text" value="{{ $item->form_title }}" name="form_title" class="form-control" placeholder="এখনই অর্ডার করুন">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Order Form Subtitle</label>
                                        <input type="text" value="{{ $item->form_subtitle }}" name="form_subtitle" class="form-control" placeholder="ফর্ম পূরণ করুন">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Order Submit Button</label>
                                        <input type="text" value="{{ $item->btn_text_form }}" name="btn_text_form" class="form-control" placeholder="অর্ডার কনফার্ম করুন">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Sticky Bottom Bar Text</label>
                                        <input type="text" value="{{ $item->final_cta_btn_text }}" name="final_cta_btn_text" class="form-control" placeholder="এখনই অর্ডার করুন">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Footer Company Name</label>
                                        <input type="text" value="{{ $item->footer_company }}" name="footer_company" class="form-control" placeholder="প্রিমিয়াম পাঞ্জাবি">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Footer Email</label>
                                        <input type="text" value="{{ $item->footer_email }}" name="footer_email" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Footer Copyright</label>
                                        <input type="text" value="{{ $item->footer_copyright }}" name="footer_copyright" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Footer Address</label>
                                        <input type="text" value="{{ $item->dhamaka_title }}" name="dhamaka_title" class="form-control" placeholder="ঢাকা, বাংলাদেশ">
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
                        <div class="product-search-box mb-2" id="product_search" style="display: {{ $single_product ? 'none' : 'block' }};">
                            <label class="form-label"><i class="fas fa-search me-1"></i> Add/Change Product</label>
                            <input type="text" id="search2" class="form-control form-control-lg" placeholder="Search by name or SKU...">
                        </div>
                        <div class="table-responsive mb-3" id="product_container" style="display: {{ $single_product ? 'block' : 'none' }};">
                            <table class="table table-bordered table-centered mb-0" id="product_table">
                                <thead class="table-light"></thead>
                                <tbody id="data">
                                    @if($single_product)
                                    <tr>
                                        <td><img src="{{ getImage('products', $single_product->image) }}" height="50" width="50" class="rounded"/></td>
                                        <td class="fw-bold">{{ $single_product->name }}</td>
                                        <td>{{ $single_product->sell_price }} Tk</td>
                                        <td><a class="remove btn btn-sm btn-soft-danger"><i class="fas fa-trash"></i> Remove</a></td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SIDEBAR --}}
            <div class="col-lg-4">
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-images"></i><h5>Visual Assets</h5></div>
                    <div class="card-body-custom">
                        <div class="mb-3">
                            <label class="form-label">Main Product Image (Gallery main)</label>
                            @if($item->right_product_image)
                            <div class="existing-img-container">
                                <div class="existing-img-box"><img src="{{ asset('landing_pages/'.$item->right_product_image)}}" alt="img"></div>
                            </div>
                            @endif
                            <input type="file" name="right_product_image" class="form-control" accept="image/*">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Spec Section Image</label>
                            @if($item->landing_bg)
                            <div class="existing-img-container">
                                <div class="existing-img-box"><img src="{{ asset('landing_pages/'.$item->landing_bg)}}" alt="bg"></div>
                            </div>
                            @endif
                            <input type="file" name="landing_bg" class="form-control" accept="image/*">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Gallery Thumbnail Images</label>
                            <div class="existing-img-container">
                                @foreach ($item->images as $image)
                                    <div class="existing-img-box">
                                        <a href="{{ route('admin.delete_slider',[$image->id])}}" class="btn-delete-img" onclick="return confirm('Delete?');">&times;</a>
                                        <img src="{{ getImage('landing_sliders',$image->image)}}">
                                    </div>
                                @endforeach
                            </div>
                            <input type="file" name="sliderimage[]" multiple class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>

                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-user-friends"></i><h5>Review Images (optional)</h5></div>
                    <div class="card-body-custom">
                        <div class="existing-img-container">
                            @foreach ($review_images as $rv)
                                <div class="existing-img-box">
                                    <a href="{{ route('admin.delete_review',[$rv->id])}}" class="btn-delete-img" onclick="return confirm('Delete?');">&times;</a>
                                    <img src="{{ asset('review_landing_sliders/'.$rv->review_image) }}">
                                </div>
                            @endforeach
                        </div>
                        <input type="file" name="review_product_image[]" multiple class="form-control">
                    </div>
                </div>

                <div class="premium-card">
                    <div class="card-body-custom text-center">
                        <button type="button" id="save_btn" class="btn-save w-100">
                            <i class="fas fa-tshirt me-2"></i> Update Page
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
$(document).ready(function() { $('.summernote').summernote({ height: 180 }); });

var path2 = "{{ route('admin.lp.getOrderProduct2') }}";
const products = [];
$("#search2").autocomplete({
    minLength: 2,
    source: function(request, response) {
        $.ajax({ url: path2, type: 'GET', dataType: "json", data: { search: request.term },
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
        url: '{{ route("admin.lp.landingProductEntry")}}', type: 'GET', dataType: "json", data: { id: item.id },
        success: function(res) {
            if (res.html) { $('div#product_container').show(); $('tbody#data').html(res.html); $('#product_search').hide(); }
            if (res.pr_id) { $('#new_product_id').val(res.pr_id); }
            let newVarId = ''; if (res.variations && res.variations.length > 0) newVarId = res.variations[0].id;
            $('#variation_id').val(newVarId);
        }
    });
}

$(document).off('click.lp16_remove', '.remove').on('click.lp16_remove', ".remove", function(e){
    e.preventDefault(); e.stopImmediatePropagation();
    $(this).closest("tr").remove(); $('#product_search').show();
    $('#new_product_id').val(''); $('#variation_id').val('');
});

$(document).off('click.lp16_update', '#save_btn').on('click.lp16_update', '#save_btn', function(e){
    e.preventDefault();
    const form = document.getElementById('ajax_form');
    const btn  = $('#save_btn');
    if (btn.data('submitting') === 1) return false;
    btn.data('submitting', 1);
    const originalText = btn.html();
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

    let formData = new FormData(form);
    let varId = $('#variation_id').val();
    if(varId && varId !== "") formData.set('variation_id', varId); else formData.delete('variation_id');

    $.ajax({
        url: $(form).attr('action'), type: 'POST', data: formData, processData: false, contentType: false,
        success: function(res){
            toastr.success('Page Updated Successfully!');
            window.location.href = res.url || "{{ route('admin.landing_pages_sixteen') }}";
        },
        error: function(xhr){
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                let firstKey = Object.keys(xhr.responseJSON.errors)[0];
                toastr.error(xhr.responseJSON.errors[firstKey][0]);
            } else { toastr.error('Error occurred'); }
            btn.data('submitting', 0);
            btn.prop('disabled', false).html(originalText);
        }
    });
});
</script>
@endpush
