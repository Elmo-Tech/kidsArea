<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Authenticate the user and generate a token pair.
     *
     * @param array{
     *     username: string,
     *     password: string
     * } $credentials
     */
    public function login(array $credentials): array
    {
        $user = User::query()
            ->where('username', $credentials['username'])
            ->first();

        if (
            ! $user ||
            ! Hash::check($credentials['password'], $user->password)
        ) {
            return [
                'status_code' => [
                    'INVALID_CREDENTIALS',
                ],
            ];
        }

        if (! $user->isActive()) {
            return [
                'status_code' => [
                    'USER_INACTIVE',
                ],
            ];
        }

        return DB::transaction(function () use ($user): array {

            $user->tokens()->delete();

            return $this->issueTokenPair($user);
        });
    }

    /**
     * Revoke all user access and refresh tokens.
     */
    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Revoke the old token pair and generate a new one.
     */
    public function refreshToken(User $user): array
    {
        if (! $user->isActive()) {
            $user->tokens()->delete();

            return [
                'status_code' => [
                    'USER_INACTIVE',
                ],
            ];
        }

        return DB::transaction(function () use ($user): array {

            $user->tokens()->delete();

            return $this->issueTokenPair($user);
        });
    }

    /**
     * Generate access and refresh tokens.
     */
    private function issueTokenPair(User $user): array
    {
        $accessTokenTtlMinutes = config(
            'auth_tokens.access_token_ttl_minutes',
            15
        );

        $refreshTokenTtlDays = config(
            'auth_tokens.refresh_token_ttl_days',
            30
        );

        $accessTokenExpiresAt = now()->addMinutes(
            $accessTokenTtlMinutes
        );

        $refreshTokenExpiresAt = now()->addDays(
            $refreshTokenTtlDays
        );

        $accessToken = $user->createToken(
            name: 'access_token',
            abilities: ['access'],
            expiresAt: $accessTokenExpiresAt
        );

        $refreshToken = $user->createToken(
            name: 'refresh_token',
            abilities: ['refresh'],
            expiresAt: $refreshTokenExpiresAt
        );

        return [
            'user' => $user,

            'accessToken' => $accessToken->plainTextToken,

            'refreshToken' => $refreshToken->plainTextToken,

            'tokenType' => 'Bearer',

            'expiresIn' => $accessTokenTtlMinutes * 60,

            'refreshExpiresIn' => $refreshTokenTtlDays * 24 * 60 * 60
        ];
    }
}
