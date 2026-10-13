<?php

namespace Tests\Feature;

use App\Exceptions\PreOrderException;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\PreorderDeliveryLog;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use App\Models\Variation;
use App\Notifications\PreOrderDeliveryDateChanged;
use App\Notifications\PreOrderDuePaymentReminder;
use App\Notifications\PreOrderPlaced;
use App\Notifications\PreOrderStatusChanged;
use App\Services\PreOrderService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Pre-order system: eligibility, limit, advance, variation-level pre-order,
 * mixed carts, checkout, admin flow, notifications.
 *
 * Runs against the project's MySQL database like the other feature tests,
 * inside a transaction that is rolled back — nothing it creates is kept.
 */
class PreOrderTest extends TestCase
{
    use DatabaseTransactions;

    private PreOrderService $preorders;

    protected function setUp(): void
    {
        parent::setUp();

        // This project keeps a cached config (bootstrap/cache/config.php), which
        // makes Laravel ignore the <env> overrides in phpunit.xml. Set what the
        // tests rely on explicitly so they behave the same with or without it.
        config(['session.driver' => 'array', 'queue.default' => 'sync', 'mail.default' => 'array']);
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $this->preorders = app(PreOrderService::class);

        Notification::fake();
        Http::fake();

        // Shop settings that would get in the way of placing several test orders.
        DB::table('informations')->update([
            'otp_system'          => 0,
            'is_mobile_check'     => 0,
            'is_ip_check'         => 0,
            'notification_active' => 0,
            'max_order_qty'       => 0,
            'max_order_amount'    => 0,
        ]);
    }

    /* ---------------------------------------------------------------- helpers */

    /** A single product priced 2000 with the given stock and pre-order settings. */
    private function product(array $preorder = [], int $stock = 0, array $attrs = []): Product
    {
        $product = Product::create(array_merge([
            'name'           => 'PO Test ' . uniqid(),
            'type'           => 'single',
            'category_id'    => DB::table('categories')->value('id') ?? 1,
            'sell_price'     => 2000,
            'is_stock'       => 1,
            'status'         => 1,
            'stock_quantity' => $stock,
        ], $attrs, $preorder));

        if ($product->type === 'single') {
            $this->variation($product, [], $stock);
        }

        return $product->fresh();
    }

    private function variation(Product $product, array $attrs = [], int $stock = 0): Variation
    {
        $variation = Variation::create(array_merge([
            'product_id'     => $product->id,
            'price'          => 2000,
            'stock_quantity' => $stock,
        ], $attrs));

        ProductStock::create(['product_id' => $product->id, 'variation_id' => $variation->id, 'quantity' => $stock]);

        return $variation->fresh();
    }

    private function preorderOn(array $extra = []): array
    {
        return array_merge([
            'is_preorder'            => 1,
            'preorder_delivery_text' => 'Estimated Delivery: 15–20 October',
            'preorder_payment_type'  => 'full',
        ], $extra);
    }

    private function addToCart(Product $product, array $data = [])
    {
        return $this->postJson(route('front.carts.store'), array_merge([
            'product_id'   => $product->id,
            'variation_id' => $product->variations()->orderBy('id')->value('id'),
            'quantity'     => 1,
        ], $data));
    }

    private function checkout(array $data = [])
    {
        return $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->post(route('front.checkouts.store'), array_merge([
                'first_name'         => 'Pre Order Tester',
                'mobile'             => '017' . random_int(10000000, 99999999),
                'shipping_address'   => 'House 1, Road 2, Dhaka',
                'payment_method'     => 'Cash on Delivery',
                'delivery_charge_id' => DB::table('delivery_charges')->value('id'),
                'email'              => 'customer@example.com',
            ], $data));
    }

    /** Places a pre-order through the real cart + checkout and returns the order. */
    private function placePreorder(Product $product, int $qty = 1, array $cart = [], array $checkout = []): Order
    {
        $this->addToCart($product, ['quantity' => $qty] + $cart)->assertOk();

        $response = $this->checkout($checkout)->assertOk();
        $this->assertTrue($response->json('success'), (string) $response->json('msg'));

        return Order::findOrFail($response->json('order_id'));
    }

    private function admin(): User
    {
        return User::findOrFail(1);
    }

    private function sold(Product $product): int
    {
        return (int) DB::table('products')->where('id', $product->id)->value('preorder_sold_qty');
    }

    private function stock(Product $product): int
    {
        return resolveStock($product->fresh());
    }

    /* ---------------------------------------------------------------- eligibility */

    public function test_preorder_is_available_only_when_switched_on_and_below_the_limit(): void
    {
        $off = $this->product();
        $this->assertFalse($off->isPreorderAvailable());
        $this->assertNull($this->preorders->state($off));

        $unlimited = $this->product($this->preorderOn());
        $this->assertTrue($unlimited->isPreorderAvailable());
        $this->assertNull($unlimited->preorderRemaining());
        $this->assertSame('preorder', $this->preorders->state($unlimited));

        $limited = $this->product($this->preorderOn(['preorder_limit' => 5, 'preorder_sold_qty' => 3]));
        $this->assertTrue($limited->isPreorderAvailable());
        $this->assertSame(2, $limited->preorderRemaining());

        $full = $this->product($this->preorderOn(['preorder_limit' => 5, 'preorder_sold_qty' => 5]));
        $this->assertFalse($full->isPreorderAvailable());
        $this->assertSame(0, $full->preorderRemaining());
        $this->assertSame('closed', $this->preorders->state($full));
    }

