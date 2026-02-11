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
        Schema::create('vendors', function (Blueprint $table) {
            $table->ulid('id')->primary();
            // Identity
            $table->string('name');
            $table->string('business_name');
            $table->string('email')->unique(); // login
            $table->string('phone')->nullable();

            // Authentication
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();

            // Business info
            $table->string('code')->unique(); // public identifier
            $table->boolean('is_active')->default(true)->index();

            // Address (primary location)
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable()->index();
            $table->string('state')->nullable()->index();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable()->index();

            // Offline / sync support
            $table->timestamp('last_stock_sync_at')->nullable()->index();

            // Security / sessions
            $table->rememberToken();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
