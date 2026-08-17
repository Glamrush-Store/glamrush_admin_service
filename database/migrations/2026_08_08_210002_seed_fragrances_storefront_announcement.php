<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('categories')
            ->where('slug', 'fragrances')
            ->whereNull('parent_id')
            ->whereNull('announcement_primary_text')
            ->update(['announcement_primary_text' => 'Free Lagos delivery on orders over ₦100,000']);

        DB::table('categories')
            ->where('slug', 'fragrances')
            ->whereNull('parent_id')
            ->whereNull('announcement_secondary_text')
            ->update(['announcement_secondary_text' => 'Complimentary scent consultation']);
    }

    public function down(): void
    {
        // Preserve administrator-authored storefront copy during rollbacks.
    }
};
