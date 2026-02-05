<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Usecases\Category;

use App\Actions\Category\AttachCategoryImageAction;
use App\Actions\Category\UpdateCategoryAction;
use App\Actions\Shared\CreateAppLogAction;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UpdateCategoryUseCase
{
    public function __construct(
        private UpdateCategoryAction $updateCategory,
        private AttachCategoryImageAction $attachImage,
        private CreateAppLogAction $log
    ) {}

    public function run(Category $category, array $data, ?UploadedFile $photo): Category
    {
        try {
            return DB::transaction(function () use ($category, $data, $photo) {

                $this->updateCategory->run($category, $data);

                if ($photo) {
                    $this->attachImage->run($category, $photo);
                }

                return $category->refresh();
            });
        } catch (\Throwable $e)
        {
            $this->log->run(
                level: 'error',
                event: 'CATEGORY_UPDATE_FAILED',
                message: 'Failed to update category',
                context: [
                    'data' => $data,
                    'exception' => $e->getMessage(),
                ],
                actor: auth()->user()
            );

            throw new \RuntimeException('failed to update Category', 0, $e);
        }

    }
}
