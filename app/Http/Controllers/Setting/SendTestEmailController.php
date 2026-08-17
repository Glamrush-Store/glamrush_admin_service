<?php

namespace App\Http\Controllers\Setting;

use App\Http\Requests\Setting\SendTestEmailRequest;
use App\Http\Responses\ApiResponse;
use App\Mail\Settings\SettingsTestEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendTestEmailController
{
    public function __invoke(SendTestEmailRequest $request): JsonResponse
    {
        $data = $request->validated();
        $mailer = (string) config('mail.default');
        $fromAddress = config('mail.from.address');

        try {
            Mail::to($data['email'], $data['name'] ?? null)->send(
                new SettingsTestEmail(
                    recipientName: $data['name'] ?? null,
                    mailerName: $mailer,
                    fromAddress: is_string($fromAddress) ? $fromAddress : null,
                )
            );
        } catch (Throwable $exception) {
            report($exception);

            return ApiResponse::error(
                'Unable to send test email with the current mail configuration.',
                [
                    'email' => [$exception->getMessage()],
                    'mailer' => [$mailer],
                ],
                422
            );
        }

        return ApiResponse::success([
            'email' => $data['email'],
            'mailer' => $mailer,
            'from_address' => $fromAddress,
        ], 'Test email sent');
    }
}

