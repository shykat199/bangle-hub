<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index()
    {
        $q=request()->q;
        $query=Supplier::query();
                if(!empty($q)){
                    $query->where(function($row) use ($q){
                        $row->where('name','Like','%'.$q.'%');
                        $row->orwhere('mobile','Like','%'.$q.'%');
                        $row->orwhere('address','Like','%'.$q.'%');
                        $row->orwhere('contact_id','Like','%'.$q.'%');
                    });
                }
     
        $items=$query->latest()->paginate(30);
        
        return view('backend.suppliers.index', compact('items','q'));
    }
    
    
    public function create()
    {
        return view('backend.suppliers.create');
    }
    

    public function store(Request $request)
    {

        
        $data=$request->validate([
             'name'=> 'required',
             'mobile'=> 'required',
             'address'=> 'required',
             'contact_id'=> 'nullable|unique:suppliers,contact_id',
        ]);
        

        Supplier::create($data);
    
        return response()->json(['status'=>true ,'msg'=>'Supplier  Is Created !!']);
    }
    
    
    public function show($id)
    {

        $item=Supplier::with('purchase')->find($id);
        
        $purchase=DB::table('purchases')
                    ->where('purchases.supplier_id', $id)
                    ->leftJoin('purchase_payments as pp','pp.purchase_id','purchases.id')
                    ->select('purchases.id','purchases.amount', DB::raw("IFNULL(SUM(pp.amount),0) as paid"))
                    ->groupBy('purchases.id', 'purchases.amount')
                    ->get();
        
                    
        return view('backend.suppliers.show', compact('item','purchase'));
        
    }
    
    public function edit($id,Request $request)
    {

        $item=Supplier::find($id);
        return view('backend.suppliers.edit', compact('item'));
        
    }
    
    
    public function update($id,Request $request)
    {

        $item=Supplier::find($id);
        
         $data=$request->validate([
             'name'=> 'required',
             'mobile'=> 'required',
             'address'=> 'required',
             'contact_id'=> 'nullable|unique:suppliers,contact_id,'.$id,
        ]);
    
        $item->update($data);
    
        return response()->json(['status'=>true ,'msg'=>'Supplier  Is Updated !!']);
    }
    
    public function destroy($id)
    {
        if(!auth()->user()->can('product.delete'))
        {
            abort(403, 'unauthorized');
        }

        $product=Supplier::find($id);
        $product->delete();
        return response()->json(['status'=>true ,'msg'=>'Supplier Is Deleted !!']);

    }
    
    
}
