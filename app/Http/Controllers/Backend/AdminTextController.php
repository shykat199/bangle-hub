<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\AdminText;
use App\Models\Information; // ✅ Information মডেল যুক্ত করা হয়েছে

class AdminTextController extends Controller
{
    public function edit()
    {
        $text = AdminText::first();
        if(!$text){
            $text = AdminText::create([
                'popular_category_title' => 'জনপ্রিয় ক্যাটাগরি',
                'view_all_text' => 'View All',
                'add_to_cart_text' => 'কার্টে যোগ করুন',
                'order_now_text' => 'অর্ডার করুন',
                'product_code_label' => 'প্রোডাক্ট কোড',
                'courier_delivery_title' => 'কুরিয়ার ডেলিভারি খরচ',
                'short_description_title' => 'Short Description',
            ]);
        }

        // কালার ডাটাগুলো ভিউতে পাঠানোর জন্য
        $info = Information::first();

        return view('backend.admin_texts.edit', compact('text', 'info'));
    }

    public function update(Request $request)
    {
        // ১. AdminText (টেক্সট) আপডেট
        $text = AdminText::first() ?? new AdminText();

        $text->popular_category_title  = $request->popular_category_title;
        $text->view_all_text           = $request->view_all_text;
        $text->add_to_cart_text        = $request->add_to_cart_text;
        $text->order_now_text          = $request->order_now_text;
        $text->product_code_label      = $request->product_code_label;
        $text->courier_delivery_title  = $request->courier_delivery_title;
        $text->short_description_title = $request->short_description_title;
        
        // ✅ নতুন টেক্সট ফিল্ডগুলো
        $text->call_btn_text           = $request->call_btn_text;
        $text->whatsapp_btn_text       = $request->whatsapp_btn_text;
        $text->submit_review_btn_text  = $request->submit_review_btn_text;
        $text->details_tab_text        = $request->details_tab_text;
        $text->reviews_tab_text        = $request->reviews_tab_text;

        $text->save();

        // ২. Information (কালার) আপডেট
        $info = Information::first();
        if ($info) {
            $info->common_btn_color         = $request->common_btn_color;
            $info->common_btn_text_color    = $request->common_btn_text_color;
            $info->order_now_btn_color      = $request->order_now_btn_color;
            $info->order_now_btn_text_color = $request->order_now_btn_text_color;
            $info->save();
        }

        return back()->with('success','Dynamic Text & Colors Updated Successfully!');
    }

    // Homepage bottom strip: shop block, three feature items and the contact button.
    public function homeStripEdit()
    {
        $text  = AdminText::first() ?? new AdminText();
        $strip = $text->homeStrip();
        $icons = AdminText::HOME_STRIP_ICONS;
        $info  = Information::orderBy('id', 'desc')->first();

        return view('backend.admin_texts.home_strip', compact('strip', 'icons', 'info'));
    }

    public function homeStripUpdate(Request $request)
    {
        $data = $request->validate([
            'shop_label'     => 'nullable|string|max:80',
            'shop_name'      => 'nullable|string|max:120',
            'shop_address'   => 'nullable|string|max:255',
            'items'          => 'nullable|array|max:3',
            'items.*.icon'   => ['nullable', 'string', Rule::in(array_keys(AdminText::HOME_STRIP_ICONS))],
            'items.*.title'  => 'nullable|string|max:60',
            'items.*.text'   => 'nullable|string|max:120',
            'contact_label'  => 'nullable|string|max:40',
            'contact_phone'  => 'nullable|string|max:30',
        ]);

        $items = [];
        foreach (array_values($data['items'] ?? []) as $item) {
            $items[] = [
                'icon'  => $item['icon'] ?? 'fa-check-circle',
                'title' => trim($item['title'] ?? ''),
                'text'  => trim($item['text'] ?? ''),
            ];
        }

        $text = AdminText::first() ?? new AdminText();
        $text->home_strip = [
            'active'        => $request->boolean('active'),
            'shop_label'    => trim($data['shop_label'] ?? ''),
            'shop_name'     => trim($data['shop_name'] ?? ''),
            'shop_address'  => trim($data['shop_address'] ?? ''),
            'items'         => $items,
            'contact_label' => trim($data['contact_label'] ?? ''),
            'contact_phone' => trim($data['contact_phone'] ?? ''),
        ];
        $text->save();

        return back()->with('success', 'Home Bottom Strip Updated Successfully!');
    }
}