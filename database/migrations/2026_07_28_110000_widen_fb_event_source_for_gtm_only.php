<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('informations', 'fb_event_source')) return;

        // The column was varchar(10); the new 'gtm_only' choice needs room.
        DB::statement("ALTER TABLE `informations` MODIFY `fb_event_source` VARCHAR(20) NOT NULL DEFAULT 'theme'");
    }

    public function down(): void
    {
        if (!Schema::hasColumn('informations', 'fb_event_source')) return;

        // Anything that no longer fits goes back to the safe default rather
        // than being silently truncated into a meaningless value.
        DB::table('informations')->where('fb_event_source', 'gtm_only')->update(['fb_event_source' => 'gtm']);
        DB::statement("ALTER TABLE `informations` MODIFY `fb_event_source` VARCHAR(10) NOT NULL DEFAULT 'theme'");
    }
};
