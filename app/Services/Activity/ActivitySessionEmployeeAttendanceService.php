<?php

declare(strict_types=1);

namespace App\Services\Activity;

use App\Enums\ActivitySessionEmployeeAttendanceStatusEnum;
use App\Exceptions\ActivitySessionEmployeeAttendance\AttendanceAlreadyExistsException;
use App\Exceptions\ActivitySessionEmployeeAttendance\EmployeeNotAssignedToSessionException;
use App\Models\ActivitySession;
use App\Models\ActivitySessionEmployeeAttendance;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ActivitySessionEmployeeAttendanceService
{
    public function all(Request $request): LengthAwarePaginator
    {
        $perPage = min(max((int) $request->integer('perPage', 15), 1), 100);

        return QueryBuilder::for(ActivitySessionEmployeeAttendance::class)
            ->with([
                'session',
                'employee.jobTitle',
            ])
            ->allowedFilters([
                AllowedFilter::exact('activitySessionId', 'activity_session_id'),
                AllowedFilter::exact('employeeId', 'employee_id'),
                AllowedFilter::exact('status'),
            ])
            ->latest('id')
            ->paginate($perPage);
    }

    public function createAttendance(array $data): ActivitySessionEmployeeAttendance
    {
        return DB::transaction(function () use ($data): ActivitySessionEmployeeAttendance {
            $session = ActivitySession::query()
                ->with('employees')
                ->findOrFail($data['activitySessionId']);

            $this->ensureEmployeeAssigned($session, (int) $data['employeeId']);

            $exists = ActivitySessionEmployeeAttendance::query()
                ->where('activity_session_id', $session->id)
                ->where('employee_id', $data['employeeId'])
                ->exists();

            if ($exists) {
                throw new AttendanceAlreadyExistsException();
            }

            $checkIn = isset($data['checkInAt'])
                ? Carbon::parse($data['checkInAt'])
                : null;

            $checkOut = isset($data['checkOutAt'])
                ? Carbon::parse($data['checkOutAt'])
                : null;

            $lateMinutes = $this->calculateLateMinutes(
                $session,
                $checkIn
            );

            $attendance = ActivitySessionEmployeeAttendance::create([
                'activity_session_id' => $session->id,
                'employee_id' => $data['employeeId'],
                'check_in_at' => $checkIn,
                'check_out_at' => $checkOut,
                'late_minutes' => $lateMinutes,
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]);

            return $this->loadAttendance($attendance);
        });
    }

    public function showAttendance(
        ActivitySessionEmployeeAttendance $attendance
    ): ActivitySessionEmployeeAttendance {
        return $this->loadAttendance($attendance);
    }

    public function updateAttendance(
        ActivitySessionEmployeeAttendance $attendance,
        array $data
    ): ActivitySessionEmployeeAttendance {
        return DB::transaction(function () use ($attendance, $data): ActivitySessionEmployeeAttendance {
            $attendance = ActivitySessionEmployeeAttendance::query()
                ->whereKey($attendance->id)
                ->lockForUpdate()
                ->firstOrFail();

            $attendance->load('session');

            $checkIn = array_key_exists('checkInAt', $data)
                ? ($data['checkInAt'] !== null ? Carbon::parse($data['checkInAt']) : null)
                : $attendance->check_in_at;

            $checkOut = array_key_exists('checkOutAt', $data)
                ? ($data['checkOutAt'] !== null ? Carbon::parse($data['checkOutAt']) : null)
                : $attendance->check_out_at;

            $lateMinutes = $this->calculateLateMinutes(
                $attendance->session,
                $checkIn
            );

            $attendance->update([
                'check_in_at' => $checkIn,
                'check_out_at' => $checkOut,
                'late_minutes' => $lateMinutes,
                'status' => $data['status'] ?? $attendance->status->value,
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $attendance->notes,
            ]);

            return $this->loadAttendance($attendance->refresh());
        });
    }

    public function deleteAttendance(
        ActivitySessionEmployeeAttendance $attendance
    ): bool {
        return DB::transaction(
            fn (): bool => (bool) $attendance->delete()
        );
    }

    private function ensureEmployeeAssigned(
        ActivitySession $session,
        int $employeeId
    ): void {
        $assigned = $session->employees
            ->contains('id', $employeeId);

        if (! $assigned) {
            throw new EmployeeNotAssignedToSessionException();
        }
    }

    private function calculateLateMinutes(
        ActivitySession $session,
        ?Carbon $checkIn
    ): int {
        if ($checkIn === null) {
            return 0;
        }

        $scheduledStart = Carbon::parse(
            $session->session_date->format('Y-m-d')
            . ' '
            . $session->start_time
        );

        if ($checkIn->lessThanOrEqualTo($scheduledStart)) {
            return 0;
        }

        return (int) $scheduledStart->diffInMinutes($checkIn);
    }

    private function loadAttendance(
        ActivitySessionEmployeeAttendance $attendance
    ): ActivitySessionEmployeeAttendance {
        return $attendance->load([
            'session',
            'employee.jobTitle',
        ]);
    }
}
