<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Employee;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Employee\MyActivitySessionCollection;
use App\Services\Employee\MyActivitySessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\V1\Employee\ActivitySessionAttendanceResource;
use App\Models\ActivitySession;

final class ActivitySessionController extends Controller
{
    public function __construct(
        private readonly MyActivitySessionService $activitySessionService
    ) {}

    public function index(
        Request $request
    ): JsonResponse {
        return ApiResponse::success(
            new MyActivitySessionCollection(
                $this->activitySessionService->all(
                    $request
                )
            )
        );
    }

    public function attendance(
        ActivitySession $activitySession
    ): JsonResponse {
        $session = $this->activitySessionService
            ->attendance($activitySession);

        return ApiResponse::success(
            new ActivitySessionAttendanceResource(
                $session
            )
        );
    }
}
