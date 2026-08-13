<?php

declare(strict_types=1);

namespace App\QueryFilters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

final class ActivitySearchFilter implements Filter
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

        $query->where(function (Builder $query) use ($search): void {
            $query
                ->where(
                    'name',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'description',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'notes',
                    'like',
                    "%{$search}%"
                );
        });
    }
}
