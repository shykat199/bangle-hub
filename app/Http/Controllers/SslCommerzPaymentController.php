<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Library\SslCommerz\SslCommerzNotification;
use App\Models\Information;
use App\Models\DeliveryCharge;
use App\Models\User;
use App\Models\Order;
use App\Jobs\SendOrderNotification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Session;

class SslCommerzPaymentController extends Controller
{
    use \App\Http\Traits\FiresPurchaseEvent;

    /**
     * ✅ SSLCommerz Configuration Setup (Dynamic from Database)
     */
    private function setSslConfig()
    {
        $info = Information::first(); 

        $store_id = null;
        $store_pass = null;
        $is_sandbox = true; 

        if ($info) {
            $is_sandbox = ($info->ssl_sandbox == 1); 

            if ($is_sandbox) {
                $store_id = $info->ssl_sandbox_store_id;
                $store_pass = $info->ssl_sandbox_store_password;
            } else {
                $store_id = $info->ssl_store_id;
                $store_pass = $info->ssl_store_password;
            }
        }

        $domain = $is_sandbox ? 'https://sandbox.sslcommerz.com' : 'https://securepay.sslcommerz.com';

        config(['sslcommerz.apiCredentials.store_id' => $store_id]);
        config(['sslcommerz.apiCredentials.store_password' => $store_pass]);
        config(['sslcommerz.apiDomain' => $domain]);
        config(['sslcommerz.connect_from_localhost' => $is_sandbox]);
        config(['sslcommerz.sandbox' => $is_sandbox]);
    }

    /** ===============================
     * ✅ WORKER ASSIGNMENT LOGIC
     * =============================== */
    private function getActiveWorkerIds()
    {
        return User::query()
            ->where(function ($q) {
                $q->whereNull('status')
                    ->orWhereIn('status', [1, '1', true, 'true', 'active', 'Active']);
            })
            ->when(
                Schema::hasColumn((new User)->getTable(), 'deleted_at'),
                fn($q) => $q->whereNull('deleted_at')
            )
            ->whereHas('roles', fn($q) => $q->whereRaw('LOWER(name)=?', ['worker']))
            ->orderBy('id')
            ->pluck('id');
    }

    private function pickNextWorkerId()
    {
        $activeIds = $this->getActiveWorkerIds();
        
        if ($activeIds->isEmpty()) {
            return 1;
        }

        $candidateId = DB::table('users as u')
            ->join('model_has_roles as m', 'm.model_id', '=', 'u.id')
            ->join('roles as r', 'r.id', '=', 'm.role_id')
            ->leftJoin('orders as o', function ($join) {
                $join->on('o.assign_user_id', '=', 'u.id')
                    ->whereDate('o.created_at', DB::raw('CURDATE()'));
            })
            ->whereIn('u.id', $activeIds->toArray())
            ->whereRaw('LOWER(r.name) = ?', ['worker'])
            ->where(function ($q) {
                $q->whereNull('u.status')
                    ->orWhereIn('u.status', [1, '1', true, 'true', 'active', 'Active']);
            })
            ->groupBy('u.id')
            ->orderByRaw('COUNT(o.id) ASC')
            ->orderBy('u.id', 'ASC')
            ->value('u.id');

        return (int) ($candidateId ?? $activeIds->first());
    }

