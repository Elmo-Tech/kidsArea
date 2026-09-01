<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EmployeeAttendanceStatusEnum;
use App\Enums\SalaryTypeEnum;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeContract;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AugustMonthlyEmployeeAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $contracts = EmployeeContract::query()
                ->with('employee')
                ->where('salary_type', SalaryTypeEnum::MONTHLY->value)
                ->whereDate('start_date', '<=', '2026-08-31')
                ->where(function ($query): void {
                    $query->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', '2026-08-01');
                })
                ->get();

            foreach ($contracts as $contract) {
                $date = Carbon::create(2026, 8, 1)->startOfDay();
                $end = Carbon::create(2026, 8, 31)->startOfDay();

                while ($date->lessThanOrEqualTo($end)) {
                    if (! $this->isWorkDay($date, $contract->work_days ?? [])) {
                        $date->addDay();
                        continue;
                    }

                    $this->seedAttendance(
                        employeeId: (int) $contract->employee_id,
                        nationalId: (string) $contract->employee->national_id,
                        date: $date->format('Y-m-d')
                    );

                    $date->addDay();
                }
            }
        });
    }

    private function seedAttendance(
        int $employeeId,
        string $nationalId,
        string $date
    ): void {
        $absences = [
            '30001010000003' => ['2026-08-10', '2026-08-24'],
            '30001010000005' => ['2026-08-12'],
            '30001010000006' => ['2026-08-18'],
        ];

        $lateMinutes = match (true) {
            $nationalId === '30001010000003' && $date === '2026-08-05' => 15,
            $nationalId === '30001010000005' && $date === '2026-08-20' => 20,
            default => 0,
        };

        $earlyLeaveMinutes = match (true) {
            $nationalId === '30001010000006' && $date === '2026-08-27' => 30,
            default => 0,
        };

        $isAbsent = in_array(
            $date,
            $absences[$nationalId] ?? [],
            true
        );

        if ($isAbsent) {
            EmployeeAttendance::query()->updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'attendance_date' => $date,
                ],
                [
                    'check_in_at' => null,
                    'check_out_at' => null,
                    'worked_minutes' => 0,
                    'late_minutes' => 0,
                    'excused_late_minutes' => 0,
                    'early_leave_minutes' => 0,
                    'excused_early_leave_minutes' => 0,
                    'status' => EmployeeAttendanceStatusEnum::ABSENT->value,
                    'notes' => 'August 2026 attendance seeder',
                ]
            );

            return;
        }

        $checkInMinutes = 9 * 60 + $lateMinutes;
        $checkOutMinutes = 17 * 60 - $earlyLeaveMinutes;
        $workedMinutes = max(0, $checkOutMinutes - $checkInMinutes);

        EmployeeAttendance::query()->updateOrCreate(
            [
                'employee_id' => $employeeId,
                'attendance_date' => $date,
            ],
            [
                'check_in_at' => sprintf('%02d:%02d:00', intdiv($checkInMinutes, 60), $checkInMinutes % 60),
                'check_out_at' => sprintf('%02d:%02d:00', intdiv($checkOutMinutes, 60), $checkOutMinutes % 60),
                'worked_minutes' => $workedMinutes,
                'late_minutes' => $lateMinutes,
                'excused_late_minutes' => 0,
                'early_leave_minutes' => $earlyLeaveMinutes,
                'excused_early_leave_minutes' => 0,
                'status' => EmployeeAttendanceStatusEnum::PRESENT->value,
                'notes' => 'August 2026 attendance seeder',
            ]
        );
    }

    private function isWorkDay(Carbon $date, array $workDays): bool
    {
        if ($workDays === []) {
            return false;
        }

        $hasNumericDays = collect($workDays)
            ->every(fn ($day): bool => is_numeric($day));

        if ($hasNumericDays) {
            return in_array(
                $date->dayOfWeek,
                array_map('intval', $workDays),
                true
            );
        }

        return in_array(
            strtolower($date->format('l')),
            array_map(
                fn ($day): string => strtolower((string) $day),
                $workDays
            ),
            true
        );
    }
}
