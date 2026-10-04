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

  th, td, h4, .cr_manage, .form-label {
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
  .btn-primary:focus,
  .btn-primary:hover{
    box-shadow: 0 10px 20px rgba(37,99,235,.25);
  }

  .search-wrapper{
    display:flex;
    justify-content:flex-end;
    gap:.5rem;
    align-items:center;
  }
  @media (max-width: 767.98px){
    .search-wrapper{
      justify-content:stretch;
      margin-top:.75rem;
    }
  }

  .search-input{
    max-width:220px;
  }
  @media (max-width: 767.98px){
    .search-input{
      max-width:100%;
    }
  }

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

  .badge-serial{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width: 36px;
    padding:.12rem .55rem;
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
    background:#fee2e2;
    color:#b91c1c;
    transition:.15s;
    margin: 0 2px;
  }
  .action-icon i{
    font-size:16px;
  }
  .action-icon:hover{
    background:#fecaca;
    color:#7f1d1d;
  }
  .action-icon.edit-icon {
    background: #e0f2fe;
    color: #0284c7;
  }
  .action-icon.edit-icon:hover {
    background: #bae6fd;
    color: #0369a1;
  }

  .table-container{
    border-radius: 16px;
    border:1px solid rgba(148,163,184,.25);
    overflow:hidden;
    background:#f9fafb;
  }

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

  .homecat-pagination p{
    margin-top: .85rem;
    margin-bottom: 0;
  }
  .homecat-pagination .pagination{
    margin:0;
  }
  .homecat-pagination .pagination li a,
  .homecat-pagination .pagination li span{
    border-radius:999px !important;
    margin:0 2px;
    font-size:.8rem;
  }
</style>

<div class="row">
  <div class="col-12">
    <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
      <div>
        <h4 class="page-title mb-1">Home Category Manage</h4>
        <ol class="breadcrumb m-0">
          <li class="breadcrumb-item"><a href="javascript:void(0);">SIS</a></li>
          <li class="breadcrumb-item"><a href="javascript:void(0);">CRM</a></li>
          <li class="breadcrumb-item active cr_manage">Home Category Manage</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="p-1 col-lg-4 col-md-12 col-sm-12 mb-3 mb-lg-0">
    <div class="card card-modern">
      <div class="card-header">
        <h4>Home Category Create</h4>
      </div>
      <div class="card-body">
        @can('category.create')
          <form method="POST" action="{{ route('admin.store-homecat')}}" id="ajax_form" enctype="multipart/form-data">
            @csrf
            <div class="row">
              <div class="col-lg-12">

                <div class="mb-3">
                  <label class="form-label">Add Home Category</label>
                  <select class="form-control select2" name="category_id">
                    <option value="" hidden>Select Category ..</option>
                    @foreach($all_categories as $key=>$cat)
                      <option value="{{ $cat->id }}">{{ $cat->name}}</option>
                    @endforeach
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label">Serial</label>
                  <input type="text" name="serial" class="form-control" placeholder="Serial">
                </div>

                <div class="mb-3">
                  <label class="form-label">Cover Image (Optional)</label>
                  <input type="file" name="cover_image" class="form-control" accept="image/*">
                </div>

              </div>

              <div class="col-lg-12">
                <div class="mb-1 d-flex justify-content-end">
                  <button type="submit" class="btn btn-primary">
                    Save
                  </button>
                </div>
              </div>
            </div>
          </form>
        @endcan
      </div>
    </div>
  </div>

  <div class="p-1 col-md-12 col-sm-12 col-lg-8">
    <div class="card card-modern">
      <div class="card-body">
        <div class="col-lg-12">
          <div class="row align-items-center">
            <div class="col-12 col-md-6 mb-2 mb-md-0">
              <h5 class="mb-0" style="font-weight:600; color:#0f172a;">Home Category List</h5>
              <small class="text-muted">Manage categories shown on home page</small>
            </div>

            <div class="col-12 col-md-6">
              <form class="row gy-2 gx-2 align-items-center justify-content-xl-start justify-content-between search-wrapper">
                <div class="col-auto flex-grow-1">
                  <label for="inputPassword2" class="visually-hidden">Search</label>
                  <input type="search"
                         class="form-control search-input"
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
                         value="Search">
                </div>
              </form>
            </div>
          </div>
        </div>

        <div class="col-lg-12 mt-3">
          <div class="table-container">
            <div class="table-responsive">
              <table class="table table-centered table-modern mb-0">
                <thead>
                  <tr>
                    <th>Category Name</th>
                    <th>Cover Image</th>
                    <th>Serial</th>
                    <th>Show on Home</th>
                    <th style="width: 110px;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($home_categories as $key=> $item)
                    <tr>
                      <td data-label="Category Name">
                        {{ $item->category != null ? $item->category->name  : '' }}
                      </td>
                      <td data-label="Cover Image">
                        @if($item->cover_image)
                          <img src="{{ asset('uploads/home_categories/cover/'.$item->cover_image) }}" alt="Cover" width="60" class="rounded shadow-sm">
                        @else
                          <span class="text-muted small">No Image</span>
                        @endif
                      </td>
                      <td data-label="Serial">
                        <span class="badge-serial">{{$item->serial}}</span>
                      </td>
                      <td data-label="Show on Home">
                        <div class="form-check form-switch mb-0">
                          <input type="checkbox" class="form-check-input homecat-status" data-url="{{ route('admin.homecat_status', [$item->id]) }}" {{ $item->status ? 'checked' : '' }} @cannot('category.edit') disabled @endcannot>
                        </div>
                      </td>
                      <td data-label="Action" class="text-md-center">
                        <a href="javascript:void(0)" class="action-icon edit-icon" data-bs-toggle="modal" data-bs-target="#editModal{{$item->id}}" title="Edit">
                          <i class="mdi mdi-square-edit-outline"></i>
                        </a>
                        <a href="{{ route('admin.del_homecat', ['id' => $item->id]) }}"
                           class="action-icon delete"
                           title="Delete">
                          <i class="mdi mdi-delete"></i>
                        </a>
                      </td>
                    </tr>

                    <!-- Edit Modal For Each Item -->
                    <div class="modal fade" id="editModal{{$item->id}}" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Edit Home Category</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <!-- Added class 'ajax_edit_form' to handle submission dynamically -->
                          <form action="{{ route('admin.update_homecat', [$item->id]) }}" method="POST" enctype="multipart/form-data" class="ajax_edit_form">
                            @csrf
                            <div class="modal-body">
                              <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-control" name="category_id">
                                  @foreach($all_categories as $key=>$cat)
                                    <option value="{{ $cat->id }}" {{ $item->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name}}</option>
                                  @endforeach
                                </select>
                              </div>
                              <div class="mb-3">
                                <label class="form-label">Serial</label>
                                <input type="text" name="serial" class="form-control" value="{{ $item->serial }}">
                              </div>
                              <div class="mb-3">
                                <label class="form-label">Cover Image (Optional)</label>
                                <input type="file" class="form-control" name="cover_image" accept="image/*">
                                @if($item->cover_image)
                                    <div class="mt-3 text-center">
                                        <img src="{{ asset('uploads/home_categories/cover/'.$item->cover_image) }}" width="150" class="rounded shadow-sm">
                                        <br>
                                        <a href="{{ route('admin.remove_homecat_cover', $item->id) }}" class="btn btn-sm btn-danger mt-3 delete">
                                            <i class="mdi mdi-delete"></i> Remove Image
                                        </a>
                                    </div>
                                @endif
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                              <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  @endforeach

                  @if($home_categories->count() === 0)
                    <tr>
                      <td colspan="5" class="text-center py-3 text-muted">
                        No home category found.
                      </td>
                    </tr>
                  @endif
                </tbody>
              </table>
            </div>
          </div>

          <div class="homecat-pagination">
            <p>{!! urldecode(str_replace("/?","?",$home_categories->appends(Request::all())->render())) !!}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('js')
<script>
  $(document).ready(function(){

    $(".check_all").on('change',function(){
      $(".checkbox").prop('checked',$(this).is(":checked"));
    });

    // Show / hide a category on the home page
    $(document).on('change', '.homecat-status', function(){
      var box = $(this);
      $.ajax({
        type: 'POST',
        url: box.data('url'),
        data: { _token: '{{ csrf_token() }}', status: box.is(':checked') ? 1 : 0 },
        success: function(res){
          toastr.success(res.msg);
        },
        error: function(){
          box.prop('checked', !box.is(':checked'));
          toastr.error('Something went wrong!');
        }
      });
    });

    // AJAX form submission for the Edit Modal
    $(document).on('submit', '.ajax_edit_form', function(e){
      e.preventDefault();
      var form = $(this);
      var url = form.attr('action');
      var formData = new FormData(this);

      $.ajax({
        type: 'POST',
        url: url,
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: function(res){
          if(res.status == true){
            toastr.success(res.msg);
            setTimeout(()=>location.reload(), 1000);
          } else {
            toastr.error(res.msg);
          }
        },
        error: function(err){
          toastr.error('Something went wrong!');
        }
      });
    });

    $(document).on('click', 'a.popular_update', function(e){
      e.preventDefault();
      var url = $(this).attr('href');

      var product = $('input.checkbox:checked').map(function(){
        return $(this).val();
      });
      var cat_ids = product.get();

      if(cat_ids.length == 0){
        toastr.error('Please Select A Product First !');
        return;
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