<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceDeviceLock
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $token = $request->user()?->currentAccessToken();

        if (! $token) {
            return response()->json(['message' => 'Unauthenticated from here'], 401);
        }

        $deviceId = $request->header('X-Device-ID');

        if (! $deviceId) {
            return response()->json(['message' => 'Device ID missing'], 403);
        }

        if (! $token->can('device:'.$deviceId)) {
            return response()->json(['message' => 'Token not valid for this device'], 403);
        }

        return $next($request);
    }
}
