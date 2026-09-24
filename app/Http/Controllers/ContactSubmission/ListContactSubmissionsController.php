<?php

namespace App\Http\Controllers\ContactSubmission;

use App\Domain\Contact\UseCases\ListContactSubmissionsUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContactSubmission\ListContactSubmissionsRequest;
use App\Http\Resources\ContactSubmission\ContactSubmissionListResource;
use App\Http\Responses\ApiResponse;

class ListContactSubmissionsController extends Controller
{
    public function __construct(private ListContactSubmissionsUseCase $useCase) {}

    public function __invoke(ListContactSubmissionsRequest $request)
    {
        $filters = $request->validated();
        $submissions = $this->useCase->run($filters, (int) ($filters['per_page'] ?? 15));

        return ApiResponse::success(ContactSubmissionListResource::collection($submissions));
    }
}
