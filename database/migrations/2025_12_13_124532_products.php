<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->ulid('id')->primary();

            /*
             |--------------------------------------------------------------------------
             | Identity
             |--------------------------------------------------------------------------
             */
            $table->string('name');
            $table->string('sku')->unique()->nullable();
            $table->string('slug')->unique();

            /*
             |--------------------------------------------------------------------------
             | Content
             |--------------------------------------------------------------------------
             */
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();

            /*
             |--------------------------------------------------------------------------
             | Product Type & Publishing
             |--------------------------------------------------------------------------
             */
            // simple | variable | digital | service
            $table->enum('type', ['simple', 'variable', 'digital', 'service'])->default('simple');

            // draft | published | archived
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable()->default(date('Y-m-d H:i:s'));

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
         | Pricing
         |--------------------------------------------------------------------------
         */
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->timestamp('sale_starts_at')->nullable();
            $table->timestamp('sale_ends_at')->nullable();

            /*
             |--------------------------------------------------------------------------
             | Inventory
             |--------------------------------------------------------------------------
             */
            $table->boolean('manage_stock')->default(true);
            $table->integer('stock_quantity')->default(0);
            $table->boolean('in_stock')->default(true);

            /*
             |--------------------------------------------------------------------------
             | Merchandising
             |--------------------------------------------------------------------------
             */
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            /*
             |--------------------------------------------------------------------------
             | Relationships
             |--------------------------------------------------------------------------
             */
            $table->ulid('category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->ulid('brand_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
             |--------------------------------------------------------------------------
             | Analytics
             |--------------------------------------------------------------------------
             */
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('sales_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            /*
             |--------------------------------------------------------------------------
             | Indexes (Postgres-friendly)
             |--------------------------------------------------------------------------
             */
            $table->index(['status', 'type']);
            $table->index('published_at');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
