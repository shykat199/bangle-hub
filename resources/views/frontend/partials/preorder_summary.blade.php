{{--
    Pre-order block for the customer's order pages (confirmation, invoice,
    order details): badge, estimated delivery, payment mode, paid and due.
    Usage: @include('frontend.partials.preorder_summary', ['order' => $order])
--}}
@if(!empty($order->is_preorder))
    @php
        $poDelivery  = $order->preorderDeliveryLabel();
        $poIsAdvance = $order->isAdvancePreorder();
    @endphp

    @once
    <style>
        .po-summary{ margin: 14px 0; padding: 14px 16px; border: 1px solid #fcd9a8; border-radius: 10px; background: #fffaf0; text-align: left; font-size: 14px; line-height: 1.5; color: #374151; }
        .po-summary__head{ display: flex; flex-wrap: wrap; align-items: center; gap: 8px 10px; margin-bottom: 8px; }
        .po-summary__eta{ font-weight: 700; color: #92400e; font-size: 15px; }
        .po-summary__row{ display: flex; justify-content: space-between; gap: 16px; padding: 3px 0; }
        .po-summary__row span:last-child{ font-weight: 700; white-space: nowrap; }
        .po-summary__row.is-paid span:last-child{ color: #15803d; }
        .po-summary__row.is-due{ margin-top: 4px; padding-top: 7px; border-top: 1px dashed #fcd9a8; font-size: 15px; }
        .po-summary__row.is-due span:last-child{ color: #b91c1c; }
        .po-summary__note{ margin: 6px 0 0; font-size: 12.5px; color: #6b7280; }
    </style>
    @endonce

    <div class="po-summary">
        <div class="po-summary__head">
            <x-preorder-badge />
            <span class="po-summary__eta">{{ $poDelivery ?: 'Delivery date will be confirmed by email' }}</span>
        </div>

        <div class="po-summary__row">
            <span>Order status</span>
            <span>{{ $order->status }}</span>
        </div>
        <div class="po-summary__row">
            <span>Payment mode</span>
            <span>{{ $poIsAdvance ? 'Advance payment' : 'Full payment' }}</span>
        </div>
        @if($poIsAdvance && !$order->advanceIsPaid())
            <div class="po-summary__row">
                <span>Advance payable now</span>
                <span>{{ priceFormate($order->advance_amount) }}</span>
            </div>
        @endif
        <div class="po-summary__row is-paid">
            <span>Paid amount</span>
            <span>{{ priceFormate($order->advance_paid) }}</span>
        </div>
        <div class="po-summary__row is-due">
            <span>Due amount</span>
            <span>{{ priceFormate($order->due_amount) }}</span>
        </div>

        <p class="po-summary__note">
            @if($poIsAdvance && !$order->advanceIsPaid())
                Your advance is being verified. The due amount is payable when your order is delivered.
            @elseif((float) $order->due_amount > 0)
                The due amount is payable when your order is delivered.
            @else
                This pre-order is fully paid.
            @endif
            This is a pre-order: it ships as soon as the product arrives, and we email you every update.
        </p>
    </div>
@endif
