<?php

namespace App\Http\Controllers\Authentication;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Authentication\AuthService;
use App\Http\Requests\Authentication\LoginRequest;
use App\Http\Requests\Authentication\RegisterRequest;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function register(RegisterRequest $request)
    {
        $data = $this->authService->register(
            $request->validated(),
            $request->userAgent()
        );

        return $this->respondWithCookie($data, 201);
    }

    public function login(LoginRequest $request)
    {
        $data = $this->authService->login(
            $request->validated(),
            $request->userAgent()
        );

        if (! $data) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        return $this->respondWithCookie($data);
    }

    public function refresh(Request $request)
    {
        $token = $request->cookie('refresh_token');

        if (! $token) {
            return $this->errorResponse('Missing refresh token', 401);
        }

        try {
            $data = $this->authService->refresh($token, $request->userAgent());
            return $this->respondWithCookie($data);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Unauthorized: ' . $e->getMessage(),
                401
            )->withCookie(Cookie::forget('refresh_token'));
        }
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->cookie('refresh_token'));

        return $this->successResponse(null, 'Logged out')
            ->withCookie(Cookie::forget('refresh_token'));
    }

    public function me()
    {
        return $this->successResponse($this->authService->me(), 'Data user berhasil diambil');
    }
    
    public function activeDevices(Request $request)
    {
        $userId = auth('api')->id();

        $devices = $this->authService->activeDevices($userId);

        // Ambil refresh token dari cookie (device saat ini)
        $currentToken = $request->cookie('refresh_token');
        $currentHash = $currentToken ? hash('sha256', $currentToken) : null;

        foreach ($devices as &$device) {
            $device['is_current'] = ($currentHash && $device['token_hash'] === $currentHash);
            unset($device['token_hash']); // jangan expose ke client
        }

        return $this->successResponse($devices, 'Data device berhasil diambil');
    }

    public function globalLogout(Request $request)
    {
        $this->authService->globalLogout(auth('api')->id());

        return $this->successResponse(null, 'All devices logged out')
            ->withCookie(Cookie::forget('refresh_token'));
    }

    private function respondWithCookie(array $data, int $status = 200)
    {
        $cookie = $this->makeRefreshTokenCookie($data['refresh_token']);
        unset($data['refresh_token']);

        return $this->successResponse($data, 'Success', $status)
            ->withCookie($cookie);
    }

    private function makeRefreshTokenCookie(string $token)
    {
        return cookie(
            'refresh_token',
            $token,
            60 * 24 * 30,
            '/',
            null,
            app()->isProduction(),
            true,
            false,
            'Lax'
        );
    }
}
