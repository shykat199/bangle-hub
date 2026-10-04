<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

use App\Models\Combo;
use App\Models\Product;
use App\Models\Category;
use App\Models\Type;
use App\Models\Size;
use App\Models\Information;
use App\Models\LandingPage;
use App\Models\DeliveryCharge;
use App\Models\BanglaText;
use App\Models\Page;

use App\Models\Order;
use App\Models\User;
use App\Models\Variation;
use App\Models\LandingPagePackage;

use App\Facades\FacebookConversion;
use App\Facades\TikTokConversion;

class ProductController extends Controller
{
    /* ===========================
     * Helpers
     * =========================== */

    private function sizeLabelColumn(): string
    {
        if (Schema::hasColumn('sizes', 'name'))  return 'name';
        if (Schema::hasColumn('sizes', 'size'))  return 'size';
        if (Schema::hasColumn('sizes', 'title')) return 'title';
        if (Schema::hasColumn('sizes', 'label')) return 'label';
        if (Schema::hasColumn('sizes', 'value')) return 'value';
        return 'id';
    }

    private function orderKey(Request $request): string
    {
        $shorting = trim((string)$request->get('shorting', ''));
        $sort     = trim((string)$request->get('sort', ''));

        $key = $shorting !== '' ? $shorting : ($sort !== '' ? $sort : 'latest');

        if ($key === 'desc') return 'latest';
        if ($key === 'asc')  return 'oldest';

        return $key ?: 'latest';
    }

    private function applySort($query, string $key)
    {
        if ($key === 'latest')        return $query->orderBy('products.id', 'desc');
        if ($key === 'oldest')        return $query->orderBy('products.id', 'asc');
        if ($key === 'name')          return $query->orderBy('products.name', 'asc');
        
        if ($key === 'price_low') {
            return $query->orderByRaw('IF(products.after_discount > 0, products.after_discount, products.sell_price) ASC');
        }
        
        if ($key === 'price_high') {
            return $query->orderByRaw('IF(products.after_discount > 0, products.after_discount, products.sell_price) DESC');
        }

        return $query->orderByRaw('IF(products.priority IS NULL, 1, 0), products.priority ASC')
                     ->orderBy('products.id', 'desc');
    }

    private function applyCommonFilters($query, array $brand_ids, array $size_ids, string $q, $min_price, $max_price)
    {
        if ($q !== '') {
            // A search that matches a category name also brings every product of that category.
            $matchedCatIds = Category::where('name', 'like', "%{$q}%")->pluck('id')->all();

            $query->where(function ($row) use ($q, $matchedCatIds) {
                $row->where('products.name', 'like', "%{$q}%")
                    ->orWhere('products.description', 'like', "%{$q}%");

                if (!empty($matchedCatIds)) {
                    $row->orWhere(fn ($w) => $w->inCategory($matchedCatIds));
                }
            });
        }

        if ($min_price !== null && $max_price !== null && $min_price !== '' && $max_price !== '') {
            $query->whereBetween('products.sell_price', [(float)$min_price, (float)$max_price]);
        }

        if (!empty($brand_ids)) {
            $query->whereIn('products.type_id', $brand_ids);
        }

        if (!empty($size_ids)) {
            $query->whereHas('variation', function ($v) use ($size_ids) {
                $v->whereIn('size_id', $size_ids);
            });
        }

        return $query;
    }

    /* ===========================
     * Index (All products)
     * =========================== */

    public function index(Request $request)
    {
        $q         = trim((string)$request->input('q', ''));
        $type_id   = (array)$request->input('brand_id', []);
        $cat_id    = (array)$request->input('cat_id', []);
        $size_id   = (array)$request->input('size_id', []);
        $min_price = $request->input('min_price');
        $max_price = $request->input('max_price');

        $orderKey = $this->orderKey($request);
        $sort     = $orderKey;

        // প্রতিটা কার্ড resolveStock() ডাকে, যেটা variations আর product_stocks দুটোই দেখে।
        // eager-load না থাকায় ১২টা কার্ডের পেজে কয়েক ডজন বাড়তি কোয়েরি হতো (N+1)।
        $query = Product::query()
            ->with(['variation', 'category:id,name,url', 'variations.stocks', 'images'])
            ->select('products.*')
            ->where('products.status', 1);

        if (!empty($cat_id))  $query->inCategory($cat_id);
        if (!empty($type_id)) $query->whereIn('products.type_id', $type_id);

        $this->applyCommonFilters($query, $type_id, $size_id, $q, $min_price, $max_price);
        $this->applySort($query, $orderKey);

        $items = $query->paginate(32)->withQueryString();

        if ($request->ajax()) {
            return view('frontend.products.partials.category_products', compact('items'))->render();
        }

        $types    = Type::orderBy('name')->get();
        $cats     = Category::whereNull('parent_id')->get();

        $sizeCol = $this->sizeLabelColumn();

        $sizeIds = DB::table('variations')
            ->join('products', 'products.id', '=', 'variations.product_id')
            ->where('products.status', 1)
            ->whereNotNull('variations.size_id')
            ->distinct()
            ->pluck('variations.size_id')
            ->toArray();

        $hasSizes = !empty($sizeIds);
        $sizes    = $hasSizes ? Size::whereIn('id', $sizeIds)->orderBy($sizeCol)->get() : collect([]);

        $minDb = Product::where('status', 1)->min('sell_price') ?? 0;
        $maxDb = Product::where('status', 1)->max('sell_price') ?? 0;

        return view('frontend.products.index', compact(
            'items', 'cats', 'sizes', 'types', 'sizeCol',
            'hasSizes', 'minDb', 'maxDb', 'sort'
        ));
    }

    public function comboProducts()
    {
        $items = Combo::with('product')->paginate(32);
        return view('frontend.products.combo', compact('items'));
    }

    /* ===========================
     * Product Popup (Checkout modal)
     * =========================== */

    public function popup($id)
    {
        $singleProduct = Product::with([
            'images',
            'category',
            'variations',
            'reviews' => function ($q) {
                $q->where('status', 1);
            }
        ])->findOrFail($id);

        $charges     = DeliveryCharge::orderBy('id', 'asc')->get();
        $info        = Information::first();
        $bangla_text = BanglaText::first();
        $data        = getProductInfo($singleProduct);

        $aboutUs        = Page::where('page', 'about')->first();
        $termsCondition = Page::where('page', 'term')->first();

        // Quick-view counts as a product view, so it gets its own deduplicated
        // ViewContent pair rather than a browser-only event with no server match.
        $popupEventId = "QV_" . $singleProduct->id . "_" . now()->format('YmdHis') . "_" . uniqid();

        $popupPrice = ($singleProduct->after_discount && $singleProduct->after_discount > 0)
            ? $singleProduct->after_discount
            : $singleProduct->sell_price;

        try {
            FacebookConversion::sendViewContent([
                'product_id'       => $singleProduct->id,
                'product_name'     => $singleProduct->name,
                'value'            => (float) $popupPrice,
                'currency'         => 'BDT',
                'content_type'     => 'product',
                'content_category' => $singleProduct->category->name ?? 'Unknown Category',
                'event_time'       => now()->timestamp,
                'action_source'    => 'website',
            ], $popupEventId);
        } catch (\Throwable $e) {
            Log::error('Facebook CAPI (QuickView) Error: ' . $e->getMessage());
        }

        try {
            TikTokConversion::sendViewContent([
                'product_id'   => $singleProduct->id,
                'product_name' => $singleProduct->name,
                'value'        => (float) $popupPrice,
                'currency'     => 'BDT',
            ], $popupEventId);
        } catch (\Throwable $e) {
            Log::error('TikTok Events API (QuickView) Error: ' . $e->getMessage());
        }

        return view('frontend.products.partials.product_popup', compact(
            'singleProduct',
            'charges',
            'info',
            'bangla_text',
            'data',
            'aboutUs',
            'termsCondition',
            'popupEventId'
        ));
    }

    /* ===========================
     * Product Details
     * =========================== */

