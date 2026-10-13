<?php

namespace App\Models\Concerns;

use App\Models\Product;
use App\Services\PreOrderService;

/**
 * Pre-order helpers shared by Product and Variation. The rules themselves
 * live in PreOrderService; these only say "ask about me".
 *
 * On a Variation every answer already takes the product-level fallbacks and
 * the product-level limit into account.
 */
trait HasPreOrder
{
    public function initializeHasPreOrder(): void
    {
        $this->mergeCasts([
            'is_preorder'             => 'boolean',
            'preorder_limit'          => 'integer',
            'preorder_sold_qty'       => 'integer',
            'preorder_delivery_start' => 'date',
            'preorder_delivery_end'   => 'date',
            'preorder_advance_amount' => 'float',
        ]);
    }

    /** [product, variation|null] in the order PreOrderService expects. */
    private function preorderSubject(): array
    {
        return $this instanceof Product ? [$this, null] : [$this->product, $this];
    }

    /** is_preorder = true AND (limit is null OR preorder_sold_qty < preorder_limit). */
    public function isPreorderAvailable(): bool
    {
        [$product, $variation] = $this->preorderSubject();

        return $product ? app(PreOrderService::class)->isAvailable($product, $variation) : false;
    }

    /** Pre-order slots left; null when no limit is set. */
    public function preorderRemaining(): ?int
    {
        [$product, $variation] = $this->preorderSubject();

        return $product ? app(PreOrderService::class)->remaining($product, $variation) : 0;
    }

    /** Advance for one unit (0 = no advance option). */
    public function advanceAmount(?float $unitPrice = null): float
    {
        [$product, $variation] = $this->preorderSubject();

        return $product ? app(PreOrderService::class)->advancePerUnit($product, $variation, $unitPrice) : 0.0;
    }

    /** "Estimated Delivery: 15–20 October" */
    public function deliveryLabel(): ?string
    {
        [$product, $variation] = $this->preorderSubject();

        return $product ? app(PreOrderService::class)->deliveryLabel($product, $variation) : null;
    }
}
