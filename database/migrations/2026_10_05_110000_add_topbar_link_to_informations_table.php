<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('informations')) {
            return;
        }

        // This wide table already exceeds InnoDB's worst-case 8126-byte row estimate,
        // so strict mode rejects any rebuilding ALTER. Actual row data is far smaller.
        DB::statement('SET SESSION innodb_strict_mode = 0');

        // Optional link for the announcement bar: where it goes and the button label.
        Schema::table('informations', function (Blueprint $table) {
            if (!Schema::hasColumn('informations', 'topbar_link')) {
                $table->string('topbar_link', 500)->nullable();
            }
            if (!Schema::hasColumn('informations', 'topbar_link_text')) {
                $table->string('topbar_link_text', 60)->nullable();
            }
        });

        DB::statement('SET SESSION innodb_strict_mode = 1');
    }

    public function down(): void
    {
        if (!Schema::hasTable('informations')) {
            return;
        }

        Schema::table('informations', function (Blueprint $table) {
            $columns = array_filter(
                ['topbar_link', 'topbar_link_text'],
                fn ($c) => Schema::hasColumn('informations', $c)
            );
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
