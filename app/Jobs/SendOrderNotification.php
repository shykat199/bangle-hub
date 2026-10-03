<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Information;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Mail\NewOrderMail;

class SendOrderNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    public function __construct($order)
    {
        // Load relationships
        $this->order = $order->load(['details.product', 'details.variation.size', 'details.variation.color']);
    }

    public function handle()
    {
        try {
            $settings = Information::first(); 
            if(!$settings) return;

            // 1. Set Mail Config Dynamically
            if ($settings->smtp_host && $settings->smtp_user) {
                Config::set('mail.mailers.smtp.host', $settings->smtp_host);
                Config::set('mail.mailers.smtp.port', $settings->smtp_port);
                Config::set('mail.mailers.smtp.username', $settings->smtp_user);
                Config::set('mail.mailers.smtp.password', $settings->smtp_pass);
                Config::set('mail.from.address', $settings->smtp_user);
                Config::set('mail.from.name', $settings->site_name ?? 'Order System');
            }

            // 2. Send Premium Email to Admin
            if ($settings->admin_email) {
                try {
                    Mail::to($settings->admin_email)->send(new NewOrderMail($this->order, $settings));
                } catch (\Exception $e) {
                    Log::error("Job Mail Error: " . $e->getMessage());
                }
            }

            // 3. Check SMS Settings
            if ($settings->sms_api_key && $settings->sms_sender_id) {

                // ====================================================
                // (A) Send SMS to ADMIN
                // ====================================================
                $customerName = trim(($this->order->first_name ?? '') . ' ' . ($this->order->last_name ?? ''));

                if ($settings->admin_phone) {
                    $adminTemplate = $settings->sms_new_order_admin ?? "New Order! ID: #{order_id}, Amount: {amount} Tk.";
                    $adminMsg = str_replace(
                        ['{order_id}', '{invoice}', '{amount}', '{name}'],
                        [$this->order->id, $this->order->invoice_no ?? '', $this->order->final_amount, $customerName],
                        $adminTemplate
                    );

                    try {
                        Http::get("http://bulksmsbd.net/api/smsapi", [
                            'api_key' => $settings->sms_api_key,
                            'type' => 'text',
                            'number' => $settings->admin_phone,
                            'senderid' => $settings->sms_sender_id,
                            'message' => $adminMsg,
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Admin SMS Error: " . $e->getMessage());
                    }
                }

                // ====================================================
                // (B) Send SMS to CUSTOMER (Fixed & Updated)
                // ====================================================
                if ($this->order->mobile) {
                    
                    // Default status jodi na thake tobe pending dhora hobe
                    $status = strtolower($this->order->status ?? 'pending'); 
                    $customerTemplate = '';

                    // Status onujayi template select kora
                    switch ($status) {
                        case 'pending':
                            $customerTemplate = $settings->sms_pending;
                            break;
                        case 'processing':
                            $customerTemplate = $settings->sms_processing;
                            break;
                        case 'courier':
                        case 'shipped':
                            $customerTemplate = $settings->sms_courier;
                            break;
                        case 'completed':
                        case 'delivered':
                            $customerTemplate = $settings->sms_complete;
                            break;
                        case 'cancelled':
                        case 'cancel':
                            $customerTemplate = $settings->sms_cancell;
                            break;
                        case 'return':
                        case 'returned':
                            $customerTemplate = $settings->sms_return;
                            break;
                        case 'on_hold':
                        case 'hold':
                            $customerTemplate = $settings->sms_on_hold;
                            break;
                        default:
                            $customerTemplate = $settings->sms_status_update;
                            break;
                    }

                    // Jodi kono template faka thake, tobe ekta default text set kora holo
                    if (empty($customerTemplate)) {
                        $customerTemplate = $settings->sms_status_update ?? "Dear Customer, your order #{order_id} is now {status}. Total: {amount} Tk.";
                    }
                    
                    // Placeholders ({order_id}, {invoice}, {tracking_id}, {courier}, {amount}, {status}, {name}) replace kora
                    $courierNames = [1 => 'Redx', 2 => 'Pathao', 3 => 'Steadfast', 4 => 'Carrybee'];
                    $courierName  = $courierNames[$this->order->courier_id ?? 0] ?? '';

                    $customerMsg = str_replace(
                        ['{order_id}', '{invoice}', '{tracking_id}', '{courier}', '{amount}', '{status}', '{name}'],
                        [$this->order->id, $this->order->invoice_no ?? '', $this->order->courier_tracking_id ?? '', $courierName, $this->order->final_amount, ucfirst($status), $customerName],
                        $customerTemplate
                    );

                    try {
                        Http::get("http://bulksmsbd.net/api/smsapi", [
                            'api_key' => $settings->sms_api_key,
                            'type' => 'text',
                            'number' => $this->order->mobile, // Customer Phone
                            'senderid' => $settings->sms_sender_id,
                            'message' => $customerMsg,
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Customer SMS Error: " . $e->getMessage());
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error("Notification Job Error: " . $e->getMessage());
        }
    }
}