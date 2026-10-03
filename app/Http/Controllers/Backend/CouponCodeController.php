<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CouponCode;
use App\Models\Information;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CouponCodeController extends Controller
{
    public function index()
    {
        $items = CouponCode::latest()->get();

        // per-coupon usage, counted from orders.coupon_code (added by EXIT_POPUP_UPDATE.sql)
        $usage = collect();
        if (Schema::hasColumn('orders', 'coupon_code')) {
            $usage = DB::table('orders')
                ->whereNotNull('coupon_code')
                ->where('status', '!=', 'incomplete')
                ->selectRaw('coupon_code, COUNT(*) as orders_count, SUM(final_amount) as orders_amount')
                ->groupBy('coupon_code')
                ->get()
                ->keyBy('coupon_code');
        }

        $popupStats = null;
        if (Schema::hasTable('exit_popup_stats')) {
            $popupStats = DB::table('exit_popup_stats')
                ->selectRaw('COALESCE(SUM(shows),0) as shows, COALESCE(SUM(copies),0) as copies')
                ->first();
        }

        // for the exit popup's "specific products" scope picker
        $productsList = Product::where('status', 1)->orderBy('name')->get(['id', 'name']);

        return view('backend.coupon_codes.index', compact('items', 'usage', 'popupStats', 'productsList'));
    }

    // Exit-intent popup: every behaviour is an admin choice — on/off, which
    // coupon, texts, repeat interval, and per-device (desktop/mobile) triggers.
    public function exitPopupSave(Request $request)
    {
        $data = $request->validate([
            'exit_popup_active'    => 'required|in:0,1',
            'exit_popup_coupon_id' => 'nullable|exists:coupon_codes,id',
            'exit_popup_title'     => 'nullable|string|max:255',
            'exit_popup_text'      => 'nullable|string|max:2000',
            'exit_popup_hours'     => 'required|integer|min:0|max:720',
            'exit_popup_desktop'   => 'required|in:0,1',
            'exit_popup_mobile'    => 'required|in:0,1',
            'exit_popup_delay_seconds' => 'required|integer|min:0|max:120',
            'exit_popup_max_shows'     => 'required|integer|min:1|max:10',
            'exit_popup_scope'         => 'required|in:all,site,landing,products',
            'exit_popup_product_ids'   => 'nullable|array',
            'exit_popup_product_ids.*' => 'integer|exists:products,id',
        ]);

        $data['exit_popup_product_ids'] = json_encode(array_map('intval', $request->input('exit_popup_product_ids', [])));

        Information::first()->update($data);

        return back()->with('success', 'Exit popup settings saved!');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // যদি পারমিশন সেট করা থাকে তবে আন-কমেন্ট করুন
        /*
        if(!auth()->user()->can('coupon_codes.create')) {
            abort(403, 'unauthorized');
        }
        */
        return view('backend.coupon_codes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /*
        if(!auth()->user()->can('coupon_codes.create')) {
            abort(403, 'unauthorized');
        }
        */

        $data = $request->validate([
            'code'               => 'required|unique:coupon_codes,code',
            'amount'             => 'required',
            'start'              => 'required',
            'end'                => 'required',
            'minimum_amount'     => 'nullable',
            'discount_type'      => 'required',
            'per_customer_limit' => 'nullable|integer|min:0',
            'total_limit'        => 'nullable|integer|min:0',
        ]);

        // Form-e faka rakhle middleware "" -> NULL kore dey, kintu ei column-gulo NOT NULL (default 0).
        // NULL gele "Column cannot be null" error-e coupon save hoto na -> 0 bosao.
        foreach (['minimum_amount', 'per_customer_limit', 'total_limit'] as $k) {
            if (!isset($data[$k]) || $data[$k] === '') $data[$k] = 0;
        }

        CouponCode::create($data);

        return response()->json([
            'status' => true,
            'msg'    => 'CouponCode Is Created !!',
            'url'    => route('admin.coupon_codes.index')
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        /*
        if(!auth()->user()->can('coupon_codes.edit')) {
            abort(403, 'unauthorized');
        }
        */

        $item = CouponCode::find($id);
        return view('backend.coupon_codes.edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        /*
        if(!auth()->user()->can('coupon_codes.edit')) {
            abort(403, 'unauthorized');
        }
        */

        $coupon = CouponCode::find($id); // Fixed variable name from $category to $coupon
        
        $data = $request->validate([
            'code'               => 'required|unique:coupon_codes,code,'.$id,
            'amount'             => 'required',
            'start'              => 'required',
            'end'                => 'required',
            'minimum_amount'     => 'nullable',
            'discount_type'      => 'required',
            'per_customer_limit' => 'nullable|integer|min:0',
            'total_limit'        => 'nullable|integer|min:0',
        ]);

        foreach (['minimum_amount', 'per_customer_limit', 'total_limit'] as $k) {
            if (!isset($data[$k]) || $data[$k] === '') $data[$k] = 0;
        }

        $coupon->update($data);

        return response()->json([
            'status' => true,
            'msg'    => 'CouponCode Is Updated !!',
            'url'    => route('admin.coupon_codes.index')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        /*
        if(!auth()->user()->can('coupon_codes.delete')) {
            abort(403, 'unauthorized');
        }
        */

        $coupon = CouponCode::find($id); // Fixed variable name
        $coupon->delete();
        
        return response()->json([
            'status' => true, 
            'msg'    => 'CouponCode Is Deleted !!'
        ]);
    }
}