<?php

namespace App\Http\Resources\ContactSubmission;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactSubmissionListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'storefront_category_id' => $this->storefront_category_id,
            'storefront' => $this->whenLoaded('storefront', fn () => $this->storefront ? [
                'id' => $this->storefront->id,
                'name' => $this->storefront->name,
                'slug' => $this->storefront->slug,
            ] : null),
            'customer_account_id' => $this->customer_account_id,
            'customer' => $this->whenLoaded('customer', fn () => $this->customer ? [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
                'phone' => $this->customer->phone,
            ] : null),
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'subject' => $this->subject,
            'status' => $this->status->value,
            'source' => $this->source,
            'resolved_at' => $this->resolved_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
