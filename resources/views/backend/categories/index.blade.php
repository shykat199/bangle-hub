@extends('backend.app')
@section('content')

<style>
  th, td, h4, .cr_manage, .form-label {
    color: #111827 !important;
  }

  .card {
    border: none !important;
    border-radius: 12px !important;
    box-shadow: 0 2px 12px rgba(0,0,0,.08);
  }

  .page-title-box {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
  }

  .form-control, select, input[type="file"] {
    border-radius: 8px !important;
  }

  .table thead {
    background-color: #f1f5f9;
  }

  @media(max-width:768px){
    .card-header h4{
      font-size: 1.1rem;
    }
    .table thead{
      font-size: 14px;
    }
    .table td{
      font-size: 13px;
    }
  }
</style>

<div class="row gy-4">
  <div class="col-12">
    <div class="page-title-box mb-3">
      <div>
        <h4 class="page-title mb-0 fw-bold">Category Manage</h4>
        <small class="text-muted">Manage all product categories easily</small>
      </div>
      <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);">SIS</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);">CRM</a></li>
        <li class="breadcrumb-item active">Category Manage</li>
      </ol>
    </div>
  </div>

  <!-- Category Create -->
  <div class="col-lg-4 col-md-12">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-light">
        <h5 class="mb-0 fw-semibold text-dark">➕ Create Category</h5>
      </div>
      <div class="card-body">
        @can('category.create')
        <form method="POST" action="{{ route('admin.categories.store')}}" id="ajax_form" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <label class="form-label fw-medium">Category Name</label>
            <input type="text" name="name" class="form-control" placeholder="Enter Category Name" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-medium">Category Image</label>
            <input type="file" name="image" class="form-control">
          </div>

          <div class="mb-3">
            <label class="form-label fw-medium">Parent Category</label>
            <select class="form-select" name="parent_id">
              <option value="" hidden>Select Category...</option>
              @foreach($cats as $key=>$cat)
                <option value="{{ $key}}">{{ $cat}}</option>
              @endforeach
            </select>
          </div>

          <button type="submit" class="btn btn-primary w-100 py-2 rounded-3">
            <i class="mdi mdi-content-save-outline"></i> Save Category
          </button>
        </form>
        @endcan
      </div>
    </div>
  </div>

  <!-- Category List -->
  <div class="col-lg-8 col-md-12">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
          <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-sm btn-success popular_update" href="{{ route('admin.popularCatgeory')}}?is_popular=1">Activate Popular</a>
            <a class="btn btn-sm btn-outline-danger popular_update" href="{{ route('admin.popularCatgeory')}}?is_popular=0">Deactivate Popular</a>
            <a class="btn btn-sm btn-info popular_update" href="{{ route('admin.popularCatgeory')}}?is_menu=1">Activate Menu</a>
            <a class="btn btn-sm btn-outline-danger popular_update" href="{{ route('admin.popularCatgeory')}}?is_menu=0">Deactivate Menu</a>
          </div>
          <form class="d-flex gap-2" method="get">
            <input type="search" class="form-control form-control-sm" name="q" placeholder="Search..." value="{{ $q??'' }}">
            <button class="btn btn-primary btn-sm"><i class="mdi mdi-magnify"></i></button>
          </form>
        </div>

        <div class="table-responsive">
          <table class="table table-centered align-middle">
            <thead class="table-light">
              <tr>
                <th>
                  <div class="form-check">
                    <input type="checkbox" class="form-check-input check_all" id="check_all">
                    <label class="form-check-label" for="check_all">All</label>
                  </div>
                </th>
                <th>Name</th>
                <th>Parent</th>
                <th>Image</th>
                <th>Popular</th>
                <th>Menu</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($items as $item)
              <tr>
                <td><input type="checkbox" class="checkbox form-check-input" value="{{ $item->id}}"></td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->parent? $item->parent->name : '-' }}</td>
                <td><img src="{{ getImage('categories',$item->image) }}" width="60" class="rounded shadow-sm"></td>
                <td><span class="badge bg-{{ $item->is_popular ? 'success' : 'secondary' }}">{{ $item->is_popular ? 'Yes' : 'No' }}</span></td>
                <td><span class="badge bg-{{ $item->is_menu ? 'info' : 'secondary' }}">{{ $item->is_menu ? 'Yes' : 'No' }}</span></td>
                <td class="text-center">
                  @can('category.edit')
                    <a href="{{ route('admin.categories.edit',$item->id)}}" class="btn btn-sm btn-outline-primary btn_modal"><i class="mdi mdi-square-edit-outline"></i></a>
                  @endcan
                  @can('category.delete')
                    <a href="{{ route('admin.categories.destroy',$item->id)}}" class="btn btn-sm btn-outline-danger delete"><i class="mdi mdi-delete"></i></a>
                  @endcan
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="mt-3">
          {!! urldecode(str_replace("/?","?",$items->appends(Request::all())->render())) !!}
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('js')
<script>
$(function(){
  $(".check_all").on('change',function(){
    $(".checkbox").prop('checked',$(this).is(":checked"));
  });

  $('a.popular_update').on('click',function(e){
    e.preventDefault();
    const url = $(this).attr('href');
    const cat_ids = $('.checkbox:checked').map(function(){ return $(this).val(); }).get();

    if(cat_ids.length === 0){
      toastr.error('Please select at least one category!');
      return;
    }

    $.ajax({
      type:'GET',
      url:url,
      data:{cat_ids},
      success:function(res){
        if(res.status){
          toastr.success(res.msg);
          setTimeout(()=>location.reload(),1000);
        } else {
          toastr.error(res.msg);
        }
      }
    });
  });
});
</script>
@endpush
