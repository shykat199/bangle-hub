<?php

namespace App\Http\Controllers\Backend;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\AboutUs;

class AboutUsController extends Controller
{
    public function index()
    {
        $item = AboutUs::first();
        return view('backend.about_us.about_us', compact('item'));
    }
    
    
    public function store(Request $request)
    {
        $item=AboutUs::first();
        
        $data=$request->validate([
             'site_name'=> 'required',
             'site_url'=> 'required',
             'page_title'=> 'required',
             'sub_title'=> 'required',
             'speech'=> 'required',
             'page_desc'=> '',
             'title_one'=> '',
             'desc_one'=> '',
             'title_two'=> '',
             'desc_two'=> '',
        ]);
        
        $url=$request->video;
        
        if(!empty($url) && ($item->video !=$url)){
            $data['video']=$this->getUrl($url);
        }

        if($request->hasFile('cover_image')) {
            deleteImage('aboutus', $item->cover_image);
            $originName = $request->file('cover_image')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $extension = $request->file('cover_image')->getClientOriginalExtension();
            $fileName =$fileName.time().'.'.$extension;
        
            $request->file('cover_image')->move(public_path('aboutus'), $fileName);
            $data['cover_image']=$fileName;
        }

        if($request->hasFile('signature')) {
            deleteImage('aboutus', $item->signature);
            $originName = $request->file('signature')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $extension = $request->file('signature')->getClientOriginalExtension();
            $fileName =$fileName.time().'.'.$extension;
        
            $request->file('signature')->move(public_path('aboutus'), $fileName);
            $data['signature']=$fileName;
        }
        
    
        
        
        if($item){
            $item->update($data);
        }else{
            AboutUs::create($data);
        }

        return response()->json(['status'=>true ,'msg'=>'About Us Info  Is Created !!']);
    }
    
    private function getUrl($url){
        
        parse_str( parse_url( $url, PHP_URL_QUERY ), $my_array_of_vars );
        return $my_array_of_vars['v'];    
        
    }
}
