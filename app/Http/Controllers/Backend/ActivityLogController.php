<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $workers = User::whereHas('roles', function($q) {
            $q->whereRaw('LOWER(name) = ?', ['worker']);
        })->get();

        $query = ActivityLog::select('activity_logs.*')
                ->with('user', 'order')
                ->leftJoin('orders', 'activity_logs.order_id', '=', 'orders.id')
                ->orderBy('activity_logs.id', 'desc');

        if ($request->filled('order_id')) {
            $searchTerm = ltrim($request->order_id, '#');
            $query->where(function($q) use ($searchTerm) {
                $q->where('activity_logs.order_id', $searchTerm)
                  ->orWhere('orders.invoice_no', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($request->filled('user_id')) {
            $query->where('activity_logs.user_id', $request->user_id);
        }

        if ($request->filled('action_type')) {
            $query->where('activity_logs.action', 'LIKE', '%' . $request->action_type . '%');
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = Carbon::parse($request->start_date)->startOfDay();
            $end = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('activity_logs.created_at', [$start, $end]);
        } elseif ($request->filled('start_date')) {
            $start = Carbon::parse($request->start_date)->startOfDay();
            $query->where('activity_logs.created_at', '>=', $start);
        }

        $logs = $query->paginate(100)->appends($request->all());
        
        return view('backend.logs.index', compact('logs', 'workers'));
    }

    public function getOrderHistory($id)
    {
        $order = Order::find($id);
        $invoiceNo = $order ? $order->invoice_no : 'N/A';

        $logs = ActivityLog::with('user')
                    ->where('order_id', $id)
                    ->orderBy('id', 'desc')
                    ->get();

        $html = '<div class="table-responsive"><table class="table table-bordered table-striped mb-0">';
        $html .= '<thead class="bg-light"><tr><th style="min-width:140px;">Date & Time</th><th>User</th><th>Action</th><th>Details & Changes</th></tr></thead><tbody>';

        if($logs->count() > 0) {
            foreach($logs as $log) {
                $userName = $log->user ? ($log->user->name ?? ($log->user->first_name . ' ' . $log->user->last_name)) : 'System / Auto';
                
                $desc = htmlspecialchars($log->description);

                $hasJsonData = !empty($log->old_data) && !empty($log->new_data) && is_array($log->new_data);

                if (preg_match('/Changes:\s*\[(.*?)\]/is', $desc, $matches)) {
                    if ($hasJsonData) {
                        $desc = str_replace($matches[0], '', $desc);
                    } else {
                        $changesStr = $matches[1];
                        $changesArr = explode(', ', $changesStr);
                        
                        $ul = '<ul class="mb-2 ps-3 mt-1" style="font-size:13px; color:#334155; list-style-type: square;">';
                        foreach($changesArr as $ch) {
                            $ch = str_replace("'", "<b class='text-danger'>", $ch);
                            $ch = str_replace("➞", "</b> <i class='mdi mdi-arrow-right text-muted'></i> <b class='text-success'>", $ch);
                            $ul .= "<li style='margin-bottom: 4px;'>{$ch}</li>";
                        }
                        $ul .= '</ul>';
                        
                        $desc = str_replace($matches[0], $ul, $desc);
                    }
                }

                $desc = str_replace('Order updated.', '<div class="fw-bold text-dark mb-1"><i class="mdi mdi-check-circle text-success"></i> Order Updated:</div>', $desc);
                $desc = str_replace('No primary fields modified.', '<span class="text-muted"><i class="mdi mdi-information-outline"></i> No primary fields modified.</span>', $desc);
                $desc = str_replace('(Product items were modified).', '<div class="badge bg-warning text-dark border mt-1"><i class="mdi mdi-cube-outline"></i> Product items were modified</div>', $desc);
                
                $desc = preg_replace('/(status changed to) ([a-zA-Z0-9_ ]+)/i', '$1 <span class="badge bg-info text-white px-2 py-1">$2</span>', $desc);

                $desc = preg_replace_callback('/Assigned to User ID: (\d+)/i', function($matches) {
                    $userId = $matches[1];
                    $user = User::find($userId);
                    $name = $user ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) : '';
                    if (empty($name) && $user) $name = $user->name ?? '';
                    $name = !empty($name) ? $name : "Unknown (ID: {$userId})";
                    return 'Assigned to <span class="badge bg-primary text-white px-2"><i class="mdi mdi-account"></i> ' . $name . '</span>';
                }, $desc);
                
                $changesHtml = '';
                if ($hasJsonData) {
                    $changesHtml .= '<div class="bg-light p-2 rounded border mt-2"><ul class="mb-0 ps-3" style="font-size:13px; color:#475569;">';
                    foreach ($log->new_data as $key => $newValue) {
                        $oldValue = $log->old_data[$key] ?? 'N/A';
                        $keyName = ucwords(str_replace('_', ' ', $key));
                        $changesHtml .= "<li style='margin-bottom: 3px;'><strong>{$keyName}:</strong> <span class='text-danger text-decoration-line-through'>{$oldValue}</span> <i class='mdi mdi-arrow-right text-muted'></i> <span class='text-success fw-bold'>{$newValue}</span></li>";
                    }
                    $changesHtml .= '</ul></div>';
                }

                $actColor = 'bg-secondary';
                if(stripos($log->action, 'create') !== false) $actColor = 'bg-success';
                if(stripos($log->action, 'update') !== false) $actColor = 'bg-info';
                if(stripos($log->action, 'delete') !== false) $actColor = 'bg-danger';

                $html .= '<tr>';
                $html .= '<td><div class="fw-bold text-dark" style="font-size:12px;">' . $log->created_at->format('d M, Y') . '</div><div class="text-muted" style="font-size:11px;">' . $log->created_at->format('h:i A') . '</div></td>';
                $html .= '<td class="fw-bold text-primary" style="font-size:13px;">' . trim($userName) . '</td>';
                $html .= '<td><span class="badge ' . $actColor . '" style="font-size:10px;">' . strtoupper($log->action) . '</span></td>';
                $html .= '<td style="line-height: 1.6;">';
                
                $html .= '<div class="text-wrap" style="font-size: 13px;">' . trim($desc) . '</div>';
                $html .= $changesHtml;
                
                $html .= '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr><td colspan="4" class="text-center text-muted py-4">No history found for this order.</td></tr>';
        }
        
        $html .= '</tbody></table></div>';

        return response()->json([
            'html' => $html, 
            'invoice' => $invoiceNo,
            'title' => 'Order Log: #' . $id . ' | Inv: ' . $invoiceNo
        ]);
    }

    public function undoActivity($id)
    {
        try {
            $log = ActivityLog::findOrFail($id);

            if (empty($log->old_data)) {
                return response()->json(['status' => false, 'msg' => 'No previous data found to undo!']);
            }

            if ($log->order_id) {
                $order = Order::find($log->order_id);
                if (!$order) {
                    return response()->json(['status' => false, 'msg' => 'Order not found in database!']);
                }

                $oldData = is_string($log->old_data) ? json_decode($log->old_data, true) : $log->old_data;
                $order->update($oldData);

                logActivity('Undo', 'Order', "Reverted action ID #{$log->id}. Restored previous state.", $order->id);

                return response()->json(['status' => true, 'msg' => 'Action reverted successfully! Order restored.']);
            }

            return response()->json(['status' => false, 'msg' => 'Rollback not supported for this action.']);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'msg' => $e->getMessage()]);
        }
    }
}