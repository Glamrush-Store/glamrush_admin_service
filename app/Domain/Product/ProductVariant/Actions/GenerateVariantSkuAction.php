<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Product\ProductVariant\Actions;

use App\Domain\Shared\Actions\ResolveSkuAttributeCodeAction;

class GenerateVariantSkuAction
{
    public function __construct(
        private ResolveSkuAttributeCodeAction $resolve
    ) {}

    public function run(string $productSku, array $attributes): string
    {

        $codes = [];

        foreach ($attributes as $type => $attribute) {
            if (is_array($attribute) && isset($attribute['type'], $attribute['value'])) {
                $type = $attribute['type'];
                $value = $attribute['value'];
            } else {
                $value = $attribute;
            }

            $codes[] = $this->resolve->run((string) $type, (string) $value);
        }

        return $productSku.'-'.implode('-', $codes);
    }
}
