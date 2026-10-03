<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variation extends Model
{
    use HasFactory;

    // যেহেতু $guarded খালি রাখা হয়েছে, তাই 'image' সহ যেকোনো ফিল্ড অটোমেটিক সেভ হবে।
    protected $guarded = [];

    // চাইলে অটো-অ্যাপেন্ড করতে পারেন
    // protected $appends = ['display_title', 'size_label', 'color_label'];

    public function product()  { return $this->belongsTo(Product::class); }
    public function color()    { return $this->belongsTo(Color::class); }
    public function size()     { return $this->belongsTo(Size::class); }
    public function stocks()   { return $this->hasMany(ProductStock::class,'variation_id'); }

    /** Human-readable size text */
    public function getSizeLabelAttribute(): string
    {
        $s = $this->relationLoaded('size') ? $this->size : $this->size()->first();
        if (!$s) return '';
        // আপনার Size মডেলে যে কলামটা আছে সেটি ব্যবহার করুন:
        // সাধারণত 'title' বা 'name'
        return (string)($s->title ?? $s->name ?? '');
    }

    /** Human-readable color text */
    public function getColorLabelAttribute(): string
    {
        $c = $this->relationLoaded('color') ? $this->color : $this->color()->first();
        if (!$c) return '';
        // সাধারণত 'name' বা 'title'
        $label = (string)($c->name ?? $c->title ?? '');
        // 'Default' বা '000' হলে হাইড করতে চাইলে:
        if (in_array(strtolower($label), ['default', '000'])) return '';
        return $label;
    }

    /** Final title to show for a variation */
    public function getDisplayTitleAttribute(): string
    {
        // যদি variation টেবিলে নিজস্ব title/name/label থাকে, আগে সেটাই দেখান
        $admin = $this->title ?? $this->name ?? $this->label ?? null;

        $size  = $this->size_label;   // accessor
        $color = $this->color_label;  // accessor

        $display = $admin ?: trim($size . ($color ? ' - '.$color : ''));
        return $display !== '' ? $display : 'Variant';
    }
}