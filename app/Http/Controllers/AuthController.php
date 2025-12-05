<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

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
     *
     * @param \App\Services\AuthService $authService
     * @return void
     */
    public function __construct(\App\Services\AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Get a JWT via given credentials.
     *
     * @param \App\Http\Requests\LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(\App\Http\Requests\LoginRequest $request)
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
     *
     * @param \App\Http\Requests\RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(\App\Http\Requests\RegisterRequest $request)
    {
        $token = $this->authService->register($request->validated());

        return $this->successResponse($this->authService->respondWithToken($token), 'User berhasil terdaftar', 201);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return $this->successResponse($this->authService->me(), 'Data user berhasil diambil');
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->authService->logout();

        return $this->successResponse(null, 'Successfully logged out');
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->successResponse($this->authService->respondWithToken($this->authService->refresh()));
    }
}
