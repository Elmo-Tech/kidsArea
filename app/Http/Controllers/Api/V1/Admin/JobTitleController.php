<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\JobTitle\StoreJobTitleRequest;
use App\Http\Requests\V1\JobTitle\UpdateJobTitleRequest;
use App\Http\Resources\V1\JobTitle\JobTitleCollection;
use App\Http\Resources\V1\JobTitle\JobTitleResource;
use App\Models\JobTitle;
use App\Services\JobTitle\JobTitleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class JobTitleController extends Controller
{
    public function __construct(
        private readonly JobTitleService $jobTitleService
    ) {
    }

    /**
     * Display paginated jobTitles.
     *
     * GET /api/v1/admin/jobTitles
     */
    public function index(Request $request): JsonResponse
    {
        $jobTitles = $this->jobTitleService->all($request);

        return ApiResponse::success(
            new JobTitleCollection($jobTitles)
        );
    }

    /**
     * Create a new jobTitle.
     *
     * POST /api/v1/admin/jobTitles
     */
    public function store(
        StoreJobTitleRequest $request
    ): JsonResponse {
        $jobTitle = $this->jobTitleService->createJobTitle(
            $request->validated()
        );

        return ApiResponse::success(
            new JobTitleResource($jobTitle),
            __('messages.created_successfully')
        );
    }

    /**
     * Display jobTitle data for editing.
     *
     * GET /api/v1/admin/jobTitles/{jobTitle}/edit
     */
    public function show(
        JobTitle $jobTitle
    ): JsonResponse {
        $jobTitle = $this->jobTitleService->edit($jobTitle);

        return ApiResponse::success(
            new JobTitleResource($jobTitle)
        );
    }

    /**
     * Update jobTitle.
     *
     * PUT/PATCH /api/v1/admin/jobTitles/{jobTitle}
     */
    public function update(
        UpdateJobTitleRequest $request,
        JobTitle $jobTitle
    ): JsonResponse {
        $jobTitle = $this->jobTitleService->update(
            $jobTitle,
            $request->validated()
        );

        return ApiResponse::success(
            new JobTitleResource($jobTitle),
            __('messages.updated_successfully')
        );
    }

    /**
     * Delete jobTitle.
     *
     * DELETE /api/v1/admin/jobTitles/{jobTitle}
     */
    public function destroy(
        JobTitle $jobTitle
    ): JsonResponse {
        $this->jobTitleService->deleteJobTitle($jobTitle);

        return ApiResponse::success(
            null,
            __('messages.deleted_successfully')
        );
    }
}
