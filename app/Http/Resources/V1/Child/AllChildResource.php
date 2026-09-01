<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Child;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllChildResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,

            'birthDate' =>
                $this->birth_date?->format('Y-m-d'),

            'gender' => $this->gender,

            'notes' => $this->notes,

            'guardian' => [
                'name' =>
                    $this->guardian_name,

                'phone' =>
                    $this->guardian_phone,

                'relation' =>
                    $this->guardian_relation,

                'email' =>
                    $this->guardian_email,

                'notes' =>
                    $this->guardian_notes,
            ],

            'activityMemberships' =>
                $this->whenLoaded(
                    'activityMemberships',
                    function () {
                        return $this
                            ->activityMemberships
                            ->map(
                                function ($membership): array {
                                    return [
                                        'id' =>
                                            $membership->id,

                                        'activity' =>
                                            $membership->activity
                                                ? [
                                                    'id' =>
                                                        $membership
                                                            ->activity
                                                            ->id,

                                                    'name' =>
                                                        $membership
                                                            ->activity
                                                            ->name,
                                                ]
                                                : null,

                                        'pricingPlan' =>
                                            $membership->pricingPlan
                                                ? [
                                                    'id' =>
                                                        $membership
                                                            ->pricingPlan
                                                            ->id,

                                                    'name' =>
                                                        $membership
                                                            ->pricingPlan
                                                            ->name,
                                                ]
                                                : null,

                                        'startDate' =>
                                            $membership
                                                ->start_date
                                                ?->format('Y-m-d'),

                                        'endDate' =>
                                            $membership
                                                ->end_date
                                                ?->format('Y-m-d'),

                                        'status' =>
                                            $membership
                                                ->status
                                                ->value,
                                    ];
                                }
                            );
                    }
                ),

            'createdAt' =>
                $this->created_at,

        ];
    }
}
