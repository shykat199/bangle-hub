<?php

namespace App\Notifications;

class PreOrderDuePaymentReminder extends PreOrderNotification
{
    protected function subject(): string
    {
        return 'Payment reminder for your pre-order ' . $this->reference();
    }

    protected function heading(): string
    {
        return 'A payment is due on your pre-order';
    }

    protected function lines(): array
    {
        return [
            "Dear {$this->customerName()}, this is a reminder about your pre-order {$this->reference()}.",
            'You have paid ' . biz_format_currency($this->order->advance_paid) . ' so far. Please keep the remaining amount ready — it is collected when your order is delivered.',
        ];
    }

    protected function highlight(): ?string
    {
        return 'Due amount: ' . biz_format_currency($this->order->due_amount);
    }
}
