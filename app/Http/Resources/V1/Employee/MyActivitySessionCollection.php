<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MyActivitySessionCollection extends ResourceCollection
{
    public $collects = MyActivitySessionResource::class;

    public function toArray(Request $request): array
    {
        return [
            'myActivitySessions' => $this->collection,

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
