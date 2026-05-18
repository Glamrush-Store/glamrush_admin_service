<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
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
        Schema::dropIfExists('collections');
    }
};
