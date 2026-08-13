<?php

use App\Domain\Storefront\Enums\HomepageSectionType;
use App\Models\AttributeType;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\SkuAttributeCode;
use App\Models\StorefrontCampaign;
use App\Models\StorefrontHomepageSection;
use Database\Seeders\AppDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('seeds cohesive application data idempotently', function () {
    Storage::fake('local');
    config([
        'media-library.disk_name' => 'local',
        'queue.default' => 'database',
    ]);

    $this->seed(AppDataSeeder::class);
    $this->seed(AppDataSeeder::class);

    $fragrances = Category::where('slug', 'fragrances')->firstOrFail();
    $perfumeOils = Category::where('slug', 'perfume-oils')->firstOrFail();
    expect($perfumeOils->parent_id)->toBe($fragrances->id);

    expect(AttributeType::where('value', 'volume')->count())->toBe(1)
        ->and(SkuAttributeCode::where('type', 'volume')->where('value', '50ml')->count())->toBe(1);

    expect(Category::count())->toBeGreaterThanOrEqual(25)
        ->and(Product::count())->toBeGreaterThanOrEqual(150)
        ->and(Product::where('type', 'variable')->count())->toBeGreaterThanOrEqual(50);

    $product = Product::where('slug', 'midnight-amber-perfume-oil')->with(['variants', 'primaryCategory'])->firstOrFail();
    expect($product->primaryCategory->first()?->id)->toBe($perfumeOils->id)
        ->and($product->variants)->toHaveCount(2)
        ->and($product->variants->first()->attributes[0])->toBe(['type' => 'volume', 'value' => '30ml'])
        ->and($product->getMedia('catalog-photos'))->toHaveCount(1)
        ->and($perfumeOils->getMedia('catalog-photos'))->toHaveCount(1)
        ->and(file_exists(database_path('seeders/assets/perfume-oils/perfume-oil-01.jpg')))->toBeTrue();

    $collection = Collection::where('slug', 'midnight-edit')->with('products')->firstOrFail();
    expect($collection->products->pluck('slug')->all())->toBe([
        'midnight-amber-perfume-oil',
        'after-dark-eau-de-parfum',
        'velvet-oud-perfume-oil',
    ]);

    expect(StorefrontCampaign::count())->toBeGreaterThanOrEqual(8)
        ->and(StorefrontCampaign::where('storefront_slug', 'fragrances')->count())->toBe(6)
        ->and(StorefrontCampaign::where('storefront_slug', 'fragrances')->current()->orderByDesc('priority')->first()->internal_name)->toBe('The Midnight Edit');

    $manual = StorefrontHomepageSection::where('storefront_slug', 'fragrances')
        ->where('type', HomepageSectionType::ManualProducts->value)
        ->with('products:id,slug')
        ->firstOrFail();

    expect(StorefrontHomepageSection::where('storefront_slug', 'fragrances')->count())->toBe(7)
        ->and($manual->products->pluck('slug')->all())->toBe([
            'after-dark-eau-de-parfum',
            'midnight-amber-perfume-oil',
            'white-bloom-eau-de-parfum',
            'velvet-oud-perfume-oil',
        ]);
});


