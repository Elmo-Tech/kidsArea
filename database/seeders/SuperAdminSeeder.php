<?php

namespace Database\Seeders;

use App\Enums\StatusEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    private const GUARD_NAME = 'web';

    private const SUPER_ADMIN_ROLE = 'super-admin';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminRole = Role::query()
            ->where('name', self::SUPER_ADMIN_ROLE)
            ->where('guard_name', self::GUARD_NAME)
            ->firstOrFail();

        $user = User::create([
                'username' => 'admin',
                'status' => StatusEnum::ACTIVE->value,
                'avatar' => null,
                'email_verified_at' => now(),
                'password' => Hash::make('mans123456'),
            ]);


        $user->syncRoles([
            $superAdminRole,
        ]);
    }
}
