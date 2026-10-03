<?php

if (!function_exists('biz_format_currency')) {
    function biz_format_currency($amount) {
        return '৳ ' . number_format($amount, 2);
    }
}

function setting($key = null) {
    static $info = null;
    if ($info === null) {
        $info = App\Models\Information::first();
    }
    return $key ? ($info->$key ?? null) : $info;
}

// ✅ Memoized: Information::first() — query only once per request
function getInfo() {
    static $info = null;
    if ($info === null) {
        $info = App\Models\Information::first();
    }
    return $info;
}

function dateFormate($date=null){
    $value='';
    if ($date) {
        $value=date('M d Y', strtotime($date));
    }
    return $value;
}

function getImage($folder=null,$value=null){
    $url = asset('images/no_found.png');
    $path = public_path($folder.'/'.$value);
    if (!empty($folder) && (!empty($value))) {
        if(file_exists($path)){
            $url = asset($folder.'/'.$value);
        }
    }
    return $url;
}

function deleteImage($folder=null, $file=null){
    if (!empty($folder) && !empty($file)) {
        $path = public_path($folder.'/'.$file);
        $isExists = file_exists($path);
        if ($isExists) {
            unlink($path);
        }
    }
    return true;
}

/*
|--------------------------------------------------------------------------
| স্টকের একটাই নিয়ম
|--------------------------------------------------------------------------
| স্টক তিন জায়গায় থাকে — product_stocks (সবচেয়ে নির্ভরযোগ্য), variations.stock_quantity
| আর products.stock_quantity। আগে অ্যাডমিন লিস্ট, প্রোডাক্ট কার্ড, popup আর ডিটেইল পেজ
| তিনটা আলাদা উৎস পড়ত, ফলে এক প্রোডাক্টের তিন রকম সংখ্যা দেখাত। এখন সবাই এখান থেকেই পড়ে।
|
| গুরুত্বপূর্ণ: product_stocks-এ row থাকলে সেটাই চূড়ান্ত — এমনকি ০ হলেও। শুধু row-ই না
| থাকলে পুরনো কলামে নামা হয়, নাহলে বিক্রি হয়ে যাওয়া স্টক আবার "আছে" দেখাত।
*/

if (!function_exists('variationStockRowsExist')) {
    function variationStockRowsExist($variation): bool
    {
        if (!$variation) {
            return false;
        }

        return $variation->relationLoaded('stocks')
            ? $variation->stocks->count() > 0
            : \App\Models\ProductStock::where('variation_id', $variation->id)->exists();
    }
}

if (!function_exists('resolveVariationStock')) {
    function resolveVariationStock($variation): int
    {
        if (!$variation) {
            return 0;
        }

        if (variationStockRowsExist($variation)) {
            $sum = $variation->relationLoaded('stocks')
                ? $variation->stocks->sum('quantity')
                : \App\Models\ProductStock::where('variation_id', $variation->id)->sum('quantity');

            return max(0, (int) $sum);
        }

        return max(0, (int) ($variation->stock_quantity ?? 0));
    }
}

if (!function_exists('resolveStock')) {
    /**
     * @param  \App\Models\Product|null    $product
     * @param  \App\Models\Variation|null  $variation  দিলে শুধু ওই ভ্যারিয়েশনের স্টক
     */
    function resolveStock($product, $variation = null): int
    {
        if ($variation) {
            return resolveVariationStock($variation);
        }

        if (!$product) {
            return 0;
        }

        $vars = $product->relationLoaded('variations')
            ? $product->variations
            : $product->variations()->get();

        if ($vars->isEmpty()) {
            return max(0, (int) ($product->stock_quantity ?? 0));
        }

        if (($product->type ?? 'single') === 'variable') {
            return (int) $vars->sum(fn ($v) => resolveVariationStock($v));
        }

        // single: একটাই ভ্যারিয়েশন থাকার কথা
        $first = $vars->first();
        if (variationStockRowsExist($first) || (int) ($first->stock_quantity ?? 0) > 0) {
            return resolveVariationStock($first);
        }

        return max(0, (int) ($product->stock_quantity ?? 0));
    }
}