    public function test_preorder_does_not_apply_while_the_product_is_in_stock(): void
    {
        $product = $this->product($this->preorderOn(), 4);

        $this->assertTrue($product->isPreorderAvailable());
        $this->assertFalse($this->preorders->appliesTo($product), 'in-stock products sell normally');

        $this->addToCart($product)->assertOk()->assertJson(['success' => true, 'is_preorder' => false]);
        $this->assertFalse(session('cart')[array_key_first(session('cart'))]['is_preorder']);
    }

    public function test_delivery_label_uses_the_text_or_is_built_from_the_dates(): void
    {
        $withText = $this->product($this->preorderOn());
        $this->assertSame('Estimated Delivery: 15–20 October', $withText->deliveryLabel());

        $year = now()->year;
        $withDates = $this->product($this->preorderOn([
            'preorder_delivery_text'  => null,
            'preorder_delivery_start' => "$year-10-15",
            'preorder_delivery_end'   => "$year-10-20",
        ]));
        $this->assertSame('Estimated Delivery: 15–20 October', $withDates->deliveryLabel());

        $this->assertSame('28 October – 3 November', PreOrderService::formatDateRange("$year-10-28", "$year-11-03"));
    }

    /* ---------------------------------------------------------------- advance */

    public function test_advance_amount_calculation(): void
    {
        $fixed = $this->product($this->preorderOn(['preorder_payment_type' => 'advance', 'preorder_advance_type' => 'fixed', 'preorder_advance_amount' => 500]));
        $this->assertSame(500.0, $fixed->advanceAmount());
        $this->assertSame(['advance'], $this->preorders->paymentModes($fixed));

        $percent = $this->product($this->preorderOn(['preorder_payment_type' => 'both', 'preorder_advance_type' => 'percentage', 'preorder_advance_amount' => 25]));
        $this->assertSame(500.0, $percent->advanceAmount());
        $this->assertSame(['full', 'advance'], $this->preorders->paymentModes($percent));
        $this->assertSame('advance', $this->preorders->resolvePaymentMode($percent, null, 'advance'));
        $this->assertSame('full', $this->preorders->resolvePaymentMode($percent, null, 'something-else'));

        // the advance follows the discounted price the customer actually pays
        $discounted = $this->product($this->preorderOn(['preorder_payment_type' => 'advance', 'preorder_advance_type' => 'percentage', 'preorder_advance_amount' => 10]), 0, ['after_discount' => 1500]);
        $this->assertSame(150.0, $discounted->advanceAmount());

        // "Full" never takes an advance, and an advance that is not below the price is no advance
        $full = $this->product($this->preorderOn(['preorder_payment_type' => 'full', 'preorder_advance_type' => 'fixed', 'preorder_advance_amount' => 500]));
        $this->assertSame(0.0, $full->advanceAmount());

        $tooHigh = $this->product($this->preorderOn(['preorder_payment_type' => 'advance', 'preorder_advance_type' => 'fixed', 'preorder_advance_amount' => 2000]));
        $this->assertSame(0.0, $tooHigh->advanceAmount());
        $this->assertSame(['full'], $this->preorders->paymentModes($tooHigh));
    }

    public function test_advance_preorder_charges_the_advance_and_stores_the_due(): void
    {
        $product = $this->product($this->preorderOn(['preorder_payment_type' => 'both', 'preorder_advance_type' => 'fixed', 'preorder_advance_amount' => 500]));

        $this->addToCart($product, ['quantity' => 2, 'payment_mode' => 'advance'])->assertOk();

        // cash on delivery cannot collect an advance
        $refused = $this->checkout()->assertOk();
        $this->assertFalse($refused->json('success'));
        $this->assertStringContainsString('Advance payment', $refused->json('msg'));
        $this->assertSame(0, $this->sold($product), 'a refused checkout reserves nothing');

        // manual transfer with a transaction id
        $response = $this->checkout(['payment_method' => 'bKash', 'sender_number' => '01700000000', 'transaction_id' => 'TRX123'])->assertOk();
        $this->assertTrue($response->json('success'), (string) $response->json('msg'));

        $order = Order::findOrFail($response->json('order_id'));
        $shipping = (float) $order->shipping_charge;

        $this->assertSame('advance', $order->payment_mode);
        $this->assertEquals(1000, $order->advance_amount, '500 per piece x 2');
        $this->assertEquals(0, $order->advance_paid);
        $this->assertEquals(4000 + $shipping, $order->final_amount);
        $this->assertEquals(4000 + $shipping, $order->due_amount);
        $this->assertEquals(1000, $order->payableNow(), 'a gateway would charge the advance only');
        Notification::assertSentTo($order, PreOrderPlaced::class);

        // the admin confirms the transfer
        $this->actingAs($this->admin())
            ->postJson(route('admin.pre_orders.advance_received', $order->id))
            ->assertOk()->assertJson(['status' => true]);

        $order->refresh();
        $this->assertEquals(1000, $order->advance_paid);
        $this->assertEquals(3000 + $shipping, $order->due_amount);
        $this->assertSame('Partial', $order->payment_status);
        $this->assertSame(PreOrderService::STATUS_PREORDER, $order->status);
        $this->assertTrue($order->advanceIsPaid());

        // delivered = cash collected: nothing is due any more
        $order->status = 'Delivered';
        $order->payment_status = 'Paid';
        $order->save();
        $this->assertEquals(0, $order->fresh()->due_amount);
    }

