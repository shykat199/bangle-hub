<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_source')) {
                $table->string('order_source', 60)->nullable()->index();
            }
            if (!Schema::hasColumn('orders', 'utm_source')) {
                $table->string('utm_source', 100)->nullable();
            }
            if (!Schema::hasColumn('orders', 'utm_medium')) {
                $table->string('utm_medium', 100)->nullable();
            }
            if (!Schema::hasColumn('orders', 'utm_campaign')) {
                $table->string('utm_campaign', 150)->nullable();
            }
            if (!Schema::hasColumn('orders', 'referer_url')) {
                $table->text('referer_url')->nullable();
            }
            if (!Schema::hasColumn('orders', 'landing_page_type')) {
                $table->string('landing_page_type', 30)->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $columns = array_filter(
                ['order_source', 'utm_source', 'utm_medium', 'utm_campaign', 'referer_url', 'landing_page_type'],
                fn($c) => Schema::hasColumn('orders', $c)
            );
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
