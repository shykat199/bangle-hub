@extends('backend.app')
@section('content')

<style>
  :root{
    --bg:#f3f4f6;
    --card:#ffffff;
    --primary:#0ea5e9;
    --primary-soft:rgba(14,165,233,.12);
    --border:#e5e7eb;
    --text:#111827;
    --muted:#6b7280;
    --shadow-soft:0 10px 30px rgba(15,23,42,.08);
  }

  body{
    background:var(--bg);
  }

  th, td, h4, .cr_manage, .form-label {
    color: var(--text) !important;
  }

  .page-title-box{
    margin-bottom:.75rem;
  }
  .page-title-box h4.page-title{
    font-weight:700;
    letter-spacing:.3px;
  }
  .breadcrumb{
    background:transparent;
    padding:0;
    margin-bottom:0;
  }

  /* Modern card */
  .card-modern{
    border:0;
    border-radius:18px;
    background:var(--card);
    box-shadow:var(--shadow-soft);
    overflow:hidden;
  }
  .card-modern .card-header{
    border-bottom:1px solid rgba(15,23,42,.06);
    background:linear-gradient(135deg,#eff6ff,#ecfeff);
    padding:.85rem 1.25rem;
  }
  .card-modern .card-header h4{
    margin:0;
    font-size:1rem;
    font-weight:700;
    color:#0f172a;
  }
  .card-modern .card-body{
    padding:1rem 1.25rem 1.15rem;
  }

  /* Form */
  .form-label{
    font-size:.85rem;
    font-weight:600;
    color:var(--muted) !important;
    text-transform:uppercase;
    letter-spacing:.05em;
    margin-bottom:.25rem;
  }
  .form-control{
    border-radius:.75rem;
    border-color:var(--border);
    font-size:.9rem;
  }
  .form-control:focus{
    border-color:var(--primary);
    box-shadow:0 0 0 .15rem var(--primary-soft);
  }
  .btn-primary{
    border-radius:.75rem;
    padding:.45rem 1.2rem;
    font-weight:600;
    font-size:.9rem;
    border:none;
    background:linear-gradient(135deg,#0ea5e9,#2563eb);
  }
  .btn-primary:hover,
  .btn-primary:focus{
    box-shadow:0 10px 20px rgba(37,99,235,.25);
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
    vertical-align:middle;
  }
  .table-modern tbody td{
    font-size:.9rem;
    border-top:1px solid #f1f5f9;
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

  .table-container{
    border-radius:16px;
    border:1px solid rgba(148,163,184,.25);
    overflow:hidden;
    background:#f9fafb;
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

  @media (max-width: 991.98px){
    .p-1.col-lg-4,
    .p-1.col-lg-8{
      padding-left:.4rem !important;
      padding-right:.4rem !important;
    }
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
                <h4 class="page-title mb-1">Expense Manage</h4>
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active cr_manage">Expense Manage</li>
                </ol>
            </div>
        </div>
    </div>
</div>   
<!-- end page title --> 

<div class="row">
    {{-- Left: Create --}}
    <div class="p-1 col-lg-4 col-md-12 col-sm-12">
        @can('category.create')
        <div class="card card-modern">
            <div class="card-header">
                <h4>Expense Create</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.expenses.store')}}" id="ajax_form">
                    @csrf
                    <div class="row g-3">
                        <div class="col-lg-12">
                            <div class="mb-2">
                                <label class="form-label">Expense Title</label>
                                <input type="text" name="title" class="form-control" placeholder="Expense Title">
                            </div>
                            
                            <div class="mb-2">
                                <label class="form-label">Expense Amount</label>
                                <input type="text" name="amount" class="form-control" placeholder="Expense Amount">
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Expense Date</label>
                                <input type="date" name="date" class="form-control">
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="mb-1 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div> <!-- end card-body-->
        </div> <!-- end card-->
        @endcan
    </div>

    {{-- Right: List --}}
    <div class="p-1 col-md-12 col-sm-12 col-lg-8">
        <div class="card card-modern">
            <div class="card-body">
                <div class="col-lg-12">
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            {{-- Hidden popular button block as আগের মতই রাখা হয়েছে --}}
                            <div class="col-auto d-none">
                                <a class="btn btn-sm btn-info popular_update"
                                   href="{{ route('admin.popularCatgeory')}}?is_popular=1">
                                    Active Popular
                                </a>
                                <a class="btn btn-sm btn-danger popular_update"
                                   href="{{ route('admin.popularCatgeory')}}?is_popular=0">
                                    De-active Popular
                                </a>
                            </div>
                        </div>
                      
                        <div class="col-lg-6 mt-2 mt-lg-0">
                            <form class="row gy-2 gx-2 align-items-center justify-content-xl-start justify-content-between">
                                <div class="col-auto">
                                    <label for="inputPassword2" class="visually-hidden">Search</label>
                                    <input type="search"
                                           class="form-control"
                                           id="inputPassword2"
                                           placeholder="Search..."
                                           name="q"
                                           value="{{ $q??''}}">
                                </div>
                                
                                <div class="col-auto">
                                    <label for="submit" class="visually-hidden">Submit</label>
                                    <input type="submit"
                                           class="form-control btn btn-sm btn-primary"
                                           id="submit"
                                           value="Submit">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-12 mt-4">
                    <div class="table-container">
                        <div class="table-responsive">
                            <table class="table table-centered table-modern mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SL</th>
                                        <th>Expense Title</th>
                                        <th>Expense Amount</th>
                                        <th>Expense Date</th>
                                        <th style="width: 125px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $key=> $item)
                                    <tr>
                                        <td data-label="SL">
                                            <span class="badge-sl">{{ $key+1 }}</span>
                                        </td>
                                        <td data-label="Expense Title">
                                            {{ $item->title }}
                                        </td>
                                        <td data-label="Expense Amount">
                                            {{ $item->amount }}
                                        </td>
                                        <td data-label="Expense Date">
                                            {{ $item->date }}
                                        </td>
                                        <td data-label="Action">
                                            @can('category.edit')
                                            <a href="{{ route('admin.expenses.edit',[$item->id])}}"
                                               class="action-icon btn_modal"
                                               title="Edit">
                                                <i class="mdi mdi-square-edit-outline"></i>
                                            </a>
                                            @endcan
                                            @can('category.delete')
                                            <a href="{{ route('admin.expenses.destroy',[$item->id])}}"
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
                                                No expenses found.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <p class="mt-2 mb-0">
                        {!! urldecode(str_replace("/?","?",$items->appends(Request::all())->render())) !!}
                    </p>
                </div>
            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div> <!-- end col -->
</div> <!-- end row -->
@endsection 

@push('js')
<script>
$(document).ready(function(){
    $(".check_all").on('change',function(){
      $(".checkbox").prop('checked',$(this).is(":checked"));
    });
    
    $(document).on('click', 'a.popular_update', function(e){
        e.preventDefault();
        var url = $(this).attr('href');
    
        var product = $('input.checkbox:checked').map(function(){
          return $(this).val();
        });
        var cat_ids=product.get();
        
        if(cat_ids.length ==0){
            toastr.error('Please Select A Product First !');
            return ;
        }
        
        $.ajax({
           type:'GET',
           url:url,
           data:{cat_ids},
           success:function(res){
               if(res.status==true){
                    toastr.success(res.msg);
                    window.location.reload();
               }else if(res.status==false){
                    toastr.error(res.msg);
               }
           }
        });
    });
});
</script>
@endpush
