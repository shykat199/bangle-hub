@extends('frontend.app')
@section('content')

@php
  use App\Models\Information;

  $information   = Information::first();

  // ✅ same as "Order করুন" button logic
  $brandGradient = $information->gradient_code ?? 'linear-gradient(90deg,#0d6efd,#00276C)';
  $brandText     = $information->primary_color ?? '#ffffff';
@endphp

<style>
  :root{
    /* ✅ brand */
    --brand-gradient: {!! $brandGradient !!};
    --brand-text: {{ $brandText }};

    --primary:#00276C;
    --primary2:#033199;
    --bg:#f5f7ff;
    --card:#ffffff;
    --text:#0f172a;
    --muted:#64748b;
    --border:rgba(2,6,23,.10);
    --shadow:0 14px 40px rgba(0,39,108,.10);
    --shadow2:0 10px 24px rgba(0,39,108,.14);
    --radius:18px;
  }

  /* page bg */
  .axil-dashboard-area{
    background:
      radial-gradient(circle at 15% 12%, rgba(0,39,108,.07), transparent 45%),
      radial-gradient(circle at 88% 18%, rgba(3,49,153,.07), transparent 45%),
      linear-gradient(180deg, var(--bg), var(--bg));
    padding: 22px 0 50px;
  }

  /* breadcrumb area polish */
  .axil-breadcrumb-area{
    background:
      linear-gradient(90deg, rgba(0,39,108,.10), rgba(3,49,153,.06)),
      #fff;
    border-bottom:1px solid rgba(0,39,108,.08);
    padding: 18px 0;
  }
  .axil-breadcrumb a{
    color: var(--primary) !important;
    font-weight: 700;
    text-decoration: none;
  }
  .axil-breadcrumb-item.active{
    color: var(--text) !important;
    font-weight: 800;
  }

  /* container card */
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
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap: 14px;
  }
  .dash-hello{
    margin:0;
    font-weight: 900;
    color: var(--text);
    letter-spacing: .2px;
  }
  .dash-sub{
    margin: 2px 0 0;
    color: var(--muted);
    font-weight: 600;
    font-size: 14px;
  }

  .user-badge{
    display:flex;
    align-items:center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 999px;
    background: #fff;
    border: 1px solid rgba(0,39,108,.10);
    box-shadow: 0 10px 20px rgba(0,39,108,.08);
    white-space: nowrap;
  }
  .user-avatar{
    width: 36px;
    height: 36px;
    border-radius: 999px;

    /* ✅ brand */
    background: var(--brand-gradient);

    display:flex;
    align-items:center;
    justify-content:center;
    color: var(--brand-text);
    font-weight: 900;
    letter-spacing:.5px;
    box-shadow: 0 12px 20px rgba(0,39,108,.20);
    flex: 0 0 auto;
  }
  .user-meta{
    display:flex;
    flex-direction:column;
    line-height: 1.1;
  }
  .user-name{
    font-weight: 900;
    color: var(--text);
    font-size: 14px;
  }
  .user-tag{
    font-size: 12px;
    font-weight: 700;
    color: rgba(0,39,108,.75);
  }

  /* left sidebar card */
  .dash-aside{
    background: #fff;
    border: 1px solid rgba(0,39,108,.08);
    border-radius: var(--radius);
    box-shadow: var(--shadow2);
    padding: 14px;
    position: sticky;
    top: 110px;
  }

  .dash-nav-title{
    font-weight: 900;
    color: var(--text);
    margin: 2px 4px 10px;
    font-size: 14px;
    letter-spacing:.2px;
  }

  .dash-nav{
    display:flex;
    flex-direction:column;
    gap: 10px;
  }

  .dash-link{
    display:flex;
    align-items:center;
    gap: 10px;
    padding: 12px 12px;
    border-radius: 14px;
    border: 1px solid rgba(0,39,108,.08);
    background: #fff;
    color: var(--text) !important;
    font-weight: 800;
    text-decoration:none !important;
    transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease, background .15s ease;
  }

  .dash-link .ico{
    width: 36px;
    height: 36px;
    border-radius: 12px;
    display:flex;
    align-items:center;
    justify-content:center;
    background: rgba(0,39,108,.08);
    color: var(--primary);
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
  .dash-link:hover .ico{
    background: rgba(0,39,108,.12);
  }

  /* ✅ active = brand gradient */
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
  .dash-link.active i{
    color: var(--brand-text) !important;
  }

  .dash-link.logout{
    border-color: rgba(220,38,38,.22);
  }
  .dash-link.logout .ico{
    background: rgba(220,38,38,.10);
    color: #dc2626;
  }
  .dash-link.logout:hover{
    border-color: rgba(220,38,38,.38);
    background: rgba(220,38,38,.06);
  }

  /* ✅ FORCE logout icon render (FA4/FA5/FA6 safe) */
  .dash-link.logout .ico i{
    display:block !important;
    font-family: "Font Awesome 6 Free","Font Awesome 5 Free","FontAwesome" !important;
    font-weight: 900 !important;
    line-height: 1 !important;
  }

  /* right panel */
  .dash-panel{
    background: #fff;
    border: 1px solid rgba(0,39,108,.08);
    border-radius: var(--radius);
    box-shadow: var(--shadow2);
    padding: 18px;
    height: 100%;
  }

  .overview-card{
    border-radius: 18px;
    border: 1px solid rgba(0,39,108,.10);
    background:
      radial-gradient(circle at 18% 20%, rgba(0,39,108,.10), transparent 55%),
      linear-gradient(180deg, rgba(0,39,108,.03), rgba(255,255,255,1));
    padding: 18px;
  }

  .welcome-text{
    font-size: 18px;
    font-weight: 1000;
    color: var(--text);
    margin-bottom: 6px;
  }
  .welcome-text .hi{ color: var(--primary); }

  .overview-card p{
    margin: 0;
    color: var(--muted);
    font-weight: 600;
    line-height: 1.6;
  }

  .quick-actions{
    margin-top: 14px;
    display:flex;
    gap: 10px;
    flex-wrap: wrap;
  }
  .qa-btn{
    display:inline-flex;
    align-items:center;
    gap: 8px;
    padding: 10px 12px;
    border-radius: 14px;
    border: 1px solid rgba(0,39,108,.12);
    background: #fff;
    text-decoration:none !important;
    color: var(--primary) !important;
    font-weight: 900;
    transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease, background .15s ease, color .15s ease;
  }
  .qa-btn:hover{
    transform: translateY(-1px);
    box-shadow: 0 14px 24px rgba(0,39,108,.10);
    border-color: rgba(0,39,108,.22);
    background: rgba(0,39,108,.03);
  }

  /* ✅ primary button = brand gradient */
  .qa-btn.primary{
    background: var(--brand-gradient);
    color: var(--brand-text) !important;
    border-color: rgba(255,255,255,.20);
    box-shadow: 0 18px 34px rgba(0,39,108,.18);
  }
  .qa-btn.primary:hover{
    background: var(--brand-gradient);
    filter: brightness(1.04);
  }
  .qa-btn.primary i{ color: var(--brand-text) !important; }

  /* responsive */
  @media (max-width: 991px){
    .dash-aside{ position: static; top:auto; }
    .axil-dashboard-warp.p-5{ padding: 16px !important; }
    .dash-top{ flex-direction: column; align-items:flex-start; }
    .user-badge{ width: 100%; justify-content:flex-start; }
  }
</style>

<main class="main-wrapper">
  <!-- Start Breadcrumb Area -->
  <div class="axil-breadcrumb-area">
    <div class="container-fluid">
      <div class="row align-items-center">
        <div class="col-lg-6 col-md-8">
          <div class="inner">
            <ul class="axil-breadcrumb">
              <li class="axil-breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
              <li class="separator"></li>
              <li class="axil-breadcrumb-item active" aria-current="page">My Dashboard</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- End Breadcrumb Area -->

  <!-- Start My Account Area -->
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
              <p class="dash-sub">Welcome back! Manage your orders & account details easily.</p>
            </div>

            <div class="user-badge">
              <div class="user-avatar">{{ $short }}</div>
              <div class="user-meta">
                <div class="user-name">{{ $fullName }}</div>
                <div class="user-tag">My Account</div>
              </div>
            </div>
          </div>

          <div class="row g-4 p-4">
            {{-- left nav --}}
            <div class="col-xl-3 col-md-4">
              <aside class="dash-aside">
                <div class="dash-nav-title">Navigation</div>

                <nav class="dash-nav">
                  <a class="dash-link active" href="{{ route('front.dashboard.index') }}">
                    <span class="ico"><i class="fas fa-th-large"></i></span>
                    Dashboard
                  </a>

                  <a class="dash-link" href="{{ route('front.orders.index') }}">
                    <span class="ico"><i class="fas fa-shopping-basket"></i></span>
                    Orders
                  </a>

                  <a class="dash-link" href="{{ route('front.account_details.index') }}">
                    <span class="ico"><i class="fas fa-home"></i></span>
                    Account Details
                  </a>

                  {{-- ✅ FIXED Logout --}}
                  <a class="dash-link logout" href="{{ route('logout') }}"
                     onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <span class="ico">
                      <i class="fa fa-sign-out fas fa-sign-out-alt fa-right-from-bracket"></i>
                    </span>
                    Logout
                  </a>

                  <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                  </form>
                </nav>
              </aside>
            </div>

            {{-- right content --}}
            <div class="col-xl-9 col-md-8">
              <div class="dash-panel">
                <div class="overview-card">
                  <div class="welcome-text">
                    <span class="hi">Hello</span> {{ auth()->user()->first_name }} 👋
                  </div>

                  <p>
                    From your account dashboard you can view your recent orders,
                    manage your shipping and billing addresses, and edit your password
                    and account details.
                  </p>

                  <div class="quick-actions">
                    <a class="qa-btn primary" href="{{ route('front.orders.index') }}">
                      <i class="fas fa-shopping-basket"></i> View Orders
                    </a>
                    <a class="qa-btn" href="{{ route('front.account_details.index') }}">
                      <i class="fas fa-user-cog"></i> Edit Profile
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>

      </div>
    </div>
  </div>
  <!-- End My Account Area -->
</main>

@endsection
