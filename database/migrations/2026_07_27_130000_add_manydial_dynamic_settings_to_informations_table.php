<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('informations', function (Blueprint $table) {
            if (!Schema::hasColumn('informations', 'manydial_welcome')) {
                $table->text('manydial_welcome')->nullable()->after('manydial_status');
            }
            if (!Schema::hasColumn('informations', 'manydial_msg_confirm')) {
                $table->text('manydial_msg_confirm')->nullable()->after('manydial_welcome');
            }
            if (!Schema::hasColumn('informations', 'manydial_msg_cancel')) {
                $table->text('manydial_msg_cancel')->nullable()->after('manydial_msg_confirm');
            }
            if (!Schema::hasColumn('informations', 'manydial_key_confirm')) {
                $table->string('manydial_key_confirm', 5)->nullable()->after('manydial_msg_cancel');
            }
            if (!Schema::hasColumn('informations', 'manydial_key_cancel')) {
                $table->string('manydial_key_cancel', 5)->nullable()->after('manydial_key_confirm');
            }
            if (!Schema::hasColumn('informations', 'manydial_per_call_duration')) {
                $table->string('manydial_per_call_duration', 10)->nullable()->after('manydial_key_cancel');
            }
            if (!Schema::hasColumn('informations', 'manydial_repeat')) {
                $table->string('manydial_repeat', 10)->nullable()->after('manydial_per_call_duration');
            }
            if (!Schema::hasColumn('informations', 'manydial_forward')) {
                $table->string('manydial_forward', 30)->nullable()->after('manydial_repeat');
            }
            if (!Schema::hasColumn('informations', 'manydial_max_attempts')) {
                $table->unsignedTinyInteger('manydial_max_attempts')->nullable()->after('manydial_forward');
            }
            if (!Schema::hasColumn('informations', 'manydial_retry_delay')) {
                $table->unsignedSmallInteger('manydial_retry_delay')->nullable()->after('manydial_max_attempts');
            }
        });
    }

    public function down(): void
    {
        Schema::table('informations', function (Blueprint $table) {
            foreach ([
                'manydial_welcome',
                'manydial_msg_confirm',
                'manydial_msg_cancel',
                'manydial_key_confirm',
                'manydial_key_cancel',
                'manydial_per_call_duration',
                'manydial_repeat',
                'manydial_forward',
                'manydial_max_attempts',
                'manydial_retry_delay',
            ] as $column) {
                if (Schema::hasColumn('informations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
