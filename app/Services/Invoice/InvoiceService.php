<?php

declare(strict_types=1);

namespace App\Services\Invoice;

use App\Models\ActivityUsage;
use App\Models\CafeOrder;
use App\Models\Invoice;
use App\Models\VisitCheckout;
use App\Models\ActivityMembership;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function createFor(Model $invoiceable): Invoice
    {
        return DB::transaction(function () use ($invoiceable): Invoice {
            $invoiceable = $this->lockInvoiceable($invoiceable);

            $existingInvoice = $invoiceable->invoice()->first();

            if ($existingInvoice) {
                return $existingInvoice;
            }

            $totals = $this->resolveTotals($invoiceable);

            $invoice = $invoiceable->invoice()->create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'total' => $totals['total'],
                'issued_at' => now(),
                'notes' => null,
            ]);

            return $invoice->load('invoiceable');
        });
    }

    public function show(Invoice $invoice): Invoice
    {
        $invoice->load('invoiceable');

        if ($invoice->invoiceable instanceof VisitCheckout) {
            $invoice->invoiceable->load([
                'visit.child',
                'visit.activityUsages.activity',
                'visit.cafeOrders.items.product',
            ]);
        }

        if ($invoice->invoiceable instanceof ActivityUsage) {
            $invoice->invoiceable->load([
                'child',
                'activity',
                'pricingPlan',
            ]);
        }

        if ($invoice->invoiceable instanceof CafeOrder) {
            $invoice->invoiceable->load([
                'items.product',
            ]);
        }

        if ($invoice->invoiceable instanceof ActivityMembership) {
            $invoice->invoiceable->load([
                'child',
                'activity',
                'pricingPlan',
            ]);
        }

        return $invoice;
    }
    private function resolveTotals(Model $invoiceable): array
    {
        return match (true) {
            $invoiceable instanceof VisitCheckout => [
                'subtotal' => (float) $invoiceable->subtotal,
                'discount' => (float) $invoiceable->discount,
                'total' => (float) $invoiceable->total,
            ],

            $invoiceable instanceof CafeOrder => [
                'subtotal' => (float) $invoiceable->subtotal,
                'discount' => (float) $invoiceable->discount,
                'total' => (float) $invoiceable->total,
            ],

            $invoiceable instanceof ActivityUsage => [
                'subtotal' => (float) $invoiceable->final_amount,
                'discount' => 0,
                'total' => (float) $invoiceable->final_amount,
            ],

            $invoiceable instanceof ActivityMembership => [
                'subtotal' => (float) $invoiceable->price,
                'discount' => 0,
                'total' => (float) $invoiceable->price,
            ],

            default => throw new \InvalidArgumentException(
                'Unsupported invoiceable model.'
            ),
        };
    }
    private function lockInvoiceable(Model $invoiceable): Model
    {
        return $invoiceable::query()
            ->whereKey($invoiceable->getKey())
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ymd');

        $lastInvoice = Invoice::query()
            ->where('invoice_number', 'like', "{$prefix}-%")
            ->lockForUpdate()
            ->latest('id')
            ->first();

        $sequence = 1;

        if ($lastInvoice) {
            $sequence = ((int) substr($lastInvoice->invoice_number, -6)) + 1;
        }

        return sprintf('%s-%06d', $prefix, $sequence);
    }

    public function findFor(Model $invoiceable): Invoice
    {
        $invoice = $invoiceable->invoice()->firstOrFail();

        return $this->show($invoice);
    }
}
