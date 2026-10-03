<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminText extends Model
{
    protected $table = 'admin_texts';

    protected $fillable = [
        'popular_category_title',
        'view_all_text',
        'add_to_cart_text',
        'order_now_text',
        'product_code_label',
        'courier_delivery_title',
        'short_description_title',
        
        // ✅ নতুন যুক্ত করা টেক্সট ফিল্ডগুলো
        'call_btn_text',
        'whatsapp_btn_text',
        'submit_review_btn_text',
        'details_tab_text',
        'reviews_tab_text',
    ];
}