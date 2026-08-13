<?php

declare(strict_types=1);

namespace App\QueryFilters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

final class JobTitleSearchFilter implements Filter
{
    public function __invoke(
        Builder $query,
        mixed $value,
        string $property
    ): void {
        $search = trim((string) $value);

        if ($search === '') {
            return;
        }

        $query->where('name', 'like', "%{$search}%");
    }
}
