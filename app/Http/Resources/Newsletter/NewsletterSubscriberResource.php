<?php

namespace App\Http\Resources\Newsletter;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsletterSubscriberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'status' => $this->status->value,
            'source' => $this->source,
            'consented_at' => $this->consented_at?->toISOString(),
            'confirmed_at' => $this->confirmed_at?->toISOString(),
            'unsubscribed_at' => $this->unsubscribed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
