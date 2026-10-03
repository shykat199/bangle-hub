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

  body{ background:var(--bg); }

  th, td, h4, .form-label { color: var(--text) !important; }

  .page-title-box{ margin-bottom: .75rem; }
  .page-title-box h4.page-title{ font-weight: 700; letter-spacing: .3px; }

  .breadcrumb{ background: transparent; padding: 0; margin-bottom: 0; }

  .card-modern{
    border:0; border-radius: 18px; background: var(--card);
    box-shadow: var(--shadow-soft); overflow: hidden;
  }
  .card-modern .card-body{ padding: 1rem 1.25rem 1.15rem; }

  .filter-pill{
    background:#f9fafb; border-radius:16px; padding:.75rem .85rem;
    border:1px solid #e5e7eb;
  }
  .filter-label{
    font-size:.8rem; font-weight:600; color:var(--muted);
    text-transform:uppercase; letter-spacing:.05em; margin-bottom:.2rem;
  }

  .form-control{
    border-radius:.75rem; border-color:var(--border); font-size:.9rem;
  }
  .form-control:focus{
    border-color:var(--primary);
    box-shadow:0 0 0 .15rem var(--primary-soft);
  }
  .btn-sm-rounded{
    border-radius:.75rem; font-size:.8rem; padding:.35rem .8rem; font-weight:600;
  }

  .btn-add-user{
    border-radius:.9rem; padding:.45rem 1.3rem; font-weight:600; border:none;
    background:linear-gradient(135deg,#f97316,#ef4444);
    box-shadow:0 10px 20px rgba(239,68,68,.25);
  }
  .btn-add-user i{ font-size:16px; }
  .btn-add-user:hover, .btn-add-user:focus{
    box-shadow:0 10px 24px rgba(239,68,68,.35);
  }

  .badge-role{
    background:#ecfdf5; color:#15803d; padding:.15rem .45rem;
    border-radius:999px; font-size:.75rem; font-weight:600;
  }

  .status-pill{
    display:inline-flex; align-items:center; gap:6px;
    padding:.15rem .6rem; border-radius:999px;
    font-size:.75rem; font-weight:600;
  }
  .status-dot{ width:8px; height:8px; border-radius:999px; }
  .status-active{ background:#ecfdf3; color:#15803d; }
  .status-active .status-dot{ background:#16a34a; }
  .status-inactive{ background:#fef2f2; color:#b91c1c; }
  .status-inactive .status-dot{ background:#ef4444; }

  .table-modern{ margin-bottom:0; }
  .table-modern thead{ background:#f9fafb; }
  .table-modern thead th{
    border-bottom:1px solid var(--border) !important;
    font-size:.8rem; text-transform:uppercase; letter-spacing:.06em;
    font-weight:600; color:var(--muted) !important;
  }
  .table-modern tbody tr{ vertical-align: middle; }
  .table-modern tbody td{ font-size:.9rem; border-top:1px solid #f1f5f9; }

  .user-name{ font-weight:600; }
  .user-sub{ font-size:.8rem; color:var(--muted); }

  .action-icon{
    display:inline-flex; align-items:center; justify-content:center;
    width:30px; height:30px; border-radius:999px;
    background:#eff6ff; color:#1d4ed8; transition:.15s;
  }
  .action-icon i{ font-size:16px; }
  .action-icon:hover{ background:#dbeafe; color:#1d4ed8; }
  .action-icon.delete{ background:#fee2e2; color:#b91c1c; }
  .action-icon.delete:hover{ background:#fecaca; color:#7f1d1d; }

  .table-container{
    border-radius: 16px; border:1px solid rgba(148,163,184,.25);
    overflow:hidden; background:#f9fafb;
  }

  .table-responsive > nav{ margin-top:.75rem; }

  .worker-warning{
    text-align:center; color:#b91c1c; font-weight:700; margin-top:1.5rem;
    padding:.75rem 1rem; border-radius:12px; background:#fef2f2; border:1px solid #fecaca;
  }

  .admin-lock{
    display:inline-flex; align-items:center; gap:6px;
    font-size:.78rem; font-weight:700;
    background:#fff7ed; color:#9a3412;
    padding:.18rem .55rem; border-radius:999px;
    border:1px solid #fed7aa;
  }

  @media (max-width: 767.98px){
    .table-responsive{ border:0; }
    .table-modern thead{ display:none; }
    .table-modern tbody tr{
      display:block; margin-bottom:.85rem; border-radius:14px;
      border:1px solid #e5e7eb; background:#ffffff;
      box-shadow:0 4px 12px rgba(15,23,42,.05);
      padding:.55rem .75rem;
    }
    .table-modern tbody td{
      display:grid; grid-template-columns: 120px 1fr; gap:4px;
      border-top:0 !important; padding:.25rem 0 !important;
    }
    .table-modern tbody td::before{
      content: attr(data-label);
      font-size:.78rem; text-transform:uppercase; letter-spacing:.05em;
      color:var(--muted); font-weight:600;
    }
    .table-modern tbody td:first-child{ grid-template-columns: 40px 1fr; }
    .table-modern tbody td:last-child{ margin-top:.15rem; }
    .table-modern tbody td:last-child::before{ content:"Action"; }
    .filter-pill{ margin-bottom:.6rem; }
  }
</style>

<div class="row">
  <div class="col-12">
    <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
      <div>
        <h4 class="page-title mb-1">User List</h4>
        <ol class="breadcrumb m-0">
          <li class="breadcrumb-item active">User List</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card card-modern">
      <div class="card-body">
        @if(Session::has('success'))
          <div class="alert alert-success mb-3">
            <strong>{{ Session::get('success') }}</strong>
          </div>
        @endif

        <div class="row g-2 mb-3">
          {{-- Search --}}
          <div class="col-xl-3 col-md-6 col-12">
            <div class="filter-pill">
              <div class="filter-label">Search</div>
              <form>
                <input type="text" name="q" class="form-control" placeholder="Search here..." value="{{ request('q')??''}}">
              </form>
            </div>
          </div>

          {{-- Role filter --}}
          <div class="col-xl-3 col-md-6 col-12">
            <div class="filter-pill">
              <div class="filter-label">Role</div>
              <select class="form-control" name="role">
                <option value="" selected disabled>Select One</option>
                <option value="">All User</option>
                @foreach($roles as $role)
                  <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                    {{ ucfirst($role->name) }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          {{-- Bulk status --}}
          <div class="col-xl-3 col-md-6 col-12">
            <div class="filter-pill text-md-start text-center">
              <div class="filter-label">Bulk Status</div>
              @can('user.edit')
                <a href="{{ route('admin.userStatusUpdate') }}?status=0" class="status btn btn-primary btn-sm btn-sm-rounded mb-1 me-1">Active</a>
                <a href="{{ route('admin.userStatusUpdate') }}?status=1" class="status btn btn-info btn-sm btn-sm-rounded mb-1">De-Active</a>
              @endcan
            </div>
          </div>

          {{-- Add new user --}}
          <div class="col-xl-3 col-md-6 col-12">
            <div class="filter-pill text-md-end text-center">
              <div class="filter-label">Create User</div>
              @can('user.create')
                <a href="{{ route('admin.users.create') }}" class="btn btn-danger btn-add-user mb-1">
                  <i class="mdi mdi-basket me-1"></i> Add New User
                </a>
              @endcan
            </div>
          </div>
        </div>

        <div class="table-container">
          <div class="table-responsive">
            <table class="table table-centered table-modern table-nowrap mb-0">
              <thead class="table-light">
                <tr>
                  <th><input type="checkbox" id="parent_item"></th>
                  <th>First Name</th>
                  <th>Last Name</th>
                  <th>Email</th>
                  <th>Username</th>
                  <th>Business Name</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th style="width: 125px;">Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($users as $user)
                  @php
                    $isAdmin = ($user->hasRole('admin') || $user->hasRole('super-admin'));
                    $isMainAdmin = ($user->id == 1);
                  @endphp

                  <tr>
                    <td data-label="Select">
                      <input
                        type="checkbox"
                        value="{{ $user->id }}"
                        class="user_status"
                        {{ $isMainAdmin ? 'disabled' : '' }}
                        title="{{ $isMainAdmin ? 'Main Admin locked' : '' }}"
                      >
                      @if($isMainAdmin)
                        <span class="admin-lock ms-2"><i class="mdi mdi-lock"></i> Main Admin</span>
                      @endif
                    </td>

                    <td data-label="First Name">
                      <span class="user-name">{{ $user->first_name }}</span>
                    </td>
                    <td data-label="Last Name">{{ $user->last_name }}</td>

                    <td data-label="Email">
                      <span class="user-sub">{{ $user->email }}</span>
                    </td>

                    <td data-label="Username">{{ $user->username }}</td>
                    <td data-label="Business Name">{{ $user->business_name }}</td>

                    <td data-label="Role">
                      @foreach($user->getRoleNames() as $role)
                        <span class="badge-role">{{ $role }}</span>
                      @endforeach
                    </td>

                    <td data-label="Status">
                      @if($user->status == null)
                        <span class="status-pill status-inactive">
                          <span class="status-dot"></span> De-Active
                        </span>
                      @else
                        <span class="status-pill status-active">
                          <span class="status-dot"></span> Active
                        </span>
                      @endif
                    </td>

                    <td data-label="Action">
                      @can('user.edit')
                        {{-- ✅ UPDATED: এখন সবার ক্ষেত্রেই শুধু Edit Page এ যাবে --}}
                        <a href="{{ route('admin.users.edit', [$user->id]) }}" class="action-icon" title="Edit">
                          <i class="mdi mdi-square-edit-outline"></i>
                        </a>
                      @endcan

                      @can('user.delete')
                        @if(!$isAdmin)
                          <a href="{{ route('admin.users.destroy',[$user->id])}}" class="delete action-icon" title="Delete">
                            <i class="mdi mdi-delete"></i>
                          </a>
                        @endif
                      @endcan
                    </td>
                  </tr>
                @endforeach

                @if($users->count() === 0)
                  <tr>
                    <td colspan="9" class="text-center text-muted py-3">No users found.</td>
                  </tr>
                @endif
              </tbody>
            </table>

            {{ $users->links() }}
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="worker-warning">
      কমপক্ষে ১ জন Active Worker রাখুন। অন্যথায় Site এ Order আসতে Problem হবে।
    </div>
  </div>
</div>

@endsection

@push('js')
<script>
$(document).ready(function(){

  $(document).on('click', 'a.status', function(e){
    e.preventDefault();
    var url = $(this).attr('href');

    var user_ids = $('input.user_status:checked:not(:disabled)').map(function(){
      return $(this).val();
    }).get();

    if(user_ids.length === 0){
      toastr.error('Please Select A User First !');
      return;
    }

    $.ajax({
      type:'GET',
      url:url,
      data:{user_ids},
      success:function(res){
        if(res.status==true){
          toastr.success(res.msg);
          window.location.reload();
        }else{
          toastr.error(res.msg);
        }
      }
    });
  });

  $(document).on('change', 'select[name="role"]', function(){
    let query = $('input[name="q"]').val();
    let value = $(this).val();
    location.href = '?q=' + encodeURIComponent(query) + '&role=' + encodeURIComponent(value);
  });

  $('#parent_item').change(function(){
    $('.user_status:not(:disabled)').prop('checked', $(this).prop('checked'));
  });

  $('.user_status').change(function(){
    const total = $('.user_status:not(:disabled)').length;
    const checked = $('.user_status:not(:disabled):checked').length;
    $('#parent_item').prop('checked', total > 0 && total === checked);
  });

});
</script>
@endpush