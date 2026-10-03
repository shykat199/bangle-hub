<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Size;

class SizeController extends Controller
{
    public function index()
    {
        $items=Size::all();
        return view('backend.sizes.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(!auth()->user()->can('size.create'))
        {
            abort(403, 'unauthorized');
        }

        return view('backend.sizes.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->can('size.create'))
        {
            abort(403, 'unauthorized');
        }

        $data=$request->validate([
             'title'=> 'required',
        ]);

        Size::create($data);

        return response()->json(['status'=>true ,'msg'=>'Size Is  Created !!','url'=>route('admin.sizes.index')]);
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
        if(!auth()->user()->can('size.edit'))
        {
            abort(403, 'unauthorized');
        }

        $item=Size::find($id);
        return view('backend.sizes.edit', compact('item'));
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
        if(!auth()->user()->can('size.edit'))
        {
            abort(403, 'unauthorized');
        }

        $category=Size::find($id);
        $data=$request->validate([
             'title'=> 'required',
        ]);
       
        $category->update($data);

        return response()->json(['status'=>true ,'msg'=>'Size Is Updated !!','url'=>route('admin.sizes.index')]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(!auth()->user()->can('size.delete'))
        {
            abort(403, 'unauthorized');
        }

        $category=Size::find($id);
        $category->delete();
        return response()->json(['status'=>true ,'msg'=>'Size Is Deleted !!']);

    }
}
