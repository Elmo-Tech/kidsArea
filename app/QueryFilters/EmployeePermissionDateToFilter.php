<?php

declare(strict_types=1);

namespace App\QueryFilters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

final class EmployeePermissionDateToFilter implements Filter
{
    public function __invoke(
        Builder $query,
        mixed $value,
        string $property
    ): void {
        if (empty($value)) {
            return;
        }

        $query->whereDate(
            'permission_date',
            '<=',
            $value
        );
    }
}