    public function test_gateway_payment_books_the_advance_and_keeps_the_preorder_status(): void
    {
        $product = $this->product($this->preorderOn(['preorder_payment_type' => 'advance', 'preorder_advance_type' => 'fixed', 'preorder_advance_amount' => 500]));
        $order = $this->placePreorder($product, 1, [], ['payment_method' => 'bKash', 'sender_number' => '01700000000', 'transaction_id' => 'TRX1']);

        $this->assertTrue($this->preorders->settleGatewayPayment($order, 500));

        $order->refresh();
        $this->assertSame(PreOrderService::STATUS_PREORDER, $order->status, 'a paid pre-order is not moved to Confirmed');
        $this->assertEquals(500, $order->advance_paid);
        $this->assertEquals($order->final_amount - 500, $order->due_amount);

        // a repeated callback must not book the money twice
        $this->assertFalse($this->preorders->settleGatewayPayment($order, 500));
        $this->assertEquals(500, $order->fresh()->advance_paid);
        $this->assertSame(0.0, $order->fresh()->gatewayMinimum());
    }

    /* ---------------------------------------------------------------- variations */

    public function test_preorder_applies_only_to_the_variations_where_it_is_enabled(): void
    {
        $product = $this->product(
            $this->preorderOn(['preorder_delivery_text' => 'Estimated Delivery: November']),
            0,
            ['type' => 'variable']
        );
        $open   = $this->variation($product, ['is_preorder' => 1, 'preorder_limit' => 3, 'preorder_delivery_text' => 'Estimated Delivery: 1–5 November']);
        $plain  = $this->variation($product, ['is_preorder' => 1]);
        $closed = $this->variation($product, ['is_preorder' => 0]);
        $product->refresh();

        $this->assertTrue($this->preorders->appliesTo($product, $open));
        $this->assertFalse($this->preorders->appliesTo($product, $closed), 'the product toggle alone does not open a variation');
        $this->assertSame('preorder', $this->preorders->cardState($product));

        // variation settings override, blanks fall back to the product
        $this->assertSame('Estimated Delivery: 1–5 November', $open->deliveryLabel());
        $this->assertSame('Estimated Delivery: November', $plain->deliveryLabel());
        $this->assertSame(3, $open->preorderRemaining());
        $this->assertNull($plain->preorderRemaining());

        // storefront: the closed variation still cannot be bought
        $this->addToCart($product, ['variation_id' => $closed->id])
            ->assertStatus(422)->assertJson(['success' => false, 'msg' => 'Stock Not Available!']);

        $this->addToCart($product, ['variation_id' => $open->id, 'quantity' => 4])
            ->assertStatus(422)->assertJsonFragment(['msg' => 'Only 3 pre-order slot(s) left for this product.']);

        $this->addToCart($product, ['variation_id' => $open->id, 'quantity' => 2])
            ->assertOk()->assertJson(['success' => true, 'is_preorder' => true]);

        $response = $this->checkout()->assertOk();
        $this->assertTrue($response->json('success'), (string) $response->json('msg'));

        // both counters move: the variation's own and the product total
        $this->assertSame(2, (int) $open->fresh()->preorder_sold_qty);
        $this->assertSame(2, $this->sold($product));
        $this->assertSame(0, (int) $plain->fresh()->preorder_sold_qty);

        $line = OrderDetails::where('order_id', $response->json('order_id'))->firstOrFail();
        $this->assertSame((int) $open->id, (int) $line->variation_id);
        $this->assertSame('Estimated Delivery: 1–5 November', $line->estimated_delivery_text);
    }

    public function test_a_product_level_limit_caps_all_variations_together(): void
    {
        $product = $this->product($this->preorderOn(['preorder_limit' => 3]), 0, ['type' => 'variable']);
        $red  = $this->variation($product, ['is_preorder' => 1]);
        $blue = $this->variation($product, ['is_preorder' => 1]);

        $this->preorders->reserve($product->id, $red->id, 2);

        $this->assertSame(1, $blue->fresh()->preorderRemaining());

        $this->expectException(PreOrderException::class);
        $this->preorders->reserve($product->id, $blue->id, 2);
    }

    /* ---------------------------------------------------------------- stock-out without pre-order */

    public function test_stock_out_without_preorder_still_blocks_the_purchase(): void
    {
        $product = $this->product();

        $this->addToCart($product)->assertStatus(422)->assertJson(['success' => false, 'msg' => 'Stock Not Available!']);
        $this->assertEmpty(session('cart', []));

        // even a cart forged past the "add to cart" check is refused at checkout
        $ordersBefore = Order::count();
        $response = $this->withSession(['cart' => [$product->id . '_0' => [
            'name' => $product->name, 'product_id' => $product->id, 'variation_id' => $product->variations()->value('id'),
            'quantity' => 1, 'price' => 2000, 'discount' => 0, 'is_stock' => 1,
        ]]])->checkout()->assertOk();

        $this->assertFalse($response->json('success'));
        $this->assertSame('Stock Not Available!', $response->json('msg'));
        $this->assertSame($ordersBefore, Order::count());
    }

