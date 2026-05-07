<?php
/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace Database\Seeders;

use App\Models\AttributeType;
use App\Support\AttributeType as AttributeTypeSupport;
use Illuminate\Database\Seeder;

class AttributeTypeSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Fashion'   => ['color', 'size', 'material', 'pattern', 'fit', 'style', 'season'],
            'Cosmetics' => ['shade', 'finish', 'coverage', 'skin_type', 'undertone', 'formula', 'texture', 'spf'],
            'Fragrance' => ['volume', 'concentration', 'scent_family', 'longevity', 'sillage'],
            'Universal' => ['gender', 'age_group', 'limited_edition'],
        ];

        foreach ($data as $category => $values) {
            foreach ($values as $value) {
                AttributeType::updateOrCreate(
                    ['value' => $value],
                    [
                        'category'     => $category,
                        'label'        => ucwords(str_replace('_', ' ', $value)),
                        'display_type' => AttributeTypeSupport::displayType($value),
                    ]
                );
            }
        }
    }
}
