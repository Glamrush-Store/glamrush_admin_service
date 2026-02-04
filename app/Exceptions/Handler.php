<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // For API requests, always return JSON
        if ($request->is('api/*') || $request->expectsJson()) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle API exceptions
     */
    private function handleApiException($request, Throwable $e)
    {
        $status = 500;
        $response = [
            'message' => 'Server error',
        ];

        if ($e instanceof AuthenticationException) {
            $status = 401;
            $response['message'] = 'Unauthenticated.';
        } elseif ($e instanceof ValidationException) {
            $status = 422;
            $response['message'] = 'Validation failed.';
            $response['errors'] = $e->errors();
        } elseif ($e instanceof HttpException) {
            $status = $e->getStatusCode();
            $response['message'] = $e->getMessage() ?: 'Server error';
        } else {
            $response['message'] = $e->getMessage();
        }

        // Add debug info in local environment
        if (config('app.debug')) {
            $response['exception'] = get_class($e);
            $response['file'] = $e->getFile();
            $response['line'] = $e->getLine();
            $response['trace'] = collect($e->getTrace())->map(fn ($trace) => [
                'file' => $trace['file'] ?? null,
                'line' => $trace['line'] ?? null,
            ])->take(10);
        }

        return response()->json($response, $status);
    }

    /**
     * Convert authentication exception to JSON
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
