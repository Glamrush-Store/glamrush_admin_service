<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->ulid('id')->primary();

            /*
             |--------------------------------------------------------------------------
             | Relationship
             |--------------------------------------------------------------------------
             */
            $table->ulid('product_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
             |--------------------------------------------------------------------------
             | Identity
             |--------------------------------------------------------------------------
             */
            $table->string('sku')->unique();

            $table->boolean('is_default')->default(false);

            /*
             |--------------------------------------------------------------------------
             | Pricing
             |--------------------------------------------------------------------------
             */
            $table->decimal('price', 12, 2);
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
             | Variant Attributes
             |--------------------------------------------------------------------------
             */
            // Examples:
            // simple product: {}
            // variable product: {"color":"red","size":"M"}
            $table->jsonb('attributes')->default('{}');

            /*
             |--------------------------------------------------------------------------
             | Merchandising
             |--------------------------------------------------------------------------
             */
            $table->unsignedInteger('sort_order')->default(0);

            /*
             |--------------------------------------------------------------------------
             | Status
             |--------------------------------------------------------------------------
             */
            $table->string('status')->default('active'); // active | disabled

            $table->timestamps();

            /*
             |--------------------------------------------------------------------------
             | Indexes (Postgres-optimized)
             |--------------------------------------------------------------------------
             */
            $table->index('product_id');
            $table->index('status');
            $table->index('sort_order');

            // JSONB GIN index for attribute searching
            $table->index('attributes', null, 'gin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
