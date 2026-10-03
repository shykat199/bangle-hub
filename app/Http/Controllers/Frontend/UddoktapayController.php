<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Information;

class UddoktapayController extends Controller
{
    use \App\Http\Traits\FiresPurchaseEvent;

    public function pay($id)
    {
        $info = Information::first();
        if (!$info || $info->uddoktapay_active != 1) {
            return redirect()->route('front.checkouts.index')->with("error", "UddoktaPay Gateway is disabled.");
        }

        $order = Order::findOrFail($id);

        if (empty($info->uddoktapay_base_url) || empty($info->uddoktapay_api_key)) {
            \Log::error('UddoktaPay credentials incomplete — payment aborted for order ' . $id);
            return redirect()->route('front.checkouts.index')->with("error", "UddoktaPay is not available right now. Please choose another payment method.");
        }

        $baseURL = rtrim($info->uddoktapay_base_url, '/') . '/';
        $apiKEY = $info->uddoktapay_api_key;

        $fields = [
            'full_name'    => $order->first_name . ' ' . $order->last_name,
            'email'        => $order->user->email ?? 'customer@example.com',
            'amount'       => number_format($order->final_amount, 2, '.', ''),
            'metadata'     => [
                'order_id' => $order->invoice_no
            ],
            'redirect_url' => route('uddoktapay.success'),
            'cancel_url'   => route('uddoktapay.cancel'),
            'webhook_url'  => ''
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $baseURL . "api/checkout-v2",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($fields),
            CURLOPT_HTTPHEADER => [
                "RT-UDDOKTAPAY-API-KEY: " . $apiKEY,
                "accept: application/json",
                "content-type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        $responseObject = json_decode($response, true);

        if (isset($responseObject['status']) && $responseObject['status'] == true && $responseObject['payment_url'] != null) {
            return redirect()->away($responseObject['payment_url']);
        }

        return redirect()->route('front.checkouts.index')->with('error', 'Payment Url Generation Failed!');
    }

    public function success(Request $request)
    {
        if ($request->has('invoice_id') && $request->invoice_id != null) {
            $info = Information::first();
            $baseURL = rtrim($info->uddoktapay_base_url, '/') . '/';
            $apiKEY = $info->uddoktapay_api_key;

            $fields = ['invoice_id' => $request->invoice_id];

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $baseURL . "api/verify-payment",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($fields),
                CURLOPT_HTTPHEADER => [
                    "RT-UDDOKTAPAY-API-KEY: " . $apiKEY,
                    "accept: application/json",
                    "content-type: application/json"
                ],
            ]);

            $response = curl_exec($curl);
            curl_close($curl);

            $responseObject = json_decode($response, true);
            
            if (isset($responseObject['status']) && $responseObject['status'] == 'COMPLETED') {
                $orderInvoice = $responseObject['metadata']['order_id'] ?? null;
                $order = Order::where('invoice_no', $orderInvoice)->first();

                if ($order) {
                    // Never confirm on "COMPLETED" alone — make sure the amount
                    // actually paid matches what the order is worth.
                    $paidAmount = (float) ($responseObject['amount'] ?? 0);
                    if ($paidAmount + 1 < (float) $order->final_amount) {
                        \Log::warning('UddoktaPay amount mismatch', ['order' => $order->id, 'paid' => $paidAmount, 'due' => $order->final_amount]);
                        OrderPayment::create([
                            'order_id'       => $order->id,
                            'payment_method' => 'UddoktaPay',
                            'amount'         => $paidAmount,
                            'transaction_id' => $request->invoice_id,
                            'status'         => 'amount_mismatch',
                        ]);
                        return redirect()->route('front.home')->with('error', 'Paid amount does not match the order total. Please contact support.');
                    }

                    $order->payment_status = 'Paid';
                    $order->status = 'Confirmed';
                    $order->transaction_id = $request->invoice_id;
                    $order->save();

                    OrderPayment::create([
                        'order_id'       => $order->id,
                        'payment_method' => 'UddoktaPay',
                        'amount'         => $paidAmount ?: $order->final_amount,
                        'transaction_id' => $request->invoice_id,
                        'status'         => 'success',
                    ]);

                    // UddoktaPay has verified the payment, so the sale is real.
                    $this->firePurchaseEvents($order);

                    rememberPlacedOrder($order->id);
                    return redirect(confirmOrderUrl($order, true));
                }
            }
        }

        return redirect()->route('front.home')->with('error', 'Payment Failed or Invalid!');
    }

    public function cancel()
    {
        return redirect()->route('front.home')->with('error', 'Payment Cancelled!');
    }
}