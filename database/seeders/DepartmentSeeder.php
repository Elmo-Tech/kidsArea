<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            'الإدارة',
            'السباحة',
            'الحضانة',
            'الكافيه',
        ];

        foreach ($departments as $department) {
            Department::query()->firstOrCreate([
                'name' => $department,
            ]);
        }
    }
}
