<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Information;
class UserOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        // Route has no auth middleware, so a logged-out visitor used to hit a
        // 500 here instead of being asked to log in.
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $id=auth()->user()->id;

        $status=$request->status;
        $q=$request->q;
        $items=Order::where('user_id', $id)->latest()->paginate(30);
        return view('frontend.dashboard.orders', compact('items','q','status'));
    }
    
        

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item=Order::find($id);

        // Without a guard the order id was the only thing standing between a
        // visitor and every customer's name, phone and address — walking
        // /orders/1, /orders/2 … dumped the whole customer list.
        // canViewPlacedOrder allows: the owner, staff roles, the ?t= order
        // token (guest invoice links), or the just-ordered session.
        if (!canViewPlacedOrder($item)) {
            if (!auth()->check()) {
                return redirect()->route('login');
            }
            abort(404);
        }

		$info=Information::first();
        return view("frontend.dashboard.show", compact('item', 'info'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
