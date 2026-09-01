<?php

declare(strict_types=1);

namespace App\Services\Invoice;

use App\Models\ActivityUsage;
use App\Models\CafeOrder;
use App\Models\Invoice;
use App\Models\VisitCheckout;
use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfService
{
    public function stream(Invoice $invoice)
    {
        $invoice = $this->prepareInvoice($invoice);

        $html = view('pdf.invoice', [
            'invoice' => $invoice,
        ])->render();

        $arabic = new Arabic();

        $html = $arabic->utf8Glyphs(
            $html,
            100,
            false,
            true
        );

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4');

        return $pdf->stream(
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
