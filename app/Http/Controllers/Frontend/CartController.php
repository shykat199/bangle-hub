<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Variation;
use App\Models\ProductStock; 
use App\Facades\FacebookConversion;
use App\Facades\TikTokConversion;
use App\Utils\Util;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CartController extends Controller
{
    protected $util;

    public function __construct(Util $util)
    {
        $this->util = $util;
    }

    /**
     * ------------------------
     * Helper: price calculation
     * ------------------------
     * return: [finalPrice, discountPerUnit, basePrice]
     */
    private function calculatePrice(Product $product, ?Variation $variation): array
    {
        if ($variation) {
            $basePrice  = (float) ($variation->price ?? 0);
            
            // Fallback if variation has no price
            if ($basePrice <= 0) {
                $basePrice = (float) ($product->sell_price ?? 0);
            }

            $afterDisc  = (float) ($variation->after_discount_price ?? 0);
            $otherDisc  = (float) ($variation->discount_price ?? 0); // Just in case

            if ($afterDisc > 0 && $afterDisc < $basePrice) {
                $finalPrice = $afterDisc;
            } elseif ($otherDisc > 0 && $otherDisc < $basePrice) {
                $finalPrice = $otherDisc;
            } else {
                $finalPrice = $basePrice;
            }
        } else {
            $basePrice  = (float) ($product->sell_price ?? 0);
            $afterDisc  = (float) ($product->after_discount ?? 0);
            
            if ($afterDisc > 0 && $afterDisc < $basePrice) {
                $finalPrice = $afterDisc;
            } else {
                $finalPrice = $basePrice;
            }
        }

        $discountPerUnit = max(0, $basePrice - $finalPrice);

        return [$finalPrice, $discountPerUnit, $basePrice];
    }

    /**
     * ✅ STOCK HELPER
     */
    private function getAvailableStock(Product $product, int $variationId = 0): int
    {
        // আগে এখানে PHP_INT_MAX ফেরত যেত — অর্থাৎ Manage Stock = No মানে ছিল
        // "আনলিমিটেড স্টক", তাই ০ স্টকের প্রোডাক্টও যত খুশি অর্ডার করা যেত।
        // এখন Manage Stock = No মানে প্রোডাক্ট বন্ধ, তাই ০।
        if (!productStockManaged($product)) {
            return 0;
        }

        $variation = $variationId > 0
            ? Variation::where('id', $variationId)->where('product_id', $product->id)->first()
            : null;

        // স্টক পড়ার নিয়ম একটাই — helpers.php-এর resolveStock()
        return resolveStock($product, $variation);
    }

    public function index()
    {
        $cart = session()->get('cart', []);

        if (request()->ajax()) {
            $segm = request()->segment(1) ?? 'home';
            $view = view('frontend.partials.cart_sidebar', compact('cart','segm'))->render();
            return response()->json(['success'=>true, 'html'=>$view]);
        }

        return view('frontend.cart.index', compact('cart'));
    }

    /**
     * ✅ Core add-to-cart logic
     */
    private function addToCart(Request $request, bool $sendFacebookCapi = false): array
    {
        $request->validate([
            'product_id'   => 'required|integer|min:1',
            'variation_id' => 'nullable|integer',
            'quantity'     => 'nullable|integer|min:1',
            'action_type'  => 'nullable|string',
            'event_id'     => 'nullable|string' // ✅ Event ID Accept korar jonno
        ]);

        $segm = request()->segment(1) ?? 'home';

        $product_id   = (int) $request->product_id;
        $variation_id = (int) ($request->variation_id ?? 0);
        $quantity     = (int) ($request->quantity ?? 1);
        $eventId      = $request->event_id ?? "ATC_" . $product_id . "_" . time(); 

        if ($quantity <= 0) {
            return [
                'ok' => false,
                'payload' => $this->errorResponse($request, 'Please Select Minimum 1 Quantity', 422)
            ];
        }

        $product = Product::with(['category'])->where('status', 1)->find($product_id);
        if (!$product) {
            return [
                'ok' => false,
                'payload' => $this->errorResponse($request, 'Product not found!', 404)
            ];
        }

        /**
         * ✅ Variation strict check
         */
        $variation = null;

        if ($product->type === 'single') {
            if ($variation_id > 0) {
                $variation = Variation::with(['size','color'])
                    ->where('id', $variation_id)
                    ->where('product_id', $product->id)
                    ->first();
            }
            
            if (!$variation) {
                $variation = Variation::with(['size','color'])
                    ->where('product_id', $product->id)
                    ->orderBy('id')
                    ->first();
            }

            if ($variation) {
                $variation_id = (int)$variation->id;
            } else {
                $variation_id = 0;
                $variation = null;
            }

        } else {
            if ($variation_id > 0) {
                $variation = Variation::with(['size','color'])
                    ->where('id', $variation_id)
                    ->where('product_id', $product->id)
                    ->first();

                if (!$variation) {
                    return [
                        'ok' => false,
                        'payload' => $this->errorResponse($request, 'Invalid variation selected!', 422)
                    ];
                }
            } else {
                return [
                    'ok' => false,
                    'payload' => $this->errorResponse($request, 'Please select product options!', 422)
                ];
            }
        }

        // ✅ Price & Discount Calculation
        [$finalPrice, $discountPerUnit, $basePrice] = $this->calculatePrice($product, $variation);

        $cart = session()->get('cart', []);

        // --- FIX: ইউনিক Cart Key তৈরি করা হলো যাতে Overwrite না হয় ---
        $cartKey = $product_id . '_' . $variation_id;

        // Wholesale minimum is per cart line (so per variant). One-click
        // buttons always send quantity 1, so the line is topped up to the
        // minimum instead of being refused.
        $minQty = $product->minOrderQty();
        $raisedToMin = false;
        $existingLineQty = (int) ($cart[$cartKey]['quantity'] ?? 0);
        if ($existingLineQty + $quantity < $minQty) {
            $quantity = $minQty - $existingLineQty;
            $raisedToMin = true;
        }

        // ✅ Stock Check
        $is_stock = (int) ($product->is_stock ?? 0);

        // Manage Stock = No মানে প্রোডাক্ট বন্ধ। আগে এই পুরো শাখাটাই স্কিপ হয়ে যেত,
        // তাই থিমে বাটন লুকানো থাকলেও যে কেউ সরাসরি POST দিয়ে অর্ডার করতে পারত।
        if (!productStockManaged($product)) {
            return [
                'ok' => false,
                'payload' => $this->errorResponse($request, 'This product is currently unavailable.', 422)
            ];
        }

        if ($is_stock === 1) {
            $stockQty = $this->getAvailableStock($product, (int)$variation_id);

            if ($stockQty < $quantity) {
                return [
                    'ok' => false,
                    'payload' => $this->errorResponse($request, 'Stock Not Available!', 422)
                ];
            }
            
            if (isset($cart[$cartKey])) {
                $existingQty = (int)($cart[$cartKey]['quantity'] ?? 0);
                if ($stockQty < ($existingQty + $quantity)) {
                    return [
                        'ok' => false,
                        'payload' => $this->errorResponse($request, 'Stock limit reached! You already have this item in cart.', 422)
                    ];
                }
            }
        }

        // ✅ Add/Update Cart (Using Unique $cartKey)
        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity']       += $quantity;
            $cart[$cartKey]['price']          = $finalPrice;
            $cart[$cartKey]['discount']       = $discountPerUnit;
            $cart[$cartKey]['original_price'] = $basePrice;
            $cart[$cartKey]['variation_id']   = $variation_id;
            $cart[$cartKey]['product_id']     = $product_id;
        } else {
            $cart[$cartKey] = [
                "name"             => $product->name,
                "size"             => $variation && $variation->size ? ($variation->size->title ?? '') : '',
                "color"            => $variation && $variation->color ? ($variation->color->name ?? '') : '',
                "quantity"         => $quantity,
                "price"            => $finalPrice,
                "discount"         => $discountPerUnit,
                "original_price"   => $basePrice,
                "variation_id"     => $variation_id,
                "product_id"       => $product_id,
                "category_name"    => $product->category->name ?? '',
                "purchase_price"   => $product->purchase_prices,
                "image"            => ($variation && !empty($variation->image)) ? $variation->image : $product->image,
                "is_stock"         => $is_stock,
                "is_free_shipping" => $product->is_free_shipping
            ];
        }

        session()->put('cart', $cart);

        // ✅ Facebook CAPI
        if ($sendFacebookCapi) {
            try {
                $dedupeKey = 'capi_atc_' . $eventId;
                if (!session()->has($dedupeKey)) {
                    session()->put($dedupeKey, 1);

                    // Hashed identifiers raise Meta's Event Match Quality. The
                    // TikTok call below already sent them; Meta was getting the
                    // event without them.
                    $fbUser = [];
                    $authUser = auth()->user();
                    if (!empty($authUser?->email))        $fbUser['em'] = [hash('sha256', strtolower(trim($authUser->email)))];
                    if (!empty($authUser?->phone_number)) $fbUser['ph'] = [hash('sha256', preg_replace('/\D/', '', $authUser->phone_number))];

                    FacebookConversion::sendAddToCart([
                        'content_ids' => [$product->id],
                        'value'       => $finalPrice * $quantity,
                        'currency'    => 'BDT',
                        'contents'    => [
                            [
                                'id'         => $product->id,
                                'quantity'   => $quantity,
                                'item_price' => $finalPrice,
                            ]
                        ]
                    ], $eventId, $fbUser);

                    try {
                        $ttUser = [];
                        if (!empty(auth()->user()?->email)) $ttUser['email'] = TikTokConversion::hash(auth()->user()->email);
                        $ttPhone = auth()->user()->phone_number ?? null;
                        if (!empty($ttPhone)) $ttUser['phone'] = TikTokConversion::hashPhone($ttPhone);

                        TikTokConversion::sendAddToCart([
                            'content_ids' => [$product->id],
                            'value'       => $finalPrice * $quantity,
                            'currency'    => 'BDT',
                            'contents'    => [
                                [
                                    'id'         => $product->id,
                                    'name'       => $product->name,
                                    'quantity'   => $quantity,
                                    'item_price' => $finalPrice,
                                ]
                            ]
                        ], $eventId . '_TT', $ttUser);
                    } catch (\Throwable $e) {
                        Log::error('TikTok Events API AddToCart Error: ' . $e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                Log::error('Facebook CAPI AddToCart Error: ' . $e->getMessage());
            }
        }

        $view         = view('frontend.partials.cart_sidebar', compact('cart','segm'))->render();
        $total_item   = function_exists('getTotalCart') ? getTotalCart() : count($cart);
        $total_amount = function_exists('getTotalAmount') ? getTotalAmount() : 0;

        $actionType = $request->action_type ?? 'order';
        $url = ($actionType === 'cart')
            ? ''
            : route('front.checkouts.index');

        return [
            'ok' => true,
            'payload' => [
                'success' => true,
                'msg'     => $raisedToMin
                    ? "Minimum order quantity is {$minQty} — {$minQty} added to cart."
                    : 'Product added to cart successfully!',
                'html'    => $view,
                'item'    => $total_item,
                'amount'  => $total_amount,
                'url'     => $url
            ]
        ];
    }

    private function errorResponse(Request $request, string $msg, int $status = 422)
    {
        if (!$request->ajax() && !$request->expectsJson()) {
            return redirect()->back()->with('error', $msg);
        }
        return response()->json(['success'=>false, 'msg'=>$msg], $status);
    }

    public function storeCart(Request $request)
    {
        $res = $this->addToCart($request, true);

        if (!$res['ok']) {
            return $res['payload'];
        }

        return response()->json($res['payload']);
    }

    public function store(Request $request)
    {
        $res = $this->addToCart($request, false);

        if (!$res['ok']) {
            return $res['payload'];
        }

        if (!$request->ajax() && !$request->expectsJson()) {
            $actionType = $request->action_type ?? 'order';

            if ($actionType === 'cart') {
                return redirect()->route('front.carts.index')->with('success', 'Product added to cart successfully!');
            }

            return redirect()->route('front.checkouts.index')->with('success', 'Product added to cart successfully!');
        }

        return response()->json($res['payload']);
    }

    public function edit(Request $request, $id)
    {
        if ($id === null || $id === '') {
            return response()->json(['success'=>false, 'msg'=>'Something Went Wrong!']);
        }

        $qty  = (int) $request->quantity;
        $cart = session()->get('cart', []);

        if (!isset($cart[$id])) {
            return response()->json(['success'=>false, 'msg'=>'Item not found in cart!']);
        }

        $segm = $request->segment ? $request->segment : 'home';

        if ($qty <= 0) {
            unset($cart[$id]);
        } else {
            $product_id   = (int) ($cart[$id]["product_id"] ?? 0);
            $variation_id = (int) ($cart[$id]["variation_id"] ?? 0);

            $product = Product::find($product_id);
            if (!$product) {
                unset($cart[$id]);
                session()->put('cart', $cart);
                return response()->json(['success'=>false, 'msg'=>'Product not found!']);
            }

            // Stock Check for Edit
            $is_stock = (int) ($product->is_stock ?? 0);
            if (!productStockManaged($product)) {
                return response()->json(['success'=>false, 'msg'=>'This product is currently unavailable.']);
            }
            $minQty = $product->minOrderQty();
            if ($qty < $minQty) {
                return response()->json(['success'=>false, 'msg'=>"Minimum order quantity for this product is {$minQty}."]);
            }

            if ($is_stock === 1) {
                $stockQty = $this->getAvailableStock($product, $variation_id);
                if ($stockQty < $qty) {
                    return response()->json(['success'=>false, 'msg'=>'Stock Not Available!']);
                }
            }

            $cart[$id]["quantity"] = $qty;
        }

        session()->put('cart', $cart);

        $totalPrice = 0;
        foreach ($cart as $item) {
            $totalPrice += ((float)$item['price']) * ((int)$item['quantity']);
        }

        $view  = view('frontend.partials.cart_sidebar', compact('cart','segm'))->render();
        $view2 = view('frontend.cart.details')->render();
        $view3 = view('frontend.cart.other_details', compact('totalPrice'))->render();

        return response()->json([
            'success' => true,
            'msg'     => 'Update cart successfully!',
            'html'    => $view,
            'html2'   => $view2,
            'html3'   => $view3,
            'segment' => $segm
        ]);
    }

    public function destroy($id)
    {
        if ($id === null || $id === '') {
            return response()->json(['success'=>false, 'msg'=>'Something Went Wrong!']);
        }

        $segm = request()->segment(1) ?? 'home';

        $cart = session()->get('cart', []);
        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        $totalPrice = 0;
        foreach ($cart as $item) {
            $totalPrice += ((float)$item['price']) * ((int)$item['quantity']);
        }

        $view       = view('frontend.partials.cart_sidebar', compact('cart','segm'))->render();
        $view2      = view('frontend.cart.details')->render();
        $view3      = view('frontend.cart.other_details', compact('totalPrice'))->render();
        $total_item = function_exists('getTotalCart') ? getTotalCart() : count($cart);
        $url        = route('front.home');

        return response()->json([
          'success' => true,
          'msg'     => 'Product removed successfully!',
          'html'    => $view,
          'html2'   => $view2,
          'html3'   => $view3,
          'item'    => $total_item,
          'segment' => $segm,
          'url'     => $url,
        ]);
    }

    public function clearAll()
    {
        session()->put('cart', []);
        return redirect()->route('front.home');
    }
}