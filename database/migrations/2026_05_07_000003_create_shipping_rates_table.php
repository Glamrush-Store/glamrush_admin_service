<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('shipping_zone_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('shipping_method_id')->constrained()->cascadeOnDelete();
            $table->string('rate_type');
            $table->decimal('amount', 10, 2);
            $table->decimal('free_over_amount', 10, 2)->nullable();
            $table->decimal('min_order_amount', 10, 2)->nullable();
            $table->decimal('max_order_amount', 10, 2)->nullable();
            $table->unsignedSmallInteger('estimated_days_min')->nullable();
            $table->unsignedSmallInteger('estimated_days_max')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
