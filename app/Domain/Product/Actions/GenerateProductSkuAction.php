<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Product\Actions;

use Illuminate\Support\Str;

class GenerateProductSkuAction
{
    public function run(
        string $brandCode,
        string $productName,
        int $sequence
    ): string {
        $productCode = $this->productCodeFromName($productName);

        return sprintf(
            '%s-%s-%03d',
            strtoupper($brandCode),
            $productCode,
            $sequence
        );
    }

    private function productCodeFromName(string $name): string
    {
        return strtoupper(
            Str::of(
                Str::of($name)
                    ->ascii()
                    ->replaceMatches('/[^A-Za-z0-9 ]/', '')
                    ->explode(' ')
                    ->map(fn ($word) => substr($word, 0, 3))
                    ->join('')
            )->limit(8, '')
        );
    }
}
