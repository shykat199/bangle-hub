@extends('backend.app')
@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush
@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active">About Us  Manage</li>
                </ol>
            </div>
            <h4 class="page-title">About Us  Manage</h4>
        </div>
    </div>
</div>   
<!-- end page title --> 

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.about_us.store')}}" id="ajax_form">
                    @csrf
                    <div class="row">
                        <div class="col-lg-8 p-5">
                            
                            <div class="mb-3">
                                <label for="example-email" class="form-label">Cover Image</label>
                                <input type="file" name="cover_image" class="form-control">
                            </div>
                            
                            <div class="mb-3">
                                <label for="example-email" class="form-label">Site Name</label>
                                <input type="text" name="site_name" class="form-control" value="{{ $item->site_name}}">
                            </div>
                            
                            <div class="mb-3">
                                <label for="example-email" class="form-label">Site Url</label>
                                <input type="text" name="site_url" class="form-control" value="{{ $item->site_name}}">
                            </div>
                            
                            
                            <div class="mb-3">
                                <label  class="form-label">Page title</label>
                                <input type="text" name="page_title" class="form-control" placeholder="Page title" value="{{ $item->page_title}}">
                            </div>
                            
                            <div class="mb-3">
                                <label  class="form-label">Sub title</label>
                                <input type="text" name="sub_title" class="form-control" placeholder="Sub title" value="{{ $item->sub_title}}">
                            </div>
                            
                            
                            <div class="mb-3">
                                <label  class="form-label">Speech</label>
                                <textarea class="form-control" name="speech" rows="5">{{ $item->speech}}</textarea>
                            </div>
                            

                            

                            <div class="mb-3">
                                <label for="example-email" class="form-label">signature</label>
                                <input type="file" name="signature" class="form-control">
                            </div>


                            <div class="mb-3">
                                <label  class="form-label">Page Description</label>
                                <textarea class="form-control" name="page_desc" rows="5">{{ $item->page_desc}}</textarea>
                            </div>
                            
                            
                            <div class="mb-3">
                                <label for="example-email" class="form-label">title one</label>
                                <input type="text" name="title_one" value="{{ $item->title_one}}" class="form-control">
                            </div>


                            <div class="mb-3">
                                <label  class="form-label">title one Description</label>
                                <textarea class="form-control" name="desc_one" rows="5">{{ $item->desc_one}}</textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="example-email" class="form-label">title Two</label>
                                <input type="text" name="title_two" value="{{ $item->title_two}}" class="form-control">
                            </div>


                            <div class="mb-3">
                                <label  class="form-label">title Two Description</label>
                                <textarea class="form-control" name="desc_two" rows="5">{{ $item->desc_two}}</textarea>
                            </div>
                            
                            
                            
                            <div class="mb-3">
                                <label  class="form-label">Video Link</label>
                                <input type="text" name="video" class="form-control" placeholder="Video Link" value="{{ $item->video}}">
                            </div>
                            
                        </div>



                        <div class="col-lg-12 pl-5">
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </div>

                </form>
       
            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div> <!-- end col -->
</div> <!-- end row -->
@endsection 

@push('js')
<script src="https://cdn.ckeditor.com/4.12.1/standard/ckeditor.js"></script>
<script type="text/javascript">
    CKEDITOR.replace('body', {
        filebrowserUploadUrl: "{{route('admin.ckeditor.upload', ['_token' => csrf_token() ])}}",
        filebrowserUploadMethod: 'form'
    });

    $(document).ready(function() {
        $('.js-example-basic-multiple').select2();
    });

</script>

@endpush