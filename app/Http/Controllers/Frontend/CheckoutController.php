<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use App\Jobs\SendOrderNotification;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderDetails;
use App\Models\DeliveryCharge;
use App\Utils\ModulUtil;
use App\Utils\Util;
use App\Models\CouponCode;
use App\Models\User;
use App\Models\Product;
use App\Models\Variation;
use App\Models\Information;
use App\Models\LandingPagePackage;
use App\Facades\FacebookConversion;
use App\Facades\TikTokConversion;
use App\Http\Traits\DetectsOrderSource;
use Illuminate\Support\Facades\Schema;

class CheckoutController extends Controller
{
    use DetectsOrderSource;
    use \App\Http\Traits\FiresPurchaseEvent;

    public $modulutil;
    public $util;

    public function __construct(ModulUtil $modulutil, Util $util){
        $this->util=$util;
        $this->modulutil=$modulutil;
    }

    private function getActiveWorkerIds($allowedWorkers = [])
    {
        if (empty($allowedWorkers)) return collect([]);

        return User::query()
            ->where(function ($q) {
                $q->whereNull('status')
                    ->orWhereIn('status', [1, '1', true, 'true', 'active', 'Active']);
            })
            ->when(
                Schema::hasColumn((new User)->getTable(), 'deleted_at'),
                fn($q) => $q->whereNull('deleted_at')
            )
            ->whereIn('id', $allowedWorkers) 
            ->orderBy('id')
            ->pluck('id');
    }

    private function pickNextWorkerId($allowedWorkers = [])
    {
        $activeIds = $this->getActiveWorkerIds($allowedWorkers);
        
        if ($activeIds->isEmpty()) {
            throw new \Exception('No active workers found for this status to assign.');
        }

        $candidateId = DB::table('users as u')
            ->leftJoin('orders as o', function ($join) {
                $join->on('o.assign_user_id', '=', 'u.id')
                    ->whereDate('o.created_at', now()->toDateString()); 
            })
            ->whereIn('u.id', $activeIds->toArray())
            ->groupBy('u.id')
            ->orderByRaw('COUNT(o.id) ASC') 
            ->orderBy('u.id', 'ASC')
            ->value('u.id');

        return (int) ($candidateId ?? $activeIds->first());
    }
    
    /**
     * How many units may still be sold, using the same rules the cart applies
     * when it lets an item in. The checkout used to gate purely on the parent
     * products.stock_quantity column, which ignored "Manage Stock = No" and
     * per-variation stock — a variable product whose main quantity box was
     * left at 1 could be added to the cart and then refused at checkout
     * forever with "Stock Not Available!".
     */
    private function availableStockFor($product, $variationId = null)
    {
        if (!$product) return 0;

        // Manage Stock = No মানে প্রোডাক্ট বন্ধ — আগের মতো "আনলিমিটেড" নয়।
        // CartController-ও এখন ঠিক এই নিয়মেই চলে, তাই কার্ট আর চেকআউট একমত।
        if (!productStockManaged($product)) {
            return 0;
        }

        $variation = !empty($variationId)
            ? \App\Models\Variation::where('id', $variationId)->where('product_id', $product->id)->first()
            : null;

        // স্টক পড়ার নিয়ম একটাই — helpers.php-এর resolveStock()
        return resolveStock($product, $variation);
    }

    /**
     * Authoritative unit price for a product/variation, straight from the
     * database. Landing pages and the incomplete-order draft used to take the
     * price out of the submitted form, so a hand-made POST with amount=1 got a
     * real order for one taka. Mirrors CartController::calculatePrice so both
     * routes agree on what a product costs.
     */
    public function serverUnitPrice($product, $variationId = null)
    {
        $variation = null;
        if (!empty($variationId)) {
            $variation = \App\Models\Variation::where('id', $variationId)
                ->where('product_id', $product->id)
                ->first();
        }

        if ($variation) {
            $base = (float) ($variation->price ?? 0);
            if ($base <= 0) $base = (float) ($product->sell_price ?? 0);

            $afterDisc = (float) ($variation->after_discount_price ?? 0);
            $otherDisc = (float) ($variation->discount_price ?? 0);

            if ($afterDisc > 0 && $afterDisc < $base) return $afterDisc;
            if ($otherDisc > 0 && $otherDisc < $base) return $otherDisc;

            return $base;
        }

        $base = (float) ($product->sell_price ?? 0);
        $afterDisc = (float) ($product->after_discount ?? 0);

        return ($afterDisc > 0 && $afterDisc < $base) ? $afterDisc : $base;
    }

    /**
     * Actual taka value of the coupon in session for a given subtotal.
     * Percentage coupons store the percent number (e.g. 10) in the session,
     * so it must be converted before being subtracted from any order amount —
     * the same formula the checkout/landing JS uses for display.
     */
    public function couponDiscountValue($subTotal)
    {
        $amount = (float) (session()->get('coupon_discount') ?? 0);
        if ($amount <= 0) return 0;

        $type = (string) session()->get('discount_type');
        if ($type === 'percentage' || $type === 'percent') {
            return round(((float) $subTotal * $amount) / 100);
        }

        return $amount;
    }

    /**
     * couponDiscountValue() trusts whatever total was in session when the
     * coupon was applied — and getCouponDiscount() has to accept a
     * client-supplied total when the cart is empty (a landing-page "buy now"
     * has no session cart at all). That let a coupon's minimum_amount be
     * satisfied against a fake total at apply time, then ridden into a real
     * order far below that minimum. Every place an order is actually created
     * must call this instead, re-checking minimum_amount against the real,
     * server-computed subtotal for THIS order before trusting the discount.
     */
    private function verifiedCouponDiscount(float $realSubTotal): float
    {
        $code = session('applied_coupon_code');
        if (!$code) {
            return 0;
        }

        $coupon = CouponCode::where('code', $code)->first();
        if (!$coupon) {
            session()->forget(['coupon_discount', 'discount_type', 'applied_coupon_code']);
            return 0;
        }

        $minimum = (float) ($coupon->minimum_amount ?? 0);
        if ($minimum > 0 && $realSubTotal < $minimum) {
            session()->forget(['coupon_discount', 'discount_type', 'applied_coupon_code']);
            return 0;
        }

        return $this->couponDiscountValue($realSubTotal);
    }

