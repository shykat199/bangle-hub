<?php

namespace App\Services;

use App\Exceptions\PreOrderException;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\OrderPayment;
use App\Models\PreorderDeliveryLog;
use App\Models\Product;
use App\Models\Variation;
use App\Notifications\PreOrderDeliveryDateChanged;
use App\Notifications\PreOrderDuePaymentReminder;
use App\Notifications\PreOrderPlaced;
use App\Notifications\PreOrderStatusChanged;
use App\Utils\Util;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Everything the shop needs to know about pre-orders lives here: whether a
 * product (or one of its variations) can be pre-ordered, how many slots are
 * left, what the advance is, and how an order's lines hold and give back
 * those slots.
 *
 * Pre-order is NOT stock-out. It only comes into play when a product cannot
 * be bought normally (no stock) AND the admin switched pre-order on for it.
 *
 * Variable products: only the variations with the toggle on are
 * pre-orderable. A field left blank on the variation falls back to the
 * product-level value (when the product-level toggle is on), and a
 * product-level limit caps the total across all its variations.
 */
class PreOrderService
{
    public const STATUS_PREORDER   = 'Pre-Order';
    public const STATUS_PROCESSING = 'Processing';
    public const STATUS_READY      = 'Ready to Ship';

    public const MODE_FULL    = 'full';
    public const MODE_ADVANCE = 'advance';

    /** The admin moves a pre-order forward one step at a time. */
    public const FLOW = [
        self::STATUS_PREORDER   => self::STATUS_PROCESSING,
        self::STATUS_PROCESSING => self::STATUS_READY,
    ];

    /* ------------------------------------------------------------------
     | Settings
     | ------------------------------------------------------------------ */

    public function isVariable($product): bool
    {
        return ($product->type ?? 'single') === 'variable';
    }

    /**
     * A single product carries one technical variation row; its pre-order
     * settings and counter live on the product, so that row is ignored.
     */
    private function effectiveVariation($product, $variation)
    {
        return ($variation && $this->isVariable($product)) ? $variation : null;
    }

    private function filled($value): bool
    {
        return $value !== null && $value !== '';
    }

    public function settings($product, $variation = null): array
    {
        $variation = $this->effectiveVariation($product, $variation);
        $productOn = (bool) ($product->is_preorder ?? false);

        if ($variation) {
            $enabled = (bool) ($variation->is_preorder ?? false);
            $pick = fn ($field) => $this->filled($variation->$field ?? null)
                ? $variation->$field
                : ($productOn ? ($product->$field ?? null) : null);
        } else {
            $enabled = $productOn;
            $pick = fn ($field) => $product->$field ?? null;
        }

        $start = $pick('preorder_delivery_start');
        $end   = $pick('preorder_delivery_end');

        // Advance type and amount only make sense as a pair, so they are
        // taken together from whichever level set an amount.
        if ($variation && $this->filled($variation->preorder_advance_amount ?? null)) {
            $advanceSource = $variation;
        } else {
            $advanceSource = (!$variation || $productOn) ? $product : null;
        }

        return [
            'enabled'        => $enabled,
            'delivery_text'  => $pick('preorder_delivery_text'),
            'delivery_start' => $start ? Carbon::parse($start)->toDateString() : null,
            'delivery_end'   => $end ? Carbon::parse($end)->toDateString() : null,
            'payment_type'   => $pick('preorder_payment_type') ?: 'full',
            'advance_type'   => $advanceSource->preorder_advance_type ?? null,
            'advance_amount' => (float) ($advanceSource->preorder_advance_amount ?? 0),
        ];
    }

    public function isEnabled($product, $variation = null): bool
    {
        return $this->settings($product, $variation)['enabled'];
    }

    /** Slots still open. null = no limit set. */
    public function remaining($product, $variation = null): ?int
    {
        $variation = $this->effectiveVariation($product, $variation);
        $caps = [];

        if ($variation) {
            if ($variation->preorder_limit !== null) {
                $caps[] = max(0, (int) $variation->preorder_limit - (int) $variation->preorder_sold_qty);
            }
            if (!empty($product->is_preorder) && $product->preorder_limit !== null) {
                $caps[] = max(0, (int) $product->preorder_limit - (int) $product->preorder_sold_qty);
            }
        } elseif ($product->preorder_limit !== null) {
            $caps[] = max(0, (int) $product->preorder_limit - (int) $product->preorder_sold_qty);
        }

        return $caps ? min($caps) : null;
    }