    public function show(Product $product)
    {
        $routeParam = request()->route('product');
        if (is_numeric($routeParam) && (int)$routeParam === (int)$product->id && !empty($product->slug)) {
            return redirect()->route('front.products.show', $product->slug, 301);
        }

        $recent_product = session()->get('recent_product', []);
        if (!in_array($product->id, $recent_product)) {
            $recent_product[] = $product->id;
            session()->put('recent_product', $recent_product);
        }

        // status = 1 here too: a disabled product vanished from every listing
        // but its direct URL still rendered, added to cart and took orders,
        // so switching a product off did not actually stop sales.
        $singleProduct = Product::with([
            'sizes',
            'reviews' => function ($q) { $q->where('status', 1); }
        ])->where('status', 1)->findOrFail($product->id);

        // Must be unique per request: the old "SV_{id}_{ymdhi}" collided for every
        // visitor viewing the same product in the same minute, so Meta collapsed them
        // into a single event. The blade echoes this exact id back to fbq/ttq.
        $eventId   = "SV_" . $singleProduct->id . "_" . now()->format('YmdHis') . "_" . uniqid();
        $ttEventId = $eventId . "_TT";

        $finalPrice = ($singleProduct->after_discount && $singleProduct->after_discount > 0)
            ? $singleProduct->after_discount
            : $singleProduct->sell_price;

        try {
            $userData = [
                'em'          => [hash('sha256', strtolower(trim(auth()->user()->email ?? '')))],
                'ph'          => [hash('sha256', preg_replace('/\D/', '', auth()->user()->phone_number ?? ''))],
                'fn'          => [hash('sha256', strtolower(trim(auth()->user()->name ?? '')))],
            ];
            if (!empty(auth()->user()?->id)) {
                $userData['external_id'] = [hash('sha256', (string) auth()->user()->id)];
            }

            FacebookConversion::sendViewContent([
                'product_id'       => $singleProduct->id,
                'product_name'     => $singleProduct->name,
                'value'            => $finalPrice,
                'currency'         => 'BDT',
                'content_type'     => 'product',
                'content_category' => $singleProduct->category->name ?? 'Unknown Category',
                'event_time'       => now()->timestamp,
                'action_source'    => 'website',
                'user_data'        => $userData
            ], $eventId);

        } catch (\Exception $e) {
            Log::error('Facebook CAPI Error: ' . $e->getMessage());
        }

        try {
            $ttUser = [];
            if (!empty(auth()->user()?->email)) $ttUser['email'] = TikTokConversion::hash(auth()->user()->email);
            $ttPhone = auth()->user()->phone_number ?? null;
            if (!empty($ttPhone)) $ttUser['phone'] = TikTokConversion::hashPhone($ttPhone);

            TikTokConversion::sendViewContent([
                'product_id'   => $singleProduct->id,
                'product_name' => $singleProduct->name,
                'value'        => $finalPrice,
                'currency'     => 'BDT',
            ], $ttEventId, $ttUser);
        } catch (\Throwable $e) {
            Log::error('TikTok Events API (ViewContent) Error: ' . $e->getMessage());
        }

        $products = Product::where('id', '!=', $product->id)
            ->where('category_id', $singleProduct->category_id)
            ->where('status', 1)
            ->orderByRaw('IF(priority IS NULL, 1, 0), priority ASC')
            ->take(6)
            ->get();

        $charges = DeliveryCharge::all();

        return view('frontend.products.show', compact('singleProduct', 'products', 'charges', 'eventId', 'ttEventId'));
    }

    public function relativeProduct(Product $product)
    {
        $product = Product::with('sizes', 'sizes.stocks')->findOrFail($product->id);

        $products = Product::with('variation')
            ->select('products.*')
            ->where('products.category_id', $product->category_id)
            ->whereNotIn('products.id', [$product->id])
            ->where('products.status', 1)
            ->orderByRaw('IF(priority IS NULL, 1, 0), priority ASC')
            ->take(12)
            ->get();

        $view = view('frontend.products.partials.relative_product', compact('products'))->render();
        return response()->json(['success' => true, 'html' => $view]);
    }

    /* ===========================
     * Home blocks
     * =========================== */

    public function trendingProduct()
    {
        $info           = Information::first();
        $newarrival_num = $info->newarrival_num;

        $products = Product::with('variation')
            ->whereNull('products.discount_type')
            ->where('products.status', 1)
            ->select('products.*')
            ->orderByRaw('IF(priority IS NULL, 1, 0), priority ASC')
            ->latest()
            ->take($newarrival_num)
            ->get();

        $view = view('frontend.products.partials.trending_product', compact('products'))->render();
        return response()->json(['success' => true, 'html' => $view]);
    }

    public function hotdealProduct()
    {
        $info         = Information::first();
        $discount_num = $info->discount_num;

        $products = Product::with('variation')
            ->whereNotNull('products.discount_type')
            ->where('products.status', 1)
            ->select('products.*')
            ->orderByRaw('IF(priority IS NULL, 1, 0), priority ASC')
            ->take($discount_num)
            ->get();

        $view = view('frontend.products.partials.hotdeal_product', compact('products'))->render();
        return response()->json(['success' => true, 'html' => $view]);
    }

    public function recommendedProduct()
    {
        $info      = Information::first();
        $recom_num = $info->recommend_num;

        $products = Product::with('variation')
            ->where('products.status', 1)
            ->where('products.is_recommended', 1)
            ->select('products.*')
            ->orderByRaw('IF(priority IS NULL, 1, 0), priority ASC')
            ->take($recom_num)
            ->get();

        $view = view('frontend.products.partials.recommended_product', compact('products'))->render();
        return response()->json(['success' => true, 'html' => $view]);
    }

    public function discountProduct(Request $request)
    {
        if ($request->ajax()) {
            $items = Product::with('variation')
                ->whereNotNull('products.discount_type')
                ->where('products.status', 1)
                ->select('products.*')
                ->orderBy('products.id', 'desc')
                ->paginate(32);

            $view = view('frontend.products.partials.discount', compact('items'))->render();
            return response()->json(['success' => true, 'html' => $view]);
        }

        return view('frontend.products.discount');
    }

    /* ===========================
     * Category / Subcategory pages
     * =========================== */

    public function subCategories($slug)
    {
        $cat  = Category::where('url', $slug)->first();
        $q    = Category::whereNotNull('parent_id');
        if ($cat) $q->where('parent_id', $cat->id);
        $subs = $q->get();

        return view('frontend.sub_categories', compact('subs'));
    }

    /**
     * Root-level category URL (/{slug}): a top-level category lists by
     * category_id, a sub-category by sub_category_id.
     */
    public function categoryPage(Request $request, $slug)
    {
        $cat = Category::where('url', $slug)->firstOrFail();

        return $cat->parent_id
            ? $this->subsubCategories($request, $slug)
            : $this->subCategories1($request, $slug);
    }

    public function subCategories1(Request $request, $slug)
    {
        $cat = Category::where('url', $slug)->firstOrFail();

        $q         = trim((string)$request->get('q', ''));
        $brand_ids = (array)$request->get('brand_id', []);
        $size_ids  = (array)$request->get('size_id', []);
        $min_price = $request->get('min_price');
        $max_price = $request->get('max_price');

        $orderKey = $this->orderKey($request);
        $sort     = $orderKey;

        $query = Product::with(['variation', 'category:id,name,url', 'variations.stocks', 'images'])
            ->select('products.*')
            ->inCategory([$cat->id])
            ->where('products.status', 1);

        $this->applyCommonFilters($query, $brand_ids, $size_ids, $q, $min_price, $max_price);
        $this->applySort($query, $orderKey);

        $items = $query->paginate(32)->withQueryString();

        if ($request->ajax()) {
            return view('frontend.products.partials.category_products', compact('items'))->render();
        }

        $minDb = Product::inCategory([$cat->id])->min('sell_price') ?? 0;
        $maxDb = Product::inCategory([$cat->id])->max('sell_price') ?? 0;

        $types = Type::orderBy('name')->get();
        $cats  = Category::whereNull('parent_id')->get();

        $sizeIds = Variation::whereIn('product_id', Product::inCategory([$cat->id])->where('status', 1)->select('id'))
            ->whereNotNull('size_id')
            ->distinct()
            ->pluck('size_id')
            ->toArray();

        $hasSizes = !empty($sizeIds);
        $sizeCol  = $this->sizeLabelColumn();
        $sizes    = $hasSizes ? Size::whereIn('id', $sizeIds)->orderBy($sizeCol)->get() : collect([]);

        return view('frontend.products.another_index', compact(
            'items', 'types', 'cats', 'sizes', 'cat',
            'minDb', 'maxDb', 'hasSizes', 'sizeCol', 'sort'
        ));
    }

