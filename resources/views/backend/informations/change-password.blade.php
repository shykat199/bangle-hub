@extends('backend.app')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
<style>
  /* =========================================
     PREMIUM PASSWORD PAGE STYLES
     ========================================= */
  :root {
      --primary-color: #4F46E5; /* Matches Navbar */
      --secondary-color: #64748b;
      --border-color: #e2e8f0;
  }

  /* Card Styling */
  .card-premium {
      border: none;
      border-radius: 16px;
      box-shadow: 0 0 40px rgba(0,0,0,0.03);
      overflow: hidden;
      background: #fff;
  }

  /* Header Section */
  .card-header-premium {
      background-color: var(--primary-color);
      padding: 25px 30px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
  }
  .card-header-premium h4 {
      color: #fff;
      margin: 0;
      font-weight: 600;
      font-size: 1.25rem;
  }
  .card-header-premium p {
      color: rgba(255,255,255,0.7);
      margin: 5px 0 0;
      font-size: 0.9rem;
  }

  /* Form Inputs */
  .form-label {
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: var(--secondary-color);
      font-weight: 700;
      margin-bottom: 8px;
  }
  .input-group-text {
      background-color: #f8fafc;
      border: 1px solid var(--border-color);
      border-right: none;
      color: var(--secondary-color);
  }
  .form-control {
      border: 1px solid var(--border-color);
      padding: 12px 18px;
      border-radius: 0 8px 8px 0; /* Rounded right only due to input group */
      font-size: 0.95rem;
      color: #334155;
      font-weight: 500;
      transition: all 0.2s ease;
  }
  .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.1);
      z-index: 3;
  }
  
  /* Buttons */
  .btn-primary-premium {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
      color: #fff;
      padding: 10px 25px;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.3s;
  }
  .btn-primary-premium:hover {
      background-color: #1e293b;
      box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);
  }

  /* Helpers */
  .is-invalid { border-color: #ef4444 !important; }
  .text-danger { font-size: 0.8rem; margin-top: 5px; font-weight: 500; }
  
  /* Spinner */
  .spinner {
    width: 18px; height: 18px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: #fff;
    border-radius: 50%;
    display: inline-block;
    animation: spin 0.8s linear infinite;
    margin-right: 8px;
    vertical-align: middle;
  }
  @keyframes spin{to{transform:rotate(360deg)}}

  @media(max-width:576px){
    .breadcrumb{display:none}
  }
</style>
@endpush

@section('content')
<div class="row mb-3">
  <div class="col-12">
    <div class="page-title-box">
      <div class="page-title-right">
        <ol class="breadcrumb m-0">
          <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
          <li class="breadcrumb-item active">Security</li>
        </ol>
      </div>
      <h4 class="page-title">Change Password</h4>
    </div>
  </div>
</div>

<div class="row justify-content-center">
  {{-- Centered Column for better look --}}
  <div class="col-xl-6 col-lg-8 col-md-10">
    
    <div class="card card-premium">
        
        {{-- Header --}}
        <div class="card-header-premium">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4>Security Settings</h4>
                    <p>Ensure your account is secure by using a strong password.</p>
                </div>
                <div>
                    <i class="uil uil-shield-check text-white opacity-50" style="font-size: 36px;"></i>
                </div>
            </div>
        </div>

        <div class="card-body p-4 p-md-5">
            
            <a href="{{route('admin.dashboard')}}" class="btn btn-light text-muted border mb-4 btn-sm">
                <i class="uil uil-arrow-left me-1"></i> Back to Dashboard
            </a>

            <div id="message"></div>

            <form action="{{route('admin.password.update')}}" method="POST" id="password_update_form">
                @csrf
                
                <div class="mb-4">
                    <label for="password" class="form-label">Old Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="uil uil-lock"></i></span>
                        <input type="password" id="password" class="form-control" name="password" placeholder="Enter current password">
                    </div>
                    <p class="text-danger" id="password_error"></p>
                </div>

                <div class="mb-4">
                    <label for="new_password" class="form-label">New Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="uil uil-key-skeleton"></i></span>
                        <input type="password" id="new_password" class="form-control" name="new_password" placeholder="Enter new password">
                    </div>
                    <p class="text-danger" id="new_password_error"></p>
                </div>

                <div class="mb-4">
                    <label for="cpassword" class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="uil uil-check-circle"></i></span>
                        <input type="password" id="cpassword" class="form-control" name="password_confirmation" placeholder="Confirm new password">
                    </div>
                    <p class="text-danger" id="cpassword_error"></p>
                </div>

                <div class="d-grid mt-5">
                    <button type="submit" class="btn btn-primary-premium" id="updateBtn">
                        Update Password
                    </button>
                </div>

            </form>
        </div>
    </div>

  </div>
</div>
@endsection 

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript">
    
    // Helper to clear errors on input
    function clearError(id) {
        $(`#${id}`).removeClass('is-invalid');
        $(`#${id}_error`).html('');
    }

    ['password', 'new_password', 'cpassword'].forEach(id => {
        $(`#${id}`).on('input', function() {
            clearError(id);
        });
    });

    $(document).ready(function(){
       $("#password_update_form").submit(function(e){
            e.preventDefault();
            let url = $(this).attr("action");
            let method = $(this).attr("method");
            let data = $(this).serialize();
            
            $.ajax({
               url,
               method,
               data,
               beforeSend:function()
               {
                   $("#updateBtn").attr('disabled', true).html('<span class="spinner"></span> Updating...');
               },
               success:function(res)
               {
                    $("#updateBtn").attr('disabled', false).html('Update Password');
                    
                    if(res.errors)
                    {
                        let errors = res.errors;
                        if(errors.password){
                            $("#password").addClass('is-invalid');
                            $("#password_error").html(errors.password[0]);
                        }
                        if(errors.new_password){
                            $("#new_password").addClass('is-invalid');
                            $("#new_password_error").html(errors.new_password[0]);
                        }
                        if(errors.password_confirmation){
                            $("#cpassword").addClass('is-invalid');
                            $("#cpassword_error").html(errors.password_confirmation[0]);
                        }
                    }
                    else if(res.success)
                    {
                           // Clear inputs and errors
                           $('#password_update_form')[0].reset();
                           $('.form-control').removeClass('is-invalid');
                           $('.text-danger').html('');

                           $("#message").html(`<div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <i class="uil uil-check-circle me-1"></i> <strong>Success!</strong> ${res.success}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>`);
                           
                           setTimeout(function(){
                                $("#message").html('');
                                // Optional: location.reload(); 
                           }, 2000);
                    }                    
                    else if(res.error)
                    {
                           $('.text-danger').html(''); 
                           $("#message").html(`<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <i class="uil uil-exclamation-triangle me-1"></i> <strong>Error!</strong> ${res.error}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>`);
                    }
               },
               error: function(xhr) {
                   $("#updateBtn").attr('disabled', false).html('Update Password');
                   $("#message").html(`<div class="alert alert-danger">Something went wrong. Please try again.</div>`);
               }
            });
       });
    });
  
</script>
@endpush