if (!function_exists('productStockManaged')) {
    /** Manage Stock = No মানে প্রোডাক্ট বন্ধ, "আনলিমিটেড স্টক" নয়। */
    function productStockManaged($product): bool
    {
        return (int) ($product->is_stock ?? 0) === 1;
    }
}

if (!function_exists('productIsOrderable')) {
    function productIsOrderable($product, $variation = null): bool
    {
        if (!$product || (int) ($product->status ?? 1) !== 1) {
            return false;
        }

        if (!productStockManaged($product)) {
            return false;
        }

        return resolveStock($product, $variation) > 0;
    }
}

function priceFormate($amount=0){
    return '৳'.number_format($amount,0);
}

function getRole(){
    return auth()->user()->roles->pluck('name')[0] ??'';
}

function getTotalAmount(){
    $cart = session()->get('cart', []);
    $total = 0;
    foreach($cart as $cartItem){
        $total += $cartItem['price'] * $cartItem['quantity'];
    }
    return $total;
}

function getTotalCart(){
    return count(session()->get('cart',[]));
}

function getProductInfo($product, $variation = null){
    // ভ্যারিয়েবল প্রোডাক্টে দাম products.sell_price/after_discount এ না, ভ্যারিয়েশনে থাকে —
    // আগে এখানে সবসময় প্রোডাক্ট-লেভেল কলাম পড়ত বলে কার্ড/popup/admin order entry-তে
    // ভ্যারিয়েবল প্রোডাক্টের দাম ডিটেইল পেজের চেয়ে আলাদা (প্রায়ই ০) দেখাত।
    // show.blade.php যেভাবে ডিফল্ট ভ্যারিয়েশন বাছাই করে ও দাম হিসাব করে, এখানেও সেটাই করা হলো।
    if(($product->type ?? 'single') === 'variable'){
        if(!$variation){
            $vars = $product->relationLoaded('variations') ? $product->variations : collect();

            // eager-load এ variations কে কম কলামে টানা হলে (যেমন
            // `variations:id,product_id,stock_quantity`) দামের ঘরই আসে না, তখন হিসাব
            // করলে ০ বেরোত। আগে থেকে লোড করা রো-তে price না থাকলে তাই একবার নতুন
            // করে টেনে নিই। কলামের নাম হাতে লিখি না — সাইটভেদে variations টেবিলে
            // discount_price / is_default থাকতেও পারে, না-ও থাকতে পারে।
            if($vars->isEmpty() || !array_key_exists('price', $vars->first()->getAttributes())){
                $vars = $product->variations()->get();
            }

            if($vars->isNotEmpty()){
                $variation = $vars->first();
                foreach($vars as $v){
                    if(!empty($v->is_default) && (int)$v->is_default === 1){
                        $variation = $v; break;
                    }
                }
            }
        }

        if($variation){
            $base  = (float)($variation->price ?? 0);
            $after = (float)($variation->after_discount_price ?? 0);
            $disc  = (float)($variation->discount_price ?? 0);

            if($after > 0 && $after < $base) $final = $after;
            elseif($disc > 0 && $disc < $base) $final = $disc;
            else $final = $base;

            return [
                'price'            => $final,
                'discount_amount'  => ($final < $base) ? round($base - $final, 2) : 0,
                'old_price'        => $base,
            ];
        }
    }

    $price=($product->after_discount  > 0) ? $product->after_discount : $product->sell_price;
    $discount_amount=$product->dicount_amount;

    $old_price=$product->sell_price;

    return ['price'=>$price,'discount_amount'=>$discount_amount,'old_price'=>$old_price];
}

function getSectionLists(){
    return ['0'=>'None','1'=>'Trending','2'=>'Hot Deals','3'=>'Recommended','4'=>'Top Brand'];
}

function getOrderStatus($type=""){
    return [
        ''                 => 'All Order',
        'Pending'          => 'Pending',
        'Incomplete'       => 'Incomplete',
        'On Hold'          => 'On Hold',
        'Scheduled'        => 'Scheduled',
        'Confirmed'        => 'Confirmed',
        'Cancelled'        => 'Cancelled',
        'Processing'       => 'Processing',
        'Courier Complete' => 'Courier Complete',
        'Shipped'          => 'Shipped',
        'Delivered'        => 'Delivered',
        'Returning'        => 'Returning',
        'Return Received'  => 'Return Received',
        'Return Missing'   => 'Return Missing'
    ];
}

