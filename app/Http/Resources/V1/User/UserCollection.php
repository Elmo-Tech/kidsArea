<?php


namespace App\Http\Resources\V1\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

final class UserCollection extends ResourceCollection
{
    /**
     * Resource used for every user item.
     *
     * @var class-string
     */
    public $collects = AllUserResource::class;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'users' => $this->collection,

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
