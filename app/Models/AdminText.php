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

        'home_strip',
    ];

    protected $casts = [
        'home_strip' => 'array',
    ];

    // Homepage bottom strip as it looked before it became editable. Blank shop
    // name / address / phone fall back to the site's own in the view.
    public const HOME_STRIP_DEFAULTS = [
        'active'        => true,
        'shop_label'    => 'Visit Our Wholesale Shop',
        'shop_name'     => '',
        'shop_address'  => '',
        'items'         => [
            ['icon' => 'fa-handshake',  'title' => 'Wholesale Only',    'text' => 'Retail Not Available'],
            ['icon' => 'fa-globe-asia', 'title' => 'Imported Products', 'text' => 'China | India | Dubai | Malaysia | USA | UK'],
            ['icon' => 'fa-shield-alt', 'title' => 'Genuine Quality',   'text' => 'Trusted by Resellers'],
        ],
        'contact_label' => 'Contact Us',
        'contact_phone' => '',
    ];

    // Font Awesome 5 icons the admin can pick for a strip item.
    public const HOME_STRIP_ICONS = [
        'fa-handshake'       => 'Handshake',
        'fa-globe-asia'      => 'Globe',
        'fa-shield-alt'      => 'Shield',
        'fa-truck'           => 'Truck',
        'fa-shipping-fast'   => 'Fast Shipping',
        'fa-box-open'        => 'Open Box',
        'fa-boxes'           => 'Boxes',
        'fa-warehouse'       => 'Warehouse',
        'fa-store'           => 'Store',
        'fa-tags'            => 'Price Tags',
        'fa-money-bill-wave' => 'Money',
        'fa-credit-card'     => 'Card',
        'fa-award'           => 'Award',
        'fa-medal'           => 'Medal',
        'fa-certificate'     => 'Certificate',
        'fa-check-circle'    => 'Check',
        'fa-thumbs-up'       => 'Thumbs Up',
        'fa-star'            => 'Star',
        'fa-gem'             => 'Gem',
        'fa-headset'         => 'Support',
        'fa-clock'           => 'Clock',
        'fa-undo-alt'        => 'Return',
    ];

    public function homeStrip(): array
    {
        $saved = is_array($this->home_strip) ? $this->home_strip : [];

        return array_merge(self::HOME_STRIP_DEFAULTS, array_intersect_key($saved, self::HOME_STRIP_DEFAULTS));
    }
}
