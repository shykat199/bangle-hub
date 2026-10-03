<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Models\Information;

class NewOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $settings;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($order, $settings)
    {
        $this->order = $order;
        $this->settings = $settings;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('New Order Alert #' . $this->order->invoice_no)
                    ->view('emails.admin_new_order');
    }
}