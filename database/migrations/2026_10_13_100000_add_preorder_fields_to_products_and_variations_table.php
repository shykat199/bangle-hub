<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COLUMNS = [
        'is_preorder',
        'preorder_limit',
        'preorder_sold_qty',
        'preorder_delivery_text',
        'preorder_delivery_start',
        'preorder_delivery_end',
        'preorder_payment_type',
        'preorder_advance_type',
        'preorder_advance_amount',
    ];

    public function up(): void
    {
        foreach (['products', 'variations'] as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'is_preorder')) {
                    $table->boolean('is_preorder')->default(false);
                }
                if (!Schema::hasColumn($tableName, 'preorder_limit')) {
                    $table->unsignedInteger('preorder_limit')->nullable();
                }
                if (!Schema::hasColumn($tableName, 'preorder_sold_qty')) {
                    $table->unsignedInteger('preorder_sold_qty')->default(0);
                }
                if (!Schema::hasColumn($tableName, 'preorder_delivery_text')) {
                    $table->string('preorder_delivery_text')->nullable();
                }
                if (!Schema::hasColumn($tableName, 'preorder_delivery_start')) {
                    $table->date('preorder_delivery_start')->nullable();
                }
                if (!Schema::hasColumn($tableName, 'preorder_delivery_end')) {
                    $table->date('preorder_delivery_end')->nullable();
                }
                if (!Schema::hasColumn($tableName, 'preorder_payment_type')) {
                    // A variation left at NULL inherits the product's payment type.
                    $column = $table->enum('preorder_payment_type', ['full', 'advance', 'both']);
                    $tableName === 'products' ? $column->default('full') : $column->nullable();
                }
                if (!Schema::hasColumn($tableName, 'preorder_advance_type')) {
                    $table->enum('preorder_advance_type', ['fixed', 'percentage'])->nullable();
                }
                if (!Schema::hasColumn($tableName, 'preorder_advance_amount')) {
                    $table->decimal('preorder_advance_amount', 10, 2)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['products', 'variations'] as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                foreach (self::COLUMNS as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
