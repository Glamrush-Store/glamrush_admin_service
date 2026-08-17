<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\SettingCategory;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->categories() as $categoryDefinition) {
            $category = SettingCategory::updateOrCreate(
                ['slug' => $categoryDefinition['slug']],
                [
                    'name' => $categoryDefinition['name'],
                    'description' => $categoryDefinition['description'],
                    'sort_order' => $categoryDefinition['sort_order'],
                    'is_active' => true,
                ]
            );

            foreach ($categoryDefinition['settings'] as $key => $definition) {
                $setting = Setting::firstOrNew([
                    'setting_category_id' => $category->id,
                    'key' => $key,
                ]);

                if (! $setting->exists) {
                    $setting->value = ['value' => $definition['value']];
                }

                $setting->fill([
                    'value_type' => $definition['type'],
                    'description' => $definition['description'],
                    'is_public' => false,
                    'is_active' => true,
                ])->save();
            }
        }
    }

    private function categories(): array
    {
        return [
            [
                'name' => 'API_RATE_LIMITING',
                'slug' => 'api-rate-limiting',
                'description' => 'Runtime API rate limits, cache TTLs, idempotency windows, and storefront limits.',
                'sort_order' => 10,
                'settings' => [
                    'API_RATE_LIMIT_GENERAL_PER_MINUTE' => $this->integer(120, 'General API requests allowed per minute.'),
                    'API_RATE_LIMIT_CATALOG_PER_MINUTE' => $this->integer(300, 'Catalog API requests allowed per minute.'),
                    'API_RATE_LIMIT_LOGIN_PER_MINUTE' => $this->integer(5, 'Login attempts allowed per minute.'),
                    'API_RATE_LIMIT_ONBOARDING_PER_MINUTE' => $this->integer(5, 'Registration and onboarding requests allowed per minute.'),
                    'API_RATE_LIMIT_PASSWORD_FORGOT_EMAIL_PER_HOUR' => $this->integer(3, 'Password reset emails allowed per email address per hour.'),
                    'API_RATE_LIMIT_PASSWORD_FORGOT_IP_PER_HOUR' => $this->integer(10, 'Password reset requests allowed per IP per hour.'),
                    'API_RATE_LIMIT_PASSWORD_VERIFY_PER_REQUEST' => $this->integer(5, 'Password reset verification attempts allowed per request window.'),
                    'API_RATE_LIMIT_PASSWORD_RESET_PER_HOUR' => $this->integer(5, 'Password reset completions allowed per hour.'),
                    'API_RATE_LIMIT_CART_MUTATIONS_PER_MINUTE' => $this->integer(30, 'Cart mutation requests allowed per minute.'),
                    'API_RATE_LIMIT_CHECKOUT_PAYMENT_PER_MINUTE' => $this->integer(10, 'Checkout and payment initialization requests allowed per minute.'),
                    'API_RATE_LIMIT_PAYMENT_VERIFY_PER_MINUTE' => $this->integer(10, 'Payment verification requests allowed per minute.'),
                    'API_RATE_LIMIT_WEBHOOK_IP_PER_MINUTE' => $this->integer(120, 'Webhook requests allowed per IP per minute.'),
                    'API_RATE_LIMIT_WEBHOOK_PROVIDER_PER_MINUTE' => $this->integer(600, 'Webhook requests allowed per payment provider per minute.'),
                    'API_RATE_LIMIT_NEWSLETTER_EMAIL_PER_HOUR' => $this->integer(3, 'Newsletter subscription requests allowed per email per hour.'),
                    'API_RATE_LIMIT_NEWSLETTER_IP_PER_HOUR' => $this->integer(10, 'Newsletter subscription requests allowed per IP per hour.'),
                    'API_RATE_LIMIT_NEWSLETTER_ACTION_PER_MINUTE' => $this->integer(10, 'Newsletter confirmation/action requests allowed per minute.'),
                    'API_RATE_LIMIT_CONTACT_SUBMISSIONS_PER_MINUTE' => $this->integer(5, 'Contact submissions allowed per minute.'),
                    'STOREFRONT_HOMEPAGE_CACHE_TTL' => $this->integer(300, 'Storefront homepage cache TTL in seconds.'),
                    'STOREFRONT_CONTEXT_CACHE_TTL' => $this->integer(300, 'Storefront context cache TTL in seconds.'),
                    'PAYMENT_METHODS_CACHE_TTL' => $this->integer(600, 'Payment methods cache TTL in seconds.'),
                    'SHIPPING_CACHE_TTL' => $this->integer(300, 'Shipping cache TTL in seconds.'),
                    'PUBLIC_HTTP_CACHE_MAX_AGE' => $this->integer(60, 'Public HTTP cache max-age in seconds.'),
                    'PUBLIC_HTTP_CACHE_SHARED_MAX_AGE' => $this->integer(60, 'Public HTTP shared cache max-age in seconds.'),
                    'PUBLIC_HTTP_CACHE_STALE_WHILE_REVALIDATE' => $this->integer(15, 'Public HTTP stale-while-revalidate window in seconds.'),
                    'IDEMPOTENCY_LOCK_SECONDS' => $this->integer(60, 'Idempotency lock duration in seconds.'),
                    'IDEMPOTENCY_WAIT_SECONDS' => $this->integer(10, 'Idempotency wait duration in seconds.'),
                    'STOREFRONT_HOMEPAGE_MAX_ITEMS' => $this->integer(50, 'Maximum items returned for storefront homepage sections.'),
                ],
            ],
            [
                'name' => 'PAYMENTS',
                'slug' => 'payments',
                'description' => 'Payment gateway credentials, callback URLs, and provider endpoints.',
                'sort_order' => 20,
                'settings' => [
                    'PAYSTACK_PUBLIC_KEY' => $this->string('', 'Paystack public key.'),
                    'PAYSTACK_SECRET_KEY' => $this->string('', 'Paystack secret key.'),
                    'PAYSTACK_CALLBACK_URL' => $this->string('http://127.0.0.1:3000/payment/callback?provider=paystack', 'Paystack payment callback URL.'),
                    'FLUTTERWAVE_PUBLIC_KEY' => $this->string('', 'Flutterwave public key.'),
                    'FLUTTERWAVE_SECRET_KEY' => $this->string('', 'Flutterwave secret key.'),
                    'FLUTTERWAVE_SECRET_HASH' => $this->string('', 'Flutterwave webhook secret hash.'),
                    'FLUTTERWAVE_CALLBACK_URL' => $this->string('http://127.0.0.1:3000/payment/callback?provider=flutterwave', 'Flutterwave payment callback URL.'),
                    'FLUTTERWAVE_BASE_URL' => $this->string('https://api.flutterwave.com/v3', 'Flutterwave API base URL.'),
                ],
            ],
            [
                'name' => 'MEDIA STORAGE',
                'slug' => 'media-storage',
                'description' => 'Cloudflare R2, legacy Google Cloud Storage, and public media configuration.',
                'sort_order' => 25,
                'settings' => [
                    'R2_ACCESS_KEY_ID' => $this->string('', 'Cloudflare R2 S3 API access key ID.'),
                    'R2_SECRET_ACCESS_KEY' => $this->string('', 'Cloudflare R2 S3 API secret access key.'),
                    'R2_BUCKET' => $this->string('commerce-media-production', 'Cloudflare R2 bucket name.'),
                    'R2_ENDPOINT' => $this->string('https://<ACCOUNT_ID>.r2.cloudflarestorage.com', 'Cloudflare R2 authenticated S3 API endpoint.'),
                    'R2_URL' => $this->string('https://media.yourdomain.com', 'Public custom domain used to serve R2 media.'),
                    'R2_REGION' => $this->string('auto', 'Cloudflare R2 S3 compatibility region.'),
                    'R2_USE_PATH_STYLE_ENDPOINT' => $this->boolean(false, 'Whether the R2 S3 client should use path-style endpoints.'),
                    'GCP_PROJECT_ID' => $this->string('glamrush', 'Google Cloud project ID.'),
                    'GCP_BUCKET' => $this->string('glamrush-images-dev', 'Google Cloud Storage bucket name.'),
                    'GOOGLE_APPLICATION_CREDENTIALS' => $this->string('C:\\Users\\USER\\PhpstormProjects\\shared\\storage-sa.json', 'Path to the Google service account credentials file.'),
                    'GOOGLE_APPLICATION_CREDENTIALS_BASE64' => $this->string('', 'Base64-encoded Google service account credentials JSON.'),
                    'VISIBILITY' => $this->string('public', 'Default storage visibility.'),
                    'USE_GCP_KEY_FILE' => $this->boolean(false, 'Whether to use the credentials file path instead of base64 credentials.'),
                ],
            ],
            [
                'name' => 'ORDER NOTIFICATION MAILS',
                'slug' => 'order-notification-mails',
                'description' => 'Email recipient lists for order notification workflows.',
                'sort_order' => 30,
                'settings' => [
                    'NEW_ORDER_EMAILS' => $this->string('', 'Comma-separated admin email recipients for new order notifications.'),
                    'PAYMENT_FAILED_EMAILS' => $this->string('', 'Comma-separated admin email recipients for failed payment notifications.'),
                    'ABANDONED_CART_EMAILS' => $this->string('', 'Comma-separated admin email recipients for abandoned cart notifications.'),
                ],
            ],
        ];
    }

    private function string(string $value, string $description): array
    {
        return ['value' => $value, 'type' => 'string', 'description' => $description];
    }

    private function integer(int $value, string $description): array
    {
        return ['value' => $value, 'type' => 'integer', 'description' => $description];
    }

    private function boolean(bool $value, string $description): array
    {
        return ['value' => $value, 'type' => 'boolean', 'description' => $description];
    }
}