    public function subsubCategories(Request $request, $slug)
    {
        $s_cat = Category::where('url', $slug)->firstOrFail();

        $q         = trim((string)$request->get('q', ''));
        $brand_ids = (array)$request->get('brand_id', []);
        $size_ids  = (array)$request->get('size_id', []);
        $min_price = $request->get('min_price');
        $max_price = $request->get('max_price');

        $orderKey = $this->orderKey($request);
        $sort     = $orderKey;

        $query = Product::with(['variation', 'images'])
            ->select('products.*')
            ->inCategory([$s_cat->id])
            ->where('products.status', 1);

        $this->applyCommonFilters($query, $brand_ids, $size_ids, $q, $min_price, $max_price);
        $this->applySort($query, $orderKey);

        $items = $query->paginate(32)->withQueryString();

        if ($request->ajax()) {
            return view('frontend.products.partials.category_products', compact('items'))->render();
        }

        $minDb = Product::inCategory([$s_cat->id])->min('sell_price') ?? 0;
        $maxDb = Product::inCategory([$s_cat->id])->max('sell_price') ?? 0;

        $types = Type::orderBy('name')->get();
        $cats  = Category::whereNull('parent_id')->get();

        $sizeIds = Variation::whereIn('product_id', Product::inCategory([$s_cat->id])->where('status', 1)->select('id'))
            ->whereNotNull('size_id')
            ->distinct()
            ->pluck('size_id')
            ->toArray();

        $hasSizes = !empty($sizeIds);
        $sizeCol  = $this->sizeLabelColumn();
        $sizes    = $hasSizes ? Size::whereIn('id', $sizeIds)->orderBy($sizeCol)->get() : collect([]);

        return view('frontend.products.another_index', [
            'items'    => $items,
            'types'    => $types,
            'cats'     => $cats,
            'sizes'    => $sizes,
            'cat'      => $s_cat,
            'minDb'    => $minDb,
            'maxDb'    => $maxDb,
            'hasSizes' => $hasSizes,
            'sizeCol'  => $sizeCol,
            'sort'     => $sort
        ]);
    }

    /* ===========================
     * Others
     * =========================== */

    public function categories()
    {
        $category_id = request('category_id');

        $cats = Category::whereNull('parent_id')->get();
        $q = Category::whereNotNull('parent_id');

        if (!empty($category_id)) $q->where('parent_id', $category_id);
        $subs = $q->get();

        return view('frontend.categories', compact('cats', 'subs'));
    }

    public function free_shipping()
    {
        $items = Product::with('variation')
            ->where('products.is_free_shipping', 1)
            ->select('products.*')
            ->orderBy('products.id', 'desc')
            ->paginate(32);

        return view('frontend.products.free_shipping_products', compact('items'));
    }

    public function get_variation_price(Request $request)
    {
        $data = Product::find($request->product_id);

        return response()->json([
            'success'         => true,
            'discount_amount' => (int)($data->dicount_amount ?? 0),
            'discount_type'   => $data->discount_type ?? null
        ]);
    }

    public function brands()
    {
        $items = Type::orderBy('name')->get();
        return view('frontend.brands', compact('items'));
    }

    /**
     * অ্যাডমিন চাইলে id-র বদলে নিজের slug URL-এ বসাতে পারেন। সংখ্যা দিলে সেটা
     * id হিসেবে, না হলে slug হিসেবে খোঁজা হয় — পুরনো id-ভিত্তিক লিংকও তাই
     * অক্ষত থাকে।
     */
    private function findLandingPageByIdOrSlug($idOrSlug, array $with = [])
    {
        $query = LandingPage::query();
        if (!empty($with)) {
            $query->with($with);
        }

        return ctype_digit((string) $idOrSlug)
            ? $query->find($idOrSlug)
            : $query->where('slug', $idOrSlug)->first();
    }

    public function landing_page($id)
    {
        $ln_pg   = $this->findLandingPageByIdOrSlug($id, ['images', 'packages']);
        if(!$ln_pg) abort(404);
        $title   = $ln_pg->title1;
        $charges = DeliveryCharge::whereNotNull('status')->get();
        $product = $ln_pg->product_id ? Product::with('category')->find($ln_pg->product_id) : null;
        [$lpEventBase, $lpEventIdVC, $lpEventIdIC] = $this->sendLandingViewEvents($ln_pg, $product, 'LP1');
        return view('backend.landing_pages.land_page', compact('ln_pg', 'charges', 'title', 'product', 'lpEventBase', 'lpEventIdVC', 'lpEventIdIC'));
    }

    public function landing_pages_two($id)
    {
        $ln_pg   = $this->findLandingPageByIdOrSlug($id, ['images', 'packages']);
        if(!$ln_pg) abort(404);
        $title   = $ln_pg->title1;
        $charges = DeliveryCharge::whereNotNull('status')->get();
        $product = $ln_pg->product_id ? Product::with('category')->find($ln_pg->product_id) : null;
        [$lpEventBase, $lpEventIdVC, $lpEventIdIC] = $this->sendLandingViewEvents($ln_pg, $product, 'LP2');
        return view('backend.landing_pages.land_page_two', compact('ln_pg', 'charges', 'title', 'product', 'lpEventBase', 'lpEventIdVC', 'lpEventIdIC'));
    }

    /**
     * Fire the browser-matched ViewContent + InitiateCheckout pair on both platforms
     * and hand the ids back so the blade can echo the exact same ones.
     */
    private function sendLandingViewEvents($ln_pg, $product, string $prefix): array
    {
        if (!$product) {
            return [null, null, null];
        }

        $base = "{$prefix}_{$product->id}_" . now()->format('YmdHis') . '_' . uniqid();
        $vc   = $base . '_VC';
        $ic   = $base . '_IC';

        $finalPrice = $this->landingDisplayPrice($ln_pg, $product);
        $category   = $product->category->name ?? 'Landing Page';

        $userData = [];
        $authUser = auth()->user();
        if (!empty($authUser?->email))        $userData['em'] = [hash('sha256', strtolower(trim($authUser->email)))];
        if (!empty($authUser?->phone_number)) $userData['ph'] = [hash('sha256', preg_replace('/\D/', '', $authUser->phone_number))];

        try {
            FacebookConversion::sendViewContent([
                'product_id'       => $product->id,
                'product_name'     => $product->name,
                'value'            => (float) $finalPrice,
                'currency'         => 'BDT',
                'content_category' => $category,
                'event_time'       => now()->timestamp,
                'action_source'    => 'website',
            ], $vc, $userData);

            FacebookConversion::sendInitiateCheckout([
                'product_id'    => $product->id,
                'value'         => (float) $finalPrice,
                'currency'      => 'BDT',
                'num_items'     => 1,
                'event_time'    => now()->timestamp,
                'action_source' => 'website',
            ], $ic, $userData);
        } catch (\Throwable $e) {
            Log::error("Facebook CAPI ({$prefix} View) Error: " . $e->getMessage());
        }

        try {
            $ttUser  = [];
            if (!empty($authUser?->email)) $ttUser['email'] = TikTokConversion::hash($authUser->email);
            $ttPhone = $authUser->mobile ?? $authUser->phone_number ?? null;
            if (!empty($ttPhone)) $ttUser['phone'] = TikTokConversion::hashPhone($ttPhone);

            TikTokConversion::sendViewContent([
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'value'        => (float) $finalPrice,
                'currency'     => 'BDT',
            ], $vc, $ttUser);

            TikTokConversion::sendInitiateCheckout([
                'value'       => (float) $finalPrice,
                'currency'    => 'BDT',
                'content_ids' => [$product->id],
                'contents'    => [[
                    'id'         => $product->id,
                    'name'       => $product->name,
                    'quantity'   => 1,
                    'item_price' => (float) $finalPrice,
                ]],
            ], $ic, $ttUser);
        } catch (\Throwable $e) {
            Log::error("TikTok Events API ({$prefix} View) Error: " . $e->getMessage());
        }

        return [$base, $vc, $ic];
    }

