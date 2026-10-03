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
    /* ✅ brand (same as order button) */
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

  /* breadcrumb polish */
  .axil-breadcrumb-area{
    background:
      linear-gradient(90deg, rgba(0,39,108,.10), rgba(3,49,153,.06)),
      #fff;
    border-bottom:1px solid rgba(0,39,108,.08);
    padding: 18px 0;
  }
  .axil-breadcrumb a{
    color: var(--primary) !important;
    font-weight: 800;
    text-decoration: none;
  }
  .axil-breadcrumb-item.active{
    color: var(--text) !important;
    font-weight: 900;
  }

  /* page bg */
  .axil-dashboard-area{
    background:
      radial-gradient(circle at 15% 12%, rgba(0,39,108,.07), transparent 45%),
      radial-gradient(circle at 88% 18%, rgba(3,49,153,.07), transparent 45%),
      linear-gradient(180deg, var(--bg), var(--bg));
    padding: 22px 0 50px;
  }

  /* wrapper card */
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
    font-weight: 1000;
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

    /* ✅ same brand gradient */
    background: var(--brand-gradient);

    display:flex;
    align-items:center;
    justify-content:center;
    color: var(--brand-text);
    font-weight: 1000;
    letter-spacing:.5px;
    box-shadow: 0 12px 20px rgba(0,39,108,.20);
    flex: 0 0 auto;
  }
  .user-meta{ display:flex; flex-direction:column; line-height: 1.1; }
  .user-name{ font-weight: 900; color: var(--text); font-size: 14px; }
  .user-tag{ font-size: 12px; font-weight: 800; color: rgba(0,39,108,.75); }

  /* left nav */
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
    font-weight: 900;
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

  /* ✅ active same brand gradient */
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

  /* right panel */
  .dash-panel{
    background: #fff;
    border: 1px solid rgba(0,39,108,.08);
    border-radius: var(--radius);
    box-shadow: var(--shadow2);
    padding: 16px;
    height: 100%;
  }

  .panel-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap: 12px;
    margin-bottom: 12px;
  }
  .panel-title{
    margin:0;
    font-size: 16px;
    font-weight: 1000;
    color: var(--text);
    letter-spacing:.2px;
  }
  .panel-sub{
    margin:2px 0 0;
    font-size: 13px;
    color: var(--muted);
    font-weight: 600;
  }

  /* premium table */
  .orders-table{
    border: 1px solid rgba(0,39,108,.10);
    border-radius: 16px;
    overflow: hidden;
    background: #fff;
  }
  .orders-table table{ margin:0; }
  .orders-table thead th{
    background: linear-gradient(90deg, rgba(0,39,108,.08), rgba(3,49,153,.06));
    color: var(--text);
    font-weight: 1000;
    border-bottom: 1px solid rgba(0,39,108,.10) !important;
    padding: 12px 12px;
    white-space: nowrap;
  }
  .orders-table tbody td, .orders-table tbody th{
    padding: 12px 12px;
    border-color: rgba(0,39,108,.08) !important;
    vertical-align: middle;
    color: var(--text);
    font-weight: 700;
  }
  .orders-table tbody tr:hover{
    background: rgba(0,39,108,.03);
  }

  /* status badge */
  .status-badge{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 1000;
    border: 1px solid rgba(0,39,108,.12);
    background: rgba(0,39,108,.06);
    color: var(--primary);
    white-space: nowrap;
  }
  .status-badge .dot{
    width:8px; height:8px; border-radius:999px;
    background: var(--primary);
    box-shadow: 0 0 0 3px rgba(0,39,108,.10);
  }
  /* common statuses */
  .status-processing{ background: rgba(245,158,11,.10); border-color: rgba(245,158,11,.22); color: #b45309; }
  .status-processing .dot{ background:#f59e0b; box-shadow:0 0 0 3px rgba(245,158,11,.12); }

  .status-completed{ background: rgba(34,197,94,.10); border-color: rgba(34,197,94,.22); color: #15803d; }
  .status-completed .dot{ background:#22c55e; box-shadow:0 0 0 3px rgba(34,197,94,.12); }

  .status-cancelled{ background: rgba(239,68,68,.10); border-color: rgba(239,68,68,.22); color: #b91c1c; }
  .status-cancelled .dot{ background:#ef4444; box-shadow:0 0 0 3px rgba(239,68,68,.12); }

  .status-pending{ background: rgba(59,130,246,.10); border-color: rgba(59,130,246,.22); color:#1d4ed8; }
  .status-pending .dot{ background:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.12); }

  /* ✅ view button (brand hover) */
  .view-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    padding: 10px 12px;
    border-radius: 14px;
    font-weight: 1000;
    text-decoration:none !important;
    border: 1px solid rgba(0,39,108,.14);
    background: #fff;
    color: var(--primary) !important;
    transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease, background .15s ease, color .15s ease;
    white-space: nowrap;
  }
  .view-btn:hover{
    transform: translateY(-1px);
    box-shadow: 0 14px 24px rgba(0,39,108,.10);
    border-color: rgba(0,39,108,.28);
    background: var(--brand-gradient);
    color: var(--brand-text) !important;
  }
  .view-btn:hover i{ color: var(--brand-text) !important; }

  /* empty state */
  .empty-orders{
    border: 1px dashed rgba(0,39,108,.22);
    border-radius: 16px;
    background: rgba(0,39,108,.03);
    padding: 22px;
    text-align:center;
  }
  .empty-orders h5{
    margin: 0 0 6px;
    font-weight: 1000;
    color: var(--text);
  }
  .empty-orders p{
    margin: 0;
    color: var(--muted);
    font-weight: 600;
  }

  /* mobile: table -> cards */
  @media (max-width: 767px){
    .axil-dashboard-warp.p-5{ padding: 16px !important; }
    .dash-aside{ position: static; top:auto; margin-bottom: 12px; }
    .dash-top{ flex-direction: column; align-items:flex-start; }
    .user-badge{ width: 100%; justify-content:flex-start; }

    .orders-table thead{ display:none; }
    .orders-table tbody tr{
      display:block;
      border-bottom: 1px solid rgba(0,39,108,.08);
      padding: 10px 10px;
    }
    .orders-table tbody th,
    .orders-table tbody td{
      display:flex;
      justify-content:space-between;
      gap:10px;
      padding: 8px 6px;
      border: 0 !important;
    }
    .orders-table tbody th::before,
    .orders-table tbody td::before{
      content: attr(data-label);
      font-weight: 900;
      color: var(--muted);
      flex: 0 0 42%;
    }
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
                            <li class="axil-breadcrumb-item active" aria-current="page">My Account</li>
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
                            <p class="dash-sub">Here are all your recent orders in one place.</p>
                        </div>

                        <div class="user-badge">
                            <div class="user-avatar">{{ $short }}</div>
                            <div class="user-meta">
                                <div class="user-name">{{ $fullName }}</div>
                                <div class="user-tag">Orders</div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 p-4">
                        <!-- Left Nav -->
                        <div class="col-xl-3 col-md-4">
                            <aside class="dash-aside">
                                <div class="dash-nav-title">Navigation</div>

                                <nav class="dash-nav">
                                    <a class="dash-link" href="{{ route('front.dashboard.index') }}">
                                        <span class="ico"><i class="fas fa-th-large"></i></span>
                                        Dashboard
                                    </a>

                                    <a class="dash-link active" href="{{ route('front.orders.index') }}">
                                        <span class="ico"><i class="fas fa-shopping-basket"></i></span>
                                        Orders
                                    </a>

                                    <a class="dash-link" href="{{ route('front.account_details.index') }}">
                                        <span class="ico"><i class="fas fa-home"></i></span>
                                        Account Details
                                    </a>

                                    <a class="dash-link logout" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <span class="ico"><i class="fal fa-sign-out"></i></span>
                                        Logout
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </nav>
                            </aside>
                        </div>

                        <!-- Right Content -->
                        <div class="col-xl-9 col-md-8">
                            <div class="dash-panel">

                                <div class="panel-head">
                                    <div>
                                        <h4 class="panel-title">My Orders</h4>
                                        <p class="panel-sub">Track status, view details & invoice anytime.</p>
                                    </div>
                                </div>

                                @if(isset($items) && $items->count())
                                  <div class="orders-table">
                                      <div class="table-responsive">
                                          <table class="table">
                                              <thead>
                                                  <tr>
                                                      <th scope="col">Order</th>
                                                      <th scope="col">Date</th>
                                                      <th scope="col">Status</th>
                                                      <th scope="col">Total</th>
                                                      <th scope="col">Actions</th>
                                                  </tr>
                                              </thead>

                                              <tbody>
                                                  @foreach($items as $item)
                                                    @php
                                                      $st = strtolower(trim($item->status ?? ''));
                                                      $statusClass = 'status-badge';

                                                      if(str_contains($st, 'process')) $statusClass .= ' status-processing';
                                                      elseif(str_contains($st, 'complete') || str_contains($st, 'delivered')) $statusClass .= ' status-completed';
                                                      elseif(str_contains($st, 'cancel')) $statusClass .= ' status-cancelled';
                                                      elseif(str_contains($st, 'pending')) $statusClass .= ' status-pending';
                                                    @endphp

                                                    <tr>
                                                        <th scope="row" data-label="Order">#{{ $item->invoice_no }}</th>
                                                        <td data-label="Date">{{ dateFormate($item->date) }}</td>

                                                        <td data-label="Status">
                                                          <span class="{{ $statusClass }}">
                                                            <span class="dot"></span>
                                                            {{ $item->status }}
                                                          </span>
                                                        </td>

                                                        <td data-label="Total">
                                                          {{ priceFormate($item->final_amount) }}
                                                          <span style="color:var(--muted);font-weight:700;">for {{ $item->details->count() }} items</span>
                                                        </td>

                                                        <td data-label="Actions">
                                                          <a href="{{ route('front.orders.show', [$item->id]) }}" class="view-btn">
                                                            <i class="fas fa-eye"></i> View
                                                          </a>
                                                        </td>
                                                    </tr>
                                                  @endforeach
                                              </tbody>
                                          </table>
                                      </div>
                                  </div>
                                @else
                                  <div class="empty-orders">
                                      <h5>No orders found</h5>
                                      <p>You haven’t placed any order yet. Start shopping and your orders will appear here.</p>
                                  </div>
                                @endif

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
