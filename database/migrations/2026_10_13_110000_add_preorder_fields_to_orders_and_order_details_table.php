<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ORDER_COLUMNS = [
        'is_preorder', 'payment_mode', 'advance_amount', 'advance_paid', 'due_amount', 'due_reminder_sent_at',
    ];

    private const DETAIL_COLUMNS = [
        'is_preorder', 'preorder_stock_allocated', 'estimated_delivery_text', 'estimated_delivery_start', 'estimated_delivery_end',
    ];

    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'is_preorder')) {
                    $table->boolean('is_preorder')->default(false)->index();
                }
                if (!Schema::hasColumn('orders', 'payment_mode')) {
                    $table->string('payment_mode', 20)->nullable(); // full | advance
                }
                // advance_amount is what the customer has to pay up front;
                // advance_paid is how much of it has actually arrived.
                if (!Schema::hasColumn('orders', 'advance_amount')) {
                    $table->decimal('advance_amount', 10, 2)->default(0);
                }
                if (!Schema::hasColumn('orders', 'advance_paid')) {
                    $table->decimal('advance_paid', 10, 2)->default(0);
                }
                if (!Schema::hasColumn('orders', 'due_amount')) {
                    $table->decimal('due_amount', 10, 2)->default(0);
                }
                if (!Schema::hasColumn('orders', 'due_reminder_sent_at')) {
                    $table->timestamp('due_reminder_sent_at')->nullable();
                }
            });
        }

        if (Schema::hasTable('order_details')) {
            Schema::table('order_details', function (Blueprint $table) {
                if (!Schema::hasColumn('order_details', 'is_preorder')) {
                    $table->boolean('is_preorder')->default(false);
                }
                // false = the line holds a pre-order slot, true = the arrived
                // stock has been deducted for it (order left "Pre-Order").
                if (!Schema::hasColumn('order_details', 'preorder_stock_allocated')) {
                    $table->boolean('preorder_stock_allocated')->default(false);
                }
                if (!Schema::hasColumn('order_details', 'estimated_delivery_text')) {
                    $table->string('estimated_delivery_text')->nullable();
                }
                if (!Schema::hasColumn('order_details', 'estimated_delivery_start')) {
                    $table->date('estimated_delivery_start')->nullable();
                }
                if (!Schema::hasColumn('order_details', 'estimated_delivery_end')) {
                    $table->date('estimated_delivery_end')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                foreach (self::ORDER_COLUMNS as $column) {
                    if (Schema::hasColumn('orders', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('order_details')) {
            Schema::table('order_details', function (Blueprint $table) {
                foreach (self::DETAIL_COLUMNS as $column) {
                    if (Schema::hasColumn('order_details', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
