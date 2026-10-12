<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_zone_locations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('shipping_zone_id')->constrained('shipping_zones')->cascadeOnDelete();
            $table->string('country');
            $table->string('scope', 20);
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('match_key')->unique();
            $table->timestamps();

            $table->index(['country', 'scope']);
            $table->index(['country', 'state', 'city']);
        });

        DB::table('shipping_zones')
            ->orderBy('id')
            ->each(function (object $zone): void {
                $country = strtoupper(trim((string) $zone->country));
                $state = filled($zone->state) ? strtoupper(trim((string) $zone->state)) : null;
                $city = filled($zone->city) ? trim((string) $zone->city) : null;
                $scope = $city !== null ? 'city' : ($state !== null ? 'state' : 'country');
                $matchKey = match ($scope) {
                    'city' => implode('|', [$country, 'city', $state, mb_strtolower($city)]),
                    'state' => implode('|', [$country, 'state', $state]),
                    default => implode('|', [$country, 'country']),
                };

                DB::table('shipping_zone_locations')->insert([
                    'id' => (string) Str::ulid(),
                    'shipping_zone_id' => $zone->id,
                    'country' => $country,
                    'scope' => $scope,
                    'state' => $state,
                    'city' => $city,
                    'match_key' => $matchKey,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_zone_locations');
    }
};
