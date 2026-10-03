<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Product;
use App\Models\User;
use App\Models\Expense;
use App\Models\ProductReview;
use App\Models\ProductStock;
use App\Models\Information;
use App\Services\MasterNoticeService;
use Auth;
use Carbon\Carbon;
use DB;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        if (!auth()->user()->can('dashboard.access')) {
            abort(403, 'unauthorized');
        }

        $status = $request->status;
        $q      = $request->q;

        $query = Order::whereHas('details.product', function ($q) {
            $q->whereNotNull('name');
        });

        if (!empty($q)) {
            $query->where(function ($row) use ($q) {
                $row->where('invoice_no', 'Like', '%'.$q.'%');
            });
        }

        if (!empty($status)) {
            $query->where('status', 'Like', '%'.$status.'%');
        }

        if (Auth::user()->hasRole('worker')) {
            $query->where('assign_user_id', Auth::id());
        }

        $items         = $query->latest()->take(20)->get();
        $statuses      = getOrderStatus();
        $total_stocks  = Product::sum('stock_quantity');
        $isWorker      = Auth::user()->hasRole('worker');
        $masterNotices = MasterNoticeService::fetch();

        return view('backend.dashboard', compact('items', 'status', 'q', 'statuses', 'total_stocks', 'isWorker', 'masterNotices'));
    }

    /**
     * KPI JSON (Top Cards + Profit Analytics + Chart)
     */
    public function getDashboardData2(Request $request)
    {
        $user      = auth()->user();
        $userStart = optional($user->created_at)?->startOfDay() ?? now()->startOfDay();

        // ডিফল্ট স্টার্ট ডেট 2026-01-01
        $startDateUi = $request->filled('startDate') ? $request->startDate : '2026-01-01';
        $endDateUi   = $request->filled('endDate')   ? $request->endDate   : now()->toDateString();

        $start = Carbon::parse($startDateUi)->startOfDay();
        $end   = Carbon::parse($endDateUi)->addDay()->startOfDay();

        if ($start->lt($userStart)) $start = $userStart->copy();
        if ($end->lte($start))      $end   = $start->copy()->addDay();

        $base = Order::query()
            ->where('orders.created_at', '>=', $start)
            ->where('orders.created_at', '<',  $end)
            ->when($user->hasRole('worker') && !$user->can('order.view_all'), function ($q) use ($user) {
                $q->where('orders.assign_user_id', $user->id);
            });

        // 1. Basic Stats
        $total_orders      = (clone $base)->count();
        $pending_orders    = (clone $base)->whereIn('orders.status', ['Pending', 'pending'])->count();
        $complete_orders   = (clone $base)->whereIn('orders.status', ['Complete', 'completed', 'Delivered', 'delivered'])->count();

        // Cancel & Return আলাদা
        $cancel_orders     = (clone $base)->whereIn('orders.status', ['Cancelled', 'cancelled'])->count();
        $return_orders     = (clone $base)->whereIn('orders.status', ['Returning', 'Return Received', 'Return Missing'])->count();
        $cancell_orders    = $cancel_orders + $return_orders;

        // শুধুমাত্র 'Incomplete' স্ট্যাটাসের অর্ডার কাউন্ট
        $incomplete_orders = (clone $base)->where('orders.status', 'Incomplete')->count();

        // 2. Sales & Costs Stats (Delivered Only)
        $deliveredOrders = clone $base;
        $deliveredOrders->whereIn('orders.status', ['Complete', 'completed', 'Delivered', 'delivered']);

        $sell_amount     = (clone $deliveredOrders)->sum('orders.final_amount');
        $shipping_charge = (clone $deliveredOrders)->sum('orders.shipping_charge');
        $total_discount  = (clone $deliveredOrders)->sum('orders.discount');

        // NEW BOXES CALCULATIONS
        $total_sale_val       = $sell_amount;
        $total_courier_val    = $shipping_charge;
        // Order Value = date range-er SOB order (status jai hok) er final_amount, courier/shipping charge bade.
        // Age shudhu Delivered order dhora hoto, tai "Total Order" card-er sathe milto na.
        $total_order_val      = (clone $base)->sum('orders.final_amount') - (clone $base)->sum('orders.shipping_charge');

        // শুধুমাত্র 'Incomplete' স্ট্যাটাসের টাকার হিসাব (কুরিয়ার চার্জ বাদে)
        $incomplete_query     = (clone $base)->where('orders.status', 'Incomplete');
        $total_incomplete_val = $incomplete_query->sum('orders.final_amount') - $incomplete_query->sum('orders.shipping_charge');

        // Return Value Calculation
        $total_return_val     = (clone $base)->whereIn('orders.status', ['Returning', 'Return Received', 'Return Missing'])
                                             ->sum('orders.final_amount');

        // 3. Purchase Cost of Sold Items
        $purchaseCostQuery = clone $deliveredOrders;
        $total_purchase_cost = $purchaseCostQuery
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->select(DB::raw('
                SUM(
                    IF(order_details.purchase_price IS NOT NULL AND order_details.purchase_price > 0,
                        order_details.purchase_price,
                        products.purchase_prices
                    ) * order_details.quantity
                ) as total_cost
            '))->value('total_cost') ?: 0;

        // 4. Other Expenses (Expense Model)
        $totalExpense  = Expense::whereDate('date', '>=', $start->toDateString())
                            ->whereDate('date', '<',  $end->toDateString())
                            ->sum('amount');

        // ✅ আপডেট করা হয়েছে: Expense কে Sourcing Cost এর সাথে যোগ করা হয়েছে
        $combined_sourcing_cost = $total_purchase_cost + $totalExpense;

        // 5. Net Profit & Gross Profit Calculation
        $product_revenue = $sell_amount - $shipping_charge; // এটি টোটাল অর্ডার ভ্যালু ($total_order_val)
        
        // ✅ আপডেট করা হয়েছে: Gross Profit = Order Value - Sourcing Cost (যাতে ড্যাশবোর্ডের ভিজ্যুয়াল হিসেব হুবহু মিলে যায়)
        $grossProfit     = $product_revenue - $combined_sourcing_cost;
        
        // Discount is already excluded from final_amount, so adding it back
        // here counted every coupon as income and made net profit render
        // larger than gross profit on the same screen.
        $netProfit       = $grossProfit;

        // 6. Stock Warning Logic & Other Global Stats
        $threshold = Information::orderBy('id', 'desc')->value('stock_warning_limit') ?? 5;
        $lowStockCount = ProductStock::where('quantity', '<=', $threshold)->count();
        $lowStockItems = ProductStock::with(['product', 'variation.size', 'variation.color'])
                            ->where('quantity', '<=', $threshold)
                            ->orderBy('quantity', 'asc')
                            ->limit(10)
                            ->get();

        $total_products  = Product::count();
        $total_employees = User::whereHas('roles', fn($q) => $q->where('name', 'worker'))->count();
        $total_stocks    = Product::sum('stock_quantity');

        // 7. Chart Data Logic (Daily Sales)
        $chartQuery = clone $deliveredOrders;
        $chartData = $chartQuery->select(
            DB::raw('DATE(orders.created_at) as date'),
            DB::raw('SUM(orders.final_amount) as total')
        )
        ->groupBy(DB::raw('DATE(orders.created_at)'))
        ->orderBy('date', 'ASC')
        ->get();

        $chart_dates  = $chartData->pluck('date');
        $chart_totals = $chartData->pluck('total');

        return response()->json([
            'success'              => true,
            'profit'               => $netProfit,
            
            // 'Other Expense' এর মান 0 করে দেওয়া হলো এবং Expense কে Sourcing Cost এ যুক্ত করা হলো
            'totalExpense'         => 0, 
            'sourcing_cost'        => $combined_sourcing_cost, 
            
            'total_orders'         => $total_orders,
            'pending_orders'       => $pending_orders,
            'complete_orders'      => $complete_orders,
            'cancell_orders'       => $cancell_orders,
            'cancel_orders'        => $cancel_orders,
            'return_orders'        => $return_orders,
            'gross_profit'         => $grossProfit,
            'incomplete_orders'    => $incomplete_orders,
            'sell_amount'          => $sell_amount,
            'purchase_cost'        => $total_purchase_cost,
            'low_stock_count'      => $lowStockCount,
            'low_stock_items'      => $lowStockItems,
            'chart_dates'          => $chart_dates,
            'chart_totals'         => $chart_totals,

            // Values
            'total_sale_val'       => $total_sale_val,
            'total_order_val'      => $total_order_val,
            'total_courier_val'    => $total_courier_val,
            'total_incomplete_val' => $total_incomplete_val,
            'total_return_val'     => $total_return_val,

            // Global/Other Stats
            'total_products'       => $total_products,
            'total_employees'      => $total_employees,
            'total_stocks'         => $total_stocks,
        ]);
    }

    public function index()
    {
        $s = request('q');
        $query = ProductReview::latest();

        if (!empty($s)) {
            $query->where(function ($row) use ($s) {
                $row->where('name', 'Like', '%'.$s.'%');
            });
        }

        $data = $query->paginate(30);
        return view('backend.review.index', compact('data'));
    }

    public function destroy($id)
    {
        ProductReview::destroy($id);
        return response()->json(['status' => true, 'msg' => 'Review has been deleted']);
    }

    public function getDashboardData(Request $request)
    {
        $workerCount = User::whereHas('roles', function ($query) {
            $query->where('name', 'worker');
        })->count();

        $data['products']           = Product::count();
        $data['orders']             = Order::count();
        $data['users']              = $workerCount;

        $data['current_month_sell'] = Order::whereBetween('orders.created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('final_amount');
        $data['today_sell']         = Order::whereDate('orders.created_at', now()->toDateString())->sum('final_amount');
        $data['prev_month_sell']    = Order::whereBetween('orders.created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])->sum('final_amount');

        return view('backend.partials.dashboard_data', $data);
    }

    public function reviewAction(Request $request)
    {
        $ids = $request->ids ?? [];
        if (empty($ids)) { return response()->json(['status' => false, 'msg' => 'No reviews selected!']); }
        if ($request->has('delete')) {
            ProductReview::whereIn('id', $ids)->delete();
            return response()->json(['status' => true, 'msg' => 'Selected reviews deleted successfully!']);
        }
        if ($request->has('status')) {
            $status = $request->status == 1 ? 1 : 0;
            ProductReview::whereIn('id', $ids)->update(['status' => $status]);
            $msg = $status ? 'Selected reviews approved!' : 'Selected reviews rejected!';
            return response()->json(['status' => true, 'msg' => $msg]);
        }
        return response()->json(['status' => false, 'msg' => 'Invalid action!']);
    }
}