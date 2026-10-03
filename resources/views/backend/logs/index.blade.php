@extends('backend.app')
@section('content')

<style>
    .premium-card { border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: none; overflow: hidden; }
    
    .premium-table { border-collapse: separate; border-spacing: 0 6px; }
    
    .premium-table thead th { 
        background-color: #f1f5f9; 
        color: #475569; 
        font-weight: 700; 
        text-transform: uppercase; 
        font-size: 11px; 
        padding: 14px 12px; 
        white-space: nowrap;
        border: none;
        letter-spacing: 0.5px;
    }
    
    .premium-table tbody tr {
        background-color: #ffffff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        transition: all 0.2s ease-in-out;
    }
    
    .premium-table tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }

    .premium-table tbody tr:hover {
        background-color: #f0fdf4;
        box-shadow: 0 4px 10px rgba(0,0,0,0.06);
        transform: translateY(-1px);
    }
    
    .premium-table tbody td { 
        padding: 14px 12px; 
        vertical-align: middle; 
        color: #334155; 
        font-size: 12px;
        border-top: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .premium-table tbody td:first-child { border-left: 1px solid #e2e8f0; border-top-left-radius: 8px; border-bottom-left-radius: 8px; }
    .premium-table tbody td:last-child { border-right: 1px solid #e2e8f0; border-top-right-radius: 8px; border-bottom-right-radius: 8px; }

    .text-wrap-custom {
        white-space: normal !important;
        line-height: 1.6;
        font-size: 13px;
    }

    .ip-column {
        white-space: nowrap !important;
        word-break: keep-all !important;
        text-align: center;
    }

    .badge-soft { 
        padding: 4px 8px; 
        border-radius: 4px; 
        font-weight: 600; 
        font-size: 10px; 
        text-decoration: none; 
        display: inline-block;
        white-space: nowrap; 
        margin: 2px 0;
        cursor: default;
        transition: all 0.2s;
    }
    
    .bg-soft-primary { background-color: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; }
    .bg-soft-primary:hover { background-color: #4338ca; color: #ffffff; }
    
    .bg-soft-info { background-color: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; }
    .bg-soft-info:hover { background-color: #0284c7; color: #ffffff; }
    
    .bg-soft-warning { background-color: #fef9c3; color: #854d0e; border: 1px solid #fde047; }
    .bg-soft-success { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .bg-soft-danger { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    .btn-custom-undo {
        background-color: #fff1f2;
        color: #e11d48;
        border: 1px solid #fecaca;
        transition: all 0.2s ease-in-out;
        font-weight: 600;
        box-shadow: 0 1px 2px rgba(225, 29, 72, 0.05);
    }
    .btn-custom-undo:hover {
        background-color: #e11d48;
        color: #ffffff;
        border-color: #e11d48;
        box-shadow: 0 4px 8px rgba(225, 29, 72, 0.25);
        transform: translateY(-1px);
    }

    .btn-filter { background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); border: none; color: white; transition: all 0.2s; }
    .btn-filter:hover { box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3); transform: translateY(-1px); color: white; }
    
    .btn-reset { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; transition: all 0.2s; }
    .btn-reset:hover { background: #e2e8f0; color: #1e293b; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }

    .table-responsive { overflow-x: auto; padding: 0 5px; }
    
    @media (min-width: 992px) {
        .table-responsive { overflow-x: hidden; } 
    }

    .custom-list {
        margin-bottom: 5px;
        padding-left: 18px;
        list-style-type: square;
    }
    .custom-list li {
        margin-bottom: 6px;
        color: #334155;
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title"><i class="mdi mdi-history text-primary"></i> System Activity Logs</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card premium-card">
            <div class="card-body">
                
                <form action="{{ route('admin.activity_logs') }}" method="GET" class="mb-4 bg-white p-3 rounded" style="border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                    <div class="row g-2 align-items-end">
                        
                        <div class="col-md-2">
                            <label for="order_id" class="form-label fw-bold small text-dark mb-1">Order ID / Invoice</label>
                            <input type="text" name="order_id" id="order_id" class="form-control form-control-sm" placeholder="e.g. 10254" value="{{ request('order_id') }}">
                        </div>

                        <div class="col-md-3">
                            <label for="user_id" class="form-label fw-bold small text-dark mb-1">Worker</label>
                            <select name="user_id" id="user_id" class="form-select form-select-sm">
                                <option value="">All Workers</option>
                                @foreach($workers as $worker)
                                    <option value="{{ $worker->id }}" {{ request('user_id') == $worker->id ? 'selected' : '' }}>
                                        {{ $worker->first_name }} {{ $worker->last_name }} ({{ $worker->mobile }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="action_type" class="form-label fw-bold small text-dark mb-1">Action Type</label>
                            <select name="action_type" id="action_type" class="form-select form-select-sm">
                                <option value="">All Actions</option>
                                <option value="Create" {{ request('action_type') == 'Create' ? 'selected' : '' }}>Create</option>
                                <option value="Update" {{ request('action_type') == 'Update' ? 'selected' : '' }}>Update</option>
                                <option value="Assign" {{ request('action_type') == 'Assign' ? 'selected' : '' }}>Assign</option>
                                <option value="Delete" {{ request('action_type') == 'Delete' ? 'selected' : '' }}>Delete</option>
                                <option value="Undo" {{ request('action_type') == 'Undo' ? 'selected' : '' }}>Undo</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="start_date" class="form-label fw-bold small text-dark mb-1">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                        </div>

                        <div class="col-md-2">
                            <label for="end_date" class="form-label fw-bold small text-dark mb-1">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                        </div>

                        <div class="col-md-1 d-flex gap-1">
                            <button type="submit" class="btn btn-sm btn-filter w-100" title="Filter"><i class="mdi mdi-filter"></i></button>
                            <a href="{{ route('admin.activity_logs') }}" class="btn btn-sm btn-reset w-100" title="Reset"><i class="mdi mdi-refresh"></i></a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table premium-table mb-0" style="border-collapse: separate; border-spacing: 0 8px;">
                        <thead>
                            <tr>
                                <th style="min-width: 60px;">ID</th>
                                <th style="min-width: 140px;">Date & Time</th>
                                <th style="min-width: 170px;">User</th>
                                <th style="min-width: 100px;">Action</th>
                                <th style="min-width: 110px;">Order Info</th>
                                <th style="width: 100%;">Activity Details</th>
                                <th style="min-width: 120px; text-align: center;">IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                @php
                                    $rawText = $log->description ?? $log->note ?? '';
                                    $desc = htmlspecialchars($rawText);
                                    
                                    $extractedOrderId = $log->order_id;
                                    if(!$extractedOrderId && preg_match('/Order\s*#?(\d+)/i', $rawText, $matches)) {
                                        $extractedOrderId = $matches[1];
                                    }

                                    $invoiceNo = null;
                                    if($extractedOrderId) {
                                        $invoiceNo = \App\Models\Order::where('id', $extractedOrderId)->value('invoice_no');
                                    }

                                    $hasJsonData = !empty($log->old_data) && !empty($log->new_data) && is_array($log->new_data);
                                    
                                    if (preg_match('/Changes:\s*\[(.*?)\]/is', $desc, $matches)) {
                                        if ($hasJsonData) {
                                            $desc = str_replace($matches[0], '', $desc);
                                        } else {
                                            $changesStr = $matches[1];
                                            $changesArr = explode(', ', $changesStr);
                                            
                                            $ul = '<div class="bg-white p-2 rounded border mt-2" style="box-shadow: 0 1px 2px rgba(0,0,0,0.02);"><ul class="custom-list mb-0">';
                                            foreach($changesArr as $ch) {
                                                $ch = str_replace("'", "<b class='text-danger'>", $ch);
                                                $ch = str_replace("➞", "</b> <i class='mdi mdi-arrow-right text-muted'></i> <b class='text-success'>", $ch);
                                                $ul .= "<li>{$ch}</li>";
                                            }
                                            $ul .= '</ul></div>';
                                            
                                            $desc = str_replace($matches[0], $ul, $desc);
                                        }
                                    }

                                    $desc = str_replace('Order updated.', '<div class="fw-bold text-dark mb-1"><i class="mdi mdi-check-circle text-success fs-5 align-middle"></i> Order Updated:</div>', $desc);
                                    $desc = str_replace('No primary fields modified.', '<span class="text-muted"><i class="mdi mdi-information-outline"></i> No primary fields modified.</span>', $desc);
                                    $desc = str_replace('(Product items were modified).', '<div class="badge-soft bg-soft-warning text-dark border mt-2"><i class="mdi mdi-cube-outline"></i> Product items were modified</div>', $desc);
                                    
                                    $desc = preg_replace('/(status changed to) ([a-zA-Z0-9_ ]+)/i', '$1 <span class="badge-soft bg-soft-warning">$2</span>', $desc);
                                    $desc = preg_replace('/User ID: (\d+)/i', '<span class="badge-soft bg-soft-info"><i class="mdi mdi-account"></i> User ID: $1</span>', $desc);
                                    $desc = preg_replace('/(\d+) order\(s\)/i', '<strong class="text-dark">$1 order(s)</strong>', $desc);

                                    $changesHtml = '';
                                    if ($hasJsonData) {
                                        $changesHtml .= '<div class="bg-white p-2 rounded border mt-2" style="box-shadow: 0 1px 2px rgba(0,0,0,0.02);"><ul class="custom-list mb-0">';
                                        foreach ($log->new_data as $key => $newValue) {
                                            $oldValue = $log->old_data[$key] ?? 'N/A';
                                            $keyName = ucwords(str_replace('_', ' ', $key));
                                            $changesHtml .= "<li><strong>{$keyName}:</strong> <span class='text-danger text-decoration-line-through'>{$oldValue}</span> <i class='mdi mdi-arrow-right text-muted'></i> <span class='text-success fw-bold'>{$newValue}</span></li>";
                                        }
                                        $changesHtml .= '</ul></div>';
                                    }

                                    $actColor = 'bg-soft-primary'; 
                                    if(stripos($log->action, 'create') !== false) $actColor = 'bg-soft-success';
                                    if(stripos($log->action, 'update') !== false) $actColor = 'bg-soft-info';
                                    if(stripos($log->action, 'delete') !== false) $actColor = 'bg-soft-danger';
                                    if(stripos($log->action, 'undo') !== false) $actColor = 'bg-soft-warning';
                                @endphp
                            <tr>
                                <td class="text-muted fw-bold">#{{ $log->id }}</td>
                                
                                <td>
                                    <div class="fw-bold text-dark">{{ $log->created_at ? $log->created_at->format('d M, Y') : 'N/A' }}</div>
                                    <div class="text-muted" style="font-size: 11px;">{{ $log->created_at ? $log->created_at->format('h:i A') : '' }}</div>
                                </td>
                                
                                <td>
                                    @if($log->user)
                                        <div class="fw-bold text-primary">{{ $log->user->first_name }} {{ $log->user->last_name }}</div>
                                        <div class="text-muted" style="font-size: 11px;"><i class="mdi mdi-phone"></i> {{ $log->user->mobile ?? 'N/A' }}</div>
                                    @else
                                        <span class="badge-soft bg-soft-primary" style="font-size: 10px;">System</span>
                                    @endif
                                </td>
                                
                                <td>
                                    <span class="badge-soft {{ $actColor }}">{{ strtoupper($log->action ?? 'LOG') }}</span>
                                </td>

                                <td>
                                    @if($extractedOrderId)
                                        <div class="d-flex flex-column gap-2 align-items-start">
                                            <a href="{{ url('admin/orders/'.$extractedOrderId.'/edit') }}" target="_blank" class="badge-soft bg-soft-primary" title="View Order">
                                                <i class="mdi mdi-open-in-new"></i> #{{ $extractedOrderId }} @if($invoiceNo) | {{ $invoiceNo }} @endif
                                            </a>
                                            <a href="javascript:void(0);" data-id="{{ $extractedOrderId }}" class="badge-soft bg-soft-info view-activity-log" title="View Full History">
                                                <i class="mdi mdi-history"></i> History
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                
                                <td class="text-wrap-custom">
                                    {!! $desc !!} 
                                    {!! $changesHtml !!}

                                    @if(empty($rawText)) 
                                        <span class="text-danger" style="font-size: 11px;">(No details)</span>
                                    @endif

                                    @if(!empty($log->old_data) && stripos($log->action, 'undo') === false)
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-custom-undo py-1 px-3 mt-1 undo-action-btn" data-id="{{ $log->id }}">
                                                <i class="mdi mdi-undo-variant me-1"></i> Undo Action
                                            </button>
                                        </div>
                                    @endif
                                </td>
                                
                                <td class="ip-column">
                                    <div class="bg-white rounded px-3 py-1 d-inline-block border text-center" style="box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                                        <span class="text-muted fw-bold" style="font-size: 11px;">{{ $log->ip_address ?? '127.0.0.1' }}</span>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 bg-white rounded">
                                    <i class="mdi mdi-folder-open-outline text-muted" style="font-size: 48px;"></i>
                                    <h6 class="text-muted mt-3">No activity logs found!</h6>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 d-flex justify-content-end">
                    {!! $logs->appends(request()->query())->links('pagination::bootstrap-5') !!}
                </div>
                
            </div>
        </div>
    </div>
</div>

<div class="modal fade orderActivityLogModal" id="activityLogModal" tabindex="-1" aria-hidden="true" style="z-index: 99999;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; border:none; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header border-bottom-0 bg-light py-3">
                <h6 class="modal-title fw-bold text-dark m-0 fs-5">Order Log: <span id="logOrderId" class="text-primary"></span></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 activityLogBody bg-white" style="font-size: 13px;">Loading...</div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
$(document).ready(function() {
    
    $(document).on('click', '.view-activity-log', function (e) {
        e.preventDefault();
        const orderId = $(this).data('id');
        
        $('#logOrderId').text('#' + orderId);
        $('#activityLogModal').modal('show');
        
        $('.activityLogBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><div class="mt-3 text-muted fw-bold">Fetching logs...</div></div>');

        let logUrl = "{{ route('admin.order.history', ':id') }}".replace(':id', orderId);

        $.ajax({
            url: logUrl,
            type: 'GET',
            success: function (response) {
                $('.activityLogBody').html(response.html);
            },
            error: function(xhr) {
                $('.activityLogBody').html('<div class="text-danger text-center py-5 fw-bold"><i class="mdi mdi-alert-circle-outline" style="font-size: 40px;"></i><br>Error fetching logs.</div>');
            }
        });
    });

    $(document).on('click', '.undo-action-btn', function (e) {
        e.preventDefault();
        const logId = $(this).data('id');
        const btn = $(this);

        if(confirm('Are you sure you want to undo this action? The order will revert to its previous state.')) {
            
            btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Undoing...').prop('disabled', true);

            let undoUrl = "{{ route('admin.activity_logs.undo', ':id') }}".replace(':id', logId);

            $.ajax({
                url: undoUrl,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    if(response.status) {
                        alert(response.msg);
                        location.reload(); 
                    } else {
                        alert(response.msg);
                        btn.html('<i class="mdi mdi-undo-variant me-1"></i> Undo Action').prop('disabled', false);
                    }
                },
                error: function() {
                    alert('Something went wrong!');
                    btn.html('<i class="mdi mdi-undo-variant me-1"></i> Undo Action').prop('disabled', false);
                }
            });
        }
    });

});
</script>
@endpush