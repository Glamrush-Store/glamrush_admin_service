<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_imports', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('vendor_id')->constrained()->restrictOnDelete();
            $table->foreignUlid('brand_id')->constrained()->restrictOnDelete();
            $table->json('category_ids');
            $table->foreignUlid('primary_category_id')->constrained('categories')->restrictOnDelete();
            $table->json('category_sequences')->nullable();
            $table->string('status')->default('pending')->index();
            $table->string('default_product_status')->default('draft');
            $table->string('image_failure_policy')->default('warn');
            $table->string('original_filename');
            $table->string('file_path')->nullable();
            $table->string('file_hash', 64)->index();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('total_products')->default(0);
            $table->unsignedInteger('processed_products')->default(0);
            $table->unsignedInteger('imported_products')->default(0);
            $table->unsignedInteger('failed_products')->default(0);
            $table->unsignedInteger('warning_count')->default(0);
            $table->json('issues')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_imports');
    }
};
