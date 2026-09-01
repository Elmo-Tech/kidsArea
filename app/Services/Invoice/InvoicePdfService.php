<?php

declare(strict_types=1);

namespace App\Services\Invoice;

use App\Models\ActivityUsage;
use App\Models\CafeOrder;
use App\Models\Invoice;
use App\Models\VisitCheckout;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class InvoicePdfService
{
    public function stream(Invoice $invoice): Response
    {
        $invoice = $this->prepareInvoice($invoice);

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
        ])->setPaper('a4');

        return $pdf->stream(
            "{$invoice->invoice_number}.pdf"
        );
    }

    public function download(Invoice $invoice): Response
    {
        $invoice = $this->prepareInvoice($invoice);

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
        ])->setPaper('a4');

        return $pdf->download(
            "{$invoice->invoice_number}.pdf"
        );
    }

    private function prepareInvoice(Invoice $invoice): Invoice
    {
        $invoice->load('invoiceable');

        if ($invoice->invoiceable instanceof VisitCheckout) {
            $invoice->invoiceable->load([
                'visit.child',
                'visit.activityUsages.activity',
                'visit.cafeOrders.items.product',
                'payments',
            ]);
        }

        if ($invoice->invoiceable instanceof ActivityUsage) {
            $invoice->invoiceable->load([
                'child',
                'activity',
                'pricingPlan',
                'payments',
            ]);
        }

        if ($invoice->invoiceable instanceof CafeOrder) {
            $invoice->invoiceable->load([
                'items.product',
                'payments',
            ]);
        }

        return $invoice;
    }
}
