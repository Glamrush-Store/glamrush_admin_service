<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

use App\Domain\Product\ProductVariant\Actions\GenerateVariantSkuAction;
use App\Domain\Shared\Actions\ResolveSkuAttributeCodeAction;

it('generates a variant sku by appending resolved attribute codes', function () {
    // Arrange
    $resolver = Mockery::mock(ResolveSkuAttributeCodeAction::class);

    $resolver
        ->shouldReceive('run')
        ->with('color', 'Red')
        ->once()
        ->andReturn('RED');

    $resolver
        ->shouldReceive('run')
        ->with('size', 'Large')
        ->once()
        ->andReturn('L');

    $action = new GenerateVariantSkuAction($resolver);

    // Act
    $sku = $action->run(
        'BRD-PROD-001',
        [
            'color' => 'Red',
            'size' => 'Large',
        ]
    );

    // Assert
    expect($sku)->toBe('BRD-PROD-001-RED-L');
});

it('generates a variant sku from imported type and value entries', function () {
    $resolver = Mockery::mock(ResolveSkuAttributeCodeAction::class);
    $resolver->shouldReceive('run')->with('size', 'Small')->once()->andReturn('S');

    $action = new GenerateVariantSkuAction($resolver);

    $sku = $action->run('BRD-PROD-001', [
        ['type' => 'size', 'value' => 'Small'],
    ]);

    expect($sku)->toBe('BRD-PROD-001-S');
});
