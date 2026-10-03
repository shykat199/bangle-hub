<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('informations', function (Blueprint $table) {
            if (!Schema::hasColumn('informations', 'manydial_voice_type')) {
                $table->string('manydial_voice_type', 10)->nullable()->after('manydial_retry_delay');
            }
            if (!Schema::hasColumn('informations', 'manydial_voice_id')) {
                $table->string('manydial_voice_id', 30)->nullable()->after('manydial_voice_type');
            }
            if (!Schema::hasColumn('informations', 'manydial_welcome_audio')) {
                $table->string('manydial_welcome_audio', 64)->nullable()->after('manydial_voice_id');
            }
            if (!Schema::hasColumn('informations', 'manydial_sms_status')) {
                $table->boolean('manydial_sms_status')->default(0)->after('manydial_welcome_audio');
            }
        });
    }

    public function down(): void
    {
        Schema::table('informations', function (Blueprint $table) {
            foreach ([
                'manydial_voice_type',
                'manydial_voice_id',
                'manydial_welcome_audio',
                'manydial_sms_status',
            ] as $column) {
                if (Schema::hasColumn('informations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
