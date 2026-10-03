<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Order;
use App\Models\Information;

class EPSController extends Controller
{
    use \App\Http\Traits\FiresPurchaseEvent;

    private function getEpsInfo()
    {
        $info = Information::first();
        if (!$info || $info->eps_active != 1) {
            abort(redirect()->route('front.home')->with('error', 'EPS Payment is currently unavailable.'));
        }
        return $info;
    }

    /**
     * Get dynamic credentials based on Sandbox/Live mode
     */
    private function getEpsCredentials($info)
    {
        $isSandbox = (isset($info->eps_sandbox) && $info->eps_sandbox == 1);

        return [
            'merchantId' => trim($isSandbox ? $info->eps_sandbox_merchant_id : $info->eps_merchant_id),
            'storeId'    => trim($isSandbox ? $info->eps_sandbox_store_id : $info->eps_store_id),
            'username'   => trim($isSandbox ? $info->eps_sandbox_username : $info->eps_username),
            'password'   => trim($isSandbox ? $info->eps_sandbox_password : $info->eps_password),
            'hashKey'    => trim($isSandbox ? $info->eps_sandbox_hash_key : $info->eps_hash_key),
            'baseUrl'    => $isSandbox ? 'https://sandboxpgapi.eps.com.bd/v1' : 'https://pgapi.eps.com.bd/v1',
        ];
    }

    /**
     * EPS Mechanism for Hash
     */
    private function generateHash($dataToHash, $hashKey)
    {
        // Step 1: Encode Hash Key using UTF8
        $key = mb_convert_encoding(trim($hashKey), 'UTF-8');
        $data = mb_convert_encoding(trim($dataToHash), 'UTF-8');

        // Step 2 & 3: Create HMACSHA512 using encoded data
        $hash = hash_hmac('sha512', $data, $key, true);

        // Step 4: Return Base64 string of Hash
        return base64_encode($hash);
    }

    private function getToken($epsConfig)
    {
        $xHash = $this->generateHash($epsConfig['username'], $epsConfig['hashKey']);

        // Force JSON format for the request
        $response = Http::withoutVerifying()
            ->asJson() // Explicitly format body as JSON
            ->withHeaders([
                'x-hash'       => $xHash,
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json' // Required by EPS
            ])->post("{$epsConfig['baseUrl']}/Auth/GetToken", [
                'userName' => $epsConfig['username'],
                'password' => $epsConfig['password']
            ]);

        if ($response->successful() && isset($response['token'])) {
            return $response->json('token');
        }

        \Log::error('EPS API Token Error. Response: ' . $response->body());
        abort(redirect()->route('front.home')->with('error', 'EPS Payment is temporarily unavailable. Please try another payment method.'));
    }

    public function pay(Request $request, $order_id = null)
    {
        $order = Order::find($order_id ?? $request->order_id);

        if(!$order) {
            return redirect()->route('front.home')->with('error', 'Order not found!');
        }

        $info = $this->getEpsInfo();
        $epsConfig = $this->getEpsCredentials($info);

        $merchantTransactionId = date('YmdHis') . rand(1000, 9999);
        $order->update(['transaction_id' => $merchantTransactionId, 'payment_method' => 'eps']);

        $token = $this->getToken($epsConfig);

        // InitializeEPS-এর জন্য merchantTransactionId দিয়ে হ্যাশ করতে হয়
        $xHash = $this->generateHash($merchantTransactionId, $epsConfig['hashKey']);

        $payload = [
            'merchantId'            => $epsConfig['merchantId'],
            'storeId'               => $epsConfig['storeId'],
            'CustomerOrderId'       => (string) $order->invoice_no,
            'merchantTransactionId' => $merchantTransactionId,
            'TransactionTypeId'     => 1, // 1 = Web
            'totalAmount'           => (float) $order->final_amount,
            'successUrl'            => route('eps.success'),
            'failUrl'               => route('eps.fail'),
            'cancelUrl'             => route('eps.cancel'),
            'customerName'          => $order->first_name ?? 'Customer',
            'customerEmail'         => $order->email ?? 'no-email@deshibazzar.shop',
            'customerAddress'       => $order->shipping_address ?? 'Dhaka',
            'customerCity'          => 'Dhaka',
            'customerState'         => 'Dhaka',
            'customerPostcode'      => '1000',
            'customerCountry'       => 'BD',
            'customerPhone'         => $order->mobile,
            'productName'           => 'Online Purchase'
        ];

        // Force JSON for initialization as well
        $response = Http::withoutVerifying()
            ->asJson()
            ->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'x-hash'        => $xHash,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json'
            ])->post("{$epsConfig['baseUrl']}/EPSEngine/InitializeEPS", $payload);

        if ($response->successful()) {
            $responseData = $response->json();
            if(!empty($responseData['RedirectURL'])) {
                return redirect($responseData['RedirectURL']);
            }
        }

        \Log::error('EPS Initialization Failed. Status: ' . $response->status() . ' Body: ' . $response->body());
        return redirect()->route('front.home')->with('error', 'EPS Payment initialization failed. Please try another payment method.');
    }

    public function success(Request $request)
    {
        $merchantTransactionId = $request->input('MerchantTransactionId') ?? $request->input('merchantTransactionId');

        if (!$merchantTransactionId) {
            return redirect()->route('front.home')->with('error', 'Invalid Request!');
        }

        $info = $this->getEpsInfo();
        $epsConfig = $this->getEpsCredentials($info);

        $token = $this->getToken($epsConfig);
        $xHash = $this->generateHash($merchantTransactionId, $epsConfig['hashKey']);

        $response = Http::withoutVerifying()->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'x-hash'        => $xHash,
            'Accept'        => 'application/json',
        ])->get("{$epsConfig['baseUrl']}/EPSEngine/CheckMerchantTransactionStatus", [
            'merchantTransactionId' => $merchantTransactionId
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if(isset($data['Status']) && strtolower($data['Status']) == 'success') {
                $order = Order::where('transaction_id', $merchantTransactionId)->first();
                if ($order) {
                    // Best-effort amount verification: if EPS returns the paid
                    // amount under any known key, make sure it covers the order.
                    $paidAmount = null;
                    foreach (['Amount', 'TransactionAmount', 'amount', 'MerchantAmount', 'PaidAmount'] as $k) {
                        if (isset($data[$k]) && is_numeric($data[$k])) { $paidAmount = (float) $data[$k]; break; }
                    }
                    if ($paidAmount !== null && $paidAmount + 1 < (float) $order->final_amount) {
                        \Log::warning('EPS amount mismatch', ['order' => $order->id, 'paid' => $paidAmount, 'due' => $order->final_amount]);
                        return redirect()->route('front.home')->with('error', 'Paid amount does not match the order total. Please contact support.');
                    }

                    $order->update(['status' => 'confirmed', 'payment_status' => 'paid']);

                    // Fires only now that EPS has confirmed the payment.
                    $this->firePurchaseEvents($order);
                    session()->forget(['cart', 'coupon_discount', 'applied_coupon_code', 'otp_verified', 'order_token']);
                    rememberPlacedOrder($order->id);
                    return redirect(confirmOrderUrl($order));
                }
            }
        }

        return redirect()->route('front.home')->with('error', 'Payment Verification Failed!');
    }

    public function fail(Request $request) { 
        return redirect()->route('front.home')->with('error', 'Payment Failed!'); 
    }
    
    public function cancel(Request $request) { 
        return redirect()->route('front.home')->with('warning', 'Payment Cancelled!'); 
    }
}