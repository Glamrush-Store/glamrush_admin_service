<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\ContentPage;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Infrastructure\CacheMetrics\RegisterCacheMetricsListeners;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        app(RegisterCacheMetricsListeners::class)->register();
        $encoded = env('GOOGLE_APPLICATION_CREDENTIALS_BASE64');

        if ($encoded) {
            $directory = storage_path('app');
            $path = $directory.'/google-credentials.json';

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $decoded = base64_decode($encoded, true);

            if ($decoded === false) {
                throw new RuntimeException('Invalid GOOGLE_APPLICATION_CREDENTIALS_BASE64 value.');
            }

            if (! file_exists($path)) {
                file_put_contents($path, $decoded);
            }

            putenv('GOOGLE_APPLICATION_CREDENTIALS='.$path);
            $_ENV['GOOGLE_APPLICATION_CREDENTIALS'] = $path;
            $_SERVER['GOOGLE_APPLICATION_CREDENTIALS'] = $path;
        }

        Relation::enforceMorphMap([
            'category' => Category::class,
            'product' => Product::class,
            'product_variant' => ProductVariant::class,
            'user' => User::class,
            'brand' => \App\Models\Brand::class,
            'vendor' => \App\Models\Vendor::class,
            'sku_attribute_code' => \App\Models\SkuAttributeCode::class,
            'collection' => \App\Models\Collection::class,
            'storefront_campaign' => \App\Models\StorefrontCampaign::class,
            'content_page' => ContentPage::class,
        ]);
    }
}



