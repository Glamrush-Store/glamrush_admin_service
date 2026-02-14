<?php
/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */


namespace App\Support;

class AttributeType
{
    public static function all(): array
    {
        return [

            // Fashion
            'color',
            'size',
            'material',
            'pattern',
            'fit',
            'style',
            'season',

            // Cosmetics
            'shade',
            'finish',
            'coverage',
            'skin_type',
            'undertone',
            'formula',
            'texture',
            'spf',

            // Fragrance
            'volume',
            'concentration',
            'scent_family',
            'longevity',
            'sillage',

            // Universal
            'gender',
            'age_group',
            'limited_edition',
        ];
    }

    public static function formatted(): array
    {
        return collect(self::all())
            ->map(fn($type) => [
                'value' => $type,
                'label' => ucwords(str_replace('_', ' ', $type)),
            ])
            ->values()
            ->toArray();
    }
}
