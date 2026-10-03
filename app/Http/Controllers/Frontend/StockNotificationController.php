<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockNotification;
use App\Models\Variation;
use Illuminate\Http\Request;

class StockNotificationController extends Controller
{
    /**
     * "Notify Me When Available" — stores a phone number against an
     * out-of-stock product (or one of its variations).
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id'   => 'required|integer|exists:products,id',
            'variation_id' => 'nullable|integer',
            'phone'        => 'required|string|max:20',
            'consent'      => 'accepted',
        ], [
            'consent.accepted' => 'Please agree to receive SMS notifications.',
        ]);

        // 01712-345678 / +8801712345678 / 8801712345678 → 01712345678
        $phone = preg_replace('/\D+/', '', $request->phone);
        if (str_starts_with($phone, '880')) {
            $phone = substr($phone, 2);
        }
        if (!preg_match('/^01[3-9]\d{8}$/', $phone)) {
            return response()->json([
                'status'  => false,
                'message' => 'Please enter a valid mobile number (e.g. 01712345678).',
            ], 422);
        }

        $product = Product::findOrFail($request->product_id);

        $variation = null;
        if ($request->filled('variation_id')) {
            $variation = Variation::where('product_id', $product->id)->find($request->variation_id);
        }

        $variantName = null;
        if ($variation && $product->type === 'variable') {
            $variantName = $variation->display_title;
        }

        // One pending request per phone + product (+ variant)
        $alreadyRequested = StockNotification::where('product_id', $product->id)
            ->where('variation_id', $variation?->id)
            ->where('phone', $phone)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyRequested) {
            return response()->json([
                'status'  => false,
                'already' => true,
                'message' => 'You have already requested a notification for this product with this number. We will SMS you when it is back in stock.',
            ], 409);
        }

        StockNotification::create([
            'product_id'   => $product->id,
            'variation_id' => $variation?->id,
            'variant_name' => $variantName,
            'phone'        => $phone,
            'status'       => 'pending',
            'ip'           => $request->ip(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => "You will be notified when this product is back in stock!",
        ]);
    }
}
