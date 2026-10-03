@extends('backend.app')
@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    :root{
        --bg:#f3f4f6;
        --card:#ffffff;
        --border:#e5e7eb;
        --primary:#0ea5e9;
        --primary-soft:rgba(14,165,233,.15);
        --text:#111827;
        --muted:#6b7280;
        --shadow:0 10px 28px rgba(15,23,42,.08);
    }

    body{
        background: var(--bg);
    }

    .page-title-box h4{
        font-weight:700;
    }

    .card-modern{
        border:0;
        border-radius:20px;
        background:var(--card);
        box-shadow:var(--shadow);
        overflow:hidden;
    }
    .card-modern .card-header{
        background:linear-gradient(135deg,#eff6ff,#dbeafe);
        padding:12px 20px;
        border-bottom:1px solid rgba(15,23,42,.05);
    }
    .card-modern .card-header h4{
        margin:0;
        font-weight:700;
        color:#1e293b;
        font-size:1rem;
    }
    .card-modern .card-body{
        padding:1rem 1.25rem 1.5rem;
    }

    .form-label{
        font-size:.85rem;
        color:var(--muted);
        font-weight:600;
        text-transform:none;
    }
    .form-control{
        border-radius:10px;
        border-color:var(--border);
        font-size:.9rem;
    }
    .form-control:focus{
        border-color:var(--primary);
        box-shadow:0 0 0 .15rem var(--primary-soft);
    }

    .btn-primary{
        border:none;
        border-radius:10px;
        padding:.45rem 1.4rem;
        font-weight:600;
        font-size:.9rem;
        background:linear-gradient(to right,#0ea5e9,#2563eb);
    }

    .section-title{
        font-size:.95rem;
        font-weight:600;
        color:#0f172a;
        margin-bottom:.75rem;
    }

    @media (max-width: 768px){
        .card-modern .card-body{
            padding:1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active">Page Update</li>
                </ol>
                <h4 class="page-title mt-1">Page Update</h4>
            </div>
        </div>
    </div>
</div>   
<!-- end page title --> 

<div class="row">
    <div class="col-12">            
        <div class="card card-modern">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Edit Page Content</h4>
                <a href="{{ route('admin.pages.index') }}" class="btn btn-sm btn-outline-secondary">
                    Back to List
                </a>
            </div>
            
            <div class="card-body">
                <form method="POST" action="{{ route('admin.pages.update',[$item->id])}}" id="ajax_form">
                    @csrf
                    @method('PATCH')
                    <div class="row g-3">
                        <div class="col-lg-12">
                            <p class="section-title">Basic Info</p>
                            <div class="mb-2">
                                <label class="form-label">Page Slug / Name</label>
                                <input type="text"
                                       name="page"
                                       value="{{ $item->page }}"
                                       class="form-control"
                                       placeholder="e.g. about-us, terms-and-conditions">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Page Title</label>
                                <input type="text"
                                       name="title"
                                       class="form-control"
                                       placeholder="Title"
                                       value="{{ $item->title }}">
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <p class="section-title">Page Body</p>
                            <div class="mb-3">
                                <label class="form-label d-flex justify-content-between align-items-center">
                                    <span>Content</span>
                                    <small class="text-muted" style="font-size:.75rem;">
                                        You can format text, add images and links using the editor.
                                    </small>
                                </label>
                                <textarea class="form-control"
                                          id="body"
                                          name="body"
                                          rows="10"
                                          cols="10">{!! $item->body !!}</textarea>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </div>

                </form>
            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div>  
</div> <!-- end row -->
@endsection 

@push('js')
<script src="https://cdn.ckeditor.com/4.12.1/standard/ckeditor.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script type="text/javascript">
    CKEDITOR.replace('body', {
        filebrowserUploadUrl: "{{route('admin.ckeditor.upload', ['_token' => csrf_token() ])}}",
        filebrowserUploadMethod: 'form'
    });
</script>
@endpush
