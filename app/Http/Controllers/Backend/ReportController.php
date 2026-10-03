<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\User;
use App\Models\Courier;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Exports\OrderReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

// ✅ নতুন যোগ করা মডেল
use App\Models\AdCost;
use App\Models\OtherExpense;

class ReportController extends Controller
{
    /**
     * Cost of one order line.
     *
     * Every report used to read $item->product->purchase_price, but the
     * products table column is purchase_prices — the property was always null,
     * so cost came out as 0 and "profit" equalled the full sale price on the
     * Sales, Daily Profit and Monthly Profit reports. Same precedence the
     * dashboard uses: the price captured on the line at sale time, falling
     * back to the product's current purchase price.
     */
    private static function lineCost($line)
    {
        $captured = (float) ($line->purchase_price ?? 0);
        if ($captured > 0) return $captured;

        return (float) ($line->product->purchase_prices ?? 0);
    }

    /**
     * ✅ 1. SALES REPORT (FIXED)
     */
    public function salesReport(Request $request)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '0');

        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate   = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
        $status    = $request->status ?? 'all';

        $query = Order::query()->whereBetween('created_at', [$startDate, $endDate]);

        if ($status != 'all') {
            $query->where('status', $status);
        }

        $orders = $query->with(['details.product'])
                        ->orderBy('created_at', 'desc')
                        ->get();

        // ✅ FIX: শুধুমাত্র "Delivered" অর্ডারের রেভিনিউ এবং প্রফিট হিসাব করা হলো
        $validOrders = $orders->where('status', 'Delivered');

        $totalOrders      = $orders->count(); // ব্লেডে দেখানোর জন্য সব অর্ডার
        $totalSales       = $validOrders->sum('final_amount'); 
        $totalDelivery    = $validOrders->sum('shipping_charge');
        $totalOnlinePaid  = $validOrders->whereNotNull('transaction_id')->sum('final_amount');

        $totalProductCost = 0;
        foreach ($validOrders as $order) {
            foreach ($order->details as $item) {
                // ✅ FIX: প্রোডাক্ট ডিলিট হয়ে গেলে এরর এড়ানোর জন্য
                $purchasePrice = self::lineCost($item);
                $totalProductCost += ($purchasePrice * $item->quantity);
            }
        }

        $netSales = $totalSales - $totalDelivery;
        $grossProfit = $netSales - $totalProductCost;

        $soldProducts = OrderDetails::with('product')
            ->whereIn('order_id', $validOrders->pluck('id'))
            ->select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(unit_price * quantity) as total_amount'))
            ->groupBy('product_id')
            ->orderBy('total_qty', 'desc')
            ->take(20)
            ->get();

        $dailyStats = $validOrders->groupBy(function($date) {
            return Carbon::parse($date->created_at)->format('Y-m-d');
        })->map(function ($row) {
            return $row->sum('final_amount');
        });

        $chartLabels = [];
        $chartData   = [];
        
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $chartLabels[] = $date->format('d M');
            $chartData[] = isset($dailyStats[$dateString]) ? $dailyStats[$dateString] : 0;
        }

        $orderStatuses = getOrderStatus();

        return view('backend.reports.sales', compact(
            'orders', 'totalOrders', 'totalSales', 'totalDelivery',
            'totalProductCost', 'grossProfit', 'netSales',
            'startDate', 'endDate', 'status', 'chartLabels', 'chartData',
            'totalOnlinePaid', 'soldProducts', 'orderStatuses'
        ));
    }

    /**
     * ✅ EXPORT SALES REPORT (CSV)
     */
    public function exportSales(Request $request)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '0');

        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate   = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
        $status    = $request->status ?? 'all';

        $query = Order::query()->with('details.product');
        $query->whereBetween('created_at', [$startDate, $endDate]);

        if ($status != 'all') {
            $query->where('status', $status);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $response = new StreamedResponse(function() use ($orders) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Invoice No', 'Customer Name', 'Mobile', 'Date', 'Status',
                'Payment Method', 'Transaction ID', 'Items Qty',
                'Total Amount (Tk)', 'Delivery Charge', 'Product Cost', 'Profit'
            ]);

            foreach ($orders as $order) {
                $orderCost = 0;
                foreach($order->details as $detail) {
                    $orderCost += (self::lineCost($detail) * $detail->quantity);
                }
                
                $delivery = $order->shipping_charge ?? 0;
                $netSales = $order->final_amount - $delivery;
                
                // শুধুমাত্র ডেলিভারড হলে প্রফিট দেখাবে, নাহলে ০
                $profit   = ($order->status === 'Delivered') ? ($netSales - $orderCost) : 0;

                $payMethod = $order->transaction_id ? 'Online/SSL' : 'COD';
                $trxId     = $order->transaction_id ?? 'N/A';

                fputcsv($handle, [
                    $order->invoice_no, $order->first_name . ' ' . $order->last_name,
                    $order->mobile, $order->created_at->format('d M, Y h:i A'),
                    $order->status, $payMethod, $trxId, $order->details->sum('quantity'),
                    $order->final_amount, $delivery, $orderCost, $profit
                ]);
            }
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="sales-report-'.date('Y-m-d').'.csv"');

        return $response;
    }

    /**
     * ✅ 2. ORDER REPORT
     */
    public function orderReport()
    {
        $details = OrderDetails::with(['order', 'product', 'variation'])
          ->whereHas('order', function($q){
            $q->whereNotNull('invoice_no');
          })          
          ->whereHas('product', function($q){
            $q->whereNotNull('name');
          })
          ->latest()->paginate(100);
        
        $users = User::with("roles")->whereHas("roles", function($q) {
                    $q->whereIn("name", ["admin", "worker"]);
                })->get();
 
        $couriers = Courier::all();
        
        return view('backend.reports.order', compact('details', 'users', 'couriers'));
    }
    
    public function filterOrder(Request $request)
    {      
       $details = OrderDetails::join('orders as o', 'order_details.order_id', 'o.id')
                                ->join('products as p', 'order_details.product_id', 'p.id')
                                ->join('variations as v', 'order_details.variation_id', 'v.id')
                                ->select('o.*', 'order_details.*', 'p.*', 'v.*')
                                ->where(function($query){
                                   if(!empty(request()->status)) {
                                      $query->where('o.status', request()->status);
                                   }  
                                   
                                   if(!empty(request()->input('query'))) {
                                      $query->where('o.invoice_no', 'like', '%'.request()->input('query').'%')
                                                  ->orWhere('p.name', 'like', '%'.request()->input('query').'%');
                                   }        
                                   
                                   if(!empty(request()->from && request()->to)) {
                                       $query->whereBetween('o.date', [request()->from, request()->to]);
                                   }        
                                   
                                   if(!empty(request()->assign)) {
                                      $query->where('o.assign_user_id', request()->assign);
                                   }                                      
                                   
                                   if(!empty(request()->courier)) {
                                      $query->where('o.courier_id', request()->courier);
                                   }
                                })
                                ->paginate(100)
                                ->appends($request->all());        
  
        $users = User::with("roles")->whereHas("roles", function($q) {
                    $q->whereIn("name", ["admin", "worker"]);
                })->get();
                
        $couriers = Courier::all();
        
        return view('backend.reports.order', compact('details', 'users', 'couriers'));        
    }

    public function exportOrderReport(Request $request)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '0');

        $details = OrderDetails::join('orders as o', 'order_details.order_id', 'o.id')
                                ->join('products as p', 'order_details.product_id', 'p.id')
                                ->join('variations as v', 'order_details.variation_id', 'v.id')
                                ->select('o.*', 'order_details.*', 'p.*', 'v.*')
                                ->where(function($query) use ($request) {
                                   if(!empty($request->status)) {
                                      $query->where('o.status', $request->status);
                                   }  
                                   if(!empty($request->input('query'))) {
                                      $query->where('o.invoice_no', 'like', '%'.$request->input('query').'%')
                                            ->orWhere('p.name', 'like', '%'.$request->input('query').'%');
                                   }        
                                   if(!empty($request->from && $request->to)) {
                                       $query->whereBetween('o.date', [$request->from, $request->to]);
                                   }        
                                   if(!empty($request->assign)) {
                                      $query->where('o.assign_user_id', $request->assign);
                                   }                                      
                                   if(!empty($request->courier)) {
                                      $query->where('o.courier_id', $request->courier);
                                   }
                                })
                                ->orderBy('order_details.created_at', 'desc')->get();
        
        return Excel::download(new OrderReportExport($details), 'order_report.xlsx');
    }

    /**
     * ✅ 3. PRODUCT REPORT
     */
    public function productReport()
    {
        $details = OrderDetails::Leftjoin("products as p", "order_details.product_id","p.id")  
                              ->Leftjoin("orders as o","o.id","order_details.order_id")              
                              ->select("p.id","p.name","order_details.unit_price",DB::raw("SUM(quantity) as total_qty"))
                              ->groupBy('p.id','p.name','order_details.unit_price')   
                              ->get();

        $users = User::with("roles")->whereHas("roles", function($q) {
                    $q->whereIn("name", ["admin", "worker"]);
                })->get();
 
        $couriers = Courier::all();
        
        return view('backend.reports.product', compact('details', 'users', 'couriers'));
    }
    
    public function filterProduct(Request $request){  
      
      $details = OrderDetails::Leftjoin("products as p", "order_details.product_id","p.id")  
                              ->Leftjoin("orders as o","o.id","order_details.order_id")              
                              ->select("p.id","p.name","order_details.unit_price",DB::raw("SUM(quantity) as total_qty"))
                               ->where(function($query){
                                 if(!empty(request()->status)) {
                                   $query->where('o.status', request()->status);
                                 }
                                 if(!empty(request()->from && request()->to)) {
                                      $query->whereBetween('o.date', [request()->from, request()->to]);
                                 }        
                                 if(!empty(request()->assign)) {
                                      $query->where('o.assign_user_id', request()->assign);
                                 }                                      
                                 if(!empty(request()->courier)) {
                                      $query->where('o.courier_id', request()->courier);
                                 }
                               })
                               ->groupBy('p.id','p.name','order_details.unit_price')        
                              ->get();   
        
        $users = User::with("roles")->whereHas("roles", function($q) {
                    $q->whereIn("name", ["admin", "worker"]);
                })->get();
 
        $couriers = Courier::all();
        
        return view('backend.reports.product', compact('details', 'users', 'couriers'));
    }

    /**
     * ✅ 4. USER REPORT
     */
    public function userReport(Request $request)
    {
        if ($request->ajax()) {
            $startDate = $request->startDate;
            $endDate = $request->endDate;
            $assignUser = $request->assignUser;
            
            $statuses = getOrderStatus(); 
            
            $selects = [
                'users.first_name as assign_user_name',
                'users.last_name', // ✅ Last Name যোগ করা হলো
                'orders.assign_user_id',
                DB::raw('COUNT(orders.id) as total_orders'),
                
                // ✅ প্রফেশনাল KPI ক্যালকুলেশন
                DB::raw("SUM(CASE WHEN orders.status = 'Delivered' THEN 1 ELSE 0 END) as kpi_delivered"),
                DB::raw("SUM(CASE WHEN orders.status IN ('Returning', 'Return Received', 'Return Missing', 'Cancelled') THEN 1 ELSE 0 END) as kpi_failed"),
            ];

            foreach ($statuses as $key => $label) {
                $safeColumnName = 'status_' . preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($key)); 
                $selects[] = DB::raw("SUM(CASE WHEN orders.status = '{$key}' THEN 1 ELSE 0 END) as `{$safeColumnName}`");
            }

            $query = Order::leftJoin('users', 'orders.assign_user_id', '=', 'users.id')
                ->select($selects);

            if (!empty($assignUser)) {
                $query->where('orders.assign_user_id', $assignUser);
            }

            if (!empty($startDate) && !empty($endDate)) {
                $start = Carbon::parse($startDate)->startOfDay();
                $end   = Carbon::parse($endDate)->endOfDay();
                $query->whereBetween('orders.created_at', [$start, $end]);
            } elseif (!empty($startDate)) {
                $start = Carbon::parse($startDate)->startOfDay();
                $query->where('orders.created_at', '>=', $start);
            } elseif (!empty($endDate)) {
                $end = Carbon::parse($endDate)->endOfDay();
                $query->where('orders.created_at', '<=', $end);
            }
            
            // ✅ Group By তে last_name যোগ করা হলো
            $query->groupBy('orders.assign_user_id', 'users.first_name', 'users.last_name');
            
            // ✅ প্রফেশনাল র‍্যাংকিং: Net Contribution (Delivered - Failed) এর ভিত্তিতে বড় থেকে ছোট
            $query->orderByRaw("(SUM(CASE WHEN orders.status = 'Delivered' THEN 1 ELSE 0 END) - SUM(CASE WHEN orders.status IN ('Returning', 'Return Received', 'Return Missing', 'Cancelled') THEN 1 ELSE 0 END)) DESC");
            $query->orderByRaw("SUM(CASE WHEN orders.status = 'Delivered' THEN 1 ELSE 0 END) DESC"); // টাই ব্রেকার
            
            $items = $query->paginate(20);
            
            $html = view('backend.reports.getUserData', compact('items'))->render();
            return response()->json(['success' => true, 'html' => $html]);
        }
        
        $users = User::with("roles")->whereHas("roles", function($q) {
            $q->whereIn("name", ["admin", "worker"]);
        })->get();
                
        return view('backend.reports.user', compact('users'));
    }

    /**
     * ✅ 5. DAILY PROFIT REPORT (FIXED)
     */
    public function dailyProfitReport(Request $request)
    {
        $date = $request->date ? Carbon::parse($request->date)->format('Y-m-d') : Carbon::today()->format('Y-m-d');

        $orders = Order::with('details.product')
            ->whereDate('created_at', $date)
            ->where('status', 'Delivered') // ✅ FIX: শুধুমাত্র Delivered
            ->get();

        $totalSales = $orders->sum('final_amount');
        $totalDelivery = $orders->sum('shipping_charge');
        $netSales = $totalSales - $totalDelivery;

        $totalProductCost = 0;
        foreach ($orders as $order) {
            foreach ($order->details as $item) {
                $qty = $item->quantity;
                $purchasePrice = self::lineCost($item);
                $totalProductCost += ($purchasePrice * $qty);
            }
        }

        $grossProfit = $netSales - $totalProductCost;

        $adCosts = AdCost::whereDate('date', $date)->get();
        $totalAdCost = $adCosts->sum('total_cost');

        $otherExpenses = OtherExpense::whereDate('date', $date)->get();
        $totalOtherExpense = $otherExpenses->sum('amount');

        $finalNetProfit = $grossProfit - ($totalAdCost + $totalOtherExpense);

        return view('backend.reports.daily_profit', compact(
            'date', 'orders', 'totalSales', 'netSales', 'totalProductCost',
            'grossProfit', 'adCosts', 'totalAdCost', 'otherExpenses', 'totalOtherExpense', 'finalNetProfit'
        ));
    }

    /**
     * ✅ 6. STORE AD COST
     */
    public function storeAdCost(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'platform' => 'required|string',
            'usd_amount' => 'required|numeric',
            'dollar_rate' => 'required|numeric',
        ]);

        $total_cost = $request->usd_amount * $request->dollar_rate;

        AdCost::create([
            'date' => $request->date,
            'platform' => $request->platform,
            'usd_amount' => $request->usd_amount,
            'dollar_rate' => $request->dollar_rate,
            'total_cost' => $total_cost,
        ]);

        return back()->with('success', 'Ad Cost saved successfully!');
    }

    /**
     * ✅ 7. STORE OTHER EXPENSE
     */
    public function storeOtherExpense(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'details' => 'required|string',
            'amount' => 'required|numeric',
        ]);

        OtherExpense::create([
            'date' => $request->date,
            'details' => $request->details,
            'amount' => $request->amount,
        ]);

        return back()->with('success', 'Expense saved successfully!');
    }

    /**
     * ✅ 8. MONTHLY PROFIT REPORT (FIXED)
     */
    public function monthlyProfitReport(Request $request)
    {
        $year = $request->year ?? Carbon::now()->year;
        $month = $request->month ?? Carbon::now()->month;

        $orders = Order::with('details.product')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('status', 'Delivered') // ✅ FIX: শুধুমাত্র Delivered
            ->get();

        $monthlySales = $orders->sum('final_amount');
        $monthlyDelivery = $orders->sum('shipping_charge');
        $monthlyNetSales = $monthlySales - $monthlyDelivery;

        $monthlyProductCost = 0;
        foreach ($orders as $order) {
            foreach ($order->details as $item) {
                $monthlyProductCost += self::lineCost($item) * $item->quantity;
            }
        }

        $monthlyGrossProfit = $monthlyNetSales - $monthlyProductCost;

        $monthlyAdCost = AdCost::whereYear('date', $year)->whereMonth('date', $month)->sum('total_cost');
        $monthlyOtherExpense = OtherExpense::whereYear('date', $year)->whereMonth('date', $month)->sum('amount');

        $monthlyNetProfit = $monthlyGrossProfit - ($monthlyAdCost + $monthlyOtherExpense);

        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        $chartLabels = [];
        $chartData = [];

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $currentDate = Carbon::create($year, $month, $i)->format('Y-m-d');
            $chartLabels[] = $i . ' ' . Carbon::create($year, $month)->format('M');
            
            $daySales = $orders->filter(function($order) use ($currentDate) {
                return $order->created_at->format('Y-m-d') == $currentDate;
            })->sum('final_amount');
            
            $chartData[] = $daySales;
        }

        return view('backend.reports.monthly_profit', compact(
            'year', 'month', 'monthlySales', 'monthlyNetSales', 'monthlyProductCost', 
            'monthlyGrossProfit', 'monthlyAdCost', 'monthlyOtherExpense', 'monthlyNetProfit',
            'chartLabels', 'chartData'
        ));
    }

    /**
     * ✅ 9. PRODUCT PERFORMANCE REPORT (FIXED)
     */
    public function productPerformanceReport(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate   = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
        
        $statusFilter = $request->status ?? 'all';
        $groupBy      = $request->group_by ?? 'product'; // product, date, month
        $searchQuery  = trim($request->q);

        // ✅ FIX: হেল্পারের সাথে হুবহু মিল রেখে স্ট্যাটাস সেট করা হলো
        $badStatuses = "('Cancelled', 'Returning', 'Return Received', 'Return Missing')";
        $goodStatuses = "('Delivered')";

        $baseQuery = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->leftJoin('variations', 'order_details.variation_id', '=', 'variations.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate]);

        if (!empty($searchQuery)) {
            $baseQuery->where(function($q) use ($searchQuery) {
                $q->where('products.name', 'like', '%' . $searchQuery . '%')
                  ->orWhere('products.sku', 'like', '%' . $searchQuery . '%');
            });
        }

        $summaryQuery = clone $baseQuery;
        $statusSummaryRaw = $summaryQuery->select('orders.status', DB::raw('SUM(order_details.quantity) as total_qty'))
                                      ->groupBy('orders.status')
                                      ->pluck('total_qty', 'orders.status')
                                      ->toArray();

        // ✅ FIX: Case-sensitivity প্রবলেম সলভ করার জন্য সব key কে ছোট হাতের করা হলো
        $statusSummary = [];
        foreach ($statusSummaryRaw as $dbStatus => $qty) {
            $statusSummary[strtolower($dbStatus)] = $qty;
        }

        if ($statusFilter !== 'all') {
            $baseQuery->where('orders.status', $statusFilter);
        }

        $selects = [
            'products.id',
            'products.name',
            'products.image',
            DB::raw('SUM(order_details.quantity) as total_ordered_qty'),
            DB::raw("SUM(CASE WHEN orders.status IN $goodStatuses THEN order_details.quantity ELSE 0 END) as delivered_qty"),
            DB::raw("SUM(CASE WHEN orders.status IN $badStatuses THEN order_details.quantity ELSE 0 END) as returned_qty"),
            // ✅ FIX: Revenue, Cost এবং Profit শুধুমাত্র Delivered অর্ডারের উপর ভিত্তি করে
            DB::raw("SUM(CASE WHEN orders.status IN $goodStatuses THEN (order_details.quantity * order_details.unit_price) ELSE 0 END) as total_revenue"),
            DB::raw("SUM(CASE WHEN orders.status IN $goodStatuses THEN (order_details.quantity * COALESCE(variations.purchase_price, 0)) ELSE 0 END) as total_cost"),
            DB::raw("SUM(CASE WHEN orders.status IN $goodStatuses THEN ((order_details.unit_price - COALESCE(variations.purchase_price, 0)) * order_details.quantity) ELSE 0 END) as total_profit")
        ];

        $groups = ['products.id', 'products.name', 'products.image'];

        if ($groupBy === 'date') {
            $selects[] = DB::raw('DATE(orders.created_at) as report_date');
            $groups[] = DB::raw('DATE(orders.created_at)');
        } elseif ($groupBy === 'month') {
            $selects[] = DB::raw('DATE_FORMAT(orders.created_at, "%Y-%m") as report_month');
            $groups[] = DB::raw('DATE_FORMAT(orders.created_at, "%Y-%m")');
        }

        $baseQuery->select($selects)
              ->groupBy($groups)
              ->havingRaw('total_ordered_qty > 0');

        if ($groupBy === 'date') {
            $baseQuery->orderByDesc('report_date')->orderByDesc('total_ordered_qty');
        } elseif ($groupBy === 'month') {
            $baseQuery->orderByDesc('report_month')->orderByDesc('total_ordered_qty');
        } else {
            $baseQuery->orderByDesc('total_ordered_qty');
        }

        $productPerformance = $baseQuery->paginate(50);
        $orderStatuses = getOrderStatus();

        return view('backend.reports.product_performance', compact('productPerformance', 'startDate', 'endDate', 'statusFilter', 'groupBy', 'orderStatuses', 'searchQuery', 'statusSummary'));
    }

    /**
     * ✅ 10. COURIER PERFORMANCE REPORT (FIXED)
     */
    public function courierPerformanceReport(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate   = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();

        // ✅ FIX: হেল্পারের সাথে হুবহু মিল রেখে স্ট্যাটাস কুয়েরি
        $courierPerformance = DB::table('orders')
            ->leftJoin('couriers', 'orders.courier_id', '=', 'couriers.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNotNull('orders.courier_id')
            ->select(
                'couriers.name as courier_name',
                DB::raw('COUNT(orders.id) as total_assigned'),
                DB::raw("SUM(CASE WHEN orders.status = 'Delivered' THEN 1 ELSE 0 END) as total_delivered"),
                DB::raw("SUM(CASE WHEN orders.status IN ('Cancelled', 'Returning', 'Return Received', 'Return Missing') THEN 1 ELSE 0 END) as total_returned"),
                DB::raw("SUM(CASE WHEN orders.status IN ('Pending', 'Confirmed', 'Scheduled', 'Processing', 'On Hold', 'Shipped') THEN 1 ELSE 0 END) as total_processing")
            )
            ->groupBy('orders.courier_id', 'couriers.name')
            ->orderByDesc('total_assigned')
            ->get();

        return view('backend.reports.courier_performance', compact('courierPerformance', 'startDate', 'endDate'));
    }

}