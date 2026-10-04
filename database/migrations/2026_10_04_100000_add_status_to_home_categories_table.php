<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_categories', function (Blueprint $table) {
            // 1 = shown on the home page, 0 = hidden (row is kept so it can be switched back on).
            $table->boolean('status')->default(1)->after('serial');
        });
    }

    public function down(): void
    {
        Schema::table('home_categories', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
