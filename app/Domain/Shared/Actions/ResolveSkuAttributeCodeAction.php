<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Shared\Actions;

use App\Models\SkuAttributeCode;

class ResolveSkuAttributeCodeAction
{
    public function run(string $type, string $value): string
    {
        $t = SkuAttributeCode::where('type', $type)->get();

        return SkuAttributeCode::where('type', $type)
            ->where('value', $value)
            ->where('is_active', true)
            ->value('code')
            ?? throw new \DomainException(
                "SKU code not defined for {$type}: {$value}"
            );
    }
}
