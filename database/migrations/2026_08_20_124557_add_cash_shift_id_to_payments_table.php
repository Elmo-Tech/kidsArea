<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->foreignId('cash_shift_id')
                ->nullable()
                ->after('payment_method')
                ->constrained('cash_shifts')
                ->restrictOnDelete();

            $table->index([
                'payment_method',
                'cash_shift_id',
            ]);
        });

    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropIndex([
                'payment_method',
                'cash_shift_id',
            ]);

            $table->dropForeign([
                'cash_shift_id',
            ]);

            $table->dropColumn(
                'cash_shift_id'
            );
        });
    }
};
