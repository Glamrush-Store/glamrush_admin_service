<?php
/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */


use App\Domain\Product\Actions\GenerateProductSkuAction;

it('generates a SKU using brand code, product name, and sequence', function () {
    $action = new GenerateProductSkuAction();

    $sku = $action->run(
        brandCode: 'brd',
        productName: 'Cool Product',
        sequence: 1
    );

    expect($sku)->toBe('BRD-COOPRO-001');
});
