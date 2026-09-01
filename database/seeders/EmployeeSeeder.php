<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ContractStatusEnum;
use App\Enums\SalaryTypeEnum;
use App\Enums\StatusEnum;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeContract;
use App\Models\JobTitle;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = 'mans123456';

    public function run(): void
    {
        DB::transaction(function (): void {
            $employees = [
                /*
                |--------------------------------------------------------------------------
                | Trainers
                |--------------------------------------------------------------------------
                */
                [
                    'name' => 'أحمد محمد',
                    'username' => 'trainer1',
                    'email' => 'trainer1@kidsarea.test',
                    'phone' => '01000000001',
                    'nationalId' => '30001010000001',

                    'department' => 'السباحة',
                    'jobTitle' => 'مدرب سباحة',

                    'roles' => [
                        'employee',
                    ],

                    'salaryType' => SalaryTypeEnum::SESSION,
                    'basicSalary' => null,
                    'hourlyRate' => null,
                    'sessionRate' => 150,
                    'requiredMonthlyHours' => null,
                ],
                [
                    'name' => 'محمود علي',
                    'username' => 'trainer2',
                    'email' => 'trainer2@kidsarea.test',
                    'phone' => '01000000002',
                    'nationalId' => '30001010000002',

                    'department' => 'السباحة',
                    'jobTitle' => 'مدرب سباحة',

                    'roles' => [
                        'employee',
                    ],

                    'salaryType' => SalaryTypeEnum::MONTHLY_PLUS_SESSION,
                    'basicSalary' => 5000,
                    'hourlyRate' => null,
                    'sessionRate' => 150,
                    'requiredMonthlyHours' => 176,
                ],

                /*
                |--------------------------------------------------------------------------
                | Teachers
                |--------------------------------------------------------------------------
                */
                [
                    'name' => 'سارة أحمد',
                    'username' => 'teacher1',
                    'email' => 'teacher1@kidsarea.test',
                    'phone' => '01000000003',
                    'nationalId' => '30001010000003',

                    'department' => 'الحضانة',
                    'jobTitle' => 'معلمة',

                    'roles' => [
                        'employee',
                    ],

                    'salaryType' => SalaryTypeEnum::MONTHLY,
                    'basicSalary' => 6000,
                    'hourlyRate' => null,
                    'sessionRate' => null,
                    'requiredMonthlyHours' => 176,
                ],
                [
                    'name' => 'منى محمود',
                    'username' => 'teacher2',
                    'email' => 'teacher2@kidsarea.test',
                    'phone' => '01000000004',
                    'nationalId' => '30001010000004',

                    'department' => 'الحضانة',
                    'jobTitle' => 'معلمة',

                    'roles' => [
                        'employee',
                    ],

                    'salaryType' => SalaryTypeEnum::HOURLY,
                    'basicSalary' => null,
                    'hourlyRate' => 35,
                    'sessionRate' => null,
                    'requiredMonthlyHours' => null,
                ],

                /*
                |--------------------------------------------------------------------------
                | Cashiers
                |--------------------------------------------------------------------------
                */
                [
                    'name' => 'محمد حسن',
                    'username' => 'cashier1',
                    'email' => 'cashier1@kidsarea.test',
                    'phone' => '01000000005',
                    'nationalId' => '30001010000005',

                    'department' => 'الكافيه',
                    'jobTitle' => 'كاشير',

                    'roles' => [
                        'employee',
                        'cashier',
                    ],

                    'salaryType' => SalaryTypeEnum::MONTHLY,
                    'basicSalary' => 5000,
                    'hourlyRate' => null,
                    'sessionRate' => null,
                    'requiredMonthlyHours' => 176,
                ],
                [
                    'name' => 'علي إبراهيم',
                    'username' => 'cashier2',
                    'email' => 'cashier2@kidsarea.test',
                    'phone' => '01000000006',
                    'nationalId' => '30001010000006',

                    'department' => 'الكافيه',
                    'jobTitle' => 'كاشير',

                    'roles' => [
                        'employee',
                        'cashier',
                    ],

                    'salaryType' => SalaryTypeEnum::MONTHLY,
                    'basicSalary' => 5000,
                    'hourlyRate' => null,
                    'sessionRate' => null,
                    'requiredMonthlyHours' => 176,
                ],
            ];

            foreach ($employees as $data) {
                $this->createEmployee($data);
            }
        });
    }

    private function createEmployee(array $data): void
    {
        $department = Department::query()
            ->where('name', $data['department'])
            ->firstOrFail();

        $jobTitle = JobTitle::query()
            ->where('name', $data['jobTitle'])
            ->firstOrFail();

        $employee = Employee::query()->updateOrCreate(
            [
                'national_id' => $data['nationalId'],
            ],
            [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => 'المنصورة',
                'department_id' => $department->id,
                'job_title_id' => $jobTitle->id,
            ]
        );

        $user = User::query()->updateOrCreate(
            [
                'username' => $data['username'],
            ],
            [
                'employee_id' => $employee->id,
                'status' => StatusEnum::ACTIVE->value,
                'avatar' => null,
                'email_verified_at' => now(),
                'password' => Hash::make(
                    self::DEFAULT_PASSWORD
                ),
            ]
        );

        $user->syncRoles(
            $data['roles']
        );

        EmployeeContract::query()->updateOrCreate(
            [
                'employee_id' => $employee->id,
                'start_date' => '2026-08-01',
            ],
            [
                'end_date' => '2028-07-31',

                'salary_type' =>
                    $data['salaryType']->value,

                'basic_salary' =>
                    $data['basicSalary'],

                'hourly_rate' =>
                    $data['hourlyRate'],

                'session_rate' =>
                    $data['sessionRate'],

                'required_monthly_hours' =>
                    $data['requiredMonthlyHours'],

                'work_start_time' => '09:00:00',

                'work_end_time' => '17:00:00',

                'work_days' => [
                    0,
                    1,
                    2,
                    3,
                    4,
                    5,
                ],

                'status' =>
                    ContractStatusEnum::ACTIVE->value,

                'notes' =>
                    'عقد لمدة سنتين من أغسطس 2026',
            ]
        );
    }
}
