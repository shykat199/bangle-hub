<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Information;
use App\Models\ProductStock;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\Size;
use App\Models\Type;
use App\Models\Color;
use App\Models\Variation;
use DB;
use App\Exports\ProductExport;
use Maatwebsite\Excel\Facades\Excel;
use Image;

class ProductController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));

        $items = Product::with([
                'variations.size',
                'variations.color',
                'variations.stocks'
            ])
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->limit(20)
            ->get();

        $data = $items->map(function ($p) {
            $imageUrl = function_exists('getImage')
                ? getImage('products', $p->image)
                : asset('products/' . $p->image);

            $basePrice = 0.0;
            foreach ([$p->after_discount, $p->sell_price, $p->regular_price] as $cand) {
                $n = is_null($cand) ? null : (float) $cand;
                if (!is_null($n) && $n > 0) {
                    $basePrice = $n;
                    break;
                }
            }

            $vars = $p->variations->map(function ($v) {
                $best = 0.0;
                foreach ([$v->after_discount_price, $v->discount_price ?? null, $v->price] as $cand) {
                    $n = is_null($cand) ? null : (float) $cand;
                    if (!is_null($n) && $n > 0) {
                        $best = $n;
                        break;
                    }
                }

                return [
                    'id' => (int) $v->id,
                    'size_id' => (int) ($v->size_id ?? 0),
                    'size_name' => $v->size->name ?? ($v->size_label ?? $v->size ?? ''),
                    'color_id' => (int) ($v->color_id ?? 0),
                    'color_name' => $v->color->name ?? ($v->color_label ?? $v->color ?? ''),
                    'price' => (float) $best,
                    'raw' => (float) ($v->price ?? 0),
                    'stock' => (int) ($v->stocks->sum('quantity') ?? 0),
                    'image' => $v->image ? asset('products/' . $v->image) : null, 
                ];
            })->values();

            $sizes = $vars->filter(fn($v) => !empty($v['size_id']))
                ->map(fn($v) => ['id' => $v['size_id'], 'name' => $v['size_name']])
                ->unique('id')
                ->values();

            $colors = $vars->filter(fn($v) => !empty($v['color_id']))
                ->map(fn($v) => ['id' => $v['color_id'], 'name' => $v['color_name']])
                ->unique('id')
                ->values();

            $matrix = [];
            foreach ($vars as $v) {
                $key = $v['size_id'] . '_' . $v['color_id'];
                $matrix[$key] = [
                    'variation_id' => $v['id'],
                    'price' => $v['price'],
                    'raw' => $v['raw'],
                    'stock' => $v['stock'],
                    'image' => $v['image'], 
                ];
            }

            return [
                'id' => (int) $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'image' => $imageUrl,
                'price' => (float) $basePrice,
                'sell_price' => (float) ($p->sell_price ?? 0),
                'regular_price' => (float) ($p->regular_price ?? 0),
                'after_discount_price' => (float) ($p->after_discount ?? 0),
                'variations' => $vars,
                'sizes' => $sizes,
                'colors' => $colors,
                'matrix' => $matrix,
            ];
        })->values();

        return response()->json($data);
    }

    public function variationMatrix($productId)
    {
        $p = Product::with([
                'variations.size',
                'variations.color',
                'variations.stocks'
            ])->findOrFail($productId);

        $vars = $p->variations->map(function ($v) {
            $best = 0.0;
            foreach ([$v->after_discount_price, $v->discount_price ?? null, $v->price] as $cand) {
                $n = is_null($cand) ? null : (float) $cand;
                if (!is_null($n) && $n > 0) {
                    $best = $n;
                    break;
                }
            }

            return [
                'id' => (int) $v->id,
                'size_id' => (int) ($v->size_id ?? 0),
                'size_name' => $v->size->name ?? ($v->size_label ?? $v->size ?? ''),
                'color_id' => (int) ($v->color_id ?? 0),
                'color_name' => $v->color->name ?? ($v->color_label ?? $v->color ?? ''),
                'price' => (float) $best,
                'raw' => (float) ($v->price ?? 0),
                'stock' => (int) ($v->stocks->sum('quantity') ?? 0),
                'image' => $v->image ? asset('products/' . $v->image) : null, 
            ];
        })->values();

        $sizes = $vars->filter(fn($v) => !empty($v['size_id']))
            ->map(fn($v) => ['id' => $v['size_id'], 'name' => $v['size_name']])
            ->unique('id')
            ->values();

        $colors = $vars->filter(fn($v) => !empty($v['color_id']))
            ->map(fn($v) => ['id' => $v['color_id'], 'name' => $v['color_name']])
            ->unique('id')
            ->values();

        $matrix = [];
        foreach ($vars as $v) {
            $key = $v['size_id'] . '_' . $v['color_id'];
            $matrix[$key] = [
                'variation_id' => $v['id'],
                'price' => $v['price'],
                'raw' => $v['raw'],
                'stock' => $v['stock'],
                'image' => $v['image'], 
            ];
        }

        return response()->json([
            'product_id' => (int) $p->id,
            'sizes' => $sizes,
            'colors' => $colors,
            'variations' => $vars,
            'matrix' => $matrix,
        ]);
    }

    public function productExport()
    {
        return Excel::download(new ProductExport, 'products.xlsx');
    }

    public function index()
    {
        if (!auth()->user()->can('product.view')) {
            abort(403, 'unauthorized');
        }

        $cat_id = request()->category_id;
        $q = request()->q;
        $stock_status = request()->stock_status;
        $brand_id = request()->type_id;
        $product_type = request()->product_type;
        // Stock কলাম এখন resolveStock() দিয়ে ভ্যারিয়েশন ও product_stocks-ও দেখে,
        // তাই eager-load — নাহলে প্রতি সারিতে দুইটা করে বাড়তি কোয়েরি হতো (N+1)।
        $query = Product::query()->with(['variations.stocks', 'category', 'categories:id,name']);

        if (!empty($q)) {
            $query->where(function ($row) use ($q) {
                $row->where('name', 'Like', '%' . $q . '%')
                    ->orwhere('description', 'Like', '%' . $q . '%')
                    ->orwhere('sku', 'Like', '%' . $q . '%');
            });
        }

        if (auth()->user()->hasRole('admin') == false) {
            $query->where('user_id', auth()->user()->id);
        }

        if (!empty($cat_id)) {
            $query->inCategory([$cat_id]);
        }

        if (!empty($brand_id)) {
            $query->where('type_id', $brand_id);
        }

        if (in_array($product_type, ['single', 'variable'], true)) {
            $query->where('type', $product_type);
        }

        // Stock filter uses the same number the Stock column shows. Counts are
        // taken before the stock filter so every option shows its own total.
        $lowLimit = (int) (Information::orderBy('id', 'desc')->value('stock_warning_limit') ?? 5);
        $stockCounts = ['' => (clone $query)->count()];
        foreach (['in_stock', 'low_stock', 'stock_out'] as $status) {
            $stockCounts[$status] = (clone $query)->stockStatus($status, $lowLimit)->count();
        }

        if (in_array($stock_status, ['in_stock', 'low_stock', 'stock_out'], true)) {
            $query->stockStatus($stock_status, $lowLimit);
        }

        $categories = Category::where('parent_id', null)->get();
        $brands = Type::orderBy('name')->get();
        $items = $query->latest()->paginate(30);

        return view('backend.products.index', compact('items', 'q', 'categories', 'cat_id', 'brands', 'brand_id', 'product_type', 'stock_status', 'stockCounts', 'lowLimit'));
    }

    /** Product the current user may edit stock for (workers only see their own products, like the list). */
    private function quickStockProduct($id)
    {
        if (!auth()->user()->can('product.edit')) {
            abort(403, 'unauthorized');
        }

        return Product::with(['variations.size', 'variations.color', 'variations.stocks'])
            ->when(!auth()->user()->hasRole('admin'), fn ($q) => $q->where('user_id', auth()->id()))
            ->findOrFail($id);
    }

    /** Data for the quick stock modal on the product list. */
    public function quickStock($id)
    {
        $product = $this->quickStockProduct($id);
        $isVariable = $product->type === 'variable' && $product->variations->isNotEmpty();

        return response()->json([
            'status' => true,
            'name' => $product->name,
            'sku' => $product->sku,
            'image' => getImage('products', $product->image),
            'is_variable' => $isVariable,
            'stock' => resolveStock($product),
            'variations' => $isVariable ? $product->variations->map(fn ($v) => [
                'id' => $v->id,
                // display_title leaves a stray " - " when a variation has only a size or only a colour.
                'title' => trim($v->display_title, ' -') ?: 'Variant',
                'image' => $v->image ? getImage('products', $v->image) : null,
                'stock' => resolveVariationStock($v),
            ])->values() : [],
        ]);
    }

    public function quickStockUpdate(Request $request, $id)
    {
        $product = $this->quickStockProduct($id);
        $isVariable = $product->type === 'variable' && $product->variations->isNotEmpty();

        $request->validate([
            'stock' => $isVariable ? 'nullable' : 'required|integer|min:0',
            'variations' => $isVariable ? 'required|array' : 'nullable',
            'variations.*' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $product, $isVariable) {
            if ($isVariable) {
                // Only this product's own variations are touched, whatever ids were posted.
                foreach ($product->variations as $variation) {
                    if ($request->has('variations.' . $variation->id)) {
                        $this->setVariationStock($product->id, $variation->id, (int) $request->input('variations.' . $variation->id));
                    }
                }
            } else {
                // Same places the edit form writes a single product's stock.
                $qty = (int) $request->stock;
                $product->stock_quantity = $qty;
                $product->save();

                if ($singleVar = $product->variations->first()) {
                    $this->setVariationStock($product->id, $singleVar->id, $qty);
                }
            }

            $this->cleanOrphanStocks($product->id);
        });

        return response()->json([
            'status' => true,
            'msg' => 'Stock updated !!',
            'stock' => resolveStock($product->fresh(['variations.stocks'])),
        ]);
    }

    public function getSubcategory()
    {
        $cats = Category::where('parent_id', request('cat_id'))
            ->select('name', 'id')->pluck('name', 'id')->toArray();

        return response()->json($cats);
    }

    public function create()
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'unauthorized');
        }

        $cats = Category::whereNull('parent_id')->with('subcats')->get();
        $sizes = Size::all();
        $types = Type::all();
        $colors = Color::all();
        return view('backend.products.create', compact('cats', 'sizes', 'types', 'colors'));
    }

    /**
     * একটি ভ্যারিয়েশনের স্টক লেখার একমাত্র নিরাপদ উপায়।
     * আগে `firstOrNew` ব্যবহার হতো — কিন্তু একই ভ্যারিয়েশনের নামে একাধিক
     * product_stocks row থাকলে (ভুল product_id সহ) নতুন row তৈরি হয়ে
     * স্টক ফুলে যেত। এখন ওই ভ্যারিয়েশনের সব row মুছে ঠিক একটাই রাখা হয়,
     * আর variations টেবিলের সংখ্যাও এক করে দেওয়া হয়।
     */
    private function setVariationStock(int $productId, int $variationId, int $qty): void
    {
        $qty = max(0, $qty);

        ProductStock::where('variation_id', $variationId)->delete();

        $stock = new ProductStock();
        $stock->product_id   = $productId;
        $stock->variation_id = $variationId;
        $stock->quantity     = $qty;
        $stock->save();

        Variation::where('id', $variationId)->update(['stock_quantity' => $qty]);
    }

    /** ওই প্রোডাক্টের যেসব stock row-এর ভ্যারিয়েশন আর নেই, সেগুলো সাফ করা */
    private function cleanOrphanStocks(int $productId): void
    {
        $validIds = Variation::where('product_id', $productId)->pluck('id')->toArray();

        ProductStock::where('product_id', $productId)
            ->when(!empty($validIds), fn ($q) => $q->whereNotIn('variation_id', $validIds))
            ->delete();
    }

    public function store(Request $request)
    {
        ini_set('memory_limit', '512M');

        if (!auth()->user()->can('product.create')) {
            abort(403, 'unauthorized');
        }

        $data = $request->validate([
            'name' => 'required',
            'slug' => 'nullable|string|max:255',
            'type' => 'required|in:single,variable',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', 
            'category_id' => 'required',
            'sub_category_id' => 'nullable',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:categories,id',
            'type_id' => 'nullable',
            'short_description' => 'nullable',
            'description' => 'nullable',
            'body' => 'nullable',
            'feature' => 'nullable',
            'sku' => 'nullable|unique:products,sku',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'purchase_prices' => 'nullable',
            'sell_price' => 'required|numeric',
            'regular_price' => 'nullable',
            'is_stock' => 'nullable',
            'video_link' => 'nullable',
            'is_video_active' => 'nullable',
            'discount_type' => 'nullable',
            'dicount_amount' => 'nullable',
            'after_discount' => 'nullable',
            'weight' => 'nullable|numeric', 
            'is_wholesale' => 'nullable|boolean',
            'min_order_qty' => 'nullable|required_if:is_wholesale,1|integer|min:1',
        ]);

        $data['is_wholesale'] = $request->boolean('is_wholesale');
        $data['min_order_qty'] = $request->filled('min_order_qty') ? (int) $request->min_order_qty : null;

        // 'images' হলো গ্যালারি ফাইলের অ্যারে — products টেবিলে ওই নামে কোনো কলাম নেই।
        // Product-এ $guarded = [] বলে validated অ্যারের সব কী-ই mass-assign হয়ে যায়,
        // ফলে গ্যালারি ছবি দিলে insert-এ "Unknown column 'images'" এসে পুরো সেভ ফেল করত।
        // ছবিগুলো নিচে আলাদা করে product_images-এ যায়, তাই এখানে বাদ দেওয়া হলো।
        unset($data['images']);
        // Extra categories live in the category_product table, not on products.
        unset($data['category_ids']);

        $data['user_id'] = auth()->user()->id;

        // Admin may type a custom slug; left blank it is built from the name.
        $data['slug'] = $this->uniqueSlug($request->filled('slug') ? $request->slug : $request->name);

        DB::beginTransaction();
        try {
            if ($request->hasFile('image')) {
                $image = Image::make($request->file('image'));
                $extention = $request->image->getClientOriginalExtension();
                $image_name = Str::slug($request->name) . date('-Y-m-d-h-i-s-') . rand(999, 9999) . '.' . $extention;
                $destinationPath = public_path('products/');
                
                $image->resize(1200, 1200, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $image->save($destinationPath . $image_name);
                $data['image'] = $image_name;

                $destinationPathThumbnail = public_path('thumb_products/');
                $image->resize(500, 500, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $image->save($destinationPathThumbnail . $image_name);
            }

            // Create Product
            $product = Product::create($data);
            $this->syncExtraCategories($product, $request->input('category_ids', []));
            
            // FORCE UPDATE STOCK AND SETTINGS TO BYPASS $fillable
            $mainQty = (int) ($request->pro_quantity ?? 0);
            $product->stock_quantity = max(0, $mainQty); // FIX: 0 দিলে 0-ই থাকবে (আগে জোর করে 1 হতো)
            $product->is_stock = (int) ($request->is_stock ?? 0);
            $product->is_video_active = (int) ($request->is_video_active ?? 1);
            $product->save();

            if (isset($request->images)) {
                $image_data = [];
                // New gallery images go after the existing ones.
                $nextOrder = (int) $product->images()->max('sort_order');
                foreach ($request->images as $image) {
                    $extention = $image->getClientOriginalExtension();
                    $image_name = Str::slug($request->name) . date('-Y-m-d-h-i-s-') . rand(999, 9999) . '.' . $extention;
                    $img = Image::make($image);
                    $destinationPath = public_path('products/');
                    
                    $img->resize(1200, 1200, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $img->save($destinationPath . $image_name);
                    $image_data[] = ['image' => $image_name, 'sort_order' => ++$nextOrder];
                }
                if (!empty($image_data)) {
                    $product->images()->createMany($image_data);
                }
            }

            if ($request->type == 'variable') {
                $sizes = $this->variantOptionIds($request->size_id);
                $colors = $this->variantOptionIds($request->color_id);
                $purchases = (array) $request->purchase_price;
                $prices = (array) $request->price;
                $after = (array) $request->after_discount_price;
                $qtys = (array) $request->quantity;
                $varImages = $request->file('variation_image'); 

                $count = max(count($sizes), count($colors), count($prices));

                for ($i = 0; $i < $count; $i++) {
                    if (empty($sizes[$i]) && empty($colors[$i]) && empty($prices[$i])) continue;

                    $vQty = (int) ($qtys[$i] ?? 0);
                    $qty = max(0, $vQty); // FIX: 0 = সত্যিই স্টক নেই

                    $varImageName = null;
                    // isValid() only proves the upload arrived intact — it says
                    // nothing about the file type, so a .php upload used to land
                    // in public/products/ and run. Extension + MIME are checked.
                    if (isset($varImages[$i]) && \App\Utils\Util::isSafeImageUpload($varImages[$i])) {
                        $vImage = $varImages[$i];
                        $ext = strtolower($vImage->getClientOriginalExtension());
                        $varImageName = 'var-' . time() . '-' . rand(1000, 9999) . '.' . $ext;
                        $vImage->move(public_path('products/'), $varImageName);
                    }

                    $var = new Variation();
                    $var->product_id = $product->id;
                    $var->size_id = !empty($sizes[$i]) ? $sizes[$i] : null;
                    $var->color_id = !empty($colors[$i]) ? $colors[$i] : null;
                    $var->image = $varImageName; 
                    $var->purchase_price = $purchases[$i] ?? 0;
                    $var->price = $prices[$i] ?? 0;
                    $var->after_discount_price = $after[$i] ?? null;
                    $var->stock_quantity = $qty; // Force save
                    $var->save();

                    $this->setVariationStock($product->id, $var->id, $qty);
                }
            } else {
                $singleQty = (int) ($request->pro_quantity ?? 0);
                $qty = max(0, $singleQty); // FIX: 0 = সত্যিই স্টক নেই

                // --- সিঙ্গেল প্রোডাক্টের ক্ষেত্রে size_id এবং color_id null করা হলো ---
                $var = new Variation();
                $var->product_id = $product->id;
                $var->size_id = null;
                $var->color_id = null;
                $var->purchase_price = $request->purchase_prices ?? 0;
                $var->price = $request->sell_price ?? 0;
                $var->after_discount_price = $request->after_discount;
                $var->stock_quantity = $qty; // Force save
                $var->save();
                // ----------------------------------------------------------------------

                $this->setVariationStock($product->id, $var->id, $qty);
            }

            DB::commit();
            return response()->json(['status' => true, 'msg' => 'Product Is Created !!', 'url' => route('admin.products.index')]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $product = Product::with('sizes', 'sizes.stocks')->find($id);
        return view('backend.products.show', compact('product'));
    }

    public function edit($id)
    {
        if (!auth()->user()->can('product.edit')) {
            abort(403, 'unauthorized');
        }

        // stocks-ও লোড করা হয় — এডিট ফর্মে অর্ডারে কমে যাওয়া আসল স্টকই দেখাতে হবে
        $item = Product::with(['sizes', 'images', 'variations.stocks', 'categories:id'])->findOrFail($id);
        $cats = Category::whereNull('parent_id')->with('subcats')->get();
        $sizes = Size::all();
        $types = Type::all();
        $colors = Color::all();
        $subs = Category::where('parent_id', $item->category_id)->get();

        return view('backend.products.edit', compact('item', 'cats', 'sizes', 'types', 'subs', 'colors'));
    }

    public function productCopy($id)
    {
        $item = Product::with('sizes')->find($id);
        $cats = Category::whereNull('parent_id')->get();
        $sizes = Size::all();
        $types = Type::all();
        $colors = Color::all();
        $subs = Category::where('parent_id', $item->category_id)->get();
        return view('backend.products.copy', compact('item', 'cats', 'sizes', 'types', 'subs', 'colors'));
    }

    /**
     * One-click duplicate: product row, gallery images, sizes, variations and
     * their stocks are all copied to a new product whose slug ends in "-copy".
     *
     * destroy() unlinks image files from disk, so the copy gets its own
     * physical image files — otherwise deleting one product would break the
     * other's images.
     */
    public function duplicate($id)
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'unauthorized');
        }

        $product = Product::with(['images', 'sizes', 'variations'])->findOrFail($id);
        $copiedFiles = [];

        DB::beginTransaction();
        try {
            $new = $product->replicate();

            // Strip any "-copy"/"-copy-N" tail first, so copying a copy gives
            // "name-copy-2" instead of "name-copy-copy".
            $baseSlug = $this->stripCopySuffix($product->slug ?: Str::slug($product->name)) . '-copy';
            $slug = $baseSlug;
            $i = 2;
            while (Product::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $i++;
            }
            $new->slug = $slug;

            // update() validates sku as unique, so an identical sku would make
            // the copy impossible to save from the edit page.
            if (!empty($product->sku)) {
                $baseSku = $this->stripCopySuffix($product->sku) . '-copy';
                $sku = $baseSku;
                $i = 2;
                while (Product::where('sku', $sku)->exists()) {
                    $sku = $baseSku . '-' . $i++;
                }
                $new->sku = $sku;
            }

            $new->image = $this->copyProductFile($product->image, ['products', 'thumb_products'], $copiedFiles);
            $new->optional_image = $this->copyProductFile($product->optional_image, ['products'], $copiedFiles);
            $new->user_id = auth()->id();
            $new->save();
            $new->categories()->sync($product->categories()->pluck('categories.id')->all());

            foreach ($product->images as $img) {
                $new->images()->create([
                    'image' => $this->copyProductFile($img->image, ['products'], $copiedFiles),
                    'sort_order' => $img->sort_order,
                ]);
            }

            $new->sizes()->sync($product->sizes->pluck('id'));

            foreach ($product->variations as $variation) {
                $newVariation = $variation->replicate();
                $newVariation->product_id = $new->id;
                $newVariation->image = $this->copyProductFile($variation->image, ['products'], $copiedFiles);
                $newVariation->save();

                foreach (ProductStock::where('product_id', $product->id)->where('variation_id', $variation->id)->get() as $stock) {
                    $newStock = $stock->replicate();
                    $newStock->product_id = $new->id;
                    $newStock->variation_id = $newVariation->id;
                    $newStock->save();
                }
            }

            // product-level stock rows (single products) that aren't tied to a variation
            foreach (ProductStock::where('product_id', $product->id)->whereNull('variation_id')->get() as $stock) {
                $newStock = $stock->replicate();
                $newStock->product_id = $new->id;
                $newStock->save();
            }

            DB::commit();
            return back()->with('success', 'Product duplicated: ' . $new->slug);
        } catch (\Exception $e) {
            DB::rollback();
            foreach ($copiedFiles as $path) {
                @unlink($path);
            }
            return back()->with('error', 'Duplicate failed: ' . $e->getMessage());
        }
    }

    /**
     * Size / color ids posted per variant row. Anything non-numeric (the
     * "+ Add new…" placeholder left selected) counts as "none".
     */
    private function variantOptionIds($ids): array
    {
        return array_map(fn ($id) => is_numeric($id) ? $id : null, (array) $ids);
    }

    /**
     * Product form: "+ Add new…" in a variant's Size / Color dropdown. Creates
     * the size or color (or returns the existing one with the same name) so it
     * can be selected without leaving the form.
     */
    public function storeVariantOption(Request $request)
    {
        if (!auth()->user()->can('product.create') && !auth()->user()->can('product.edit')) {
            abort(403, 'unauthorized');
        }

        $request->merge(['name' => trim((string) $request->name)]);
        $request->validate([
            'type' => 'required|in:size,color',
            'name' => 'required|string|max:255',
        ]);

        [$model, $column] = $request->type === 'size' ? [Size::class, 'title'] : [Color::class, 'name'];

        $option = $model::whereRaw("LOWER($column) = ?", [mb_strtolower($request->name)])->first();
        $existed = (bool) $option;
        if (!$option) {
            $option = $model::create([$column => $request->name]);
        }

        return response()->json([
            'status' => true,
            'id' => $option->id,
            'name' => $option->$column,
            'msg' => $existed
                ? ucfirst($request->type) . ' already exists — selected it.'
                : ucfirst($request->type) . ' added.',
        ]);
    }

    /**
     * Removes trailing "-copy" / "-copy-N" segments left by earlier duplicates.
     */
    /** The main and sub category are already on the product, so they are not repeated as extras. */
    private function syncExtraCategories(Product $product, $categoryIds): void
    {
        $ids = collect($categoryIds)->map(fn ($id) => (int) $id)->filter()
            ->reject(fn ($id) => $id === (int) $product->category_id || $id === (int) $product->sub_category_id)
            ->unique()->values()->all();

        $product->categories()->sync($ids);
    }

    private function uniqueSlug($value, $ignoreId = null): string
    {
        $base = Str::slug((string) $value);
        $slug = $base;
        $count = 1;
        while (Product::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $count++;
        }

        return $slug;
    }

    private function stripCopySuffix($value)
    {
        $stripped = preg_replace('/(-copy(-\d+)?)+$/i', '', $value);

        return $stripped !== '' ? $stripped : $value;
    }

    /**
     * Copies an image file into each given public folder under a fresh name
     * and returns that name. Returns the original value when there is no file.
     */
    private function copyProductFile($fileName, array $folders, array &$copiedFiles)
    {
        if (empty($fileName) || !file_exists(public_path($folders[0] . '/' . $fileName))) {
            return $fileName;
        }

        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $baseName = preg_replace('/(-copy-[A-Za-z0-9]{6})+$/', '', pathinfo($fileName, PATHINFO_FILENAME));
        $newName = $baseName . '-copy-' . Str::random(6) . ($ext ? '.' . $ext : '');

        foreach ($folders as $folder) {
            $src = public_path($folder . '/' . $fileName);
            if (file_exists($src) && copy($src, public_path($folder . '/' . $newName))) {
                $copiedFiles[] = public_path($folder . '/' . $newName);
            }
        }

        return $newName;
    }

    public function update(Request $request, $id)
    {
        ini_set('memory_limit', '512M'); 

        if (!auth()->user()->can('product.edit')) {
            abort(403, 'unauthorized');
        }

        $product = Product::with(['variations', 'variations.stocks'])->findOrFail($id);

        $data = $request->validate([
            'name' => 'required',
            'slug' => 'nullable|string|max:255',
            'type' => 'required|in:single,variable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120', 
            'category_id' => 'required',
            'sub_category_id' => 'nullable',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:categories,id',
            'type_id' => 'nullable',
            'short_description' => 'nullable',
            'description' => 'nullable',
            'body' => 'nullable',
            'feature' => 'nullable',
            'sku' => 'nullable|unique:products,sku,' . $id,
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'purchase_prices' => 'nullable',
            'regular_price' => 'nullable',
            'sell_price' => 'required|numeric',
            'is_stock' => 'nullable',
            'video_link' => 'nullable',
            'is_video_active' => 'nullable',
            'discount_type' => 'nullable',
            'dicount_amount' => 'nullable',
            'after_discount' => 'nullable',
            'weight' => 'nullable|numeric', 
            'is_wholesale' => 'nullable|boolean',
            'min_order_qty' => 'nullable|required_if:is_wholesale,1|integer|min:1',
        ]);

        $data['is_wholesale'] = $request->boolean('is_wholesale');
        $data['min_order_qty'] = $request->filled('min_order_qty') ? (int) $request->min_order_qty : null;
        
        // store()-এর মতোই: গ্যালারি ফাইলের অ্যারে products টেবিলের কলাম নয়,
        // mass-assign হলে update-ও "Unknown column 'images'" দিয়ে ফেল করে।
        unset($data['images']);
        // Extra categories live in the category_product table, not on products.
        unset($data['category_ids']);

        // আগে প্রতিবার সেভে নাম থেকে slug নতুন করে বানানো হতো — নামের একটা অক্ষর
        // বদলালেই URL বদলে যেত আর পুরনো লিংক (ফেসবুক অ্যাড, শেয়ার করা লিংক,
        // গুগলে ইনডেক্স হওয়া পেজ) সব 404 হয়ে যেত। slug একবার বসলে আর বদলায় না;
        // কোনো কারণে ফাঁকা থাকলে তখনই শুধু বানানো হয়।
        // The slug only changes when the admin edits the slug field themselves.
        $requestedSlug = Str::slug((string) $request->slug);
        if ($requestedSlug !== '' && $requestedSlug !== $product->slug) {
            $data['slug'] = $this->uniqueSlug($requestedSlug, $id);
        } elseif (empty($product->slug)) {
            $data['slug'] = $this->uniqueSlug($request->name, $id);
        } else {
            unset($data['slug']);
        }

        // পুরনো মূল ছবি এখন আর আগেভাগে মোছা হয় না। আগে সেভ শেষ হওয়ার আগেই ফাইল
        // মুছে ফেলা হতো — পরে কোনো ধাপে এরর হলে DB rollback হয়ে পুরনো নামেই ফিরে যেত,
        // কিন্তু ফাইল তো নেই, তাই ছবি ভাঙা দেখাত আর বারবার আপলোড করতে হতো।
        // এখন commit সফল হলে তবেই পুরনো ফাইল মোছা হয়; ফেল করলে নতুন ফাইলটা মোছা হয়।
        $oldMainImage = null;
        $newMainImage = null;

        DB::beginTransaction();
        try {
            if ($request->hasFile('image')) {
                $oldMainImage = $product->image;

                $image = Image::make($request->file('image'));
                $extention = $request->image->getClientOriginalExtension();
                $image_name = Str::slug($request->name) . date('-Y-m-d-h-i-s-') . rand(999, 9999) . '.' . $extention;

                $destinationPath = public_path('products/');
                $image->resize(1200, 1200, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $image->save($destinationPath . $image_name);
                $data['image'] = $image_name;
                $newMainImage = $image_name;

                $destinationPathThumbnail = public_path('thumb_products/');
                $image->resize(500, 500, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $image->save($destinationPathThumbnail . $image_name);
            }

            // Update Product
            $product->update($data);
            $this->syncExtraCategories($product, $request->input('category_ids', []));
            
            // FORCE UPDATE STOCK AND SETTINGS TO BYPASS $fillable
            $mainQty = (int) ($request->pro_quantity ?? 0);
            $product->stock_quantity = max(0, $mainQty); // FIX: 0 দিলে 0-ই থাকবে (আগে জোর করে 1 হতো)
            $product->is_stock = (int) ($request->is_stock ?? 0);
            $product->is_video_active = (int) ($request->is_video_active ?? 1);
            $product->after_discount = $request->after_discount;
            $product->dicount_amount = $request->dicount_amount;
            $product->save();

            if (isset($request->images)) {
                $image_data = [];
                // New gallery images go after the existing ones.
                $nextOrder = (int) $product->images()->max('sort_order');
                foreach ($request->images as $image) {
                    $extention = $image->getClientOriginalExtension();
                    $image_name = Str::slug($request->name) . date('-Y-m-d-h-i-s-') . rand(999, 9999) . '.' . $extention;
                    $img = Image::make($image);
                    $destinationPath = public_path('products/');
                    
                    $img->resize(1200, 1200, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $img->save($destinationPath . $image_name);
                    $image_data[] = ['image' => $image_name, 'sort_order' => ++$nextOrder];
                }
                if (!empty($image_data)) {
                    $product->images()->createMany($image_data);
                }
            }

            if ($request->type === 'single') {
                // আগে এখানে সব ভ্যারিয়েশন delete করে নতুন একটা বানানো হতো। ফলে শুধু দাম
                // বদলে সেভ করলেও ভ্যারিয়েশনের ID বদলে যেত, আর পুরনো order_details-এর
                // variation_id মৃত ID-তে গিয়ে ঠেকত — কোনো এরর ছাড়াই অর্ডারের হিসাব নষ্ট।
                // এখন প্রথম ভ্যারিয়েশনটা ধরে রেখে সেটাই আপডেট করা হয়, বাড়তিগুলো সরে।
                $existingVars = $product->variations->sortBy('id')->values();
                $singleVar    = $existingVars->first();

                foreach ($existingVars->slice(1) as $extraVar) {
                    if ($extraVar->image) {
                        deleteImage('products', $extraVar->image);
                    }
                    ProductStock::where('product_id', $product->id)
                        ->where('variation_id', $extraVar->id)
                        ->delete();
                    $extraVar->delete();
                }

                $singleQty = (int) ($request->pro_quantity ?? 0);
                $qty = max(0, $singleQty); // FIX: 0 = সত্যিই স্টক নেই

                if (!$singleVar) {
                    $singleVar = new Variation();
                    $singleVar->product_id = $product->id;
                } elseif ($singleVar->image) {
                    // single-এ ভ্যারিয়েশন ছবি দেখানো হয় না, তাই পুরনোটা ছেড়ে দেওয়া
                    deleteImage('products', $singleVar->image);
                    $singleVar->image = null;
                }

                // --- সিঙ্গেল প্রোডাক্টের ক্ষেত্রে size_id এবং color_id null করা হলো ---
                $singleVar->size_id = null;
                $singleVar->color_id = null;
                $singleVar->purchase_price = $request->purchase_prices ?? 0;
                $singleVar->price = $request->sell_price ?? 0;
                $singleVar->after_discount_price = $request->after_discount;
                $singleVar->stock_quantity = $qty; // Force save
                $singleVar->save();
                // ----------------------------------------------------------------------

                $this->setVariationStock($product->id, $singleVar->id, $qty);
                $this->cleanOrphanStocks($product->id);

                DB::commit();
                $this->removeMainImageFiles($oldMainImage);
                return response()->json(['status' => true, 'msg' => 'Product Is Updated !!', 'url' => route('admin.products.index')]);
            }

            $variationIdsReq = (array) $request->variation_id;
            $sizeIds = $this->variantOptionIds($request->size_id);
            $colorIds = $this->variantOptionIds($request->color_id);
            $purchasePrices = (array) $request->purchase_price;
            $prices = (array) $request->price;
            $afterPrices = (array) $request->after_discount_price;
            $qtys = (array) $request->quantity;
            $varImages = $request->file('variation_image'); 

            if (!empty($variationIdsReq)) {
                $delete_variations = Variation::where('product_id', $product->id)
                    ->whereNotIn('id', array_filter($variationIdsReq))
                    ->get();

                foreach ($delete_variations as $dv) {
                    if ($dv->image) {
                        deleteImage('products', $dv->image);
                    }
                    ProductStock::where('product_id', $product->id)->where('variation_id', $dv->id)->delete();
                    $dv->delete();
                }
            }

            $count = max(count($sizeIds), count($colorIds), count($prices));

            for ($i = 0; $i < $count; $i++) {
                if (empty($sizeIds[$i]) && empty($colorIds[$i]) && empty($prices[$i])) continue;

                $vId = $variationIdsReq[$i] ?? null;
                
                $vQty = (int) ($qtys[$i] ?? 0);
                $qty = max(0, $vQty); // FIX: 0 = সত্যিই স্টক নেই
                
                $currentSize = !empty($sizeIds[$i]) ? $sizeIds[$i] : null;
                $currentColor = !empty($colorIds[$i]) ? $colorIds[$i] : null;

                $varImageName = null;
                if (isset($varImages[$i]) && \App\Utils\Util::isSafeImageUpload($varImages[$i])) {
                    $vImage = $varImages[$i];
                    $ext = strtolower($vImage->getClientOriginalExtension());
                    $varImageName = 'var-' . time() . '-' . rand(1000, 9999) . '.' . $ext;
                    $vImage->move(public_path('products/'), $varImageName);
                }

                if ($vId) {
                    $variable = Variation::where('id', $vId)->where('product_id', $product->id)->first();
                    if (!$variable) continue;

                    if ($varImageName && $variable->image) {
                        deleteImage('products', $variable->image);
                    }

                    $variable->size_id = $currentSize;
                    $variable->color_id = $currentColor;
                    if ($varImageName) {
                        $variable->image = $varImageName; 
                    }
                    $variable->purchase_price = $purchasePrices[$i] ?? 0;
                    $variable->price = $prices[$i] ?? 0;
                    $variable->after_discount_price = $afterPrices[$i] ?? null;
                    $variable->stock_quantity = $qty; // Force save
                    $variable->save();

                    $this->setVariationStock($product->id, $variable->id, $qty);

                } else {
                    $newVar = new Variation();
                    $newVar->product_id = $product->id;
                    $newVar->size_id = $currentSize;
                    $newVar->color_id = $currentColor;
                    $newVar->image = $varImageName; 
                    $newVar->purchase_price = $purchasePrices[$i] ?? 0;
                    $newVar->price = $prices[$i] ?? 0;
                    $newVar->after_discount_price = $afterPrices[$i] ?? null;
                    $newVar->stock_quantity = $qty; // Force save
                    $newVar->save();

                    $this->setVariationStock($product->id, $newVar->id, $qty);
                }
            }

            $this->cleanOrphanStocks($product->id);

            DB::commit();
            $this->removeMainImageFiles($oldMainImage);
            return response()->json(['status' => true, 'msg' => 'Product Is Updated !!', 'url' => route('admin.products.index')]);
        } catch (\Exception $e) {
            DB::rollback();
            $this->removeMainImageFiles($newMainImage);
            return response()->json(['status' => false, 'msg' => $e->getMessage()]);
        }
    }

    private function removeMainImageFiles($fileName)
    {
        if (empty($fileName)) {
            return;
        }
        // Another product (e.g. an older duplicate) might still point to the same file.
        if (Product::where('image', $fileName)->exists()) {
            return;
        }
        deleteImage('products', $fileName);
        deleteImage('thumb_products', $fileName);
    }

    public function updatePriority(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->priority = $request->input('priority');
            $product->save();
            return response()->json(['message' => 'Priority updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function cat_wise_product(Request $request)
    {
        // The list filters (category, search, stock) now all live in index().
        return $this->index();
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('product.delete')) {
            abort(403, 'unauthorized');
        }

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($id);
            deleteImage('products', $product->image);
            // store()/update() মূল ছবির পাশাপাশি thumb_products/-এও একটা 500px কপি রাখে।
            // এতদিন delete-এ শুধু products/ মুছত, ফলে থাম্বনেইলগুলো অনাথ হয়ে জমতে থাকত।
            deleteImage('thumb_products', $product->image);
            deleteImage('products', $product->optional_image);

            if ($product->images()->count()) {
                foreach ($product->images as $image) {
                    deleteImage('products', $image->image);
                }
                $product->images()->delete();
            }

            foreach ($product->variations as $dv) {
                if ($dv->image) {
                    deleteImage('products', $dv->image);
                }
                ProductStock::where('product_id', $product->id)->where('variation_id', $dv->id)->delete();
                $dv->delete();
            }

            $product->categories()->detach();
            $product->delete();
            DB::commit();
            return response()->json(['status' => true, 'msg' => 'Product Is Deleted !!']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function deleteImage($id)
    {
        $item = ProductImage::findOrFail($id);
        deleteImage('products', $item->image);
        $item->delete();
        return back();
    }

    /**
     * Delete several gallery images of one product at once (admin edit page).
     */
    public function bulkDeleteImages(Request $request, $productId)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $images = ProductImage::where('product_id', $productId)
            ->whereIn('id', $request->ids)
            ->get();

        foreach ($images as $image) {
            deleteImage('products', $image->image);
            $image->delete();
        }

        return response()->json(['status' => true, 'deleted' => $images->pluck('id')]);
    }

    /**
     * Save gallery order after drag & drop. `ids` is the full list in display order.
     */
    public function sortImages(Request $request, $productId)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        DB::transaction(function () use ($request, $productId) {
            foreach (array_values($request->ids) as $position => $id) {
                ProductImage::where('product_id', $productId)
                    ->where('id', $id)
                    ->update(['sort_order' => $position + 1]);
            }
        });

        return response()->json(['status' => true]);
    }

    public function fileUpload(Request $request)
    {
        if ($request->hasFile('upload')) {
            $file = $request->file('upload');

            // Only real image files are allowed — blocks .php/.phtml/.html
            // uploads that could otherwise run as code on the server.
            $allowedExt  = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
            $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/svg+xml'];
            $extension   = strtolower($file->getClientOriginalExtension());

            if (!in_array($extension, $allowedExt, true) || !in_array($file->getMimeType(), $allowedMime, true)) {
                return response()->json(['error' => 'Only image files (jpg, png, gif, webp) are allowed.'], 422);
            }

            $originName = $file->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            // Sanitize the base name so nothing weird lands on disk.
            $fileName = preg_replace('/[^A-Za-z0-9_-]/', '_', $fileName);
            $fileName = $fileName . '_' . time() . '.' . $extension;

            $request->file('upload')->move(public_path('ck-images'), $fileName);
            $url = asset('ck-images/' . $fileName);

            if ($request->has('CKEditorFuncNum')) {
                $CKEditorFuncNum = $request->input('CKEditorFuncNum');
                $msg = 'Image uploaded successfully';
                $response = "<script>window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url', '$msg')</script>";
                @header('Content-type: text/html; charset=utf-8');
                echo $response;
            } else {
                return response()->json(['url' => $url]);
            }
        } else {
            return response()->json(['error' => 'No file uploaded'], 400);
        }
    }

    public function recommendedUpdate()
    {
        $status = (request('is_recommended') == 1) ? 1 : null;
        DB::table('products')->whereIn('id', request('product_ids'))->update(['is_recommended' => $status]);
        return response()->json(['status' => true, 'msg' => 'Product Status Updated !!']);
    }

    /**
     * Marks the selected products as checkout add-ons — the little "you may
     * also need this" strip on the checkout page. Kept separate from
     * is_recommended (home page) because the two lists rarely overlap: the
     * home page shows hero products, checkout wants cheap impulse buys.
     */
    public function checkoutPickUpdate()
    {
        $status = (request('is_checkout_pick') == 1) ? 1 : 0;

        DB::table('products')
            ->whereIn('id', (array) request('product_ids'))
            ->update(['is_checkout_pick' => $status]);

        return response()->json(['status' => true, 'msg' => 'Checkout recommendation updated!']);
    }

    public function showUpdate()
    {
        $status = (request('status') == 1) ? 1 : 0;
        DB::table('products')->whereIn('id', request('product_ids'))->update(['status' => $status]);
        return response()->json(['status' => true, 'msg' => 'Product Status Updated !!']);
    }

    public function forYouUpdate(Request $request)
    {
        if (empty($request->product_ids)) {
            return response()->json(['status' => false, 'msg' => 'Please select at least one product.']);
        }
        $status = $request->is_for_you ?? 0;
        Product::whereIn('id', $request->product_ids)->update(['is_for_you' => $status]);
        $msg = $status == 1 ? 'Added to For You successfully!' : 'Removed from For You successfully!';
        return response()->json(['status' => true, 'msg' => $msg]);
    }

    public function stockWarningIndex()
    {
        if (!auth()->user()->can('product.view')) {
            abort(403, 'unauthorized');
        }

        $information = \App\Models\Information::orderBy('id', 'desc')->first();
        $threshold = $information->stock_warning_limit ?? 5; 

        $items = ProductStock::with(['product', 'variation.size', 'variation.color'])
                    ->where('quantity', '<=', $threshold)
                    ->orderBy('quantity', 'asc')
                    ->paginate(30);

        return view('backend.products.stock_warning', compact('items', 'threshold'));
   }

   public function togglePopular(Request $request) 
   {
       $product = \App\Models\Product::find($request->id);
       if($product){
           $product->is_popular = $request->is_popular;
           $product->save();
           return response()->json(['status' => true, 'msg' => 'Product popular status updated!']);
       }
       return response()->json(['status' => false, 'msg' => 'Product not found!']);
   }
}