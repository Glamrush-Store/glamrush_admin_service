<?php

namespace App\Domain\Collection\UseCases;

use App\Domain\Collection\Actions\CreateCollectionAction;
use App\Domain\Shared\Actions\AttachImageAction;
use App\Domain\Shared\Actions\CreateAppLogAction;
use App\Domain\Shared\Actions\GenerateUniqueSlugAction;
use App\Models\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CreateCollectionUseCase
{
    public function __construct(
        private CreateCollectionAction $createCollection,
        private GenerateUniqueSlugAction $generateSlug,
        private AttachImageAction $attachImage,
        private CreateAppLogAction $log
    ) {}

    public function run(array $data, ?UploadedFile $photo): Collection
    {
        try {
            return DB::transaction(function () use ($data, $photo) {
                $data['slug'] = $this->generateSlug->run($data['name']);
                $collection = $this->createCollection->run($data);

                if ($photo) {
                    $this->attachImage->run($collection, $photo);
                }

                return $collection;
            });
        } catch (\Throwable $e) {
            $this->log->run(
                level: 'error',
                event: 'COLLECTION_CREATE_FAILED',
                message: 'Failed to create collection',
                context: [
                    'data' => $data,
                    'exception' => $e->getMessage(),
                ],
                actor: auth()->user()
            );

            throw new \RuntimeException('failed to create Collection', 0, $e);
        }
    }
}
