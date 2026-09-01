<?php

declare(strict_types=1);

namespace App\Services\Child;

use App\Models\Child;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ChildService
{
    public function all(Request $request): LengthAwarePaginator
    {
        $perPage = min(
            max((int) $request->integer('perPage', 15), 1),
            100
        );

        return QueryBuilder::for(Child::class)
            ->allowedFilters(
                AllowedFilter::callback(
                    'search',
                    function ($query, $value): void {
                        $query->where(
                            function ($query) use ($value): void {
                                $query
                                    ->where(
                                        'name',
                                        'like',
                                        '%' . $value . '%'
                                    )
                                    ->orWhere(
                                        'guardian_name',
                                        'like',
                                        '%' . $value . '%'
                                    )
                                    ->orWhere(
                                        'guardian_phone',
                                        'like',
                                        '%' . $value . '%'
                                    );
                            }
                        );
                    }
                ),

                AllowedFilter::exact(
                    'gender'
                ),
            )
            ->latest('id')
            ->paginate($perPage);
    }

    public function create(array $data): Child
    {
        return DB::transaction(
            function () use ($data): Child {
                return Child::query()->create([
                    'name' => $data['name'],
                    'birth_date' => $data['birthDate'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'notes' => $data['notes'] ?? null,

                    'guardian_name' => $data['guardianName'],
                    'guardian_phone' => $data['guardianPhone'],
                    'guardian_relation' =>
                        $data['guardianRelation'] ?? null,
                    'guardian_email' =>
                        $data['guardianEmail'] ?? null,
                    'guardian_notes' =>
                        $data['guardianNotes'] ?? null,
                ]);
            }
        );
    }

    public function show(Child $child): Child
    {
        return $child->load([
            'activityMemberships.activity',
            'activityMemberships.pricingPlan',
        ]);
    }

    public function update(
        Child $child,
        array $data
    ): Child {
        return DB::transaction(
            function () use ($child, $data): Child {
                $child->update([
                    'name' =>
                        $data['name']
                        ?? $child->name,

                    'birth_date' =>
                        array_key_exists('birthDate', $data)
                            ? $data['birthDate']
                            : $child->birth_date,

                    'gender' =>
                        array_key_exists('gender', $data)
                            ? $data['gender']
                            : $child->gender,

                    'notes' =>
                        array_key_exists('notes', $data)
                            ? $data['notes']
                            : $child->notes,

                    'guardian_name' =>
                        $data['guardianName']
                        ?? $child->guardian_name,

                    'guardian_phone' =>
                        $data['guardianPhone']
                        ?? $child->guardian_phone,

                    'guardian_relation' =>
                        array_key_exists(
                            'guardianRelation',
                            $data
                        )
                            ? $data['guardianRelation']
                            : $child->guardian_relation,

                    'guardian_email' =>
                        array_key_exists(
                            'guardianEmail',
                            $data
                        )
                            ? $data['guardianEmail']
                            : $child->guardian_email,

                    'guardian_notes' =>
                        array_key_exists(
                            'guardianNotes',
                            $data
                        )
                            ? $data['guardianNotes']
                            : $child->guardian_notes,
                ]);

                return $child->refresh();
            }
        );
    }

    public function delete(Child $child): bool
    {
        return DB::transaction(
            fn (): bool => (bool) $child->delete()
        );
    }
}