    public function landing_page_three($id)
    {
        $ln_pg = $this->findLandingPageByIdOrSlug($id, ['images', 'review_images']);
        if(!$ln_pg) abort(404);

        $title = $ln_pg->title1 ?? '';
        $charges = DeliveryCharge::whereNotNull('status')->get();
        $product = $ln_pg->product_id ? Product::with(['variations.size', 'variations.color', 'variations.stocks', 'category'])->find($ln_pg->product_id) : null;

        $lpEventBase = null;
        if ($product && class_exists(\App\Facades\FacebookConversion::class)) {
            try {
                $finalPrice = $this->landingDisplayPrice($ln_pg, $product);
                $base = $lpEventBase = "LP_{$product->id}_" . now()->format('YmdHis') . '_' . uniqid();
                
                FacebookConversion::sendViewContent([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'value' => (float)$finalPrice,
                    'currency' => 'BDT',
                    'content_category' => $product->category->name ?? 'Landing Page',
                    'event_time' => now()->timestamp,
                    'action_source' => 'website',
                ], $base . "_VC");

                FacebookConversion::sendInitiateCheckout([
                    'product_id'    => $product->id,
                    'value'         => (float)$finalPrice,
                    'currency'      => 'BDT',
                    'num_items'     => 1,
                    'event_time'    => now()->timestamp,
                    'action_source' => 'website',
                ], $base . "_IC");

                $ttUser  = [];
                $ttUsr   = auth()->user();
                if (!empty($ttUsr?->email)) $ttUser['email'] = TikTokConversion::hash($ttUsr->email);
                $ttPhone = $ttUsr->mobile ?? $ttUsr->phone_number ?? null;
                if (!empty($ttPhone)) $ttUser['phone'] = TikTokConversion::hashPhone($ttPhone);

                TikTokConversion::sendViewContent([
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'value'        => (float)$finalPrice,
                    'currency'     => 'BDT',
                ], $base . "_VC", $ttUser);

                TikTokConversion::sendInitiateCheckout([
                    'value'       => (float)$finalPrice,
                    'currency'    => 'BDT',
                    'content_ids' => [$product->id],
                    'contents'    => [[
                        'id'         => $product->id,
                        'name'       => $product->name,
                        'quantity'   => 1,
                        'item_price' => (float)$finalPrice,
                    ]],
                ], $base . "_IC", $ttUser);
            } catch (\Throwable $e) {
                Log::error('Landing CAPI (LP3/LP4/LP5 View) Error: ' . $e->getMessage());
            }
        }

        $matrix = [];
        $sizes  = collect();
        $colors = collect();

        if ($product) {
            foreach ($product->variations as $v) {
                $stock = $v->stocks->sum('quantity');
                $key = ($v->size_id ?? 0) . '_' . ($v->color_id ?? 0);
                $price = $v->after_discount_price ?: $v->price;
                
                $matrix[$key] = [
                    'variation_id' => $v->id,
                    'price' => (float)$price,
                    'stock' => (int)$stock
                ];

                if ($v->size) $sizes->push(['id' => $v->size->id, 'name' => $v->size->name]);
                if ($v->color) $colors->push(['id' => $v->color->id, 'name' => $v->color->name, 'code' => $v->color->code]);
            }
        }
        $sizes = $sizes->unique('id')->values();
        $colors = $colors->unique('id')->values();

        return view('frontend.landing_pages.land_page_three', compact('ln_pg', 'charges', 'title', 'product', 'matrix', 'sizes', 'colors', 'lpEventBase'));
    }

    public function landing_page_four($id)
    {
        $ln_pg = $this->findLandingPageByIdOrSlug($id, ['images', 'review_images']);
        if(!$ln_pg) abort(404);

        $title = $ln_pg->title1 ?? '';
        $charges = DeliveryCharge::whereNotNull('status')->get();
        $product = $ln_pg->product_id ? Product::with(['variations.size', 'variations.color', 'variations.stocks', 'category'])->find($ln_pg->product_id) : null;

        $lpEventBase = null;
        if ($product && class_exists(\App\Facades\FacebookConversion::class)) {
            try {
                $finalPrice = $this->landingDisplayPrice($ln_pg, $product);
                $base = $lpEventBase = "LP_{$product->id}_" . now()->format('YmdHis') . '_' . uniqid();
                
                FacebookConversion::sendViewContent([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'value' => (float)$finalPrice,
                    'currency' => 'BDT',
                    'content_category' => $product->category->name ?? 'Landing Page',
                    'event_time' => now()->timestamp,
                    'action_source' => 'website',
                ], $base . "_VC");

                FacebookConversion::sendInitiateCheckout([
                    'product_id'    => $product->id,
                    'value'         => (float)$finalPrice,
                    'currency'      => 'BDT',
                    'num_items'     => 1,
                    'event_time'    => now()->timestamp,
                    'action_source' => 'website',
                ], $base . "_IC");

                $ttUser  = [];
                $ttUsr   = auth()->user();
                if (!empty($ttUsr?->email)) $ttUser['email'] = TikTokConversion::hash($ttUsr->email);
                $ttPhone = $ttUsr->mobile ?? $ttUsr->phone_number ?? null;
                if (!empty($ttPhone)) $ttUser['phone'] = TikTokConversion::hashPhone($ttPhone);

                TikTokConversion::sendViewContent([
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'value'        => (float)$finalPrice,
                    'currency'     => 'BDT',
                ], $base . "_VC", $ttUser);

                TikTokConversion::sendInitiateCheckout([
                    'value'       => (float)$finalPrice,
                    'currency'    => 'BDT',
                    'content_ids' => [$product->id],
                    'contents'    => [[
                        'id'         => $product->id,
                        'name'       => $product->name,
                        'quantity'   => 1,
                        'item_price' => (float)$finalPrice,
                    ]],
                ], $base . "_IC", $ttUser);
            } catch (\Throwable $e) {
                Log::error('Landing CAPI (LP3/LP4/LP5 View) Error: ' . $e->getMessage());
            }
        }

        $matrix = [];
        $sizes  = collect();
        $colors = collect();

        if ($product) {
            foreach ($product->variations as $v) {
                $stock = $v->stocks->sum('quantity');
                $key = ($v->size_id ?? 0) . '_' . ($v->color_id ?? 0);
                $price = $v->after_discount_price ?: $v->price;
                
                $matrix[$key] = [
                    'variation_id' => $v->id,
                    'price' => (float)$price,
                    'stock' => (int)$stock
                ];

                if ($v->size) $sizes->push(['id' => $v->size->id, 'name' => $v->size->name]);
                if ($v->color) $colors->push(['id' => $v->color->id, 'name' => $v->color->name, 'code' => $v->color->code]);
            }
        }
        $sizes = $sizes->unique('id')->values();
        $colors = $colors->unique('id')->values();

        return view('frontend.landing_pages.land_page_four', compact('ln_pg', 'charges', 'title', 'product', 'matrix', 'sizes', 'colors', 'lpEventBase'));
    }

