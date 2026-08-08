<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_codes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type', 32);
            $table->decimal('value', 15, 2)->nullable();
            $table->char('currency', 3)->nullable();
            $table->decimal('maximum_discount_amount', 15, 2)->nullable();
            $table->decimal('minimum_subtotal', 15, 2)->nullable();
            $table->timestampTz('starts_at')->nullable();
            $table->timestampTz('ends_at')->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->unsignedInteger('total_usage_limit')->nullable();
            $table->unsignedInteger('per_customer_usage_limit')->nullable();
            $table->boolean('first_order_only')->default(false);
            $table->boolean('applies_to_sale_items')->default(false);
            $table->boolean('applies_to_all_storefronts')->default(true);
            $table->foreignId('created_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->index(['type', 'starts_at', 'ends_at']);
        });

        Schema::create('discount_code_storefronts', function (Blueprint $table) {
            $table->foreignUlid('discount_code_id')->constrained('discount_codes')->cascadeOnDelete();
            $table->foreignUlid('category_id')->constrained('categories')->restrictOnDelete();
            $table->timestampsTz();
            $table->primary(['discount_code_id', 'category_id']);
        });

        Schema::create('discount_code_targets', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('discount_code_id')->constrained('discount_codes')->cascadeOnDelete();
            $table->string('target_type', 32);
            $table->ulid('target_id');
            $table->string('mode', 16);
            $table->timestampsTz();
            $table->unique(['discount_code_id', 'target_type', 'target_id']);
            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_code_targets');
        Schema::dropIfExists('discount_code_storefronts');
        Schema::dropIfExists('discount_codes');
    }
};
