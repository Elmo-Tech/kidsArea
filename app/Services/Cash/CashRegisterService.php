<?php

declare(strict_types=1);

namespace App\Services\Cash;

use App\Enums\CashRegisterStatusEnum;
use App\Exceptions\Cash\CashRegisterCannotBeDeletedException;
use App\Exceptions\Cash\MainCashRegisterAlreadyExistsException;
use App\Models\CashRegister;
use App\Models\CashTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CashRegisterService
{
    public function all(Request $request): LengthAwarePaginator
    {
        $perPage = min(max((int) $request->integer('perPage', 15), 1), 100);

        return QueryBuilder::for(CashRegister::class)
            ->withExists('openShift')
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::exact('status'),
            ])
            ->latest('id')
            ->paginate($perPage);
    }

    public function create(array $data): CashRegister
    {
        return DB::transaction(function () use ($data): CashRegister {
            if (! empty($data['isMain'])) {
                $this->ensureOnlyOneMainRegister();
            }

            $register = CashRegister::query()->create([
                'name' => $data['name'],
                'status' => $data['status'] ?? CashRegisterStatusEnum::ACTIVE->value,
                'notes' => $data['notes'] ?? null,
                'is_main' => $data['isMain'] ?? false,
            ]);

            return $this->loadRegister($register);
        });
    }

    public function show(CashRegister $cashRegister): CashRegister
    {
        return $this->loadRegister($cashRegister);
    }

    public function update(CashRegister $cashRegister, array $data): CashRegister
    {
        return DB::transaction(function () use ($cashRegister, $data): CashRegister {
            $cashRegister = CashRegister::query()
                ->whereKey($cashRegister->id)
                ->lockForUpdate()
                ->firstOrFail();

            $cashRegister->update([
                'name' => $data['name'] ?? $cashRegister->name,
                'status' => $data['status'] ?? $cashRegister->status->value,
                'notes' => array_key_exists('notes', $data)
                    ? $data['notes']
                    : $cashRegister->notes,
            ]);

            return $this->loadRegister($cashRegister->refresh());
        });
    }

    public function delete(CashRegister $cashRegister): bool
    {
        return DB::transaction(function () use ($cashRegister): bool {
            $cashRegister = CashRegister::query()
                ->whereKey($cashRegister->id)
                ->lockForUpdate()
                ->firstOrFail();

            $hasShifts = $cashRegister->shifts()->exists();

            $hasTransactions = CashTransaction::query()
                ->where('cash_register_id', $cashRegister->id)
                ->exists();

            if ($cashRegister->is_main || $hasShifts || $hasTransactions) {
                throw new CashRegisterCannotBeDeletedException();
            }

            return (bool) $cashRegister->delete();
        });
    }

    public function getMainRegister(): CashRegister
    {
        $register = CashRegister::query()
            ->where('is_main', true)
            ->firstOrFail();

        return $this->loadRegister($register);
    }

    private function loadRegister(CashRegister $cashRegister): CashRegister
    {
        return $cashRegister->load([
            'openShift.openedBy',
        ]);
    }

    private function ensureOnlyOneMainRegister(): void
    {
        if (CashRegister::query()->where('is_main', true)->exists()) {
            throw new MainCashRegisterAlreadyExistsException();
        }
    }
}
