<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

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

        $isApiRequest = fn ($request) =>
            $request->expectsJson() || $request->is('api/*');

        // Validation error (422)
        $exceptions->renderable(function (ValidationException $e, $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
                'data' => null,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        // Unauthorized (403) - Spatie
        $exceptions->renderable(function (
            \Spatie\Permission\Exceptions\UnauthorizedException $e,
            $request
        ) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'status_code' => Response::HTTP_FORBIDDEN,
                'message' => 'Anda tidak memiliki akses untuk tindakan ini',
                'data' => null,
            ], Response::HTTP_FORBIDDEN);
        });

        // Not Found (404)
        $exceptions->renderable(function (
            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e,
            $request
        ) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'status_code' => Response::HTTP_NOT_FOUND,
                'message' => 'Resource tidak ditemukan',
                'data' => null,
            ], Response::HTTP_NOT_FOUND);
        });

        // HTTP Exception (custom status)
        $exceptions->renderable(function (
            \Symfony\Component\HttpKernel\Exception\HttpException $e,
            $request
        ) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'status_code' => $e->getStatusCode(),
                'message' => $e->getMessage() ?: 'Terjadi kesalahan',
                'data' => null,
            ], $e->getStatusCode());
        });

        // Internal Server Error (500)
        // $exceptions->renderable(function (\Throwable $e, $request) use ($isApiRequest) {
        //     if (! $isApiRequest($request)) {
        //         return null;
        //     }

        //     return response()->json([
        //         'success' => false,
        //         'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
        //         'message' => config('app.debug')
        //             ? $e->getMessage()
        //             : 'Terjadi kesalahan internal server',
        //         'data' => null,
        //     ], Response::HTTP_INTERNAL_SERVER_ERROR);
        // });
    })
    ->create();
