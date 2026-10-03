<?php

namespace App\Http\Traits;

use App\Facades\FacebookConversion;
use App\Facades\TikTokConversion;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

/**
 * Sends the Purchase conversion for an order — once, and only once money is
 * actually due or received.
 *
 * The event used to fire the moment an order row was created, which for an
 * online gateway is *before* the customer has paid a taka. Anyone who opened
 * the bKash/Nagad page and walked away was still reported to Meta and TikTok
 * as a sale, so Ads Manager showed a higher ROAS than the shop really had.
 * Cash on delivery is different — the order itself is the conversion there —
 * so that path still fires immediately.
 *
 * The event id stays 'PUR_<order id>', the same id the thank-you page's
 * browser pixel uses, so browser and server continue to deduplicate. The
 * order-scoped id is also what makes the guard below safe: even if two code
 * paths call this, Meta counts one Purchase.
 */
trait FiresPurchaseEvent
{
    // Payment methods where the money arrives through a gateway, so the sale
    // is only real once that gateway confirms it.
    private static array $onlinePaymentMethods = [
        'online', 'sslcommerz', 'ssl', 'bkash', 'nagad', 'eps', 'uddoktapay',
    ];

    protected function isOnlinePayment($paymentMethod): bool
    {
        return in_array(strtolower(trim((string) $paymentMethod)), self::$onlinePaymentMethods, true);
    }

    /**
     * Fires Purchase for a paid/confirmed order. Safe to call more than once:
     * the order carries a flag so a retried callback or a refreshed success
     * page does not send it again.
     */
    protected function firePurchaseEvents($order, array $userData = [], array $ttUserData = [], ?string $url = null): void
    {
        try {
            if (!$order) return;

            $order = $order instanceof Order ? $order : Order::find($order);
            if (!$order) return;

            // Fire-once guard. The column may not exist on older installs, in
            // which case Meta's own event-id deduplication is the only guard —
            // which is still correct, just less tidy.
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'purchase_event_sent')) {
                if ((int) $order->purchase_event_sent === 1) return;
                $order->forceFill(['purchase_event_sent' => 1])->saveQuietly();
            }

            $eventId  = 'PUR_' . $order->id;
            $contents = [];
            $ids      = [];

            foreach ($order->details as $line) {
                $ids[] = (string) $line->product_id;
                $contents[] = [
                    'id'         => (string) $line->product_id,
                    'quantity'   => (int) $line->quantity,
                    'item_price' => (float) $line->unit_price,
                ];
            }

            if (empty($userData) && !empty($order->mobile)) {
                $phone = preg_replace('/\D+/', '', $order->mobile);
                if (strlen($phone) === 11 && str_starts_with($phone, '0')) $phone = '88' . $phone;
                $userData['ph'] = [hash('sha256', $phone)];
            }

            FacebookConversion::sendPurchase([
                'currency'    => 'BDT',
                'value'       => (float) $order->final_amount,
                'content_ids' => $ids,
                'contents'    => $contents,
                'order_id'    => $order->id,
                'num_items'   => (int) $order->details->sum('quantity'),
            ], $eventId, $userData, $url);

            try {
                if (empty($ttUserData) && !empty($order->mobile)) {
                    $ttUserData['phone'] = TikTokConversion::hashPhone($order->mobile);
                }

                TikTokConversion::sendPurchase([
                    'currency'    => 'BDT',
                    'value'       => (float) $order->final_amount,
                    'content_ids' => $ids,
                    'contents'    => $contents,
                    'order_id'    => $order->id,
                ], $eventId, $ttUserData, $url);
            } catch (\Throwable $e) {
                Log::error('TikTok Purchase failed for Order ' . $order->id . ': ' . $e->getMessage());
            }

        } catch (\Throwable $e) {
            Log::error('Purchase conversion failed for Order ' . ($order->id ?? '?') . ': ' . $e->getMessage());
        }
    }
}