    public function calculateShippingCharge($request, $productId = null, $qty = 1)
    {
        $globalSetting = DB::table('delivery_charges')->first();
        $totalWeight = 0;
        $hasWeightyProduct = false;

        if ($productId) {
            $product = Product::find($productId);
            
            if ($product && $product->is_free_shipping == 1) {
                return 0;
            }

            $weight = (float)($product->weight ?? 0);
            $totalWeight = $weight * (int)$qty;
            if($weight > 0) $hasWeightyProduct = true;
        } else {
            $cart = session()->get('cart', []);
            $allFreeShipping = true;

            if (empty($cart)) {
                $allFreeShipping = false;
            }

            foreach ($cart as $item) {
                $p = Product::find($item['product_id']);
                
                if (!$p || $p->is_free_shipping != 1) {
                    $allFreeShipping = false;
                }

                $weight = $p ? (float)($p->weight ?? 0) : 0;
                $totalWeight += $weight * (int)$item['quantity'];
                if($weight > 0) $hasWeightyProduct = true;
            }

            if (!empty($cart) && $allFreeShipping) {
                return 0;
            }
        }

        if ($globalSetting && $globalSetting->charge_type == 'weight_based' && $hasWeightyProduct) {
            
            $location = 'outside'; 
            if ($request->has('delivery_charge_id') && is_numeric($request->delivery_charge_id)) {
                $area = DeliveryCharge::find($request->delivery_charge_id);
                if ($area) {
                    $title = strtolower($area->title);
                    if (str_contains($title, 'inside') || str_contains($title, 'dhaka city') || str_contains($title, 'ভেতর') || str_contains($title, 'ভিতর')) {
                        $location = 'inside';
                    }
                }
            }

            $selectedCourier = $request->courier ?? 'steadfast'; 
            $courierName = $selectedCourier . '_' . $location;
            
            $courierRate = DB::table('courier_rates')->where('courier_name', $courierName)->first();
            
            if ($courierRate) {
                $baseW = (float)$courierRate->base_weight;
                $baseC = (float)$courierRate->base_charge;
                $extraC = (float)$courierRate->extra_per_kg_charge;

                $calcWeight = $totalWeight > 0 ? $totalWeight : 1;

                if ($calcWeight <= $baseW) {
                    return $baseC;
                } else {
                    $extraKg = ceil($calcWeight - $baseW);
                    return $baseC + ($extraKg * $extraC);
                }
            }
        }

        if ($request->has('delivery_charge_id') && is_numeric($request->delivery_charge_id)) {
            $charge = DeliveryCharge::find($request->delivery_charge_id);
            return $charge ? $charge->amount : 0;
        }
        
        return 0;
    }

    public function getDeliveryChargeAjax(Request $request)
    {
        $chargeAmount = $this->calculateShippingCharge($request, $request->product_id, $request->quantity ?? 1);
        return response()->json(['success' => true, 'charge' => $chargeAmount]);
    }

    public function index(){
        session()->forget(['coupon_discount', 'discount_type', 'applied_coupon_code']);

        $cart = session()->get('cart', []);
        if (empty($cart)) { return redirect()->route('front.home'); }

        $info    = Information::first();
        $charges = DeliveryCharge::whereNotNull('status')->get();
        
        $totalPrice = 0;
        foreach ($cart as $item) { 
            $totalPrice += $item['price'] * $item['quantity']; 
        }

        $coupon        = session()->get('coupon_discount');
        $coupon_code   = session()->get('applied_coupon_code');
        $coupn_item    = null;

        if ($coupon_code) {
            $coupn_item = CouponCode::where('code', $coupon_code)->first();
        } elseif ($coupon) {
            $coupn_item = CouponCode::where('amount', $coupon)->first();
        }

        if ($coupon > 0) {
            if (!$coupn_item || $coupn_item->minimum_amount > $totalPrice || date('Y-m-d', strtotime($coupn_item->end)) < date('Y-m-d')) {
                session()->forget(['coupon_discount', 'discount_type', 'applied_coupon_code']);
                $coupon = 0;
            }
        }
        
        $icEventId   = "IC_" . now()->format('YmdHis') . '_' . uniqid();
        $ttIcEventId = $icEventId . '_TT';

        $contents   = [];
        $contentIds = [];
        $totalValue = 0;
        foreach ($cart as $item) {
            $contents[] = [
                'id'             => $item['product_id'],
                'name'           => $item['name'] ?? '',
                'quantity'       => $item['quantity'],
                'item_price'     => $item['price']
            ];
            $contentIds[] = $item['product_id'];
            $totalValue  += $item['price'] * $item['quantity'];
        }

        try {
            FacebookConversion::sendEvent('InitiateCheckout', [
                'currency'      => 'BDT',
                'value'         => $totalValue,
                'content_ids'   => $contentIds,
                'contents'      => $contents,
                'num_items'     => count($cart),
                'content_type'  => 'product'
            ], $icEventId);
        } catch (\Exception $e) { \Log::error('Facebook CAPI BeginCheckout Error: ' . $e->getMessage()); }

        try {
            TikTokConversion::sendInitiateCheckout([
                'currency'    => 'BDT',
                'value'       => $totalValue,
                'content_ids' => $contentIds,
                'contents'    => $contents,
            ], $ttIcEventId);
        } catch (\Throwable $e) { \Log::error('TikTok Events API BeginCheckout Error: ' . $e->getMessage()); }

        // Checkout add-ons the shop owner ticked in the products list. Anything
        // already in the cart is filtered out — offering what the customer has
        // just added reads as broken.
        $recoProducts = collect();
        if (($info->checkout_reco_active ?? 0) == 1) {
            $inCart = array_column($cart, 'product_id');
            $recoProducts = Product::where('is_checkout_pick', 1)
                ->where('status', 1)
                ->whereNotIn('id', $inCart)
                ->latest('id')
                ->take(max(1, min(12, (int) ($info->checkout_reco_limit ?: 6))))
                ->get();
        }

        return view('frontend.cart.checkout', compact('cart','charges','totalPrice','icEventId','ttIcEventId','recoProducts'));
    }

    public function courierPercentage(Request $request){
        $id     = $request->id;
        $number = $request->phone;
        $summary = null;
        if($id){
            $customer = User::findOrFail($id);
            if($number){
                $checkCourier = $this->callApi($number);
                if(isset($checkCourier)){
                    $customer->curier_summery = $checkCourier;
                    $customer->save();
                    $summary = $checkCourier;
                }
            }
        }
        return response()->json(['success' => true, 'data' => $summary]);
    }
    
