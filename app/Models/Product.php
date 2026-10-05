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
        'is_wholesale' => 'boolean',
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

    /**
     * Smallest quantity a customer may order of this product (per variant).
     * 1 unless wholesale is enabled with a minimum above 1.
     */
    public function minOrderQty(): int
    {
        return $this->is_wholesale ? max(1, (int) $this->min_order_qty) : 1;
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
        // Gallery order is controlled from the admin edit page (drag & drop).
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /** Extra categories, on top of the main category_id / sub_category_id. */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }

    /**
     * Products that belong to any of the given categories — as main category,
     * as sub category, or as one of the extra categories.
     */
    public function scopeInCategory($query, $categoryIds)
    {
        $categoryIds = collect($categoryIds)->filter()->values()->all();

        return $query->where(function ($w) use ($categoryIds) {
            $w->whereIn('products.category_id', $categoryIds)
                ->orWhereIn('products.sub_category_id', $categoryIds)
                ->orWhereExists(function ($sub) use ($categoryIds) {
                    $sub->selectRaw('1')
                        ->from('category_product')
                        ->whereColumn('category_product.product_id', 'products.id')
                        ->whereIn('category_product.category_id', $categoryIds);
                });
        });
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

    /**
     * SQL twin of the resolveStock() helper, so product lists can be filtered
     * by the same stock number they display. Keep the two in sync.
     */
    public static function resolvedStockSql(): string
    {
        $rows = '(select sum(ps.quantity) from product_stocks ps where ps.variation_id = v.id)';
        $hasRows = 'exists (select 1 from product_stocks ps where ps.variation_id = v.id)';
        $variationStock = "case when $hasRows then greatest(0, coalesce($rows, 0)) else greatest(0, coalesce(v.stock_quantity, 0)) end";
        $ownStock = 'greatest(0, coalesce(products.stock_quantity, 0))';

        return "(case
            when not exists (select 1 from variations v where v.product_id = products.id) then $ownStock
            when coalesce(products.type, 'single') = 'variable' then
                (select coalesce(sum($variationStock), 0) from variations v where v.product_id = products.id)
            else
                (select case
                        when $hasRows then greatest(0, coalesce($rows, 0))
                        when coalesce(v.stock_quantity, 0) > 0 then v.stock_quantity
                        else $ownStock
                    end
                 from variations v where v.product_id = products.id order by v.id limit 1)
        end)";
    }

    /**
     * SQL for the price a product card shows (getProductInfo()): a variable
     * product shows its first variation's price, anything else its own —
     * discounted price when one is set. The shop price filter uses this so
     * a product is filtered by the same number the customer sees.
     */
    public static function displayPriceSql(): string
    {
        $own = 'if(products.after_discount > 0, products.after_discount, products.sell_price)';
        $variation = 'if(v.after_discount_price > 0 and v.after_discount_price < v.price, v.after_discount_price, v.price)';

        return "(case
            when coalesce(products.type, 'single') = 'variable'
                 and exists (select 1 from variations v where v.product_id = products.id)
            then (select $variation from variations v where v.product_id = products.id order by v.id limit 1)
            else $own
        end)";
    }

    /** Orderable on the storefront (productIsOrderable()): stock is managed and something is left. */
    public function scopeAvailability($query, $status)
    {
        $stock = static::resolvedStockSql();

        return match ($status) {
            'in_stock'  => $query->where('products.is_stock', 1)->whereRaw("$stock > 0"),
            'stock_out' => $query->where(fn ($w) => $w->where('products.is_stock', '!=', 1)->orWhereNull('products.is_stock')->orWhereRaw("$stock <= 0")),
            default     => $query,
        };
    }

    /** $status: in_stock (above the low-stock limit), low_stock (1..limit), stock_out (0). */
    public function scopeStockStatus($query, $status, int $lowLimit = 5)
    {
        $stock = static::resolvedStockSql();

        return match ($status) {
            'in_stock'  => $query->whereRaw("$stock > ?", [$lowLimit]),
            'low_stock' => $query->whereRaw("$stock between 1 and ?", [$lowLimit]),
            'stock_out' => $query->whereRaw("$stock <= 0"),
            default     => $query,
        };
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