<?php

use App\Exceptions\BusinessException;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->append(App\Http\Middleware\ForceJsonResponse::class);

        $middleware->alias([
            'ability' => CheckForAnyAbility::class,
            'abilities' => CheckAbilities::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'device-lock' => App\Http\Middleware\EnforceDeviceLock::class,
        ]);
    })->withExceptions(function (Exceptions $exceptions): void {



        // Handle 404 model not found
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return ApiResponse::error(
                    'Resource not found',
                    [],
                    404
                );
            }
            return 'Resource not found';
        });

        // Handle business exceptions
        $exceptions->render(function (BusinessException $e, Request $request) {
            if ($request->expectsJson()) {
                return ApiResponse::error(
                    $e->getMessage(),
                    $e->data,
                    $e->getCode()
                );
            }
        });


        // Handle business exceptions
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return ApiResponse::error(
                    $e->getMessage(),
                    [],
                    401
                );
            }
        });



        // Catch-all for unexpected errors (500s)
        $exceptions->render(function (Throwable $e, Request $request) {

            if ($request->expectsJson()) {
                // Log the error for debugging
                report($e);

                // Don't expose internal errors in production
                $message = config('app.debug')
                    ? $e->getMessage()
                    : 'An unexpected error occurred';

                return ApiResponse::error(
                    $message,
                    config('app.debug') ? ['trace' => $e->getTraceAsString()] : [],
                    500
                );
            }
            return 'An unexpected error occurred';
        });


    })->create();
