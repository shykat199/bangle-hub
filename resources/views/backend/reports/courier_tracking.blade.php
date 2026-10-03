@extends('backend.app')
@section('content')

<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0">Courier Tracking &amp; Payment</h4>
            <small class="text-muted">কোন দিন কোন অর্ডারে কুরিয়ার ট্র্যাকিং আইডি দিয়েছে, আর কোনগুলোর টাকা পাওয়া গেছে</small>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
            <i class="mdi mdi-printer"></i> Print
        </button>
    </div>

    {{-- সারাংশ --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body py-2">
                <div class="text-muted" style="font-size: 11px;">মোট কুরিয়ারে গেছে</div>
                <div class="fw-bold fs-5" id="sm-total">{{ $summary['total_orders'] }} <small class="text-muted" style="font-size:12px;">টি · ৳{{ number_format($summary['total_amount'], 0) }}</small></div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="background:#e8f7ee;"><div class="card-body py-2">
                <div class="text-success" style="font-size: 11px;">টাকা পেয়েছি</div>
                <div class="fw-bold fs-5 text-success" id="sm-received">{{ $summary['received_count'] }} <small style="font-size:12px;">টি · ৳{{ number_format($summary['received_amount'], 0) }}</small></div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="background:#fff8e1;"><div class="card-body py-2">
                <div class="text-warning" style="font-size: 11px;">টাকা বাকি</div>
                <div class="fw-bold fs-5 text-warning" id="sm-pending">{{ $summary['pending_count'] }} <small style="font-size:12px;">টি · ৳{{ number_format($summary['pending_amount'], 0) }}</small></div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body py-2">
                <div class="text-muted" style="font-size: 11px;">সিলেক্টেড</div>
                <div class="fw-bold fs-5"><span id="sel-count">0</span> <small class="text-muted" style="font-size:12px;">টি অর্ডার</small></div>
            </div></div>
        </div>
    </div>

    {{-- ফিল্টার --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-6 col-md-3">
                    <label class="form-label mb-1" style="font-size: 11px;">শুরুর তারিখ</label>
                    <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label mb-1" style="font-size: 11px;">শেষ তারিখ</label>
                    <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label mb-1" style="font-size: 11px;">কুরিয়ার</label>
                    <select name="courier_id" class="form-select form-select-sm">
                        <option value="">সব</option>
                        @foreach($couriers as $c)
                            <option value="{{ $c->id }}" {{ (string)$courier === (string)$c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label mb-1" style="font-size: 11px;">পেমেন্ট</label>
                    <select name="payment" class="form-select form-select-sm">
                        <option value="">সব</option>
                        <option value="pending"  {{ $payment === 'pending'  ? 'selected' : '' }}>বাকি</option>
                        <option value="received" {{ $payment === 'received' ? 'selected' : '' }}>টাকা পেয়েছি</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button class="btn btn-sm btn-primary w-100"><i class="mdi mdi-magnify"></i> খুঁজুন</button>
                    <a href="{{ route('admin.report.courier_tracking') }}" class="btn btn-sm btn-light border">রিসেট</a>
                </div>
            </form>
        </div>
    </div>

    {{-- অ্যাকশন --}}
    <div class="d-flex gap-2 mb-2 flex-wrap">
        <button type="button" class="btn btn-sm btn-success" id="btn-mark-paid" disabled>
            <i class="mdi mdi-cash-check"></i> সিলেক্টেডগুলো "টাকা পেয়েছি" মার্ক করুন
        </button>
        <button type="button" class="btn btn-sm btn-outline-warning" id="btn-mark-pending" disabled>
            <i class="mdi mdi-undo"></i> আবার "বাকি" করুন
        </button>
        <input type="date" id="paid-date" class="form-control form-control-sm" style="width: 160px;" value="{{ now()->format('Y-m-d') }}" title="টাকা পাওয়ার তারিখ">
    </div>

    {{-- টেবিল --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr style="font-size: 11px;">
                        <th class="px-1 text-center" style="width: 34px;"><input type="checkbox" class="form-check-input" id="ct-check-all"></th>
                        <th class="px-1">কুরিয়ারে গেছে</th>
                        <th class="px-1">ইনভয়েস</th>
                        <th class="px-1">কাস্টমার</th>
                        <th class="px-1">কুরিয়ার</th>
                        <th class="px-1">ট্র্যাকিং আইডি</th>
                        <th class="px-1">স্ট্যাটাস</th>
                        <th class="px-1 text-end">টাকা</th>
                        <th class="px-1 text-center">কুরিয়ার পেমেন্ট</th>
                    </tr>
                </thead>
                <tbody id="ct-rows">
                    @include('backend.reports.courier_tracking_rows', ['orders' => $orders])
                </tbody>
            </table>
        </div>
        <div class="p-2">{{ $orders->links() }}</div>
    </div>
</div>

@endsection

@push('js')
<script>
$(function () {
    function refreshSel() {
        const n = $('.ct-check:checked').length;
        $('#sel-count').text(n);
        $('#btn-mark-paid, #btn-mark-pending').prop('disabled', n === 0);
    }

    $(document).on('change', '#ct-check-all', function () {
        $('.ct-check').prop('checked', $(this).is(':checked'));
        refreshSel();
    });
    $(document).on('change', '.ct-check', refreshSel);

    function bulk(url, extra) {
        const ids = $('.ct-check:checked').map(function () { return $(this).val(); }).get();
        if (!ids.length) { toastr.error('কোনো অর্ডার সিলেক্ট করা হয়নি!'); return; }
        $.post(url, Object.assign({ order_ids: ids, _token: '{{ csrf_token() }}' }, extra || {}), function (res) {
            if (res.status) { toastr.success(res.msg); setTimeout(() => location.reload(), 700); }
            else { toastr.error(res.msg || 'কিছু একটা সমস্যা হয়েছে!'); }
        }).fail(function () { toastr.error('সার্ভারে সমস্যা হয়েছে!'); });
    }

    $('#btn-mark-paid').on('click', function () {
        bulk("{{ route('admin.report.courier_tracking.markPaid') }}", { paid_at: $('#paid-date').val() });
    });
    $('#btn-mark-pending').on('click', function () {
        bulk("{{ route('admin.report.courier_tracking.markPending') }}");
    });
});
</script>
@endpush
