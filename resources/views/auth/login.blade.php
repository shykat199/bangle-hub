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
            /* ✅ updated to match brand vibe (still soft like yours) */
            background: radial-gradient(circle at top, color-mix(in srgb, var(--auth-accent) 10%, transparent) 0, color-mix(in srgb, var(--auth-accent) 5%, transparent) 25%, #ffffff 60%);
        }

        .axil-checkout-area {
            padding: 70px 0;
        }

        .auth-card {
            border-radius: 22px;
            overflow: hidden;
            border: 0;
            box-shadow: 0 22px 55px rgba(15, 23, 42, 0.14);
            background: #ffffff;
        }

        .auth-header {
            /* ✅ same as header: gradient + text */
            background: var(--brand-gradient);
            color: var(--brand-text);
            padding: 26px 22px;
            text-align: center;
        }

        .auth-header h2 {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--brand-text);
        }

        .auth-header p {
            font-size: 13px;
            margin-top: 6px;
            opacity: .95;
            color: var(--brand-text);
        }

        .auth-body {
            padding: 26px;
            background: #f9fafb;
        }

        .auth-inner {
            background: white;
            border-radius: 16px;
            padding: 20px 18px 18px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #374151;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            color: #9ca3af;
        }

        .input-wrapper input {
            width: 100%;
            border-radius: 999px;
            padding: 10px 14px 10px 40px;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            font-size: 14px;
            transition: all .18s ease;
        }

        .input-wrapper input:focus {
            background: #ffffff;
            /* ✅ brand focus */
            border-color: rgba(255,255,255,.0);
            box-shadow: 0 0 0 .15rem rgba(0, 39, 108, .18);
            outline: none;
        }

        .auth-btn {
            width: 100%;
            border-radius: 999px;
            padding: 12px 14px;
            font-weight: 600;
            border: none;
            font-size: 15px;
            background: var(--auth-accent);
            color: #fff;
            margin-top: 8px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            transition: .18s ease;
        }

        .auth-btn:hover {
            filter: brightness(1.06);
            box-shadow: 0 12px 24px color-mix(in srgb, var(--auth-accent) 32%, transparent);
            transform: translateY(-1px);
        }

        /* "New here?" — a divider, then a clear secondary button to the sign-up page */
        .auth-extra {
            margin-top: 20px;
            text-align: center;
        }
        .auth-extra-label {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 12px;
            font-size: 13px; font-weight: 600; color: #6b7280;
        }
        .auth-extra-label::before,
        .auth-extra-label::after { content: ""; flex: 1; height: 1px; background: #e5e7eb; }
        .auth-extra a {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%; padding: 11px 14px; border-radius: 999px;
            border: 1.5px solid var(--auth-accent); background: #fff;
            color: var(--auth-accent) !important; font-size: 15px; font-weight: 600;
            text-decoration: none !important; transition: background .18s ease, color .18s ease;
        }
        .auth-extra a:hover {
            background: var(--auth-accent); color: #fff !important;
        }
        .auth-extra a:hover i { color: #fff !important; }
        .auth-extra a i { font-size: 13px; color: inherit !important; }

        .invalid-feedback {
            display: block;
            font-size: 12px;
            margin-top: 4px;
            color: #dc2626;
        }

        @media (max-width: 767.98px){
            .axil-checkout-area {
                padding: 40px 0;
            }
            .auth-body {
                padding: 20px 16px;
            }
            .auth-inner {
                padding: 16px 14px 14px;
            }
        }
    </style>

    <div class="axil-checkout-area">
        <div class="container">
            <form method="POST" action="{{ route('front.login') }}" id="login_form">
                @csrf

                <div class="row justify-content-center">
                    <div class="col-lg-6 col-md-8">

                        <div class="auth-card">

                            {{-- HEADER --}}
                            <div class="auth-header">
                                <h2>Sign In</h2>
                                <p>Access your account securely</p>
                            </div>

                            {{-- BODY --}}
                            <div class="auth-body">
                                <div class="auth-inner">

                                    {{-- Global Errors --}}
                                    @if ($errors->any())
                                        <div class="alert alert-danger small py-2 px-3 mb-3" style="border-radius:10px;">
                                            {{ $errors->first() }}
                                        </div>
                                    @endif

                                    <h4 class="auth-title" style="font-weight:700; font-size:18px;">Welcome Back</h4>
                                    <p class="auth-subtitle" style="margin-bottom:22px;">
                                        Enter your email and password to continue.
                                    </p>
                                    <br>

                                    {{-- EMAIL --}}
                                    <div class="form-group">
                                        <label>Email Address</label>
                                        <div class="input-wrapper">
                                            <i class="fas fa-envelope"></i>
                                            <input type="email" name="email" value="{{ old('email') }}" placeholder="example@mail.com" required>
                                        </div>
                                        @error('email')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    {{-- PASSWORD --}}
                                    <div class="form-group">
                                        <label>Password</label>
                                        <div class="input-wrapper">
                                            <i class="fas fa-lock"></i>
                                            <input type="password" name="password" placeholder="Your password" required>
                                        </div>
                                        @error('password')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    {{-- LOGIN BUTTON --}}
                                    <button type="submit" class="auth-btn">
                                        Sign In
                                        <i class="fas fa-arrow-right"></i>
                                    </button>

                                    {{-- REGISTER LINK --}}
                                    <div class="auth-extra">
                                        <div class="auth-extra-label">New here?</div>
                                        <a href="{{ url('register') }}"><i class="fas fa-user-plus"></i> Create an account</a>
                                    </div>

                                </div>
                            </div>

                        </div> <!-- /auth-card -->

                    </div>
                </div>

            </form>
        </div>
    </div>
</main>
@endsection
