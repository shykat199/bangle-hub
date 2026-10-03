<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * Courier Tracking & Payment Report
 * ---------------------------------
 * তারিখ অনুযায়ী দেখায় — কোন অর্ডারগুলো কুরিয়ারে গেছে (ট্র্যাকিং আইডি সহ),
 * আর কোনগুলোর টাকা কুরিয়ার থেকে পাওয়া গেছে / এখনো বাকি।
 *
 * টাকা পাওয়ার তথ্য দুইভাবে আসে:
 *   ১. কুরিয়ারের webhook (payment_status / cod_status ইত্যাদি) — অটো
 *   ২. এই রিপোর্ট থেকে হাতে "টাকা পেয়েছি" মার্ক করে — ম্যানুয়াল
 */
class CourierTrackingReportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeView();

        $from    = $request->input('from', now()->subDays(30)->format('Y-m-d'));
        $to      = $request->input('to', now()->format('Y-m-d'));
        $courier = $request->input('courier_id');
        $payment = $request->input('payment');   // '', pending, received

        $query = $this->baseQuery($from, $to, $courier, $payment);

        $orders   = (clone $query)->orderByDesc('courier_sent_at')->paginate(100)->withQueryString();
        $summary  = $this->summary($from, $to, $courier);
        $couriers = Courier::orderBy('name')->get();

        if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'view'   => view('backend.reports.courier_tracking_rows', compact('orders'))->render(),
                'summary' => $summary,
            ]);
        }

        return view('backend.reports.courier_tracking', compact('orders', 'summary', 'couriers', 'from', 'to', 'courier', 'payment'));
    }

    /** নির্বাচিত অর্ডারগুলোকে "কুরিয়ার থেকে টাকা পেয়েছি" মার্ক করা */
    public function markPaid(Request $request)
    {
        $this->authorizeView();

        $ids = array_filter((array) $request->input('order_ids', []));
        if (empty($ids)) {
            return response()->json(['status' => false, 'msg' => 'কোনো অর্ডার সিলেক্ট করা হয়নি!']);
        }

        $paidAt = $request->input('paid_at') ?: now();
        $count  = 0;

        foreach (Order::whereIn('id', $ids)->get() as $order) {
            $order->courier_payment_status = 'received';
            $order->courier_paid_at        = $paidAt;
            // আলাদা করে টাকার অঙ্ক না দিলে অর্ডারের মোট টাকাই ধরা হয়
            $order->courier_paid_amount = $order->courier_paid_amount ?: (float) $order->final_amount;
            $order->save();
            $count++;

            if (function_exists('logActivity')) {
                logActivity('Courier Payment', 'Order', "কুরিয়ার থেকে টাকা পাওয়া মার্ক করা হলো — ৳{$order->courier_paid_amount}", $order->id);
            }
        }

        return response()->json(['status' => true, 'msg' => "{$count} টি অর্ডার 'টাকা পেয়েছি' হিসেবে মার্ক হয়েছে!"]);
    }

    /** ভুল করে মার্ক হলে আবার bakeya করা */
    public function markPending(Request $request)
    {
        $this->authorizeView();

        $ids = array_filter((array) $request->input('order_ids', []));
        if (empty($ids)) {
            return response()->json(['status' => false, 'msg' => 'কোনো অর্ডার সিলেক্ট করা হয়নি!']);
        }

        $count = Order::whereIn('id', $ids)->update([
            'courier_payment_status' => 'pending',
            'courier_paid_at'        => null,
            'courier_paid_amount'    => null,
        ]);

        return response()->json(['status' => true, 'msg' => "{$count} টি অর্ডার আবার 'বাকি' করা হলো!"]);
    }

    private function baseQuery($from, $to, $courier = null, $payment = null)
    {
        $q = Order::with('courier')
            ->whereNotNull('courier_tracking_id')
            ->where('courier_tracking_id', '<>', '');

        if (Schema::hasColumn('orders', 'courier_sent_at')) {
            $q->whereBetween('courier_sent_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
        }
        if (!empty($courier)) {
            $q->where('courier_id', $courier);
        }
        if ($payment === 'received') {
            $q->where('courier_payment_status', 'received');
        } elseif ($payment === 'pending') {
            $q->where(function ($x) {
                $x->whereNull('courier_payment_status')->orWhere('courier_payment_status', '<>', 'received');
            });
        }

        return $q;
    }

    private function summary($from, $to, $courier = null): array
    {
        $all = $this->baseQuery($from, $to, $courier)->get();

        $received = $all->where('courier_payment_status', 'received');
        $pending  = $all->where('courier_payment_status', '!=', 'received');

        return [
            'total_orders'    => $all->count(),
            'total_amount'    => (float) $all->sum('final_amount'),
            'received_count'  => $received->count(),
            'received_amount' => (float) $received->sum(fn($o) => $o->courier_paid_amount ?: $o->final_amount),
            'pending_count'   => $pending->count(),
            'pending_amount'  => (float) $pending->sum('final_amount'),
        ];
    }

    private function authorizeView(): void
    {
        if (auth()->check() && auth()->user()->hasRole('worker')) {
            abort(403, 'Unauthorized');
        }
    }
}
