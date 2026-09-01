<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Child;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ChildCollection extends ResourceCollection
{
    public $collects = ChildResource::class;

    public function toArray(Request $request): array
    {
        return [
            'children' => $this->collection,

            'pagination' => [
                'perPage' =>
                    $this->resource->perPage(),

                'totalPages' =>
                    $this->resource->lastPage(),

                'currentPage' =>
                    $this->resource->currentPage(),
            ],
        ];
    }
}
