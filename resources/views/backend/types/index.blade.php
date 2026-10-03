@extends('backend.app')
@section('content')

@push('css')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --bg-body: #F4F7FE;
        --card-bg: #FFFFFF;
        --primary-color: #4318FF;
        --primary-hover: #3311CC;
        --text-main: #2B3674;
        --text-sub: #A3AED0;
        --border-color: #E0E5F2;
        --shadow-soft: 0px 18px 40px rgba(112, 144, 176, 0.12);
        --radius-xl: 20px;
        --radius-lg: 14px;
        --transition: all 0.3s ease;
    }

    body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg-body); color: var(--text-main); }

    /* Page Header */
    .page-title-box { margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; }
    .page-title { font-size: 1.6rem; font-weight: 700; color: var(--text-main); letter-spacing: -0.02em; }
    .breadcrumb-item a { color: var(--text-sub); text-decoration: none; font-weight: 500; }
    .breadcrumb-item.active { color: var(--primary-color); font-weight: 600; }

    /* Premium Cards */
    .card {
        border: none; border-radius: var(--radius-xl);
        background: var(--card-bg);
        box-shadow: var(--shadow-soft);
        margin-bottom: 24px; transition: var(--transition);
        overflow: hidden;
    }
    .card:hover { box-shadow: 0px 20px 50px rgba(112, 144, 176, 0.2); }

    .card-header {
        background: #fff; border-bottom: 1px solid #F4F7FE;
        padding: 24px; font-weight: 700; font-size: 1.1rem; color: var(--text-main);
        display: flex; align-items: center;
    }
    .card-body { padding: 24px; }

    /* Modern Form Elements */
    .form-label { font-weight: 600; color: var(--text-main); font-size: 0.9rem; margin-bottom: 8px; }
    .form-control {
        border-radius: var(--radius-lg); border: 1px solid var(--border-color);
        padding: 12px 16px; font-size: 0.95rem; font-weight: 500; color: var(--text-main);
        background: #fff; transition: var(--transition);
    }
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(67, 24, 255, 0.1);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #868CFF 0%, #4318FF 100%);
        border: none; border-radius: var(--radius-lg); padding: 12px 24px;
        font-weight: 600; box-shadow: 0 4px 12px rgba(67, 24, 255, 0.2);
        transition: var(--transition); width: 100%;
    }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 16px rgba(67, 24, 255, 0.3); }

    /* Premium Table Styling */
    .table-responsive { 
        border-radius: var(--radius-lg); 
        overflow-x: auto; 
        -webkit-overflow-scrolling: touch;
    }
    .table { margin-bottom: 0; vertical-align: middle; white-space: nowrap; border-collapse: separate; border-spacing: 0; }
    
    .table thead th {
        background: #F9FAFC; color: var(--text-sub); font-weight: 700; text-transform: uppercase;
        font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 1px solid #EFF2F7; padding: 18px 24px;
    }
    
    .table tbody td {
        padding: 20px 24px; border-bottom: 1px solid #F4F7FE;
        font-weight: 600; color: var(--text-main); font-size: 0.95rem;
    }
    .table tbody tr:last-child td { border-bottom: none; }
    .table tbody tr:hover td { background-color: #FAFCFE; }

    /* Images & Badges */
    .brand-img {
        width: 48px; height: 48px; object-fit: contain; border-radius: 10px;
        border: 1px solid #F4F7FE; padding: 4px; background: #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }
    
    .badge-status {
        padding: 6px 14px; border-radius: 30px; font-size: 0.75rem; font-weight: 700;
        text-transform: capitalize; display: inline-flex; align-items: center; gap: 5px;
    }
    .badge-yes { background: rgba(5, 205, 153, 0.1); color: #05CD99; }
    .badge-no { background: rgba(255, 181, 71, 0.1); color: #FFB547; }

    /* Action Buttons */
    .action-icon {
        width: 34px; height: 34px; border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        margin-left: 6px; transition: var(--transition); font-size: 1.1rem;
    }
    .btn-edit { background: #F4F7FE; color: var(--primary-color); }
    .btn-edit:hover { background: var(--primary-color); color: #fff; transform: translateY(-2px); }
    .btn-delete { background: #FFF5F5; color: #EE5D50; }
    .btn-delete:hover { background: #EE5D50; color: #fff; transform: translateY(-2px); }

    /* Top Action Bar */
    .top-action-bar {
        background: #fff; padding: 16px; border-radius: var(--radius-lg);
        display: flex; flex-wrap: wrap; gap: 16px; align-items: center;
        justify-content: space-between; border: 1px solid var(--border-color);
        margin-bottom: 20px; box-shadow: var(--shadow-soft);
    }
    .btn-action-group { display: flex; gap: 10px; }
    .btn-action-group .btn {
        padding: 10px 18px; border-radius: 10px; font-size: 0.85rem; font-weight: 600; border: none;
        display: inline-flex; align-items: center; transition: var(--transition);
    }
    .btn-active { background: #E6FBF5; color: #05CD99; }
    .btn-active:hover { background: #05CD99; color: #fff; transform: translateY(-2px); }
    .btn-deactive { background: #FFF5F5; color: #EE5D50; }
    .btn-deactive:hover { background: #EE5D50; color: #fff; transform: translateY(-2px); }

    /* Search Input Fixed */
    .search-box { position: relative; width: 100%; max-width: 320px; }
    .search-box input { 
        padding-right: 50px; border-radius: 50px; width: 100%; 
        border: 1px solid #E0E5F2; height: 46px; padding-left: 20px;
    }
    .search-box input:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(67, 24, 255, 0.1); }
    .search-box button {
        position: absolute; right: 5px; top: 5px; bottom: 5px;
        width: 36px; border-radius: 50%; background: var(--primary-color); 
        color: #fff; border: none; display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: var(--transition);
    }
    .search-box button:hover { background: var(--primary-hover); transform: scale(1.05); }

    /* Mobile Responsive */
    @media (max-width: 991px) {
        .page-title-box { flex-direction: column; align-items: flex-start; gap: 10px; }
        .top-action-bar { flex-direction: column; align-items: stretch; gap: 15px; }
        .btn-action-group { width: 100%; display: flex; }
        .btn-action-group .btn { flex: 1; justify-content: center; }
        .search-box { max-width: 100%; }
        .card-body { padding: 16px; }
    }
</style>
@endpush

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Brand Manager</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active">Brand Manage</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="mdi mdi-plus-circle me-2 text-primary" style="font-size: 1.2rem;"></i> Create New Brand
            </div>
            <div class="card-body">
                @can('type.create')
                <form method="POST" action="{{ route('admin.types.store')}}" id="ajax_form" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label">Brand Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Ex: Apple, Samsung...">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Brand Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="text-sub d-block mt-2" style="font-size: 0.8rem;">
                            <i class="mdi mdi-information-outline"></i> Recommended: 200x200 px
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-check-circle-outline me-1"></i> Save Brand
                    </button>
                </form>
                @endcan
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                
                <div class="top-action-bar">
                    <div class="btn-action-group">
                        <a href="{{ route('admin.topBrandUpdate')}}?is_top=1" class="btn btn-active top_update">
                            <i class="mdi mdi-star me-1"></i> Set Top
                        </a>
                        <a href="{{ route('admin.topBrandUpdate')}}?is_top=0" class="btn btn-deactive top_update">
                            <i class="mdi mdi-star-off me-1"></i> Unset Top
                        </a>
                    </div>

                    <form class="d-flex align-items-center m-0 w-100-mobile" action="{{ url()->current() }}" method="GET" style="flex-grow: 1; justify-content: flex-end;">
                        <div class="search-box">
                            <input type="search" class="form-control" name="q" value="{{ request('q') }}" placeholder="Search brands..." autocomplete="off">
                            <button type="submit" title="Search"><i class="mdi mdi-magnify"></i></button>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-centered">
                        <thead>
                            <tr>
                                <th style="width: 50px;">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input check_all" id="checkAll">
                                        <label class="form-check-label" for="checkAll"></label>
                                    </div>
                                </th>
                                <th>Brand Name</th> <th>Logo</th>
                                <th>Status</th>
                                <th class="text-end">Manage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $key=> $item)
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input checkbox" value="{{ $item->id}}">
                                    </div>
                                </td>
                                <td>
                                    <h5 class="m-0 text-dark" style="font-weight: 700;">{{ $item->name }}</h5>
                                </td>
                                <td>
                                    <img src="{{ getImage('types', $item->image)}}" class="brand-img" alt="brand">
                                </td>
                                <td>
                                    @if($item->is_top == '1')
                                        <span class="badge-status badge-yes">
                                            <i class="mdi mdi-check-circle"></i> Top Brand
                                        </span>
                                    @else
                                        <span class="badge-status badge-no">
                                            <i class="mdi mdi-circle-outline"></i> Standard
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @can('type.edit')
                                        <a href="{{ route('admin.types.edit',[$item->id])}}" class="action-icon btn-edit btn_modal" title="Edit">
                                            <i class="mdi mdi-pencil-outline"></i>
                                        </a>
                                    @endcan
                                    @can('type.delete')
                                        <a href="{{ route('admin.types.destroy',[$item->id])}}" class="action-icon btn-delete delete" title="Delete">
                                            <i class="mdi mdi-trash-can-outline"></i>
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($items->isEmpty())
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="mdi mdi-cube-outline text-sub" style="font-size: 4rem; opacity: 0.5;"></i>
                        </div>
                        <h5 class="text-sub">No brands found</h5>
                        @if(request('q'))
                            <p class="text-muted">Try clearing your search query.</p>
                            <a href="{{ url()->current() }}" class="btn btn-sm btn-light mt-2">Clear Search</a>
                        @endif
                    </div>
                @endif

            </div> 
        </div>
    </div> 
</div> 
@endsection 

@push('js')
<script>
$(document).ready(function(){
    
    // Check All Functionality
    $(".check_all").on('change',function(){
      $(".checkbox").prop('checked',$(this).is(":checked"));
    });
    
    // Top Brand Update AJAX
    $(document).on('click', 'a.top_update', function(e){
        e.preventDefault();
        var url = $(this).attr('href');
    
        var product = $('input.checkbox:checked').map(function(){
          return $(this).val();
        });
        var brand_ids = product.get();
        
        if(brand_ids.length == 0){
            toastr.error('Select at least one brand first!');
            return;
        }
        
        $.ajax({
           type:'GET',
           url:url,
           data:{brand_ids},
           success:function(res){
               if(res.status==true){
                toastr.success(res.msg);
                setTimeout(() => window.location.reload(), 1000);
            }else if(res.status==false){
                toastr.error(res.msg);
            }
           }
        });
    });
});
</script>
@endpush