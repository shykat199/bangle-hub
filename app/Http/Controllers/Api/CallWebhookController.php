<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Information;
use App\Jobs\SendOrderConfirmationCall;
use App\Utils\Util;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class CallWebhookController extends Controller
{
    // Statuses an auto call result may still act on. Anything further along
    // (Processing, Shipped, Delivered, Cancelled, ...) must never be reopened
    // by a late, duplicated or forged webhook.
    const ACTIONABLE_STATUSES = ['pending', 'incomplete', 'on hold', 'scheduled'];

    // Mirrors OrderController: leaving an active status returns the stock.
    const ACTIVE_STATUSES = ['pending', 'incomplete', 'on hold', 'scheduled', 'confirmed', 'processing', 'courier complete', 'shipped', 'delivered'];

    public function handleManyDialWebhook(Request $request)
    {
        try {
            // Reject forged requests. This fails CLOSED: with no token set in
            // .env the endpoint refuses everything rather than waving it
            // through, because an open webhook lets anyone flip orders to
            // Confirmed/Cancelled and burn SMS credit.
            $expectedToken = (string) config('services.manydial.webhook_token');

            if ($expectedToken === '') {
                Log::warning('ManyDial webhook rejected: MANYDIAL_WEBHOOK_TOKEN is not configured', ['ip' => $request->ip()]);
                return response()->json(['error' => 'Webhook not configured'], 503);
            }

            $providedToken = $request->input('token', $request->header('X-Webhook-Token'));
            if (!is_string($providedToken) || !hash_equals($expectedToken, $providedToken)) {
                Log::warning('ManyDial webhook rejected: invalid token', ['ip' => $request->ip()]);
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $payload = $request->input('callPayload');
            $userPressed = $request->input('userPressed');
            $status = $request->input('status');

            if (!$payload) {
                return response()->json(['error' => 'Invalid Payload'], 400);
            }

            $orderId = str_replace('Order-', '', $payload);
            $order = Order::with('details')->find($orderId);

            if (!$order) {
                return response()->json(['status' => 'Order not found, ignored'], 200);
            }

            $old_status = $order->status;

            // A call result can only belong to an order we actually rang, and
            // only while that order is still waiting on the customer's answer.
            if ((int) $order->call_attempt < 1 || !in_array(strtolower(trim((string) $old_status)), self::ACTIONABLE_STATUSES)) {
                Log::warning('ManyDial webhook ignored', [
                    'order_id'     => $order->id,
                    'status'       => $old_status,
                    'call_attempt' => $order->call_attempt,
                    'ip'           => $request->ip(),
                ]);
                return response()->json(['status' => 'Not actionable, ignored'], 200);
            }

            // Same key config the dispatch job used, so a custom confirm/cancel
            // key set in the admin panel is understood here too.
            $info = Information::first();
            [$confirmKey, $cancelKey] = SendOrderConfirmationCall::menuKeys($info);

            if ($status === 'ANSWER') {
                if ($userPressed == $confirmKey) {
                    $order->status = 'Confirmed';
                    $order->save();

                    if (function_exists('logActivity')) {
                        logActivity('Auto Call', 'Order', "Customer pressed 1. Order auto-confirmed via ManyDial.", $order->id, ['status' => $old_status], ['status' => 'Confirmed']);
                    }

                    if (!$this->smsSentByManyDial($info)) {
                        $this->sendSMS($order, 'sms_confirmed_active', 'sms_confirmed');
                    }

                } elseif ($userPressed == $cancelKey) {
                    // Put the reserved stock back before cancelling, exactly as
                    // a manual cancel from the admin panel does.
                    $this->restoreStock($order, $old_status);

                    $order->status = 'Cancelled';
                    $order->save();

                    if (function_exists('logActivity')) {
                        logActivity('Auto Call', 'Order', "Customer pressed 2. Order auto-cancelled via ManyDial.", $order->id, ['status' => $old_status], ['status' => 'Cancelled']);
                    }

                    if (!$this->smsSentByManyDial($info)) {
                        $this->sendSMS($order, 'sms_cancell_active', 'sms_cancell');
                    }

                } else {
                    if (function_exists('logActivity')) {
                        logActivity('Auto Call', 'Order', "Customer answered but did not press any button.", $order->id);
                    }
                }
            } else {
                if ((int) $order->call_attempt < SendOrderConfirmationCall::maxAttempts($info)) {
                    $delay = SendOrderConfirmationCall::retryDelayMinutes($info);
                    SendOrderConfirmationCall::dispatch($order, SendOrderConfirmationCall::webhookUrl())->delay(now()->addMinutes($delay));

                    if (function_exists('logActivity')) {
                        logActivity('Auto Call Retry', 'Order', "Call Status: {$status}. Scheduling retry in {$delay} minutes.", $order->id);
                    }
                } else {
                    $order->status = 'On Hold';
                    $order->save();

                    if (function_exists('logActivity')) {
                        logActivity('Auto Call Failed', 'Order', "All call attempts failed. Status: {$status}. Order moved to On Hold.", $order->id, ['status' => $old_status], ['status' => 'On Hold']);
                    }

                    $this->sendSMS($order, 'sms_on_hold_active', 'sms_on_hold');
                }
            }

            return response()->json(['status' => 'Webhook received and processed successfully'], 200);

        } catch (\Exception $e) {
            Log::error('ManyDial Webhook Error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    /**
     * Returns the stock held by an order that is being auto-cancelled. Uses the
     * same Util call as the manual cancel flow so both paths stay in sync.
     */
    private function restoreStock($order, $old_status)
    {
        if (!in_array(strtolower(trim((string) $old_status)), self::ACTIVE_STATUSES)) return;

        try {
            $util = new Util();
            foreach ($order->details as $line) {
                $util->increaseProductStock($line->product_id, $line->variation_id, $line->quantity);
            }
        } catch (\Exception $e) {
            Log::error('ManyDial auto-cancel stock restore failed for Order ID: ' . $order->id . ' — ' . $e->getMessage());
        }
    }

    /**
     * When ManyDial-side SMS is on, the customer was already texted the moment
     * they pressed confirm/cancel (sms1/sms2 in the call flow) — sending again
     * from here would double it. On-hold SMS still always goes via bulksmsbd
     * because an unanswered call never triggers ManyDial's own SMS.
     */
    private function smsSentByManyDial($info = null)
    {
        $info = $info ?: Information::first();
        return $info && ($info->manydial_sms_status ?? 0) == 1;
    }

    private function sendSMS($order, $activeCol, $tempCol)
    {
        $settings = Information::first();

        if ($settings && $settings->sms_api_key && $order->mobile) {
            if ($settings->$activeCol == 1 && !empty($settings->$tempCol)) {
                
                $courierNames = [1 => 'Redx', 2 => 'Pathao', 3 => 'Steadfast', 4 => 'Carrybee'];
                $courierName  = $courierNames[$order->courier_id ?? 0] ?? '';

                $msg = str_replace(
                    ['{order_id}', '{invoice}', '{tracking_id}', '{courier}', '{amount}', '{status}', '{name}'],
                    [$order->id, $order->invoice_no ?? '', $order->courier_tracking_id ?? '', $courierName, $order->final_amount, ucfirst($order->status), trim(($order->first_name ?? '') . ' ' . ($order->last_name ?? ''))],
                    $settings->$tempCol
                );
                
                try {
                    Http::get("http://bulksmsbd.net/api/smsapi", [
                        'api_key'  => $settings->sms_api_key,
                        'type'     => 'text',
                        'number'   => $order->mobile,
                        'senderid' => $settings->sms_sender_id,
                        'message'  => $msg,
                    ]);
                } catch (\Exception $e) {
                    Log::error('ManyDial Auto SMS Failed: ' . $e->getMessage());
                }
            }
        }
    }
}