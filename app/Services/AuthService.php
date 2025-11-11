<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Login user
     */
    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return [
                'success' => false,
                'message' => 'Invalid credentials',
            ];
        }

        // Create token (using Sanctum or custom token)
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user->load('roles', 'permissions'),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ];
    }

    /**
     * Logout user
     */
    public function logout($user): void
    {
        if ($user) {
            $user->tokens()->delete();
        }
    }

    /**
     * Refresh token
     */
    public function refreshToken($user): array
    {
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not authenticated',
            ];
        }

        // Delete old tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'success' => true,
            'message' => 'Token refreshed',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ];
    }
}

