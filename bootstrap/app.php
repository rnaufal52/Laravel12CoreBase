<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use App\Helpers\ApiResponseHelper;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'jwt.custom' => \App\Http\Middleware\JwtMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // error validation
        $exceptions->renderable(function (ValidationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $apiResponse = new ApiResponseHelper();

                return $apiResponse->validationErrorResponse(
                    $e->errors(),  // array validation errors
                    'Validasi gagal',
                    422
                );
            }
        });

        // error unauthorized
        $exceptions->renderable(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $apiResponse = new ApiResponseHelper();
                return $apiResponse->errorResponse(
                    'Anda tidak memiliki akses untuk tindakan ini',
                    403
                );
            }
        });

        // error not found
        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $apiResponse = new ApiResponseHelper();
                return $apiResponse->errorResponse(
                    $e->getMessage() ?: 'Resource tidak ditemukan',
                    404
                );
            }
        });

        // error http exception
        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $apiResponse = new ApiResponseHelper();
                return $apiResponse->errorResponse(
                    $e->getMessage() ?: 'Terjadi kesalahan pada server',
                    $e->getStatusCode()
                );
            }
        });

        // error 500
        $exceptions->renderable(function (\Throwable $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $apiResponse = new ApiResponseHelper();
                $message = config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan internal server';
                return $apiResponse->errorResponse(
                    $message,
                    500
                );
            }
        });
    })->create();
