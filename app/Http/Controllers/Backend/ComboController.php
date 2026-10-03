<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Combo;


class ComboController extends Controller
{
    public function index()
    {
        if(!auth()->user()->can('combo.view'))
        {
            abort(403, 'unauthorized');
        }
        $items=Combo::with('products','product')->paginate(30);
        $q=request('q');
        
        return view('backend.combos.index', compact('items','q'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(!auth()->user()->can('combo.create'))
        {
            abort(403, 'unauthorized');
        }
        $products=Product::with('sizes')->get();
        $items=ProductSize::with('product','size')->get();
        
        
        
        return view('backend.combos.create', compact('products','items'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->can('combo.create'))
        {
            abort(403, 'unauthorized');
        }

        $data=$request->validate([
             'product_id'=> 'required',
        ]);

        $combo=Combo::create($data);
        
        if(isset($request->products)){
            $product_data=[];
            foreach($request->products as $key=>$id){
                
                $ps=ProductSize::find($id);
                $product_data[]=[
                        'product_id'=>$ps->product_id,
                        'size_id'=>$ps->size_id,
                        'quantity'=>$request->quantity[$key]
                    ];
                
            }
            
            if(!empty($product_data)){
                $combo->products()->createMany($product_data);
            }
        }

        return response()->json(['status'=>true ,'msg'=>'Combo Is  Created !!','url'=>route('admin.combos.index')]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item=Combo::find($id);
        return view('backend.combos.show', compact('item'));
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(!auth()->user()->can('combo.edit'))
        {
            abort(403, 'unauthorized');
        }

        $item=Combo::find($id);
        
        $products=Product::with('sizes')->get();
        $items=ProductSize::with('product','size')->get();
        return view('backend.combos.edit', compact('item','items','products'));
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
        if(!auth()->user()->can('combo.edit'))
        {
            abort(403, 'unauthorized');
        }
        
        $combo=Combo::find($id);

        $data=$request->validate([
             'product_id'=> 'required',
        ]);

        $combo->update($data);
        
        if($combo->products()->count()){
            $combo->products()->delete();
        }
        
        if(isset($request->products)){
            $product_data=[];
            foreach($request->products as $key=>$id){
                
                $ps=ProductSize::find($id);
                $product_data[]=[
                        'product_id'=>$ps->product_id,
                        'size_id'=>$ps->size_id,
                        'quantity'=>$request->quantity[$key]
                    ];
                
            }
            
            if(!empty($product_data)){
                $combo->products()->createMany($product_data);
            }
        }
        

        return response()->json(['status'=>true ,'msg'=>'Combo Is Updated !!','url'=>route('admin.combos.index')]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(!auth()->user()->can('combo.delete'))
        {
            abort(403, 'unauthorized');
        }

        $combo=Combo::find($id);
        
        if($combo->products()->count()){
            $combo->products()->delete();
        }
        
        $combo->delete();
        return response()->json(['status'=>true ,'msg'=>'Combo Is Deleted !!']);

    }
    
    
}
