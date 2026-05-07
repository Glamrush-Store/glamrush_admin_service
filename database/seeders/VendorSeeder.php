<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = [
            [
                'name'           => 'Sarah Johnson',
                'business_name'  => 'GlamRush Official',
                'email'          => 'vendor@glamrush.com',
                'phone'          => '+1 555 001 0001',
                'password'       => Hash::make('password'),
                'email_verified_at' => now(),
                'code'           => 'GLMRSH01',
                'is_active'      => true,
                'address_line_1' => '123 Beauty Lane',
                'address_line_2' => 'Suite 10',
                'city'           => 'New York',
                'state'          => 'NY',
                'postal_code'    => '10001',
                'country'        => 'US',
            ],
            [
                'name'           => 'Michael Chen',
                'business_name'  => 'BeautyHub Supplies',
                'email'          => 'vendor@beautyhub.com',
                'phone'          => '+1 555 002 0002',
                'password'       => Hash::make('password'),
                'email_verified_at' => now(),
                'code'           => 'BTYHHB02',
                'is_active'      => true,
                'address_line_1' => '456 Glamour Ave',
                'address_line_2' => null,
                'city'           => 'Los Angeles',
                'state'          => 'CA',
                'postal_code'    => '90001',
                'country'        => 'US',
            ],
        ];

        foreach ($vendors as $data) {
            Vendor::firstOrCreate(['email' => $data['email']], $data);
        }
    }
}
