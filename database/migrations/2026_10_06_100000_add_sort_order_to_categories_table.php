<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('categories', 'sort_order')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('parent_id');
        });

        // Start from the order the menus already showed, so nothing moves until the
        // admin sorts: main categories by id, subcategories by name under their parent.
        $position = 0;
        foreach (DB::table('categories')->whereNull('parent_id')->orderBy('id')->pluck('id') as $id) {
            DB::table('categories')->where('id', $id)->update(['sort_order' => ++$position]);
        }

        $positions = [];
        foreach (DB::table('categories')->whereNotNull('parent_id')->orderBy('name')->get(['id', 'parent_id']) as $sub) {
            $positions[$sub->parent_id] = ($positions[$sub->parent_id] ?? 0) + 1;
            DB::table('categories')->where('id', $sub->id)->update(['sort_order' => $positions[$sub->parent_id]]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('categories', 'sort_order')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};