    public function test_a_product_with_stock_management_off_stays_closed_unless_preorder_is_on(): void
    {
        $closed = $this->product([], 0, ['is_stock' => 0]);
        $this->addToCart($closed)->assertStatus(422)->assertJson(['msg' => 'This product is currently unavailable.']);

        $open = $this->product($this->preorderOn(), 0, ['is_stock' => 0]);
        $this->addToCart($open)->assertOk()->assertJson(['success' => true, 'is_preorder' => true]);
    }

    /* ---------------------------------------------------------------- mixed cart */

    public function test_preorder_and_in_stock_items_cannot_share_a_cart(): void
    {
        $inStock  = $this->product([], 5);
        $preorder = $this->product($this->preorderOn());

        $this->addToCart($inStock)->assertOk();
        $this->addToCart($preorder)->assertStatus(422)->assertJson(['success' => false, 'mixed_cart' => true]);
        $this->assertCount(1, session('cart'));

        // the customer may swap the cart for the pre-order instead
        $this->addToCart($preorder, ['replace_cart' => 1])->assertOk()->assertJson(['is_preorder' => true]);
        $this->assertCount(1, session('cart'));
        $this->assertTrue($this->preorders->cartIsPreorder(session('cart')));

        // and the other way round
        $this->addToCart($inStock)->assertStatus(422)->assertJson(['mixed_cart' => true]);
    }

    public function test_checkout_refuses_a_mixed_cart(): void
    {
        $inStock  = $this->product([], 5);
        $preorder = $this->product($this->preorderOn());
        $line = fn (Product $p, bool $flag) => [
            'name' => $p->name, 'product_id' => $p->id, 'variation_id' => $p->variations()->value('id'),
            'quantity' => 1, 'price' => 2000, 'discount' => 0, 'is_stock' => 1, 'is_preorder' => $flag,
        ];

        $ordersBefore = Order::count();
        $response = $this->withSession(['cart' => [
            $inStock->id . '_a'  => $line($inStock, false),
            $preorder->id . '_b' => $line($preorder, true),
        ]])->checkout()->assertOk();

        $this->assertFalse($response->json('success'));
        $this->assertStringContainsString('cannot be ordered together', $response->json('msg'));
        $this->assertSame($ordersBefore, Order::count());
        $this->assertSame(0, $this->sold($preorder));
        $this->assertSame(5, $this->stock($inStock));
    }

    /* ---------------------------------------------------------------- placing a pre-order */

    public function test_placing_a_preorder_creates_a_preorder_order_and_reserves_slots(): void
    {
        $product = $this->product($this->preorderOn(['preorder_limit' => 10]));

        $order = $this->placePreorder($product, 3);
        $shipping = (float) $order->shipping_charge;

        $this->assertTrue((bool) $order->is_preorder);
        $this->assertSame(PreOrderService::STATUS_PREORDER, $order->status);
        $this->assertSame('full', $order->payment_mode);
        $this->assertSame('customer@example.com', $order->email);
        $this->assertEquals(6000 + $shipping, $order->final_amount);
        $this->assertEquals(6000 + $shipping, $order->due_amount, 'cash on delivery: everything is still due');
        $this->assertEquals(0, $order->advance_paid);

        $line = $order->details()->firstOrFail();
        $this->assertTrue((bool) $line->is_preorder);
        $this->assertFalse((bool) $line->preorder_stock_allocated);
        $this->assertSame('Estimated Delivery: 15–20 October', $line->deliveryLabel());
        $this->assertSame('Estimated Delivery: 15–20 October', $order->preorderDeliveryLabel());

        $this->assertSame(3, $this->sold($product), 'slots are taken');
        $this->assertSame(0, $this->stock($product), 'stock is not touched');
        $this->assertSame(7, $product->fresh()->preorderRemaining());
        $this->assertEmpty(session('cart', []));

        Notification::assertSentTo($order, PreOrderPlaced::class, fn ($notification, $channels) => $channels === ['mail']);
    }

    public function test_a_preorder_needs_an_email_address(): void
    {
        $product = $this->product($this->preorderOn());
        $this->addToCart($product)->assertOk();

        $response = $this->checkout(['email' => ''])->assertOk();

        $this->assertFalse($response->json('success'));
        $this->assertStringContainsString('email', $response->json('msg'));
        $this->assertSame(0, $this->sold($product));
        Notification::assertNothingSent();
    }

    public function test_the_preorder_mails_render(): void
    {
        $product = $this->product($this->preorderOn(['preorder_payment_type' => 'advance', 'preorder_advance_type' => 'fixed', 'preorder_advance_amount' => 500]));
        $order = $this->placePreorder($product, 1, [], ['payment_method' => 'bKash', 'sender_number' => '01700000000', 'transaction_id' => 'TRX9']);

        $placed = (new PreOrderPlaced($order))->toMail($order);
        $html = (string) $placed->render();
        $this->assertStringContainsString('Pre-order confirmed', $placed->subject);
        $this->assertStringContainsString('PRE-ORDER', $html);
        $this->assertStringContainsString('Estimated Delivery: 15–20 October', $html);
        $this->assertStringContainsString('Due amount', $html);
        $this->assertStringContainsString('Advance payment', $html);

        foreach ([
            new PreOrderDeliveryDateChanged($order, 'Estimated Delivery: 15–20 October', 'Estimated Delivery: 1–5 November'),
            new PreOrderStatusChanged($order, PreOrderService::STATUS_READY),
            new PreOrderDuePaymentReminder($order),
        ] as $notification) {
            $this->assertSame(['mail'], $notification->via($order));
            $this->assertNotEmpty((string) $notification->toMail($order)->render());
        }
    }

