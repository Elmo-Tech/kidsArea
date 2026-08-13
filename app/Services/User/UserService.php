<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Enums\StatusEnum;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class UserService
{
    public function all(Request $request): LengthAwarePaginator
    {
        $perPage = min(
            max((int) ($filters['perPage'] ?? 15), 1),
            100
        );

        return QueryBuilder::for(User::class)
            ->with([
                'roles',
                'employee',
            ])
            ->allowedFilters(
                AllowedFilter::partial('username'),
                AllowedFilter::exact('status'),
                AllowedFilter::callback(
                    'role',
                    function ($query, mixed $value): void {
                        $query->role((string) $value);
                    }
                ),
            )
            ->latest('id')
            ->paginate($perPage);
    }

    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $employeeId = $data['employeeId'] ?? null;

            if ($employeeId !== null) {
                $this->ensureEmployeeCanBeLinked((int) $employeeId);
            }

            $avatarPath = null;

            if (
                isset($data['avatar'])
                && $data['avatar'] instanceof UploadedFile
            ) {
                $avatarPath = $this->storeAvatar($data['avatar']);
            }

            try {
                $user = User::create([
                    'employee_id' => $employeeId,
                    'username' => $data['username'],
                    'password' => $data['password'],
                    'status' => $data['status'],
                    'avatar' => $avatarPath,
                ]);

                $user->syncRoles($this->normalizeRoles($data));

                return $user->load([
                    'employee.department',
                    'employee.jobTitle',
                    'roles',
                ]);
            } catch (\Throwable $exception) {
                if ($avatarPath !== null) {
                    Storage::disk('public')->delete($avatarPath);
                }

                throw $exception;
            }
        });
    }

    public function editUser(User $user): User
    {
        return $user->load([
            'employee.department',
            'employee.jobTitle',
            'roles.permissions',
        ]);
    }

    public function updateUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $oldAvatarPath = $user->getRawOriginal('avatar');
            $newAvatarPath = null;

            if (array_key_exists('employeeId', $data)) {
                $employeeId = $data['employeeId'];

                if ($employeeId !== null) {
                    $this->ensureEmployeeCanBeLinked(
                        employeeId: (int) $employeeId,
                        ignoredUserId: $user->id
                    );
                }
            }

            if (
                isset($data['avatar'])
                && $data['avatar'] instanceof UploadedFile
            ) {
                $newAvatarPath = $this->storeAvatar($data['avatar']);
            }

            try {
                $updateData = [];

                if (array_key_exists('employeeId', $data)) {
                    $updateData['employee_id'] = $data['employeeId'];
                }

                if (array_key_exists('username', $data)) {
                    $updateData['username'] = $data['username'];
                }

                if (array_key_exists('status', $data)) {
                    $updateData['status'] = $data['status'];
                }

                if (
                    array_key_exists('password', $data)
                    && filled($data['password'])
                ) {
                    $updateData['password'] = $data['password'];
                }

                if ($newAvatarPath !== null) {
                    $updateData['avatar'] = $newAvatarPath;
                }

                if (
                    array_key_exists('removeAvatar', $data)
                    && $data['removeAvatar'] === true
                ) {
                    $updateData['avatar'] = null;
                }

                $user->update($updateData);

                if (
                    array_key_exists('roles', $data)
                    || array_key_exists('role', $data)
                    || array_key_exists('roleId', $data)
                ) {
                    $user->syncRoles($this->normalizeRoles($data));
                }

                $passwordChanged = array_key_exists('password', $data)
                    && filled($data['password']);

                $userDisabled = array_key_exists('status', $data)
                    && ! $user->isActive();

                if ($passwordChanged || $userDisabled) {
                    $user->tokens()->delete();
                }

                if (
                    $newAvatarPath !== null
                    && $oldAvatarPath !== null
                ) {
                    Storage::disk('public')->delete($oldAvatarPath);
                }

                if (
                    ($data['removeAvatar'] ?? false) === true
                    && $oldAvatarPath !== null
                ) {
                    Storage::disk('public')->delete($oldAvatarPath);
                }

                return $user
                    ->refresh()
                    ->load([
                        'employee.department',
                        'employee.jobTitle',
                        'roles',
                    ]);
            } catch (\Throwable $exception) {
                if ($newAvatarPath !== null) {
                    Storage::disk('public')->delete($newAvatarPath);
                }

                throw $exception;
            }
        });
    }

    public function deleteUser(User $user): bool
    {
        return DB::transaction(function () use ($user): bool {
            $avatarPath = $user->getRawOriginal('avatar');

            $user->tokens()->delete();
            $user->syncRoles([]);

            $deleted = (bool) $user->delete();

            if ($deleted && $avatarPath !== null) {
                Storage::disk('public')->delete($avatarPath);
            }

            return $deleted;
        });
    }

    private function ensureEmployeeCanBeLinked(
        int $employeeId,
        ?int $ignoredUserId = null
    ): void {
        $employeeExists = Employee::query()
            ->whereKey($employeeId)
            ->exists();

        if (! $employeeExists) {
            throw ValidationException::withMessages([
                'employeeId' => 'الموظف المحدد غير موجود.',
            ]);
        }

        $hasUserAccount = User::query()
            ->where('employee_id', $employeeId)
            ->when(
                $ignoredUserId !== null,
                fn ($query) => $query->whereKeyNot($ignoredUserId)
            )
            ->exists();

        if ($hasUserAccount) {
            throw ValidationException::withMessages([
                'employeeId' => 'هذا الموظف مرتبط بحساب مستخدم بالفعل.',
            ]);
        }
    }

    private function normalizeRoles(array $data): array
    {
        if (array_key_exists('roles', $data)) {
            return array_values((array) $data['roles']);
        }

        if (array_key_exists('role', $data)) {
            return [$data['role']];
        }

        if (array_key_exists('roleId', $data)) {
            return [(int) $data['roleId']];
        }

        return [];
    }

    private function storeAvatar(UploadedFile $avatar): string
    {
        return $avatar->store('users/avatars', 'public');
    }
}
