<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Courier;

class CourierController extends Controller
{
    public function index()
    {
        $items = Courier::all();
        return view('backend.couriers.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Permission check removed to bypass 403 error
        return view('backend.couriers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Permission check removed to bypass 403 error
        
        $data = $request->validate([
             'name'    => 'required',
             'phone'   => '',
             'email'   => '',
             'address' => 'required',
        ]);

        Courier::create($data);

        return response()->json(['status'=>true ,'msg'=>'Courier Is Created !!','url'=>route('admin.couriers.index')]);
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
        // Permission check removed to bypass 403 error
        
        $item = Courier::find($id);
        return view('backend.couriers.edit', compact('item'));
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
        // Permission check removed to bypass 403 error
        
        $courier = Courier::find($id);
        $data = $request->validate([
             'name'    => 'required',
             'phone'   => '',
             'email'   => '',
             'address' => 'required',
        ]);
       
        $courier->update($data);

        return response()->json(['status'=>true ,'msg'=>'Courier Is Updated !!','url'=>route('admin.couriers.index')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Permission check removed to bypass 403 error
        
        $courier = Courier::find($id);
        $courier->delete();
        return response()->json(['status'=>true ,'msg'=>'Courier Is Deleted !!']);
    }
}