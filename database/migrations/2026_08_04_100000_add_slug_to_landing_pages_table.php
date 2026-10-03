<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * landing_pages already has 200+ VARCHAR(255) utf8mb4 columns, right at
 * InnoDB's 65535-byte counted-row-size ceiling — a plain VARCHAR(255) slug
 * column fails ALTER TABLE with "Row size too large". TEXT columns are
 * stored off-page and count only a small pointer toward that limit, so
 * slug is declared TEXT here with a prefix-length unique index instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('landing_pages', 'slug')) {
            DB::statement('ALTER TABLE landing_pages ADD COLUMN slug TEXT NULL AFTER id');
            DB::statement('ALTER TABLE landing_pages ADD UNIQUE KEY landing_pages_slug_unique (slug(191))');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('landing_pages', 'slug')) {
            DB::statement('ALTER TABLE landing_pages DROP INDEX landing_pages_slug_unique');
            Schema::table('landing_pages', function ($table) {
                $table->dropColumn('slug');
            });
        }
    }
};
