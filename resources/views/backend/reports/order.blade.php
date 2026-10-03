@extends('backend.app')
@section('content')

<style>
  /* Print tweaks */
  @media print{
    nav, .no-print, .pagination{ display:none !important; }
    .card{ border:none !important; box-shadow:none !important; }
    .print-header{ display:block !important; margin-bottom:8px; }
    table tbody td{ font-size:12px !important; }
  }

  /* Sticky actions */
  .toolbar-sticky{
    position: sticky; top: 0; z-index: 6; background: #fff; padding: .5rem 0;
    border-bottom: 1px solid #f0f0f0;
  }

  /* Mobile responsive table */
  @media (max-width:576px){
    .table-responsive{ border:0; }
    table.table thead{ display:none; }
    table.table tbody tr{
      display:block; margin-bottom:10px; border:1px solid #eee; border-radius:10px; padding:10px;
    }
    table.table tbody td{
      display:flex; justify-content:space-between; gap:10px; border:0 !important; padding:.25rem 0 !important;
      font-size:13px !important;
    }
    table.table tbody td::before{
      content: attr(data-label);
      font-weight:600; color:#111;
    }
  }

  .text-truncate-2{
    display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
  }
  .nowrap{ white-space:nowrap; }
</style>

<div class="row">
  <div class="col-12">
    <div class="page-title-box">
      <div class="page-title-right">
        <ol class="breadcrumb m-0">
          <li class="breadcrumb-item"><a href="#">SIS</a></li>
          <li class="breadcrumb-item"><a href="#">CRM</a></li>
          <li class="breadcrumb-item active">Order report</li>
        </ol>
      </div>
      <h4 class="page-title">Order Report</h4>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">

        {{-- PRINT HEADER --}}
        <div class="print-header" style="display:none">
          <h5 class="mb-0">Order Report</h5>
          <small>
            @php
              $fromTxt = request('from') ?: '—';
              $toTxt   = request('to') ?: '—';
            @endphp
            Date Range: {{ $fromTxt }} to {{ $toTxt }}
          </small>
        </div>

        {{-- FILTERS --}}
        <div class="row mb-2 no-print">
          <div class="col-12 toolbar-sticky">
            <form class="row gy-2 gx-2 align-items-end" method="GET" action="{{ route('admin.report.order.search') }}">
              <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="search" class="form-control" name="query" placeholder="Search..." value="{{ request('query') }}">
              </div>

              <div class="col-md-4">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                  <option value="">Status...</option>
                  @foreach(getOrderStatus() as $key=>$value)
                    <option value="{{ $key }}" {{ (string)request('status')===(string)$key ? 'selected' : '' }}>{{ $value }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label">Assign By</label>
                <select class="form-select" name="assign">
                  <option value="">Assign By...</option>
                  @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ (string)request('assign')===(string)$user->id ? 'selected':'' }}>
                      {{ full_name($user) }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
              </div>

              <div class="col-md-4">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
              </div>

              <div class="col-md-4">
                <label class="form-label">Courier</label>
                <select class="form-select" name="courier">
                  <option value="">Choose...</option>
                  @foreach($couriers as $courier)
                    <option value="{{ $courier->id }}" {{ (string)request('courier')===(string)$courier->id ? 'selected' : '' }}>
                      {{ $courier->name }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary">Submit</button>
              </div>
              <div class="col-auto">
                <a href="{{ route('admin.report.order') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
              </div>

              <div class="col-auto ms-auto">
                <a class="btn btn-dark btn-sm"
                   href="{{ route('admin.report.order.export', [
                        'query'=>request('query'),
                        'status'=>request('status'),
                        'assign'=>request('assign'),
                        'from'=>request('from'),
                        'to'=>request('to'),
                        'courier'=>request('courier')
                   ]) }}">
                  Export
                </a>
                <button type="button" class="btn btn-info btn-sm print-btn">Print</button>
              </div>
            </form>
          </div>
        </div>

        {{-- TABLE --}}
        <div class="row">
          <div class="col-sm-12">
            <div class="table-responsive">
              @php $total = 0; $seenOrders = []; @endphp
              <table class="table table-sm align-middle table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="nowrap" style="font-size:11px;">Order ID</th>
                    <th class="nowrap" style="font-size:11px;">Invoice No</th>
                    <th style="font-size:11px;">Customer</th>
                    <th class="nowrap" style="font-size:11px;">Phone</th>
                    <th style="font-size:11px;">Address</th>
                    <th style="font-size:11px;">Product</th>
                    <th class="nowrap" style="font-size:11px;">Qty</th>
                    <th style="font-size:11px;">Courier</th>
                    <th style="font-size:11px;">Courier ID</th>
                    <th style="font-size:11px;">Assigned To</th>
                    <th class="nowrap" style="font-size:11px;">Work Date</th>
                    <th class="nowrap" style="font-size:11px;">Total</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($details as $item)
                    @php
                      $unit = $item->unit_price ?? 0;
                      $qty  = $item->quantity ?? 0;

                      // Add the order's delivery charge into the Total once per
                      // order (on its first line) so delivery is included without
                      // being double-counted across multiple product lines.
                      $oid = optional($item->order)->id;
                      $shipping = 0;
                      if ($oid && !in_array($oid, $seenOrders)) {
                          $shipping = (float) (optional($item->order)->shipping_charge ?? 0);
                          $seenOrders[] = $oid;
                      }
                      $row_total = ($unit * $qty) + $shipping;
                      $total += $row_total;

                      $order = $item->order;
                      $assignedUser = $order->assignUser ?? \App\Models\User::find($order->assign_user_id);
                      $workDate = $order->assigned_at ?? $order->updated_at ?? $order->created_at;
                      $workDateText = $workDate ? \Carbon\Carbon::parse($workDate)->format('Y-m-d') : '—';

                      $courierName = $order->courier->name ?? '—';
                      $productName = optional($item->product)->name ?? '—';
                    @endphp
                    <tr>
                      <td class="nowrap" data-label="Order ID" style="font-size:11px;">{{ $order->id }}</td>
                      <td class="nowrap" data-label="Invoice No" style="font-size:11px;">
                        <a href="{{ route('admin.orders.show',$order->id)}}" target="_blank" style="color:#000;">#{{ $order->invoice_no }}</a>
                      </td>
                      <td data-label="Customer" style="font-size:11px;">{{ $order->first_name }}</td>
                      <td data-label="Phone" class="nowrap" style="font-size:11px;">{{ $order->mobile }}</td>
                      <td data-label="Address" style="font-size:11px;">
                        <span class="text-truncate-2" title="{{ $order->shipping_address }}">{{ $order->shipping_address }}</span>
                      </td>
                      <td data-label="Product" style="font-size:11px;">
                        <span class="text-truncate-2" title="{{ $productName }}">{{ $productName }}</span>
                      </td>
                      <td data-label="Quantity" class="nowrap" style="font-size:11px;">{{ $qty }}</td>
                      <td data-label="Courier" style="font-size:11px;">{{ $courierName }}</td>
                      <td data-label="Courier ID" style="font-size:11px;">{{ $order->courier_tracking_id ?? '—' }}</td>
                      <td data-label="Assigned To" style="font-size:11px;">
                        {{ $assignedUser ? ($assignedUser->first_name.' '.$assignedUser->last_name) : '—' }}
                      </td>
                      <td data-label="Work Date" class="nowrap" style="font-size:11px;">{{ $workDateText }}</td>
                      <td data-label="Total" class="nowrap" style="font-size:11px;">{{ number_format($row_total,2) }}</td>
                    </tr>
                  @empty
                    <tr><td colspan="12" class="text-center text-danger">No data found</td></tr>
                  @endforelse

                  <tr>
                    <td colspan="11" class="text-end"><h6 class="mb-0">Total Amount (delivery included) :</h6></td>
                    <td class="nowrap"><h6 class="text-danger mb-0">{{ number_format($total,2) }}</h6></td>
                  </tr>
                </tbody>
              </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-2">{{ $details->appends(request()->query())->links() }}</div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection

@push('js')
<script>
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.print-btn').forEach(btn=>{
      btn.addEventListener('click', ()=> window.print());
    });
  });
</script>
@endpush
