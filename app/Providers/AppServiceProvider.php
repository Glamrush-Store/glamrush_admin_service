<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services\[xcv]
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

        // $encoded = env('GOOGLE_APPLICATION_CREDENTIALS_BASE64');

        // $path = '';

        // if ($encoded) {
        //     $path = storage_path('app/google-credentials.json');

        //     if (!file_exists($path)) {
        //         file_put_contents($path, base64_decode($encoded));
        //     }

        //     putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $path);
        //     $_ENV['GOOGLE_APPLICATION_CREDENTIALS'] = $path;
        //     $_SERVER['GOOGLE_APPLICATION_CREDENTIALS'] = $path;
        // }





        Relation::enforceMorphMap([
            'category' => Category::class,
            'product' => Product::class,
            'product_variant' => ProductVariant::class,
            'user' => User::class,
            'brand' => \App\Models\Brand::class,
            'vendor' => \App\Models\Vendor::class,
            'sku_attribute_code' => \App\Models\SkuAttributeCode::class,
            'collection' => \App\Models\Collection::class,
        ]);
    }
}
