<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Auth\LoginResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RefreshTokenController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    /**
     * Generate a new access and refresh token pair.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $result = $this->authService->refreshToken(
            $request->user()
        );

        if (
            isset($result['status_code']) &&
            in_array(
                'USER_INACTIVE',
                $result['status_code'],
                true
            )
        ) {
            return ApiResponse::error(
                __('messages.user_inactive'),
                [
                    'status_code' => $result['status_code'],
                ],
                HttpStatusCode::FORBIDDEN
            );
        }

        return ApiResponse::success(new LoginResource($result));
    }
}
