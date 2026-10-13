<?php

namespace App\Notifications;

use App\Models\Order;
use App\Services\PreOrderService;

class PreOrderStatusChanged extends PreOrderNotification
{
    public function __construct(Order $order, public string $status)
    {
        parent::__construct($order);
    }

    private function isReady(): bool
    {
        return strcasecmp($this->status, PreOrderService::STATUS_READY) === 0;
    }

    protected function subject(): string
    {
        return ($this->isReady() ? 'Your pre-order is ready to ship ' : 'Your pre-order is being processed ') . $this->reference();
    }

    protected function heading(): string
    {
        return $this->isReady() ? 'Your pre-order is ready to ship' : 'Your pre-order has arrived';
    }

    protected function lines(): array
    {
        $lines = [$this->isReady()
            ? "Dear {$this->customerName()}, your pre-order {$this->reference()} is packed and ready to ship. It will be handed to the courier shortly."
            : "Dear {$this->customerName()}, good news — the product for your pre-order {$this->reference()} has arrived and we are now processing your order."];

        if ((float) $this->order->due_amount > 0) {
            $lines[] = 'Amount payable on delivery: ' . biz_format_currency($this->order->due_amount) . '.';
        }

        return $lines;
    }
}
