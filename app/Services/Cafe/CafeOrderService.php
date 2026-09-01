<?php

declare(strict_types=1);

namespace App\Services\Cafe;

use App\Enums\CafeOrderStatusEnum;
use App\Enums\VisitStatusEnum;
use App\Exceptions\Cafe\CafeOrderAlreadyCancelledException;
use App\Exceptions\Cafe\CafeOrderAlreadyCompletedException;
use App\Exceptions\Cafe\CafeOrderAlreadyConfirmedException;
use App\Exceptions\Cafe\CafeOrderDiscountExceedsSubtotalException;
use App\Exceptions\Cafe\CafeOrderHasNoItemsException;
use App\Exceptions\Cafe\CafeOrderNotEditableException;
use App\Exceptions\Cafe\CafeOrderVisitNotOpenException;
use App\Models\CafeOrder;
use App\Models\CafeProduct;
use App\Models\Visit;
use App\Services\Inventory\StockConsumptionService;
use App\Services\Payment\PaymentService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CafeOrderService
{
    public function __construct(
        private readonly StockConsumptionService $stockConsumptionService,
        private readonly PaymentService $paymentService
    ) {}

    public function all(Request $request): LengthAwarePaginator
    {
        $perPage = min(max((int) $request->integer('perPage', 15), 1), 100);

        return QueryBuilder::for(CafeOrder::class)
            ->with(['visit'])
            ->withCount(['items'])
            ->allowedFilters([
                AllowedFilter::exact('visitId', 'visit_id'),
                AllowedFilter::exact('status'),
            ])
            ->latest('id')
            ->paginate($perPage);
    }

    public function createOrder(array $data): CafeOrder
    {
        return DB::transaction(function () use ($data): CafeOrder {
            $visitId = $data['visitId'] ?? null;

            if ($visitId !== null) {
                $this->ensureVisitIsOpen((int) $visitId);
            }

            $order = CafeOrder::create([
                'visit_id' => $visitId,
                'order_number' => $this->generateOrderNumber(),
                'subtotal' => 0,
                'discount' => $data['discount'] ?? 0,
                'total' => 0,
                'status' => CafeOrderStatusEnum::DRAFT->value,
                'confirmed_at' => null,
                'completed_at' => null,
                'cancelled_at' => null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncItems($order, $data['items']);
            $this->recalculateTotals($order);
            $this->confirmDraftOrder($order);

            return $this->loadOrder($order->refresh());
        });
    }

    public function showOrder(CafeOrder $order): CafeOrder
    {
        return $this->loadOrder($order);
    }

    public function updateOrder(CafeOrder $order, array $data): CafeOrder
    {
        return DB::transaction(function () use ($order, $data): CafeOrder {
            $order = $this->lockOrder($order);

            $this->ensureOrderIsEditable($order);

            if (array_key_exists('visitId', $data) && $data['visitId'] !== null) {
                $this->ensureVisitIsOpen((int) $data['visitId']);
            }

            $order->update([
                'visit_id' => array_key_exists('visitId', $data) ? $data['visitId'] : $order->visit_id,
                'discount' => $data['discount'] ?? $order->discount,
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $order->notes,
            ]);

            if (array_key_exists('items', $data)) {
                $this->syncItems($order, $data['items']);
            }

            $this->recalculateTotals($order);

            return $this->loadOrder($order->refresh());
        });
    }

    public function confirmOrder(CafeOrder $order): CafeOrder
    {
        return DB::transaction(function () use ($order): CafeOrder {
            $order = $this->lockOrder($order);

            if ($order->status === CafeOrderStatusEnum::CONFIRMED) {
                throw new CafeOrderAlreadyConfirmedException();
            }

            if ($order->status === CafeOrderStatusEnum::COMPLETED) {
                throw new CafeOrderAlreadyCompletedException();
            }

            if ($order->status === CafeOrderStatusEnum::CANCELLED) {
                throw new CafeOrderAlreadyCancelledException();
            }

            if ($order->status !== CafeOrderStatusEnum::DRAFT) {
                throw new CafeOrderNotEditableException();
            }

            $this->confirmDraftOrder($order);

            return $this->loadOrder($order->refresh());
        });
    }

    public function completeOrder(CafeOrder $order): CafeOrder
    {
        return DB::transaction(function () use ($order): CafeOrder {
            $order = $this->lockOrder($order);

            if ($order->status === CafeOrderStatusEnum::COMPLETED) {
                throw new CafeOrderAlreadyCompletedException();
            }

            if ($order->status === CafeOrderStatusEnum::CANCELLED) {
                throw new CafeOrderAlreadyCancelledException();
            }

            if ($order->status !== CafeOrderStatusEnum::CONFIRMED) {
                throw new CafeOrderNotEditableException();
            }

            $order->update([
                'status' => CafeOrderStatusEnum::COMPLETED->value,
                'completed_at' => now(),
            ]);

            return $this->loadOrder($order->refresh());
        });
    }

    public function cancelOrder(CafeOrder $order, array $data): CafeOrder
    {
        return DB::transaction(function () use ($order, $data): CafeOrder {
            $order = $this->lockOrder($order);

            if ($order->status === CafeOrderStatusEnum::COMPLETED) {
                throw new CafeOrderAlreadyCompletedException();
            }

            if ($order->status === CafeOrderStatusEnum::CANCELLED) {
                throw new CafeOrderAlreadyCancelledException();
            }

            if ($order->status !== CafeOrderStatusEnum::DRAFT) {
                throw new CafeOrderNotEditableException();
            }

            $order->update([
                'status' => CafeOrderStatusEnum::CANCELLED->value,
                'cancelled_at' => now(),
                'notes' => $this->appendCancellationReason($order->notes, $data['reason']),
            ]);

            return $this->loadOrder($order->refresh());
        });
    }

    private function confirmDraftOrder(CafeOrder $order): void
    {
        if ($order->visit_id !== null) {
            $this->ensureVisitIsOpen((int) $order->visit_id);
        }

        $order->load(['items.product.ingredients.inventoryItem']);

        if ($order->items->isEmpty()) {
            throw new CafeOrderHasNoItemsException();
        }

        foreach ($order->items as $item) {
            $this->stockConsumptionService->consumeProduct(
                product: $item->product,
                quantity: (int) $item->quantity,
                sourceType: 'cafe_order',
                sourceId: $order->id
            );
        }

        $order->update([
            'status' => CafeOrderStatusEnum::CONFIRMED->value,
            'confirmed_at' => now(),
        ]);
    }

    private function syncItems(CafeOrder $order, array $items): void
    {
        $order->items()->delete();

        foreach ($items as $item) {
            $product = CafeProduct::query()->findOrFail($item['cafeProductId']);

            $quantity = (int) $item['quantity'];
            $unitPrice = (float) $product->selling_price;
            $total = round($unitPrice * $quantity, 2);

            $order->items()->create([
                'cafe_product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'total' => $total,
                'notes' => $item['notes'] ?? null,
            ]);
        }
    }

    private function recalculateTotals(
        CafeOrder $order
    ): void {
        $subtotal = round(
            (float) $order->items()->sum('total'),
            2
        );

        $discount = round(
            (float) $order->discount,
            2
        );

        if ($discount > $subtotal) {
            throw new CafeOrderDiscountExceedsSubtotalException();
        }

        $total = round(
            $subtotal - $discount,
            2
        );

        $order->update([
            'subtotal' => $subtotal,
            'total' => $total,
        ]);
    }
    private function ensureOrderIsEditable(CafeOrder $order): void
    {
        if ($order->status !== CafeOrderStatusEnum::DRAFT) {
            throw new CafeOrderNotEditableException();
        }
    }

    private function ensureVisitIsOpen(int $visitId): void
    {
        $visit = Visit::query()->findOrFail($visitId);

        if ($visit->status !== VisitStatusEnum::OPEN) {
            throw new CafeOrderVisitNotOpenException();
        }
    }

    private function lockOrder(CafeOrder $order): CafeOrder
    {
        return CafeOrder::query()
            ->whereKey($order->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function loadOrder(CafeOrder $order): CafeOrder
    {
        $order->load([
            'visit',
            'items.product',
        ]);

        if (
            $order->visit_id === null
            && $order->status === CafeOrderStatusEnum::COMPLETED
        ) {
            $order->load([
                'payments',
                'invoice',
            ]);

            $order->setAttribute(
                'payment_summary',
                $this->paymentService->getPaymentSummary($order)
            );
        }

        return $order;
    }

    private function generateOrderNumber(): string
    {
        $prefix = 'CAFE-' . now()->format('Ymd');

        $lastOrder = CafeOrder::query()
            ->where('order_number', 'like', "{$prefix}-%")
            ->lockForUpdate()
            ->latest('id')
            ->first();

        $sequence = 1;

        if ($lastOrder) {
            $lastSequence = (int) substr($lastOrder->order_number, -6);
            $sequence = $lastSequence + 1;
        }

        return sprintf('%s-%06d', $prefix, $sequence);
    }

    private function appendCancellationReason(?string $notes, string $reason): string
    {
        $cancellationNote = "Cancellation reason: {$reason}";

        if ($notes === null || trim($notes) === '') {
            return $cancellationNote;
        }

        return $notes . PHP_EOL . $cancellationNote;
    }
}