    public function landing_page_five($id)
    {
        $ln_pg = $this->findLandingPageByIdOrSlug($id, ['images', 'review_images']);
        if(!$ln_pg) abort(404);

        $title = $ln_pg->title1 ?? '';
        $charges = DeliveryCharge::whereNotNull('status')->get();
        $product = $ln_pg->product_id ? Product::with(['variations.size', 'variations.color', 'variations.stocks', 'category'])->find($ln_pg->product_id) : null;

        $lpEventBase = null;
        if ($product && class_exists(\App\Facades\FacebookConversion::class)) {
            try {
                $finalPrice = $this->landingDisplayPrice($ln_pg, $product);
                $base = $lpEventBase = "LP_{$product->id}_" . now()->format('YmdHis') . '_' . uniqid();
                
                FacebookConversion::sendViewContent([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'value' => (float)$finalPrice,
                    'currency' => 'BDT',
                    'content_category' => $product->category->name ?? 'Landing Page',
                    'event_time' => now()->timestamp,
                    'action_source' => 'website',
                ], $base . "_VC");

                FacebookConversion::sendInitiateCheckout([
                    'product_id'    => $product->id,
                    'value'         => (float)$finalPrice,
                    'currency'      => 'BDT',
                    'num_items'     => 1,
                    'event_time'    => now()->timestamp,
                    'action_source' => 'website',
                ], $base . "_IC");

                $ttUser  = [];
                $ttUsr   = auth()->user();
                if (!empty($ttUsr?->email)) $ttUser['email'] = TikTokConversion::hash($ttUsr->email);
                $ttPhone = $ttUsr->mobile ?? $ttUsr->phone_number ?? null;
                if (!empty($ttPhone)) $ttUser['phone'] = TikTokConversion::hashPhone($ttPhone);

                TikTokConversion::sendViewContent([
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'value'        => (float)$finalPrice,
                    'currency'     => 'BDT',
                ], $base . "_VC", $ttUser);

                TikTokConversion::sendInitiateCheckout([
                    'value'       => (float)$finalPrice,
                    'currency'    => 'BDT',
                    'content_ids' => [$product->id],
                    'contents'    => [[
                        'id'         => $product->id,
                        'name'       => $product->name,
                        'quantity'   => 1,
                        'item_price' => (float)$finalPrice,
                    ]],
                ], $base . "_IC", $ttUser);
            } catch (\Throwable $e) {
                Log::error('Landing CAPI (LP3/LP4/LP5 View) Error: ' . $e->getMessage());
            }
        }

        $matrix = [];
        $sizes  = collect();
        $colors = collect();

        if ($product) {
            foreach ($product->variations as $v) {
                $stock = $v->stocks->sum('quantity');
                $key = ($v->size_id ?? 0) . '_' . ($v->color_id ?? 0);
                $price = $v->after_discount_price ?: $v->price;
                
                $matrix[$key] = [
                    'variation_id' => $v->id,
                    'price' => (float)$price,
                    'stock' => (int)$stock
                ];

                if ($v->size) $sizes->push(['id' => $v->size->id, 'name' => $v->size->name]);
                if ($v->color) $colors->push(['id' => $v->color->id, 'name' => $v->color->name, 'code' => $v->color->code]);
            }
        }
        $sizes = $sizes->unique('id')->values();
        $colors = $colors->unique('id')->values();

        return view('backend.landing_pages.land_page_five', compact('ln_pg', 'charges', 'title', 'product', 'matrix', 'sizes', 'colors', 'lpEventBase'));
    }

    public function landing_page_six($id)
    {
        $ln_pg = $this->findLandingPageByIdOrSlug($id, ['images', 'review_images', 'packages']);
        if(!$ln_pg) abort(404);

        $title = $ln_pg->title1 ?? '';
        $charges = DeliveryCharge::whereNotNull('status')->get();
        $product = $ln_pg->product_id ? Product::with(['variations.size', 'variations.color', 'variations.stocks', 'category'])->find($ln_pg->product_id) : null;

        $lpEventBase = null;
        $lpEventIdVC = null;
        $lpEventIdIC = null;
        if ($product) {
            try {
                $finalPrice = $this->landingDisplayPrice($ln_pg, $product);

                $base        = "LP_{$product->id}_" . now()->format('YmdHis') . '_' . uniqid();
                $eventIdVC = $base . "_VC";
                $eventIdIC = $base . "_IC";
                $lpEventBase = $base;
                $lpEventIdVC = $eventIdVC;
                $lpEventIdIC = $eventIdIC;

                $userData = [];
                $authUser = auth()->user();

                if (!empty($authUser?->email)) $userData['em'] = [hash('sha256', strtolower(trim($authUser->email)))];
                if (!empty($authUser?->phone_number)) $userData['ph'] = [hash('sha256', preg_replace('/\D/', '', $authUser->phone_number))];
                if (!empty($authUser?->id)) $userData['external_id'] = [hash('sha256', (string)$authUser->id)];

                FacebookConversion::sendViewContent([
                    'product_id'        => $product->id,
                    'product_name'      => $product->name,
                    'value'             => (float)$finalPrice,
                    'currency'          => 'BDT',
                    'content_category' => $product->category->name ?? 'Landing Page',
                    'event_time'        => now()->timestamp,
                    'action_source'     => 'website',
                ], $eventIdVC, $userData);

                FacebookConversion::sendInitiateCheckout([
                    'product_id'    => $product->id,
                    'value'         => (float)$finalPrice,
                    'currency'      => 'BDT',
                    'num_items'     => 1,
                    'event_time'    => now()->timestamp,
                    'action_source' => 'website',
                ], $eventIdIC, $userData);

                // Same ids as the Meta events — the blade echoes them to ttq too,
                // so TikTok can deduplicate browser against server.
                try {
                    $ttUser  = [];
                    $ttUsr   = auth()->user();
                    if (!empty($ttUsr?->email)) $ttUser['email'] = TikTokConversion::hash($ttUsr->email);
                    $ttPhone = $ttUsr->mobile ?? $ttUsr->phone_number ?? null;
                    if (!empty($ttPhone)) $ttUser['phone'] = TikTokConversion::hashPhone($ttPhone);

                    TikTokConversion::sendViewContent([
                        'product_id'   => $product->id,
                        'product_name' => $product->name,
                        'value'        => (float) $finalPrice,
                        'currency'     => 'BDT',
                    ], $eventIdVC, $ttUser);

                    TikTokConversion::sendInitiateCheckout([
                        'value'       => (float) $finalPrice,
                        'currency'    => 'BDT',
                        'content_ids' => [$product->id],
                        'contents'    => [[
                            'id'         => $product->id,
                            'name'       => $product->name,
                            'quantity'   => 1,
                            'item_price' => (float) $finalPrice,
                        ]],
                    ], $eventIdIC, $ttUser);
                } catch (\Throwable $e) {
                    Log::error('TikTok Events API (Landing View) Error: ' . $e->getMessage());
                }

            } catch (\Throwable $e) {
                Log::error('Facebook CAPI (LP View) Error: ' . $e->getMessage());
            }
        }

        $matrix = [];
        $sizes  = collect();
        $colors = collect();

        if ($product) {
            foreach ($product->variations as $v) {
                $stock = $v->stocks->sum('quantity');
                $key = ($v->size_id ?? 0) . '_' . ($v->color_id ?? 0);
                $price = $v->after_discount_price ?: $v->price;
                
                $matrix[$key] = [
                    'variation_id' => $v->id,
                    'price' => (float)$price,
                    'stock' => (int)$stock
                ];

                if ($v->size) $sizes->push(['id' => $v->size->id, 'name' => $v->size->name]);
                if ($v->color) $colors->push(['id' => $v->color->id, 'name' => $v->color->name, 'code' => $v->color->code]);
            }
        }
        $sizes = $sizes->unique('id')->values();
        $colors = $colors->unique('id')->values();

        return view('frontend.landing_pages.landing_page_six', compact('ln_pg', 'charges', 'title', 'product', 'matrix', 'sizes', 'colors', 'lpEventBase', 'lpEventIdVC', 'lpEventIdIC'));
    }

