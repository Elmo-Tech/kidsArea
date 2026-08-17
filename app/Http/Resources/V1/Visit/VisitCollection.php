<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Visit;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class VisitCollection extends ResourceCollection
{
    public $collects = AllVisitResource::class;

    public function toArray(Request $request): array
    {
        return [
            'visits' => $this->collection,

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
