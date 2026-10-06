<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PopularCategory;
use App\Models\HomeCategory;
use App\Models\Category;
use App\Models\Type;
use DB;
use Image;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $types=Type::all();
        $items=Category::latest()->paginate(20);
        $cats=Category::whereNull('parent_id')->select('name','id')->pluck('name','id')->toArray();
        return view('backend.categories.index', compact('items','types','cats'));
    }

    public function store(Request $request)
    {
        if(!auth()->user()->can('category.create'))
        {
            abort(403, 'unauthorized');
        }
        $data=$request->validate([
             'name'=> 'required',
             'parent_id'=> '',
        ]);
        
        $slug = Str::slug($data['name']);
        $originalSlug = $slug;
        $counter = 1;
    
        while (Category::where('url', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
    
        $data['url'] = $slug;

        // A new category goes to the end of its own list (main categories, or its parent's subcategories).
        $siblings = empty($data['parent_id']) ? Category::whereNull('parent_id') : Category::where('parent_id', $data['parent_id']);
        $data['sort_order'] = (int) $siblings->max('sort_order') + 1;
        
        if($request->hasFile('image')) {
            $image = Image::make($request->file('image'));
            $imageName = $request->file('image')->getClientOriginalName();
            $destinationPath = public_path('categories/');
            
            // ৯০ থেকে বাড়িয়ে ৪০০ করা হলো যাতে ইমেজ কোয়ালিটি ভালো থাকে
            $image->resize(400, 400); 
            
            $image->save($destinationPath.$imageName);
            $data['image']=$imageName;
        }

        Category::create($data);

        return response()->json(['status'=>true ,'msg'=>'Category Is Created !!','url'=>route('admin.categories.index')]);
    }

    public function show($id)
    {
        
    }

    // Drag & drop page for the order categories appear in on the storefront menus.
    public function sort()
    {
        if(!auth()->user()->can('category.edit'))
        {
            abort(403, 'unauthorized');
        }

        $cats = Category::whereNull('parent_id')->ordered()->with('subcats')->get();
        return view('backend.categories.sort', compact('cats'));
    }

    // Saves one list: the main categories (no parent_id) or one category's subcategories.
    public function saveSort(Request $request)
    {
        if(!auth()->user()->can('category.edit'))
        {
            abort(403, 'unauthorized');
        }

        $request->validate([
            'ids'       => 'required|array|min:1',
            'ids.*'     => 'integer',
            'parent_id' => 'nullable|integer',
        ]);

        $parentId = $request->filled('parent_id') ? (int) $request->parent_id : null;

        DB::transaction(function () use ($request, $parentId) {
            foreach (array_values($request->ids) as $position => $id) {
                Category::where('id', $id)
                    ->when($parentId, fn ($q) => $q->where('parent_id', $parentId), fn ($q) => $q->whereNull('parent_id'))
                    ->update(['sort_order' => $position + 1]);
            }
        });

        return response()->json(['status' => true]);
    }

    public function edit($id)
    {
        if(!auth()->user()->can('category.edit'))
        {
            abort(403, 'unauthorized');
        }

        $item=Category::find($id);
        $types=Type::all();
        $cats=Category::whereNull('parent_id')->select('name','id')->pluck('name','id')->toArray();
        return view('backend.categories.edit', compact('item','types','cats'));
    }

    public function update(Request $request, $id)
    {
        if(!auth()->user()->can('category.edit'))
        {
            abort(403, 'unauthorized');
        }

        $category=Category::find($id);
        $data=$request->validate([
             'name'=> 'required',
             'url'=> 'required|unique:categories,url,'.$id,
             'parent_id'=> '',
        ]);

        if($request->hasFile('image')) {
            deleteImage('categories', $category->image);
            
            $image = Image::make($request->file('image'));
            $imageName = $request->file('image')->getClientOriginalName();
            $destinationPath = public_path('categories/');
            
            // ৯০ থেকে বাড়িয়ে ৪০০ করা হলো যাতে ইমেজ কোয়ালিটি ভালো থাকে
            $image->resize(400, 400); 
            
            $image->save($destinationPath.$imageName);
            $data['image']=$imageName;
        }
       
        $category->update($data);

        return response()->json(['status'=>true ,'msg'=>'Category Is Updated !!','url'=>route('admin.categories.index')]);
    }

    public function destroy($id)
    {
        if(!auth()->user()->can('category.delete'))
        {
            abort(403, 'unauthorized');
        }

        $category=Category::find($id);
        deleteImage('categories', $category->image);
        $home_cat = HomeCategory::where('category_id', $id)->first();
        if($home_cat){
            $home_cat->delete();
        }
        $category->delete();
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
             'category_id'=> 'required',
             'serial'=> ''
        ]);

        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $filename = time() . '_homecover.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/home_categories/cover'), $filename);
            $data['cover_image'] = $filename;
        }
        
        HomeCategory::create($data);
        return response()->json(['status'=>true ,'msg'=>'Home Category Is Created !!','url'=>route('admin.homecat')]);
    }

    public function updatehomeCatgeory(Request $request, $id) {
        $home_category = HomeCategory::find($id);
        
        $data = $request->validate([
             'category_id'=> 'required',
             'serial'=> ''
        ]);

        if ($request->hasFile('cover_image')) {
            if ($home_category->cover_image && file_exists(public_path('uploads/home_categories/cover/' . $home_category->cover_image))) {
                unlink(public_path('uploads/home_categories/cover/' . $home_category->cover_image));
            }

            $file = $request->file('cover_image');
            $filename = time() . '_homecover.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/home_categories/cover'), $filename);
            $data['cover_image'] = $filename;
        }
        
        $home_category->update($data);
        return response()->json(['status'=>true ,'msg'=>'Home Category Updated !!','url'=>route('admin.homecat')]);
    }
    
    public function homeCategoryStatus(Request $request, $id) {
        $home_category = HomeCategory::findOrFail($id);
        $home_category->update(['status' => $request->boolean('status')]);

        return response()->json(['status'=>true ,'msg'=> $home_category->status ? 'Category is now shown on home page.' : 'Category is hidden from home page.']);
    }

    public function delhomeCatgeory(Request $request, $id) {
        $delete_data = HomeCategory::find($id);

        if ($delete_data->cover_image && file_exists(public_path('uploads/home_categories/cover/' . $delete_data->cover_image))) {
            unlink(public_path('uploads/home_categories/cover/' . $delete_data->cover_image));
        }

        $delete_data->delete();
        return response()->json(['status'=>true ,'msg'=>'Home Category Is Deleted !!']);
    }

    public function removeHomeCover($id) {
        $home_category = HomeCategory::find($id);
        
        if ($home_category->cover_image && file_exists(public_path('uploads/home_categories/cover/' . $home_category->cover_image))) {
            unlink(public_path('uploads/home_categories/cover/' . $home_category->cover_image));
        }
        
        $home_category->update(['cover_image' => null]);
        return response()->json(['status'=>true ,'msg'=>'Cover Image Removed !!','url'=>route('admin.homecat')]);
    }
    
    public function popularCatgeory(Request $request)
    {
        $catIds = $request->cat_ids;
        $updateData = [];
    
        if ($request->has('is_popular')) {
            $updateData['is_popular'] = $request->is_popular == 1 ? 1 : 0;
        }
    
        if ($request->has('is_menu')) {
            $updateData['is_menu'] = $request->is_menu == 1 ? 1 : 0;
        }
    
        if (empty($updateData)) {
            return response()->json(['status' => false, 'msg' => 'No valid action found.']);
        }
    
        DB::table('categories')
            ->whereIn('id', $catIds)
            ->update($updateData);
    
        return response()->json(['status' => true, 'msg' => 'Category status updated successfully!']);
    }
}