<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'is_manual_assign')) {
                $table->boolean('is_manual_assign')->default(0)->after('assign_user_id');
            }
            if (!Schema::hasColumn('orders', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('is_manual_assign');
            }
        });

        // One-time cleanup: empty statuses become Pending, then lowercase /
        // padded variants are normalized to their canonical casing so they
        // match the admin panel dropdowns and filters again.
        DB::statement("UPDATE `orders` SET `status` = 'Pending' WHERE `status` IS NULL OR TRIM(`status`) = ''");
        DB::statement("UPDATE `orders` SET `status` = TRIM(`status`) WHERE BINARY `status` <> TRIM(`status`)");

        $canonical = ['Pending', 'Incomplete', 'On Hold', 'Scheduled', 'Confirmed', 'Cancelled', 'Processing', 'Courier Complete', 'Shipped', 'Delivered', 'Returning', 'Return Received', 'Return Missing'];
        foreach ($canonical as $status) {
            DB::statement("UPDATE `orders` SET `status` = ? WHERE LOWER(`status`) = LOWER(?) AND BINARY `status` <> ?", [$status, $status, $status]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'is_manual_assign')) {
                $table->dropColumn('is_manual_assign');
            }
            if (Schema::hasColumn('orders', 'assigned_at')) {
                $table->dropColumn('assigned_at');
            }
        });
    }
};
