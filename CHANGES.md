# Bizcare — Cart Sidebar & OTP Fixes

Site: `allproducts.demo.bizcareit.com`
Applied: 2026-08-23

All paths are relative to the Laravel app root:
`/home/demobizcare/public_html/allproducts.demo.bizcareit.com/`

---

## 1. OTP: 6 digits generated but input only accepted 4

**File:** `app/Http/Controllers/Frontend/CheckoutController.php` (line 1331)

The generator was producing a 6-digit code while the checkout form's OTP input
had `maxlength="4"`, so only the first 4 digits could ever be typed and
`hash_equals()` always failed ("Invalid code!"). After 5 tries the user was
locked out by `OTP_MAX_ATTEMPTS`.

Reverted the generator to 4 digits to match the existing input and the existing
"Your 4 digit code" messages (lines 1326, 1350).

```diff
-        $otp = random_int(100000, 999999);
+        $otp = random_int(1000, 9999);
```

Note: 4 digits = 10,000 combinations vs 1,000,000 for 6. The brute-force risk is
mitigated by `OTP_MAX_ATTEMPTS = 5` and `OTP_VALID_MINUTES = 10`.

---

## 2. Quantity +/- in the cart sidebar reloaded the whole page

**File:** `resources/views/frontend/partials/js.blade.php` (line 72)

The handler already refreshed the sidebar over AJAX, then immediately threw that
work away with a full page reload. Combined with issue 3 below, the reload left
the user back on the product page with an empty sidebar.

```diff
                 $(document).find('div.cart_other_details').html(res.html3);
-                window.location.reload();
+                // No reload here: the AJAX above already refreshed the cart sidebar.
             }else{
```

---

## 3. Cart sidebar rendered empty on every page load

**File:** `resources/views/frontend/partials/footer.blade.php` (line 1133)

The container was emitted empty and only ever filled by an AJAX response. So on
any fresh page load — including after the reload in issue 2 — the sidebar had no
contents and no Checkout button, even when the cart badge showed items.

```diff
-<div class="cart-dropdown" id="cart-dropdown"></div>
+<div class="cart-dropdown" id="cart-dropdown">
+    {{-- Render the sidebar server-side so it is never empty on a fresh page load. --}}
+    @include('frontend.partials.cart_sidebar', [
+        'cart' => session()->get('cart', []),
+        'segm' => request()->segment(1) ?? 'home',
+    ])
+</div>
```

Variable names match `CartController.php` lines 89-93, which renders the same
partial with `compact('cart','segm')`.

---

## 4. "Order Now" fallback pointed at a non-existent route

**Files:**
- `resources/views/frontend/products/show.blade.php` (line 1969)
- `resources/views/frontend/app.blade.php` (line 826)

`routes/web.php:224` declares `Route::resource('/checkouts', CheckoutController::class)`,
so the checkout URL is `/checkouts` (plural). Both fallbacks used the singular
form, which returns 404.

```diff
-{{ url('/checkout') }}
+{{ url('/checkouts') }}
```

---

## Verified after the fixes (live)

| Check | Before | After |
|---|---|---|
| Sidebar contents on page load | 0 chars (empty) | 5,723 chars |
| Checkout button | missing | present, links to `/checkouts` |
| Quantity `+` click | full page reload | no reload, sidebar survives |
| Quantity value | — | 2 to 3 |
| `/checkout` (singular) left in page | yes | no |

No-reload was confirmed by setting a marker variable on `window` before the
click; the marker was still present afterwards.

---

## Still open (not changed — outside the approved scope)

`resources/views/frontend/app.blade.php` line 821 targets `#cart_section`:

```js
if(res.view) $('#cart_section').html(res.view);
```

No element with that id exists on the site; the correct target is
`#cart-dropdown`. The line is currently a no-op. `show.blade.php` has its own
handler that uses the right selector, so nothing is visibly broken today.

---

## Line endings

`js.blade.php` and `footer.blade.php` were saved through cPanel's legacy editor,
which converted them from CRLF to LF throughout. No functional impact on Blade or
PHP. `show.blade.php` and `app.blade.php` kept their original CRLF.
