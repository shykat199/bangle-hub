<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeliveryCharge;
use Illuminate\Support\Facades\DB;

class DeliveryChargeController extends Controller
{
    public function index()
    {
        if(!auth()->user()->can('delivery_charge.view'))
        {
            abort(403, 'unauthorized');
        }
        
        // ১. Flat রেট আইটেমগুলো আনা হলো
        $items = DeliveryCharge::all();

        // ২. গ্লোবাল সেটিংস আনা হলো
        $globalSetting = DB::table('delivery_charges')->first();
        $chargeType = $globalSetting ? $globalSetting->charge_type : 'flat';

        // ৩. কুরিয়ার রেট আনা হলো
        $rates = DB::table('courier_rates')->get()->keyBy('courier_name');
        
        // কুরিয়ার রেট টেবিল ফাঁকা থাকলেও যেন ব্লেডে এরর না আসে, তার জন্য ডিফল্ট অবজেক্ট সেট করা হলো
        $courierRates = [];
        $couriers = ['steadfast', 'pathao', 'redx']; // আপনার সম্ভাব্য কুরিয়ারগুলোর নাম
        
        foreach ($couriers as $courier) {
            $courierRates[$courier.'_inside'] = $rates[$courier.'_inside'] ?? (object) ['base_charge' => 0, 'extra_per_kg_charge' => 0];
            $courierRates[$courier.'_outside'] = $rates[$courier.'_outside'] ?? (object) ['base_charge' => 0, 'extra_per_kg_charge' => 0];
        }

        return view('backend.delivery_charge.index', compact('items', 'chargeType', 'courierRates'));
    }

    public function globalUpdate(Request $request)
    {
        if(!auth()->user()->can('delivery_charge.edit')) {
            abort(403, 'unauthorized');
        }

        DB::table('delivery_charges')->update(['charge_type' => $request->charge_type]);

        if ($request->charge_type == 'weight_based') {
            $couriers = ['pathao', 'redx', 'steadfast'];
            $locations = ['inside', 'outside'];
            
            foreach ($couriers as $courier) {
                foreach ($locations as $loc) {
                    $key = $courier . '_' . $loc;
                    DB::table('courier_rates')->updateOrInsert(
                        ['courier_name' => $key],
                        [
                            'base_weight' => 1.00,
                            'base_charge' => $request->input($key.'_base') ?? 0,
                            'extra_per_kg_charge' => $request->input($key.'_extra') ?? 0,
                            'status' => 1,
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }

        return back()->with('success', 'Delivery Charge Settings Updated Successfully!');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(!auth()->user()->can('delivery_charge.create'))
        {
            abort(403, 'unauthorized');
        }

        return view('backend.delivery_charge.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->can('delivery_charge.create'))
        {
            abort(403, 'unauthorized');
        }

        $data = $request->validate([
             'title' => 'required',
             'amount' => 'required',
             'status' => '',
        ]);

        DeliveryCharge::create($data);

        // JSON এর পরিবর্তে Redirect ব্যবহার করা হয়েছে
        return redirect()->route('admin.delivery_charge.index')->with('success', 'Delivery Charge Is Created Successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(!auth()->user()->can('delivery_charge.edit'))
        {
            abort(403, 'unauthorized');
        }

        $item = DeliveryCharge::find($id);
        return view('backend.delivery_charge.edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if(!auth()->user()->can('delivery_charge.edit'))
        {
            abort(403, 'unauthorized');
        }

        $deliveryCharge = DeliveryCharge::find($id);
        
        $data = $request->validate([
             'title' => 'required',
             'amount' => 'required',
        ]);
        
        $data['status'] = $request->status ?? 0;
        
        $deliveryCharge->update($data);

        // JSON এর পরিবর্তে Redirect ব্যবহার করা হয়েছে
        return redirect()->route('admin.delivery_charge.index')->with('success', 'Delivery Charge Is Updated Successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(!auth()->user()->can('delivery_charge.delete'))
        {
            abort(403, 'unauthorized');
        }

        $deliveryCharge = DeliveryCharge::find($id);
        
        if($deliveryCharge) {
            $deliveryCharge->delete();
        }
        
        // Delete এর জন্যও JSON রেসপন্স রাখা হয়েছে কারণ সাধারণত ডিলিট বাটনে ক্লিক করলে sweetalert দিয়ে AJAX কাজ করে। 
        // যদি ডিলিট করলেও সাদা পেজ আসে, তবে আমাকে জানাবেন, আমি এটিও পরিবর্তন করে দেব।
        return response()->json(['status' => true, 'msg' => 'Delivery Charge Is Deleted !!']);
    }
}