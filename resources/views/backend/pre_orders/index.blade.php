@extends('backend.app')
@section('content')

@php
    use App\Services\PreOrderService;

    $statusClass = [
        PreOrderService::STATUS_PREORDER   => 'bg-warning text-dark',
        PreOrderService::STATUS_PROCESSING => 'bg-info',
        PreOrderService::STATUS_READY      => 'bg-success',
    ];
    $tabQuery = fn ($value) => array_merge(request()->except(['status', 'page']), ['status' => $value]);
    $canEdit  = auth()->user()->can('order.edit');
@endphp

<style>
    .po-table thead th{ font-size: 13px; font-weight: 700; padding: 10px 8px; background: #f8f9fa; white-space: nowrap; }
    .po-table tbody td{ font-size: 13px; padding: 10px 8px; vertical-align: top; }
    .po-tabs .btn{ font-size: 13px; }
    .po-badge{ display: inline-block; padding: 2px 8px; border-radius: 4px; background: #fff4e5; color: #b45309; border: 1px solid #fcd9a8; font-size: 10px; font-weight: 800; letter-spacing: .8px; }
    .po-line + .po-line{ margin-top: 8px; padding-top: 8px; border-top: 1px dashed #e5e7eb; }
    .po-line .po-eta{ color: #b45309; font-size: 12px; }
    .po-money{ display: flex; justify-content: space-between; gap: 12px; white-space: nowrap; }
    .po-money.is-due{ color: #b91c1c; font-weight: 700; }
    .po-money.is-paid{ color: #15803d; }
    .po-bulk{ display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
    .po-filters .form-control, .po-filters .form-select{ font-size: 13px; }

    @media (max-width: 767px){
        .po-table thead{ display: none; }
        .po-table, .po-table tbody, .po-table tr, .po-table td{ display: block; width: 100%; }
        .po-table tr{ border: 1px solid #e5e7eb; border-radius: 10px; margin-bottom: 10px; padding: 8px; }
        .po-table tbody td{ border: 0; padding: 4px 6px; }
        .po-table td[data-label]::before{ content: attr(data-label) ": "; font-weight: 600; color: #64748b; font-size: 12px; display: block; }
    }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <div class="row mt-3">
                <div class="col-12">
                    <div class="page-title-box d-flex align-items-center justify-content-between">
                        <h4 class="page-title mb-0" style="font-size: 1.2rem;">
                            <i class="mdi mdi-clock-outline me-1 text-warning"></i> Pre-Orders
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0" style="font-size: 0.8rem;">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Pre-Orders</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
            @endif

            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body p-2 p-md-3">

                            {{-- Status tabs --}}
                            <div class="btn-group po-tabs mb-3 flex-wrap">
                                @foreach($tabs as $tab)
                                    <a href="{{ route('admin.pre_orders.index', $tabQuery($tab)) }}"
                                       class="btn btn-sm {{ $status === $tab ? 'btn-primary' : 'btn-light border' }}">
                                        {{ $tab }} ({{ $counts[$tab] ?? 0 }})
                                    </a>
                                @endforeach
                                <a href="{{ route('admin.pre_orders.index', $tabQuery('all')) }}"
                                   class="btn btn-sm {{ $status === 'all' ? 'btn-primary' : 'btn-light border' }}">
                                    All ({{ $counts->sum() }})
                                </a>
                            </div>

                            {{-- Filters --}}
                            <form method="GET" action="{{ route('admin.pre_orders.index') }}" class="row g-2 align-items-end po-filters mb-3">
                                <input type="hidden" name="status" value="{{ $status }}">
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label mb-1 small">Search</label>
                                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm" placeholder="Order ID / name / phone / product">
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label mb-1 small">Product</label>
                                    <select name="product_id" class="form-select form-select-sm">
                                        <option value="">All products</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" {{ (int) ($filters['product_id'] ?? 0) === $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-4">
                                    <label class="form-label mb-1 small">Payment mode</label>
                                    <select name="payment_mode" class="form-select form-select-sm">
                                        <option value="">Any</option>
                                        <option value="full" {{ ($filters['payment_mode'] ?? '') === 'full' ? 'selected' : '' }}>Full payment</option>
                                        <option value="advance" {{ ($filters['payment_mode'] ?? '') === 'advance' ? 'selected' : '' }}>Advance payment</option>
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-4 col-6">
                                    <label class="form-label mb-1 small">Delivery from</label>
                                    <input type="date" name="delivery_from" value="{{ $filters['delivery_from'] ?? '' }}" class="form-control form-control-sm">
                                </div>
                                <div class="col-lg-2 col-md-4 col-6">
                                    <label class="form-label mb-1 small">Delivery to</label>
                                    <input type="date" name="delivery_to" value="{{ $filters['delivery_to'] ?? '' }}" class="form-control form-control-sm">
                                </div>
                                <div class="col-12 d-flex gap-1">
                                    <button class="btn btn-sm btn-primary"><i class="mdi mdi-filter-outline"></i> Filter</button>
                                    <a href="{{ route('admin.pre_orders.index', ['status' => $status]) }}" class="btn btn-sm btn-light border">Reset</a>
                                </div>
                            </form>

                            {{-- Bulk actions --}}
                            @if($canEdit)
                            <div class="po-bulk mb-3">
                                <span class="small text-muted me-1"><span id="poSelectedCount">0</span> selected:</span>
                                <button type="button" class="btn btn-sm btn-info text-white po-status-btn" data-status="{{ PreOrderService::STATUS_PROCESSING }}">
                                    <i class="mdi mdi-package-variant"></i> Product arrived → Processing
                                </button>
                                <button type="button" class="btn btn-sm btn-success po-status-btn" data-status="{{ PreOrderService::STATUS_READY }}">
                                    <i class="mdi mdi-truck-check-outline"></i> Ready to Ship
                                </button>
                                <button type="button" class="btn btn-sm btn-warning" id="poDateSelected">
                                    <i class="mdi mdi-calendar-edit"></i> Change delivery date
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="poReminder">
                                    <i class="mdi mdi-email-alert-outline"></i> Send due reminder
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary ms-auto" id="poDateProduct">
                                    <i class="mdi mdi-calendar-sync"></i> Change delivery date for a product
                                </button>
                            </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-centered po-table mb-0 w-100">
                                    <thead>
                                        <tr>
                                            <th style="width:32px;"><input type="checkbox" id="poCheckAll" class="form-check-input"></th>
                                            <th>Order</th>
                                            <th>Customer</th>
                                            <th>Products</th>
                                            <th>Payment</th>
                                            <th>Status</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($items as $order)
                                            @php $next = PreOrderService::FLOW[$order->status] ?? null; @endphp
                                            <tr>
                                                <td><input type="checkbox" class="form-check-input po-check" value="{{ $order->id }}"></td>

                                                <td data-label="Order">
                                                    <strong>#{{ $order->id }}</strong>
                                                    <span class="po-badge ms-1">PRE-ORDER</span>
                                                    <div class="text-muted">Invoice: {{ $order->invoice_no }}</div>
                                                    <div class="text-muted">{{ optional($order->created_at)->format('d M Y, h:i A') }}</div>
                                                </td>

                                                <td data-label="Customer">
                                                    <strong>{{ trim($order->first_name . ' ' . $order->last_name) }}</strong>
                                                    <div>{{ $order->mobile }}</div>
                                                    <div class="text-muted">{{ $order->email ?: 'No email — cannot be notified' }}</div>
                                                </td>

                                                <td data-label="Products">
                                                    @foreach($order->details as $line)
                                                        @php
                                                            $variant = collect([
                                                                optional(optional($line->variation)->size)->title,
                                                                optional(optional($line->variation)->color)->name,
                                                            ])->filter()->implode(' / ');
                                                        @endphp
                                                        <div class="po-line">
                                                            <strong>{{ optional($line->product)->name ?? 'Deleted product' }}</strong> × {{ (int) $line->quantity }}
                                                            @if($variant)<div class="text-muted">{{ $variant }}</div>@endif
                                                            @if($line->is_preorder)
                                                                <div class="po-eta"><i class="mdi mdi-calendar-clock"></i> {{ $line->deliveryLabel() ?: 'No delivery estimate' }}</div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </td>

                                                <td data-label="Payment" style="min-width: 190px;">
                                                    <span class="badge {{ $order->payment_mode === 'advance' ? 'bg-primary' : 'bg-secondary' }}">
                                                        {{ $order->payment_mode === 'advance' ? 'Advance' : 'Full' }}
                                                    </span>
                                                    <span class="text-muted">{{ $order->payment_method }}</span>
                                                    <div class="po-money mt-1"><span>Total</span><span>{{ biz_format_currency($order->final_amount) }}</span></div>
                                                    @if($order->isAdvancePreorder())
                                                        <div class="po-money"><span>Advance</span><span>{{ biz_format_currency($order->advance_amount) }}</span></div>
                                                    @endif
                                                    <div class="po-money is-paid"><span>Paid</span><span>{{ biz_format_currency($order->advance_paid) }}</span></div>
                                                    <div class="po-money is-due"><span>Due</span><span>{{ biz_format_currency($order->due_amount) }}</span></div>
                                                    @if($order->isAdvancePreorder() && !$order->advanceIsPaid())
                                                        @if($order->transaction_id)
                                                            <div class="text-muted small">TrxID: {{ $order->transaction_id }}@if($order->sender_number) ({{ $order->sender_number }})@endif</div>
                                                        @endif
                                                        @if($canEdit)
                                                            <button type="button" class="btn btn-sm btn-outline-success mt-1 po-advance-btn"
                                                                    data-url="{{ route('admin.pre_orders.advance_received', $order->id) }}">
                                                                Mark advance received
                                                            </button>
                                                        @endif
                                                    @endif
                                                </td>

                                                <td data-label="Status">
                                                    <span class="badge {{ $statusClass[$order->status] ?? 'bg-secondary' }}">{{ $order->status }}</span>
                                                </td>

                                                <td data-label="Action" class="text-end" style="white-space: nowrap;">
                                                    @if($canEdit && $next)
                                                        <button type="button" class="btn btn-sm btn-primary po-next-btn" data-id="{{ $order->id }}" data-status="{{ $next }}" title="Move to {{ $next }}">
                                                            → {{ $next }}
                                                        </button>
                                                    @endif
                                                    <a href="{{ route('admin.orders.show', $order->id) }}" target="_blank" class="btn btn-sm btn-light border" title="Invoice"><i class="mdi mdi-printer"></i></a>
                                                    @if($canEdit)
                                                        <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn btn-sm btn-light border" title="Edit order"><i class="mdi mdi-pencil"></i></a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="7" class="text-center text-muted py-4">No pre-orders found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">{{ $items->links() }}</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Change delivery date --}}
<div class="modal fade" id="poDateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="poDateForm">
            <div class="modal-header">
                <h5 class="modal-title">Change estimated delivery</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="scope" id="poDateScope" value="orders">

                <p class="small text-muted mb-3" id="poDateInfo"></p>

                <div id="poDateProductFields" style="display:none;">
                    <div class="mb-2">
                        <label class="form-label">Product</label>
                        <select name="product_id" id="poDateProductId" class="form-select">
                            <option value="">Choose a product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2" id="poDateVariationWrap" style="display:none;">
                        <label class="form-label">Variation</label>
                        <select name="variation_id" id="poDateVariationId" class="form-select">
                            <option value="">All variations</option>
                        </select>
                    </div>
                    <div class="form-check mb-3">
                        <input type="hidden" name="update_product" value="0">
                        <input class="form-check-input" type="checkbox" name="update_product" value="1" id="poDateUpdateProduct" checked>
                        <label class="form-check-label" for="poDateUpdateProduct">Also use this date for new pre-orders of the product</label>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label">Delivery text</label>
                    <input type="text" name="delivery_text" maxlength="255" class="form-control" placeholder="Estimated Delivery: 25–30 October">
                    <small class="text-muted">Leave blank to build the text from the dates.</small>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label">From</label>
                        <input type="date" name="delivery_start" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label">To</label>
                        <input type="date" name="delivery_end" class="form-control">
                    </div>
                </div>
                <p class="small text-muted mt-3 mb-0"><i class="mdi mdi-email-outline"></i> Every affected customer with an email address is notified, and the change is logged.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save &amp; notify customers</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('js')
<script>
(function () {
    const token = @json(csrf_token());
    const urls = {
        status:   @json(route('admin.pre_orders.status')),
        delivery: @json(route('admin.pre_orders.delivery_date')),
        reminder: @json(route('admin.pre_orders.due_reminder')),
    };
    // Variable products: product id → its variations, for the "product" scope of the date modal.
    const variations = @json($products->filter(fn ($p) => $p->type === 'variable')->mapWithKeys(fn ($p) => [
        $p->id => $p->variations->map(fn ($v) => ['id' => $v->id, 'label' => $v->display_title])->values(),
    ]));

    function selectedIds() {
        return $('.po-check:checked').map(function () { return this.value; }).get();
    }
    function refreshCount() { $('#poSelectedCount').text(selectedIds().length); }

    function post(url, data, done) {
        $.ajax({
            url: url, type: 'POST', data: Object.assign({ _token: token }, data),
            success: function (res) {
                if (res.status) { toastr.success(res.msg); setTimeout(function () { window.location.reload(); }, 900); }
                else { toastr.error(res.msg); }
                if (done) done(res);
            },
            error: function (xhr) {
                const res = xhr.responseJSON || {};
                const first = res.errors ? Object.values(res.errors)[0][0] : null;
                toastr.error(first || res.message || 'Something went wrong.');
                if (done) done(null);
            }
        });
    }

    function needSelection() {
        const ids = selectedIds();
        if (!ids.length) toastr.warning('Please select at least one pre-order.');
        return ids;
    }

    $('#poCheckAll').on('change', function () { $('.po-check').prop('checked', this.checked); refreshCount(); });
    $(document).on('change', '.po-check', refreshCount);

    // Bulk and single status moves
    $('.po-status-btn').on('click', function () {
        const ids = needSelection();
        const status = $(this).data('status');
        if (ids.length && confirm('Move ' + ids.length + ' pre-order(s) to "' + status + '"? The customers will be emailed.')) {
            post(urls.status, { order_ids: ids, status: status });
        }
    });
    $(document).on('click', '.po-next-btn', function () {
        const status = $(this).data('status');
        if (confirm('Move this pre-order to "' + status + '"? The customer will be emailed.')) {
            post(urls.status, { order_ids: [$(this).data('id')], status: status });
        }
    });

    $(document).on('click', '.po-advance-btn', function () {
        if (confirm('Confirm that the advance payment for this order has been received?')) {
            post($(this).data('url'), {});
        }
    });

    $('#poReminder').on('click', function () {
        const ids = needSelection();
        if (ids.length && confirm('Email a due payment reminder for ' + ids.length + ' order(s)?')) {
            post(urls.reminder, { order_ids: ids });
        }
    });

    // Delivery date modal
    const modalEl = document.getElementById('poDateModal');
    let modalIds = [];
    function openDateModal(scope) {
        $('#poDateScope').val(scope);
        $('#poDateProductFields').toggle(scope === 'product');
        $('#poDateInfo').text(scope === 'product'
            ? 'Applies to every pre-order of the product that has not shipped yet.'
            : 'Applies to the ' + modalIds.length + ' selected pre-order(s).');
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
    $('#poDateSelected').on('click', function () {
        modalIds = needSelection();
        if (modalIds.length) openDateModal('orders');
    });
    $('#poDateProduct').on('click', function () { modalIds = []; openDateModal('product'); });

    $('#poDateProductId').on('change', function () {
        const list = variations[this.value] || [];
        const $select = $('#poDateVariationId').html('<option value="">All variations</option>');
        list.forEach(function (v) { $select.append($('<option>').val(v.id).text(v.label)); });
        $('#poDateVariationWrap').toggle(list.length > 0);
    });

    $('#poDateForm').on('submit', function (e) {
        e.preventDefault();
        const data = {};
        $(this).serializeArray().forEach(function (f) { data[f.name] = f.value; });
        if (data.scope === 'orders') { data.order_ids = modalIds; delete data.product_id; delete data.variation_id; }
        const $btn = $(this).find('button[type="submit"]').prop('disabled', true);
        post(urls.delivery, data, function () { $btn.prop('disabled', false); });
    });
})();
</script>
@endpush
