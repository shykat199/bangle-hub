@extends('backend.app')

@section('content')
@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
{{-- আপনার দেওয়া CSS গুলো এখানে থাকবে (সংক্ষিপ্ততার জন্য বাদ দেওয়া হলো, আপনি create_two থেকে স্টাইল কপি করতে পারেন) --}}
<style>
    /* ... create_two.blade.php এর CSS এখানে পেস্ট করুন ... */
    .premium-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.03); margin-bottom: 24px; overflow: hidden; }
    .card-header-custom { padding: 20px 24px; border-bottom: 1px solid #e5e7eb; background: #fff; display: flex; align-items: center; gap: 10px; }
    .card-body-custom { padding: 24px; }
    .file-upload-wrapper { position: relative; border: 2px dashed #d1d5db; border-radius: 12px; padding: 20px; text-align: center; background: #f9fafb; cursor: pointer; }
    .img-preview-container { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 15px; justify-content: center; }
    .preview-box { width: 80px; height: 80px; border-radius: 8px; overflow: hidden; border: 1px solid #ddd; }
    .preview-box img { width: 100%; height: 100%; object-fit: cover; }
    .btn-save { background: #4f46e5; color: white; padding: 12px 30px; border-radius: 8px; border: none; width: 100%; font-weight: 600; }
</style>
@endpush

<div class="container-fluid">
    <div class="row"><div class="col-12"><div class="page-title-box d-flex align-items-center justify-content-between"><h4 class="page-title">Create Premium Page (Type 3)</h4></div></div></div>

    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.landing_pages_three.store') }}" id="ajax_form">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-info-circle"></i><h5>Hero Information</h5></div>
                    <div class="card-body-custom">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Page Title *</label>
                                <input type="text" name="title1" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">URL Slug <small class="text-muted">(optional — খালি রাখলে টাইটেল থেকে বানানো হবে)</small></label>
                                <input type="text" name="slug" class="form-control" placeholder="e.g. summer-sale-2026" pattern="[a-z0-9]+(-[a-z0-9]+)*" title="শুধু ছোট হাতের অক্ষর, সংখ্যা ও হাইফেন">
                                {{-- ✅ PAGE TYPE 3 --}}
                                <input type="hidden" name="page_type" value="3">
                            </div>
                            <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label">Call Text</label><input type="text" name="call_text" class="form-control"></div>
                        </div>
                    </div>
                </div>

                <div class="premium-card">
                    <div class="card-header-custom"><i class="fas fa-align-left"></i><h5>Content</h5></div>
                    <div class="card-body-custom">
                        <div class="mb-3"><label class="form-label">Headline</label><input type="text" name="left_side_title" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Details</label><textarea class="form-control summernote" name="left_side_desc"></textarea></div>
                    </div>
                </div>

                <div class="premium-card border-primary">
                    <div class="card-header-custom" style="background: #eff6ff;"><h5 class="text-primary">Product</h5></div>
                    <div class="card-body-custom">
                        <input type="text" id="search2" class="form-control" placeholder="Search Product...">
                        <input type="hidden" id="product_id" name="product_id">
                        <div id="data" class="mt-3"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="premium-card">
                    <div class="card-header-custom"><h5>Media</h5></div>
                    <div class="card-body-custom">
                        <div class="mb-3"><label>Background</label><input type="file" name="landing_bg" class="form-control"></div>
                        <div class="mb-3"><label>Video URL</label><input type="text" name="video_url" class="form-control"></div>
                        <div class="mb-3"><label>Slider Images</label><input type="file" name="sliderimage[]" multiple class="form-control"></div>
                    </div>
                </div>
                <button type="button" id="save_btn" class="btn-save"><i class="fas fa-save"></i> Save & Publish</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script>
    // আপনার create_two.blade.php এর JS কোডটি এখানে হুবহু বসাবেন।
    // সংক্ষিপ্ত করার জন্য আমি এখানে পুরোটা দিচ্ছি না, শুধু মনে রাখবেন:
    // 1. Summernote Init
    // 2. Product Search Logic (path2 = getOrderProduct2)
    // 3. Save Button Ajax Logic
    $(document).ready(function() {
        $('.summernote').summernote({ height: 200 });
        
        var path2 = "{{ route('admin.getOrderProduct2') }}";
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
                url: '{{ route("admin.landingProductEntry")}}', type: 'GET', dataType: "json", data: { id: item.id },
                success: function(res) { $('div#data').html(res.html); $('#product_id').val(res.pr_id); }
            });
        }

        $(document).on('click', '#save_btn', function(e) {
            e.preventDefault();
            let form = document.getElementById('ajax_form');
            let formData = new FormData(form);
            $.ajax({
                url: $(form).attr('action'), type: 'POST', data: formData, processData: false, contentType: false,
                success: function(res) { toastr.success('Saved!'); window.location.href = res.url; },
                error: function(err) { toastr.error('Error!'); }
            });
        });
    });
</script>
@endpush