    public function index(Request $request)
    {
        $this->setSslConfig();

        if (empty(config('sslcommerz.apiCredentials.store_id'))) {
            return back()->with('error', 'Payment Gateway Config Missing!');
        }

        if(!$request->amount || !$request->mobile){
            return back()->with('error', 'Amount or Mobile number is missing!');
        }

        // ১. চার্জ এবং ডিসকাউন্ট হিসাব — মূল চেকআউটের সাথে এক সূত্রে, যাতে
        // ফ্রি-শিপিং প্রোডাক্ট, ওজনভিত্তিক কুরিয়ার রেট আর percentage কুপন
        // সব ক্ষেত্রেই কাস্টমার যে টাকা দেখে সেটাই SSL-এ চার্জ হয়।
        $checkout = app(\App\Http\Controllers\Frontend\CheckoutController::class);

        $qty = (int) ($request->quantity > 0 ? $request->quantity : 1);
        $shipping_charge = $checkout->calculateShippingCharge($request, $request->prd_id, $qty);

        // কার্ট চেকআউটে প্রোডাক্ট টোটাল সবসময় সার্ভারের কার্ট থেকে হিসাব হয় —
        // ফর্মের amount ফিল্ডে JS সর্বমোট বিল (শিপিং ও কুপনসহ) বসায়, সেটা
        // নিলে শিপিং/কুপন দুইবার হিসাব হয়ে ভুল টাকা চার্জ হতো। ল্যান্ডিং পেজ
        // (prd_id সহ) হলে আগের মতোই রিকোয়েস্টের amount = প্যাকেজ দাম।
        $cartItems = session()->get('cart', []);
        $product_total = 0;
        if (empty($request->prd_id) && !empty($cartItems)) {
            foreach ($cartItems as $item) {
                $product_total += $item['price'] * $item['quantity'];
            }
        } else {
            $product_total = (float) $request->amount;
        }

        $coupon_discount = $checkout->couponDiscountValue($product_total);

        $final_total_amount = ($product_total + $shipping_charge) - $coupon_discount;

        $post_data = array();
        $post_data['total_amount'] = $final_total_amount;
        $post_data['currency'] = "BDT";
        $post_data['tran_id'] = uniqid(); 

        $post_data['cus_name'] = $request->first_name ?? 'Customer';
        $post_data['cus_email'] = $request->email ?? 'N/A';
        $post_data['cus_add1'] = $request->shipping_address ?? 'Dhaka';
        $post_data['cus_city'] = "";
        $post_data['cus_country'] = "Bangladesh";
        $post_data['cus_phone'] = $request->mobile;

        $post_data['ship_name'] = "Store Test";
        $post_data['ship_add1'] = "Dhaka";
        $post_data['ship_city'] = "Dhaka";
        $post_data['ship_country'] = "Bangladesh";

        $post_data['shipping_method'] = "NO";
        $post_data['product_name'] = "Online Product";
        $post_data['product_category'] = "Goods";
        $post_data['product_profile'] = "physical-goods";

        $assignUserId = $this->pickNextWorkerId();

        DB::beginTransaction();
        try {
            $userId = auth()->id() ?? 0;

            // =========================================================
            // ১. চেক করি এই মোবাইল নাম্বারের কোনো Incomplete অর্ডার আছে কি না
            // =========================================================
            $existingOrder = DB::table('orders')
                ->where('mobile', $post_data['cus_phone'])
                ->where('status', 'incomplete')
                ->orderBy('id', 'desc')
                ->first();

            $orderData = [
                'invoice_no'        => $existingOrder ? $existingOrder->invoice_no : generateUniqueInvoiceNo(),
                'user_id'           => $userId,
                'first_name'        => $post_data['cus_name'],
                'mobile'            => $post_data['cus_phone'],
                'email'             => $request->email,
                'shipping_address'  => $post_data['cus_add1'],
                'amount'            => $product_total,          
                'discount'          => $coupon_discount,      
                'shipping_charge'   => $shipping_charge,      
                'final_amount'      => $final_total_amount, 
                'status'            => 'Pending', // Incomplete হলে Pending এ আপডেট হবে
                'payment_status'    => 'Unpaid',
                'transaction_id'    => $post_data['tran_id'],
                'currency'          => $post_data['currency'],
                'payment_method'    => 'sslcommerz',
                'assign_user_id'    => $assignUserId, 
                'date'              => date('Y-m-d'),
                'updated_at'        => now(),
            ];

            $order_id = null;

            if ($existingOrder) {
                // যদি Incomplete অর্ডার থাকে, তবে সেটিই আপডেট হবে
                DB::table('orders')->where('id', $existingOrder->id)->update($orderData);
                $order_id = $existingOrder->id;
            } else {
                // না থাকলে নতুন তৈরি হবে
                $orderData['created_at'] = now();
                $order_id = DB::table('orders')->insertGetId($orderData);
            }

            // =========================================================
            // ২. কার্ট আইটেম বা ল্যান্ডিং পেজ আইটেম সেভ (FIXED)
            // =========================================================
            $cart = session()->get('cart', []);
            
            // ✅ যদি ল্যান্ডিং পেজ থেকে সরাসরি অর্ডার আসে (request-এ prd_id থাকলে)
            if ($request->has('prd_id') && !empty($request->prd_id)) {
                
                // আগের অসম্পূর্ণ ডিটেইলস মুছে সর্বশেষ ডেটা ইনসার্ট করা হচ্ছে
                DB::table('order_details')->where('order_id', $order_id)->delete();
                
                $qty = (int) ($request->quantity > 0 ? $request->quantity : 1);
                $unitPrice = $product_total / $qty; 
                
                DB::table('order_details')->insert([
                    'order_id'       => $order_id,
                    'product_id'     => $request->prd_id,
                    'quantity'       => $qty,
                    'unit_price'     => $unitPrice,
                    'purchase_price' => 0, 
                    'discount'       => 0,
                    'variation_id'   => $request->variation_id ?: null, // ✅ ভেরিয়েন্ট সেভ হচ্ছে
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            } 
            // ✅ যদি সাধারণ কার্ট থেকে অর্ডার আসে
            elseif (!empty($cart)) {
                DB::table('order_details')->where('order_id', $order_id)->delete();

                $orderDetailsData = [];
                foreach ($cart as $item) {
                    $orderDetailsData[] = [
                        'order_id'       => $order_id,
                        'product_id'     => $item['product_id'],
                        'quantity'       => $item['quantity'],
                        'unit_price'     => $item['price'],
                        'purchase_price' => $item['purchase_price'] ?? 0,
                        'discount'       => $item['discount'] ?? 0,
                        'variation_id'   => $item['variation_id'] ?? null,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ];
                }
                
                if (!empty($orderDetailsData)) {
                    DB::table('order_details')->insert($orderDetailsData);
                }
            }

            DB::commit(); 

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Database Error: " . $e->getMessage());
        }

        $sslc = new SslCommerzNotification();
        $payment_options = $sslc->makePayment($post_data, 'hosted');

        if (!is_array($payment_options)) {
            print_r($payment_options);
            $payment_options = array();
        }
    }

    /**
     * SSL অর্ডার তৈরি হয় পেমেন্টের আগে, তাই স্টক কমে পেমেন্ট কনফার্ম হওয়ার
     * পরে (success/IPN)। দুটোই status=Pending গার্ডের ভেতরে চলে বলে একই
     * অর্ডারের স্টক দুইবার কমার সুযোগ নেই।
     */
    private function decrementStockForOrder($orderId)
    {
        try {
            $details = DB::table('order_details')->where('order_id', $orderId)->get();
            $util = new \App\Utils\Util();

            foreach ($details as $line) {
                $util->decreaseProductStock($line->product_id, $line->variation_id ?? null, $line->quantity);
            }
        } catch (\Exception $e) {
            \Log::error('SSL stock decrement failed for Order ID: ' . $orderId . ' — ' . $e->getMessage());
        }
    }

    public function success(Request $request)
    {
        $this->setSslConfig();

        $tran_id = $request->input('tran_id');
        $amount = $request->input('amount');
        $currency = $request->input('currency');

        $sslc = new SslCommerzNotification();

        $order_details = DB::table('orders')->where('transaction_id', $tran_id)->first();

        if (!$order_details) {
            return redirect('/')->with('error', 'Invalid Transaction ID');
        }

        if ($order_details->status == 'Pending') {
            $validation = $sslc->orderValidate($request, $tran_id, $amount, $currency);

            if ($validation) {
                DB::table('orders')->where('transaction_id', $tran_id)->update([
                    'status' => 'Confirmed', // ✅ Processing থেকে Confirmed করা হয়েছে
                    'payment_status' => 'Paid'
                ]);

                $this->decrementStockForOrder($order_details->id);

                // Payment is confirmed here, so this is the first moment the
                // sale is real — the Purchase conversion belongs here, not at
                // order creation where the customer had not paid yet.
                $this->firePurchaseEvents($order_details->id);

                // ✅✅✅ NEW: পেমেন্ট সফল হলে ইমেইল ও মেসেজ পাঠানোর আগে সেটিংস চেক
                $order = Order::find($order_details->id);
                $info = Information::first();
                if ($order && $info && $info->notification_active == 1) {
                    SendOrderNotification::dispatch($order);
                }
                // ===============================================
                
                // ✅ FIXED: Complete Session Clear Here (For Online Payment)
                session()->forget(['cart', 'coupon_discount', 'discount_type', 'applied_coupon_code', 'otp_verified', 'order_token']);

                rememberPlacedOrder($order_details->id);
                return redirect(confirmOrderUrl($order_details));
            }
        } else if ($order_details->status == 'Confirmed' || $order_details->status == 'Processing' || $order_details->status == 'Complete') {
            
            // ✅ FIXED: Complete Session Clear Here (If already Processed)
            session()->forget(['cart', 'coupon_discount', 'discount_type', 'applied_coupon_code', 'otp_verified', 'order_token']);

            return redirect(confirmOrderUrl($order_details));
        } else {
            DB::table('orders')->where('transaction_id', $tran_id)->update(['status' => 'Failed']);
            return $this->loadFailedView($order_details);
        }
        
        return $this->loadFailedView($order_details);
    }

    public function fail(Request $request)
    {
        $tran_id = $request->input('tran_id');
        $order_details = DB::table('orders')->where('transaction_id', $tran_id)->first();
        if ($order_details && $order_details->status == 'Pending') {
            DB::table('orders')->where('transaction_id', $tran_id)->update(['status' => 'Failed']);
        }
        return $this->loadFailedView($order_details);
    }

    public function cancel(Request $request)
    {
        $tran_id = $request->input('tran_id');
        $order_details = DB::table('orders')->where('transaction_id', $tran_id)->first();
        if ($order_details && $order_details->status == 'Pending') {
            DB::table('orders')->where('transaction_id', $tran_id)->update(['status' => 'Canceled']);
        }
        return $this->loadFailedView($order_details);
    }

    public function ipn(Request $request)
    {
        $this->setSslConfig();
        
        if ($request->input('tran_id')) {
            $tran_id = $request->input('tran_id');
            $order_details = DB::table('orders')->where('transaction_id', $tran_id)->first();
            
            if ($order_details && $order_details->status == 'Pending') {
                $sslc = new SslCommerzNotification();
                if ($sslc->orderValidate($request, $tran_id, $order_details->amount, $order_details->currency)) {
                    DB::table('orders')->where('transaction_id', $tran_id)->update([
                        'status' => 'Confirmed', // ✅ Processing থেকে Confirmed করা হয়েছে
                        'payment_status' => 'Paid'
                    ]);

                    $this->decrementStockForOrder($order_details->id);

                    $this->firePurchaseEvents($order_details->id);

                    // ✅✅✅ NEW: IPN থেকে পেমেন্ট সফল হলেও মেসেজ পাঠানোর আগে সেটিংস চেক
                    $order = Order::find($order_details->id);
                    $info = Information::first();
                    if ($order && $info && $info->notification_active == 1) {
                        SendOrderNotification::dispatch($order);
                    }
                    // ===============================================

                    echo "Transaction is successfully Completed";
                }
            }
        }
    }

    private function loadFailedView($order_details = null)
    {
        if (view()->exists('frontend.dashboard.payment_failed')) {
            return view('frontend.dashboard.payment_failed', ['order' => $order_details]);
        }
        return redirect('/')->with('error', 'Payment Failed or Canceled');
    }
}