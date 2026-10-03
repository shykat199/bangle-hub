@extends('backend.app')

@push('css')
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

  th, td, h4, .form-label {
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

  /* Header button */
  .btn-add-courier{
    border-radius: .9rem;
    padding: .45rem 1.3rem;
    font-weight: 600;
    border:none;
    background: linear-gradient(135deg,#f97316,#ef4444);
    box-shadow:0 10px 20px rgba(239,68,68,.25);
  }
  .btn-add-courier i{
    font-size:16px;
  }
  .btn-add-courier:hover,
  .btn-add-courier:focus{
    box-shadow:0 10px 24px rgba(239,68,68,.35);
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

  .courier-name{
    font-weight:600;
  }
  .courier-sub{
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

  .table-container{
    border-radius: 16px;
    border:1px solid rgba(148,163,184,.25);
    overflow:hidden;
    background:#f9fafb;
  }

  /* Mobile: table → card */
  @media (max-width: 767.98px){
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
      grid-template-columns: 110px 1fr;
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
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h4 class="page-title mb-1">Courier Manage</h4>
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active">Courier Manage</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card card-modern">
            <div class="card-body">
                <div class="row mb-2 align-items-center">
                    <div class="col-md-8 col-12 mb-2 mb-md-0">
                        <h5 class="mb-0" style="font-weight:600; color:#0f172a;">Courier List</h5>
                        <small class="text-muted">Manage all courier partners</small>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="text-md-end text-start mt-2 mt-md-0">
                            <a href="{{ route('admin.couriers.create')}}"
                               class="btn btn-danger mb-2 me-0 btn_modal btn-add-courier">
                                <i class="mdi mdi-basket me-1"></i> Add Courier
                            </a>
                        </div>
                    </div></div>

                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-centered table-modern table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>SL</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Address</th>
                                    <th style="width: 125px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $key=> $item)
                                <tr>
                                    <td data-label="SL">
                                        <span class="badge-sl">{{ $key+1 }}</span>
                                    </td>
                                    <td data-label="Name">
                                        <div class="courier-name">{{ $item->name }}</div>
                                    </td>
                                    <td data-label="Phone">
                                        {{ $item->phone }}
                                    </td>
                                    <td data-label="Email">
                                        <span class="courier-sub">{{ $item->email }}</span>
                                    </td>
                                    <td data-label="Address">
                                        <span class="courier-sub">{{ $item->address }}</span>
                                    </td>
                                    <td data-label="Action">
                                        @can('size.edit')
                                            <a href="{{ route('admin.couriers.edit',[$item->id])}}"
                                               class="action-icon btn_modal"
                                               title="Edit">
                                                <i class="mdi mdi-square-edit-outline"></i>
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach

                                @if($items->count() === 0)
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">
                                            No courier found.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

            </div> </div> </div> </div> {{-- ✅ MODAL CONTAINER ADDED HERE ✅ --}}
<div class="modal fade" id="common_modal" tabindex="-1" aria-hidden="true"></div>

@endsection

{{-- ✅ JAVASCRIPT FOR MODAL ADDED HERE ✅ --}}
@push('js')
<script>
    $(document).ready(function(){
        $(document).off('click', '.btn_modal').on('click', '.btn_modal', function(e){
            e.preventDefault();
            var url = $(this).attr('href');
            $.ajax({
                url: url,
                type: 'GET',
                success: function(res) {
                    $('#common_modal').html(res).modal('show');
                },
                error: function(err) {
                    if(typeof toastr !== 'undefined') {
                        toastr.error('Failed to load modal data!');
                    } else {
                        alert('Failed to load modal data!');
                    }
                }
            });
        });
    });
</script>
@endpush