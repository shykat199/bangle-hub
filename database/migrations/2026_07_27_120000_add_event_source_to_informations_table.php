<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('informations', function (Blueprint $table) {
            if (!Schema::hasColumn('informations', 'fb_event_source')) {
                $table->string('fb_event_source', 10)->default('theme')->after('fb_access_token');
            }
            if (!Schema::hasColumn('informations', 'tt_event_source')) {
                $table->string('tt_event_source', 10)->default('theme')->after('tt_test_event_code');
            }
        });

        // Preserve the behaviour existing installs already have: before this setting
        // existed, any GTM container in tracking_code silenced the theme's fbq events.
        DB::table('informations')
            ->where('tracking_code', 'like', '%GTM-%')
            ->update(['fb_event_source' => 'gtm']);
    }

    public function down(): void
    {
        Schema::table('informations', function (Blueprint $table) {
            if (Schema::hasColumn('informations', 'fb_event_source')) {
                $table->dropColumn('fb_event_source');
            }
            if (Schema::hasColumn('informations', 'tt_event_source')) {
                $table->dropColumn('tt_event_source');
            }
        });
    }
};