    public function landing_page_seven($id)
    {
        $ln_pg = $this->findLandingPageByIdOrSlug($id, ['images', 'review_images', 'packages']);
        if(!$ln_pg) abort(404);

        $title = $ln_pg->title1 ?? '';
        $charges = DeliveryCharge::whereNotNull('status')->get();
        $product = $ln_pg->product_id ? Product::with(['variations.size', 'variations.color', 'variations.stocks', 'category'])->find($ln_pg->product_id) : null;

        $lpEventBase = null;
        $lpEventIdVC = null;
        $lpEventIdIC = null;
        if ($product) {
            try {
                $finalPrice = $this->landingDisplayPrice($ln_pg, $product);

                $base        = "LP7_{$product->id}_" . now()->format('YmdHis') . '_' . uniqid();
                $eventIdVC = $base . "_VC";
                $eventIdIC = $base . "_IC";
                $lpEventBase = $base;
                $lpEventIdVC = $eventIdVC;
                $lpEventIdIC = $eventIdIC;

                $userData = [];
                $authUser = auth()->user();

                if (!empty($authUser?->email)) $userData['em'] = [hash('sha256', strtolower(trim($authUser->email)))];
                if (!empty($authUser?->phone_number)) $userData['ph'] = [hash('sha256', preg_replace('/\D/', '', $authUser->phone_number))];
                if (!empty($authUser?->id)) $userData['external_id'] = [hash('sha256', (string)$authUser->id)];

                FacebookConversion::sendViewContent([
                    'product_id'        => $product->id,
                    'product_name'      => $product->name,
                    'value'             => (float)$finalPrice,
                    'currency'          => 'BDT',
                    'content_category' => $product->category->name ?? 'Landing Page',
                    'event_time'        => now()->timestamp,
                    'action_source'     => 'website',
                ], $eventIdVC, $userData);

                FacebookConversion::sendInitiateCheckout([
                    'product_id'    => $product->id,
                    'value'         => (float)$finalPrice,
                    'currency'      => 'BDT',
                    'num_items'     => 1,
                    'event_time'    => now()->timestamp,
                    'action_source' => 'website',
                ], $eventIdIC, $userData);

                // Same ids as the Meta events — the blade echoes them to ttq too,
                // so TikTok can deduplicate browser against server.
                try {
                    $ttUser  = [];
                    $ttUsr   = auth()->user();
                    if (!empty($ttUsr?->email)) $ttUser['email'] = TikTokConversion::hash($ttUsr->email);
                    $ttPhone = $ttUsr->mobile ?? $ttUsr->phone_number ?? null;
                    if (!empty($ttPhone)) $ttUser['phone'] = TikTokConversion::hashPhone($ttPhone);

                    TikTokConversion::sendViewContent([
                        'product_id'   => $product->id,
                        'product_name' => $product->name,
                        'value'        => (float) $finalPrice,
                        'currency'     => 'BDT',
                    ], $eventIdVC, $ttUser);

                    TikTokConversion::sendInitiateCheckout([
                        'value'       => (float) $finalPrice,
                        'currency'    => 'BDT',
                        'content_ids' => [$product->id],
                        'contents'    => [[
                            'id'         => $product->id,
                            'name'       => $product->name,
                            'quantity'   => 1,
                            'item_price' => (float) $finalPrice,
                        ]],
                    ], $eventIdIC, $ttUser);
                } catch (\Throwable $e) {
                    Log::error('TikTok Events API (Landing View) Error: ' . $e->getMessage());
                }

            } catch (\Throwable $e) {
                Log::error('Facebook CAPI (LP7 View) Error: ' . $e->getMessage());
            }
        }

        return view('frontend.landing_pages.landing_page_seven', compact('ln_pg', 'charges', 'title', 'product', 'lpEventBase', 'lpEventIdVC', 'lpEventIdIC'));
    }

    public function landing_page_eight($id)
    {
        $ln_pg = $this->findLandingPageByIdOrSlug($id, ['images', 'review_images', 'packages']);
        if(!$ln_pg) abort(404);

        $title = $ln_pg->title1 ?? '';
        $charges = DeliveryCharge::whereNotNull('status')->get();
        $product = $ln_pg->product_id ? Product::with(['variations.size', 'variations.color', 'variations.stocks', 'category'])->find($ln_pg->product_id) : null;

        $lpEventBase = null;
        $lpEventIdVC = null;
        $lpEventIdIC = null;
        if ($product) {
            try {
                $finalPrice = $this->landingDisplayPrice($ln_pg, $product);

                $base        = "LP8_{$product->id}_" . now()->format('YmdHis') . '_' . uniqid();
                $eventIdVC = $base . "_VC";
                $eventIdIC = $base . "_IC";
                $lpEventBase = $base;
                $lpEventIdVC = $eventIdVC;
                $lpEventIdIC = $eventIdIC;

                $userData = [];
                $authUser = auth()->user();

                if (!empty($authUser?->email)) $userData['em'] = [hash('sha256', strtolower(trim($authUser->email)))];
                if (!empty($authUser?->phone_number)) $userData['ph'] = [hash('sha256', preg_replace('/\D/', '', $authUser->phone_number))];
                if (!empty($authUser?->id)) $userData['external_id'] = [hash('sha256', (string)$authUser->id)];

                FacebookConversion::sendViewContent([
                    'product_id'        => $product->id,
                    'product_name'      => $product->name,
                    'value'             => (float)$finalPrice,
                    'currency'          => 'BDT',
                    'content_category' => $product->category->name ?? 'Landing Page',
                    'event_time'        => now()->timestamp,
                    'action_source'     => 'website',
                ], $eventIdVC, $userData);

                FacebookConversion::sendInitiateCheckout([
                    'product_id'    => $product->id,
                    'value'         => (float)$finalPrice,
                    'currency'      => 'BDT',
                    'num_items'     => 1,
                    'event_time'    => now()->timestamp,
                    'action_source' => 'website',
                ], $eventIdIC, $userData);

                // Same ids as the Meta events — the blade echoes them to ttq too,
                // so TikTok can deduplicate browser against server.
                try {
                    $ttUser  = [];
                    $ttUsr   = auth()->user();
                    if (!empty($ttUsr?->email)) $ttUser['email'] = TikTokConversion::hash($ttUsr->email);
                    $ttPhone = $ttUsr->mobile ?? $ttUsr->phone_number ?? null;
                    if (!empty($ttPhone)) $ttUser['phone'] = TikTokConversion::hashPhone($ttPhone);

                    TikTokConversion::sendViewContent([
                        'product_id'   => $product->id,
                        'product_name' => $product->name,
                        'value'        => (float) $finalPrice,
                        'currency'     => 'BDT',
                    ], $eventIdVC, $ttUser);

                    TikTokConversion::sendInitiateCheckout([
                        'value'       => (float) $finalPrice,
                        'currency'    => 'BDT',
                        'content_ids' => [$product->id],
                        'contents'    => [[
                            'id'         => $product->id,
                            'name'       => $product->name,
                            'quantity'   => 1,
                            'item_price' => (float) $finalPrice,
                        ]],
                    ], $eventIdIC, $ttUser);
                } catch (\Throwable $e) {
                    Log::error('TikTok Events API (Landing View) Error: ' . $e->getMessage());
                }

            } catch (\Throwable $e) {
                Log::error('Facebook CAPI (LP8 View) Error: ' . $e->getMessage());
            }
        }

        $matrix = [];
        $sizes  = collect();
        $colors = collect();

        if ($product) {
            foreach ($product->variations as $v) {
                $stock = $v->stocks->sum('quantity');
                $key = ($v->size_id ?? 0) . '_' . ($v->color_id ?? 0);
                $price = $v->after_discount_price ?: $v->price;
                
                $matrix[$key] = [
                    'variation_id' => $v->id,
                    'price' => (float)$price,
                    'stock' => (int)$stock
                ];

                if ($v->size) $sizes->push(['id' => $v->size->id, 'name' => $v->size->name]);
                if ($v->color) $colors->push(['id' => $v->color->id, 'name' => $v->color->name, 'code' => $v->color->code]);
            }
        }
        $sizes = $sizes->unique('id')->values();
        $colors = $colors->unique('id')->values();

        return view('frontend.landing_pages.landing_page_eight', compact('ln_pg', 'charges', 'title', 'product', 'matrix', 'sizes', 'colors', 'lpEventBase', 'lpEventIdVC', 'lpEventIdIC'));
    }

