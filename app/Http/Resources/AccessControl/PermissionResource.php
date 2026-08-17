<?php

namespace App\Http\Resources\AccessControl;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        [$action, $resource] = array_pad(explode('_', $this->name, 2), 2, null);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'action' => $action,
            'resource' => $resource,
        ];
    }
}
