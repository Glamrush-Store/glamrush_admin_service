<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cache_metric_snapshots', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('service_name', 80);
            $table->string('area', 80);
            $table->timestamp('bucket_started_at');
            $table->timestamp('bucket_ended_at');
            $table->unsignedBigInteger('hits')->default(0);
            $table->unsignedBigInteger('misses')->default(0);
            $table->unsignedBigInteger('writes')->default(0);
            $table->unsignedBigInteger('forgets')->default(0);
            $table->decimal('hit_ratio', 8, 4)->default(0);
            $table->json('redis_metrics')->nullable();
            $table->timestamps();

            $table->unique(['service_name', 'area', 'bucket_started_at'], 'cache_metric_snapshots_unique_bucket');
            $table->index(['bucket_started_at', 'service_name', 'area'], 'cache_metric_snapshots_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cache_metric_snapshots');
    }
};
