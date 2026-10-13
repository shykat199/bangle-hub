<?php

namespace Tests\Feature;

use App\Exceptions\PreOrderException;
use App\Models\Product;
use App\Services\PreOrderService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Two customers go for the last pre-order slot at the same moment.
 *
 * Simulated with two real database connections: the first checkout holds its
 * transaction open (as a checkout does until the order is committed) while
 * the second one tries to reserve. A rolled-back test transaction cannot show
 * this, so the product row is really committed and removed again in tearDown.
 */
class PreOrderConcurrencyTest extends TestCase
{
    private ?int $productId = null;
    private string $defaultConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->defaultConnection = DB::getDefaultConnection();

        // A second, independent session to the same database.
        config(['database.connections.preorder_second' => config('database.connections.' . $this->defaultConnection)]);

        $this->productId = Product::create([
            'name'              => 'PO Concurrency ' . uniqid(),
            'type'              => 'single',
            'category_id'       => DB::table('categories')->value('id') ?? 1,
            'sell_price'        => 2000,
            'is_stock'          => 1,
            'status'            => 0, // never visible on the storefront
            'stock_quantity'    => 0,
            'is_preorder'       => 1,
            'preorder_limit'    => 1,
            'preorder_sold_qty' => 0,
        ])->id;
    }

    protected function tearDown(): void
    {
        DB::setDefaultConnection($this->defaultConnection);

        while (DB::connection($this->defaultConnection)->transactionLevel() > 0) {
            DB::connection($this->defaultConnection)->rollBack();
        }

        if ($this->productId) {
            DB::table('products')->where('id', $this->productId)->delete();
        }

        DB::purge('preorder_second');

        parent::tearDown();
    }

    private function sold(): int
    {
        return (int) DB::connection('preorder_second')->table('products')->where('id', $this->productId)->value('preorder_sold_qty');
    }

    public function test_two_concurrent_orders_cannot_both_take_the_last_slot(): void
    {
        $preorders = app(PreOrderService::class);

        // Customer A: checkout transaction open, last slot reserved, not committed yet.
        DB::connection($this->defaultConnection)->beginTransaction();
        $preorders->reserve($this->productId, null, 1);

        // Customer B, on another connection, at the same moment.
        DB::setDefaultConnection('preorder_second');
        DB::statement('SET SESSION innodb_lock_wait_timeout = 1');

        $this->assertSame(0, $this->sold(), "A's reservation is not visible before it commits");

        try {
            $preorders->reserve($this->productId, null, 1);
            $this->fail('B reserved the slot while A still held it: the row lock is missing.');
        } catch (QueryException $e) {
            // B is made to wait for A's row lock instead of reading the stale count.
            $this->assertStringContainsString('Lock wait timeout', $e->getMessage());
        }

        // A commits its order.
        DB::setDefaultConnection($this->defaultConnection);
        DB::connection($this->defaultConnection)->commit();

        // B's turn: it now sees the slot is gone and is refused.
        DB::setDefaultConnection('preorder_second');
        try {
            $preorders->reserve($this->productId, null, 1);
            $this->fail('The limit was oversold.');
        } catch (PreOrderException $e) {
            $this->assertStringContainsString('Pre-order is closed', $e->getMessage());
        }

        $this->assertSame(1, $this->sold(), 'exactly one of the two orders got the slot');
    }

    public function test_a_rolled_back_order_gives_its_slot_to_the_next_customer(): void
    {
        $preorders = app(PreOrderService::class);

        // A reserves, then its checkout fails and rolls back.
        DB::connection($this->defaultConnection)->beginTransaction();
        $preorders->reserve($this->productId, null, 1);
        DB::connection($this->defaultConnection)->rollBack();

        $this->assertSame(0, $this->sold());

        // B can have the slot.
        DB::setDefaultConnection('preorder_second');
        $preorders->reserve($this->productId, null, 1);

        $this->assertSame(1, $this->sold());
    }
}
