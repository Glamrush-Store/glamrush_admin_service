<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Shared\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class AttachImageAction
{
    public function run(Model $model, UploadedFile $file): void
    {
        $model
            ->addMedia($file)
            ->toMediaCollection('image');
    }
}
