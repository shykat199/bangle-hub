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

    th, td, h4, .pg_manage, .form-label {
        color: var(--text) !important;
    }

    .page-title-box h4{
        font-weight:700;
    }

    /* Modern card */
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

    .btn-add{
        border:none;
        border-radius:999px;
        padding:.45rem 1.2rem;
        font-weight:600;
        font-size:.85rem;
        background:linear-gradient(to right,#f97316,#ea580c);
        display:inline-flex;
        align-items:center;
        gap:.35rem;
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

    .badge-sl{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-width:32px;
        padding:.12rem .6rem;
        border-radius:999px;
        background:var(--primary-soft);
        color:#0369a1;
        font-weight:600;
        font-size:.8rem;
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
        transition:.15s;
    }
    .action-icon i{
        font-size:16px;
    }
    .action-icon:hover{
        background:#dbeafe;
    }

    /* Mobile: table → card */
    @media(max-width:768px){
        .table-responsive{
            border:0;
        }
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
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active pg_manage">Page Manage</li>
                </ol>
                <h4 class="page-title mt-1">Page Manage</h4>
            </div>
            <div class="mt-2 mt-md-0">
                @can('product.create')
                <a href="{{ route('admin.pages.create')}}"
                   class="btn btn-add mb-2 me-2">
                    <i class="mdi mdi-plus"></i>
                    Add New Page
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>   
<!-- end page title --> 

<div class="row">
    <div class="col-12">
        <div class="card card-modern">
            <div class="card-header">
                <h4>All Pages</h4>
            </div>
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-centered table-modern mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>SL</th>
                                <th>Page Name</th>
                                <th>Page Title</th>
                                <th style="width: 110px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $key=> $item)
                            <tr>
                                <td data-label="SL">
                                    <span class="badge-sl">{{ $key+1 }}</span>
                                </td>
                                <td data-label="Page Name">
                                    {{ $item->page }}
                                </td>
                                <td data-label="Page Title">
                                    {{ $item->title }}
                                </td>
                                <td data-label="Action">
                                    <a href="{{ route('admin.pages.edit',[$item->id])}}"
                                       class="action-icon"
                                       title="Edit">
                                        <i class="mdi mdi-square-edit-outline"></i>
                                    </a>
                                    {{-- Delete commented in original, তাই রাখলাম --}}
                                    {{-- 
                                    <a href="{{ route('admin.pages.destroy',[$item->id])}}"
                                       class="delete action-icon ms-1"
                                       title="Delete">
                                        <i class="mdi mdi-delete"></i>
                                    </a> 
                                    --}}
                                </td>
                            </tr>
                            @endforeach

                            @if($items->count() === 0)
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        No pages found.
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
