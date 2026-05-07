<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Brand\UseCases;

use App\Domain\Brand\Actions\CreateBrandAction;
use App\Domain\Shared\Actions\AttachImageAction;
use App\Domain\Shared\Actions\CreateAppLogAction;
use App\Domain\Shared\Actions\GenerateUniqueSlugAction;
use App\Models\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CreateBrandUseCase
{
    public function __construct(
        private CreateBrandAction $createBrand,
        private GenerateUniqueSlugAction $generateSlug,
        private AttachImageAction $attachImage,
        private CreateAppLogAction $log
    ) {}

    public function run(array $data, ?UploadedFile $photo): Brand
    {
        try {
            return DB::transaction(function () use ($data, $photo) {
                $data['slug'] = $this->generateSlug->run($data['name']);

                $brand = $this->createBrand->run($data);

                if ($photo) {
                    $this->attachImage->run($brand, $photo);
                }

                return $brand;
            });
        } catch (\Throwable $e) {
            $this->log->run(
                level: 'error',
                event: 'BRAND_CREATE_FAILED',
                message: 'Failed to create brand',
                context: [
                    'data' => $data,
                    'exception' => $e->getMessage(),
                ],
                actor: auth()->user()
            );

            throw new \RuntimeException('failed to create Brand', 0, $e);
        }
    }
}
