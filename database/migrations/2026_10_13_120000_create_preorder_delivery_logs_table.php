<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('preorder_delivery_logs')) {
            return;
        }

        Schema::create('preorder_delivery_logs', function (Blueprint $table) {
            $table->id();
            // orders.id is a plain bigint on this install (no FK-compatible
            // unsigned type guarantee), so this is an indexed column, not a constraint.
            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedBigInteger('order_detail_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            // old_date / new_date are the start of the estimated delivery
            // window; the *_end_date pair is its end (null for a single day).
            $table->date('old_date')->nullable();
            $table->date('new_date')->nullable();
            $table->date('old_end_date')->nullable();
            $table->date('new_end_date')->nullable();
            $table->string('old_text')->nullable();
            $table->string('new_text')->nullable();
            $table->string('notified_via', 30)->nullable(); // mail
            $table->timestamp('notified_at')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preorder_delivery_logs');
    }
};
