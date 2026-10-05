@extends('frontend.app')

@section('content')
@php
    use App\Models\Information;
    $information  = Information::first();

    // ✅ same as header
    $brandGradient = $information->gradient_code ?? 'linear-gradient(90deg,#0d6efd,#00276C)';
    $brandText     = $information->primary_color ?? '#ffffff';
@endphp

<main class="main-wrapper auth-page">
    <style>
        :root{
            --brand-gradient: {!! $brandGradient !!};
            --brand-text: {{ $brandText }};
            /* solid accent for buttons and links: the header gradient can be white on this theme */
            --auth-accent: {{ themeAccent() }};
        }

        .auth-page {
            background: radial-gradient(circle at top, color-mix(in srgb, var(--auth-accent) 10%, transparent) 0, color-mix(in srgb, var(--auth-accent) 5%, transparent) 30%, #ffffff 70%);
        }

        .axil-checkout-area { padding: 70px 0; }

        .auth-card{
            border-radius: 22px;
            overflow: hidden;
            border: 0;
            box-shadow: 0 22px 55px rgba(15, 23, 42, 0.14);
            background: #ffffff;
        }

        .auth-header{
            background: var(--brand-gradient);
            color: var(--brand-text);
            padding: 24px 22px;
            text-align: center;
        }

        .auth-header .auth-title{
            font-size: 22px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-bottom: 6px;
            color: var(--brand-text);
        }

        .auth-body{
            padding: 22px 22px 20px;
            background: #f9fafb;
        }

        .auth-inner{
            background: #ffffff;
            border-radius: 16px;
            padding: 18px 18px 16px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
        }

        .form-group{ margin-bottom: 14px; }

        .form-group label{
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-group span{ color:#ef4444; }

        .input-wrapper{ position:relative; }

        .input-wrapper i{
            position:absolute;
            left:14px;
            top:50%;
            transform:translateY(-50%);
            font-size:14px;
            color:#9ca3af;
        }

        .input-wrapper input{
            width:100%;
            border-radius:999px;
            padding:10px 14px 10px 38px;
            border:1px solid #e5e7eb;
            background:#f9fafb;
            font-size:14px;
            transition:all .18s ease;
        }

        .input-wrapper input:focus{
            outline:none;
            border-color:rgba(255,255,255,0);
            background:#ffffff;
            box-shadow:0 0 0 .15rem rgba(0, 39, 108, .18);
        }

        .auth-btn{
            width:100%;
            border-radius:999px;
            padding:12px 14px;
            font-weight:600;
            border:none;
            font-size:15px;
            background:var(--auth-accent);
            color:#fff;
            margin-top:10px;
            display:inline-flex;
            justify-content:center;
            align-items:center;
            gap:6px;
            transition:.18s ease;
            cursor:pointer;
        }

        .auth-btn:hover{
            filter:brightness(1.05);
            box-shadow:0 12px 24px color-mix(in srgb, var(--auth-accent) 32%, transparent);
            transform:translateY(-1px);
        }

        .auth-btn:disabled{
            opacity:.7;
            cursor:not-allowed;
            transform:none;
            box-shadow:none;
        }

        /* "Already have an account?" — a divider, then a clear secondary button to the login page */
        .auth-login-link{
            margin-top:20px;
            text-align:center;
        }
        .auth-login-label{
            display:flex; align-items:center; gap:12px;
            margin-bottom:12px;
            font-size:13px; font-weight:600; color:#6b7280;
        }
        .auth-login-label::before,
        .auth-login-label::after{ content:""; flex:1; height:1px; background:#e5e7eb; }
        .auth-login-link a{
            display:flex; align-items:center; justify-content:center; gap:8px;
            width:100%; padding:11px 14px; border-radius:999px;
            border:1.5px solid var(--auth-accent); background:#fff;
            color:var(--auth-accent) !important; font-size:15px; font-weight:600;
            text-decoration:none !important; transition:background .18s ease, color .18s ease;
        }
        .auth-login-link a i{ font-size:13px; color:inherit !important; }
        .auth-login-link a:hover{ background:var(--auth-accent); color:#fff !important; }
        .auth-login-link a:hover i{ color:#fff !important; }

        .invalid-feedback{
            display:block;
            font-size:12px;
            margin-top:3px;
            color:#dc2626;
        }

        @media (max-width: 767.98px){
            .axil-checkout-area{ padding:40px 0; }
            .auth-body{ padding:18px 14px 16px; }
            .auth-inner{ padding:14px 12px 12px; }
        }
    </style>

    <!-- Start Register Area -->
    <div class="axil-checkout-area">
        <div class="container">
            <form action="{{ route('front.register') }}" method="POST" id="register_form">
                @csrf

                <div class="row justify-content-center">
                    <div class="col-lg-6 col-md-8">

                        <div class="card auth-card">
                            <div class="auth-header">
                                <div class="auth-title">Sign Up</div>
                            </div>

                            <div class="auth-body">
                                <div class="auth-inner">

                                    {{-- Global error --}}
                                    @if ($errors->any())
                                        <div class="alert alert-danger py-2 px-3 mb-3" style="border-radius:10px;">
                                            <small>{{ $errors->first() }}</small>
                                        </div>
                                    @endif

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>First Name <span>*</span></label>
                                                <div class="input-wrapper">
                                                    <i class="fas fa-user"></i>
                                                    <input type="text" name="first_name" value="{{ old('first_name') }}" placeholder="Adam" required>
                                                </div>
                                                @error('first_name')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Last Name <span>*</span></label>
                                                <div class="input-wrapper">
                                                    <i class="fas fa-user"></i>
                                                    <input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="John" required>
                                                </div>
                                                @error('last_name')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Username <span>*</span></label>
                                        <div class="input-wrapper">
                                            <i class="fas fa-id-badge"></i>
                                            <input type="text" name="username" value="{{ old('username') }}" placeholder="yourname" required>
                                        </div>
                                        @error('username')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Phone <span>*</span></label>
                                        <div class="input-wrapper">
                                            <i class="fas fa-phone-alt"></i>
                                            <input type="tel" name="mobile" value="{{ old('mobile') }}" placeholder="01XXXXXXXXX" required>
                                        </div>
                                        @error('mobile')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Email Address <span>*</span></label>
                                        <div class="input-wrapper">
                                            <i class="fas fa-envelope"></i>
                                            <input type="email" name="email" value="{{ old('email') }}" placeholder="example@mail.com" required>
                                        </div>
                                        @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Password <span>*</span></label>
                                        <div class="input-wrapper">
                                            <i class="fas fa-lock"></i>
                                            <input type="password" name="password" placeholder="Enter Password Here" required>
                                        </div>
                                        @error('password')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Confirm Password <span>*</span></label>
                                        <div class="input-wrapper">
                                            <i class="fas fa-lock"></i>
                                            <input type="password" name="password_confirmation" placeholder="Re-Enter Password Here" required>
                                        </div>
                                    </div>

                                    <div class="form-group mb-1">
                                        <button type="submit" class="auth-btn" id="register_submit_btn">
                                            Create Account <i class="fas fa-arrow-right"></i>
                                        </button>
                                    </div>

                                    <div class="auth-login-link">
                                        <div class="auth-login-label">Already have an account?</div>
                                        <a href="{{ route('login') }}"><i class="fas fa-sign-in-alt"></i> Sign In</a>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </form>
        </div>
    </div>
    <!-- End Register Area -->
</main>
@endsection

@push('js')

<script>
(function () {
  const form = document.getElementById('register_form');
  if (!form) return;

  const btn = document.getElementById('register_submit_btn');

  form.addEventListener('submit', async function (e) {
    e.preventDefault();

    // prevent double submit
    if (form.dataset.submitting === "1") return;
    form.dataset.submitting = "1";

    if (btn) {
      btn.disabled = true;
      btn.innerText = 'Processing...';
    }

    try {
      const res = await fetch(form.action, {
        method: "POST",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
        },
        body: new FormData(form)
      });

      const data = await res.json();

      if (data.success) {
        window.location.href = data.url || "/";
        return;
      }

      // fallback if server returns success=false
      alert(data.msg || "Something went wrong!");
      form.dataset.submitting = "0";
      if (btn) {
        btn.disabled = false;
        btn.innerText = 'Create Account';
      }

    } catch (err) {
      console.log(err);
      alert("Network error!");
      form.dataset.submitting = "0";
      if (btn) {
        btn.disabled = false;
        btn.innerText = 'Create Account';
      }
    }
  });
})();
</script>
@endpush
