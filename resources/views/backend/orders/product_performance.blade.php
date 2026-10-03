@extends('backend.app')
@section('content')

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Product Performance Report</h4>
        </div>
    </div>
</div>

<style>
    .pp-lbl { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 3px; display: block; }
    .pp-filter .form-control, .pp-filter .form-select { height: 38px; font-size: 13px; border-radius: 8px; border-color: #e5e9f0; transition: border-color .15s ease, box-shadow .15s ease; }
    .pp-filter .form-control:focus, .pp-filter .form-select:focus { border-color: #93c5fd; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
    .pp-filter .btn { height: 38px; border-radius: 8px; }
    .pp-filter .select2-container { width: 100% !important; }
    .pp-filter .select2-container--default .select2-selection--single { height: 38px !important; border-color: #e5e9f0; border-radius: 8px; }
    .pp-filter .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; font-size: 13px; padding-left: 10px; }
    .pp-filter .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
    /* White summary boxes with black text (number stays coloured) */
    .pp-stat { display: inline-flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #e5e9f0; border-radius: 10px; padding: 7px 16px; font-size: 13px; font-weight: 700; color: #111827; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
    .pp-stat .pp-num { font-size: 16px; color: #111827; }
    .pp-stat .n-blue, .pp-stat .n-green { color: #111827; }
    /* All report text black */
    #pp_result_card, #pp_qty_card { color: #111827; }
    #pp_result_card th, #pp_qty_card th, #pp_result_card td, #pp_qty_card td { color: #111827 !important; }
    .pp-qty-row:hover { background: #f1f5f9; }
    .pp-detail-wrap { background: #f8fafc; padding: 4px; overflow-x: auto; }
    .pp-detail-tbl th { background: #eef2f6; }
    .pp-caret { transition: none; }
    /* Mobile: keep cells on one line so the table scrolls sideways instead of
       breaking text vertically. */
    #pp_result_card .table-responsive table th,
    #pp_result_card .table-responsive table td,
    #pp_qty_card .table-responsive table th,
    #pp_qty_card .table-responsive table td,
    .pp-detail-tbl th, .pp-detail-tbl td { white-space: nowrap; }
    .table-responsive { -webkit-overflow-scrolling: touch; }
</style>

<div class="card mb-3">
    <div class="card-body pp-filter">
        <div class="row g-2 align-items-end">
            <div class="col-lg-4 col-md-6">
                <label class="pp-lbl">Product (search by name)</label>
                <select id="pp_product" class="form-select select2" style="width: 100%;">
                    <option value="">-- Select Product --</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 col-md-3 col-6">
                <label class="pp-lbl">Start Date</label>
                <input type="date" id="pp_start" class="form-control" value="{{ now()->subDays(29)->toDateString() }}">
            </div>
            <div class="col-lg-3 col-md-3 col-6">
                <label class="pp-lbl">End Date</label>
                <input type="date" id="pp_end" class="form-control" value="{{ now()->toDateString() }}">
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="pp-lbl d-none d-lg-block">&nbsp;</label>
                <button type="button" id="pp_load" class="btn btn-primary fw-bold w-100"><i class="mdi mdi-chart-bar"></i> View Report</button>
            </div>
        </div>
    </div>
</div>

<div class="card" id="pp_result_card" style="display:none;">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h5 class="mb-0 fw-bold" id="pp_heading"></h5>
            <div class="d-flex flex-wrap gap-2">
                <div class="pp-stat" id="pp_total_orders"></div>
                <div class="pp-stat" id="pp_total_qty"></div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle" style="font-size: 13px;">
                <thead class="bg-light text-center">
                    <tr>
                        <th>Date</th>
                        <th>Total Orders</th>
                        <th>Total Qty</th>
                        <th>Pending</th>
                        <th>Confirmed</th>
                        <th>Processing</th>
                        <th>Courier/Shipped</th>
                        <th>Delivered</th>
                        <th>Cancel/Return</th>
                        <th>Incomplete</th>
                    </tr>
                </thead>
                <tbody id="pp_rows" class="text-center"></tbody>
                <tfoot class="bg-light fw-bold text-center" id="pp_totals"></tfoot>
            </table>
        </div>
        <div class="text-muted" style="font-size: 11px;">* Date = order created date. Trashed orders are not counted.</div>
    </div>
</div>

{{-- Quantity-wise breakdown: how many orders bought 1 pc, 2 pcs, 4 pcs... --}}
<div class="card" id="pp_qty_card" style="display:none;">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h5 class="mb-0 fw-bold"><i class="mdi mdi-numeric text-primary"></i> Quantity Breakdown</h5>
            <span class="text-muted" style="font-size:12px;">How many orders bought each quantity of this product</span>
        </div>
        <div class="row">
            <div class="col-lg-7">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle text-center" style="font-size: 13px;">
                        <thead class="bg-light">
                            <tr>
                                <th>Quantity per Order</th>
                                <th>Number of Orders</th>
                                <th>Total Pieces</th>
                            </tr>
                        </thead>
                        <tbody id="pp_qty_rows"></tbody>
                        <tfoot class="bg-light fw-bold" id="pp_qty_totals"></tfoot>
                    </table>
                </div>
                <div class="text-muted" style="font-size: 11px;">* "Quantity per Order" = total pieces of this product in a single order.</div>
            </div>
        </div>
    </div>
</div>

<div class="card" id="pp_empty_card" style="display:none;">
    <div class="card-body text-center text-muted py-5">
        <i class="mdi mdi-package-variant-closed" style="font-size: 40px;"></i>
        <p class="mb-0 fw-bold">No orders found for this product in the selected period.</p>
    </div>
</div>

@endsection

@push('js')
<script>
$(function(){
    if($.fn.select2){ $('#pp_product').select2({ placeholder: '-- Select Product --' }); }

    function loadReport(){
        const product_id = $('#pp_product').val();
        if(!product_id){ toastr.error('Please select a product first!'); return; }

        const btn = $('#pp_load');
        btn.prop('disabled', true).html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');

        $.get("{{ route('admin.productPerformanceData') }}", {
            product_id: product_id,
            start_date: $('#pp_start').val(),
            end_date: $('#pp_end').val()
        }, function(res){
            btn.prop('disabled', false).html('<i class="mdi mdi-chart-bar"></i> View Report');

            if(!res.status){ toastr.error(res.msg || 'Could not load the report!'); return; }

            if(!res.rows.length){
                $('#pp_result_card').hide();
                $('#pp_qty_card').hide();
                $('#pp_empty_card').show();
                return;
            }

            $('#pp_empty_card').hide();
            $('#pp_heading').text(res.product + ' (' + res.range.start + ' to ' + res.range.end + ')');
            $('#pp_total_orders').html('Total Orders: <span class="pp-num n-blue">' + res.totals.total_orders + '</span>');
            $('#pp_total_qty').html('Total Qty: <span class="pp-num n-green">' + Math.round(res.totals.total_qty) + '</span>');

            let html = '';
            res.rows.forEach(function(r){
                html += '<tr>'
                    + '<td class="fw-bold">' + r.order_date + '</td>'
                    + '<td class="fw-bold text-primary">' + r.total_orders + '</td>'
                    + '<td class="fw-bold text-success">' + Math.round(r.total_qty) + '</td>'
                    + '<td>' + r.st_pending + '</td>'
                    + '<td>' + r.st_confirmed + '</td>'
                    + '<td>' + r.st_processing + '</td>'
                    + '<td>' + r.st_courier + '</td>'
                    + '<td>' + r.st_delivered + '</td>'
                    + '<td>' + r.st_cancelled + '</td>'
                    + '<td>' + r.st_incomplete + '</td>'
                    + '</tr>';
            });
            $('#pp_rows').html(html);

            const t = res.totals;
            $('#pp_totals').html('<tr>'
                + '<td>Grand Total</td>'
                + '<td class="text-primary">' + t.total_orders + '</td>'
                + '<td class="text-success">' + Math.round(t.total_qty) + '</td>'
                + '<td>' + t.st_pending + '</td>'
                + '<td>' + t.st_confirmed + '</td>'
                + '<td>' + t.st_processing + '</td>'
                + '<td>' + t.st_courier + '</td>'
                + '<td>' + t.st_delivered + '</td>'
                + '<td>' + t.st_cancelled + '</td>'
                + '<td>' + t.st_incomplete + '</td>'
                + '</tr>');

            $('#pp_result_card').show();

            // ---- Quantity Breakdown (click a row to see who ordered) ----
            let qHtml = '';
            (res.qty_dist || []).forEach(function(q){
                const qty = Math.round(q.qty_per_order);
                qHtml += '<tr class="pp-qty-row" data-qty="' + q.qty_per_order + '" style="cursor:pointer;">'
                    + '<td class="fw-bold text-start"><i class="mdi mdi-chevron-right pp-caret"></i> ' + qty + ' pc' + (qty > 1 ? 's' : '') + '</td>'
                    + '<td class="fw-bold">' + q.order_count + '</td>'
                    + '<td class="fw-bold">' + Math.round(q.total_pcs) + '</td>'
                    + '</tr>'
                    + '<tr class="pp-detail-row" data-for="' + q.qty_per_order + '" style="display:none;"><td colspan="3" class="p-0"><div class="pp-detail-wrap"></div></td></tr>';
            });
            $('#pp_qty_rows').html(qHtml);
            $('#pp_qty_totals').html('<tr>'
                + '<td>Grand Total</td>'
                + '<td class="text-primary">' + (res.qty_totals ? res.qty_totals.orders : 0) + '</td>'
                + '<td class="text-success">' + (res.qty_totals ? Math.round(res.qty_totals.pcs) : 0) + '</td>'
                + '</tr>');
            $('#pp_qty_card').show();

        }).fail(function(){
            btn.prop('disabled', false).html('<i class="mdi mdi-chart-bar"></i> View Report');
            toastr.error('Server error — please try again!');
        });
    }

    $('#pp_load').on('click', loadReport);
    $('#pp_product').on('change', function(){ if($(this).val()){ loadReport(); } });

    // Expand a quantity row to see which customers ordered that quantity.
    $(document).on('click', '.pp-qty-row', function(){
        const qty = $(this).data('qty');
        const detailRow = $('.pp-detail-row[data-for="' + qty + '"]');
        const wrap = detailRow.find('.pp-detail-wrap');
        const caret = $(this).find('.pp-caret');

        if(detailRow.is(':visible')){
            detailRow.hide();
            caret.removeClass('mdi-chevron-down').addClass('mdi-chevron-right');
            return;
        }

        caret.removeClass('mdi-chevron-right').addClass('mdi-chevron-down');
        detailRow.show();

        if(detailRow.data('loaded')) return;

        wrap.html('<div class="text-center py-2"><i class="mdi mdi-spin mdi-loading"></i> Loading...</div>');
        $.get("{{ route('admin.productPerformanceOrders') }}", {
            product_id: $('#pp_product').val(),
            qty: qty,
            start_date: $('#pp_start').val(),
            end_date: $('#pp_end').val()
        }, function(res){
            if(!res.status || !res.orders.length){
                wrap.html('<div class="text-center py-2 text-muted">No customers found.</div>');
                return;
            }
            let h = '<table class="table table-sm mb-0 pp-detail-tbl" style="font-size:12px;"><thead><tr>'
                + '<th>#</th><th>Invoice</th><th>Customer</th><th>Phone</th><th>Date</th><th>Status</th></tr></thead><tbody>';
            res.orders.forEach(function(o, i){
                const name = ((o.first_name||'') + ' ' + (o.last_name||'')).trim();
                const d = o.created_at ? String(o.created_at).substring(0,10) : '';
                h += '<tr><td>' + (i+1) + '</td><td>#' + o.invoice_no + '</td><td>' + name + '</td><td>'
                    + (o.mobile||'') + '</td><td>' + d + '</td><td>' + (o.status||'') + '</td></tr>';
            });
            h += '</tbody></table>';
            wrap.html(h);
            detailRow.data('loaded', true);
        }).fail(function(){
            wrap.html('<div class="text-center py-2 text-danger">Failed to load.</div>');
        });
    });
});
</script>
@endpush
