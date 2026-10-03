<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Page;


class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items=Page::all();

        return view('backend.pages.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.pages.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $data=$request->validate([
             'title'=> 'required',
             'page'=> 'required',
             'body'=> 'required',
        ]);
        

        Page::create($data);

        return response()->json(['status'=>true ,'msg'=>'Page Is  Created !!','url'=>route('admin.pages.index')]);
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
        if(!auth()->user()->can('category.edit'))
        {
            abort(403, 'unauthorized');
        }

        $item=Page::find($id);
        return view('backend.pages.edit', compact('item'));
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
        

        $item=Page::find($id);
        $data=$request->validate([
             'title'=> 'required',
             'page'=> 'required',
             'body'=> 'required',
        ]);
       
        $item->update($data);

        return response()->json(['status'=>true ,'msg'=>'Page Is Updated !!','url'=>route('admin.pages.index')]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(!auth()->user()->can('category.delete'))
        {
            abort(403, 'unauthorized');
        }

        $item=Page::find($id);
        $item->delete();
        return response()->json(['status'=>true ,'msg'=>'Page Is Deleted !!']);

    }
}
