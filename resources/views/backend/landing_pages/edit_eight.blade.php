@extends('backend.app')
@section('title', 'Edit Landing Page (Type 8)')
@section('content')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
<style>
    .premium-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px; }
    .card-header-custom { padding: 15px 20px; border-bottom: 1px solid #eee; background: #f8fafc; font-weight: bold; color: #dc2626; border-radius: 12px 12px 0 0; }
    .img-box { position: relative; display: inline-block; margin-right: 10px; margin-bottom: 10px; }
    .img-box img { width: 60px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd; }
    .img-box .btn-del { position: absolute; top: -5px; right: -5px; background: red; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 12px; }
</style>
@endpush

<div class="content-wrapper">
    <div class="container-fluid py-3">
        <form action="{{ route('admin.landing_pages_eight.update', $item->id) }}" method="POST" enctype="multipart/form-data" id="ajax_form">
            @csrf
            @method('PATCH')
            <input type="hidden" name="page_type" value="8">
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-heading"></i> ১. হেডলাইন ও ভিডিও</div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="fw-bold">মেইন হেডলাইন (Title 1) <span class="text-danger">*</span></label>
                                <input type="text" name="title1" class="form-control" value="{{ $item->title1 }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">URL Slug <small class="text-muted">(optional — খালি রাখলে আগেরটাই থাকবে)</small></label>
                                <input type="text" name="slug" class="form-control" value="{{ $item->slug }}" placeholder="e.g. summer-sale-2026" pattern="[a-z0-9]+(-[a-z0-9]+)*" title="শুধু ছোট হাতের অক্ষর, সংখ্যা ও হাইফেন">
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">সাব-হেডলাইন (Title 2)</label>
                                <input type="text" name="title2" class="form-control" value="{{ $item->title2 }}">
                            </div>

                            {{-- ✅ FIXED: Added Phone Number and Call Text Option for Edit Mode ✅ --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="fw-bold">যোগাযোগের নাম্বার (Phone) <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control" value="{{ $item->phone ?? $item->phone_number ?? '' }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="fw-bold">কল বাটন টেক্সট (Call Text)</label>
                                    <input type="text" name="call_text" class="form-control" value="{{ $item->call_text ?? '' }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold">YouTube Embed URL / Iframe</label>
                                <input type="text" name="video_url" class="form-control" value="{{ $item->video_url }}">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="fw-bold">ফর্ম টাইটেল</label>
                                    <input type="text" name="form_title" class="form-control" value="{{ $item->form_title }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="fw-bold">টাইমার টেক্সট</label>
                                    <input type="text" name="countdown_title" class="form-control" value="{{ $item->countdown_title }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-list"></i> ২. বিস্তারিত বিবরণ ও ফিচার</div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="fw-bold">ফিচার সেকশন টাইটেল</label>
                                <input type="text" name="feature_title" class="form-control" value="{{ $item->feature_title }}">
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">ফিচার লিস্ট (Summernote Editor)</label>
                                <textarea name="feature_list" class="form-control summernote" rows="4">{!! $item->feature_list !!}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">অন্যান্য বিস্তারিত বিবরণ (Summernote Editor)</label>
                                <textarea name="left_side_desc" class="form-control summernote">{!! $item->left_side_desc !!}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="premium-card" style="border: 2px solid #ef4444;">
                        <div class="card-header-custom" style="background: #fef2f2;"><i class="fas fa-boxes"></i> ৩. প্যাকেজ / বান্ডেল অফার</div>
                        <div class="card-body p-4">
                            <table class="table table-bordered text-center" id="packageTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>পরিমাণ (Qty)</th>
                                        <th>প্যাকেজ মূল্য (Price)</th>
                                        <th>ছাড়ের টেক্সট (Discount)</th>
                                        <th>অ্যাকশন</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($item->packages && $item->packages->count() > 0)
                                        @foreach($item->packages as $k => $pkg)
                                        <tr>
                                            <td><input type="number" name="pkg_qty[]" class="form-control" value="{{ $pkg->qty }}" required></td>
                                            <td><input type="number" name="pkg_price[]" class="form-control" value="{{ $pkg->price }}" required></td>
                                            <td><input type="text" name="pkg_discount_text[]" class="form-control" value="{{ $pkg->discount_text }}"></td>
                                            <td>
                                                <input type="radio" name="pkg_is_default" value="{{ $k }}" {{ $pkg->is_default ? 'checked' : '' }} class="d-none">
                                                <button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td><input type="number" name="pkg_qty[]" class="form-control" value="1" required></td>
                                            <td><input type="number" name="pkg_price[]" class="form-control" placeholder="প্যাকেজ মূল্য" required></td>
                                            <td><input type="text" name="pkg_discount_text[]" class="form-control"></td>
                                            <td>
                                                <input type="radio" name="pkg_is_default" value="0" checked class="d-none">
                                                <button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            <button type="button" id="addPkgRow" class="btn btn-sm btn-success"><i class="fas fa-plus"></i> Add Another Package</button>
                        </div>
                    </div>

                    {{-- ✅ FAQ SECTION --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-question-circle"></i> ৪. সচরাচর জিজ্ঞাসা (FAQ)</div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="fw-bold">FAQ Title</label>
                                    <input type="text" name="faq_title" class="form-control" value="{{ $item->faq_title ?? 'সচরাচর জিজ্ঞাসা (FAQ)' }}">
                                </div>
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

                </div>

                <div class="col-lg-4">
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-box"></i> প্রোডাক্ট সিলেক্ট</div>
                        <div class="card-body p-4">
                            <div class="form-group mb-3 position-relative">
                                <label class="fw-bold">প্রোডাক্ট পরিবর্তন করুন (নাম বা SKU)</label>
                                <input type="text" id="search_product" class="form-control" placeholder="সার্চ..." autocomplete="off">
                                <input type="hidden" name="new_product_id" id="new_product_id" value="{{ $item->product_id }}" required>
                            </div>
                            
                            <div id="selected_product_preview" class="mb-3 p-2 border rounded bg-light">
                                <b class="text-success"><i class="fas fa-check-circle"></i> সিলেক্টেড:</b> 
                                <span id="selected_name" class="fw-bold">{{ $single_product ? $single_product->name : '' }}</span>
                            </div>
                            
                            <div class="mb-3">
                                <label class="fw-bold">অফার মূল্য (New Price)</label>
                                <input type="number" name="new_price" id="new_price_input" class="form-control" value="{{ $item->new_price }}">
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">রেগুলার মূল্য (Old Price)</label>
                                <input type="number" name="old_price" class="form-control" value="{{ $item->old_price }}">
                            </div>
                        </div>
                    </div>

                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-images"></i> ছবি ও ট্রাস্ট ব্যাজ</div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="fw-bold d-block">বিকল্প প্রোডাক্ট ছবি</label>
                                @if($item->right_product_image)
                                    <div class="img-box mb-2"><img src="{{ asset('landing_pages/'.$item->right_product_image)}}"></div>
                                @endif
                                <input type="file" name="right_product_image" class="form-control" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold d-block">ট্রাস্ট/সার্টিফিকেট ছবি</label>
                                <input type="text" name="review_title" class="form-control mb-2" value="{{ $item->review_title }}">
                                @if(isset($review_images) && $review_images->count() > 0)
                                    <div class="mb-2">
                                        @foreach($review_images as $rv)
                                        <div class="img-box">
                                            <a href="{{ route('admin.delete_review', $rv->id) }}" class="btn-del">&times;</a>
                                            <img src="{{ asset('review_landing_sliders/'.$rv->review_image) }}">
                                        </div>
                                        @endforeach
                                    </div>
                                @endif
                                <input type="file" name="review_product_image[]" class="form-control" accept="image/*" multiple>
                            </div>
                        </div>
                    </div>

                    <button type="submit" id="save_btn" class="btn btn-danger btn-lg w-100 fw-bold"><i class="fas fa-sync"></i> Update Page</button>
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
                $('#new_price_input').val(ui.item.price);
                $('#search_product').val(''); return false;
            }
        }).autocomplete("instance")._renderItem = function(ul, item) {
            return $("<li>").append(`<div class="d-flex align-items-center p-2 border-bottom"><img src="${item.image}" width="40" height="40" class="me-2 rounded"><div><h6 class="m-0">${item.name}</h6><small class="text-danger fw-bold">৳${item.price}</small></div></div>`).appendTo(ul);
        };

        // Add Package Row
        let pkgIndex = 99; // Offset for edit
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
            btn.data('submitting', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
            
            let formData = new FormData(this);
            $.ajax({
                url: $(this).attr('action'), type: 'POST', data: formData, processData: false, contentType: false,
                success: function(res) {
                    window.location.href = res.url || "{{ route('admin.landing_pages_eight') }}";
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