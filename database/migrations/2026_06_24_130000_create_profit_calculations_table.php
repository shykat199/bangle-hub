<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('profit_calculations')) {
            return;
        }

        Schema::create('profit_calculations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();

            $table->string('product_name');
            $table->string('product_category')->nullable();
            $table->text('note')->nullable();

            $table->decimal('cost_price', 14, 2)->default(0);
            $table->decimal('selling_price', 14, 2)->default(0);
            $table->decimal('marketing_cost', 14, 2)->default(0);
            $table->decimal('shipping_cost', 14, 2)->default(0);
            $table->decimal('extra_shipping_profit', 14, 2)->default(0);
            $table->decimal('per_return_loss', 14, 2)->default(0);

            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('return_percentage', 6, 2)->default(0);
            $table->decimal('call_cancel_percentage', 6, 2)->default(0);
            $table->unsignedInteger('target_delivered_units')->default(0);

            $table->decimal('net_profit', 14, 2)->default(0);
            $table->decimal('gross_profit', 14, 2)->default(0);
            $table->decimal('total_selling', 14, 2)->default(0);
            $table->decimal('total_cost', 14, 2)->default(0);
            $table->decimal('profit_margin', 8, 2)->default(0);
            $table->decimal('roi', 8, 2)->default(0);
            $table->decimal('sold_units', 12, 2)->default(0);
            $table->decimal('return_units', 12, 2)->default(0);
            $table->string('health_status', 20)->default('loss');

            $table->boolean('is_favorite')->default(false)->index();
            $table->string('formula_version', 10)->default('v1.1');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profit_calculations');
    }
};
