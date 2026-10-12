<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('admin_texts')) {
            return;
        }

        // Homepage bottom info strip (shop block, feature items, contact button), stored as JSON.
        Schema::table('admin_texts', function (Blueprint $table) {
            if (!Schema::hasColumn('admin_texts', 'home_strip')) {
                $table->text('home_strip')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('admin_texts')) {
            return;
        }

        Schema::table('admin_texts', function (Blueprint $table) {
            if (Schema::hasColumn('admin_texts', 'home_strip')) {
                $table->dropColumn('home_strip');
            }
        });
    }
};
