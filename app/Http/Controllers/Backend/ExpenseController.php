<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use DB;
use Image;
class ExpenseController extends Controller
{
    public function index()
    {
        $items=Expense::latest()->paginate(50);
        return view('backend.expenses.index', compact('items'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->can('category.create'))
        {
            abort(403, 'unauthorized');
        }
        $data=$request->validate([
             'title'=> 'required',
          	 'amount'=> 'required',
             'date'=> 'required'
        ]);

        Expense::create($data);

        return response()->json(['status'=>true ,'msg'=>'Expense Is  Created !!','url'=>route('admin.expenses.index')]);
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

        $item=Expense::find($id);
        return view('backend.expenses.edit', compact('item'));
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
        if(!auth()->user()->can('category.edit'))
        {
            abort(403, 'unauthorized');
        }

        $expense=Expense::find($id);
        $data=$request->validate([
             'title'=> 'required',
          	 'amount'=> 'required',
             'date'=> 'required'
        ]);
       
        $expense->update($data);

        return response()->json(['status'=>true ,'msg'=>'Expense Is Updated !!','url'=>route('admin.expenses.index')]);

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

        $expense=Expense::find($id);
        $expense->delete();
        return response()->json(['status'=>true ,'msg'=>'Category Is Deleted !!']);
    }
    
    public function homepage_categories(Request $request) {
        $popular_categories = PopularCategory::with('category')->latest()->get();
       
        $categories = Category::where('parent_id', null)->get();
        return view('backend.popular_categories.index', compact('popular_categories','categories'));
    }
    
    public function homeCatgeory() {
        $all_categories = Category::where('parent_id', null)->get();
        $home_categories = HomeCategory::with('category')->latest()->paginate(10);
        return view('backend.home_categories.index', compact('all_categories','home_categories'));
    }
    
    public function storehomeCatgeory(Request $request) {
        
        $data=$request->validate([
             'category_id'=> '',
          	 'serial'=> ''
        ]);
        
        HomeCategory::create($data);
        return response()->json(['status'=>true ,'msg'=>'Home Category Is  Created !!','url'=>route('admin.homecat')]);
    }
    
    public function delhomeCatgeory(Request $request, $id) {
      $delete_data = HomeCategory::find($id);
      $delete_data->delete();
      return response()->json(['status'=>true ,'msg'=>'Home Category Is Deleted !!']);
    }
    
    public function popularCatgeory(){
        
        $status=(request('is_popular')==1)?1:null;
        DB::table('categories')->whereIn('id', request('cat_ids'))->update(['is_popular'=>$status]);
        return response()->json(['status'=>true ,'msg'=>'Category Status Updated !!']);
    }

}