    /** is_preorder = true AND (no limit OR sold < limit). */
    public function isAvailable($product, $variation = null): bool
    {
        if (!$this->isEnabled($product, $variation)) {
            return false;
        }

        $remaining = $this->remaining($product, $variation);

        return $remaining === null || $remaining > 0;
    }

    private function isActive($product): bool
    {
        return $product && (int) ($product->status ?? 1) === 1;
    }

    /**
     * Cannot be bought the normal way — the same rule the cart applies
     * (Manage Stock = No means the product is closed, not unlimited). Pass the
     * variation that is being bought; without one the product's total counts.
     */
    public function isStockOut($product, $variation = null): bool
    {
        return !productStockManaged($product) || resolveStock($product, $variation) <= 0;
    }

    /**
     * 'preorder' — out of stock and open for pre-order
     * 'closed'   — out of stock, pre-order on, but the limit is used up
     * null       — pre-order plays no part (in stock, or switched off)
     */
    public function state($product, $variation = null): ?string
    {
        if (!$this->isActive($product) || !$this->isEnabled($product, $variation) || !$this->isStockOut($product, $variation)) {
            return null;
        }

        return $this->isAvailable($product, $variation) ? 'preorder' : 'closed';
    }

    /** Should a purchase of this product/variation go through as a pre-order? */
    public function appliesTo($product, $variation = null): bool
    {
        return $this->state($product, $variation) === 'preorder';
    }

    /** State for a product card, where no variation has been picked yet. */
    public function cardState($product): ?string
    {
        if (!$this->isVariable($product)) {
            return $this->state($product);
        }

        if (!$this->isActive($product) || productIsOrderable($product)) {
            return null;
        }

        $variations = $product->relationLoaded('variations') ? $product->variations : $product->variations()->get();
        $closed = false;

        foreach ($variations as $variation) {
            $state = $this->state($product, $variation);
            if ($state === 'preorder') {
                return 'preorder';
            }
            $closed = $closed || $state === 'closed';
        }

        return $closed ? 'closed' : null;
    }

    /* ------------------------------------------------------------------
     | Price, advance, delivery
     | ------------------------------------------------------------------ */

    /** Mirrors CartController::calculatePrice() / CheckoutController::serverUnitPrice(). */
    public function unitPrice($product, $variation = null): float
    {
        if ($variation) {
            $base = (float) ($variation->price ?? 0);
            if ($base <= 0) {
                $base = (float) ($product->sell_price ?? 0);
            }

            $after = (float) ($variation->after_discount_price ?? 0);
            $other = (float) ($variation->discount_price ?? 0);

            if ($after > 0 && $after < $base) return $after;
            if ($other > 0 && $other < $base) return $other;

            return $base;
        }

        $base  = (float) ($product->sell_price ?? 0);
        $after = (float) ($product->after_discount ?? 0);

        return ($after > 0 && $after < $base) ? $after : $base;
    }

    /**
     * Advance for ONE unit, 0 when no advance can be taken. An advance that
     * would not be smaller than the price is not an advance, so it is ignored.
     */
    public function advancePerUnit($product, $variation = null, ?float $unitPrice = null): float
    {
        $settings = $this->settings($product, $variation);

        if (!in_array($settings['payment_type'], ['advance', 'both'], true)) {
            return 0.0;
        }

        $amount = $settings['advance_amount'];
        if ($amount <= 0 || empty($settings['advance_type'])) {
            return 0.0;
        }

        $unitPrice = $unitPrice ?? $this->unitPrice($product, $variation);

        $advance = $settings['advance_type'] === 'percentage'
            ? round($unitPrice * $amount / 100)
            : round($amount, 2);

        return ($advance > 0 && $advance < $unitPrice) ? (float) $advance : 0.0;
    }