    /* ---------------------------------------------------------------- limit */

    public function test_the_limit_is_enforced_in_the_cart_and_at_checkout(): void
    {
        $product = $this->product($this->preorderOn(['preorder_limit' => 2]));

        $this->addToCart($product, ['quantity' => 3])
            ->assertStatus(422)->assertJsonFragment(['msg' => 'Only 2 pre-order slot(s) left for this product.']);

        $this->placePreorder($product, 2);
        $this->assertSame(2, $this->sold($product));
        $this->assertSame('closed', $this->preorders->state($product->fresh()));

        // the next customer finds it closed...
        $this->addToCart($product)->assertStatus(422)->assertJson(['preorder_closed' => true]);

        // ...and a cart filled before it closed cannot slip through checkout
        $ordersBefore = Order::count();
        $response = $this->withSession(['cart' => [$product->id . '_0' => [
            'name' => $product->name, 'product_id' => $product->id, 'variation_id' => $product->variations()->value('id'),
            'quantity' => 1, 'price' => 2000, 'discount' => 0, 'is_stock' => 1, 'is_preorder' => true, 'payment_mode' => 'full',
        ]]])->checkout()->assertOk();

        $this->assertFalse($response->json('success'));
        $this->assertSame($ordersBefore, Order::count());
        $this->assertSame(2, $this->sold($product), 'never oversold');
    }

    public function test_reserve_refuses_to_go_past_the_limit_and_release_never_goes_below_zero(): void
    {
        $product = $this->product($this->preorderOn(['preorder_limit' => 5]));

        $this->preorders->reserve($product->id, null, 4);
        $this->assertSame(4, $this->sold($product));

        try {
            $this->preorders->reserve($product->id, null, 2);
            $this->fail('The limit was exceeded.');
        } catch (PreOrderException $e) {
            $this->assertStringContainsString('Only 1 pre-order slot(s) left', $e->getMessage());
        }
        $this->assertSame(4, $this->sold($product));

        $this->preorders->release($product->id, null, 10);
        $this->assertSame(0, $this->sold($product));
    }

    /* ---------------------------------------------------------------- admin flow */

    public function test_cancelling_releases_the_slots_and_arrival_allocates_the_stock(): void
    {
        $product = $this->product($this->preorderOn(['preorder_limit' => 10]));
        $order = $this->placePreorder($product, 2);
        $this->actingAs($this->admin());

        // cancel -> slots come back, and no phantom stock appears
        $this->post(route('admin.orderStatusUPdate', $order->id), ['status' => 'Cancelled'])->assertOk()->assertJson(['status' => true]);
        $this->assertSame(0, $this->sold($product));
        $this->assertSame(0, $this->stock($product));

        // un-cancel -> slots are taken again
        $this->post(route('admin.orderStatusUPdate', $order->id), ['status' => PreOrderService::STATUS_PREORDER])->assertJson(['status' => true]);
        $this->assertSame(2, $this->sold($product));

        // the product arrives: 5 pieces go into stock, the pre-order takes its 2
        app(\App\Utils\Util::class)->increaseProductStock($product->id, $product->variations()->value('id'), 5);
        $this->assertSame(5, $this->stock($product));

        $this->postJson(route('admin.pre_orders.status'), ['order_ids' => [$order->id], 'status' => PreOrderService::STATUS_PROCESSING])
            ->assertOk()->assertJson(['status' => true]);

        $order->refresh();
        $this->assertSame(PreOrderService::STATUS_PROCESSING, $order->status);
        $this->assertSame(3, $this->stock($product));
        $this->assertTrue((bool) $order->details()->first()->preorder_stock_allocated);
        Notification::assertSentTo($order, PreOrderStatusChanged::class, fn ($n) => $n->status === PreOrderService::STATUS_PROCESSING);

        // steps cannot be skipped backwards or repeated
        $this->postJson(route('admin.pre_orders.status'), ['order_ids' => [$order->id], 'status' => PreOrderService::STATUS_PROCESSING])
            ->assertJson(['status' => false]);
        $this->assertSame(3, $this->stock($product));

        $this->postJson(route('admin.pre_orders.status'), ['order_ids' => [$order->id], 'status' => PreOrderService::STATUS_READY])
            ->assertJson(['status' => true]);
        $this->assertSame(PreOrderService::STATUS_READY, $order->fresh()->status);
        $this->assertSame(3, $this->stock($product), 'stock is allocated only once');
        Notification::assertSentTo($order, PreOrderStatusChanged::class, fn ($n) => $n->status === PreOrderService::STATUS_READY);

        // cancelled after arrival: the pieces go back on the shelf and the slots are freed
        $this->post(route('admin.orderStatusUPdate', $order->id), ['status' => 'Cancelled'])->assertJson(['status' => true]);
        $this->assertSame(5, $this->stock($product));
        $this->assertSame(0, $this->sold($product));
    }

