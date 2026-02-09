<?php

namespace Database\Seeders;

use App\Models\SkuAttributeCode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SkuAttributeCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributes = [
            'color' => [
                'Red'   => 'RED',
                'Blue'  => 'BLUE',
                'Grey'  => 'GREY',
                'Black' => 'BLACK',
            ],
            'size' => [
                'Small'       => 'S',
                'Medium'      => 'M',
                'Large'       => 'L',
                'Extra Large' => 'XL',
            ],
        ];

        foreach ($attributes as $type => $values) {
            foreach ($values as $value => $code) {
                SkuAttributeCode::updateOrCreate(
                    [
                        'type' => $type,
                        'code' => $code,
                    ],
                    [
                        'value' => $value,
                        'is_active' => true,
                    ]
                );
            }
        }
        }
}
