<?php

use App\Models\Setting;
use App\Models\SettingCategory;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds Cloudflare R2 settings without overwriting configured values', function () {
    $this->seed(SiteSettingSeeder::class);

    $category = SettingCategory::query()->where('slug', 'media-storage')->firstOrFail();
    $keys = Setting::query()
        ->where('setting_category_id', $category->id)
        ->whereIn('key', [
            'R2_ACCESS_KEY_ID',
            'R2_SECRET_ACCESS_KEY',
            'R2_BUCKET',
            'R2_ENDPOINT',
            'R2_URL',
            'R2_REGION',
            'R2_USE_PATH_STYLE_ENDPOINT',
        ])
        ->pluck('value_type', 'key');

    expect($keys)->toHaveCount(7)
        ->and($keys['R2_USE_PATH_STYLE_ENDPOINT'])->toBe('boolean')
        ->and($keys['R2_BUCKET'])->toBe('string');

    $secret = Setting::query()->where('key', 'R2_SECRET_ACCESS_KEY')->firstOrFail();
    $secret->update(['value' => ['value' => 'configured-secret']]);

    $this->seed(SiteSettingSeeder::class);

    expect($secret->refresh()->decodedValue())->toBe('configured-secret');
});
