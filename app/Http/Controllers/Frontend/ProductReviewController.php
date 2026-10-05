<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductReview;
use App\Models\Product;
use Image;

class ProductReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /**
     * "Log in" from the review box: remember the product so the customer lands
     * back on its review form after logging in (the login action follows session('url')).
     */
    public function loginFirst(Product $product)
    {
        $back = route('front.products.show', ['product' => $product->slug ?: $product->id]) . '?review=1';

        if (auth()->check()) {
            return redirect($back);
        }

        session()->put('url', $back);

        return redirect()->route('login');
    }

    public function store(Request $request)
    {
        // Only logged-in customers can review. The form is not shown to guests,
        // this covers anyone posting to the endpoint directly.
        if (!auth()->check()) {
            return response()->json(['status' => false, 'login' => true, 'msg' => 'Please log in to write a review.'], 401);
        }

        $data=$request->validate([
            'review' => 'required|numeric|min:1|max:5',
            'name' => 'nullable|string|max:255',
            // the form marks notes as optional
            'message' => 'nullable|string|max:2000',
            'product_id' => 'required|numeric',
            'image' => 'nullable|image|max:4096',
        ]);

        $data['name']    = trim((string) ($data['name'] ?? '')) ?: (auth()->user()->name ?? 'Customer');
        $data['message'] = $data['message'] ?? '';
        $data['user_id'] = auth()->id();
        unset($data['image']);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            
            // Generate a unique file name with the original extension
            $name = uniqid() . '.' . $image->getClientOriginalExtension();
            
            // Define the path where the image will be saved
            $path = public_path('reviews');
            
            // 🔥 Resize অংশটুকু বাদ দেওয়া হয়েছে। ছবি এখন ফুল সাইজে সেভ হবে 🔥
            Image::make($image)->save($path . '/' . $name);
            
            // Save the image path in the data array
            $data['image'] = 'reviews/' . $name;
        }
        
        // one review per customer per product — sending again updates it
        $old_check=['user_id'=>auth()->id(),'product_id'=>$data['product_id']];
        unset($data['product_id']);
        
        ProductReview::updateOrCreate($old_check,$data);
        
        $singleProduct = Product::with(['sizes', 'reviews' => function($q){
            $q->where('status', 1);
        }])->find($request->product_id);
        
        $view = view("frontend.products.partials.reviewList", compact("singleProduct"))->render();
        return response()->json(['status'=>true,'msg'=>'Product Review Is Created successfully! Please Wait for Approval.', 'view'=>$view]);
    }

    public function update2(Request $request)
    {
        $data=$request->validate([
            'review' => 'required|numeric',
            'name' => 'required',
            'message' => 'required',
            'product_id' => 'required|numeric',
        ]);
        
        if($request->hasFile('image')) {
            $image = $request->file('image');
            $name = uniqid().'.'.$image->getClientOriginalExtension();
            
            // 🔥 Resize অংশটুকু বাদ দেওয়া হয়েছে। ছবি এখন ফুল সাইজে সেভ হবে 🔥
            Image::make($image)->save('reviews/'.$name);
            $imgReq = "reviews/".$name;
            $data['image'] = $imgReq;
        }
        
        // 🔥 dd($data); সরিয়ে ফেলা হয়েছে, নাহলে রিকোয়েস্ট আটকে যেত 🔥
        
        $old_check=['user_id'=>auth()->user()->id,'product_id'=>$data['product_id']];
        unset($data['product_id']);
        
        ProductReview::updateOrCreate($old_check,$data);
        return response()->json(['success'=>true,'msg'=>'Product Review Is Updated successfully!']);
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}