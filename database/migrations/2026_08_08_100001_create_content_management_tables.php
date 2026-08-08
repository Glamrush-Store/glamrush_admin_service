<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_pages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('slug', 160)->unique();
            $table->string('title');
            $table->string('navigation_title', 100)->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('page_type', 32);
            $table->json('settings')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->boolean('is_published')->default(false)->index();
            $table->timestampTz('published_at')->nullable();
            $table->timestampTz('expires_at')->nullable();
            $table->boolean('applies_to_all_storefronts')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            $table->foreignId('created_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->index(['page_type', 'display_order']);
            $table->index(['published_at', 'expires_at']);
        });

        Schema::create('content_page_storefronts', function (Blueprint $table) {
            $table->foreignUlid('content_page_id')->constrained('content_pages')->cascadeOnDelete();
            $table->foreignUlid('category_id')->constrained('categories')->restrictOnDelete();
            $table->timestampsTz();
            $table->primary(['content_page_id', 'category_id']);
        });

        Schema::create('faq_categories', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug', 160)->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('faq_category_id')->constrained('faq_categories')->restrictOnDelete();
            $table->string('question', 500);
            $table->longText('answer');
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_published')->default(false)->index();
            $table->timestampTz('published_at')->nullable();
            $table->timestampTz('expires_at')->nullable();
            $table->boolean('applies_to_all_storefronts')->default(true);
            $table->foreignId('created_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->index(['faq_category_id', 'display_order']);
            $table->index(['published_at', 'expires_at']);
        });

        Schema::create('faq_storefronts', function (Blueprint $table) {
            $table->foreignUlid('faq_id')->constrained('faqs')->cascadeOnDelete();
            $table->foreignUlid('category_id')->constrained('categories')->restrictOnDelete();
            $table->timestampsTz();
            $table->primary(['faq_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faq_storefronts');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('faq_categories');
        Schema::dropIfExists('content_page_storefronts');
        Schema::dropIfExists('content_pages');
    }
};
