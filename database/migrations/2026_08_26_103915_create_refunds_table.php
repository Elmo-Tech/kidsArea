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
        Schema::create('refunds', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('payment_id')
                ->constrained('payments')
                ->restrictOnDelete();

            $table->foreignId('cash_register_id')
                ->nullable()
                ->constrained('cash_registers')
                ->restrictOnDelete();

            $table->foreignId('cash_shift_id')
                ->nullable()
                ->constrained('cash_shifts')
                ->restrictOnDelete();

            $table->decimal('amount', 12, 2);
            $table->dateTime('refunded_at');
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();

            $this->createdUpdatedByRelationship($table);

            $table->timestamps();

            $table->index('refunded_at');
            $table->index(['payment_id', 'refunded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
