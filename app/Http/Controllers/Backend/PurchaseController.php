<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductSize;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseLine;
use App\Models\PurchasePayment;
use App\Models\Supplier;
use App\Models\Variation;
use App\Utils\Util;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller 
{
    public $util;

    public function __construct(Util $util){
        $this->util=$util;
    }
    
    public function index()
    {
        $q = request('q');
      	if(!auth()->user()->can('purchase.view'))
        {
            abort(403, 'unauthorized');
        }
        $query=Purchase::with('lines');        
            if(auth()->user()->hasRole('admin')==false){
              $query->where('user_id', auth()->user()->id);
            }
            if($q){
                $query->where(function($row) use ($q){
                    $row->where('ref','Like','%'.$q.'%');
                    $row->orwhere('note','Like','%'.$q.'%');
                });
            }
        $items=$query->paginate(30);
        return view('backend.purchase.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(!auth()->user()->can('purchase.create'))
        {
            abort(403, 'unauthorized');
        }
        
        $suppliers=Supplier::all();

        return view('backend.purchase.create', compact('suppliers'));
    }

    public function getPurchaseProduct(Request $request){

        $data = Variation::join('products' ,'products.id','variations.product_id')
                    ->join('sizes' ,'sizes.id','variations.size_id')
                    ->join('colors' ,'colors.id','variations.color_id')
                    ->select("variations.id",

                        DB::raw("TRIM(CONCAT(products.name,' (',sizes.title,'),(',colors.name,')')) AS value")
                        )
                    ->where('products.name', 'LIKE', '%'. $request->get('search'). '%')
                    ->get();    
        return response()->json($data);

    }
    public function purchaseProductEntry(Request $request){

        $id=$request->id;
        $product_size=Variation::find($id);

        if ($product_size) {
            $html='<tr><td>'.$product_size->product->name.'</td>
                    <td>'.$product_size->size->title.'</td>
                    <td>'.$product_size->color->name.'</td>
                    <td>
                        <input class="form-control quantity" name="quantity[]" type="number" value="1" required min="1"/>
                        <input type="hidden" class="form-control" name="variation_id[]" type="number" value="'.$product_size->id.'" required/>
                        <input type="hidden" class="form-control" name="product_id[]" type="number" value="'.$product_size->product->id.'" required/>
                    </td>
                    <td><input class="form-control unit_price" name="unit_price[]" type="number" value="'.$product_size->product->purchase_price.'" required/></td>
                    <td class="row_total">'.$product_size->product->purchase_price.'</td>
                    <td><a class="remove btn btn-sm btn-danger"> - </tr> ';

            return response()->json(['success'=>true,'html'=>$html]);
        }else{
            return response()->json(['success'=>false,'msg'=>'Product Note Found !!']);
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
        
        // dd($request->variation_id);
        
        if(!auth()->user()->can('purchase.create'))
        {
            abort(403, 'unauthorized');
        }

        $data=$request->validate([
             'note'=> '',
             'date'=> 'required',
             'status'=> 'required',
             'discount_type'=> '',
             'discount_amount'=> '',
             'shipping_cost'=> '',
             'amount'=> 'required|numeric',
             'ref'=> '',
        ]);
        $data['user_id']=auth()->user()->id;
        $data['supplier_id']=auth()->user()->id;
        if(empty($request->ref))
        {
            $data['ref'] = 'ref'.rand(111111,999999);
        }

        DB::beginTransaction();

        try {

            $purchase=Purchase::create($data);

            if (isset($request->product_id)) {
                $data=[];

                foreach ($request->product_id as $key => $product_id) {
                    $qty=$request->quantity[$key];
                    $variation_id=$request->variation_id[$key];
                    $data[]=[
                        'product_id'=>$product_id,
                        'variation_id'=>$variation_id,
                        'quantity'=>$qty,
                        'unit_price'=>$request->unit_price[$key],
                    ];
                    $this->util->increaseProductStock($product_id,$variation_id,$qty);
                }
                $purchase->lines()->createMany($data);
            }


            // if (!empty(request('payment_amount'))) {
                
            //     $pay=[
            //         'purchase_id'=>$purchase->id,
            //         'amount'=>$request->payment_amount,
            //         'method'=>$request->method,
            //         'date'=>$request->date,
            //         'note'=>$request->pay_note,
            //         'account_id'=>$request->account_id,
            //     ];
            //     PurchasePayment::create($pay);
            // }

            // $this->util->purchaseStatus($purchase);

            DB::commit();
            return response()->json(['status'=>true ,'msg'=>'Purchase Is  Created !!','url'=> route('admin.purchase.index')]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status'=>false ,'msg'=>$e->getMessage()]);
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $purchases = DB::table('purchases')
                    ->join('suppliers', 'purchases.supplier_id', 'suppliers.id')
                    ->join('users', 'purchases.user_id', 'users.id')
                    ->where('purchases.id', $id)
                    ->select('purchases.*', 'suppliers.name', 'users.first_name', 'users.last_name')
                    ->get();

        $items=PurchaseLine::where('purchase_id', $id)->get();
        return view('backend.purchase.show', compact('items', 'purchases'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {	
      	if(!auth()->user()->can('purchase.edit'))
        {
            abort(403, 'unauthorized');
        }
        

        $item=Purchase::with('lines','lines.variation')->find($id);
        $suppliers=Supplier::all();
        return view('backend.purchase.edit', compact('item','suppliers'));
        
    }


    public function update(Request $request, $id)
    {
        if(!auth()->user()->can('purchase.edit'))
        {
            abort(403, 'unauthorized');
        }

        $purchase=Purchase::find($id);
        $data=$request->validate([
             'note'=> '',
             'date'=> 'required',
             'status'=> 'required',
             'discount_type'=> '',
             'discount_amount'=> '',
             'shipping_cost'=> '',
             'amount'=> 'required|numeric',
             'ref'=> '',
        ]);
        $data['user_id']=auth()->user()->id;

         DB::beginTransaction();

        try {

            $purchase->update($data);

            // delete purchase line and decrease product stock
            if (isset($request->purchase_line_id)) {
                $delete_line=PurchaseLine::where('purchase_id', $id)
                                ->whereNotIn('id', $request->purchase_line_id)
                                ->get();


                if ($delete_line->count()) {
                    foreach ($delete_line as $key => $line) {
                        $this->util->decreaseProductStock($line->product_id, $line->variation_id, $line->quantity);
                        $line->delete();
                    }
                }
            }
            else{
                foreach ($purchase->lines as $key => $line) {
                    $this->util->decreaseProductStock($line->product_id, $line->variation_id, $line->quantity);
                    $line->delete();
                }
            }
            // update purchase line and product stock
            if (isset($request->product_id)) {
                $data=[];
                foreach ($request->product_id as $key => $product_id) {
                    //product stock increase/decrease
                    if (isset($request->purchase_line_id[$key])) {

                        
                        $qty=$request->quantity[$key];
                        $line_id=$request->purchase_line_id[$key];
                        $line=PurchaseLine::find($line_id);



                        $this->util->updateProductStock($line->product_id, $line->variation_id, $line->quantity,$qty);
                        $line->quantity=$qty;
                        $line->unit_price=$request->unit_price[$key];
                        $line->save();

                    }
                    //product stock increase
                    else{

                       
                        $qty=$request->quantity[$key];
                        $variation_id=$request->variation_id[$key];
                        $data[]=[
                            'product_id'=>$product_id,
                            'quantity'=>$qty,
                            'variation_id'=>$variation_id,
                            'unit_price'=>$request->unit_price[$key],
                        ];
                        $this->util->increaseProductStock($product_id,$variation_id, $qty);                    
                    }
                    
                }
                if (!empty($data)) {
                    $purchase->lines()->createMany($data);
                }
                
            }

            DB::commit();
            return response()->json(['status'=>true ,'msg'=>'Purchase Is  Updated !!','url'=> route('admin.purchase.index')]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status'=>false ,'msg'=>$e->getMessage()]);
        }

        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){
        if(!auth()->user()->can('purchase.delete'))
        {
            abort('403', 'Unauthorized');
        }

        $purchase=Purchase::find($id);
      
        foreach ($purchase->lines as $key => $line) {
            $this->util->decreaseProductStock($line->product_id, $line->variation_id,$line->quantity);
            $line->delete();
        }

        $purchase->delete();
        return response()->json(['status'=>true ,'msg'=>'Deleted Successfully !!']);
        

    }


}
