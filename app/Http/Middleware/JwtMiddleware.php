<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    use \App\Traits\ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (! $user) {
                return $this->errorResponse('User tidak ditemukan', 404);
            }
        } catch (TokenExpiredException $e) {
            return $this->errorResponse('Token sudah kadaluarsa', 401);
        } catch (TokenInvalidException $e) {
            return $this->errorResponse('Token tidak valid', 401);
        } catch (JWTException $e) {
            return $this->errorResponse('Token otorisasi tidak ditemukan', 401);
        }

        return $next($request);
    }
}
