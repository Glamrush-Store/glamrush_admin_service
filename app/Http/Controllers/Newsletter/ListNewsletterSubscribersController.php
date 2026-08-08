<?php

namespace App\Http\Controllers\Newsletter;

use App\Domain\Newsletter\UseCases\ListNewsletterSubscribersUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Newsletter\ListNewsletterSubscribersRequest;
use App\Http\Resources\Newsletter\NewsletterSubscriberResource;
use App\Http\Responses\ApiResponse;

class ListNewsletterSubscribersController extends Controller
{
    public function __construct(private ListNewsletterSubscribersUseCase $useCase) {}

    public function __invoke(ListNewsletterSubscribersRequest $request)
    {
        $filters = $request->validated();
        $subscribers = $this->useCase->run($filters, (int) ($filters['per_page'] ?? 15));

        return ApiResponse::success(NewsletterSubscriberResource::collection($subscribers));
    }
}
