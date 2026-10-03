<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedBigInteger('variation_id')->nullable();
            $table->string('variant_name')->nullable();
            $table->string('phone', 20);
            $table->string('status', 20)->default('pending'); // pending | notified
            $table->timestamp('notified_at')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['product_id', 'variation_id', 'status']);
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_notifications');
    }
};
