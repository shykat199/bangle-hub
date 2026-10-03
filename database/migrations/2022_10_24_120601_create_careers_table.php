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
        if (!Schema::hasTable('careers')) {
            Schema::create('careers', function (Blueprint $table) {
                $table->id();
                $table->string('cover_image')->nullable();
                $table->string('cover_image_caption');
                $table->string('career_img_one')->nullable();
                $table->string('career_one_title');
                $table->string('career_one_desc');
                $table->string('career_img_two')->nullable();
                $table->string('career_two_title');
                $table->string('career_two_desc');
                $table->string('career_img_three')->nullable();
                $table->string('career_three_title');
                $table->string('career_three_desc');
                $table->string('career_img_four')->nullable();
                $table->string('career_four_title');
                $table->string('career_four_desc');
                $table->string('career_img_five')->nullable();
                $table->string('career_five_title');
                $table->string('career_five_desc');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('careers');
    }
};