    private function callApi($number){
        $info   = Information::first();
        $apiKey = $info->fraudApi;
        $url    = "https://dash.hoorin.com/api/courier/sheet.php?apiKey=$apiKey&searchTerm=$number";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public function storelandData(Request $request) {
        $data = $request->validate([
            'mobile'             => 'required|digits_between:11,11',
            'first_name'         => 'required',
            'payment_method'     => 'nullable|string',
            'sender_number'      => 'nullable|string',
            'transaction_id'     => 'nullable|string',
            'shipping_address'   => 'required',
            'note'               => '',
            'delivery_charge_id' => 'nullable',
            'courier'            => 'nullable|string', 
            'final_amount'       => '',
            'amount'             => '',
            'purchase_event_id'  => 'nullable|string',
            'prd_id'             => 'required',
            'variation_id'       => 'nullable',
            'quantity'           => 'nullable',
            'selected_package_id' => 'nullable|integer',
        ]);

        if (isset($data['delivery_charge_id']) && !is_numeric($data['delivery_charge_id'])) {
            $data['delivery_charge_id'] = null;
        }

        $info = Information::first();
        if(isset($info->otp_system) && $info->otp_system == 1) {
            // Bind the verified flag to the number that was actually verified.
            if(session()->get('otp_verified') !== true || session()->get('otp_mobile') != $request->mobile) {
                 return response()->json([
                    'success' => false, 
                    'msg' => 'Mobile verification required to confirm order.'
                ]);
            }
        }
        
        $product = Product::with('variations')->where('id', $request->prd_id)->first();
        // CartController's /cart/store filters on status=1 in the query itself —
        // this endpoint loaded any product regardless, so a disabled product's
        // own landing page could still be bought.
        if(!$product || (int) $product->status !== 1) {
            return response()->json(['success' => false, 'msg' => 'Product not found!']);
        }

        $quantity = $request->quantity;
        $proQty   = ($quantity == null || $quantity == '') ? 1 : (int)$quantity;

        // A negative value here used to inflate stock instead of reducing it —
        // decreaseProductStock() would just add the (negative) quantity back.
        if ($proQty <= 0) {
            return response()->json(['success' => false, 'msg' => 'Please select a valid quantity.']);
        }

        if ($proQty < $product->minOrderQty()) {
            return response()->json(['success' => false, 'msg' => "Minimum order quantity for this product is {$product->minOrderQty()}."]);
        }

        // Landing pages let the customer pick a bulk-discount package (e.g.
        // "2 pcs for ৳9000"). This endpoint used to ignore it entirely and
        // charge quantity × the product's normal unit price, so a customer
        // choosing a discounted bundle was billed a different amount than
        // the page showed them. Scoped to this product's own landing pages
        // so a package id can't be borrowed to discount an unrelated product.
        $package = null;
        if ($request->filled('selected_package_id')) {
            $package = LandingPagePackage::whereHas('landingPage', function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                })
                ->find($request->selected_package_id);

            if ($package) {
                $proQty = max(1, (int) $package->qty);
            }
        }

        // This endpoint had none of CartController's stock checks at all — it
        // could oversell past product_stocks, and "Manage Stock = No" (which
        // means the product is disabled, not "unlimited") did not stop it either.
        if (!productStockManaged($product)) {
            return response()->json(['success' => false, 'msg' => 'This product is currently unavailable.']);
        }

        $orderVariation = !empty($request->variation_id)
            ? Variation::where('id', $request->variation_id)->where('product_id', $product->id)->first()
            : null;

        if (resolveStock($product, $orderVariation) < $proQty) {
            return response()->json(['success' => false, 'msg' => 'Stock Not Available!']);
        }

        // ✅ ফিক্স: ডাবল মাল্টিপ্লিকেশন বাগ সমাধান
        // ফ্রন্টএন্ড থেকে আসা $request->amount হলো টোটাল প্যাকেজের দাম। 
        // তাই ১ পিসের দাম বের করতে হলে তাকে quantity দিয়ে ভাগ করতে হবে।
        // Price always comes from the database — the submitted amount is not
        // trusted. A hand-made POST with amount=1 used to buy a 1,250tk product
        // for one taka, because this figure went straight into final_amount.
        // A selected package overrides the per-unit math entirely — its price
        // is the admin-set bundle total, not proQty × the normal unit price.
        if ($package) {
            $subTotal   = (float) $package->price;
            $unit_price = $subTotal / $proQty;
        } else {
            $unit_price = $this->serverUnitPrice($product, $request->variation_id);
            $subTotal   = $unit_price * $proQty;
        }

        if (isset($info->max_order_qty) && $info->max_order_qty > 0) {
            if ($proQty > $info->max_order_qty) {
                return response()->json(['success' => false, 'msg' => "You can order a maximum of {$info->max_order_qty} items at a time."]);
            }
        }
        if (isset($info->max_order_amount) && $info->max_order_amount > 0) {
            if ($subTotal > $info->max_order_amount) {
                return response()->json(['success' => false, 'msg' => "Your order amount cannot exceed ৳{$info->max_order_amount}"]);
            }
        }

        if (empty(auth()->user()->id)) {
            $user = User::where('mobile', $request->mobile)->first();
            if(!$user) {
                $user = User::create([
                    'first_name'       => $request->first_name,
                    'mobile'           => $request->mobile,
                    'shipping_address' => $request->shipping_address,
                    'note'             => $request->note,
                    'username'         => strtolower(str_replace(' ', '', $request->first_name)) . rand(100,999),
                    'status'           => 1
                ]);
            } elseif (!$user->roles()->exists()) {
                // Staff accounts (any role) must never be renamed by a checkout.
                $user->update([
                    'first_name' => $request->first_name,
                    'shipping_address' => $request->shipping_address
                ]);
            }
            $data['user_id'] = $user->id;
        } else {
            $user = auth()->user(); 
            $data['user_id'] = $user->id;
        }

        $total_discount_val = $proQty * ($product['discount'] ?? 0);
        
        $pr_data = [
            'product_id'     => $request->prd_id,
            'quantity'       => $proQty,
            'unit_price'     => $unit_price, // ✅ সঠিক ১ পিসের দাম
            'discount'       => $product['discount'] ?? 0,
            'is_stock'       => $product['is_stock'] ?? 1,
            'purchase_price' => $product['purchase_prices'] ?? 0,
            'variation_id'   => $request['variation_id']
        ];
        
        $chargeAmount = $this->calculateShippingCharge($request, $request->prd_id, $proQty);
        
        $data['date'] = date('Y-m-d');
        $data['invoice_no']      = generateUniqueInvoiceNo();
        $data['discount']        = $total_discount_val;
        $data['shipping_charge'] = $chargeAmount;
        $data['courier_id']      = 3; 

        $coupon_discount = $this->verifiedCouponDiscount($subTotal);

        // Admin-set usage caps: authoritative check at placement, because the
        // mobile number (=customer identity) is only certain here.
        if ($coupon_discount > 0) {
            $couponProblem = couponLimitProblem(session('applied_coupon_code'), $request->mobile);
            if ($couponProblem) {
                session()->forget(['coupon_discount', 'discount_type', 'applied_coupon_code']);
                return response()->json(['success' => false, 'msg' => $couponProblem]);
            }
        }

        $data['amount'] = $subTotal; // ✅ সঠিক সাব-টোটাল (ডাবল গুণ হবে না)
        $data['discount'] = $total_discount_val + $coupon_discount; 
        $data['final_amount'] = ($subTotal + $chargeAmount) - $coupon_discount;
        if (Schema::hasColumn('orders', 'coupon_code')) {
            $data['coupon_code'] = $coupon_discount > 0 ? session('applied_coupon_code') : null;
        }
        $data['status'] = 'pending';
        
        $isAutoAssignActive = $info->is_auto_assign ?? 0;
        $rules = !empty($info->auto_assign_rules) ? json_decode($info->auto_assign_rules, true) : [];
        $orderStatus = strtolower($data['status'] ?? 'pending'); 

        if (auth()->check() && auth()->user()->hasRole('worker')) {
            $data['assign_user_id'] = (int) auth()->id();
        } else {
            if ($isAutoAssignActive == 1 && isset($rules[$orderStatus]) && count($rules[$orderStatus]) > 0) {
                try {
                    $allowedWorkersForThisStatus = $rules[$orderStatus];
                    $data['assign_user_id'] = $this->pickNextWorkerId($allowedWorkersForThisStatus);
                } catch (\Exception $e) {
                    $data['assign_user_id'] = null;
                }
            } else {
                $data['assign_user_id'] = null; 
            }
        }

        unset($data['purchase_event_id']);
        unset($data['prd_id']);
        unset($data['variation_id']);
        unset($data['quantity']);
        unset($data['courier']);
        unset($data['selected_package_id']);

        // Capture the real visitor IP (server-side, spoof-proof) so fraud IPs can be blocked later
        $data['ip_address'] = $request->ip();

        if (isset($data['payment_method']) && !in_array(strtolower($data['payment_method']), ['cash on delivery', 'online', 'cod', ''])) {
            $data['payment_status'] = 'Pending';
        }

        $src = $this->detectOrderSource();
        if (Schema::hasColumn('orders', 'order_source')) {
            $data['order_source']  = $src['source'];
            $data['utm_source']    = $src['utm_source'];
            $data['utm_medium']    = $src['utm_medium'];
            $data['utm_campaign']  = $src['utm_campaign'];
            $data['referer_url']   = $src['referer'];
        }
        if ($request->filled('landing_page_type') && Schema::hasColumn('orders', 'landing_page_type')) {
            $data['landing_page_type'] = (string) $request->input('landing_page_type');
        }

        DB::beginTransaction();
        try {
            // ⚠️ আগে শর্ত ছিল (user_id = X বা mobile = Y) — OR হওয়ায় লগ-ইন করা
            // অবস্থায় (যেমন অ্যাডমিন/স্টাফ ফোনে অর্ডার নিলে) ওই user_id-র *অন্য*
            // কাস্টমারের ইনকমপ্লিট অর্ডারও ম্যাচ করে ওভাররাইট হয়ে যেতে পারত।
            // ড্রাফট তৈরি হয় মোবাইল দিয়ে, তাই এখন মোবাইলকেই প্রাধান্য দেওয়া হয়।
            $order = Order::where('status', 'incomplete')
                ->when(!empty($request->mobile), function ($query) use ($request) {
                    $query->where('mobile', $request->mobile);
                })
                ->when(empty($request->mobile) && $user, function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->latest()
                ->lockForUpdate()
                ->first();

            if($order){
                $order->update($data);
                DB::table('order_details')->where('order_id', $order->id)->delete();
                if (!empty($pr_data)) {
                    $order->details()->create($pr_data);
                }
            } else {
                $order = Order::create($data);
                if (!empty($pr_data)) {
                    $order->details()->create($pr_data);
                }
            }

            // Util moves both ledgers (products.stock_quantity and the
            // product_stocks/variations rows), so every sales channel now
            // agrees on how much is left.
            $this->util->decreaseProductStock(
                $pr_data['product_id'] ?? $product->id,
                $pr_data['variation_id'] ?? null,
                $proQty
            );
            $this->checkAndSendStockAlert($product->fresh());

            $this->modulutil->orderPayment($order, $request->all());
            $this->modulutil->orderstatus($order);
            $this->sendAdminNotification($order);

            $paymentMethod = $request->payment_method ?? 'cod';
            $order->update(['payment_method' => $paymentMethod]);

            if ($paymentMethod == 'eps') {
                $url = route('eps.pay', $order->id);
            } elseif ($paymentMethod == 'nagad') {
                $url = route('nagad.pay', $order->id);
            } elseif ($paymentMethod == 'uddoktapay') {
                $url = route('uddoktapay.pay', $order->id);
            } else {
                $url = confirmOrderUrl($order, true);
            }

            rememberPlacedOrder($order->id);

            session()->forget(['cart', 'coupon_discount', 'discount_type', 'applied_coupon_code', 'otp_verified', 'order_token']);

            DB::commit();

            \App\Jobs\SendOrderConfirmationCall::maybeDispatch($order, '(Landing Checkout)');

            // Purchase goes to Meta/TikTok as 'PUR_<order id>', the same id the
            // thank-you page's browser pixel uses, so the two deduplicate.
            //
            // For a gateway payment the customer has not paid anything yet at
            // this point — they are about to be sent to bKash/Nagad/SSL — so
            // firing here counted abandoned payments as revenue. Those orders
            // now fire from the gateway's own success callback instead. Cash on
            // delivery still fires here, because the order IS the conversion.
            if (!$this->isOnlinePayment($order->payment_method)) {
                $this->firePurchaseEvents($order, [], [], $url);
            }
            
            if($request->ajax()){
                return response()->json([
                    'success' => true,
                    'msg'     => 'Checkout Successfully..!!',
                    'url'     => $url,
                    'purchase_event_id' => 'PUR_' . $order->id,
                    'order_id' => $order->id
                ]);
            } else {
                return redirect($url);
            }

        } catch (\Exception $e) {
            DB::rollback();
            if($request->ajax()){
                return response()->json(['success'=>false,'msg'=>$e->getMessage()]);
            } else {
                return back()->with('error', $e->getMessage());
            }
        }
    }
    
    public function incompleteStore(Request $request){
        $req_data = $request->validate([
            'mobile'       => 'required|numeric|min:11',
            'name'         => 'nullable',
            'address'      => 'nullable',
            'prd_id'       => 'nullable',
            'amount'       => 'nullable',
            'quantity'     => 'nullable',
            'variation_id' => 'nullable'
        ]);
        
        DB::beginTransaction();
        try {
            $existingOrder = Order::where('mobile', $req_data['mobile'])
                ->where('status', 'incomplete')
                ->latest()
                ->lockForUpdate()
                ->first();

            $user = null;
            if (!empty($request->mobile)) {
                 $user = User::where('mobile', $request->mobile)->first();
                 if(!$user) {
                     $user = User::create([
                         'mobile'           => $request->mobile,
                         'first_name'       => $req_data['name'] ?? 'Guest',
                         'username'         => strtolower(str_replace(' ', '', $req_data['name'] ?? 'guest')) . rand(100,999),
                         'status'           => 1,
                         'shipping_address' => $req_data['address'] ?? ''
                     ]);
                 }
            }
            
            $product_list = [];
            $unique_check = []; 
            $total = 0;
            $total_discount = 0;

            if($request->has('prd_id') && !empty($request->prd_id)) {
                $prodInfo = Product::find($request->prd_id);
                if($prodInfo) {
                    // max(1, …) because a landing page that posts an empty or
                    // zero quantity used to reach a division and 500 here.
                    $qty = max(1, (int) ($request->quantity ?? 1));

                    // Same rule as the live order: never price a draft from
                    // the submitted amount.
                    $unit_price = $this->serverUnitPrice($prodInfo, $request->variation_id);
                    $total      = $unit_price * $qty;
                    
                    $product_list[] = [
                        'product_id'     => $request->prd_id,
                        'quantity'       => $qty,
                        'unit_price'     => $unit_price, // ✅ সঠিক ১ পিসের দাম
                        'purchase_price' => $prodInfo->purchase_price,
                        'variation_id'   => $request->variation_id ?? null,
                        'discount'       => 0,
                        'is_stock'       => $prodInfo->is_stock,
                    ];
                }
            } else {
                $carts = session()->get('cart',[]);
                if ($carts) {
                    foreach($carts as $key=>$item){
                        $total          += $item['quantity'] * $item['price'];
                        $total_discount += $item['quantity'] * ($item['discount'] ?? 0);
                        
                        $uniqueKey = $item['product_id'] . '_' . ($item['variation_id'] ?? 0);

                        if(isset($unique_check[$uniqueKey])) {
                            $product_list[$unique_check[$uniqueKey]]['quantity'] += $item['quantity'];
                        } else {
                            $product_list[] = [
                                'product_id'     => $item['product_id'],
                                'quantity'       => $item['quantity'],
                                'unit_price'     => $item['price'],
                                'purchase_price' => $item['purchase_price'] ?? 0,
                                'variation_id'   => $item['variation_id'],
                                'discount'       => $item['discount'] ?? 0,
                                'is_stock'       => $item['is_stock'] ?? 1,
                            ];
                            $unique_check[$uniqueKey] = count($product_list) - 1;
                        }
                    }
                } 
            }
            
            if(empty($product_list)) {
                DB::rollback();
                return response()->json(['success' => false, 'message'=>'No products to save']);
            }

            $coupn_discount = $this->verifiedCouponDiscount($total);

            $info = Information::first();
            $isAutoAssignActive = $info->is_auto_assign ?? 0;
            $rules = !empty($info->auto_assign_rules) ? json_decode($info->auto_assign_rules, true) : [];
            $assignUserId = null;

            if ($isAutoAssignActive == 1 && isset($rules['incomplete']) && count($rules['incomplete']) > 0) {
                try {
                    $assignUserId = $this->pickNextWorkerId($rules['incomplete']);
                } catch (\Exception $e) {}
            }

            $data = [
                'date'             => date('Y-m-d'),
                'invoice_no'       => generateUniqueInvoiceNo(),
                'discount'         => $total_discount + $coupn_discount,
                'amount'           => $total_discount + $total,
                'shipping_charge'  => 0,
                'first_name'       => $req_data['name'] ?? ($user->first_name ?? ''),
                'mobile'           => $req_data['mobile'],
                'shipping_address' => $req_data['address'] ?? '',
                'status'           => 'incomplete',
                'final_amount'     => $total - $coupn_discount,
                'user_id'          => $user ? $user->id : null,
                'assign_user_id'   => $assignUserId
            ];
            if (Schema::hasColumn('orders', 'coupon_code')) {
                $data['coupon_code'] = $coupn_discount > 0 ? session('applied_coupon_code') : null;
            }

            $src = $this->detectOrderSource();
            if (Schema::hasColumn('orders', 'order_source')) {
                $data['order_source']  = $src['source'];
                $data['utm_source']    = $src['utm_source'];
                $data['utm_medium']    = $src['utm_medium'];
                $data['utm_campaign']  = $src['utm_campaign'];
                $data['referer_url']   = $src['referer'];
            }
            if ($request->filled('landing_page_type') && Schema::hasColumn('orders', 'landing_page_type')) {
                $data['landing_page_type'] = (string) $request->input('landing_page_type');
            }

            // Capture the real visitor IP (server-side, spoof-proof) so fraud IPs can be blocked later
            $data['ip_address'] = $request->ip();

            if ($existingOrder) {
                $existingOrder->update($data);
                DB::table('order_details')->where('order_id', $existingOrder->id)->delete();
                $existingOrder->details()->createMany($product_list);
            } else {
                $order = Order::create($data);
                $order->details()->createMany($product_list);
            }
            
            DB::commit();
            return response()->json(['success' => true, 'message'=>'Incomplete Order Saved']);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function store(Request $request){
        $data = $request->validate([
            'mobile'             => 'required|digits_between:11,11',
            'first_name'         => 'required',            
            'payment_method'     => 'required|string',
            'sender_number'      => 'nullable|string',
            'transaction_id'     => 'nullable|string',
            'shipping_address'   => 'required',        
            'ip_address'         => '',
            'note'               => '',
            'delivery_charge_id' => 'nullable', 
            'courier'            => 'nullable|string',              
            // The form has always sent this and the orders table has a UNIQUE
            // index on it, but the value was never accepted or saved — so
            // every row held NULL and the duplicate-order guard did nothing.
            'order_token'        => 'nullable|string|max:64',
        ]);

        if (isset($data['delivery_charge_id']) && !is_numeric($data['delivery_charge_id'])) {
            $data['delivery_charge_id'] = null;
        }

        $user = auth()->user(); 
        // Always use the server-side visitor IP (spoof-proof) — never trust a client-provided ip_address
        $data['ip_address'] = $request->ip();

        $info         = Information::first();

        // OTP was enforced only by JavaScript here, so a direct POST placed a
        // confirmed order on an unverified phone number — exactly what the OTP
        // was added to prevent. The landing-page twin already checked this.
        if (isset($info->otp_system) && $info->otp_system == 1) {
            if (session()->get('otp_verified') !== true || session()->get('otp_mobile') != $request->mobile) {
                $msg = 'Mobile verification required to confirm order.';
                if ($request->ajax()) return response()->json(['success' => false, 'msg' => $msg]);
                return back()->with('error', $msg);
            }
        }
        $limitMinutes = $info->time_limit ?? 60;
        
        $appliesMobileCheck = ($info->is_mobile_check == 1) && !empty($request->mobile);
        $appliesIpCheck     = ($info->is_ip_check == 1) && !empty($data['ip_address']);
        
        if ($appliesMobileCheck || $appliesIpCheck) {
            $query = Order::whereNot('status', 'incomplete');
            $query->where(function($q) use ($appliesMobileCheck, $appliesIpCheck, $request, $data) {
                if ($appliesMobileCheck) $q->where('mobile', $request->mobile);
                if ($appliesIpCheck) $q->orWhere('ip_address', $data['ip_address']);
            });
            $recentOrder = $query->where('created_at', '>=', now()->subMinutes($limitMinutes))->latest()->first();
            if ($recentOrder) {
                $minutesPassed = now()->diffInMinutes($recentOrder->created_at);
                $remaining     = max(0, $limitMinutes - $minutesPassed);
                return response()->json([
                    'success' => false,
                    'msg'     => "You can place a new order after {$remaining} minutes."
                ]);
            }
        }

        $carts          = session()->get('cart',[]);

        // An empty cart used to sail straight through to Order::create() below,
        // producing a real order with zero order_details rows. incompleteStore()
        // already guards this ('No products to save') — this is the real
        // checkout, so the same guard belongs here too.
        if (empty($carts)) {
            $msg = 'Your cart is empty.';
            if ($request->ajax()) return response()->json(['success' => false, 'msg' => $msg]);
            return redirect()->route('home')->with('error', $msg);
        }

        // Wholesale minimum, re-checked here because the cart may predate the
        // admin enabling wholesale (or raising the minimum) on a product.
        $cartProducts = Product::whereIn('id', array_column($carts, 'product_id'))->get()->keyBy('id');
        foreach ($carts as $item) {
            $cartProduct = $cartProducts->get($item['product_id'] ?? 0);
            if ($cartProduct && (int) $item['quantity'] < $cartProduct->minOrderQty()) {
                $msg = "Minimum order quantity for {$cartProduct->name} is {$cartProduct->minOrderQty()}.";
                if ($request->ajax()) return response()->json(['success' => false, 'msg' => $msg]);
                return back()->with('error', $msg);
            }
        }

        $total_cart_qty = 0;
        $total          = 0;

        if ($carts) {
            foreach($carts as $item){
                $total_cart_qty += $item['quantity'];
                $total          += $item['quantity'] * $item['price'];
            }
        }

        $coupn_discount = $this->verifiedCouponDiscount($total);

        // Admin-set usage caps: authoritative check at placement, because the
        // mobile number (=customer identity) is only certain here.
        if ($coupn_discount > 0) {
            $couponProblem = couponLimitProblem(session('applied_coupon_code'), $request->mobile);
            if ($couponProblem) {
                session()->forget(['coupon_discount', 'discount_type', 'applied_coupon_code']);
                if($request->ajax()) {
                    return response()->json(['success' => false, 'msg' => $couponProblem]);
                }
                return back()->with('error', $couponProblem);
            }
        }

        if (isset($info->max_order_qty) && $info->max_order_qty > 0) {
            if ($total_cart_qty > $info->max_order_qty) {
                if($request->ajax()) {
                    return response()->json(['success' => false, 'msg' => "You can order a maximum of {$info->max_order_qty} items at a time."]);
                }
                return back()->with('error', "You can order a maximum of {$info->max_order_qty} items at a time.");
            }
        }

        if (isset($info->max_order_amount) && $info->max_order_amount > 0) {
            if ($total > $info->max_order_amount) {
                if($request->ajax()) {
                    return response()->json(['success' => false, 'msg' => "Your order amount cannot exceed ৳{$info->max_order_amount}"]);
                }
                return back()->with('error', "Your order amount cannot exceed ৳{$info->max_order_amount}");
            }
        }

        if (!empty($request->mobile)) {
            $user = User::where('mobile', $request->mobile)->first();

            if (!$user) {
                // Username is generated ONCE — only when a brand-new customer
                // is created. Existing usernames must never be overwritten.
                $baseUsername = strtolower(str_replace(' ', '', $data['first_name']));
                $username     = $baseUsername;
                $counter      = 1;
                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername . $counter;
                    $counter++;
                }

                $user = User::create([
                    'mobile'     => $request->mobile,
                    'first_name' => $data['first_name'],
                    'username'   => $username,
                    'status'     => 1,
                ]);
            } elseif (!$user->roles()->exists()) {
                // Existing plain customer: refresh the display name only.
                // username and status stay untouched; staff accounts (any
                // role) are never modified by a checkout.
                $user->update(['first_name' => $data['first_name']]);
            }

            $data['user_id'] = $user->id;
        }

        if (auth()->check()) {
            $data['user_id'] = auth()->id();
            $user = auth()->user();
        }

        $product_list   = [];
        $unique_check   = []; 
        $total_discount = 0;

        if ($carts) {
            foreach($carts as $key=>$item){
                $total_discount += $item['quantity'] * ($item['discount'] ?? 0);
                
                $uniqueKey = $item['product_id'] . '_' . ($item['variation_id'] ?? 0);

                if(isset($unique_check[$uniqueKey])) {
                    $product_list[$unique_check[$uniqueKey]]['quantity'] += $item['quantity'];
                } else {
                    $product_list[] = [
                        'product_id'     => $item['product_id'],
                        'quantity'       => $item['quantity'],
                        'unit_price'     => $item['price'],
                        'variation_id'   => $item['variation_id'],
                        'purchase_price' => $item['purchase_price'] ?? 0,
                        'discount'       => $item['discount'] ?? 0,
                        'is_stock'       => $item['is_stock'] ?? 1,
                    ];
                    $unique_check[$uniqueKey] = count($product_list) - 1;
                }
            }
        } 

        $chargeAmount = $this->calculateShippingCharge($request);
        
        $data['date'] = date('Y-m-d');
        $data['invoice_no']      = generateUniqueInvoiceNo();
        $data['discount']        = $total_discount + $coupn_discount;
        $data['amount']          = $total_discount + $total;
        $data['shipping_charge'] = $chargeAmount;
        $data['final_amount']    = $total + $chargeAmount - $coupn_discount;
        if (Schema::hasColumn('orders', 'coupon_code')) {
            $data['coupon_code'] = $coupn_discount > 0 ? session('applied_coupon_code') : null;
        }
        $data['status']          = 'pending';

        $isAutoAssignActive = $info->is_auto_assign ?? 0;
        $rules = !empty($info->auto_assign_rules) ? json_decode($info->auto_assign_rules, true) : [];
        $orderStatus = strtolower($data['status'] ?? 'pending'); 

        if (auth()->check() && auth()->user()->hasRole('worker')) {
            $data['assign_user_id'] = (int) auth()->id();
        } else {
            if ($isAutoAssignActive == 1 && isset($rules[$orderStatus]) && count($rules[$orderStatus]) > 0) {
                try {
                    $allowedWorkersForThisStatus = $rules[$orderStatus];
                    $data['assign_user_id'] = $this->pickNextWorkerId($allowedWorkersForThisStatus);
                } catch (\Exception $e) {
                    $data['assign_user_id'] = null;
                }
            } else {
                $data['assign_user_id'] = null; 
            }
        }
        
        DB::beginTransaction();
        try {
            unset($data['courier']);

            if (isset($data['payment_method']) && !in_array(strtolower($data['payment_method']), ['cash on delivery', 'online', 'cod', ''])) {
                $data['payment_status'] = 'Pending';
            }

            $src = $this->detectOrderSource();
            if (Schema::hasColumn('orders', 'order_source')) {
                $data['order_source']  = $src['source'];
                $data['utm_source']    = $src['utm_source'];
                $data['utm_medium']    = $src['utm_medium'];
                $data['utm_campaign']  = $src['utm_campaign'];
                $data['referer_url']   = $src['referer'];
            }

            // ⚠️ আগে শর্ত ছিল (user_id = X বা mobile = Y) — OR হওয়ায় লগ-ইন করা
            // অবস্থায় (যেমন অ্যাডমিন/স্টাফ ফোনে অর্ডার নিলে) ওই user_id-র *অন্য*
            // কাস্টমারের ইনকমপ্লিট অর্ডারও ম্যাচ করে ওভাররাইট হয়ে যেতে পারত।
            // ড্রাফট তৈরি হয় মোবাইল দিয়ে, তাই এখন মোবাইলকেই প্রাধান্য দেওয়া হয়।
            $order = Order::where('status', 'incomplete')
                ->when(!empty($request->mobile), function ($query) use ($request) {
                    $query->where('mobile', $request->mobile);
                })
                ->when(empty($request->mobile) && $user, function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->latest()
                ->lockForUpdate()
                ->first();

            if(!$order){
                // The UNIQUE index on order_token is what stops a double
                // submit (two clicks, or a retried request) from creating two
                // live orders and deducting stock twice.
                try {
                    $order = Order::create($data);
                } catch (\Illuminate\Database\QueryException $e) {
                    if ((int) ($e->errorInfo[1] ?? 0) === 1062 && !empty($data['order_token'])) {
                        DB::rollback();
                        $existing = Order::where('order_token', $data['order_token'])->first();
                        if ($existing) {
                            if($request->ajax()) {
                                return response()->json([
                                    'success'  => true,
                                    'msg'      => 'Order already placed.',
                                    'url'      => confirmOrderUrl($existing),
                                    'order_id' => $existing->id,
                                ]);
                            }
                            return redirect(confirmOrderUrl($existing));
                        }
                    }
                    throw $e;
                }
                if (!empty($product_list)) { 
                    foreach ($product_list as $item) {
                        $pro = Product::find($item['product_id']);
                        if($this->availableStockFor($pro, $item['variation_id'] ?? null) < $item['quantity']){
                            DB::rollback();
                            if($request->ajax()) return response()->json(['success'=>false,'msg'=>'Stock Not Available!']);
                            return back()->with('error', 'Stock Not Available!');
                        } else {
                            $this->util->decreaseProductStock($item['product_id'], $item['variation_id'] ?? null, $item['quantity']);
                            $this->checkAndSendStockAlert($pro->fresh());
                        }
                    }
                    $order->details()->createMany($product_list);
                }   
            } else {  
                DB::table('order_details')->where('order_id', $order->id)->delete();
                $order->details()->createMany($product_list);

                foreach ($product_list as $item) {
                    $pro = Product::find($item['product_id']);
                    if($this->availableStockFor($pro, $item['variation_id'] ?? null) < $item['quantity']){
                        DB::rollback();
                        if($request->ajax()) return response()->json(['success'=>false,'msg'=>'Stock Not Available!']);
                        return back()->with('error', 'Stock Not Available!');
                    } else {
                         $this->util->decreaseProductStock($item['product_id'], $item['variation_id'] ?? null, $item['quantity']);
                         $this->checkAndSendStockAlert($pro->fresh());
                    }
                }

                $order->update($data);
            }
            
            $this->modulutil->orderPayment($order, $request->all());
            $this->modulutil->orderstatus($order);
            $this->sendAdminNotification($order);

            $paymentMethod = $request->payment_method ?? 'cod';
            $order->update(['payment_method' => $paymentMethod]);

            if ($paymentMethod == 'eps') {
                $url = route('eps.pay', $order->id);
            } elseif ($paymentMethod == 'nagad') {
                $url = route('nagad.pay', $order->id);
            } elseif ($paymentMethod == 'uddoktapay') {
                $url = route('uddoktapay.pay', $order->id);
            } else {
                $url = confirmOrderUrl($order);
            }

            rememberPlacedOrder($order->id);

            DB::commit();

            \App\Jobs\SendOrderConfirmationCall::maybeDispatch($order, '(Website Checkout)');

            // Same rule as the landing checkout: a gateway order has not been
            // paid for yet, so its Purchase fires from the gateway's success
            // callback. Cash on delivery converts here and now.
            if (!$this->isOnlinePayment($order->payment_method)) {
                $this->firePurchaseEvents($order, [], [], $url);
            }

            session()->forget(['cart', 'coupon_discount', 'discount_type', 'applied_coupon_code', 'otp_verified', 'order_token']);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'msg'     => 'Order Create successfully!',
                    'url'     => $url,
                    'order_id'=> $order->id
                ]);
            } else {
                return redirect($url);
            }

        } catch (\Exception $e) {
            DB::rollback();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'msg' => $e->getMessage()]);
            } else {
                return back()->with('error', $e->getMessage());
            }
        }
    }
    
    public function storeData(Request $request) { return $this->store($request); }
    
    public function StoreChk(Request $request){
          $this->validate($request, [
            'first_name'         => 'required',
            'mobile'             => 'required',
            'shipping_address'   => 'required',
            'delivery_charge_id' => 'nullable' 
        ]);
    }
    
    public function getCouponDiscount(Request $request){
        $info = Information::first();
        if(isset($info->coupon_visibility) && $info->coupon_visibility == 0){
             return response()->json(['success'=>false, 'msg' => 'Coupon system is currently disabled.']);
        }
        // Some landing page designs send the code as coupon_code and the
        // running total as amount — accept both spellings so every design
        // talks to this one endpoint.
        $code = trim((string) $request->input('code', $request->input('coupon_code')));
        if ($code === '') {
            return response()->json(['success'=>false, 'msg' => 'Coupon code is required.']);
        }

        $total = 0;
        $cart  = session()->get('cart');
        if($cart){
            foreach($cart as $id=>$item){ $total += $item['price'] * $item['quantity']; }
        }

        if($request->has('total_price') && $total == 0) {
            $total = (float) $request->total_price;
        }
        if($request->has('amount') && $total == 0) {
            $total = (float) $request->amount;
        }

        $item = CouponCode::where('code', $code)
                    ->where(function($row) use($total){
                        $row->where('minimum_amount','0')
                            ->orWhereNull('minimum_amount')
                            ->orWhere('minimum_amount','<=',$total);
                    })
                    ->whereDate('start','<=', date('Y-m-d'))
                    ->whereDate('end','>=', date('Y-m-d'))->first();
        
        if($item){
            // Early usage-cap feedback at apply time (mobile may not be typed
            // yet, so the per-customer cap re-checks at order placement too)
            $couponProblem = couponLimitProblem($item->code, $request->input('mobile'));
            if ($couponProblem) {
                return response()->json(['success' => false, 'msg' => $couponProblem]);
            }

            session()->put('coupon_discount', $item->amount);
            session()->put('discount_type', $item->discount_type);
            session()->put('applied_coupon_code', $item->code);
            
            return response()->json([
                'success' => true,
                'msg' => 'You Got Coupon Discount!',
                'amount' => $item->amount,
                'discount_type' => $item->discount_type,
                // Taka value for this total — some landing designs read res.discount directly.
                'discount' => $this->couponDiscountValue($total)
            ]);
        } else {
            return response()->json(['success'=>false,'msg'=>'Invalid Coupon or Minimum Amount Not Reached!']);
        }
    }

    private function sendAdminNotification($order) {
        try { 
            $info = Information::first();
            if ($info && $info->notification_active == 1) {
                SendOrderNotification::dispatchAfterResponse($order); 
            }
        } catch (\Exception $e) { }
    }

    public function sendOtp(Request $request) {
        $request->validate([ 'mobile' => 'required|numeric|digits:11' ]);
        if(session()->has('otp_sent_at') && session()->has('otp_mobile')) {
            $lastSent = session()->get('otp_sent_at');
            $lastMobile = session()->get('otp_mobile');
            if($lastMobile == $request->mobile && now()->diffInSeconds($lastSent) < 60) {
                 return response()->json([ 'success' => true, 'msg' => 'Your 4 digit code was already sent.' ]);
            }
        }
        // random_int, not rand(): the old generator is predictable from a
        // couple of observed codes.
        $otp = random_int(1000, 9999);
        session()->put('otp_code', $otp);
        session()->put('otp_mobile', $request->mobile);
        session()->put('otp_verified', false);
        session()->put('otp_sent_at', now());
        session()->put('otp_attempts', 0);
        $msg = "Your OTP code is: " . $otp . " . Please do not share this code.";
        $settings = Information::first();
        if (!$settings || empty($settings->sms_api_key) || empty($settings->sms_sender_id)) {
             return response()->json([ 'success' => false, 'msg' => 'SMS Gateway not configured properly.' ]);
        }
        try {
            $response = Http::get("http://bulksmsbd.net/api/smsapi", [
                'api_key' => $settings->sms_api_key,
                'type' => 'text',
                'number' => $request->mobile,
                'senderid' => $settings->sms_sender_id,
                'message' => $msg,
            ]);
            return response()->json([ 'success' => true, 'msg' => 'Your 4 digit code has been sent.' ]);
        } catch (\Exception $e) {
            return response()->json([ 'success' => false, 'msg' => 'Error sending SMS: ' . $e->getMessage() ]);
        }
    }

    // A code with no attempt limit and no expiry could simply be guessed:
    // there was nothing to stop a script walking every combination, and the
    // code stayed valid for the whole session once sent.
    const OTP_MAX_ATTEMPTS = 5;
    const OTP_VALID_MINUTES = 10;

    public function verifyOtp(Request $request) {
        $request->validate([ 'otp' => 'required', 'mobile' => 'required' ]);

        $session_otp = session()->get('otp_code');
        $session_mobile = session()->get('otp_mobile');
        $sentAt = session()->get('otp_sent_at');

        if (empty($session_otp) || empty($sentAt)) {
            return response()->json([ 'success' => false, 'msg' => 'Please request a code first.' ]);
        }

        if (now()->diffInMinutes($sentAt) >= self::OTP_VALID_MINUTES) {
            session()->forget(['otp_code', 'otp_sent_at', 'otp_attempts']);
            return response()->json([ 'success' => false, 'msg' => 'This code has expired. Please request a new one.' ]);
        }

        $attempts = (int) session()->get('otp_attempts', 0);
        if ($attempts >= self::OTP_MAX_ATTEMPTS) {
            session()->forget(['otp_code', 'otp_sent_at']);
            return response()->json([ 'success' => false, 'msg' => 'Too many wrong attempts. Please request a new code.' ]);
        }

        if ($request->mobile == $session_mobile && hash_equals((string) $session_otp, (string) $request->otp)) {
            session()->put('otp_verified', true);
            session()->forget(['otp_attempts']);
            return response()->json([ 'success' => true, 'msg' => 'Verification successful!' ]);
        }

        session()->put('otp_attempts', $attempts + 1);
        $left = self::OTP_MAX_ATTEMPTS - ($attempts + 1);

        return response()->json([ 'success' => false, 'msg' => 'Invalid code! ' . max(0, $left) . ' attempt(s) left.' ]);
    }

    private function checkAndSendStockAlert($product)
    {
        if ($product->fresh()->stock_quantity <= 0) {
            $info = Information::first();
            if ($info && $info->sms_api_key && $info->admin_phone) {
                $msg = "Alert: Product '{$product->name}' is now Out of Stock!";
                try {
                    $response = Http::get("http://bulksmsbd.net/api/smsapi", [
                        'api_key' => $info->sms_api_key,
                        'type' => 'text',
                        'number' => $info->admin_phone,
                        'senderid' => $info->sms_sender_id,
                        'message' => $msg,
                    ]);
                } catch (\Exception $e) { }
            }
        }
     }
}