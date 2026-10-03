<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('informations')) {
            return;
        }

        Schema::table('informations', function (Blueprint $table) {
            if (!Schema::hasColumn('informations', 'topbar_bg_color')) {
                $table->string('topbar_bg_color', 20)->nullable();
            }
            if (!Schema::hasColumn('informations', 'topbar_text_color')) {
                $table->string('topbar_text_color', 20)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('informations')) {
            return;
        }

        Schema::table('informations', function (Blueprint $table) {
            $columns = array_filter(
                ['topbar_bg_color', 'topbar_text_color'],
                fn($c) => Schema::hasColumn('informations', $c)
            );
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
