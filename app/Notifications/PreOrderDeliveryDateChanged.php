<?php

namespace App\Notifications;

use App\Models\Order;

class PreOrderDeliveryDateChanged extends PreOrderNotification
{
    public function __construct(Order $order, public ?string $oldLabel, public ?string $newLabel)
    {
        parent::__construct($order);
    }

    protected function subject(): string
    {
        return 'Delivery date updated for your pre-order ' . $this->reference();
    }

    protected function heading(): string
    {
        return 'Your delivery date has changed';
    }

    protected function lines(): array
    {
        $lines = ["Dear {$this->customerName()}, the estimated delivery for your pre-order {$this->reference()} has been updated."];

        if ($this->oldLabel) {
            $lines[] = 'Previously: ' . $this->oldLabel;
        }

        $lines[] = 'We are sorry for any inconvenience and will keep you informed.';

        return $lines;
    }

    protected function highlight(): ?string
    {
        return $this->newLabel ? 'New: ' . $this->newLabel : null;
    }
}
