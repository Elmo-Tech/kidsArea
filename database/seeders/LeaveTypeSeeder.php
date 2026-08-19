<?php

namespace Database\Seeders;

use App\Enums\StatusEnum;
use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $leaveTypes = [
            [
                'name' => 'إجازة سنوية',
                'description' => 'إجازة سنوية للموظف.',
                'status' => StatusEnum::ACTIVE->value,
            ],
            [
                'name' => 'إجازة مرضية',
                'description' => 'إجازة بسبب حالة مرضية.',
                'status' => StatusEnum::ACTIVE->value,
            ],
            [
                'name' => 'إجازة طارئة',
                'description' => 'إجازة لحالة طارئة أو ظرف مفاجئ.',
                'status' => StatusEnum::ACTIVE->value,
            ],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::query()->firstOrCreate(
                [
                    'name' => $leaveType['name'],
                ],
                [
                    'description' => $leaveType['description'],
                    'status' => $leaveType['status'],
                ]
            );
        }
    }
}
