<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Type;
use DB;
use Image;

class TypeController extends Controller
{
    public function index()
    {
        $items=Type::all();
        return view('backend.types.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(!auth()->user()->can('type.create'))
        {
            abort(403, 'unauthorized');
        }

        return view('backend.types.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->can('type.create'))
        {
            abort(403, 'unauthorized');
        }
        

        $data=$request->validate([
             'name'=> 'required',
        ]);
        
        if($request->hasFile('image')) {
                $image = Image::make($request->file('image'));
      
                /**
                 * Main Image Upload on Folder Code
                 */
                $imageName = $request->file('image')->getClientOriginalName();
                $destinationPath = public_path('types/');
                $image->resize(100,50);
                $image->save($destinationPath.$imageName);
                $data['image']=$imageName;
        }

        Type::create($data);
        return response()->json(['status'=>true ,'msg'=>'Type Is  Created !!','url'=>route('admin.types.index')]);
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
        if(!auth()->user()->can('type.edit'))
        {
            abort(403, 'unauthorized');
        }

        $item=Type::find($id);
        return view('backend.types.edit', compact('item'));
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
        if(!auth()->user()->can('type.edit'))
        {
            abort(403, 'unauthorized');
        }

        $category=Type::find($id);
        $data=$request->validate([
             'name'=> 'required',
        ]);

        if($request->hasFile('image')) {
            deleteImage('types', $category->image);
            $image = Image::make($request->file('image'));
      
                /**
                 * Main Image Upload on Folder Code
                 */
                $imageName = $request->file('image')->getClientOriginalName();
                $destinationPath = public_path('types/');
                $image->resize(100,50);
                $image->save($destinationPath.$imageName);
                $data['image']=$imageName;
        }
       
        $category->update($data);

        return response()->json(['status'=>true ,'msg'=>'Category Is Updated !!','url'=>route('admin.types.index')]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(!auth()->user()->can('type.delete'))
        {
            abort(403, 'unauthorized');
        }

        $category=Type::find($id);
        deleteImage('types', $category->image);
        $category->delete();
        return response()->json(['status'=>true ,'msg'=>'Type Is Deleted !!']);

    }
    
    public function topBrandUpdate(){
        
        $status=(request('is_top')==1)?1:null;
        DB::table('types')->whereIn('id', request('brand_ids'))->update(['is_top'=>$status]);
        return response()->json(['status'=>true ,'msg'=>'Brand Status Updated !!']);
    }
}
