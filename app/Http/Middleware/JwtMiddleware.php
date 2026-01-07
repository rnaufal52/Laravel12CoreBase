<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\ApiResponse;

class JwtMiddleware
{
    use ApiResponse;

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (! $user) {
                return $this->errorResponse(
                    'Unauthorized',
                    Response::HTTP_UNAUTHORIZED
                );
            }

            // optional: inject user ke request
            $request->attributes->set('auth_user', $user);

        } catch (TokenExpiredException $e) {
            return $this->errorResponse(
                'Token sudah kadaluarsa',
                Response::HTTP_UNAUTHORIZED
            );
        } catch (TokenInvalidException $e) {
            return $this->errorResponse(
                'Token tidak valid',
                Response::HTTP_UNAUTHORIZED
            );
        } catch (JWTException $e) {
            return $this->errorResponse(
                'Token otorisasi tidak ditemukan',
                Response::HTTP_UNAUTHORIZED
            );
        }

        return $next($request);
    }
}
