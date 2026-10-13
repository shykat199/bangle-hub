<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\PreOrderDeliveryDateRequest;
use App\Http\Requests\Backend\PreOrderFilterRequest;
use App\Http\Requests\Backend\PreOrderSelectionRequest;
use App\Http\Requests\Backend\PreOrderStatusRequest;
use App\Models\Order;
use App\Models\Product;
use App\Services\PreOrderService;
use Illuminate\Http\Request;

/**
 * Admin "Pre-Orders" screen: the list with its filters, moving pre-orders
 * forward when the product arrives, changing the estimated delivery date and
 * the money side (advance received, due reminder).
 *
 * Access: the admin route group (auth + staff) plus the order.view /
 * order.edit permissions checked by the Form Requests. Workers only ever see
 * and touch the orders assigned to them, as on the normal order list.
 */
class PreOrderController extends Controller
{
    public function __construct(private PreOrderService $preorders)
    {
    }

    /** Pre-orders this user may work with. */
    private function scoped()
    {
        return Order::preorders()
            ->when(auth()->user()->hasRole('worker'), fn ($q) => $q->where('assign_user_id', auth()->id()));
    }

    private function selected(array $ids)
    {
        return $this->scoped()->whereIn('id', $ids)->orderBy('id')->get();
    }

    public function index(PreOrderFilterRequest $request)
    {
        $filters = $request->validated();
        $status  = $filters['status'] ?? PreOrderService::STATUS_PREORDER;
        $q       = ltrim(trim((string) ($filters['q'] ?? '')), '#');

        $query = $this->scoped()->with(['details.product', 'details.variation.size', 'details.variation.color', 'assign']);

        if ($q !== '') {
            $query->where(function ($row) use ($q) {
                $row->where('orders.id', 'like', "%{$q}%")
                    ->orWhere('orders.invoice_no', 'like', "%{$q}%")
                    ->orWhere('orders.first_name', 'like', "%{$q}%")
                    ->orWhere('orders.last_name', 'like', "%{$q}%")
                    ->orWhere('orders.mobile', 'like', "%{$q}%")
                    ->orWhere('orders.email', 'like', "%{$q}%")
                    ->orWhereHas('details.product', fn ($p) => $p->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%"));
            });
        }

        if (!empty($filters['product_id'])) {
            $query->whereHas('details', fn ($d) => $d->where('product_id', $filters['product_id'])->where('is_preorder', 1));
        }

        if (!empty($filters['payment_mode'])) {
            $query->where('payment_mode', $filters['payment_mode']);
        }

        // Delivery window overlaps the chosen range (a line without an end
        // date is treated as a single day).
        if (!empty($filters['delivery_from']) || !empty($filters['delivery_to'])) {
            $from = $filters['delivery_from'] ?? null;
            $to   = $filters['delivery_to'] ?? null;

            $query->whereHas('details', function ($d) use ($from, $to) {
                $d->where('is_preorder', 1)->whereNotNull('estimated_delivery_start');
                if ($from) $d->whereRaw('COALESCE(estimated_delivery_end, estimated_delivery_start) >= ?', [$from]);
                if ($to)   $d->where('estimated_delivery_start', '<=', $to);
            });
        }

        // Tab counts follow every filter except the status itself.
        $counts = (clone $query)->reorder()
            ->selectRaw('orders.status, COUNT(*) as total')
            ->groupBy('orders.status')
            ->pluck('total', 'status');

        if ($status !== 'all') {
            $query->where('orders.status', $status);
        }

        $items = $query->orderByDesc('orders.id')->paginate(50)->appends($request->query());

        $products = Product::where(fn ($w) => $w->where('is_preorder', 1)
                ->orWhereHas('variations', fn ($v) => $v->where('is_preorder', 1))
                ->orWhereIn('id', fn ($sub) => $sub->select('product_id')->from('order_details')->where('is_preorder', 1)))
            ->with('variations.size', 'variations.color')
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        return view('backend.pre_orders.index', [
            'items'    => $items,
            'counts'   => $counts,
            'status'   => $status,
            'filters'  => $filters,
            'products' => $products,
            'tabs'     => [PreOrderService::STATUS_PREORDER, PreOrderService::STATUS_PROCESSING, PreOrderService::STATUS_READY],
        ]);
    }

    /** Pre-Order → Processing → Ready to Ship, for one order or many. */
    public function updateStatus(PreOrderStatusRequest $request)
    {
        [$moved, $skipped] = $this->preorders->advanceStatus($this->selected($request->order_ids), $request->status);

        if (empty($moved)) {
            return response()->json([
                'status' => false,
                'msg'    => 'Nothing was moved. Only orders in the step before "' . $request->status . '" can be moved to it.',
            ]);
        }

        $msg = count($moved) . ' pre-order(s) moved to ' . $request->status . '.';
        if ($skipped) {
            $msg .= ' ' . count($skipped) . ' skipped (not in the previous step).';
        }

        return response()->json(['status' => true, 'msg' => $msg]);
    }

    /** New estimated delivery: emails the customers and writes preorder_delivery_logs. */
    public function updateDeliveryDate(PreOrderDeliveryDateRequest $request)
    {
        $productId   = $request->scope === 'product' ? (int) $request->product_id : null;
        $variationId = $productId && $request->filled('variation_id') ? (int) $request->variation_id : null;

        if ($productId) {
            // Every pre-order of the product that has not shipped yet.
            $orders = $this->scoped()
                ->whereIn('orders.status', array_merge(array_keys(PreOrderService::FLOW), array_values(PreOrderService::FLOW)))
                ->whereHas('details', fn ($d) => $d->where('is_preorder', 1)
                    ->where('product_id', $productId)
                    ->when($variationId, fn ($q) => $q->where('variation_id', $variationId)))
                ->get();
        } else {
            $orders = $this->selected($request->order_ids);
        }

        $changed = $this->preorders->changeDeliveryDate(
            $orders,
            $request->delivery_text,
            $request->delivery_start,
            $request->delivery_end,
            $productId,
            $variationId
        );

        if ($productId && $request->boolean('update_product')) {
            $this->preorders->updateDeliverySetting($productId, $variationId, $request->delivery_text, $request->delivery_start, $request->delivery_end);
        }

        return response()->json([
            'status' => true,
            'msg'    => $changed
                ? "Delivery date updated on {$changed} pre-order(s). Customers with an email address have been notified."
                : 'No pre-order needed a change' . ($productId && $request->boolean('update_product') ? ' — the product setting was updated.' : '.'),
        ]);
    }

    /** The advance of a manual-payment pre-order has arrived. */
    public function markAdvanceReceived($id)
    {
        if (!auth()->user()->can('order.edit')) abort(403, 'unauthorized');

        $order  = $this->scoped()->findOrFail($id);
        $amount = $this->preorders->markAdvanceReceived($order);

        if ($amount <= 0) {
            return response()->json(['status' => false, 'msg' => 'There is no outstanding advance on this order.']);
        }

        logActivity('Order Payment', 'Order', 'Pre-order advance of ' . biz_format_currency($amount) . ' marked as received.', $order->id);

        return response()->json(['status' => true, 'msg' => 'Advance of ' . biz_format_currency($amount) . ' recorded. Due is now ' . biz_format_currency($order->due_amount) . '.']);
    }

    /** Emails a due payment reminder for the selected pre-orders that still owe money. */
    public function sendDueReminder(PreOrderSelectionRequest $request)
    {
        $sent = 0;
        $orders = $this->selected($request->order_ids);

        foreach ($orders as $order) {
            if ($this->preorders->sendDueReminder($order)) {
                $sent++;
            }
        }

        return response()->json([
            'status' => $sent > 0,
            'msg'    => $sent > 0
                ? "Due payment reminder sent for {$sent} of " . $orders->count() . ' selected order(s).'
                : 'No reminder sent — the selected orders have no due amount or no email address.',
        ]);
    }
}
