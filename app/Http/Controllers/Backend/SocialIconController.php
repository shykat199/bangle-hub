<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SocialIcon;


class SocialIconController extends Controller
{
    public function index()
    {
        $items = SocialIcon::all();
        return view('backend.social_icons.index', compact('items'));
    }
    
    
    public function create()
    {
        return view('backend.social_icons.create');
    }
    

    public function store(Request $request)
    {

        
        $data=$request->validate([
             'title'=> 'required',
             'link'=> 'required',
        ]);
        
        
        if($request->hasFile('icon')) {
            $originName = $request->file('icon')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $extension = $request->file('icon')->getClientOriginalExtension();
            $fileName =$fileName.time().'.'.$extension;
        
            $request->file('icon')->move(public_path('social_icons'), $fileName);
            $data['image']=$fileName;
        }
        

        SocialIcon::create($data);
    
        return response()->json(['status'=>true ,'msg'=>'SocialIcon  Is Created !!']);
    }
    
    
    
    public function edit($id,Request $request)
    {

        $item=SocialIcon::find($id);
        return view('backend.social_icons.edit', compact('item'));
        
    }
    
    
    public function update($id,Request $request)
    {

        $item=SocialIcon::find($id);
        
        $data=$request->validate([
             'title'=> 'required',
             'link'=> 'required',
        ]);
        
        
        if($request->hasFile('icon')) {
            deleteImage('social_icons', $item->image);
            $originName = $request->file('icon')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $extension = $request->file('icon')->getClientOriginalExtension();
            $fileName =$fileName.time().'.'.$extension;
        
            $request->file('icon')->move(public_path('social_icons'), $fileName);
            $data['image']=$fileName;
        }
        

        $item->update($data);
    
        return response()->json(['status'=>true ,'msg'=>'SocialIcon  Is Updated !!']);
    }
    
    public function destroy($id)
    {
        if(!auth()->user()->can('product.delete'))
        {
            abort(403, 'unauthorized');
        }

        $product=SocialIcon::find($id);
        deleteImage('social_icons', $product->image);
        $product->delete();
        return response()->json(['status'=>true ,'msg'=>'SocialIcon Is Deleted !!']);

    }
}
