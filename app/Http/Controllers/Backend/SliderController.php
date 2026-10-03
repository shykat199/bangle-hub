<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Slider;
use Image;


class SliderController extends Controller
{
    public function index()
    {
        $items=Slider::get();
        return view('backend.sliders.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(!auth()->user()->can('slider.create'))
        {
            abort(403, 'unauthorized');
        }

        return view('backend.sliders.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->can('slider.create'))
        {
            abort(403, 'unauthorized');
        }

        $data=$request->validate([
             'title'=> 'required',
             'description'=> '',
             'link'=> '',
             'image'=> 'required|image',
        ]);
        
        if ($request->hasFile('image')) {
    $image = Image::make($request->file('image'));
    $originName = $request->file('image')->getClientOriginalName();
    $fileName = pathinfo($originName, PATHINFO_FILENAME);
    $extension = $request->file('image')->getClientOriginalExtension();
    $fileName = $fileName . '_' . time() . '.' . $extension; 
    
    $image->resize(1500, 500);
    $image->save(public_path('sliders') . '/' . $fileName); 
    
    $data['image'] = $fileName;
}

if ($request->hasFile('mobile_image')) {
    $homeImage = Image::make($request->file('mobile_image'));
    $originName = $request->file('mobile_image')->getClientOriginalName();
    $fileName = pathinfo($originName, PATHINFO_FILENAME);
    $extension = $request->file('mobile_image')->getClientOriginalExtension();
    $fileName = $fileName . '_' . time() . '.' . $extension; 
    
    $homeImage->resize(1000, 500);
    $homeImage->save(public_path('mobile_sliders') . '/' . $fileName); 
    
    $data['mobile_image'] = $fileName;
}

        
        // if($request->hasFile('mobile_image')) {
        //     $image = Image::make($request->file('mobile_image'));
        //     $originName = $request->file('mobile_image')->getClientOriginalName();
        //     $fileName = pathinfo($originName, PATHINFO_FILENAME);
        //     $extension = $request->file('mobile_image')->getClientOriginalExtension();
        //     $fileName =$fileName.time().'.'.$extension;
            
        //     $image->resize(1000,500);
        //     $image->save(public_path('sliders').'/'.$fileName);
        
        //     // $request->file('mobile_image')->move(public_path('sliders'), $fileName);
        //     $data['mobile_image']=$fileName;
        // }
        
//         if ($request->hasFile('mobile_image')) {
//     $image = Image::make($request->file('mobile_image'));
//     $originName = $request->file('mobile_image')->getClientOriginalName();
//     $fileName = pathinfo($originName, PATHINFO_FILENAME);
//     $extension = $request->file('mobile_image')->getClientOriginalExtension();
//     $fileName = $fileName . '_' . time() . '.' . $extension; // Corrected line
    
//     $image->resize(1000, 500);
//     $image->save(public_path('sliders') . '/' . $fileName); // Corrected line
    
//     $data['mobile_image'] = $fileName;
// }



        // if($request->hasFile('image')) {
        //     $image = Image::make($request->file('image'));
        //     $originName = $request->file('image')->getClientOriginalName();
        //     $fileName = pathinfo($originName, PATHINFO_FILENAME);
        //     $extension = $request->file('image')->getClientOriginalExtension();
        //     $fileName =$fileName.time().'.'.$extension;
            
        //     $image->resize(1500,500);
        //     $image->save(public_path('sliders').'/'.$fileName);
        
        //     // $request->file('image')->move(public_path('sliders'), $fileName);
        //     $data['image']=$fileName;
        // }
        
        // if ($request->hasFile('image')) {
        //     $image = Image::make($request->file('image'));
        //     $originName = $request->file('image')->getClientOriginalName();
        //     $fileName = pathinfo($originName, PATHINFO_FILENAME);
        //     $extension = $request->file('image')->getClientOriginalExtension();
        //     $fileName = $fileName . '_' . time() . '.' . $extension; // Corrected line
            
        //     $image->resize(1500, 500);
        //     $image->save(public_path('sliders') . '/' . $fileName); // Corrected line
            
        //     $data['image'] = $fileName;
        // }

    
        Slider::create($data);

        return response()->json(['status'=>true ,'msg'=>'Slider Is  Created !!','url'=>route('admin.sliders.index')]);
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
        if(!auth()->user()->can('slider.edit'))
        {
            abort(403, 'unauthorized');
        }

        $item=Slider::find($id);

        
        return view('backend.sliders.edit', compact('item'));
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
        if(!auth()->user()->can('slider.edit'))
        {
            abort(403, 'unauthorized');
        }

        $slider=Slider::find($id);
        $data=$request->validate([
             'title'=> 'required',
             'description'=> '',
             'link'=> '',
        ]);

        if ($request->hasFile('image')) {
    $image = Image::make($request->file('image'));
    $originName = $request->file('image')->getClientOriginalName();
    $fileName = pathinfo($originName, PATHINFO_FILENAME);
    $extension = $request->file('image')->getClientOriginalExtension();
    $fileName = $fileName . '_' . time() . '.' . $extension; 
    
    $image->resize(1500, 500);
    $image->save(public_path('sliders') . '/' . $fileName); 
    
    $data['image'] = $fileName;
}

if ($request->hasFile('mobile_image')) {
    $homeImage = Image::make($request->file('mobile_image'));
    $originName = $request->file('mobile_image')->getClientOriginalName();
    $fileName = pathinfo($originName, PATHINFO_FILENAME);
    $extension = $request->file('mobile_image')->getClientOriginalExtension();
    $fileName = $fileName . '_' . time() . '.' . $extension; 
    
    $homeImage->resize(1000, 500);
    $homeImage->save(public_path('mobile_sliders') . '/' . $fileName); 
    
    $data['mobile_image'] = $fileName;
}

       
        $slider->update($data);

        return response()->json(['status'=>true ,'msg'=>'Slider Is Updated !!','url'=>route('admin.sliders.index')]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(!auth()->user()->can('slider.delete'))
        {
            abort(403, 'unauthorized');
        }

        $slider=Slider::find($id);
        deleteImage('sliders', $slider->image);
        deleteImage('sliders', $slider->mobile_image);
        $slider->delete();
        return response()->json(['status'=>true ,'msg'=>'Slider Is Deleted !!']);

    }

}
