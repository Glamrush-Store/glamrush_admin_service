<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Usecases\Brand;

use App\Actions\Brand\AttachBrandImageAction;
use App\Actions\Brand\UpdateBrandAction;
use App\Actions\Shared\CreateAppLogAction;
use App\Models\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PHPUnit\Event\Code\Throwable;

class UpdateBrandUseCase
{
    public function __construct(
        private UpdateBrandAction $updateBrand,
        private AttachBrandImageAction $attachImage,
        private CreateAppLogAction $log
    ) {}

    public function run(Brand $brand, array $data, ?UploadedFile $photo): Brand
    {
        try{
            return DB::transaction(function () use ($brand, $data, $photo) {

                $this->updateBrand->run($brand, $data);

                if ($photo) {
                    $this->attachImage->run($brand, $photo);
                }

                return $brand->refresh();
            });
        }catch(\Throwable $e){
        $this->log->run(
            level: 'error',
            event: 'BRAND_UPDATE_FAILED',
            message: 'Failed to update brand',
            context: [
                'data' => $data,
                'exception' => $e->getMessage(),
            ],
            actor: auth()->user()
        );

        throw new \RuntimeException('failed to update Brand', 0, $e);
    }

    }
}
