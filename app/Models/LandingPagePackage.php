<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LandingPage;

class LandingPagePackage extends Model
{
    use HasFactory;

    // কোন টেবিলে ডেটা সেভ হবে তা বলে দেওয়া (ঐচ্ছিক, তবে সেফটির জন্য ভালো)
    protected $table = 'landing_page_packages';

    // যে ফিল্ডগুলোতে ডেটা ইনসার্ট করা যাবে
    protected $fillable = [
        'landing_page_id', 
        'qty', 
        'price', 
        'discount_text', 
        'is_default'
    ];

    // ল্যান্ডিং পেজের সাথে রিলেশন (Inverse)
    public function landingPage()
    {
        return $this->belongsTo(LandingPage::class, 'landing_page_id');
    }
} // <-- এখানে ক্লাস ক্লোজ করার ব্র্যাকেটটি মিসিং ছিল