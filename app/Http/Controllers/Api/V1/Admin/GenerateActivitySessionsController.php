<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ActivitySchedule\GenerateActivitySessionsRequest;
use App\Models\ActivitySchedule;
use App\Services\Activity\ActivityScheduleService;
use Illuminate\Http\JsonResponse;

final class GenerateActivitySessionsController extends Controller
{
    public function __construct(
        private readonly ActivityScheduleService $activityScheduleService
    ) {
    }

    public function __invoke(
        GenerateActivitySessionsRequest $request,
        ActivitySchedule $schedule
    ): JsonResponse {
        $generatedCount = $this->activityScheduleService
            ->generateSessions(
                $schedule,
                $request->validated('employeeIds', [])
            );

        return ApiResponse::success([
            'generatedCount' => $generatedCount,
        ]);
    }
}
