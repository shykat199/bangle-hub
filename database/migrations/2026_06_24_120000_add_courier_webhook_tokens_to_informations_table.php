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
            if (!Schema::hasColumn('informations', 'steadfast_webhook_token')) {
                $table->string('steadfast_webhook_token', 191)->nullable();
            }
            if (!Schema::hasColumn('informations', 'pathao_webhook_token')) {
                $table->string('pathao_webhook_token', 191)->nullable();
            }
            if (!Schema::hasColumn('informations', 'redx_webhook_token')) {
                $table->string('redx_webhook_token', 191)->nullable();
            }
            if (!Schema::hasColumn('informations', 'carrybee_webhook_token')) {
                $table->string('carrybee_webhook_token', 191)->nullable();
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
                ['steadfast_webhook_token', 'pathao_webhook_token', 'redx_webhook_token', 'carrybee_webhook_token'],
                fn($c) => Schema::hasColumn('informations', $c)
            );
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