    public function landing_page_nine($id)
    {
        $ln_pg = $this->findLandingPageByIdOrSlug($id, ['images', 'review_images', 'packages']);
        if(!$ln_pg) abort(404);

        $title = $ln_pg->title1 ?? '';
        $charges = DeliveryCharge::whereNotNull('status')->get();
        $product = $ln_pg->product_id ? Product::with(['variations.size', 'variations.color', 'variations.stocks', 'category'])->find($ln_pg->product_id) : null;

        $lpEventBase = null;
        $lpEventIdVC = null;
        $lpEventIdIC = null;
        if ($product) {
            try {
                $finalPrice = $this->landingDisplayPrice($ln_pg, $product);

                $base       = "LP9_{$product->id}_" . now()->format('YmdHis') . '_' . uniqid();
                $eventIdVC  = $base . "_VC";
                $eventIdIC  = $base . "_IC";
                $lpEventBase = $base;
                $lpEventIdVC = $eventIdVC;
                $lpEventIdIC = $eventIdIC;

                $userData = [];
                $authUser = auth()->user();

                if (!empty($authUser?->email)) $userData['em'] = [hash('sha256', strtolower(trim($authUser->email)))];
                if (!empty($authUser?->phone_number)) $userData['ph'] = [hash('sha256', preg_replace('/\D/', '', $authUser->phone_number))];
                if (!empty($authUser?->id)) $userData['external_id'] = [hash('sha256', (string)$authUser->id)];

                FacebookConversion::sendViewContent([
                    'product_id'       => $product->id,
                    'product_name'     => $product->name,
                    'value'            => (float)$finalPrice,
                    'currency'         => 'BDT',
                    'content_category' => $product->category->name ?? 'Landing Page',
                    'event_time'       => now()->timestamp,
                    'action_source'    => 'website',
                ], $eventIdVC, $userData);

                FacebookConversion::sendInitiateCheckout([
                    'product_id'    => $product->id,
                    'value'         => (float)$finalPrice,
                    'currency'      => 'BDT',
                    'num_items'     => 1,
                    'event_time'    => now()->timestamp,
                    'action_source' => 'website',
                ], $eventIdIC, $userData);

                // Same ids as the Meta events — the blade echoes them to ttq too,
                // so TikTok can deduplicate browser against server.
                try {
                    $ttUser  = [];
                    $ttUsr   = auth()->user();
                    if (!empty($ttUsr?->email)) $ttUser['email'] = TikTokConversion::hash($ttUsr->email);
                    $ttPhone = $ttUsr->mobile ?? $ttUsr->phone_number ?? null;
                    if (!empty($ttPhone)) $ttUser['phone'] = TikTokConversion::hashPhone($ttPhone);

                    TikTokConversion::sendViewContent([
                        'product_id'   => $product->id,
                        'product_name' => $product->name,
                        'value'        => (float) $finalPrice,
                        'currency'     => 'BDT',
                    ], $eventIdVC, $ttUser);

                    TikTokConversion::sendInitiateCheckout([
                        'value'       => (float) $finalPrice,
                        'currency'    => 'BDT',
                        'content_ids' => [$product->id],
                        'contents'    => [[
                            'id'         => $product->id,
                            'name'       => $product->name,
                            'quantity'   => 1,
                            'item_price' => (float) $finalPrice,
                        ]],
                    ], $eventIdIC, $ttUser);
                } catch (\Throwable $e) {
                    Log::error('TikTok Events API (Landing View) Error: ' . $e->getMessage());
                }

            } catch (\Throwable $e) {
                Log::error('Facebook CAPI (LP9 View) Error: ' . $e->getMessage());
            }
        }

        $matrix = [];
        $sizes  = collect();
        $colors = collect();

        if ($product) {
            foreach ($product->variations as $v) {
                $stock = $v->stocks->sum('quantity');
                $key   = ($v->size_id ?? 0) . '_' . ($v->color_id ?? 0);
                $price = $v->after_discount_price ?: $v->price;

                $matrix[$key] = [
                    'variation_id' => $v->id,
                    'price'        => (float)$price,
                    'stock'        => (int)$stock
                ];

                if ($v->size)  $sizes->push(['id' => $v->size->id, 'name' => $v->size->name]);
                if ($v->color) $colors->push(['id' => $v->color->id, 'name' => $v->color->name, 'code' => $v->color->code]);
            }
        }
        $sizes  = $sizes->unique('id')->values();
        $colors = $colors->unique('id')->values();

        // Same figure the CAPI events above were sent with, so the browser
        // pixel cannot disagree with the server on a packaged landing page.
        $lpDisplayPrice = $this->landingDisplayPrice($ln_pg, $product);

        return view('frontend.landing_pages.landing_page_nine', compact('ln_pg', 'charges', 'title', 'product', 'matrix', 'sizes', 'colors', 'lpEventBase', 'lpEventIdVC', 'lpEventIdIC', 'lpDisplayPrice'));
    }

    public function landing_page_ten($id)      { return $this->renderLandingPage($id, 'ten',      'LP10'); }
    public function landing_page_eleven($id)   { return $this->renderLandingPage($id, 'eleven',   'LP11'); }
    public function landing_page_twelve($id)   { return $this->renderLandingPage($id, 'twelve',   'LP12'); }
    public function landing_page_thirteen($id) { return $this->renderLandingPage($id, 'thirteen', 'LP13'); }
    // 14/15/16 render `landing_page_*` like every other suffix — $viewName is never
    // passed, so the `land_page_fourteen/fifteen/sixteen` blades are unreachable.
    public function landing_page_fourteen($id) { return $this->renderLandingPage($id, 'fourteen', 'LP14'); }
    public function landing_page_fifteen($id)  { return $this->renderLandingPage($id, 'fifteen',  'LP15'); }
    public function landing_page_sixteen($id)  { return $this->renderLandingPage($id, 'sixteen',  'LP16'); }

    /**
     * The price a landing page actually shows by default.
     *
     * The blades price from the default package, then the page's own new_price,
     * and only then fall back to the product. The server used to always send the
     * product price, so browser and server reported different `value` under the
     * same event id and Meta/TikTok kept whichever arrived first.
     */
    private function landingDisplayPrice($ln_pg, $product): float
    {
        $packages = $ln_pg->packages ?? collect();
        if ($packages instanceof \Illuminate\Support\Collection && $packages->isNotEmpty()) {
            $default = $packages->firstWhere('is_default', 1) ?: $packages->first();
            if ($default && (float) $default->price > 0) {
                return (float) $default->price;
            }
        }

        if (!empty($ln_pg->new_price) && (float) $ln_pg->new_price > 0) {
            return (float) $ln_pg->new_price;
        }

        if (!$product) {
            return 0.0;
        }

        return (float) (($product->after_discount && $product->after_discount > 0)
            ? $product->after_discount
            : $product->sell_price);
    }

