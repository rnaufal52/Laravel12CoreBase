<?php

namespace App\Services\Authentication;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Exception;

class AuthService
{
    protected int $refreshTokenTtl = 30 * 24 * 60 * 60;

    /* ===================== REGISTER / LOGIN ===================== */

    public function register(array $data, string $userAgent)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $user->assignRole('staff');

        return $this->generateAuthData($user, null, $userAgent);
    }

    public function login(array $credentials, string $userAgent)
    {
        if (! $accessToken = auth('api')->attempt($credentials)) {
            return false;
        }

        return $this->generateAuthData(auth('api')->user(), $accessToken, $userAgent);
    }

    public function me()
    {
        return auth('api')->user();
    }

    /* ===================== REFRESH ===================== */

    public function refresh(string $rawRefreshToken, string $userAgent)
    {
        $hashed = $this->hashToken($rawRefreshToken);
        $key = $this->refreshKey($hashed);

        $data = Cache::get($key);

        // 🔥 REUSE DETECTION
        if (! $data) {
            throw new Exception('Refresh token reuse detected');
        }

        if ($data['revoked']) {
            $this->revokeAllUserTokens($data['user_id']);
            throw new Exception('Refresh token revoked');
        }

        if ($data['user_agent'] !== $userAgent) {
            $this->revokeAllUserTokens($data['user_id']);
            throw new Exception('Device mismatch');
        }

        if (Carbon::parse($data['expires_at'])->isPast()) {
            $this->revokeAllUserTokens($data['user_id']);
            throw new Exception('Refresh token expired');
        }

        $user = User::find($data['user_id']);
        if (! $user) {
            throw new Exception('User not found');
        }

        // ROTATION
        $this->revokeSingleToken($hashed);

        return $this->generateAuthData($user, auth('api')->login($user), $userAgent);
    }

    /* ===================== LOGOUT ===================== */

    public function logout(?string $rawRefreshToken = null)
    {
        try {
            auth('api')->logout();
        } catch (Exception $e) {}

        if ($rawRefreshToken) {
            $this->revokeSingleToken($this->hashToken($rawRefreshToken));
        }
    }

    public function globalLogout(int $userId): void
    {
        $this->revokeAllUserTokens($userId);
    }

    // Get active devices
    public function activeDevices(int $userId): array
    {
        $devices = [];
        $tokenHashes = Cache::get($this->userIndexKey($userId), []);

        foreach ($tokenHashes as $hash) {
            $data = Cache::get($this->refreshKey($hash));

            if (! $data) {
                continue;
            }

            if ($data['revoked'] === true) {
                continue;
            }

            if (now()->isAfter($data['expires_at'])) {
                continue;
            }

            $devices[] = [
                'device_id'   => $data['device_id'],
                'user_agent'  => $data['user_agent'],
                'expires_at'  => $data['expires_at'],
                'is_current'  => false, // ditentukan di controller
            ];
        }

        return $devices;
    }

    /* ===================== TOKEN GENERATION ===================== */

    private function generateAuthData(User $user, ?string $accessToken, string $userAgent): array
    {
        $accessToken ??= auth('api')->login($user);

        $rawToken = Str::random(64);
        $hashed = $this->hashToken($rawToken);
        $expiresAt = now()->addSeconds($this->refreshTokenTtl);

        $payload = [
            'user_id' => $user->id,
            'device_id' => (string) Str::uuid(),
            'user_agent' => $userAgent,
            'revoked' => false,
            'expires_at' => $expiresAt->toIso8601String(),
        ];

        Cache::put($this->refreshKey($hashed), $payload, $expiresAt);

        // Index per user (MULTI DEVICE)
        $this->attachTokenToUser($user->id, $hashed, $expiresAt);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $rawToken,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => $user,
        ];
    }

    /* ===================== REVOKE ===================== */

    private function revokeSingleToken(string $hashed): void
    {
        $data = Cache::get($this->refreshKey($hashed));
        if (! $data) return;

        $data['revoked'] = true;

        $ttl = Carbon::parse($data['expires_at'])->diffInSeconds(now(), false);
        $ttl > 0
            ? Cache::put($this->refreshKey($hashed), $data, $ttl)
            : Cache::forget($this->refreshKey($hashed));
    }

    private function revokeAllUserTokens(int $userId): void
    {
        $indexKey = $this->userIndexKey($userId);
        $tokens = Cache::get($indexKey, []);

        foreach ($tokens as $hashed) {
            $this->revokeSingleToken($hashed);
        }

        Cache::forget($indexKey);
    }

    private function attachTokenToUser(int $userId, string $hashed, Carbon $expiresAt): void
    {
        $key = $this->userIndexKey($userId);
        $tokens = Cache::get($key, []);

        $tokens[] = $hashed;

        Cache::put($key, array_unique($tokens), $expiresAt);
    }

    /* ===================== HELPERS ===================== */

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    private function refreshKey(string $hashed): string
    {
        return "auth:refresh_token:{$hashed}";
    }

    private function userIndexKey(int $userId): string
    {
        return "auth:user_tokens:{$userId}";
    }
}
