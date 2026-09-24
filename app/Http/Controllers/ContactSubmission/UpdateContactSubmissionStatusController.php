<?php

namespace App\Http\Controllers\ContactSubmission;

use App\Domain\Contact\Enums\ContactSubmissionStatus;
use App\Domain\Contact\UseCases\UpdateContactSubmissionStatusUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContactSubmission\UpdateContactSubmissionStatusRequest;
use App\Http\Resources\ContactSubmission\ContactSubmissionResource;
use App\Http\Responses\ApiResponse;
use App\Models\ContactSubmission;

class UpdateContactSubmissionStatusController extends Controller
{
    public function __construct(private UpdateContactSubmissionStatusUseCase $useCase) {}

    public function __invoke(
        UpdateContactSubmissionStatusRequest $request,
        ContactSubmission $submission,
    ) {
        $submission = $this->useCase->run(
            $submission,
            ContactSubmissionStatus::from($request->validated('status')),
        );

        return ApiResponse::success(
            new ContactSubmissionResource($submission),
            'Contact submission status updated',
        );
    }
}
