<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\Wishlist;
use Illuminate\Http\Request;

class UserWishlistController extends Controller
{
    /** The wishlist page — open to guests too (their list lives in a cookie). */
    public function index()
    {
        $items = Wishlist::products();

        return view('frontend.wishlist.index', compact('items'));
    }

    /** Heart buttons: save the product, or remove it when it is already saved. */
    public function toggle(Request $request)
    {
        $product = Product::where('status', 1)->find((int) $request->input('product_id'));

        if (!$product) {
            return response()->json(['status' => false, 'msg' => 'This product is no longer available.'], 404);
        }

        $saved = Wishlist::toggle($product->id);

        return response()->json([
            'status' => true,
            'saved'  => $saved,
            'count'  => Wishlist::count(),
            'msg'    => $saved ? 'Saved to your wishlist' : 'Removed from your wishlist',
        ]);
    }
}
