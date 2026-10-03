@extends('backend.app')
@section('content')

<style>
  :root{
    --bg:#f3f4f6;
    --card:#ffffff;
    --primary:#0ea5e9;
    --primary-soft:rgba(14,165,233,.08);
    --border:#e5e7eb;
    --text:#111827;
    --muted:#6b7280;
    --shadow-soft:0 10px 30px rgba(15,23,42,.08);
  }

  body{
    background:var(--bg);
  }

  th, td, h4, .cl_manage, .form-label {
    color: var(--text) !important;
  }

  .page-title-box{
    margin-bottom: .75rem;
  }
  .page-title-box h4.page-title{
    font-weight: 700;
    letter-spacing: .3px;
  }

  .breadcrumb{
    background: transparent;
    padding: 0;
    margin-bottom: 0;
  }

  /* Modern card */
  .card-modern{
    border:0;
    border-radius: 18px;
    background: var(--card);
    box-shadow: var(--shadow-soft);
    overflow: hidden;
  }
  .card-modern .card-header{
    border-bottom: 1px solid rgba(15,23,42,.06);
    background: linear-gradient(135deg,#eff6ff,#ecfeff);
    padding: .85rem 1.25rem;
  }
  .card-modern .card-header h4{
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
  }
  .card-modern .card-body{
    padding: 1rem 1.25rem 1.15rem;
  }

  /* Form */
  .form-label{
    font-size: .85rem;
    font-weight: 600;
    color: var(--muted) !important;
    text-transform: uppercase;
    letter-spacing: .05em;
  }
  .form-control{
    border-radius: .75rem;
    border-color: var(--border);
    font-size: .9rem;
  }
  .form-control:focus{
    border-color: var(--primary);
    box-shadow: 0 0 0 .15rem var(--primary-soft);
  }
  .btn-primary{
    border-radius: .75rem;
    padding: .45rem 1.2rem;
    font-weight: 600;
    font-size: .9rem;
    border: none;
    background: linear-gradient(135deg,#0ea5e9,#2563eb);
  }
  .btn-primary:hover,
  .btn-primary:focus{
    box-shadow: 0 10px 20px rgba(37,99,235,.25);
  }

  /* Table modern */
  .table-modern{
    margin-bottom:0;
  }
  .table-modern thead{
    background:#f9fafb;
  }
  .table-modern thead th{
    border-bottom:1px solid var(--border) !important;
    font-size:.8rem;
    text-transform:uppercase;
    letter-spacing:.06em;
    font-weight:600;
    color:var(--muted) !important;
  }
  .table-modern tbody tr{
    vertical-align: middle;
  }
  .table-modern tbody td{
    font-size:.9rem;
    border-top:1px solid #f1f5f9;
  }

  .badge-sl{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width: 32px;
    padding:.12rem .6rem;
    border-radius:999px;
    background:var(--primary-soft);
    color:#0369a1;
    font-weight:600;
    font-size:.8rem;
  }

  .color-pill{
    display:inline-flex;
    align-items:center;
    justify-content:flex-start;
    gap:6px;
  }
  .color-dot{
    width:18px;
    height:18px;
    border-radius:999px;
    border:1px solid #e5e7eb;
    box-shadow:0 0 0 1px rgba(15,23,42,.06);
  }
  .color-code-text{
    font-size:.8rem;
    color:var(--muted);
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
    color:#1d4ed8;
  }
  .action-icon.delete{
    background:#fee2e2;
    color:#b91c1c;
  }
  .action-icon.delete:hover{
    background:#fecaca;
    color:#7f1d1d;
  }

  .table-container{
    border-radius: 16px;
    border:1px solid rgba(148,163,184,.25);
    overflow:hidden;
    background:#f9fafb;
  }

  /* Mobile: table → card */
  @media (max-width: 767.98px){
    .col-form,
    .col-list{
      margin-bottom: 1rem;
    }

    .table-responsive{
      border:0;
    }
    .table-modern thead{
      display:none;
    }
    .table-modern tbody tr{
      display:block;
      margin-bottom:.85rem;
      border-radius:14px;
      border:1px solid #e5e7eb;
      background:#ffffff;
      box-shadow:0 4px 12px rgba(15,23,42,.05);
      padding:.55rem .75rem;
    }
    .table-modern tbody td{
      display:grid;
      grid-template-columns: 120px 1fr;
      gap:4px;
      border-top:0 !important;
      padding:.25rem 0 !important;
    }
    .table-modern tbody td::before{
      content: attr(data-label);
      font-size:.78rem;
      text-transform:uppercase;
      letter-spacing:.05em;
      color:var(--muted);
      font-weight:600;
    }
    .table-modern tbody td:last-child{
      margin-top:.15rem;
    }
    .table-modern tbody td:last-child::before{
      content:"Action";
    }
  }
</style>


<div class="row">
  <div class="col-12">
    <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
      <div>
        <h4 class="page-title mb-1">Color Manage</h4>
        <ol class="breadcrumb m-0">
          <li class="breadcrumb-item"><a href="javascript:void(0);">SIS</a></li>
          <li class="breadcrumb-item"><a href="javascript:void(0);">CRM</a></li>
          <li class="breadcrumb-item active cl_manage">Color Manage</li>
        </ol>
      </div>
    </div>
  </div>
</div>
<!-- end page title --> 

<div class="row">
    {{-- Left: Create --}}
    @can('size.create')
    <div class="col-lg-4 col-md-6 col-12 col-form">            
        <div class="card card-modern">
            <div class="card-header">
                <h4>Color Create</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.colors.store')}}" id="ajax_form">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Color Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Color Name">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Color Code</label>
                        <input type="text" name="code" class="form-control" placeholder="#FFFFFF or rgb(...)">
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            Save
                        </button>
                    </div>
                </form>
            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div>   
    @endcan

    {{-- Right: List --}}
    <div class="col-lg-8 col-md-6 col-12 col-list">
        <div class="card card-modern">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                    <div>
                        <h5 class="mb-0" style="font-weight:600; color:#0f172a;">Color List</h5>
                        <small class="text-muted">Manage all colors for products</small>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-centered table-modern table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>SL</th>
                                    <th>Color Name</th>
                                    <th>Color Code</th>
                                    <th style="width: 130px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $key=> $item)
                                <tr>
                                    <td data-label="SL">
                                        <span class="badge-sl">{{ $key+1 }}</span>
                                    </td>
                                    <td data-label="Color Name">
                                        {{ $item->name }}
                                    </td>
                                    <td data-label="Color Code">
                                        <div class="color-pill">
                                            <span class="color-dot" style="background: {{ $item->code }};"></span>
                                            <span class="color-code-text">{{ $item->code }}</span>
                                        </div>
                                    </td>
                                    <td data-label="Action">
                                        @if($key == 0)
                                            {{-- প্রথম row lock করা থাকল আগের মতোই --}}
                                        @else
                                            @can('size.edit')
                                            <a href="{{ route('admin.colors.edit',[$item->id])}}"
                                               class="action-icon btn_modal"
                                               title="Edit">
                                                <i class="mdi mdi-square-edit-outline"></i>
                                            </a>
                                            @endcan

                                            @can('size.delete')
                                            <a href="{{ route('admin.colors.destroy',[$item->id])}}"
                                               class="delete action-icon"
                                               title="Delete">
                                                <i class="mdi mdi-delete"></i>
                                            </a>
                                            @endcan
                                        @endif
                                    </td>
                                </tr>
                                @endforeach

                                @if($items->count() === 0)
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">
                                            No color found.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div> <!-- end col -->
</div> <!-- end row -->
@endsection  