    /** Payment modes the customer may pick: ['full'], ['advance'] or both. */
    public function paymentModes($product, $variation = null, ?float $unitPrice = null): array
    {
        if ($this->advancePerUnit($product, $variation, $unitPrice) <= 0) {
            return [self::MODE_FULL];
        }

        return match ($this->settings($product, $variation)['payment_type']) {
            'advance' => [self::MODE_ADVANCE],
            'both'    => [self::MODE_FULL, self::MODE_ADVANCE],
            default   => [self::MODE_FULL],
        };
    }

    /** The requested mode when it is allowed, otherwise the product's default. */
    public function resolvePaymentMode($product, $variation = null, ?string $requested = null, ?float $unitPrice = null): string
    {
        $modes = $this->paymentModes($product, $variation, $unitPrice);

        return in_array($requested, $modes, true) ? $requested : $modes[0];
    }

    /** "Estimated Delivery: 15–20 October" — the admin's text, else built from the dates. */
    public function deliveryLabel($product, $variation = null): ?string
    {
        $settings = $this->settings($product, $variation);

        return self::formatDelivery($settings['delivery_text'], $settings['delivery_start'], $settings['delivery_end']);
    }

    public static function formatDelivery($text, $start, $end): ?string
    {
        if (is_string($text) && trim($text) !== '') {
            return trim($text);
        }

        $range = self::formatDateRange($start, $end);

        return $range ? 'Estimated Delivery: ' . $range : null;
    }

    public static function formatDateRange($start, $end): ?string
    {
        $start = $start ? Carbon::parse($start) : null;
        $end   = $end ? Carbon::parse($end) : null;

        if (!$start && !$end) {
            return null;
        }
        if (!$start || !$end || $start->isSameDay($end)) {
            return self::formatDay($start ?: $end);
        }
        if ($start->isSameMonth($end, true)) {
            return $start->format('j') . '–' . self::formatDay($end);
        }

        return self::formatDay($start, $start->year !== $end->year) . ' – ' . self::formatDay($end);
    }

    private static function formatDay(Carbon $date, bool $forceYear = false): string
    {
        return $date->format(($forceYear || $date->year !== now()->year) ? 'j F Y' : 'j F');
    }

    /** Everything a storefront view needs about one product/variation. */
    public function displayData($product, $variation = null, ?float $unitPrice = null): array
    {
        $state = $this->state($product, $variation);
        $unitPrice = $unitPrice ?? $this->unitPrice($product, $variation);

        return [
            'state'     => $state,
            'active'    => $state === 'preorder',
            'closed'    => $state === 'closed',
            'delivery'  => $state ? $this->deliveryLabel($product, $variation) : null,
            'remaining' => $state ? $this->remaining($product, $variation) : null,
            'modes'     => $state === 'preorder' ? $this->paymentModes($product, $variation, $unitPrice) : [],
            'advance'   => $state === 'preorder' ? $this->advancePerUnit($product, $variation, $unitPrice) : 0,
        ];
    }

    /* ------------------------------------------------------------------
     | Counters (preorder_sold_qty)
     | ------------------------------------------------------------------ */

    /**
     * Rows are always locked product first, then variation, so two orders can
     * never deadlock each other and the second one always sees the first
     * one's count.
     */
    private function lockTargets(int $productId, $variationId): array
    {
        $product = Product::whereKey($productId)->lockForUpdate()->first();
        $variation = null;

        if ($product && $this->isVariable($product) && !empty($variationId)) {
            $variation = Variation::whereKey($variationId)
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->first();
        }

        return [$product, $variation];
    }

    private function bump($product, $variation, int $delta): void
    {
        foreach (array_filter([$product, $variation]) as $row) {
            $value = max(0, (int) $row->preorder_sold_qty + $delta);

            // Query builder on purpose: a counter change is not an edit of the
            // product, so updated_at and the model events stay untouched.
            DB::table($row->getTable())->where('id', $row->id)->update(['preorder_sold_qty' => $value]);

            $row->preorder_sold_qty = $value;
            $row->syncOriginalAttribute('preorder_sold_qty');
        }
    }

