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
    .form-control, .form-select { border-radius: 8px; border: 1px solid #d1d5db; padding: 9px 13px; font-size: 0.92rem; transition: all 0.2s; }
    .form-control:focus, .form-select:focus { border-color: var(--pri); box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1); }
    .file-upload-wrapper { position: relative; border: 2px dashed #d1d5db; border-radius: 12px; padding: 16px; text-align: center; background: var(--pri-light); cursor: pointer; }
    .existing-img-container { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
    .existing-img-box { position: relative; border: 1px solid #e5e7eb; border-radius: 8px; padding: 4px; background: #fff; }
    .existing-img-box img { border-radius: 6px; width: 64px; height: 64px; object-fit: cover; }
    .btn-delete-img { position: absolute; top: -7px; right: -7px; background: #ef4444; color: white; border-radius: 50%; width: 20px; height: 20px; font-size: 11px; display: flex; align-items: center; justify-content: center; text-decoration: none; }
    .btn-delete-img:hover { background: #dc2626; color: white; }
    .product-search-box { background: var(--pri-light); padding: 16px; border-radius: 12px; border: 1px solid var(--pri-bdr); }
    .btn-save { background: linear-gradient(135deg, var(--pri), var(--pri-dark)); color: white; padding: 12px 30px; border-radius: 50px; font-weight: 700; border: none; box-shadow: 0 4px 12px rgba(22,101,52,0.25); }
    .btn-save:hover { color: white; transform: translateY(-1px); }
    .section-tabs .nav-link { color: #4b5563; font-weight: 600; border: none; padding: 10px 16px; border-radius: 8px 8px 0 0; }
    .section-tabs .nav-link.active { background: var(--pri-light); color: var(--pri-dark); border-bottom: 3px solid var(--pri); }
    .mini-row { background: #fafafa; border: 1px solid #e5e7eb; border-radius: 10px; padding: 14px; margin-bottom: 12px; }
    .mini-row .form-label { font-size: 0.8rem; margin-bottom: 4px; color: #6b7280; }
    #product_table td small, #product_table td .text-muted { display: none !important; }
</style>
@endpush

<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-12 text-end">
            <a href="{{ route('front.landing_pages_fourteen.view_page', $item->slug ?: $item->id) }}" target="_blank" class="btn btn-success btn-sm">
                <i class="fas fa-eye me-1"></i> Preview Page
            </a>
        </div>
    </div>

<form method="POST" enctype="multipart/form-data" action="{{ route('admin.landing_pages_fourteen.update', [$item->id]) }}" id="ajax_form">
        @csrf
        @method('PATCH')

        <div class="row">
            <div class="col-lg-8">

                {{-- TAB NAV --}}
                <ul class="nav section-tabs mb-3" role="tablist">
                    <li class="nav-item"><button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-hero">Hero</button></li>
                    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-benefits">6 Benefits</button></li>
                    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-steps">3 Steps</button></li>
                    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-spec">Spec Table</button></li>
                    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-faq">FAQ</button></li>
                    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-footer">Footer</button></li>
                </ul>

                <div class="tab-content">
                    {{-- ============= HERO TAB ============= --}}
                    <div class="tab-pane fade show active" id="tab-hero">
                        <div class="premium-card">
                            <div class="card-header-custom"><i class="fas fa-star"></i><h5>Hero Section</h5></div>
                            <div class="card-body-custom">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Hero Main Title *</label>
                                        <input type="text" value="{{ $item->title1 }}" name="title1" class="form-control" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">URL Slug <small class="text-muted">(optional — খালি রাখলে আগেরটাই থাকবে)</small></label>
                                        <input type="text" value="{{ $item->slug }}" name="slug" class="form-control" placeholder="e.g. summer-sale-2026" pattern="[a-z0-9]+(-[a-z0-9]+)*" title="শুধু ছোট হাতের অক্ষর, সংখ্যা ও হাইফেন">
                                        <input type="hidden" name="page_type" value="14">
                                        <input type="hidden" name="product_id" id="product_id" value="{{ $item->product_id }}">
                                        <input type="hidden" id="new_product_id" name="new_product_id">
                                        <input type="hidden" name="variation_id" id="variation_id" value="{{ $item->variation_id }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Brand Name (Logo)</label>
                                        <input type="text" value="{{ $item->title2 }}" name="title2" class="form-control" placeholder="সবুজ পাতা / PURE MORINGA CO.">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Brand Subtitle (header-er nicher choto text)</label>
                                        <input type="text" value="{{ $item->brand_sub ?? '' }}" name="brand_sub" class="form-control" placeholder="PURE MORINGA CO.">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Top Bar Countdown Text</label>
                                        <input type="text" value="{{ $item->countdown_title }}" name="countdown_title" class="form-control" placeholder="বিশেষ অফার শেষ হবে">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Countdown Hours</label>
                                        <input type="number" value="{{ $item->countdown_hours ?? 3 }}" name="countdown_hours" class="form-control" placeholder="3">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" value="{{ $item->phone }}" name="phone" class="form-control">
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
                                        <label class="form-label">Hero Button Text (Primary)</label>
                                        <input type="text" value="{{ $item->btn_text_hero }}" name="btn_text_hero" class="form-control" placeholder="এখনই অর্ডার করুন">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Hero Button Text (Secondary)</label>
                                        <input type="text" value="{{ $item->btn_text_video }}" name="btn_text_video" class="form-control" placeholder="আরো জানুন">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Premium Badge Text</label>
                                        <input type="text" value="{{ $item->feature }}" name="feature" class="form-control" placeholder="PREMIUM QUALITY">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Hero Description</label>
                                        <textarea class="form-control summernote" name="left_side_desc">{!! $item->left_side_desc !!}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="premium-card">
                            <div class="card-header-custom"><i class="fas fa-chart-bar"></i><h5>Hero Stats (3 boxes)</h5></div>
                            <div class="card-body-custom">
                                <div class="row g-3">
                                    @foreach([1,2,3] as $n)
                                    <div class="col-md-4">
                                        <div class="mini-row">
                                            <label class="form-label">Stat {{ $n }} Number</label>
                                            <input type="text" name="stat_{{ $n }}_num" value="{{ $item->{'stat_'.$n.'_num'} }}" class="form-control mb-2" placeholder="{{ ['50+','46','18'][$n-1] }}">
                                            <label class="form-label">Stat {{ $n }} Label</label>
                                            <input type="text" name="stat_{{ $n }}_text" value="{{ $item->{'stat_'.$n.'_text'} }}" class="form-control" placeholder="{{ ['দেশে চলে','পুষ্টি উপাদান','এমাইনো এসিড'][$n-1] }}">
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="premium-card">
                            <div class="card-header-custom"><i class="fas fa-link"></i><h5>Tab Navigation (4 links)</h5></div>
                            <div class="card-body-custom">
                                <div class="row g-3">
                                    @foreach([1,2,3,4] as $n)
                                    <div class="col-md-6">
                                        <label class="form-label">Tab {{ $n }} Text</label>
                                        <input type="text" name="trust_{{ $n }}_title" value="{{ $item->{'trust_'.$n.'_title'} }}" class="form-control" placeholder="{{ ['১০০% খাঁটি','উপকারিতা','কেন আমরা','ভিডিও আক্রমণ'][$n-1] }}">
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ============= 6 BENEFITS TAB ============= --}}
                    <div class="tab-pane fade" id="tab-benefits">
                        <div class="premium-card">
                            <div class="card-header-custom"><i class="fas fa-list-ol"></i><h5>6 Benefits Section</h5></div>
                            <div class="card-body-custom">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Section Badge (small label)</label>
                                        <input type="text" value="{{ $item->promise_badge }}" name="promise_badge" class="form-control" placeholder="কেন আমরা">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Section Title</label>
                                        <input type="text" value="{{ $item->feature_title }}" name="feature_title" class="form-control" placeholder="প্রতিদিন এক চামচ, সারাজীবন সুস্থ থাকুন।">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Section Right-Side Paragraph</label>
                                        <textarea name="right_side_desc" class="form-control" rows="3">{{ $item->right_side_desc }}</textarea>
                                    </div>
                                </div>

                                @foreach([1,2,3,4,5,6] as $n)
                                <div class="mini-row">
                                    <h6 class="text-success fw-bold mb-2">{{ str_pad($n,2,'0',STR_PAD_LEFT) }} - Benefit #{{ $n }}</h6>
                                    <div class="row g-2">
                                        <div class="col-md-5">
                                            <label class="form-label">Title</label>
                                            <input type="text" name="id_{{ $n }}_title" value="{{ $item->{'id_'.$n.'_title'} }}" class="form-control" placeholder="শরীরের রোগ প্রতিরোধ ক্ষমতা">
                                        </div>
                                        <div class="col-md-7">
                                            <label class="form-label">Description</label>
                                            <input type="text" name="id_{{ $n }}_desc" value="{{ $item->{'id_'.$n.'_desc'} }}" class="form-control" placeholder="ভিটামিন C ও অ্যান্টিঅক্সিডেন্টে ভরপুর — শরীরের রোগ প্রতিরোধ বাড়ায়।">
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- ============= 3 STEPS TAB ============= --}}
                    <div class="tab-pane fade" id="tab-steps">
                        <div class="premium-card">
                            <div class="card-header-custom"><i class="fas fa-shoe-prints"></i><h5>3 Easy Steps (Dark Green Section)</h5></div>
                            <div class="card-body-custom">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Section Badge</label>
                                        <input type="text" value="{{ $item->identify_badge }}" name="identify_badge" class="form-control" placeholder="দৈনিক রুটিন">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Section Title</label>
                                        <input type="text" value="{{ $item->identify_title }}" name="identify_title" class="form-control" placeholder="তিনটি সহজ ধাপে, প্রতিদিনের রুটিন।">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Section Subtitle</label>
                                        <input type="text" value="{{ $item->identify_subtitle }}" name="identify_subtitle" class="form-control" placeholder="যেকোনো একটি ধাপ পছন্দ করে শুরু করুন">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Steps Sub-Description (sec-sub)</label>
                                        <input type="text" value="{{ $item->identify_desc ?? '' }}" name="identify_desc" class="form-control" placeholder="৪ – ৬ মাস ব্যবহার করলে দীর্ঘস্থায়ী উপকারিতা সম্ভব। প্রতিদিন এক চামচ যথেষ্ট।">
                                    </div>
                                </div>

                                @foreach([1,2,3] as $n)
                                @php $stepLabels = ['এক','দুই','তিন']; @endphp
                                <div class="mini-row">
                                    <h6 class="text-success fw-bold mb-2">Step {{ $n }} ({{ $stepLabels[$n-1] }})</h6>
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <label class="form-label">Step Title</label>
                                            <input type="text" name="promise_{{ $n }}_title" value="{{ $item->{'promise_'.$n.'_title'} }}" class="form-control" placeholder="{{ ['চামচ পরিমাণ','লিকুইডে দিন','উপভোগ করুন'][$n-1] }}">
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label">Step Description</label>
                                            <input type="text" name="promise_{{ $n }}_desc" value="{{ $item->{'promise_'.$n.'_desc'} }}" class="form-control" placeholder="বিস্তারিত বর্ণনা">
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- ============= SPEC TABLE TAB ============= --}}
                    <div class="tab-pane fade" id="tab-spec">
                        <div class="premium-card">
                            <div class="card-header-custom"><i class="fas fa-table"></i><h5>Product Spec Table ("যা আপনি পাচ্ছেন")</h5></div>
                            <div class="card-body-custom">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Section Badge (small label)</label>
                                        <input type="text" value="{{ $item->spec_badge ?? '' }}" name="spec_badge" class="form-control" placeholder="স্পেসিফিকেশন">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Section Title</label>
                                        <input type="text" value="{{ $item->spec_title }}" name="spec_title" class="form-control" placeholder="যা আপনি পাচ্ছেন">
                                    </div>
                                </div>
                                @foreach([1,2,3,4,5,6,7,8] as $n)
                                <div class="mini-row">
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <label class="form-label">Row {{ $n }} - Label</label>
                                            <input type="text" name="spec_{{ $n }}_label" value="{{ $item->{'spec_'.$n.'_label'} }}" class="form-control" placeholder="{{ ['ব্র্যান্ড','টাইপ','ওজন','উৎপাদন','মেয়াদ','সংরক্ষণ','উৎপত্তি','ডেলিভারি'][$n-1] }}">
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label">Row {{ $n }} - Value</label>
                                            <input type="text" name="spec_{{ $n }}_value" value="{{ $item->{'spec_'.$n.'_value'} }}" class="form-control" placeholder="মান লিখুন">
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- ============= FAQ TAB ============= --}}
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
                                        <input type="text" value="{{ $item->faq_title }}" name="faq_title" class="form-control" placeholder="সাধারণ জিজ্ঞাসা">
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

                    {{-- ============= FOOTER TAB ============= --}}
                    <div class="tab-pane fade" id="tab-footer">
                        <div class="premium-card">
                            <div class="card-header-custom"><i class="fas fa-shoe-prints"></i><h5>Footer Content</h5></div>
                            <div class="card-body-custom">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Company Name (Footer)</label>
                                        <input type="text" value="{{ $item->footer_company }}" name="footer_company" class="form-control" placeholder="সবুজ পাতা">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Footer Email</label>
                                        <input type="text" value="{{ $item->footer_email }}" name="footer_email" class="form-control" placeholder="hello@moringa.com">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Footer Copyright</label>
                                        <input type="text" value="{{ $item->footer_copyright }}" name="footer_copyright" class="form-control" placeholder="© 2024 Sobuj Pata">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Footer Address</label>
                                        <input type="text" value="{{ $item->dhamaka_title }}" name="dhamaka_title" class="form-control" placeholder="ঢাকা, বাংলাদেশ">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Order Form Title</label>
                                        <input type="text" value="{{ $item->form_title }}" name="form_title" class="form-control" placeholder="এখনই অর্ডার করুন">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Order Button Text</label>
                                        <input type="text" value="{{ $item->btn_text_form }}" name="btn_text_form" class="form-control" placeholder="অর্ডার কনফার্ম করুন">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Order Form Sub-Description</label>
                                        <input type="text" value="{{ $item->form_desc ?? '' }}" name="form_desc" class="form-control" placeholder="ফর্মটি পূরণ করুন এবং ক্যাশ অন ডেলিভারিতে প্রোডাক্ট গ্রহণ করুন">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Footer Contact Heading</label>
                                        <input type="text" value="{{ $item->footer_contact_label ?? '' }}" name="footer_contact_label" class="form-control" placeholder="যোগাযোগ">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Footer Address Heading</label>
                                        <input type="text" value="{{ $item->footer_address_label ?? '' }}" name="footer_address_label" class="form-control" placeholder="ঠিকানা">
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

            {{-- ============= RIGHT SIDEBAR ============= --}}
            <div class="col-lg-4">
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-images"></i><h5>Visual Assets</h5></div>
                    <div class="card-body-custom">
                        <div class="mb-4">
                            <label class="form-label">Hero Product Image (Main bowl image)</label>
                            @if($item->landing_bg)
                            <div class="existing-img-container">
                                <div class="existing-img-box"><img src="{{ asset('landing_pages/'.$item->landing_bg)}}" alt="hero"></div>
                            </div>
                            @endif
                            <input type="file" name="landing_bg" class="form-control" accept="image/*">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Video Embed Code (optional)</label>
                            <input type="text" value="{{ $item->video_url }}" name="video_url" class="form-control" placeholder="<iframe>...</iframe>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Slider Images</label>
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
                    <div class="card-header-custom"><i class="fas fa-star"></i><h5>Review Images (optional)</h5></div>
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
                            <i class="fas fa-leaf me-2"></i> Update Page
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
    $('.summernote').summernote({ height: 180 });
});

