<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Login user
     * POST /api/auth/login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        try {
            $result = $this->authService->login($request->email, $request->password);

            if (!$result['success']) {
                return ApiResponse::error($result['message'], 401);
            }

            return ApiResponse::success($result['data'], 'Login successful');
        } catch (\Exception $e) {
            return ApiResponse::error('Login failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get authenticated user
     * GET /api/auth/me
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return ApiResponse::unauthorized();
        }

        $user->load('roles', 'permissions');

        return ApiResponse::success($user, 'User retrieved successfully');
    }

    /**
     * Logout user
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        try {
            $this->authService->logout($request->user());
            return ApiResponse::success(null, 'Logout successful');
        } catch (\Exception $e) {
            return ApiResponse::error('Logout failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Refresh token (if using JWT)
     * POST /api/auth/refresh
     */
    public function refresh(Request $request)
    {
        try {
            $result = $this->authService->refreshToken($request->user());
            
            if (!$result['success']) {
                return ApiResponse::error($result['message'], 401);
            }

            return ApiResponse::success($result['data'], 'Token refreshed successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Token refresh failed: ' . $e->getMessage(), 500);
        }
    }
}

