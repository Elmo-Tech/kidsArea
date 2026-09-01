<?php

use App\Enums\CashTransactionSourceEnum;
use App\Enums\CashTransactionTypeEnum;
use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use CreatedUpdatedByMigration;

    public function up(): void
    {
        Schema::create('cash_transactions', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('cash_shift_id')
                ->constrained('cash_shifts')
                ->cascadeOnDelete();

            $table->tinyInteger('type');

            $table->decimal('amount', 12, 2);

            $table->tinyInteger('source')
                ->default(CashTransactionSourceEnum::MANUAL->value);

            $table->nullableMorphs('sourceable');

            $table->dateTime('transaction_at');

            $table->text('notes')->nullable();

            $this->createdUpdatedByRelationship($table);

            $table->timestamps();

            $table->index([
                'cash_shift_id',
                'type',
            ]);

            $table->index([
                'cash_shift_id',
                'transaction_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};
