<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\User\StoreUserRequest;
use App\Http\Requests\V1\User\UpdateUserRequest;
use App\Http\Resources\V1\User\UserCollection;
use App\Http\Resources\V1\User\UserResource;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {
    }

    /**
     * Display paginated users.
     *
     * GET /api/v1/admin/users
     */
    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->all($request);

        return ApiResponse::success(
            new UserCollection($users)
        );
    }

    /**
     * Create a new user.
     *
     * POST /api/v1/admin/users
     */
    public function store(
        StoreUserRequest $request
    ): JsonResponse {
        $user = $this->userService->createUser(
            $request->validated()
        );

        return ApiResponse::success(
            new UserResource($user),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    /**
     * Display user data for editing.
     *
     * GET /api/v1/admin/users/{user}/edit
     */
    public function show(
        User $user
    ): JsonResponse {
        $user = $this->userService->editUser($user);

        return ApiResponse::success(
            new UserResource($user)
        );
    }

    /**
     * Update user.
     *
     * PUT/PATCH /api/v1/admin/users/{user}
     */
    public function update(
        UpdateUserRequest $request,
        User $user
    ): JsonResponse {
        $user = $this->userService->updateUser(
            $user,
            $request->validated()
        );

        return ApiResponse::success(
            new UserResource($user),
            __('messages.updated_successfully')
        );
    }

    /**
     * Delete user.
     *
     * DELETE /api/v1/admin/users/{user}
     */
    public function destroy(
        User $user
    ): JsonResponse {
        $this->userService->deleteUser($user);

        return ApiResponse::success(
            null,
            __('messages.deleted_successfully')
        );
    }
}
