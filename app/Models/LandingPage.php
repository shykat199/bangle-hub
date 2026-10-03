<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LandingPageSlider;
use App\Models\reviewProductImage;
use App\Models\Product;
use App\Models\LandingPagePackage; 

class LandingPage extends Model
{
    use HasFactory;

    /**
     * ✅ ফিক্সড Fillable লিস্ট
     * page_type এবং কন্ট্রোলারে ব্যবহৃত অন্যান্য ফিল্ড যুক্ত করা হয়েছে।
     */
    protected $fillable = [
        // IDs & Type
        'product_id',
        'variation_id',
        'page_type', // ✅ এটি Type 5 সহ যেকোনো টাইপের পেজ সেভ করার জন্য কাজ করবে
        'slug', // অ্যাডমিন চাইলে URL-এ id-র বদলে নিজের slug বসাতে পারেন

        // Basic Info
        'title1',
        'title2',
        'video_url',
        'phone',
        'whatsapp',
        'call_text',
        'pay_text',

        // Images
        'image',
        'landing_bg',
        'right_product_image',

        // Pricing
        'new_price',
        'old_price',
        'regular_price_text',
        'offer_price_text',

        // Section Content
        'feature',
        'top_heading_text',
        'left_side_title',
        'left_side_desc',
        'left_product_details',
        'right_side_title',
        'right_side_desc',
        'review_top_text',

        // ✅ Dynamic Colors & Button Texts
        'theme_primary_col',
        'theme_gradient_col',
        'btn_bg_color',
        'btn_text_color',
        'btn_text_hero',
        'btn_text_video',
        'btn_text_feature',
        'btn_text_form',

        // ✅ NEW: Dynamic Text Fields
        'hot_badge_text',
        'warranty_text',
        'order_btn_text',
        'dhamaka_title',
        'offer_price_label',
        'currency_text',
        'call_to_action_text',
        'feature_title',
        'feature_list',
        'details_title',
        'review_title',
        'form_title',
        'form_subtitle',
        'name_label',
        'name_placeholder',
        'phone_label',
        'address_label',
        'address_placeholder',
        'delivery_label',
        'payment_title',
        'cod_title',
        'cod_subtitle',
        'online_payment_title',
        'online_payment_subtitle',
        'order_summary_title',
        'variation_label',
        'total_bill_label',
        'processing_text',
        'error_msg',

        // ==========================================
        // ✅ LATEST UPDATES (নতুন যুক্ত করা সেকশন)
        // ==========================================

        // ১. হিরো সেকশন ও কাউন্টডাউন
        'countdown_title',
        'countdown_bg_color',
        'countdown_text_color',
        'hero_btn_bg_color',
        'hero_btn_text_color',
        'countdown_hours',

        // ২. প্রাইস টেক্সট (Price Texts)
        'old_price_text',
        'new_price_text',

        // ৩. প্রতিশ্রুতি সেকশন (Promise Section)
        'promise_badge',
        'promise_title',
        'promise_img_badge',
        'promise_1_title', 'promise_1_desc',
        'promise_2_title', 'promise_2_desc',
        'promise_3_title', 'promise_3_desc',
        'negative_title', 'negative_tags',

        // ৪. চেনার উপায় (Identify Product)
        'identify_badge',
        'identify_title',
        'identify_subtitle',

        // ৫. ট্রাস্ট ব্যাজ (Trust Badges)
        'trust_sec_title',
        'trust_1_icon', 'trust_1_title',
        'trust_2_icon', 'trust_2_title',
        'trust_3_icon', 'trust_3_title',
        'trust_4_icon', 'trust_4_title',

        // ৬. আইকন ও ফিচার (Icon Features 1-8)
        'id_1_icon', 'id_1_title', 'id_1_desc',
        'id_2_icon', 'id_2_title', 'id_2_desc',
        'id_3_icon', 'id_3_title', 'id_3_desc',
        'id_4_icon', 'id_4_title', 'id_4_desc',
        'id_5_icon', 'id_5_title', 'id_5_desc',
        'id_6_icon', 'id_6_title', 'id_6_desc',
        'id_7_icon', 'id_7_title', 'id_7_desc',
        'id_8_icon', 'id_8_title', 'id_8_desc',

        // ৭. রিভিউ সেকশন ও পরিসংখ্যান (Reviews & Stats)
        'review_badge',
        'review_subtitle',
        'stat_1_num', 'stat_1_text',
        'stat_2_num', 'stat_2_text',
        'stat_3_num', 'stat_3_text',

        // ৮. কাস্টমার রিভিউ বক্স (Customer Reviews)
        'rev_1_text', 'rev_1_name', 'rev_1_loc',
        'rev_2_text', 'rev_2_name', 'rev_2_loc',
        'rev_3_text', 'rev_3_name', 'rev_3_loc',
        'rev_4_text', 'rev_4_name', 'rev_4_loc',

        // ৯. সচরাচর জিজ্ঞাসিত প্রশ্ন (FAQ)
        'faq_badge', 'faq_title',
        'faq_1_q', 'faq_1_a',
        'faq_2_q', 'faq_2_a',
        'faq_3_q', 'faq_3_a',
        'faq_4_q', 'faq_4_a',

        // ✅ Landing Page 9 (Cool Conversion Zone style) fields
        'hero_rating', 'hero_rating_count', 'hero_rating_label',
        'discount_save_text',
        'spec_title',
        'spec_1_label', 'spec_1_value',
        'spec_2_label', 'spec_2_value',
        'spec_3_label', 'spec_3_value',
        'spec_4_label', 'spec_4_value',
        'spec_5_label', 'spec_5_value',
        'spec_6_label', 'spec_6_value',
        'spec_7_label', 'spec_7_value',
        'stock_count', 'stock_text',
        'urgency_title', 'urgency_subtitle',
        'final_cta_title', 'final_cta_subtitle', 'final_cta_btn_text',
        'footer_company', 'footer_email', 'footer_copyright',
        'security_badge_text',

        // ==========================================
        // ✅ NEW: ৩.৫. বিস্তারিত বৈশিষ্ট্য (Organic Features)
        // ==========================================
        'special_feature_title',
        'sf_1_title', 'sf_1_desc',
        'sf_2_title', 'sf_2_desc',
        'sf_3_title', 'sf_3_desc',
        'sf_4_title', 'sf_4_desc',
        'sf_5_title', 'sf_5_desc',
        'sf_6_title', 'sf_6_desc',
        'sf_7_title', 'sf_7_desc',
        'sf_8_title', 'sf_8_desc',

        // ==========================================
        // ✅ Landing Pages 14 / 15 / 16 — new dynamic text fields
        // ==========================================
        // LP 14 (moringa)
        'brand_sub',
        'spec_badge',
        'identify_desc',
        'form_desc',
        'footer_contact_label',
        'footer_address_label',
        'spec_8_label', 'spec_8_value',
        'faq_5_q', 'faq_5_a',

        // LP 15 (jersey)
        'team_badge',
        'team_pick_btn',
        'promise_1_badge',
        'promise_2_badge',
        'gallery_badge',
        'form_badge',
        'btn_text_top',
        'hero_small_stats',
        'footer_desc',
    ];

    public function product(){
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function images() {
        return $this->hasMany(LandingPageSlider::class, 'landing_page_id');
    }
    
    public function review_images() {
        return $this->hasMany(reviewProductImage::class, 'landing_page_id');
    }

    // ✅✅✅ NEW: প্যাকেজের জন্য নতুন রিলেশনশিপ যুক্ত করা হলো ✅✅✅
    public function packages() {
        return $this->hasMany(LandingPagePackage::class, 'landing_page_id');
    }
}