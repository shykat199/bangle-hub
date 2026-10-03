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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->BigInteger('user_id');
            $table->string('invoice_no',100)->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('city',100)->nullable();
            $table->string('state',100)->nullable();
            $table->string('zip_code',100)->nullable();
            $table->string('first_name',200)->nullable();
            $table->string('last_name',200)->nullable();
            $table->string('mobile',50)->nullable();
            $table->date('date')->nullable();
            $table->string('payment_status',50)->nullable()->default('due');
            $table->string('status',50)->nullable()->default('pending');
            $table->decimal('amount',10,2)->nullable()->default(0);
            $table->decimal('tax',10,2)->nullable()->default(0);
            $table->decimal('discount',10,2)->nullable()->default(0);
            $table->decimal('final_amount',10,2)->nullable()->default(0);
            $table->decimal('shipping_charge',10,2)->nullable()->default(0);
            $table->tinyInteger('delivery_type')->nullable();
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
        Schema::dropIfExists('orders');
    }
};
