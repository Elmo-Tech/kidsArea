<?php

namespace App\Http\Resources\V1\Reports;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PayrollReportCollection extends ResourceCollection
{
    public $collects = AllPayrollReportResource::class;

    public function toArray(Request $request): array
    {
        return [
            'payrollReports' => $this->collection,

            'pagination' => [
                'perPage' => $this->resource->perPage(),
                'totalPages' => $this->resource->lastPage(),
                'currentPage' => $this->resource->currentPage(),
            ],
        ];
    }
}
