<?php


namespace App\Http\Resources\V1\JobTitle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

final class JobTitleCollection extends ResourceCollection
{
    /**
     * Resource used for every job title item.
     *
     * @var class-string
     */
    public $collects = AllJobTitleResource::class;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'jobTitles' => $this->collection,

            'pagination' => [
                'perPage' => $this->resource->perPage(),
                'totalPages' => $this->resource->lastPage(),
                'currentPage' => $this->resource->currentPage(),
            ],
        ];
    }

    /**
     * Prevent Laravel from adding the default links and meta.
     *
     * @param array<string, mixed> $paginated
     * @param array<string, mixed> $default
     * @return array<string, mixed>
     */
    public function paginationInformation(
        Request $request,
        array $paginated,
        array $default
    ): array {
        return [];
    }
}
