<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Product\ProductVariant\Actions;

use App\Domain\Shared\Actions\ResolveSkuAttributeCodeAction;
use Illuminate\Support\Facades\Log;

class GenerateVariantSkuAction
{
    public function __construct(
        private ResolveSkuAttributeCodeAction $resolve
    ) {}

    public function run(string $productSku, array $attributes): string
    {


        $codes = [];

        foreach ($attributes as $attribute) {

            $codes[] = $this->resolve->run($attribute['type'], $attribute['value']);
        }

        return $productSku.'-'.implode('-', $codes);
    }
}
