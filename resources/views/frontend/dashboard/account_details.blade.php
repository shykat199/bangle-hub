@extends('frontend.app')
@section('content')

@php
  use App\Models\Information;

  $information   = Information::first();

  $brandGradient = $information->gradient_code ?? 'linear-gradient(90deg,#0d6efd,#00276C)';
  $brandText     = $information->primary_color ?? '#ffffff';

  $primary   = $information->primary_color2 ?? '#00276C';
  $primary2  = $information->primary_color3 ?? '#033199';
@endphp

<style>
  :root{
    --brand-gradient: {!! $brandGradient !!};
    --brand-text: {{ $brandText }};

    --primary:#00276C;
    --primary2:#033199;
    --bg:#f5f7ff;
    --card:#ffffff;
    --text:#0f172a;
    --muted:#64748b;
    --shadow:0 14px 40px rgba(0,39,108,.10);
    --shadow2:0 10px 24px rgba(0,39,108,.14);
    --radius:18px;

    /* compact input */
    --field-h: 46px;
    --field-radius: 18px;
    --field-font: 14px;
    --field-px: 16px;
  }

  .axil-breadcrumb-area{
    background: linear-gradient(90deg, rgba(0,39,108,.10), rgba(3,49,153,.06)), #fff;
    border-bottom:1px solid rgba(0,39,108,.08);
    padding: 18px 0;
  }
  .axil-breadcrumb a{ color: var(--primary) !important; font-weight: 800; text-decoration: none; }
  .axil-breadcrumb-item.active{ color: var(--text) !important; font-weight: 900; }

  .axil-dashboard-area{
    background:
      radial-gradient(circle at 15% 12%, rgba(0,39,108,.07), transparent 45%),
      radial-gradient(circle at 88% 18%, rgba(3,49,153,.07), transparent 45%),
      linear-gradient(180deg, var(--bg), var(--bg));
    padding: 22px 0 50px;
  }

  .dash-shell{
    background: var(--card);
    border: 1px solid rgba(0,39,108,.08);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
  }

  .dash-top{
    padding: 18px 22px;
    background:
      radial-gradient(circle at 20% 30%, rgba(0,39,108,.10), transparent 52%),
      linear-gradient(90deg, rgba(0,39,108,.06), rgba(3,49,153,.04));
    border-bottom: 1px solid rgba(0,39,108,.08);
    display:flex; align-items:center; justify-content:space-between; gap: 14px;
  }
  .dash-hello{ margin:0; font-weight: 1000; color: var(--text); letter-spacing: .2px; }
  .dash-sub{ margin: 2px 0 0; color: var(--muted); font-weight: 600; font-size: 14px; }

  .user-badge{
    display:flex; align-items:center; gap: 10px;
    padding: 10px 12px; border-radius: 999px;
    background: #fff; border: 1px solid rgba(0,39,108,.10);
    box-shadow: 0 10px 20px rgba(0,39,108,.08);
    white-space: nowrap;
  }
  .user-avatar{
    width: 36px; height: 36px; border-radius: 999px;
    background: var(--brand-gradient);
    display:flex; align-items:center; justify-content:center;
    color: var(--brand-text); font-weight: 1000; letter-spacing:.5px;
    box-shadow: 0 12px 20px rgba(0,39,108,.20);
    flex: 0 0 auto;
  }
  .user-meta{ display:flex; flex-direction:column; line-height: 1.1; }
  .user-name{ font-weight: 900; color: var(--text); font-size: 14px; }
  .user-tag{ font-size: 12px; font-weight: 800; color: rgba(0,39,108,.75); }

  .dash-aside{
    background: #fff;
    border: 1px solid rgba(0,39,108,.08);
    border-radius: var(--radius);
    box-shadow: var(--shadow2);
    padding: 14px;
    position: sticky;
    top: 110px;
  }
  .dash-nav-title{ font-weight: 900; color: var(--text); margin: 2px 4px 10px; font-size: 14px; }
  .dash-nav{ display:flex; flex-direction:column; gap: 10px; }

  .dash-link{
    display:flex; align-items:center; gap: 10px;
    padding: 12px 12px;
    border-radius: 14px;
    border: 1px solid rgba(0,39,108,.08);
    background: #fff;
    color: var(--text) !important;
    font-weight: 900;
    text-decoration:none !important;
    transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease, background .15s ease;
  }
  .dash-link .ico{
    width: 36px; height: 36px; border-radius: 12px;
    display:flex; align-items:center; justify-content:center;
    background: rgba(0,39,108,.08);
    color: #00276C;
    flex: 0 0 auto;
    transition: .15s ease;
  }
  .dash-link i{ font-size: 15px; }
  .dash-link:hover{
    transform: translateY(-1px);
    box-shadow: 0 14px 26px rgba(0,39,108,.10);
    border-color: rgba(0,39,108,.20);
    background: rgba(0,39,108,.03);
  }

  .dash-link.active{
    border-color: rgba(0,39,108,.45);
    background: var(--brand-gradient);
    color: var(--brand-text) !important;
    box-shadow: 0 0 0 4px rgba(0,39,108,.10), 0 16px 28px rgba(0,39,108,.12);
  }
  .dash-link.active .ico{
    background: rgba(255,255,255,.18);
    color: var(--brand-text);
    box-shadow: 0 14px 24px rgba(0,39,108,.18);
  }
  .dash-link.active span,
  .dash-link.active i{ color: var(--brand-text) !important; }

  .dash-link.logout{ border-color: rgba(220,38,38,.22); }
  .dash-link.logout .ico{ background: rgba(220,38,38,.10); color: #dc2626; }
  .dash-link.logout:hover{ border-color: rgba(220,38,38,.38); background: rgba(220,38,38,.06); }

  .dash-panel{
    background: #fff;
    border: 1px solid rgba(0,39,108,.08);
    border-radius: var(--radius);
    box-shadow: var(--shadow2);
    padding: 16px;
    height: 100%;
  }

  .panel-head{
    display:flex; align-items:flex-start; justify-content:space-between;
    gap: 12px; margin-bottom: 12px;
  }
  .panel-title{ margin:0; font-size: 16px; font-weight: 1000; color: var(--text); }
  .panel-sub{ margin:2px 0 0; font-size: 13px; color: var(--muted); font-weight: 600; }

  /* labels */
  .account-details-form .form-group{ margin-bottom: 14px; }
  .account-details-form .form-group > label,
  .account-details-form label{
    display:block !important;
    position: static !important;
    transform: none !important;
    background: transparent !important;
    padding: 0 !important;
    margin: 0 0 8px 0 !important;
    font-weight: 900 !important;
    color: var(--text) !important;
  }

  /* input */
  .account-details-form .form-control{
    height: var(--field-h) !important;
    border-radius: var(--field-radius) !important;
    border: 1px solid rgba(0,39,108,.18) !important;
    box-shadow: 0 8px 16px rgba(0,39,108,.06) !important;
    padding: 0 var(--field-px) !important;
    font-size: var(--field-font) !important;
    font-weight: 700 !important;
    color: var(--text) !important;
  }
  .account-details-form .form-control:focus{
    border-color: rgba(0,39,108,.65) !important;
    box-shadow: 0 0 0 4px rgba(0,39,108,.12), 0 14px 24px rgba(0,39,108,.10) !important;
    outline: none !important;
  }

  /* password block */
  .pw-block{
    margin-top: 6px;
    border-radius: 18px;
    border: 1px solid rgba(0,39,108,.10);
    background:
      radial-gradient(circle at 18% 20%, rgba(0,39,108,.10), transparent 55%),
      linear-gradient(180deg, rgba(0,39,108,.02), rgba(255,255,255,1));
    padding: 18px;
  }
  .pw-title{
    margin:0 0 14px;
    font-weight: 1000;
    color: var(--text);
    letter-spacing:.2px;
    font-size: 18px;
  }

  .save-btn{
    width: 100%;
    border: 0;
    border-radius: 18px;
    padding: 16px 16px;
    font-weight: 1000;
    color: var(--brand-text);
    background: var(--brand-gradient);
    box-shadow: 0 18px 34px rgba(0,39,108,.18);
  }
  .save-btn:hover{
    transform: translateY(-2px);
    box-shadow: 0 22px 48px rgba(0,39,108,.24);
    filter: brightness(1.04);
  }

  @media (max-width: 991px){
    .axil-dashboard-warp.p-5{ padding: 16px !important; }
    .dash-aside{ position: static; top:auto; margin-bottom: 12px; }
    .dash-top{ flex-direction: column; align-items:flex-start; }
    .user-badge{ width: 100%; justify-content:flex-start; }
  }
</style>

<main class="main-wrapper">
  <div class="axil-breadcrumb-area">
    <div class="container-fluid">
      <div class="row align-items-center">
        <div class="col-lg-6 col-md-8">
          <div class="inner">
            <ul class="axil-breadcrumb">
              <li class="axil-breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
              <li class="separator"></li>
              <li class="axil-breadcrumb-item active" aria-current="page">My Account</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="axil-dashboard-area">
    <div class="container-fluid">
      <div class="axil-dashboard-warp p-5">
        <div class="dash-shell">
          @php
            $fullName = trim((auth()->user()->first_name ?? '').' '.(auth()->user()->last_name ?? ''));
            $short = strtoupper(mb_substr(auth()->user()->first_name ?? 'U', 0, 1));
          @endphp

          <div class="dash-top">
            <div>
              <h5 class="dash-hello">Hello {{ $fullName }}</h5>
              <p class="dash-sub">Update your personal details & change password securely.</p>
            </div>

            <div class="user-badge">
              <div class="user-avatar">{{ $short }}</div>
              <div class="user-meta">
                <div class="user-name">{{ $fullName }}</div>
                <div class="user-tag">Account Details</div>
              </div>
            </div>
          </div>

          <div class="row g-4 p-4">
            <div class="col-xl-3 col-md-4">
              <aside class="dash-aside">
                <div class="dash-nav-title">Navigation</div>
                <nav class="dash-nav">
                  <a class="dash-link" href="{{ route('front.dashboard.index') }}">
                    <span class="ico"><i class="fas fa-th-large"></i></span> Dashboard
                  </a>

                  <a class="dash-link" href="{{ route('front.orders.index') }}">
                    <span class="ico"><i class="fas fa-shopping-basket"></i></span> Orders
                  </a>

                  <a class="dash-link active" href="{{ route('front.account_details.index') }}">
                    <span class="ico"><i class="fas fa-home"></i></span> Account Details
                  </a>

                  <a class="dash-link logout" href="{{ route('logout') }}"
                     onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <span class="ico"><i class="fas fa-sign-out-alt"></i></span> Logout
                  </a>

                  <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                  </form>
                </nav>
              </aside>
            </div>

            <div class="col-xl-9 col-md-8">
              <div class="dash-panel">

                <div class="panel-head">
                  <div>
                    <h4 class="panel-title">Account Details</h4>
                    <p class="panel-sub">Keep your profile updated for faster checkout experience.</p>
                  </div>
                </div>

                <div class="axil-dashboard-account">
                  <form class="account-details-form" method="post"
                        action="{{ route('front.account_details.update',[$user->id])}}"
                        id="ajax_form">
                    @csrf
                    @method('PATCH')

                    <div class="row">
                      <div class="col-lg-4">
                        <div class="form-group">
                          <label>First Name</label>
                          <input type="text" class="form-control" value="{{ $user->first_name}}" name="first_name">
                        </div>
                      </div>

                      <div class="col-lg-4">
                        <div class="form-group">
                          <label>Last Name</label>
                          <input type="text" class="form-control" value="{{ $user->last_name}}" name="last_name">
                        </div>
                      </div>

                      <div class="col-lg-4">
                        <div class="form-group">
                          <label>Username</label>
                          <input type="text" class="form-control" value="{{ $user->username}}" name="username" readonly>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group">
                          <label>E-mail Address</label>
                          <input type="text" class="form-control" value="{{ $user->email}}" name="email">
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group">
                          <label>Mobile Number</label>
                          <input type="text" class="form-control" value="{{ $user->mobile}}" name="mobile">
                        </div>
                      </div>

                      <div class="col-12">
                        <div class="pw-block">
                          <h5 class="pw-title">Password Change</h5>
                            <br>
                          <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" class="form-control" name="old_password" placeholder="Current password">
                          </div>

                          <div class="form-group">
                            <label>New Password</label>
                            <input type="password" class="form-control" name="password" placeholder="New password">
                          </div>

                          <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" class="form-control" name="password_confirmation" placeholder="Confirm new password">
                          </div>

                          <div class="form-group mb--0">
                            <button type="submit" class="save-btn">Save Changes</button>
                          </div>

                        </div>
                      </div>

                    </div>
                  </form>
                </div>

              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</main>

@endsection
