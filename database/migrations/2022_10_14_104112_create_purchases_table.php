<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('supplier_id')->nullable();
            $table->smallInteger('user_id')->nullable();
            $table->text('note')->nullable();
            $table->string('ref',50)->nullable();
            $table->date('date')->nullable();
            $table->string('status',30)->nullable();
            $table->string('discount_type',30)->nullable();
            $table->decimal('discount_amount',10,2)->nullable();
            $table->decimal('shipping_cost',10,2)->nullable();
            $table->decimal('amount',10,2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchases');
    }
};
