<?php

namespace App\Domain\Collection\UseCases;

use App\Domain\Collection\Actions\UpdateCollectionAction;
use App\Domain\Shared\Actions\AttachImageAction;
use App\Domain\Shared\Actions\CreateAppLogAction;
use App\Models\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UpdateCollectionUseCase
{
    public function __construct(
        private UpdateCollectionAction $updateCollection,
        private AttachImageAction $attachImage,
        private CreateAppLogAction $log
    ) {}

    public function run(Collection $collection, array $data, ?UploadedFile $photo): Collection
    {
        try {
            return DB::transaction(function () use ($collection, $data, $photo) {
                $this->updateCollection->run($collection, $data);

                if ($photo) {
                    $this->attachImage->run($collection, $photo);
                }

                return $collection->refresh();
            });
        } catch (\Throwable $e) {
            $this->log->run(
                level: 'error',
                event: 'COLLECTION_UPDATE_FAILED',
                message: 'Failed to update collection',
                context: [
                    'data' => $data,
                    'exception' => $e->getMessage(),
                ],
                actor: auth()->user()
            );

            throw new \RuntimeException('failed to update Collection', 0, $e);
        }
    }
}
