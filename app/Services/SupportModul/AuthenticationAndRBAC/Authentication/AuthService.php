<?php

namespace App\Services\SupportModul\AuthenticationAndRBAC\Authentication;

use Illuminate\Support\Facades\Auth;
use App\Models\Support\Modul\AuthenticationAndRBAC\User;

class AuthService
{
    /**
     * Register a new user and return a token.
     *
     * @param array $data
     * @return string|bool
     */
    public function register(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $user->assignRole('staff');

        if (! $token = auth('api')->login($user)) {
            return false;
        }

        return $token;
    }

    /**
     * Attempt to authenticate the user and return a token.
     *
     * @param array $credentials
     * @return string|bool
     */
    public function login(array $credentials)
    {
        if (! $token = auth('api')->attempt($credentials)) {
            return false;
        }

        return $token;
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function me()
    {
        return auth('api')->user();
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return void
     */
    public function logout()
    {
        auth('api')->logout();
    }

    /**
     * Refresh a token.
     *
     * @return string
     */
    public function refresh()
    {
        return auth('api')->refresh();
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     * @return array
     */
    public function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ];
    }
}
