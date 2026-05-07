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
            'color' => 'color_swatch',
            'size' => 'size_label',
            'material' => 'select',
            'pattern' => 'select',
            'fit' => 'select',
            'style' => 'select',
            'season' => 'select',

            // Cosmetics
            'shade' => 'select',
            'finish' => 'select',
            'coverage' => 'select',
            'skin_type' => 'select',
            'undertone' => 'select',
            'formula' => 'select',
            'texture' => 'select',
            'spf' => 'select',

            // Fragrance
            'volume' => 'select',
            'concentration' => 'select',
            'scent_family' => 'select',
            'longevity' => 'select',
            'sillage' => 'select',

            // Universal
            'gender' => 'select',
            'age_group' => 'select',
            'limited_edition' => 'select',
        ];
    }

    public static function displayType(string $type): string
    {
        return self::all()[$type] ?? 'select';
    }


    public static function formatted(): array
{
    return collect(self::all())
        ->map(fn($displayType, $type) => [
            'value'        => $type,
            'label'        => ucwords(str_replace('_', ' ', $type)),
            'display_type' => $displayType,
        ])
        ->values()
        ->toArray();
}
}
