<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacebookFeedController extends Controller
{
    public function index()
    {
        // ✅ Feed ON/OFF check from settings table
        $feedStatus = DB::table('settings')
            ->where('key', 'facebook_feed')
            ->value('value');

        if ($feedStatus !== 'on') {
            return response('Facebook Feed Disabled', 403);
        }

        // ✅ Products + variations + variations stock sum
        $products = Product::where('status', 1)
            ->with('variations')
            ->withSum('variations', 'stock_quantity') // -> variations_sum_stock_quantity
            ->get();

        ob_start();

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">';
        echo '<channel>';
        echo '<title>Deshi Bazzar Products</title>';
        echo '<link>' . url('/') . '</link>';
        echo '<description>Facebook Product Feed</description>';

        foreach ($products as $product) {

            /**
             * ✅ PRICE LOGIC (Your site)
             * Regular = sell_price
             * Sale    = after_discount
             * Variable হলে variation থেকে min sell_price + min after_discount
             */

            // Default: simple product
            $regularPrice = (float) ($product->sell_price ?? 0);
            $salePrice    = (float) ($product->after_discount ?? 0);

            // Variable product handling
            if (($product->type ?? '') === 'variable' && $product->variations && $product->variations->isNotEmpty()) {

                $minRegular = (float) ($product->variations->min('sell_price') ?? 0);
                $minSale    = (float) ($product->variations->min('after_discount') ?? 0);

                if ($minRegular > 0) {
                    $regularPrice = $minRegular;
                }

                // sale only if valid discount
                if ($minSale > 0 && $minSale < $regularPrice) {
                    $salePrice = $minSale;
                } else {
                    $salePrice = 0;
                }
            }

            // safety: if regular is 0 but sale exists
            if ($regularPrice <= 0 && $salePrice > 0) {
                $regularPrice = $salePrice;
                $salePrice = 0;
            }

            // still invalid => skip
            if ($regularPrice <= 0) {
                continue;
            }

            /**
             * ✅ STOCK / AVAILABILITY
             * Prefer product total_stock, fallback variations sum
             */
            $totalStock = (int) ($product->total_stock ?? 0);

            $varSumStock = (int) ($product->variations_sum_stock_quantity ?? 0);
            if ($totalStock <= 0 && $varSumStock > 0) {
                $totalStock = $varSumStock;
            }

            $availability = $totalStock > 0 ? 'in stock' : 'out of stock';

            /**
             * ✅ IMAGE URL
             */
            $image = ltrim((string) ($product->image ?? ''), '/');
            $imageUrl = '';

            if (!empty($image)) {
                $imageUrl = str_starts_with($image, 'http')
                    ? $image
                    : url('products/' . $image);
            }

            /**
             * ✅ Product URL slug/id
             */
            $slugOrId = !empty($product->slug) ? $product->slug : $product->id;

            /**
             * ✅ Description safe fallback
             */
            $desc = $product->feed_description
                ?? strip_tags($product->description ?? '');

            echo '<item>';
            echo '<g:id>' . $product->id . '</g:id>';
            echo '<g:title><![CDATA[' . htmlspecialchars($product->name ?? '', ENT_QUOTES, 'UTF-8') . ']]></g:title>';
            echo '<g:description><![CDATA[' . $desc . ']]></g:description>';
            echo '<g:link>' . url('/product-show/' . $slugOrId) . '</g:link>';

            if (!empty($imageUrl)) {
                echo '<g:image_link><![CDATA[' . $imageUrl . ']]></g:image_link>';
            }

            echo '<g:availability>' . $availability . '</g:availability>';
            echo '<g:condition>new</g:condition>';

            // ✅ Regular price always
            echo '<g:price>' . number_format($regularPrice, 2, '.', '') . ' BDT</g:price>';

            // ✅ Sale price only if valid discount
            if ($salePrice > 0 && $salePrice < $regularPrice) {
                echo '<g:sale_price>' . number_format($salePrice, 2, '.', '') . ' BDT</g:sale_price>';
            }

            echo '</item>';
        }

        echo '</channel>';
        echo '</rss>';

        return response(ob_get_clean(), 200)
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }

    public function settings()
    {
        $feedStatus = DB::table('settings')
            ->where('key', 'facebook_feed')
            ->value('value');

        return view('backend.facebook_feed.settings', compact('feedStatus'));
    }

    public function toggle(Request $request)
    {
        $status = ((int) $request->status === 1) ? 'on' : 'off';

        DB::table('settings')->updateOrInsert(
            ['key' => 'facebook_feed'],
            ['value' => $status]
        );

        return response()->json([
            'success' => true,
            'status'  => $status
        ]);
    }
}
