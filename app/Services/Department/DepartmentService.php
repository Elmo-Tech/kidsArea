<?php

declare(strict_types=1);

namespace App\Services\Department;

use App\Models\Department;
use App\QueryFilters\DepartmentSearchFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class DepartmentService
{
    /**
     * Get paginated departments.
     */
    public function all(Request $request): LengthAwarePaginator
    {
        $page = max(
            (int) $request->integer('page', 1),
            1
        );

        $perPage = min(
            max((int) $request->integer('perPage', 15), 1),
            100
        );

        return QueryBuilder::for(Department::query())
            ->allowedFilters(
                AllowedFilter::custom('search', new DepartmentSearchFilter()),
            )
            ->latest('id')
            ->paginate(
                perPage: $perPage,
                page: $page
            )
            ->appends($request->query());
    }

    /**
     * Create a department.
     */
    public function createDepartment(array $data): Department
    {
        return Department::query()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * Get department for editing.
     */
    public function edit(Department $department): Department
    {
        return $department;
    }

    /**
     * Update a department.
     */
    public function update(
        Department $department,
        array $data
    ): Department {
        $department->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        return $department->refresh();
    }

    /**
     * Delete a department.
     */
    public function deleteDepartment(Department $department): bool
    {
        return (bool) $department->delete();
    }
}
