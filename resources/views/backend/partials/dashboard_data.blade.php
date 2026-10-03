{{-- ===============================
  Beautiful Stat Cards (Premium)
  Drop-in Replacement for your block
================================= --}}
@push('css')
<style>
  :root{
    --card-radius:16px; --card-shadow:0 8px 24px rgba(0,0,0,.08);
  }
  .stat-card{
    position:relative; border:0; border-radius:var(--card-radius);
    box-shadow:var(--card-shadow); overflow:hidden;
    transition:transform .18s ease, box-shadow .18s ease;
  }
  .stat-card:hover{ transform:translateY(-2px); box-shadow:0 10px 30px rgba(0,0,0,.12); }
  .stat-card .icon-wrap{
    width:44px; height:44px; border-radius:12px;
    background:rgba(255,255,255,.18);
    display:flex; align-items:center; justify-content:center;
  }
  .stat-card::after{
    content:""; position:absolute; inset:0; pointer-events:none;
    background: radial-gradient(600px circle at var(--x,50%) var(--y,0%), rgba(255,255,255,.18), transparent 40%);
    opacity:0; transition:opacity .25s ease;
  }
  .stat-card:hover::after{ opacity:1; }
  .stat-card .title{ font-size:.95rem; letter-spacing:.2px; margin-bottom:.25rem; }
  .stat-card .value{ font-weight:800; line-height:1; font-size:clamp(1.6rem,2.6vw,2.2rem); }
  .bg-teal   { background:linear-gradient(135deg,#0EA3AC 0%,#23C9B0 100%); }
  .bg-amber  { background:linear-gradient(135deg,#F5BD08 0%,#FFD24D 100%); }
  .bg-navy   { background:linear-gradient(135deg,#133F5C 0%,#1B567C 100%); }
  .bg-red    { background:linear-gradient(135deg,#C9213B 0%,#EB5F5E 100%); }
  .text-on-dark *{ color:#fff !important; }
  .text-on-amber *{ color:#1f2937 !important; } /* dark text on amber */
  .countup[data-money="1"]::before{ content:"৳ "; opacity:.95; }
</style>
@endpush

<div class="row g-3">
  {{-- ✅ Total Products --}}
  <div class="col-md-6 col-xl-3">
    <div class="card stat-card bg-teal text-on-dark" onmousemove="cardGlow(event,this)">
      <a href="{{ url('admin/products') }}" class="text-decoration-none">
        <div class="card-body py-3">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="title">Total Products</div>
              <div class="value countup" data-value="{{ (int)$products }}" data-money="0">{{ number_format($products,0) }}</div>
            </div>
            <div class="icon-wrap"><i class="uil uil-box fs-4 text-white"></i></div>
          </div>
        </div>
      </a>
    </div>
  </div>

  {{-- ✅ Total Employee --}}
  <div class="col-md-6 col-xl-3">
    <div class="card stat-card bg-amber text-on-amber" onmousemove="cardGlow(event,this)">
      <a href="{{ url('admin/users') }}" class="text-decoration-none">
        <div class="card-body py-3">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="title">Total Employee</div>
              <div class="value countup" data-value="{{ (int)$users }}" data-money="0">{{ number_format($users,0) }}</div>
            </div>
            <div class="icon-wrap"><i class="uil uil-users-alt fs-4"></i></div>
          </div>
        </div>
      </a>
    </div>
  </div>

  {{-- ✅ Site Info / Settings (Admin only) --}}
  {{-- Spatie হলে: @role('admin') ... @endrole --}}
  {{-- অথবা: @if(auth()->user()->hasRole('admin')) --}}
  @if(auth()->user()->role == 'admin')
  <div class="col-md-6 col-xl-3">
    <div class="card stat-card bg-navy text-on-dark" onmousemove="cardGlow(event,this)">
      <a href="{{ route('admin.settings.index') }}" class="text-decoration-none">
        <div class="card-body py-3">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="title">Site Info</div>
              <div class="value">Settings</div>
            </div>
            <div class="icon-wrap"><i class="uil uil-setting fs-4 text-white"></i></div>
          </div>
        </div>
      </a>
    </div>
  </div>
  @endif

  {{-- ✅ Report (all) --}}
  <div class="col-md-6 col-xl-3">
    <div class="card stat-card bg-red text-on-dark" onmousemove="cardGlow(event,this)">
      <a href="{{ url('admin/order-report') }}" class="text-decoration-none">
        <div class="card-body py-3">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="title">Report</div>
              <div class="value">View Report</div>
            </div>
            <div class="icon-wrap"><i class="uil uil-chart-line fs-4 text-white"></i></div>
          </div>
        </div>
      </a>
    </div>
  </div>

  {{-- ✅ Today Sell (optional – hidden by default) --}}
  <div class="col-md-6 col-xl-3 d-none">
    <div class="card stat-card bg-red text-on-dark" onmousemove="cardGlow(event,this)">
      <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="title">Today Sell</div>
            <div class="value countup" data-value="{{ (float)$today_sell }}" data-money="1">{{ number_format($today_sell,2) }}</div>
          </div>
          <div class="icon-wrap"><i class="uil uil-money-bill fs-4 text-white"></i></div>
        </div>
      </div>
    </div>
  </div>

  {{-- ✅ This Month Revenue (optional; show if you want) --}}
  <div class="col-md-6 col-xl-3 d-none">
    <div class="card stat-card bg-amber text-on-amber" onmousemove="cardGlow(event,this)">
      <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="title">This Month Revenue</div>
            <div class="value countup" data-value="{{ (float)$current_month_sell }}" data-money="1">{{ number_format($current_month_sell,2) }}</div>
          </div>
          <div class="icon-wrap"><i class="uil uil-calendar-alt fs-4"></i></div>
        </div>
      </div>
    </div>
  </div>

  {{-- ✅ Last Month Revenue (optional; show if you want) --}}
  <div class="col-md-6 col-xl-3 d-none">
    <div class="card stat-card bg-amber text-on-amber" onmousemove="cardGlow(event,this)">
      <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="title">Last Month Revenue</div>
            <div class="value countup" data-value="{{ (float)$prev_month_sell }}" data-money="1">{{ number_format($prev_month_sell,2) }}</div>
          </div>
          <div class="icon-wrap"><i class="uil uil-schedule fs-4"></i></div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('js')
<script>
  // hover glow
  function cardGlow(e, card){
    const r = card.getBoundingClientRect();
    card.style.setProperty('--x', (e.clientX - r.left) + 'px');
    card.style.setProperty('--y', (e.clientY - r.top)  + 'px');
  }

  // simple count-up
  function animateCount(el, to, money=false, duration=800){
    const start = 0;
    const startTime = performance.now();
    const isInt = Number.isInteger(to);
    const nf = new Intl.NumberFormat('en-BD', { maximumFractionDigits: isInt? 0 : 2 });

    function frame(now){
      const p = Math.min((now - startTime)/duration, 1);
      const val = start + (to - start)*p;
      el.textContent = (money ? '' : '') + nf.format(isInt ? Math.round(val) : val);
      if(p<1) requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }

  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.countup').forEach(el=>{
      const to = parseFloat(el.getAttribute('data-value') || '0');
      const money = el.getAttribute('data-money') === '1';
      animateCount(el, to, money);
    });
  });
</script>
@endpush
