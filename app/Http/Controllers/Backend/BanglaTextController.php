<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\BanglaText;
use Illuminate\Http\Request;
use App\Utils\Util;
use Auth, Validator;
class BanglaTextController extends Controller{
    
    public function index()
    {
        $bangla_text = BanglaText::first();
        
        return view('backend.informations.index', compact('information'));
    }
    
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'site_name'    => ['required'],    
            'site_logo'     => ['max:2048'],    
            'owner_phone'   => ['required'],    
            'owner_email'   => ['required'],    
            'address'       => ['required'],
          	'copyright'     => ['required'],
          	'facebook'      => '',
          	'instagram'     => '',
          	'youtube'       => '',
            'tracking_code' => '',  
            'recommend_num' => '', 
            'discount_num'  => '', 
            'newarrival_num' => '',   
            'bkash' => '',
            'bkash_number' => '',
            'nogod' => '',
            'nogod_number' => '',
            'rocket' => '',
            'rocket_number' => '',
            'paypal' => '',
            'paypal_account' => '',
            'stripe' => '', 
            'stripe_account' => '',
            'supp_num1' => '',
            'supp_num2' => '',
            'supp_num3' => '',
            'number_visibility' => '',
            'currency' => '',
            'redx_api_base_url' => '',
            'redx_api_access_token' => '',            
          	'pathao_api_base_url' => '',
            'pathao_api_access_token' => '',
            'pathao_store_id' => '',
            'steadfast_api_base_url' => '',
            'steadfast_api_key' => '',
            'steadfast_secret_key' => ''
        ]);
        
        $information = Information::findOrFail($id);
        
        if($request->hasFile('site_logo'))
        {
            Util::deleteFile($information->site_logo, 'img');
            $data['site_logo'] = Util::uploadFile($request->site_logo, 'img');
        }
        
        $information->update($data);
        return back()->with(['msg'=> 'Site settings has been updated']);
        
    }
  
    public function statusCoupon(Request $request)
    {
    	$information = Information::first();
        $information->coupon_visibility = $request->coupon_visibility;
        $information->save();       
        
        return back()->with(['msg'=> 'Site settings has been updated']);
    }
    
    public function showProfile()
    {
        $data = Auth::user();
        
         return view('backend.informations.profile', compact('data'));
    }    
    
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
                'first_name' => ['required'],
                'last_name' => ['required'],
                'username' => ['required', 'unique:users,username,'.$user->id],
                'mobile' => ['required', 'unique:users,mobile,'.$user->id],
                'business_name' => ['required'],
                'image' => ['max:2048'],
            ]);
            
        $data = $request->only(['first_name', 'last_name', 'username', 'mobile', 'business_name']);
        
        if($validator->fails())
        {
            return response()->json(['errors' => $validator->errors()]);
        }
           
         if($request->hasFile('image'))
         {

             Util::deleteFile($user->image, 'img');
             $data['image'] = Util::uploadFile($request->image, 'img');
         }
         
         $user->update($data);
         
         return response()->json(['success' => 'Profile has been updated']);
            
    }
    
}