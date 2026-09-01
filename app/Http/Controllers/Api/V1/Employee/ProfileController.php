<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Employee;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Employee\EmployeeProfileResource;
use App\Services\Employee\EmployeeProfileService;
use Illuminate\Http\JsonResponse;

final class ProfileController extends Controller
{
    public function __construct(
        private readonly EmployeeProfileService $employeeProfileService
    ) {}

    public function __invoke(): JsonResponse
    {
        $employee = $this->employeeProfileService->getProfile();

        return ApiResponse::success(
            new EmployeeProfileResource($employee)
        );
    }
}
