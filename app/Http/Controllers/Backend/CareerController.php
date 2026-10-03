<?php

namespace App\Http\Controllers\Backend;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Career;
use File;

class CareerController extends Controller
{
    public function index()
    {
        $items = Career::all();
        return view('backend.career.index', compact('items'));
    }
    
    
    public function create()
    {
        return view('backend.career.create');
    }
    

    public function store(Request $request)
    {

        
        $data=$request->validate([
             'title'=> 'required',
             'description'=> 'required',
        ]);
        
        
        if($request->hasFile('image')) {
            $originName = $request->file('image')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $extension = $request->file('image')->getClientOriginalExtension();
            $fileName =$fileName.time().'.'.$extension;
        
            $request->file('image')->move(public_path('career'), $fileName);
            $data['image']=$fileName;
        }
        

        Career::create($data);
    
        return response()->json(['status'=>true ,'msg'=>'Career  Is Created !!']);
    }
    
    
    
    public function edit($id,Request $request)
    {

        $item=Career::find($id);
        return view('backend.career.edit', compact('item'));
        
    }
    
    
    public function update($id,Request $request)
    {

        $item=Career::find($id);
        
        $data=$request->validate([
             'title'=> 'required',
             'description'=> 'required',
        ]);
        
        
        if($request->hasFile('image')) {
            deleteImage('career', $item->image);
            $originName = $request->file('image')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $extension = $request->file('image')->getClientOriginalExtension();
            $fileName =$fileName.time().'.'.$extension;
        
            $request->file('image')->move(public_path('career'), $fileName);
            $data['image']=$fileName;
        }
        

        $item->update($data);
    
        return response()->json(['status'=>true ,'msg'=>'Career  Is Updated !!']);
    }
    
    public function destroy($id)
    {
        if(!auth()->user()->can('product.delete'))
        {
            abort(403, 'unauthorized');
        }

        $product=Career::find($id);
        deleteImage('career', $product->image);
        $product->delete();
        return response()->json(['status'=>true ,'msg'=>'Career Is Deleted !!']);

    }
    
}
