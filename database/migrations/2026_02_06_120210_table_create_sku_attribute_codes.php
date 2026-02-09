<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sku_attribute_codes', function (Blueprint $table) {
            $table->id();

            $table->string('type');        // color, size, storage, material
            $table->string('value');       // Black, Large, 128GB
            $table->string('code');        // BLK, L, 128G

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['type', 'value']);
            $table->unique(['type', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sku_attribute_codes');
    }
};
