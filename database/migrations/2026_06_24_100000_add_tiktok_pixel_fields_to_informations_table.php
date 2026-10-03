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
            if (!Schema::hasColumn('informations', 'tt_pixel_id')) {
                if (Schema::hasColumn('informations', 'fb_access_token')) {
                    $table->string('tt_pixel_id', 100)->nullable()->after('fb_access_token');
                } else {
                    $table->string('tt_pixel_id', 100)->nullable();
                }
            }
            if (!Schema::hasColumn('informations', 'tt_access_token')) {
                $table->text('tt_access_token')->nullable()->after('tt_pixel_id');
            }
            if (!Schema::hasColumn('informations', 'tt_test_event_code')) {
                $table->string('tt_test_event_code', 100)->nullable()->after('tt_access_token');
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
                ['tt_pixel_id', 'tt_access_token', 'tt_test_event_code'],
                fn($c) => Schema::hasColumn('informations', $c)
            );
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
