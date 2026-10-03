@extends('backend.app')
@section('title', 'Create Landing Page (Type 8)')
@section('content')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
<style>
    .premium-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px; }
    .card-header-custom { padding: 15px 20px; border-bottom: 1px solid #eee; background: #f8fafc; font-weight: bold; color: #dc2626; border-radius: 12px 12px 0 0; }
</style>
@endpush

<div class="content-wrapper">
    <div class="container-fluid py-3">
        <form action="{{ route('admin.landing_pages_eight.store') }}" method="POST" enctype="multipart/form-data" id="ajax_form">
            @csrf
            <input type="hidden" name="page_type" value="8">
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-heading"></i> ১. হেডলাইন ও ভিডিও</div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="fw-bold">মেইন হেডলাইন (Title 1) <span class="text-danger">*</span></label>
                                <input type="text" name="title1" class="form-control" placeholder="যেমন: আস্থার ৪০ বছর: গুণগত মানে আজও সেরা" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">URL Slug <small class="text-muted">(optional — খালি রাখলে টাইটেল থেকে বানানো হবে)</small></label>
                                <input type="text" name="slug" class="form-control" placeholder="e.g. summer-sale-2026" pattern="[a-z0-9]+(-[a-z0-9]+)*" title="শুধু ছোট হাতের অক্ষর, সংখ্যা ও হাইফেন">
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">সাব-হেডলাইন (Title 2)</label>
                                <input type="text" name="title2" class="form-control" placeholder="যেমন: সেরা অফারে কিনে নিন">
                            </div>

                            {{-- ✅ FIXED: Added Phone Number and Call Text Option ✅ --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="fw-bold">যোগাযোগের নাম্বার (Phone) <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control" placeholder="যেমন: 01863538478" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="fw-bold">কল বাটন টেক্সট (Call Text)</label>
                                    <input type="text" name="call_text" class="form-control" placeholder="যেমন: সরাসরি কল করুন">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold">YouTube Embed URL / Iframe</label>
                                <input type="text" name="video_url" class="form-control" placeholder="https://www.youtube.com/embed/xxxxx">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="fw-bold">ফর্ম টাইটেল (ডান পাশে)</label>
                                    <input type="text" name="form_title" class="form-control" value="এখনই অর্ডার করুন">
                                </div>
                                <div class="col-md-6">
                                    <label class="fw-bold">টাইমার টেক্সট (নিচে)</label>
                                    <input type="text" name="countdown_title" class="form-control" value="১০ মিনিটের মধ্যে অর্ডার করলে ডেলিভারি ফ্রি">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-list"></i> ২. বিস্তারিত বিবরণ ও ফিচার</div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="fw-bold">ফিচার সেকশন টাইটেল</label>
                                <input type="text" name="feature_title" class="form-control" value="উপকারিতা">
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">ফিচার লিস্ট (Summernote Editor)</label>
                                <textarea name="feature_list" class="form-control summernote" rows="4">
                                    <ul>
                                        <li><i class="fas fa-check-circle"></i> ১০০% অরিজিনাল প্রোডাক্ট</li>
                                    </ul>
                                </textarea>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">অন্যান্য বিস্তারিত বিবরণ (Summernote Editor)</label>
                                <textarea name="left_side_desc" class="form-control summernote"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="premium-card" style="border: 2px solid #ef4444;">
                        <div class="card-header-custom" style="background: #fef2f2;"><i class="fas fa-boxes"></i> ৩. প্যাকেজ / বান্ডেল অফার (ঐচ্ছিক)</div>
                        <div class="card-body p-4">
                            <table class="table table-bordered text-center" id="packageTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>পরিমাণ (Qty)</th>
                                        <th>প্যাকেজ মূল্য (Price)</th>
                                        <th>ছাড়ের টেক্সট (Discount Text)</th>
                                        <th>অ্যাকশন</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="number" name="pkg_qty[]" class="form-control" value="1" required></td>
                                        <td><input type="number" name="pkg_price[]" class="form-control" placeholder="প্যাকেজ মূল্য" required></td>
                                        <td><input type="text" name="pkg_discount_text[]" class="form-control" placeholder="যেমন: ১০০ টাকা ছাড়"></td>
                                        <td>
                                            <input type="radio" name="pkg_is_default" value="0" checked class="d-none">
                                            <button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" id="addPkgRow" class="btn btn-sm btn-success"><i class="fas fa-plus"></i> Add Another Package</button>
                            <small class="d-block text-muted mt-2">* আপনি যদি প্যাকেজ তৈরি না করেন, তবে অটোমেটিক ১ পিসের ডিফল্ট অপশন তৈরি হয়ে যাবে।</small>
                        </div>
                    </div>

                    {{-- ✅ FAQ SECTION --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-question-circle"></i> ৪. সচরাচর জিজ্ঞাসা (FAQ)</div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="fw-bold">FAQ Title</label>
                                    <input type="text" name="faq_title" class="form-control" placeholder="যেমন: সচরাচর জিজ্ঞাসিত প্রশ্ন" value="সচরাচর জিজ্ঞাসা (FAQ)">
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
                                <input type="number" name="new_price" id="new_price_input" class="form-control" placeholder="যেমন: ১২৪৯">
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">রেগুলার মূল্য (Old Price)</label>
                                <input type="number" name="old_price" class="form-control" placeholder="যেমন: ১৮৯০">
                            </div>
                        </div>
                    </div>

                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-images"></i> ছবি ও সার্টিফিকেট</div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="fw-bold">বিকল্প প্রোডাক্ট ছবি (যদি ভিডিও না দেন)</label>
                                <input type="file" name="right_product_image" class="form-control" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">ট্রাস্ট/সার্টিফিকেট ছবি (Multiple)</label>
                                <input type="text" name="review_title" class="form-control mb-2" placeholder="সেকশন টাইটেল (যেমন: ল্যাব টেস্টেড)">
                                <input type="file" name="review_product_image[]" class="form-control" accept="image/*" multiple>
                            </div>
                        </div>
                    </div>

                    <button type="submit" id="save_btn" class="btn btn-danger btn-lg w-100 fw-bold"><i class="fas fa-save"></i> Save Landing Page</button>
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
    // Apply Summernote to both Feature List and Details
    $('.summernote').summernote({ height: 200 });

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

        // Add Package Row
        let pkgIndex = 1;
        $('#addPkgRow').click(function() {
            let tr = `<tr>
                <td><input type="number" name="pkg_qty[]" class="form-control" required></td>
                <td><input type="number" name="pkg_price[]" class="form-control" required></td>
                <td><input type="text" name="pkg_discount_text[]" class="form-control"></td>
                <td>
                    <input type="radio" name="pkg_is_default" value="${pkgIndex}" class="d-none">
                    <button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
            $('#packageTable tbody').append(tr);
            pkgIndex++;
        });

        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
        });

        // Submit Logic
        $('#ajax_form').submit(function(e) {
            e.preventDefault();
            let btn = $('#save_btn');
            if(btn.data('submitting')) return;
            btn.data('submitting', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            
            let formData = new FormData(this);
            $.ajax({
                url: $(this).attr('action'), type: 'POST', data: formData, processData: false, contentType: false,
                success: function(res) {
                    window.location.href = res.url || "{{ route('admin.landing_pages_eight') }}";
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