var path2 = "{{ route('admin.lp.getOrderProduct2') }}";
const products = [];
$("#search2").autocomplete({
    minLength: 2,
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
        url: '{{ route("admin.lp.landingProductEntry")}}', type: 'GET', dataType: "json", data: { id: item.id },
        success: function(res) {
            if (res.html) {
                $('div#product_container').show();
                $('tbody#data').html(res.html);
                $('#product_search').hide();
            }
            if (res.pr_id) { $('#new_product_id').val(res.pr_id); }
            let newVarId = '';
            if (res.variations && res.variations.length > 0) { newVarId = res.variations[0].id; }
            $('#variation_id').val(newVarId);
        }
    });
}

$(document).off('click.lp14_remove', '.remove').on('click.lp14_remove', ".remove", function(e){
    e.preventDefault(); e.stopImmediatePropagation();
    $(this).closest("tr").remove();
    $('#product_search').show();
    $('#new_product_id').val('');
    $('#variation_id').val('');
});

$(document).off('click.lp14_update', '#save_btn').on('click.lp14_update', '#save_btn', function(e){
    e.preventDefault();
    const form = document.getElementById('ajax_form');
    const btn  = $('#save_btn');
    if (btn.data('submitting') === 1) return false;
    btn.data('submitting', 1);
    const originalText = btn.html();
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

    let formData = new FormData(form);
    let varId = $('#variation_id').val();
    if(varId && varId !== "") { formData.set('variation_id', varId); } else { formData.delete('variation_id'); }

    $.ajax({
        url: $(form).attr('action'), type: 'POST', data: formData, processData: false, contentType: false,
        success: function(res){
            toastr.success('Page Updated Successfully!');
            window.location.href = res.url || "{{ route('admin.landing_pages_fourteen') }}";
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
