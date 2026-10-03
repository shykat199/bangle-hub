<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HomeSectionImage;

class HomeSectionImageController extends Controller
{
    public function index()
    {
        $items = HomeSectionImage::paginate(20);
        return view('backend.home_section_images.index', compact('items'));
    }

    public function store(Request $request)
    {
        if(!auth()->user()->can('image.create'))
        {
            abort(403, 'unauthorized');
        }

        $request->validate([
             'section'=> 'required'
        ]);

        $homeimage = new HomeSectionImage();
        
        $homeimage->section = ($request->section == 'none') ? null : $request->section;
        
        $homeimage->link = $request->link;
        $homeimage->is_for_small = $request->is_for_small;
        
        $homeimage->left_link_1 = $request->left_link_1;
        $homeimage->left_link_2 = $request->left_link_2;
        $homeimage->left_link_3 = $request->left_link_3;
        $homeimage->left_link_4 = $request->left_link_4;
        $homeimage->right_link  = $request->right_link;

        if($request->hasFile('mobile_image')) {
            $homeimage->mobile_image = saveCompressedUpload($request->file('mobile_image'), 'homeimages', 1000);
        }

        if($request->hasFile('image')) {
            $homeimage->image = saveCompressedUpload($request->file('image'), 'homeimages', 1600);
        }

        if($request->hasFile('banner_video')) {
            $originName = $request->file('banner_video')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $extension = $request->file('banner_video')->getClientOriginalExtension();
            $fileName = $fileName.time().'.'.$extension;
        
            $request->file('banner_video')->move(public_path('homeimages/videos'), $fileName);
            $homeimage->banner_video = $fileName;
        }

        $newImageFields = ['left_image_1', 'left_image_2', 'left_image_3', 'left_image_4', 'right_image'];

        foreach ($newImageFields as $field) {
            if ($request->hasFile($field)) {
                $homeimage->$field = saveCompressedUpload($request->file($field), 'homeimages', 1600);
            }
        }

        $homeimage->save();

        return response()->json(['status'=>true ,'msg'=>'HomeSectionImage Is Created !!','url'=>route('admin.home_section_images.index')]);
    }

    public function show($id)
    {
    }

    public function edit($id)
    {
        if(!auth()->user()->can('image.edit'))
        {
            abort(403, 'unauthorized');
        }

        $item=HomeSectionImage::find($id);
        return view('backend.home_section_images.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        if(!auth()->user()->can('image.edit'))
        {
            abort(403, 'unauthorized');
        }

        $homeimage = HomeSectionImage::find($id);
        
        $request->validate([
             'section'=> 'required'
        ]);

        $homeimage->title = $request->title ?? $homeimage->title;
        $homeimage->text = $request->text ?? $homeimage->text;
        $homeimage->link = $request->link ?? $homeimage->link;
        
        $newSectionValue = ($request->section == 'none') ? null : $request->section;
        $homeimage->section = $newSectionValue ?? $homeimage->section;
        
        $homeimage->is_for_small = $request->has('is_for_small') ? $request->is_for_small : $homeimage->is_for_small;

        $homeimage->left_link_1 = $request->left_link_1 ?? $homeimage->left_link_1;
        $homeimage->left_link_2 = $request->left_link_2 ?? $homeimage->left_link_2;
        $homeimage->left_link_3 = $request->left_link_3 ?? $homeimage->left_link_3;
        $homeimage->left_link_4 = $request->left_link_4 ?? $homeimage->left_link_4;
        $homeimage->right_link  = $request->right_link ?? $homeimage->right_link;

        if ($request->has('remove_banner_video') && $request->remove_banner_video == 1) {
            if ($homeimage->banner_video && file_exists(public_path('homeimages/videos/'.$homeimage->banner_video))) {
                unlink(public_path('homeimages/videos/'.$homeimage->banner_video));
            }
            $homeimage->banner_video = null;
        }

        if($request->hasFile('banner_video')) {
            if ($homeimage->banner_video && file_exists(public_path('homeimages/videos/'.$homeimage->banner_video))) {
                unlink(public_path('homeimages/videos/'.$homeimage->banner_video));
            }

            $originName = $request->file('banner_video')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $extension = $request->file('banner_video')->getClientOriginalExtension();
            $fileName = $fileName . time() . '.' . $extension;
        
            $request->file('banner_video')->move(public_path('homeimages/videos'), $fileName);
            $homeimage->banner_video = $fileName;
        }

        if($request->hasFile('image')) {
            if (!empty($homeimage->image)) {
                deleteImage('homeimages', $homeimage->image);
            }
            $homeimage->image = saveCompressedUpload($request->file('image'), 'homeimages', 1600);
        }

        if($request->hasFile('mobile_image')) {
            if (!empty($homeimage->mobile_image)) {
                deleteImage('homeimages', $homeimage->mobile_image);
            }
            $homeimage->mobile_image = saveCompressedUpload($request->file('mobile_image'), 'homeimages', 1000);
        }

        $newImageFields = ['left_image_1', 'left_image_2', 'left_image_3', 'left_image_4', 'right_image'];

        foreach ($newImageFields as $field) {
            if ($request->hasFile($field)) {
                if (!empty($homeimage->$field)) {
                    deleteImage('homeimages', $homeimage->$field);
                }
                $homeimage->$field = saveCompressedUpload($request->file($field), 'homeimages', 1600);
            }
        }
        
        $homeimage->save();

        return response()->json(['status'=>true ,'msg'=>'HomeSectionImage Is Updated !!','url'=>route('admin.home_section_images.index')]);
    }

    public function destroy($id)
    {
        if(!auth()->user()->can('image.delete'))
        {
            abort(403, 'unauthorized');
        }

        $item=HomeSectionImage::find($id);
        
        deleteImage('homeimages', $item->image);
        deleteImage('homeimages', $item->mobile_image);

        if ($item->banner_video && file_exists(public_path('homeimages/videos/'.$item->banner_video))) {
            unlink(public_path('homeimages/videos/'.$item->banner_video));
        }

        $newImageFields = ['left_image_1', 'left_image_2', 'left_image_3', 'left_image_4', 'right_image'];
        foreach ($newImageFields as $field) {
            if (!empty($item->$field)) {
                deleteImage('homeimages', $item->$field);
            }
        }

        $item->delete();
        return response()->json(['status'=>true ,'msg'=>'HomeSectionImage Is Deleted !!']);
    }
}