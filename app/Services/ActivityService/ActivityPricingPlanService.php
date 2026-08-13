<?php

declare(strict_types=1);

namespace App\Services\Activity;

use App\Enums\ActivityPricingTypeEnum;
use App\Exceptions\Activity\InvalidActivityPricingPlanDataException;
use App\Models\ActivityPricingPlan;
use App\QueryFilters\ActivityPricingPlanSearchFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ActivityPricingPlanService
{
    public function all(
        Request $request
    ): LengthAwarePaginator {
        $perPage = min(
            max((int) $request->integer('perPage', 15), 1),
            100
        );

        return QueryBuilder::for(ActivityPricingPlan::class)
            ->with([
                'activity',
            ])
            ->allowedFilters(
                AllowedFilter::custom(
                    'search',
                    new ActivityPricingPlanSearchFilter()
                ),

                AllowedFilter::exact(
                    'activityId',
                    'activity_id'
                ),

                AllowedFilter::exact(
                    'type'
                ),

                AllowedFilter::exact(
                    'status'
                ),
            )
            ->latest('id')
            ->paginate($perPage);
    }

    public function createPricingPlan(
        array $data
    ): ActivityPricingPlan {
        return DB::transaction(function () use ($data): ActivityPricingPlan {
            $this->validatePricingData($data);

            $normalizedData = $this->normalizePricingData(
                $data
            );

            $pricingPlan = ActivityPricingPlan::create([
                'activity_id' =>
                    $data['activityId'],

                'name' =>
                    $data['name'],

                'type' =>
                    $data['type'],

                'price' =>
                    $data['price'],

                'duration_value' =>
                    $normalizedData['durationValue'],

                'duration_unit' =>
                    $normalizedData['durationUnit'],

                'sessions_count' =>
                    $normalizedData['sessionsCount'],

                'status' =>
                    $data['status'] ?? 1,

                'notes' =>
                    $data['notes'] ?? null,
            ]);

            return $pricingPlan->load([
                'activity',
            ]);
        });
    }

    public function editPricingPlan(
        ActivityPricingPlan $pricingPlan
    ): ActivityPricingPlan {
        return $pricingPlan->load([
            'activity',
        ]);
    }

    public function updatePricingPlan(
        ActivityPricingPlan $pricingPlan,
        array $data
    ): ActivityPricingPlan {
        return DB::transaction(function () use (
            $pricingPlan,
            $data
        ): ActivityPricingPlan {
            $type = array_key_exists('type', $data)
                ? (int) $data['type']
                : $pricingPlan->type->value;

            $pricingData = [
                'type' => $type,

                'durationValue' =>
                    array_key_exists('durationValue', $data)
                        ? $data['durationValue']
                        : $pricingPlan->duration_value,

                'durationUnit' =>
                    array_key_exists('durationUnit', $data)
                        ? $data['durationUnit']
                        : $pricingPlan->duration_unit?->value,

                'sessionsCount' =>
                    array_key_exists('sessionsCount', $data)
                        ? $data['sessionsCount']
                        : $pricingPlan->sessions_count,
            ];

            $this->validatePricingData(
                $pricingData
            );

            $normalizedData = $this->normalizePricingData(
                $pricingData
            );

            $pricingPlan->update([
                'activity_id' =>
                    $data['activityId']
                    ?? $pricingPlan->activity_id,

                'name' =>
                    $data['name']
                    ?? $pricingPlan->name,

                'type' =>
                    $type,

                'price' =>
                    $data['price']
                    ?? $pricingPlan->price,

                'duration_value' =>
                    $normalizedData['durationValue'],

                'duration_unit' =>
                    $normalizedData['durationUnit'],

                'sessions_count' =>
                    $normalizedData['sessionsCount'],

                'status' =>
                    $data['status']
                    ?? $pricingPlan->status->value,

                'notes' =>
                    array_key_exists('notes', $data)
                        ? $data['notes']
                        : $pricingPlan->notes,
            ]);

            return $pricingPlan
                ->refresh()
                ->load([
                    'activity',
                ]);
        });
    }

    public function deletePricingPlan(
        ActivityPricingPlan $pricingPlan
    ): bool {
        return DB::transaction(
            fn (): bool => (bool) $pricingPlan->delete()
        );
    }

    private function validatePricingData(
        array $data
    ): void {
        $type = (int) $data['type'];

        if (
            $type === ActivityPricingTypeEnum::SUBSCRIPTION->value
            && (
                empty($data['durationValue'])
                || ! array_key_exists('durationUnit', $data)
                || $data['durationUnit'] === null
            )
        ) {
            throw new InvalidActivityPricingPlanDataException();
        }

        if (
            $type === ActivityPricingTypeEnum::PACKAGE->value
            && empty($data['sessionsCount'])
        ) {
            throw new InvalidActivityPricingPlanDataException();
        }
    }

    private function normalizePricingData(
        array $data
    ): array {
        $type = (int) $data['type'];

        return match ($type) {
            ActivityPricingTypeEnum::SUBSCRIPTION->value => [
                'durationValue' =>
                    $data['durationValue'] ?? null,

                'durationUnit' =>
                    $data['durationUnit'] ?? null,

                'sessionsCount' =>
                    null,
            ],

            ActivityPricingTypeEnum::PACKAGE->value => [
                'durationValue' =>
                    null,

                'durationUnit' =>
                    null,

                'sessionsCount' =>
                    $data['sessionsCount'] ?? null,
            ],

            ActivityPricingTypeEnum::HOURLY->value,
            ActivityPricingTypeEnum::SESSION->value => [
                'durationValue' =>
                    null,

                'durationUnit' =>
                    null,

                'sessionsCount' =>
                    null,
            ],
        };
    }
}
