<?php

namespace App\Http\Requests\ContactSubmission;

use App\Domain\Contact\Enums\ContactSubmissionStatus;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class UpdateContactSubmissionStatusRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ContactSubmissionStatus::class)],
        ];
    }
}
