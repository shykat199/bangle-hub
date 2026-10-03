<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('informations')) {
            return;
        }

        Schema::table('informations', function (Blueprint $table) {
            if (!Schema::hasColumn('informations', 'gtm_id')) {
                if (Schema::hasColumn('informations', 'ga4_id')) {
                    $table->string('gtm_id', 50)->nullable()->after('ga4_id');
                } else {
                    $table->string('gtm_id', 50)->nullable();
                }
            }
            if (!Schema::hasColumn('informations', 'whatsapp')) {
                $table->string('whatsapp', 50)->nullable();
            }
        });

        // Backfill whatsapp from existing whats_num if present
        if (Schema::hasColumn('informations', 'whatsapp')
            && Schema::hasColumn('informations', 'whats_num')) {
            DB::table('informations')
                ->whereNull('whatsapp')
                ->orWhere('whatsapp', '')
                ->update([
                    'whatsapp' => DB::raw('`whats_num`'),
                ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('informations')) {
            return;
        }

        Schema::table('informations', function (Blueprint $table) {
            $columns = array_filter(
                ['gtm_id', 'whatsapp'],
                fn($c) => Schema::hasColumn('informations', $c)
            );
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
