<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\StockNotification;
use Illuminate\Http\Request;

class StockNotificationController extends Controller
{
    /** "Notify Me When Available" requests left by customers on out-of-stock products. */
    public function index(Request $request)
    {
        if (!auth()->user()->can('product.view')) {
            abort(403, 'unauthorized');
        }

        $status = $request->get('status', 'pending');
        $q = trim((string) $request->get('q'));

        $items = StockNotification::with('product')
            ->when(in_array($status, ['pending', 'notified']), fn ($query) => $query->where('status', $status))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('phone', 'like', "%{$q}%")
                      ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%"));
                });
            })
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $counts = StockNotification::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('backend.stock_notifications.index', compact('items', 'status', 'q', 'counts'));
    }

    /** Toggle pending ⇄ notified (e.g. after calling the customer). */
    public function toggle($id)
    {
        if (!auth()->user()->can('product.edit')) {
            abort(403, 'unauthorized');
        }

        $item = StockNotification::findOrFail($id);
        $notified = $item->status !== 'notified';
        $item->update([
            'status'      => $notified ? 'notified' : 'pending',
            'notified_at' => $notified ? now() : null,
        ]);

        return back()->with('success', $notified ? 'Marked as notified.' : 'Moved back to pending.');
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('product.delete')) {
            abort(403, 'unauthorized');
        }

        StockNotification::findOrFail($id)->delete();

        return back()->with('success', 'Request deleted.');
    }
}
