{{-- FILE: resources/views/backend/cash_sales/index.blade.php --}}
@extends('backend.app')

@push('css')
<style>
    /* =========================================
       📱 PREMIUM MOBILE RESPONSIVE CSS
       ========================================= */
    :root {
        --bg-body: #f8fafc;
        --bg-card: #ffffff;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
        --primary: #2563eb;
        --accent: #4f46e5;
        --danger: #ef4444;
        --success: #10b981;
        --radius: 12px;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    }

    body { background-color: var(--bg-body); font-family: 'Inter', sans-serif; font-size: 14px; color: var(--text-main); }

    /* --- LAYOUT UTILS --- */
    .page-title-box { padding: 20px 0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
    .page-title { font-weight: 800; font-size: 24px; color: var(--text-main); margin: 0; }
    .card { border: 1px solid var(--border-color); border-radius: var(--radius); box-shadow: var(--shadow-sm); background: var(--bg-card); margin-bottom: 30px; }
    .card-body { padding: 25px; }

    /* --- BUTTONS --- */
    .btn-custom {
        border-radius: 8px; font-weight: 600; font-size: 13px; padding: 10px 18px;
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        transition: all 0.2s; border: 1px solid transparent; box-shadow: var(--shadow-sm);
        text-decoration: none; white-space: nowrap;
    }
    .btn-custom:active { transform: translateY(1px); }
    .btn-primary-solid { background: #2563eb; color: #fff; }
    .btn-primary-solid:hover { background: #1d4ed8; color: #fff; }
    .btn-success-solid { background: #10b981; color: #fff; }
    .btn-success-solid:hover { background: #059669; color: #fff; }

    /* --- TABLE STYLES --- */
    .table-responsive { position: relative; overflow-x: auto; }
    .table-responsive::-webkit-scrollbar { height: 8px; }
    .table-responsive::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
    .table-responsive::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }

    .order-table { width: 100%; border-collapse: separate; border-spacing: 0 10px; }
    .order-table thead th {
        background: transparent; color: var(--text-muted); font-size: 12px;
        text-transform: uppercase; font-weight: 700; padding: 0 20px 10px; border: none; white-space: nowrap;
    }
    .order-table tbody tr {
        background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border-radius: 12px;
        transition: transform 0.2s;
    }
    .order-table tbody tr:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.08); z-index: 10; position: relative; }
    .order-table tbody td {
        padding: 16px 20px; vertical-align: middle; background: #fff;
        border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);
        color: var(--text-main); font-size: 14px;
    }
    .order-table tbody td:first-child { border-left: 1px solid var(--border-color); border-top-left-radius: 12px; border-bottom-left-radius: 12px; }
    .order-table tbody td:last-child { border-right: 1px solid var(--border-color); border-top-right-radius: 12px; border-bottom-right-radius: 12px; }

    /* Elements */
    .cell-stack { display: flex; flex-direction: column; gap: 3px; }
    .line-strong { font-weight: 700; color: var(--text-main); font-size: 14px; }
    .line-muted { color: var(--text-muted); font-size: 13px; font-weight: 500; }
    
    .product-tag {
        background: #f1f5f9; padding: 4px 8px; border-radius: 6px; border: 1px solid #e2e8f0;
        font-size: 12px; font-weight: 600; display: inline-block; margin: 2px;
    }

    .action-icons { display: flex; gap: 8px; }
    .action-icon {
        width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center;
        border-radius: 8px; background: #f8fafc; color: var(--text-muted); border: 1px solid var(--border-color);
        transition: all 0.2s; font-size: 16px; text-decoration: none;
    }
    .action-icon.print:hover { background: var(--success); color: #fff; border-color: var(--success); }
    .action-icon.delete:hover { background: var(--danger); color: #fff; border-color: var(--danger); }

    /* --- BULK BAR (Floating Bottom Bar) --- */
    .bulk-bar {
        position: fixed; left: 50%; bottom: 30px; transform: translateX(-50%);
        z-index: 9999; display: none; gap: 12px; padding: 12px 24px;
        background: #1e293b; border-radius: 50px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
        color: #fff; align-items: center;
    }
    .bulk-bar .count { background: rgba(255,255,255,0.15); padding: 4px 10px; border-radius: 20px; font-weight: 700; font-size: 12px; }

    /* =========================================
       📱 MOBILE RESPONSIVE LOGIC
       ========================================= */
    @media (max-width: 767.98px) {
        .page-title-box { flex-direction: column; align-items: flex-start; }
        .card-body { padding: 15px; }
        
        .order-table thead { display: none; }
        .order-table, .order-table tbody, .order-table tr, .order-table td { display: block; width: 100%; }
        
        .order-table tbody tr {
            margin-bottom: 20px; 
            padding-top: 50px; 
            position: relative; 
            border: 1px solid var(--border-color);
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.03);
        }

        .order-table td {
            display: flex; 
            justify-content: space-between; 
            align-items: flex-start; 
            text-align: right; 
            border-bottom: 1px dashed #e2e8f0; 
            padding: 12px 15px;
            min-height: 45px;
        }
        .order-table td:last-child { border-bottom: none; border-radius: 0 0 12px 12px; }

        .order-table td::before {
            content: attr(data-label);
            font-weight: 700; color: var(--text-muted); font-size: 11px; 
            text-transform: uppercase; letter-spacing: 0.5px;
            text-align: left; margin-right: 15px;
            flex-shrink: 0; max-width: 40%;
        }

        .cell-stack, .action-icons { align-items: flex-end; text-align: right; width: 100%; }
        
        /* Mobile Specific Positioning */
        .order-table td[data-label="Select"] {
            position: absolute; top: 0; left: 0;
            width: 50px; height: 50px;
            border: none; padding: 0;
            display: flex; justify-content: center; align-items: center; background: transparent;
        }
        .order-table td[data-label="Action"] {
            position: absolute; top: 8px; right: 8px;
            width: auto; height: auto;
            border: none; padding: 0; background: transparent;
        }
        .order-table td[data-label="Invoice ID"] {
            display: block; text-align: center; background: #f8fafc;
            border-bottom: 1px solid var(--border-color); border-radius: 12px 12px 0 0;
            margin-top: -50px; height: 50px; padding-top: 14px; pointer-events: none;
        }
        
        .order-table td[data-label="Select"]::before, 
        .order-table td[data-label="Action"]::before,
        .order-table td[data-label="Invoice ID"]::before { display: none; }
        
        .bulk-bar { width: 90%; flex-wrap: wrap; justify-content: center; bottom: 20px; padding: 10px; }
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Shop Cash Sales</h4>
            <div class="d-flex gap-2">
                {{-- Desktop Bulk Print Button --}}
                <a href="{{ route('admin.orderList') }}" class="btn-custom btn-success-solid multi_order_print">
                    <i class="mdi mdi-printer"></i> Print Selected
                </a>
                
                <a href="{{ route('admin.cash_sales.create') }}" class="btn-custom btn-primary-solid">
                    <i class="mdi mdi-plus"></i> Add Order
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table order-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">
                                    <div class="form-check d-flex justify-content-center">
                                        <input type="checkbox" class="form-check-input check_all" style="width:18px; height:18px;">
                                    </div>
                                </th>
                                <th>Invoice ID</th>
                                <th>Customer Name</th>
                                <th>Products</th>
                                <th>Date</th>
                                <th>Total Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            <tr>
                                <td data-label="Select">
                                    <div class="form-check d-flex justify-content-center">
                                        <input type="checkbox" class="form-check-input order_checkbox" value="{{ $item->id }}" style="width:18px; height:18px;">
                                    </div>
                                </td>
                                
                                <td data-label="Invoice ID">
                                    <div class="line-strong text-primary">#{{ $item->invoice_no }}</div>
                                </td>
                                
                                <td data-label="Customer Name">
                                    <div class="cell-stack">
                                        <span class="line-strong">{{ $item->first_name }}</span>
                                        <span class="line-muted">{{ $item->mobile }}</span>
                                    </div>
                                </td>
                                
                                <td data-label="Products">
                                    <div class="d-flex flex-wrap" style="gap:4px; max-width: 250px;">
                                        @foreach($item->details as $detail)
                                            <span class="product-tag">{{ $detail->product->name ?? 'N/A' }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                
                                <td data-label="Date">
                                    <span class="line-strong">{{ date('d M, Y', strtotime($item->date)) }}</span>
                                </td>
                                
                                <td data-label="Total Amount">
                                    <span class="line-strong fs-5 text-success">৳ {{ (int) $item->amount }}</span>
                                </td>
                                
                                <td data-label="Action">
                                    <div class="action-icons">
                                        {{-- Single Print Button --}}
                                        <a href="javascript:void(0)" data-id="{{ $item->id }}" class="action-icon print single_print_btn" title="Print"><i class="mdi mdi-printer-outline"></i></a>
                                        
                                        {{-- Delete Button --}}
                                        <a href="{{ route('admin.orders.destroy', $item->id) }}" class="action-icon delete" title="Delete"><i class="mdi mdi-trash-can-outline"></i></a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 d-flex justify-content-end">
                    {!! $items->links() !!}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Floating Bulk Action Bar --}}
<div class="bulk-bar" id="bulkBar">
    <span class="count" id="bulkCount">0 Selected</span>
    <button type="button" class="btn btn-sm btn-success text-white border-0" id="bb-print" style="border-radius: 6px; padding: 6px 12px; font-weight: 600;">
        <i class="mdi mdi-printer me-1"></i> Print Orders
    </button>
</div>

@endsection

@push('js')
<script>
$(document).ready(function(){
    
    // --- Bulk Bar Logic ---
    function refreshBulk() {
        const cnt = $('input.order_checkbox:checked').length;
        $('#bulkCount').text(cnt + ' Selected');
        
        if(cnt > 0) { 
            $('#bulkBar').fadeIn(200); 
        } else { 
            $('#bulkBar').fadeOut(200); 
        }
    }

    // ১. Check/Uncheck All Logic
    $(".check_all").on('change', function(){
        $(".order_checkbox").prop('checked', $(this).is(":checked"));
        refreshBulk();
    });

    // ২. Single Checkbox Change
    $(document).on('change', '.order_checkbox', function(){
        refreshBulk();
    });

    // ৩. Bulk Print Logic
    function executeBulkPrint(e) {
        e.preventDefault();
        
        const url = $('.multi_order_print').attr('href');
        const order_ids = $('input.order_checkbox:checked').map(function(){ return $(this).val(); }).get();
        
        if(!order_ids.length){ 
            if(typeof toastr !== 'undefined') toastr.error('Please Select At least One Order First!');
            else alert('Please Select At least One Order First!');
            return; 
        }
        
        sendPrintRequest(url, order_ids);
    }

    // ৪. Single Print Logic (From Action Column)
    $(document).on('click', '.single_print_btn', function(e){
        e.preventDefault();
        
        const url = $('.multi_order_print').attr('href');
        const order_id = $(this).data('id');
        
        // Sending array with single ID
        sendPrintRequest(url, [order_id]);
    });

    // কমন AJAX প্রিন্ট ফাংশন
    function sendPrintRequest(url, ids) {
        $.get(url, {order_ids: ids}, function(res){
            if(res.status){
                const w = window.open("", "_blank");
                w.document.write(res.view);
                w.document.close();
            } else { 
                if(typeof toastr !== 'undefined') toastr.error(res.msg);
                else alert(res.msg);
            }
        }).fail(function() {
            if(typeof toastr !== 'undefined') toastr.error('Something went wrong!');
            else alert('Something went wrong!');
        });
    }

    // Bind bulk print functions
    $(document).on('click', 'a.multi_order_print', executeBulkPrint);
    $(document).on('click', '#bb-print', executeBulkPrint);

});
</script>
@endpush