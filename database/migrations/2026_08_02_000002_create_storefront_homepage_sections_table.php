<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storefront_homepage_sections', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('storefront_slug')->index();
            $table->string('type');
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->json('config');
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(false)->index();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['storefront_slug', 'is_active', 'display_order'], 'storefront_sections_published_index');
        });

        Schema::create('storefront_homepage_section_product', function (Blueprint $table) {
            $table->ulid('section_id')->constrained('storefront_homepage_sections')->cascadeOnDelete();
            $table->ulid('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('display_order');
            $table->primary(['section_id', 'product_id']);
            $table->index(['section_id', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storefront_homepage_section_product');
        Schema::dropIfExists('storefront_homepage_sections');
    }
};
