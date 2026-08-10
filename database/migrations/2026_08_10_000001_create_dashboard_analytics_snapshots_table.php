<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_analytics_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('period', 32)->index();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->json('payload');
            $table->timestamp('aggregated_at')->index();
            $table->timestamps();

            $table->unique(['period', 'starts_at', 'ends_at'], 'dashboard_analytics_period_range_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_analytics_snapshots');
    }
};
