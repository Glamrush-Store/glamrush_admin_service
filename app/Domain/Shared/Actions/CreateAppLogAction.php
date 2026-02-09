<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Shared\Actions;

use App\Models\AppLog;
use Illuminate\Database\Eloquent\Model;

class CreateAppLogAction
{
    public function run(
        string $level,
        string $event,
        string $message,
        ?array $context = null,
        ?Model $actor = null
    ): void {
        AppLog::create([
            'level' => $level,
            'event' => $event,
            'message' => $message,
            'context' => $context,
            'actor_type' => $actor ? get_class($actor) : null,
            'actor_id' => $actor?->getKey(),
            'actor_name' => $actor?->name ?? null,
        ]);
    }
}
