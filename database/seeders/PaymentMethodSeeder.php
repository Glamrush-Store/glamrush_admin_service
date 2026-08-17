<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Paystack',
                'code' => 'paystack',
                'description' => 'Card, bank transfer, USSD and other Paystack-supported channels.',
                'is_active' => true,
                'sort_order' => 1,
                'public_config' => [
                    'channels' => ['card', 'bank', 'ussd', 'transfer'],
                    'requires_redirect' => true,
                    'payment_flow' => 'redirect',
                    'supports_verification' => true,
                ],
            ],
            [
                'name' => 'Flutterwave',
                'code' => 'flutterwave',
                'description' => 'Card, bank transfer and other Flutterwave-supported channels.',
                'is_active' => true,
                'sort_order' => 2,
                'public_config' => [
                    'channels' => ['card', 'bank_transfer'],
                    'requires_redirect' => true,
                    'payment_flow' => 'redirect',
                    'supports_verification' => true,
                ],
            ],
            [
                'name' => 'Cash',
                'code' => 'cash',
                'description' => 'Cash received for an offline or in-person sale.',
                'is_active' => true,
                'sort_order' => 10,
                'public_config' => ['manual_entry' => true, 'requires_reference' => false],
            ],
            [
                'name' => 'POS',
                'code' => 'pos',
                'description' => 'Payment received through a point-of-sale terminal.',
                'is_active' => true,
                'sort_order' => 11,
                'public_config' => ['manual_entry' => true, 'requires_reference' => true],
            ],
            [
                'name' => 'Bank Transfer',
                'code' => 'bank_transfer',
                'description' => 'Bank transfer confirmed outside the online checkout.',
                'is_active' => true,
                'sort_order' => 12,
                'public_config' => ['manual_entry' => true, 'requires_reference' => true],
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['code' => $method['code']],
                $method
            );
        }
    }
}