    public function test_changing_the_delivery_date_logs_the_change_and_notifies_the_customer(): void
    {
        $year = now()->year;
        $product = $this->product($this->preorderOn());
        $order = $this->placePreorder($product);
        $silent = $this->placePreorder($product, 1, [], ['email' => 'second@example.com']);
        $this->actingAs($this->admin());

        // end date before the start date is rejected
        $this->postJson(route('admin.pre_orders.delivery_date'), [
            'scope' => 'orders', 'order_ids' => [$order->id], 'delivery_start' => "$year-11-10", 'delivery_end' => "$year-11-05",
        ])->assertStatus(422)->assertJsonValidationErrors('delivery_end');

        // for the whole product, and remembered for future pre-orders
        $this->postJson(route('admin.pre_orders.delivery_date'), [
            'scope' => 'product', 'product_id' => $product->id, 'update_product' => 1,
            'delivery_start' => "$year-11-01", 'delivery_end' => "$year-11-05",
        ])->assertOk()->assertJson(['status' => true]);

        $line = $order->details()->firstOrFail();
        $this->assertSame('Estimated Delivery: 1–5 November', $line->deliveryLabel());
        $this->assertSame('Estimated Delivery: 1–5 November', $product->fresh()->deliveryLabel());

        $log = PreorderDeliveryLog::where('order_id', $order->id)->firstOrFail();
        $this->assertSame('Estimated Delivery: 15–20 October', $log->old_text);
        $this->assertNull($log->old_date);
        $this->assertSame("$year-11-01", $log->new_date->toDateString());
        $this->assertSame("$year-11-05", $log->new_end_date->toDateString());
        $this->assertSame('mail', $log->notified_via);
        $this->assertNotNull($log->notified_at);
        $this->assertSame((int) $this->admin()->id, (int) $log->changed_by);
        $this->assertSame(1, PreorderDeliveryLog::where('order_id', $silent->id)->count());

        Notification::assertSentTo($order, PreOrderDeliveryDateChanged::class, fn ($n) => $n->newLabel === 'Estimated Delivery: 1–5 November'
            && $n->oldLabel === 'Estimated Delivery: 15–20 October');
        Notification::assertSentTo($silent, PreOrderDeliveryDateChanged::class);

        // saving the same date again changes (and emails) nothing
        $this->postJson(route('admin.pre_orders.delivery_date'), [
            'scope' => 'orders', 'order_ids' => [$order->id], 'delivery_start' => "$year-11-01", 'delivery_end' => "$year-11-05",
        ])->assertOk();
        $this->assertSame(1, PreorderDeliveryLog::where('order_id', $order->id)->count());
    }

    public function test_the_admin_preorder_list_filters_and_searches(): void
    {
        $product = $this->product($this->preorderOn(['preorder_payment_type' => 'both', 'preorder_advance_type' => 'fixed', 'preorder_advance_amount' => 500]));
        $full = $this->placePreorder($product, 1, [], ['first_name' => 'Fullpay Customer']);
        $advance = $this->placePreorder($product, 1, ['payment_mode' => 'advance'], [
            'first_name' => 'Advance Customer', 'payment_method' => 'bKash', 'sender_number' => '01700000000', 'transaction_id' => 'TRX77',
        ]);
        $this->actingAs($this->admin());

        $this->get(route('admin.pre_orders.index'))->assertOk()
            ->assertSee('Fullpay Customer')->assertSee('Advance Customer')->assertSee($product->name);

        $this->get(route('admin.pre_orders.index', ['payment_mode' => 'advance']))->assertOk()
            ->assertSee('Advance Customer')->assertDontSee('Fullpay Customer');

        $this->get(route('admin.pre_orders.index', ['q' => $full->mobile]))->assertOk()
            ->assertSee('Fullpay Customer')->assertDontSee('Advance Customer');

        $this->get(route('admin.pre_orders.index', ['q' => (string) $advance->id, 'product_id' => $product->id]))->assertOk()
            ->assertSee('Advance Customer');

        $this->get(route('admin.pre_orders.index', ['status' => PreOrderService::STATUS_READY, 'product_id' => $product->id]))->assertOk()
            ->assertDontSee('Fullpay Customer');

        // the pre-order also shows up, badged, in the normal order list and its invoice
        $this->get(route('admin.orders.index', ['status' => PreOrderService::STATUS_PREORDER]))->assertOk()->assertSee('PRE-ORDER');
        $this->get(route('admin.orders.show', $advance->id))->assertOk()->assertSee('Due Amount');
    }

    public function test_customers_cannot_open_the_admin_preorder_screens(): void
    {
        $customer = User::create(['first_name' => 'Shopper', 'username' => 'po' . uniqid(), 'mobile' => '01800000000', 'status' => 1]);

        $this->actingAs($customer)->get(route('admin.pre_orders.index'))->assertForbidden();
        $this->actingAs($customer)->postJson(route('admin.pre_orders.status'), ['order_ids' => [1], 'status' => PreOrderService::STATUS_PROCESSING])->assertForbidden();
    }

