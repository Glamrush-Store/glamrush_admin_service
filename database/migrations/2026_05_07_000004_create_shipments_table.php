<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('order_id')->index();
            $table->foreignUlid('shipping_method_id')->constrained()->nullOnDelete();
            $table->foreignUlid('shipping_zone_id')->constrained()->nullOnDelete();
            $table->decimal('shipping_amount', 10, 2);
            $table->string('status')->default('pending')->index();
            $table->string('tracking_number')->nullable();
            $table->string('carrier')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
