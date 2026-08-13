<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Exports\Reports\PayrollReportExport;
use App\Http\Controllers\Controller;
use App\Models\PayrollPeriod;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ExportPayrollReportController extends Controller
{
    public function __invoke(
        PayrollPeriod $period
    ): BinaryFileResponse {
        $fileName = sprintf(
            'payroll_report_%04d_%02d.xlsx',
            $period->year,
            $period->month
        );

        return Excel::download(
            new PayrollReportExport($period),
            $fileName
        );
    }
}
