<?php

namespace App\Domain\Collection\Actions;

use App\Models\Collection;

class CreateCollectionAction
{
    public function run(array $data): Collection
    {
        try {
            return Collection::create($data);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to create collection', 0, $e);
        }
    }
}
