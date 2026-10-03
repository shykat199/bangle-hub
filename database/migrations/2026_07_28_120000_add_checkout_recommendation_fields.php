<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'is_checkout_pick')) {
                // Separate from is_recommended, which drives the home page. A
                // shop usually wants cheap add-ons here (cable, cover, pouch),
                // not the same hero products it features on the front page.
                $table->boolean('is_checkout_pick')->default(0)->after('is_recommended');
            }
        });

        Schema::table('informations', function (Blueprint $table) {
            if (!Schema::hasColumn('informations', 'checkout_reco_active')) {
                $table->boolean('checkout_reco_active')->default(0)->after('coupon_visibility');
            }
            if (!Schema::hasColumn('informations', 'checkout_reco_title')) {
                $table->string('checkout_reco_title', 150)->nullable()->after('checkout_reco_active');
            }
            if (!Schema::hasColumn('informations', 'checkout_reco_limit')) {
                $table->unsignedTinyInteger('checkout_reco_limit')->nullable()->after('checkout_reco_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'is_checkout_pick')) {
                $table->dropColumn('is_checkout_pick');
            }
        });

        Schema::table('informations', function (Blueprint $table) {
            foreach (['checkout_reco_active', 'checkout_reco_title', 'checkout_reco_limit'] as $column) {
                if (Schema::hasColumn('informations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
