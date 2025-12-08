<?php

namespace App\Http\Controllers\SupportModul\AuthenticationAndRBAC\Authentication;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Services\SupportModul\AuthenticationAndRBAC\Authentication\AuthService;
use App\Http\Requests\SupportModul\AuthenticationAndRBAC\Authentication\LoginRequest;
use App\Http\Requests\SupportModul\AuthenticationAndRBAC\Authentication\RegisterRequest;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    protected $authService;

    /**
     * Create a new AuthController instance.
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Get a JWT via given credentials.
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (! $token = $this->authService->login($credentials)) {
            return $this->errorResponse('Email atau password salah', 401);
        }

        // dd($this->getRoleNames()->first());

        return $this->successResponse($this->authService->respondWithToken($token),'Login berhasil');
    }

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request)
    {
        $token = $this->authService->register($request->validated());

        return $this->successResponse($this->authService->respondWithToken($token), 'User berhasil terdaftar', 201);
    }

    /**
     * Get the authenticated User.
     */
    public function me()
    {
        return $this->successResponse($this->authService->me(), 'Data user berhasil diambil');
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout()
    {
        $this->authService->logout();

        return $this->successResponse(null, 'Successfully logged out');
    }

    /**
     * Refresh a token.
     */
    public function refresh()
    {
        return $this->successResponse($this->authService->respondWithToken($this->authService->refresh()));
    }
}
