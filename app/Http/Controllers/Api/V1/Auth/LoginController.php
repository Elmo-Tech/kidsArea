<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\V1\Auth\LoginResource;
class LoginController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    /**
     * Handle the incoming login request.
     */
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->validated()
        );

        if (isset($result['status_code']) && $result['status_code'] === ['INVALID_CREDENTIALS']) {
            return ApiResponse::error(
                __('auth.failed'),
                HttpStatusCode::UNAUTHORIZED,
            );
        }

        if (isset($result['status_code']) && $result['status_code'] === ['USER_INACTIVE']) {
            return ApiResponse::error(
                __('auth.user_inactive'),
                HttpStatusCode::FORBIDDEN,
            );
        }

        return ApiResponse::success(
            new LoginResource($result),
            "",
        );
    }
}
