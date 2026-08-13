<?php

declare(strict_types=1);

namespace App\Services\Activity;

use App\Models\Activity;
use App\QueryFilters\ActivitySearchFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ActivityService
{
    public function all(
        Request $request
    ): LengthAwarePaginator {
        $perPage = min(
            max((int) $request->integer('perPage', 15), 1),
            100
        );

        return QueryBuilder::for(Activity::class)
            ->withCount('pricingPlans')
            ->allowedFilters(
                AllowedFilter::custom(
                    'search',
                    new ActivitySearchFilter()
                ),

                AllowedFilter::exact(
                    'status'
                ),
            )
            ->latest('id')
            ->paginate($perPage);
    }

    public function createActivity(
        array $data
    ): Activity {
        return DB::transaction(function () use ($data): Activity {
            $activity = Activity::create([
                'name' => $data['name'],

                'description' =>
                    $data['description'] ?? null,

                'status' =>
                    $data['status'] ?? 1,

                'notes' =>
                    $data['notes'] ?? null,
            ]);

            return $activity->load([
                'pricingPlans',
            ]);
        });
    }

    public function editActivity(
        Activity $activity
    ): Activity {
        return $activity->load([
            'pricingPlans',
        ]);
    }

    public function updateActivity(
        Activity $activity,
        array $data
    ): Activity {
        return DB::transaction(function () use (
            $activity,
            $data
        ): Activity {
            $activity->update([
                'name' =>
                    $data['name']
                    ?? $activity->name,

                'description' =>
                    array_key_exists('description', $data)
                        ? $data['description']
                        : $activity->description,

                'status' =>
                    $data['status']
                    ?? $activity->status->value,

                'notes' =>
                    array_key_exists('notes', $data)
                        ? $data['notes']
                        : $activity->notes,
            ]);

            return $activity
                ->refresh()
                ->load([
                    'pricingPlans',
                ]);
        });
    }

    public function deleteActivity(
        Activity $activity
    ): bool {
        return DB::transaction(
            fn (): bool => (bool) $activity->delete()
        );
    }
}
