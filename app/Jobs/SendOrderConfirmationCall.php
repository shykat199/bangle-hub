<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Information;

class SendOrderConfirmationCall implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Fallbacks used when the admin has not filled the matching setting in
    // the ManyDial section of the settings page (Information row).
    const MAX_ATTEMPTS = 2;
    const RETRY_DELAY_MINUTES = 10;
    const CALL_DURATION = 2;
    const DEFAULT_WELCOME = "হ্যালো, আপনার অর্ডারটি সফলভাবে প্লেস হয়েছে। কনফার্ম করতে ১ চাপুন। বাতিল করতে ২ চাপুন।";
    const DEFAULT_MSG_CONFIRM = "ধন্যবাদ, আপনার অর্ডারটি কনফার্ম করা হয়েছে।";
    const DEFAULT_MSG_CANCEL = "আপনার অর্ডারটি বাতিল করা হয়েছে।";

    public $order;

    // Captured while a real request is running, because a queue worker runs on
    // the CLI where url() can only fall back to APP_URL — which is often stale
    // or points at another site on the same server.
    public $webhookUrl;

    public function __construct(Order $order, $webhookUrl = null)
    {
        $this->order = $order;
        $this->webhookUrl = $webhookUrl;
    }

    /**
     * Single entry point for every place that creates an order (admin panel,
     * checkout, landing pages). Keeps the "should this order be auto called?"
     * rules in one place instead of repeating them in each controller.
     */
    public static function maybeDispatch($order, $context = '')
    {
        try {
            if (!$order || empty($order->id)) return false;

            // Read the row back so status / payment_method / call_attempt are
            // the committed values, not whatever the controller left in memory.
            $order = Order::find($order->id);
            if (!$order) return false;

            $info = Information::first();
            if (!$info || ($info->manydial_status ?? 0) != 1) return false;

            // Landing page orders are placed without a payment method — those
            // are always cash on delivery.
            $method = strtolower(trim((string) $order->payment_method));
            if (!in_array($method, ['', 'cod', 'cash on delivery', 'cash_on_delivery'])) return false;

            // Only a fresh order should be auto called, never one an operator
            // has already moved forward.
            if (!in_array(strtolower(trim((string) $order->status)), ['', 'pending'])) return false;

            // Already called (or already queued) — never ring twice for the
            // same order because a controller ran again.
            if ((int) $order->call_attempt > 0) return false;

            if (self::normalizePhone($order->mobile ?? $order->phone) === '') {
                Log::warning('ManyDial: no usable phone number for Order ID: ' . $order->id);
                return false;
            }

            self::dispatch($order, self::webhookUrl());

            if (function_exists('logActivity')) {
                logActivity('Auto Call', 'Order', trim('Confirmation call job dispatched for COD order. ' . $context), $order->id);
            }

            return true;

        } catch (\Exception $e) {
            Log::error('ManyDial maybeDispatch failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ManyDial is handed the same local 11 digit number the rest of the system
     * stores (01XXXXXXXXX); this only strips spaces/dashes and the +88 prefix.
     * Returns '' when the number cannot be trusted, so no call is placed.
     */
    public static function normalizePhone($raw)
    {
        $digits = preg_replace('/\D+/', '', (string) $raw);

        if (strlen($digits) === 13 && strpos($digits, '880') === 0) {
            $digits = substr($digits, 2);          // 8801XXXXXXXXX -> 01XXXXXXXXX
        } elseif (strlen($digits) === 10 && strpos($digits, '1') === 0) {
            $digits = '0' . $digits;               // 1XXXXXXXXX    -> 01XXXXXXXXX
        }

        return (strlen($digits) === 11 && strpos($digits, '01') === 0) ? $digits : '';
    }

    /**
     * DTMF keys for confirm / cancel, configurable from settings. Falls back
     * to 1 / 2 when unset, invalid or identical, so the call flow and the
     * webhook can never disagree about which key means what.
     */
    public static function menuKeys($info = null)
    {
        $info = $info ?: Information::first();

        $confirm = trim((string) ($info->manydial_key_confirm ?? ''));
        $cancel  = trim((string) ($info->manydial_key_cancel ?? ''));

        if (!preg_match('/^[0-9]$/', $confirm)) $confirm = '1';
        if (!preg_match('/^[0-9]$/', $cancel))  $cancel  = '2';
        if ($confirm === $cancel) { $confirm = '1'; $cancel = '2'; }

        return [$confirm, $cancel];
    }

    /**
     * Total call attempts allowed per order (first call + retries).
     */
    public static function maxAttempts($info = null)
    {
        $info = $info ?: Information::first();
        $n = (int) ($info->manydial_max_attempts ?? 0);

        return ($n >= 1 && $n <= 5) ? $n : self::MAX_ATTEMPTS;
    }

    /**
     * Minutes to wait before a retry call.
     */
    public static function retryDelayMinutes($info = null)
    {
        $info = $info ?: Information::first();
        $m = (int) ($info->manydial_retry_delay ?? 0);

        return ($m >= 1 && $m <= 720) ? $m : self::RETRY_DELAY_MINUTES;
    }

    /**
     * perCallDuration ManyDial is told to reserve (minutes). The API marks it
     * required, so a default is always sent even when the setting is empty.
     */
    public static function callDuration($info = null)
    {
        $info = $info ?: Information::first();
        $d = (int) ($info->manydial_per_call_duration ?? 0);

        return ($d >= 1 && $d <= 60) ? $d : self::CALL_DURATION;
    }

    /**
     * Delivery hook ManyDial posts the call result back to. The shared secret
     * (MANYDIAL_WEBHOOK_TOKEN) rides along in the query string when configured;
     * CallWebhookController accepts it from there or from X-Webhook-Token.
     */
    public static function webhookUrl()
    {
        $url   = url('/api/webhook/manydial');
        $token = config('services.manydial.webhook_token');

        return !empty($token) ? $url . '?token=' . urlencode($token) : $url;
    }

    public function handle()
    {
        try {
            $info = Information::first();

            if (!$info || empty($info->manydial_api_key) || empty($info->manydial_caller_id)) {
                Log::warning('ManyDial credentials are missing in settings.');
                return;
            }

            $phone = self::normalizePhone($this->order->mobile ?? $this->order->phone);

            if ($phone === '') {
                Log::warning('ManyDial: invalid phone number for Order ID: ' . $this->order->id);
                return;
            }

            // Counted only once a call is really about to be placed, so missing
            // credentials or a bad number never burn the order's attempts.
            $this->order->increment('call_attempt');

            [$confirmKey, $cancelKey] = self::menuKeys($info);

            $confirmMsg = trim((string) ($info->manydial_msg_confirm ?? '')) ?: self::DEFAULT_MSG_CONFIRM;
            $cancelMsg  = trim((string) ($info->manydial_msg_cancel ?? ''))  ?: self::DEFAULT_MSG_CANCEL;

            // Welcome priority: admin-uploaded audio (md5 from ManyDial's
            // convert API, played as-is) > admin's custom text > the default.
            $welcomeAudio = trim((string) ($info->manydial_welcome_audio ?? ''));
            $welcomeText  = trim((string) ($info->manydial_welcome ?? '')) ?: self::DEFAULT_WELCOME;

            $messages = [
                'welcome' => $welcomeAudio !== '' ? $welcomeAudio : $this->personalize($welcomeText),
                'menuMessage' . $confirmKey => $this->personalize($confirmMsg),
                'menuMessage' . $cancelKey => $this->personalize($cancelMsg),
            ];

            $repeat = trim((string) ($info->manydial_repeat ?? ''));
            if ($repeat !== '') {
                $messages['repeat'] = $repeat;
            }

            $forward = trim((string) ($info->manydial_forward ?? ''));
            if ($forward !== '') {
                $messages['forward'] = $forward;
            }

            // When enabled, ManyDial texts the customer the moment they press
            // confirm/cancel; CallWebhookController then skips its own
            // bulksmsbd send for those two statuses to avoid a double SMS.
            if (($info->manydial_sms_status ?? 0) == 1) {
                $confirmSms = self::renderSmsTemplate($info, 'sms_confirmed_active', 'sms_confirmed', $this->order, 'Confirmed');
                $cancelSms  = self::renderSmsTemplate($info, 'sms_cancell_active', 'sms_cancell', $this->order, 'Cancelled');
                if ($confirmSms !== '') $messages['sms' . $confirmKey] = $confirmSms;
                if ($cancelSms !== '')  $messages['sms' . $cancelKey]  = $cancelSms;
            }

            $buttons = [
                ['id' => 'menuMessage' . $confirmKey, 'key' => $confirmKey, 'value' => 'Confirm Order'],
                ['id' => 'menuMessage' . $cancelKey, 'key' => $cancelKey, 'value' => 'Cancel Order']
            ];

            $parts = [
                ['name' => 'callPayload', 'contents' => 'Order-' . $this->order->id],
                ['name' => 'callerId', 'contents' => $info->manydial_caller_id],
                // ManyDial's call/dispatch requires the country code
                // (+8801XXXXXXXXX), not the bare local 01XXXXXXXXX.
                ['name' => 'number', 'contents' => '+88' . $phone],
                ['name' => 'perCallDuration', 'contents' => (string) self::callDuration($info)],
                ['name' => 'messages', 'contents' => json_encode($messages, JSON_UNESCAPED_UNICODE)],
                ['name' => 'buttons', 'contents' => json_encode($buttons)],
                ['name' => 'deliveryHook', 'contents' => !empty($this->webhookUrl) ? $this->webhookUrl : self::webhookUrl()]
            ];

            // Premium (Gemini) TTS voice — needs Premium Voice access on the
            // ManyDial subscription; azure remains ManyDial's default otherwise.
            if (($info->manydial_voice_type ?? '') === 'gemini' && !empty($info->manydial_voice_id)) {
                $parts[] = ['name' => 'voiceType', 'contents' => 'gemini'];
                $parts[] = ['name' => 'voiceId', 'contents' => $info->manydial_voice_id];
            }

            $response = Http::withHeaders([
                'x-api-key' => $info->manydial_api_key
            ])->asMultipart()->post('https://api.manydial.com/v1/portal/call/dispatch', $parts);

            if ($response->successful()) {
                Log::info('ManyDial call dispatched successfully for Order ID: ' . $this->order->id);
                return;
            }

            Log::error('ManyDial call dispatch failed for Order ID: ' . $this->order->id . ' Response: ' . $response->body());
            $this->retryLater();

        } catch (\Exception $e) {
            Log::error('Call Dispatch Exception: ' . $e->getMessage());
            $this->retryLater();
        }
    }

    /**
     * The admin writes call texts once in settings; these tokens let the same
     * text speak each customer's own order back to them.
     */
    protected function personalize($text)
    {
        $name = trim(($this->order->first_name ?? '') . ' ' . ($this->order->last_name ?? ''));

        return str_replace(
            ['{name}', '{order_id}', '{invoice}', '{amount}'],
            [$name, $this->order->id, $this->order->invoice_no ?? '', $this->order->final_amount ?? ''],
            (string) $text
        );
    }

    /**
     * Renders one of the admin SMS templates for ManyDial to send from inside
     * the call flow. Returns '' when that status SMS is switched off or has no
     * template. Uses the same tokens as CallWebhookController::sendSMS.
     */
    public static function renderSmsTemplate($info, $activeCol, $tempCol, $order, $status)
    {
        if (($info->$activeCol ?? 0) != 1 || empty($info->$tempCol)) return '';

        $courierNames = [1 => 'Redx', 2 => 'Pathao', 3 => 'Steadfast', 4 => 'Carrybee'];
        $courierName  = $courierNames[$order->courier_id ?? 0] ?? '';

        return str_replace(
            ['{order_id}', '{invoice}', '{tracking_id}', '{courier}', '{amount}', '{status}', '{name}'],
            [$order->id, $order->invoice_no ?? '', $order->courier_tracking_id ?? '', $courierName, $order->final_amount, $status, trim(($order->first_name ?? '') . ' ' . ($order->last_name ?? ''))],
            $info->$tempCol
        );
    }

    /**
     * ManyDial only calls the delivery hook for calls it actually placed, so a
     * failed dispatch would otherwise leave the order waiting forever.
     */
    protected function retryLater()
    {
        if ((int) $this->order->call_attempt >= self::maxAttempts()) return;

        $delay = self::retryDelayMinutes();
        self::dispatch($this->order, $this->webhookUrl)->delay(now()->addMinutes($delay));

        if (function_exists('logActivity')) {
            logActivity('Auto Call Retry', 'Order', "ManyDial dispatch failed. Scheduling retry in {$delay} minutes.", $this->order->id);
        }
    }
}
