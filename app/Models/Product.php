<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; 
use App\Models\Category;
use App\Models\Size;
use App\Models\ProductStock;
use App\Models\ProductImage;
use App\Models\Type;
use App\Models\Variation;
use App\Models\User;
use App\Models\ProductReview;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'weight' => 'float',
        'is_video_active' => 'boolean',
        'is_popular' => 'integer',
    ];

    protected $appends = [
        'total_stock',
        'availability_text',
        'feed_description',
    ];

    protected static function booted()
    {
        static::creating(function ($product) {
            if (empty($product->slug) && !empty($product->name)) {
                $base = Str::slug($product->name);
                $slug = $base;
                $i = 1;

                while (static::where('slug', $slug)->exists()) {
                    $slug = $base . '-' . $i;
                    $i++;
                }

                $product->slug = $slug;
            }
        });

        static::updating(function ($product) {
            if (empty($product->slug) && !empty($product->name)) {
                $base = Str::slug($product->name);
                $slug = $base;
                $i = 1;

                while (static::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                    $slug = $base . '-' . $i;
                    $i++;
                }

                $product->slug = $slug;
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * MySQL casts a string to a number by reading its leading digits, so
     * `id = '4k-ultra-webcam-pro'` silently matched id 4 — any product whose
     * name starts with a digit could resolve to a completely different
     * product (wrong name, price, image). Only fall back to id lookup when
     * the value is genuinely numeric, e.g. an old bookmarked "/product-show/42".
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $query = $this->where('slug', $value);

        if (ctype_digit((string) $value)) {
            $query->orWhere('id', $value);
        }

        return $query->firstOrFail();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sizes()
    {
        return $this->belongsToMany(Size::class, 'product_sizes');
    }

    public function brand()
    {
        return $this->belongsTo(Type::class, 'type_id');
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variations()
    {
        return $this->hasMany(Variation::class, 'product_id');
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function variation()
    {
        return $this->belongsTo(Variation::class, 'id', 'product_id')->orderBy('id');
    }

    public function getTotalStockAttribute()
    {
        if ($this->is_stock == 0) {
            return 0;
        }

        if ($this->type === 'variable') {
            return (int) ($this->variations_sum_stock_quantity ?? $this->variations()->sum('stock_quantity'));
        }

        return max(0, (int) ($this->stock_quantity ?? 0));
    }

    public function getAvailabilityTextAttribute()
    {
        return $this->total_stock > 0 ? 'in stock' : 'out of stock';
    }

    public function getFeedDescriptionAttribute()
    {
        $desc = trim(strip_tags($this->description ?? ''));
        
        if (empty($desc)) {
            $desc = trim(strip_tags($this->short_description ?? ''));
        }
        
        if (empty($desc)) {
            $desc = trim(strip_tags($this->body ?? ''));
        }
        
        if (empty($desc)) {
            $desc = $this->name;
        }
        
        return $desc;
    }
}