<?php

namespace App\Support;

use App\Models\Product;
use App\Models\Wishlist as WishlistRow;
use Illuminate\Support\Facades\Cookie;

/**
 * A customer's saved products.
 *
 * Logged-in customers: rows in the wishlists table, so the list follows the
 * account across devices. Guests: a one-year cookie holding product ids. The
 * first time a logged-in request arrives with that cookie, its products are
 * moved into the account and the cookie is dropped.
 */
class Wishlist
{
    private const COOKIE = 'wishlist';
    private const COOKIE_MINUTES = 60 * 24 * 365;
    private const GUEST_LIMIT = 100;

    /** Per-request cache of the current customer's product ids, newest first. */
    private static ?array $ids = null;

    /** @return int[] */
    public static function ids(): array
    {
        if (self::$ids !== null) {
            return self::$ids;
        }

        if (!auth()->check()) {
            return self::$ids = self::guestIds();
        }

        self::adoptGuestList();

        return self::$ids = WishlistRow::where('user_id', auth()->id())
            ->orderByDesc('id')
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public static function has($productId): bool
    {
        return in_array((int) $productId, self::ids(), true);
    }

    public static function count(): int
    {
        return count(self::ids());
    }

    /** Saves the product, or removes it if it was already saved. Returns true when it is saved afterwards. */
    public static function toggle(int $productId): bool
    {
        if (self::has($productId)) {
            self::remove($productId);
            return false;
        }

        self::add($productId);
        return true;
    }

    public static function add(int $productId): void
    {
        if (self::has($productId)) {
            return;
        }

        if (auth()->check()) {
            WishlistRow::firstOrCreate(['user_id' => auth()->id(), 'product_id' => $productId]);
            self::$ids = null;
            return;
        }

        self::storeGuestIds(array_merge([$productId], self::ids()));
    }

    public static function remove(int $productId): void
    {
        if (auth()->check()) {
            WishlistRow::where('user_id', auth()->id())->where('product_id', $productId)->delete();
            self::$ids = null;
            return;
        }

        self::storeGuestIds(array_values(array_diff(self::ids(), [$productId])));
    }

    /** Saved products that can still be shown, in the order they were saved (newest first). */
    public static function products()
    {
        $ids = self::ids();
        if (empty($ids)) {
            return collect();
        }

        $order = array_flip($ids);

        return Product::with(['variation', 'category:id,name,url', 'variations.stocks', 'images'])
            ->where('status', 1)
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn ($p) => $order[$p->id] ?? PHP_INT_MAX)
            ->values();
    }

    /** @return int[] */
    private static function guestIds(): array
    {
        $raw = json_decode((string) request()->cookie(self::COOKIE, '[]'), true);

        return is_array($raw)
            ? array_values(array_unique(array_filter(array_map('intval', $raw), fn ($id) => $id > 0)))
            : [];
    }

    private static function storeGuestIds(array $ids): void
    {
        $ids = array_slice(array_values(array_unique($ids)), 0, self::GUEST_LIMIT);

        Cookie::queue(self::COOKIE, json_encode($ids), self::COOKIE_MINUTES);
        self::$ids = $ids;
    }

    /** Moves what the customer saved before logging in into their account. */
    private static function adoptGuestList(): void
    {
        $guestIds = self::guestIds();
        if (empty($guestIds)) {
            return;
        }

        // oldest first, so the newest save ends up with the highest id (= first in the list)
        foreach (array_reverse(Product::whereIn('id', $guestIds)->pluck('id')->all()) as $productId) {
            WishlistRow::firstOrCreate(['user_id' => auth()->id(), 'product_id' => $productId]);
        }

        Cookie::queue(Cookie::forget(self::COOKIE));
        request()->cookies->remove(self::COOKIE);
    }
}
