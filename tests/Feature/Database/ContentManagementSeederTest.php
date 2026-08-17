<?php

use App\Models\Category;
use App\Models\ContentPage;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\User;
use Database\Seeders\ContentManagementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds content pages FAQ categories and FAQs idempotently', function () {
    $admin = User::factory()->create();
    $fragrances = Category::factory()->create(['name' => 'Fragrances', 'slug' => 'fragrances', 'parent_id' => null, 'is_active' => true]);
    $skincare = Category::factory()->create(['name' => 'Skincare', 'slug' => 'skincare', 'parent_id' => null, 'is_active' => true]);

    $this->seed(ContentManagementSeeder::class);
    $this->seed(ContentManagementSeeder::class);

    expect(ContentPage::count())->toBe(10)
        ->and(FaqCategory::count())->toBe(6)
        ->and(Faq::count())->toBe(18)
        ->and(ContentPage::where('slug', 'contact')->firstOrFail()->settings['email'])->toBe('support@glamrush.test')
        ->and(ContentPage::where('slug', 'beauty-journal-preview')->first()->state())->toBe('draft')
        ->and(ContentPage::where('slug', 'skincare-routine-guide')->first()->state())->toBe('scheduled')
        ->and(ContentPage::where('slug', 'summer-delivery-notice')->first()->state())->toBe('expired');

    $fragranceGuide = ContentPage::where('slug', 'fragrance-care-guide')->firstOrFail();
    expect($fragranceGuide->applies_to_all_storefronts)->toBeFalse()
        ->and($fragranceGuide->storefronts()->pluck('categories.id')->all())->toBe([$fragrances->id])
        ->and(Faq::where('question', 'How should I introduce a new skincare product?')->firstOrFail()->storefronts()->value('categories.id'))->toBe($skincare->id)
        ->and(ContentPage::where('slug', 'about-us')->value('created_by_admin_id'))->toBe($admin->id);
});

it('fails clearly when required storefront seed data is missing', function () {
    User::factory()->create();

    expect(fn () => $this->seed(ContentManagementSeeder::class))
        ->toThrow(RuntimeException::class, 'Run AppDataSeeder first');

    expect(ContentPage::count())->toBe(0)
        ->and(FaqCategory::count())->toBe(0)
        ->and(Faq::count())->toBe(0);
});
