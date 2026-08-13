<?php


namespace App\Services\JobTitle;

use App\Models\JobTitle;
use App\QueryFilters\JobTitleSearchFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class JobTitleService
{
    /**
     * Get paginated jobTitles.
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

        return QueryBuilder::for(JobTitle::query())
            ->allowedFilters(
                AllowedFilter::custom('search', new JobTitleSearchFilter()),
            )
            ->latest('id')
            ->paginate(
                perPage: $perPage,
                page: $page
            )
            ->appends($request->query());
    }

    /**
     * Create a jobTitle.
     */
    public function createJobTitle(array $data): JobTitle
    {
        return JobTitle::query()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * Get jobTitle for editing.
     */
    public function edit(JobTitle $jobTitle): JobTitle
    {
        return $jobTitle;
    }

    /**
     * Update a jobTitle.
     */
    public function update(
        JobTitle $jobTitle,
        array $data
    ): JobTitle {
        $jobTitle->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        return $jobTitle->refresh();
    }

    /**
     * Delete a jobTitle.
     */
    public function deleteJobTitle(JobTitle $jobTitle): bool
    {
        return (bool) $jobTitle->delete();
    }
}