/**
 * Maps any casing/spacing variant of a known status ('pending', ' PENDING ')
 * to its canonical key ('Pending'). Unknown values pass through unchanged.
 * The website checkout used to save lowercase statuses which then failed to
 * match the admin dropdowns — this keeps every writer consistent.
 */
function canonicalOrderStatus($value){
    if (!is_string($value) || trim($value) === '') return $value;

    $v = trim($value);
    foreach (array_keys(getOrderStatus()) as $key) {
        if ($key !== '' && strcasecmp($key, $v) === 0) return $key;
    }
    return $v;
}

/**
 * Generates a 6-digit invoice number guaranteed not to collide with an
 * existing order. Replaces bare rand(111111,999999) which could hand two
 * simultaneous orders the same number.
 */
function generateUniqueInvoiceNo(){
    do {
        $candidate = (string) random_int(111111, 999999);
    } while (\App\Models\Order::withTrashed()->where('invoice_no', $candidate)->exists());
    return $candidate;
}

function getPaymentStatus(){
    return [
        'Unpaid'   => 'Unpaid',
        'Partial'  => 'Partial',
        'Paid'     => 'Paid',
        'Refunded' => 'Refunded'
    ];
}

function getOrderMethod(){
    return ['cash'=>'Cash','Card'=>'Card'];
}

function SendSms($number=null,$message=null){
    $data = [
            'user' => 'sahaalfash',
            'pwd' => '66pueu99',
            'senderid' => '8809617611152', 
            'CountryCode' => '+880',
            'mobileno' => $number,   
            'msgtext' => $message
    ];
    $query = http_build_query($data);
    $url = "http://mshastra.com/sendurl.aspx?$query";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $curl_scraped_page =  curl_exec($ch);
    curl_close($ch);
    return $curl_scraped_page;
}

function getPageName(){
    return ['about-us'=>'About Us','return-policy'=>'Return Policy','privacy-policy'=>'Privacy Policy','terms-condition'=>'Term And Condition'];
}

function getCouponDiscount(){
    $coupon=session()->get('coupon_discount');
    $type=session()->get('discount_type');
  
    $cart = session()->get('cart');
    $total=0;
    $amount=0;
    
    if($cart){
        foreach($cart as $id=>$item){
            $total +=$item['price'] * $item['quantity'];
        }
    }
  
    if($type=='fixed'){
        $amount=$coupon;
    }else{
        $amount=(($total*$coupon)/ 100);
    }
    
    if(($total >0) and ($coupon)){
        $amount=$amount;
    }
    
    return round($amount);
}

function full_name($user)
{
    if($user)
    {
        return $user->first_name.' '.$user->last_name;
    }
    
    return '';
}

function BanglaText($index)
{      
  $bangla_text = array(
    "cust_info"             =>"কাস্টমার ইনফরমেশন",
    "offer"                 => "মেগা অফার",
    'tk'                    => "টাকা",
    "do_order"              => "অর্ডার করতে ক্লিক করুন",
    "instruction"           =>"অর্ডার কনফার্ম করতে আপনার নাম, ঠিকানা, মোবাইল নাম্বার লিখে অর্ডার কনফার্ম করুন বাটনে ক্লিক করুন",
    "name"                  => "আপনার নাম",
    "placeholder_name"      => "আপনার নাম লিখুন",
    "mobile"                => "আপনার মোবাইল নাম্বার",
    "placeholder_mobile"    => "আপনার  মোবাইল নাম্বার লিখুন",
    "address"               => "আপনার সম্পূর্ন ঠিকানা",
    "placeholder_address"   => "",
    "delivery_zone"         => "ডেলিভারি এলাকা নির্বাচন করুন",
    "confirm_order"         => "অর্ডার কনফার্ম করুন",
    "alert"                 => "অনুগ্রহ করে পার্সেল রিসিভ করার ব্যাপারে ১০০% নিশ্চিত হয়েই অর্ডার করুন। আপনার সহযোগিতা আমাদের ডেলিভারি প্রক্রিয়াকে আরও নির্ভুল ও দ্রুত করতে সাহায্য করবে।",
    "order_information"     => "অর্ডার ইনফরমেশন",
    "order"                 => "অর্ডার করুন",
    "land_order"            => "অর্ডার করতে চাই",
    "cart"                  => "কার্টে যোগ করুন",
    "land_instruction"      => "অর্ডার করতে নিচের ফর্মটি সঠিক তথ্য দিয়ে পূরন করুন",
    "order_ensure"          => "১০০% শিউর হয়ে অর্ডার করুন" 
    );
  return $bangla_text[$index]; 
}

