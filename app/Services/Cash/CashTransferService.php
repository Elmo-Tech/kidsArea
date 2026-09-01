<?php

declare(strict_types=1);

namespace App\Services\Cash;

use App\Enums\CashShiftStatusEnum;
use App\Enums\CashTransactionSourceEnum;
use App\Enums\CashTransactionTypeEnum;
use App\Models\CashRegister;
use App\Models\CashShift;
use App\Models\CashTransaction;
use App\Models\CashTransfer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashTransferService
{
    public function transferCurrentUserShiftToMain(array $data): CashTransfer
    {
        return DB::transaction(function () use ($data): CashTransfer {
            $fromShift = CashShift::query()
                ->where('opened_by', Auth::id())
                ->where('status', CashShiftStatusEnum::CLOSED->value)
                ->whereNotNull('actual_closing_balance')
                ->where('actual_closing_balance', '>', 0)
                ->whereNotIn(
                    'id',
                    CashTransfer::query()->select('from_cash_shift_id')
                )
                ->latest('closed_at')
                ->lockForUpdate()
                ->firstOrFail();

            $mainRegister = CashRegister::query()
                ->where('is_main', true)
                ->lockForUpdate()
                ->firstOrFail();

            $amount = round((float) $fromShift->actual_closing_balance, 2);
            $transferredAt = now();

            $transfer = CashTransfer::query()->create([
                'from_cash_shift_id' => $fromShift->id,
                'to_cash_register_id' => $mainRegister->id,
                'amount' => $amount,
                'transferred_at' => $transferredAt,
                'notes' => $data['notes'] ?? null,
            ]);

            CashTransaction::query()->create([
                'cash_register_id' => $fromShift->cash_register_id,
                'cash_shift_id' => null,
                'type' => CashTransactionTypeEnum::OUT->value,
                'amount' => $amount,
                'source' => CashTransactionSourceEnum::TRANSFER->value,
                'sourceable_type' => CashTransfer::class,
                'sourceable_id' => $transfer->id,
                'transaction_at' => $transferredAt,
                'notes' => $data['notes'] ?? null,
            ]);

            CashTransaction::query()->create([
                'cash_register_id' => $mainRegister->id,
                'cash_shift_id' => null,
                'type' => CashTransactionTypeEnum::IN->value,
                'amount' => $amount,
                'source' => CashTransactionSourceEnum::TRANSFER->value,
                'sourceable_type' => CashTransfer::class,
                'sourceable_id' => $transfer->id,
                'transaction_at' => $transferredAt,
                'notes' => $data['notes'] ?? null,
            ]);

            return $transfer->load([
                'fromShift.register',
                'toRegister',
                'createdBy',
            ]);
        });
    }
}
