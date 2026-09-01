<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Invoice\InvoicePdfService;

final class InvoicePdfController extends Controller
{
    public function __construct(
        private readonly InvoicePdfService $invoicePdfService
    ) {}

    public function show(Invoice $invoice)
    {
        return $this->invoicePdfService->stream($invoice);
    }
}
