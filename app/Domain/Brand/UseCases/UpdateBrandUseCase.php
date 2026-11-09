<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Brand\UseCases;

use App\Domain\Brand\Actions\AttachBrandImageAction;
use App\Domain\Brand\Actions\UpdateBrandAction;
use App\Domain\Shared\Actions\CreateAppLogAction;
use App\Domain\Shared\Actions\GenerateUniqueSlugAction;
use App\Models\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UpdateBrandUseCase
{
    public function __construct(
        private UpdateBrandAction $updateBrand,
        private AttachBrandImageAction $attachImage,
        private CreateAppLogAction $log,
        private GenerateUniqueSlugAction $generateSlug,
    ) {}

    public function run(Brand $brand, array $data, ?UploadedFile $photo): Brand
    {
        try {
            return DB::transaction(function () use ($brand, $data, $photo) {
                if (array_key_exists('name', $data)) {
                    $data['slug'] = $this->generateSlug->run($data['name'], Brand::class, $brand->id);
                }

                $this->updateBrand->run($brand, $data);

                if ($photo) {
                    $this->attachImage->run($brand, $photo);
                }

                return $brand->refresh();
            });
        } catch (\Throwable $e) {
            $this->log->run(
                level: 'error',
                event: 'BRAND_UPDATE_FAILED',
                message: 'Failed to update brand',
                context: [
                    'data' => $data,
                    'exception' => $e->getMessage(),
                ],
                actor: auth()->user()
            );

            throw new \RuntimeException('failed to update Brand', 0, $e);
        }

    }
}
