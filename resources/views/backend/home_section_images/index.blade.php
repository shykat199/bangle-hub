@extends('backend.app')
@section('content')

<style>
    th, td, h4, .img_manage, .form-label {
        color: black !important;
    }
    
    .feat-img-thumb {
        width: 45px;
        height: 45px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #ddd;
        transition: transform 0.2s;
        margin-right: 3px;
    }
    .feat-img-thumb:hover {
        transform: scale(2.5);
        position: relative;
        z-index: 10;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }

    .upload-group {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #e3e6f0;
        margin-bottom: 15px;
    }
    .upload-group label {
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 8px;
        display: block;
        color: #4e73df !important;
    }

    @media (max-width: 768px) {
        .card-body { padding: 10px; }
        .btn-primary { width: 100%; }
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active img_manage">Home Section Image Manage</li>
                </ol>
            </div>
            <h4 class="page-title">Home Section Image Manage</h4>
        </div>
    </div>
</div>   

<div class="row">
    <div class="col-sm-12 col-md-12 col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom">
                <h4 class="m-0 h5"><i class="mdi mdi-plus-circle me-1"></i> Create Featured Collection</h4>
            </div>
            <div class="card-body">
                @can('image.create')
                <form method="POST" action="{{ route('admin.home_section_images.store')}}" id="ajax_form" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="d-none">
                        <input type="file" name="image">
                        <input type="file" name="mobile_image">
                        <select name="section"><option value="none">None</option></select>
                        <input type="text" name="link">
                        <input type="checkbox" name="is_for_small" value="1">
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="upload-group border-secondary" style="background: #f8f9fa;">
                                <label class="text-dark"><i class="mdi mdi-video"></i> Banner Background Video (MP4)</label>
                                <input type="file" name="banner_video" class="form-control mb-2" accept="video/mp4,video/x-m4v,video/*">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="upload-group">
                                <label>Image 1 (Small)</label>
                                <input type="file" name="left_image_1" class="form-control mb-2">
                                <input type="text" name="left_link_1" placeholder="Link 1" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="upload-group">
                                <label>Image 2 (Small)</label>
                                <input type="file" name="left_image_2" class="form-control mb-2">
                                <input type="text" name="left_link_2" placeholder="Link 2" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="upload-group">
                                <label>Image 3 (Small)</label>
                                <input type="file" name="left_image_3" class="form-control mb-2">
                                <input type="text" name="left_link_3" placeholder="Link 3" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="upload-group">
                                <label>Image 4 (Small)</label>
                                <input type="file" name="left_image_4" class="form-control mb-2">
                                <input type="text" name="left_link_4" placeholder="Link 4" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="upload-group border-primary" style="background: #eef2ff;">
                                <label class="text-primary"><i class="mdi mdi-image-size-select-actual"></i> Main Large Image (Right Side)</label>
                                <input type="file" name="right_image" class="form-control mb-2">
                                <input type="text" name="right_link" placeholder="Main Link" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="col-12 mt-2">
                            <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm">
                                <i class="mdi mdi-content-save"></i> Save Collection
                            </button>
                        </div>
                    </div>
                </form>
                @endcan
            </div>
        </div>
    </div>

    <div class="col-sm-12 col-md-12 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom">
                <h4 class="m-0 h5"><i class="mdi mdi-table me-1"></i> Managed Collections</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>SL</th>
                                <th>Collection Images & Video</th> 
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $key=> $item)
                            <tr>
                                <td>{{ $key+1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center flex-wrap gap-1">
                                        @if($item->left_image_1) <img src="{{ asset('homeimages/'.$item->left_image_1) }}" class="feat-img-thumb" title="Img 1"> @endif
                                        @if($item->left_image_2) <img src="{{ asset('homeimages/'.$item->left_image_2) }}" class="feat-img-thumb" title="Img 2"> @endif
                                        @if($item->left_image_3) <img src="{{ asset('homeimages/'.$item->left_image_3) }}" class="feat-img-thumb" title="Img 3"> @endif
                                        @if($item->left_image_4) <img src="{{ asset('homeimages/'.$item->left_image_4) }}" class="feat-img-thumb" title="Img 4"> @endif
                                        @if($item->right_image)  <img src="{{ asset('homeimages/'.$item->right_image) }}"  class="feat-img-thumb border-primary" title="Large Img"> @endif
                                        
                                        @if($item->banner_video) 
                                            <span class="badge bg-success mt-1 ms-1 p-1"><i class="mdi mdi-video"></i> Video</span> 
                                        @endif
                                    </div>
                                </td>
                                <td class="text-end">
                                    @can('image.edit')
                                        <a href="{{ route('admin.home_section_images.edit',[$item->id])}}" class="btn btn-sm btn-outline-primary btn_modal rounded-pill px-2"> 
                                            <i class="mdi mdi-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('image.delete')
                                        <a href="{{ route('admin.home_section_images.destroy',[$item->id])}}" class="btn btn-sm btn-outline-danger delete rounded-pill px-2 ms-1"> 
                                            <i class="mdi mdi-delete"></i>
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection