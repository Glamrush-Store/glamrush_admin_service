<?php

namespace App\Domain\Collection\Actions;

use App\Models\Collection;

class UpdateCollectionAction
{
    public function run(Collection $collection, array $data): Collection
    {
        try {
            $collection->update($data);
            return $collection;
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to update collection', 0, $e);
        }
    }
}
