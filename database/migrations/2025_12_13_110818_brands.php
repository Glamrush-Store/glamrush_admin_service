<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->ulid('id')->primary();

            /*
             |--------------------------------------------------------------------------
             | Identity
             |--------------------------------------------------------------------------
             */
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code')->unique();

            /*
             |--------------------------------------------------------------------------
             | Content
             |--------------------------------------------------------------------------
             */
            $table->text('description')->nullable();

            /*
             |--------------------------------------------------------------------------
             | Media
             |--------------------------------------------------------------------------
             */
            // Can be replaced with Spatie Media Library
            $table->string('logo')->nullable();

            /*
             |--------------------------------------------------------------------------
             | SEO
             |--------------------------------------------------------------------------
             */
            $table->string('meta_title')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();

            /*
             |--------------------------------------------------------------------------
             | Visibility & Merchandising
             |--------------------------------------------------------------------------
             */
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

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
            $table->index('is_active');
            $table->index('sort_order');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
