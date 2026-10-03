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
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->BigInteger('order_id');
            $table->string('method',50)->nullable()->default('cash');
            $table->decimal('amount',10,2)->nullable()->default(0);
            $table->date('date')->nullable();
            $table->string('tnx_id',100)->nullable();
            $table->string('email',200)->nullable();
            $table->string('name',200)->nullable();
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
        Schema::dropIfExists('order_payments');
    }
};
