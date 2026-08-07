<?php

namespace App\Http\Controllers\Newsletter;

use App\Domain\Newsletter\Enums\NewsletterSubscriberStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Newsletter\ExportNewsletterSubscribersRequest;
use App\Models\NewsletterSubscriber;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportNewsletterSubscribersController extends Controller
{
    public function __invoke(ExportNewsletterSubscribersRequest $request): StreamedResponse
    {
        $filters = $request->validated();
        $query = NewsletterSubscriber::query()
            ->select(['id', 'email', 'source', 'consented_at', 'confirmed_at'])
            ->withStatus(NewsletterSubscriberStatus::Subscribed->value)
            ->fromSource($filters['source'] ?? null)
            ->confirmedBetween($filters['confirmed_from'] ?? null, $filters['confirmed_to'] ?? null)
            ->orderBy('confirmed_at')
            ->orderBy('id');

        $filename = 'newsletter-subscribers-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $output = fopen('php://output', 'wb');
            fputcsv($output, ['email', 'source', 'consented_at', 'confirmed_at'], ',', '"', '\\', "\n");

            foreach ($query->cursor() as $subscriber) {
                fputcsv($output, [
                    $this->safeCsvValue($subscriber->email),
                    $this->safeCsvValue($subscriber->source),
                    $subscriber->consented_at?->toISOString(),
                    $subscriber->confirmed_at?->toISOString(),
                ], ',', '"', '\\', "\n");
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function safeCsvValue(?string $value): string
    {
        $value ??= '';

        return preg_match('/^[=+\-@]/', $value) ? "'".$value : $value;
    }
}
