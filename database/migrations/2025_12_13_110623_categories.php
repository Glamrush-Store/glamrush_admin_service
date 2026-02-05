<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->ulid('id')->primary();

            /*
             |--------------------------------------------------------------------------
             | Identity
             |--------------------------------------------------------------------------
             */
            $table->string('name');
            $table->string('slug')->unique();

            /*
             |--------------------------------------------------------------------------
             | Hierarchy
             |--------------------------------------------------------------------------
             */
            $table->ulid('parent_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            /*
             |--------------------------------------------------------------------------
             | Content
             |--------------------------------------------------------------------------
             */
            $table->text('description')->nullable();

            /*
             |--------------------------------------------------------------------------
             | SEO
             |--------------------------------------------------------------------------
             */
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();

            /*
             |--------------------------------------------------------------------------
             | Merchandising
             |--------------------------------------------------------------------------
             */
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            /*
             |--------------------------------------------------------------------------
             | Media (optional)
             |--------------------------------------------------------------------------
             */
            // Use Spatie Media Library or remove if not needed
            $table->string('image')->nullable();

            /*
             |--------------------------------------------------------------------------
             | Timestamps
             |--------------------------------------------------------------------------
             */
            $table->timestamps();
            $table->softDeletes();

            /*
             |--------------------------------------------------------------------------
             | Indexes (Postgres-friendly)
             |--------------------------------------------------------------------------
             */
            $table->index('parent_id');
            $table->index('is_active');
            $table->index('sort_order');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
