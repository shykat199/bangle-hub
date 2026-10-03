<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'purchase_event_sent')) {
                // Set once the Purchase conversion has gone to Meta/TikTok, so a
                // retried gateway callback or a refreshed success page cannot
                // send the same sale a second time.
                $table->boolean('purchase_event_sent')->default(0)->after('transaction_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'purchase_event_sent')) {
                $table->dropColumn('purchase_event_sent');
            }
        });
    }
};
