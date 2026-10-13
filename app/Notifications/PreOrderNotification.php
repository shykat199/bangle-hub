<?php

namespace App\Notifications;

use App\Models\Order;
use App\Support\SiteMail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Base for every pre-order email. Mail is the only channel, and the mail is
 * sent straight away — no queue, so no worker or cron is needed. They are
 * sent through PreOrderService::notify(), which holds a mail back until the
 * surrounding database transaction has committed.
 */
abstract class PreOrderNotification extends Notification
{
    public function __construct(public Order $order)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    abstract protected function subject(): string;

    abstract protected function heading(): string;

    /** Paragraphs shown above the order summary. */
    abstract protected function lines(): array;

    /** Short coloured note under the paragraphs (e.g. the new delivery date). */
    protected function highlight(): ?string
    {
        return null;
    }

    public function toMail($notifiable): MailMessage
    {
        $settings = SiteMail::configure();
        $order = $this->order->loadMissing(['details.product', 'details.variation.size', 'details.variation.color']);

        $message = (new MailMessage)
            ->subject($this->subject())
            ->view('emails.preorder.notification', [
                'order'     => $order,
                'settings'  => $settings,
                'heading'   => $this->heading(),
                'lines'     => $this->lines(),
                'highlight' => $this->highlight(),
                'orderUrl'  => confirmOrderUrl($order),
            ]);

        // Sender: the SMTP account from the admin settings; when MAIL_FROM_ADDRESS
        // is not set either, fall back to the shop's own email so the mail
        // is not rejected for having no "From".
        $from = ($settings->smtp_user ?? null)
            ?: (config('mail.from.address') ? null : (($settings->owner_email ?? null) ?: ($settings->admin_email ?? null)));

        if ($from) {
            $message->from($from, $settings->site_name ?? config('app.name'));
        }

        return $message;
    }

    protected function customerName(): string
    {
        return trim(($this->order->first_name ?? '') . ' ' . ($this->order->last_name ?? '')) ?: 'Customer';
    }

    protected function reference(): string
    {
        return '#' . ($this->order->invoice_no ?: $this->order->id);
    }
}