    private function renderLandingPage($id, $suffix, $eventPrefix, $viewName = null)
    {
        $ln_pg = $this->findLandingPageByIdOrSlug($id, ['images', 'review_images', 'packages']);
        if(!$ln_pg) abort(404);

        $title   = $ln_pg->title1 ?? '';
        $charges = DeliveryCharge::whereNotNull('status')->get();
        $product = $ln_pg->product_id
            ? Product::with(['variations.size', 'variations.color', 'variations.stocks', 'category'])->find($ln_pg->product_id)
            : null;

        $lpEventBase = $product
            ? "{$eventPrefix}_{$product->id}_" . now()->format('YmdHis') . '_' . substr(bin2hex(random_bytes(3)), 0, 6)
            : "{$eventPrefix}_0_" . now()->format('YmdHis');
        $lpEventIdVC = $lpEventBase . '_VC';
        $lpEventIdIC = $lpEventBase . '_IC';

        if ($product) {
            try {
                $finalPrice = $this->landingDisplayPrice($ln_pg, $product);

                $eventIdVC = $lpEventIdVC;
                $eventIdIC = $lpEventIdIC;

                $userData = [];
                $authUser = auth()->user();
                if (!empty($authUser?->email))        $userData['em'] = [hash('sha256', strtolower(trim($authUser->email)))];
                if (!empty($authUser?->phone_number)) $userData['ph'] = [hash('sha256', preg_replace('/\D/', '', $authUser->phone_number))];
                if (!empty($authUser?->id))           $userData['external_id'] = [hash('sha256', (string)$authUser->id)];

                FacebookConversion::sendViewContent([
                    'product_id'       => $product->id,
                    'product_name'     => $product->name,
                    'value'            => (float)$finalPrice,
                    'currency'         => 'BDT',
                    'content_category' => $product->category->name ?? 'Landing Page',
                    'event_time'       => now()->timestamp,
                    'action_source'    => 'website',
                ], $eventIdVC, $userData);

                FacebookConversion::sendInitiateCheckout([
                    'product_id'    => $product->id,
                    'value'         => (float)$finalPrice,
                    'currency'      => 'BDT',
                    'num_items'     => 1,
                    'event_time'    => now()->timestamp,
                    'action_source' => 'website',
                ], $eventIdIC, $userData);
            } catch (\Throwable $e) {
                Log::error("Facebook CAPI ({$eventPrefix} View) Error: " . $e->getMessage());
            }

            try {
                $ttUser = [];
                $authUser = auth()->user();
                if (!empty($authUser?->email)) $ttUser['email'] = TikTokConversion::hash($authUser->email);
                $ttPhone = $authUser->mobile ?? $authUser->phone_number ?? null;
                if (!empty($ttPhone)) $ttUser['phone'] = TikTokConversion::hashPhone($ttPhone);

                TikTokConversion::sendViewContent([
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'value'        => (float)$finalPrice,
                    'currency'     => 'BDT',
                ], $eventIdVC, $ttUser);

                TikTokConversion::sendInitiateCheckout([
                    'value'        => (float)$finalPrice,
                    'currency'     => 'BDT',
                    'content_ids'  => [$product->id],
                ], $eventIdIC, $ttUser);
            } catch (\Throwable $e) {
                Log::error("TikTok CAPI ({$eventPrefix} View) Error: " . $e->getMessage());
            }
        }

        $matrix = [];
        $sizes  = collect();
        $colors = collect();

        if ($product) {
            foreach ($product->variations as $v) {
                $stock = $v->stocks->sum('quantity');
                $key   = ($v->size_id ?? 0) . '_' . ($v->color_id ?? 0);
                $price = $v->after_discount_price ?: $v->price;
                $matrix[$key] = ['variation_id' => $v->id, 'price' => (float)$price, 'stock' => (int)$stock];
                if ($v->size)  $sizes->push(['id' => $v->size->id, 'name' => $v->size->name]);
                if ($v->color) $colors->push(['id' => $v->color->id, 'name' => $v->color->name, 'code' => $v->color->code]);
            }
        }
        $sizes  = $sizes->unique('id')->values();
        $colors = $colors->unique('id')->values();

        // The price the CAPI events were sent with. Designs 9-13 used to work
        // their own price out from the product / new_price alone, ignoring the
        // package list — so as soon as a landing page had packages the browser
        // pixel and the server reported different values under the SAME event
        // id, and Meta kept whichever arrived first. Handing the blade the
        // server's own figure keeps the two sides identical by construction.
        $lpDisplayPrice = $this->landingDisplayPrice($ln_pg, $product);

        $blade = $viewName ?: ('landing_page_' . $suffix);
        return view('frontend.landing_pages.' . $blade,
            compact('ln_pg', 'charges', 'title', 'product', 'matrix', 'sizes', 'colors',
                    'lpEventBase', 'lpEventIdVC', 'lpEventIdIC', 'lpDisplayPrice'));
    }

    public function storelandData(Request $request)
    {
        $data = $request->validate([
            'mobile' => 'required|digits:11',
            'first_name' => 'required',
            'shipping_address' => 'required',
            'delivery_charge_id' => 'required|numeric',
            'prd_id' => 'required|integer',
            'purchase_event_id' => 'nullable|string',
            'variation_id' => 'nullable|integer',
            'quantity' => 'nullable|integer|min:1',
            'selected_package_id' => 'nullable', 
            'selected_items' => 'nullable|json'  
        ]);

        if (empty(auth()->user()?->id)) {
            $user = User::create([
                'first_name' => $request->first_name,
                'mobile' => $request->mobile,
                'shipping_address' => $request->shipping_address,
                'note' => $request->input('note'),
            ]);
            $data['user_id'] = $user->id;
        } else {
            $data['user_id'] = auth()->user()->id;
        }

        $product = Product::findOrFail($request->prd_id);
        $charge = DeliveryCharge::find($request->delivery_charge_id);
        $chargeAmount = $charge ? (float)$charge->amount : 0;
        
        $invoiceNo = generateUniqueInvoiceNo();
        $admins = DB::table('model_has_roles')->where('role_id', 8)->pluck('model_id')->toArray();
        $assignUserId = (!empty($admins)) ? $admins[array_rand($admins)] : 1;

        DB::beginTransaction();
        try {
            if ($request->filled('selected_items')) {
                
                $selectedItems = json_decode($request->selected_items, true);
                
                if (empty($selectedItems)) {
                    throw new \Exception("Please select at least one item.");
                }

                $totalSubtotal = 0;
                
                $order = Order::create([
                    'mobile' => $request->mobile,
                    'first_name' => $request->first_name,
                    'shipping_address' => $request->shipping_address,
                    'note' => $request->input('note'),
                    'delivery_charge_id' => $request->delivery_charge_id,
                    'user_id' => $data['user_id'],
                    'assign_user_id' => $assignUserId,
                    'date' => date('Y-m-d'),
                    'invoice_no' => $invoiceNo,
                    'amount' => 0, 
                    'shipping_charge' => $chargeAmount,
                    'final_amount' => 0, 
                ]);

                foreach ($selectedItems as $item) {
                    $qty = max((int)$item['quantity'], 1);
                    
                    $varId = null;
                    if ($item['id'] !== 'default') {
                        $varId = (int)$item['id'];
                    }

                    $unitPrice = (float)$item['price'];
                    $totalSubtotal += ($unitPrice * $qty);

                    $order->details()->create([
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'variation_id' => $varId,
                    ]);
                }

                $finalAmount = $totalSubtotal + $chargeAmount;
                $order->update([
                    'amount' => $totalSubtotal,
                    'final_amount' => $finalAmount
                ]);

            } 
            else {
                $v_id = $request->variation_id ?: Variation::where('product_id', $product->id)->value('id');
                $selectedVariation = $v_id ? Variation::with('stocks')->find($v_id) : null;
                
                $qty = max((int)$request->quantity, 1);
                $unitPrice = $selectedVariation ? ($selectedVariation->after_discount_price ?: $selectedVariation->price) : ($product->after_discount ?: $product->sell_price);

                if ($request->has('selected_package_id') && $request->selected_package_id != null) {
                    $package = LandingPagePackage::find($request->selected_package_id);
                    if ($package) {
                        $qty = $package->qty;
                        $unitPrice = $package->price / $qty; 
                    }
                }

                $subTotal = $unitPrice * $qty;
                $finalAmount = $subTotal + $chargeAmount;

                $order = Order::create([
                    'mobile' => $request->mobile,
                    'first_name' => $request->first_name,
                    'shipping_address' => $request->shipping_address,
                    'note' => $request->input('note'),
                    'delivery_charge_id' => $request->delivery_charge_id,
                    'user_id' => $data['user_id'],
                    'assign_user_id' => $assignUserId,
                    'date' => date('Y-m-d'),
                    'invoice_no' => $invoiceNo,
                    'amount' => $subTotal,
                    'shipping_charge' => $chargeAmount,
                    'final_amount' => $finalAmount,
                ]);

                $order->details()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'variation_id' => $selectedVariation?->id,
                ]);
            }

            DB::commit();

            \App\Jobs\SendOrderConfirmationCall::maybeDispatch($order, '(Landing Page)');


            rememberPlacedOrder($order->id);

            return response()->json([
                'success' => true,
                'msg' => 'Order Created Successfully!',
                'url' => confirmOrderUrl($order),
                'invoice_no' => $invoiceNo
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Landing Page Order Error: " . $e->getMessage());
            return response()->json(['success' => false, 'msg' => 'Order process failed. Try again.']);
        }
    }
}