<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            RoleAndPermissionsSeeder::class,
            SuperAdminSeeder::class,
            DepartmentSeeder::class,
            JobTitleSeeder::class,
            LeaveTypeSeeder::class,
            PayrollSettingSeeder::class,
            LeavePayrollPolicySeeder::class,
            EmployeeSeeder::class

        ]);
    }
}
