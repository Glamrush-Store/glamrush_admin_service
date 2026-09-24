<?php

namespace App\Http\Resources\ContactSubmission;

use Illuminate\Http\Request;

class ContactSubmissionResource extends ContactSubmissionListResource
{
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'message' => $this->message,
            'metadata' => $this->metadata,
        ];
    }
}
