<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStock extends Model
{
    use HasFactory;
    protected $guarded = [];

    // রিলেশনশিপ: প্রোডাক্ট এর নাম ও ছবি পাওয়ার জন্য
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // রিলেশনশিপ: সাইজ ও কালার পাওয়ার জন্য (ভেরিয়েশন টেবিল থেকে)
    public function variation()
    {
        return $this->belongsTo(Variation::class, 'variation_id');
    }

    // আপনার আগের মেথড (যদি কোথাও ব্যবহার হয়ে থাকে তবে থাক)
    public function product_sizes()
    {
        return $this->belongsTo(Size::class, 'size_id');
    }
}