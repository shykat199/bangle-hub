<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Variation;
use App\Utils\Util;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CashSaleController extends Controller
{
    private $util;

    public function __construct(Util $util)
    {
        $this->util = $util;
    }

    private function generateUniqueInvoice(): string
    {
        do {
            $candidate = 'CS-' . random_int(111111, 999999);
        } while (Order::where('invoice_no', $candidate)->exists());
        return $candidate;
    }

    public function index(Request $request)
    {
        if (!auth()->user()->can('order.view')) abort(403, 'unauthorized');

        // Note: Cash sale filter korar jonno amra assume korchi "delivery_charge_id" null ba courier_id null thakbe. 
        // Sobcheye valo hoy jodi "orders" table e 'is_pos' ba 'order_type' column thake.
        $query = Order::with(['details.product'])
            ->whereNull('courier_id')
            ->where('shipping_charge', 0)
            ->where('invoice_no', 'like', 'CS-%') // <-- এই লাইনটি ওয়েবসাইটের অর্ডারগুলোকে আটকে দেবে
            ->orderBy('id', 'desc');

        $items = $query->paginate(100);

        return view('backend.cash_sales.index', compact('items'));
    }

    public function create()
    {
        return view('backend.cash_sales.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('order.create')) abort(403, 'unauthorized');

        $data = $request->validate([
            'first_name'   => 'required',
            'mobile'       => 'nullable',
            'shipping_address' => 'nullable',
            'date'         => 'required',
            'final_amount' => 'required|numeric',
        ]);

        // 'completed' was not one of the system's statuses (see getOrderStatus),
        // so walk-in sales fell outside every profit report and their stock was
        // never returned when one was cancelled. 'Delivered' is what a handed-
        // over-the-counter sale actually is, and every report already counts it.
        $data['status'] = 'Delivered';
        $data['shipping_charge'] = 0;
        $data['discount'] = $request->discount ?? 0;
        $data['amount'] = $data['final_amount'] + $data['discount'];
        $data['user_id'] = auth()->id();
        $data['invoice_no'] = $this->generateUniqueInvoice();
        $data['courier_id'] = null; // No courier for cash sale

        DB::beginTransaction();
        try {
            $order = Order::create($data);
            $lines = [];

            if (isset($request->product_id) && is_array($request->product_id)) {
                foreach ($request->product_id as $k => $pid) {
                    $qty = (int)($request->quantity[$k] ?? 1);
                    $vid = (int)($request->variation_id[$k] ?? 0);
                    $lines[] = [
                        'product_id'   => (int)$pid,
                        'quantity'     => $qty,
                        'variation_id' => $vid,
                        'unit_price'   => (float)($request->unit_price[$k] ?? 0),
                        'discount'     => 0,
                    ];
                    
                    // Stock decrease
                    $this->util->decreaseProductStock((int)$pid, (int)$vid, $qty);
                }
            }

            if (empty($lines)) {
                throw new \Exception('No products added to the sale.');
            }

            $order->details()->createMany($lines);
            DB::commit();

            if (function_exists('logActivity')) {
                logActivity('Cash Sale', 'Order', "Cash Sale #{$order->id} created.", $order->id);
            }

            return response()->json(['status' => true, 'msg' => 'Cash Sale Completed Successfully!', 'url' => route('admin.cash_sales.index')]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cash Sale Failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'msg' => $e->getMessage()]);
        }
    }
}