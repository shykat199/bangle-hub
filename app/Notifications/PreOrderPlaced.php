<?php

namespace App\Notifications;

class PreOrderPlaced extends PreOrderNotification
{
    protected function subject(): string
    {
        return 'Pre-order confirmed ' . $this->reference();
    }

    protected function heading(): string
    {
        return 'Your pre-order is confirmed';
    }

    protected function lines(): array
    {
        $lines = [
            "Dear {$this->customerName()}, thank you for your pre-order {$this->reference()}.",
            'This is a pre-order: the product is not in stock yet and will be shipped as soon as it arrives.',
        ];

        if ($this->order->isAdvancePreorder()) {
            $lines[] = $this->order->advanceIsPaid()
                ? 'We have received your advance payment. The remaining amount is payable on delivery.'
                : 'Your pre-order is held against the advance payment. The remaining amount is payable on delivery.';
        }

        return $lines;
    }

    protected function highlight(): ?string
    {
        return $this->order->preorderDeliveryLabel();
    }
}
