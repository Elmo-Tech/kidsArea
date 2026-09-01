<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Invoice\InvoicePdfService;
use Symfony\Component\HttpFoundation\Response;

final class InvoicePdfController extends Controller
{
    public function __construct(
        private readonly InvoicePdfService $invoicePdfService
    ) {}

    public function show(Invoice $invoice): Response
    {
        return $this->invoicePdfService->stream($invoice);
    }

    public function download(Invoice $invoice): Response
    {
        return $this->invoicePdfService->download($invoice);
    }
}
