<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Category\UseCases;

use App\Domain\Category\Actions\CreateCategoryAction;
use App\Domain\Shared\Actions\AttachImageAction;
use App\Domain\Shared\Actions\CreateAppLogAction;
use App\Domain\Shared\Actions\GenerateUniqueSlugAction;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CreateCategoryUseCase
{
    public function __construct(
        private CreateCategoryAction $createCategory,
        private GenerateUniqueSlugAction $generateSlug,
        private AttachImageAction $attachImage,
        private CreateAppLogAction $log
    ) {}

    public function run(array $data, ?UploadedFile $photo): Category
    {
        try {
            return DB::transaction(function () use ($data, $photo) {
                $data['slug'] = $this->generateSlug->run($data['name']);

                $category = $this->createCategory->run($data);

                if ($photo) {
                    $this->attachImage->run($category, $photo);
                }

                return $category;
            });
        } catch (\Throwable $e) {
            $this->log->run(
                level: 'error',
                event: 'CATEGORY_CREATE_FAILED',
                message: 'Failed to create category',
                context: [
                    'data' => $data,
                    'exception' => $e->getMessage(),
                ],
                actor: auth()->user()
            );

            throw new \RuntimeException('failed to create Category', 0, $e);
        }

    }
}
