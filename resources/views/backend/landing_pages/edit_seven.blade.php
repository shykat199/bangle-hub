@extends('backend.app')
@section('title', 'Edit Landing Page (Type 7)')

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
    .btn-save { background: #006400; color: #fff !important; padding: 12px 30px; border-radius: 8px; border: none; width: 100%; font-weight: 700; box-shadow: 0 4px 6px rgba(0,100,0,0.2); transition: 0.3s; }
    .btn-save:hover { background: #004d00; }
    .existing-img-container { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 10px; }
    .existing-img-box { position: relative; border: 1px solid #e5e7eb; border-radius: 8px; padding: 4px; background: #fff; }
    .existing-img-box img { border-radius: 6px; width: 70px; height: 70px; object-fit: cover; }
    .btn-delete-img { position: absolute; top: -8px; right: -8px; background: #ef4444; color: white; border-radius: 50%; width: 22px; height: 22px; font-size: 12px; display: flex; align-items: center; justify-content: center; text-decoration: none;}
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <div class="container-fluid">
        <div class="page-title-box py-3 d-flex justify-content-between">
            <h4 class="page-title fw-bold text-dark mb-0">Edit Landing Page (Type 7)</h4>
            <a href="{{ route('admin.landing_pages_seven') }}" class="btn btn-secondary btn-sm">Back</a>
        </div>

        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.landing_pages_seven.update', [$item->id]) }}" id="ajax_form">
            @csrf @method('PATCH')
            
            <div class="row">
                <div class="col-lg-8">
                    
                    {{-- 1. BASIC INFO --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-heading text-success"></i><h5>১. বেসিক ইনফরমেশন ও হেডলাইন</h5></div>
                        <div class="card-body-custom">
                            <div class="form-group mb-3">
                                <label class="fw-bold">মেইন হেডলাইন (Title 1) *</label>
                                <input type="text" name="title1" value="{{ $item->title1 }}" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">URL Slug <small class="text-muted">(optional — খালি রাখলে আগেরটাই থাকবে)</small></label>
                                <input type="text" name="slug" value="{{ $item->slug }}" class="form-control" placeholder="e.g. summer-sale-2026" pattern="[a-z0-9]+(-[a-z0-9]+)*" title="শুধু ছোট হাতের অক্ষর, সংখ্যা ও হাইফেন">
                                <input type="hidden" name="page_type" value="7">
                                <input type="hidden" name="new_product_id" id="new_product_id" value="{{ $item->product_id }}">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">যোগাযোগের নাম্বার</label>
                                    <input type="text" name="phone" value="{{ $item->phone }}" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">YouTube Video (Embed Code)</label>
                                    <input type="text" name="video_url" value="{{ $item->video_url }}" class="form-control">
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
                                <input type="text" name="feature_title" value="{{ $item->feature_title }}" class="form-control">
                            </div>
                            <div class="form-group mb-3">
                                <label class="fw-bold">ফিচার লিস্ট (Summernote)</label>
                                <textarea name="feature_list" class="form-control summernote">{!! $item->feature_list !!}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- 3. PROMISES --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-shield-alt text-success"></i><h5>৩. আমাদের প্রতিশ্রুতি</h5></div>
                        <div class="card-body-custom">
                            <div class="form-group mb-3">
                                <label class="fw-bold">Promise Title</label>
                                <input type="text" name="promise_title" value="{{ $item->promise_title }}" class="form-control">
                            </div>
                            <div class="row g-3">
                                @for($i=1; $i<=3; $i++)
                                <div class="col-md-4">
                                    <label class="fw-bold">পয়েন্ট {{ $i }}</label>
                                    <input type="text" name="promise_{{ $i }}_title" value="{{ $item->{'promise_'.$i.'_title'} }}" class="form-control mb-2">
                                    <textarea name="promise_{{ $i }}_desc" class="form-control" rows="2">{{ $item->{'promise_'.$i.'_desc'} }}</textarea>
                                </div>
                                @endfor
                            </div>
                            
                            <hr class="my-4">
                            <div class="form-group">
                                <label class="fw-bold text-danger"><i class="fas fa-times-circle"></i> নেগেটিভ ট্যাগ (Negative Points)</label>
                                <input type="text" name="negative_tags" value="{{ $item->negative_tags }}" class="form-control">
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
                                    <input type="text" name="faq_title" value="{{ $item->faq_title }}" class="form-control">
                                </div>
                                @for($i=1; $i<=4; $i++)
                                <div class="col-md-12 border-bottom pb-2 mt-3">
                                    <label class="fw-bold">প্রশ্ন {{ $i }}</label>
                                    <input type="text" name="faq_{{ $i }}_q" value="{{ $item->{'faq_'.$i.'_q'} }}" class="form-control mb-2">
                                    <textarea name="faq_{{ $i }}_a" class="form-control" rows="2">{{ $item->{'faq_'.$i.'_a'} }}</textarea>
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
                            <div id="product_search" style="display: {{ $single_product ? 'none' : 'block' }};">
                                <input type="text" id="search2" class="form-control mb-3" placeholder="Search product...">
                            </div>
                            <div id="product_container" style="display: {{ $single_product ? 'block' : 'none' }};">
                                <table class="table table-bordered mb-0" id="product_table">
                                    <tbody id="data">
                                        @if($single_product)
                                        <tr>
                                            <td><img src="{{ getImage('products', $single_product->image) }}" height="50" width="50" class="rounded"/></td>
                                            <td class="fw-bold">{{ $single_product->name }}</td>
                                            <td><a class="remove-product btn btn-sm btn-danger"><i class="fas fa-trash"></i></a></td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            <div class="row g-2 mt-3">
                                <div class="col-6"><label class="fw-bold text-danger">Regular Price</label><input type="number" name="old_price" value="{{ $item->old_price }}" class="form-control"></div>
                                <div class="col-6"><label class="fw-bold text-success">Offer Price</label><input type="number" name="new_price" value="{{ $item->new_price }}" class="form-control"></div>
                            </div>
                        </div>
                    </div>

                    {{-- IMAGES --}}
                    <div class="premium-card">
                        <div class="card-header-custom"><i class="fas fa-images text-success"></i><h5>ছবি আপলোড</h5></div>
                        <div class="card-body-custom">
                            <div class="mb-4">
                                <label class="fw-bold mb-2">Main Product Image</label>
                                @if($item->right_product_image)
                                <div class="existing-img-container">
                                    <div class="existing-img-box"><img src="{{ asset('landing_pages/'.$item->right_product_image)}}"></div>
                                </div>
                                @endif
                                <input type="file" name="right_product_image" class="form-control">
                            </div>

                            <div class="mb-4">
                                <label class="fw-bold mb-2">Gallery Images</label>
                                <div class="existing-img-container">
                                    @foreach ($item->images as $img)
                                        <div class="existing-img-box">
                                            <a href="{{ route('admin.delete_slider',[$img->id])}}" class="btn-delete-img">&times;</a>
                                            <img src="{{ asset('landing_sliders/'.$img->image)}}">
                                        </div>
                                    @endforeach
                                </div>
                                <input type="file" name="sliderimage[]" multiple class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold mb-2">Review Screenshots</label>
                                <div class="existing-img-container">
                                    @foreach ($review_images as $rv)
                                        <div class="existing-img-box">
                                            <a href="{{ route('admin.delete_review',[$rv->id])}}" class="btn-delete-img">&times;</a>
                                            <img src="{{ asset('review_landing_sliders/'.$rv->review_image)}}">
                                        </div>
                                    @endforeach
                                </div>
                                <input type="file" name="review_product_image[]" multiple class="form-control">
                            </div>
                        </div>
                    </div>

                    <button type="submit" id="save_btn" class="btn-save"><i class="fas fa-save"></i> Update Landing Page</button>
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

    var path2 = "{{ route('admin.getOrderProduct2') }}";
    $("#search2").autocomplete({
        source: function(request, response) {
            $.getJSON(path2, { search: request.term }, function(data){
                if (data.length == 0) { toastr.error('Product Not Found'); } else { response(data); }
            });
        },
        select: function(event, ui) {
            $.getJSON('{{ route("admin.landingProductEntry")}}', {id: ui.item.id}, function(res){
                if (res.html) {
                    $('div#product_container').show();
                    $('tbody#data').html(res.html);
                    $('#product_search').hide();
                }
                if (res.pr_id) { $('#new_product_id').val(res.pr_id); }
            });
            $('#search2').val(''); return false;
        }
    });

    $(document).on('click', '.remove-product', function(e){
        e.preventDefault();
        $(this).closest("tr").remove();
        $('#product_search').show();
        $('#new_product_id').val('');
    });

    $('#ajax_form').submit(function(e) {
        if ($('#new_product_id').val() === '') {
            e.preventDefault();
            toastr.error('দয়া করে একটি প্রোডাক্ট সিলেক্ট করুন!');
            $('#search2').focus();
        } else {
            $('#save_btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
        }
    });
});
</script>
@endpush