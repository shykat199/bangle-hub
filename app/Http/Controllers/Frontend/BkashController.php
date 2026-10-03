<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Information;

class BkashController extends Controller
{
    use \App\Http\Traits\FiresPurchaseEvent;

    private function getBkashConfig()
    {
        $info = Information::first();
        $isSandbox = $info->bkash_sandbox ?? 1; 
        
        return [
            'base_url'   => $isSandbox ? 'https://checkout.sandbox.bka.sh/v1.2.0-beta' : 'https://checkout.pay.bka.sh/v1.2.0-beta',
            'app_key'    => $info->bkash_app_key ?? env('BKASH_APP_KEY'),
            'app_secret' => $info->bkash_app_secret ?? env('BKASH_APP_SECRET'),
            'username'   => $info->bkash_username ?? env('BKASH_USERNAME'),
            'password'   => $info->bkash_password ?? env('BKASH_PASSWORD'),
            'is_active'  => $info->bkash_active ?? 0 
        ];
    }

    private function getToken()
    {
        session()->forget('bkash_token');
        $config = $this->getBkashConfig();

        if (!$config['is_active']) {
            return null; 
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'username'     => $config['username'],
            'password'     => $config['password'],
        ])->post($config['base_url'] . '/checkout/token/grant', [
            'app_key'    => $config['app_key'],
            'app_secret' => $config['app_secret'],
        ]);

        if ($response->successful()) {
            $token = $response->json('id_token');
            session()->put('bkash_token', $token);
            return $token;
        }
        
        Log::error('bKash Token Error: ' . $response->body());
        return null;
    }

    public function createPayment(Request $request)
    {
        $config = $this->getBkashConfig();
        $token = $this->getToken();

        if (!$token) {
            return response()->json(['errorMessage' => 'bKash Gateway is currently Disabled or Credentials Invalid.'], 400);
        }

        $order = Order::findOrFail($request->order_id);

        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Authorization' => $token,
            'X-APP-Key'     => $config['app_key'],
        ])->post($config['base_url'] . '/checkout/payment/create', [
            'amount'                => $order->final_amount,
            'currency'              => 'BDT',
            'intent'                => 'sale',
            'merchantInvoiceNumber' => $order->invoice_no,
        ]);

        return $response->json();
    }

    public function executePayment(Request $request)
    {
        $config = $this->getBkashConfig();
        $token = session()->get('bkash_token');
        $paymentID = $request->paymentID;

        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Authorization' => $token,
            'X-APP-Key'     => $config['app_key'],
        ])->post($config['base_url'] . '/checkout/payment/execute/' . $paymentID);

        $result = $response->json();

        if (isset($result['transactionStatus']) && ($result['transactionStatus'] == 'Completed' || $result['transactionStatus'] == 'Authorized')) {
            $order = Order::where('invoice_no', $result['merchantInvoiceNumber'])->first();

            if ($order) {
                // Verify the charged amount against the order total before
                // confirming — bKash "Completed" alone is not proof of full payment.
                $paidAmount = (float) ($result['amount'] ?? 0);
                if ($paidAmount + 1 < (float) $order->final_amount) {
                    Log::warning('bKash amount mismatch', ['order' => $order->id, 'paid' => $paidAmount, 'due' => $order->final_amount]);
                    OrderPayment::create([
                        'order_id'       => $order->id,
                        'payment_method' => 'bKash PGW',
                        'amount'         => $paidAmount,
                        'transaction_id' => $result['trxID'] ?? null,
                        'status'         => 'amount_mismatch',
                    ]);
                    return $result;
                }

                $order->payment_status = 'Paid';
                $order->status = 'Confirmed';
                $order->save();

                OrderPayment::create([
                    'order_id'       => $order->id,
                    'payment_method' => 'bKash PGW',
                    'amount'         => $result['amount'],
                    'transaction_id' => $result['trxID'],
                    'status'         => 'success',
                ]);

                // bKash has taken the money — this is where the sale becomes
                // real, so the Purchase conversion fires here rather than at
                // order creation.
                $this->firePurchaseEvents($order);
                rememberPlacedOrder($order->id);
            }
        } else {
            Log::error('bKash Execute Failed: ' . json_encode($result));
        }

        return $result;
    }
}