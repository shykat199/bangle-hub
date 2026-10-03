<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Order, Information};
class DashboardController extends Controller
{
    public function index()
    {
        $id=auth()->user()->id;
        $items=Order::where('user_id', $id)->latest()->get();
        
        return view('frontend.dashboard.dashboard', compact('items'));
    }
    public function orders(Request $request)
    {
        $id=auth()->user()->id;

        $status=$request->status;
        $q=$request->q;
        $items=Order::where('user_id', $id)->latest()->paginate(30);
        return view('frontend.dashboard.orders','items','q','status');
    }
    public function account_details(){
        return view('frontend.dashboard.account_details');
    }
    public function wishlists(){
        return view('frontend.dashboard.wishlists');
    }
    
    public function confirmOrder($id){
        $order=Order::find($id);

        if (!canViewPlacedOrder($order)) {
            abort(404);
        }

        $info=Information::first();
        return view('frontend.dashboard.thank_you', compact('order', 'info'));
    }

    public function confirmOrderlanding($id){
        $order=Order::find($id);

        if (!canViewPlacedOrder($order)) {
            abort(404);
        }

        $info=Information::first();
        return view('frontend.dashboard.thank_you_landing', compact('order', 'info'));
    }
}