    /**
     * Takes $qty pre-order slots. With $enforceLimit the limit is checked
     * under the row lock, so concurrent checkouts can never oversell it.
     * Admin corrections (restoring a cancelled order) pass false.
     *
     * @throws PreOrderException
     */
    public function reserve(int $productId, $variationId, int $qty, bool $enforceLimit = true): void
    {
        if ($qty <= 0) {
            return;
        }

        DB::transaction(function () use ($productId, $variationId, $qty, $enforceLimit) {
            [$product, $variation] = $this->lockTargets($productId, $variationId);

            if (!$product) {
                if ($enforceLimit) {
                    throw new PreOrderException('Product not found.');
                }
                return;
            }

            if ($enforceLimit) {
                if (!$this->isEnabled($product, $variation)) {
                    throw new PreOrderException("Pre-order is not available for {$product->name}.");
                }

                $remaining = $this->remaining($product, $variation);
                if ($remaining !== null && $remaining < $qty) {
                    throw new PreOrderException($remaining > 0
                        ? "Only {$remaining} pre-order slot(s) left for {$product->name}."
                        : "Pre-order is closed for {$product->name}.");
                }
            }

            $this->bump($product, $variation, $qty);
        });
    }

    /** Gives $qty slots back (cancel / refund / delete). Never drops below zero. */
    public function release(int $productId, $variationId, int $qty): void
    {
        if ($qty <= 0) {
            return;
        }

        DB::transaction(function () use ($productId, $variationId, $qty) {
            [$product, $variation] = $this->lockTargets($productId, $variationId);

            if ($product) {
                $this->bump($product, $variation, -$qty);
            }
        });
    }

    /* ------------------------------------------------------------------
     | Cart
     | ------------------------------------------------------------------ */

