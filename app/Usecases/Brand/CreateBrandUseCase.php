<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Usecases\Brand;

use App\Actions\Brand\AttachBrandImageAction;
use App\Actions\Brand\CreateBrandAction;
use App\Actions\Shared\CreateAppLogAction;
use App\Actions\Shared\GenerateUniqueSlugAction;
use App\Models\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CreateBrandUseCase
{
    public function __construct(
        private CreateBrandAction $createBrand,
        private GenerateUniqueSlugAction $generateSlug,
        private AttachBrandImageAction $attachImage,
        private CreateAppLogAction $log
    ) {}

    public function run(array $data, ?UploadedFile $photo): Brand
    {
        try {
            return DB::transaction(function () use ($data, $photo) {
                $data['slug'] = $this->generateSlug->run($data['name']);

                $category = $this->createBrand->run($data);

                if ($photo) {
                    $this->attachImage->run($category, $photo);
                }

                return $category;
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
