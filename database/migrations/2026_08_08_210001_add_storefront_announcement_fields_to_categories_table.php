<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->string('announcement_primary_text', 160)->nullable()->after('description');
            $table->string('announcement_secondary_text', 160)->nullable()->after('announcement_primary_text');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn(['announcement_primary_text', 'announcement_secondary_text']);
        });
    }
};