    /** Does the session cart hold pre-order items? (A cart is never mixed.) */
    public function cartIsPreorder(array $cart): bool
    {
        foreach ($cart as $item) {
            if (!empty($item['is_preorder'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Totals for a pre-order cart. One payment mode applies to the whole
     * order: advance as soon as any item was added with it.
     */
    public function cartSummary(array $cart): array
    {
        $isPreorder = $this->cartIsPreorder($cart);
        $subtotal = 0;
        $advance = 0;
        $mode = self::MODE_FULL;
        $deliveries = [];

        foreach ($cart as $item) {
            $qty = (int) ($item['quantity'] ?? 0);
            $subtotal += (float) ($item['price'] ?? 0) * $qty;

            if (empty($item['is_preorder'])) {
                continue;
            }

            if (($item['payment_mode'] ?? self::MODE_FULL) === self::MODE_ADVANCE) {
                $mode = self::MODE_ADVANCE;
            }
            if (!empty($item['preorder_delivery'])) {
                $deliveries[$item['preorder_delivery']] = true;
            }
        }

        if ($mode === self::MODE_ADVANCE) {
            foreach ($cart as $item) {
                $qty = (int) ($item['quantity'] ?? 0);
                $perUnit = (float) ($item['preorder_advance'] ?? 0);
                // An item without an advance option is paid in full up front.
                $advance += ($perUnit > 0 ? $perUnit : (float) ($item['price'] ?? 0)) * $qty;
            }
        }

        return [
            'is_preorder'  => $isPreorder,
            'payment_mode' => $isPreorder ? $mode : null,
            'subtotal'     => $subtotal,
            'advance'      => $isPreorder && $mode === self::MODE_ADVANCE ? round(min($advance, $subtotal), 2) : 0,
            'deliveries'   => array_keys($deliveries),
        ];
    }

    /**
     * Re-checks a pre-order cart against the database right before the order
     * is created and returns what the order needs: the payment mode, the
     * advance to collect and, per cart line, the delivery estimate to freeze
     * on the order line.
     *
     * @throws PreOrderException when the cart can no longer be pre-ordered
     */
    public function planForCart(array $cart): array
    {
        $lines = [];
        $mode = self::MODE_FULL;

        foreach ($cart as $item) {
            $name = $item['name'] ?? 'This product';

            if (empty($item['is_preorder'])) {
                throw new PreOrderException('Pre-order items and in-stock items cannot be ordered together. Please check them out separately.');
            }

            $product = Product::find($item['product_id'] ?? 0);
            $variation = ($product && !empty($item['variation_id']))
                ? Variation::where('id', $item['variation_id'])->where('product_id', $product->id)->first()
                : null;

            if (!$product || !$this->appliesTo($product, $variation)) {
                throw new PreOrderException("Pre-order is no longer available for {$name}. Please remove it from your cart and add it again.");
            }

            $price = (float) ($item['price'] ?? 0);
            $settings = $this->settings($product, $variation);

            if ($this->resolvePaymentMode($product, $variation, $item['payment_mode'] ?? null, $price) === self::MODE_ADVANCE) {
                $mode = self::MODE_ADVANCE;
            }

            $lines[($item['product_id'] ?? 0) . '_' . ($item['variation_id'] ?? 0)] = [
                'qty'            => (int) ($item['quantity'] ?? 0),
                'price'          => $price,
                'advance'        => $this->advancePerUnit($product, $variation, $price),
                'delivery_text'  => $settings['delivery_text'],
                'delivery_start' => $settings['delivery_start'],
                'delivery_end'   => $settings['delivery_end'],
            ];
        }

        $advance = 0;
        if ($mode === self::MODE_ADVANCE) {
            foreach ($lines as $line) {
                // An item without an advance option is paid in full up front.
                $advance += ($line['advance'] > 0 ? $line['advance'] : $line['price']) * $line['qty'];
            }
        }

        return [
            'payment_mode'   => $mode,
            'advance_amount' => round($advance, 2),
            'lines'          => $lines,
        ];
    }

    /** Fields to freeze on an order line for one planned cart line. */
    public function lineAttributes(array $plannedLine): array
    {
        return [
            'is_preorder'              => 1,
            'preorder_stock_allocated' => 0,
            'estimated_delivery_text'  => $plannedLine['delivery_text'],
            'estimated_delivery_start' => $plannedLine['delivery_start'],
            'estimated_delivery_end'   => $plannedLine['delivery_end'],
        ];
    }

    /** Advance needs money up front — cash on delivery cannot provide that. */
    public function isCashOnDelivery($paymentMethod): bool
    {
        return in_array(strtolower(trim((string) $paymentMethod)), ['', 'cod', 'cash on delivery', 'cash_on_delivery'], true);
    }

    /* ------------------------------------------------------------------
     | Order lines: slot vs. stock
     | ------------------------------------------------------------------ */

    /**
     * While an order is "Pre-Order" its pre-order lines hold a slot, not
     * stock. Once it moves on (the product has arrived) the real stock is
     * deducted for them; moving it back returns that stock. Safe to call any
     * number of times.
     */
    public function syncAllocation(Order $order): void
    {
        if (empty($order->is_preorder) || !Order::statusHoldsStock($order->status)) {
            return;
        }

        $shouldBeAllocated = strcasecmp(trim((string) $order->status), self::STATUS_PREORDER) !== 0;

        DB::transaction(function () use ($order, $shouldBeAllocated) {
            $util = app(Util::class);

            foreach (OrderDetails::where('order_id', $order->id)->where('is_preorder', 1)->get() as $line) {
                if ((bool) $line->preorder_stock_allocated === $shouldBeAllocated) {
                    continue;
                }

                if ($shouldBeAllocated) {
                    $util->decreaseProductStock($line->product_id, $line->variation_id, (int) $line->quantity);
                } else {
                    $util->increaseProductStock($line->product_id, $line->variation_id, (int) $line->quantity);
                }

                $line->preorder_stock_allocated = $shouldBeAllocated;
                $line->save();
            }
        });
    }

    /** Called from the Order model whenever a pre-order's status changes. */
    public function handleStatusChanged(Order $order, $oldStatus): void
    {
        // A change that crosses the cancelled/returned line is settled by the
        // controller that made it (it moves the lines' slots/stock first) and
        // then calls syncAllocation() itself.
        if (Order::statusHoldsStock($oldStatus) && Order::statusHoldsStock($order->status)) {
            $this->syncAllocation($order);
        }

        $new = strtolower(trim((string) $order->status));
        if (in_array($new, [strtolower(self::STATUS_PROCESSING), strtolower(self::STATUS_READY)], true)) {
            $this->notify($order, new PreOrderStatusChanged($order, $order->status));
        }
    }

    /**
     * Moves pre-orders one step forward (Pre-Order → Processing → Ready to
     * Ship). Returns [moved ids, skipped ids].
     */
    public function advanceStatus(iterable $orders, string $toStatus): array
    {
        $from = array_search($toStatus, self::FLOW, true);
        $moved = $skipped = [];

        foreach ($orders as $order) {
            if (!$from || empty($order->is_preorder) || strcasecmp(trim((string) $order->status), $from) !== 0) {
                $skipped[] = $order->id;
                continue;
            }

            $old = ['status' => $order->status];
            $order->status = $toStatus;
            $order->save();

            if (function_exists('logActivity')) {
                logActivity('Update Status', 'Order', "Pre-order #{$order->id} moved to {$toStatus}", $order->id, $old, ['status' => $toStatus]);
            }

            $moved[] = $order->id;
        }

        return [$moved, $skipped];
    }

    /* ------------------------------------------------------------------
     | Money
     | ------------------------------------------------------------------ */

    /** Books money received for a pre-order; the order stays in its status. */
    public function applyPayment(Order $order, float $amount): Order
    {
        $final = (float) $order->final_amount;

        $order->advance_paid = round(min($final, (float) $order->advance_paid + max(0, $amount)), 2);
        $order->due_amount = round(max(0, $final - (float) $order->advance_paid), 2);
        $order->payment_status = $order->due_amount <= 0 ? 'Paid' : 'Partial';
        $order->save();

        return $order;
    }

    /**
     * A payment gateway confirmed the customer's payment for a pre-order.
     * Unlike a normal order it is not moved to "Confirmed" — it keeps waiting
     * in "Pre-Order"; only the money is booked and the customer is emailed.
     * A repeated callback for an order that was already settled does nothing.
     */
    public function settleGatewayPayment(Order $order, float $amount): bool
    {
        if (empty($order->is_preorder) || (float) $order->advance_paid > 0) {
            return false;
        }

        $this->applyPayment($order, $amount > 0 ? $amount : $order->payableNow());
        $this->notifyPlaced($order);

        return true;
    }

    /**
     * The admin confirms the advance arrived (manual bKash/Nagad transfer).
     * Recorded as a normal order payment so the order's payment history shows it.
     */
    public function markAdvanceReceived(Order $order): float
    {
        $amount = round((float) $order->advance_amount - (float) $order->advance_paid, 2);
        if (empty($order->is_preorder) || $amount <= 0) {
            return 0.0;
        }

        DB::transaction(function () use ($order, $amount) {
            OrderPayment::create([
                'order_id'   => $order->id,
                'method'     => $order->payment_method ?: 'cash',
                'amount'     => $amount,
                'date'       => now()->toDateString(),
                'account_no' => $order->sender_number,
                'tnx_id'     => $order->transaction_id,
                'note'       => 'Pre-order advance',
            ]);

            $this->applyPayment($order, $amount);
        });

        return $amount;
    }

    /* ------------------------------------------------------------------
     | Delivery date changes
     | ------------------------------------------------------------------ */

    /**
     * Sets a new estimated delivery on the pre-order lines of the given
     * orders (optionally only the lines of one product/variation), logs each
     * change and emails the customers. Returns the number of orders changed.
     */
    public function changeDeliveryDate(iterable $orders, ?string $text, $start, $end, ?int $productId = null, ?int $variationId = null): int
    {
        $start = $start ? Carbon::parse($start)->toDateString() : null;
        $end   = $end ? Carbon::parse($end)->toDateString() : null;
        $text  = $this->filled($text) ? trim($text) : null;
        $changed = [];

        DB::transaction(function () use ($orders, $text, $start, $end, $productId, $variationId, &$changed) {
            foreach ($orders as $order) {
                $lines = OrderDetails::where('order_id', $order->id)
                    ->where('is_preorder', 1)
                    ->when($productId, fn ($q) => $q->where('product_id', $productId))
                    ->when($variationId, fn ($q) => $q->where('variation_id', $variationId))
                    ->get();

                $logIds = [];
                $oldLabel = null;

                foreach ($lines as $line) {
                    $oldStart = $line->estimated_delivery_start ? Carbon::parse($line->estimated_delivery_start)->toDateString() : null;
                    $oldEnd   = $line->estimated_delivery_end ? Carbon::parse($line->estimated_delivery_end)->toDateString() : null;

                    if ($oldStart === $start && $oldEnd === $end && $line->estimated_delivery_text === $text) {
                        continue;
                    }

                    $oldLabel = $oldLabel ?: $line->deliveryLabel();

                    $logIds[] = PreorderDeliveryLog::create([
                        'order_id'        => $order->id,
                        'order_detail_id' => $line->id,
                        'product_id'      => $line->product_id,
                        'old_date'        => $oldStart,
                        'new_date'        => $start,
                        'old_end_date'    => $oldEnd,
                        'new_end_date'    => $end,
                        'old_text'        => $line->estimated_delivery_text,
                        'new_text'        => $text,
                        'changed_by'      => auth()->id(),
                    ])->id;

                    $line->estimated_delivery_text  = $text;
                    $line->estimated_delivery_start = $start;
                    $line->estimated_delivery_end   = $end;
                    $line->save();
                }

                if ($logIds) {
                    $changed[] = [$order, $logIds, $oldLabel];
                }
            }
        });

        $newLabel = self::formatDelivery($text, $start, $end);

        foreach ($changed as [$order, $logIds, $oldLabel]) {
            if ($this->notify($order, new PreOrderDeliveryDateChanged($order, $oldLabel, $newLabel))) {
                PreorderDeliveryLog::whereIn('id', $logIds)->update(['notified_via' => 'mail', 'notified_at' => now()]);
            }

            if (function_exists('logActivity')) {
                logActivity('Update Delivery Date', 'Order', 'Estimated delivery changed' . ($oldLabel ? " from '{$oldLabel}'" : '') . " to '{$newLabel}'", $order->id);
            }
        }

        return count($changed);
    }

    /** Stores the new estimate on the product/variation so later orders get it too. */
    public function updateDeliverySetting(int $productId, ?int $variationId, ?string $text, $start, $end): void
    {
        $target = $variationId
            ? Variation::where('id', $variationId)->where('product_id', $productId)->first()
            : Product::find($productId);

        if (!$target) {
            return;
        }

        $target->preorder_delivery_text  = $this->filled($text) ? trim($text) : null;
        $target->preorder_delivery_start = $start ?: null;
        $target->preorder_delivery_end   = $end ?: null;
        $target->save();
    }

    /* ------------------------------------------------------------------
     | Notifications (email only)
     | ------------------------------------------------------------------ */

    /**
     * Emails the order's customer. False when there is no address to send to.
     *
     * The mail is sent immediately (no queue). Inside a database transaction
     * it waits for the commit, so an action that is rolled back emails nobody.
     */
    public function notify(Order $order, $notification): bool
    {
        if (!filter_var((string) $order->email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        DB::afterCommit(function () use ($order, $notification) {
            try {
                $order->notify($notification);
            } catch (\Throwable $e) {
                // A mail problem must never undo or block an order action.
                Log::error('Pre-order notification failed for order ' . $order->id . ': ' . $e->getMessage());
            }
        });

        return true;
    }

    public function notifyPlaced(Order $order): bool
    {
        return $this->notify($order, new PreOrderPlaced($order));
    }

    public function sendDueReminder(Order $order): bool
    {
        if (empty($order->is_preorder) || (float) $order->due_amount <= 0) {
            return false;
        }

        if (!$this->notify($order, new PreOrderDuePaymentReminder($order))) {
            return false;
        }

        $order->forceFill(['due_reminder_sent_at' => now()])->saveQuietly();

        return true;
    }
}
