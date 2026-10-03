<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderPayment;
use App\Models\Order;
use App\Utils\Util;
use App\Utils\ModulUtil;

class OrderPaymentController extends Controller
{
    public $modulutil;
    public $util;

    public function __construct(ModulUtil $modulutil, Util $util){

        $this->util=$util;
        $this->modulutil=$modulutil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id){

        $order=Order::find($id);
        $due=$this->modulutil->orderDue($order);
        $methods=getOrderMethod();

        return view('backend.orders.payment', compact('order','due','methods'));
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){

        $order=Order::find($id);
        $data=$request->validate([
             'amount'=> 'required',
             'method'=> 'required',
             'date'=> 'required',
             'note'=> '',
        ]);
      	if($request->amount <= 0)
        {
         	return response()->json(['status'=>false ,'msg'=>'Amount should be greater than 1 !!']); 
        }
        $data['order_id']=$id;
        OrderPayment::create($data);
        $this->modulutil->orderstatus($order);
        return response()->json(['status'=>true ,'msg'=>'Order Payment is Successfully done!!']);
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
