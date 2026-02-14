<?php
/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Media;


use App\Http\Responses\ApiResponse;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DeleteMediaController
{
    public function __invoke(Media $media)
    {
        $media->delete();

        return ApiResponse::success([], '', 204);
    }
}
