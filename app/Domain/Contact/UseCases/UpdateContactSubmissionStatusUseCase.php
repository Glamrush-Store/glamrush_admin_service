<?php

namespace App\Domain\Contact\UseCases;

use App\Domain\Contact\Enums\ContactSubmissionStatus;
use App\Models\ContactSubmission;
use Illuminate\Support\Facades\DB;

class UpdateContactSubmissionStatusUseCase
{
    public function run(ContactSubmission $submission, ContactSubmissionStatus $status): ContactSubmission
    {
        return DB::transaction(function () use ($submission, $status): ContactSubmission {
            $submission = ContactSubmission::query()
                ->whereKey($submission->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $submission->forceFill([
                'status' => $status,
                'resolved_at' => $status === ContactSubmissionStatus::Resolved
                    ? ($submission->resolved_at ?? now())
                    : null,
            ])->save();

            return $submission->refresh()->load([
                'storefront:id,name,slug',
                'customer:id,name,email,phone',
            ]);
        });
    }
}
