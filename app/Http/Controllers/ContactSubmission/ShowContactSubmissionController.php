<?php

namespace App\Http\Controllers\ContactSubmission;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactSubmission\ContactSubmissionResource;
use App\Http\Responses\ApiResponse;
use App\Models\ContactSubmission;

class ShowContactSubmissionController extends Controller
{
    public function __invoke(ContactSubmission $submission)
    {
        $submission->load([
            'storefront:id,name,slug',
            'customer:id,name,email,phone',
        ]);

        return ApiResponse::success(new ContactSubmissionResource($submission));
    }
}
