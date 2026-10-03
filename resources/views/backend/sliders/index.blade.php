@extends('backend.app')
@section('content')

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
        padding:1rem 1.2rem 1.25rem;
    }

    .form-label{
        font-size:.85rem;
        color:var(--muted);
        font-weight:600;
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
        padding:.45rem 1.3rem;
        font-weight:600;
        font-size:.9rem;
        background:linear-gradient(to right,#0ea5e9,#2563eb);
    }

    .slider-thumb{
        width:120px;
        max-height:70px;
        object-fit:cover;
        border-radius:10px;
        box-shadow:0 4px 10px rgba(15,23,42,.18);
        border:1px solid #e5e7eb;
    }

    /* Modern table */
    .table-modern thead{
        background:#f8fafc;
    }
    .table-modern thead th{
        font-size:.8rem;
        color:var(--muted);
        text-transform:uppercase;
        letter-spacing:.05em;
        border-bottom:1px solid var(--border);
    }
    .table-modern tbody td{
        border-top:1px solid #f1f5f9;
        font-size:.9rem;
        vertical-align:middle;
    }

    .action-icon{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        width:30px;
        height:30px;
        border-radius:999px;
        background:#eff6ff;
        color:#1d4ed8;
    }
    .action-icon i{
        font-size:16px;
    }
    .action-icon.delete{
        background:#fee2e2;
        color:#b91c1c;
    }

    /* Mobile view – table → card */
    @media(max-width:768px){
        .table-modern thead{ display:none; }
        .table-modern tbody tr{
            display:block;
            margin-bottom:12px;
            background:#fff;
            border-radius:14px;
            padding:10px;
            box-shadow:0 4px 10px rgba(0,0,0,.06);
        }
        .table-modern tbody td{
            display:grid;
            grid-template-columns:130px 1fr;
            border:none !important;
            padding:6px 4px !important;
        }
        .table-modern tbody td::before{
            content:attr(data-label);
            font-size:.75rem;
            color:var(--muted);
            text-transform:uppercase;
            font-weight:600;
        }
        .slider-thumb{
            width:100%;
            max-width:100%;
        }
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active">Slider Manage</li>
                </ol>
            </div>
            <h4 class="page-title">Slider Manage</h4>
        </div>
    </div>
</div>   
<!-- end page title --> 

<div class="row">
    {{-- Left: Create Slider --}}
    <div class="p-1 m-0 col-sm-12 col-md-12 col-lg-4">
        @can('slider.create')
        <div class="card card-modern">
            <div class="card-header">
                <h4>Slider Create</h4>
            </div>
            <div class="card-body">
                <form method="POST"
                      action="{{ route('admin.sliders.store')}}"
                      id="ajax_form"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="row g-2">
                        <div class="col-lg-12">
                            <div class="mb-2">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" placeholder="Title">
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Description</label>
                                <input type="text" name="description" class="form-control" placeholder="Description">
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Desktop Image</label>
                                <input type="file" name="image" class="form-control">
                                <small class="text-muted" style="font-size:.75rem;">Recommended: wide banner size 1500*500</small>
                            </div>
                            
                            <div class="mb-2">
                                <label class="form-label">Mobile Image</label>
                                <input type="file" name="mobile_image" class="form-control">
                                <small class="text-muted" style="font-size:.75rem;">Recommended: wide banner size 1000*500 mobile size</small>
                            </div>
                            
                            <div class="mb-2">
                                <label class="form-label">Link</label>
                                <input type="text" name="link" class="form-control" placeholder="https://example.com/product">
                            </div>
                        </div>

                        <div class="col-lg-12 mt-1">
                            <button type="submit" class="btn btn-primary w-100">Save</button>
                        </div>
                    </div>
                </form>
            </div> <!-- end card-body-->
        </div> <!-- end card-->
        @endcan
    </div>

    {{-- Right: Slider List --}}
    <div class="p-1 m-0 col-sm-12 col-md-12 col-lg-8">
        <div class="card card-modern p-0 m-0">
            <div class="card-header">
                <h4>Slider List</h4>
            </div>
            <div class="card-body">
  
                <div class="table-responsive">
                    <table class="table table-centered table-modern mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Desktop Image</th>
                                <th>Mobile Image</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th style="width: 110px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $key=> $item)
                            <tr>
                                <td data-label="Desktop Image">
                                    @if($item->image)
                                        <img src="{{ getImage('sliders', $item->image)}}" class="slider-thumb" alt="Desktop Slider">
                                    @endif
                                </td>
                                
                                <td data-label="Mobile Image">
                                    @if($item->mobile_image)
                                        <img src="{{ getImage('mobile_sliders', $item->mobile_image)}}" class="slider-thumb" alt="Mobile Slider">
                                    @endif
                                </td>
                                
                                <td data-label="Title">
                                    {{ $item->title }}
                                </td>

                                <td data-label="Description">
                                    {{ $item->description }}
                                </td>

                                <td data-label="Action">
                                    @can('slider.edit')
                                        <a href="{{ route('admin.sliders.edit',[$item->id])}}"
                                           class="action-icon btn_modal me-1"
                                           title="Edit">
                                            <i class="mdi mdi-square-edit-outline"></i>
                                        </a>
                                    @endcan
                                    @can('slider.delete')
                                        <a href="{{ route('admin.sliders.destroy',[$item->id])}}"
                                           class="delete action-icon"
                                           title="Delete">
                                            <i class="mdi mdi-delete"></i>
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                            
                            @if($items->count() === 0)
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        No sliders found.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div> <!-- end col -->
</div> <!-- end row -->
@endsection