    public function test_the_admin_can_send_a_due_payment_reminder(): void
    {
        $product = $this->product($this->preorderOn(['preorder_payment_type' => 'both', 'preorder_advance_type' => 'fixed', 'preorder_advance_amount' => 500]));
        $owing = $this->placePreorder($product, 1, ['payment_mode' => 'advance'], ['payment_method' => 'bKash', 'sender_number' => '01700000000', 'transaction_id' => 'TRX5']);
        $this->preorders->markAdvanceReceived($owing);

        $settled = $this->placePreorder($product, 1, [], ['email' => 'paid@example.com']);
        $settled->update(['payment_status' => 'Paid']);
        $this->assertEquals(0, $settled->fresh()->due_amount);

        $this->actingAs($this->admin())
            ->postJson(route('admin.pre_orders.due_reminder'), ['order_ids' => [$owing->id, $settled->id]])
            ->assertOk()->assertJson(['status' => true]);

        Notification::assertSentToTimes($owing, PreOrderDuePaymentReminder::class, 1);
        Notification::assertNotSentTo($settled, PreOrderDuePaymentReminder::class);
        $this->assertNotNull($owing->fresh()->due_reminder_sent_at);
    }

    public function test_emails_are_sent_immediately_and_only_after_the_transaction_commits(): void
    {
        $product = $this->product($this->preorderOn());
        $order = $this->placePreorder($product);

        // no queue involved: nothing needs a worker or a cron job
        $this->assertNotInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, new PreOrderPlaced($order));

        // an action that is rolled back emails nobody
        try {
            DB::transaction(function () use ($order) {
                $order->update(['status' => PreOrderService::STATUS_PROCESSING]);
                throw new \RuntimeException('something failed after the status change');
            });
        } catch (\RuntimeException $e) {
        }
        Notification::assertNotSentTo($order, PreOrderStatusChanged::class);
        $this->assertSame(PreOrderService::STATUS_PREORDER, $order->fresh()->status);

