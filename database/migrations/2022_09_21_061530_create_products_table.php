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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->smallInteger('category_id');
            $table->decimal('purchase_price',8,2)->nullable()->default(0);
            $table->decimal('sell_price',8,2)->nullable()->default(0);
            $table->string('image')->nullable();
            $table->string('optional_image')->nullable();
            $table->text('description')->nullable();
            $table->longText('body')->nullable();
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
        Schema::dropIfExists('products');
    }
};
