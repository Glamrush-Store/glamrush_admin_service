<?php

namespace App\Http\Controllers\Newsletter;

use App\Http\Controllers\Controller;
use App\Http\Resources\Newsletter\NewsletterSubscriberResource;
use App\Http\Responses\ApiResponse;
use App\Models\NewsletterSubscriber;

class ShowNewsletterSubscriberController extends Controller
{
    public function __invoke(NewsletterSubscriber $subscriber)
    {
        return ApiResponse::success(new NewsletterSubscriberResource($subscriber));
    }
}