        // the same action committed does
        DB::transaction(fn () => $order->fresh()->update(['status' => PreOrderService::STATUS_PROCESSING]));
        Notification::assertSentToTimes($order, PreOrderStatusChanged::class, 1);
    }

    /* ---------------------------------------------------------------- admin product form */

    public function test_product_form_validates_the_preorder_settings(): void
    {
        $product = $this->product();
        $this->actingAs($this->admin());

        $form = fn (array $preorder) => array_merge([
            'name' => $product->name, 'type' => 'single', 'category_id' => $product->category_id,
            'sell_price' => 2000, 'is_stock' => 1, 'pro_quantity' => 0, 'is_preorder' => 1,
        ], $preorder);

        // advance must be less than the price
        $this->patchJson(route('admin.products.update', $product->id), $form([
            'preorder_payment_type' => 'advance', 'preorder_advance_type' => 'fixed', 'preorder_advance_amount' => 2000,
        ]))->assertStatus(422)->assertJsonValidationErrors('preorder_advance_amount');

        $this->patchJson(route('admin.products.update', $product->id), $form([
            'preorder_payment_type' => 'both', 'preorder_advance_type' => 'percentage', 'preorder_advance_amount' => 100,
        ]))->assertStatus(422)->assertJsonValidationErrors('preorder_advance_amount');

        // delivery end date must not be before the start date
        $this->patchJson(route('admin.products.update', $product->id), $form([
            'preorder_delivery_start' => '2026-10-20', 'preorder_delivery_end' => '2026-10-15',
        ]))->assertStatus(422)->assertJsonValidationErrors('preorder_delivery_end');

        $this->assertFalse((bool) $product->fresh()->is_preorder, 'nothing is saved while the form is invalid');

        // a valid form is saved
        $this->patchJson(route('admin.products.update', $product->id), $form([
            'preorder_limit' => 50, 'preorder_delivery_text' => 'Estimated Delivery: 15–20 October',
            'preorder_delivery_start' => '2026-10-15', 'preorder_delivery_end' => '2026-10-20',
            'preorder_payment_type' => 'both', 'preorder_advance_type' => 'fixed', 'preorder_advance_amount' => 500,
        ]))->assertOk()->assertJson(['status' => true]);

        $product->refresh();
        $this->assertTrue($product->is_preorder);
        $this->assertSame(50, $product->preorder_limit);
        $this->assertSame('both', $product->preorder_payment_type);
        $this->assertSame(500.0, $product->advanceAmount());
        $this->assertSame('2026-10-20', $product->preorder_delivery_end->toDateString());

        // switching it off clears the settings but never the sold counter
        DB::table('products')->where('id', $product->id)->update(['preorder_sold_qty' => 7]);
        $this->patchJson(route('admin.products.update', $product->id), $form(['is_preorder' => 0]))->assertOk();
        $product->refresh();
        $this->assertFalse($product->is_preorder);
        $this->assertNull($product->preorder_limit);
        $this->assertSame(7, $product->preorder_sold_qty);
    }

    public function test_product_form_saves_and_validates_variation_preorder_settings(): void
    {
        $product = $this->product([], 0, ['type' => 'variable']);
        $first  = $this->variation($product);
        $second = $this->variation($product, ['price' => 1500]);
        $this->actingAs($this->admin());

        $form = fn (array $rows) => array_merge([
            'name' => $product->name, 'type' => 'variable', 'category_id' => $product->category_id,
            'sell_price' => 2000, 'is_stock' => 1, 'pro_quantity' => 0, 'is_preorder' => 0,
            'variation_id' => [$first->id, $second->id], 'size_id' => ['', ''], 'color_id' => ['', ''],
            'price' => [2000, 1500], 'quantity' => [0, 0],
            'var_is_preorder' => [0, 1], 'var_preorder_limit' => ['', 20],
            'var_preorder_delivery_text' => ['', 'Estimated Delivery: December'],
            'var_preorder_delivery_start' => ['', ''], 'var_preorder_delivery_end' => ['', ''],
            'var_preorder_payment_type' => ['', 'advance'], 'var_preorder_advance_type' => ['fixed', 'fixed'],
            'var_preorder_advance_amount' => ['', 300],
        ], $rows);

        // 1500 advance on a 1500 variation is not an advance
        $this->patchJson(route('admin.products.update', $product->id), $form(['var_preorder_advance_amount' => ['', 1500]]))
            ->assertStatus(422)->assertJsonValidationErrors('preorder_variations');

        $this->patchJson(route('admin.products.update', $product->id), $form([]))->assertOk()->assertJson(['status' => true]);

        $this->assertFalse((bool) $first->fresh()->is_preorder);
        $second->refresh();
        $this->assertTrue($second->is_preorder);
        $this->assertSame(20, $second->preorder_limit);
        $this->assertSame('advance', $second->preorder_payment_type);
        $this->assertSame(300.0, $second->advanceAmount());
        $this->assertSame('Estimated Delivery: December', $second->deliveryLabel());
    }

    /* ---------------------------------------------------------------- storefront */

    public function test_storefront_shows_the_preorder_badge_and_button(): void
    {
        $open = $this->product($this->preorderOn(['preorder_limit' => 8, 'preorder_payment_type' => 'both', 'preorder_advance_type' => 'fixed', 'preorder_advance_amount' => 500]));

        // #pdxTop carries the state the page was rendered in (JS keeps it in step afterwards)
        $openState   = '/class="pdx-top\s+is-preorder" id="pdxTop"/';
        $closedState = '/class="pdx-top is-stock-out\s*" id="pdxTop"/';

        $page = $this->get(route('front.products.show', ['product' => $open->slug]))->assertOk();
        $page->assertSee('Pre-Order Now')->assertSee('preorder-badge', false)
            ->assertSee('Estimated Delivery: 15–20 October')->assertSee('Only 8 pre-order slot(s) left');
        $this->assertMatchesRegularExpression($openState, $page->getContent());
        $this->assertStringContainsString('"modes":["full","advance"]', $page->getContent());
        $this->assertStringContainsString('"advance":500', $page->getContent());

        $closed = $this->product($this->preorderOn(['preorder_limit' => 2, 'preorder_sold_qty' => 2]));
        $page = $this->get(route('front.products.show', ['product' => $closed->slug]))->assertOk()->assertSee('Pre-Order Closed');
        $this->assertMatchesRegularExpression($closedState, $page->getContent());
        $this->assertStringContainsString('"closed":true', $page->getContent());

        $plain = $this->product();
        $page = $this->get(route('front.products.show', ['product' => $plain->slug]))->assertOk()->assertSee('Out of Stock');
        $this->assertMatchesRegularExpression($closedState, $page->getContent());
        $this->assertStringContainsString('"active":false,"closed":false', $page->getContent());

        // product card
        $card = view('frontend.products.partials.product_section', ['product' => $open->load('variations', 'images')])->render();
        $this->assertStringContainsString('preorder-badge', $card);
        $this->assertStringContainsString('data-is-out="0"', $card);
        $this->assertStringNotContainsString('Out of Stock</span>', $card);

        $plainCard = view('frontend.products.partials.product_section', ['product' => $plain->load('variations', 'images')])->render();
        $this->assertStringContainsString('data-is-out="1"', $plainCard);
    }

    public function test_checkout_and_order_pages_show_the_preorder_details(): void
    {
        $product = $this->product($this->preorderOn(['preorder_payment_type' => 'advance', 'preorder_advance_type' => 'fixed', 'preorder_advance_amount' => 500]));
        $this->addToCart($product, ['quantity' => 2])->assertOk();

        $this->get(route('front.checkouts.index'))->assertOk()
            ->assertSee('This is a pre-order.')
            ->assertSee('name="email"', false)
            ->assertSee('Pay now (Advance):')
            ->assertSee('Due on delivery:')
            ->assertDontSee('id="payment_cod"', false);

        $response = $this->checkout(['payment_method' => 'bKash', 'sender_number' => '01700000000', 'transaction_id' => 'TRX2'])->assertOk();
        $order = Order::findOrFail($response->json('order_id'));

        // confirmation page, invoice and "my orders"
        $this->get(confirmOrderUrl($order))->assertOk()
            ->assertSee('Estimated Delivery: 15–20 October')->assertSee('Advance payment')->assertSee('Due amount');

        $this->get(route('front.orders.show', ['order' => $order->id, 't' => $order->order_token]))->assertOk()
            ->assertSee('Due Amount')->assertSee('Estimated Delivery: 15–20 October');

        $this->actingAs(User::findOrFail($order->user_id))->get(route('front.orders.index'))->assertOk()
            ->assertSee('preorder-badge', false)->assertSee('Advance payment');
    }
}
