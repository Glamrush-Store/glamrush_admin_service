<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ContentPage;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ContentManagementSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $adminId = User::query()->value('id');
            $storefronts = $this->storefronts(['fragrances', 'skincare']);

            $this->seedPages($adminId, $storefronts);
            $categories = $this->seedFaqCategories();
            $this->seedFaqs($categories, $storefronts, $adminId);
        });
    }

    /** @param array<string, Category> $storefronts */
    private function seedPages(?int $adminId, array $storefronts): void
    {
        $publishedAt = CarbonImmutable::parse('2026-08-01T09:00:00Z');
        $pages = [
            [
                'slug' => 'about-us', 'title' => 'About Glamrush', 'navigation_title' => 'About', 'page_type' => 'about', 'display_order' => 10,
                'excerpt' => 'Discover the thinking, care, and curation behind Glamrush.',
                'content' => '<h2>Beauty, thoughtfully curated</h2><p>Glamrush brings together fragrance, skincare, makeup, haircare, and body essentials chosen for quality, character, and everyday joy.</p><h3>Our promise</h3><p>We make it easier to discover products you will love, understand what you are buying, and shop with confidence.</p>',
                'meta_title' => 'About Glamrush', 'meta_description' => 'Learn about Glamrush and our approach to thoughtfully curated beauty.',
            ],
            [
                'slug' => 'contact', 'title' => 'Contact Us', 'navigation_title' => 'Contact', 'page_type' => 'contact', 'display_order' => 20,
                'excerpt' => 'Questions about an order or product? Our team is ready to help.',
                'content' => '<h2>We would love to hear from you</h2><p>Send our customer-care team a message and include your order reference when your question concerns an existing order.</p><h3>Customer-care hours</h3><p>Monday to Friday, 9:00–17:00 WAT, excluding public holidays.</p>',
                'settings' => ['email' => 'support@glamrush.test', 'phone' => '+2348000000000', 'whatsapp' => '+2348000000000', 'business_hours' => 'Monday–Friday, 9:00–17:00 WAT', 'address' => 'Lagos, Nigeria', 'social_links' => [['platform' => 'instagram', 'url' => 'https://instagram.com/glamrush']]],
                'meta_title' => 'Contact Glamrush', 'meta_description' => 'Contact the Glamrush customer-care team.',
            ],
            [
                'slug' => 'privacy-policy', 'title' => 'Privacy Policy', 'navigation_title' => 'Privacy', 'page_type' => 'privacy_policy', 'display_order' => 30,
                'excerpt' => 'How Glamrush collects, uses, and protects personal information.',
                'content' => '<h2>Your privacy matters</h2><p>We collect information needed to provide our services, fulfil orders, prevent fraud, and improve your shopping experience.</p><h3>How information is used</h3><ul><li>To process and deliver orders.</li><li>To provide customer support.</li><li>To meet legal and security obligations.</li></ul><p>Contact customer care to ask about your personal information.</p>',
                'meta_title' => 'Privacy Policy | Glamrush', 'meta_description' => 'Read the Glamrush privacy policy.',
            ],
            [
                'slug' => 'terms-and-conditions', 'title' => 'Terms and Conditions', 'navigation_title' => 'Terms', 'page_type' => 'terms', 'display_order' => 40,
                'excerpt' => 'The terms that apply when using Glamrush and placing an order.',
                'content' => '<h2>Using Glamrush</h2><p>By using our services or placing an order, you agree to these terms and all applicable laws.</p><h3>Orders and availability</h3><p>Orders are subject to product availability, payment confirmation, and acceptance. We may correct genuine pricing or catalogue errors before dispatch.</p>',
                'meta_title' => 'Terms and Conditions | Glamrush', 'meta_description' => 'Review the terms for using and shopping with Glamrush.',
            ],
            [
                'slug' => 'shipping-policy', 'title' => 'Shipping Policy', 'navigation_title' => 'Shipping', 'page_type' => 'shipping_policy', 'display_order' => 50,
                'excerpt' => 'Delivery areas, processing times, tracking, and shipping expectations.',
                'content' => '<h2>Delivery made clear</h2><p>Available shipping methods, prices, and estimates are shown at checkout for your delivery address.</p><h3>Processing</h3><p>Orders are normally prepared on business days. Delivery estimates begin after dispatch and may change during unusually busy periods.</p><h3>Tracking</h3><p>When tracking is available, we will share it using the contact details supplied with your order.</p>',
                'meta_title' => 'Shipping Policy | Glamrush', 'meta_description' => 'Learn about Glamrush processing, shipping, and tracking.',
            ],
            [
                'slug' => 'returns-and-refunds', 'title' => 'Returns and Refunds', 'navigation_title' => 'Returns', 'page_type' => 'returns_policy', 'display_order' => 60,
                'excerpt' => 'What to do when an item is damaged, incorrect, or unsuitable.',
                'content' => '<h2>Returns and refunds</h2><p>Contact customer care promptly if an item arrives damaged or incorrect. Include your order reference and clear photographs where relevant.</p><h3>Beauty-product hygiene</h3><p>For health and hygiene reasons, opened or used beauty products may not be returnable unless they are faulty.</p><h3>Approved refunds</h3><p>Approved refunds are returned through the original payment method and may take additional processing time.</p>',
                'meta_title' => 'Returns and Refunds | Glamrush', 'meta_description' => 'Read the Glamrush returns and refunds policy.',
            ],
            [
                'slug' => 'fragrance-care-guide', 'title' => 'Fragrance Care Guide', 'navigation_title' => 'Fragrance Care', 'page_type' => 'custom', 'display_order' => 70,
                'excerpt' => 'Simple ways to store and enjoy fragrance for longer.',
                'content' => '<h2>Help your fragrance last</h2><p>Keep fragrance away from direct sunlight, heat, and rapid temperature changes. Store bottles upright with their caps securely fitted.</p><h3>Application</h3><p>Apply to clean skin and avoid rubbing fragrance into the skin, which can change how its notes develop.</p>',
                'meta_title' => 'Fragrance Care Guide | Glamrush', 'meta_description' => 'Learn how to store and apply your Glamrush fragrances.',
                'storefronts' => ['fragrances'],
            ],
            [
                'slug' => 'skincare-routine-guide', 'title' => 'Build Your Skincare Routine', 'navigation_title' => 'Routine Guide', 'page_type' => 'custom', 'display_order' => 80,
                'excerpt' => 'A straightforward guide to building a consistent skincare routine.',
                'content' => '<h2>Start with the essentials</h2><p>A simple routine can begin with a gentle cleanser, a suitable moisturiser, and daily sunscreen.</p><h3>Introduce products gradually</h3><p>Patch test new products and introduce one change at a time so you can understand how your skin responds.</p>',
                'meta_title' => 'Skincare Routine Guide | Glamrush', 'meta_description' => 'Build a simple Glamrush skincare routine.',
                'storefronts' => ['skincare'], 'published_at' => CarbonImmutable::parse('2027-01-15T09:00:00Z'),
            ],
            [
                'slug' => 'beauty-journal-preview', 'title' => 'Beauty Journal Preview', 'navigation_title' => 'Journal', 'page_type' => 'custom', 'display_order' => 90,
                'excerpt' => 'A draft preview of the upcoming Glamrush beauty journal.',
                'content' => '<h2>Stories, routines, and new discoveries</h2><p>This page is intentionally seeded as a draft for the administrative publishing workflow.</p>',
                'meta_title' => 'Beauty Journal | Glamrush', 'meta_description' => 'Beauty stories and guides from Glamrush.', 'is_published' => false, 'published_at' => null,
            ],
            [
                'slug' => 'summer-delivery-notice', 'title' => 'Summer Delivery Notice', 'navigation_title' => 'Delivery Notice', 'page_type' => 'custom', 'display_order' => 100,
                'excerpt' => 'An expired delivery notice for lifecycle demonstrations.',
                'content' => '<h2>Seasonal delivery update</h2><p>This notice is intentionally expired and remains available to administrators for lifecycle testing.</p>',
                'meta_title' => 'Summer Delivery Notice | Glamrush', 'meta_description' => 'A seasonal Glamrush delivery notice.',
                'published_at' => CarbonImmutable::parse('2026-06-01T09:00:00Z'), 'expires_at' => CarbonImmutable::parse('2026-07-01T00:00:00Z'),
            ],
        ];

        foreach ($pages as $definition) {
            $storefrontSlugs = $definition['storefronts'] ?? [];
            unset($definition['storefronts']);
            $definition += ['settings' => null, 'is_published' => true, 'published_at' => $publishedAt, 'expires_at' => null];
            $definition['applies_to_all_storefronts'] = $storefrontSlugs === [];
            $definition['created_by_admin_id'] = $adminId;
            $definition['updated_by_admin_id'] = $adminId;

            $page = ContentPage::withTrashed()->updateOrCreate(['slug' => $definition['slug']], $definition);
            if ($page->trashed()) {
                $page->restore();
            }
            $page->storefronts()->sync(collect($storefrontSlugs)->map(fn (string $slug) => $storefronts[$slug]->id)->all());
        }
    }

    /** @return array<string, FaqCategory> */
    private function seedFaqCategories(): array
    {
        $definitions = [
            ['slug' => 'orders', 'name' => 'Orders', 'description' => 'Placing, changing, and tracking orders.'],
            ['slug' => 'shipping', 'name' => 'Shipping', 'description' => 'Delivery methods, estimates, and tracking.'],
            ['slug' => 'returns', 'name' => 'Returns and Refunds', 'description' => 'Returns, damaged items, and refund timing.'],
            ['slug' => 'payments', 'name' => 'Payments', 'description' => 'Payment methods, charges, and failed payments.'],
            ['slug' => 'products', 'name' => 'Products', 'description' => 'Choosing, using, and caring for products.'],
            ['slug' => 'account', 'name' => 'Account', 'description' => 'Accounts, saved details, and communication preferences.'],
        ];

        $categories = [];
        foreach ($definitions as $index => $definition) {
            $category = FaqCategory::withTrashed()->updateOrCreate(['slug' => $definition['slug']], [...$definition, 'display_order' => $index + 1, 'is_active' => true]);
            if ($category->trashed()) {
                $category->restore();
            }
            $categories[$definition['slug']] = $category;
        }

        return $categories;
    }

    /** @param array<string, FaqCategory> $categories @param array<string, Category> $storefronts */
    private function seedFaqs(array $categories, array $storefronts, ?int $adminId): void
    {
        $publishedAt = CarbonImmutable::parse('2026-08-01T09:00:00Z');
        $definitions = [
            ['category' => 'orders', 'question' => 'How do I know my order was received?', 'answer' => '<p>After a successful checkout, you will receive an order reference and confirmation using the contact details supplied with your order.</p>'],
            ['category' => 'orders', 'question' => 'Can I change an order after placing it?', 'answer' => '<p>Contact customer care as soon as possible. Changes are not guaranteed once fulfilment has started.</p>'],
            ['category' => 'orders', 'question' => 'Where can I find my order reference?', 'answer' => '<p>Your order reference appears on the confirmation screen and in your order confirmation message.</p>'],
            ['category' => 'shipping', 'question' => 'How long does delivery take?', 'answer' => '<p>Available delivery estimates are shown at checkout and depend on your address and selected shipping method.</p>'],
            ['category' => 'shipping', 'question' => 'How can I track my delivery?', 'answer' => '<p>When tracking is available, we will send tracking details after your order has been dispatched.</p>'],
            ['category' => 'shipping', 'question' => 'What happens if my delivery is delayed?', 'answer' => '<p>Carrier delays can occasionally occur. Contact customer care with your order reference if the latest estimate has passed.</p>'],
            ['category' => 'returns', 'question' => 'Can I return an opened beauty product?', 'answer' => '<p>For hygiene reasons, opened or used beauty products may not be returnable unless they are faulty. Review the returns policy for details.</p>'],
            ['category' => 'returns', 'question' => 'What should I do if an item arrives damaged?', 'answer' => '<p>Contact customer care promptly with your order reference and clear photographs of the item and packaging.</p>'],
            ['category' => 'returns', 'question' => 'How long do approved refunds take?', 'answer' => '<p>Approved refunds are sent to the original payment method. Your bank or payment provider may require additional processing time.</p>'],
            ['category' => 'payments', 'question' => 'Which payment methods can I use?', 'answer' => '<p>The payment methods currently available to you are displayed securely during checkout.</p>'],
            ['category' => 'payments', 'question' => 'Why did my payment fail?', 'answer' => '<p>Check your payment details, available balance, and bank authentication. If the problem continues, try another displayed payment method or contact your provider.</p>'],
            ['category' => 'payments', 'question' => 'Will I be charged twice if checkout is retried?', 'answer' => '<p>Glamrush uses payment safeguards to avoid duplicate processing. Contact customer care if you see more than one completed charge for the same order.</p>'],
            ['category' => 'products', 'question' => 'How should I store fragrance?', 'answer' => '<p>Keep fragrance upright in a cool, dry place away from direct sunlight and rapid temperature changes.</p>', 'storefronts' => ['fragrances']],
            ['category' => 'products', 'question' => 'How can I choose a fragrance concentration?', 'answer' => '<p>Perfume oils are concentrated and close-wearing, while eau de parfum and body mist offer different projection and wear experiences.</p>', 'storefronts' => ['fragrances']],
            ['category' => 'products', 'question' => 'How should I introduce a new skincare product?', 'answer' => '<p>Patch test first and introduce one new product at a time. Stop use and seek appropriate advice if irritation occurs.</p>', 'storefronts' => ['skincare']],
            ['category' => 'account', 'question' => 'Do I need an account to shop?', 'answer' => '<p>Available checkout options are shown during your purchase. An account can make it easier to review orders and save preferences.</p>'],
            ['category' => 'account', 'question' => 'How do I reset my password?', 'answer' => '<p>Use the password-reset option on the sign-in screen and follow the verification instructions sent to your registered address.</p>'],
            ['category' => 'account', 'question' => 'How do I update my communication preferences?', 'answer' => '<p>Use the preference links in eligible messages or contact customer care for assistance.</p>'],
        ];

        $categoryPositions = [];
        foreach ($definitions as $definition) {
            $category = $categories[$definition['category']];
            $position = ($categoryPositions[$definition['category']] ?? 0) + 1;
            $categoryPositions[$definition['category']] = $position;
            $storefrontSlugs = $definition['storefronts'] ?? [];
            $faq = Faq::withTrashed()->updateOrCreate(
                ['faq_category_id' => $category->id, 'question' => $definition['question']],
                ['answer' => $definition['answer'], 'display_order' => $position, 'is_published' => true, 'published_at' => $publishedAt, 'expires_at' => null, 'applies_to_all_storefronts' => $storefrontSlugs === [], 'created_by_admin_id' => $adminId, 'updated_by_admin_id' => $adminId]
            );
            if ($faq->trashed()) {
                $faq->restore();
            }
            $faq->storefronts()->sync(collect($storefrontSlugs)->map(fn (string $slug) => $storefronts[$slug]->id)->all());
        }
    }

    /** @return array<string, Category> */
    private function storefronts(array $slugs): array
    {
        $storefronts = Category::query()->whereIn('slug', $slugs)->whereNull('parent_id')->where('is_active', true)->get()->keyBy('slug');
        foreach ($slugs as $slug) {
            if (! $storefronts->has($slug)) {
                throw new RuntimeException("Content seeding requires the active root storefront [{$slug}]. Run AppDataSeeder first.");
            }
        }

        return $storefronts->all();
    }
}
