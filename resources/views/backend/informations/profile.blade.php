@extends('backend.app')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
<style>
  /* =========================================
     PREMIUM PROFILE PAGE STYLES
     ========================================= */
  :root {
      --primary-color: #4F46E5; /* Matches your Navbar */
      --secondary-color: #64748b;
      --border-color: #e2e8f0;
  }

  /* Card Styling */
  .card-premium {
      border: none;
      border-radius: 16px;
      box-shadow: 0 0 40px rgba(0,0,0,0.03); /* Soft Premium Shadow */
      overflow: hidden;
      background: #fff;
  }

  /* Header Section (Matches Navbar Color) */
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

  /* Form & Inputs */
  .form-label {
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: var(--secondary-color);
      font-weight: 700;
      margin-bottom: 8px;
  }
  .form-control {
      border: 1px solid var(--border-color);
      padding: 12px 18px;
      border-radius: 8px;
      font-size: 0.95rem;
      color: #334155;
      font-weight: 500;
      transition: all 0.2s ease;
  }
  .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.1); /* Focus Ring */
  }

  /* Profile Image Area */
  .profile-upload-box {
      background: #f8fafc;
      border: 2px dashed var(--border-color);
      border-radius: 12px;
      padding: 20px;
      text-align: center;
      transition: all 0.3s;
      position: relative;
  }
  .profile-upload-box:hover {
      border-color: var(--primary-color);
      background: #f1f5f9;
  }
  .preview-wrap img {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 50%; /* Circle Avatar */
      border: 4px solid #fff;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      margin-bottom: 15px;
  }
  .file-custom-btn {
      font-size: 0.9rem;
      color: var(--primary-color);
      font-weight: 600;
      cursor: pointer;
  }

  /* Sticky Footer Actions */
  .sticky-actions {
      background: #f8fafc;
      padding: 20px 30px;
      border-top: 1px solid var(--border-color);
      display: flex;
      justify-content: space-between;
      align-items: center;
  }

  /* Helpers */
  .section-title {
      font-size: 1.1rem;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid #f1f5f9;
      display: inline-block;
  }
  .is-invalid { border-color: #ef4444 !important; }
  .text-danger { font-size: 0.8rem; margin-top: 5px; }
  .help-text { font-size: 0.8rem; color: #94a3b8; }
  
  /* Spinner */
  .spinner {
    width: 18px; height: 18px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: #fff;
    border-radius: 50%;
    display: inline-block;
    animation: spin 0.8s linear infinite;
    margin-right: 8px;
  }
  @keyframes spin{to{transform:rotate(360deg)}}

  @media(max-width:576px){
    .breadcrumb{display:none}
    .sticky-actions { flex-direction: column; gap: 15px; }
    .sticky-actions .btn { width: 100%; }
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
          <li class="breadcrumb-item active">Profile Settings</li>
        </ol>
      </div>
      <h4 class="page-title">My Account</h4>
    </div>
  </div>
</div>

<div class="row justify-content-center">
  <div class="col-xl-10 col-lg-12">
    
    <div class="card card-premium">
      
      {{-- Header Section (Matches Navbar Color) --}}
      <div class="card-header-premium">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4>Edit Profile</h4>
                <p>Update your personal information and profile picture.</p>
            </div>
            <div class="d-none d-md-block">
                <i class="uil uil-user-circle text-white opacity-50" style="font-size: 40px;"></i>
            </div>
        </div>
      </div>

      <div class="card-body p-0">
        <div id="message" class="px-4 pt-3"></div>

        <form action="{{route('admin.profile.update')}}" method="POST" enctype="multipart/form-data" id="profile_update_form">
          @csrf

          <div class="p-4 p-md-5">
            <div class="row">
                
                {{-- Left Side: Profile Image --}}
                <div class="col-lg-4 mb-4 mb-lg-0 text-center border-end-lg" style="border-right-color: #f1f5f9;">
                    <div class="section-title w-100 text-start">Profile Photo</div>
                    
                    <div class="profile-upload-box mt-3">
                        <div class="preview-wrap">
                            <img src="{{ asset('uploads/img/'.$data->image) }}" alt="Profile" id="preview_img" onerror="this.src='{{ asset('backend/img/default-avatar.png') }}'">
                        </div>
                        
                        <div class="mt-3">
                            <label for="image" class="btn btn-outline-primary btn-sm rounded-pill">
                                <i class="uil uil-camera me-1"></i> Change Photo
                            </label>
                            <input type="file" id="image" class="d-none" name="image" accept="image/*">
                            <small class="help-text d-block mt-2">Max 1MB (JPG, PNG, WEBP)</small>
                            <small class="text-danger d-block" id="image_error"></small>
                        </div>
                    </div>
                </div>

                {{-- Right Side: Form Inputs --}}
                <div class="col-lg-8 ps-lg-5">
                    <div class="section-title w-100">Personal Information</div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" id="first_name" class="form-control" name="first_name" value="{{ $data->first_name }}">
                            <small class="text-danger d-block" id="first_name_error"></small>
                        </div>

                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" id="last_name" class="form-control" name="last_name" value="{{ $data->last_name }}">
                            <small class="text-danger d-block" id="last_name_error"></small>
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 bg-light"><i class="uil uil-envelope-alt"></i></span>
                                <input type="email" id="email" class="form-control border-start-0 ps-0" name="email" value="{{ $data->email }}" required>
                            </div>
                            <small class="text-danger d-block" id="email_error"></small>
                        </div>

                        <div class="col-md-6">
                            <label for="mobile" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 bg-light"><i class="uil uil-phone"></i></span>
                                <input type="tel" id="mobile" class="form-control border-start-0 ps-0" name="mobile" value="{{ $data->mobile }}">
                            </div>
                            <small class="text-danger d-block" id="mobile_error"></small>
                        </div>

                        <div class="col-md-6">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" id="username" class="form-control bg-light" name="username" value="{{ $data->username }}">
                            <small class="help-text">Public display name (min 3 chars)</small>
                            <small class="text-danger d-block" id="username_error"></small>
                        </div>

                        <div class="col-md-6">
                            <label for="business_name" class="form-label">Business / Shop Name</label>
                            <input type="text" id="business_name" class="form-control" name="business_name" value="{{ $data->business_name }}">
                            <small class="text-danger d-block" id="business_name_error"></small>
                        </div>
                    </div>
                </div>

            </div>
          </div>

          {{-- Sticky Actions --}}
          <div class="sticky-actions">
            <a href="{{route('admin.dashboard')}}" class="btn btn-light text-muted border">
                <i class="uil uil-arrow-left me-1"></i> Back to Dashboard
            </a>
            <button type="submit" class="btn btn-primary px-4 py-2" id="updateBtn" style="background-color: var(--primary-color); border: none;">
                <i class="uil uil-save me-1"></i> Save Changes
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
<script>
  // --- Helpers ---
  function setError(field, msg){
    const input = document.getElementById(field);
    const target = document.getElementById(field + '_error');
    if (input) input.classList.add('is-invalid');
    if (target) target.textContent = msg || '';
  }
  function clearError(field){
    const input = document.getElementById(field);
    const target = document.getElementById(field + '_error');
    if (input) input.classList.remove('is-invalid');
    if (target) target.textContent = '';
  }
  function showSuccess(msg){
    const box = document.getElementById('message');
    box.innerHTML = `<div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                        <i class="uil uil-check-circle me-1"></i> <strong>Success!</strong> ${msg}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                     </div>`;
    // Auto scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(()=> box.innerHTML = '', 4000);
  }
  
  function showErrors(errors){
    const keys = ['first_name','last_name','email','username','mobile','business_name','image'];
    keys.forEach(k=>{
      if(errors[k]) setError(k, errors[k][0]);
      else clearError(k);
    });
    // If there are errors, scroll to top to see them
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  // Image Preview + Validation
  const imageInput = document.getElementById('image');
  const previewImg = document.getElementById('preview_img');
  
  if(imageInput && previewImg){
    imageInput.addEventListener('change', (e)=>{
      const file = e.target.files?.[0];
      if(!file) return;
      
      const validTypes = ['image/jpeg','image/png','image/webp'];
      if(!validTypes.includes(file.type)){
        setError('image', 'Only JPG, PNG or WEBP allowed.');
        imageInput.value=''; return;
      }
      if(file.size > 1024*1024){ // 1MB
        setError('image', 'Max size 1MB.');
        imageInput.value=''; return;
      }
      
      clearError('image');
      previewImg.src = URL.createObjectURL(file);
    });
  }

  // Clear errors on input
  ['first_name','last_name','email','username','mobile','business_name'].forEach(id=>{
    const el = document.getElementById(id);
    if(el){
      el.addEventListener('input', ()=> clearError(id));
      el.addEventListener('change', ()=> clearError(id));
    }
  });

  // Submit via AJAX
  $(document).ready(function(){
    $("#profile_update_form").on('submit', function(e){
      e.preventDefault();
      const $btn = $("#updateBtn");
      const original = $btn.html();
      const data = new FormData(this);

      $.ajax({
        url: $(this).attr("action"),
        method: $(this).attr("method"),
        data,
        contentType:false,
        processData:false,
        beforeSend: function(){
          $btn.prop('disabled', true).html(`<span class="spinner"></span> Updating...`);
        },
        success: function(res){
          $btn.prop('disabled', false).html(original);

          if(res?.errors){
            showErrors(res.errors);
          } else if(res?.success){
            // Clear all error states
            ['first_name','last_name','email','username','mobile','business_name','image'].forEach(clearError);
            showSuccess(res.success);

            if(res.image_url){
              $("#preview_img").attr('src', res.image_url);
            }
          } else {
             $("#message").html(`<div class="alert alert-warning">Unexpected response.</div>`);
          }
        },
        error: function(xhr){
          $btn.prop('disabled', false).html(original);
          let msg = 'Something went wrong.';
          if(xhr?.responseJSON?.message) msg = xhr.responseJSON.message;
          $("#message").html(`<div class="alert alert-danger">${msg}</div>`);
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
      });
    });
  });
</script>
@endpush