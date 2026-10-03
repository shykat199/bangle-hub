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
        if (!Schema::hasTable('about_us')) {
            Schema::create('about_us', function (Blueprint $table) {
                $table->id();
                $table->string('cover_image')->nullable();
                $table->string('page_title');
                $table->string('sub_title');
                $table->string('speech');
                $table->string('signature')->nullable();
                $table->string('page_desc');
                $table->string('slider_img_one')->nullable();
                $table->string('slider_img_two')->nullable();
                $table->string('slider_img_three')->nullable();
                $table->string('slider_caption_one');
                $table->string('slider_caption_two');
                $table->string('slider_caption_three');
                $table->string('title_one');
                $table->string('title_two');
                $table->string('desc_one');
                $table->string('desc_two');
                $table->string('video')->nullable();
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
        Schema::dropIfExists('about_us');
    }
};
