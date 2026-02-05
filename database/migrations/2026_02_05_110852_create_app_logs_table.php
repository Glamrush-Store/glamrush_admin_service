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
        Schema::create('app_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level'); // error, warning, info
            $table->string('event'); // BRAND_CREATE_FAILED, etc
            $table->text('message');
            $table->json('context')->nullable();
            $table->nullableMorphs('actor'); // user/admin/system
            $table->string('actor_name')->nullable(); // name of the actor
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_logs');
    }
};
