@extends('backend.app')

@section('content')
@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
<style>
    .premium-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.03); margin-bottom: 24px; overflow: hidden; }
    .card-header-custom { padding: 20px 24px; border-bottom: 1px solid #e5e7eb; background: #fff; display: flex; align-items: center; gap: 10px; color: #0f766e; }
    .card-body-custom { padding: 24px; }
    .form-control:focus { border-color: #0d9488; box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1); }
    .file-upload-wrapper { position: relative; border: 2px dashed #d1d5db; border-radius: 12px; padding: 20px; text-align: center; background: #f0fdf4; cursor: pointer; transition: 0.3s; }
    .file-upload-wrapper:hover { border-color: #0d9488; }
    .existing-img-container { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 15px; }
    .existing-img-box { position: relative; border: 1px solid #e5e7eb; border-radius: 8px; padding: 4px; background: #fff; }
    .existing-img-box img { border-radius: 6px; width: 70px; height: 70px; object-fit: cover; }
    .btn-delete-img { position: absolute; top: -8px; right: -8px; background: #ef4444; color: white; border-radius: 50%; width: 22px; height: 22px; font-size: 12px; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
    .btn-save { background: #0d9488; color: white; padding: 12px 30px; border-radius: 8px; border: none; width: 100%; font-weight: 600; transition: 0.3s; font-size: 16px; }
    .btn-save:hover { background: #0f766e; }
    #product_table td small, #product_table td .text-muted { display: none !important; }
    .section-title { font-size: 1.1rem; font-weight: 700; color: #374151; margin-bottom: 15px; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px; }
</style>
@endpush

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="page-title">Edit Custom Page (Type 5)</h4>
                <div class="page-title-right">
                    <a href="{{ route('admin.landing_pages_five') }}" class="btn btn-sm btn-secondary">Back to List</a>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.landing_pages_five.update', [$item->id]) }}" id="ajax_form">
        @csrf
        @method('PATCH')
        
        <div class="row">
            {{-- LEFT COLUMN --}}
            <div class="col-lg-8">
                
                {{-- 1. BASIC INFORMATION --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-edit"></i><h5 class="m-0 fw-bold">Basic Information</h5></div>
                    <div class="card-body-custom">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Page Title *</label>
                                <input type="text" value="{{ $item->title1 }}" name="title1" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">URL Slug <small class="text-muted">(optional — খালি রাখলে আগেরটাই থাকবে)</small></label>
                                <input type="text" value="{{ $item->slug }}" name="slug" class="form-control" placeholder="e.g. summer-sale-2026" pattern="[a-z0-9]+(-[a-z0-9]+)*" title="শুধু ছোট হাতের অক্ষর, সংখ্যা ও হাইফেন">
                                <input type="hidden" name="page_type" value="5">
                                <input type="hidden" name="product_id" id="product_id" value="{{ $item->product_id }}">
                                <input type="hidden" id="new_product_id" name="new_product_id">
                                <input type="hidden" name="variation_id" id="variation_id" value="{{ $item->variation_id }}">
                            </div>
                            <div class="col-md-6"><label class="form-label">Phone Number</label><input type="text" value="{{ $item->phone }}" name="phone" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label">WhatsApp Number</label><input type="text" value="{{ $item->call_text }}" name="call_text" class="form-control"></div>
                        </div>
                    </div>
                </div>

                {{-- 2. HERO & COUNTDOWN --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-clock"></i><h5 class="m-0 fw-bold">Hero & Countdown</h5></div>
                    <div class="card-body-custom">
                        <div class="row g-3">
                            <div class="col-md-12"><label class="form-label">Countdown Title</label><input type="text" value="{{ $item->countdown_title }}" name="countdown_title" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label">Countdown Hours</label><input type="number" value="{{ $item->countdown_hours }}" name="countdown_hours" class="form-control"></div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Countdown BG Color</label>
                                <input type="color" value="{{ $item->countdown_bg_color ?? '#e67e22' }}" name="countdown_bg_color" class="form-control form-control-color w-100">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Countdown Text Color</label>
                                <input type="color" value="{{ $item->countdown_text_color ?? '#ffffff' }}" name="countdown_text_color" class="form-control form-control-color w-100">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Hero Button BG</label>
                                <input type="color" value="{{ $item->hero_btn_bg_color ?? '#d35400' }}" name="hero_btn_bg_color" class="form-control form-control-color w-100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hero Button Text Color</label>
                                <input type="color" value="{{ $item->hero_btn_text_color ?? '#ffffff' }}" name="hero_btn_text_color" class="form-control form-control-color w-100">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. PRICE & PROMISES --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-tags"></i><h5 class="m-0 fw-bold">Price & Promises</h5></div>
                    <div class="card-body-custom">
                        
                        {{-- Price Texts REMOVED --}}

                        <h6 class="section-title">Promises Header</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4"><label class="form-label">Badge Text</label><input type="text" value="{{ $item->promise_badge }}" name="promise_badge" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label">Section Title</label><input type="text" value="{{ $item->promise_title }}" name="promise_title" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label">Image Badge</label><input type="text" value="{{ $item->promise_img_badge }}" name="promise_img_badge" class="form-control"></div>
                        </div>

                        <h6 class="section-title">Promise Details (3 Boxes)</h6>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Promise 1</label>
                                <input type="text" value="{{ $item->promise_1_title }}" name="promise_1_title" class="form-control mb-2" placeholder="Title">
                                <textarea name="promise_1_desc" class="form-control" rows="2" placeholder="Description">{{ $item->promise_1_desc }}</textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Promise 2</label>
                                <input type="text" value="{{ $item->promise_2_title }}" name="promise_2_title" class="form-control mb-2" placeholder="Title">
                                <textarea name="promise_2_desc" class="form-control" rows="2" placeholder="Description">{{ $item->promise_2_desc }}</textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Promise 3</label>
                                <input type="text" value="{{ $item->promise_3_title }}" name="promise_3_title" class="form-control mb-2" placeholder="Title">
                                <textarea name="promise_3_desc" class="form-control" rows="2" placeholder="Description">{{ $item->promise_3_desc }}</textarea>
                            </div>
                        </div>

                        <h6 class="section-title mt-4">Negative List (আমাদের পণ্যে যা নেই)</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Negative Title</label>
                                <input type="text" value="{{ $item->negative_title ?? 'আমাদের পণ্যে যা নেই:' }}" name="negative_title" class="form-control">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Negative Tags (Comma Separated)</label>
                                <input type="text" value="{{ $item->negative_tags }}" name="negative_tags" class="form-control" placeholder="যেমন: কোনো কেমিক্যাল, কোনো ভেজাল">
                            </div>
                        </div>

                    </div>
                </div>

                {{-- 4. IDENTIFY & TRUST BADGES --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-shield-alt"></i><h5 class="m-0 fw-bold">Identify & Trust</h5></div>
                    <div class="card-body-custom">
                        <h6 class="section-title">Identify Product Section</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4"><label class="form-label">Badge</label><input type="text" value="{{ $item->identify_badge }}" name="identify_badge" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label">Title</label><input type="text" value="{{ $item->identify_title }}" name="identify_title" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label">Subtitle</label><input type="text" value="{{ $item->identify_subtitle }}" name="identify_subtitle" class="form-control"></div>
                        </div>

                        <h6 class="section-title">Trust Badges (Footer)</h6>
                        <div class="mb-3"><label class="form-label">Section Title</label><input type="text" value="{{ $item->trust_sec_title }}" name="trust_sec_title" class="form-control"></div>
                        <div class="row g-3">
                            <div class="col-md-3"><input type="text" value="{{ $item->trust_1_icon }}" name="trust_1_icon" class="form-control mb-1" placeholder="Icon Class"><input type="text" value="{{ $item->trust_1_title }}" name="trust_1_title" class="form-control" placeholder="Title"></div>
                            <div class="col-md-3"><input type="text" value="{{ $item->trust_2_icon }}" name="trust_2_icon" class="form-control mb-1" placeholder="Icon Class"><input type="text" value="{{ $item->trust_2_title }}" name="trust_2_title" class="form-control" placeholder="Title"></div>
                            <div class="col-md-3"><input type="text" value="{{ $item->trust_3_icon }}" name="trust_3_icon" class="form-control mb-1" placeholder="Icon Class"><input type="text" value="{{ $item->trust_3_title }}" name="trust_3_title" class="form-control" placeholder="Title"></div>
                            <div class="col-md-3"><input type="text" value="{{ $item->trust_4_icon }}" name="trust_4_icon" class="form-control mb-1" placeholder="Icon Class"><input type="text" value="{{ $item->trust_4_title }}" name="trust_4_title" class="form-control" placeholder="Title"></div>
                        </div>
                    </div>
                </div>

                {{-- 5. FEATURES & REVIEW SECTION --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-list"></i><h5 class="m-0 fw-bold">Features & Reviews</h5></div>
                    <div class="card-body-custom">
                        <h6 class="section-title">Review Section Headers</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4"><label class="form-label">Badge</label><input type="text" value="{{ $item->review_badge }}" name="review_badge" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label">Title</label><input type="text" value="{{ $item->review_title }}" name="review_title" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label">Subtitle</label><input type="text" value="{{ $item->review_subtitle }}" name="review_subtitle" class="form-control"></div>
                        </div>

                        <h6 class="section-title">Statistics (3 Items)</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4"><input type="text" value="{{ $item->stat_1_num }}" name="stat_1_num" class="form-control mb-1" placeholder="Number"><input type="text" value="{{ $item->stat_1_text }}" name="stat_1_text" class="form-control" placeholder="Text"></div>
                            <div class="col-md-4"><input type="text" value="{{ $item->stat_2_num }}" name="stat_2_num" class="form-control mb-1" placeholder="Number"><input type="text" value="{{ $item->stat_2_text }}" name="stat_2_text" class="form-control" placeholder="Text"></div>
                            <div class="col-md-4"><input type="text" value="{{ $item->stat_3_num }}" name="stat_3_num" class="form-control mb-1" placeholder="Number"><input type="text" value="{{ $item->stat_3_text }}" name="stat_3_text" class="form-control" placeholder="Text"></div>
                        </div>

                        <h6 class="section-title">Customer Reviews (4 Boxes)</h6>
                        @for($i=1; $i<=4; $i++)
                        <div class="mb-3 border p-3 rounded">
                            <label class="fw-bold">Review {{ $i }}</label>
                            <input type="text" value="{{ $item->{'rev_'.$i.'_name'} }}" name="rev_{{ $i }}_name" class="form-control mb-2" placeholder="Customer Name">
                            <input type="text" value="{{ $item->{'rev_'.$i.'_loc'} }}" name="rev_{{ $i }}_loc" class="form-control mb-2" placeholder="Location">
                            <textarea name="rev_{{ $i }}_text" class="form-control" rows="2" placeholder="Review Text">{{ $item->{'rev_'.$i.'_text'} }}</textarea>
                        </div>
                        @endfor
                    </div>
                </div>

                {{-- 6. FAQ SECTION --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-question-circle"></i><h5 class="m-0 fw-bold">FAQ Section</h5></div>
                    <div class="card-body-custom">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6"><label class="form-label">Badge</label><input type="text" value="{{ $item->faq_badge }}" name="faq_badge" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label">Title</label><input type="text" value="{{ $item->faq_title }}" name="faq_title" class="form-control"></div>
                        </div>
                        @for($i=1; $i<=4; $i++)
                        <div class="mb-3">
                            <input type="text" value="{{ $item->{'faq_'.$i.'_q'} }}" name="faq_{{ $i }}_q" class="form-control mb-2 fw-bold" placeholder="Question {{ $i }}">
                            <textarea name="faq_{{ $i }}_a" class="form-control" rows="2" placeholder="Answer {{ $i }}">{{ $item->{'faq_'.$i.'_a'} }}</textarea>
                        </div>
                        @endfor
                    </div>
                </div>

                {{-- 7. TOP HEADLINE & DESC (Main Content) --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-align-left"></i><h5 class="m-0 fw-bold">Main Content Area</h5></div>
                    <div class="card-body-custom">
                        <div class="mb-3"><label class="form-label">Top Headline</label><input type="text" value="{{ $item->left_side_title }}" name="left_side_title" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Main Description</label><textarea class="form-control summernote" name="left_side_desc">{!! $item->left_side_desc !!}</textarea></div>
                    </div>
                </div>

                {{-- 8. PRODUCT INTEGRATION --}}
                <div class="premium-card" style="border: 1px solid #14b8a6;">
                    <div class="card-header-custom" style="background: #f0fdfa;"><i class="fas fa-box-open"></i><h5 class="m-0 fw-bold">Product Integration</h5></div>
                    <div class="card-body-custom">
                        <div id="product_search" style="display: {{ $single_product ? 'none' : 'block' }};">
                            <input type="text" id="search2" class="form-control form-control-lg" placeholder="Search by name or SKU...">
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
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN --}}
            <div class="col-lg-4">
                {{-- 9. VISUAL ASSETS --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-images"></i><h5 class="m-0 fw-bold">Visual Assets</h5></div>
                    <div class="card-body-custom">
                        <div class="mb-4">
                            <label class="form-label">Video Embed Code (iframe)</label>
                            <input type="text" value="{{ $item->video_url }}" name="video_url" class="form-control" placeholder="<iframe>...</iframe>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Slider Images</label>
                            <div class="existing-img-container">
                                @foreach ($item->images as $image)
                                    <div class="existing-img-box">
                                        <a href="{{ route('admin.delete_slider',[$image->id])}}" class="btn-delete-img" onclick="return confirm('Delete image?');">&times;</a>
                                        <img src="{{ getImage('landing_sliders',$image->image)}}">
                                    </div>
                                @endforeach
                            </div>
                            <div class="file-upload-wrapper">
                                <div class="file-upload-text">Upload New Images</div>
                                <input type="file" name="sliderimage[]" multiple style="position:absolute; top:0; left:0; width:100%; height:100%; opacity:0; cursor:pointer;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Background Image</label>
                            @if($item->landing_bg)
                            <div class="existing-img-container">
                                <div class="existing-img-box"><img src="{{ asset('landing_pages/'.$item->landing_bg)}}" alt="bg"></div>
                            </div>
                            @endif
                            <input type="file" name="landing_bg" class="form-control">
                        </div>
                        
                        {{-- Review Images REMOVED --}}

                    </div>
                </div>
                
                {{-- 10. ICON FEATURES (1-8) --}}
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-star"></i><h5 class="m-0 fw-bold">Icon Features</h5></div>
                    <div class="card-body-custom" style="max-height: 600px; overflow-y: auto;">
                        @for($i=1; $i<=8; $i++)
                        <div class="mb-3 border-bottom pb-3">
                            <label class="fw-bold text-muted small">Feature {{ $i }}</label>
                            <input type="text" value="{{ $item->{'id_'.$i.'_icon'} }}" name="id_{{ $i }}_icon" class="form-control mb-1 form-control-sm" placeholder="Icon (e.g. fa-star)">
                            <input type="text" value="{{ $item->{'id_'.$i.'_title'} }}" name="id_{{ $i }}_title" class="form-control mb-1 form-control-sm" placeholder="Title">
                            <textarea name="id_{{ $i }}_desc" class="form-control form-control-sm" rows="2" placeholder="Description">{{ $item->{'id_'.$i.'_desc'} }}</textarea>
                        </div>
                        @endfor
                    </div>
                </div>

                <button type="submit" id="save_btn" class="btn-save mt-3"><i class="fas fa-save me-2"></i> Update Page</button>
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
    $('.summernote').summernote({ height: 250 });

    // Product Search Logic
    var path2 = "{{ route('admin.getOrderProduct2') }}";
    const products = [];

    $("#search2").autocomplete({
        selectFirst: true, minLength: 2,
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
            url: '{{ route("admin.landingProductEntry")}}', type: 'GET', dataType: "json", data: { id: item.id },
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

    $(document).off('click.lp_remove', '.remove-product').on('click.lp_remove', ".remove-product", function(e){
        e.preventDefault(); e.stopImmediatePropagation();
        $(this).closest("tr").remove();
        $('#product_search').show();
        $('#new_product_id').val('');
        $('#variation_id').val(''); 
    });

    // ✅ FIX: Changed event to form submit instead of button click
    $(document).off('submit', '#ajax_form').on('submit', '#ajax_form', function(e){
        e.preventDefault();
        
        const form = this;
        const btn  = $('#save_btn');
        
        if (btn.data('submitting') === 1) return false;
        btn.data('submitting', 1);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        let formData = new FormData(form);
        let varId = $('#variation_id').val();
        if(varId && varId !== "") { formData.set('variation_id', varId); } else { formData.delete('variation_id'); }

        $.ajax({
            url: $(form).attr('action'), 
            type: 'POST', 
            data: formData, 
            processData: false, 
            contentType: false,
            success: function(res){
                toastr.success('Page Updated Successfully!');
                window.location.href = res.url || "{{ route('admin.landing_pages_five') }}";
            },
            error: function(xhr){
                toastr.error('Error occurred! Please check all required fields.');
                btn.data('submitting', 0);
                btn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
@endpush