function logActivity($action, $module, $description, $order_id = null, $old_data = null, $new_data = null) {
    try {
        if (!\Illuminate\Support\Facades\Cache::has('daily_log_cleanup')) {
            \App\Models\ActivityLog::where('created_at', '<', now()->subDays(10))->delete();
            \Illuminate\Support\Facades\Cache::put('daily_log_cleanup', true, now()->addHours(24));
        }

        \App\Models\ActivityLog::create([
            'user_id'     => auth()->check() ? auth()->id() : null,
            'action'      => $action,
            'module'      => $module,
            'description' => $description,
            'order_id'    => $order_id,
            'old_data'    => $old_data,
            'new_data'    => $new_data,
            'url'         => request()->fullUrl(),
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);
    } catch (\Exception $e) {
    }
}
/**
 * The thank-you pages (/confirm-order/{id}) are shown to guests right after
 * checkout, so they cannot require a login — but the order id was previously
 * the only thing guarding them, which made every customer's name, address and
 * phone number readable by counting upwards. Each checkout now records the
 * order it just created in the visitor's own session, and the page will only
 * render an order that is either in that list or owned by the logged-in user.
 */
if (!function_exists('rememberPlacedOrder')) {
    function rememberPlacedOrder($orderId)
    {
        if (empty($orderId)) return;

        $ids = session()->get('placed_orders', []);
        if (!in_array((int) $orderId, $ids, true)) {
            $ids[] = (int) $orderId;
        }

        // Keep the tail only; a session never needs a long history here.
        session()->put('placed_orders', array_slice($ids, -20));
    }
}

if (!function_exists('canViewPlacedOrder')) {
    function canViewPlacedOrder($order)
    {
        if (!$order) return false;

        if (auth()->check() && (int) $order->user_id === (int) auth()->id()) {
            return true;
        }

        // Staff can already read every order in the admin panel, so the
        // frontend invoice must not 404 for them (support staff routinely
        // open customers' links). Same role line as StaffOnly middleware.
        if (auth()->check() && method_exists(auth()->user(), 'getRoleNames')) {
            $staffRoles = auth()->user()->getRoleNames()
                ->map(fn ($r) => strtolower(trim($r)))
                ->reject(fn ($r) => in_array($r, ['user', 'customer', 'client']));
            if ($staffRoles->isNotEmpty()) {
                return true;
            }
        }

        // Tokenized link (?t=...): keeps working after the session dies and on
        // any device, while ids alone still can't be enumerated. hash_equals
        // keeps the comparison timing-safe.
        $t = (string) request()->query('t', '');
        if ($t !== '' && !empty($order->order_token) && hash_equals((string) $order->order_token, $t)) {
            return true;
        }

        return in_array((int) $order->id, session()->get('placed_orders', []), true);
    }
}

/**
 * Admin-set coupon usage caps. Returns the customer-facing error message when
 * a cap is exhausted, null when the coupon may be used. Customer identity is
 * the mobile number (most buyers are guests); when it is not known yet, only
 * the total cap can be checked. Cancelled and incomplete orders do not count
 * as a use.
 */
if (!function_exists('couponLimitProblem')) {
    function couponLimitProblem($couponCode, $mobile = null)
    {
        if (empty($couponCode)) return null;
        if (!\Illuminate\Support\Facades\Schema::hasColumn('orders', 'coupon_code')) return null;

        $coupon = \App\Models\CouponCode::where('code', $couponCode)->first();
        if (!$coupon) return null;

        $used = \App\Models\Order::where('coupon_code', $coupon->code)
            ->whereNotIn('status', ['Incomplete', 'Cancelled']);

        $total = (int) ($coupon->total_limit ?? 0);
        if ($total > 0 && (clone $used)->count() >= $total) {
            return 'দুঃখিত, এই কুপনের ব্যবহারের সীমা শেষ হয়ে গেছে!';
        }

        $per = (int) ($coupon->per_customer_limit ?? 0);
        if ($per > 0 && !empty($mobile) && (clone $used)->where('mobile', $mobile)->count() >= $per) {
            return 'এই কুপনটি আপনি ইতিমধ্যে সর্বোচ্চ সংখ্যকবার ব্যবহার করেছেন!';
        }

        return null;
    }
}

/**
 * Confirm-order / invoice URL that guests can open any time: the order's
 * token rides along as ?t=. Falls back to the plain URL for orders that
 * somehow have no token (then the session grant still applies).
 */
if (!function_exists('confirmOrderUrl')) {
    function confirmOrderUrl($order, $landing = false)
    {
        $name   = $landing ? 'front.confirmOrderlanding' : 'front.confirmOrder';
        $params = ['id' => $order->id];
        if (!empty($order->order_token)) {
            $params['t'] = $order->order_token;
        }
        return route($name, $params);
    }
}

/**
 * Product cards and cart rows render at 150-300px, but were serving the full
 * 1200px image (~500KB each; a homepage shows ~72 of them). Uploads since the
 * thumbnail feature exist in thumb_products/ (~47KB); older ones are generated
 * by /optimize-images. Falls back to the full image so a missing thumb can
 * never break a card.
 */
if (!function_exists('getThumbImage')) {
    function getThumbImage($value = null)
    {
        if (!empty($value)) {
            // /optimize-images leaves a webp sibling next to heavy PNG thumbs
            if (file_exists(public_path('thumb_products/' . $value . '.webp'))) {
                return asset('thumb_products/' . $value . '.webp');
            }
            if (file_exists(public_path('thumb_products/' . $value))) {
                return asset('thumb_products/' . $value);
            }
            if (file_exists(public_path('products/' . $value))) {
                return asset('products/' . $value);
            }
        }
        return asset('images/no_found.png');
    }
}

/**
 * Banner/section uploads were moved to disk untouched — phone-camera originals
 * of 4-6MB ended up served to every visitor. Caps the width and recompresses;
 * returns the stored filename. Falls back to a plain move for anything GD
 * cannot decode.
 */
if (!function_exists('saveCompressedUpload')) {
    function saveCompressedUpload($file, $folder, $maxWidth = 1600, $quality = 80)
    {
        $originName = $file->getClientOriginalName();
        $fileName   = pathinfo($originName, PATHINFO_FILENAME);
        $extension  = $file->getClientOriginalExtension();
        $fileName   = $fileName . time() . '.' . $extension;

        $destination = public_path($folder);
        if (!is_dir($destination)) {
            @mkdir($destination, 0755, true);
        }

        try {
            $image = \Intervention\Image\Facades\Image::make($file);
            if ($image->width() > $maxWidth) {
                $image->resize($maxWidth, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            $image->save($destination . '/' . $fileName, $quality);
        } catch (\Throwable $e) {
            $file->move($destination, $fileName);
        }

        return $fileName;
    }
}

if (!function_exists('fittedImage')) {
    /**
     * ব্যানার/স্লাইডারের ছবিগুলো লেআউটের বক্সের সাথে এক মাপের নয় — কোনোটা 3:1,
     * কোনোটা বর্গাকার। object-fit:cover দিলে অর্ধেক কেটে যেত, contain দিলে বড়
     * ফাঁকা বার পড়ত। তাই আগেই বক্সের মাপে ফিট করানো কপি বানিয়ে রাখা হয়েছে
     * ({dir}/fit/), যেখানে আসল ছবিটা পুরো থাকে আর চারপাশ ওই ছবিরই ঝাপসা কপিতে ভরা।
     * কপিটা না থাকলে চুপচাপ আসল ছবিতেই ফিরে যায়।
     */
    function fittedImage(string $dir, ?string $file, string $fitSub = 'fit'): string
    {
        if (empty($file)) {
            return asset($dir . '/');
        }

        $fitted = $dir . '/' . $fitSub . '/' . $file;

        return file_exists(public_path($fitted))
            ? asset($fitted)
            : asset($dir . '/' . $file);
    }
}
