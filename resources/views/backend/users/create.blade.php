@extends('backend.app')
@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
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

  body{ background:var(--bg); }

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

  .card-modern{
    border:0;
    border-radius:18px;
    background:var(--card);
    box-shadow:var(--shadow-soft);
    overflow:hidden;
  }
  .card-modern .card-body{
    padding:1.25rem 1.5rem 1.4rem;
  }

  .btn-back{
    border-radius:.75rem;
    padding:.35rem .9rem;
    font-size:.85rem;
    font-weight:600;
  }

  .form-label{
    font-size:.85rem;
    font-weight:600;
    color:var(--muted);
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

  .btn-submit{
    border-radius:.9rem;
    padding:.45rem 1.4rem;
    font-weight:600;
    font-size:.9rem;
    border:none;
    background:linear-gradient(135deg,#22c55e,#16a34a);
    box-shadow:0 10px 20px rgba(22,163,74,.25);
  }
  .btn-submit:hover,
  .btn-submit:focus{
    box-shadow:0 10px 24px rgba(22,163,74,.35);
  }

  .section-title{
    font-size:.95rem;
    font-weight:600;
    color:#0f172a;
    margin-bottom:.75rem;
  }

  @media (max-width: 767.98px){
    .card-modern .card-body{
      padding:1rem 1rem 1.1rem;
    }
    .page-title-box{
      margin-bottom:.5rem;
    }
  }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h4 class="page-title mb-1">User Create</h4>
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);">CRM</a></li>
                    <li class="breadcrumb-item active">User Create</li>
                </ol>
            </div>
        </div>
    </div>
</div>   
<!-- end page title --> 

<div class="row">
    <div class="col-12">
        <div class="card card-modern">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <a href="{{route('admin.users.index')}}" class="btn btn-secondary btn-back">
                        ← Back to User List
                    </a>
                    <span class="text-muted small">
                        Create a new user for your team
                    </span>
                </div>

                <form action="{{route('admin.users.store')}}" method="POST" id="ajax_form">
                    @csrf

                    <p class="section-title">Basic Information</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="form-label" for="first_name">First Name</label>
                                <input type="text"
                                       id="first_name"
                                       class="form-control"
                                       name="first_name"
                                       placeholder="First name...">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="form-label" for="last_name">Last Name</label>
                                <input type="text"
                                       id="last_name"
                                       class="form-control"
                                       name="last_name"
                                       placeholder="Last name...">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="form-label" for="business_name">Business Name</label>
                                <input type="text"
                                       id="business_name"
                                       class="form-control"
                                       name="business_name"
                                       placeholder="Business name...">
                            </div>
                        </div>
                      
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="form-label" for="email">Email</label>
                                <input type="email"
                                       id="email"
                                       class="form-control"
                                       name="email"
                                       placeholder="Email...">
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <p class="section-title">Login & Role</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="form-label" for="username">Username</label>
                                <input type="text"
                                       id="username"
                                       class="form-control"
                                       name="username"
                                       placeholder="Username...">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="form-label" for="password">Password</label>
                                <input type="password"
                                       id="password"
                                       class="form-control"
                                       name="password"
                                       placeholder="Password...">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="form-label" for="confirm_password">Confirm Password</label>
                                <input type="password"
                                       id="confirm_password"
                                       class="form-control"
                                       name="confirm_password"
                                       placeholder="Re-type Password...">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="form-label" for="role">Role</label>
                                <select name="role" id="role" class="form-control">
                                    <option value="" disabled selected>Choose a Role</option>
                                    @foreach($roles as $role)
                                        <option value="{{$role->id}}">{{$role->name}}</option>
                                    @endforeach
                                </select>  
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <input type="submit" value="Save User" class="btn btn-success btn-submit">
                    </div>
                </form>
            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div> <!-- end col -->
</div> <!-- end row -->
@endsection 

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript">
  // future custom JS if needed
</script>
@endpush
