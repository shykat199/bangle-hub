<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Size;

class ProductDiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items=Product::whereNotNull('discount_type')->paginate(30);
        return view('backend.product_discounts.index', compact('items'));
    }
    
    public function free_shipping() 
    {
        $items=Product::whereNotNull('is_free_shipping')->paginate(30);
        return view('backend.product_discounts.free_shipping', compact('items'));
    }
    
    public function create_free_shipping() {
        return view('backend.product_discounts.create_free_shipping');
    }
    
    public function store_free_shipping(Request $request) {
         
        if (isset($request->product_id)) {
            
            foreach ($request->product_id as $key => $product_id) {
                $product=Product::find($product_id);
                $data=[
                        'is_free_shipping'=>1
                ];
                $product->update($data);
            }
        }

        return response()->json(['status'=>true ,'msg'=>'Free Shipping Added Successfully !!','url'=>route('admin.free_shipping')]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(!auth()->user()->can('discount.create'))
        {
            abort(403, 'unauthorized');
        }

        return view('backend.product_discounts.create');
    }

    public function getDiscountProduct(Request $request){

        $data = Product::select("name as value", "id")
                    ->where('name', 'LIKE', '%'. $request->get('search'). '%')
                    ->get();
    
        return response()->json($data);

    }
    
    public function productEntry2(Request $request){

        $product = Product::select("*")
                    ->where('id',$request->get('product_id'))
                    ->get();

        if ($product) {
            $html='';
            foreach ($product as $key => $item) {
               $html.='<tr>
                        <td>'.$item->name.'</td>
                        <td>'.$item->category->name.'</td>
                        <td class="sell_price">'.$item->sell_price.'</td>
                       
                        <td>
                            <input type="hidden" name="product_id[]" value="'.$item->id.'">
                        </td>

                        <td>
                            <a class="remove action-icon"> <i class="mdi mdi-delete"></i></a>
                        </td>
                    </tr>';
            }
            return response()->json(['data'=>$html]);
             
        }else{
            return response()->json(['data'=>'']);
        }
    
        

    }

    public function productEntry(Request $request){

        $product = Product::select("*")
                    ->where('id',$request->get('product_id'))
                    ->get();

        if ($product) {
            $html='';
            foreach ($product as $key => $item) {
               $html.='<tr>
                        <td>'.$item->name.'</td>
                        <td>'.$item->category->name.'</td>
                        <td class="sell_price">'.$item->sell_price.'</td>

                        <td>
                            <select class="form-control dicount_type" name="dicount_type[]">
                                <option value="fixed">Fixed</option>
                                <option value="percentage">Percentage</option>
                            </select>
                        </td>
                        <td>
                            <input type="number" step="any" name="dicount_amount[]" class="form-control dicount_amount" value="0">
                            <input type="hidden" name="product_id[]" value="'.$item->id.'">
                        </td>

                        <td>
                            <input type="number" step="any" name="after_discount[]" class="form-control after_discount" value="0">
                        </td>

                        <td>
                            <a class="remove action-icon"> <i class="mdi mdi-delete"></i></a>
                        </td>
                    </tr>';
            }
            return response()->json(['data'=>$html]);
             
        }else{
            return response()->json(['data'=>'']);
        }
    
        

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        if(!auth()->user()->can('discount.create'))
        {
            abort(403, 'unauthorized');
        }

        // $data=$request->validate([
        //      'name'=> 'required'
        // ]);

        if (isset($request->product_id)) {
            
            foreach ($request->product_id as $key => $product_id) {
                $product=Product::find($product_id);
                $dis_amount=$product->sell_price - $request->after_discount[$key];
                $data=[
                        'discount_type'=>$request->dicount_type[$key],
                        'after_discount'=>$request->after_discount[$key],
                        'discount'=>$request->dicount_amount[$key],
                        'dicount_amount'=>$dis_amount,
                ];

                $product->update($data);
            }
        }

        return response()->json(['status'=>true ,'msg'=>'Product Is  Created !!','url'=>route('admin.product_discounts.index')]);
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
        if(!auth()->user()->can('discount.edit'))
        {
            abort(403, 'unauthorized');
        }

       
        $item=Product::find($id);

        return view('backend.product_discounts.edit', compact('item'));
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
        if(!auth()->user()->can('discount.edit'))
        {
            abort(403, 'unauthorized');
        }

        $product=Product::find($id);
        $dis_amount=$product->sell_price - $request->after_discount;
        $data=[
                'discount_type'=>$request->dicount_type,
                'after_discount'=>$request->after_discount,
                'discount'=>$request->dicount_amount,
                'dicount_amount'=>$dis_amount,
        ];
        $product->update($data);
        return response()->json(['status'=>true ,'msg'=>'Product Discount Is Updated !!','url'=>route('admin.product_discounts.index')]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     
    public function fshippingdestroy(Request $request) {
        $product = Product::find($request->product_id);
        $data=[
             'is_free_shipping'=> null
        ];
        $product->update($data);
        return response()->json(['status'=>true ,'msg'=>'Free Shipping Is Deleted !!']);
    } 
     
    public function destroy($id)
    {
        if(!auth()->user()->can('discount.delete'))
        {
            abort(403, 'unauthorized');
        }

        $product=Product::find($id);
        $data=[
                'discount_type'=>null,
                'after_discount'=>0,
                'dicount_amount'=>0,
        ];

        $product->update($data);
        return response()->json(['status'=>true ,'msg'=>'Product Is Deleted !!']);

    }
}
