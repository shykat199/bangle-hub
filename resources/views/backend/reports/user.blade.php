@extends('backend.app')
@section('content')

@php
  $isWorker = auth()->user()->hasRole('worker');
  $selfId   = auth()->id();
@endphp

<style>
  /* =========================================
     PREMIUM STYLING & NO-SCROLL TABLE
     ========================================= */
  .card { 
      border-radius: 12px; 
      box-shadow: 0 4px 20px rgba(0,0,0,0.04); 
      border: 1px solid rgba(0,0,0,0.05); 
  }
  .toolbar-sticky{ 
      position:sticky; top:0; z-index:6; background:#fff; 
      padding:.75rem 0; border-bottom:1px solid #f0f0f0; 
  }
  
  /* Filter Buttons */
  .btn-preset {
      border-radius: 20px; padding: 5px 15px; font-size: 12px; font-weight: 600;
      border: 1px solid #e2e8f0; background: #f8fafc; color: #475569; transition: all 0.2s;
  }
  .btn-preset:hover { background: #e2e8f0; color: #0f172a; }

  /* Report Wrap & Table */
  .userReport-wrap{ 
      position:relative; min-height: 300px; border: 1px solid #e2e8f0; 
      border-radius: 10px; overflow: hidden; background: #fff;
  }
  
  /* ✅ NO LEFT-RIGHT SCROLL: Force table to fit */
  .userReport .table-responsive { 
      overflow-x: hidden !important; /* স্ক্রল বন্ধ */
  }
  .userReport table {
      width: 100%; table-layout: auto; margin-bottom: 0;
  }
  .userReport th, .userReport td {
      padding: 10px 4px !important; /* প্যাডিং কমানো হয়েছে */
      font-size: 11px !important; /* ছোট ফন্ট সাইজ */
      text-align: center; vertical-align: middle;
      white-space: normal !important; /* টেক্সট যাতে নিচে নিচে ভাঙে */
      word-wrap: break-word; line-height: 1.3;
  }
  .userReport th {
      background-color: #f8fafc !important; color: #475569 !important;
      font-weight: 700; text-transform: uppercase; border-bottom: 2px solid #e2e8f0;
  }
  .userReport tbody tr { transition: background 0.2s; }
  .userReport tbody tr:hover { background-color: #f1f5f9 !important; }

  /* Loading & Toast */
  .loading-overlay{ 
      position:absolute; inset:0; background:rgba(255,255,255,.7); 
      display:none; align-items:center; justify-content:center; z-index:5; backdrop-filter: blur(2px); 
  }
  .spinner{ width:30px;height:30px;border:3px solid #0ea5e9;border-top-color:transparent;border-radius:50%; animation:spin .7s linear infinite; }
  @keyframes spin{ to{ transform:rotate(360deg); } }
  .toast-mini{ position:fixed; right:12px; bottom:12px; background:#212529; color:#fff; padding:10px 12px; border-radius:8px; z-index:9999; font-size:13px; box-shadow:0 8px 20px rgba(0,0,0,.2); }

  /* Print tweaks */
  @media print{
    nav, .no-print, .pagination { display:none !important; }
    .card { border:none !important; box-shadow:none !important; }
    .print-header{ display:block !important; margin-bottom:8px; }
  }
</style>

<div class="row">
  <div class="col-12">
    <div class="page-title-box">
      <div class="page-title-right">
        <ol class="breadcrumb m-0">
          <li class="breadcrumb-item"><a href="javascript:void(0)">SIS</a></li>
          <li class="breadcrumb-item"><a href="javascript:void(0)">CRM</a></li>
          <li class="breadcrumb-item active">User report</li>
        </ol>
      </div>
      <h4 class="page-title">User Report</h4>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">

        {{-- PRINT HEADER (visible only on print) --}}
        <div class="print-header" style="display:none">
          <h5 class="mb-0">User Report</h5>
          <small>
            @php
              $fromTxt = request('from') ?: '—';
              $toTxt   = request('to') ?: '—';
              $assignName = '';
              if(!$isWorker && request('assign')){
                $u = $users->firstWhere('id', (int)request('assign'));
                $assignName = $u ? full_name($u) : '';
              } elseif($isWorker) {
                $assignName = full_name(auth()->user());
              }
            @endphp
            Date Range: {{ $fromTxt }} to {{ $toTxt }} {{ $assignName ? ' | Assign: '.$assignName : '' }}
          </small>
        </div>

        {{-- Filters --}}
        <div class="row no-print">
          <div class="col-12 toolbar-sticky">
            <form class="row gy-2 gx-2 align-items-end" id="user_report_filters" onsubmit="return false">

              {{-- Assign By: Admin only --}}
              @if(!$isWorker)
                <div class="col-md-4">
                  <label class="form-label fw-bold text-muted" style="font-size: 12px;">Assign By</label>
                  <select class="form-select shadow-sm" id="assign" name="assign">
                    <option value="">Choose...</option>
                    @foreach($users as $user)
                      <option value="{{ $user->id }}" {{ (string)request('assign')===(string)$user->id ? 'selected':'' }}>
                        {{ full_name($user) }}
                      </option>
                    @endforeach
                  </select>
                </div>
              @else
                {{-- Worker: hide field, force own id --}}
                <input type="hidden" id="assign" name="assign" value="{{ $selfId }}">
              @endif

              <div class="col-md-4">
                <label class="form-label fw-bold text-muted" style="font-size: 12px;">From</label>
                <input type="date" name="from" id="from" class="form-control shadow-sm" value="{{ request('from') }}">
              </div>

              <div class="col-md-4">
                <label class="form-label fw-bold text-muted" style="font-size: 12px;">To</label>
                <input type="date" name="to" id="to" class="form-control shadow-sm" value="{{ request('to') }}">
              </div>

              @php
                $exportBase = \Illuminate\Support\Facades\Route::has('admin.report.user.export')
                    ? route('admin.report.user.export')
                    : null;
              @endphp

              <div class="col-12 d-flex gap-2 mt-3">
                <button type="button" class="btn btn-primary btn-sm px-3 fw-bold shadow-sm" id="btnApply">Apply Filter</button>
                <button type="button" class="btn btn-light btn-sm px-3 fw-bold border shadow-sm" id="btnReset">Reset</button>
                <div class="ms-auto d-flex gap-2">
                  @if($exportBase)
                    <a href="{{ $exportBase }}" class="btn btn-success btn-sm px-3 fw-bold shadow-sm" id="btnExport"><i class="uil-export"></i> Export</a>
                  @endif
                  <button type="button" class="btn btn-dark btn-sm px-3 fw-bold shadow-sm" id="btnPrint"><i class="uil-print"></i> Print</button>
                </div>
              </div>

              <div class="col-12 mt-3">
                <div class="d-flex flex-wrap gap-2">
                  <button type="button" class="btn-preset" data-preset="today">Today</button>
                  <button type="button" class="btn-preset" data-preset="week">This Week</button>
                  <button type="button" class="btn-preset" data-preset="month">This Month</button>
                </div>
              </div>
            </form>
          </div>
        </div>

        {{-- Report Container --}}
        <div class="row mt-4">
          <div class="col-12">
            <div class="userReport-wrap shadow-sm">
              <div class="loading-overlay" id="loadingOverlay"><span class="spinner"></span></div>
              <div class="userReport">
                {{-- AJAX content goes here --}}
              </div>
            </div>
          </div>
        </div>

        <div id="toast" class="toast-mini" style="display:none"></div>

      </div>
    </div>
  </div>
</div>
@endsection

@push('js')
<script>
(function(){
  const IS_WORKER = {{ $isWorker ? 'true' : 'false' }};
  const SELF_ID   = {{ (int)$selfId }};

  const $from   = document.querySelector('input[name="from"]');
  const $to     = document.querySelector('input[name="to"]');
  const $assign = document.querySelector('#assign'); // may be hidden for worker
  const $overlay= document.getElementById('loadingOverlay');
  const $wrap   = document.querySelector('div.userReport');
  const $toast  = document.getElementById('toast');
  const exportBtn = document.getElementById('btnExport');

  // If worker, hard-lock assign value & disable it
  if (IS_WORKER && $assign) {
    $assign.value = SELF_ID;
    if ($assign.tagName === 'SELECT') {
      $assign.setAttribute('disabled','disabled');
    }
  }

  // ✅ FIXED TIMEZONE ISSUE (Local Time Output instead of UTC)
  const fmt = (d) => {
      const year = d.getFullYear();
      const month = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
  };

  function showToast(msg, type='dark'){
    if(!$toast) return;
    $toast.textContent = msg;
    $toast.style.background = (type==='danger' ? '#dc3545' : type==='success' ? '#198754' : '#212529');
    $toast.style.display = 'block';
    setTimeout(()=> $toast.style.display='none', 2200);
  }

  function setPreset(preset){
    const today = new Date();
    let start, end;
    if(preset==='today'){
      start = end = today;
    }else if(preset==='week'){
      const day = today.getDay(); // 0 Sun
      const diffToMon = (day===0 ? 6 : day-1);
      start = new Date(today); start.setDate(today.getDate() - diffToMon);
      end   = new Date(start); end.setDate(start.getDate() + 6);
      if(end > today) end = today;
    }else if(preset==='month'){
      start = new Date(today.getFullYear(), today.getMonth(), 1);
      end   = new Date(today.getFullYear(), today.getMonth()+1, 0);
      if(end > today) end = today;
    }
    if($from && $to){
      $from.value = fmt(start);
      $to.value   = fmt(end);
      fetchReport();
    }
  }

  function validRange(){
    if(!$from || !$to) return true;
    if(!$from.value || !$to.value) return true;
    return (new Date($from.value) <= new Date($to.value));
  }

  let debounceTimer;
  function debounceFetch(){
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(fetchReport, 300);
  }

  function toggleLoading(show=true){
    if(!$overlay) return;
    $overlay.style.display = show ? 'flex' : 'none';
  }

  function syncUrl(){
    const params = new URLSearchParams(window.location.search);
    const assignVal = IS_WORKER ? SELF_ID : ($assign?.value || '');
    assignVal ? params.set('assign', assignVal) : params.delete('assign');
    ($from.value) ? params.set('from', $from.value) : params.delete('from');
    ($to.value)   ? params.set('to',   $to.value)   : params.delete('to');
    const newUrl = `${window.location.pathname}?${params.toString()}`;
    window.history.replaceState(null,'', newUrl);
  }

  function fetchReport(){
    if(!validRange()){
      showToast('From date cannot be after To date', 'danger');
      return;
    }
    syncUrl();
    toggleLoading(true);

    const startDate = $from?.value || '';
    const endDate   = $to?.value   || '';
    const assignUser= IS_WORKER ? SELF_ID : ($assign?.value || '');

    fetch("{{ route('admin.report.user') }}" + `?startDate=${encodeURIComponent(startDate)}&endDate=${encodeURIComponent(endDate)}&assignUser=${encodeURIComponent(assignUser)}`, {
      method: "GET",
      headers: { "X-Requested-With": "XMLHttpRequest" }
    })
    .then(async r => {
        const text = await r.text();
        try {
            const res = JSON.parse(text);
            return res.html !== undefined ? res.html : text;
        } catch(e) {
            return text;
        }
    })
    .then(html => {
        if(html && html.trim() !== '') {
            $wrap.innerHTML = html;
        } else {
            $wrap.innerHTML = '<div class="text-center text-muted py-5"><i class="mdi mdi-inbox-outline display-4"></i><p class="mt-2">No data found for the selected filter.</p></div>';
        }
    })
    .catch(err => {
      console.error("Fetch Error:", err);
      $wrap.innerHTML = '<div class="text-center text-danger py-4">Failed to load report. Please check console/network.</div>';
    })
    .finally(()=> toggleLoading(false));
  }

  document.getElementById('btnApply')?.addEventListener('click', fetchReport);
  document.getElementById('btnReset')?.addEventListener('click', ()=>{
    if(!$assign) return;
    if(!IS_WORKER){ $assign.value = ''; }
    if($from) $from.value = '';
    if($to)   $to.value   = '';
    fetchReport();
  });

  document.querySelectorAll('[data-preset]').forEach(btn=>{
    btn.addEventListener('click', ()=> setPreset(btn.dataset.preset));
  });

  $from?.addEventListener('change', debounceFetch);
  $to?.addEventListener('change', debounceFetch);
  if(!IS_WORKER){ $assign?.addEventListener('change', debounceFetch); }

  document.getElementById('btnPrint')?.addEventListener('click', ()=> window.print());

  if (exportBtn) {
    exportBtn.addEventListener('click', (e)=>{
      e.preventDefault();
      const base = exportBtn.getAttribute('href');
      const params = new URLSearchParams({
        startDate: $from?.value || '',
        endDate:   $to?.value || '',
        assignUser: IS_WORKER ? SELF_ID : ($assign?.value || '')
      });
      window.location.href = base + '?' + params.toString();
    });
  }

  // ✅ DOM লোড হওয়ার পর ডাটা কল
  document.addEventListener('DOMContentLoaded', fetchReport);

})();
</script>
@endpush