<?php

use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use CreatedUpdatedByMigration;

    public function up(): void
    {
        Schema::create('cash_transfers', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('from_cash_shift_id')
                ->constrained('cash_shifts')
                ->restrictOnDelete();

            $table->foreignId('to_cash_register_id')
                ->constrained('cash_registers')
                ->restrictOnDelete();

            $table->decimal('amount', 12, 2);

            $table->dateTime('transferred_at');

            $table->text('notes')->nullable();

            $this->createdUpdatedByRelationship($table);

            $table->timestamps();

            $table->index([
                'from_cash_shift_id',
                'to_cash_register_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_transfers');
    }
};
